<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\PhpUnitJobs\Reminders\ReminderNotificationGeneratorJob;
use App\Properties\User\UserIdProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Buttons\QMButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Buttons\RelationshipButtons\TrackingReminderNotification\TrackingReminderNotificationTrackingReminderButton;
use App\Buttons\RelationshipButtons\TrackingReminderNotification\TrackingReminderNotificationUserVariableButton;
use App\Buttons\States\RemindersInboxStateButton;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoDeviceTokensException;
use App\Logging\QMLog;
use App\Models\Base\BaseTrackingReminderNotification;
use App\Astral\Actions\NotifyAction;
use App\Slim\Model\DBModel;
use App\Slim\Model\Notifications\IndividualPushNotificationData;
use App\Slim\Model\Notifications\QMDeviceToken;
use App\Slim\Model\QMUnit;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Traits\HasDBModel;

use App\Traits\HasModel\HasTrackingReminder;
use App\Traits\HasModel\HasUnit;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\QMColor;
use App\Variables\QMVariableCategory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Titasgailius\SearchRelations\SearchesRelations;
/**
 *
 * @mixin QMTrackingReminderNotification
 * App\Models\TrackingReminderNotification
 * @property int $id
 * @property int $tracking_reminder_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property int $user_id
 * @property Carbon|null $notified_at
 * @property Carbon|null $received_at
 * @property string|null $client_id
 * @property int|null $variable_id
 * @method static Builder|TrackingReminderNotification newModelQuery()
 * @method static Builder|TrackingReminderNotification newQuery()
 * @method static Builder|TrackingReminderNotification query()
 * @method static Builder|TrackingReminderNotification whereAdditionalMetaData($value)
 * @method static Builder|TrackingReminderNotification whereClientId($value)
 * @method static Builder|TrackingReminderNotification whereCreatedAt($value)
 * @method static Builder|TrackingReminderNotification whereDeletedAt($value)
 * @method static Builder|TrackingReminderNotification whereId($value)
 * @method static Builder|TrackingReminderNotification whereNotifiedAt($value)
 * @method static Builder|TrackingReminderNotification whereReceivedAt($value)
 * @method static Builder|TrackingReminderNotification whereReminderTime($value)
 * @method static Builder|TrackingReminderNotification whereTrackingReminderId($value)
 * @method static Builder|TrackingReminderNotification whereUpdatedAt($value)
 * @method static Builder|TrackingReminderNotification whereUserId($value)
 * @method static Builder|TrackingReminderNotification whereVariableId($value)
 * @mixin Eloquent
 * @property Carbon|null $notify_at
 * @method static Builder|TrackingReminderNotification whereNotifyAt($value)
 * @property-read OAClient|null $oa_client
 * @property-read User $user
 * @property int|null $user_variable_id
 * @method static Builder|TrackingReminderNotification whereUserVariableId($value)
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @property-read TrackingReminder $tracking_reminder
 * @property-read UserVariable|null $user_variable
 * @property-read Variable $variable
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()

 * @property-read int $default_unit_id
 * @property mixed $raw
 * @property-read mixed $raw_user_variable
 * @property-read mixed $raw_variable
 * @property-read OAClient|null $client
 */
class TrackingReminderNotification extends BaseTrackingReminderNotification {
    use HasFactory;

