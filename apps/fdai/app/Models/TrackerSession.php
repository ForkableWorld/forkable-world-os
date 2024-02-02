<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseTrackerSession;
use App\Traits\HasModel\HasUser;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\TrackerSession
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property int|null $device_id
 * @property int|null $agent_id
 * @property string $client_ip
 * @property int|null $referer_id
 * @property int|null $cookie_id
 * @property int|null $geoip_id
 * @property bool $is_robot
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $client_id
 * @method static Builder|TrackerSession newModelQuery()
 * @method static Builder|TrackerSession newQuery()
 * @method static Builder|TrackerSession query()
 * @method static Builder|TrackerSession whereAgentId($value)
 * @method static Builder|TrackerSession whereClientId($value)
 * @method static Builder|TrackerSession whereClientIp($value)
 * @method static Builder|TrackerSession whereCookieId($value)
 * @method static Builder|TrackerSession whereCreatedAt($value)
 * @method static Builder|TrackerSession whereDeletedAt($value)
 * @method static Builder|TrackerSession whereDeviceId($value)
 * @method static Builder|TrackerSession whereGeoipId($value)
 * @method static Builder|TrackerSession whereId($value)
 * @method static Builder|TrackerSession whereIsRobot($value)
 * @method static Builder|TrackerSession whereRefererId($value)
 * @method static Builder|TrackerSession whereUpdatedAt($value)
 * @method static Builder|TrackerSession whereUserId($value)
 * @method static Builder|TrackerSession whereUuid($value)
 * @mixin Eloquent
 * @property-read OAClient|null $oa_client
 * @property-read User $user
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient|null $client
 */
class TrackerSession extends BaseTrackerSession {
	public const CLASS_DESCRIPTION = "An way to make data accessible across the various pages of an entire website is to use a PHP Session.";
	use HasUser;
	protected array $rules = [
		self::FIELD_UUID => 'required|max:255', //|unique:tracker_sessions,uuid',  // Unique checks too slow
		self::FIELD_USER_ID => 'required|numeric|min:1',
		self::FIELD_DEVICE_ID => 'nullable|numeric|min:1',
		self::FIELD_AGENT_ID => 'nullable|numeric|min:1',
		self::FIELD_CLIENT_IP => 'required|max:255',
		self::FIELD_REFERER_ID => 'nullable|numeric|min:1',
		self::FIELD_COOKIE_ID => 'nullable|numeric|min:1',
		self::FIELD_GEOIP_ID => 'nullable|numeric|min:1',
		self::FIELD_IS_ROBOT => 'required|boolean',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
	];

	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
}
