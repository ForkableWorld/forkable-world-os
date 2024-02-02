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
use App\Models\TrackerSession;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseTrackerSession
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property int $device_id
 * @property int $agent_id
 * @property string $client_ip
 * @property int $referer_id
 * @property int $cookie_id
 * @property int $geoip_id
 * @property bool $is_robot
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property string $client_id
 * @property OAClient $oa_client
 * @property \App\Models\User $user
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerSession newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseTrackerSession onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerSession query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerSession whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerSession whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerSession whereClientIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerSession whereCookieId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerSession whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerSession whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerSession whereGeoipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerSession whereIsRobot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerSession whereRefererId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerSession whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerSession whereUuid($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseTrackerSession withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseTrackerSession withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseTrackerSession extends BaseModel {
	use SoftDeletes;
	public const FIELD_AGENT_ID = 'agent_id';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CLIENT_IP = 'client_ip';
	public const FIELD_COOKIE_ID = 'cookie_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_DEVICE_ID = 'device_id';
	public const FIELD_GEOIP_ID = 'geoip_id';
	public const FIELD_ID = 'id';
	public const FIELD_IS_ROBOT = 'is_robot';
	public const FIELD_REFERER_ID = 'referer_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_UUID = 'uuid';
	public const TABLE = 'tracker_sessions';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_AGENT_ID => 'int',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_CLIENT_IP => 'string',
		self::FIELD_COOKIE_ID => 'int',
		self::FIELD_DEVICE_ID => 'int',
		self::FIELD_GEOIP_ID => 'int',
		self::FIELD_ID => 'int',
		self::FIELD_IS_ROBOT => 'bool',
		self::FIELD_REFERER_ID => 'int',
		self::FIELD_USER_ID => 'int',
		self::FIELD_UUID => 'string',	];
	protected array $rules = [
		self::FIELD_AGENT_ID => 'nullable|numeric|min:0',
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_CLIENT_IP => 'required|max:255',
		self::FIELD_COOKIE_ID => 'nullable|numeric|min:0',
		self::FIELD_DEVICE_ID => 'nullable|numeric|min:0',
		self::FIELD_GEOIP_ID => 'nullable|numeric|min:0',
		self::FIELD_IS_ROBOT => 'required|boolean',
		self::FIELD_REFERER_ID => 'nullable|numeric|min:0',
		self::FIELD_USER_ID => 'required|numeric|min:0',
		self::FIELD_UUID => 'required|max:255|unique:tracker_sessions,uuid',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_UUID => '',
		self::FIELD_USER_ID => '',
		self::FIELD_DEVICE_ID => '',
		self::FIELD_AGENT_ID => '',
		self::FIELD_CLIENT_IP => '',
		self::FIELD_REFERER_ID => '',
		self::FIELD_COOKIE_ID => '',
		self::FIELD_GEOIP_ID => '',
		self::FIELD_IS_ROBOT => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => TrackerSession::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => TrackerSession::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => TrackerSession::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => TrackerSession::FIELD_USER_ID,
			'methodName' => 'user',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, TrackerSession::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			TrackerSession::FIELD_CLIENT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, TrackerSession::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			TrackerSession::FIELD_USER_ID);
	}
}