	use HasDBModel;
	use SearchesRelations;
	use HasTrackingReminder;

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
	public static $group = TrackingReminderNotification::CLASS_CATEGORY;
	public static function getSlimClass(): string{ return QMTrackingReminderNotification::class; }
	public const DEFAULT_SEARCH_FIELD = 'variable.name';
	public const DEFAULT_ORDERINGS = [self::FIELD_NOTIFY_AT => self::ORDER_DIRECTION_ASC];
	public const DEFAULT_IMAGE = ImageUrls::BASIC_FLAT_ICONS_BELL;
	public const CLASS_DESCRIPTION = "Specific reminder notification instances that still need to be tracked. ";
	public const CLASS_DISPLAY_NAME = "Tracking Reminder Notifications";
	public const FONT_AWESOME = FontAwesome::BELL;
	public const COLOR = QMColor::HEX_PURPLE;
	const CLASS_CATEGORY = "Reminders";
	protected $with = [
		//'variable:'.Variable::IMPORTANT_FIELDS,
		//'user_variable:'.UserVariable::IMPORTANT_FIELDS,
		//'tracking_reminder', // Too complicated and redundant data. Just get relations directly
	];
	protected array $rules = [
		self::FIELD_TRACKING_REMINDER_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_USER_ID => 'required|numeric|min:1',
		self::FIELD_NOTIFIED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_RECEIVED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_VARIABLE_ID => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_NOTIFY_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => TrackingReminderNotification::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => TrackingReminderNotification::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'tracking_reminder' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => TrackingReminder::class,
			'foreignKeyColumnName' => 'tracking_reminder_id',
			'foreignKey' => TrackingReminderNotification::FIELD_TRACKING_REMINDER_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => TrackingReminder::FIELD_ID,
			'ownerKeyColumnName' => 'tracking_reminder_id',
			'ownerKey' => TrackingReminderNotification::FIELD_TRACKING_REMINDER_ID,
			'methodName' => 'tracking_reminder',
			'title' => 'Reminder Settings',
			'description' => TrackingReminder::CLASS_DESCRIPTION,
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => TrackingReminderNotification::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => TrackingReminderNotification::FIELD_USER_ID,
			'methodName' => 'user',
			'title' => 'Recipient',
			'description' => "Person to receive the notification",
		],
		'user_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => UserVariable::class,
			'foreignKeyColumnName' => 'user_variable_id',
			'foreignKey' => TrackingReminderNotification::FIELD_USER_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => UserVariable::FIELD_ID,
			'ownerKeyColumnName' => 'user_variable_id',
			'ownerKey' => TrackingReminderNotification::FIELD_USER_VARIABLE_ID,
			'methodName' => 'user_variable',
			'title' => 'User Variable',
			'description' => UserVariable::CLASS_DESCRIPTION,
		],
		'variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'variable_id',
			'foreignKey' => TrackingReminderNotification::FIELD_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'variable_id',
			'ownerKey' => TrackingReminderNotification::FIELD_VARIABLE_ID,
			'methodName' => 'variable',
			'title' => 'Common Variable',
			'description' => Variable::CLASS_DESCRIPTION,
		],
	];
	/**
	 * @param int $minutes
	 * @return TrackingReminderNotification|Builder
	 */
	public static function whereDueInNextXMinutes(int $minutes){
		return static::where(static::FIELD_NOTIFY_AT, ">", db_date(time()))
			->where(static::FIELD_NOTIFY_AT, "<", db_date(time() + 60 * $minutes));
	}
	public static function dueInNextXMinutes(int $minutes): int{
		$created = static::whereDueInNextXMinutes($minutes)->count();
		QMLog::info($created . " " . static::getClassNameTitle() . " DUE in next $minutes minutes...");
		return $created;
	}
	public function getNotifyAtUtc(): string{
		return $this->notify_at;
	}
	public function getLogMetaDataString(): string{
		$str = $this->getVariableName() . " notification $this->id for user $this->user_id";
		if($at = $this->notify_at){
			$str .= " at $at";
		}
		return $str;
	}
	public function getUserVariableButton(): QMButton{
		return $this->getUserVariable()->getButton();
	}
	public function getQMVariableCategory(): QMVariableCategory{
		return $this->getVariable()->getQMVariableCategory();
	}
	public function getQMUnit(): QMUnit{
		return $this->user_variable->getQMUnit();
	}
	public function getNotifyAtLocal(): string{
		$at = $this->getNotifyAtUtc();
		$u = $this->getUser();
		return $u->convertToLocalTimezone($at);
	}
	public function getEditUrl(array $params = []): string{
		return RemindersInboxStateButton::make()->getUrl($params);
	}
	/**
	 * @return QMTrackingReminderNotification
	 */
	public function getDBModel(): DBModel{
		if($dbm = $this->getDBModelFromMemory()){
			return $dbm;
		}
		$dbm = new QMTrackingReminderNotification();
		$dbm->populateByLaravelModel($this);
		return $dbm;
	}
	public static function newFake(int $userId = UserIdProperty::USER_ID_TEST_USER): BaseModel{
		$m = parent::newFake();
		$r = TrackingReminder::firstOrFakeSave();
		$m->tracking_reminder_id = $r->id;
		$m->variable_id = $r->variable_id;
		$m->user_variable_id = $r->user_variable_id;
		$m->user_id = $r->user_id;
		return $m;
	}
	/**
	 * @return static
	 */
	public static function fakeFromPropertyModels(int $userId = UserIdProperty::USER_ID_TEST_USER): BaseModel{
		$trn = static::factory()->make();
		try {
			$trn->updated_at = now_at();
			$trn->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
		return $trn;
	}
	public function getDefaultValueInUserUnit(): ?float{
		$inCommonUnit = $this->tracking_reminder->default_value;
		if($inCommonUnit === null){
			return null;
		}
		try {
			return $this->convertFromCommonToUserUnit($inCommonUnit);
		} catch (IncompatibleUnitException | InvalidVariableValueException $e) {
			le($e);
		}
		return null;
	}
	/**
	 * @param float|null $inCommonUnit
	 * @return float
	 * @throws \App\Exceptions\IncompatibleUnitException
	 * @throws \App\Exceptions\InvalidVariableValueException
	 */
	public function convertFromCommonToUserUnit(?float $inCommonUnit): ?float{
		if($inCommonUnit === null){
			return null;
		}
		$uv = $this->getUserVariable();
		$userUnitId = $uv->default_unit_id;
		if(!$userUnitId){
			return $inCommonUnit;
		}
		$commonUnitId = $this->getVariable()->default_unit_id;
		if($commonUnitId === $userUnitId){
			return $inCommonUnit;
		}
		$uv->unsetUserUnitIfInvalid();
		return QMUnit::convertValue($inCommonUnit, $commonUnitId, $userUnitId, $this);
	}
	public function getAt(): string{
		return $this->getNotifyAtUtc();
	}
	/**
	 * @return string
	 */
	public function getNotifyAtHumanized(): string{
		$utcStr = $this->getNotifyAtUtc();
		$user = $this->getQMUser();
		$freq = $this->getReminderFrequencyAttribute();
		return $user->humanizeNotifyAt($utcStr);
	}
	private function getReminderFrequencyAttribute(): int{
		return $this->getTrackingReminder()->reminder_frequency;
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		return [
			new TrackingReminderNotificationTrackingReminderButton($this),
			new TrackingReminderNotificationUserVariableButton($this),
		];
	}
	public function getVariableCategoryId(): int{
		return $this->getVariable()->getVariableCategoryId();
	}
	public function hasVariableCategoryId(): bool{
		return $this->attributes[self::FIELD_VARIABLE_ID] ?? false;
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID] ?? null;
	}
	/**
	 * Get the actions available for the resource.
	 * @param Request $request
	 * @return array
	 */
	public function actions(Request $request): array{
		return [
			new NotifyAction($request),
		];
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{
		return true;
	}
	public function getNotifiedAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[TrackingReminderNotification::FIELD_NOTIFIED_AT] ?? null;
		} else{
			/** @var QMTrackingReminderNotification $this */
			return $this->notifiedAt;
		}
	}
	public function setNotifiedAt(string $notifiedAt): void{
		$this->setAttribute(TrackingReminderNotification::FIELD_NOTIFIED_AT, $notifiedAt);
	}
	public function setNotifyAt(string $notifyAt): void{
		$this->setAttribute(TrackingReminderNotification::FIELD_NOTIFY_AT, $notifyAt);
	}
	public function getReceivedAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[TrackingReminderNotification::FIELD_RECEIVED_AT] ?? null;
		} else{
			/** @var QMTrackingReminderNotification $this */
			return $this->receivedAt;
		}
	}
	public function setReceivedAt(string $receivedAt): void{
		$this->setAttribute(TrackingReminderNotification::FIELD_RECEIVED_AT, $receivedAt);
	}
	public function setTrackingReminderId(int $trackingReminderId): void{
		$this->setAttribute(TrackingReminderNotification::FIELD_TRACKING_REMINDER_ID, $trackingReminderId);
	}
	public function setUserVariableId(int $userVariableId): void{
		$this->setAttribute(TrackingReminderNotification::FIELD_USER_VARIABLE_ID, $userVariableId);
	}
	public function getNameAttribute(): string{
		return $this->getVariableName();
	}
	/**
	 * @param QMDeviceToken|null $deviceToken
	 * @return IndividualPushNotificationData
	 */
	public function getOrCreateIndividualPushNotificationData(?QMDeviceToken $deviceToken = null): IndividualPushNotificationData{
		/** @var IndividualPushNotificationData $d */
		$d = $this->getIndividualPushNotificationData();
		if($d){
			return $d;
		}
		$d = $this->createIndividualPushNotificationData($deviceToken);
		return $d;
	}
	/**
	 * @param QMDeviceToken|null $deviceToken
	 * @return IndividualPushNotificationData
	 */
	protected function createIndividualPushNotificationData(QMDeviceToken $deviceToken = null): IndividualPushNotificationData{
		if($this->last_value !== null){
			$d = new IndividualPushNotificationData($deviceToken, $this); // Need user variable for last values
		} else{
			$d = new IndividualPushNotificationData($deviceToken,
				$this->getQMUserVariable()); // Need user variable for last values
		}
		return $d;
	}
	/**
	 * @return array
	 */
	public static function send(): array{
		$notifications = QMTrackingReminderNotification::getWhereDue();
		QMLog::info(count($notifications) . " notifications are due");
		$results = [];
		foreach($notifications as $n){
			try {
				$results[] = $n->sendNotification();
			} catch (NoDeviceTokensException $e) {
				$n->logInfo(__METHOD__.": ".$e->getMessage());
				continue;
			}
		}
		return $results;
	}
	protected function updateNotifiedAt(): void{
		$this->l()->notified_at = now_at();
		try {
			$this->l()->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
	}
	/**
	 * @return array
	 * @throws NoDeviceTokensException
	 */
	public function sendNotification(): array{
		$user = $this->getUser();
		$data = $this->getOrCreateIndividualPushNotificationData();
		$data->setTrackingReminderNotificationId($this->getId());
		$results = $user->notifyByPushData($data);
		$this->updateNotifiedAt();
		return $results;
	}
	public static function generate(){
		return ReminderNotificationGeneratorJob::deleteOldAndCreateNewNotifications();
	}
	public static function generateForUser(int $userId): int{
		return ReminderNotificationGeneratorJob::generateForUser($userId);
	}
}
