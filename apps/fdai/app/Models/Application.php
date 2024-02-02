<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Exceptions\InvalidClientIdException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\AppSettings\AppDesign;
use App\AppSettings\AppSettings;
use App\AppSettings\AppStatus;
use App\Buttons\QMButton;
use App\Buttons\RelationshipButtons\Application\ApplicationUserButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Buttons\States\ConfigurationStateButton;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\QMException;
use App\Models\Base\BaseApplication;
use App\Properties\Base\BaseAppTypeProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Collaborator\CollaboratorTypeProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
use App\Storage\Memory;
use App\Traits\HasDBModel;

use App\Traits\HasModel\HasUser;
use App\Traits\HasName;
use App\Traits\IsEditable;
use App\Types\ObjectHelper;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\FontAwesome;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Subscription;
use Stripe\Customer;
use Stripe\StripeObject;
/**
 * @mixin AppSettings
 * App\Models\Application
 * @property integer $id
 * @property string $client_id registered through client's application
 * @property string $user_id
 * @property string $app_display_name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|Application whereCreatedAt($value)
 * @method static Builder|Application whereUpdatedAt($value)
 * @property int|null $organization_id
 * @property string|null $app_description
 * @property string|null $long_description
 * @property string|null $icon_url
 * @property string|null $text_logo
 * @property string|null $splash_screen
 * @property string|null $homepage_url
 * @property string|null $app_type
 * @property string|null $app_design
 * @property string|null $deleted_at
 * @property int $enabled
 * @property int $stripe_active
 * @property string|null $stripe_id
 * @property string|null $stripe_subscription
 * @property string|null $stripe_plan
 * @property string|null $last_four
 * @property Carbon|null $trial_ends_at
 * @property Carbon|null $subscription_ends_at
 * @property string|null $company_name
 * @property string|null $country
 * @property string|null $address
 * @property string|null $state
 * @property string|null $city
 * @property string|null $zip
 * @property int|null $plan_id
 * @property int $exceeding_call_count
 * @property float|null $exceeding_call_charge
 * @property int $study
 * @property int $billing_enabled
 * @property int|null $outcome_variable_id
 * @property int|null $predictor_variable_id
 * @property int $physician
 * @property string|null $additional_settings
 * @property string|null $app_status
 * @property int $build_enabled
 * @property-read Collection|Collaborator[] $collaborators
 * @property-read OAClient $credentials
 * @property-read Organization $organization
 * @property-read Variable $outcome
 * @property-read Variable $predictor
 * @property-read Collection|Subscription[] $subscriptions
 * @property-read User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|Application newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Application newQuery()
 * @method static Builder|Application onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Application query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereAdditionalSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereAppDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereAppDesign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereAppDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereAppStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereAppType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereBillingEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereBuildEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereExceedingCallCharge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereExceedingCallCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereHomepageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereIconUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereLongDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereOutcomeVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application wherePhysician($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application wherePredictorVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereSplashScreen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereStripeActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereStripeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereStripePlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereStripeSubscription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereStudy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereSubscriptionEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereTextLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereTrialEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereZip($value)
 * @method static Builder|Application withTrashed()
 * @method static Builder|Application withoutTrashed()
 * @mixin Eloquent
 * @property-read int|null $collaborators_count
 * @property-read int|null $subscriptions_count
 * @property-read OAClient $oa_client
 * @property int|null $wp_post_id
 * @property-read Variable|null $variable
 * @property-read WpPost $wp_post
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereWpPostId($value)
 * @property-read Variable|null $outcome_variable
 * @property-read Variable|null $predictor_variable
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel nPerGroup($group, $n = 10)
 * @property int|null $number_of_collaborators_where_app Number of Collaborators for this App.
 *                 [Formula:
 *                     update applications
 *                         left join (
 *                             select count(id) as total, app_id
 *                             from collaborators
 *                             group by app_id
 *                         )
 *                         as grouped on applications.id = grouped.app_id
 *                     set applications.number_of_collaborators_where_app = count(grouped.total)
 *                 ]
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereNumberOfCollaboratorsWhereApp($value)
 * @property bool|null $is_public
 * @property int $sort_order
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereSortOrder($value)
 * @property string|null $slug The slug is the part of a URL that identifies a page in human-readable keywords.
 * @property-read OAClient $client
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereSlug($value)
 */
