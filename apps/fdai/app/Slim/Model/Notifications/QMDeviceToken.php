<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Notifications;
use App\AppSettings\AppSettings;
use App\Computers\ThisComputer;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\DuplicateNotificationException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\InvalidClientIdException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoTestDeviceTokenException;
use App\Logging\QMLog;
use App\Models\Application;
use App\Models\BaseModel;
use App\Models\DeviceToken;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BasePlatformProperty;
use App\Properties\User\UserIdProperty;
use App\Slim\Model\QMUserRelatedModel;
use App\Slim\Model\User\QMUser;
use App\Storage\DB\QMDB;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\DB\Writable;
use App\Storage\Firebase\FirebaseGlobalPermanent;
use App\Storage\Memory;
use App\Traits\HasModel\HasUser;
use App\Traits\TestableTrait;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\Utils\AppMode;
use Carbon\Carbon;
use Exception;
use InvalidArgumentException;
use LogicException;
class QMDeviceToken extends QMUserRelatedModel {
	use TestableTrait, HasUser;
	//private $user;  // Just get global user instead, otherwise there will be duplicates and we can use the user as a storage object
	public const ERROR_RESPONSE_INVALID_REGISTRATION                     = "InvalidRegistration";
	public const ERROR_RESPONSE_NOT_REGISTERED                           = "NotRegistered";
	public const FIELD_CLIENT_ID                                         = 'client_id';
	public const FIELD_CREATED_AT                                        = 'created_at';
	public const FIELD_DELETED_AT                                        = 'deleted_at';
	public const FIELD_DEVICE_TOKEN                                      = 'device_token';
	public const FIELD_ERROR_MESSAGE                                     = 'error_message';
	public const FIELD_ID                                                = 'device_token';
	public const FIELD_LAST_CHECKED_AT                                   = 'last_checked_at';
	public const FIELD_LAST_NOTIFIED_AT                                  = 'last_notified_at';
	public const FIELD_NUMBER_OF_NEW_TRACKING_REMINDER_NOTIFICATIONS     = 'number_of_new_tracking_reminder_notifications';
	public const FIELD_NUMBER_OF_NOTIFICATIONS_LAST_SENT                 = 'number_of_notifications_last_sent';
	public const FIELD_NUMBER_OF_WAITING_TRACKING_REMINDER_NOTIFICATIONS = 'number_of_waiting_tracking_reminder_notifications';
	public const FIELD_PLATFORM                                          = 'platform';
	public const FIELD_RECEIVED_AT                                       = 'received_at';
	public const FIELD_SERVER_HOSTNAME                                   = 'server_hostname';
	public const FIELD_SERVER_IP                                         = 'server_ip';
	public const FIELD_UPDATED_AT                                        = 'updated_at';
	public const FIELD_USER_ID                                           = 'user_id';
	public const MINUTES_BETWEEN_CHECKS_FOR_NEW_NOTIFICATIONS            = 15;
	public const TABLE                                                   = 'device_tokens';
	private $forceStartOnPush;
	protected $lastPushNotification;
	protected $primaryKey = 'device_token';
    protected $keyType = 'string';
	protected $table = 'device_tokens';
	public $appIdentifier;
	public $clientId;
	public $combineNotifications;
	public $createdAt;
	public $deviceToken;
	public $earliestNotificationTimeCutoff;
	public $errorMessage;
	public $iconUrl;
	public $incrementing = false;
	public $lastCheckedAt;
	public $lastNotifiedAt;
	public $numberOfNotificationsLastSent;
	public $numberOfNewTrackingReminderNotifications;
	public $numberOfWaitingTrackingReminderNotifications;
	public $platform;
	public $receivedAt;
	public $serverHostname; // Do not remove
	public $serverIp; // Do not remove
	public $updatedAt;
	public $userId;
	protected static $requiredFields = [
		self::FIELD_CLIENT_ID,
		self::FIELD_DEVICE_TOKEN,
		self::FIELD_PLATFORM,
		self::FIELD_USER_ID,
	];
	private $alreadyNotified;
	/**
	 * @var bool
	 */
	private $alreadySaved;
	/**
	 * DeviceToken constructor.
	 * @param null $deviceTokenRowOrString
	 */
	public function __construct($deviceTokenRowOrString = null){
		//parent::__construct($attributes);
		if($deviceTokenRowOrString){
			if(is_string($deviceTokenRowOrString)){
				$this->deviceToken = $deviceTokenRowOrString;
			} else{
				unset($deviceTokenRowOrString->ID); // From user
				$this->populateFieldsByArrayOrObject($deviceTokenRowOrString);
				$this->validateDeviceTokenRow($deviceTokenRowOrString);
			}
		}
		parent::__construct();
		$this->getCreatedAt();
		$this->getUpdatedAt();
	}
	/**
	 * @param bool $instantiate
	 * @return QMQB
	 */
	public static function qb(bool $instantiate = false): QMQB{
		$qb = ReadonlyDB::getBuilderByTable('device_tokens')
		                ->select('count(*) as user_count, status')
		                ->select([
			'oa_clients.icon_url',
			'device_tokens.device_token',
			'device_tokens.created_at',
			'device_tokens.updated_at',
			'device_tokens.received_at',
			Writable::db()->raw('( SELECT COUNT(*)
                        FROM tracking_reminder_notifications
                        WHERE device_tokens.user_id = tracking_reminder_notifications.user_id AND
                        tracking_reminder_notifications.deleted_at IS NULL AND
                        tracking_reminder_notifications.notify_at < NOW() AND
                        tracking_reminder_notifications.notified_at IS NULL
                      ) AS number_of_waiting_tracking_reminder_notifications'),
			'device_tokens.user_id',
			'device_tokens.platform',
			'device_tokens.error_message',
			'wp_users.combine_notifications',
			Writable::db()->raw('( SELECT COUNT(*)
                        FROM tracking_reminder_notifications tracking_reminder_notifications
                        WHERE device_tokens.user_id = tracking_reminder_notifications.user_id AND
                        tracking_reminder_notifications.deleted_at IS NULL AND
                        tracking_reminder_notifications.notify_at <= NOW() AND
                        tracking_reminder_notifications.notified_at IS NULL AND
                        tracking_reminder_notifications.notify_at >= NOW() - INTERVAL ' .
				PushNotification::MINUTES_BETWEEN_CHECKS_FOR_NEW_NOTIFICATIONS . ' MINUTE
                      ) AS number_of_new_tracking_reminder_notifications'),
			'device_tokens.last_checked_at',
			'device_tokens.last_notified_at',
			'device_tokens.received_at',
			'oa_clients.app_identifier',
			'oa_clients.client_id',
			Writable::db()->raw('NOW() - INTERVAL ' . PushNotification::MINUTES_BETWEEN_CHECKS_FOR_NEW_NOTIFICATIONS .
				' MINUTE as earliest_notification_time_cutoff'),
			Writable::db()->raw('NOW() as last_checked_at'),
		])->join('wp_users', 'device_tokens.user_id', '=', 'wp_users.ID')
			->leftJoin('oa_clients', 'device_tokens.client_id', '=', 'oa_clients.client_id');
		if(\App\Utils\Env::get('DEBUG_DEVICE_TOKEN')){
			$qb->where('device_tokens.device_token', \App\Utils\Env::get('DEBUG_DEVICE_TOKEN'));
		}
		if($instantiate){
			$qb->class = self::class;
		}
		return $qb;
	}
	/**
	 * @param string $errorString
	 */
	private static function fixTokensWithErrorContaining(string $errorString){
		$tokensWithInternalServerErrorQb = static::writable()->whereLike('error_message', '%' .
            $errorString . '%');
		$numberOfTokens = $tokensWithInternalServerErrorQb->count();
		if($numberOfTokens){
			QMLog::info($numberOfTokens .
				" tokens with error message containing $errorString.  Setting error message to null for these.");
			$tokensWithInternalServerErrorQb->update([self::FIELD_ERROR_MESSAGE => null]);
			QMLog::info($tokensWithInternalServerErrorQb->count() .
				" tokens with  error message containing $errorString.");
		}
	}
	public static function fixInternalErrorAndNotAuthorizedTokens(){
		self::fixTokensWithErrorContaining("Internal");
		self::fixTokensWithErrorContaining("not authorized");
	}
	public static function deleteErroredTokensCreatedMoreThanAMonthAgo(){
		$total = self::readonly()->count();
		$qb = self::writable()->whereNotNull(self::FIELD_ERROR_MESSAGE)
			->whereRaw('created_at < NOW() - INTERVAL 1 MONTH');
		$rows = $qb->getArray();
		if(!$rows){
			return;
		}
		QMLog::info("Deleting " . count($rows) . " errored device tokens over a month old. $total tokens exist");
		//        throw new \LogicException("Got ".count($rows)." ErroredTokensCreatedMoreThanAMonthAgo. ".
		//            "Not deleting because I'm trying to figure out why tokens are disappearing from staging DB");
		foreach($rows as $row){
			$t = new QMDeviceToken($row);
			$t->hardDelete($t->getErrorMessage() . " " . $t->getLogMetaDataString());
		}
	}
	/**
	 * Can only use force start on first notification on Android or it brings the app to the foreground
	 * @return int
	 */
	public function getForceStartForPushNotification(): int{
		if($this->forceStartOnPush === 0){
			return 0;
		}
		$this->forceStartOnPush = 0;
		return 1;
	}

