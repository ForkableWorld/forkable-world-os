<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpHierarchyChecksInspection */
/** @noinspection SummerTimeUnsafeTimeManipulationInspection */
namespace App\Slim\Model\User;
use App\AppSettings\AppSettings;
use App\Buttons\QMButton;
use App\Buttons\States\HistoryAllStateButton;
use App\Buttons\States\ImportStateButton;
use App\Buttons\States\MeasurementAddSearchStateButton;
use App\Buttons\States\OnboardingStateButton;
use App\Buttons\States\RemindersManageStateButton;
use App\Buttons\States\StudyCreationStateButton;
use App\Cards\TrackingReminderNotificationCard;
use App\VariableRelationships\QMUserVariableRelationship;
use App\DataSources\Connectors\Exceptions\ConnectorDisabledException;
use App\DataSources\Connectors\GithubConnector;
use App\DataSources\Connectors\WeatherConnector;
use App\DataSources\QMClient;
use App\DataSources\QMConnector;
use App\DataSources\QMDataSource;
use App\DataSources\QMSpreadsheetImporter;
use App\DataSources\SpreadsheetImportRequest;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\BadRequestException;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\DeletedUserException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\InvalidClientIdException;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\InvalidTimestampException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoDeviceTokensException;
use App\Exceptions\NoEmailAddressException;
use App\Exceptions\NoGeoDataException;
use App\Exceptions\NoTimeZoneException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\VariableCategoryNotFoundException;
use App\Logging\QMLog;
use App\Logging\QMLogLevel;
use App\Logging\SolutionButton;
use App\Mail\QMSendgrid;
use App\Mail\TooManyEmailsException;
use App\Models\GlobalVariableRelationship;
use App\Models\Application;
use App\Models\BaseModel;
use App\Models\Connection;
use App\Models\Connector;
use App\Models\UserVariableRelationship;
use App\Models\Credential;
use App\Models\DeviceToken;
use App\Models\IpDatum;
use App\Models\Measurement;
use App\Models\OAAccessToken;
use App\Models\OAClient;
use App\Models\OARefreshToken;
use App\Models\Purchase;
use App\Models\Study;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\User;
use App\Models\UserTag;
use App\Models\UserVariable;
use App\Models\UserVariableClient;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Models\Vote;
use App\Models\WpPost;
use App\Models\WpUsermetum;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseCountryProperty;
use App\Properties\Base\BaseRolesProperty;
use App\Properties\Base\BaseUserLoginProperty;
use App\Properties\Base\BaseZipCodeProperty;
use App\Properties\Connection\ConnectionConnectStatusProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\User\UserNumberOfCorrelationsProperty;
use App\Properties\User\UserPrimaryOutcomeVariableIdProperty;
use App\Properties\User\UserUserEmailProperty;
use App\Properties\User\UserUserLoginProperty;
use App\Properties\UserVariable\UserVariableStatusProperty;
use App\Properties\Variable\VariableIdProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Reports\AnalyticalReport;
use App\Reports\RootCauseAnalysis;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\Model\Auth\QMRefreshToken;
use App\Slim\Model\DBModel;
use App\Slim\Model\GoogleAnalyticsEvent;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Notifications\PushNotification;
use App\Slim\Model\Notifications\PushNotificationData;
use App\Slim\Model\Notifications\QMDeviceToken;
use App\Slim\Model\Phrases\Phrase;
use App\Slim\Model\QMUnit;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\Model\WP\QMWpUser;
use App\Slim\View\Request\QMRequest;
use App\Slim\View\Request\Variable\GetUserVariableRequest;
use App\Storage\DB\QMQB;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Storage\QMFileCache;
use App\Studies\QMCohortStudy;
use App\Studies\QMStudy;
use App\Tables\QMTable;
use App\Traits\QMAnalyzableTrait;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\CssHelper;
use App\Utils\AppMode;
use App\Utils\GeoLocation;
use App\Utils\IonicHelper;
use App\Utils\IPHelper;
use App\Utils\QMTimeZone;
use App\Utils\Subdomain;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\QMUserTag;
use App\Variables\QMUserVariable;
use App\Variables\QMVariableCategory;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;
use PDO;
use RuntimeException;
use SendGrid\Mail\TypeException;
use stdClass;
use Tests\TestGenerators\StagingJobTestFile;
use Throwable;
use Watson\Validating\ValidationException;
class QMUser extends PublicUser {
	use QMAnalyzableTrait;
	public $analysisEndedAt;
	public $analysisRequestedAt;
	public ?string $analysisSettingsModifiedAt = null;
	public $analysisStartedAt;
	public $newestDataAt;
	public $reasonForAnalysis;
	public $userErrorMessage;
	protected $allUserVariableRelationships;
	protected $allUserTags;
	protected $allUserVariables;
	protected $allValidAccessTokens;
	protected $averageQmScore;
	protected $carbon;
	protected ?OAClient $client = null;
	protected $correlationsForOutcome;
	protected $dataQuantities;
	protected $dataQuantityListButtonsHtml;
	protected $deleted;
	protected $deletedAt;
	protected $deletionReason;
	protected $deviceTokens;
	protected $earliestTaggedMeasurementAt;
	protected $isPublic;
	protected $iosDeviceTokens;
	protected $lastFour;
	protected $latestEarliestClientIdTimes;
	protected $latestEarliestConnectorIdTimes;
	protected $latestEarliestSourceNameTimes;
	protected $mostRecentTrackingReminderNotification;
	protected $oldUser;
	protected $password;
	protected $patients;
	public $profileHtml;
	protected $pastTrackingReminderNotifications;
	protected $physicianClientApplication;
	protected $providerToken;
	protected $referrerUserId;
	protected $rememberToken;
	protected $spam;
	protected $spreadsheetImporters;
	protected $spreadsheetImportRequests;
	protected $stripeId;
	protected $stripeSubscription;
	protected $TodayYesterdayOrDayOfWeekStrings;
	protected $trackingReminderNotifications;
	protected $trackingReminders;
	protected $trialEndsAt;
	protected $userActivationKey;
	protected $userDataSources;
	protected $userNicename;
	protected $userPass; // Why was this commented?  Commenting it causes undefined property exceptions
	protected $userStatus;
	protected $userVariablesToCorrelate;
	protected $variableUserSources;
	protected $votes;
	protected $wpStudiesUser;
	protected $sortOrder;
	protected $slug;
	protected $numberOfTrustees;
	protected $numberOfSharers;
	public $accessToken;
	public $accessTokenExpires;
	public $accessTokenExpiresAtMilliseconds;
	public $address;
	public $administrator;
	public $authorizedClients;
	public $authUrl;
	public $birthday;
	public $capabilities;
	public $cardBrand;
	public $cardLastFour;
	public $clientId;
	public $clientUserId;
	public $combineNotifications;
	public $country;
	public $coverPhoto;
	public $currency;
	public $earliestReminderTime;
	public $ethAddress;
	public $firstName;
	public $gender;
	public $getPreviewBuilds;
	public $hasAndroidApp;
	public $hasChromeExtension;
	public $hasIosApp;
	public $id;
	public $language;
	public $lastActive;
	public $lastCorrelationAt;
	public $lastEmailAt;
	public $lastLoginAt;
	public $lastName;
	public $lastPushAt;
	public $lastSmsTrackingReminderNotificationId;
	public $latestReminderTime;
	public $loginName;
	public $numberOfApplications;
	public $numberOfButtonClicks;
	public $numberOfCollaborators;
	public $numberOfConnections;
	public $numberOfConnectorImports;
	public $numberOfConnectorRequests;
	public $numberOfCorrelations;
	public $numberOfMeasurementExports;
	public $numberOfMeasurementImports;
	public $numberOfMeasurements;
	public $numberOfOauthAccessTokens;
	public $numberOfOauthAuthorizationCodes;
	public $numberOfOauthClients;
	public $numberOfOauthRefreshTokens;
	public $numberOfPatients;
	public $numberOfRawMeasurementsWithTags;
	public $numberOfRawMeasurementsWithTagsAtLastCorrelation;
	public $numberOfSentEmails;
	public $numberOfStudies;
	public $numberOfSubscriptions;
	public $numberOfTrackingReminderNotifications;
	public $numberOfTrackingReminders;
	public $numberOfUsersWhereReferrerUser;
	public $numberOfUserTags;
	public $numberOfUserVariables;
	public $numberOfVotes;
	public $phoneNumber;
	public $phoneVerificationCode;
	public $providerId;
	public $primaryOutcomeVariableId;
	public $primaryOutcomeVariableName;
	public $pushNotificationsEnabled;
	public $refreshToken;
	public $regProvider;
	public $roles;
	public $scope;
	public $sendPredictorEmails;
	public $sendReminderNotificationEmails;
	public $shareAllData;
	public $showAds;
	public $smsNotificationsEnabled;
	public $state;
	public $status;
	public $stripeActive;
	public $stripePlan;
	public $subscribed;
	public $subscriptionEndsAt;
	public $subscriptionProvider;
	public $timezone;
	public $timeZoneOffset;
	public $trackLocation;
	public $userRegistered;
	public $verified;
	public $wpPostId;
	public $zipCode;
	public const ALGORITHM_MODIFIED_AT                                          = "2020-06-20";
	public const DEFAULT_EARLIEST_REMINDER_TIME                                 = '06:00:00';
	public const DEFAULT_LATEST_REMINDER_TIME                                   = '22:00:00';
	public const FIELD_ADDRESS                                                  = 'address';
	public const FIELD_ANALYSIS_ENDED_AT                                        = 'analysis_ended_at';
	public const FIELD_ANALYSIS_REQUESTED_AT                                    = 'analysis_requested_at';
	public const FIELD_ANALYSIS_SETTINGS_MODIFIED_AT                            = 'analysis_settings_modified_at';
	public const FIELD_ANALYSIS_STARTED_AT                                      = 'analysis_started_at';
	public const FIELD_AVATAR_IMAGE                                             = 'avatar_image';
	public const FIELD_BIRTHDAY                                                 = 'birthday';
	public const FIELD_CARD_BRAND                                               = 'card_brand';
	public const FIELD_CARD_LAST_FOUR                                           = 'card_last_four';
	public const FIELD_CLIENT_ID                                                = 'client_id';
	public const FIELD_COMBINE_NOTIFICATIONS                                    = 'combine_notifications';
	public const FIELD_COUNTRY                                                  = 'country';
	public const FIELD_COVER_PHOTO                                              = 'cover_photo';
	public const FIELD_CREATED_AT                                               = 'created_at';
	public const FIELD_CURRENCY                                                 = 'currency';
	public const FIELD_DELETED                                                  = 'deleted';
	public const FIELD_DELETED_AT                                               = 'deleted_at';
	public const FIELD_DELETION_REASON                                          = 'deletion_reason';
	public const FIELD_DISPLAY_NAME                                             = 'display_name';
	public const FIELD_EARLIEST_REMINDER_TIME                                   = 'earliest_reminder_time';
	public const FIELD_FIRST_NAME                                               = 'first_name';
	public const FIELD_GENDER                                                   = 'gender';
	public const FIELD_GET_PREVIEW_BUILDS                                       = 'get_preview_builds';
	public const FIELD_HAS_ANDROID_APP                                          = 'has_android_app';
	public const FIELD_HAS_CHROME_EXTENSION                                     = 'has_chrome_extension';
	public const FIELD_HAS_IOS_APP                                              = 'has_ios_app';
	public const FIELD_ID                                                       = 'ID';
	public const FIELD_INTERNAL_ERROR_MESSAGE                                   = 'internal_error_message';
	public const FIELD_LANGUAGE                                                 = 'language';
	public const FIELD_LAST_CORRELATION_AT                                      = 'last_correlation_at';
	public const FIELD_LAST_EMAIL_AT                                            = 'last_email_at';
	public const FIELD_LAST_FOUR                                                = 'last_four';
	public const FIELD_LAST_LOGIN_AT                                            = 'last_login_at';
	public const FIELD_LAST_NAME                                                = 'last_name';
	public const FIELD_LAST_PUSH_AT                                             = 'last_push_at';
	public const FIELD_LAST_SMS_TRACKING_REMINDER_NOTIFICATION_ID               = 'last_sms_tracking_reminder_notification_id';
	public const FIELD_LATEST_REMINDER_TIME                                     = 'latest_reminder_time';
	public const FIELD_NEWEST_DATA_AT                                           = 'newest_data_at';
	public const FIELD_NUMBER_OF_CONNECTIONS                                    = 'number_of_connections';
	public const FIELD_NUMBER_OF_CORRELATIONS                                   = 'number_of_correlations';
	public const FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS                     = 'number_of_raw_measurements_with_tags';
	public const FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION = 'number_of_raw_measurements_with_tags_at_last_correlation';
	public const FIELD_NUMBER_OF_STUDIES                                        = 'number_of_studies';
	public const FIELD_NUMBER_OF_TRACKING_REMINDERS                             = 'number_of_tracking_reminders';
	public const FIELD_NUMBER_OF_USER_VARIABLES                                 = 'number_of_user_variables';
	public const FIELD_NUMBER_OF_VOTES                                          = 'number_of_votes';
	public const FIELD_OLD_USER                                                 = 'old_user';
	public const FIELD_PHONE_NUMBER                                             = 'phone_number';
	public const FIELD_PHONE_VERIFICATION_CODE                                  = 'phone_verification_code';
	public const FIELD_PRIMARY_OUTCOME_VARIABLE_ID                              = 'primary_outcome_variable_id';
	public const FIELD_PROVIDER_ID                                              = 'provider_id';
	public const FIELD_PROVIDER_TOKEN                                           = 'provider_token';
	public const FIELD_PUSH_NOTIFICATIONS_ENABLED                               = 'push_notifications_enabled';
	public const FIELD_REASON_FOR_ANALYSIS                                      = 'reason_for_analysis';
	public const FIELD_REFERRER_USER_ID                                         = 'referrer_user_id';
	public const FIELD_REFRESH_TOKEN                                            = 'refresh_token';
	public const FIELD_REG_PROVIDER                                             = 'reg_provider';
	public const FIELD_REMEMBER_TOKEN                                           = 'remember_token';
	public const FIELD_ROLES                                                    = 'roles';
	public const FIELD_SEND_PREDICTOR_EMAILS                                    = 'send_predictor_emails';
	public const FIELD_SEND_REMINDER_NOTIFICATION_EMAILS                        = 'send_reminder_notification_emails';
	public const FIELD_SMS_NOTIFICATIONS_ENABLED                                = 'sms_notifications_enabled';
	public const FIELD_SPAM                                                     = 'spam';
	public const FIELD_STATE                                                    = 'state';
	public const FIELD_STATUS                                                   = 'status';
	public const FIELD_STRIPE_ACTIVE                                            = 'stripe_active';
	public const FIELD_STRIPE_ID                                                = 'stripe_id';
	public const FIELD_STRIPE_PLAN                                              = 'stripe_plan';
	public const FIELD_STRIPE_SUBSCRIPTION                                      = 'stripe_subscription';
	public const FIELD_SUBSCRIPTION_ENDS_AT                                     = 'subscription_ends_at';
	public const FIELD_SUBSCRIPTION_PROVIDER                                    = 'subscription_provider';
	public const FIELD_TAG_LINE                                                 = 'tag_line';
	public const FIELD_TIME_ZONE_OFFSET                                         = 'time_zone_offset';
	public const FIELD_TIMEZONE                                                 = 'timezone';
	public const FIELD_TRACK_LOCATION                                           = 'track_location';
	public const FIELD_TRIAL_ENDS_AT                                            = 'trial_ends_at';
	public const FIELD_UNSUBSCRIBED                                             = 'unsubscribed';
	public const FIELD_UPDATED_AT                                               = 'updated_at';
	public const FIELD_USER_ACTIVATION_KEY                                      = 'user_activation_key';
	public const FIELD_USER_EMAIL                                               = 'user_email';
	public const FIELD_USER_ERROR_MESSAGE                                       = 'user_error_message';
	public const FIELD_USER_ID                                                  = 'ID';
	public const FIELD_USER_LOGIN                                               = 'user_login';
	public const FIELD_USER_NICENAME                                            = 'user_nicename';
	public const FIELD_USER_PASS                                                = 'user_pass';
	public const FIELD_PASSWORD                                                 = User::FIELD_PASSWORD;
	public const FIELD_USER_REGISTERED                                          = 'user_registered';
	public const FIELD_USER_STATUS                                              = 'user_status';
	public const FIELD_USER_URL                                                 = 'user_url';
	public const FIELD_VERIFIED                                                 = 'verified';
	public const FIELD_WP_POST_ID                                               = 'wp_post_id';
	public const FIELD_ZIP_CODE                                                 = 'zip_code';
	public const DB_FIELD_NAME_TO_PROPERTY_NAME_MAP                             = [
		self::FIELD_ID => 'id',
		self::FIELD_USER_LOGIN => 'loginName',
		self::FIELD_USER_EMAIL => 'email',
		self::FIELD_PROVIDER_ID => 'clientUserId',
	];
	/**
	 * QMUser constructor.
	 * @param User|null $l
	 * @param bool $addFallbackAvatar
	 */
	public function __construct(User $l = null, bool $addFallbackAvatar = false){
		if(!$l){return;}
		$this->populateByLaravelModel($l);
		parent::__construct(null, $addFallbackAvatar);
		if($addFallbackAvatar && !isset($this->avatarImage)){$this->getAvatar();}
		$this->addToMemory();
		$this->administrator = $l->isAdmin();
	}
	/** @noinspection PhpHierarchyChecksInspection */
	public static function findByLoginName(string $log): ?QMUser{
		$u = User::findByLoginName($log);
		if($u){
			return $u->instantiateQMUser();
		}
		return null;
	}
	public static function deleteById(int $id, string $reason){
		$u = static::find($id);
		$u->delete($reason);
	}
	public static function findInDB(int $int): ?QMUser{
		$u = User::find($int);
		if(!$u){
			return null;
		}
		return $u->instantiateQMUser();
	}
	/** @noinspection PhpHierarchyChecksInspection */
	public static function findDeleted(int $id): ?QMUser{
		$u = User::findDeleted($id);
		if(!$u){
			return null;
		}
		return $u->instantiateQMUser();
	}
	/**
	 * @param string $name
	 * @return QMConnector
	 */
	public function getQMConnector(string $name): QMConnector{
		$c = Arr::first($this->getQMConnectors(), static function($c) use ($name){
			/** @var QMConnector $c */
			return $c->name === $name;
		});
		return $c;
	}
	/**
	 * @param array|object $arrayOrObject
	 * @return void
	 */
	public function populateFieldsByArrayOrObject(array|object $arrayOrObject): void{
		if(is_array($arrayOrObject)){
			$arrayOrObject = json_decode(json_encode($arrayOrObject));
		}
		$this->clientUserId = $this->clientUserId ?? $arrayOrObject->provider_id ?? null;
		$this->loginName = $this->loginName ?? $arrayOrObject->user_login ?? null;
		$this->password = $this->password ?? $arrayOrObject->user_pass ?? null;
		$this->email = $this->email ?? $arrayOrObject->user_email ?? null;
		$this->capabilities = $this->capabilities ?? $arrayOrObject->roles ?? null;
		parent::populateFieldsByArrayOrObject($arrayOrObject);
	}
	/**
	 * @throws ValidationException
	 */
	public function populateDefaultFields(){
		parent::populateDefaultFields();
		$this->setLastActive();
		$this->setAdministrator();
		$this->getAvatar();
		$this->validate();
		//$this->setShowAds();
		// What is this for? $this->token = QMCookie::createWordPressCookieValue($this->loginName, $this->password,
		// $this->clientId);
		$this->setBooleanProperties();
		$this->setFirstLastDisplayName();
		$this->getCurrency();
		$this->getCoverPhoto();
	}
	/**
	 * @param QMQB|Builder $qb
	 * @param string $tableOrAlias
	 * @param string $userIdField
	 */
	public static function excludeTestAndDeletedUsers($qb, string $tableOrAlias, string $userIdField = 'user_id'): void{
		$ids = UserIdProperty::getTestSystemAndDeletedUserIds();
		$qb->whereNotIn($tableOrAlias . '.' . $userIdField, $ids);
	}
	public static function population(): QMUser{
		return QMUser::find(UserIdProperty::USER_ID_POPULATION);
	}
	/**
	 * @return User|null
	 */
	public static function getAnyOldTestUser(): ?User{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return User::whereLike(self::FIELD_USER_LOGIN, '%testuser%')
			->orderBy(self::FIELD_ID, 'asc') // asc so we don't interfere with current test users
			->first();
	}
	/**
	 * @param array|object $arrayOrObject
	 * @return QMUser
	 */
	public static function instantiateAndValidate($arrayOrObject): QMUser{
		$model = self::instantiateIfNecessary($arrayOrObject);
		if(!$model->id){
			QMLogLevel::setDebug();
			/** @var QMUser $model */
			$model = self::instantiateIfNecessary($arrayOrObject);
			if(!$model->id){
				QMLog::error("Model class: " . get_class($model));
				le("No id after instantiateIfNecessary of " . json_encode($arrayOrObject));
			}
		}
		return $model;
	}
	/**
	 * @param int|null $userId
	 * @param string|null $email
	 * @return bool
	 */
	public static function isTestUserByIdOrEmail(int $userId = null, string $email = null): bool{
		if(AppMode::isUnitOrStagingUnitTest() && $userId === UserIdProperty::USER_ID_DEMO){
			return false;
		}
		$testUserIds = [
			UserIdProperty::USER_ID_DEMO,
			UserIdProperty::USER_ID_TEST_USER,
			69632,
			70016,
			70299,
		];
		if($userId && in_array($userId, $testUserIds, true)){
			return true;
		}
		if($email && strpos($email, 'test') !== false){
			return true;
		}
		//if($email && strpos($email, 'quantimo') !== false){ return true;}
		//if(strpos(\App\Utils\Env::get('APP_ENV'), 'staging') !== false){return true;}
		//if(strpos(\App\Utils\Env::get('APP_ENV'), 'test') !== false){return true;}
		//if(strpos(\App\Utils\Env::get('APP_ENV'), 'dev') !== false){return true;}
		return false;
	}
	/**
	 * @param int $days
	 * @return bool
	 */
	public function loginInLastXDays(int $days): bool{
		if(!$this->lastLoginAt){
			return false;
		}
		return (time() - strtotime($this->getLastLoginAt())) / 86400 < $days;
	}
	/**
	 * @return bool
	 * @throws ModelValidationException
	 */
	public function updateLastLoginAtIfNecessary(): bool{
		if(!AppMode::isApiRequest()){
			return false;
		}
		if($this->loginInLastXDays(1)){
			return false;
		}
		$this->setLastLoginAt(time());
		return $this->save();
	}
	/**
	 * Returns the currently authenticated user or null. Requires that QMAuthenticator middleware was used
	 * on the endpoint
	 * @return QMUser
	 */
	public static function getAuthenticatedUser(): QMUser{
		return QMAuth::getQMUserIfSet();
	}
	/**
	 * Returns the currently authenticated user or null. Requires that QMAuthenticator middleware was used
	 * on the endpoint
	 * @return QMUser
	 * @throws ClientNotFoundException
	 */
	public static function getAuthenticatedUserWithAccessTokenAndWithoutPassword(): QMUser{
		$user = self::getAuthenticatedUser();
		unset($user->password);
		if(empty($user->getAccessTokenStringIfSet())){
			try {
				$user->getOrCreateToken();
			} catch (BadRequestException $e) {
				ExceptionHandler::dumpOrNotify($e);
			}
		}
		return $user;
	}
	/**
	 * @param string $scopes
	 * @param int|null $expiresInSeconds
	 * @return QMAccessToken|null
	 * Handle the creation of access token, also issue refresh token if supported / desirable.
	 * @throws ClientNotFoundException
	 * @internal param bool $includeRefreshToken if true, a new refresh_token will be added to the response
	 * @internal param string $client_id client identifier related to the access token.
	 * @internal param int $user_id user ID associated with the access token
	 * @internal param string $scope OPTIONAL scopes to be stored in space-separated string.
	 * @see http://tools.ietf.org/html/rfc6749#section-5
	 * @ingroup oauth2_section_5
	 */
	public function getOrCreateToken(string $scopes = 'readmeasurements writemeasurements',
		int $expiresInSeconds = null): ?QMAccessToken{
		$str = $this->getAccessTokenStringIfSet();
		if(!empty($str)){
			$at = QMAccessToken::find($str);
			return $at;
		}
		if($at = Memory::getQmAccessTokenObject()){
			$this->setAccessToken($at);
			return $at;
		}
		if(!AppMode::isApiRequest()){
			return null;
		}
		$clientId = BaseClientIdProperty::fromRequest(false);
		if(!$clientId){
			$clientId = BaseClientIdProperty::CLIENT_ID_QUANTIMODO;
		}
		if(!QMClient::find($clientId)){
			throw new ClientNotFoundException($clientId);
		}
		$this->setQMClientIdInDatabaseIfNull($clientId);
		$rt = QMRefreshToken::getOrCreateRefreshToken($clientId, $this->id, $scopes, $expiresInSeconds);
		$this->refreshToken = $rt->getRefreshToken();
		$at = $this->getOrCreateAccessToken($clientId, $scopes, $expiresInSeconds);
		$this->setAccessToken($at);
		return $at;
	}
	/**
	 * @param string $clientId
	 * @param string $scopes
	 * @param int|null $expiresInSeconds
	 * @return QMAccessToken
	 */
	public function getOrCreateAccessToken(string $clientId, string $scopes = 'readmeasurements writemeasurements',
		int $expiresInSeconds = null){
		if($token = $this->getValidAccessTokenForClient($clientId, $scopes)){
			return $token;
		}
		$token = QMAccessToken::create($clientId, $this->id, $scopes, $expiresInSeconds);
		$this->setAccessToken($token);
		$this->allValidAccessTokens[] = $token;
		return $token;
	}
	/**
	 * @param QMAccessToken|OAAccessToken $t
	 */
	public function setAccessToken($t): void{
		if(empty($this->clientId)){
			$this->setQMClientIdInDatabaseIfNull($t->clientId);
		}
		$this->accessToken = $t->getAccessToken();
		$this->scope = $t->getScope();
		if(!is_int($t->expires)){
			$this->logDebug("token expiration is not an integer: " . json_encode($t));
			$this->accessTokenExpires = $t->getExpires();
			$this->accessTokenExpiresAtMilliseconds = strtotime($t->getExpires()) * 1000;
		} else{
			$this->accessTokenExpires = date('Y-m-d H:i:s', $t->getExpires());
			$this->accessTokenExpiresAtMilliseconds = $t->getExpires() * 1000;
		}
	}
	/**
	 * @return bool
	 */
	public function isMike(): bool{
		return $this->getId() === UserIdProperty::USER_ID_MIKE;
	}
	/**
	 * @param int|string $abbreviationOrOffsetInMinutes
	 * @return bool
	 */
	public function setTimeZone($abbreviationOrOffsetInMinutes){
		if(is_int($abbreviationOrOffsetInMinutes)){
			$offsetInMinutes = $abbreviationOrOffsetInMinutes;
			$abbreviation = QMTimeZone::convertTimeZoneOffsetToStringAbbreviation($abbreviationOrOffsetInMinutes);
		} else{
			$abbreviation = $abbreviationOrOffsetInMinutes;
			$offsetInMinutes = QMTimeZone::timeZoneAbbreviationToOffsetInMinutes($abbreviation);
		}
		QMTimeZone::validateOffset($offsetInMinutes);
		$previousOffset = $this->timeZoneOffset;
		$previousAbbreviation = $this->timezone;
		if(!$offsetInMinutes){
			$this->logErrorOrInfoIfTesting("Not changing time from $previousOffset to " .
				$abbreviationOrOffsetInMinutes . " because it's probably a mistake");
			return false;
		}
		if($offsetInMinutes === $previousOffset && $abbreviation === $previousAbbreviation){
			$this->logDebug("Not changing time from $previousOffset to $offsetInMinutes because it hasn't changed");
			return false;
		}
		if($previousAbbreviation){
			$this->logErrorOrInfoIfTesting("Changed timezone offset from:
            previous offset: $previousOffset to new offset: $offsetInMinutes 
            and
            abbreviation from $previousAbbreviation to $abbreviation");
		}
		QMTimeZone::validateTimeZoneOffsetToAbbreviation($offsetInMinutes, $abbreviation);
		if(AppMode::isApiRequest()){
			QMTimeZone::notify($abbreviation);
		}
        $l = $this->l();
        $l->timezone = $abbreviation;
        $l->time_zone_offset = $offsetInMinutes;
        return $l->save();
	}
	/**
	 * @param array $body
	 * @return int
	 */
	public function setSubscriptionProvider(array $body){
		$availableSubscriptionProviders = [
			'google',
			'apple',
			'stripe',
		];
		if(!in_array($body['subscriptionProvider'], $availableSubscriptionProviders, true)){
			throw new BadRequestException('subscriptionProvider should be one of the following: ' .
				implode(", ", $availableSubscriptionProviders));
		}
		if(!isset($body['productId']) && isset($body['stripePlan'])){
			$body['productId'] = $body['stripePlan'];
		}
		if(!isset($body['productId'])){
			throw new BadRequestException('Please include productId (monthly7 or yearly60');
		}
		$availablePlans = [
			'monthly7',
			'yearly60',
			'moodimodo_monthly7',
			'quantimodo_monthly7',
			'moodimodo_yearly60',
			'quantimodo_yearly60',
		];
		if($body['subscriptionProvider'] !== null && !in_array($body['productId'], $availablePlans, true)){
			throw new BadRequestException('productId should be one of the following: ' .
				implode(", ", $availablePlans));
		}
		if($body['subscriptionProvider'] === $this->subscriptionProvider){
			if(AppMode::isProduction()){
				$this->logError('Not setting subscriptionProvider because it has not changed.');
			}
			return true;
		}
		if(!isset($body['trialEndsAt'])){
			$body['trialEndsAt'] = date("Y-m-d H:i:s", time() + 14 * 86400);
		}
		$updateArray = [
			'subscription_provider' => $body['subscriptionProvider'],
			'stripe_plan' => $body['productId'],
			'trial_ends_at' => $body['trialEndsAt'],
			'stripe_active' => true,
			'updated_at' => date('Y-m-d H:i:s'),
		];
		$customMetric['value'] = 6.95;
		if(strpos($body['productId'], '60') !== false){
			$customMetric['value'] = 60;
		}
		$customMetric['index'] = 1;
		GoogleAnalyticsEvent::logEventToGoogleAnalytics('Purchase', 'Upgrade', $customMetric['value'], $this->id,
			$this->clientId);
		return $this->updateDbRow($updateArray);
	}
	/**
	 * @param bool $combineNotifications
	 * @return int
	 */
	public function setCombineNotifications(bool $combineNotifications): int{
		$combineNotifications = filter_var((string)$combineNotifications, FILTER_VALIDATE_BOOLEAN);
		if($combineNotifications !== true && $combineNotifications !== false){
			throw new BadRequestException('combineNotifications should be true or false');
		}
		return $this->updateDbRow(['combine_notifications' => $combineNotifications]);
	}
	/**
	 * @param int $id
	 * @return QMUser
	 */
	public static function findGetTokenAndUnsetPassword(int $id): ?QMUser{
		$user = self::findWithToken($id);
		if($user){
			unset($user->password);
		}
		return $user;
	}
	/**
	 * @param int $userId
	 * @return QMUser
	 */
	public static function findWithToken(int $userId): QMUser{
		$user = self::find($userId);
		if(!$user){
			throw new RuntimeException("User $userId not found!");
		}
		$user->getOrSetAccessTokenString();
		return $user;
	}
	/**
	 * @param string $qmClientId
	 * @throws ModelValidationException
	 */
	public function updateQMClientId(string $qmClientId){
		if(stripos($qmClientId, 'googleusercontent.com')){
			$this->logError("Trying to set user QM client id to googleusercontent.com");
			return;
		}
		$this->l()->client_id = $qmClientId;
		$this->l()->save();
	}
	/**
	 * @param int|string $connectorUserId
	 * @param string $connectorName
	 * @return null|QMUser
	 */
	public static function findByConnectorUserId($connectorUserId, string $connectorName){
		$meta = WpUsermetum::whereMetaKey($connectorName . QMConnector::CONNECTOR_USER_ID_META_SUFFIX)
			->where(UserMeta::FIELD_META_VALUE, $connectorUserId)->first();
		if($meta){
			return self::find($meta->user_id);
		}
		return null;
	}
	/**
	 * @param $emailOrId
	 * @return QMUser
	 */
	public static function getByEmailOrId($emailOrId): ?QMUser{
		if(is_int($emailOrId)){
			return self::find($emailOrId);
		}
		return self::findByEmail($emailOrId);
	}
	/**
	 * Get a User for the given id
	 * @param string $email
	 * @return null|QMUser
	 */
	public static function findByEmail(string $email): ?QMUser{
		$l = User::findByEMail($email);
		if($l){
			return $l->getQMUser();
		}
		$meta = WpUsermetum::whereMetaValue($email)->first();
		if($meta){
			return self::find($meta->user_id);
		}
		return null;
	}
	/**
	 * @param string $tokenString
	 * @return null|QMUser
	 */
	public static function findByTokenString(string $tokenString): ?QMUser{
		$t = QMAccessToken::find($tokenString);
		if(!$t){
			throw new UnauthorizedException("No user with matching access token!");
		}
		if($t->getClientId() !== BaseClientIdProperty::fromMemory()){
			throw new UnauthorizedException('Token does not match provided clientId');
		}
		return self::findGetTokenAndUnsetPassword($t->getUserId());
	}
	/**
	 * Returns an array of all user ids
	 * @return array
	 */
	public static function getIds(): array{
		$sql = 'SELECT wpUser.ID FROM wp_users AS wpUser';
		$databaseConnection = Writable::pdo();
		$stmt = $databaseConnection->prepare($sql);
		$stmt->execute();
		return array_values($stmt->fetchAll(PDO::FETCH_COLUMN));
	}
	/**
	 * @param string|null $clientId
	 */
	public function setQMClientIdInDatabaseIfNull(string $clientId = null){
		if(!$clientId){
			$clientId = BaseClientIdProperty::fromMemory();
		}
		if(empty($this->clientId) && $clientId){
			try {
				$this->updateQMClientId($clientId);
			} catch (ModelValidationException $e) {
				le($e);
			}
		}
	}
	/**
	 * @param array $arr
	 * @param string|null $reason
	 * @return int
	 */
	public function updateDbRow(array $arr, string $reason = null): int{
		$res = parent::updateDbRow($arr);
		if($res){
			$this->deleteFromMemcached();
		}
		return $res;
	}
	/**
	 * @param string $localTimeString
	 * @return string
	 */
	public function localToUtcHis(string $localTimeString): string{
		try {
			$tz = $this->getTimezone();
		} catch (NoTimeZoneException $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());
			return $localTimeString;
		}
		$carbon =
			Carbon::parse($localTimeString, $tz)->setTimezone('UTC')->format(TimeHelper::FORMAT_HOURS_MINUTES_SECONDS);
		return $carbon;
	}
	/**
	 * @param int|string $utc
	 * @return string
	 */
	public function utcToLocalHis($utc): string{
		//$c = $this->getCarbon($utc);
		try {
			$tz = $this->getTimezone();
		} catch (NoTimeZoneException $e) {
			if($this->getLoginName() !== BaseUserLoginProperty::USER_LOGIN_ECONOMIC_DATA){
				$this->logError(__METHOD__.": ".$e->getMessage());
			}
			return $utc;
		}
		$c = Carbon::parse($utc, 'UTC')->setTimezone($tz);
		return $c->format(TimeHelper::FORMAT_HOURS_MINUTES_SECONDS);
	}
	/**
	 * @param string $localTimeString
	 * @return int
	 */
	public function convertLocalTimeStringToUtcSeconds(string $localTimeString): int{
		return 86400 + strtotime($localTimeString) + $this->getTimeZoneOffsetInSeconds();
	}

