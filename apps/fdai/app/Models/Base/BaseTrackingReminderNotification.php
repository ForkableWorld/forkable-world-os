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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseTrackingReminderNotification
 * @property int $id
 * @property int $tracking_reminder_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property int $user_id
 * @property Carbon $notified_at
 * @property Carbon $received_at
 * @property string $client_id
 * @property int $variable_id
 * @property Carbon $notify_at
 * @property int $user_variable_id
 * @property OAClient $oa_client
 * @property TrackingReminder $tracking_reminder
 * @property \App\Models\User $user
 * @property UserVariable $user_variable
 * @property Variable $variable
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminderNotification
 *     newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminderNotification newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseTrackingReminderNotification onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminderNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminderNotification
 *     whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminderNotification
 *     whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminderNotification
 *     whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminderNotification
 *     whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminderNotification
 *     whereNotifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminderNotification
 *     whereNotifyAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminderNotification
 *     whereReceivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminderNotification
 *     whereTrackingReminderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminderNotification
 *     whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminderNotification
 *     whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminderNotification
 *     whereUserVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackingReminderNotification
 *     whereVariableId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseTrackingReminderNotification withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseTrackingReminderNotification withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseTrackingReminderNotification extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_NOTIFIED_AT = 'notified_at';
	public const FIELD_NOTIFY_AT = 'notify_at';
	public const FIELD_RECEIVED_AT = 'received_at';
	public const FIELD_TRACKING_REMINDER_ID = 'tracking_reminder_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_USER_VARIABLE_ID = 'user_variable_id';
	public const FIELD_VARIABLE_ID = 'variable_id';
	public const TABLE = 'tracking_reminder_notifications';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_NOTIFIED_AT => 'datetime',
        self::FIELD_RECEIVED_AT => 'datetime',
        self::FIELD_NOTIFY_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_TRACKING_REMINDER_ID => 'int',
		self::FIELD_USER_ID => 'int',
		self::FIELD_USER_VARIABLE_ID => 'int',
		self::FIELD_VARIABLE_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_NOTIFIED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_NOTIFY_AT => 'required|date',
		self::FIELD_RECEIVED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_TRACKING_REMINDER_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_USER_ID => 'required|numeric|min:0',
		self::FIELD_USER_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_TRACKING_REMINDER_ID => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_USER_ID => '',
		self::FIELD_NOTIFIED_AT => 'datetime',
		self::FIELD_RECEIVED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_VARIABLE_ID => '',
		self::FIELD_NOTIFY_AT => 'datetime',
		self::FIELD_USER_VARIABLE_ID => '',
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
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => TrackingReminderNotification::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => TrackingReminderNotification::FIELD_USER_ID,
			'methodName' => 'user',
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
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, TrackingReminderNotification::FIELD_CLIENT_ID,
			OAClient::FIELD_CLIENT_ID, TrackingReminderNotification::FIELD_CLIENT_ID);
	}
	public function tracking_reminder(): BelongsTo{
		return $this->belongsTo(TrackingReminder::class, TrackingReminderNotification::FIELD_TRACKING_REMINDER_ID,
			TrackingReminder::FIELD_ID, TrackingReminderNotification::FIELD_TRACKING_REMINDER_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, TrackingReminderNotification::FIELD_USER_ID,
			\App\Models\User::FIELD_ID, TrackingReminderNotification::FIELD_USER_ID);
	}
	public function user_variable(): BelongsTo{
		return $this->belongsTo(UserVariable::class, TrackingReminderNotification::FIELD_USER_VARIABLE_ID,
			UserVariable::FIELD_ID, TrackingReminderNotification::FIELD_USER_VARIABLE_ID);
	}
	public function variable(): BelongsTo{
		return $this->belongsTo(Variable::class, TrackingReminderNotification::FIELD_VARIABLE_ID, Variable::FIELD_ID,
			TrackingReminderNotification::FIELD_VARIABLE_ID);
	}
}
