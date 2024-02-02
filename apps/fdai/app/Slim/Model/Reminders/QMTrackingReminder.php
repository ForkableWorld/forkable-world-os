<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Reminders;
use App\Buttons\QMButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Buttons\States\VariableStates\ReminderAddStateButton;
use App\CodeGenerators\Swagger\SwaggerDefinition;
use App\Exceptions\BadRequestException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\TrackingReminderNotFoundException;
use App\Exceptions\UserNotFoundException;
use App\Logging\SolutionButton;
use App\Models\BaseModel;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\User;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Variable\VariableDefaultValueProperty;
use App\Slim\Controller\TrackingReminder\PostTrackingReminderController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\QMUnit;
use App\Slim\Model\User\QMUser;
use App\Slim\QMSlim;
use App\Storage\DB\QMDB;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\QueryBuilderHelper;
use App\Traits\HasButton;

use App\Traits\HasModel\HasUserVariable;
use App\Traits\HasModel\HasVariableCategory;
use App\Traits\IsEditable;
use App\Types\ObjectHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\Units\YesNoUnit;
use App\Utils\IonicHelper;
use App\Utils\QMAPIValidator;
use App\Variables\QMUserVariable;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Tests\TestGenerators\StagingJobTestFile;
/** Class TrackingReminder
 * @package App\Slim\Model
 */
