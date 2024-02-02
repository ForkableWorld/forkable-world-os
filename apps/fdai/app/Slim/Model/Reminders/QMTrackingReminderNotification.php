<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection SlowArrayOperationsInLoopInspection */
namespace App\Slim\Model\Reminders;
use App\Buttons\States\RemindersInboxStateButton;
use App\Buttons\Tracking\TrackAllNotificationButton;
use App\Cards\TrackingReminderNotificationCard;
use App\CodeGenerators\Swagger\SwaggerDefinition;
use App\Exceptions\BadRequestException;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\NoChangesException;
use App\Exceptions\NoDeviceTokensException;
use App\Exceptions\NotFoundException;
use App\Exceptions\TrackingReminderNotificationNotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Logging\SolutionButton;
use App\Models\BaseModel;
use App\Models\Measurement;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseOriginalValueProperty;
use App\Properties\Measurement\MeasurementOriginalUnitIdProperty;
use App\Properties\Measurement\MeasurementOriginalValueProperty;
use App\Properties\TrackingReminder\TrackingReminderIdProperty;
use App\Properties\TrackingReminderNotification\TrackingReminderNotificationIdProperty;
use App\Properties\TrackingReminderNotification\TrackingReminderNotificationNotifyAtProperty;
use App\Properties\TrackingReminderNotification\TrackingReminderNotificationVariableIdProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\Variable\VariableVariableCategoryIdProperty;
use App\Slim\Controller\TrackingReminder\PostTrackingReminderNotificationsController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\Model\DBModel;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Notifications\IndividualPushNotificationData;
use App\Slim\Model\Notifications\PusherPushNotification;
use App\Slim\Model\Notifications\QMDeviceToken;
use App\Slim\Model\User\QMUser;
use App\Storage\DB\QMDB;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\DB\Writable;
use App\Storage\QueryBuilderHelper;

use App\Traits\HasModel\HasTrackingReminder;
use App\Traits\HasModel\HasUnit;
use App\Traits\ModelTraits\TrackingReminderNotificationTrait;
use App\Types\ObjectHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\Utils\AppMode;
use App\Utils\QMAPIValidator;
use App\Variables\QMUserVariable;
use Carbon\Carbon;
use Dialogflow\RichMessage\Suggestion;
use Illuminate\Database\Eloquent\Collection;
use Pusher\PusherException;
use stdClass;
/** Class TrackingReminderNotification
 * @package App\Slim\Model
 */
