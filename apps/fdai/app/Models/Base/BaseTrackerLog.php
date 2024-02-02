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
use App\Models\TrackerLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseTrackerLog
 * @property int $id
 * @property int $session_id
 * @property int $path_id
 * @property int $query_id
 * @property string $method
 * @property int $route_path_id
 * @property bool $is_ajax
 * @property bool $is_secure
 * @property array $is_json
 * @property array $wants_json
 * @property int $error_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $client_id
 * @property int $user_id
 * @property Carbon $deleted_at
 * @property OAClient $oa_client
 * @property \App\Models\User $user
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseTrackerLog onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog whereErrorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog whereIsAjax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog whereIsJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog whereIsSecure($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog wherePathId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog whereQueryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog whereRoutePathId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseTrackerLog whereWantsJson($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseTrackerLog withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseTrackerLog withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseTrackerLog extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ERROR_ID = 'error_id';
	public const FIELD_ID = 'id';
	public const FIELD_IS_AJAX = 'is_ajax';
	public const FIELD_IS_JSON = 'is_json';
	public const FIELD_IS_SECURE = 'is_secure';
	public const FIELD_METHOD = 'method';
	public const FIELD_PATH_ID = 'path_id';
	public const FIELD_QUERY_ID = 'query_id';
	public const FIELD_ROUTE_PATH_ID = 'route_path_id';
	public const FIELD_SESSION_ID = 'session_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_WANTS_JSON = 'wants_json';
	public const TABLE = 'tracker_log';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_ERROR_ID => 'int',
		self::FIELD_ID => 'int',
		self::FIELD_IS_AJAX => 'bool',
		self::FIELD_IS_JSON => 'json',
		self::FIELD_IS_SECURE => 'bool',
		self::FIELD_METHOD => 'string',
		self::FIELD_PATH_ID => 'int',
		self::FIELD_QUERY_ID => 'int',
		self::FIELD_ROUTE_PATH_ID => 'int',
		self::FIELD_SESSION_ID => 'int',
		self::FIELD_USER_ID => 'int',
		self::FIELD_WANTS_JSON => 'json',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_ERROR_ID => 'nullable|numeric|min:0',
		self::FIELD_IS_AJAX => 'required|boolean',
		self::FIELD_IS_JSON => 'required|boolean',
		self::FIELD_IS_SECURE => 'required|boolean',
		self::FIELD_METHOD => 'required|max:10',
		self::FIELD_PATH_ID => 'nullable|numeric|min:0',
		self::FIELD_QUERY_ID => 'nullable|numeric|min:0',
		self::FIELD_ROUTE_PATH_ID => 'nullable|numeric|min:0',
		self::FIELD_SESSION_ID => 'nullable|numeric|min:0',
		self::FIELD_USER_ID => 'required|numeric|min:0',
		self::FIELD_WANTS_JSON => 'required|boolean',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_SESSION_ID => '',
		self::FIELD_PATH_ID => '',
		self::FIELD_QUERY_ID => '',
		self::FIELD_METHOD => '',
		self::FIELD_ROUTE_PATH_ID => '',
		self::FIELD_IS_AJAX => '',
		self::FIELD_IS_SECURE => '',
		self::FIELD_IS_JSON => '',
		self::FIELD_WANTS_JSON => '',
		self::FIELD_ERROR_ID => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_USER_ID => '',
		self::FIELD_DELETED_AT => 'datetime',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => TrackerLog::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => TrackerLog::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => TrackerLog::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => TrackerLog::FIELD_USER_ID,
			'methodName' => 'user',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, TrackerLog::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			TrackerLog::FIELD_CLIENT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, TrackerLog::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			TrackerLog::FIELD_USER_ID);
	}
}
