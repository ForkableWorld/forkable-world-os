<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Services;
use Analytics;
use App\Exceptions\BadRequestException;
use App\Exceptions\ExceptionHandler;
use App\Logging\QMLog;
use App\Models\Application;
use App\Models\BillingPlan;
use App\Models\User;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\Utils\AppMode;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Support\Collection;
use Log;
use LogicException;
use Request;
use Session;
use Stripe\ApiResource;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Token;
/** Class StripeService
 * @package App\Services
 */
class StripeService {
    //public const MONTHLY = 'Monthly';
    //public const YEARLY = 'Yearly';  // Not sure why these are here?
    public const MONTHLY = 'monthly7';
    public const YEARLY = 'yearly60';
    private $postBody;
    private $user;
    public function __construct(){
        $secret = \App\Utils\Env::get('STRIPE_API_SECRET');
        Stripe::setApiKey($secret);
    }
    /**
     * @param int $userId
     * @return int
     */
    public function recordRefund($userId){
        $purchaseId = DB::table('purchases')->where('user_id', $userId)->update([
                'refunded_at' => date("c"),
                'client_id'   => StripeService::getClientId()
            ]);
        return $purchaseId;
    }
    /**
     * @param array $postBody
     * @param User $u
     * @return array|int
     */
    public function createUserSubscription(array $postBody, User $u){
        $this->user = $u;
        $this->postBody = $postBody;
        if($u->stripe_active){
            $errorName = 'User already upgraded';
            $errorMessage = 'User already upgraded to '.$u->stripe_plan.' plan via '.$u->subscription_provider.'.';
            $metaData = ['user' => $u];
            QMLog::error($errorName, $metaData, $errorMessage);
        }
        if($this->isTestCard()){
            $u->setStripActive(true);
            return 1;
        }
        try {
            $s = $u->newSubscription('main', $this->getProductId());
            if($this->getCouponCode()){
                $s->withCoupon($this->getCouponCode());
            }
            if(empty($this->getCouponCode()) && empty($this->getCreditCardTokenId())){
                return false;
            }
            $productId = $this->getProductId();
            $s->create($this->getCreditCardTokenId(), ['email' => $u->user_email]);
            $purchaseId = $this->upgrade($productId, $u);
            return $this->getResponseData(null, $purchaseId);
        } catch (Exception $e) {
            if(AppMode::isTestingOrStaging()){
                /** @var LogicException $e */
                throw $e;
            }
            return $this->getResponseData($e);
        }
    }
    /**
     * @return string|null
     */
    private function getStripePlanProductId(){
        $value = $this->getPostBodyParameter('productId');
        return self::standardizeProductId($value);
    }
    /**
     * @return array
     */
    public function getPostBody(){
        return $this->postBody;
    }
    /**
     * @param $parameterName
     * @param null $default
     * @return mixed
     */
    public function getPostBodyParameter(string $parameterName, $default = null){
        $camel = QMStr::toCamelCase($parameterName);
        if(array_key_exists($camel, $this->postBody)){
            return $this->postBody[$camel];
        }
        $snake = QMStr::snakize($parameterName);
        if(array_key_exists($snake, $this->postBody)){
            return $this->postBody[$snake];
        }
        return $default;
    }
    /**
     * @return string|null
     */
    private function getCouponCode(){
        if($this->getPostBodyParameter('coupon')){
            return $this->getPostBodyParameter('coupon');
        }
        if($this->getPostBodyParameter('couponCode')){
            return $this->getPostBodyParameter('couponCode');
        }
        return null;
    }
    /**
     * @return bool
     */
    private function isTestCard(){
        return isset($this->postBody["stripeToken"]["card"]["last4"]) && $this->postBody["stripeToken"]["card"]["last4"] === "4242";
    }
    /**
     * @return string|null
     */
    private function getProductId(){
        $productId = null;
        if($this->getPostBodyParameter('productId')){
            $productId = $this->getPostBodyParameter('productId');
        }
        if($this->getPostBodyParameter('plan')){
            $productId = $this->getPostBodyParameter('plan');
        }
        $productId = self::standardizeProductId($productId);
        if($productId){
            return $productId;
        }
	    QMLog::error("Unrecognized product id: " . $productId, [], false, "Defaulting to " . self::MONTHLY);
        return self::MONTHLY;
    }
    /**
     * @param $productId
     * @return string|null
     */
    public static function standardizeProductId($productId){
        if(stripos($productId, 'year') !== false){
            return self::YEARLY;
        }
        if(stripos($productId, 'annual') !== false){
            return self::YEARLY;
        }
        if(stripos($productId, 'month') !== false){
            return self::MONTHLY;
        }
        return null;
    }
    /**
     * @param Exception $exception
     * @param null $purchaseId
     * @return array
     */
    private function getResponseData($exception = null, $purchaseId = null){
        if($exception){
            ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($exception);
            $data['error'] = $exception->getMessage();
            return $data;
        }
        if($purchaseId){
            $data['purchaseId'] = $purchaseId;
        }
        $user = $this->getUser();
        $data['user'] = $user->getQMUserArray();
        $data['user']['accessToken'] = $user->getOrCreateAccessTokenString();
        $customer = $user->asStripeCustomer();
        if($customer){
            $data['stripeCustomer'] = $customer;
            $subscription = $customer->subscriptions->data[0];
            if(!is_object($subscription)){
                return $data;
            }
            /** @var Subscription $subscription */
            $plan = $subscription->plan;
            $providedProductId = $this->getProductId();
            if($plan->id !== $providedProductId){
                QMLog::error("Could not update plan from $plan to $providedProductId");
            }
        }
        return $data;
    }
    /**
     * @param array $postBody
     * @param User $user
     * @return array
     */
    public function updateCard($postBody, $user){
        $this->user = $user;
        $this->postBody = $postBody;
        if($this->isTestCard()){
            $user->setStripActive(true);
            return $this->getResponseData();
        }
        try {
            $productId = $this->getProductId();
            $user->swapPlanIfNecessary($productId);
            $user->updateCard($this->getCreditCardTokenId());
            $this->applyCouponIfNecessary();
            $purchaseId = $this->upgrade('updated_card', $user);
            return $this->getResponseData(null, $purchaseId);
        } catch (Exception $e) {
            if(AppMode::isTestingOrStaging()){
                /** @var LogicException $e */
                throw $e;
            }
            return $this->getResponseData($e);
        }
    }
    /**
     * @return bool|null|string
     */
    public function getLastFour(): string {
        return $this->postBody["stripeToken"]["card"]["last4"];
        //if(!$this->getCardNumber()){return null;}
        //return substr($this->getCardNumber(), -4);
    }
    public static function setStripeKeyToTestingIfNecessary(){
        if(AppMode::isTestingOrStaging()){
            Stripe::setApiKey("sk_test_HuJNBhLDJueoS4yWpeLfrbdI");
        }
    }
    /**
     * @return null|string
     * @throws BadRequestException
     */
    public function getCreditCardTokenId(): ?string
    {
        $cardData = $this->getPostBody();
        if(isset($cardData['stripeToken'])){
            if(is_array($cardData['stripeToken'])){
                return $cardData['stripeToken']['id'];
            }
            return $cardData['stripeToken'];
        }
        if(empty($cardData['card_number'])){
            return null;
        }
        try {
            StripeService::setStripeKeyToTestingIfNecessary();
            $creditCardToken = Token::create([
                "card" => [
                    "number"    => $cardData['card_number'],
                    "exp_month" => $cardData['card_month'],
                    "exp_year"  => $cardData['card_year'],
                    "cvc"       => $cardData['card_cvc']
                ]
            ]);
            return $creditCardToken->id;
        } catch (Exception $e) {
            if(AppMode::isTestingOrStaging()){
                /** @var LogicException $e */
                throw $e;
            }
            ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
            Log::error(__METHOD__.": ".$e->getMessage());
            throw new BadRequestException(__METHOD__.": ".$e->getMessage());
        }
    }
    /**
     * @return Application[]|Collection
     */
    public function getPaidApplications(): Collection
    {
        $freePlan = BillingPlan::free();
        //get only paid applications
        /** @var Application[] $applications */
        $applications = Application::where('plan_id', '<>', $freePlan->id)->get();
        return $applications;
    }
    public function chargeExceedingCalls(){
        $applications = $this->getPaidApplications();
        if(empty($applications)){
            Log::info('There are no paid applications');
            return;
        }
        $now = Carbon::now();
        foreach($applications as $app){
            try {
                $customer = Customer::retrieve($app->stripe_id);
                $subscription = $customer->subscriptions->retrieve($app->stripe_subscription);
                $periodEnd = TimeHelper::toCarbon($subscription->current_period_end);
            } catch (Exception $e) {
                if(AppMode::isTestingOrStaging()){
                    /** @var LogicException $e */
                    throw $e;
                }
                ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
                QMLog::error("Couldn't retrieve subscription", $app);
                continue;
            }
            //cast charge to int because stripe doesn't let us to charge below cents
            $charge = (int)$app->exceeding_call_charge;
            //check if period has ended so we can charge for extra calls
            if($app->exceeding_call_count > 0 && $charge > 0 && $now->gte($periodEnd)){
                $owner = User::find($app->user_id);
                $charge = $app->charge($charge, [
                    'description'   => "Exceeding call charge for ".$app->app_display_name,
                    'receipt_email' => $owner->user_email,
                ]);
                if(!$charge){
                    Log::error("Unable to charge application's owner", [
                        'application' => $app,
                        'owner'       => $owner
                    ]);
                    $errorName = 'Unable to charge application\'s owner';
                    $errorMessage = 'Unable to charge application\'s owner';
                    $metaData = [
                        'application' => $app,
                        'owner'       => $owner
                    ];
                    Log::error($errorMessage, $metaData);
                    QMLog::error($errorName, $metaData, $errorMessage);
                    // if we are unable to charge disable the application
                    $app->enabled = 0;
                    $app->save();
                }
                // reset count and charge for the next period
                $app->exceeding_call_count = 0;
                $app->exceeding_call_charge = 0;
                $app->save();
            }
        }
    }
    /**
     * @return string
     */
    public static function getClientId(): ?string {
        $clientId = Session::get('client_id', null);
        if(!$clientId){
            $clientId = Request::input('client_id');
        }
        if(!$clientId){
            $clientId = Request::input('clientId');
        }
        if(!$clientId){
            $clientId = Request::input('appName');
        }
        if(!$clientId){
            $clientId = Request::input('app_name');
        }
        return $clientId;
    }
    /**
     * @return bool|ApiResource|Customer
     */
    private function applyCouponIfNecessary(){
        if($this->getCouponCode()){
            $customer = $this->getCustomer();
            $customer->coupon = $this->getCouponCode();
            return $customer->save();
        }
        return false;
    }
    /**
     * @return User
     */
    public function getUser(): User {
        return $this->user;
    }
    /**
     * @return Customer
     */
    public function getCustomer(){
        return $this->getUser()->asStripeCustomer();
    }
    /**
     * @param string $productId
     * @param User $user
     * @return mixed
     */
    private function upgrade(string $productId, User $user): int {
        $user->last_four = $this->getLastFour();
        $user->subscription_provider = 'stripe';
        $user->stripe_plan = $this->getStripePlanProductId();
        $user->setStripActive(true);
        $user->save();
        $purchaseData = [
            'product_id'            => $this->getStripePlanProductId(),
            'subscription_provider' => 'stripe',
            'subscriber_user_id'    => $user->getId()
        ];
        if($this->getCouponCode()){
            $referrer = User::whereUserLogin($this->getCouponCode())->first();
            if($referrer){
                $purchaseData['referrer_user_id'] = $referrer->ID;
            }
        }
        $purchaseId = $user->recordPurchase($purchaseData);
        Analytics::trackEvent($category = 'Purchase', $action = $productId, $label = 'stripe', $value = 6.95);
        return $purchaseId;
    }
}
