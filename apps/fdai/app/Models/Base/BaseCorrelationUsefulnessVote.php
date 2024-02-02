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
use App\Models\GlobalVariableRelationship;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Models\CorrelationUsefulnessVote;
use App\Models\OAClient;
use App\Models\Variable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseCorrelationUsefulnessVote
 * @property int $id
 * @property int $cause_variable_id
 * @property int $effect_variable_id
 * @property int $correlation_id
 * @property int $global_variable_relationship_id
 * @property int $user_id
 * @property int $vote
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property string $client_id
 * @property bool $is_public
 * @property GlobalVariableRelationship $global_variable_relationship
 * @property Variable $cause_variable
 * @property OAClient $oa_client
 * @property UserVariableRelationship $correlation
 * @property Variable $effect_variable
 * @property \App\Models\User $user
 * @package App\Models\Base
 * @property mixed $raw
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCorrelationUsefulnessVote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCorrelationUsefulnessVote newQuery()
 * @method static \Illuminate\Database\Query\Builder|BaseCorrelationUsefulnessVote onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCorrelationUsefulnessVote query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCorrelationUsefulnessVote
 *     whereGlobalVariableRelationshipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCorrelationUsefulnessVote whereCauseVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCorrelationUsefulnessVote whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCorrelationUsefulnessVote whereCorrelationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCorrelationUsefulnessVote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCorrelationUsefulnessVote whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCorrelationUsefulnessVote whereEffectVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCorrelationUsefulnessVote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCorrelationUsefulnessVote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCorrelationUsefulnessVote whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCorrelationUsefulnessVote whereVote($value)
 * @method static \Illuminate\Database\Query\Builder|BaseCorrelationUsefulnessVote withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BaseCorrelationUsefulnessVote withoutTrashed()
 * @mixin \Eloquent
 */
abstract class BaseCorrelationUsefulnessVote extends BaseModel {
	use SoftDeletes;
	public const FIELD_AGGREGATE_CORRELATION_ID = 'global_variable_relationship_id';
	public const FIELD_CAUSE_VARIABLE_ID = 'cause_variable_id';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CORRELATION_ID = 'correlation_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_EFFECT_VARIABLE_ID = 'effect_variable_id';
	public const FIELD_ID = 'id';
	public const FIELD_IS_PUBLIC = 'is_public';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_VOTE = 'vote';
	public const TABLE = 'correlation_usefulness_votes';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'The opinion of the data owner on whether or not knowledge of this
                relationship is useful in helping them improve an outcome of interest.
                -1 corresponds to a down vote. 1 corresponds to an up vote. 0 corresponds to removal of a
                previous vote.  null corresponds to never having voted before.';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_AGGREGATE_CORRELATION_ID => 'int',
		self::FIELD_CAUSE_VARIABLE_ID => 'int',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_CORRELATION_ID => 'int',
		self::FIELD_EFFECT_VARIABLE_ID => 'int',
		self::FIELD_ID => 'int',
		self::FIELD_IS_PUBLIC => 'bool',
		self::FIELD_USER_ID => 'int',
		self::FIELD_VOTE => 'int',	];
	protected array $rules = [
		self::FIELD_AGGREGATE_CORRELATION_ID => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_CAUSE_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_CORRELATION_ID => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_EFFECT_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_IS_PUBLIC => 'nullable|boolean',
		self::FIELD_USER_ID => 'required|numeric|min:0',
		self::FIELD_VOTE => 'required|integer|min:-2147483648|max:2147483647',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_CAUSE_VARIABLE_ID => '',
		self::FIELD_EFFECT_VARIABLE_ID => '',
		self::FIELD_CORRELATION_ID => '',
		self::FIELD_AGGREGATE_CORRELATION_ID => '',
		self::FIELD_USER_ID => '',
		self::FIELD_VOTE => 'The opinion of the data owner on whether or not knowledge of this
                    relationship is useful in helping them improve an outcome of interest.
                    -1 corresponds to a down vote. 1 corresponds to an up vote. 0 corresponds to removal of a
                    previous vote.  null corresponds to never having voted before.',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_IS_PUBLIC => '',
	];
	protected array $relationshipInfo = [
		'global_variable_relationship' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => GlobalVariableRelationship::class,
			'foreignKeyColumnName' => 'global_variable_relationship_id',
			'foreignKey' => CorrelationUsefulnessVote::FIELD_AGGREGATE_CORRELATION_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => GlobalVariableRelationship::FIELD_ID,
			'ownerKeyColumnName' => 'global_variable_relationship_id',
			'ownerKey' => CorrelationUsefulnessVote::FIELD_AGGREGATE_CORRELATION_ID,
			'methodName' => 'global_variable_relationship',
		],
		'cause_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'cause_variable_id',
			'foreignKey' => CorrelationUsefulnessVote::FIELD_CAUSE_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'cause_variable_id',
			'ownerKey' => CorrelationUsefulnessVote::FIELD_CAUSE_VARIABLE_ID,
			'methodName' => 'cause_variable',
		],
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => CorrelationUsefulnessVote::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => CorrelationUsefulnessVote::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'correlation' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => UserVariableRelationship::class,
			'foreignKeyColumnName' => 'correlation_id',
			'foreignKey' => CorrelationUsefulnessVote::FIELD_CORRELATION_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => UserVariableRelationship::FIELD_ID,
			'ownerKeyColumnName' => 'correlation_id',
			'ownerKey' => CorrelationUsefulnessVote::FIELD_CORRELATION_ID,
			'methodName' => 'correlation',
		],
		'effect_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'effect_variable_id',
			'foreignKey' => CorrelationUsefulnessVote::FIELD_EFFECT_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'effect_variable_id',
			'ownerKey' => CorrelationUsefulnessVote::FIELD_EFFECT_VARIABLE_ID,
			'methodName' => 'effect_variable',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => CorrelationUsefulnessVote::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => CorrelationUsefulnessVote::FIELD_USER_ID,
			'methodName' => 'user',
		],
	];
	public function global_variable_relationship(): BelongsTo{
		return $this->belongsTo(GlobalVariableRelationship::class, CorrelationUsefulnessVote::FIELD_AGGREGATE_CORRELATION_ID,
			GlobalVariableRelationship::FIELD_ID, CorrelationUsefulnessVote::FIELD_AGGREGATE_CORRELATION_ID);
	}
	public function cause_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, CorrelationUsefulnessVote::FIELD_CAUSE_VARIABLE_ID, Variable::FIELD_ID,
			CorrelationUsefulnessVote::FIELD_CAUSE_VARIABLE_ID);
	}
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, CorrelationUsefulnessVote::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			CorrelationUsefulnessVote::FIELD_CLIENT_ID);
	}
	public function correlation(): BelongsTo{
		return $this->belongsTo(UserVariableRelationship::class, CorrelationUsefulnessVote::FIELD_CORRELATION_ID,
			UserVariableRelationship::FIELD_ID, CorrelationUsefulnessVote::FIELD_CORRELATION_ID);
	}
	public function effect_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, CorrelationUsefulnessVote::FIELD_EFFECT_VARIABLE_ID,
			Variable::FIELD_ID, CorrelationUsefulnessVote::FIELD_EFFECT_VARIABLE_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, CorrelationUsefulnessVote::FIELD_USER_ID,
			\App\Models\User::FIELD_ID, CorrelationUsefulnessVote::FIELD_USER_ID);
	}
}
