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
use App\Models\CtConditionTreatment;
use App\Models\Variable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseCtConditionTreatment
 * @property int $id
 * @property int $condition_id
 * @property int $treatment_id
 * @property int $condition_variable_id
 * @property int $treatment_variable_id
 * @property int $major_improvement
 * @property int $moderate_improvement
 * @property int $no_effect
 * @property int $worse
 * @property int $much_worse
 * @property int $popularity
 * @property int $average_effect
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property Variable $treatment_variable
 * @property Variable $condition_variable
 * @package App\Models\Base
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionTreatment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionTreatment newQuery()
 * @method static \Illuminate\Database\Query\Builder|BaseCtConditionTreatment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionTreatment query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionTreatment whereAverageEffect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionTreatment whereConditionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionTreatment whereConditionVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionTreatment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionTreatment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionTreatment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionTreatment whereMajorImprovement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionTreatment whereModerateImprovement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionTreatment whereMuchWorse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionTreatment whereNoEffect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionTreatment wherePopularity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionTreatment whereTreatmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionTreatment whereTreatmentVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionTreatment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionTreatment whereWorse($value)
 * @method static \Illuminate\Database\Query\Builder|BaseCtConditionTreatment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BaseCtConditionTreatment withoutTrashed()
 * @mixin \Eloquent
 */
abstract class BaseCtConditionTreatment extends BaseModel {
	use SoftDeletes;
	public const FIELD_AVERAGE_EFFECT = 'average_effect';
	public const FIELD_CONDITION_ID = 'condition_id';
	public const FIELD_CONDITION_VARIABLE_ID = 'condition_variable_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_MAJOR_IMPROVEMENT = 'major_improvement';
	public const FIELD_MODERATE_IMPROVEMENT = 'moderate_improvement';
	public const FIELD_MUCH_WORSE = 'much_worse';
	public const FIELD_NO_EFFECT = 'no_effect';
	public const FIELD_POPULARITY = 'popularity';
	public const FIELD_TREATMENT_ID = 'treatment_id';
	public const FIELD_TREATMENT_VARIABLE_ID = 'treatment_variable_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_WORSE = 'worse';
	public const TABLE = 'ct_condition_treatment';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_AVERAGE_EFFECT => 'int',
		self::FIELD_CONDITION_ID => 'int',
		self::FIELD_CONDITION_VARIABLE_ID => 'int',
		self::FIELD_ID => 'int',
		self::FIELD_MAJOR_IMPROVEMENT => 'int',
		self::FIELD_MODERATE_IMPROVEMENT => 'int',
		self::FIELD_MUCH_WORSE => 'int',
		self::FIELD_NO_EFFECT => 'int',
		self::FIELD_POPULARITY => 'int',
		self::FIELD_TREATMENT_ID => 'int',
		self::FIELD_TREATMENT_VARIABLE_ID => 'int',
		self::FIELD_WORSE => 'int',	];
	protected array $rules = [
		self::FIELD_AVERAGE_EFFECT => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_CONDITION_ID => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_CONDITION_VARIABLE_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_MAJOR_IMPROVEMENT => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_MODERATE_IMPROVEMENT => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_MUCH_WORSE => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_NO_EFFECT => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_POPULARITY => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_TREATMENT_ID => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_TREATMENT_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_WORSE => 'required|integer|min:-2147483648|max:2147483647',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_CONDITION_ID => '',
		self::FIELD_TREATMENT_ID => '',
		self::FIELD_CONDITION_VARIABLE_ID => '',
		self::FIELD_TREATMENT_VARIABLE_ID => '',
		self::FIELD_MAJOR_IMPROVEMENT => '',
		self::FIELD_MODERATE_IMPROVEMENT => '',
		self::FIELD_NO_EFFECT => '',
		self::FIELD_WORSE => '',
		self::FIELD_MUCH_WORSE => '',
		self::FIELD_POPULARITY => '',
		self::FIELD_AVERAGE_EFFECT => '',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
	];
	protected array $relationshipInfo = [
		'treatment_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'treatment_variable_id',
			'foreignKey' => CtConditionTreatment::FIELD_TREATMENT_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'treatment_variable_id',
			'ownerKey' => CtConditionTreatment::FIELD_TREATMENT_VARIABLE_ID,
			'methodName' => 'treatment_variable',
		],
		'condition_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'condition_variable_id',
			'foreignKey' => CtConditionTreatment::FIELD_CONDITION_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'condition_variable_id',
			'ownerKey' => CtConditionTreatment::FIELD_CONDITION_VARIABLE_ID,
			'methodName' => 'condition_variable',
		],
	];
	public function treatment_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, CtConditionTreatment::FIELD_TREATMENT_VARIABLE_ID, Variable::FIELD_ID,
			CtConditionTreatment::FIELD_TREATMENT_VARIABLE_ID);
	}
	public function condition_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, CtConditionTreatment::FIELD_CONDITION_VARIABLE_ID, Variable::FIELD_ID,
			CtConditionTreatment::FIELD_CONDITION_VARIABLE_ID);
	}
}
