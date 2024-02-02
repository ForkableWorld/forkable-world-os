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
use App\Models\CtConditionSymptom;
use App\Models\Variable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseCtConditionSymptom
 * @property int $id
 * @property int $condition_variable_id
 * @property int $condition_id
 * @property int $symptom_variable_id
 * @property int $symptom_id
 * @property int $votes
 * @property int $extreme
 * @property int $severe
 * @property int $moderate
 * @property int $mild
 * @property int $minimal
 * @property int $no_symptoms
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property Carbon $created_at
 * @property Variable $condition_variable
 * @property Variable $symptom_variable
 * @package App\Models\Base
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionSymptom newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionSymptom newQuery()
 * @method static \Illuminate\Database\Query\Builder|BaseCtConditionSymptom onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionSymptom query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionSymptom whereConditionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionSymptom whereConditionVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionSymptom whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionSymptom whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionSymptom whereExtreme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionSymptom whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionSymptom whereMild($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionSymptom whereMinimal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionSymptom whereModerate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionSymptom whereNoSymptoms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionSymptom whereSevere($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionSymptom whereSymptomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionSymptom whereSymptomVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionSymptom whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtConditionSymptom whereVotes($value)
 * @method static \Illuminate\Database\Query\Builder|BaseCtConditionSymptom withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BaseCtConditionSymptom withoutTrashed()
 * @mixin \Eloquent
 */
abstract class BaseCtConditionSymptom extends BaseModel {
	use SoftDeletes;
	public const FIELD_CONDITION_ID = 'condition_id';
	public const FIELD_CONDITION_VARIABLE_ID = 'condition_variable_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_EXTREME = 'extreme';
	public const FIELD_ID = 'id';
	public const FIELD_MILD = 'mild';
	public const FIELD_MINIMAL = 'minimal';
	public const FIELD_MODERATE = 'moderate';
	public const FIELD_NO_SYMPTOMS = 'no_symptoms';
	public const FIELD_SEVERE = 'severe';
	public const FIELD_SYMPTOM_ID = 'symptom_id';
	public const FIELD_SYMPTOM_VARIABLE_ID = 'symptom_variable_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_VOTES = 'votes';
	public const TABLE = 'ct_condition_symptom';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_CONDITION_ID => 'int',
		self::FIELD_CONDITION_VARIABLE_ID => 'int',
		self::FIELD_EXTREME => 'int',
		self::FIELD_ID => 'int',
		self::FIELD_MILD => 'int',
		self::FIELD_MINIMAL => 'int',
		self::FIELD_MODERATE => 'int',
		self::FIELD_NO_SYMPTOMS => 'int',
		self::FIELD_SEVERE => 'int',
		self::FIELD_SYMPTOM_ID => 'int',
		self::FIELD_SYMPTOM_VARIABLE_ID => 'int',
		self::FIELD_VOTES => 'int',	];
	protected array $rules = [
		self::FIELD_CONDITION_ID => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_CONDITION_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_EXTREME => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_MILD => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_MINIMAL => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_MODERATE => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NO_SYMPTOMS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_SEVERE => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_SYMPTOM_ID => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_SYMPTOM_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_VOTES => 'required|integer|min:-2147483648|max:2147483647',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_CONDITION_VARIABLE_ID => '',
		self::FIELD_CONDITION_ID => '',
		self::FIELD_SYMPTOM_VARIABLE_ID => '',
		self::FIELD_SYMPTOM_ID => '',
		self::FIELD_VOTES => '',
		self::FIELD_EXTREME => '',
		self::FIELD_SEVERE => '',
		self::FIELD_MODERATE => '',
		self::FIELD_MILD => '',
		self::FIELD_MINIMAL => '',
		self::FIELD_NO_SYMPTOMS => '',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
	];
	protected array $relationshipInfo = [
		'condition_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'condition_variable_id',
			'foreignKey' => CtConditionSymptom::FIELD_CONDITION_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'condition_variable_id',
			'ownerKey' => CtConditionSymptom::FIELD_CONDITION_VARIABLE_ID,
			'methodName' => 'condition_variable',
		],
		'symptom_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'symptom_variable_id',
			'foreignKey' => CtConditionSymptom::FIELD_SYMPTOM_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'symptom_variable_id',
			'ownerKey' => CtConditionSymptom::FIELD_SYMPTOM_VARIABLE_ID,
			'methodName' => 'symptom_variable',
		],
	];
	public function condition_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, CtConditionSymptom::FIELD_CONDITION_VARIABLE_ID, Variable::FIELD_ID,
			CtConditionSymptom::FIELD_CONDITION_VARIABLE_ID);
	}
	public function symptom_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, CtConditionSymptom::FIELD_SYMPTOM_VARIABLE_ID, Variable::FIELD_ID,
			CtConditionSymptom::FIELD_SYMPTOM_VARIABLE_ID);
	}
}
