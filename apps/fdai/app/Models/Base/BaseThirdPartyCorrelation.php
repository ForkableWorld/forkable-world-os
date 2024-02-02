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
use App\Models\OAClient;
use App\Models\ThirdPartyCorrelation;
use App\Models\Variable;
use App\Models\VariableCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseThirdPartyCorrelation
 * @property int $cause_id
 * @property int $effect_id
 * @property float $qm_score
 * @property float $forward_pearson_correlation_coefficient
 * @property float $value_predicting_high_outcome
 * @property float $value_predicting_low_outcome
 * @property int $predicts_high_effect_change
 * @property int $predicts_low_effect_change
 * @property float $average_effect
 * @property float $average_effect_following_high_cause
 * @property float $average_effect_following_low_cause
 * @property float $average_daily_low_cause
 * @property float $average_daily_high_cause
 * @property float $average_forward_pearson_correlation_over_onset_delays
 * @property float $average_reverse_pearson_correlation_over_onset_delays
 * @property int $cause_changes
 * @property float $cause_filling_value
 * @property int $cause_number_of_processed_daily_measurements
 * @property int $cause_number_of_raw_measurements
 * @property int $cause_unit_id
 * @property float $confidence_interval
 * @property float $critical_t_value
 * @property Carbon $created_at
 * @property string $data_source_name
 * @property Carbon $deleted_at
 * @property int $duration_of_action
 * @property int $effect_changes
 * @property float $effect_filling_value
 * @property int $effect_number_of_processed_daily_measurements
 * @property int $effect_number_of_raw_measurements
 * @property string $error
 * @property float $forward_spearman_correlation_coefficient
 * @property int $id
 * @property int $number_of_days
 * @property int $number_of_pairs
 * @property int $onset_delay
 * @property int $onset_delay_with_strongest_pearson_correlation
 * @property float $optimal_pearson_product
 * @property float $p_value
 * @property float $pearson_correlation_with_no_onset_delay
 * @property float $predictive_pearson_correlation_coefficient
 * @property float $reverse_pearson_correlation_coefficient
 * @property float $statistical_significance
 * @property float $strongest_pearson_correlation_coefficient
 * @property float $t_value
 * @property Carbon $updated_at
 * @property int $user_id
 * @property float $grouped_cause_value_closest_to_value_predicting_low_outcome
 * @property float $grouped_cause_value_closest_to_value_predicting_high_outcome
 * @property string $client_id
 * @property Carbon $published_at
 * @property int $wp_post_id
 * @property string $status
 * @property int $cause_variable_category_id
 * @property int $effect_variable_category_id
 * @property bool $interesting_variable_category_pair
 * @property int $cause_variable_id
 * @property int $effect_variable_id
 * @property VariableCategory $cause_variable_category
 * @property Variable $cause
 * @property OAClient $oa_client
 * @property VariableCategory $effect_variable_category
 * @property Variable $effect
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseThirdPartyCorrelation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereAverageDailyHighCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereAverageDailyLowCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereAverageEffect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereAverageEffectFollowingHighCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereAverageEffectFollowingLowCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereAverageForwardPearsonCorrelationOverOnsetDelays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereAverageReversePearsonCorrelationOverOnsetDelays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereCauseChanges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereCauseFillingValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation whereCauseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereCauseNumberOfProcessedDailyMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereCauseNumberOfRawMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereCauseUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereCauseVariableCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereCauseVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereConfidenceInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereCriticalTValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereDataSourceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereDurationOfAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereEffectChanges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereEffectFillingValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereEffectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereEffectNumberOfProcessedDailyMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereEffectNumberOfRawMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereEffectVariableCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereEffectVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereExperimentEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereExperimentStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereForwardPearsonCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereForwardSpearmanCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereGroupedCauseValueClosestToValuePredictingHighOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereGroupedCauseValueClosestToValuePredictingLowOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereInterestingVariableCategoryPair($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereNumberOfDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereNumberOfPairs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereOnsetDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereOnsetDelayWithStrongestPearsonCorrelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereOptimalPearsonProduct($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation wherePValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     wherePearsonCorrelationWithNoOnsetDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     wherePredictivePearsonCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     wherePredictsHighEffectChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     wherePredictsLowEffectChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation whereQmScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereReversePearsonCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereStatisticalSignificance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereStrongestPearsonCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation whereTValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereValuePredictingHighOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereValuePredictingLowOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseThirdPartyCorrelation
 *     whereWpPostId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseThirdPartyCorrelation withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseThirdPartyCorrelation withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseThirdPartyCorrelation extends BaseModel {
	use SoftDeletes;
	public const FIELD_AVERAGE_DAILY_HIGH_CAUSE = 'average_daily_high_cause';
	public const FIELD_AVERAGE_DAILY_LOW_CAUSE = 'average_daily_low_cause';
	public const FIELD_AVERAGE_EFFECT = 'average_effect';
	public const FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE = 'average_effect_following_high_cause';
	public const FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE = 'average_effect_following_low_cause';
	public const FIELD_AVERAGE_FORWARD_PEARSON_CORRELATION_OVER_ONSET_DELAYS = 'average_forward_pearson_correlation_over_onset_delays';
	public const FIELD_AVERAGE_REVERSE_PEARSON_CORRELATION_OVER_ONSET_DELAYS = 'average_reverse_pearson_correlation_over_onset_delays';
	public const FIELD_CAUSE_CHANGES = 'cause_changes';
	public const FIELD_CAUSE_FILLING_VALUE = 'cause_filling_value';
	public const FIELD_CAUSE_ID = 'cause_id';
	public const FIELD_CAUSE_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS = 'cause_number_of_processed_daily_measurements';
	public const FIELD_CAUSE_NUMBER_OF_RAW_MEASUREMENTS = 'cause_number_of_raw_measurements';
	public const FIELD_CAUSE_UNIT_ID = 'cause_unit_id';
	public const FIELD_CAUSE_VARIABLE_CATEGORY_ID = 'cause_variable_category_id';
	public const FIELD_CAUSE_VARIABLE_ID = 'cause_variable_id';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CONFIDENCE_INTERVAL = 'confidence_interval';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_CRITICAL_T_VALUE = 'critical_t_value';
	public const FIELD_DATA_SOURCE_NAME = 'data_source_name';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_DURATION_OF_ACTION = 'duration_of_action';
	public const FIELD_EFFECT_CHANGES = 'effect_changes';
	public const FIELD_EFFECT_FILLING_VALUE = 'effect_filling_value';
	public const FIELD_EFFECT_ID = 'effect_id';
	public const FIELD_EFFECT_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS = 'effect_number_of_processed_daily_measurements';
	public const FIELD_EFFECT_NUMBER_OF_RAW_MEASUREMENTS = 'effect_number_of_raw_measurements';
	public const FIELD_EFFECT_VARIABLE_CATEGORY_ID = 'effect_variable_category_id';
	public const FIELD_EFFECT_VARIABLE_ID = 'effect_variable_id';
	public const FIELD_ERROR = 'error';
	public const FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT = 'forward_pearson_correlation_coefficient';
	public const FIELD_FORWARD_SPEARMAN_CORRELATION_COEFFICIENT = 'forward_spearman_correlation_coefficient';
	public const FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME = 'grouped_cause_value_closest_to_value_predicting_high_outcome';
	public const FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME = 'grouped_cause_value_closest_to_value_predicting_low_outcome';
	public const FIELD_ID = 'id';
	public const FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR = 'interesting_variable_category_pair';
	public const FIELD_NUMBER_OF_DAYS = 'number_of_days';
	public const FIELD_NUMBER_OF_PAIRS = 'number_of_pairs';
	public const FIELD_ONSET_DELAY = 'onset_delay';
	public const FIELD_ONSET_DELAY_WITH_STRONGEST_PEARSON_CORRELATION = 'onset_delay_with_strongest_pearson_correlation';
	public const FIELD_OPTIMAL_PEARSON_PRODUCT = 'optimal_pearson_product';
	public const FIELD_P_VALUE = 'p_value';
	public const FIELD_PEARSON_CORRELATION_WITH_NO_ONSET_DELAY = 'pearson_correlation_with_no_onset_delay';
	public const FIELD_PREDICTIVE_PEARSON_CORRELATION_COEFFICIENT = 'predictive_pearson_correlation_coefficient';
	public const FIELD_PREDICTS_HIGH_EFFECT_CHANGE = 'predicts_high_effect_change';
	public const FIELD_PREDICTS_LOW_EFFECT_CHANGE = 'predicts_low_effect_change';
	public const FIELD_PUBLISHED_AT = 'published_at';
	public const FIELD_QM_SCORE = 'qm_score';
	public const FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT = 'reverse_pearson_correlation_coefficient';
	public const FIELD_STATISTICAL_SIGNIFICANCE = 'statistical_significance';
	public const FIELD_STATUS = 'status';
	public const FIELD_STRONGEST_PEARSON_CORRELATION_COEFFICIENT = 'strongest_pearson_correlation_coefficient';
	public const FIELD_T_VALUE = 't_value';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_VALUE_PREDICTING_HIGH_OUTCOME = 'value_predicting_high_outcome';
	public const FIELD_VALUE_PREDICTING_LOW_OUTCOME = 'value_predicting_low_outcome';
	public const FIELD_WP_POST_ID = 'wp_post_id';
	public const TABLE = 'third_party_correlations';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'Stores Calculated Correlation Coefficients';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_PUBLISHED_AT => 'datetime',
		self::FIELD_AVERAGE_DAILY_HIGH_CAUSE => 'float',
		self::FIELD_AVERAGE_DAILY_LOW_CAUSE => 'float',
		self::FIELD_AVERAGE_EFFECT => 'float',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE => 'float',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE => 'float',
		self::FIELD_AVERAGE_FORWARD_PEARSON_CORRELATION_OVER_ONSET_DELAYS => 'float',
		self::FIELD_AVERAGE_REVERSE_PEARSON_CORRELATION_OVER_ONSET_DELAYS => 'float',
		self::FIELD_CAUSE_CHANGES => 'int',
		self::FIELD_CAUSE_FILLING_VALUE => 'float',
		self::FIELD_CAUSE_ID => 'int',
		self::FIELD_CAUSE_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => 'int',
		self::FIELD_CAUSE_NUMBER_OF_RAW_MEASUREMENTS => 'int',
		self::FIELD_CAUSE_UNIT_ID => 'int',
		self::FIELD_CAUSE_VARIABLE_CATEGORY_ID => 'int',
		self::FIELD_CAUSE_VARIABLE_ID => 'int',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_CONFIDENCE_INTERVAL => 'float',
		self::FIELD_CRITICAL_T_VALUE => 'float',
		self::FIELD_DATA_SOURCE_NAME => 'string',
		self::FIELD_DURATION_OF_ACTION => 'int',
		self::FIELD_EFFECT_CHANGES => 'int',
		self::FIELD_EFFECT_FILLING_VALUE => 'float',
		self::FIELD_EFFECT_ID => 'int',
		self::FIELD_EFFECT_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => 'int',
		self::FIELD_EFFECT_NUMBER_OF_RAW_MEASUREMENTS => 'int',
		self::FIELD_EFFECT_VARIABLE_CATEGORY_ID => 'int',
		self::FIELD_EFFECT_VARIABLE_ID => 'int',
		self::FIELD_ERROR => 'string',
		self::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT => 'float',
		self::FIELD_FORWARD_SPEARMAN_CORRELATION_COEFFICIENT => 'float',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME => 'float',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME => 'float',
		self::FIELD_ID => 'int',
		self::FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR => 'bool',
		self::FIELD_NUMBER_OF_DAYS => 'int',
		self::FIELD_NUMBER_OF_PAIRS => 'int',
		self::FIELD_ONSET_DELAY => 'int',
		self::FIELD_ONSET_DELAY_WITH_STRONGEST_PEARSON_CORRELATION => 'int',
		self::FIELD_OPTIMAL_PEARSON_PRODUCT => 'float',
		self::FIELD_PEARSON_CORRELATION_WITH_NO_ONSET_DELAY => 'float',
		self::FIELD_PREDICTIVE_PEARSON_CORRELATION_COEFFICIENT => 'float',
		self::FIELD_PREDICTS_HIGH_EFFECT_CHANGE => 'int',
		self::FIELD_PREDICTS_LOW_EFFECT_CHANGE => 'int',
		self::FIELD_P_VALUE => 'float',
		self::FIELD_QM_SCORE => 'float',
		self::FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT => 'float',
		self::FIELD_STATISTICAL_SIGNIFICANCE => 'float',
		self::FIELD_STATUS => 'string',
		self::FIELD_STRONGEST_PEARSON_CORRELATION_COEFFICIENT => 'float',
		self::FIELD_T_VALUE => 'float',
		self::FIELD_USER_ID => 'int',
		self::FIELD_VALUE_PREDICTING_HIGH_OUTCOME => 'float',
		self::FIELD_VALUE_PREDICTING_LOW_OUTCOME => 'float',
		self::FIELD_WP_POST_ID => 'int',	];
	protected array $rules = [
		self::FIELD_AVERAGE_DAILY_HIGH_CAUSE => 'nullable|numeric',
		self::FIELD_AVERAGE_DAILY_LOW_CAUSE => 'nullable|numeric',
		self::FIELD_AVERAGE_EFFECT => 'nullable|numeric',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE => 'nullable|numeric',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE => 'nullable|numeric',
		self::FIELD_AVERAGE_FORWARD_PEARSON_CORRELATION_OVER_ONSET_DELAYS => 'nullable|numeric',
		self::FIELD_AVERAGE_REVERSE_PEARSON_CORRELATION_OVER_ONSET_DELAYS => 'nullable|numeric',
		self::FIELD_CAUSE_CHANGES => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_CAUSE_FILLING_VALUE => 'nullable|numeric',
		self::FIELD_CAUSE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_CAUSE_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_CAUSE_NUMBER_OF_RAW_MEASUREMENTS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_CAUSE_UNIT_ID => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_CAUSE_VARIABLE_CATEGORY_ID => 'nullable|boolean',
		self::FIELD_CAUSE_VARIABLE_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_CONFIDENCE_INTERVAL => 'nullable|numeric',
		self::FIELD_CRITICAL_T_VALUE => 'nullable|numeric',
		self::FIELD_DATA_SOURCE_NAME => 'nullable|max:255',
		self::FIELD_DURATION_OF_ACTION => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_EFFECT_CHANGES => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_EFFECT_FILLING_VALUE => 'nullable|numeric',
		self::FIELD_EFFECT_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_EFFECT_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_EFFECT_NUMBER_OF_RAW_MEASUREMENTS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_EFFECT_VARIABLE_CATEGORY_ID => 'nullable|boolean',
		self::FIELD_EFFECT_VARIABLE_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_ERROR => 'nullable|max:65535',
		self::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_FORWARD_SPEARMAN_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME => 'nullable|numeric',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME => 'nullable|numeric',
		self::FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR => 'nullable|boolean',
		self::FIELD_NUMBER_OF_DAYS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_PAIRS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_ONSET_DELAY => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_ONSET_DELAY_WITH_STRONGEST_PEARSON_CORRELATION => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_OPTIMAL_PEARSON_PRODUCT => 'nullable|numeric',
		self::FIELD_PEARSON_CORRELATION_WITH_NO_ONSET_DELAY => 'nullable|numeric',
		self::FIELD_PREDICTIVE_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_PREDICTS_HIGH_EFFECT_CHANGE => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_PREDICTS_LOW_EFFECT_CHANGE => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_PUBLISHED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_P_VALUE => 'nullable|numeric',
		self::FIELD_QM_SCORE => 'nullable|numeric',
		self::FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_STATISTICAL_SIGNIFICANCE => 'nullable|numeric',
		self::FIELD_STATUS => 'nullable|max:25',
		self::FIELD_STRONGEST_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_T_VALUE => 'nullable|numeric',
		self::FIELD_USER_ID => 'required|numeric|min:0',
		self::FIELD_VALUE_PREDICTING_HIGH_OUTCOME => 'nullable|numeric',
		self::FIELD_VALUE_PREDICTING_LOW_OUTCOME => 'nullable|numeric',
		self::FIELD_WP_POST_ID => 'nullable|integer|min:-2147483648|max:2147483647',
	];
	protected $hints = [
		self::FIELD_CAUSE_ID => 'variable ID of the cause variable for which the user desires user_variable_relationships',
		self::FIELD_EFFECT_ID => 'variable ID of the effect variable for which the user desires user_variable_relationships',
		self::FIELD_QM_SCORE => 'QM Score',
		self::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT => 'Pearson correlation coefficient between cause and effect measurements',
		self::FIELD_VALUE_PREDICTING_HIGH_OUTCOME => 'cause value that predicts an above average effect value (in default unit for cause variable)',
		self::FIELD_VALUE_PREDICTING_LOW_OUTCOME => 'cause value that predicts a below average effect value (in default unit for cause variable)',
		self::FIELD_PREDICTS_HIGH_EFFECT_CHANGE => 'The percent change in the outcome typically seen when the predictor value is closer to the predictsHighEffect value. ',
		self::FIELD_PREDICTS_LOW_EFFECT_CHANGE => 'The percent change in the outcome from average typically seen when the predictor value is closer to the predictsHighEffect value.',
		self::FIELD_AVERAGE_EFFECT => '',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE => '',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE => '',
		self::FIELD_AVERAGE_DAILY_LOW_CAUSE => '',
		self::FIELD_AVERAGE_DAILY_HIGH_CAUSE => '',
		self::FIELD_AVERAGE_FORWARD_PEARSON_CORRELATION_OVER_ONSET_DELAYS => '',
		self::FIELD_AVERAGE_REVERSE_PEARSON_CORRELATION_OVER_ONSET_DELAYS => '',
		self::FIELD_CAUSE_CHANGES => 'Cause changes',
		self::FIELD_CAUSE_FILLING_VALUE => '',
		self::FIELD_CAUSE_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => '',
		self::FIELD_CAUSE_NUMBER_OF_RAW_MEASUREMENTS => '',
		self::FIELD_CAUSE_UNIT_ID => 'Unit ID of Cause',
		self::FIELD_CONFIDENCE_INTERVAL => 'A margin of error around the effect size.  Wider confidence intervals reflect greater uncertainty about the Ã¢â‚¬Å“trueÃ¢â‚¬Â value of the correlation.',
		self::FIELD_CRITICAL_T_VALUE => 'Value of t from lookup table which t must exceed for significance.',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DATA_SOURCE_NAME => '',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_DURATION_OF_ACTION => 'Time over which the cause is expected to produce a perceivable effect following the onset delay',
		self::FIELD_EFFECT_CHANGES => 'Effect changes',
		self::FIELD_EFFECT_FILLING_VALUE => '',
		self::FIELD_EFFECT_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => '',
		self::FIELD_EFFECT_NUMBER_OF_RAW_MEASUREMENTS => '',
		self::FIELD_ERROR => '',
		self::FIELD_FORWARD_SPEARMAN_CORRELATION_COEFFICIENT => '',
		self::FIELD_ID => '',
		self::FIELD_NUMBER_OF_DAYS => '',
		self::FIELD_NUMBER_OF_PAIRS => 'Number of points that went into the correlation calculation',
		self::FIELD_ONSET_DELAY => 'User estimated or default time after cause measurement before a perceivable effect is observed',
		self::FIELD_ONSET_DELAY_WITH_STRONGEST_PEARSON_CORRELATION => '',
		self::FIELD_OPTIMAL_PEARSON_PRODUCT => 'Optimal Pearson Product',
		self::FIELD_P_VALUE => 'The measure of statistical significance. A value less than 0.05 means that a correlation is statistically significant or consistent enough that it is unlikely to be a coincidence.',
		self::FIELD_PEARSON_CORRELATION_WITH_NO_ONSET_DELAY => '',
		self::FIELD_PREDICTIVE_PEARSON_CORRELATION_COEFFICIENT => 'Predictive Pearson Correlation Coefficient',
		self::FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT => 'User Variable Relationship when cause and effect are reversed. For any causal relationship, the forward correlation should exceed the reverse correlation',
		self::FIELD_STATISTICAL_SIGNIFICANCE => 'A function of the effect size and sample size',
		self::FIELD_STRONGEST_PEARSON_CORRELATION_COEFFICIENT => '',
		self::FIELD_T_VALUE => 'Function of correlation and number of samples.',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_USER_ID => '',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME => '',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME => '',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_PUBLISHED_AT => 'datetime',
		self::FIELD_WP_POST_ID => '',
		self::FIELD_STATUS => '',
		self::FIELD_CAUSE_VARIABLE_CATEGORY_ID => '',
		self::FIELD_EFFECT_VARIABLE_CATEGORY_ID => '',
		self::FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR => '',
		self::FIELD_CAUSE_VARIABLE_ID => '',
		self::FIELD_EFFECT_VARIABLE_ID => '',
	];
	protected array $relationshipInfo = [
		'cause_variable_category' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => VariableCategory::class,
			'foreignKeyColumnName' => 'cause_variable_category_id',
			'foreignKey' => ThirdPartyCorrelation::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => VariableCategory::FIELD_ID,
			'ownerKeyColumnName' => 'cause_variable_category_id',
			'ownerKey' => ThirdPartyCorrelation::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
			'methodName' => 'cause_variable_category',
		],
		'cause' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'cause_id',
			'foreignKey' => ThirdPartyCorrelation::FIELD_CAUSE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'cause_id',
			'ownerKey' => ThirdPartyCorrelation::FIELD_CAUSE_ID,
			'methodName' => 'cause',
		],
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => ThirdPartyCorrelation::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => ThirdPartyCorrelation::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'effect_variable_category' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => VariableCategory::class,
			'foreignKeyColumnName' => 'effect_variable_category_id',
			'foreignKey' => ThirdPartyCorrelation::FIELD_EFFECT_VARIABLE_CATEGORY_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => VariableCategory::FIELD_ID,
			'ownerKeyColumnName' => 'effect_variable_category_id',
			'ownerKey' => ThirdPartyCorrelation::FIELD_EFFECT_VARIABLE_CATEGORY_ID,
			'methodName' => 'effect_variable_category',
		],
		'effect' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'effect_id',
			'foreignKey' => ThirdPartyCorrelation::FIELD_EFFECT_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'effect_id',
			'ownerKey' => ThirdPartyCorrelation::FIELD_EFFECT_ID,
			'methodName' => 'effect',
		],
	];
	public function cause_variable_category(): BelongsTo{
		return $this->belongsTo(VariableCategory::class, ThirdPartyCorrelation::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
			VariableCategory::FIELD_ID, ThirdPartyCorrelation::FIELD_CAUSE_VARIABLE_CATEGORY_ID);
	}
	public function cause(): BelongsTo{
		return $this->belongsTo(Variable::class, ThirdPartyCorrelation::FIELD_CAUSE_ID, Variable::FIELD_ID,
			ThirdPartyCorrelation::FIELD_CAUSE_ID);
	}
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, ThirdPartyCorrelation::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			ThirdPartyCorrelation::FIELD_CLIENT_ID);
	}
	public function effect_variable_category(): BelongsTo{
		return $this->belongsTo(VariableCategory::class, ThirdPartyCorrelation::FIELD_EFFECT_VARIABLE_CATEGORY_ID,
			VariableCategory::FIELD_ID, ThirdPartyCorrelation::FIELD_EFFECT_VARIABLE_CATEGORY_ID);
	}
	public function effect(): BelongsTo{
		return $this->belongsTo(Variable::class, ThirdPartyCorrelation::FIELD_EFFECT_ID, Variable::FIELD_ID,
			ThirdPartyCorrelation::FIELD_EFFECT_ID);
	}
}
