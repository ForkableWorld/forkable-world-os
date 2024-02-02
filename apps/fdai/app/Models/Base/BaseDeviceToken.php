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
use App\Models\DeviceToken;
use App\Models\OAClient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseDeviceToken
 * @property string $device_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property int $user_id
 * @property int $number_of_waiting_tracking_reminder_notifications
 * @property Carbon $last_notified_at
 * @property string $client_id
 * @property string $platform
 * @property int $number_of_new_tracking_reminder_notifications
 * @property int $number_of_notifications_last_sent
 * @property string $error_message
 * @property Carbon $last_checked_at
 * @property Carbon $received_at
 * @property string $server_ip
 * @property string $server_hostname
 * @property OAClient $oa_client
 * @property \App\Models\User $user
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseDeviceToken onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken whereOAClientsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken whereDeviceToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken whereLastCheckedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken whereLastNotifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken
 *     whereNumberOfNewTrackingReminderNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken
 *     whereNumberOfNotificationsLastSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken
 *     whereNumberOfWaitingTrackingReminderNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken whereReceivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken whereServerHostname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken whereServerIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseDeviceToken whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseDeviceToken withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseDeviceToken withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseDeviceToken extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_DEVICE_TOKEN = 'device_token';
	public const FIELD_ERROR_MESSAGE = 'error_message';
	public const FIELD_LAST_CHECKED_AT = 'last_checked_at';
	public const FIELD_LAST_NOTIFIED_AT = 'last_notified_at';
	public const FIELD_NUMBER_OF_NEW_TRACKING_REMINDER_NOTIFICATIONS = 'number_of_new_tracking_reminder_notifications';
	public const FIELD_NUMBER_OF_NOTIFICATIONS_LAST_SENT = 'number_of_notifications_last_sent';
	public const FIELD_NUMBER_OF_WAITING_TRACKING_REMINDER_NOTIFICATIONS = 'number_of_waiting_tracking_reminder_notifications';
	public const FIELD_PLATFORM = 'platform';
	public const FIELD_RECEIVED_AT = 'received_at';
	public const FIELD_SERVER_HOSTNAME = 'server_hostname';
	public const FIELD_SERVER_IP = 'server_ip';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const TABLE = 'device_tokens';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $primaryKey = 'device_token';
	public $incrementing = false;
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_LAST_NOTIFIED_AT => 'datetime',
        self::FIELD_LAST_CHECKED_AT => 'datetime',
        self::FIELD_RECEIVED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_DEVICE_TOKEN => 'string',
		self::FIELD_ERROR_MESSAGE => 'string',
		self::FIELD_NUMBER_OF_NEW_TRACKING_REMINDER_NOTIFICATIONS => 'int',
		self::FIELD_NUMBER_OF_NOTIFICATIONS_LAST_SENT => 'int',
		self::FIELD_NUMBER_OF_WAITING_TRACKING_REMINDER_NOTIFICATIONS => 'int',
		self::FIELD_PLATFORM => 'string',
		self::FIELD_SERVER_HOSTNAME => 'string',
		self::FIELD_SERVER_IP => 'string',
		self::FIELD_USER_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_DEVICE_TOKEN => 'required|max:255|unique:device_tokens,device_token',
		self::FIELD_ERROR_MESSAGE => 'nullable|max:255',
		self::FIELD_LAST_CHECKED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LAST_NOTIFIED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_NUMBER_OF_NEW_TRACKING_REMINDER_NOTIFICATIONS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_NOTIFICATIONS_LAST_SENT => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_WAITING_TRACKING_REMINDER_NOTIFICATIONS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_PLATFORM => 'required|max:255',
		self::FIELD_RECEIVED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_SERVER_HOSTNAME => 'nullable|max:255',
		self::FIELD_SERVER_IP => 'nullable|max:255',
		self::FIELD_USER_ID => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_DEVICE_TOKEN => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_USER_ID => '',
		self::FIELD_NUMBER_OF_WAITING_TRACKING_REMINDER_NOTIFICATIONS => 'Number of notifications waiting in the reminder inbox',
		self::FIELD_LAST_NOTIFIED_AT => 'datetime',
		self::FIELD_PLATFORM => '',
		self::FIELD_NUMBER_OF_NEW_TRACKING_REMINDER_NOTIFICATIONS => 'Number of notifications that have come due since last notification',
		self::FIELD_NUMBER_OF_NOTIFICATIONS_LAST_SENT => 'Number of notifications that were sent at last_notified_at batch',
		self::FIELD_ERROR_MESSAGE => '',
		self::FIELD_LAST_CHECKED_AT => 'datetime',
		self::FIELD_RECEIVED_AT => 'datetime',
		self::FIELD_SERVER_IP => '',
		self::FIELD_SERVER_HOSTNAME => '',
		self::FIELD_CLIENT_ID => '',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => DeviceToken::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => DeviceToken::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => DeviceToken::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => DeviceToken::FIELD_USER_ID,
			'methodName' => 'user',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, DeviceToken::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			DeviceToken::FIELD_CLIENT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, DeviceToken::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			DeviceToken::FIELD_USER_ID);
	}
}
