<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Models;
use App\Logging\QMLog;
use App\Services\StripeService;
use App\Slim\Model\User\QMUser;
use Tests\UnitTestCase;
use Throwable;
/**
 * Class UserTest
 * @package Testss
 * @covers \App\Models\Subscription
 */
class SubscriptionTest extends UnitTestCase
{
    public const STRIPE_DISABLED_UNTIL = "2023-04-01";
    public const TEST_CREDIT_CARD_NUMBER = "378282246310005";
    //use DatabaseTransactions;  This causes many nightmares
    /**
     * @param $provider
     */
    public function postUnsubscribe($provider): void{
        QMUser::writable()->update(['stripe_active' => 1, 'subscription_provider' => $provider]);
        $this->actingAsUserOne(); // Must do this after updating or the user in memory won't be up-to-date
        $response = $this->postWithClientId("account/unsubscribe", []);
        if(!isset($response['user'])){
            QMLog::error('user not set in response!  response is:' . QMLog::var_export($response, true));
        }
        $user = $response['user'];
        $this->assertEquals(0, $user['stripeActive'], "stripeActive should be false after unsubscribe");
        $this->assertEquals($provider, $user['subscriptionProvider']);
    }
    /**
     * @return array
     */
    public function stripeDowngrade(){
        $this->actingAsUserOne();
        $response = $this->postWithClientId("account/unsubscribe", []);
        if(!isset($response['user'])){
            $this->fail('user not set in response!  response is:' . 
                        QMLog::var_export($response, true));
        }
        $user = $response['user'];
        $this->assertFalse($user['stripeActive']);
        if(isset($response['data']['stripeCustomer'])){
            $this->assertEquals(0, $response['data']['stripeCustomer']['subscriptions']['total_count']);
        }
        return $response;
    }
    /**
     * @param string $plan
     * @param null $couponCode
     * @param null $postData
     * @return array
     */
    public function stripeUpgrade(string $plan, $couponCode = null, $postData = null): array{
        if(is_string($postData)){$postData = json_decode($postData, true);}
        $this->actingAsUserOne();
        if(!$postData){
            $postData = ["card_number"=> self::TEST_CREDIT_CARD_NUMBER, "card_month"=>2, "card_year"=>2023,
                         "card_cvc"=>"1234","productId"=> $plan, 'coupon' => $couponCode];
        }
        $response = $this->postWithClientId("account/subscribe", $postData);
        if(isset($response['error'])){
            $this->fail($response['error']);
        }
        if(!isset($response['user'])){
            QMLog::error('user not set in response!  response is:' .
                         QMLog::var_export($response, true));
        }
        $this->assertNotEmpty($response['user'], "response is: " . QMLog::print_r($response, true));
        $this->assertTrue($response['user']['stripeActive'], "stripeActive is not true!");
        $this->assertEquals('stripe', $response['user']['subscriptionProvider']);
        if($couponCode){
            $this->assertEquals($couponCode, $response['data']['stripeCustomer']['discount']['coupon']['id']);
        }
        if(isset($response['data']['stripeCustomer']['subscriptions']['data'][0])){
            $this->assertEquals(StripeService::standardizeProductId($plan),
                $response['data']['stripeCustomer']['subscriptions']['data'][0]['items']['data'][0]['plan']['id']);
        }
        if(isset($response['data']['stripeCustomer']['subscription'])){
            $this->assertEquals(StripeService::standardizeProductId($plan), $response['data']['stripeCustomer']['subscription']['plan']['id']);
        }
        if(isset($response['data']['stripeCustomer'])){
            $this->assertEquals(1, $response['data']['stripeCustomer']['subscriptions']['total_count']);
        }
        $this->assertEquals(StripeService::standardizeProductId($plan), $response['data']['user']['stripePlan']);
        return $response;
    }
    public function testPostGoogleUnsubscribe(): void{$this->postUnsubscribe('google');}
    public function testPostAppleUnsubscribe(): void{$this->postUnsubscribe('apple');}
    public function testStripeUpgradeMonthly(): void{
        $this->skipTest("TODO");
        if(time() < strtotime(self::STRIPE_DISABLED_UNTIL)){ // Might be temporarily broken
            $this->skipTest('TODO: Fix stripe swap plan changes. Maybe just downgrade and upgrade again');
            return;
        }
        try {
            $this->stripeDowngrade();
        } catch (Throwable $exception) {
        }
        $this->stripeUpgrade('monthly7', null);
    }
    public function testStripeUpgradeYearlyWithCoupon(): void{
        $this->skipTest("TODO");
        if(time() < strtotime(self::STRIPE_DISABLED_UNTIL)){ // Might be temporarily broken
            $this->skipTest('TODO: Fix stripe swap plan changes. Maybe just downgrade and upgrade again');
            return;
        }
        try {
            $this->stripeDowngrade();
        } catch (Throwable $exception) {
        }
        $upgradeResponse = $this->stripeUpgrade('yearly60', 'testcoupon');
        QMLog::info(QMLog::var_export($upgradeResponse, true));
        $initialResponse = $this->stripeDowngrade();
        QMLog::info(QMLog::var_export($initialResponse, true));
    }
    public function testStripSwapPlans(): void{
        $this->skipTest("TODO");
        if(time() < strtotime(self::STRIPE_DISABLED_UNTIL)){ // Might be temporarily broken
            $this->skipTest('TODO: Fix stripe swap plan changes. Maybe just downgrade and upgrade again');
            return;
        }
        $initialResponse = $this->stripeDowngrade();
        QMLog::info(QMLog::var_export($initialResponse, true));
        $upgradeResponse = $this->stripeUpgrade('monthly7', null);
        $updateResponse = $this->stripeUpgrade(StripeService::YEARLY, null);
        if(!isset($updateResponse['data']['stripeCustomer'])){
            $updateResponse = $this->stripeUpgrade(StripeService::YEARLY, null);
            QMLog::info(QMLog::var_export($updateResponse, true));
            QMLog::error("Fix plan changes!");
        } else {
            $this->assertEquals($updateResponse['data']['stripeCustomer']['id'], $upgradeResponse['data']['stripeCustomer']['id']);
        }
        $downgradeResponse = $this->stripeDowngrade();
        $this->assertEquals($downgradeResponse['data']['stripeCustomer']['id'], $upgradeResponse['data']['stripeCustomer']['id']);
        $reUpgradeResponse = $this->stripeUpgrade(StripeService::MONTHLY, null);
        $this->assertEquals($downgradeResponse['data']['stripeCustomer']['id'], $reUpgradeResponse['data']['stripeCustomer']['id']);
        $this->stripeDowngrade();
        $this->stripeUpgrade('yearly60', 'testcoupon');
        $finalResponse = $this->stripeDowngrade();
        QMLog::info(QMLog::var_export($finalResponse, true));
        $liveDebug = false;
        if($liveDebug){
            $this->stripeUpgrade("monthly7", 'testcoupon', '{"productId":"monthly7","couponCode":"testcoupon","stripeToken":{"id":"tok_ca","object":"token",
            "card":{"id":"card_1CKqbSEp2G8nRAKlvb9f0nBY","object":"card","address_city":null,"address_country":null,
            "address_line1":null,"address_line1_check":null,"address_line2":null,"address_state":null,
            "address_zip":"62034","address_zip_check":"unchecked","brand":"American Express","country":"US",
            "cvc_check":"unchecked","dynamic_last4":null,"exp_month":2,"exp_year":2020,"funding":"credit","last4":"2009",
            "metadata":{},"name":null,"tokenization_method":null},"client_ip":"24.216.163.200","created":1524674183,
            "livemode":true,"type":"card","used":false}}');
        }
    }
}
