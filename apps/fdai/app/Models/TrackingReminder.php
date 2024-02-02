<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\DevOps\XDebug;
use App\Properties\User\UserIdProperty;
use App\Storage\DB\Writable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Buttons\QMButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Buttons\RelationshipButtons\TrackingReminder\TrackingReminderTrackingReminderNotificationsButton;
use App\Buttons\RelationshipButtons\TrackingReminder\TrackingReminderUserVariableButton;
use App\Buttons\States\VariableStates\ReminderAddStateButton;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\UserNotFoundException;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\Base\BaseTrackingReminder;
use App\Astral\Filters\ReminderNotificationsEnabledFilter;
use App\Astral\Filters\VariableCategoryFilter;
use App\Astral\TrackingReminderNotificationBaseAstralResource;
use App\Properties\TrackingReminder\TrackingReminderReminderFrequencyProperty;
use App\Properties\TrackingReminder\TrackingReminderReminderStartTimeProperty;
use App\Properties\TrackingReminder\TrackingReminderStartTrackingDateProperty;
use App\Properties\TrackingReminder\TrackingReminderStopTrackingDateProperty;
use App\Properties\TrackingReminder\TrackingReminderVariableIdProperty;
use App\Properties\TrackingReminderNotification\TrackingReminderNotificationNotifyAtProperty;
use App\Properties\User\UserTimezoneProperty;
use App\Properties\UserVariable\UserVariableNumberOfTrackingRemindersProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\View\Request\QMRequest;
use App\Storage\QueryBuilderHelper;
use App\Storage\S3\S3Private;
use App\Traits\HasButton;
use App\Traits\HasDBModel;

