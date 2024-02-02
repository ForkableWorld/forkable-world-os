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
use App\Models\CtTreatment;
use App\Models\CtTreatmentSideEffect;
use App\Models\Variable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseCtTreatment
 * @property int $id
 * @property string $name
 * @property int $variable_id
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property int $number_of_conditions
 * @property int $number_of_side_effects
 * @property Collection|CtTreatmentSideEffect[] $ct_treatment_side_effects
 * @package App\Models\Base
 * @property-read int|null $ct_treatment_side_effects_count
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatment newQuery()
 * @method static \Illuminate\Database\Query\Builder|BaseCtTreatment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatment query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtTreatment whereVariableId($value)
 * @method static \Illuminate\Database\Query\Builder|BaseCtTreatment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BaseCtTreatment withoutTrashed()
 * @mixin \Eloquent
 * @method static Builder|BaseCtTreatment whereNumberOfSideEffects($value)
 * @method static Builder|BaseCtTreatment whereNumberOfSymptoms($value)
 * @method static Builder|BaseCtTreatment whereNumberOfConditions($value)
 */
abstract class BaseCtTreatment extends BaseModel {
	use SoftDeletes;
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_NAME = 'name';
	public const FIELD_NUMBER_OF_CONDITIONS = 'number_of_conditions';
	public const FIELD_NUMBER_OF_SIDE_EFFECTS = 'number_of_side_effects';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_VARIABLE_ID = 'variable_id';
	public const TABLE = 'ct_treatments';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_ID => 'int',
		self::FIELD_NAME => 'string',
		self::FIELD_NUMBER_OF_CONDITIONS => 'int',
		self::FIELD_NUMBER_OF_SIDE_EFFECTS => 'int',
		self::FIELD_VARIABLE_ID => 'int',	];
	protected array $rules = [
		self::FIELD_NAME => 'required|max:100|unique:ct_treatments,name',
		self::FIELD_NUMBER_OF_CONDITIONS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_SIDE_EFFECTS => 'required|integer|min:0|max:2147483647',
		self::FIELD_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_NAME => '',
		self::FIELD_VARIABLE_ID => '',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_NUMBER_OF_CONDITIONS => '',
		self::FIELD_NUMBER_OF_SIDE_EFFECTS => '',
	];
	protected array $relationshipInfo = [
		'variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'variable_id',
			'foreignKey' => CtTreatment::FIELD_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'variable_id',
			'ownerKey' => CtTreatment::FIELD_VARIABLE_ID,
			'methodName' => 'variable',
		],
		'ct_treatment_side_effects' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CtTreatmentSideEffect::class,
			'foreignKey' => CtTreatmentSideEffect::FIELD_TREATMENT_ID,
			'localKey' => CtTreatmentSideEffect::FIELD_ID,
			'methodName' => 'ct_treatment_side_effects',
		],
	];
	public function variable(): BelongsTo{
		return $this->belongsTo(Variable::class, CtTreatment::FIELD_VARIABLE_ID, Variable::FIELD_ID,
			CtTreatment::FIELD_VARIABLE_ID);
	}
	public function ct_treatment_side_effects(): HasMany{
		return $this->hasMany(CtTreatmentSideEffect::class, CtTreatmentSideEffect::FIELD_TREATMENT_ID,
			static::FIELD_ID);
	}
	public function ct_condition_treatments(): \Illuminate\Database\Eloquent\Relations\HasMany{
		return $this->hasMany(CtConditionTreatment::class, CtConditionTreatment::FIELD_TREATMENT_ID, static::FIELD_ID);
	}
}
