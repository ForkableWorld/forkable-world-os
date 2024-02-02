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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseCtCorrelation
 * @property int $id
 * @property int $user_id
 * @property float $correlation_coefficient
 * @property int $cause_variable_id
 * @property int $effect_variable_id
 * @property int $onset_delay
 * @property int $duration_of_action
 * @property int $number_of_pairs
 * @property float $value_predicting_high_outcome
 * @property float $value_predicting_low_outcome
 * @property float $optimal_pearson_product
 * @property float $vote
 * @property float $statistical_significance
 * @property int $cause_unit_id
 * @property int $cause_changes
 * @property int $effect_changes
 * @property float $qm_score
 * @property string $error
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @package App\Models\Base
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation newQuery()
 * @method static \Illuminate\Database\Query\Builder|BaseCtCorrelation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereCauseChanges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereCauseUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereCauseVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereDurationOfAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereEffectChanges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereEffectVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereNumberOfPairs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereOnsetDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereOptimalPearsonProduct($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereQmScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereStatisticalSignificance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereValuePredictingHighOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereValuePredictingLowOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtCorrelation whereVote($value)
 * @method static \Illuminate\Database\Query\Builder|BaseCtCorrelation withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BaseCtCorrelation withoutTrashed()
 * @mixin \Eloquent
 */
abstract class BaseCtCorrelation extends BaseModel {
	use SoftDeletes;
	public const FIELD_CAUSE_CHANGES = 'cause_changes';
	public const FIELD_CAUSE_UNIT_ID = 'cause_unit_id';
	public const FIELD_CAUSE_VARIABLE_ID = 'cause_variable_id';
	public const FIELD_CORRELATION_COEFFICIENT = 'correlation_coefficient';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_DURATION_OF_ACTION = 'duration_of_action';
	public const FIELD_EFFECT_CHANGES = 'effect_changes';
	public const FIELD_EFFECT_VARIABLE_ID = 'effect_variable_id';
	public const FIELD_ERROR = 'error';
	public const FIELD_ID = 'id';
	public const FIELD_NUMBER_OF_PAIRS = 'number_of_pairs';
	public const FIELD_ONSET_DELAY = 'onset_delay';
	public const FIELD_OPTIMAL_PEARSON_PRODUCT = 'optimal_pearson_product';
	public const FIELD_QM_SCORE = 'qm_score';
	public const FIELD_STATISTICAL_SIGNIFICANCE = 'statistical_significance';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_VALUE_PREDICTING_HIGH_OUTCOME = 'value_predicting_high_outcome';
	public const FIELD_VALUE_PREDICTING_LOW_OUTCOME = 'value_predicting_low_outcome';
	public const FIELD_VOTE = 'vote';
	public const TABLE = 'ct_correlations';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'Stores Calculated Correlation Coefficients';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CAUSE_CHANGES => 'int',
		self::FIELD_CAUSE_UNIT_ID => 'int',
		self::FIELD_CAUSE_VARIABLE_ID => 'int',
		self::FIELD_CORRELATION_COEFFICIENT => 'float',
		self::FIELD_DURATION_OF_ACTION => 'int',
		self::FIELD_EFFECT_CHANGES => 'int',
		self::FIELD_EFFECT_VARIABLE_ID => 'int',
		self::FIELD_ERROR => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_NUMBER_OF_PAIRS => 'int',
		self::FIELD_ONSET_DELAY => 'int',
		self::FIELD_OPTIMAL_PEARSON_PRODUCT => 'float',
		self::FIELD_QM_SCORE => 'float',
		self::FIELD_STATISTICAL_SIGNIFICANCE => 'float',
		self::FIELD_USER_ID => 'int',
		self::FIELD_VALUE_PREDICTING_HIGH_OUTCOME => 'float',
		self::FIELD_VALUE_PREDICTING_LOW_OUTCOME => 'float',
		self::FIELD_VOTE => 'float',	];
	protected array $rules = [
		self::FIELD_CAUSE_CHANGES => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_CAUSE_UNIT_ID => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_CAUSE_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_DURATION_OF_ACTION => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_EFFECT_CHANGES => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_EFFECT_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_ERROR => 'nullable|max:65535',
		self::FIELD_NUMBER_OF_PAIRS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_ONSET_DELAY => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_OPTIMAL_PEARSON_PRODUCT => 'nullable|numeric',
		self::FIELD_QM_SCORE => 'nullable|numeric',
		self::FIELD_STATISTICAL_SIGNIFICANCE => 'nullable|numeric',
		self::FIELD_USER_ID => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_VALUE_PREDICTING_HIGH_OUTCOME => 'nullable|numeric',
		self::FIELD_VALUE_PREDICTING_LOW_OUTCOME => 'nullable|numeric',
		self::FIELD_VOTE => 'nullable|numeric',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_USER_ID => '',
		self::FIELD_CORRELATION_COEFFICIENT => '',
		self::FIELD_CAUSE_VARIABLE_ID => '',
		self::FIELD_EFFECT_VARIABLE_ID => '',
		self::FIELD_ONSET_DELAY => '',
		self::FIELD_DURATION_OF_ACTION => '',
		self::FIELD_NUMBER_OF_PAIRS => '',
		self::FIELD_VALUE_PREDICTING_HIGH_OUTCOME => '',
		self::FIELD_VALUE_PREDICTING_LOW_OUTCOME => '',
		self::FIELD_OPTIMAL_PEARSON_PRODUCT => '',
		self::FIELD_VOTE => '',
		self::FIELD_STATISTICAL_SIGNIFICANCE => '',
		self::FIELD_CAUSE_UNIT_ID => '',
		self::FIELD_CAUSE_CHANGES => '',
		self::FIELD_EFFECT_CHANGES => '',
		self::FIELD_QM_SCORE => '',
		self::FIELD_ERROR => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
	];
	protected array $relationshipInfo = [];
}
