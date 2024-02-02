<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

/** @noinspection PhpMissingDocCommentInspection */
/** @noinspection PhpUnused */
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
/** Created by Reliese Model.
 */
namespace App\Models\Base;
use App\Models\BaseModel;
use App\Models\OAClient;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\UserVariable;
use App\Models\Variable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseTrackingReminder
 * @property int $id
 * @property int $user_id
 * @property string $client_id
 * @property int $variable_id
 * @property float $default_value
 * @property Carbon $reminder_start_time
 * @property Carbon $reminder_end_time
 * @property string $reminder_sound
 * @property int $reminder_frequency
 * @property bool $pop_up
 * @property bool $sms
 * @property bool $email
 * @property bool $notification_bar
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $start_tracking_date
 * @property Carbon $stop_tracking_date
 * @property string $instructions
 * @property Carbon $deleted_at
 * @property string $image_url
 * @property int $user_variable_id
 * @property Carbon $latest_tracking_reminder_notification_notify_at
 * @property int $number_of_tracking_reminder_notifications
 * @property OAClient $oa_client
 * @property \App\Models\User $user
 * @property UserVariable $user_variable
 * @property Variable $variable
 * @property Collection|TrackingReminderNotification[] $tracking_reminder_notifications
 * @package App\Models\Base

 * @property-read int|null $tracking_reminder_notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseTrackingReminder onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder whereDefaultValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder whereInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder whereLastTracked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder
 *     whereLatestTrackingReminderNotificationNotifyAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder
 *     whereNotificationBar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder
 *     whereNumberOfTrackingReminderNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder wherePopUp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder
 *     whereReminderEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder
 *     whereReminderFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder
 *     whereReminderSound($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder
 *     whereReminderStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder whereSms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder
 *     whereStartTrackingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder
 *     whereStopTrackingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder
 *     whereUserVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminder whereVariableId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseTrackingReminder withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseTrackingReminder withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseTrackingReminder extends BaseModel {
	use SoftDeletes;
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
	public const FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS = 'number_of_tracking_reminder_notifications';
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
	public const TABLE = 'tracking_reminders';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_REMINDER_START_TIME => 'datetime',
        self::FIELD_REMINDER_END_TIME => 'datetime',
        //self::FIELD_LAST_TRACKED => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_START_TRACKING_DATE => 'datetime',
        self::FIELD_STOP_TRACKING_DATE => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_LATEST_TRACKING_REMINDER_NOTIFICATION_NOTIFY_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_DEFAULT_VALUE => 'float',
		self::FIELD_EMAIL => 'bool',
		self::FIELD_ID => 'int',
		self::FIELD_IMAGE_URL => 'string',
		self::FIELD_INSTRUCTIONS => 'string',
		self::FIELD_NOTIFICATION_BAR => 'bool',
		self::FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS => 'int',
		self::FIELD_POP_UP => 'bool',
		self::FIELD_REMINDER_FREQUENCY => 'int',
		self::FIELD_REMINDER_SOUND => 'string',
		self::FIELD_SMS => 'bool',
		self::FIELD_USER_ID => 'int',
		self::FIELD_USER_VARIABLE_ID => 'int',
		self::FIELD_VARIABLE_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'required|max:80',
		self::FIELD_DEFAULT_VALUE => 'nullable|numeric',
		self::FIELD_EMAIL => 'nullable|boolean',
		self::FIELD_IMAGE_URL => 'nullable|max:2083',
		self::FIELD_INSTRUCTIONS => 'nullable|max:65535',
		self::FIELD_LAST_TRACKED => 'nullable|date',
		self::FIELD_LATEST_TRACKING_REMINDER_NOTIFICATION_NOTIFY_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_NOTIFICATION_BAR => 'nullable|boolean',
		self::FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_POP_UP => 'nullable|boolean',
		self::FIELD_REMINDER_END_TIME => 'nullable|date',
		self::FIELD_REMINDER_FREQUENCY => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_REMINDER_SOUND => 'nullable|max:125',
		self::FIELD_REMINDER_START_TIME => 'required|date',
		self::FIELD_SMS => 'nullable|boolean',
		self::FIELD_START_TRACKING_DATE => 'nullable|date',
		self::FIELD_STOP_TRACKING_DATE => 'nullable|date',
		self::FIELD_USER_ID => 'required|numeric|min:0',
		self::FIELD_USER_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_USER_ID => '',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_VARIABLE_ID => 'Id for the variable to be tracked',
		self::FIELD_DEFAULT_VALUE => 'Default value to use for the measurement when tracking',
		self::FIELD_REMINDER_START_TIME => 'Earliest time of day at which reminders should appear',
		self::FIELD_REMINDER_END_TIME => 'Latest time of day at which reminders should appear',
		self::FIELD_REMINDER_SOUND => 'String identifier for the sound to accompany the reminder',
		self::FIELD_REMINDER_FREQUENCY => 'Number of seconds between one reminder and the next',
		self::FIELD_POP_UP => 'True if the reminders should appear as a popup notification',
		self::FIELD_SMS => 'True if the reminders should be delivered via SMS',
		self::FIELD_EMAIL => 'True if the reminders should be delivered via email',
		self::FIELD_NOTIFICATION_BAR => 'True if the reminders should appear in the notification bar',
		//self::FIELD_LAST_TRACKED => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_START_TRACKING_DATE => 'Earliest date on which the user should be reminded to track in YYYY-MM-DD format',
		self::FIELD_STOP_TRACKING_DATE => 'Latest date on which the user should be reminded to track  in YYYY-MM-DD format',
		self::FIELD_INSTRUCTIONS => '',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_IMAGE_URL => '',
		self::FIELD_USER_VARIABLE_ID => '',
		self::FIELD_LATEST_TRACKING_REMINDER_NOTIFICATION_NOTIFY_AT => 'datetime',
		self::FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS => 'Number of Tracking Reminder Notifications for this Tracking Reminder.
                    [Formula: update tracking_reminders
                        left join (
                            select count(id) as total, tracking_reminder_id
                            from tracking_reminder_notifications
                            group by tracking_reminder_id
                        )
                        as grouped on tracking_reminders.id = grouped.tracking_reminder_id
                    set tracking_reminders.number_of_tracking_reminder_notifications = count(grouped.total)]',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => TrackingReminder::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => TrackingReminder::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => TrackingReminder::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => TrackingReminder::FIELD_USER_ID,
			'methodName' => 'user',
		],
		'user_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => UserVariable::class,
			'foreignKeyColumnName' => 'user_variable_id',
			'foreignKey' => TrackingReminder::FIELD_USER_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => UserVariable::FIELD_ID,
			'ownerKeyColumnName' => 'user_variable_id',
			'ownerKey' => TrackingReminder::FIELD_USER_VARIABLE_ID,
			'methodName' => 'user_variable',
		],
		'variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'variable_id',
			'foreignKey' => TrackingReminder::FIELD_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'variable_id',
			'ownerKey' => TrackingReminder::FIELD_VARIABLE_ID,
			'methodName' => 'variable',
		],
		'tracking_reminder_notifications' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => TrackingReminderNotification::class,
			'foreignKey' => TrackingReminderNotification::FIELD_TRACKING_REMINDER_ID,
			'localKey' => TrackingReminderNotification::FIELD_ID,
			'methodName' => 'tracking_reminder_notifications',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, TrackingReminder::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			TrackingReminder::FIELD_CLIENT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, TrackingReminder::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			TrackingReminder::FIELD_USER_ID);
	}
	public function user_variable(): BelongsTo{
		return $this->belongsTo(UserVariable::class, TrackingReminder::FIELD_USER_VARIABLE_ID, UserVariable::FIELD_ID,
			TrackingReminder::FIELD_USER_VARIABLE_ID);
	}
	public function variable(): BelongsTo{
		return $this->belongsTo(Variable::class, TrackingReminder::FIELD_VARIABLE_ID, Variable::FIELD_ID,
			TrackingReminder::FIELD_VARIABLE_ID);
	}
	/**
	 * @return HasMany|\Illuminate\Database\Eloquent\Builder
	 */
	public function tracking_reminder_notifications(): HasMany{
		return $this->hasMany(TrackingReminderNotification::class,
			TrackingReminderNotification::FIELD_TRACKING_REMINDER_ID, static::FIELD_ID);
	}
}
