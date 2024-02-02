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
use App\Models\CtSideEffect;
use App\Models\CtTreatmentSideEffect;
use App\Models\Variable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseCtTreatmentSideEffect
 * @property int $id
 * @property int $treatment_variable_id
 * @property int $side_effect_variable_id
 * @property int $treatment_id
 * @property int $side_effect_id
 * @property int $votes_percent
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property Variable $side_effect_variable
 * @property CtSideEffect $ct_side_effect
 * @property Variable $treatment_variable
 * @package App\Models\Base
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatmentSideEffect newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatmentSideEffect newQuery()
 * @method static \Illuminate\Database\Query\Builder|BaseCtTreatmentSideEffect onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatmentSideEffect query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatmentSideEffect whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatmentSideEffect whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatmentSideEffect whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatmentSideEffect whereSideEffectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatmentSideEffect whereSideEffectVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatmentSideEffect whereTreatmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatmentSideEffect whereTreatmentVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatmentSideEffect whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatmentSideEffect whereVotesPercent($value)
 * @method static \Illuminate\Database\Query\Builder|BaseCtTreatmentSideEffect withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BaseCtTreatmentSideEffect withoutTrashed()
 * @mixin \Eloquent
 */
abstract class BaseCtTreatmentSideEffect extends BaseModel {
	use SoftDeletes;
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_SIDE_EFFECT_ID = 'side_effect_id';
	public const FIELD_SIDE_EFFECT_VARIABLE_ID = 'side_effect_variable_id';
	public const FIELD_TREATMENT_ID = 'treatment_id';
	public const FIELD_TREATMENT_VARIABLE_ID = 'treatment_variable_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_VOTES_PERCENT = 'votes_percent';
	public const TABLE = 'ct_treatment_side_effect';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_ID => 'int',
		self::FIELD_SIDE_EFFECT_ID => 'int',
		self::FIELD_SIDE_EFFECT_VARIABLE_ID => 'int',
		self::FIELD_TREATMENT_ID => 'int',
		self::FIELD_TREATMENT_VARIABLE_ID => 'int',
		self::FIELD_VOTES_PERCENT => 'int',	];
	protected array $rules = [
		self::FIELD_SIDE_EFFECT_ID => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_SIDE_EFFECT_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_TREATMENT_ID => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_TREATMENT_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_VOTES_PERCENT => 'required|integer|min:-2147483648|max:2147483647',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_TREATMENT_VARIABLE_ID => '',
		self::FIELD_SIDE_EFFECT_VARIABLE_ID => '',
		self::FIELD_TREATMENT_ID => '',
		self::FIELD_SIDE_EFFECT_ID => '',
		self::FIELD_VOTES_PERCENT => '',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
	];
	protected array $relationshipInfo = [
		'side_effect_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'side_effect_variable_id',
			'foreignKey' => CtTreatmentSideEffect::FIELD_SIDE_EFFECT_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'side_effect_variable_id',
			'ownerKey' => CtTreatmentSideEffect::FIELD_SIDE_EFFECT_VARIABLE_ID,
			'methodName' => 'side_effect_variable',
		],
		'ct_side_effect' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => CtSideEffect::class,
			'foreignKeyColumnName' => 'side_effect_id',
			'foreignKey' => CtTreatmentSideEffect::FIELD_SIDE_EFFECT_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => CtSideEffect::FIELD_ID,
			'ownerKeyColumnName' => 'side_effect_id',
			'ownerKey' => CtTreatmentSideEffect::FIELD_SIDE_EFFECT_ID,
			'methodName' => 'ct_side_effect',
		],
		'treatment_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'treatment_variable_id',
			'foreignKey' => CtTreatmentSideEffect::FIELD_TREATMENT_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'treatment_variable_id',
			'ownerKey' => CtTreatmentSideEffect::FIELD_TREATMENT_VARIABLE_ID,
			'methodName' => 'treatment_variable',
		],
	];
	public function side_effect_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, CtTreatmentSideEffect::FIELD_SIDE_EFFECT_VARIABLE_ID,
			Variable::FIELD_ID, CtTreatmentSideEffect::FIELD_SIDE_EFFECT_VARIABLE_ID);
	}
	public function ct_side_effect(): BelongsTo{
		return $this->belongsTo(CtSideEffect::class, CtTreatmentSideEffect::FIELD_SIDE_EFFECT_ID,
			CtSideEffect::FIELD_ID, CtTreatmentSideEffect::FIELD_SIDE_EFFECT_ID);
	}
	public function treatment_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, CtTreatmentSideEffect::FIELD_TREATMENT_VARIABLE_ID, Variable::FIELD_ID,
			CtTreatmentSideEffect::FIELD_TREATMENT_VARIABLE_ID);
	}
}