class Application extends BaseApplication {
    use HasFactory;
	use SoftDeletes, Billable, HasUser, HasDBModel;
	use IsEditable, HasName;
	const CLASS_CATEGORY = "App Builder";
	/**
	 * @param QMUser|User $user
	 * @param string|null $clientId
	 * @param array $appData
	 * @return \App\Models\Application
	 * @throws \App\Exceptions\InvalidEmailException
	 * @throws \App\Exceptions\NoEmailAddressException
	 */
	public static function createApplication(User $user, string $clientId = null, array $appData = []): Application {
		if(!$clientId){
			$clientId = $user->getEmail();
		}
		$appData[AppSettings::FIELD_USER_ID] = $user->getId();
		$appData[AppSettings::FIELD_CLIENT_ID] = $clientId;
		if(!isset($appData[AppSettings::FIELD_APP_TYPE])){
			$appData[AppSettings::FIELD_APP_TYPE] = BaseAppTypeProperty::APP_TYPE_GENERAL;
		}
		if(!isset($appData[AppSettings::FIELD_APP_DISPLAY_NAME])){
			$appData[AppSettings::FIELD_APP_DISPLAY_NAME] = $user->getDisplayNameAttribute();
		}
		$application = new Application($appData);
		try {
			$application->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
		return $application;
	}
	public static function getSlimClass(): string{ return AppSettings::class; }
	public const CLASS_DESCRIPTION = "Get the settings for your application configurable at https://builder.quantimo.do";
	public const FONT_AWESOME = FontAwesome::MOBILE_SOLID;
	public $hidden = []; // TODO: Remove build settings?
	/**
	 * @var bool Indicates if the IDs are auto-incrementing.
	 */
	public $incrementing = true;
	protected array $rules = [
		'address' => 'nullable|max:255',
		'app_description' => 'nullable|max:255',
		'app_display_name' => 'required|max:255',
		'app_type' => 'nullable|max:32',
		'billing_enabled' => 'nullable|boolean',
		'build_enabled' => 'nullable|boolean',
		'city' => 'nullable|max:100',
		'client_id' => 'required|max:80', //|unique:applications,client_id', // Unique checks too slow
		'company_name' => 'nullable|max:100',
		'country' => 'nullable|max:100',
		'enabled' => 'nullable|boolean',
		'exceeding_call_charge' => 'nullable|numeric|max:99999999999999.99',
		'exceeding_call_count' => 'nullable|integer|min:0|max:2147483647',
		'homepage_url' => 'nullable|max:255|url',
		'icon_url' => 'nullable|max:2083|url',
		'last_four' => 'nullable|max:4',
		'organization_id' => 'nullable|integer|min:0|max:2147483647',
		'outcome_variable_id' => 'nullable|integer|min:1|max:2147483647',
		'physician' => 'nullable|boolean',
		'plan_id' => 'nullable|integer|min:1|max:2147483647',
		'predictor_variable_id' => 'nullable|integer|min:1|max:2147483647',
		'splash_screen' => 'nullable|max:2083',
		'state' => 'nullable|max:100',
		'stripe_active' => 'nullable|boolean',
		'stripe_id' => 'nullable|max:255',
		'stripe_plan' => 'nullable|max:100',
		'stripe_subscription' => 'nullable|max:255',
		'study' => 'nullable|boolean',
		'subscription_ends_at' => 'nullable|datetime',
		'text_logo' => 'nullable|max:2083',
		'trial_ends_at' => 'nullable|datetime',
		'user_id' => 'required|numeric|min:0',
		'zip' => 'nullable|max:10',
	];
	/**
	 * @return Application
	 */
	public static function quantimodo(): Application{
		return static::findByClientId(BaseClientIdProperty::CLIENT_ID_QUANTIMODO);
	}
	/**
	 * @param string $clientId
	 * @param AppSettings|array|object $newAppSettings
	 * @return \App\Models\Application
	 * @throws \App\Exceptions\ModelValidationException
	 */
	public static function updateApplication(string $clientId, $newAppSettings): Application{
		$newAppSettings = ObjectHelper::convertToObject($newAppSettings);
		$application = Application::findByClientId($clientId);
		try {
			$application->updateRedirectUriIfNecessary($newAppSettings);
		} catch (ClientNotFoundException $e) {
			le(__METHOD__.": ".$e->getMessage());
		}
		if(isset($newAppSettings->appDesign)){
			Memory::set(Memory::THROW_EXCEPTION_IF_INVALID, true);
			$application->app_design = new AppDesign($newAppSettings); // Do this for validation of menu items before
			// saving
			Memory::set(Memory::THROW_EXCEPTION_IF_INVALID, false);
		}
		if(isset($newAppSettings->additionalSettings)){
			$application->additional_settings = json_encode($newAppSettings->additionalSettings);
		}
		if(isset($newAppSettings->appStatus)){
			$appStatus = new AppStatus($newAppSettings);
			$application->app_status = json_encode($appStatus);
		}
		if(isset($newAppSettings->appDisplayName)){
			$application->app_display_name = $newAppSettings->appDisplayName;
		}
		if(isset($newAppSettings->buildEnabled)){
			$application->build_enabled = $newAppSettings->buildEnabled;
		}
		if(isset($newAppSettings->appType)){
			$application->app_type = $newAppSettings->appType;
		}
		$application->save();
		return $application;
	}
    /**
	 * @return void
	 * @throws ModelValidationException
	 */
	public function checkExceedingLimits(){
		$plan = $this->getBillingPlan();
		if(empty($plan)){
			return;
		}
		$callCount = $this->getRequestCount();
		if($callCount <= $plan->request_limit){
			return; // Let them go if they didn't exceed the limit
		}
		if(!$plan->price){
			// Applications who are in free plan can't exceed the request limit
			throw new QMException(QMException::CODE_UNAUTHORIZED,
				'You have reached your request limit in the Free Plan. Please upgrade it to continue making requests', [
					'message' => 'You have reached your request limit in the Free Plan. Please upgrade it to continue making requests',
					'client id' => $this->client_id,
					'maximum request limit' => $plan->request_limit,
					'current number of requests' => $callCount,
				]);
		}
		// Increase the call and extra charge because they exceeded the limit of their paid plan
		++$this->exceeding_call_count;
		//Store in cents
		$this->exceeding_call_charge += ($plan->extra_call * 100);
		$this->save();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getAppSettings(): AppSettings{
		return $this->getDBModel();
	}
	public function getOrCreateClient(): OAClient{
		return OAClient::getOrCreate($this->client_id, [
			OAClient::FIELD_USER_ID => $this->user_id,
			OAClient::FIELD_ICON_URL => $this->getImage(),
		]);
	}
	/**
	 * @return HasOne
	 */
	public function organization(): HasOne{
		return $this->hasOne(Organization::class);
	}
	/**
	 * @return HasOne
	 */
	public function credentials(): HasOne{
		return $this->hasOne(OAClient::class, 'client_id', 'client_id');
	}
	/**
	 * @return HasOne
	 */
	public function outcome(): HasOne{
		return $this->hasOne(Variable::class, 'id', 'outcome_variable_id');
	}
	/**
	 * @return HasOne
	 */
	public function predictor(): HasOne{
		return $this->hasOne(Variable::class, 'id', 'predictor_variable_id');
	}
	/**
	 * @return int|string
	 */
	public function getRequestCountFractionString(): string{
		$plan = $this->getBillingPlan();
		if(!$plan){
			return "0/0";
		}
		$callCount = $this->getRequestCount();
		return number_format($callCount) . '/' . number_format($plan->request_limit);
	}
	/**
	 * @return int
	 */
	public function getRequestCount(): int{
		$callCount =
			TrackerLog::where('client_id', $this->client_id)->where('created_at', '>=', $this->getPlanPeriodStartAt())
				->where('created_at', '<', $this->getPlanPeriodEndAt())->count();
		return $callCount;
	}
	/**
	 * @return BillingPlan|null
	 */
	public function getBillingPlan(): ?BillingPlan{
		if(!$this->billing_enabled){
			return null;
		}
		if(!$this->plan_id){
			return null;
		}
		return BillingPlan::find($this->plan_id);
	}
	/**
	 * @param string|int $clientOrAppId
	 * @return Application
	 */
	public static function findByClientOrAppId($clientOrAppId): ?Application{
		if(is_int($clientOrAppId)){
			return self::findInMemoryOrDB($clientOrAppId);
		} else{
			return self::findByClientId($clientOrAppId);
		}
	}
	/**
	 * @param string $clientId
	 * @return Application
	 */
	public static function findByClientId(string $clientId): ?Application{
		$app = Memory::get($clientId, __METHOD__);
		if(!$app){
			$app = self::whereClientId($clientId)->first();
			Memory::set($clientId, $app, __METHOD__);
		}
		/** @var Application $app */
		return $app;
	}
	/**
	 * @return Customer
	 */
	public function getStripeCustomer(): Customer{
		return Customer::retrieve($this->stripe_id);
	}
	/**
	 * @return array|StripeObject
	 */
	public function getStripeSubscription(){
		$customer = $this->getStripeCustomer();
		return $customer->subscriptions->retrieve($this->stripe_subscription);
	}
	/**
	 * @return string
	 */
	public function getPlanPeriodStartAt(): string{
		if(!$this->stripe_subscription){
			return $this->created_at->toString();
		}
		$subscription = $this->getStripeSubscription();
		return TimeHelper::toCarbon($subscription->current_period_start)->toString();
	}
	/**
	 * @return string
	 */
	public function getPlanPeriodEndAt(): string{
		if(!$this->stripe_subscription){
			return $this->created_at->addMonth()->toString();
		}
		$subscription = $this->getStripeSubscription();
		return TimeHelper::toCarbon($subscription->current_period_end)->toString();
	}
	public function getTitleAttribute(): string{
		if(!$this->hasId()){
			return static::getClassNameTitle();
		}
		return $this->app_display_name;
	}
	public function getImage(): string{
		if(!$this->hasId()){
			return static::DEFAULT_IMAGE;
		}
		$img = $this->icon_url;
		if(empty($img)){
			$img = $this->text_logo;
		}
		if(empty($img)){
			$img = $this->user->getImage();
		}
		return $img;
	}
	public function getEditUrl(array $params = []): string{
		$params[self::FIELD_CLIENT_ID] = $this->client_id;
		return ConfigurationStateButton::make()->getUrl($params);
	}
	/**
	 * @param $value
	 */
	public function setAppDesignAttribute($value){
		if(QMStr::isNullString($value)){
			le('$value === "null"');
		}
		if(!$value && isset($this->attributes[self::FIELD_APP_DESIGN])){
			le('!$value to '.__FUNCTION__); // Called on instantiation
		}
		$this->attributes[self::FIELD_APP_DESIGN] = $value;
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
	public static function getUniqueIndexColumns(): array{
		return [static::FIELD_ID];
	}
	public function setAdditionalSettings(string $additionalSettings): void{
		$this->setAttribute(Application::FIELD_ADDITIONAL_SETTINGS, $additionalSettings);
	}
	public function getAddress(): ?string{
		return $this->attributes[Application::FIELD_ADDRESS] ?? null;
	}
	public function setAddress(string $address): void{
		$this->setAttribute(Application::FIELD_ADDRESS, $address);
	}
	public function getAppDescription(): ?string{
		return $this->attributes[Application::FIELD_APP_DESCRIPTION] ?? null;
	}
	public function setAppDescription(string $appDescription): void{
		$this->setAttribute(Application::FIELD_APP_DESCRIPTION, $appDescription);
	}
	public function setAppDesign(string $appDesign): void{
		$this->setAttribute(Application::FIELD_APP_DESIGN, $appDesign);
	}
	public function setAppStatus(string $appStatus): void{
		$this->setAttribute(Application::FIELD_APP_STATUS, $appStatus);
	}
	public function getAppType(): ?string{
		return $this->attributes[Application::FIELD_APP_TYPE] ?? null;
	}
	public function setAppType(string $appType): void{
		$this->setAttribute(Application::FIELD_APP_TYPE, $appType);
	}
	public function setBillingEnabled(bool $billingEnabled): void{
		$this->setAttribute(Application::FIELD_BILLING_ENABLED, $billingEnabled);
	}
	public function getBuildEnabled(): ?bool{
		return $this->attributes[Application::FIELD_BUILD_ENABLED] ?? null;
	}
	public function setBuildEnabled(bool $buildEnabled): void{
		$this->setAttribute(Application::FIELD_BUILD_ENABLED, $buildEnabled);
	}
	public function getCity(): ?string{
		return $this->attributes[Application::FIELD_CITY] ?? null;
	}
	public function setCity(string $city): void{
		$this->setAttribute(Application::FIELD_CITY, $city);
	}
	public function getCountry(): ?string{
		return $this->attributes[Application::FIELD_COUNTRY] ?? null;
	}
	public function setCountry(string $country): void{
		$this->setAttribute(Application::FIELD_COUNTRY, $country);
	}
	public function getDeletedAt(): ?string{
		return $this->attributes[Application::FIELD_DELETED_AT] ?? null;
	}
	public function setDeletedAt(string $deletedAt): void{
		$this->setAttribute(Application::FIELD_DELETED_AT, $deletedAt);
	}
	public function setEnabled(bool $enabled): void{
		$this->setAttribute(Application::FIELD_ENABLED, $enabled);
	}
	public function setExceedingCallCharge(float $exceedingCallCharge): void{
		$this->setAttribute(Application::FIELD_EXCEEDING_CALL_CHARGE, $exceedingCallCharge);
	}
	public function getExceedingCallCount(): ?int{
		return $this->attributes[Application::FIELD_EXCEEDING_CALL_COUNT] ?? null;
	}
	public function setExceedingCallCount(int $exceedingCallCount): void{
		$this->setAttribute(Application::FIELD_EXCEEDING_CALL_COUNT, $exceedingCallCount);
	}
	public function getIconUrl(): ?string{
		return $this->attributes[Application::FIELD_ICON_URL] ?? null;
	}
	public function setIconUrl(string $iconUrl): void{
		$this->setAttribute(Application::FIELD_ICON_URL, $iconUrl);
	}
	public function getIsPublic(): ?bool{
		return $this->attributes[Application::FIELD_IS_PUBLIC] ?? null;
	}
	public function setIsPublic(bool $isPublic): void{
		$this->setAttribute(Application::FIELD_IS_PUBLIC, $isPublic);
	}
	public function getLastFour(): ?string{
		return $this->attributes[Application::FIELD_LAST_FOUR] ?? null;
	}
	public function setLastFour(string $lastFour): void{
		$this->setAttribute(Application::FIELD_LAST_FOUR, $lastFour);
	}
	public function setLongDescription(string $longDescription): void{
		$this->setAttribute(Application::FIELD_LONG_DESCRIPTION, $longDescription);
	}
	public function setOrganizationId(int $organizationId): void{
		$this->setAttribute(Application::FIELD_ORGANIZATION_ID, $organizationId);
	}
	public function getOutcomeVariableId(): ?int{
		return $this->attributes[Application::FIELD_OUTCOME_VARIABLE_ID] ?? null;
	}
	public function setOutcomeVariableId(int $outcomeVariableId): void{
		$this->setAttribute(Application::FIELD_OUTCOME_VARIABLE_ID, $outcomeVariableId);
	}
	public function getPlanId(): ?int{
		return $this->attributes[Application::FIELD_PLAN_ID] ?? null;
	}
	public function setPlanId(int $planId): void{
		$this->setAttribute(Application::FIELD_PLAN_ID, $planId);
	}
	public function getPredictorVariableId(): ?int{
		return $this->attributes[Application::FIELD_PREDICTOR_VARIABLE_ID] ?? null;
	}
	public function setPredictorVariableId(int $predictorVariableId): void{
		$this->setAttribute(Application::FIELD_PREDICTOR_VARIABLE_ID, $predictorVariableId);
	}
	public function getState(): ?string{
		return $this->attributes[Application::FIELD_STATE] ?? null;
	}
	public function setState(string $state): void{
		$this->setAttribute(Application::FIELD_STATE, $state);
	}
	public function getStripeActive(): ?bool{
		return $this->attributes[Application::FIELD_STRIPE_ACTIVE] ?? null;
	}
	public function setStripeActive(bool $stripeActive): void{
		$this->setAttribute(Application::FIELD_STRIPE_ACTIVE, $stripeActive);
	}
	public function getStripeId(): ?string{
		return $this->attributes[Application::FIELD_STRIPE_ID] ?? null;
	}
	public function setStripeId(string $stripeId): void{
		$this->setAttribute(Application::FIELD_STRIPE_ID, $stripeId);
	}
	public function getStripePlan(): ?string{
		return $this->attributes[Application::FIELD_STRIPE_PLAN] ?? null;
	}
	public function setStripeSubscription(string $stripeSubscription): void{
		$this->setAttribute(Application::FIELD_STRIPE_SUBSCRIPTION, $stripeSubscription);
	}
	public function getStudy(): ?bool{
		return $this->attributes[Application::FIELD_STUDY] ?? null;
	}
	public function setStudy(bool $study): void{
		$this->setAttribute(Application::FIELD_STUDY, $study);
	}
	public function getSubscriptionEndsAt(): ?string{
		return $this->attributes[Application::FIELD_SUBSCRIPTION_ENDS_AT] ?? null;
	}
	public function setSubscriptionEndsAt(string $subscriptionEndsAt): void{
		$this->setAttribute(Application::FIELD_SUBSCRIPTION_ENDS_AT, $subscriptionEndsAt);
	}
	public function getTrialEndsAt(): ?string{
		return $this->attributes[Application::FIELD_TRIAL_ENDS_AT] ?? null;
	}
	public function setTrialEndsAt(string $trialEndsAt): void{
		$this->setAttribute(Application::FIELD_TRIAL_ENDS_AT, $trialEndsAt);
	}
	public function getWpPostId(): ?int{
		return $this->attributes[Application::FIELD_WP_POST_ID] ?? null;
	}
	public function setWpPostId(int $wpPostId): void{
		$this->setAttribute(Application::FIELD_WP_POST_ID, $wpPostId);
	}
	public function getEditButton(): QMButton{
		$b = new ConfigurationStateButton();
		$b->setParameters($this->getUrlParams());
		return $b;
	}
	public function getUrlParams(): array{
		return [
			self::FIELD_CLIENT_ID => $this->getClientId(),
			'application_id' => $this->getId(),
		];
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		return [
			new ApplicationUserButton($this),
		];
	}
	/**
	 * @param AppSettings|object $newAppSettings
	 * @throws \App\Exceptions\ModelValidationException
	 */
	private function updateRedirectUriIfNecessary($newAppSettings): void{
		if(isset($newAppSettings->redirectUri)){
			$redirect = QMStr::replaceNewLineBreaks($newAppSettings->redirectUri);
			$client = $this->getApplication()->getClient();
			$client->redirect_uri = $redirect;
			$client->save();
		}
	}
	/**
	 * @param User $user
	 * @return Collaborator
	 */
	public function firstOrCreateCollaborator(User $user): Collaborator {
		return Collaborator::firstOrCreate([
            Collaborator::FIELD_USER_ID   => $user->id,
            Collaborator::FIELD_CLIENT_ID => $this->client_id
        ],[
			Collaborator::FIELD_APP_ID    => $this->id,
			Collaborator::FIELD_USER_ID   => $user->id,
			Collaborator::FIELD_TYPE      => CollaboratorTypeProperty::TYPE_OWNER,
			Collaborator::FIELD_CLIENT_ID => $this->client_id
		]);
	}
	/**
	 * @param int $userId
	 * @param string $scopes
	 * @param int|null $expiresInSeconds
	 * @param bool $includeRefreshToken
	 * @return array
	 */
	public function getOrCreateAccessAndRefreshTokenArrays(int $userId, string $scopes = 'readmeasurements writemeasurements',
		int $expiresInSeconds = null, bool $includeRefreshToken = true): array{
		return QMAuth::getOrCreateAccessAndRefreshTokenArrays($this->getClientId(), $userId, $scopes,
			$expiresInSeconds, $includeRefreshToken);
	}
	/**
	 * @return int|null
	 */
	public function countUsers(): ?int{
        return $this->getUsersCountAttribute();
//		$tokens = OAAccessToken::whereClientId($this->client_id)
//			->whereNotNull('user_id')
//			->where('expires', '>', \Carbon\Carbon::today()->toDateString())
//			->groupBy('user_id')
//			->orderBy('expires', 'desc')
//            ->max('expires');
//        return $tokens->count();
	}
    public function getUsersCountAttribute(): int
    {
        return $this->getOAClient()->getUsersCountAttribute();
    }

    public function getOAClient(): OAClient
    {
        return $this->getApplication()->getClient();
    }

    /**
     * @return User[]|Collection
     */
    public function getUsers(){
        return $this->oa_client->wp_users();
    }
    public function oa_access_tokens(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        $qb = $this->oa_client()->oa_access_tokens();
        return $qb;
    }
	public function setOwner(int $userId): Collaborator {
		$this->user_id = $userId;
		$this->save();
		$client = $this->getOAClient();
		$client->user_id = $userId;
		$client->save();
		$this->collaborators()
		     ->where(Collaborator::FIELD_TYPE, CollaboratorTypeProperty::TYPE_OWNER)
		     ->where(Collaborator::FIELD_USER_ID, "<>", $userId)
		     ->forceDelete();
		return $this->addCollaborator($userId);
	}
	public function addCollaborator(int $userId): Collaborator {
		if($userId === $this->user_id){
			$type = CollaboratorTypeProperty::TYPE_OWNER;
		} else {
			$type = CollaboratorTypeProperty::TYPE_COLLABORATOR;
		}
		return $this->collaborators()->firstOrCreate([
			Collaborator::FIELD_USER_ID => $userId,
			Collaborator::FIELD_APP_ID => $this->id,
			Collaborator::FIELD_CLIENT_ID => $this->client_id,
			Collaborator::FIELD_TYPE => $type,
		]);
	}
	/**
	 * @return void
	 * @throws ClientNotFoundException
	 */
	public static function createDefaultReminders(): void{
		try {
			$appSettings = Application::getClientAppSettings();
		} catch (InvalidClientIdException $e) {
			le($e);
		}
		$u = QMAuth::getQMUserIfSet();
		$id = $u->getId();
		$appSettings->createReminders($u);
	}
	public function getUsersWithAccessTokens(): Collection{
		$tokens = OAAccessToken::query()
		            ->whereNotNull('user_id')
					->where(OAAccessToken::FIELD_CLIENT_ID, $this->client_id)
		            ->where('expires', '>', \Carbon\Carbon::today()->toDateString())
		            ->groupBy('user_id')
		            ->orderBy('expires', 'desc')
		            ->get();
		$ids = $tokens->pluck('user_id');
		$users = User::whereIn(User::FIELD_ID, $ids)->get();
		foreach($users as $user){
			$t = $tokens->firstWhere('user_id', $user->id);
			//$user->append('access_token', $t);
			$user->access_token = $t;
		}
		return $users;
	}
	/**
	 * @return Application|null
	 */
	public static function fromRequest(): ?self{
		$clientId = BaseClientIdProperty::fromRequest();
		return static::findByClientId($clientId);
	}
}