	/**
	 * @param string $email
	 * @param string|null $reason
	 * @return bool
	 */
	public static function unsubscribeByEmail(string $email, string $reason = null){
		$user = self::findByEmail($email);
		if(!$user){
			QMLog::error('Could not find user to unsubscribe from emails', [
				'reason' => $reason,
				'email' => $email,
			]);
			return false;
		}
		QMLog::error('User un-subscribed from emails', [
			'reason' => $reason,
			'user row before unsubscribe' => $user,
		]);
		$user->setUserMetaValue(UserMeta::KEY_unsubscribe_reason, $reason);
		return $user->updateDbRow([
			self::FIELD_SEND_REMINDER_NOTIFICATION_EMAILS => 0,
			self::FIELD_SEND_PREDICTOR_EMAILS => 0,
		]);
	}
	/**
	 * @param int $length
	 * @return string
	 */
	public static function generateRandomString(int $length = 10): string{
		/** @noinspection SpellCheckingInspection */
		$characters = 'abcdefghijklmnopqrstuvwxyz';
		$charactersLength = strlen($characters);
		$randomString = '';
		for($i = 0; $i < $length; $i++){
			try {
				$randomString .= $characters[random_int(0, $charactersLength - 1)];
			} catch (Exception $e) {
				le($e);
				throw new \LogicException();
			}
		}
		return $randomString;
	}
	/**
	 * @param array $params
	 * @return stdClass|QMUser
	 */
	public static function getUserNotificationPreferences(array $params): stdClass{
		/** @var QMUser $user */
		$user = DB::table(User::TABLE)->select([ // Don't instantiate or you'll get validation errors
			'send_reminder_notification_emails as sendReminderNotificationEmails',
			'send_predictor_emails as sendPredictorEmails',
			'get_preview_builds as getPreviewBuilds',
			'push_notifications_enabled as pushNotificationsEnabled',
			'user_email as email',
			'earliest_reminder_time as earliestReminderTime',
			'latest_reminder_time as latestReminderTime',
			'combine_notifications as combineNotifications',
			'time_zone_offset as timeZoneOffset',
			'timezone as timezone',
		])->where('user_email', UserUserEmailProperty::pluck($params))->first();
		$user->sendReminderNotificationEmails =
			filter_var((string)$user->sendReminderNotificationEmails, FILTER_VALIDATE_BOOLEAN);
		$user->sendPredictorEmails = filter_var((string)$user->sendPredictorEmails, FILTER_VALIDATE_BOOLEAN);
		$user->pushNotificationsEnabled = filter_var((string)$user->pushNotificationsEnabled, FILTER_VALIDATE_BOOLEAN);
		$user->combineNotifications = filter_var((string)$user->combineNotifications, FILTER_VALIDATE_BOOLEAN);
		return $user;
	}
	/**
	 * @return bool
	 */
	public static function getPaid(): bool{
		$paid = 'false';
		$u = QMAuth::getQMUserIfSet();
		if($u && isset($u->stripeActive) && $u->stripeActive){
			$paid = 'true'; // Dimension 1
		}
		return $paid;
	}
	/**
	 * @return string
	 */
	public static function getLoggedIn(): string{
		$loggedIn = 'false';
		if(QMAuth::getQMUserIfSet()){
			$loggedIn = 'true'; // Dimension 2
		}
		return $loggedIn;
	}
	/**
	 * @return int
	 */
	public static function getLoggedInUserId(): ?int{
		$user = QMAuth::getQMUserIfSet();
		if(isset($user) && $user){
			return $user->id;
		}
		return null;
	}
	/**
	 * @param int|null $userId
	 * @return string
	 */
	public static function getGoogleAnalyticsClientId(int $userId = null): ?string{
		$visitorGoogleAnalyticsCookie = ($_COOKIE["__utma"] ?? (string)$userId);
		if(!$visitorGoogleAnalyticsCookie){
			$visitorGoogleAnalyticsCookie = IPHelper::getClientIp();
		}
        if(!$visitorGoogleAnalyticsCookie){
            $visitorGoogleAnalyticsCookie = $_COOKIE["drift_aid"] ??
                $_COOKIE["remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d"] ?? "unknown";
        }
		return $visitorGoogleAnalyticsCookie;
	}
	/**
	 * @param int $userId
	 * @param string $clientId
	 * @return int
	 */
	public static function freeUpgrade(int $userId, string $clientId): int{
		$userToUpgrade = self::findWithToken($userId);
		if(!$userToUpgrade){
			le("User $userId not found");
		}
		$requestBody['productId'] = 'free';
		$requestBody['subscriptionProvider'] = $clientId;
		$requestBody['coupon'] = QMAuth::getQMUserIfSet()->loginName;
		return $userToUpgrade->upgradeSubscription($requestBody);
	}
	/**
	 * @param array $body
	 * @return int
	 */
	public function upgradeSubscription(array $body): int{
		$purchaseData = [
			'product_id' => $body['productId'],
			'subscription_provider' => $body['subscriptionProvider'],
			'subscriber_user_id' => $this->id,
			'updated_at' => date('Y-m-d H:i:s'),
			'created_at' => date('Y-m-d H:i:s'),
			'client_id' => BaseClientIdProperty::fromRequest(false),
		];
		if(array_key_exists('coupon', $body)){
			$purchaseData['coupon'] = $body['coupon'];
			$referrer = self::findByLoginName($body['coupon']);
			if($referrer){
				$purchaseData['referrer_user_id'] = $referrer->id;
			}
		}
		$this->updateDbRow([
			'stripe_plan' => $body['productId'],
			'subscription_provider' => $body['subscriptionProvider'],
			'stripe_active' => true,
			'updated_at' => date('Y-m-d H:i:s'),
			'client_id' => BaseClientIdProperty::fromRequest(false),
		]);
		$p = Purchase::create($purchaseData);
		return $p->id;
	}
	/**
	 * @param string $avatarUrl
	 * @return int
	 */
	public function updateAvatar(string $avatarUrl): int{
		return $this->updateDbRow(['avatar_image' => $avatarUrl]);
	}
	public function getTimezoneIfSet(): ?string{
		return $this->getUser()->getTimezoneIfSet();
	}
	/**
	 * @param $utc
	 * @return Carbon
	 */
	public function convertToLocalTimezone($utc): Carbon{
		if(isset($this->carbon[$utc])){
			return $this->carbon[$utc];
		}
		$epoch = TimeHelper::universalConversionToUnixTimestamp($utc);
		$carbon = Carbon::createFromTimestamp($epoch, $this->getTimezoneIfSet());
		return $this->carbon[$utc] = $carbon;
	}
	/**
	 * @return bool
	 */
	public function isAdmin(): bool{
		if($this->administrator !== null){
			return $this->administrator;
		}
		return $this->administrator = $this->l()->isAdmin();
	}
	public function setLastActive(): void{
		$this->lastActive = TimeHelper::timeSinceHumanString($this->getLastLoginAt());
	}
	/**
	 * @return string
	 * @throws NoTimeZoneException
	 */
	public function getTimezone(): string{
		if($this->timezone){
			return $this->timezone;
		}
		if($this->timeZoneOffset === null){
			throw new NoTimeZoneException($this);
		}
		$minutes = $this->minutesBeforeUTC();
		$abbreviation = QMTimeZone::convertTimeZoneOffsetToStringAbbreviation($minutes);
		if(empty($abbreviation)){
			throw new NoTimeZoneException($this, "Could not determine time zone for $minutes minutes offset!");
		}
		return $this->timezone = $abbreviation;
	}
	/**
	 * @param null $epochUnixSecondsOrString
	 * @return float|int
	 */
	public function getHoursSincePreviousMidnight($epochUnixSecondsOrString = null){
		if(!$epochUnixSecondsOrString){
			$epochUnixSecondsOrString = time();
		}
		$carbon = $this->convertToLocalTimezone($epochUnixSecondsOrString);
		$seconds = $carbon->secondsSinceMidnight();
		return $seconds / 3600;
	}
	/**
	 * @param int|string $epochUnixSecondsOrString
	 * @return float
	 * @throws InvalidTimestampException
	 */
	public function getHourDifferenceFromLastMidnight($epochUnixSecondsOrString){
		$carbon = $this->convertToLocalTimezone($epochUnixSecondsOrString);
		$hoursAgo = $carbon->diffInHours();
		$currentHoursSinceLastMidnight = $this->getHoursSincePreviousMidnight();
		return $currentHoursSinceLastMidnight - $hoursAgo;
	}
	/**
	 * @param int|string $epochUnixSecondsOrString
	 * @param int|null $frequencyInSeconds
	 * @return string
	 * @throws InvalidTimestampException
	 */
	public function getTodayYesterdayOrDayOfWeekString($epochUnixSecondsOrString,
		int $frequencyInSeconds = null): string{
		if(isset($this->TodayYesterdayOrDayOfWeekStrings[$epochUnixSecondsOrString][$frequencyInSeconds])){
			return $this->TodayYesterdayOrDayOfWeekStrings[$epochUnixSecondsOrString][$frequencyInSeconds];
		}
		$carbon = $this->convertToLocalTimezone($epochUnixSecondsOrString);
		$hoursAgo = $carbon->diffInHours();
		$hoursDifferenceFromLastMidnight = $this->getHourDifferenceFromLastMidnight($epochUnixSecondsOrString);
		$morningNoonOrNight = TimeHelper::morningNoonOrNight($hoursDifferenceFromLastMidnight);
		if($hoursDifferenceFromLastMidnight > 0){
			$human = "this " . $morningNoonOrNight;
		} elseif($hoursDifferenceFromLastMidnight > -24){
			$human = "yesterday " . $morningNoonOrNight;
		} elseif($hoursAgo < 7 * 24){
			$human = 'on ' . $carbon->format('l') . " " . $morningNoonOrNight;
		} else{
			$human = 'on ' . $carbon->format('F d') . " " . $morningNoonOrNight;
		}
		return $this->TodayYesterdayOrDayOfWeekStrings[$epochUnixSecondsOrString][$frequencyInSeconds] = $human;
		//return "on ". date('D', $this->convertToLocalEpochSeconds($epochUnixSecondsOrString));
	}
	/**
	 * @param bool $log
	 * @return int
	 */
	public function getNumberOfUserVariables(bool $log = false): int{
		if($this->numberOfUserVariables === null){
			$this->setNumberOfUserVariables();
		}
		if($log){
			$this->logInfo("Number of user variables: $this->numberOfUserVariables");
		}
		return $this->numberOfUserVariables;
	}
	/**
	 * @return int
	 */
	public function setNumberOfUserVariables(): int{
		return $this->numberOfUserVariables = QMUserVariable::readonly()->where('user_id', $this->id)->count();
	}
	/**
	 * @param bool $log
	 * @return int
	 */
	public function getNumberOfOutcomeVariables(bool $log): int{
		//$count = 0;
		//foreach ($this->getUserVariables() as $variable){if($variable->getOutcome()){$count++;}}
		$count = $this->getBaseUserVariableCommonVariableJoinQuery()->where(VariableCategory::TABLE . '.' .
			VariableCategory::FIELD_OUTCOME, 1)->count();
		if(!$count || $log){
			QMLog::info($this->getLoginNameAndIdString() . " has $count OUTCOME user variables");
		}
		return $count;
	}
	/**
	 * @return QMQB
	 */
	private function getBaseUserVariableCommonVariableJoinQuery(): QMQB{
		$qb = QMUserVariable::readonlyWithCommonVariableJoin();
		return $qb->join(VariableCategory::TABLE, 'variables.' . Variable::FIELD_VARIABLE_CATEGORY_ID, '=',
			VariableCategory::TABLE . '.id')->where('user_variables.user_id', $this->id);
	}
	/**
	 * @return QMQB
	 */
	private function getBaseMeasurementsCommonVariableJoinQuery(): QMQB{
		return QMMeasurement::readonly()->join('variables', 'variables.id', '=', 'measurements.variable_id')
			->where('measurements.user_id', $this->id);
	}
	/**
	 * @param bool $log
	 * @return int
	 */
	public function getNumberOfNonOutcomeVariables(bool $log): int{
		//$count = 0;
		//foreach ($this->getUserVariables() as $variable){if(!$variable->getOutcome()){$count++;}}
		$count = $this->getBaseUserVariableCommonVariableJoinQuery()->where(VariableCategory::TABLE . '.' .
			VariableCategory::FIELD_OUTCOME, 0)->count();
		if(!$count || $log){
			QMLog::info($this->getLoginNameAndIdString() . " has $count NON-outcome user variables");
		}
		return $count;
	}
	/**
	 * @param bool $log
	 * @return int
	 */
	public function getNumberOfNonOutcomeRawMeasurements(bool $log): int{
		//$count = 0;
		//foreach ($this->getUserVariables() as $variable){if(!$variable->getOutcome()){$count += $variable->numberOfRawMeasurements;}}
		$count = $this->getBaseMeasurementsCommonVariableJoinQuery()->where('variables.outcome', 0)->count();
		if(!$count || $log){
			QMLog::info($this->getLoginNameAndIdString() . " has $count NON-outcome raw measurements");
		}
		return $count;
	}
	/**
	 * @param bool $log
	 * @return int
	 */
	public function getNumberOfOutcomeRawMeasurements(bool $log): int{
		//$count = 0;
		//foreach ($this->getUserVariables() as $variable){if($variable->getOutcome()){$count += $variable->numberOfRawMeasurements;}}
		$count = $this->getBaseMeasurementsCommonVariableJoinQuery()->where('variables.outcome', 1)->count();
		if(!$count || $log){
			QMLog::info($this->getLoginNameAndIdString() . " has $count OUTCOME raw measurements");
		}
		return $count;
	}
	/**
	 * @param bool $log
	 * @return int
	 */
	public function getNumberOfMeasurements(bool $log = false): int{
		if($this->numberOfMeasurements === null){
			$this->calculateNumberOfMeasurements();
		}
		if($log){
			$this->logInfo("Number of measurements: $this->numberOfMeasurements");
		}
		return $this->numberOfMeasurements;
	}
	/**
	 * @return int
	 */
	public function calculateNumberOfMeasurements(): int{
		return $this->numberOfMeasurements = $this->l()->measurements()->count();
	}
	/**
	 * @return bool
	 */
	public function isTestUser(): bool{
		return self::isTestUserByIdOrEmail($this->id, $this->email ?? $this->loginName);
	}
	/**
	 * @return bool
	 */
	public function isMikeOrTestUser(): bool{
		if($this->isTestUser()){
			return true;
		}
		if($this->id === 5135 || $this->id === UserIdProperty::USER_ID_MIKE){
			return true;
		}
		return false;
	}
	public function fixIfDifferenceBetweenEarliestAndLatestTimesIsLessThanTwelveHours(){
		$arr = explode("/", $this->latestReminderTime, 2);
		$latestHour = (int)$arr[0];
		$arr = explode("/", $this->earliestReminderTime, 2);
		$earliestHour = (int)$arr[0];
		$difference = $latestHour - $earliestHour;
		if($difference < 12){
			QMLog::error("Fixing reminder times for user " . $this->displayName);
			$this->updateDbRow([
				self::FIELD_LATEST_REMINDER_TIME => self::DEFAULT_LATEST_REMINDER_TIME,
				self::FIELD_EARLIEST_REMINDER_TIME => self::DEFAULT_EARLIEST_REMINDER_TIME,
			]);
		}
		$this->earliestReminderTime = self::DEFAULT_EARLIEST_REMINDER_TIME;
		$this->latestReminderTime = self::DEFAULT_LATEST_REMINDER_TIME;
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function getOrSetAllUserVariables(): array{
		return $this->allUserVariables ?: $this->setAllUserVariables();
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function setAllUserVariables(): array{
		$getUserVariablesRequest = new GetUserVariableRequest([], $this->id);
		$getUserVariablesRequest->setUserId($this->id);
		$getUserVariablesRequest->setLimit(0);
		QMLog::info("Getting ALL user variables for user " . $this->getLoginNameAndIdString());
		$this->allUserVariables = $getUserVariablesRequest->getVariables();
		QMLog::info("Got " . count($this->allUserVariables) . " userVariables for user " .
			$this->getLoginNameAndIdString());
		return $this->allUserVariables;
	}
	/**
	 * @param bool $correlateAll
	 * @return QMUserVariable[]
	 */
	public function getUserVariablesToCorrelate(bool $correlateAll): array{
		if($correlateAll && $this->allUserVariables){
			return $this->allUserVariables;
		}
		if($this->userVariablesToCorrelate === null){
			$this->setUserVariablesToCorrelate($correlateAll);
		}
		return $this->userVariablesToCorrelate;
	}
	/**
	 * @param bool $correlateAll
	 * @return QMUserVariable[]
	 */
	public function setUserVariablesToCorrelate(bool $correlateAll): array{
		$status = $correlateAll ? null : UserVariableStatusProperty::STATUS_CORRELATE;
		$variables = $this->getUserVariablesWithStatus($status);
		QMUserVariable::writable()->where(UserVariable::FIELD_USER_ID, $this->id)
			->where(Variable::FIELD_STATUS, UserVariableStatusProperty::STATUS_CORRELATE)
			->update([Variable::FIELD_STATUS => UserVariableStatusProperty::STATUS_CORRELATING]);
		if(!count($variables)){
			$this->logInfo("Didn't get any user variables to correlate");
		}
		$this->logInfo("Got " . count($variables) . " variables to correlate");
		foreach($variables as $v){
			if(!$v->weShouldCalculateCorrelations()){
				$this->getUserVariablesWithStatus($status);
				le("This is not a stale correlation variable");
			}
		}
		return $this->userVariablesToCorrelate = $variables;
	}
	/**
	 * @param string $status
	 * @return QMUserVariable[]
	 */
	public function getUserVariablesWithStatus(string $status): array{
		if($variables = $this->allUserVariables){
			$variables = Arr::where($variables, static function($v) use ($status){
				/** @var QMUserVariable $v */
				return $v->getStatus() === $status;
			});
		} else{
			$variables = GetUserVariableRequest::getByStatus($this->getId(), $status);
		}
		return $variables;
	}
	/**
	 * @param bool $force
	 * @return bool
	 * @throws TooSlowToAnalyzeException
	 */
	public function correlateAllStale(bool $force = false): bool{
		SolutionButton::add(__FUNCTION__, 'user_variable_relationships?userId' . $this->id . "&correlateAll=true");
		$this->getPHPUnitTestUrlForCorrelateAllStale();
		if(!$force && !$this->userHasOutcomesAndNonOutcomes()){
			$this->logInfo("User does not have outcomes and non-outcomes so setting all CORRELATE user variables to UPDATED");
			$this->setAllCorrelateVariablesToUpdated();
			return false;
		}
		$variables = $this->getUserVariablesToCorrelate(false);
		$total = count($variables);
		$current = 0;
		foreach($variables as $v){
			if(!$v->weShouldCalculateCorrelations()){
				$message = "This is not a stale correlation variable";
				$v->logInfo($message);
				continue;
				//throw new \LogicException($message);
			}
			$current++;
			$this->logInfo("Correlating $current of $total variables");
			$v->calculateCorrelationsIfNecessary();
		}
		return true;
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function analyzeUserVariables(): array{
		return QMUserVariable::analyzeWaitingStaleStuckForUser($this->getId(), UserVariable::TABLE);
	}
	/**
	 * Handle the creation of access token, also issue refresh token if supported / desirable.
	 * @param string $clientId client identifier related to the access token.
	 * @param string $scopes
	 * @param int|null $expiresInSeconds
	 * @return QMAccessToken
	 * @internal param string $scope OPTIONAL scopes to be stored in space-separated string.
	 * @see http://tools.ietf.org/html/rfc6749#section-5
	 * @ingroup oauth2_section_5
	 */
	public function getOrCreateAccessAndRefreshToken(string $clientId,
		string $scopes = 'readmeasurements writemeasurements', int $expiresInSeconds = null): QMAccessToken{
		$accessToken = QMAccessToken::getOrCreateToken($clientId, $this->id, $scopes, $expiresInSeconds);
		$this->setAccessToken($accessToken);
		if(stripos($accessToken->clientId, $clientId) === false){
			$accessToken = QMAccessToken::getOrCreateToken($clientId, $this->id, $scopes, $expiresInSeconds);
			$this->setAccessToken($accessToken);
			le("$accessToken->clientId does not match provided client id $clientId");
		}
		QMRefreshToken::getOrCreateRefreshToken($clientId, $this->id, $scopes, $expiresInSeconds);
		return $accessToken;
	}
	/**
	 * @return int
	 */
	public function setAllCorrelateVariablesToUpdated(): int{
		return QMUserVariable::writable()->where(UserVariable::FIELD_USER_ID, $this->id)
			->where(UserVariable::FIELD_STATUS, UserVariableStatusProperty::STATUS_CORRELATE)->update([
				UserVariable::FIELD_STATUS => UserVariableStatusProperty::STATUS_UPDATED,
				UserVariable::FIELD_UPDATED_AT => date('Y-m-d H:i:s'),
			]);
	}
	/**
	 * @param int $variableId
	 * @param int|null $causeVariableCategoryId
	 * @param int $limit
	 * @return QMUserVariableRelationship[]
	 */
	public function getCorrelationsForOutcome(int $variableId, int $causeVariableCategoryId = null,
		int $limit = 0): array{
		$params = [
			'effectVariableId' => $variableId,
			'userId' => $this->getId(),
			'limit' => $limit,
			//'limit' => 10  // For debugging
		];
		if($causeVariableCategoryId){
			$params['causeVariableCategoryId'] = $causeVariableCategoryId;
		}
		$key = json_encode($params);
		if(isset($this->correlationsForOutcome[$key])){
			return $this->correlationsForOutcome[$key];
		}
		if($causeVariableCategoryId){
			$this->logInfoWithoutContext("Getting user_variable_relationships for variable $variableId with predictor category $causeVariableCategoryId...");
		} else{
			$this->logInfoWithoutContext("Getting user_variable_relationships for variable $variableId...");
		}
		$correlations = QMUserVariableRelationship::getUserVariableRelationships($params);
		return $this->correlationsForOutcome[$key] = $correlations;
	}
	/**
	 * @return UserVariableRelationship[]
	 */
	public function setAllUserVariableRelationships(): array{
		$this->logInfo("Getting all user variable relationships!  WARNING: THIS USES A LOT OF MEMORY! TODO: Use laravel models");
		$rows = $this->getCorrelations();
		$this->logInfo("Got " . $rows->count() .
			" existing correlation rows.  Not instantiating now because it's too slow.");
		return $rows;
	}
	/**
	 * @return UserVariableClient[]
	 */
	public function getUserDataSources(): array{
		if($this->userDataSources !== null){
			return $this->userDataSources;
		}
		$l = $this->l();
		return $this->userDataSources = $l->user_variable_clients()->get()->all();
	}
	/**
	 * @return QMDataSource[]
	 */
	public function getQMDataSources(): array{
		$connectors = $this->getQMConnectors();
		$importers = $this->getSpreadsheetImporters();
		return array_merge($connectors, $importers);
	}
	/**
	 * @param int $causeVariableId
	 * @param int $effectVariableId
	 * @return null|QMUserVariableRelationship
	 */
	public function getExistingCorrelation(int $causeVariableId, int $effectVariableId): ?QMUserVariableRelationship{
		$correlations =
			$this->allUserVariableRelationships ?? [];  // Only use this if already set because it uses too much memory
		foreach($correlations as $c){
			$currentCauseId = $c->causeVariableId ?? $c->cause_variable_id;
			$currentEffectId = $c->effectVariableId ?? $c->effect_variable_id;
			if($currentCauseId === $causeVariableId && $currentEffectId === $effectVariableId){
				return QMUserVariableRelationship::instantiateIfNecessary($c);
			}
		}
		$c = QMUserVariableRelationship::getExistingUserVariableRelationshipByVariableIds($this->getId(), $causeVariableId,
			$effectVariableId);
		return $c ?: null;
	}
	/**
	 * @return QMUser[]
	 */
	public function getPhysicians(): array{
		$users = [];
		$apps = $this->getAuthorizedClients()->getIndividuals();
		foreach($apps as $app){
			$users[] = $app->getQmUser();
		}
		return $users;
	}
	/**
	 * @return int
	 */
	public function getNumberOfConnections(): int{
		return $this->numberOfConnections;
	}
	/**
	 * @return int
	 */
	public function getNumberOfCorrelations(): int{
		$l = $this->l();
		$num = $l->number_of_correlations;
		if($num === null){
			$num = UserNumberOfCorrelationsProperty::calculate($l);
			try {
				$l->save();
			} catch (ModelValidationException $e) {
				le($e);
			}
		}
		return $this->numberOfCorrelations = $num;
	}
	/**
	 * @return int
	 */
	public function getNumberOfRawMeasurementsWithTags(): int{
		return $this->numberOfRawMeasurementsWithTags;
	}
	/**
	 * @return int
	 */
	public function getNumberOfStudies(): int{
		return $this->numberOfStudies;
	}
	/**
	 * @return int
	 */
	public function getNumberOfVotes(): int{
		return $this->numberOfVotes;
	}
	/**
	 * @return int
	 */
	public function getNumberOfRawMeasurementsWithTagsAtLastCorrelation(): int{
		return $this->numberOfRawMeasurementsWithTagsAtLastCorrelation ?: 0;
	}
	/**
	 * @param int $variableId
	 * @return QMTrackingReminder|null
	 */
	public function getExistingReminderByVariableId(int $variableId): ?TrackingReminder{
		$all = $this->getTrackingReminders();
		$existing = Arr::first($all, static function($one) use ($variableId){
			/** @var QMTrackingReminder $one */
			return $one->variable_id === $variableId;
		});
		if($existing){
			return $existing;
		}
		return null;
	}
	/**
	 * @param int|object $reminderData
	 * @return TrackingReminder
	 */
	public function getOrCreateReminder($reminderData = null): TrackingReminder{
		if(!$reminderData){
			return $this->getOrCreateReminderForPrimaryOutcome();
		}
		$variableId = VariableIdProperty::pluckOrDefault($reminderData);
		$existing = $this->getExistingReminderByVariableId($variableId);
		if($existing){
			return $existing;
		}
		$reminder = TrackingReminder::fromData($reminderData);
		return $reminder;
	}
	/**
	 * @return TrackingReminder
	 */
	public function getOrCreateReminderForPrimaryOutcome(): TrackingReminder{
		$v = $this->getPrimaryOutcomeQMUserVariable();
		$r = $v->getOrCreateTrackingReminder();
		return $r;
	}
	/**
	 * @param string $emailType
	 * @return string
	 */
	public function getLastEmailedAt(string $emailType): ?string{
		try {
			$email = $this->getEmail();
		} catch (NoEmailAddressException | InvalidEmailException $e) {
			return null;
		}
		return QMSendgrid::getLastEmailedAt($email, $emailType);
	}
	/**
	 * @param string $emailType
	 * @param int $hours
	 * @return bool
	 */
	public function emailedInLast(string $emailType, int $hours): bool{
		$lastEmailedAt = $this->getLastEmailedAt($emailType);
		if(!$lastEmailedAt){
			$this->logInfo("Never sent $emailType email. ");
			return false;
		}
		if(strtotime($lastEmailedAt) > time() - $hours * 3600){
			$this->logInfo("Already sent $emailType email " . TimeHelper::timeSinceHumanString($lastEmailedAt) . ". ");
			return true;
		}
		$this->logInfo("Last sent $emailType email " . TimeHelper::timeSinceHumanString($lastEmailedAt) . ". ");
		return false;
	}
	/**
	 * @param AppSettings $physicianClientApplication
	 */
	public function setPhysicianClientApplication(AppSettings $physicianClientApplication): void{
		$this->physicianClientApplication = $physicianClientApplication;
	}
	/**
	 * @return UserTag[]
	 */
	public function getAllUserTags(): array{
		if($this->allUserTags !== null){
			return $this->allUserTags;
		}
		return $this->setAllUserTags();
	}
	/**
	 * @return array|static[]
	 */
	private function setAllUserTags(): array{
		$tags = QMUserTag::readonly()->where(QMUserTag::FIELD_USER_ID, $this->id)->getArray();
		if(!$tags){
			$tags = [];
		}
		return $this->allUserTags = $tags;
	}
	public function unsetAllUserTags(){
		$this->allUserTags = null;
	}
	/**
	 * @param int $taggedVariableId
	 * @return array|static[]
	 */
	public function getUserTagRowsForTaggedVariableId(int $taggedVariableId): array{
		$matches = [];
		$all = $this->getAllUserTags();
		foreach($all as $tagRow){
			if($taggedVariableId === $tagRow->tagged_variable_id && $taggedVariableId !== $tagRow->tag_variable_id){
				$matches[] = $tagRow;
			}
		}
		return $matches;
	}
	/**
	 * The time-zone offset is the difference, in minutes, between UTC and local time. Note that this means that
	 * the offset is positive if the local timezone is behind UTC (i.e. UTC−06:00 Central) and negative if it is ahead.
	 * To convert local to UTC, add this value
	 * To convert UTC to local, subtract this value
	 * @return int
	 */
	public function minutesBeforeUTC(): ?int{
		if($this->timezone){
			return QMTimeZone::timeZoneAbbreviationToOffsetInMinutes($this->timezone);
		}
		$minutesBeforeUTC = $this->timeZoneOffset;
		if($minutesBeforeUTC === null){
			return null;
		}
		if($minutesBeforeUTC > 1440 / 2 || $minutesBeforeUTC < -1 * 1440 / 2){
			$converted = $minutesBeforeUTC / 60;
			$this->setTimeZone($converted);
			$this->logError("Time zone offset is $minutesBeforeUTC minutes. Assuming seconds and converting to $converted minutes");
			$converted = (int)$converted;
			QMTimeZone::validateTimeZoneOffsetToAbbreviation($converted, $this->timezone);
			return $this->timeZoneOffset = $converted;
			//throw new \LogicException("Time zone offset is $offsetInMinutes minutes ");
		}
		$minutesBeforeUTC = (int)$minutesBeforeUTC;
		$this->timezone = QMTimeZone::convertTimeZoneOffsetToStringAbbreviation($minutesBeforeUTC);
		return $minutesBeforeUTC;
	}
	/**
	 * @return int
	 */
	public function getTimeZoneOffsetInSeconds(): ?int{
		return $this->minutesBeforeUTC() * 60;
	}
	/**
	 * @param string|null $platform
	 * @return QMDeviceToken[]
	 */
	public function getQMDeviceTokens(string $platform = null): array{
		if($this->deviceTokens === null){
			$this->setQMDeviceTokens();
		}
		if($platform && $this->deviceTokens){
			return QMArr::getElementsWithPropertyMatching('platform', $platform, $this->deviceTokens);
		}
		return $this->deviceTokens;
	}
	/**
	 * @param $id
	 * @return QMUser
	 */
	public static function find($id): ?DBModel{
		$u = User::findInMemoryOrDB($id);
		if(!$u){
			if($u = User::withTrashed()->where(User::FIELD_ID, $id)->first()){
				try {
					throw new DeletedUserException("User {$u->__toString()} is deleted! " . $u->getDataLabShowUrl());
				} catch (\Throwable $e) {
					le($e);
					throw new \LogicException();
				}
			} else{
				le("No user with id $id!");
			}
		}
		return $u->getDBModel();
	}
	public static function testUser(): QMUser{
		return self::find(UserIdProperty::USER_ID_TEST_USER);
	}
	/**
	 * @return QMDeviceToken[]
	 */
	public function setQMDeviceTokens(): array{
		return $this->deviceTokens = QMDeviceToken::getAllForUser($this->id, null, true);
	}
	/**
	 * @return float
	 */
	public function getAverageQmScore(){
		return $this->averageQmScore ?: $this->setAverageQmScore();
	}
	/**
	 * @return float|int
	 */
	public function setAverageQmScore(){
		$key = __FUNCTION__ . "-user-" . $this->getId();
		if($score = QMFileCache::get($key)){
			return $this->averageQmScore = $score;
		}
		$score = QMUserVariableRelationship::readonly()->where(UserVariableRelationship::FIELD_USER_ID, $this->id)
			->average(UserVariableRelationship::FIELD_QM_SCORE);
		$month = 60 * 60 * 24 * 30;
		if($score){
			QMFileCache::set($score, $key, $month);
		}
		return $this->averageQmScore = $score;
	}
	/**
	 * @param string|null $clientId
	 * @return string
	 * @throws ClientNotFoundException
	 * @throws UnauthorizedException
	 */
	public function getOrSetAccessTokenString(string $clientId = null): string{
		$str = $this->accessToken;
		if(empty($str)){
			if(!$clientId){
				$clientId = BaseClientIdProperty::fromRequest();
			}
			if(!$clientId){
				throw new ClientNotFoundException("No client id found");
			}
			$str = $this->setAccessTokenStringByClientId($clientId);
		}
		return $str;
	}
	/**
	 * @param string|null $clientId
	 * @return string
	 */
	public function setAccessTokenStringByClientId(string $clientId): string{
		$t = QMAccessToken::getOrCreateToken($clientId, $this->id);
		$this->setAccessToken($t);
		return $this->accessToken;
	}
	/**
	 * @param string $token
	 * @return string
	 */
	public function setAccessTokenString(string $token): string{
		return $this->accessToken = $token;
	}
	/**
	 * @return bool
	 */
	public function userHasOutcomesAndNonOutcomes(): bool{
		if(!$this->getNumberOfNonOutcomeVariables(false)){
			return false;
		}
		if(!$this->getNumberOfNonOutcomeRawMeasurements(false)){
			return false;
		}
		if(!$this->getNumberOfOutcomeVariables(false)){
			return false;
		}
		if(!$this->getNumberOfOutcomeRawMeasurements(false)){
			return false;
		}
		return true;
	}
	/**
	 * @param string|int $variableNameOrId
	 * @param array $requestParams
	 * @param array $newVariableData
	 * @return QMUserVariable
	 */
	public function getOrCreateQMUserVariable($variableNameOrId, array $requestParams = [],
		array $newVariableData = []): ?QMUserVariable{
		if($this->allUserVariables){
			/** @var QMUserVariable $variable */
			foreach($this->allUserVariables as $variable){
				if($variableNameOrId === $variable->getVariableIdAttribute()){
					return $variable;
				}
				if(strtolower($variableNameOrId) === strtolower($variable->name)){
					if(!isset($newVariableData['unitAbbreviatedName'])){
						return $variable;
					}
					if(!$variable->unitIsIncompatible($newVariableData['unitAbbreviatedName'])){
						return $variable;
					}
				}
			}
		}
		$userVariable =
			QMUserVariable::findOrCreateByNameOrId($this->getId(), $variableNameOrId, $requestParams, $newVariableData);
		if($userVariable){
			$this->allUserVariables[$userVariable->name] = $userVariable;
			return $userVariable;
		}
		return null;
	}
	/**
	 * @param string $email
	 * @param string|null $clientId
	 * @param array $data
	 * @return QMUser
	 */
	public static function getOrCreateByEmail(string $email, string $clientId = null, array $data = []): QMUser{
		$user = QMUser::findByEmail($email);
		if(!$user){
			if(!$clientId){
				$clientId = BaseClientIdProperty::fromRequest(false);
			}
			$providerId = null;
			if($u = QMAuth::getQMUserIfSet()){
				$providerId = 'qm_' . $u->id;
			} elseif($id = QMRequest::getParam(['provider_id', 'clientUserId'])){
				$providerId = $id;
			}
			$data[self::FIELD_USER_EMAIL] = $email;
			$data[self::FIELD_CLIENT_ID] = $clientId;
			if($providerId){
				$data[self::FIELD_PROVIDER_ID] = $providerId;
			}
			$data[self::FIELD_USER_LOGIN] = UserUserLoginProperty::pluckOrDefault($data);
			$user = User::createNewUser($data);
			return $user->getQMUser();
		}
		return $user;
	}
	/**
	 * @param string $reason
	 */
	public function hardDeleteRelated(string $reason){
		$apps = $this->getApplications();
		foreach($apps as $app){
			$app->hardDeleteWithRelations($reason);
		}
		$clients = $this->getOAuthClients();
		foreach($clients as $client){
			try {
				$client->hardDeleteWithRelations($reason);
			} catch (Throwable $e) {
				QMLog::info(__METHOD__.": ".$e->getMessage());
				$client->hardDeleteWithRelations($reason);
			}
		}
		//QMDB::hardDeleteFromAllTablesWhere('user_id', $this->getId(), $reason);
		UserVariableClient::whereUserId($this->getId())->forceDelete();
        return true;
		parent::hardDeleteRelated($reason);
	}
	/**
	 * @param string $reason
	 * @return int
	 */
	public function delete(string $reason): int{
		if($this->isTestUser()){
			return $this->hardDeleteWithRelations($reason);
		}
		//QMFirebase::tryToSetPermanent("deleted_user/$this->id", $reason);
		$this->deletionReason = $reason;
		QMLog::errorOrInfoIfTesting("User deleted account because $reason");
		TrackingReminderNotification::forceDeleteWhereUserId($this->getId());
		Credential::forceDeleteWhereUserId($this->getId());
		OARefreshToken::forceDeleteWhereUserId($this->getId());
		OAAccessToken::forceDeleteWhereUserId($this->getId());
		WpUsermetum::forceDeleteWhereUserId($this->getId());
		DeviceToken::forceDeleteWhereUserId($this->getId());
		TrackingReminder::forceDeleteWhereUserId($this->getId());
		Connection::whereUserId($this->getId())
			->update([Connection::FIELD_CONNECT_STATUS => ConnectionConnectStatusProperty::CONNECT_STATUS_DISCONNECTED]);
		return $this->updateDbRow([
			self::FIELD_DELETED_AT => date('Y-m-d H:i:s'),
			self::FIELD_USER_EMAIL => 'deleted_' . $this->email,
			self::FIELD_USER_LOGIN => 'deleted_' . $this->loginName,
			self::FIELD_DELETION_REASON => $reason,
		]);
	}
	/**
	 * @return TrackingReminder[]|Collection
	 */
	public function getTrackingReminders(): Collection{
		return $this->l()->getTrackingReminders();
	}
	/**
	 * @param int $variableId
	 * @return TrackingReminder[]
	 */
	public function getTrackingRemindersByVariableId(int $variableId): array{
		$trackingReminders = $this->getTrackingReminders();
		return $trackingReminders->filter(function(TrackingReminder $trackingReminder) use ($variableId){
			return $trackingReminder->variable_id == $variableId;
		})->all();
	}
	/**
	 * @param string $variableName
	 * @return QMTrackingReminder[]
	 */
	public function getTrackingRemindersByVariableName(string $variableName): array{
		$trackingReminders = $this->getTrackingReminders();
		return $trackingReminders->filter(function(QMTrackingReminder $trackingReminder) use ($variableName){
			return $trackingReminder->getVariableName() === $variableName;
		})->all();
	}

	/**
	 * @return string
	 */
	public function getCountry(): ?string{
		$l = $this->l();
		return $this->country = $l->country;
	}
	/**
	 * @param string $ip
	 * @return string
	 */
	public function setCountry(string $ip = "Visitor"): string{
		if(AppMode::isApiRequest()){
			$countryCode = BaseCountryProperty::getCountryCode($ip);
			if($countryCode && $countryCode !== $this->country){
				$this->setAttribute(User::FIELD_COUNTRY, $countryCode);
				try {
					$this->l()->save();
				} catch (ModelValidationException $e) {
					le($e);
				}
			}
		}
		return $this->country;
	}
	/**
	 * @return bool
	 */
	public function getShareAllData(): bool{
		if(!AppMode::isUnitTest() && $this->getId() === UserIdProperty::USER_ID_DEMO){
			return $this->l()->share_all_data = $this->shareAllData = true;
		}
		return $this->shareAllData ?? false;
	}
	/**
	 * @return bool
	 */
	private function deleteFromMemcached(): bool{
		$result = QMFileCache::delete(self::getMemcachedKey($this->getUserId()));
		return $result;
	}
	/**
	 * @param int $userId
	 * @return string
	 */
	private static function getMemcachedKey(int $userId): string{
		return 'user_' . $userId;
	}
	public function unsetPasswordAndTokenProperties(){
		unset($this->password);
		foreach($this as $key => $value){
			if(stripos($key, 'token') !== false){
				unset($this->$key);
			}
		}
	}
	public function getNotificationSettings(){
		$arr = [];
		foreach($this as $key => $value){
			if(QMUser::isNotificationSetting($key)){
				$arr[$key] = $value;
			}
		}
		return $arr;
	}
	/**
	 * @return QMConnector[]
	 */
	public function getOrSetConnectors(): array{
		if($this->connectors === null){
			$this->setQMConnectors();
		}
		return $this->connectors;
	}
	/**
	 * @return QMDataSource[]
	 */
	public function getDataSources(): array{
		$QMConnectors = $this->getQMConnectors();
		$QMSpreadsheetImporters = $this->getSpreadsheetImporters();
		return array_merge($QMConnectors, $QMSpreadsheetImporters);
	}
	/**
	 * @param int $secondsCutoff
	 * @return bool
	 * @throws InvalidTimestampException
	 */
	public function isOlderThanXSeconds(int $secondsCutoff): bool{
		$registered = $this->userRegistered;
		if(!$registered){
			le("No userRegistered for $this");
		}
		$ageInSeconds = time() - TimeHelper::universalConversionToUnixTimestamp($registered);
		return $ageInSeconds > $secondsCutoff;
	}
	/**
	 * @return bool
	 */
	public function isOlderThan1Day(): bool{
		return $this->isOlderThanXSeconds(86400);
	}
	/**
	 * @return bool
	 */
	public function setShowAds(): bool{
		if($this->stripeActive){
			return $this->showAds = false;
		}
		if(!$this->isOlderThan1Day()){
			return $this->showAds = false;
		}
		if(!AppMode::isApiRequest()){
			return $this->showAds = false;
		}
		$clientId = BaseClientIdProperty::fromRequest(false);
		if($clientId){
			//$appSettings = AppSettings::getClientAppSettings($clientId, null, false);
			$appSettings = Memory::getClientAppSettings($clientId);  // TODO: Might want to uncomment above sometime
			if($appSettings && !$appSettings->getAdditionalSettings()->getMonetizationSettings()->advertisingEnabled){
				return $this->showAds = false;
			}
		}
		return $this->showAds = true;
	}
	/**
	 * @return AuthorizedClients
	 */
	public function getAuthorizedClients(): AuthorizedClients{
		if($this->authorizedClients !== null){
			return $this->authorizedClients;
		}
		return $this->setAuthorizedAppsStudiesAndUsers();
	}
	/**
	 * @return AuthorizedClients
	 */
	private function setAuthorizedAppsStudiesAndUsers(): AuthorizedClients{
		return $this->authorizedClients = new AuthorizedClients($this->id);
	}
	/**
	 * @param string $clientIdToRevoke
	 * @return AuthorizedClients
	 */
	public function revokeClientAccess(string $clientIdToRevoke): AuthorizedClients{
		$result = QMClient::revokeAccess($clientIdToRevoke, $this->id);
		if(!$result){
			le("Could not revoke access for $clientIdToRevoke");
		}
		return $this->setAuthorizedAppsStudiesAndUsers();
	}
	/**
	 * @return QMCohortStudy[]
	 */
	public function getStudiesJoined(): array{
		return $this->getAuthorizedClients()->getStudies();
	}
	/**
	 * @param string $clientId
	 * @param string|null $scopes
	 * @return bool|QMAccessToken
	 */
	public function getValidAccessTokenForClient(string $clientId, string $scopes = null){
		$tokens = $this->getAllValidAccessTokens();
		if(!$tokens){
			return false;
		}
		foreach($tokens as $accessToken){
			if(strtolower($clientId) === strtolower($accessToken->clientId)){
				if($scopes && !QMStr::stringContainsAllWordsInAnotherString($scopes, $accessToken->scope)){
					continue;
				}
				if($accessToken->isExpired()){
					continue;
				}
				return $accessToken;
			}
		}
		return false;
	}
	/**
	 * @return QMAccessToken[]
	 */
	public function getAllValidAccessTokens(): array{
		if($this->allValidAccessTokens === null){
			$this->setAllValidAccessTokens();
		}
		return $this->allValidAccessTokens;
	}
	/**
	 * @return QMAccessToken[]
	 */
	public function setAllValidAccessTokens(): array{
		$tokens = QMAccessToken::getAllForUser($this);
		return $this->allValidAccessTokens = $tokens;
	}
	/**
	 * @return string
	 */
	public function getCurrency(): ?string{
		if(is_object($this->currency)){ // currency needs to be string or there's a validation exception when laravel tries to save
			if(isset($this->currency->user_currency)){
				$this->currency = $this->currency->user_currency;
			} else{
				$this->exceptionIfNotProductionAPI("Please implement currency formatter for " .
					\App\Logging\QMLog::print_r($this->currency));
				$this->currency = json_encode($this->currency);
			}
		}
		return $this->currency;
	}
	/**
	 * @return string
	 */
	public function getLanguage(): ?string{
		return $this->language;
	}
	/**
	 * @return string
	 */
	public function getZipCode(): ?string{
		$zip = $this->zipCode;
		if($zip !== null){
			return $zip;
		}
		try {
			$geoLocation = $this->getIpGeoLocation();
			$zip = $geoLocation->zip;
		} catch (NoGeoDataException $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());
		}
		if(!$zip){
			$c = WeatherConnector::getByUserId($this->getId());
			$zip = $c->getZipFromCredentials();
		}
		if(!$zip){
			$ip = $this->getIpAddress();
			if($ip){
				$zip = BaseZipCodeProperty::fromIP($ip);
			}
		}
		if(!$zip){
			/** @var WeatherConnector $c */
			$c = WeatherConnector::getByUserId($this->getId());
			$lat = $c->getCredentialsArray('latitude');
			$long = $c->getCredentialsArray('longitude');
			if($lat && $long){
				try {
					$zip = BaseZipCodeProperty::getZipCodeFromLatitudeAndLongitude($lat, $long);
				} catch (NoGeoDataException $e) {
					$this->logError(__METHOD__.": ".$e->getMessage());
				}
			}
		}
		if($zip){
			$this->updateDbRow([self::FIELD_ZIP_CODE => $zip], __FUNCTION__);
		}
		return $this->zipCode = $zip;
	}
	/**
	 * @return bool
	 */
	public function tooLateOrEarlyForNotifications(): bool{
		$localTime = $this->utcToLocalHis(time());
		if($localTime < $this->earliestReminderTime){
			$this->logInfo("$localTime is too early to send notifications. earliestReminderTime is $this->earliestReminderTime.");
			return true;
		}
		if($localTime > $this->latestReminderTime){
			$this->logInfo("$localTime is too late to send notifications. latestReminderTime is $this->latestReminderTime.");
			return true;
		}
		return false;
	}
	/**
	 * @return QMUser
	 */
	public static function mike(): QMUser{
		return User::mike()->getQMUser();
	}
	/**
	 * @return QMUser
	 */
	public static function demo(): QMUser{
		$user = User::demo()->getQMUser();
		$user->setAccessTokenString(UserUserLoginProperty::USERNAME_DEMO);
		$user->getShareAllData();
		return $user;
	}
	/**
	 * @param Phrase $phrase
	 * @return PushNotification[]
	 * @throws NoDeviceTokensException
	 */
	public function askQuestion(Phrase $phrase): array{
		$data = new PushNotificationData();
		$data->setTitle($phrase->title ?: "Question from " . $phrase->getQMUser()->displayName);
		$data->setMessage($phrase->text);
		$data->setForceStart(1);
		$data->setNotId($phrase->id);
		$data->setUrl(IonicHelper::getChatUrl(['phrase' => $phrase]));
		return $this->getUser()->notifyByPushData($data);
	}
	/**
	 * @param PushNotificationData}string $pushData
	 * @param string|null $message
	 * @param int|null $notId
	 * @return array
	 * @throws NoDeviceTokensException
	 */
	public function notifyByString($pushDataOrTitle, string $message = null, int $notId = null): array{
		if(is_string($pushDataOrTitle)){
			$pushData = new PushNotificationData();
			$pushData->setTitle($pushDataOrTitle);
			$pushData->setNotId(time());
		} elseif($pushDataOrTitle instanceof PushNotificationData){
			$pushData = $pushDataOrTitle;
		} else{
			le("No push data or title!");
			throw new \LogicException();
		}
		if($message){
			$pushData->setMessage($message);
		}
		if($notId){
			$pushData->setNotId($notId);
		}
		return $this->notifyByPushData($pushData);
	}
	/**
	 * @param bool $includeStudyCards
	 * @param int $limit
	 * @return TrackingReminderNotificationCard[]
	 */
	public function getTrackingRemindersNotificationCards(bool $includeStudyCards, int $limit = 10): array{
		$notifications = $this->getPastTrackingRemindersNotifications($limit);
		$cards = [];
		$noCorrelations = $aggregateCorrelationIds = $userVariableRelationshipIds = [];
		foreach($notifications as $n){
			if($id = $n->bestUserVariableRelationshipId){
				$userVariableRelationshipIds[] = $id;
			} elseif($id = $n->bestGlobalVariableRelationshipId){
				$aggregateCorrelationIds[] = $id;
			} else{
				if(stripos($n->variableName, "test") === false){
					$noCorrelations[] = $n->variableName;
				}
			}
		}
		if($noCorrelations){
			$noCorrelations = array_unique($noCorrelations);
			QMLog::error("No best user or global variable relationships for the following notifications:\n\t" .
				implode(", ", $noCorrelations));
		}
		$userVariableRelationshipIds = array_unique($userVariableRelationshipIds);
		$aggregateCorrelationIds = array_unique($aggregateCorrelationIds);
		$correlations = ($userVariableRelationshipIds) ? UserVariableRelationship::getWithVariables($userVariableRelationshipIds) : [];
		$aggregateCorrelations = ($aggregateCorrelationIds) ?
			GlobalVariableRelationship::getWithVariables($aggregateCorrelationIds) : [];
		foreach($notifications as $n){
			$cards[] = $n->getOptionsListCard();
			if($includeStudyCards){
				if($id = $n->bestUserVariableRelationshipId){
					$cards[] = $correlations[$id]->getCard();
				} elseif($id = $n->bestGlobalVariableRelationshipId){
					$cards[] = $aggregateCorrelations[$id]->getCard();
				} else{
					if($n->numberOfRawMeasurementsWithTagsJoinsChildren){
						$n->logError("No best study! " . $n->getMeasurementQuantitySentence());
						if($n->needToCorrelate()){
							$n->queueCorrelation("No best study was available for notification feed. ");
						}
					} else{
						$n->logError("There are no measurements to create a study. ");
					}
				}
			}
		}
		return $cards;
	}
	/**
	 * @param int $limit
	 * @return QMTrackingReminderNotification[]
	 */
	public function getPastTrackingRemindersNotifications(int $limit = 10): array{
		/** @var QMTrackingReminderNotification[] $notifications */
		$notifications = $this->pastTrackingReminderNotifications;
		if($notifications === null){
			$notifications = $this->setPastTrackingReminderNotifications($limit);
		}
		return $this->pastTrackingReminderNotifications = $notifications;
	}
	/**
	 * @param int $limit
	 * @return QMTrackingReminderNotification[]
	 */
	public function setPastTrackingReminderNotifications(int $limit = 10): array{
		$notifications = QMTrackingReminderNotification::getPastQMTrackingReminderNotifications([
			'limit' => $limit,
			QMTrackingReminderNotification::FIELD_USER_ID => $this->getId(),
		]);
		return $this->pastTrackingReminderNotifications = $notifications;
	}
	/**
	 * @param int|null $variableIdToExclude
	 * @return QMTrackingReminderNotification
	 */
	public function getMostRecentPendingNotification(int $variableIdToExclude = null): ?QMTrackingReminderNotification{
		if($n = $this->mostRecentTrackingReminderNotification){
			return $n;
		}
		if($n === false){
			return null;
		}
		return $this->setMostRecentPendingNotification($variableIdToExclude);
	}
	/**
	 * @param int|null $variableIdToExclude
	 * @return QMTrackingReminderNotification
	 */
	public function setMostRecentPendingNotification(int $variableIdToExclude = null): ?QMTrackingReminderNotification{
		$n = $this->mostRecentTrackingReminderNotification =
			QMTrackingReminderNotification::getMostRecent($this->id, $variableIdToExclude);
		if(!$n){
			return null;
		}
		return $n;
	}
	/**
	 * @return QMUserVariable
	 */
	public function getPrimaryOutcomeQMUserVariable(): QMUserVariable{
		$id = $this->primaryOutcomeVariableId;
		if($id){
			$id = (int)$id;
			$vFromMeta = QMUserVariable::getOrCreateById($this->getId(), $id);
			if(!$vFromMeta->getNumberOfMeasurements() && $this->numberOfRawMeasurementsWithTags){
				$this->logInfo("No measurements for primary outcome: $vFromMeta->name so going to try and find a better one");
			} else{
				return $vFromMeta;
			}
		}
		$v = UserPrimaryOutcomeVariableIdProperty::calculate($this->l());
		return $v;
	}
	/**
	 * @param string|int|null $effectNameOrId
	 * @return RootCauseAnalysis
	 */
	public function getRootCauseAnalysis($effectNameOrId = null): RootCauseAnalysis{
		if($effectNameOrId){
			$v = $this->getOrCreateQMUserVariable($effectNameOrId);
		} else{
			$v = $this->getPrimaryOutcomeQMUserVariable();
		}
		return $v->getRootCauseAnalysis();
	}
	/**
	 * @param string|null $like
	 * @return QMButton[]
	 */
	public function getFileButtons(string $like = null): array{
		return $this->l()->getFileButtons($like);
	}
	/**
	 * @param string $variableCategoryName
	 * @return bool
	 */
	public function setDoneAddingCategory(string $variableCategoryName): bool{
		return $this->setUserMetaValue($this->getDoneAddingCategoryKey($variableCategoryName), true);
	}
	/**
	 * @param string $variableCategoryName
	 * @return string
	 * @throws VariableCategoryNotFoundException
	 */
	private function getDoneAddingCategoryKey(string $variableCategoryName): string{
		$category = QMVariableCategory::findByNameOrSynonym($variableCategoryName);
		$key = 'done_adding_' . QMStr::snakize($category->getNameAttribute());
		return $key;
	}
	/**
	 * @param string $variableCategoryName
	 * @return bool
	 */
	public function getDoneAddingCategory(string $variableCategoryName): bool{
		return (bool)$this->getUserMetaValue($this->getDoneAddingCategoryKey($variableCategoryName));
	}
	/**
	 * @return bool
	 */
	public function isCloudTestLab(): bool{
		return stripos($this->loginName, 'cloudtestlabaccounts') !== false;
	}
	/**
	 * @return QMWpUser
	 */
	public function getOrCreateWordPressStudiesUser(): QMWpUser{
		if($this->wpStudiesUser){
			return $this->wpStudiesUser;
		}
		//        $wpUserId = $this->getWpStudiesUserId();
		//        if($wpUserId){
		//            $this->logInfo("Getting existing WP user id $wpUserId...");
		//            $wpUser = WpUser::getById($wpUserId);
		//        }else{
		//            $this->logInfo("No existing WP user id so creating one");
		//            $wpUser = new WpUser();
		//        }
		$wpUser = QMWpUser::find($this->getId());
		if(!$wpUser){
			$wpUser = new QMWpUser();
		}
		$clone = clone $this;
		//unset($clone->id);
		$wpUser->populateFieldsByArrayOrObject($clone);
		$wpUser->setQmUser($this);
		$wpUser->setUserLogin($this->getLoginName());
		$wpUser->setUserPass($this->getEncryptedPasswordHash());
		try {
			$wpUser->setUserEmail($this->getEmail());
		} catch (NoEmailAddressException | InvalidEmailException $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());
		}
		$wpUser->setUserNicename($this->getUrlSafeNiceName());
		try {
			$wpUser->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
		return $this->wpStudiesUser = $wpUser;
	}
	/**
	 * @return string
	 */
	public function getPHPUnitTestUrlForCorrelateAllStale(): ?string{
		return StagingJobTestFile::getUrl('CorrelateAllUser' . $this->getId(),
			'self::find(' . $this->getId() . ')->correlateAllStale();', \App\Slim\Model\User\QMUser::class);
	}
	/**
	 * @return string
	 */
	public function getLastLoginAt(): ?string{
		return $this->lastLoginAt;
	}
	/**
	 * @param int|string $lastLoginAt
	 */
	public function setLastLoginAt($lastLoginAt = null): void{
		$this->lastLoginAt = db_date($lastLoginAt);
		$this->setLastActive();
	}
	/**
	 * @return QMSpreadsheetImporter[]
	 */
	public function getSpreadsheetImporters(): array{
		QMLog::debug(__METHOD__);
		if($this->spreadsheetImporters !== null){
			return $this->spreadsheetImporters;
		} // Why was this commented?
		$importers = QMSpreadsheetImporter::get();
		foreach($importers as $importer){
			$importer->userId = $this->getId();
		}
		if(QMDataSource::getAvoidDatabaseQueries()){
			return $this->spreadsheetImporters = $importers;
		}
		if(AppMode::isApiRequest() && !QMRequest::urlContains('connectors')){
			return $this->spreadsheetImporters = $importers;
		}
		$requests = $this->getMostRecentImportRequestsForEachSource();
		foreach($importers as $importer){
			foreach($requests as $request){
				if(QMStr::isCaseInsensitiveMatch($request->getSourceName(), $importer->getNameAttribute())){
					$importer->setRequest($request);
				}
			}
			$this->spreadsheetImporters[$importer->name] = $importer;
		}
		return $this->spreadsheetImporters;
	}
	/**
	 * @return SpreadsheetImportRequest[]
	 */
	public function getMostRecentImportRequestsForEachSource(): array{
		QMLog::debug(__FUNCTION__);
		//if($this->spreadsheetImportRequests !== null){return $this->spreadsheetImportRequests;}
		$indexedBySource = [];
		$rows = SpreadsheetImportRequest::readonly()->where(SpreadsheetImportRequest::FIELD_USER_ID, $this->getId())
			->orderBy(SpreadsheetImportRequest::FIELD_CREATED_AT)->getArray();
		QMLog::debug(count($rows) . " SpreadsheetImportRequests ");
		if(!$rows){
			return $this->spreadsheetImportRequests = [];
		}
		$requests = SpreadsheetImportRequest::instantiateNonDBRows($rows);
		foreach($requests as $request){
			$indexedBySource[$request->getSourceName()] = $request;
		}
		return $this->spreadsheetImportRequests = $indexedBySource;
	}
	/**
	 * @return string
	 */
	public function getCapabilitiesString(): string{
		if($this->isAdmin()){
			return 'a:1:{s:13:"administrator";b:1;}';
		}
		return 'a:1:{s:6:"author";b:1;}';
	}
	/**
	 * @return string
	 */
	public function getSubscriptionProvider(): ?string{
		return $this->subscriptionProvider;
	}

	/**
	 * @return string
	 */
	public function getLastCorrelationAt(): ?string{
		return $this->lastCorrelationAt;
	}
	public function updateUserStatisticsAndCorrelate(){
		$this->logInfo(__FUNCTION__);
		$this->logInfo("Last correlated " . TimeHelper::timeSinceHumanString($this->getLastCorrelationAt()));
		$arr = $this->calculateAndLogInterestingRelationCounts();
		$m = $arr[self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS];
		$mAtLastAnalysis = (int)QMUserVariable::readonly()->where(UserVariable::FIELD_USER_ID, $this->getId())
			->sum(UserVariable::FIELD_NUMBER_OF_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION);
		if($m > $mAtLastAnalysis){
			$this->logInfo("Correlating because $m RAW_MEASUREMENTS > $mAtLastAnalysis MEASUREMENTS_AT_LAST_ANALYSIS ");
			$arr[self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION] = $m;
			try {
				$this->correlateAllStale();
			} catch (TooSlowToAnalyzeException $e) {
				le("We shouldn't be calling updateUserStatisticsAndCorrelate in API requests!");
			}
		} else{
			$this->logInfo("Not correlating because $m RAW_MEASUREMENTS === $mAtLastAnalysis MEASUREMENTS_AT_LAST_ANALYSIS ");
		}
		$arr[self::FIELD_LAST_CORRELATION_AT] = now_at();
		$this->updateDbRow($arr);
	}
	/**
	 * @param bool $calculate
	 * @return string
	 */
	public function getDataQuantityListRoundedButtonsHTML(bool $calculate = true): string{
		if($this->dataQuantityListButtonsHtml){
			return $this->dataQuantityListButtonsHtml;
		}
		//if(AppMode::xDebugActive()){$calculate = false;}
		if($calculate){
			$this->calculateAndLogInterestingRelationCounts();
		}
		$str = '';
		if($number = $this->getNumberOfTrackingReminders()){
			$str .= RemindersManageStateButton::instance()->getBarChartTableRowHtmlForEmail($number . " Reminders",
				"Manage Reminders &rarr;");
		} else{
			$str .= OnboardingStateButton::instance()->getBarChartTableRowHtmlForEmail($number . " Reminders",
				"Create Reminder &rarr;");
		}
		if($number = $this->getNumberOfMeasurements()){
			$str .= HistoryAllStateButton::instance()->getBarChartTableRowHtmlForEmail($number . " Measurements",
				"See History &rarr;");
		} else{
			$str .= MeasurementAddSearchStateButton::instance()->getBarChartTableRowHtmlForEmail($number .
				" Measurements", "Record Measurement &rarr;");
		}
		$str .= ImportStateButton::instance()->getBarChartTableRowHtmlForEmail($this->getNumberOfConnections() .
			" Data Sources", "Import Data &rarr;");
		$str .= StudyCreationStateButton::instance()->getBarChartTableRowHtmlForEmail($this->getNumberOfCorrelations() .
			" Studies", "Create Study &rarr;");
		$maxWidth = CssHelper::GLOBAL_MAX_POST_CONTENT_WIDTH;
		return $this->dataQuantityListButtonsHtml = "
            <div style='max-width: " . $maxWidth . "px; margin: 10px auto 10px auto;'>
                $str
            </div>
        ";
	}
	/**
	 * @return string
	 */
	public function getDataQuantityTableHTML(): string{
		return QMTable::associativeArrayToTable($this->getRelationCountsArray());
	}
	/**
	 * @return array
	 */
	public function getRelationCountsArray(): array{
		if($this->dataQuantities){
			return $this->dataQuantities;
		}
		$arr = [
			"Connections to 3rd Party Data Sources" => $this->getNumberOfConnections(),
			"Studies on the Relationship Between Two Variables" => $this->getNumberOfCorrelations(),
			"Raw Measurements" => $this->getNumberOfMeasurements(),
			"Outcome Variables" => $this->getNumberOfOutcomeVariables(false),
			"Predictor Variables" => $this->getNumberOfNonOutcomeVariables(false),
			"Processed Measurements (Including Tag-Derived)" => $this->getNumberOfRawMeasurementsWithTags(),
			"Processed Measurements At Last Full Analysis" => $this->getNumberOfRawMeasurementsWithTagsAtLastCorrelation(),
			"Published Studies" => $this->getNumberOfStudies(),
			"Reviewed Studies" => $this->getNumberOfVotes(),
			"Tracking Reminders" => $this->getNumberOfTrackingReminders(),
		];
		return $this->dataQuantities = $arr;
	}
	public function logRelationCounts(){
		$stats = $this->getRelationCountsArray();
		$this->logInfo("Data Quantities: ");
		QMLog::logKeyValueArray($stats);
	}
	/**
	 * @return string
	 */
	public function getPatientHistoryUrl(): string{
		$t = $this->getOrSetAccessTokenString(BaseClientIdProperty::CLIENT_ID_SYSTEM);
		return IonicHelper::getPatientHistoryUrl($t);
	}
	/**
	 * @return void
	 */
	public function logPatientHistoryUrl(): void{
		$this->logInfoWithoutObfuscation($this->getPatientHistoryUrl());
	}
	/**
	 * @return array
	 */
	public function calculateAndLogInterestingRelationCounts(): array{
		$arr = $this->calculateInterestingNumberOfRelationCounts();
		$this->logRelationCounts();
		return $arr;
	}
	/**
	 * @return QMUserVariable
	 */
	public function calculatePrimaryOutcomeVariable(): QMUserVariable{
		$v = $this->getPrimaryOutcomeFromReminders();
		if(!$v || $v->getNumberOfMeasurements() < 30){
			$v = $this->getPrimaryOutcomeFromVariablesTable();
		}
		if(!$v){
			try {
				$app = $this->getAppSettingsForClientThatCreated();
				$searchResult = $app->getPrimaryOutcomeVariable();
				$v = $searchResult->findQMUserVariable($this->getId());
			} catch (ClientNotFoundException $e) {
				$this->logError(__METHOD__.": ".$e->getMessage());
			}
		}
		if(!$v){
			$v = OverallMoodCommonVariable::instance()->findQMUserVariable($this->getId());
			$this->logError("Could not determine primary outcome variable so using $v->name");
			$this->primaryOutcomeVariableId = $v->getVariableIdAttribute();
			return $v;  // Don't save fallback id so that we allow a better one to be saved later
		}
		$this->updatePrimaryOutcomeVariableIdNameAndCreateReminder($v->getVariableIdAttribute());
		return $v;
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function getOutcomeVariablesWithData(): array{
		$qb = UserPrimaryOutcomeVariableIdProperty::outcomeUserVariableQb($this)->where(UserVariable::TABLE . '.' .
			UserVariable::FIELD_NUMBER_OF_MEASUREMENTS, '>', 30)->where(UserVariable::TABLE . '.' .
			UserVariable::FIELD_NUMBER_OF_CORRELATIONS, '>', 1);
		$qb->whereIn(Variable::TABLE . '.' . Variable::FIELD_VARIABLE_CATEGORY_ID, [
			QMVariableCategory::getSymptoms()->getId(),   // We want to give preference to symptoms and emotions over
			QMVariableCategory::getEmotions()->getId()    // Rescuetime and Fitbit generated data
		])->whereIn(Variable::TABLE . '.' . Variable::FIELD_DEFAULT_UNIT_ID, [
			QMUnit::getPercent()->getId(),
			QMUnit::getOneToFiveRating()->getId(),
		]);
		$userVariables = $qb->getDBModels();
		return $userVariables;
	}
	/**
	 * @param int|string $variableIdOrName
	 * @param array $newVariableData
	 * @return QMUserVariable
	 */
	public function findOrCreateQMUserVariable($variableIdOrName, array $newVariableData = []): QMUserVariable{
		return QMUserVariable::findOrCreateByNameOrIdOrSynonym($this->getId(), $variableIdOrName, [], $newVariableData);
	}
	/**
	 * @return string
	 * @throws InvalidEmailException
	 * @throws NoEmailAddressException
	 */
	public function getMailToLink(): string{
		return 'mailto:' . $this->getEmail();
	}
	/**
	 * @param int $primaryOutcomeVariableId
	 * @return int
	 */
	public function updatePrimaryOutcomeVariableIdNameAndCreateReminder(int $primaryOutcomeVariableId): int{
		if($this->primaryOutcomeVariableId === $primaryOutcomeVariableId){
			$this->logError("Primary outcome was already $primaryOutcomeVariableId");
			return 0;
		}
		$result = $this->updateDbRow([self::FIELD_PRIMARY_OUTCOME_VARIABLE_ID => $primaryOutcomeVariableId]);
		$this->getOrSetPrimaryOutcomeVariableNameFromGlobals(); // Must be done afterwards
		$v = QMUserVariable::getOrCreateById($this->getId(), $primaryOutcomeVariableId);
		if($v->getManualTracking()){
			if(!$v->getNumberOfMeasurements() && !$v->numberOfTrackingReminders){
				$reminder = $v->getQMTrackingReminder();
				if($reminder){
					$this->logError("Why is numberOfTrackingReminders $v->numberOfTrackingReminders if we have a reminder?");
				}
			}
			$v->getOrCreateTrackingReminder();
		}
		return $result;
	}
	/**
	 * @return string|null
	 */
	public function getOrSetPrimaryOutcomeVariableNameFromGlobals(): ?string{
		$name = $this->primaryOutcomeVariableName;
		$id = $this->primaryOutcomeVariableId;
		if(!$name && $id){
			$name = VariableNameProperty::fromId($id);
		}
		return $this->primaryOutcomeVariableName = $name;
	}
	/**
	 * @return string|null
	 */
	public function getOrSetPrimaryOutcomeVariableName(): ?string{
		$name = $this->getOrSetPrimaryOutcomeVariableNameFromGlobals();
		$id = $this->primaryOutcomeVariableId;
		if(!$name && $id){
			$name = VariableNameProperty::fromId($id);
		}
		return $this->primaryOutcomeVariableName = $name;
	}
	/**
	 * @return string
	 */
	public function getAccessTokenStringIfSet(): ?string{
		if($this->isDemoUser()){
			$this->accessToken = "demo";
		}
		if(!isset($this->accessToken)){
			return $this->accessToken = null; // Property gets unset somehow
		}
		return $this->accessToken;
	}
	public function sendRootCauseAnalysis(){
		$v = $this->getPrimaryOutcomeQMUserVariable();
		$v->sendRootCauseAnalysis();
	}
	/**
	 * @return AnalyticalReport
	 */
	public function getReport(): AnalyticalReport{
		$v = $this->getPrimaryOutcomeQMUserVariable();
		return $v->getReport();
	}
	/**
	 * @param int $id
	 * @return QMSpreadsheetImporter|null
	 */
	public function getSpreadsheetImporter(int $id): ?QMSpreadsheetImporter{
		return Arr::first($this->getSpreadsheetImporters(), function($c) use ($id){
			/** @var QMSpreadsheetImporter $c */
			return $c->id === $id;
		});
	}

    /**
     * @param int $id
     * @return QMDataSource
     * @throws ConnectorDisabledException
     * @throws NoGeoDataException
     */
	public function getDataSource(int $id): QMDataSource{
		$connectors = $this->getOrSetConnectors();
		$s = Arr::first($connectors, function($c) use ($id){
			/** @var QMConnector $c */
			return $c->id === $id;
		});
		if(!$s){
			$s = $this->getSpreadsheetImporter($id);
		}
		if(!$s){
            $connectors = QMConnector::getAnonymousConnectors();
            $s = Arr::first($connectors, function($c) use ($id){
                /** @var QMConnector $c */
                return $c->id === $id;
            });
            if($s && !$s->availableOutsideUS){
                if($this->isOutsideUS()){
                    $geo = $this->getIpGeoLocation()->print();
                    throw new NoGeoDataException("cannot import from $s for $geo ");
                }
            }
            $c = Connector::find($id);
            if($c){
                if(!$c->enabled){
                    throw new ConnectorDisabledException($c);
                } else {
                    le("Could not get connector $c->name but it's in the database and enabled: ".$c->print());
                }
            }
			le("No connector or spreadsheet importer with id $id found: " . QMLog::var_export($s, true));
		}
		return $s;
	}
	/**
	 * @param Connection[] $connections
	 * @return Connection[]
	 */
	public function setConnections(?array $connections): ?array{
		return $this->connections = $connections;
	}
	/**
	 * @return WpPost
	 */
	public function firstOrCreateWpPost(): WpPost{
		return $this->l()->firstOrCreateWpPost();
	}
	/**
	 * @return bool
	 */
	public function isDemoUser(): bool{
		if(AppMode::isAnyKindOfUnitTest() && !AppMode::isStagingUnitTesting()){
			return false;
		}
		if(Subdomain::isTesting()){
			return false;
		}
		return $this->getId() === UserIdProperty::USER_ID_DEMO;
	}
	/**
	 * @return WpPost[]
	 */
	public function getWpPosts(): array{
		return $this->l()->wp_posts->all();
	}
	/**
	 */
	protected function setFirstLastDisplayName(): void{
		$displayName = $this->displayName;
		if($displayName && is_object($displayName)){
			if(empty($this->firstName) && isset($displayName->givenName)){
				$this->setFirstName($displayName->givenName);
			}
			if(empty($this->lastName) && isset($displayName->familyName)){
				$this->setLastName($displayName->familyName);
			}
			$this->setDisplayName($this->firstName . " " . $this->lastName);
			return;
		}
		if($displayName && !isset($this->firstName) && !isset($this->lastName)){
			$displayNames = explode(' ', $this->displayName);
			if(isset($displayNames[0])){
				$this->setFirstName($displayNames[0]);
			}
			if(isset($displayNames[1])){
				$this->setLastName($displayNames[count($displayNames) - 1]);
			}
		}
	}
	protected function setBooleanProperties(): void{
		$this->combineNotifications = filter_var((string)$this->combineNotifications, FILTER_VALIDATE_BOOLEAN);
		$this->getPreviewBuilds = filter_var((string)$this->getPreviewBuilds, FILTER_VALIDATE_BOOLEAN);
		$this->hasAndroidApp = filter_var((string)$this->hasAndroidApp, FILTER_VALIDATE_BOOLEAN);
		$this->hasChromeExtension = filter_var((string)$this->hasChromeExtension, FILTER_VALIDATE_BOOLEAN);
		$this->hasIosApp = filter_var((string)$this->hasIosApp, FILTER_VALIDATE_BOOLEAN);
		$this->pushNotificationsEnabled = filter_var((string)$this->pushNotificationsEnabled, FILTER_VALIDATE_BOOLEAN);
		$this->sendPredictorEmails = filter_var((string)$this->sendPredictorEmails, FILTER_VALIDATE_BOOLEAN);
		$this->sendReminderNotificationEmails =
			filter_var((string)$this->sendReminderNotificationEmails, FILTER_VALIDATE_BOOLEAN);
		$this->smsNotificationsEnabled = filter_var((string)$this->smsNotificationsEnabled, FILTER_VALIDATE_BOOLEAN);
		$this->stripeActive = filter_var((string)$this->stripeActive, FILTER_VALIDATE_BOOLEAN);
		$this->trackLocation = filter_var((string)$this->trackLocation, FILTER_VALIDATE_BOOLEAN);
	}
	protected function setAdministrator(): void{
		if($this->administrator !== null){
			return;
		}
		if($this->laravelModel){
			$this->administrator = $this->l()->isAdmin();
		} elseif($this->roles){
			$this->administrator = $this->hasRole(BaseRolesProperty::ROLE_ADMINISTRATOR);
		}
	}
	/**
	 * @return GithubConnector
	 */
	public function github(): GithubConnector{
		return $this->getConnectorByName(GithubConnector::NAME);
	}
	/**
	 * @param $variableIdOrName
	 * @return QMUserVariable|null
	 */
	public function findQMUserVariable($variableIdOrName): ?QMUserVariable{
		return QMUserVariable::findUserVariableByNameIdOrSynonym($this->getId(), $variableIdOrName);
	}
	/**
	 * @return QMUserVariable
	 */
	private function getPrimaryOutcomeFromReminders(): ?QMUserVariable{
		$reminders = $this->getTrackingReminders();
		if(!$reminders->count()){
			return null;
		}
		$reminders = $reminders->filter(function(TrackingReminder $reminder){
			return $reminder->isRating();
		});
		if(!$reminders->count()){
			return null;
		}
		$reminders = $reminders->sortByDesc(function(TrackingReminder $reminder){
			return $reminder->getUserVariable()->number_of_measurements;
		});
		$most = $reminders->first()->getQMUserVariable();
		return $most;
	}
	/**
	 * @return QMUserVariable
	 */
	private function getPrimaryOutcomeFromVariablesTable(): ?QMUserVariable{
		$qb = $this->outcomeUserVariableQb();
		$qb->whereIn(Variable::TABLE . '.' . Variable::FIELD_VARIABLE_CATEGORY_ID, [
			QMVariableCategory::getSymptoms()->getId(),   // We want to give preference to symptoms and emotions over
			QMVariableCategory::getEmotions()->getId()    // Rescuetime and Fitbit generated data
		])->whereIn(Variable::TABLE . '.' . Variable::FIELD_DEFAULT_UNIT_ID, [
			QMUnit::getPercent()->getId(),
			QMUnit::getOneToFiveRating()->getId(),
		]);
		/** @var QMUserVariable $row */
		$row = $qb->first();
		if(!$row || $row->numberOfMeasurements < 30){  // Fallback to allow Fitbit and Rescuetime outcomes
			$qb = $this->outcomeUserVariableQb();
			$qb->whereIn(Variable::TABLE . '.' . Variable::FIELD_DEFAULT_UNIT_ID, [
				QMUnit::getPercent()->getId(),
				QMUnit::getOneToFiveRating()->getId(),
			]);
			$row = $qb->first();
		}
		if(!$row){
			return null;
		}
		if($row->userId !== $this->getId()){
			le("No user id!");
		}
		$v = QMUserVariable::findUserVariableByNameIdOrSynonym($row->userId, $row->variableId);
		return $v;
	}
	/**
	 * @return QMQB
	 */
	private function outcomeUserVariableQb(): QMQB{
		$qb =
			GetUserVariableRequest::qb()->where(UserVariable::TABLE . '.' . UserVariable::FIELD_USER_ID, '=', $this->id)
				->where(Variable::TABLE . '.' . Variable::FIELD_OUTCOME, '=', 1)->orderBy(UserVariable::TABLE . '.' .
					UserVariable::FIELD_NUMBER_OF_MEASUREMENTS, 'desc');
		return $qb;
	}
	/**
	 * @return int
	 */
	public function getNumberOfTrackingReminders(): int{
		$number = $this->numberOfTrackingReminders;
		return $this->numberOfTrackingReminders = $number;
	}
	public function outputDebugStatsAndUrlWithToken(){
		$this->logInfo($this->email);
		QMLog::infoWithoutObfuscation(ImportStateButton::url(['access_token' => $this->getOrSetAccessTokenString()]));
		$this->calculateAndLogInterestingRelationCounts();
		$reminders = QMTrackingReminder::getTrackingReminders($this);
		QMLog::infoWithoutObfuscation(count($reminders) . " reminders");
		$totalNotifications = $this->notificationsQb()->count();
		QMLog::infoWithoutObfuscation($totalNotifications . " total Notifications");
		$notifications = QMTrackingReminderNotification::getPastTrackingReminderNotifications(['userId' => $this->id]);
		QMLog::infoWithoutObfuscation(count($notifications) . " past notifications");
		$deletedNotifications =
			$this->notificationsQb()->whereNotNull(QMTrackingReminderNotification::FIELD_DELETED_AT)->count();
		QMLog::infoWithoutObfuscation($deletedNotifications . " deleted Notifications");
		$notifiedNotifications =
			$this->notificationsQb()->whereNotNull(QMTrackingReminderNotification::FIELD_NOTIFIED_AT)->count();
		QMLog::infoWithoutObfuscation($notifiedNotifications . " notified Notifications");
		$deviceTokens = QMDeviceToken::getAllForUser($this->id);
		QMLog::infoWithoutObfuscation(count($deviceTokens) . " deviceTokens");
		foreach($deviceTokens as $deviceToken){
			QMLog::infoWithoutObfuscation("========== $deviceToken->platform token ==========");
			QMLog::infoWithoutObfuscation("Device token received: " . $deviceToken->receivedAt);
			QMLog::infoWithoutObfuscation("Device token notified: " . $deviceToken->getLastNotifiedAt());
			QMLog::infoWithoutObfuscation("Device token error:" . $deviceToken->errorMessage);
		}
	}
	/**
	 * @return Builder
	 */
	private function notificationsQb(): Builder{
		return QMTrackingReminderNotification::readonly()
			->where(QMTrackingReminderNotification::FIELD_USER_ID, $this->id);
	}
	/**
	 * @return string
	 */
	public function getIpAddress(): ?string{
		// Don't use this because the wrong one seems to get set somehow
		$ip = $this->getUserMetaValue(UserMeta::KEY_ip_address);
		if($ip){
			return $ip;
		}
		$ip = IPHelper::getClientIp();
		if($ip){
			$this->setUserMetaValue(UserMeta::KEY_ip_address, $ip);
		}
		return $ip;
	}
	/**
	 * @param string|null $ip
	 * @return IpDatum|null
	 * @throws NoGeoDataException
	 */
	public function getIpGeoLocation(string $ip = null): ?IpDatum{
		$ipData = $this->getUserMetaValue(UserMeta::KEY_geo_location);
		if($ipData){
			$val = QMStr::jsonDecodeIfNecessary($ipData);
			if($val instanceof IpDatum){return $val;}
			return new IpDatum((array)$val);
		}
		if($ip = $ip ?? $this->getIpAddress()){
			$ipData = GeoLocation::ipData($ip);
			$this->setUserMetaValue(UserMeta::KEY_geo_location, $ipData, true);
		}
		return $ipData;
	}
	public function rootCauseAnalysisForAllOutcomes(){
		$variables = $this->getOutcomeVariablesWithData();
		$this->logInfoWithoutObfuscation("Got " . count($variables) . " outcome variables...");
		foreach($variables as $v){
			$a = $v->getRootCauseAnalysis();
			$a->getDownloadOrCreateFile(AnalyticalReport::FILE_TYPE_PDF);
		}
	}
	/**
	 * @return AppSettings
	 * @throws ClientNotFoundException
	 * @throws InvalidClientIdException
	 */
	public function getAppSettingsForClientThatCreated(): AppSettings{
		$clientId = $this->getClientId();
		if(!$clientId){
			throw new InvalidClientIdException($clientId, "User has no client id from creation! ");
		}
		$app = Application::getByClientId($clientId);
		return $app;
	}
	/**
	 * @param string $subject
	 * @param string $htmlContent
	 * @param bool $ccPhysicians
	 * @return QMSendgrid
	 */
	public function sendEmail(string $subject, string $htmlContent, bool $ccPhysicians = false): QMSendgrid{
		$sentTo = [];
		$email = new QMSendgrid($this->getId(), $subject, $htmlContent, $this);
		try {
			$address = $this->getEmail();
			$email->setRecipientEmailAddress($address);
			$email->send();
		} catch (InvalidEmailException | NoEmailAddressException | TooManyEmailsException $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());
		} catch (TypeException $e) {
			le($e);
		}
		if($ccPhysicians){
			if($this->isTestUser() || $this->isDemoUser()){
				return $email;
			}
			$physicians = $this->getPhysicians();
			foreach($physicians as $p){
				try {
					$address = $p->getEmail();
					if(in_array($address, $sentTo)){
						$this->logInfo("Skipping duplicate $address because already sent");
						continue;
					} // No duplicates
					$sentTo[] = $address;
					$email->sendToThirdParty($p, $this);
				} catch (InvalidEmailException | NoEmailAddressException | TooManyEmailsException $e) {
					$this->logError(__METHOD__.": ".$e->getMessage());
				} catch (Throwable $e) {
					try {
						$email->sendToThirdParty($p, $this);
					} catch (InvalidEmailException | NoEmailAddressException | TooManyEmailsException | TypeException $e) {
						le($e);
					}
					/** @var LogicException $e */
					throw $e;
				}
			}
		}
		return $email;
	}
	/**
	 * @return User
	 */
	public function l(): User{
		return parent::l();
	}
	/**
	 * @inheritDoc
	 */
	public function analyzeFully(string $reason){
		$this->importData();
		$this->analyzePartially($reason);
		$this->analyzeUserVariables();
		$this->analyzePartially($reason); // Need to re-analyze because this data comes from user_variables table
		$v = $this->getPrimaryOutcomeQMUserVariable();
		$v->correlateIfNecessary();
		$variables = $this->getUserVariables();
		foreach($variables as $v){
			$dbm = $v->getQMUserVariable();
			$dbm->correlateIfNecessary();
		}
		// What is the point of this? It's inefficient $this->laravelModel = null; // Unset because we have stale wp_posts
		$this->setAlreadyAnalyzed(true);
	}
	/**
	 * @inheritDoc
	 * @throws AlreadyAnalyzedException
	 */
	public function analyzePartially(string $reason){
		$this->beforeAnalysis($reason);
		$this->calculateAndLogInterestingRelationCounts();
	}
	public function firstOrNewLaravelModel(): BaseModel{
		$l = parent::firstOrNewLaravelModel();
		return $l;
	}
	/**
	 * @inheritDoc
	 */
	public function getNewestDataAt(): ?string{
		$at = $this->newestDataAt;
		if($at === false){
			return null;
		}
		if($at !== null){
			return $at;
		}
		$this->newestDataAt = $at = Measurement::whereUserId($this->getId())->max(Measurement::UPDATED_AT);
		if($at === null){
			$this->newestDataAt = false;
			return null;
		}
		return $at;
	}
	/**
	 * @inheritDoc
	 */
	public function generatePostContent(): string{
		$l = $this->l();
		$content = $l->generatePostContent();
		$content .= $this->getDataQuantityListRoundedButtonsHTML();
		return $content;
	}
	/**
	 * @inheritDoc
	 */
	public function getCategoryName(): string{
		return $this->l()->getCategoryName();
	}
	/**
	 * @inheritDoc
	 */
	public function getParentCategoryName(): ?string{
		return $this->l()->getParentCategoryName();
	}
	/**
	 * @return string
	 */
	public function getCategoryDescription(): string{
		return $this->l()->getCategoryDescription();
	}
	/**
	 * @return string
	 */
	public function getSubtitleAttribute(): string{
		return $this->l()->getSubtitleAttribute();
	}
	/**
	 * @return string
	 */
	public function getReportTitleAttribute(): string{
		return $this->l()->getTitleAttribute();
	}
	/**
	 * @param string $previewText
	 * @param int $limit
	 * @return string
	 * Really slow!  Use getWpPostPreviewTableHtml instead
	 */
	public function getWpPostPreviewTableHtml(string $previewText, int $limit = 10): string{
		$l = $this->l();
		return $l->getWpPostPreviewTableHtml($previewText, $limit);
	}
	/**
	 * @return QMUser
	 */
	public static function ivy(): QMUser{
		return self::find(UserIdProperty::USER_ID_IVY);
	}
	/**
	 * @return array
	 */
	public static function getSqlCalculatedFields(): array{
		return self::$sqlCalculatedFields = [
			self::FIELD_NUMBER_OF_CORRELATIONS => [
				'table' => UserVariableRelationship::TABLE,
				'foreign_key' => UserVariableRelationship::FIELD_USER_ID,
				'sql' => 'count(' . UserVariableRelationship::FIELD_ID . ')',
			],
			self::FIELD_NUMBER_OF_CONNECTIONS => [
				'table' => Connection::TABLE,
				'foreign_key' => Connection::FIELD_USER_ID,
				'sql' => 'count(' . Connection::FIELD_ID . ')',
			],
			self::FIELD_NUMBER_OF_TRACKING_REMINDERS => [
				'table' => TrackingReminder::TABLE,
				'foreign_key' => TrackingReminder::FIELD_USER_ID,
				'sql' => 'count(' . TrackingReminder::FIELD_ID . ')',
			],
			self::FIELD_NUMBER_OF_USER_VARIABLES => [
				'table' => UserVariable::TABLE,
				'foreign_key' => UserVariable::FIELD_USER_ID,
				'sql' => 'count(' . UserVariable::FIELD_ID . ')',
			],
			self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS => [
				'table' => UserVariable::TABLE,
				'foreign_key' => UserVariable::FIELD_USER_ID,
				'sql' => 'sum(' . UserVariable::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN . ')',
			],
			self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION => '',
			self::FIELD_NUMBER_OF_VOTES => [
				'table' => Vote::TABLE,
				'foreign_key' => Vote::FIELD_USER_ID,
				'sql' => 'count(' . Vote::FIELD_ID . ')',
			],
			self::FIELD_NUMBER_OF_STUDIES => [
				'table' => Study::TABLE,
				'foreign_key' => Study::FIELD_USER_ID,
				'sql' => 'count(' . Study::FIELD_ID . ')',
			],
			self::FIELD_LAST_CORRELATION_AT => 'datetime',
			self::FIELD_LAST_EMAIL_AT => 'datetime',
			self::FIELD_LAST_PUSH_AT => 'datetime',
			self::FIELD_PRIMARY_OUTCOME_VARIABLE_ID => '',
			self::FIELD_WP_POST_ID => '',
			self::FIELD_NEWEST_DATA_AT => [
				'table' => Measurement::TABLE,
				'foreign_key' => Measurement::FIELD_USER_VARIABLE_ID,
				'sql' => 'max(' . Measurement::UPDATED_AT . ')',
			],
			self::FIELD_ADDRESS => '',
			self::FIELD_AVATAR_IMAGE => '',
			self::FIELD_BIRTHDAY => '',
			self::FIELD_CARD_BRAND => '',
			self::FIELD_CARD_LAST_FOUR => '4',
			self::FIELD_CLIENT_ID => '',
			self::FIELD_COMBINE_NOTIFICATIONS => '',
			self::FIELD_COUNTRY => '',
			self::FIELD_COVER_PHOTO => '',
			self::FIELD_CURRENCY => '',
			self::FIELD_DELETED => '',
			self::FIELD_DISPLAY_NAME => '',
			self::FIELD_EARLIEST_REMINDER_TIME => '',
			self::FIELD_FIRST_NAME => '',
			self::FIELD_GENDER => '',
			self::FIELD_GET_PREVIEW_BUILDS => '',
			self::FIELD_HAS_ANDROID_APP => '',
			self::FIELD_HAS_CHROME_EXTENSION => '',
			self::FIELD_HAS_IOS_APP => '',
			self::FIELD_ID => '',
			self::FIELD_LANGUAGE => '',
			self::FIELD_LAST_FOUR => '',
			self::FIELD_LAST_LOGIN_AT => 'datetime',
			self::FIELD_LAST_NAME => '',
			self::FIELD_LAST_SMS_TRACKING_REMINDER_NOTIFICATION_ID => '',
			self::FIELD_LATEST_REMINDER_TIME => '',
			self::FIELD_OLD_USER => '',
			self::FIELD_PHONE_NUMBER => '',
			self::FIELD_PHONE_VERIFICATION_CODE => '',
			self::FIELD_PROVIDER_ID => '',
			self::FIELD_PROVIDER_TOKEN => '',
			self::FIELD_PUSH_NOTIFICATIONS_ENABLED => '',
			self::FIELD_REFERRER_USER_ID => '',
			self::FIELD_REFRESH_TOKEN => '',
			self::FIELD_REG_PROVIDER => '',
			self::FIELD_REMEMBER_TOKEN => '',
			self::FIELD_ROLES => '',
			self::FIELD_SEND_PREDICTOR_EMAILS => '',
			self::FIELD_SEND_REMINDER_NOTIFICATION_EMAILS => '',
			self::FIELD_SMS_NOTIFICATIONS_ENABLED => '',
			self::FIELD_SPAM => '',
			self::FIELD_STATE => '',
			self::FIELD_STRIPE_ACTIVE => '',
			self::FIELD_STRIPE_ID => '',
			self::FIELD_STRIPE_PLAN => '',
			self::FIELD_STRIPE_SUBSCRIPTION => '',
			self::FIELD_SUBSCRIPTION_ENDS_AT => 'datetime',
			self::FIELD_SUBSCRIPTION_PROVIDER => '',
			self::FIELD_TAG_LINE => '',
			self::FIELD_TIME_ZONE_OFFSET => '',
			self::FIELD_TIMEZONE => '',
			self::FIELD_TRACK_LOCATION => '',
			self::FIELD_TRIAL_ENDS_AT => 'datetime',
			self::FIELD_UNSUBSCRIBED => '',
			self::FIELD_USER_ACTIVATION_KEY => '',
			self::FIELD_USER_EMAIL => '',
			self::FIELD_USER_LOGIN => '',
			self::FIELD_USER_NICENAME => '',
			self::FIELD_USER_PASS => '',
			self::FIELD_USER_REGISTERED => '',
			self::FIELD_USER_STATUS => '',
			self::FIELD_USER_URL => '',
			self::FIELD_VERIFIED => '',
			self::FIELD_ZIP_CODE => '',
		];
	}
	/**
	 * Don't delete this because PostableTrait overrides PublicUser getUserId function
	 * @return int
	 */
	public function getUserId(): ?int{
		return $this->getId();
	}
	public static function getDefaultPrincipalInvestigator(): PublicUser{
		return self::find(QMStudy::DEFAULT_PRINCIPAL_INVESTIGATOR_ID)->getPublicUser();
	}
	public function cleanup(){
		throw new LogicException(__FUNCTION__ . " not implemented for " . static::class);
	}
	/**
	 * @return string
	 */
	public function getLatestReminderTime(): string{
		return $this->latestReminderTime;
	}
	/**
	 * @return string
	 */
	public function getEarliestReminderTime(): string{
		return $this->earliestReminderTime;
	}
	private function getCoverPhoto(): ?string{
		$cp = $this->coverPhoto;
		if(is_object($cp)){
			return $this->coverPhoto = $cp->url;
		}
		return $cp;
	}
	/**
	 * @param string $name
	 * @return QMDataSource|GithubConnector
	 */
	public function getConnectorByName(string $name): QMDataSource{
		return QMDataSource::getByNameAndUserId($name, $this->getId());
	}
	public function validateId(){
		if($this->id < 0){
			le('$this->id < 0');
		}
	}
	public function getInvalidSourceData(): array{
		if($this->invalidSourceData){
			return $this->invalidSourceData;
		}
		$invalid = [];
		$correlations = $this->getCorrelations();
		foreach($correlations as $correlation){
			try {
				$correlation->validate();
			} catch (ModelValidationException $e) {
				$invalid[] = $correlation;
			}
		}
		return $this->invalidSourceData = $invalid;
	}
	public function getVariableSettingsLink(): string{
		return UserVariable::getDataLabIndexLink([
			UserVariable::FIELD_USER_ID => $this->getId(),
		]);
	}
	public function getLogMetaDataString(): string{
		return $this->getDisplayNameAttribute();
	}
	/**
	 * @param int|string $variableIdOrName
	 * @return UserVariable
	 */
	public function findUserVariable($variableIdOrName): ?UserVariable{
		return UserVariable::findByVariableIdOrName($variableIdOrName, $this->getUserId());
	}
	/**
	 * @return self
	 */
	public function getQMUser(): self{ return $this; }
	public function getSourceObjects(): array{
		return $this->l()->getSourceObjects();
	}
	public function getUrl(array $params = []): string{
		return $this->l()->getUrl($params);
	}
	public function importData(){
		$connections = $this->getConnections();
		foreach($connections as $connection){
			try {
				$connection->import();
			} catch (ConnectorDisabledException $e) {
				QMLog::info("Connector disabled: " . $connection->getTitleAttribute());
			} catch (NoGeoDataException $e) {
				QMLog::info("No geo data: " . $connection->getTitleAttribute());
			}
		}
	}
	public function isSystem(){
		return $this->getId() === UserIdProperty::USER_ID_SYSTEM;
	}
	/**
	 * @param int|string $key
	 * @return bool
	 */
	public static function isNotificationSetting(string $key): bool{
		return stripos($key, 'notification') !== false || 
		       stripos($key, 'reminder') !== false ||
		       stripos($key, 'emails') !== false; // don't just use email
	}
}