class QMTrackingReminder extends QMUserVariable {
	use HasUserVariable, HasButton, IsEditable, HasVariableCategory;
	private static $trackingReminderButtons;
	protected $commonVariableValence;
	protected $defaultValueInUserUnit;
	protected $email;
	protected $notificationBar;
	protected $popUp;
	protected $sms;
	protected $trackingReminderImageUrl;
	protected $userVariable;
	public $clientId;
	public $createdAt;
	public $defaultValue;
	public $earliestReminderTime;
	public $firstDailyReminderTime;
	public $frequencyTextDescription;
	public $frequencyTextDescriptionWithTime;
	public $icon;
	public $id;
	public $imageUrl;
	public $instructions;
	public $lastTracked;
	public $lastValueInUserUnit;
	public $latestReminderTime;
	public $latestTrackingReminderNotificationNotifyAt;
	public $latestTrackingReminderNotificationReminderTime;
	public $localDailyReminderNotificationTimes;
	public $localDailyReminderNotificationTimesForAllReminders;
	public $modifiedValue;
	public $nextReminderTimeEpochSeconds;
	public $numberOfPendingNotifications;
	public $reminderEndTime;
	public $reminderFrequency;
	public $reminderSound;
	public $reminderStartEpochSeconds;
	public $reminderStartTime;
	public $reminderStartTimeLocal;
	public $reminderStartTimeLocalHumanFormatted;
	public $secondDailyReminderTime;
	public $secondToLastValueInUserUnit;
	public $startTrackingDate;
	public $stopTrackingDate;
	public $thirdDailyReminderTime;
	public $thirdToLastValueInUserUnit;
	public $timeZoneOffset;
	public $title;
	public $trackAllActions;
	public $trackingReminderId;
	public $trackingReminderNotificationTime;
	public $userVariableId;
	public $valueAndFrequencyTextDescription;
	public $valueAndFrequencyTextDescriptionWithTime;
	public $variableName;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DEFAULT_VALUE = 'default_value';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_EMAIL = 'email';
	public const FIELD_ID = 'id';
	public const FIELD_IMAGE_URL = 'image_url';
	public const FIELD_INSTRUCTIONS = 'instructions';
	public const FIELD_LAST_TRACKED = 'last_tracked';
	public const FIELD_LATEST_TRACKING_REMINDER_NOTIFICATION_NOTIFY_AT = 'latest_tracking_reminder_notification_notify_at';
	public const FIELD_NOTIFICATION_BAR = 'notification_bar';
	public const FIELD_POP_UP = 'pop_up';
	public const FIELD_REMINDER_END_TIME = 'reminder_end_time';
	public const FIELD_REMINDER_FREQUENCY = 'reminder_frequency';
	public const FIELD_REMINDER_SOUND = 'reminder_sound';
	public const FIELD_REMINDER_START_TIME = 'reminder_start_time';
	public const FIELD_SMS = 'sms';
	public const FIELD_START_TRACKING_DATE = 'start_tracking_date';
	public const FIELD_STOP_TRACKING_DATE = 'stop_tracking_date';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_USER_VARIABLE_ID = 'user_variable_id';
	public const FIELD_VARIABLE_ID = 'variable_id';
	public const INSTANCE_SIZE_IN_MB = 2;
	public const LARAVEL_CLASS = TrackingReminder::class;
	public const TABLE = 'tracking_reminders';
	public const DB_FIELD_NAME_TO_PROPERTY_NAME_MAP = [
		'default_value' => 'defaultValueInUserUnit',
		'id' => 'trackingReminderId',
		'image_url' => 'trackingReminderImageUrl',
	];
	/** @noinspection MagicMethodsValidityInspection */
	/**
	 * @param null $row
	 * @param QMUser|null $user
	 * @param QMUserVariable|null $userVariable
	 */
	public function __construct($row = null, QMUser $user = null, QMUserVariable $userVariable = null){
		if(!$row){
			return;
		}
		$this->dbRow = $row;
		if($userVariable){
			$this->setQMUserVariable($userVariable);
		}
		if(is_array($row)){
			$row = ObjectHelper::convertToObject($row);
		}
		foreach($row as $key => $value){
			if($value instanceof BaseModel){
				continue;
			}
			if($value !== null && property_exists($this, $key)){
				$this->$key = $value;
			}
		} // 3X faster than calling populateFieldsByArrayOrObject
		// Can't do this here or doctors can't view patient reminders QMUser::compareUserIdToAuthenticatedUserId($this);
		if($i = $row->trackingReminderImageUrl ?? $row->variableImageUrl ?? null){
			$this->setImageUrl($i);
		}
		$this->setValence();
		$this->getQMVariableCategory();
		$this->setNameAndDisplayName();
		if(!$user){
			return;
		}
		$this->setUserId($user->getId());
		$this->populateDefaultFields();
	}
	/**
	 * @param int $userId
	 * @param string $variableName
	 * @return QMTrackingReminder[]
	 */
	public static function getByVariableName(int $userId, string $variableName): array{
		return self::getTrackingReminders($userId, ['variableName' => $variableName]);
	}
	/**
	 * @param $remindersArray
	 * @return array
	 */
	public static function breakDailyTimesIntoSeparateReminders($remindersArray): array{
		$newRemindersArray = [];
		foreach($remindersArray as $reminder){
			if(isset($reminder['firstDailyReminderTime'])){
				$newReminder = $reminder;
				$newReminder['reminderStartTime'] = $reminder['firstDailyReminderTime'];
				$newReminder['reminderFrequency'] = 86400;
				$newRemindersArray[] = $newReminder;
			}
			if(isset($reminder['secondDailyReminderTime'])){
				$newReminder = $reminder;
				$newReminder['reminderStartTime'] = $reminder['secondDailyReminderTime'];
				$newReminder['reminderFrequency'] = 86400;
				$newRemindersArray[] = $newReminder;
			}
			if(isset($reminder['thirdDailyReminderTime'])){
				$newReminder = $reminder;
				$newReminder['reminderStartTime'] = $reminder['thirdDailyReminderTime'];
				$newReminder['reminderFrequency'] = 86400;
				$newRemindersArray[] = $newReminder;
			}
		}
		return $newRemindersArray;
	}
	public function getUserOrCommonUnit(): QMUnit{
		$id = $this->userUnitId ?? $this->commonUnitId;
		if(!$id){
			$id = $this->getUserVariable()->default_unit_id;
		}
		if(!$id){
			$id = $this->getVariable()->default_unit_id;
		}
		return QMUnit::findInMemoryOrDB($id);
	}
	/**
	 * @param QMUser|int|null $user
	 * @param array $params
	 * @return QMTrackingReminder[]
	 */
	public static function getTrackingReminders($user = null, array $params = []): array{
		if(is_int($user)){
			$user = QMUser::find($user);
		}
		$params = self::formatAndValidateParams($params);
		$qb = self::qb();
		if($user){
			self::addSingleUserVariableSelectFields($qb, $user);
		}
		self::addQueryClauses($params, $qb);
		$rows = $qb->getArray();
		if(!$rows){
			return [];
		}
		$reminders = self::convertRowsToModel($rows, $user);
		SwaggerDefinition::addOrUpdateSwaggerDefinition($reminders, __CLASS__);
		ObjectHelper::addLegacyPropertiesToObjectsInArray($reminders);
		return $reminders;
	}
	protected function valueInvalidForVariable(float $valueInCommonUnit, string $type, $v = null,
	                                         int $durationInSeconds = null): ?string{
		$commonUnit = $this->getCommonUnit();
		if($durationInSeconds &&
		   $this->getCombinationOperation() === BaseCombinationOperationProperty::COMBINATION_SUM){
			$valueInCommonUnit = $valueInCommonUnit / ($durationInSeconds / 86400);
			$maxInCommonUnit = $this->maximumAllowedDailyValue;
			if($maxInCommonUnit !== null){
				if($maxInCommonUnit < $valueInCommonUnit){
					return "$type value $valueInCommonUnit $commonUnit->abbreviatedName  exceeds maximum 
$maxInCommonUnit $commonUnit->abbreviatedName for variable $this->name";
				}
			}
		}
		$maxInCommonUnit = $this->maximumAllowedValueInCommonUnit;
		if($maxInCommonUnit !== null && $maxInCommonUnit < $valueInCommonUnit){
			if($commonUnit->id === YesNoUnit::ID){
				return null;
			} // YesNo is countable/summable
			return "$type value $valueInCommonUnit $commonUnit->abbreviatedName
                exceeds maximum $maxInCommonUnit $commonUnit->abbreviatedName for variable $this->name
                minimumAllowedValueInCommonUnit = $this->minimumAllowedValueInCommonUnit
               {$this->getUrl()}";
		}
		$min = $this->minimumAllowedValueInCommonUnit;
		if($min !== null && $min > $valueInCommonUnit){
			return "$type value $valueInCommonUnit $commonUnit->abbreviatedName
                is below minimum $min $commonUnit->abbreviatedName for variable $this->name
                View and Delete at:
                {$this->getUrl()} ";
		}
		return null;
	}
	public function getUrl(array $params = []): string{
		return IonicHelper::getReminderEditUrl([['id' => $this->id]]);
	}
	/**
	 * @param object[]|QMTrackingReminder[] $rows
	 * @param QMUser|null $user
	 * @return self[]
	 */
	public static function convertRowsToModel(array $rows, QMUser $user = null): array{
		$models = [];
		foreach($rows as $row){
			self::setNumberOrUniqueValuesOnRow($row);
			$model = new self($row, $user);
			if($model->name !== $model->variableName){
				le('$model->name !== $model->variableName');
			}
			$models[] = $model;
		}
		if($user){
			$models = self::populateLocalDailyReminderNotificationTimes($user, $models);
		}
		$models = self::populateFrequencyTextDescriptions($models);
		return $models;
	}
	public function setLocalTimes(){
		$this->setReminderStartTimeLocalHumanFormatted();
		$this->setReminderStartTimeLocal();
	}
	/**
	 * @param int $userId
	 * @param int $id
	 * @param bool $soft
	 * @return int
	 */
	public static function deleteTrackingReminder(int $userId, int $id, bool $soft = false): int{
		$success = self::writable()->where('id', $id)->where('user_id', $userId)->delete();
		if($soft){
			self::writable()->where('id', $id)->update([QMDB::FIELD_DELETED_AT => date('Y-m-d H:i:s')]);
		} else{
			self::writable()->where('id', $id)->where('user_id', $userId)->delete();
		}
		if($success){
			if($soft){
				QMTrackingReminderNotification::writable()->where('tracking_reminder_id', $id)
					->update([QMDB::FIELD_DELETED_AT => date('Y-m-d H:i:s')]);
			} else{
				QMTrackingReminderNotification::writable()->where('tracking_reminder_id', $id)->delete();
			}
		} elseif(!QMUser::isTestUserByIdOrEmail($userId)){
			throw new TrackingReminderNotFoundException('Could not delete this reminder. Are you sure it exists?');
		}
		return $success;
	}
	/**
	 * @param QMQB $qb
	 * @return QMQB
	 */
	protected static function addTrackingReminderSelectFields(QMQB $qb): QMQB{
		$qb->columns[] = self::TABLE . '.reminder_frequency as reminderFrequency';
		$qb->columns[] = self::TABLE . '.client_id as clientId';
		$qb->columns[] = self::TABLE . '.default_value as defaultValueInUserUnit';
		$qb->columns[] = self::TABLE . '.id as trackingReminderId';
		$qb->columns[] = self::TABLE . '.image_url as trackingReminderImageUrl';
		$qb->columns[] = self::TABLE . '.instructions as instructions';
		$qb->columns[] = self::TABLE . '.last_tracked as lastTracked';
		$qb->columns[] = self::TABLE . '.reminder_start_time as reminderStartTime';
		$qb->columns[] = self::TABLE . '.start_tracking_date as startTrackingDate';
		$qb->columns[] = self::TABLE . '.stop_tracking_date as stopTrackingDate';
		$qb->columns[] = self::TABLE . '.user_id as userId';
		$qb->columns[] = self::TABLE . '.variable_id as variableId';
		//$qb->columns[] = self::TABLE.'.email',
		//$qb->columns[] = self::TABLE.'.notification_bar as notificationBar',
		//$qb->columns[] = self::TABLE.'.pop_up as popUp',
		//$qb->columns[] = self::TABLE.'.reminder_end_time as reminderEndTime',  //$qb->columns[] =  This is done globally in wp_users table
		//$qb->columns[] = self::TABLE.'.reminder_sound as reminderSound',
		//$qb->columns[] = self::TABLE.'.sms',
		return $qb;
	}
	/**
	 * @param QMQB $qb
	 * @return QMQB
	 */
	public static function addCommonVariableSelectFields(QMQB $qb): QMQB{
		$qb->join(Variable::TABLE, self::TABLE . '.variable_id', '=', Variable::TABLE . '.id');
		$qb->columns[] = Variable::TABLE . '.combination_operation as combinationOperation';
		$qb->columns[] = Variable::TABLE . '.default_unit_id as commonUnitId';
		$qb->columns[] = Variable::TABLE . '.description as description';
		$qb->columns[] = Variable::TABLE . '.filling_value as commonVariableFillingValue';
		$qb->columns[] = Variable::TABLE . '.image_url as variableImageUrl';
		$qb->columns[] = Variable::TABLE . '.ion_icon as ionIcon';
		$qb->columns[] = Variable::TABLE . '.most_common_value AS mostCommonValueInCommonUnit';
		$qb->columns[] = Variable::TABLE . '.name as variableName';
		$qb->columns[] = Variable::TABLE . '.outcome as outcome';
		$qb->columns[] = Variable::TABLE . '.product_url as productUrl';
		$qb->columns[] = Variable::TABLE . '.second_most_common_value AS secondMostCommonValueInCommonUnit';
		$qb->columns[] = Variable::TABLE . '.third_most_common_value AS thirdMostCommonValueInCommonUnit';
		$qb->columns[] = Variable::TABLE . '.valence as commonVariableValence';
		$qb->columns[] = Variable::TABLE . '.variable_category_id as variableCategoryId';
		$qb->columns[] = Variable::TABLE . '.' . Variable::FIELD_OPTIMAL_VALUE_MESSAGE .
			' as commonVariableOptimalValueMessage';
		$qb->columns[] = Variable::TABLE . '.' . Variable::FIELD_BEST_AGGREGATE_CORRELATION_ID .
			' as bestGlobalVariableRelationshipId';
		$qb->columns[] = Variable::TABLE . '.' . Variable::FIELD_NUMBER_OF_UNIQUE_VALUES .
			' as commonNumberOfUniqueValues';
		$qb->columns[] = Variable::TABLE . '.' . Variable::FIELD_MAXIMUM_ALLOWED_VALUE .
			' as maximumAllowedValueInCommonUnit';
		$qb->columns[] = Variable::TABLE . '.' . Variable::FIELD_MINIMUM_ALLOWED_VALUE .
			' as minimumAllowedValueInCommonUnit';
		return $qb;
	}
	/**
	 * @return float
	 */
	public function getMaximumAllowedValueAttribute(): ?float{
		$max = $this->maximumAllowedValueInCommonUnit;
		if($max !== null){
			return $max;
		}
		return $this->getCommonUnit()->maximumValue;
	}
	/**
	 * @return float
	 */
	public function getMinimumAllowedValueAttribute(): ?float{
		$min = $this->minimumAllowedValueInCommonUnit;
		if($min !== null){
			return $min;
		}
		return $this->getCommonUnit()->minimumValue;
	}
	/**
	 * @param QMQB $qb
	 * @param $user
	 */
	public static function addSingleUserVariableSelectFields(QMQB $qb, $user){
		if(is_int($user)){
			$user = QMUser::find($user);
		}
		//return; //This prevents us from getting reminders which don't have a user variable yet
		$qb->where(self::TABLE . '.user_id', $user->id);
		$qb->leftJoin('user_variables AS uv', static function(JoinClause $join) use ($user){
			$join->on(self::TABLE . '.variable_id', '=', 'uv.variable_id')->where('uv.user_id', '=', $user->id);
		});
		self::addUserVariableColumns($qb);
	}
	/**
	 * @param QMQB $qb
	 */
	protected static function addUserVariableColumns(QMQB $qb){
		$userVariableFields = QMUserVariable::getColumns();
		foreach($userVariableFields as $userVariableField){
			if(stripos($userVariableField, 'last_value') !== false ||
				stripos($userVariableField, 'daily_value') !== false){
				$string = 'uv.' . $userVariableField . ' as ' . QMStr::camelize($userVariableField);
				$string .= 'InCommonUnit';
				$qb->columns[] = $string;
			}
		}
		$qb->columns[] = 'uv.default_unit_id as userUnitId';
		$qb->columns[] = 'uv.filling_value as userVariableFillingValue';
		$qb->columns[] = 'uv.id as userVariableId';
		$qb->columns[] = 'uv.variable_category_id as userVariableVariableCategoryId';
		$qb->columns[] = 'uv.number_of_measurements as numberOfMeasurements';
		$qb->columns[] = 'uv.number_of_unique_values as userNumberOfUniqueValues';
		$qb->columns[] = 'uv.valence as userVariableValence';
		$qb->columns[] = 'uv.' . UserVariable::FIELD_OPTIMAL_VALUE_MESSAGE . ' as userOptimalValueMessage';
		$qb->columns[] = 'uv.' . UserVariable::FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID . ' as bestUserVariableRelationshipId';
		$qb->columns[] = 'uv.' . UserVariable::FIELD_MEAN . ' as meanInCommonUnit';
		$qb->columns[] = 'uv.' . UserVariable::FIELD_MAXIMUM_RECORDED_VALUE . ' as maximumRecordedValueInCommonUnit';
		$qb->columns[] = 'uv.' . UserVariable::FIELD_MINIMUM_RECORDED_VALUE . ' as minimumRecordedValueInCommonUnit';
	}
	/**{
	 * @param QMTrackingReminder[] $reminders
	 * @return QMTrackingReminder[]
	 */
	private static function populateFrequencyTextDescriptions(array $reminders): array{
		$dailyReminderTimes = null;
		$numberOfDailyTimes = [];
		$time = time(); // Faster than calling time() a million times
		foreach($reminders as $tr){
			$numberOfDailyTimes[$tr->variableId] = 0;
			if($tr->reminderFrequency < 1){
				$tr->nextReminderTimeEpochSeconds = null;
				continue;
			}
			$tr->reminderStartEpochSeconds = strtotime($tr->startTrackingDate . 'T' . $tr->reminderStartTime);
			$tr->nextReminderTimeEpochSeconds = $tr->reminderStartEpochSeconds;
			while($tr->nextReminderTimeEpochSeconds < $time){
				$tr->nextReminderTimeEpochSeconds += $tr->reminderFrequency;
			}
		}
		foreach($reminders as $tr){
			if($tr->reminderFrequency === 86400){
				$numberOfDailyTimes[$tr->variableId]++;
				$dailyReminderTimes[$tr->variableId][$numberOfDailyTimes[$tr->variableId]] = $tr->reminderStartTime;
			}
		}
		foreach($reminders as $tr){
			if($numberOfDailyTimes[$tr->variableId] === 3){
				//$trackingReminder->frequencyTextDescription = 'three times a day';
				$tr->firstDailyReminderTime = $dailyReminderTimes[$tr->variableId][1];
				$tr->secondDailyReminderTime = $dailyReminderTimes[$tr->variableId][2];
				$tr->thirdDailyReminderTime = $dailyReminderTimes[$tr->variableId][3];
				continue;
			}
			if($numberOfDailyTimes[$tr->variableId] === 2){
				//$trackingReminder->frequencyTextDescription = 'two times a day';
				$tr->firstDailyReminderTime = $dailyReminderTimes[$tr->variableId][1];
				$tr->secondDailyReminderTime = $dailyReminderTimes[$tr->variableId][2];
				continue;
			}
			if($numberOfDailyTimes[$tr->variableId] === 1){
				//$trackingReminder->frequencyTextDescription = 'once a day';
				$tr->firstDailyReminderTime = $dailyReminderTimes[$tr->variableId][1];
			}
		}
		foreach($reminders as $tr){
			$tr->setValueFrequency();
		}
		return $reminders;
	}
	/**
	 * @param QMUser $user
	 * @param QMTrackingReminder[] $trackingReminders
	 * @return QMTrackingReminder[]
	 */
	private static function populateLocalDailyReminderNotificationTimes(QMUser $user, array $trackingReminders): array{
		$localDailyReminderNotificationTimesForAllReminders = [];
		foreach($trackingReminders as $trackingReminder){
			if(!$trackingReminder->reminderStartTimeLocal){
				le("reminderStartTimeLocal not set!");
			}
			/** @var QMTrackingReminder $trackingReminder */
			if(!isset($user->timeZoneOffset)){
				$trackingReminder->internalErrorMessage = 'User time zone is not defined!  Please include ' .
					' timeZoneOffset (in minutes) in body of reminder creation requests.';
				continue;
			}
			if(!$trackingReminder->reminderFrequency){
				$trackingReminder->internalErrorMessage = 'Reminder frequency not set';
				continue;
			}
			if($trackingReminder->reminderFrequency > 86400){
				$trackingReminder->internalErrorMessage = 'Reminder interval exceeds one day';
				continue;
			}
			$trackingReminder->localDailyReminderNotificationTimes = [];
			if($trackingReminder->reminderFrequency === 86400){
				if($trackingReminder->reminderStartTimeLocal > $user->earliestReminderTime &&
					$trackingReminder->reminderStartTimeLocal < $user->latestReminderTime){
					$trackingReminder->localDailyReminderNotificationTimes[] =
						$trackingReminder->reminderStartTimeLocal;
					$localDailyReminderNotificationTimesForAllReminders[] = $trackingReminder->reminderStartTimeLocal;
				} else{
					$trackingReminder->internalErrorMessage = 'reminderStartTimeLocal is less than ' .
						' $user->earliestReminderTime or greater than  $user->latestReminderTime';
				}
				continue;
			}
			$seconds = 0;
			$currentReminderTimeSecondsUtc = $user->convertLocalTimeStringToUtcSeconds('00:00:00');
			while($seconds < 86400){
				$currentReminderTimeStringLocal = $user->utcToLocalHis($currentReminderTimeSecondsUtc);
				if($currentReminderTimeStringLocal >= $user->earliestReminderTime &&
					$currentReminderTimeStringLocal <= $user->latestReminderTime){
					$localDailyNotificationTime = $user->utcToLocalHis($currentReminderTimeSecondsUtc);
					$trackingReminder->localDailyReminderNotificationTimes[] = $localDailyNotificationTime;
					$localDailyReminderNotificationTimesForAllReminders[] = $localDailyNotificationTime;
				}
				$currentReminderTimeSecondsUtc += $trackingReminder->reminderFrequency;
				$seconds += $trackingReminder->reminderFrequency;
			}
		}
		$localDailyReminderNotificationTimesForAllReminders =
			array_unique($localDailyReminderNotificationTimesForAllReminders);
		$localDailyReminderNotificationTimesForAllReminders =
			array_values($localDailyReminderNotificationTimesForAllReminders);
		usort($localDailyReminderNotificationTimesForAllReminders, function($a, $b){
			return strcmp(strtolower($a), strtolower($b));
		});
		foreach($trackingReminders as $trackingReminder){
			if(!isset($user->timeZoneOffset) || $user->timeZoneOffset === null){
				$trackingReminder->internalErrorMessage = 'User time zone is not defined! ' .
					' Please include timeZoneOffset (in minutes) in body of reminder creation requests.';
				continue;
			}
			$trackingReminder->localDailyReminderNotificationTimesForAllReminders =
				$localDailyReminderNotificationTimesForAllReminders;
		}
		return $trackingReminders;
	}
	/**
	 * @return array
	 */
	protected static function getLegacyProperties(): array{
		// legacy => current
		return [];
	}
	/**
	 * @return QMQB
	 */
	public static function getBaseSelectQuery(): QMQB{
		$db = ReadonlyDB::db();
		$fields = static::getSelectColumns(self::TABLE);
		$qb = $db->table(self::TABLE)->select($fields);
		$qb->columns[] = self::TABLE . '.id';
		return $qb;
	}
	/**
	 * @param bool $instantiate
	 * @return QMQB
	 */
	public static function qb(bool $instantiate = false): QMQB{
		$qb = self::getBaseSelectQuery();
		self::addRelatedTablesToQuery($qb);
		if($instantiate){
			$qb->class = self::class;
		}
		return $qb;
	}
	/**
	 * @param QMQB $qb
	 * @return QMQB
	 */
	public static function addUserSelectFields(QMQB $qb): QMQB{
		$qb->columns[] = User::TABLE . '.' . User::FIELD_DISPLAY_NAME . " as userDisplayName";
		$qb->columns[] = User::TABLE . '.' . User::FIELD_LATEST_REMINDER_TIME . " as latestReminderTime";
		$qb->columns[] = User::TABLE . '.' . User::FIELD_EARLIEST_REMINDER_TIME . " as earliestReminderTime";
		$qb->columns[] = User::TABLE . '.' . User::FIELD_TIME_ZONE_OFFSET . " as timeZoneOffset";
		return $qb;
	}
	/**
	 * @param array $requestParams
	 * @param QMQB $qb
	 */
	private static function addQueryClauses(array $requestParams, QMQB $qb){
		$aliasToFieldNameMap = [
			//'clientId' => self::TABLE.'.client_id',  // This causes problems because we include the client id informationally
			'createdAt' => self::TABLE . '.created_at',
			'deletedAt' => self::TABLE . '.deleted_at',
			'email' => self::TABLE . '.email',
			'id' => self::TABLE . '.id',
			'lastTracked' => self::TABLE . '.last_tracked',
			'latestTrackingReminderNotificationReminderTime' => self::TABLE .
				'.latest_tracking_reminder_notification_notify_at',
			'latestTrackingReminderNotificationNotifyAt' => self::TABLE .
				'.latest_tracking_reminder_notification_notify_at',
			'notificationBar' => self::TABLE . '.notification_bar',
			'popUp' => self::TABLE . '.pop_up',
			'reminderFrequency' => self::TABLE . '.reminder_frequency',
			'sms' => self::TABLE . '.sms',
			'startTrackingDate' => self::TABLE . '.' . self::FIELD_START_TRACKING_DATE,
			'stopTrackingDate' => self::TABLE . '.stop_tracking_date',
			'trackingReminderImageUrl' => self::TABLE . '.image_url',
			'updatedAt' => self::TABLE . '.updated_at',
			'variableCategoryId' => Variable::TABLE . '.variable_category_id',
			'variableId' => self::TABLE . '.variable_id',
			'variableName' => Variable::TABLE . '.name',
		];
		QueryBuilderHelper::applyFilterParamsIfExist($qb, $aliasToFieldNameMap, $requestParams);
		if(!isset($requestParams['limit'])){
			$requestParams['limit'] = 200;
		}
		QueryBuilderHelper::applyOffsetLimitSort($qb, $requestParams, $aliasToFieldNameMap);
	}
	/**
	 * @param QMUserVariable[] $paymentUserVariables
	 */
	public static function createRemindersForNewPaymentVariables(array $paymentUserVariables){
		foreach($paymentUserVariables as $paymentUserVariable){
			$moreThanAMonthOld = $paymentUserVariable->latestTaggedMeasurementMoreThanAMonthAgo();
			if($moreThanAMonthOld){
				continue;
			}
			if(!$paymentUserVariable->isFood() && !$paymentUserVariable->isTreatment()){
				continue;
			}
			if($paymentUserVariable->isPaymentVariable()){
				$nonPaymentVariable = $paymentUserVariable->getNonPaymentVariable();
				if($nonPaymentVariable->createdMoreThanXSecondsAgo(5 * 60)){
					continue;
				}
				$numberOfExistingReminders = $nonPaymentVariable->getOrCalculateNumberOfTrackingReminders();
				if($numberOfExistingReminders){
					continue;
				}
				$nonPaymentVariable->createTrackingReminder();
			}
		}
	}
	/**
	 * @param QMQB $qb
	 */
	private static function addRelatedTablesToQuery(QMQB $qb): void{
		self::addCommonVariableSelectFields($qb);
		self::addTrackingReminderSelectFields($qb);
		$qb->join(User::TABLE, self::TABLE . '.user_id', '=', User::TABLE . '.ID');
		self::addUserSelectFields($qb);
	}
	/**
	 * @param array $params
	 * @return array
	 * @throws BadRequestException
	 */
	private static function formatAndValidateParams(array $params): array{
		$allowedParams = [
			'clientId',
			'variableId',
			'variableName',
			'updatedAt',
			'createdAt',
			'variableCategoryId',
			'variableCategoryName',
			'id',
			'reminderFrequency',
			'trackingReminderImageUrl',
		];
		$params = QMStr::properlyFormatRequestParams($params);
		QMAPIValidator::validateParams($allowedParams, array_keys($params), 'trackingReminder/trackingReminder_get');
		return $params;
	}
	/**
	 * @return QMTrackingReminderNotification[]
	 */
	public static function handleDeleteRequest(): array{
		$body = QMSlim::getInstance()->getRequestJsonBodyAsArray(false);
		if(isset($body['id'])){
			$trackingReminderId = $body['id'];
		}
		if(QMSlim::getInstance()->request()->get('id')){
			$trackingReminderId = QMSlim::getInstance()->request()->get('id');
		}
		if(!isset($trackingReminderId)){
			throw new BadRequestException('Please supply the tracking reminder id.');
		}
		self::deleteTrackingReminder(QMAuth::id(), $trackingReminderId);
		return QMTrackingReminderNotification::getPastQMTrackingReminderNotifications();
	}
	/**
	 * @return string
	 */
	public function getTitleAttribute(): string{
		return $this->title ?? $this->setTitle();
	}
	/**
	 * @return int
	 */
	public function getReminderFrequencyAttribute(): ?int{
		$freq = $this->reminderFrequency;
		return $freq;
	}
	/**
	 * @return string
	 */
	public function getStopTrackingDate(): ?string{
		return $this->stopTrackingDate;
	}
	/**
	 * @return string
	 */
	public function getStartTrackingDate(): ?string{
		return $this->startTrackingDate;
	}
	/**
	 * @return string
	 */
	public function getLatestNotificationAt(): ?string{
		if(!$this->isEnabled()){
			return $this->latestTrackingReminderNotificationReminderTime = null;
		}
		$at = TrackingReminderNotification::whereTrackingReminderId($this->getTrackingReminderId())
			->max(QMTrackingReminderNotification::FIELD_NOTIFY_AT);
		if(!$at){
			$this->logInfo("No existing notifications");
			return $this->latestTrackingReminderNotificationReminderTime = null;
		}
		$this->latestTrackingReminderNotificationReminderTime = strtotime($at);
		return $this->latestTrackingReminderNotificationNotifyAt = $at;
	}
	/**
	 * @return string
	 */
	public function getUtcHis(): string{
		return $this->reminderStartTime;
	}
	/**
	 * @return string
	 */
	public function getLocalHis(): string{
		$t = $this->reminderStartTime;
		return $this->getQMUser()->utcToLocalHis($t);
	}
	/**
	 * @return int
	 */
	public function getUserId(): ?int{
		if($this->userId){
			return $this->userId;
		}
		return QMAuth::id();
	}
	/**
	 * @return float
	 */
	public function getDefaultValueInUserUnit(): ?float{
		return $this->defaultValueInUserUnit ?: $this->setDefaultValueInUserUnit();
	}
	/**
	 * @return float
	 */
	public function setDefaultValueInUserUnit(): ?float{
		$inCommonUnit = $this->getDefaultValueInCommonUnit();
		if($inCommonUnit === null){
			return $this->defaultValueInUserUnit = null;
		}
		$userOrCommonUnit = $this->getUserUnitOrFallbackToCommon();
		if(!$userOrCommonUnit){
			$this->logError("Could not get unit!");
			return $this->defaultValueInUserUnit = $this->defaultValue;
		}
		$commonUnit = $this->getCommonUnit();
		try {
			$commonUnit->throwExceptionIfValueNotValidForUnit($inCommonUnit, $this);
		} catch (InvalidVariableValueException $e) {
			$this->logErrorOrInfoIfTesting("Default value $inCommonUnit $commonUnit->abbreviatedName not valid
            \n for unit so setting defaultValueInUserUnit = null. \nInvalidVariableValueException: " .
				$e->getMessage());
			return $this->defaultValueInUserUnit = null;
		}
		$inUserUnit = $this->toUserUnit($inCommonUnit);
		return $this->defaultValueInUserUnit = $inUserUnit;
	}
	/**
	 * @return string
	 */
	public function setValueFrequency(): string{
		if($this->reminderFrequency){
			$this->setValueFrequencyForFrequencies();
		} else{
			$this->setValueFrequencyForZeroFrequency();
		}
		$this->capitalizeValueAndFrequencyTextDescriptions();
		return $this->valueAndFrequencyTextDescription;
	}
	/**
	 * @return float
	 */
	public function setDefaultValueToDefaultValueInUserUnit(): ?float{
		return $this->defaultValue = $this->getDefaultValueInUserUnit();
	}
	/**
	 * @return string
	 */
	protected function setTitle(): string{
		$title = $this->getOrSetVariableDisplayName();
		if($this->getDefaultValueInUserUnit() !== null && !$this->isVitalSign()){
			$title = $this->getDefaultValueInUserUnit() . " " . strtolower($this->unitAbbreviatedName) . " " .
				$this->getOrSetVariableDisplayName();
		}
		if($this->isRating()){
			$title = "Rate " . $this->getOrSetVariableDisplayName();
		}
		if($this->isYesNoOrCountWithOnlyOnesAndZeros()){
			$title = $this->getOrSetVariableDisplayName();
		}
		if(stripos($title, '(yes/no)') !== false){
			le("Bad title $title");
		}
		return $this->title = $title;
	}
	/**
	 * @param string|null $imageUrl
	 * @param bool $updateIfDifferent
	 * @return string
	 */
	public function setImageUrl(string $imageUrl = null, bool $updateIfDifferent = false): string{
		if($imageUrl){
			$this->imageUrl = $imageUrl;
			return $imageUrl;
		}
		if(isset($this->variableImageUrl)){
			$this->imageUrl = $this->variableImageUrl;
		}
		if($this->trackingReminderImageUrl && strpos($this->trackingReminderImageUrl, 'Not Found') === false){
			$this->imageUrl = $this->trackingReminderImageUrl;
		}
		if(!$this->imageUrl){
			$this->imageUrl = $this->getQMVariableCategory()->getImageUrl();
		}
		return $this->imageUrl;
	}
	/**
	 * @return string
	 */
	public function getTrackingReminderImageUrl(): string{
		return $this->trackingReminderImageUrl;
	}
	/**
	 * @return int
	 * @throws UserNotFoundException
	 */
	public function createNotifications(): ?int{
		return $this->l()->createNotifications();
	}
	private function setValueFrequencyForNonRating(){
		$this->valueAndFrequencyTextDescription = $this->frequencyTextDescription;
		if($this->getDefaultValueInUserUnit() !== null){
			$this->valueAndFrequencyTextDescription =
				$this->getDefaultValueInUserUnit() . ' ' . $this->unitAbbreviatedName . ' ' .
				$this->frequencyTextDescription;
		}
		$this->valueAndFrequencyTextDescriptionWithTime = $this->frequencyTextDescriptionWithTime;
		if($this->getDefaultValueInUserUnit() !== null){
			$this->valueAndFrequencyTextDescriptionWithTime =
				$this->getDefaultValueInUserUnit() . ' ' . $this->unitAbbreviatedName . ' ' .
				$this->frequencyTextDescriptionWithTime;
		}
	}
	private function setValueFrequencyForRatingOrYesNo(){
		$prefix = 'Rate ';
		if($this->isYesNoOrCountWithOnlyOnesAndZeros()){
			$prefix = '';
		}
		$freq = $this->getReminderFrequencyAttribute();
		if($freq){
			$this->valueAndFrequencyTextDescription =
				$prefix . 'every ' . TimeHelper::convertSecondsToHumanString($freq);
			$this->valueAndFrequencyTextDescriptionWithTime = $this->valueAndFrequencyTextDescription;
		}
		if($freq === 86400){
			$this->valueAndFrequencyTextDescription = $prefix . 'daily';
			$this->valueAndFrequencyTextDescriptionWithTime =
				$prefix . 'daily at ' . $this->reminderStartTimeLocalHumanFormatted;
		}
		if($freq > 86400){
			$this->valueAndFrequencyTextDescriptionWithTime =
				$this->valueAndFrequencyTextDescription . ' at ' . $this->reminderStartTimeLocalHumanFormatted;
		}
	}
	/**
	 * @return string
	 */
	private function setValueFrequencyForEndedReminders(): string{
		$at = $this->getStopTrackingDate();
		if(empty($at)){
			le('empty($at)');
		}
		$suffix = ' (ended ' . $at . ')';
		$this->frequencyTextDescription .= $suffix;
		$this->frequencyTextDescriptionWithTime .= $suffix;
		$this->valueAndFrequencyTextDescription .= $suffix;
		$this->valueAndFrequencyTextDescriptionWithTime .= $suffix;
		return $suffix;
	}
	private function setValueFrequencyForNotStartedYet(){
		$suffix = ' (starts ' . $this->startTrackingDate . ')';
		$this->frequencyTextDescription .= $suffix;
		$this->frequencyTextDescriptionWithTime .= $suffix;
		$this->valueAndFrequencyTextDescription .= $suffix;
		$this->valueAndFrequencyTextDescriptionWithTime .= $suffix;
	}
	private function setValueFrequencyForZeroFrequency(){
		$this->frequencyTextDescription = 'never';
		$this->frequencyTextDescriptionWithTime = 'never';
		$this->valueAndFrequencyTextDescription = 'Favorite';
		$this->valueAndFrequencyTextDescriptionWithTime = 'Favorite';
		if($this->variableCategoryName === "Treatments"){
			$this->valueAndFrequencyTextDescription = 'As-Needed';
			$this->valueAndFrequencyTextDescriptionWithTime = 'As-Needed';
		}
	}
	private function capitalizeValueAndFrequencyTextDescriptions(){
		$this->frequencyTextDescription = ucfirst($this->frequencyTextDescription);
		$this->frequencyTextDescriptionWithTime = ucfirst($this->frequencyTextDescriptionWithTime);
		$this->valueAndFrequencyTextDescription = ucfirst($this->valueAndFrequencyTextDescription);
		$this->valueAndFrequencyTextDescriptionWithTime = ucfirst($this->valueAndFrequencyTextDescriptionWithTime);
	}
	public function getFrequencyDescription(): string{
		if(!$this->frequencyTextDescription){
			$this->setValueFrequency();
		}
		return $this->frequencyTextDescription;
	}
	private function setValueFrequencyForFrequencies(){
		$freq = $this->getReminderFrequencyAttribute();
		$this->frequencyTextDescription = 'every ' . TimeHelper::convertSecondsToHumanString($freq);
		$this->frequencyTextDescriptionWithTime = $this->frequencyTextDescription;
		if($freq === 86400){
			$this->frequencyTextDescription = 'daily';
			$this->frequencyTextDescriptionWithTime =
				$this->frequencyTextDescription . ' at ' . $this->reminderStartTimeLocalHumanFormatted;
		}
		if(!$this->isRating()){
			$this->setValueFrequencyForNonRating();
		}
		if(isset($this->reminderStartTimeLocalHumanFormatted) &&
			($this->isRating() || $this->isYesNoOrCountWithOnlyOnesAndZeros())){
			$this->setValueFrequencyForRatingOrYesNo();
		}
		if($this->hasEnded()){
			$this->setValueFrequencyForEndedReminders();
		}
		if($this->hasNotStarted()){
			$this->setValueFrequencyForNotStartedYet();
		}
	}
	/**
	 * @return bool
	 */
	public function hasEnded(): bool{
		$at = $this->stopTrackingDate;
		if(!$at){
			return false;
		}
		return time_or_exception($at) < time();
	}
	/**
	 * @return bool
	 */
	public function hasNotStarted(): bool{
		$at = $this->startTrackingDate;
		if(!$at){
			return false;
		}
		return time_or_exception($at) > time();
	}
	/**
	 * @return string
	 */
	public function setReminderStartTimeLocal(): string{
		$time = $this->reminderStartTime;
		if(!$time){
			le("Please set reminderStartTime");
		}
		$u = $this->getQMUser();
		return $this->reminderStartTimeLocal = $u->utcToLocalHis($this->reminderStartTime);
	}
	/**
	 * @return string
	 */
	public function setReminderStartTimeLocalHumanFormatted(): string{
		$time = $this->reminderStartTime;
		if(!$time){
			le("Please set reminderStartTime");
		}
		$u = $this->getQMUser();
		return $this->reminderStartTimeLocalHumanFormatted = $u->getHourAmPm($time);
	}
	/**
	 * @return int
	 */
	public function deleteNotifications(): int{
		$deleted = QMTrackingReminderNotification::writable()
			->where(QMTrackingReminderNotification::FIELD_TRACKING_REMINDER_ID, $this->getId())
			->hardDelete(__METHOD__, false);
		$this->updateDbRow([TrackingReminder::FIELD_LATEST_TRACKING_REMINDER_NOTIFICATION_NOTIFY_AT => null]);
		$this->latestTrackingReminderNotificationReminderTime = null;
		return $deleted;
	}
	/**
	 * @param QMUserVariable|null $userVariable
	 */
	public function setQMUserVariable(QMUserVariable $userVariable): void{
		$this->userVariable = $userVariable;
	}
	/**
	 * @param QMQB $qb
	 */
	protected function addUniqueWhereClauses($qb){
		$qb->where(static::FIELD_ID, $this->getId());
	}
	/**
	 * @return int
	 */
	public function getId(): int{
		if($this->trackingReminderId){
			$this->id = $this->trackingReminderId;
		}
		return $this->id;
	}
	/**
	 * @param int $id
	 * @return int
	 */
	public function setId($id): int{
		return $this->id = $this->trackingReminderId = $id;
	}
	/**
	 * @return QMButton[]
	 */
	public function setDefaultButtons(): array{
		$buttons = $this->buttons ?: [];
		$buttons = array_merge($buttons, $this->getUserVariableButtons(true));
		$buttons = array_merge($buttons, $this->getCommonVariableButtons(true));
		$buttons = array_merge($buttons, $this->getTrackingReminderButtons());
		return $this->buttons = $buttons;
	}
	/**
	 * @return array
	 */
	public function getTrackingReminderButtons(): array{
		if(isset(self::$trackingReminderButtons[$this->getTrackingReminderId()])){
			return self::$trackingReminderButtons[$this->getTrackingReminderId()];
		}
		$buttons = [];
		$params = [
			'trackingReminderId' => $this->getTrackingReminderId(),
			'reminderFrequency' => $this->getReminderFrequencyAttribute(),
			'stopTrackingDate' => $this->getStopTrackingDate(),
			'startTrackingDate' => $this->getStartTrackingDate(),
			'defaultValue' => $this->getDefaultValueInUserUnit(),
		];
		$button = new ReminderAddStateButton($this, $params);
		$button->setTextAndTitle("Edit Reminder");
		$buttons[] = $button;
		self::$trackingReminderButtons[$this->getTrackingReminderId()] = $buttons;
		return $buttons;
	}
	/**
	 * @return int
	 */
	public function getTrackingReminderId(): int{
		return $this->getId();
	}
	protected function setImagesAndIcons(){
		$this->setIonIcon();
		$this->setSvgUrl();
		$this->getPngUrl();
		$this->setImageUrl();
	}
	protected function getQuestions(){
		$this->getQuestion();
		$this->getLongQuestion();
	}
	protected function makeSureNotAnActivity(){
		$n = $this->getVariableName();
		if(strpos($n, ' Activities') !== false){
			$this->logError("we should not have reminders for Activities but we have one for " . "$n created " .
				$this->getCreatedAt() . " by client " . $this->getClientId());
			//$model->getTrackingReminder()->delete("we should not have reminders for Activities");
		}
	}
	/**
	 * @return string
	 */
	public function getPHPUnitJobTest(): ?string{
		$functions = 'TrackingReminderNotification::getById(' . $this->getId() . ')->sendPushNotifications();';
		$testName = "PushNotificationsUser" . $this->getUserId() . 'Variable' . $this->getVariableIdAttribute();
		return StagingJobTestFile::getUrl($testName, $functions,
			\App\Slim\Model\Reminders\QMTrackingReminderNotification::class);
	}
	/**
	 * @return float|null
	 */
	public function getDefaultValueInCommonUnit(): ?float{
		return $this->defaultValue;
	}
	/**
	 * @return array
	 */
	public function getLastValuesInUserUnit(): array{
		$lastValues = parent::getLastValuesInUserUnit();
		$default = $this->getDefaultValueInUserUnitIfValid();
		if($default !== null){
			array_unshift($lastValues, $default);
			$lastValues = QMArr::uniqueFloats($lastValues);
		}
		return $this->lastValuesInUserUnit = $lastValues;
	}
	/**
	 * @return QMTrackingReminderNotification[]
	 * @throws \App\Exceptions\UserNotFoundException
	 * @throws \App\Exceptions\UserNotFoundException
	 */
	public function getOrCreateNotifications(): array{
		$rel = $this->l()->tracking_reminder_notifications();
		$n = $rel->get();
		if(!$n->count()){
			$this->createNotifications();
			$n = $rel->get();
			if(!$n->count()){
				le("create didn't work!");
			}
		}
		return QMTrackingReminderNotification::toDBModels($n);
	}
	/**
	 * @return QMTrackingReminderNotification[]
	 * @throws \App\Exceptions\UserNotFoundException
	 */
	public function recreateNotifications(): array{
		$deleted = $this->deleteNotifications();
		$this->logInfo("Deleted $deleted notifications");
		$notifications = $this->getOrCreateNotifications();
		return $notifications;
	}
	/**
	 * @param array $uniqueParams
	 * @return static
	 */
	public static function getOrCreate(array $uniqueParams): QMTrackingReminder{
		$u = QMUser::find($uniqueParams['userId']);
		$models = self::getTrackingReminders($u, $uniqueParams);
		if($models){
			return $models[0];
		}
		$uniqueParams[TrackingReminder::FIELD_USER_ID] = $u->getId();
		$new = TrackingReminder::fromData($uniqueParams);
		return $new->getDBModel();
	}
	/**
	 * @return QMTrackingReminderNotification[]
	 */
	public function getNotifications(): array{
		if($this->trackingReminderNotifications !== null){
			return $this->trackingReminderNotifications;
		}
		$arr = QMTrackingReminderNotification::getTrackingReminderNotifications($this->getQMUser(),
			[QMTrackingReminderNotification::FIELD_TRACKING_REMINDER_ID => $this->getId()]);
		return $this->trackingReminderNotifications = $arr;
	}
	/**
	 * @param array|Collection $array
	 * @return QMTrackingReminder[]
	 */
	public static function instantiateNonDBRows($array): array{
		return parent::instantiateNonDBRows($array);
	}
	/**
	 * @param string $reason
	 * @param bool $countFirst
	 * @return int
	 */
	public function hardDelete(string $reason, bool $countFirst = false): int{
		$this->logError("Hard-deleting $this->variableName reminder because $reason");
		QMTrackingReminderNotification::writable()
			->where(QMTrackingReminderNotification::FIELD_TRACKING_REMINDER_ID, $this->getId())
			->hardDelete($reason, $countFirst);
		return static::writable()->where(static::FIELD_ID, $this->getId())->hardDelete($reason, $countFirst);
	}
	/**
	 * @return TrackingReminder
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function l(){
		return $this->firstOrNewLaravelModel();
	}
	/**
	 * @return TrackingReminder
	 * @noinspection PhpDocSignatureInspection
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function firstOrNewLaravelModel(){
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return parent::firstOrNewLaravelModel();
	}
	public function getQMUserVariable(): QMUserVariable{
		$uv = $this->userVariable;
		if(!$uv){
			$uv = parent::findQMUserVariable($this->getUserId());
			if(!$uv instanceof QMUserVariable){
				le('!$uv instanceof QMUserVariable');
			}
			$this->setQMUserVariable($uv);
		}
		if(!$uv instanceof QMUserVariable){
			le('!$uv instanceof QMUserVariable');
		}
		return $uv;
	}
	public function validateId(){
		if(!$this->id){
			le('!$this->id');
		}
		if(!$this->trackingReminderId){
			le('!$this->trackingReminderId');
		}
		if($this->trackingReminderId !== $this->id){
			le('$this->trackingReminderId !== $this->id');
		}
	}
	public function isActive(): bool{
		if(!$this->reminderFrequency){
			return false;
		}
		$start = $this->getStartTrackingDate();
		if($start && $start > date('Y-m-d')){
			return false;
		}
		$end = $this->getStopTrackingDate();
		if($end && $end < date('Y-m-d')){
			return false;
		}
		return true;
	}
	public static function getRequiredPropertyNames(): array{
		$arr = parent::getRequiredPropertyNames();
		$arr[] = 'userVariableId';
		return $arr;
	}
	public function populateDefaultFields(): void{
		$this->setUserUnit($this->userUnitId ?? $this->unitId ?? $this->commonUnitId);
		$this->setNameAndDisplayName();
		$this->setLocalTimes();
		$this->setImagesAndIcons();
		$this->setDefaultValueToDefaultValueInUserUnit();
		$this->convertValuesToUserUnit();
		$this->getNotificationActionButtons();
		if($this->laravelModel){
			$this->setOutcomeFromLaravelModelIfNecessary();
		}
		$this->getQuestions();
		$this->addToMemory();
		SolutionButton::addIfDebugMode($this->getTitleAttribute(), 
		                               "trackingReminders?userId=" . $this->getUserId());
		$this->makeSureNotAnActivity();
		TimeHelper::convertAllDateTimeValuesToRFC3339($this);
		$this->getOrSetCombinationOperation();
		$this->getInputType();
		$this->setVariableCategory($this->getVariableCategoryId());
		$this->validateUnit();
		$this->setValueFrequency();
		$this->getNotificationActionButtons();
		if(empty($this->inputType)){
			le('empty($this->inputType)');
		}
	}
	/**
	 * @return float|null
	 */
	public function getDefaultValueInCommonUnitIfValid(): ?float{
		return VariableDefaultValueProperty::calculate($this);
	}
	/**
	 * @return float|null
	 */
	public function getDefaultValueInUserUnitIfValid(): ?float{
		$val = $this->getDefaultValueInCommonUnitIfValid();
		if($val === null){
			return null;
		}
		return $this->toUserUnit($val);
	}
	/**
	 * @param TrackingReminder $r
	 */
	public function populateByLaravelTrackingReminder(TrackingReminder $r): void{
		$arr = $r->attributesToArray();
		foreach($arr as $key => $value){
			$this->setAttributeIfNotSet($key, $value);
		}
		if(is_array($this->userVariable)){
			le('is_array($this->userVariable)');
		}
	}
	/**
	 * @param TrackingReminder $reminder
	 * @noinspection PhpDocSignatureInspection
	 */
	public function populateByLaravelModel(BaseModel $r){
		/** @var TrackingReminder $r */
		$v = $r->getVariable();
		$uv = $r->getUserVariable();
		$this->setVariableName($v->name);
		$this->userUnitId = $uv->default_unit_id;
		$this->unitId = $this->userUnitId ?? $v->default_unit_id;
		if($r->id){
			$this->trackingReminderId = $r->id;
		}
		$this->setLaravelModel($r);
		if(is_array($this->userVariable)){
			le('is_array($this->userVariable)');
		}
		$this->populateByLaravelTrackingReminder($r);
		if(is_array($this->userVariable)){
			le('is_array($this->userVariable)');
		}
		$this->populateByLaravelUserVariable($uv);
		if(is_array($this->userVariable)){
			le('is_array($this->userVariable)');
		}
		$this->populateByLaravelVariable($v);
		$this->populateDefaultFields();
		if($r->hasId()){
			$this->addToMemory();
		}
		$this->validateId();
	}
	public function firstOrCreateNotification(): TrackingReminderNotification{
		return $this->l()->firstOrCreateNotification();
	}
	protected function setOutcomeFromLaravelModelIfNecessary(): void{
		if($this->outcome === 0){
			$this->setOutcome(false);
		}
		if($this->outcome === 1){
			$this->setOutcome(true);
		}
		if(!is_bool($this->outcome)){
			$uv = $this->getUserVariable();
			$outcome = $uv->getOutcomeAttribute();
			if($outcome !== null){
				$this->setOutcome($outcome);
			}
		}
	}
	public function getUrlParams(): array{
		$params = parent::getUrlParams();
		$params['tracking_reminder_id'] = $this->getTrackingReminderId();
		return $params;
	}
	public function getVariableCategoryId(): int{
		return $this->userVariableVariableCategoryId ?? $this->variableCategoryId;
	}
	public function getVariableIdAttribute(): ?int{ return $this->variableId; }
	public function getEditButton(): QMButton{
		return $this->getTrackingReminder()->getEditButton();
	}
	public function getTrackingReminder(): TrackingReminder{
		return $this->l();
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons():array{
		return $this->getTrackingReminder()->getInterestingRelationshipButtons();
	}
	public function getWebhookUrl(): string{
		return PostTrackingReminderController::getUrl($this->getUrlParams());
	}
}
