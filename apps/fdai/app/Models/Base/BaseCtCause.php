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
use App\Models\CtCause;
use App\Models\CtConditionCause;
use App\Models\Variable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseCtCause
 * @property int $id
 * @property string $name
 * @property int $variable_id
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property int $number_of_conditions
 * @property Variable $variable
 * @package App\Models\Base
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCause newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCause newQuery()
 * @method static \Illuminate\Database\Query\Builder|BaseCtCause onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCause query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCause whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCause whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCause whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCause whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCause whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCause whereVariableId($value)
 * @method static \Illuminate\Database\Query\Builder|BaseCtCause withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BaseCtCause withoutTrashed()
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCause whereNumberOfConditions($value)
 */
abstract class BaseCtCause extends BaseModel {
	use SoftDeletes;
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_NAME = 'name';
	public const FIELD_NUMBER_OF_CONDITIONS = 'number_of_conditions';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_VARIABLE_ID = 'variable_id';
	public const TABLE = 'ct_causes';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'Causes of illness';
	protected $casts = [
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_ID => 'int',
		self::FIELD_NAME => 'string',
		self::FIELD_NUMBER_OF_CONDITIONS => 'int',
		self::FIELD_VARIABLE_ID => 'int',	];
	protected array $rules = [
		self::FIELD_NAME => 'required|max:100|unique:ct_causes,name',
		self::FIELD_NUMBER_OF_CONDITIONS => 'required|integer|min:0|max:2147483647',
		self::FIELD_VARIABLE_ID => 'required|integer|min:0|max:2147483647|unique:ct_causes,variable_id',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_NAME => '',
		self::FIELD_VARIABLE_ID => '',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_NUMBER_OF_CONDITIONS => '',
	];
	protected array $relationshipInfo = [
		'variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'variable_id',
			'foreignKey' => CtCause::FIELD_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'variable_id',
			'ownerKey' => CtCause::FIELD_VARIABLE_ID,
			'methodName' => 'variable',
		],
	];
	public function variable(): BelongsTo{
		return $this->belongsTo(Variable::class, CtCause::FIELD_VARIABLE_ID, Variable::FIELD_ID,
			CtCause::FIELD_VARIABLE_ID);
	}
	public function intuitive_condition_cause_votes(): \Illuminate\Database\Eloquent\Relations\HasMany{
		return $this->hasMany(CtConditionCause::class, CtConditionCause::FIELD_CAUSE_ID, static::FIELD_ID);
	}
}
