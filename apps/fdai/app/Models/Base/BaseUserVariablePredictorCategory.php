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
use App\Models\UserVariable;
use App\Models\UserVariablePredictorCategory;
use App\Models\Variable;
use App\Models\VariableCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseUserVariablePredictorCategory
 * @property int $id
 * @property int $user_variable_id
 * @property int $variable_id
 * @property int $variable_category_id
 * @property int $number_of_predictor_user_variables
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property UserVariable $user_variable
 * @property VariableCategory $variable_category
 * @property Variable $variable
 * @package App\Models\Base
 */
abstract class BaseUserVariablePredictorCategory extends BaseModel {
	use SoftDeletes;
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_NUMBER_OF_PREDICTOR_USER_VARIABLES = 'number_of_predictor_user_variables';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_VARIABLE_ID = 'user_variable_id';
	public const FIELD_VARIABLE_CATEGORY_ID = 'variable_category_id';
	public const FIELD_VARIABLE_ID = 'variable_id';
	public const TABLE = 'user_variable_predictor_category';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_ID => 'int',
		self::FIELD_NUMBER_OF_PREDICTOR_USER_VARIABLES => 'int',
		self::FIELD_USER_VARIABLE_ID => 'int',
		self::FIELD_VARIABLE_CATEGORY_ID => 'int',
		self::FIELD_VARIABLE_ID => 'int',	];
	protected array $rules = [
		self::FIELD_NUMBER_OF_PREDICTOR_USER_VARIABLES => 'required|integer|min:0|max:2147483647',
		self::FIELD_USER_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_VARIABLE_CATEGORY_ID => 'required|boolean',
		self::FIELD_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_USER_VARIABLE_ID => '',
		self::FIELD_VARIABLE_ID => '',
		self::FIELD_VARIABLE_CATEGORY_ID => '',
		self::FIELD_NUMBER_OF_PREDICTOR_USER_VARIABLES => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
	];
	protected array $relationshipInfo = [
		'user_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => UserVariable::class,
			'foreignKeyColumnName' => 'user_variable_id',
			'foreignKey' => UserVariablePredictorCategory::FIELD_USER_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => UserVariable::FIELD_ID,
			'ownerKeyColumnName' => 'user_variable_id',
			'ownerKey' => UserVariablePredictorCategory::FIELD_USER_VARIABLE_ID,
			'methodName' => 'user_variable',
		],
		'variable_category' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => VariableCategory::class,
			'foreignKeyColumnName' => 'variable_category_id',
			'foreignKey' => UserVariablePredictorCategory::FIELD_VARIABLE_CATEGORY_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => VariableCategory::FIELD_ID,
			'ownerKeyColumnName' => 'variable_category_id',
			'ownerKey' => UserVariablePredictorCategory::FIELD_VARIABLE_CATEGORY_ID,
			'methodName' => 'variable_category',
		],
		'variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'variable_id',
			'foreignKey' => UserVariablePredictorCategory::FIELD_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'variable_id',
			'ownerKey' => UserVariablePredictorCategory::FIELD_VARIABLE_ID,
			'methodName' => 'variable',
		],
	];
	public function user_variable(): BelongsTo{
		return $this->belongsTo(UserVariable::class, UserVariablePredictorCategory::FIELD_USER_VARIABLE_ID,
			UserVariable::FIELD_ID, UserVariablePredictorCategory::FIELD_USER_VARIABLE_ID);
	}
	public function variable_category(): BelongsTo{
		return $this->belongsTo(VariableCategory::class, UserVariablePredictorCategory::FIELD_VARIABLE_CATEGORY_ID,
			VariableCategory::FIELD_ID, UserVariablePredictorCategory::FIELD_VARIABLE_CATEGORY_ID);
	}
	public function variable(): BelongsTo{
		return $this->belongsTo(Variable::class, UserVariablePredictorCategory::FIELD_VARIABLE_ID, Variable::FIELD_ID,
			UserVariablePredictorCategory::FIELD_VARIABLE_ID);
	}
}
