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
use App\Models\OAAccessToken;
use App\Models\OAClient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseOAAccessToken
 * @property string $access_token
 * @property string $client_id
 * @property int $user_id
 * @property Carbon $expires
 * @property string $scope
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property OAClient $oa_client
 * @property \App\Models\User $user
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAccessToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAccessToken newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseOAAccessToken onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAccessToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAccessToken whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAccessToken whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAccessToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAccessToken whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAccessToken whereExpires($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAccessToken whereScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAccessToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAccessToken whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseOAAccessToken withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseOAAccessToken withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseOAAccessToken extends BaseModel {
	use SoftDeletes;
	public const FIELD_ACCESS_TOKEN = 'access_token';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_EXPIRES = 'expires';
	public const FIELD_SCOPE = 'scope';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const TABLE = 'oa_access_tokens';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $primaryKey = 'access_token';
	public $incrementing = false;
	protected $casts = [
        self::FIELD_EXPIRES => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_ACCESS_TOKEN => 'string',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_SCOPE => 'string',
		self::FIELD_USER_ID => 'int',	];
	protected array $rules = [
		self::FIELD_ACCESS_TOKEN => 'required|max:40|unique:oa_access_tokens,access_token',
		self::FIELD_CLIENT_ID => 'required|max:80',
		self::FIELD_EXPIRES => 'nullable|date',
		self::FIELD_SCOPE => 'nullable|max:2000',
		self::FIELD_USER_ID => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ACCESS_TOKEN => '',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_USER_ID => '',
		self::FIELD_EXPIRES => '',
		self::FIELD_SCOPE => '',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => OAAccessToken::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => OAAccessToken::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => OAAccessToken::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => OAAccessToken::FIELD_USER_ID,
			'methodName' => 'user',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, OAAccessToken::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			OAAccessToken::FIELD_CLIENT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, OAAccessToken::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			OAAccessToken::FIELD_USER_ID);
	}
}
