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
use App\Models\CtCondition;
use App\Models\Variable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseCtCondition
 * @property int $id
 * @property string $name
 * @property int $variable_id
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property int $number_of_treatments
 * @property int $number_of_symptoms
 * @property int $number_of_causes
 * @package App\Models\Base
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCondition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCondition newQuery()
 * @method static \Illuminate\Database\Query\Builder|BaseCtCondition onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCondition query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCondition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCondition whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCondition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCondition whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCondition whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCondition whereVariableId($value)
 * @method static \Illuminate\Database\Query\Builder|BaseCtCondition withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BaseCtCondition withoutTrashed()
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCondition whereNumberOfCauses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCondition whereNumberOfSymptoms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCondition whereNumberOfTreatments($value)
 */
abstract class BaseCtCondition extends BaseModel {
	use SoftDeletes;
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_NAME = 'name';
	public const FIELD_NUMBER_OF_CAUSES = 'number_of_causes';
	public const FIELD_NUMBER_OF_SYMPTOMS = 'number_of_symptoms';
	public const FIELD_NUMBER_OF_TREATMENTS = 'number_of_treatments';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_VARIABLE_ID = 'variable_id';
	public const TABLE = 'ct_conditions';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_ID => 'int',
		self::FIELD_NAME => 'string',
		self::FIELD_NUMBER_OF_CAUSES => 'int',
		self::FIELD_NUMBER_OF_SYMPTOMS => 'int',
		self::FIELD_NUMBER_OF_TREATMENTS => 'int',
		self::FIELD_VARIABLE_ID => 'int',	];
	protected array $rules = [
		self::FIELD_NAME => 'required|max:100|unique:ct_conditions,name',
		self::FIELD_NUMBER_OF_CAUSES => 'required|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_SYMPTOMS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_TREATMENTS => 'required|integer|min:0|max:2147483647',
		self::FIELD_VARIABLE_ID => 'required|integer|min:0|max:2147483647|unique:ct_conditions,variable_id',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_NAME => '',
		self::FIELD_VARIABLE_ID => '',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_NUMBER_OF_TREATMENTS => '',
		self::FIELD_NUMBER_OF_SYMPTOMS => '',
		self::FIELD_NUMBER_OF_CAUSES => '',
	];
	protected array $relationshipInfo = [
		'variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'variable_id',
			'foreignKey' => CtCondition::FIELD_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'variable_id',
			'ownerKey' => CtCondition::FIELD_VARIABLE_ID,
			'methodName' => 'variable',
		],
	];
	public function variable(): BelongsTo{
		return $this->belongsTo(Variable::class, CtCondition::FIELD_VARIABLE_ID, Variable::FIELD_ID,
			CtCondition::FIELD_VARIABLE_ID);
	}
}
