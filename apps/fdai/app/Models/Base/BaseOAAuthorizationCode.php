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
use App\Models\OAAuthorizationCode;
use App\Models\OAClient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseOAAuthorizationCode
 * @property string $authorization_code
 * @property string $client_id
 * @property int $user_id
 * @property string $redirect_uri
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAuthorizationCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAuthorizationCode newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseOAAuthorizationCode onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAuthorizationCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAuthorizationCode
 *     whereAuthorizationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAuthorizationCode whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAuthorizationCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAuthorizationCode whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAuthorizationCode whereExpires($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAuthorizationCode
 *     whereRedirectUri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAuthorizationCode whereScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAuthorizationCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAAuthorizationCode whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseOAAuthorizationCode withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseOAAuthorizationCode withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseOAAuthorizationCode extends BaseModel {
	use SoftDeletes;
	public const FIELD_AUTHORIZATION_CODE = 'authorization_code';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_EXPIRES = 'expires';
	public const FIELD_REDIRECT_URI = 'redirect_uri';
	public const FIELD_SCOPE = 'scope';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const TABLE = 'oa_authorization_codes';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $primaryKey = 'authorization_code';
	public $incrementing = false;
	protected $casts = [
        self::FIELD_EXPIRES => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_AUTHORIZATION_CODE => 'string',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_REDIRECT_URI => 'string',
		self::FIELD_SCOPE => 'string',
		self::FIELD_USER_ID => 'int',	];
	protected array $rules = [
		self::FIELD_AUTHORIZATION_CODE => 'required|max:40|unique:oa_authorization_codes,authorization_code',
		self::FIELD_CLIENT_ID => 'required|max:80',
		self::FIELD_EXPIRES => 'nullable|date',
		self::FIELD_REDIRECT_URI => 'nullable|max:2000',
		self::FIELD_SCOPE => 'nullable|max:2000',
		self::FIELD_USER_ID => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_AUTHORIZATION_CODE => '',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_USER_ID => '',
		self::FIELD_REDIRECT_URI => '',
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
			'foreignKey' => OAAuthorizationCode::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => OAAuthorizationCode::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => OAAuthorizationCode::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => OAAuthorizationCode::FIELD_USER_ID,
			'methodName' => 'user',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, OAAuthorizationCode::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			OAAuthorizationCode::FIELD_CLIENT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, OAAuthorizationCode::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			OAAuthorizationCode::FIELD_USER_ID);
	}
}
