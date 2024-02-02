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
use App\Models\GlobalVariableRelationship;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Models\CorrelationCausalityVote;
use App\Models\CorrelationUsefulnessVote;
use App\Models\OAClient;
use App\Models\Unit;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Models\Vote;
use App\Models\WpPost;
use Awobaz\Compoships\Compoships;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseGlobalVariableRelationship
 * @property int $id
 * @property float $forward_pearson_correlation_coefficient
 * @property int $onset_delay
 * @property int $duration_of_action
 * @property int $number_of_pairs
 * @property float $value_predicting_high_outcome
 * @property float $value_predicting_low_outcome
 * @property float $optimal_pearson_product
 * @property float $average_vote
 * @property int $number_of_users
 * @property int $number_of_correlations
 * @property float $statistical_significance
 * @property int $cause_unit_id
 * @property int $cause_changes
 * @property int $effect_changes
 * @property float $aggregate_qm_score
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $status
 * @property float $reverse_pearson_correlation_coefficient
 * @property float $predictive_pearson_correlation_coefficient
 * @property string $data_source_name
 * @property int $predicts_high_effect_change
 * @property int $predicts_low_effect_change
 * @property float $p_value
 * @property float $t_value
 * @property float $critical_t_value
 * @property float $confidence_interval
 * @property Carbon $deleted_at
 * @property float $average_effect
 * @property float $average_effect_following_high_cause
 * @property float $average_effect_following_low_cause
 * @property float $average_daily_low_cause
 * @property float $average_daily_high_cause
 * @property float $population_trait_pearson_correlation_coefficient
 * @property float $grouped_cause_value_closest_to_value_predicting_low_outcome
 * @property float $grouped_cause_value_closest_to_value_predicting_high_outcome
 * @property string $client_id
 * @property Carbon $published_at
 * @property int $wp_post_id
 * @property int $cause_variable_category_id
 * @property int $effect_variable_category_id
 * @property bool $interesting_variable_category_pair
 * @property Carbon $newest_data_at
 * @property Carbon $analysis_requested_at
 * @property string $reason_for_analysis
 * @property Carbon $analysis_started_at
 * @property Carbon $analysis_ended_at
 * @property string $user_error_message
 * @property string $internal_error_message
 * @property int $cause_variable_id
 * @property int $effect_variable_id
 * @property float $cause_baseline_average_per_day
 * @property float $cause_baseline_average_per_duration_of_action
 * @property float $cause_treatment_average_per_day
 * @property float $cause_treatment_average_per_duration_of_action
 * @property float $effect_baseline_average
 * @property float $effect_baseline_relative_standard_deviation
 * @property float $effect_baseline_standard_deviation
 * @property float $effect_follow_up_average
 * @property float $effect_follow_up_percent_change_from_baseline
 * @property float $z_score
 * @property array $charts
 * @property int $number_of_variables_where_best_global_variable_relationship
 * @property string $deletion_reason
 * @property int $record_size_in_kb
 * @property bool $is_public
 * @property bool $boring
 * @property bool $outcome_is_a_goal
 * @property bool $predictor_is_controllable
 * @property bool $plausibly_causal
 * @property bool $obvious
 * @property int $number_of_up_votes
 * @property int $number_of_down_votes
 * @property string $strength_level
 * @property string $confidence_level
 * @property string $relationship
 * @property Unit $cause_unit
 * @property VariableCategory $cause_variable_category
 * @property Variable $cause_variable
 * @property OAClient $oa_client
 * @property VariableCategory $effect_variable_category
 * @property Variable $effect_variable
 * @property WpPost $wp_post
 * @property Collection|CorrelationCausalityVote[] $correlation_causality_votes
 * @property Collection|CorrelationUsefulnessVote[] $correlation_usefulness_votes
 * @property Collection|UserVariableRelationship[] $user_variable_relationships
 * @property Collection|Variable[] $variables_where_best_global_variable_relationship
 * @property Collection|Vote[] $votes
 * @package App\Models\Base
 * @property-read int|null $correlation_causality_votes_count
 * @property-read int|null $correlation_usefulness_votes_count
 * @property-read int|null $correlations_count
 * @property mixed $raw
 * @property-read int|null $variables_where_best_global_variable_relationship_count
 * @property-read int|null $votes_count
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship newQuery()
 * @method static \Illuminate\Database\Query\Builder|BaseGlobalVariableRelationship onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereAggregateQmScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereAnalysisEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereAnalysisRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereAnalysisStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereAverageDailyHighCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereAverageDailyLowCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereAverageEffect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship
 *     whereAverageEffectFollowingHighCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship
 *     whereAverageEffectFollowingLowCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereAverageVote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship
 *     whereCauseBaselineAveragePerDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship
 *     whereCauseBaselineAveragePerDurationOfAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereCauseChanges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship
 *     whereCauseTreatmentAveragePerDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship
 *     whereCauseTreatmentAveragePerDurationOfAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereCauseUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereCauseVariableCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereCauseVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereCharts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereConfidenceInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereCriticalTValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereDataSourceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereDeletionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereDurationOfAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereEffectBaselineAverage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship
 *     whereEffectBaselineRelativeStandardDeviation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship
 *     whereEffectBaselineStandardDeviation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereEffectChanges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereEffectFollowUpAverage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship
 *     whereEffectFollowUpPercentChangeFromBaseline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereEffectVariableCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereEffectVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship
 *     whereForwardPearsonCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship
 *     whereGroupedCauseValueClosestToValuePredictingHighOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship
 *     whereGroupedCauseValueClosestToValuePredictingLowOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship
 *     whereInterestingVariableCategoryPair($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereInternalErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereNewestDataAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereNumberOfCorrelations($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereNumberOfPairs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereNumberOfUsers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship
 *     whereNumberOfVariablesWhereBestGlobalVariableRelationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereOnsetDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereOptimalPearsonProduct($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship wherePValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship
 *     wherePopulationTraitPearsonCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship
 *     wherePredictivePearsonCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship wherePredictsHighEffectChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship wherePredictsLowEffectChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereReasonForAnalysis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereRecordSizeInKb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship
 *     whereReversePearsonCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereStatisticalSignificance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereTValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereUserErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship
 *     whereValuePredictingHighOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereValuePredictingLowOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereWpPostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGlobalVariableRelationship whereZScore($value)
 * @method static \Illuminate\Database\Query\Builder|BaseGlobalVariableRelationship withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BaseGlobalVariableRelationship withoutTrashed()
 * @mixin \Eloquent
 */
abstract class BaseGlobalVariableRelationship extends BaseModel {
	use SoftDeletes;
	use Compoships;
	public const FIELD_AGGREGATE_QM_SCORE = 'aggregate_qm_score';
	public const FIELD_ANALYSIS_ENDED_AT = 'analysis_ended_at';
	public const FIELD_ANALYSIS_REQUESTED_AT = 'analysis_requested_at';
	public const FIELD_ANALYSIS_STARTED_AT = 'analysis_started_at';
	public const FIELD_AVERAGE_DAILY_HIGH_CAUSE = 'average_daily_high_cause';
	public const FIELD_AVERAGE_DAILY_LOW_CAUSE = 'average_daily_low_cause';
	public const FIELD_AVERAGE_EFFECT = 'average_effect';
	public const FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE = 'average_effect_following_high_cause';
	public const FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE = 'average_effect_following_low_cause';
	public const FIELD_AVERAGE_VOTE = 'average_vote';
	public const FIELD_BORING = 'boring';
	public const FIELD_CAUSE_BASELINE_AVERAGE_PER_DAY = 'cause_baseline_average_per_day';
	public const FIELD_CAUSE_BASELINE_AVERAGE_PER_DURATION_OF_ACTION = 'cause_baseline_average_per_duration_of_action';
	public const FIELD_CAUSE_CHANGES = 'cause_changes';
	public const FIELD_CAUSE_TREATMENT_AVERAGE_PER_DAY = 'cause_treatment_average_per_day';
	public const FIELD_CAUSE_TREATMENT_AVERAGE_PER_DURATION_OF_ACTION = 'cause_treatment_average_per_duration_of_action';
	public const FIELD_CAUSE_UNIT_ID = 'cause_unit_id';
	public const FIELD_CAUSE_VARIABLE_CATEGORY_ID = 'cause_variable_category_id';
	public const FIELD_CAUSE_VARIABLE_ID = 'cause_variable_id';
	public const FIELD_CHARTS = 'charts';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CONFIDENCE_INTERVAL = 'confidence_interval';
	public const FIELD_CONFIDENCE_LEVEL = 'confidence_level';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_CRITICAL_T_VALUE = 'critical_t_value';
	public const FIELD_DATA_SOURCE_NAME = 'data_source_name';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_DELETION_REASON = 'deletion_reason';
	public const FIELD_DURATION_OF_ACTION = 'duration_of_action';
	public const FIELD_EFFECT_BASELINE_AVERAGE = 'effect_baseline_average';
	public const FIELD_EFFECT_BASELINE_RELATIVE_STANDARD_DEVIATION = 'effect_baseline_relative_standard_deviation';
	public const FIELD_EFFECT_BASELINE_STANDARD_DEVIATION = 'effect_baseline_standard_deviation';
	public const FIELD_EFFECT_CHANGES = 'effect_changes';
	public const FIELD_EFFECT_FOLLOW_UP_AVERAGE = 'effect_follow_up_average';
	public const FIELD_EFFECT_FOLLOW_UP_PERCENT_CHANGE_FROM_BASELINE = 'effect_follow_up_percent_change_from_baseline';
	public const FIELD_EFFECT_VARIABLE_CATEGORY_ID = 'effect_variable_category_id';
	public const FIELD_EFFECT_VARIABLE_ID = 'effect_variable_id';
	public const FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT = 'forward_pearson_correlation_coefficient';
	public const FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME = 'grouped_cause_value_closest_to_value_predicting_high_outcome';
	public const FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME = 'grouped_cause_value_closest_to_value_predicting_low_outcome';
	public const FIELD_ID = 'id';
	public const FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR = 'interesting_variable_category_pair';
	public const FIELD_INTERNAL_ERROR_MESSAGE = 'internal_error_message';
	public const FIELD_IS_PUBLIC = 'is_public';
	public const FIELD_NEWEST_DATA_AT = 'newest_data_at';
	public const FIELD_NUMBER_OF_CORRELATIONS = 'number_of_correlations';
	public const FIELD_NUMBER_OF_DOWN_VOTES = 'number_of_down_votes';
	public const FIELD_NUMBER_OF_PAIRS = 'number_of_pairs';
	public const FIELD_NUMBER_OF_UP_VOTES = 'number_of_up_votes';
	public const FIELD_NUMBER_OF_USERS = 'number_of_users';
	public const FIELD_NUMBER_OF_VARIABLES_WHERE_BEST_AGGREGATE_CORRELATION = 'number_of_variables_where_best_global_variable_relationship';
	public const FIELD_OBVIOUS = 'obvious';
	public const FIELD_ONSET_DELAY = 'onset_delay';
	public const FIELD_OPTIMAL_PEARSON_PRODUCT = 'optimal_pearson_product';
	public const FIELD_OUTCOME_IS_A_GOAL = 'outcome_is_a_goal';
	public const FIELD_P_VALUE = 'p_value';
	public const FIELD_PLAUSIBLY_CAUSAL = 'plausibly_causal';
	public const FIELD_POPULATION_TRAIT_PEARSON_CORRELATION_COEFFICIENT = 'population_trait_pearson_correlation_coefficient';
	public const FIELD_PREDICTIVE_PEARSON_CORRELATION_COEFFICIENT = 'predictive_pearson_correlation_coefficient';
	public const FIELD_PREDICTOR_IS_CONTROLLABLE = 'predictor_is_controllable';
	public const FIELD_PREDICTS_HIGH_EFFECT_CHANGE = 'predicts_high_effect_change';
	public const FIELD_PREDICTS_LOW_EFFECT_CHANGE = 'predicts_low_effect_change';
	public const FIELD_PUBLISHED_AT = 'published_at';
	public const FIELD_REASON_FOR_ANALYSIS = 'reason_for_analysis';
	public const FIELD_RECORD_SIZE_IN_KB = 'record_size_in_kb';
	public const FIELD_RELATIONSHIP = 'relationship';
	public const FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT = 'reverse_pearson_correlation_coefficient';
	public const FIELD_SLUG = 'slug';
	public const FIELD_STATISTICAL_SIGNIFICANCE = 'statistical_significance';
	public const FIELD_STATUS = 'status';
	public const FIELD_STRENGTH_LEVEL = 'strength_level';
	public const FIELD_T_VALUE = 't_value';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ERROR_MESSAGE = 'user_error_message';
	public const FIELD_VALUE_PREDICTING_HIGH_OUTCOME = 'value_predicting_high_outcome';
	public const FIELD_VALUE_PREDICTING_LOW_OUTCOME = 'value_predicting_low_outcome';
	public const FIELD_WP_POST_ID = 'wp_post_id';
	public const FIELD_Z_SCORE = 'z_score';
	public const TABLE = 'global_variable_relationships';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'Stores Calculated Global Variable Relationship Coefficients';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_PUBLISHED_AT => 'datetime',
        self::FIELD_NEWEST_DATA_AT => 'datetime',
        self::FIELD_ANALYSIS_REQUESTED_AT => 'datetime',
        self::FIELD_ANALYSIS_STARTED_AT => 'datetime',
        self::FIELD_ANALYSIS_ENDED_AT => 'datetime',
		self::FIELD_AGGREGATE_QM_SCORE => 'float',
		self::FIELD_AVERAGE_DAILY_HIGH_CAUSE => 'float',
		self::FIELD_AVERAGE_DAILY_LOW_CAUSE => 'float',
		self::FIELD_AVERAGE_EFFECT => 'float',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE => 'float',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE => 'float',
		self::FIELD_AVERAGE_VOTE => 'float',
		self::FIELD_BORING => 'bool',
		self::FIELD_CAUSE_BASELINE_AVERAGE_PER_DAY => 'float',
		self::FIELD_CAUSE_BASELINE_AVERAGE_PER_DURATION_OF_ACTION => 'float',
		self::FIELD_CAUSE_CHANGES => 'int',
		self::FIELD_CAUSE_TREATMENT_AVERAGE_PER_DAY => 'float',
		self::FIELD_CAUSE_TREATMENT_AVERAGE_PER_DURATION_OF_ACTION => 'float',
		self::FIELD_CAUSE_UNIT_ID => 'int',
		self::FIELD_CAUSE_VARIABLE_CATEGORY_ID => 'int',
		self::FIELD_CAUSE_VARIABLE_ID => 'int',
		self::FIELD_CHARTS => 'json',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_CONFIDENCE_INTERVAL => 'float',
		self::FIELD_CONFIDENCE_LEVEL => 'string',
		self::FIELD_CRITICAL_T_VALUE => 'float',
		self::FIELD_DATA_SOURCE_NAME => 'string',
		self::FIELD_DELETION_REASON => 'string',
		self::FIELD_DURATION_OF_ACTION => 'int',
		self::FIELD_EFFECT_BASELINE_AVERAGE => 'float',
		self::FIELD_EFFECT_BASELINE_RELATIVE_STANDARD_DEVIATION => 'float',
		self::FIELD_EFFECT_BASELINE_STANDARD_DEVIATION => 'float',
		self::FIELD_EFFECT_CHANGES => 'int',
		self::FIELD_EFFECT_FOLLOW_UP_AVERAGE => 'float',
		self::FIELD_EFFECT_FOLLOW_UP_PERCENT_CHANGE_FROM_BASELINE => 'float',
		self::FIELD_EFFECT_VARIABLE_CATEGORY_ID => 'int',
		self::FIELD_EFFECT_VARIABLE_ID => 'int',
		self::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT => 'float',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME => 'float',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME => 'float',
		self::FIELD_ID => 'int',
		self::FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR => 'bool',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'string',
		self::FIELD_IS_PUBLIC => 'bool',
		self::FIELD_NUMBER_OF_CORRELATIONS => 'int',
		self::FIELD_NUMBER_OF_DOWN_VOTES => 'int',
		self::FIELD_NUMBER_OF_PAIRS => 'int',
		self::FIELD_NUMBER_OF_UP_VOTES => 'int',
		self::FIELD_NUMBER_OF_USERS => 'int',
		self::FIELD_NUMBER_OF_VARIABLES_WHERE_BEST_AGGREGATE_CORRELATION => 'int',
		self::FIELD_OBVIOUS => 'bool',
		self::FIELD_ONSET_DELAY => 'int',
		self::FIELD_OPTIMAL_PEARSON_PRODUCT => 'float',
		self::FIELD_OUTCOME_IS_A_GOAL => 'bool',
		self::FIELD_PLAUSIBLY_CAUSAL => 'bool',
		self::FIELD_POPULATION_TRAIT_PEARSON_CORRELATION_COEFFICIENT => 'float',
		self::FIELD_PREDICTIVE_PEARSON_CORRELATION_COEFFICIENT => 'float',
		self::FIELD_PREDICTOR_IS_CONTROLLABLE => 'bool',
		self::FIELD_PREDICTS_HIGH_EFFECT_CHANGE => 'int',
		self::FIELD_PREDICTS_LOW_EFFECT_CHANGE => 'int',
		self::FIELD_P_VALUE => 'float',
		self::FIELD_REASON_FOR_ANALYSIS => 'string',
		self::FIELD_RECORD_SIZE_IN_KB => 'int',
		self::FIELD_RELATIONSHIP => 'string',
		self::FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT => 'float',
		self::FIELD_STATISTICAL_SIGNIFICANCE => 'float',
		self::FIELD_STATUS => 'string',
		self::FIELD_STRENGTH_LEVEL => 'string',
		self::FIELD_T_VALUE => 'float',
		self::FIELD_USER_ERROR_MESSAGE => 'string',
		self::FIELD_VALUE_PREDICTING_HIGH_OUTCOME => 'float',
		self::FIELD_VALUE_PREDICTING_LOW_OUTCOME => 'float',
		self::FIELD_WP_POST_ID => 'int',
		self::FIELD_Z_SCORE => 'float',	];
	protected array $rules = [
		self::FIELD_AGGREGATE_QM_SCORE => 'required|numeric',
		self::FIELD_ANALYSIS_ENDED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_REQUESTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_STARTED_AT => 'required|date',
		self::FIELD_AVERAGE_DAILY_HIGH_CAUSE => 'required|numeric',
		self::FIELD_AVERAGE_DAILY_LOW_CAUSE => 'required|numeric',
		self::FIELD_AVERAGE_EFFECT => 'required|numeric',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE => 'required|numeric',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE => 'required|numeric',
		self::FIELD_AVERAGE_VOTE => 'nullable|numeric',
		self::FIELD_BORING => 'nullable|boolean',
		self::FIELD_CAUSE_BASELINE_AVERAGE_PER_DAY => 'required|numeric',
		self::FIELD_CAUSE_BASELINE_AVERAGE_PER_DURATION_OF_ACTION => 'required|numeric',
		self::FIELD_CAUSE_CHANGES => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_CAUSE_TREATMENT_AVERAGE_PER_DAY => 'required|numeric',
		self::FIELD_CAUSE_TREATMENT_AVERAGE_PER_DURATION_OF_ACTION => 'required|numeric',
		self::FIELD_CAUSE_UNIT_ID => 'nullable|integer|min:0|max:65535',
		self::FIELD_CAUSE_VARIABLE_CATEGORY_ID => 'required|boolean',
		self::FIELD_CAUSE_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_CHARTS => 'required|json',
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_CONFIDENCE_INTERVAL => 'required|numeric',
		self::FIELD_CONFIDENCE_LEVEL => 'required',
		self::FIELD_CRITICAL_T_VALUE => 'required|numeric',
		self::FIELD_DATA_SOURCE_NAME => 'nullable|max:255',
		self::FIELD_DELETION_REASON => 'nullable|max:280',
		self::FIELD_DURATION_OF_ACTION => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_EFFECT_BASELINE_AVERAGE => 'required|numeric',
		self::FIELD_EFFECT_BASELINE_RELATIVE_STANDARD_DEVIATION => 'required|numeric',
		self::FIELD_EFFECT_BASELINE_STANDARD_DEVIATION => 'required|numeric',
		self::FIELD_EFFECT_CHANGES => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_EFFECT_FOLLOW_UP_AVERAGE => 'required|numeric',
		self::FIELD_EFFECT_FOLLOW_UP_PERCENT_CHANGE_FROM_BASELINE => 'required|numeric',
		self::FIELD_EFFECT_VARIABLE_CATEGORY_ID => 'required|boolean',
		self::FIELD_EFFECT_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT => 'required|numeric',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME => 'required|numeric',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME => 'required|numeric',
		self::FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR => 'required|boolean',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'nullable|max:65535',
		self::FIELD_IS_PUBLIC => 'required|boolean',
		self::FIELD_NEWEST_DATA_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_NUMBER_OF_CORRELATIONS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_DOWN_VOTES => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_PAIRS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_UP_VOTES => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_USERS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_VARIABLES_WHERE_BEST_AGGREGATE_CORRELATION => 'required|integer|min:0|max:2147483647',
		self::FIELD_OBVIOUS => 'nullable|boolean',
		self::FIELD_ONSET_DELAY => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_OPTIMAL_PEARSON_PRODUCT => 'required|numeric',
		self::FIELD_OUTCOME_IS_A_GOAL => 'nullable|boolean',
		self::FIELD_PLAUSIBLY_CAUSAL => 'nullable|boolean',
		self::FIELD_POPULATION_TRAIT_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_PREDICTIVE_PEARSON_CORRELATION_COEFFICIENT => 'required|numeric',
		self::FIELD_PREDICTOR_IS_CONTROLLABLE => 'nullable|boolean',
		self::FIELD_PREDICTS_HIGH_EFFECT_CHANGE => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_PREDICTS_LOW_EFFECT_CHANGE => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_PUBLISHED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_P_VALUE => 'required|numeric',
		self::FIELD_REASON_FOR_ANALYSIS => 'required|max:255',
		self::FIELD_RECORD_SIZE_IN_KB => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_RELATIONSHIP => 'required',
		self::FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT => 'required|numeric',
		self::FIELD_STATISTICAL_SIGNIFICANCE => 'required|numeric',
		self::FIELD_STATUS => 'required|max:25',
		self::FIELD_STRENGTH_LEVEL => 'required',
		self::FIELD_T_VALUE => 'required|numeric',
		self::FIELD_USER_ERROR_MESSAGE => 'nullable|max:65535',
		self::FIELD_VALUE_PREDICTING_HIGH_OUTCOME => 'required|numeric',
		self::FIELD_VALUE_PREDICTING_LOW_OUTCOME => 'required|numeric',
		self::FIELD_WP_POST_ID => 'nullable|numeric|min:0',
		self::FIELD_Z_SCORE => 'required|numeric',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT => 'Pearson correlation coefficient between cause and effect measurements',
		self::FIELD_ONSET_DELAY => 'User estimated or default time after cause measurement before a perceivable effect is observed',
		self::FIELD_DURATION_OF_ACTION => 'Time over which the cause is expected to produce a perceivable effect following the onset delay',
		self::FIELD_NUMBER_OF_PAIRS => 'Number of points that went into the correlation calculation',
		self::FIELD_VALUE_PREDICTING_HIGH_OUTCOME => 'cause value that predicts an above average effect value (in default unit for cause variable)',
		self::FIELD_VALUE_PREDICTING_LOW_OUTCOME => 'cause value that predicts a below average effect value (in default unit for cause variable)',
		self::FIELD_OPTIMAL_PEARSON_PRODUCT => 'Optimal Pearson Product',
		self::FIELD_AVERAGE_VOTE => 'Vote',
		self::FIELD_NUMBER_OF_USERS => 'Number of Users by which correlation is aggregated',
		self::FIELD_NUMBER_OF_CORRELATIONS => 'Number of VariableRelationships by which correlation is aggregated',
		self::FIELD_STATISTICAL_SIGNIFICANCE => 'A function of the effect size and sample size',
		self::FIELD_CAUSE_UNIT_ID => 'Unit ID of Cause',
		self::FIELD_CAUSE_CHANGES => 'The number of times the cause measurement value was different from the one preceding it.',
		self::FIELD_EFFECT_CHANGES => 'The number of times the effect measurement value was different from the one preceding it.',
		self::FIELD_AGGREGATE_QM_SCORE => 'A number representative of the relative importance of the relationship based on the strength, usefulness, and plausible causality.  The higher the number, the greater the perceived importance.  This value can be used for sorting relationships by importance. ',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_STATUS => 'Whether the correlation is being analyzed, needs to be analyzed, or is up to date already.',
		self::FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT => 'User Variable Relationship when cause and effect are reversed. For any causal relationship, the forward correlation should exceed the reverse correlation',
		self::FIELD_PREDICTIVE_PEARSON_CORRELATION_COEFFICIENT => 'Pearson correlation coefficient of cause and effect values lagged by the onset delay and grouped based on the duration of action. ',
		self::FIELD_DATA_SOURCE_NAME => '',
		self::FIELD_PREDICTS_HIGH_EFFECT_CHANGE => 'The percent change in the outcome typically seen when the predictor value is closer to the predictsHighEffect value. ',
		self::FIELD_PREDICTS_LOW_EFFECT_CHANGE => 'The percent change in the outcome from average typically seen when the predictor value is closer to the predictsHighEffect value.',
		self::FIELD_P_VALUE => 'The measure of statistical significance. A value less than 0.05 means that a correlation is statistically significant or consistent enough that it is unlikely to be a coincidence.',
		self::FIELD_T_VALUE => 'Function of correlation and number of samples.',
		self::FIELD_CRITICAL_T_VALUE => 'Value of t from lookup table which t must exceed for significance.',
		self::FIELD_CONFIDENCE_INTERVAL => 'A margin of error around the effect size.  Wider confidence intervals reflect greater uncertainty about the true value of the correlation.',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_AVERAGE_EFFECT => 'The average effect variable measurement value used in analysis in the common unit. ',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE => 'The average effect variable measurement value following an above average cause value (in the common unit). ',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE => 'The average effect variable measurement value following a below average cause value (in the common unit). ',
		self::FIELD_AVERAGE_DAILY_LOW_CAUSE => 'The average of below average cause values (in the common unit). ',
		self::FIELD_AVERAGE_DAILY_HIGH_CAUSE => 'The average of above average cause values (in the common unit). ',
		self::FIELD_POPULATION_TRAIT_PEARSON_CORRELATION_COEFFICIENT => 'The pearson correlation of pairs which each consist of the average cause value and the average effect value for a given user. ',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME => 'A realistic daily value (not a fraction from averaging) that typically precedes below average outcome values. ',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME => 'A realistic daily value (not a fraction from averaging) that typically precedes below average outcome values. ',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_PUBLISHED_AT => 'datetime',
		self::FIELD_WP_POST_ID => '',
		self::FIELD_CAUSE_VARIABLE_CATEGORY_ID => '',
		self::FIELD_EFFECT_VARIABLE_CATEGORY_ID => '',
		self::FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR => 'True if the combination of cause and effect variable categories are generally interesting.  For instance, treatment cause variables paired with symptom effect variables are interesting. ',
		self::FIELD_NEWEST_DATA_AT => 'datetime',
		self::FIELD_ANALYSIS_REQUESTED_AT => 'datetime',
		self::FIELD_REASON_FOR_ANALYSIS => 'The reason analysis was requested.',
		self::FIELD_ANALYSIS_STARTED_AT => 'datetime',
		self::FIELD_ANALYSIS_ENDED_AT => 'datetime',
		self::FIELD_USER_ERROR_MESSAGE => '',
		self::FIELD_INTERNAL_ERROR_MESSAGE => '',
		self::FIELD_CAUSE_VARIABLE_ID => '',
		self::FIELD_EFFECT_VARIABLE_ID => '',
		self::FIELD_CAUSE_BASELINE_AVERAGE_PER_DAY => 'Predictor Average at Baseline (The average low non-treatment value of the predictor per day)',
		self::FIELD_CAUSE_BASELINE_AVERAGE_PER_DURATION_OF_ACTION => 'Predictor Average at Baseline (The average low non-treatment value of the predictor per duration of action)',
		self::FIELD_CAUSE_TREATMENT_AVERAGE_PER_DAY => 'Predictor Average During Treatment (The average high value of the predictor per day considered to be the treatment dosage)',
		self::FIELD_CAUSE_TREATMENT_AVERAGE_PER_DURATION_OF_ACTION => 'Predictor Average During Treatment (The average high value of the predictor per duration of action considered to be the treatment dosage)',
		self::FIELD_EFFECT_BASELINE_AVERAGE => 'Outcome Average at Baseline (The normal value for the outcome seen without treatment during the previous duration of action time span)',
		self::FIELD_EFFECT_BASELINE_RELATIVE_STANDARD_DEVIATION => 'Outcome Average at Baseline (The average value seen for the outcome without treatment during the previous duration of action time span)',
		self::FIELD_EFFECT_BASELINE_STANDARD_DEVIATION => 'Outcome Relative Standard Deviation at Baseline (How much the outcome value normally fluctuates without treatment during the previous duration of action time span)',
		self::FIELD_EFFECT_FOLLOW_UP_AVERAGE => 'Outcome Average at Follow-Up (The average value seen for the outcome during the duration of action following the onset delay of the treatment)',
		self::FIELD_EFFECT_FOLLOW_UP_PERCENT_CHANGE_FROM_BASELINE => 'Outcome Average at Follow-Up (The average value seen for the outcome during the duration of action following the onset delay of the treatment)',
		self::FIELD_Z_SCORE => 'The absolute value of the change over duration of action following the onset delay of treatment divided by the baseline outcome relative standard deviation. A.K.A The number of standard deviations from the mean. A zScore > 2 means pValue < 0.05 and is typically considered statistically significant.',
		self::FIELD_CHARTS => '',
		self::FIELD_NUMBER_OF_VARIABLES_WHERE_BEST_AGGREGATE_CORRELATION => 'Number of Variables for this Best Global Variable Relationship.
                    [Formula: update global_variable_relationships
                        left join (
                            select count(id) as total, best_global_variable_relationship_id
                            from variables
                            group by best_global_variable_relationship_id
                        )
                        as grouped on global_variable_relationships.id = grouped.best_global_variable_relationship_id
                    set global_variable_relationships.number_of_variables_where_best_global_variable_relationship = count(grouped.total)]',
		self::FIELD_DELETION_REASON => 'The reason the variable was deleted.',
		self::FIELD_RECORD_SIZE_IN_KB => '',
		self::FIELD_IS_PUBLIC => '',
		self::FIELD_BORING => 'The relationship is boring if it is obvious, the predictor is not controllable, or the outcome is not a goal, the relationship could not be causal, or the confidence is low.  ',
		self::FIELD_OUTCOME_IS_A_GOAL => 'The effect of a food on the severity of a symptom is useful because you can control the predictor directly. However, the effect of a symptom on the foods you eat is not very useful.  The foods you eat are not generally an objective end in themselves. ',
		self::FIELD_PREDICTOR_IS_CONTROLLABLE => 'The effect of a food on the severity of a symptom is useful because you can control the predictor directly. However, the effect of a symptom on the foods you eat is not very useful.  Symptom severity is not directly controllable. ',
		self::FIELD_PLAUSIBLY_CAUSAL => 'The effect of aspirin on headaches is plausibly causal. The effect of aspirin on precipitation does not have a plausible causal relationship. ',
		self::FIELD_OBVIOUS => 'The effect of aspirin on headaches is obvious. The effect of aspirin on productivity is not obvious. ',
		self::FIELD_NUMBER_OF_UP_VOTES => 'Number of people who feel this relationship is plausible and useful. ',
		self::FIELD_NUMBER_OF_DOWN_VOTES => 'Number of people who feel this relationship is implausible or not useful. ',
		self::FIELD_STRENGTH_LEVEL => 'Strength level describes magnitude of the change in outcome observed following changes in the predictor. ',
		self::FIELD_CONFIDENCE_LEVEL => 'Describes the confidence that the strength level will remain consist in the future.  The more data there is, the lesser the chance that the findings are a spurious correlation. ',
		self::FIELD_RELATIONSHIP => 'If higher predictor values generally precede HIGHER outcome values, the relationship is considered POSITIVE.  If higher predictor values generally precede LOWER outcome values, the relationship is considered NEGATIVE. ',
	];
	protected array $relationshipInfo = [
		'cause_unit' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Unit::class,
			'foreignKeyColumnName' => 'cause_unit_id',
			'foreignKey' => GlobalVariableRelationship::FIELD_CAUSE_UNIT_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Unit::FIELD_ID,
			'ownerKeyColumnName' => 'cause_unit_id',
			'ownerKey' => GlobalVariableRelationship::FIELD_CAUSE_UNIT_ID,
			'methodName' => 'cause_unit',
		],
		'cause_variable_category' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => VariableCategory::class,
			'foreignKeyColumnName' => 'cause_variable_category_id',
			'foreignKey' => GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => VariableCategory::FIELD_ID,
			'ownerKeyColumnName' => 'cause_variable_category_id',
			'ownerKey' => GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
			'methodName' => 'cause_variable_category',
		],
		'cause_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'cause_variable_id',
			'foreignKey' => GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'cause_variable_id',
			'ownerKey' => GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID,
			'methodName' => 'cause_variable',
		],
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => GlobalVariableRelationship::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => GlobalVariableRelationship::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'effect_variable_category' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => VariableCategory::class,
			'foreignKeyColumnName' => 'effect_variable_category_id',
			'foreignKey' => GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => VariableCategory::FIELD_ID,
			'ownerKeyColumnName' => 'effect_variable_category_id',
			'ownerKey' => GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID,
			'methodName' => 'effect_variable_category',
		],
		'effect_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'effect_variable_id',
			'foreignKey' => GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'effect_variable_id',
			'ownerKey' => GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID,
			'methodName' => 'effect_variable',
		],
		'wp_post' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => WpPost::class,
			'foreignKeyColumnName' => 'wp_post_id',
			'foreignKey' => GlobalVariableRelationship::FIELD_WP_POST_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => WpPost::FIELD_ID,
			'ownerKeyColumnName' => 'wp_post_id',
			'ownerKey' => GlobalVariableRelationship::FIELD_WP_POST_ID,
			'methodName' => 'wp_post',
		],
		'correlation_causality_votes' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CorrelationCausalityVote::class,
			'foreignKey' => CorrelationCausalityVote::FIELD_AGGREGATE_CORRELATION_ID,
			'localKey' => CorrelationCausalityVote::FIELD_ID,
			'methodName' => 'correlation_causality_votes',
		],
		'correlation_usefulness_votes' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CorrelationUsefulnessVote::class,
			'foreignKey' => CorrelationUsefulnessVote::FIELD_AGGREGATE_CORRELATION_ID,
			'localKey' => CorrelationUsefulnessVote::FIELD_ID,
			'methodName' => 'correlation_usefulness_votes',
		],
		'user_variable_relationships' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariableRelationship::class,
			'foreignKey' => UserVariableRelationship::FIELD_AGGREGATE_CORRELATION_ID,
			'localKey' => UserVariableRelationship::FIELD_ID,
			'methodName' => 'user_variable_relationships',
		],
		'variables_where_best_global_variable_relationship' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Variable::class,
			'foreignKey' => Variable::FIELD_BEST_AGGREGATE_CORRELATION_ID,
			'localKey' => Variable::FIELD_ID,
			'methodName' => 'variables_where_best_global_variable_relationship',
		],
		'votes' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Vote::class,
			'foreignKey' => Vote::FIELD_AGGREGATE_CORRELATION_ID,
			'localKey' => Vote::FIELD_ID,
			'methodName' => 'votes',
		],
	];
	public function cause_unit(): BelongsTo{
		return $this->belongsTo(Unit::class, GlobalVariableRelationship::FIELD_CAUSE_UNIT_ID, Unit::FIELD_ID,
			GlobalVariableRelationship::FIELD_CAUSE_UNIT_ID);
	}
	public function cause_variable_category(): BelongsTo{
		return $this->belongsTo(VariableCategory::class, GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
			VariableCategory::FIELD_ID, GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID);
	}
	public function cause_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID, Variable::FIELD_ID,
			GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID);
	}
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, GlobalVariableRelationship::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			GlobalVariableRelationship::FIELD_CLIENT_ID);
	}
	public function effect_variable_category(): BelongsTo{
		return $this->belongsTo(VariableCategory::class, GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID,
			VariableCategory::FIELD_ID, GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID);
	}
	public function effect_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID, Variable::FIELD_ID,
			GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID);
	}
	public function wp_post(): BelongsTo{
		return $this->belongsTo(WpPost::class, GlobalVariableRelationship::FIELD_WP_POST_ID, WpPost::FIELD_ID,
			GlobalVariableRelationship::FIELD_WP_POST_ID);
	}
	public function correlation_causality_votes(): HasMany{
		return $this->hasMany(CorrelationCausalityVote::class, CorrelationCausalityVote::FIELD_AGGREGATE_CORRELATION_ID,
			static::FIELD_ID);
	}
	public function correlation_usefulness_votes(): HasMany{
		return $this->hasMany(CorrelationUsefulnessVote::class,
			CorrelationUsefulnessVote::FIELD_AGGREGATE_CORRELATION_ID, static::FIELD_ID);
	}
	public function correlations(): HasMany{
		return $this->hasMany(UserVariableRelationship::class, [self::FIELD_CAUSE_VARIABLE_ID, self::FIELD_EFFECT_VARIABLE_ID],
			[self::FIELD_CAUSE_VARIABLE_ID, self::FIELD_EFFECT_VARIABLE_ID]);
	}
	public function variables_where_best_global_variable_relationship(): HasMany{
		return $this->hasMany(Variable::class, Variable::FIELD_BEST_AGGREGATE_CORRELATION_ID, static::FIELD_ID);
	}
	public function votes(): HasMany{
		return $this->hasMany(Vote::class, Vote::FIELD_AGGREGATE_CORRELATION_ID, static::FIELD_ID);
	}
}
