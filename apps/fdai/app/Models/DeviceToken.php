<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseDeviceToken;
use App\Slim\Model\Notifications\QMDeviceToken;
use App\Traits\HasDBModel;
use App\Traits\HasErrors;
use App\Traits\HasModel\HasUser;
use App\Traits\ModelTraits\DeviceTokenTrait;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
/**
 * Class DeviceToken
 * @property string $device_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
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
 * @property User $user
 * @package App\Models
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken newQuery()
 * @method static Builder|DeviceToken onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken whereOAClientsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken whereDeviceToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken whereLastCheckedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken whereLastNotifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken
 *     whereNumberOfNewTrackingReminderNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken whereNumberOfNotificationsLastSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken
 *     whereNumberOfWaitingTrackingReminderNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken whereReceivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken whereServerHostname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken whereServerIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceToken whereWebSubscription($value)
 * @method static Builder|DeviceToken withTrashed()
 * @method static Builder|DeviceToken withoutTrashed()
 * @mixin \Eloquent
 * @property-read OAClient|null $oa_client
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient|null $client
 */
class DeviceToken extends BaseDeviceToken {
    use HasFactory;

	use DeviceTokenTrait;
	use HasUser, SoftDeletes, HasErrors, HasDBModel;
	public static function getSlimClass(): string{ return QMDeviceToken::class; }
	public const CLASS_DESCRIPTION = "User token needed to send Android, iOS, or web push notifications. ";
	public const FIELD_ID = self::FIELD_DEVICE_TOKEN;
	public const FONT_AWESOME = FontAwesome::SMS_SOLID;
	public const DEFAULT_IMAGE = ImageUrls::ESSENTIAL_COLLECTION_NOTIFICATION;
	public const DEFAULT_SEARCH_FIELD = null;
	public static function getUniqueIndexColumns(): array{
		return [
			self::FIELD_DEVICE_TOKEN,
		];
	}
	const CLASS_CATEGORY = "Messaging";
	public $guarded = [];
	public $incrementing = false;
	protected $hidden = [// Do not hide! 'device_token'
	];

	public function getId(){
		return $this->device_token;
	}
	public static function find($id, $columns = []){
		$qb = static::whereDeviceToken($id);
		return $qb->first();
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
	public function getFillable(): array {
		return static::getColumns();
	}
}
