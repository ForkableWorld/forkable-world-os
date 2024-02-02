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
use App\Models\CtConditionCause;
use App\Models\Variable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseCtConditionCause
 * @property int $id
 * @property int $condition_id
 * @property int $cause_id
 * @property int $condition_variable_id
 * @property int $cause_variable_id
 * @property int $votes_percent
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property Variable $condition_variable
 * @property Variable $cause_variable
 * @package App\Models\Base
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionCause newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionCause newQuery()
 * @method static \Illuminate\Database\Query\Builder|BaseCtConditionCause onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionCause query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionCause whereCauseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionCause whereCauseVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionCause whereConditionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionCause whereConditionVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionCause whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionCause whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionCause whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionCause whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionCause whereVotesPercent($value)
 * @method static \Illuminate\Database\Query\Builder|BaseCtConditionCause withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BaseCtConditionCause withoutTrashed()
 * @mixin \Eloquent
 */
abstract class BaseCtConditionCause extends BaseModel {
	use SoftDeletes;
	public const FIELD_CAUSE_ID = 'cause_id';
	public const FIELD_CAUSE_VARIABLE_ID = 'cause_variable_id';
	public const FIELD_CONDITION_ID = 'condition_id';
	public const FIELD_CONDITION_VARIABLE_ID = 'condition_variable_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_VOTES_PERCENT = 'votes_percent';
	public const TABLE = 'intuitive_condition_cause_votes';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CAUSE_ID => 'int',
		self::FIELD_CAUSE_VARIABLE_ID => 'int',
		self::FIELD_CONDITION_ID => 'int',
		self::FIELD_CONDITION_VARIABLE_ID => 'int',
		self::FIELD_ID => 'int',
		self::FIELD_VOTES_PERCENT => 'int',	];
	protected array $rules = [
		self::FIELD_CAUSE_ID => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_CAUSE_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_CONDITION_ID => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_CONDITION_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_VOTES_PERCENT => 'required|integer|min:-2147483648|max:2147483647',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_CONDITION_ID => '',
		self::FIELD_CAUSE_ID => '',
		self::FIELD_CONDITION_VARIABLE_ID => '',
		self::FIELD_CAUSE_VARIABLE_ID => '',
		self::FIELD_VOTES_PERCENT => '',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
	];
	protected array $relationshipInfo = [
		'condition_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'condition_variable_id',
			'foreignKey' => CtConditionCause::FIELD_CONDITION_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'condition_variable_id',
			'ownerKey' => CtConditionCause::FIELD_CONDITION_VARIABLE_ID,
			'methodName' => 'condition_variable',
		],
		'cause_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'cause_variable_id',
			'foreignKey' => CtConditionCause::FIELD_CAUSE_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'cause_variable_id',
			'ownerKey' => CtConditionCause::FIELD_CAUSE_VARIABLE_ID,
			'methodName' => 'cause_variable',
		],
	];
	public function condition_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, CtConditionCause::FIELD_CONDITION_VARIABLE_ID, Variable::FIELD_ID,
			CtConditionCause::FIELD_CONDITION_VARIABLE_ID);
	}
	public function cause_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, CtConditionCause::FIELD_CAUSE_VARIABLE_ID, Variable::FIELD_ID,
			CtConditionCause::FIELD_CAUSE_VARIABLE_ID);
	}
}