class QMTrackingReminderNotification extends QMTrackingReminder {
	use HasUnit, HasTrackingReminder;
	protected $primaryKey = 'id';
	protected $skipAll;
	protected $table = 'tracking_reminder_notifications';
	protected $trackingReminder;
	public $action;
	public $incrementing = true;
	public $measurementItems = [];
	public $modifiedValueInUserUnit;
	public $notifiedAt;
	public $notifyAt;
	public $reminderTime; // Deprecated
	public $receivedAt;
	public $shortQuestion;
	public $skip;
	public $snooze;
	public $total;
	public $track;
	public $trackAll;
	public $trackingReminderNotificationId;
	public $trackingReminderNotificationTimeEpoch;
	public $trackingReminderNotificationTimeLocal;
	public $trackingReminderNotificationTimeLocalHumanString;
	public const DEFAULT_LIMIT = 20;
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_NOTIFIED_AT = 'notified_at';
	public const FIELD_RECEIVED_AT = 'received_at';
	public const FIELD_NOTIFY_AT = 'notify_at';
	public const FIELD_TRACKING_REMINDER_ID = 'tracking_reminder_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const SKIP = "skip";
	public const SKIP_ALL = "skipAll";
	public const SNOOZE = "snooze";
	public const TABLE = 'tracking_reminder_notifications';
	public const TRACK = "track";
	public const TRACK_ALL = "trackAll";
	public const LARAVEL_CLASS = TrackingReminderNotification::class;
	public const DB_FIELD_NAME_TO_PROPERTY_NAME_MAP = [
		'id' => 'trackingReminderNotificationId',
		'notify_at' => 'trackingReminderNotificationTime',
	];
	/**
	 * TrackingReminderNotification constructor.
	 * @param null $row
	 * @param QMUser|null $user
	 * @param null $trackingReminder
	 * @param string|null $reminderTime
	 */
	public function __construct($row = null, QMUser $user = null, $trackingReminder = null,
		string $reminderTime = null){
		if($user){
			$this->userId = $user->getId();
		}
		if($reminderTime && $trackingReminder){
			$this->setReminderTimeUTC($reminderTime, $trackingReminder->reminderFrequency);
		}
		if($trackingReminder){
			$this->setTrackingReminderByTrackingReminder($trackingReminder);
		}
		if($row){
			$this->validateIdIfExists();
			if(!$user && isset($row->userId)){
				$user = QMUser::find($row->userId);
			}
			parent::__construct($row, $user, $this->userVariable);
			if(!$user){
				return;
			}
			$this->setTrackingReminderNotificationTimeLocal();
			$this->setTitle();
			if(isset($this->lastValueInCommonUnit)){
				$this->total = $this->lastValueInUserUnit;
			}
			$this->trackingReminderNotificationTime .= '+00:00';
			if($this->notifiedAt){
				$this->notifiedAt .= '+00:00';
			}
			//$this->getLongQuestion(); // Already done in parent constructor
			$this->getOptionsListCard();
			SolutionButton::addIfDebugMode($this->getTitleAttribute(), $this->getUrl());
			$this->getShortQuestion();
			$this->getOrSetCombinationOperation();
			$this->convertValuesToUserUnit();
			if($this->minimumAllowedValue === null){
				$this->setMinimumAllowedValue($this->getMinimumAllowedValueAttribute());
			}
			if($this->maximumAllowedValue === null){
				$this->setMaximumAllowedValue($this->getMaximumAllowedValueAttribute());
			}
		}
		TimeHelper::convertAllDateTimeValuesToRFC3339($this);
	}
	/**
	 * @param $mixed
	 * @param int|null $id
	 * @return QMTrackingReminderNotification
	 * @throws TrackingReminderNotificationNotFoundException
	 */
	private static function handleNotFound($mixed, ?int $id): ?QMTrackingReminderNotification{
		if($id){
			if($n = TrackingReminderNotification::withTrashed()->find($id)){
				//return $n->getDBModel();
				throw new TrackingReminderNotificationNotFoundException("TrackingReminderNotification with id $id " .
					"was already deleted: $n");
			}
		}
		/** @var QMTrackingReminder $r */
		if($r = QMTrackingReminder::pluck($mixed)){
			$n = $r->firstOrCreateNotification();
			if(!$n){
				throw new TrackingReminderNotificationNotFoundException("Could not find existing or soft-deleted " .
					"TrackingReminderNotification with id $id");
			}
			return $n->getDBModel();
		}
		throw new TrackingReminderNotificationNotFoundException("Could not find existing or soft-deleted " .
			"TrackingReminderNotification with id $id");
	}
	/**
	 * @param $mixed
	 * @return mixed|object
	 */
	private static function dataToNotification($mixed): object{
		if(is_array($mixed) && !isset($mixed['trackingReminderNotificationId']) && !isset($mixed['id']) &&
			!isset($mixed['trackingReminderId']) && isset($mixed[0])){
			$mixed = $mixed[0];
		}
		$mixed = ObjectHelper::convertToObject($mixed);
		if(isset($mixed->trackingReminderNotification)){
			$notification = $mixed->trackingReminderNotification;
			unset($mixed->trackingReminderNotification);
			$mixed = ObjectHelper::copyPublicPropertiesFromOneObjectToAnother($mixed,
				ObjectHelper::convertToObject($notification));
		}
		ObjectHelper::replaceLegacyPropertiesInObject($mixed);
		return $mixed;
	}
	/**
	 * @param QMTrackingReminderNotification[]
	 */
	public static function validateNotificationIds(array $notifications): void{
		/** @var QMTrackingReminderNotification $n */
		foreach($notifications as $n){
			$n->validateId();
			if($n->actionArray){ // We don't need it for combined notifications
				IndividualPushNotificationData::exceptionIfDuplicateButton($n->actionArray);
			}
		}
	}
	/**
	 * @return QMQB
	 */
	public static function dueQB(): QMQB{
		$qb = self::qb();
		$qb->join(QMDeviceToken::TABLE, QMDeviceToken::TABLE.'.'.QMDeviceToken::FIELD_USER_ID, '=',
		          self::TABLE.'.'.self::FIELD_USER_ID);
		$qb->groupBy([self::TABLE.'.id']);
		$qb->whereRaw(self::TABLE.'.'.self::FIELD_NOTIFY_AT.' > "'.db_date(Carbon::now()->subMinutes(5)).'"');
		$qb->whereRaw(self::TABLE.'.'.self::FIELD_NOTIFY_AT.' < "'.db_date(Carbon::now()->addMinutes(5)).'"');
		$qb->whereNull(self::TABLE.'.'.self::FIELD_DELETED_AT);
		$qb->whereNull(self::TABLE.'.'.self::FIELD_NOTIFIED_AT);
		return $qb;
	}
	public function populateDefaultFields(): void{
		$this->setNameAndDisplayName();
		$this->setUserUnit($this->userUnitId ?? $this->unitId ?? $this->commonUnitId);
		$this->setLocalTimes();
		$this->setImagesAndIcons();
		$this->setDefaultValueToDefaultValueInUserUnit();
		$this->convertValuesToUserUnit();
		$this->setOutcomeFromLaravelModelIfNecessary();
		$this->getQuestions();
		$this->getOrSetVariableDisplayName();
		$this->addToMemory();
		$this->makeSureNotAnActivity();
		TimeHelper::convertAllDateTimeValuesToRFC3339($this);
		$this->getOrSetCombinationOperation();
		$this->getInputType();
		$this->setVariableCategory($this->getVariableCategoryId());
		$this->validateUnit();
		$this->setCard();
		$this->getTitleAttribute();
		$this->setValueFrequency();
	}
	/**
	 * @param QMUser|int $user
	 * @param array $params
	 * @return array|QMTrackingReminderNotification[]
	 */
	public static function getTrackingReminderNotifications($user, array $params): array{
		$user = self::formatOrGetAuthenticatedUser($user, $params);
		$params = self::formatAndValidateParams($params);
		$qb = self::qb();
		if($user){
			QMTrackingReminder::addSingleUserVariableSelectFields($qb, $user);
		}
		self::applyQueryClauses($params, $qb);
		/** @var QMTrackingReminderNotification[] $rows */
		$rows = $qb->getArray();
		$notifications = self::convertRowsToTrackingReminderNotifications($rows, $user);
		SwaggerDefinition::addOrUpdateSwaggerDefinition($notifications, __CLASS__);
		ObjectHelper::addLegacyPropertiesToObjectsInArray($notifications);
		return $notifications;
	}
	/**
	 * @param array $params
	 * @return self[]
	 */
	public static function get(array $params = []): array{
		return self::getTrackingReminderNotifications(QMAuth::getQMUserIfSet(), $params);
	}
	/**
	 * @param int|null $userId
	 * @param int|null $variableIdToExclude
	 * @return bool|QMTrackingReminderNotification
	 */
	public static function getMostRecent(int $userId = null, int $variableIdToExclude = null){
		$params = [
			'sort' => '-updatedAt',
			'limit' => 1,
			self::FIELD_DELETED_AT => null,
			self::FIELD_NOTIFY_AT => '(lt)' . date('Y-m-d H:i:s'),
		];
		if($variableIdToExclude){
			$params['variableId'] = "(ne)$variableIdToExclude";
		}
		$items = self::getTrackingReminderNotifications($userId, $params);
		return $items[0] ?? false;
	}
	/**
	 * @param QMTrackingReminderNotification[] $notifications
	 */
	private static function addHourOfDayIfMoreThanOne($notifications){
		$indexed = [];
		foreach($notifications as $n){
			if(!isset($indexed[$n->variableId]
				[TimeHelper::YYYYmmddd($n->getNotifyAt())])){
				$indexed[$n->variableId][TimeHelper::YYYYmmddd($n->getNotifyAt())] = 0;
			}
			$indexed[$n->variableId][TimeHelper::YYYYmmddd($n->getNotifyAt())]++;
		}
		foreach($notifications as $n){
			$date = TimeHelper::YYYYmmddd($n->getNotifyAt());
			$number = $indexed[$n->variableId][$date];
			if($number > 1){
				$str = $n->getQMUser()->convertUtcToLocalHourWeekdayAndDateString($n->getNotifyAt());
				$n->setTrackingReminderNotificationTimeLocalHumanString($str);
				$n->setCard();  // Need to update with new time
			}
		}
	}
	/**
	 * @return string[]
	 */
	private static function getAllowedParams(): array{
		$arr = [
			'clientId',
			'createdAt',
			'id',
			'includeDeleted',
			'minimumReminderTimeUtcString',
			'maximumReminderTimeUtcString',
			'onlyPast',
			'reminderId',
			'reminderTime',
			'trackingReminderId',
			'updatedAt',
			'useWritableConnection',
			'variableCategoryId',
			'variableCategoryName',
			'variableId',
			'variableName',
			'userVariableId',
		];
		$arr = array_unique(array_merge($arr, array_keys(self::getAliasToFieldNameMap())));
		return $arr;
	}
	/**
	 * @return array
	 */
	public static function getLegacyProperties(): array{
		return parent::getLegacyProperties();
	}
	/**
	 * @param $user
	 * @param array $params
	 * @return QMUser
	 */
	private static function formatOrGetAuthenticatedUser($user, array $params): ?QMUser{
		if(is_int($user)){
			$user = QMUser::find($user);
		}
		if(!$user && isset($params['userId'])){
			$user = QMUser::find($params['userId']);
		}
		if(!$user && QMAuth::getQMUserIfSet()){
			$user = QMAuth::getQMUserIfSet();
		}
		return $user;
	}
	/**
	 * @param array $params
	 * @return array
	 * @throws BadRequestException
	 */
	private static function formatAndValidateParams(array $params): array{
		$params = QMStr::properlyFormatRequestParams($params);
		QMAPIValidator::validateParams(self::getAllowedParams(), array_keys($params),
			'trackingReminderNotifications/trackingReminderNotifications_get');
		return $params;
	}
	/**
	 * @param int $userId
	 * @param int $trackingReminderId
	 * @return int
	 */
	public static function deleteAllPastReminderNotifications(int $userId, int $trackingReminderId): int{
		$qb = self::writable()->whereRaw(TrackingReminderNotification::FIELD_NOTIFY_AT . ' < NOW()')
			->where('user_id', $userId)->where(self::FIELD_TRACKING_REMINDER_ID, $trackingReminderId);
		QMLog::debug("Soft deleting all notifications for reminder " . $trackingReminderId);
		$success = $qb->update([self::FIELD_DELETED_AT => date('Y-m-d H:i:s')]);
		return $success;
	}
	/**
	 * @return int
	 */
	public static function deleteOldNotifications(): int{
		return TrackingReminderNotificationNotifyAtProperty::deleteWhereLessThan(date('Y-m-d H:i:s',
				time() - 86400 * 14));
	}
	/**
	 * @param QMUser $user
	 * @return string
	 */
	public static function getPushNotificationMessage($user){
		$variableNames = [];
		$requestParams['reminderTime'] = '(lt)' . date('Y-m-d H:i:s');
		$trackingReminderNotifications = self::getTrackingReminderNotifications($user, $requestParams);
		foreach($trackingReminderNotifications as $trackingReminderNotification){
			$variableNames[] = strtolower($trackingReminderNotification->variableName);
		}
		$variableNames = array_values(array_unique($variableNames));
		if(!isset($variableNames[0])){
			return false;
		}
		$message = 'Time to track ' . $variableNames[0];
		unset($variableNames[0]);
		$variableNames = array_values($variableNames);
		$index = 1;
		foreach($variableNames as $variableName){
			if($index === 5){
				$message .= '...';
				break;
			}
			if(count($variableNames) === $index){
				$message .= ' and ' . $variableName . '!';
			} else{
				$message .= ', ' . $variableName;
			}
			$index++;
		}
		return $message;
	}
	/**
	 * @param array $params
	 * @return TrackingReminderNotification[]
	 * @noinspection PhpDocSignatureInspection
	 */
	public static function getPastTrackingReminderNotifications(array $params = []): Collection{
		$user = UserIdProperty::parentModelFromDataOrRequest($params);
		if(!$user){
			$user = QMAuth::getQMUser();
			if(!$user){
				throw new UnauthorizedException();
			}
		}
		$limit = QMArr::getValue($params, 'limit') ?? self::DEFAULT_LIMIT;
		//$limit = 1;
		$qb = $user->tracking_reminder_notifications()->with([
			'tracking_reminder',
			'user_variable',
			'variable'
		])
			->orderBy(self::FIELD_NOTIFY_AT, BaseModel::ORDER_DIRECTION_DESC)
			->where(self::FIELD_NOTIFY_AT, "<", db_date(time() + 1))
			->limit($limit);
		if($params){
			if($cat = VariableVariableCategoryIdProperty::pluckOrDefault($params)){
				$qb->whereHas('variable', function($q) use ($cat){
					$q->where(Variable::FIELD_VARIABLE_CATEGORY_ID, $cat);
				});
			}
			if($variableId = TrackingReminderNotificationVariableIdProperty::pluckOrDefault($params)){
				$qb->where(TrackingReminderNotification::FIELD_VARIABLE_ID, $variableId);
			}
		}
		$notifications = $qb->get();
		return $notifications;
	}
	/**
	 * @param array $params
	 * @return array
	 * @throws \App\Exceptions\UnauthorizedException
	 * @deprecated Use getPastTrackingReminderNotifications
	 */
	public static function getPastQMTrackingReminderNotifications(array $params = []): array{
		$notifications = static::getPastTrackingReminderNotifications($params);
		$dbms = static::toDBModels($notifications);
		QMTrackingReminderNotification::validateNotificationIds($dbms);
		return $dbms;
	}
	/**
	 * @param int $trackingReminderId
	 * @param int|null $userId
	 * @return QMTrackingReminderNotification
	 */
	public static function getMostRecentNotificationByReminderId(int $trackingReminderId,
		int $userId = null): ?QMTrackingReminderNotification{
		$requestParams['trackingReminderId'] = $trackingReminderId;
		if($userId){
			$requestParams['userId'] = $userId;
		}
		$trackingReminderNotifications = self::getPastQMTrackingReminderNotifications($requestParams);
		return $trackingReminderNotifications[0] ?? null;
	}
	/**
	 * @param array $requestParams
	 * @param QMQB $qb
	 * @return QMQB
	 */
	private static function applyQueryClauses(array $requestParams, QMQB $qb): QMQB{
		if(!isset($requestParams['includeDeleted']) || !$requestParams['includeDeleted']){
			$qb->whereRaw(self::TABLE . '.deleted_at IS NULL');
		}
		if(isset($requestParams['onlyPast']) && $requestParams['onlyPast']){
			$qb->where(self::TABLE . '.notify_at','<', Carbon::now());
		}
		if(!isset($requestParams['sort'])){
			$qb->orderBy(self::TABLE . '.notify_at', 'desc');
		}
		$max = $requestParams['maximumReminderTimeUtcString'] ?? null;
		if($max){
			$qb->whereRaw(self::TABLE . '.notify_at < "' . $max);
		}
		$min = $requestParams['minimumReminderTimeUtcString'] ?? null;
		if($min){
			$qb->whereRaw(self::TABLE . '.notify_at > "' . $min);
		}
		$aliasToFieldNameMap = self::getAliasToFieldNameMap();
		QueryBuilderHelper::applyFilterParamsIfExist($qb, $aliasToFieldNameMap, $requestParams);
		if(!isset($requestParams['limit'])){
			$requestParams['limit'] = self::DEFAULT_LIMIT;
		}
		QueryBuilderHelper::applyOffsetLimitSort($qb, $requestParams, $aliasToFieldNameMap);
		return $qb;
	}
	/**
	 * @return QMQB
	 */
	public static function getBaseSelectQuery(): QMQB{
		$db = ReadonlyDB::db();
		$qb = $db->table(self::TABLE)->select(self::TABLE . '.id', self::TABLE . '.created_at as createdAt',
				self::TABLE . '.id as trackingReminderNotificationId', self::TABLE . '.notified_at as notifiedAt',
				self::TABLE . '.received_at as receivedAt', self::TABLE . '.notify_at as reminderTime',
				self::TABLE . '.notify_at as notifyAt', self::TABLE . '.notify_at as trackingReminderNotificationTime',
				self::TABLE . '.tracking_reminder_id as trackingReminderId', self::TABLE . '.updated_at as updatedAt',
				self::TABLE . '.user_variable_id as userVariableId');
		//$qb->columns[] = $qb->raw(self::TABLE.'.notify_at - INTERVAL wu.time_zone_offset MINUTE as trackingReminderNotificationTimeLocal');
		return $qb;
	}
	/**
	 * @param array $rows
	 * @param QMUser|null $user
	 * @return QMTrackingReminderNotification[]
	 */
	private static function convertRowsToTrackingReminderNotifications(array $rows, QMUser $user = null): array{
		$notifications = [];
		/** @var QMTrackingReminderNotification $row */
		foreach($rows as $row){
			if(TimeHelper::isZeroTime($row->notifyAt)){
				le("Zero time for this notification: " . \App\Logging\QMLog::print_r($row, true));
			}
			self::setNumberOrUniqueValuesOnRow($row);
			$n = new self($row, $user);
			$n->validateId();
			$n->validateButtons();
			if(!$n->variableCategoryName){
				le('!$n->variableCategoryName');
			}
			$notifications[] = $n;
		}
		self::addHourOfDayIfMoreThanOne($notifications);
		return $notifications;
	}
	/**
	 * @param $data
	 * @param string|null $action
	 * @return QMTrackingReminderNotification
	 * @throws TrackingReminderNotificationNotFoundException
	 * @noinspection PhpHierarchyChecksInspection
	 */
	public static function fromData($data, string $action = null): QMTrackingReminderNotification{
		$nData = self::dataToNotification($data);
		if($action === null && isset($nData->action)){
			$action = $nData->action;
		}
		$unitId = MeasurementOriginalUnitIdProperty::pluckOrDefault($nData);
		$id = TrackingReminderNotificationIdProperty::pluckOrDefault($nData);
		$n = QMTrackingReminderNotification::find($id);
		if(!$n){
			$n = self::handleNotFound($nData, $id);
		}
		$valueInUserUnit = MeasurementOriginalValueProperty::pluckOrDefault($nData);
		if($action){
			$n->action = $action;
		}
		if($valueInUserUnit !== null){
			$n->setModifiedValue($valueInUserUnit);
		} else{
			if($n->modifiedValue !== null){
				le("what!", $n);
			}
		}
		if(!$n->id){
			if($id = $nData->trackingReminderNotificationId ?? $nData->id){
				$n->setId($id);
			} else{
				$n->populateByMostRecentNotification();
			}
		}
		return $n;
	}
	/**
	 * @return array
	 */
	private static function getAliasToFieldNameMap(): array{
		$aliasToFieldNameMap = [
			//'clientId' => 'tr.client_id',  // This causes problems because we include the client id informationally
			'createdAt' => self::TABLE . '.created_at',
			'deletedAt' => self::TABLE . '.deleted_at',
			'email' => TrackingReminder::TABLE . '.email',
			'id' => self::TABLE . '.id',
			'notificationBar' => TrackingReminder::TABLE . '.notification_bar',
			'notifiedAt' => self::TABLE . '.notifiedAt',
			'popUp' => TrackingReminder::TABLE . '.pop_up',
			'reminderId' => self::TABLE . '.tracking_reminder_id',
			'reminderTime' => self::TABLE . '.notify_at',
			'notifyAt' => self::TABLE . '.notify_at',
			'sms' => TrackingReminder::TABLE . '.sms',
			'trackingReminderId' => self::TABLE . '.tracking_reminder_id',
			'trackingReminderNotificationId' => self::TABLE . '.id',
			'updatedAt' => self::TABLE . '.updated_at',
			'variableCategoryId' => Variable::TABLE . '.variable_category_id',
			'variableId' => TrackingReminder::TABLE . '.variable_id',
			'variableName' => Variable::TABLE . '.name',
			'userVariableId' => self::TABLE . '.' . self::FIELD_USER_VARIABLE_ID,
		];
		return $aliasToFieldNameMap;
	}
	/**
	 * @return string
	 */
	public function setTrackingReminderNotificationTimeLocal(): string{
		$utcStr = $this->getNotifyAt();
		$epoch = $this->trackingReminderNotificationTimeEpoch = strtotime($utcStr);
		$user = $this->getQMUser();
		$latest = $user->getLatestReminderTime();
		$earliest = $user->getEarliestReminderTime();
		$freq = $this->getReminderFrequencyAttribute();
		$intraDay = $freq < 86400;
		$this->trackingReminderNotificationTimeLocal = $localHHMMSS = $user->utcToLocalHis($epoch);
		$tooLate = $localHHMMSS > $latest && $intraDay;
		$tooEarly = $localHHMMSS < $earliest && $intraDay;
		$func =
			"Debug with \App\PhpUnitJobs\Reminders\ReminderNotificationGeneratorJob::createTrackingReminderNotifications(" .
			$this->getUserId() . ");";
		if($tooEarly){
			$this->exceptionIfTesting("Reminder notification time ($localHHMMSS) " . "is earlier than user earliestReminderTime setting ($earliest).
            \n$func");
		}
		if($tooLate){
			$this->exceptionIfTesting("Reminder notification time ($localHHMMSS) later " . "than user latestReminderTime setting: $latest.
                \n$func");
		}
		if($freq < 86400 && $epoch > time() - 86400){
			$secondsAgo = time() - $epoch;
			if($secondsAgo < $freq){
				$secondsAgo = $freq;
				$human = "over the last " . TimeHelper::convertSecondsToHumanString($secondsAgo);
			} else{
				$human = TimeHelper::convertSecondsToHumanString($secondsAgo) . " ago";
			}
		} elseif($epoch > time() - 7 * 86400){
			$human = $user->getTodayYesterdayOrDayOfWeekString($utcStr, $freq);
		} else{
			$human = $user->convertUtcWeekdayAndDateString($utcStr);
		}
		$this->setTrackingReminderNotificationTimeLocalHumanString($human);
		return $this->trackingReminderNotificationTimeLocal;
	}
	/**
	 * @return int
	 */
	public function getUserId(): ?int{
		if(!$this->userId && QMAuth::getQMUserIfSet()){
			$this->userId = QMAuth::id();
		}
		return $this->userId;
	}
	/**
	 * @return int
	 */
	public function getVariableIdAttribute(): ?int{
		if(!$this->variableId){
			$this->variableId = $this->getQMTrackingReminder()->variableId;
		}
		return $this->variableId;
	}
	/**
	 * @return QMTrackingReminder
	 */
	public function getQMTrackingReminder(): QMTrackingReminder{
		$r = $this->trackingReminder;
		if(is_array($r)){
			$l = $this->l();
			$r = new QMTrackingReminder();
			$r->populateByLaravelTrackingReminder($l->getTrackingReminder());
			$r->populateByLaravelUserVariable($l->getUserVariable());
			$r->populateByLaravelVariable($l->getVariable());
		}
		if($r instanceof stdClass || is_array($r)){
			$r = QMTrackingReminder::instantiateIfNecessary($r);
		}
		if(!$r){
			$r = $this->setTrackingReminderCheckUnitAndGetFromGlobalsOrDB();
		}
		return $this->trackingReminder = $r;
	}
	/**
	 * @return int
	 */
	public function getTrackingReminderId(): int{
		$id = $this->getAttribute(self::FIELD_TRACKING_REMINDER_ID);
		if(!$id){
			/** @var QMTrackingReminder $r */
			if($r = $this->trackingReminder){
				$id = $r->getId();
			}
		}
		if(!$id){
			$l = $this->l();
			if(!$l){
				le("Could not find reminder notification with this id: " . $this->getId());
			}
			$id = $l->tracking_reminder_id;
		}
		return $this->trackingReminderId = $id;
	}
	/**
	 * @param QMTrackingReminder|array $trackingReminder
	 * @return QMTrackingReminder
	 */
	public function setTrackingReminderCheckUnitAndGetFromGlobalsOrDB($trackingReminder = null): QMTrackingReminder{
		if($trackingReminder){
			if(!isset($trackingReminder->commonUnitId)){
				$id = TrackingReminderIdProperty::pluck($trackingReminder);
				return $this->trackingReminder = QMTrackingReminder::find($id);
			}
			return $this->trackingReminder = new QMTrackingReminder($trackingReminder, QMAuth::getQMUserIfSet());
		}
		return $this->trackingReminder = QMTrackingReminder::find($this->getTrackingReminderId());
	}
	/**
	 * @return QMTrackingReminderNotification[]
	 */
	public static function getWhereDue(): array{
		$qb = self::dueQB();
		$rows = $qb->getArray();
		$whereString = $qb->getWhereString(true);
		ConsoleLog::info("Found ".count($rows)." due reminders where:\n".$whereString);
		if(!$rows){
			return [];
		}
		return self::convertRowsToTrackingReminderNotifications($rows);
	}
	public static function send(): array{
		return TrackingReminderNotification::send();
	}
	/**
	 * @throws PusherException
	 */
	public function sendCardViaPusher(){
		$card = $this->getOptionsListCard();
		new PusherPushNotification($card);
	}
	/**
	 * @return TrackingReminderNotificationCard
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getOptionsListCard(){
		if($this->card === null){
			$this->setCard();
		}
		return $this->card;
	}
	/**
	 * @return null|TrackingReminderNotificationCard
	 */
	public function setCard(): ?TrackingReminderNotificationCard {
		if(!AppMode::isApiRequest()){
			$this->logDebug("Not setting card because not API request");
			return null;
		}
		$this->card = new TrackingReminderNotificationCard($this);
		return $this->card;
	}
	/** @noinspection PhpUndefinedClassInspection */
	/** @noinspection PhpUndefinedNamespaceInspection */
	/**
	 * @return \Dialogflow\RichMessage\Dialogflow\Response\Suggestion
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getSuggestions(){
		$opts = $this->getVoiceOptions();
		$suggestions = Suggestion::create($opts);
		return $suggestions;
	}
	public function doTrackAll(){
		$user = $this->getUser();
		$notifications = $qb = $user->past_tracking_reminder_notifications()
			->where(TrackingReminderNotification::FIELD_VARIABLE_ID, $this->getVariableIdAttribute())->get();
		$uv = $this->getQMUserVariable();
		/** @var TrackingReminderNotification $n */
		foreach($notifications as $n){
			$val = $this->getModifiedValueInUserUnit();
			$userUnitId = $uv->getUserUnitId();
			$m = new QMMeasurement($n->notify_at, $val);
			$m->setOriginalUnitByNameOrId($userUnitId);
			try {
				$uv->addToMeasurementQueue($m);
			} catch (InvalidVariableValueAttributeException $e) {
				le($e);
			}
			try {
				$n->delete();
			} catch (\Exception $e) {
				le($e);
			}
		}
		try {
			$uv->saveMeasurements();
		} catch (IncompatibleUnitException | InvalidVariableValueException $e) {
			le($e);
		}
	}
	/**
	 * @param int $id
	 * @return int
	 */
	public function setId($id): int{
		return $this->id = $this->trackingReminderNotificationId = $id;
	}
	public function populateByMostRecentNotification(){
		$reminderId = $this->trackingReminderId;
		if(!$reminderId){
			$r = $this->getQMTrackingReminder();
			$reminderId = $r->getId();
		}
		$mostRecentNotification = self::getMostRecentNotificationByReminderId($reminderId);
		if($mostRecentNotification){
			foreach($mostRecentNotification as $key => $value){
				if(!isset($this->$key)){
					$this->$key = $value;
				}
			}
			return $this->id;
		}
	}
	/**
	 * @return string
	 */
	public function getTrackingReminderNotificationTimeLocalHumanString(): string{
		if($this->trackingReminderNotificationTimeLocalHumanString === null){
			$this->setTrackingReminderNotificationTimeLocal();
		}
		return $this->trackingReminderNotificationTimeLocalHumanString;
	}
	/**
	 * @return string
	 */
	public function getShortQuestion(): string{
		if($this->shortQuestion){
			return $this->shortQuestion;
		}
		$question = $this->getQuestion();
		$question = str_replace(" " . $this->getTrackingReminderNotificationTimeLocalHumanString(), '', $question);
		return $this->shortQuestion = $question;
	}
	/**
	 * @return int
	 */
	public function getTrackingReminderNotificationId(): int{
		$id = $this->id;
		if(!$this->trackingReminderNotificationId){
			if($id !== $this->trackingReminderId && $id !== $this->variableId){
				$this->trackingReminderNotificationId = $id;
			}
		}
		return $this->trackingReminderNotificationId;
	}
	/**
	 * @param QMTrackingReminder $trackingReminder
	 */
	public function setTrackingReminderByTrackingReminder($trackingReminder): void{
		$this->trackingReminder = $trackingReminder;
		$this->trackingReminderId = $trackingReminder->getId();
		$this->userVariableId = $trackingReminder->getUserVariableId();
		if($trackingReminder->userVariable){
			$this->setQMUserVariable($trackingReminder->userVariable);
		}
	}
	protected function doAction(){
		$action = $this->action;
		if(!isset($this->action)){
			if(isset($this->modifiedValue)){
				$action = $this->action = self::TRACK;
			} else{
				$this->throwNoActionException();
			}
		}
		if(QMStr::isCaseInsensitiveMatch($action, self::TRACK_ALL)){
			$this->trackAll = true;
			$this->doTrackAll();
		} elseif(QMStr::isCaseInsensitiveMatch($action, self::SKIP_ALL)){
			$this->skipAll = true;
			$this->doSkipAll();
		} elseif(QMStr::isCaseInsensitiveMatch($action, self::SKIP)){
			$this->skip = true;
			$this->doSkip();
		} elseif(stripos($action, self::TRACK) !== false){
			$this->track = true;
			$this->doTrack();
		} elseif(QMStr::isCaseInsensitiveMatch($action, self::SNOOZE)){
			$this->snooze = true;
			$this->doSnooze();
		} else{
			$this->throwNoActionException();
		}
	}
	protected function throwNoActionException(){
		$actions = self::getAvailableNotificationActions();
		le("Notification must have a modifiedValue property or an action property set to one of the following values: " .
			implode(' ', $actions));
	}
	/**
	 * @param float|string $inUserUnit
	 */
	protected function setModifiedValue($inUserUnit){
		$inUserUnit = BaseOriginalValueProperty::toFloat($inUserUnit);
		$this->modifiedValueInUserUnit = $inUserUnit;
		try {
			$this->modifiedValue = $this->convertToCommonUnit($inUserUnit);
		} catch (IncompatibleUnitException | InvalidVariableValueException $e) {
			le($e);
		}
	}
	/**
	 * @return float|null
	 */
	public function setDefaultValueInUserUnit(): ?float{
		$defaultValueInUserUnit = $this->defaultValue ?? null;
		/** @var TrackingReminderNotification $l */
		if($l = $this->laravelModel){
			$defaultValueInUserUnit = $l->getDefaultValueInUserUnit();
		} elseif($tr = $this->trackingReminder){
			/** @var QMTrackingReminder $tr */
			$defaultValueInUserUnit = $tr->getDefaultValueInUserUnit();
		}
		return $this->defaultValueInUserUnit = $defaultValueInUserUnit;
	}
	/**
	 * @return array
	 */
	public static function getAvailableNotificationActions(): array{
		return [
			self::SNOOZE,
			self::SKIP,
			self::SKIP_ALL,
			self::TRACK,
			self::TRACK_ALL,
		];
	}
	/**
	 * @return Measurement
	 * @throws NoChangesException
	 */
	public function doTrack(): Measurement{
		$clone = $this->toArray();
		unset($clone['id']);
		unset($clone['trackingReminderNotificationId']);
		try {
			$m = Measurement::upsertOne($clone);
		} catch (NoChangesException $e) {
			// Make sure notification gets deleted or we keep asking the same question if its already been recorded
			$this->softDelete([], __FUNCTION__);
			throw $e;
		}
		$this->softDelete([], __FUNCTION__);
		/** @var UserVariable $uv */
		$uv = $m->user_variable;
		$uv->updateFromMeasurements([$m]); // TODO: Queue this
		if(!$uv->latest_tagged_measurement_start_at){
			le('!$uv->latest_tagged_measurement_start_at');
		}
		return $m;
	}
	public function doSnooze(){
		$db = Writable::db();
		$success = $db->table('tracking_reminder_notifications')
			->join('tracking_reminders', 'tracking_reminder_notifications.tracking_reminder_id', '=',
				'tracking_reminders.id')->where('tracking_reminder_notifications.id', $this->getId())
			->where('tracking_reminders.user_id', $this->getUserId())
			->update(['notify_at' => QMDB::getDatabaseNowPlusSeconds(3600)]);
		if(!$success){
			QMLog::error('Could not snooze notification!', ['trackingReminderNotificationId' => $this->getId()]);
		}
	}
	/**
	 * @return int
	 */
	public function doSkip(): int{
		return $this->softDelete([], __FUNCTION__);
	}
	/**
	 * @return float
	 */
	public function getModifiedValueInUserUnit(): float{
		$value = $this->modifiedValueInUserUnit;
		if($value === null && isset($this->modifiedValue)){
			$value = $this->modifiedValueInUserUnit = $this->modifiedValue;
		}
		if($value === null){
			$value = $this->modifiedValueInUserUnit = $this->getDefaultValueInUserUnit();
		}
		return BaseOriginalValueProperty::toFloat($value);
	}
	/**
	 * @return int
	 */
	public function getTrackingReminderNotificationTime(): int{
		return $this->trackingReminderNotificationTimeEpoch ?: $this->setTrackingReminderNotificationTime();
	}
	/**
	 * @return int
	 */
	public function setTrackingReminderNotificationTime(): int{
		$time = $this->trackingReminderNotificationTimeEpoch;
		if(!$time && $str = $this->trackingReminderNotificationTime){
			$time = $this->trackingReminderNotificationTimeEpoch = strtotime($str);
		}
		if(!$time && $this->getDbRow()){
			$this->logDebug("trackingReminderNotificationTimeEpoch not provided so have to get from DB!");
			$this->trackingReminderNotificationTimeEpoch = $time = strtotime($this->l()->notify_at);
		}
		if(!$time){
			if(AppMode::isApiRequest()){
				$this->logError("trackingReminderNotificationTimeEpoch not provided so falling back to current time!");
				$time = time();
			} else{
				le("Please provide trackingReminderNotificationTimeEpoch!");
			}
		}
		return $this->trackingReminderNotificationTimeEpoch = $time;
	}
	protected function doSkipAll(){
		$user = $this->getAuthenticatedUserOrNotificationUser();
		self::deleteAllPastReminderNotifications($user->id, $this->getTrackingReminderId());
	}
	/**
	 * @return QMUser
	 */
	public function getAuthenticatedUserOrNotificationUser(): QMUser{
		$loggedInUser = QMAuth::getQMUser(QMAccessToken::SCOPE_WRITE_MEASUREMENTS);
		$notificationUser = $this->getQMUser();
		if($loggedInUser && $notificationUser && $loggedInUser->getId() !== $notificationUser->getId()){
			le("Logged in user $loggedInUser is not the same as notification user " . $notificationUser);
		}
		return $loggedInUser ?: $notificationUser;
	}
	/**
	 * @return mixed
	 */
	public function getTimeZoneOffset(): ?int{
		if($this->timeZoneOffset === null){
			return null;
		}
		return (int)$this->timeZoneOffset;
	}
	/**
	 * @return string
	 */
	public function getOptimalValueMessage(): ?string{
		if(isset($this->userOptimalValueMessage)){
			return $this->getOrCalculateUserOptimalValueMessage();
		}
		$userVariable = $this->getQMUserVariable();
		return $userVariable->getOptimalValueMessage();
	}
	/**
	 * @return bool|string
	 */
	public function getResponseSentence(): string{
		if($this->getOptimalValueMessage()){
			return $this->getOptimalValueMessage();
		}
		if($this->action === self::SNOOZE){
			return "I'll ask you again in an hour. ";
		}
		if($this->action === self::SKIP){
			return "OK. We'll skip this time. ";
		}
		if($this->action === self::SKIP_ALL){
			return "OK. We'll skip all remaining notifications for " . $this->getOrSetVariableDisplayName() . ". ";
		}
		if($this->action === self::TRACK){
			$value = $this->getModifiedValueInUserUnit();
			return "I've recorded " . $this->getUserUnit()->getValueAndUnitString($value) . " " .
				$this->getOrSetVariableDisplayName() . ".  ";
		}
		if($this->action === self::TRACK_ALL){
			return "I've recorded " . $this->getUserUnit()->getValueAndUnitString($this->getModifiedValueInUserUnit()) .
				" for all remaining " . $this->getOrSetVariableDisplayName() . " notifications.  ";
		}
		le("No action was set on this notifications $this");throw new \LogicException();
	}
	/**
	 * @param $mixed
	 * @param string|null $action
	 * @return QMTrackingReminderNotification
	 * @throws TrackingReminderNotificationNotFoundException
	 */
	public static function handleSubmittedNotification($mixed, string $action = null): QMTrackingReminderNotification{
		$n = self::fromData($mixed, $action);
		$u = QMAuth::getQMUser();
		if($n->userId && $u && $u->getId() !== $n->userId){
			throw new BadRequestException("Logged in user id $u->id does not equal notification user id $n->userId");
		}
		if(isset($n->trackingReminder)){
			$n->setTrackingReminderCheckUnitAndGetFromGlobalsOrDB($n->trackingReminder);
		}
		$n->doAction();
		$u = $n->getQMUser();
		$offset = $n->getTimeZoneOffset();
		if($offset !== null){
			$u->setTimeZone($offset);
		}
		return $n;
	}
	/**
	 * @param int $id
	 * @param bool $includeDeleted
	 * @return QMTrackingReminderNotification
	 */
	public static function getByTrackingReminderNotificationId(int $id,
		bool $includeDeleted = false): ?QMTrackingReminderNotification{
		$notifications = self::getTrackingReminderNotifications(null, [
			'id' => $id,
			'includeDeleted' => $includeDeleted,
		]);
		return $notifications[0] ?? null;
	}
	/**
	 * @return int
	 */
	public function getId(): int{
		$id = $this->trackingReminderNotificationId;
		if(!$id){
			$id = $this->id;
		}
		return $this->id = $this->trackingReminderNotificationId = $id;
	}
	/**
	 * @return array
	 */
	public function getActionSheetButtons(): array{
		$buttons = [];
		//$buttons = array_merge($buttons, $this->getUserVariableButtons(false));
		//$buttons = array_merge($buttons, $this->getCommonVariableButtons(true));
		$buttons = array_merge($buttons, $this->getTrackingReminderButtons());
		$buttons = array_merge($buttons, $this->getTrackAllButtons());
		//$buttons[] = new SkipAllNotificationButton($this);
		return $buttons;
	}
	/**
	 * @return TrackAllNotificationButton[]
	 */
	public function getTrackAllButtons(): array{
		if($this->trackAllActions){
			return $this->trackAllActions;
		}
		$trackAllActions = [];
		$buttons = $this->getNotificationActionButtons();
		foreach($buttons as $action){
			$actionType = $action->action;
			if(in_array($actionType, [
				QMTrackingReminderNotification::TRACK,
				QMTrackingReminderNotification::SKIP,
			], false)){
				$trackAllActions[] = new TrackAllNotificationButton($action, $this);
			}
		}
		return $this->trackAllActions = $trackAllActions;
	}
	/**
	 * @param $utc
	 * @param int $frequency
	 */
	public function setReminderTimeUTC($utc, int $frequency){
		$this->reminderFrequency = $frequency;
		$this->notifyAt = $this->trackingReminderNotificationTime = db_date($utc);
		$this->trackingReminderNotificationTimeEpoch = strtotime($utc);
		$this->setTrackingReminderNotificationTimeLocal();
	}
	/**
	 * @param $id
	 * @return QMTrackingReminderNotification
	 */
	public static function find($id): ?DBModel{
		$n = TrackingReminderNotification::findInMemoryOrDB($id);
		if(!$n){
			return null;
		}
		$userId = QMAuth::id();
		if($userId && $n->user_id !== $userId){
			throw new UnauthorizedException();
		}
		return $n->getDBModel();
	}
	/**
	 * @param string $reason
	 * @param bool $countFirst
	 * @return int
	 */
	public function hardDelete(string $reason, bool $countFirst = false): int{
		return static::writable()->where(static::FIELD_ID, $this->getId())->hardDelete($reason, $countFirst);
	}
	/**
	 * @param array $data
	 * @param string|null $reason
	 * @return int
	 */
	protected function softDelete(array $data = [], string $reason = null): int{
		$data[self::FIELD_DELETED_AT] = date('Y-m-d H:i:s');
		$success =
			self::writable()->where(static::FIELD_ID, $this->getId())->where(static::FIELD_USER_ID, $this->getUserId())
				->update($data);
		return $success;
	}
	/**
	 * @param bool $instantiate
	 * @return QMQB
	 */
	public static function qb(bool $instantiate = false): QMQB{
		$qb = self::getBaseSelectQuery();
		$qb->join(TrackingReminder::TABLE, TrackingReminder::TABLE . '.id', '=', self::TABLE . '.tracking_reminder_id');
		self::addTrackingReminderSelectFields($qb); // Includes UserVariable fields
		QMTrackingReminder::addCommonVariableSelectFields($qb);
		if($instantiate){
			$qb->class = self::class;
		}
		return $qb;
	}
	public function getNotifyAt(): string{
		$at = $this->trackingReminderNotificationTime =
		$this->notifyAt = $this->notifyAt ?? $this->trackingReminderNotificationTime ?? null;
		return $at;
	}
	/**
	 * @return TrackingReminderNotification
	 */
	public function l(): TrackingReminderNotification{
		return $this->firstOrNewLaravelModel();
	}
	/**
	 * @return TrackingReminderNotification
	 * @noinspection PhpDocSignatureInspection
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function firstOrNewLaravelModel(){
		$l = parent::firstOrNewLaravelModel();
		if(!$l instanceof TrackingReminderNotification){
			le("!l instanceof TrackingReminderNotification");
		}
		return $l;
	}
	public function getQMUserVariable(): QMUserVariable{
		$uv = $this->userVariable;
		if(!$uv){
			$uv = parent::findQMUserVariable($this->getUserId());
			$this->setQMUserVariable($uv);
		}
		return $uv;
	}
	/**
	 * @param BaseModel $laravelModel
	 * @return BaseModel
	 */
	public function setLaravelModel(BaseModel $laravelModel): BaseModel{
		if(!$laravelModel instanceof TrackingReminderNotification){
			le('!$laravelModel instanceof TrackingReminderNotification');
		}
		return parent::setLaravelModel($laravelModel);
	}
	public function toDbInsertionArray(): array{
		$arr = parent::toDbInsertionArray();
		if(!isset($arr[self::FIELD_NOTIFY_AT])){
			parent::toDbInsertionArray();
			le("FIELD_NOTIFY_AT");
		}
		return $arr;
	}
	/**
	 * @param BaseModel $notification
	 * @return void
	 */
	public function populateByLaravelTrackingReminderNotification(BaseModel $notification): void{
		$arr = $notification->attributesToArray();
		foreach($arr as $key => $value){
			if($value === null){
				continue;
			}
			$this->setAttributeIfNotSet($key, $value);
		}
	}
	public function validateId(){
		if(!$this->id){
			le('!$this->id');
		}
		if(!$this->trackingReminderNotificationId){
			le('!$this->trackingReminderNotificationId');
		}
		if($this->trackingReminderNotificationId !== $this->id){
			le('$this->trackingReminderNotificationId !== $this->id');
		}
	}
	public static function getRequiredPropertyNames(): array{
		$arr = parent::getRequiredPropertyNames();
		$arr[] = 'trackingReminderId';
		$arr[] = 'reminderFrequency';
		return $arr;
	}
	/**
	 * @param TrackingReminderNotification $n
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public function populateByLaravelModel(BaseModel $n){
		$v = $n->getVariable();
		$uv = $n->getUserVariable();
		$this->setVariableName($v->getNameAttribute());
		$this->userUnitId = $uv->getUnitIdAttribute();
		$this->unitId = $this->userUnitId ?? $v->getUnitIdAttribute();
		if($n->hasId()){
			$this->trackingReminderNotificationId = $n->getId();
		}
		$this->setLaravelModel($n);
		$this->populateByLaravelTrackingReminderNotification($n);
		try {
			$this->populateByLaravelTrackingReminder($r = $n->getTrackingReminder());
		} catch (NotFoundException $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());
			$n->forceDelete();
			throw $e;
		}
		$this->variableCategoryId = $v->variable_category_id;
		$this->populateByLaravelUserVariable($uv);
		$this->populateByLaravelVariable($v);
		$this->populateDefaultFields();
		if($n->hasId()){
			$this->id = $this->trackingReminderNotificationId = $n->id;
			$this->addToMemory();
		}
		$this->validateId();
	}
	public function getAt(): string{
		return $this->getNotifyAt();
	}
	public function getUrl(array $params = []): string{
		return RemindersInboxStateButton::url(array_merge($params, $this->getUrlParams()));
	}
	public function getUrlParams(): array{
		$params = parent::getUrlParams();
		$params['tracking_reminder_notification_id'] = $this->getTrackingReminderNotificationId();
		return $params;
	}
	public function getUnitIdAttribute(): ?int{
		if(!$this->unitId){
			$this->unitId = $this->getUserVariable()->getUnitIdAttribute();
		}
		return $this->unitId;
	}
	public function getVariableCategoryId(): int{
		return $this->userVariableVariableCategoryId ?? $this->variableCategoryId;
	}
	/**
	 * @param string $str
	 */
	public function setTrackingReminderNotificationTimeLocalHumanString(string $str): void{
		$this->trackingReminderNotificationTimeLocalHumanString = $str;
	}
	/**
	 * @param QMDeviceToken|null $dt
	 * @return IndividualPushNotificationData
	 */
	public function getIndividualPushNotificationData(QMDeviceToken $dt = null): ?IndividualPushNotificationData{
		$data = $this->getUserVariable()->getIndividualPushNotificationData();
		if($dt){
			$data->setQMDeviceToken($dt);
		}
		if($data){
			$data->setTrackingReminderNotificationId($this->getId());
		}
		return $data;
	}
	public function getWebhookUrl(): string{
		return PostTrackingReminderNotificationsController::getUrl($this->getUrlParams());
	}
	/**
	 * @return TrackingReminderNotification
	 * @throws NoDeviceTokensException
	 */
	public function sendNotification(): TrackingReminderNotification{
		$l = $this->l();
		$l->sendNotification();
		return $l;
    }
}