use App\Traits\HasModel\HasUserVariable;
use App\Traits\HasModel\HasVariableCategory;
use App\Traits\IsEditable;
use App\Types\QMArr;
use App\Types\TimeHelper;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\QMColor;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Variables\QMUserVariable;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Filters\Filter;
use OpenApi\Annotations as OA;
use Titasgailius\SearchRelations\SearchesRelations;
/**
 * App\Models\TrackingReminder
 * @OA\Schema (
 *      definition="TrackingReminder",
 *      required={"variable_id"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="client_id",
 *          description="client_id",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="user_id",
 *          description="ID of User",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="variable_id",
 *          description="Id for the variable to be tracked",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="default_value",
 *          description="Default value to use for the measurement when tracking",
 *          type="number",
 *          format="float"
 *      ),
 *      @OA\Property(
 *          property="reminder_start_time",
 *          description="Earliest time of day at which reminders should appear",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="reminder_end_time",
 *          description="Latest time of day at which reminders should appear",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="reminder_sound",
 *          description="String identifier for the sound to accompany the reminder",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="reminder_frequency",
 *          description="Number of seconds between one reminder and the next",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="pop_up",
 *          description="True if the reminders should appear as a popup notification",
 *          type="boolean"
 *      ),
 *      @OA\Property(
 *          property="sms",
 *          description="True if the reminders should be delivered via SMS",
 *          type="boolean"
 *      ),
 *      @OA\Property(
 *          property="email",
 *          description="True if the reminders should be delivered via email",
 *          type="boolean"
 *      ),
 *      @OA\Property(
 *          property="notification_bar",
 *          description="True if the reminders should appear in the notification bar",
 *          type="boolean"
 *      ),
 *      @OA\Property(
 *          property="latest_tracking_reminder_notification_notify_at",
 *          description="ISO 8601 timestamp for the reminder time of the latest tracking reminder notification that has
 *     been pre-emptively generated in the database", type="string", format="date-time"
 *      ),
 *      @OA\Property(
 *          property="last_tracked",
 *          description="ISO 8601 timestamp for the last time a measurement was received for this user and variable",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="When the record was first created. Use ISO 8601 datetime format",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="When the record in the database was last updated. Use ISO 8601 datetime format",
 *          type="string",
 *          format="date-time"
 *      )
 * )
 * @property integer $id
 * @property string $client_id
 * @property integer $user_id ID of User
 * @property int $variable_id int(11) NOT NULL COMMENT 'Id for the variable to be tracked'
 * @property number $default_value DOUBLE NULL COMMENT 'Default value to use for the measurement when tracking'
 * @property Carbon $reminder_start_time TIME NULL COMMENT 'Earliest time of day at which reminders should
 *     appear'
 * @property Carbon $reminder_end_time TIME NULL COMMENT 'Latest time of day at which reminders should appear'
 * @property string $reminder_sound VARCHAR(125) NULL COMMENT 'String identifier for the sound to accompany the
 *     reminder'
 * @property int $reminder_frequency INT(11) NULL COMMENT 'Number of seconds between one reminder and the next'
 * @property boolean $pop_up BOOL NULL COMMENT 'True if the reminders should appear as a popup notification'
 * @property boolean $sms BOOL NULL COMMENT 'True if the reminders should be delivered via SMS'
 * @property boolean $email BOOL NULL COMMENT 'True if the reminders should be delivered via email'
 * @property boolean $notification_bar BOOL NULL COMMENT 'True if the reminders should appear in the notification bar'
 * @property Carbon $latest_tracking_reminder_notification_notify_at DATETIME NULL COMMENT 'ISO 8601
 *     timestamp for the reminder time of the latest tracking reminder notification that has been pre-emptively
 *     generated in the database'
 * @property Carbon $last_tracked DATETIME NULL COMMENT 'ISO 8601 timestamp for the last time a measurement was
 *     received for this user and variable'
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|TrackingReminder whereId($value)
 * @method static \Illuminate\Database\Query\Builder|TrackingReminder whereClientId($value)
 * @method static \Illuminate\Database\Query\Builder|TrackingReminder whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|TrackingReminder whereVariableId($value)
 * @method static \Illuminate\Database\Query\Builder|TrackingReminder whereDefaultValue($value)
 * @method static \Illuminate\Database\Query\Builder|TrackingReminder whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|TrackingReminder whereUpdatedAt($value)
 * @property string|null $start_tracking_date Earliest date on which the user should be reminded to track in YYYY-MM-DD
 *     format
 * @property string|null $stop_tracking_date Latest date on which the user should be reminded to track  in YYYY-MM-DD
 *     format
 * @property string|null $instructions
 * @property string|null $deleted_at
 * @property string|null $image_url
 * @property-read User $userId
 * @property-read Variable $variable
 * @method static Builder|TrackingReminder newModelQuery()
 * @method static Builder|TrackingReminder newQuery()
 * @method static Builder|TrackingReminder query()
 * @method static Builder|TrackingReminder whereDeletedAt($value)
 * @method static Builder|TrackingReminder whereEmail($value)
 * @method static Builder|TrackingReminder whereImageUrl($value)
 * @method static Builder|TrackingReminder whereInstructions($value)
 * @method static Builder|TrackingReminder whereLastTracked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TrackingReminder
 *     whereLatestTrackingReminderNotificationReminderTime($value)
 * @method static Builder|TrackingReminder whereNotificationBar($value)
 * @method static Builder|TrackingReminder wherePopUp($value)
 * @method static Builder|TrackingReminder whereReminderEndTime($value)
 * @method static Builder|TrackingReminder whereReminderFrequency($value)
 * @method static Builder|TrackingReminder whereReminderSound($value)
 * @method static Builder|TrackingReminder whereReminderStartTime($value)
 * @method static Builder|TrackingReminder whereSms($value)
 * @method static Builder|TrackingReminder whereStartTrackingDate($value)
 * @method static Builder|TrackingReminder whereStopTrackingDate($value)
 * @method static Builder|TrackingReminder whereUnitId($value)
 * @mixin Eloquent
 * @property int $user_variable_id
 * @method static Builder|TrackingReminder whereAdditionalMetaData($value)
 * @method static Builder|TrackingReminder whereUserVariableId($value)
 * @method static Builder|TrackingReminder whereLatestTrackingReminderNotificationNotifyAt($value)
 * @property-read OAClient $oa_client
 * @property-read User $user
 * @property-read UserVariable $user_variable
 * @property-read Collection|TrackingReminderNotification[] $tracking_reminder_notifications
 * @property-read int|null $tracking_reminder_notifications_count
 * @property-read Unit|null $unit
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|TrackingReminder active()
 * @method static Builder|TrackingReminder withoutFutureNotifications()
 * @property int|null $number_of_tracking_reminder_notifications Number of Tracking Reminder Notifications for this
 *     Tracking Reminder.
 *                     [Formula: update tracking_reminders
 *                         left join (
 *                             select count(id) as total, tracking_reminder_id
 *                             from tracking_reminder_notifications
 *                             group by tracking_reminder_id
 *                         )
 *                         as grouped on tracking_reminders.id = grouped.tracking_reminder_id
 *                     set tracking_reminders.number_of_tracking_reminder_notifications = count(grouped.total)]
 * @method static Builder|TrackingReminder whereNumberOfTrackingReminderNotifications($value)

 * @property-read int $default_unit_id
 * @property mixed $raw
 * @property-read mixed $raw_variable
 * @property-read OAClient $client
 */
class TrackingReminder extends BaseTrackingReminder {
    use HasFactory;

