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
use App\Models\UserTag;
use App\Models\UserVariable;
use App\Models\Variable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseUserTag
 * @property int $id
 * @property int $tagged_variable_id
 * @property int $tag_variable_id
 * @property float $conversion_factor
 * @property int $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $client_id
 * @property Carbon $deleted_at
 * @property int $tagged_user_variable_id
 * @property int $tag_user_variable_id
 * @property OAClient $oa_client
 * @property UserVariable $tag_user_variable
 * @property Variable $tag_variable
 * @property UserVariable $tagged_user_variable
 * @property Variable $tagged_variable
 * @property \App\Models\User $user
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserTag newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUserTag onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserTag query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserTag whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserTag whereConversionFactor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserTag whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserTag whereTagVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserTag whereTaggedVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserTag whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserTag whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUserTag withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUserTag withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserTag whereTagUserVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserTag whereTaggedUserVariableId($value)
 */
abstract class BaseUserTag extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CONVERSION_FACTOR = 'conversion_factor';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_TAG_USER_VARIABLE_ID = 'tag_user_variable_id';
	public const FIELD_TAG_VARIABLE_ID = 'tag_variable_id';
	public const FIELD_TAGGED_USER_VARIABLE_ID = 'tagged_user_variable_id';
	public const FIELD_TAGGED_VARIABLE_ID = 'tagged_variable_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const TABLE = 'user_tags';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_CONVERSION_FACTOR => 'float',
		self::FIELD_ID => 'int',
		self::FIELD_TAGGED_USER_VARIABLE_ID => 'int',
		self::FIELD_TAGGED_VARIABLE_ID => 'int',
		self::FIELD_TAG_USER_VARIABLE_ID => 'int',
		self::FIELD_TAG_VARIABLE_ID => 'int',
		self::FIELD_USER_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_CONVERSION_FACTOR => 'required|numeric',
		self::FIELD_TAGGED_USER_VARIABLE_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_TAGGED_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_TAG_USER_VARIABLE_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_TAG_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_USER_ID => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_TAGGED_VARIABLE_ID => 'This is the id of the variable being tagged with an ingredient or something.',
		self::FIELD_TAG_VARIABLE_ID => 'This is the id of the ingredient variable whose value is determined based on the value of the tagged variable.',
		self::FIELD_CONVERSION_FACTOR => 'Number by which we multiply the tagged variable\'s value to obtain the tag variable\'s value',
		self::FIELD_USER_ID => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_TAGGED_USER_VARIABLE_ID => '',
		self::FIELD_TAG_USER_VARIABLE_ID => '',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => UserTag::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => UserTag::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'tag_user_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => UserVariable::class,
			'foreignKeyColumnName' => 'tag_user_variable_id',
			'foreignKey' => UserTag::FIELD_TAG_USER_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => UserVariable::FIELD_ID,
			'ownerKeyColumnName' => 'tag_user_variable_id',
			'ownerKey' => UserTag::FIELD_TAG_USER_VARIABLE_ID,
			'methodName' => 'tag_user_variable',
		],
		'tag_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'tag_variable_id',
			'foreignKey' => UserTag::FIELD_TAG_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'tag_variable_id',
			'ownerKey' => UserTag::FIELD_TAG_VARIABLE_ID,
			'methodName' => 'tag_variable',
		],
		'tagged_user_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => UserVariable::class,
			'foreignKeyColumnName' => 'tagged_user_variable_id',
			'foreignKey' => UserTag::FIELD_TAGGED_USER_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => UserVariable::FIELD_ID,
			'ownerKeyColumnName' => 'tagged_user_variable_id',
			'ownerKey' => UserTag::FIELD_TAGGED_USER_VARIABLE_ID,
			'methodName' => 'tagged_user_variable',
		],
		'tagged_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'tagged_variable_id',
			'foreignKey' => UserTag::FIELD_TAGGED_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'tagged_variable_id',
			'ownerKey' => UserTag::FIELD_TAGGED_VARIABLE_ID,
			'methodName' => 'tagged_variable',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => UserTag::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => UserTag::FIELD_USER_ID,
			'methodName' => 'user',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, UserTag::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			UserTag::FIELD_CLIENT_ID);
	}
	public function tag_user_variable(): BelongsTo{
		return $this->belongsTo(UserVariable::class, UserTag::FIELD_TAG_USER_VARIABLE_ID, UserVariable::FIELD_ID,
			UserTag::FIELD_TAG_USER_VARIABLE_ID);
	}
	public function tag_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, UserTag::FIELD_TAG_VARIABLE_ID, Variable::FIELD_ID,
			UserTag::FIELD_TAG_VARIABLE_ID);
	}
	public function tagged_user_variable(): BelongsTo{
		return $this->belongsTo(UserVariable::class, UserTag::FIELD_TAGGED_USER_VARIABLE_ID, UserVariable::FIELD_ID,
			UserTag::FIELD_TAGGED_USER_VARIABLE_ID);
	}
	public function tagged_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, UserTag::FIELD_TAGGED_VARIABLE_ID, Variable::FIELD_ID,
			UserTag::FIELD_TAGGED_VARIABLE_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, UserTag::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			UserTag::FIELD_USER_ID);
	}
}