    /**
     * @param PushNotificationData $pushData
     * @return bool|PushNotification
     * @throws MissingApplePushCertException
     */
	public function send(PushNotificationData $pushData): PushNotification{
		$this->alreadyNotified = true;
		$pushData->setQMDeviceToken($this);
		$uniqueId = $pushData->getTokenTitleUniqueId();
		$unique = Memory::get($uniqueId, Memory::SENT_PUSH_NOTIFICATIONS);
		if($unique){
			throw new DuplicateNotificationException("Already sent $uniqueId");
		}
		Memory::set($uniqueId, $pushData, Memory::SENT_PUSH_NOTIFICATIONS);
		if($this->platform === BasePlatformProperty::PLATFORM_ANDROID){
			$push = new GooglePushNotification($this, $pushData);
		} elseif($this->platform === BasePlatformProperty::PLATFORM_IOS){
            if(\App\Utils\Env::get('IOS_PUSH_CERT_PATH')){
                $push = new ApplePushNotification($this, $pushData);
            } else {
                throw new MissingApplePushCertException("No IOS_PUSH_CERT_PATH set.  Not sending push notification.");
            }
		} elseif($this->platform === BasePlatformProperty::PLATFORM_WEB ||
			$this->platform === BasePlatformProperty::PLATFORM_CHROME){
			$push = new GooglePushNotification($this, $pushData);
		} else{
			throw new InvalidArgumentException("Unrecognized push platform!");
		}
		$this->lastPushNotification = $push;
		if(AppMode::isTestingOrStaging()){
			$this->saveTestTokenToFirebase();
		}
		if($push->getResponse()->success){
			$this->setLastNotifiedAndLastCheckedAt();
			$push->logInfo("Success!");
		} else{
			$push->logError("ERROR: " . $push->getResponse()->error);
		}
		if(!$this->getClientId()){
			throw new InvalidArgumentException("No client id on this device token: " . json_encode($this));
		}
		$this->saveIfNecessary();
		return $push;
	}
	/**
	 * @return BaseModel|DeviceToken
	 */
	public function firstOrNewLaravelModel(){
		/** @var DeviceToken $res */
		$res = parent::firstOrNewLaravelModel();
		return $res;
	}
	/**
	 * @return string
	 */
	public function getId(): string{
		return $this->deviceToken;
	}
	/**
	 * @return string
	 */
	public function getErrorMessage(): string{
		return $this->errorMessage;
	}
	/**
	 * @param string $errorMessage
	 * @return string
	 */
	public function setErrorMessage(string $errorMessage = null): ?string{
		if(!$errorMessage){
			return $this->errorMessage = $errorMessage;
		}
		$allowedErrorMessages = [
			self::ERROR_RESPONSE_NOT_REGISTERED,
			self::ERROR_RESPONSE_INVALID_REGISTRATION,
		];
		if(!in_array($errorMessage, $allowedErrorMessages, true)){
			QMLog::error("Push error message $errorMessage not recognized!", ['allowed' => $allowedErrorMessages]);
		}
		$this->errorMessage = $errorMessage;
		QMLog::error($errorMessage, ['pushNotification' => $this]);
		if($errorMessage == 1 || $errorMessage == "1"){
			QMLog::error("Push error message is $errorMessage");
		} else{
			$this->updateRecord([
				'error_message' => $errorMessage,
				self::FIELD_SERVER_HOSTNAME => gethostname(),
				self::FIELD_SERVER_IP => ThisComputer::getCurrentServerExternalIp(),
			]);
		}
		return $this->errorMessage;
	}
	/**
	 * @return string
	 */
	public function getDeviceTokenString(): string{
		return $this->deviceToken;
	}
	/**
	 * @return string
	 */
	public function getLastNotifiedAt(): string{
		return $this->lastNotifiedAt;
	}
	/**
	 * @return void
	 */
	public function setLastNotifiedAndLastCheckedAt(): void{
		$this->setAttribute(DeviceToken::FIELD_LAST_CHECKED_AT, now_at());
		$this->setAttribute(DeviceToken::FIELD_LAST_NOTIFIED_AT, now_at());
	}
	/**
	 * @param array $updateArray
	 * @return int
	 */
	public function updateRecord(array $updateArray): int{
		$updateArray['updated_at'] = date('Y-m-d H:i:s');
		if(isset($updateArray[self::FIELD_ERROR_MESSAGE])){
			$updateArray[self::FIELD_ERROR_MESSAGE] = QMStr::truncate($updateArray[self::FIELD_ERROR_MESSAGE], 254);
		}
		return $this->updateDbRow($updateArray);
	}
	/**
	 * @param int $userId
	 * @param string|null $platform
	 * @param bool $valid
	 * @return QMDeviceToken[]
	 */
	public static function getAllForUser(int $userId, string $platform = null, bool $valid = false): array{
		$qb = DeviceToken::whereUserId($userId);
		//$qb = self::getDeviceTokensBaseQuery()->where("device_tokens." . self::FIELD_USER_ID, $userId);
		if($platform){
			$qb->where(self::FIELD_PLATFORM, $platform);
		}
		if($valid){
			$qb->whereNull(self::FIELD_ERROR_MESSAGE);
		}
		$deviceTokenRows = $qb->get();
		if(!$deviceTokenRows){
			return [];
		}
		$tokenObjects = [];
		foreach($deviceTokenRows as $l){
			$tokenObjects[] = $l->getDBModel();
		}
		return $tokenObjects;
	}
	/**
	 * @return int
	 */
	public static function getNumberUpdatedInLastDay(): int{
		return QMDB::addUpdatedInLastDayWhereClause(self::readonly())->count();
	}
	/**
	 * @return int
	 */
	public static function logNumberUpdatedInLastDay(): int{
		$numberUpdated = self::getNumberUpdatedInLastDay();
		QMLog::info($numberUpdated . " " . (new \ReflectionClass(static::class))->getShortName() .
			"s UPDATED in last 24 hours");
		return $numberUpdated;
	}
	/**
	 * @param null $platform
	 * @return int
	 */
	public static function getNumberNotifiedInLastDay($platform = null): int{
		$qb = self::readonly()->where(self::FIELD_LAST_NOTIFIED_AT, ">",  Carbon::now()->subDay());
		if($platform){
			$qb->where(self::FIELD_PLATFORM, $platform);
		}
		$count = $qb->count();
		QMLog::info($count . " $platform " . (new \ReflectionClass(static::class))->getShortName() .
			"s NOTIFIED in last 24 hours");
		return $count;
	}
	/**
	 * @param int $day
	 * @param string|null $platform
	 * @return int
	 */
	public static function getNumberReceived(int $day = 1, string $platform = null): int{
		$qb = self::readonly()->whereRaw(self::FIELD_RECEIVED_AT . " > NOW() - INTERVAL $day DAY");
		if($platform){
			$qb->where(self::FIELD_PLATFORM, $platform);
		}
		$count = $qb->count();
		QMLog::info($count . " $platform " . (new \ReflectionClass(static::class))->getShortName() .
			"s RECEIVED in last $day days");
		return $count;
	}
	/**
	 * @return bool
	 */
	public function requireAcknowledgement(): bool{
		$requireAck = !$this->receivedAt || strtotime($this->receivedAt) < time() - 86400 ||
			$this->userId === UserIdProperty::getDebugUserId();
		return $requireAck;
	}
	/**
	 * @param string $platform
	 * @return string
	 */
	private static function getTestTokenKey(string $platform): string{
		return 'test_device_tokens/' . $platform;
	}
	/**
	 * @param string $token
	 * @param string $platform
	 * @return array|bool
	 */
	public static function saveTestTokenStringToFirebase(string $token, string $platform){
		if(AppMode::isTestingOrStaging()){
			return false;
		}
		$qmdt = new QMDeviceToken();
		$qmdt->platform = $platform;
		$qmdt->deviceToken = $token;
		return $qmdt->saveTestTokenToFirebase();
	}
	/**
	 * @param string $platform
	 * @param int $userId
	 * @return DeviceToken
	 * @throws NoTestDeviceTokenException
	 */
	public static function saveTestTokenToDatabase(string $platform,
		int $userId = UserIdProperty::USER_ID_DEMO): DeviceToken{
		DeviceToken::deleteAll();
		$t = new DeviceToken();
		$t->user_id = $userId;
		$t->device_token = QMDeviceToken::getTestTokenString($platform);
		$t->platform = $platform;
		$t->client_id = BaseClientIdProperty::CLIENT_ID_QUANTIMODO;
		try {
			$t->saveOrFail();
		} catch (\Throwable $e) {
			/** @var LogicException $e */
			throw $e;
		}
		return $t;
	}
	/**
	 * @param string $platform
	 * @return array|bool
	 * @throws NoTestDeviceTokenException
	 */
	public static function getTestTokenString(string $platform): string{
		$key = self::getTestTokenKey($platform);
		$tokenStringFromFB = FirebaseGlobalPermanent::get($key, false);
		if(!is_string($tokenStringFromFB)){
			/** @var QMDeviceToken $tokenStringFromFB */
			$tokenStringFromFB = $tokenStringFromFB->deviceToken;
		}
		if(!$tokenStringFromFB){
			throw new NoTestDeviceTokenException($key);
		}
		return $tokenStringFromFB;
	}
    public function getDisplayNameAttribute(): string {
        return "Device Token for " . $this->getUser()->getTitleAttribute()." ending in ".
            substr($this->deviceToken, -4);
    }
    public function getNameAttribute(): string {
        return "Device Token for " . $this->getUser()->getTitleAttribute()." ending in ".
            substr($this->deviceToken, -4);
    }
	/**
	 * @return QMDeviceToken[]
	 */
	public function deleteTestToken(){
		$result = false;
		$key = self::getTestTokenKey($this->getPlatform());
		/** @var QMDeviceToken $from */
		$from = FirebaseGlobalPermanent::get($key);
		if($from && $from->deviceToken === $this->deviceToken){
			$result = FirebaseGlobalPermanent::delete($key);
		}
		return $result;
	}
	/**
	 * @return AppSettings
	 * @throws InvalidClientIdException
	 */
	public function getClientAppSettings(): AppSettings{
		try {
			return Application::getClientAppSettings($this->clientId);
		} catch (Exception $e) {
			$this->logError("Could not get app settings for $this->clientId so using quantimodo");
			ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
			try {
				return Application::getClientAppSettings(BaseClientIdProperty::CLIENT_ID_QUANTIMODO);
			} catch (ClientNotFoundException $e) {
				le($e);
				throw new \LogicException();
			}
		}
	}
	/**
	 * @return string
	 */
	public function getLogMetaDataString(): string{
		return $this->platform . " token for " . $this->getQMUser()->getLoginNameAndIdString() . " created " .
			TimeHelper::timeSinceHumanString($this->createdAt) . ": ";
	}
	/**
	 * @return bool
	 */
	public function getAlreadyNotified(): ?bool{
		return $this->alreadyNotified;
	}
	/**
	 * @param array $meta
	 * @return array
	 */
	public function getLogMetaData(?array $meta = []): array{
		$meta['device_token'] = $this->deviceToken;
		return $meta;
	}
	/**
	 * @return array
	 */
	public static function getAllowedPlatforms(): array{
		return [
			BasePlatformProperty::PLATFORM_ANDROID,
			BasePlatformProperty::PLATFORM_blink,
			BasePlatformProperty::PLATFORM_CHROME,
			BasePlatformProperty::PLATFORM_edge,
			BasePlatformProperty::PLATFORM_firefox,
			BasePlatformProperty::PLATFORM_ie,
			BasePlatformProperty::PLATFORM_IOS,
			BasePlatformProperty::PLATFORM_opera,
			BasePlatformProperty::PLATFORM_safari,
			BasePlatformProperty::PLATFORM_WEB,
		];
	}
	/**
	 * @param $string
	 * @return bool
	 */
	public static function inAllowedPlatforms($string): bool{
		return in_array($string, self::getAllowedPlatforms(), true);
	}
	/**
	 * @return array|bool
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	private function saveTestTokenToFirebase(){
		if(AppMode::isTestingOrStaging()){
			return false;
		}
		return FirebaseGlobalPermanent::set(self::getTestTokenKey($this->getPlatform()), $this);
	}
	/**
	 * @return string
	 */
	public function getPlatform(): string{
		if($this->platform === BasePlatformProperty::PLATFORM_CHROME){
			return BasePlatformProperty::PLATFORM_WEB;
		}
		return $this->platform;
	}
	/**
	 * @param $row
	 */
	private function validateDeviceTokenRow($row){
		if(!empty($this->bshafferOauthClientsId)){
			$this->clientId = $this->bshafferOauthClientsId;
		} // Don't use one from user
		if($this->deviceToken === 'null'){
			QMLog::error('Device token is null!  Deleting it!', ['device token' => $row]);
			self::writable()->where(self::FIELD_CREATED_AT, $this->createdAt)->where('device_token', $this->deviceToken)
				->delete();
		}
		if(!$this->userId){
			le("Device token does not have user id");
		}
	}
	/**
	 * @return bool
	 */
	public function isAndroid(): bool{
		return $this->platform === BasePlatformProperty::PLATFORM_ANDROID;
	}
	/**
	 * @return bool
	 */
	public function isIOS(): bool{
		return $this->platform === BasePlatformProperty::PLATFORM_IOS;
	}
	public function validateId(){ le('!is_string($this->id)'); }
	public function test(): void{
		$m = User::mike();
		$n = $m->getQMUser()->getMostRecentPendingNotification();
		$this->send($n->getIndividualPushNotificationData());
	}
	/**
	 * Saved last_notified_at to database
	 * It's too slow to save on every single notification when we're doing batch sends so we use alreadySaved
	 */
	protected function saveIfNecessary(): void{
		if(!$this->alreadySaved){ // Slow to save
			try {
				$this->save();
				$this->alreadySaved = true;
			} catch (ModelValidationException $e) {
				le($e);
				le($e);
				throw new \LogicException();
			}
		}
	}
}