	use HasDBModel;
	use SearchesRelations;
	use HasUserVariable, HasButton, IsEditable, HasVariableCategory;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = 'id';
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'variable' => ['name'],
	];
	/**
	 * The per-page options used the resource index.
	 * @var array
	 */
	public static $perPageOptions = [25, 50, 100];
	public static $group = TrackingReminderNotification::CLASS_CATEGORY;
	public static function getSlimClass(): string{ return QMTrackingReminder::class; }
	public const DEFAULT_LIMIT = 200; // I have over 150 reminders
	public const MAX_LIMIT = 200;
	public const DEFAULT_IMAGE = ImageUrls::BASIC_FLAT_ICONS_BELL;
	public const CLASS_DESCRIPTION = "Manage what variables you want to track and when you want to be reminded. ";
	public const FONT_AWESOME = FontAwesome::BELL;
	public const COLOR = QMColor::HEX_PURPLE;
	public const BUFFER_DAYS_FOR_NOTIFICATIONS = 2;
	public static function getUniqueIndexColumns(): array{
		return [
			self::FIELD_VARIABLE_ID,
			self::FIELD_USER_ID,
			self::FIELD_REMINDER_START_TIME,
			self::FIELD_REMINDER_FREQUENCY,
		];
	}
	protected $with = [
		//'variable:'.Variable::IMPORTANT_FIELDS, // Too complicated and redundant data. Just get relations directly
		//'user_variable:'.UserVariable::IMPORTANT_FIELDS,
		// TODO: Uncomment maybe? 'variable',
		// TODO: Uncomment maybe? 'user_variable',
	];
	protected array $rules = [
		self::FIELD_USER_ID => 'required|numeric|min:1',
		self::FIELD_CLIENT_ID => 'required|max:80',
		self::FIELD_VARIABLE_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_DEFAULT_VALUE => 'nullable|numeric',
		self::FIELD_REMINDER_START_TIME => 'required|string|max:8',
		self::FIELD_REMINDER_END_TIME => 'nullable|string|max:8',
		self::FIELD_REMINDER_SOUND => 'nullable|max:125',
		self::FIELD_REMINDER_FREQUENCY => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_POP_UP => 'nullable|boolean',
		self::FIELD_SMS => 'nullable|boolean',
		self::FIELD_EMAIL => 'nullable|boolean',
		self::FIELD_NOTIFICATION_BAR => 'nullable|boolean',
		self::FIELD_LAST_TRACKED => 'nullable|date',
		self::FIELD_START_TRACKING_DATE => 'nullable|date',
		self::FIELD_STOP_TRACKING_DATE => 'nullable|date',
		self::FIELD_INSTRUCTIONS => 'nullable|max:65535',
		self::FIELD_IMAGE_URL => 'nullable|max:2083',
		self::FIELD_USER_VARIABLE_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_LATEST_TRACKING_REMINDER_NOTIFICATION_NOTIFY_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
	];
	public $table = "tracking_reminders";
	protected array $openApiSchema = [];
	protected $casts = [
        //self::FIELD_LAST_TRACKED => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_LATEST_TRACKING_REMINDER_NOTIFICATION_NOTIFY_AT => 'datetime',
		self::FIELD_ID => 'int',
		self::FIELD_USER_ID => 'int',
		self::FIELD_VARIABLE_ID => 'int',
		self::FIELD_DEFAULT_VALUE => 'float',
		self::FIELD_REMINDER_FREQUENCY => 'int',
		self::FIELD_POP_UP => 'bool',
		self::FIELD_SMS => 'bool',
		self::FIELD_EMAIL => 'bool',
		self::FIELD_NOTIFICATION_BAR => 'bool',
		self::FIELD_USER_VARIABLE_ID => 'int',
		//        self::FIELD_REMINDER_START_TIME => 'datetime:H:i:s',
		//        self::FIELD_REMINDER_END_TIME => 'datetime:H:i:s',
		//        self::FIELD_START_TRACKING_DATE => 'date:Y-m-d',
		//        self::FIELD_STOP_TRACKING_DATE => 'date:Y-m-d',
		self::FIELD_REMINDER_START_TIME => 'string',
		self::FIELD_REMINDER_END_TIME => 'string',
		self::FIELD_START_TRACKING_DATE => 'string',
		self::FIELD_STOP_TRACKING_DATE => 'string',
	];
	public function __construct(array $attributes = []){
		if($attributes){
			//XDebug::break();
		}
		parent::__construct($attributes);
	}
	public function fill(array $attributes){
		if($attributes){
			//XDebug::break();
		}
		return parent::fill($attributes); 
	}
	public function getSubtitleAttribute(): string{
		if(!$this->hasId()){
			return static::CLASS_DESCRIPTION;
		}
		return $this->getValueFrequency();
	}
	public function getEditUrl(array $params = []): string{
		$params[self::FIELD_ID] = $this->getId();
		$b = new ReminderAddStateButton($this->getVariableName());
		return $b->getUrl($params);
	}
	/**
	 * @return QMTrackingReminder
	 */
	public function getDBModel(): DBModel{
		/** @var QMTrackingReminder $dbm */
		if($dbm = $this->getDBModelFromMemory()){
			$uv = QMUserVariable::findInMemory($this->user_variable_id);
			if($uv){ // Sometimes we update the user variable unit
				$dbm->setUserUnit($uv->getUnitIdAttribute());
			}
			$dbm->populateDefaultFields();
			if($dbm->reminderFrequency !== $this->getReminderFrequencyAttribute()){
				le('$dbm->reminderFrequency !== $this->getFrequency()');
			}
			return $dbm;
		}
		$dbm = new QMTrackingReminder();
		$dbm->populateByLaravelModel($this);
		if($dbm->reminderFrequency !== $this->getReminderFrequencyAttribute()){
			le('$dbm->reminderFrequency !== $this->getFrequency()');
		}
		return $dbm;
	}
	/**
	 * @param $data
	 * @param bool $fallback
	 * @return TrackingReminder
	 * @throws \App\Exceptions\IncompatibleUnitException
	 * @throws \App\Exceptions\InvalidVariableValueException
	 * @throws \App\Exceptions\UnauthorizedException
	 */
	public static function upsertOne($data, bool $fallback = false): BaseModel{
		if(AppMode::isApiRequest()){
			$u = QMAuth::getQMUser()->l();
			$tz = UserTimezoneProperty::pluckOrDefault($data);
			if(!empty($tz) && $tz !== $u->timezone){
				$u->timezone = $tz;
				try {
					$u->save();
				} catch (ModelValidationException $e) {le($e);}
			}
		}
		$data = QMArr::toArray($data);
		$initialVariableId = TrackingReminderVariableIdProperty::pluckOrDefault($data);
		$v = Variable::fromForeignData($data);
		unset($data['variableId']);
		$data[TrackingReminder::FIELD_VARIABLE_ID] = $v->id;
		$uv = UserVariable::fromForeignData($data);
		if($initialVariableId !== $uv->variable_id){
			$user = $uv->getUser();
			$user->tracking_reminders()
                ->where(TrackingReminder::FIELD_VARIABLE_ID, $initialVariableId)
                ->forceDelete();
			$user->tracking_reminder_notifications()
			     ->where(TrackingReminderNotification::FIELD_VARIABLE_ID, $initialVariableId)
			     ->forceDelete();
			$uv = $v->getUserVariable($uv->user_id);
		}
		$data[TrackingReminder::FIELD_REMINDER_START_TIME] = TrackingReminderReminderStartTimeProperty::pluckUTC($data);
		$data[TrackingReminder::FIELD_USER_VARIABLE_ID] = $uv->id;
		//        $tr = parent::firstOrCreate([
		//            self::FIELD_USER_VARIABLE_ID => $uv->id,
		//            self::FIELD_VARIABLE_ID => $v->id,
		//            self::FIELD_USER_ID => $uv->user_id,
		//            self::FIELD_REMINDER_FREQUENCY => TrackingReminderReminderFrequencyProperty::pluck($data),
		//            self::FIELD_REMINDER_START_TIME => TrackingReminderReminderStartTimeProperty::pluck($data),
		//        ], $data);
		$tr = static::firstOrNewByData($data);
		if($tr->exists){
			$tr->populate($data);
		}
		$tr->setUserVariable($uv);
		$tr->setRelationAndAddToMemory('variable', $v);
		/** @noinspection PhpUnusedLocalVariableInspection */
		if($changes = $tr->getDirty()){
			try {
				$tr->save();
			} catch (ModelValidationException $e) {
				le($e);
			} catch (QueryException $e) {
				if(stripos($e->getMessage(), 'UK_user_var_time_freq') !== false){
					TrackingReminder::whereUserId($tr->user_id)->where(self::FIELD_VARIABLE_ID, $tr->variable_id)
						->where(self::FIELD_REMINDER_START_TIME, $tr->reminder_start_time)
						->where(self::FIELD_REMINDER_FREQUENCY, $tr->getReminderFrequencyAttribute())->forceDelete();
					try {
						$tr->save();
					} catch (ModelValidationException $e) {
						le($e);
					}
				}
				QMLog::error("TODO: Fix this: " . $e->getMessage());
				QMRequest::addClientWarning(__METHOD__.": ".$e->getMessage());
				//                if(stripos($e->getMessage(), "Duplicate entry") !== false){
				//                    QMLog::exceptionIfNotProduction($e->getMessage(), ['provided' => $item]);
				//                } else {
				//                    throw $e;
				//                }
			}
		}
		if($tr->isActive()){
			$tr->getOrCreateTrackingReminderNotification();
		}
		if($tr->wasRecentlyCreated){
			UserVariableNumberOfTrackingRemindersProperty::calculate($uv);
			try {
				$uv->save();
			} catch (ModelValidationException $e) {
				le($e);
			}
		}
		return $tr;
	}
	private function setUserVariable(UserVariable $uv){
		$this->setRelationAndAddToMemory('user_variable', $uv);
		if(!$uv->number_of_tracking_reminders){
			$uv->number_of_tracking_reminders = 1;
			/** @var QMUserVariable $mem */
			$mem = $uv->getDBModelFromMemory();
			if($mem){$mem->setSubtitle($uv->getSubtitleAttribute());}
		}
	}
	/**
	 * @param array|object $data
	 */
	public function populate($data): void{
		parent::populate($data);
		// Have to do this in case they're set to null by client
		$this->start_tracking_date = TrackingReminderStartTrackingDateProperty::pluck($data);
		$this->stop_tracking_date = TrackingReminderStopTrackingDateProperty::pluck($data);
		$this->reminder_frequency = TrackingReminderReminderFrequencyProperty::pluckOrDefault($data);
	}
	public function save(array $options = []): bool{
		if(!$this->hasUserId()){
			$this->user_id = QMAuth::getUserId();
		}
		if(!$this->attributeIsSet(self::FIELD_USER_VARIABLE_ID)){
			$uv = $this->getVariable()->getOrCreateUserVariable($this->getUserId());
			$this->user_variable_id = $uv->getId();
		}
		$res = parent::save($options);
		return $res;
	}
	/**
	 * @param $data
	 * @return static
	 */
	public static function findByData($data): ?BaseModel{
		return parent::findByData($data);
	}
	/**
	 * @return bool|null
	 */
	public function forceDelete(): ?bool{
		$this->tracking_reminder_notifications()->forceDelete();
		return parent::forceDelete();
	}
	public function getOrCreateTrackingReminderNotification(): TrackingReminderNotification{
		return $this->firstOrCreateNotification();
	}
	public function isActive(): bool{
		if(!$this->getReminderFrequencyAttribute()){
			return false;
		}
		$start = $this->start_tracking_date;
		if($start && $start > date('Y-m-d')){
			return false;
		}
		$end = $this->stop_tracking_date;
		if($end && $end < date('Y-m-d')){
			return false;
		}
		return true;
	}
	/**
	 * @return string
	 */
	public function generateFirstNotifyAt(): string{
		$startDate = $this->start_tracking_date;
		if(date('Y-m-d') > $startDate){
			$startDate = date('Y-m-d');
		}
		$reminderStartTimeUtc = $this->getReminderStartTimeUtc();
		$reminderStartTimeLocal = $this->getReminderStartTimeLocal();
		$u = $this->getUser();
		$earliestUserTimeLocal = $u->earliest_reminder_time;
		$latestUserTimeLocal = $u->latest_reminder_time;
		$utc = $startDate . ' ' . $reminderStartTimeUtc;
		$freq = $this->getReminderFrequencyAttribute();
		$future = $tooEarly = $tooLate = true;
		$currentAt = now_at();
		$notifyAtUtc = db_date(strtotime($utc) + 86400);
		while($future || $tooLate || $tooEarly){
			$localTime = $u->utcToLocalHis($notifyAtUtc);
			if($freq < 86400){
				$tooLate = $localTime > $latestUserTimeLocal;
				$tooEarly = $localTime < $earliestUserTimeLocal;
			} else{
				$tooLate = false;
				$tooEarly = false;
			}
			$future = $notifyAtUtc > $currentAt;
			if(!$future && !$tooLate && !$tooEarly){
				break;
			}
			$notifyAtUtc = db_date(strtotime($notifyAtUtc) - $freq);
			$notifyAtLocal = $u->convertToLocalTimezone($notifyAtUtc);
			if($freq === 86400){
				if(!str_contains($notifyAtLocal, $reminderStartTimeLocal)){
					QMLog::error("notifyAtLocal $notifyAtLocal should contain reminderStartTimeLocal $reminderStartTimeLocal");
				}
			}
		}
		return $notifyAtUtc;
	}
	/**
	 * @param string|null $notifyAtUtc
	 * @return array
	 */
	public function toNotificationArray(string $notifyAtUtc = null): array{
		$arr = $this->toNewRelationArray(TrackingReminderNotification::class);
		if(!$notifyAtUtc){
			$notifyAtUtc = $this->generateFirstNotifyAt();
		}
		$arr[TrackingReminderNotification::FIELD_NOTIFY_AT] = $notifyAtUtc;
		return $arr;
	}
	/**
	 * @return int
	 * @throws UserNotFoundException
	 */
	public function createNotifications(): int{
		$notifyAts = TrackingReminderNotificationNotifyAtProperty::generate($this);
		$count = count($notifyAts);
		if(!$count){
			$this->logInfo("No new notifications to insert");
			return $count;
		}
		$data = [];
		foreach($notifyAts as $at){
			$data[] = $this->toNotificationArray($at);
			$this->latest_tracking_reminder_notification_notify_at = $at;
		}
		$success = TrackingReminderNotification::insert($data);
		if(!$success){
			le('"!\$success"');
		}
		try {
			$this->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
		$this->logInfo("Inserted $count new notifications");
		return $count;
	}
	public static function newFake(int $userId = UserIdProperty::USER_ID_TEST_USER): BaseModel{
		$m = parent::newFake();
		$uv = UserVariable::firstOrFakeSave();
		$m->user_variable_id = $uv->id;
		$m->variable_id = $uv->variable_id;
		return $m;
	}
	public function firstOrCreateNotification(): TrackingReminderNotification{
		$eb = $this->tracking_reminder_notifications();
		/** @var TrackingReminderNotification $n */
		$n = $eb->firstOrCreate($this->toNotificationArray());
		if($n->wasRecentlyCreated){
			$this->setIfGreaterThanExisting(self::FIELD_LATEST_TRACKING_REMINDER_NOTIFICATION_NOTIFY_AT, $n->notify_at);
			try {
				$this->save();
			} catch (ModelValidationException $e) {
				le($e);
			}
		}
		return $n;
	}
	public function getTrackingReminderNotification(): TrackingReminderNotification{
		return $this->firstOrCreateNotification();
	}
	public function delete(): ?bool{
		$this->tracking_reminder_notifications()->forceDelete();
		return parent::delete();
	}
	/**
	 * @return Filter[]
	 */
	public function getFilters(): array{
		$filters = parent::getFilters();
		$filters[] = new ReminderNotificationsEnabledFilter();
		$filters[] = new VariableCategoryFilter();
		return $filters;
	}
	/**
	 * @return array
	 */
	public function getFields(): array{
		$fields = parent::getFields();
		$fields[] = TrackingReminderNotificationBaseAstralResource::hasMany();
		return $fields;
	}
	/**
	 * @return Builder
	 */
	public static function whereActive(): Builder{
		$qb = static::query();
		if(Env::APP_DEBUG()){
			QMLog::info($qb->count() . " reminders total");
		}
		$qb->whereNull(self::FIELD_DELETED_AT);
		if(Env::APP_DEBUG()){
			QMLog::info($qb->count() . " reminders with deleted null");
		}
		$qb->where(self::FIELD_REMINDER_FREQUENCY, '>', 0);
		if(Env::APP_DEBUG()){
			QMLog::info($qb->count() . " reminders with frequency greater than 0");
		}
		$qb->where(self::FIELD_START_TRACKING_DATE, '<=', TimeHelper::YYYYmmddd(now_at()));
		if(Env::APP_DEBUG()){
			QMLog::info($qb->count() . " reminders with start tracking date less than now");
		}
		$qb->where(static function($query){
			/** @var \Illuminate\Database\Query\Builder $query */
			$query->where(self::FIELD_STOP_TRACKING_DATE, '>', TimeHelper::YYYYmmddd(now_at()))
				->orWhereNull(self::FIELD_STOP_TRACKING_DATE);
		});
		if(Env::APP_DEBUG()){
			QMLog::info($qb->count() . " reminders with stop tracking date null or greater than now");
		}
		return $qb;
	}
	public static function whereActiveCreatedAMonthAgo(): Builder{
		$monthAgo = db_date(time() - 30 * 86400);
		$q = TrackingReminder::whereActive()->where(TrackingReminder::CREATED_AT, "<", $monthAgo)
			->where(static function($query) use ($monthAgo){
				/** @var \Illuminate\Database\Query\Builder $query */
				$query->whereNull(TrackingReminder::FIELD_LAST_TRACKED)
					->orWhere(TrackingReminder::FIELD_LAST_TRACKED, '<', $monthAgo);
			});
		$sql = QueryBuilderHelper::toPreparedSQL($q);
		ConsoleLog::info($sql);
		return $q;
	}
	public static function getS3Bucket(): string{ return S3Private::getBucketName(); }
	public static function whereNeedNotifications(int $userId = null): Builder{
		$qb = self::whereActive();
		$now = Carbon::now();
		$qb->where(function ($query) use ($now){
			$days = self::BUFFER_DAYS_FOR_NOTIFICATIONS;
			if(Writable::isSQLite()){
				$query->where(self::FIELD_LATEST_TRACKING_REMINDER_NOTIFICATION_NOTIFY_AT, '<', 
				              Writable::db()->raw("datetime('$now', '+$days days', '+reminder_frequency seconds')"));
			} else {
				$query->whereRaw(self::FIELD_LATEST_TRACKING_REMINDER_NOTIFICATION_NOTIFY_AT . 
				                 ' < DATE_ADD(NOW(), INTERVAL ' . $days . ' DAY)');
			}
			$query->orWhereNull('latest_tracking_reminder_notification_notify_at');
		});
		if($userId){
			$qb->where(TrackingReminder::FIELD_USER_ID, $userId);
		}
		$sql = QueryBuilderHelper::toPreparedSQL($qb);
		ConsoleLog::info($sql);
		return $qb;
	}
	public function needsNotifications(): bool{
		$freq = $this->reminder_frequency;
		if(!$freq){
			return false;
		}
		$latest = $this->latest_tracking_reminder_notification_notify_at;
		$cutoff = $this->getLatestNotificationCutoffAt();
		return $latest < $cutoff;
	}
	public function getLatestNotificationCutoffAt(): string{
		$freq = $this->reminder_frequency;
		$bufferSecs = 86400 * self::BUFFER_DAYS_FOR_NOTIFICATIONS;
		$cutoff = db_date(time() + $bufferSecs + $freq);
		return $cutoff;
	}
	public function getEarliestNotificationCutoffAt(): string{
		$freq = $this->reminder_frequency;
		$actual = $latestAt =
			$this->tracking_reminder_notifications()->withTrashed()->max(TrackingReminderNotification::FIELD_NOTIFY_AT);
		$stored = $this->latest_tracking_reminder_notification_notify_at;
		if(strtotime($actual) !== strtotime($stored)){
			//le("Why is r->latest_tracking_reminder_notification_notify_at $stored !== actual from DB $actual");
		}
		if(!$latestAt){
			$latestAt = db_date(time() - 2 * $freq);
		}
		$startTimeUtc = $this->getReminderStartTimeUtc();
		$notifyAt = TimeHelper::YYYYmmddd($latestAt) . " $startTimeUtc";
		while(strtotime($notifyAt) <= strtotime($latestAt)){
			$notifyAt = db_date(strtotime($notifyAt) + $freq);
		}
		return $notifyAt;
	}
	public function deleteNotifications(){
		$this->tracking_reminder_notifications()->forceDelete();
		$this->latest_tracking_reminder_notification_notify_at = null;
		try {
			$this->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{
		return true;
	}
	public function getFrequencyDescription(): string{
		$freq = $this->getAttribute(TrackingReminder::FIELD_REMINDER_FREQUENCY);
		$desc = 'every ' . TimeHelper::convertSecondsToHumanString($freq);
		if($freq === 86400){
			$desc = 'daily';
		}
		return $desc;
	}
	/**
	 * @param Builder $query
	 * @return Builder
	 * @noinspection PhpUnused
	 */
	public function scopeActive(Builder $query): Builder{
		$query->where(self::FIELD_REMINDER_FREQUENCY, ">", 0)
			->whereRaw(self::FIELD_START_TRACKING_DATE .' <= "'. TimeHelper::YYYYmmddd(now_at()).'"')
			->whereNull(self::FIELD_DELETED_AT)
			->where(function($query){
				/** @var \Illuminate\Database\Query\Builder $query */
				$query->whereRaw(self::FIELD_STOP_TRACKING_DATE . ' > '. TimeHelper::YYYYmmddd(now_at()))
					->orWhere(self::FIELD_STOP_TRACKING_DATE, '=', null);
			});
		return $query;
	}
	/**
	 * @param Builder $query
	 * @return Builder
	 * @noinspection PhpUnused
	 */
	public function scopeWithoutFutureNotifications(Builder $query): Builder{
		$query->where(function($query){
			/** @var \Illuminate\Database\Query\Builder $query */
			$query->where(self::FIELD_LATEST_TRACKING_REMINDER_NOTIFICATION_NOTIFY_AT, '<', Carbon::now())
				->orWhereNull(self::FIELD_LATEST_TRACKING_REMINDER_NOTIFICATION_NOTIFY_AT);
		});
		return $query;
	}
	/**
	 * @return TrackingReminderNotification[]|Collection
	 */
	public function getFutureNotifications(){
		return $this->future_notifications()->get();
	}
	/**
	 * @return Builder|TrackingReminderNotification
	 */
	public function future_notifications(){
		$query = TrackingReminderNotification::whereTrackingReminderId($this->getId())
			->where(TrackingReminderNotification::FIELD_NOTIFY_AT, ">", now_at());
		return $query;
	}
	/**
	 * @return string
	 */
	public function __toString(){
		return $this->getVariableName() . " " . $this->getFrequencyDescription();
	}
	public function getTitleAttribute(): string{
		if(!$this->attributes){
			return static::getClassNameTitle();
		}
		return "Reminder settings for " . $this->getVariableName();
	}
	public function getUrl(array $params = []): string{
		return (new ReminderAddStateButton($this))->getUrl();
	}
	public function getFontAwesome(): string{
		return TrackingReminder::FONT_AWESOME;
	}
	public function getReminderStartTimeLocal(): string{
		return $this->reminder_start_time;
	}
	public function getEditButton(): QMButton{
		return new ReminderAddStateButton($this);
	}
	public function getUrlParams(): array{
		$params = [
			TrackingReminder::FIELD_USER_VARIABLE_ID => $this->getUserVariableId(),
			UserVariable::FIELD_DEFAULT_UNIT_ID => $this->getUnitIdAttribute(),
			UserVariable::FIELD_VARIABLE_CATEGORY_ID => $this->getVariableCategoryId(),
			UserVariable::FIELD_USER_ID => $this->getUserId(),
			UserVariable::FIELD_VARIABLE_ID => $this->getVariableIdAttribute(),
		];
		if($this->hasId()){
			$params[TrackingReminder::FIELD_ID] =
			$params[TrackingReminderNotification::FIELD_TRACKING_REMINDER_ID] = $this->getId();
		}
		return $params;
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		return [
			new TrackingReminderUserVariableButton($this),
			new TrackingReminderTrackingReminderNotificationsButton($this),
		];
	}
	/**
	 * @return string
	 */
	public function getUniqueIndexIdsSlug(): string{
		return "users-" . $this->getUserId() . static::TABLE . "-" . $this->getId();
	}
	public function getImage(): string{
		$img = $this->getAttribute(TrackingReminder::FIELD_IMAGE_URL);
		if($img && $img !== "Not Found"){
			return $img;
		}
		$id = $this->getAttribute(TrackingReminder::FIELD_VARIABLE_ID);
		if(!$id){
			return TrackingReminder::getClassImage();
		}
		return $this->getVariable()->getImage();
	}
	public function getNameAttribute(): string{
		return $this->getVariableName();
	}
	/**
	 * @return string
	 */
	public function getLocalHourAmPm(): string{
		$start = $this->getReminderStartTimeLocal();
		return TimeHelper::getHourAmPm($start);
	}
	public function getReminderFrequencyAttribute(): ?int{
		return $this->attributes[self::FIELD_REMINDER_FREQUENCY] ?? null;
	}
	public function getHumanizedFrequencyAndTime(): string{
		$str = $this->getHumanizedFrequency();
		if($this->getReminderFrequencyAttribute() >= 86400){
			$str .= " at " . $this->getLocalTimeOfDay();
		}
		return $str;
	}
	public function getHumanizedFrequency(): string{
		return "Every " . TimeHelper::convertSecondsToHumanString($this->getReminderFrequencyAttribute());
	}
	public function getLocalTimeOfDay(): string{
		return $this->getLocalHourAmPm();
	}
	/**
	 * @return User
	 */
	public function getUser(): User{
		$id = $this->getUserId();
		$u = User::findInMemoryOrDB($id);
		if(!$u){
			try {
				throw new UserNotFoundException($id);
			} catch (\Throwable $e){
			    le($e);
			}
		}
		return $u;
	}
	public function getVariable(): Variable{
		$v = Variable::findInMemoryOrDB($this->getVariableIdAttribute());
		if(!$v){
			le("no variable with id " . $this->getVariableIdAttribute());
		}
		return $v;
	}
	public function getVariableIdAttribute(): ?int{
		return $this->attributes[TrackingReminder::FIELD_VARIABLE_ID] ?? null;
	}
	public function getVariableCategoryId(): int{
		if($uv = $this->getUserVariableFromMemory()){
			return $uv->getVariableCategoryId();
		}
		return $this->getVariable()->getVariableCategoryId();
	}
	public function hasVariableCategoryId(): bool{
		return $this->attributes[self::FIELD_VARIABLE_ID] ?? false;
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
	public function getValueFrequencyForRatingOrYesNo(): string{
		$prefix = 'Rate ';
		if($this->isYesNoOrCountWithOnlyOnesAndZeros()){$prefix = '';}
		$freq = $this->getReminderFrequencyAttribute();
		if($freq === 86400){return $prefix . 'daily at ' . $this->getReminderStartTimeLocalHumanFormatted();}
		if($freq > 86400){
			return $this->getValueFrequency() . ' at ' . $this->getReminderStartTimeLocalHumanFormatted();
		}
		return $prefix . 'every ' . TimeHelper::convertSecondsToHumanString($freq);
	}
	/**
	 * @return string
	 */
	public function getValueFrequency(): string{
		if($this->reminder_frequency){
			$str = $this->getValueFrequencyForFrequencies();
		} else{
			$str = $this->getValueFrequencyForZeroFrequency();
		}
		return ucfirst($str);
	}
	public function getValueFrequencyForZeroFrequency(): string{
		$str = 'Favorite';
		if($this->getVariableCategoryName() === "Treatments"){
			$str = 'As-Needed';
		}
		return $str;
	}
	/**
	 * @return string|void
	 */
	public function getValueFrequencyForFrequencies(): string {
		$freq = $this->getReminderFrequencyAttribute();
		$frequencyTextDescription = 'every ' . TimeHelper::convertSecondsToHumanString($freq);
		$frequencyTextDescriptionWithTime = $frequencyTextDescription;
		if($freq === 86400){
			$frequencyTextDescription = 'daily';
			$frequencyTextDescriptionWithTime =
				$frequencyTextDescription . ' at ' . $this->getReminderStartTimeLocalHumanFormatted();
		}
		if(!$this->isRating()){
			if($this->getDefaultValueInUserUnit() !== null){
				return $this->getDefaultValueInUserUnit() . ' ' . $this->getUnitAbbreviatedName() . ' ' .
					$this->getHumanizedFrequencyAndTime();
			}

		}
		if(isset($reminderStartTimeLocalHumanFormatted) &&
			($this->isRating() || $this->isYesNoOrCountWithOnlyOnesAndZeros())){
			return $this->getValueFrequencyForRatingOrYesNo();
		}
		if($this->hasEnded()){
			$at = $this->stop_tracking_date;
			if(empty($at)){le('empty($at)');}
			$suffix = ' (ended ' . $at . ')';
			$frequencyTextDescriptionWithTime .= $suffix;
			return $frequencyTextDescriptionWithTime;
		}
		if($this->hasNotStarted()){
			$suffix = ' (starts ' . $this->start_tracking_date . ')';
			$frequencyTextDescriptionWithTime .= $suffix;
		}
		return $frequencyTextDescriptionWithTime;
	}
	/**
	 * @return string
	 */
	public function getReminderStartTimeLocalHumanFormatted(): string{
		$time = $this->getReminderStartTimeLocal();
		return $this->getHourAmPm($time);
	}
	public function getDefaultValueInUserUnit(): ?float{
		$inCommonUnit = $this->default_value;
		if($inCommonUnit === null){return null;}
		$commonUnit = $this->getCommonUnit();
		try {
			$commonUnit->throwExceptionIfValueNotValidForUnit($inCommonUnit, $this);
		} catch (InvalidVariableValueException $e) {
			$this->logErrorOrInfoIfTesting("Default value $inCommonUnit $commonUnit->abbreviatedName not valid
            \n for unit so setting defaultValueInUserUnit = null. \nInvalidVariableValueException: " .
				$e->getMessage());
			return null;
		}
		try {
			return $this->getUserVariable()->toUserUnit($inCommonUnit);
		} catch (IncompatibleUnitException | InvalidVariableValueException $e) {
			QMLog::error(__METHOD__.": ".$e->getMessage());
			return null;
		}
	}
	/**
	 * @return bool
	 */
	public function isEnabled(): bool{
		if(!$this->reminder_frequency){
			return false;
		}
		if($this->hasEnded()){
			return false;
		}
		if($this->hasNotStarted()){
			return false;
		}
		return true;
	}
	/**
	 * @return bool
	 */
	public function hasEnded(): bool{
		$at = $this->stop_tracking_date;
		if(!$at){
			return false;
		}
		return time_or_exception($at) < time();
	}
	/**
	 * @return bool
	 */
	public function hasNotStarted(): bool{
		$at = $this->start_tracking_date;
		if(!$at){
			return false;
		}
		return time_or_exception($at) > time();
	}
	public function getReminderStartTimeUtc(): string{
		return $this->getUser()->localToUtcHis($this->reminder_start_time);
	}
}
