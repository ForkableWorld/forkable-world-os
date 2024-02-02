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
use App\Models\UserVariable;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Models\Vote;
use App\Models\WpPost;
use Awobaz\Compoships\Compoships;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseCorrelation
 * @property int $id
 * @property int $user_id
 * @property int $cause_variable_id
 * @property int $effect_variable_id
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
 * @property float $forward_spearman_correlation_coefficient
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
 * @property float $grouped_cause_value_closest_to_value_predicting_low_outcome
 * @property float $grouped_cause_value_closest_to_value_predicting_high_outcome
 * @property string $client_id
 * @property Carbon $published_at
 * @property int $wp_post_id
 * @property string $status
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
 * @property int $cause_user_variable_id
 * @property int $effect_user_variable_id
 * @property Carbon $latest_measurement_start_at
 * @property Carbon $earliest_measurement_start_at
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
 * @property Carbon $experiment_end_at
 * @property Carbon $experiment_start_at
 * @property int $global_variable_relationship_id
 * @property Carbon $aggregated_at
 * @property int $usefulness_vote
 * @property int $causality_vote
 * @property string $deletion_reason
 * @property int $record_size_in_kb
 * @property array $correlations_over_durations
 * @property array $correlations_over_delays
 * @property bool $is_public
 * @property int $sort_order
 * @property bool $boring
 * @property bool $outcome_is_goal
 * @property bool $predictor_is_controllable
 * @property bool $plausibly_causal
 * @property bool $obvious
 * @property int $number_of_up_votes
 * @property int $number_of_down_votes
 * @property string $strength_level
 * @property string $confidence_level
 * @property string $relationship
 * @property GlobalVariableRelationship $global_variable_relationship
 * @property Unit $cause_unit
 * @property VariableCategory $cause_variable_category
 * @property Variable $cause_variable
 * @property OAClient $oa_client
 * @property VariableCategory $effect_variable_category
 * @property Variable $effect_variable
 * @property \App\Models\User $user
 * @property UserVariable $cause_user_variable
 * @property UserVariable $effect_user_variable
 * @property WpPost $wp_post
 * @property Collection|CorrelationCausalityVote[] $correlation_causality_votes
 * @property Collection|CorrelationUsefulnessVote[] $correlation_usefulness_votes
 * @property Collection|UserVariable[] $user_variables_where_best_user_variable_relationship
 * @property Collection|Vote[] $votes
 * @package App\Models\Base
 * @property-read int|null $correlation_causality_votes_count
 * @property-read int|null $correlation_usefulness_votes_count
 * @property-read int|null $user_variables_where_best_user_variable_relationship_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUserVariableRelationship onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereGlobalVariableRelationshipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereAggregatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereAnalysisEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereAnalysisRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereAnalysisStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereAverageDailyHighCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereAverageDailyLowCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereAverageEffect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereAverageEffectFollowingHighCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereAverageEffectFollowingLowCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereAverageForwardPearsonCorrelationOverOnsetDelays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereAverageReversePearsonCorrelationOverOnsetDelays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereCausalityVote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereCauseBaselineAveragePerDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereCauseBaselineAveragePerDurationOfAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereCauseChanges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereCauseFillingValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereCauseNumberOfProcessedDailyMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereCauseNumberOfRawMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereCauseTreatmentAveragePerDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereCauseTreatmentAveragePerDurationOfAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereCauseUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereCauseUserVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereCauseVariableCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereCauseVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereConfidenceInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereCriticalTValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereDataSourceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereDeletionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereDurationOfAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereEarliestMeasurementStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereEffectBaselineAverage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereEffectBaselineRelativeStandardDeviation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereEffectBaselineStandardDeviation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereEffectChanges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereEffectFillingValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereEffectFollowUpAverage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereEffectFollowUpPercentChangeFromBaseline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereEffectNumberOfProcessedDailyMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereEffectNumberOfRawMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereEffectUserVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereEffectVariableCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereEffectVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereExperimentEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereExperimentEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereExperimentStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereExperimentStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereForwardPearsonCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereForwardSpearmanCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereGroupedCauseValueClosestToValuePredictingHighOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereGroupedCauseValueClosestToValuePredictingLowOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereInterestingVariableCategoryPair($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereInternalErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereLatestMeasurementStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereNewestDataAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereNumberOfDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereNumberOfPairs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereOnsetDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereOnsetDelayWithStrongestPearsonCorrelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereOptimalPearsonProduct($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship wherePValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     wherePearsonCorrelationWithNoOnsetDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     wherePredictivePearsonCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     wherePredictsHighEffectChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     wherePredictsLowEffectChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereQmScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereReasonForAnalysis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereRecordSizeInKb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereReversePearsonCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereStatisticalSignificance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereStrongestPearsonCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereTValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereUsefulnessVote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereUserErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereValuePredictingHighOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCorrelation
 *     whereValuePredictingLowOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereWpPostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUserVariableRelationship whereZScore($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUserVariableRelationship withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUserVariableRelationship withoutTrashed()
 * @mixin \Eloquent
 */
abstract class BaseUserVariableRelationship extends BaseModel {
	use SoftDeletes;
	use Compoships;
	public const FIELD_AGGREGATE_CORRELATION_ID = 'global_variable_relationship_id';
	public const FIELD_AGGREGATED_AT = 'aggregated_at';
	public const FIELD_ANALYSIS_ENDED_AT = 'analysis_ended_at';
	public const FIELD_ANALYSIS_REQUESTED_AT = 'analysis_requested_at';
	public const FIELD_ANALYSIS_STARTED_AT = 'analysis_started_at';
	public const FIELD_AVERAGE_DAILY_HIGH_CAUSE = 'average_daily_high_cause';
	public const FIELD_AVERAGE_DAILY_LOW_CAUSE = 'average_daily_low_cause';
	public const FIELD_AVERAGE_EFFECT = 'average_effect';
	public const FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE = 'average_effect_following_high_cause';
	public const FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE = 'average_effect_following_low_cause';
	public const FIELD_AVERAGE_FORWARD_PEARSON_CORRELATION_OVER_ONSET_DELAYS = 'average_forward_pearson_correlation_over_onset_delays';
	public const FIELD_AVERAGE_REVERSE_PEARSON_CORRELATION_OVER_ONSET_DELAYS = 'average_reverse_pearson_correlation_over_onset_delays';
	public const FIELD_BORING = 'boring';
	public const FIELD_CAUSALITY_VOTE = 'causality_vote';
	public const FIELD_CAUSE_BASELINE_AVERAGE_PER_DAY = 'cause_baseline_average_per_day';
	public const FIELD_CAUSE_BASELINE_AVERAGE_PER_DURATION_OF_ACTION = 'cause_baseline_average_per_duration_of_action';
	public const FIELD_CAUSE_CHANGES = 'cause_changes';
	public const FIELD_CAUSE_FILLING_VALUE = 'cause_filling_value';
	public const FIELD_CAUSE_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS = 'cause_number_of_processed_daily_measurements';
	public const FIELD_CAUSE_NUMBER_OF_RAW_MEASUREMENTS = 'cause_number_of_raw_measurements';
	public const FIELD_CAUSE_TREATMENT_AVERAGE_PER_DAY = 'cause_treatment_average_per_day';
	public const FIELD_CAUSE_TREATMENT_AVERAGE_PER_DURATION_OF_ACTION = 'cause_treatment_average_per_duration_of_action';
	public const FIELD_CAUSE_UNIT_ID = 'cause_unit_id';
	public const FIELD_CAUSE_USER_VARIABLE_ID = 'cause_user_variable_id';
	public const FIELD_CAUSE_VARIABLE_CATEGORY_ID = 'cause_variable_category_id';
	public const FIELD_CAUSE_VARIABLE_ID = 'cause_variable_id';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CONFIDENCE_INTERVAL = 'confidence_interval';
	public const FIELD_CONFIDENCE_LEVEL = 'confidence_level';
	public const FIELD_CORRELATIONS_OVER_DELAYS = 'correlations_over_delays';
	public const FIELD_CORRELATIONS_OVER_DURATIONS = 'correlations_over_durations';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_CRITICAL_T_VALUE = 'critical_t_value';
	public const FIELD_DATA_SOURCE_NAME = 'data_source_name';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_DELETION_REASON = 'deletion_reason';
	public const FIELD_DURATION_OF_ACTION = 'duration_of_action';
	public const FIELD_EARLIEST_MEASUREMENT_START_AT = 'earliest_measurement_start_at';
	public const FIELD_EFFECT_BASELINE_AVERAGE = 'effect_baseline_average';
	public const FIELD_EFFECT_BASELINE_RELATIVE_STANDARD_DEVIATION = 'effect_baseline_relative_standard_deviation';
	public const FIELD_EFFECT_BASELINE_STANDARD_DEVIATION = 'effect_baseline_standard_deviation';
	public const FIELD_EFFECT_CHANGES = 'effect_changes';
	public const FIELD_EFFECT_FILLING_VALUE = 'effect_filling_value';
	public const FIELD_EFFECT_FOLLOW_UP_AVERAGE = 'effect_follow_up_average';
	public const FIELD_EFFECT_FOLLOW_UP_PERCENT_CHANGE_FROM_BASELINE = 'effect_follow_up_percent_change_from_baseline';
	public const FIELD_EFFECT_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS = 'effect_number_of_processed_daily_measurements';
	public const FIELD_EFFECT_NUMBER_OF_RAW_MEASUREMENTS = 'effect_number_of_raw_measurements';
	public const FIELD_EFFECT_USER_VARIABLE_ID = 'effect_user_variable_id';
	public const FIELD_EFFECT_VARIABLE_CATEGORY_ID = 'effect_variable_category_id';
	public const FIELD_EFFECT_VARIABLE_ID = 'effect_variable_id';
	public const FIELD_EXPERIMENT_END_AT = 'experiment_end_at';
	public const FIELD_EXPERIMENT_START_AT = 'experiment_start_at';
	public const FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT = 'forward_pearson_correlation_coefficient';
	public const FIELD_FORWARD_SPEARMAN_CORRELATION_COEFFICIENT = 'forward_spearman_correlation_coefficient';
	public const FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME = 'grouped_cause_value_closest_to_value_predicting_high_outcome';
	public const FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME = 'grouped_cause_value_closest_to_value_predicting_low_outcome';
	public const FIELD_ID = 'id';
	public const FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR = 'interesting_variable_category_pair';
	public const FIELD_INTERNAL_ERROR_MESSAGE = 'internal_error_message';
	public const FIELD_IS_PUBLIC = 'is_public';
	public const FIELD_LATEST_MEASUREMENT_START_AT = 'latest_measurement_start_at';
	public const FIELD_NEWEST_DATA_AT = 'newest_data_at';
	public const FIELD_NUMBER_OF_DAYS = 'number_of_days';
	public const FIELD_NUMBER_OF_DOWN_VOTES = 'number_of_down_votes';
	public const FIELD_NUMBER_OF_PAIRS = 'number_of_pairs';
	public const FIELD_NUMBER_OF_UP_VOTES = 'number_of_up_votes';
	public const FIELD_OBVIOUS = 'obvious';
	public const FIELD_ONSET_DELAY = 'onset_delay';
	public const FIELD_ONSET_DELAY_WITH_STRONGEST_PEARSON_CORRELATION = 'onset_delay_with_strongest_pearson_correlation';
	public const FIELD_OPTIMAL_PEARSON_PRODUCT = 'optimal_pearson_product';
	public const FIELD_OUTCOME_IS_GOAL = 'outcome_is_goal';
	public const FIELD_P_VALUE = 'p_value';
	public const FIELD_PEARSON_CORRELATION_WITH_NO_ONSET_DELAY = 'pearson_correlation_with_no_onset_delay';
	public const FIELD_PLAUSIBLY_CAUSAL = 'plausibly_causal';
	public const FIELD_PREDICTIVE_PEARSON_CORRELATION_COEFFICIENT = 'predictive_pearson_correlation_coefficient';
	public const FIELD_PREDICTOR_IS_CONTROLLABLE = 'predictor_is_controllable';
	public const FIELD_PREDICTS_HIGH_EFFECT_CHANGE = 'predicts_high_effect_change';
	public const FIELD_PREDICTS_LOW_EFFECT_CHANGE = 'predicts_low_effect_change';
	public const FIELD_PUBLISHED_AT = 'published_at';
	public const FIELD_QM_SCORE = 'qm_score';
	public const FIELD_REASON_FOR_ANALYSIS = 'reason_for_analysis';
	public const FIELD_RECORD_SIZE_IN_KB = 'record_size_in_kb';
	public const FIELD_RELATIONSHIP = 'relationship';
	public const FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT = 'reverse_pearson_correlation_coefficient';
	public const FIELD_SLUG = 'slug';
	public const FIELD_SORT_ORDER = 'sort_order';
	public const FIELD_STATISTICAL_SIGNIFICANCE = 'statistical_significance';
	public const FIELD_STATUS = 'status';
	public const FIELD_STRENGTH_LEVEL = 'strength_level';
	public const FIELD_STRONGEST_PEARSON_CORRELATION_COEFFICIENT = 'strongest_pearson_correlation_coefficient';
	public const FIELD_T_VALUE = 't_value';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USEFULNESS_VOTE = 'usefulness_vote';
	public const FIELD_USER_ERROR_MESSAGE = 'user_error_message';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_VALUE_PREDICTING_HIGH_OUTCOME = 'value_predicting_high_outcome';
	public const FIELD_VALUE_PREDICTING_LOW_OUTCOME = 'value_predicting_low_outcome';
	public const FIELD_WP_POST_ID = 'wp_post_id';
	public const FIELD_Z_SCORE = 'z_score';
	public const TABLE = 'user_variable_relationships';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'Examination of the relationship between predictor and outcome variables.  This includes the potential optimal values for a given variable. ';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_PUBLISHED_AT => 'datetime',
        self::FIELD_NEWEST_DATA_AT => 'datetime',
        self::FIELD_ANALYSIS_REQUESTED_AT => 'datetime',
        self::FIELD_ANALYSIS_STARTED_AT => 'datetime',
        self::FIELD_ANALYSIS_ENDED_AT => 'datetime',
        self::FIELD_LATEST_MEASUREMENT_START_AT => 'datetime',
        self::FIELD_EARLIEST_MEASUREMENT_START_AT => 'datetime',
        self::FIELD_EXPERIMENT_END_AT => 'datetime',
        self::FIELD_EXPERIMENT_START_AT => 'datetime',
        self::FIELD_AGGREGATED_AT => 'datetime',
		self::FIELD_AGGREGATE_CORRELATION_ID => 'int',
		self::FIELD_AVERAGE_DAILY_HIGH_CAUSE => 'float',
		self::FIELD_AVERAGE_DAILY_LOW_CAUSE => 'float',
		self::FIELD_AVERAGE_EFFECT => 'float',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE => 'float',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE => 'float',
		self::FIELD_AVERAGE_FORWARD_PEARSON_CORRELATION_OVER_ONSET_DELAYS => 'float',
		self::FIELD_AVERAGE_REVERSE_PEARSON_CORRELATION_OVER_ONSET_DELAYS => 'float',
		self::FIELD_BORING => 'bool',
		self::FIELD_CAUSALITY_VOTE => 'int',
		self::FIELD_CAUSE_BASELINE_AVERAGE_PER_DAY => 'float',
		self::FIELD_CAUSE_BASELINE_AVERAGE_PER_DURATION_OF_ACTION => 'float',
		self::FIELD_CAUSE_CHANGES => 'int',
		self::FIELD_CAUSE_FILLING_VALUE => 'float',
		self::FIELD_CAUSE_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => 'int',
		self::FIELD_CAUSE_NUMBER_OF_RAW_MEASUREMENTS => 'int',
		self::FIELD_CAUSE_TREATMENT_AVERAGE_PER_DAY => 'float',
		self::FIELD_CAUSE_TREATMENT_AVERAGE_PER_DURATION_OF_ACTION => 'float',
		self::FIELD_CAUSE_UNIT_ID => 'int',
		self::FIELD_CAUSE_USER_VARIABLE_ID => 'int',
		self::FIELD_CAUSE_VARIABLE_CATEGORY_ID => 'int',
		self::FIELD_CAUSE_VARIABLE_ID => 'int',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_CONFIDENCE_INTERVAL => 'float',
		self::FIELD_CONFIDENCE_LEVEL => 'string',
		self::FIELD_CORRELATIONS_OVER_DELAYS => 'array',
		self::FIELD_CORRELATIONS_OVER_DURATIONS => 'array',
		self::FIELD_CRITICAL_T_VALUE => 'float',
		self::FIELD_DATA_SOURCE_NAME => 'string',
		self::FIELD_DELETION_REASON => 'string',
		self::FIELD_DURATION_OF_ACTION => 'int',
		self::FIELD_EFFECT_BASELINE_AVERAGE => 'float',
		self::FIELD_EFFECT_BASELINE_RELATIVE_STANDARD_DEVIATION => 'float',
		self::FIELD_EFFECT_BASELINE_STANDARD_DEVIATION => 'float',
		self::FIELD_EFFECT_CHANGES => 'int',
		self::FIELD_EFFECT_FILLING_VALUE => 'float',
		self::FIELD_EFFECT_FOLLOW_UP_AVERAGE => 'float',
		self::FIELD_EFFECT_FOLLOW_UP_PERCENT_CHANGE_FROM_BASELINE => 'float',
		self::FIELD_EFFECT_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => 'int',
		self::FIELD_EFFECT_NUMBER_OF_RAW_MEASUREMENTS => 'int',
		self::FIELD_EFFECT_USER_VARIABLE_ID => 'int',
		self::FIELD_EFFECT_VARIABLE_CATEGORY_ID => 'int',
		self::FIELD_EFFECT_VARIABLE_ID => 'int',
		self::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT => 'float',
		self::FIELD_FORWARD_SPEARMAN_CORRELATION_COEFFICIENT => 'float',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME => 'float',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME => 'float',
		self::FIELD_ID => 'int',
		self::FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR => 'bool',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'string',
		self::FIELD_IS_PUBLIC => 'bool',
		self::FIELD_NUMBER_OF_DAYS => 'int',
		self::FIELD_NUMBER_OF_DOWN_VOTES => 'int',
		self::FIELD_NUMBER_OF_PAIRS => 'int',
		self::FIELD_NUMBER_OF_UP_VOTES => 'int',
		self::FIELD_OBVIOUS => 'bool',
		self::FIELD_ONSET_DELAY => 'int',
		self::FIELD_ONSET_DELAY_WITH_STRONGEST_PEARSON_CORRELATION => 'int',
		self::FIELD_OPTIMAL_PEARSON_PRODUCT => 'float',
		self::FIELD_OUTCOME_IS_GOAL => 'bool',
		self::FIELD_PEARSON_CORRELATION_WITH_NO_ONSET_DELAY => 'float',
		self::FIELD_PLAUSIBLY_CAUSAL => 'bool',
		self::FIELD_PREDICTIVE_PEARSON_CORRELATION_COEFFICIENT => 'float',
		self::FIELD_PREDICTOR_IS_CONTROLLABLE => 'bool',
		self::FIELD_PREDICTS_HIGH_EFFECT_CHANGE => 'int',
		self::FIELD_PREDICTS_LOW_EFFECT_CHANGE => 'int',
		self::FIELD_P_VALUE => 'float',
		self::FIELD_QM_SCORE => 'float',
		self::FIELD_REASON_FOR_ANALYSIS => 'string',
		self::FIELD_RECORD_SIZE_IN_KB => 'int',
		self::FIELD_RELATIONSHIP => 'string',
		self::FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT => 'float',
		self::FIELD_SORT_ORDER => 'int',
		self::FIELD_STATISTICAL_SIGNIFICANCE => 'float',
		self::FIELD_STATUS => 'string',
		self::FIELD_STRENGTH_LEVEL => 'string',
		self::FIELD_STRONGEST_PEARSON_CORRELATION_COEFFICIENT => 'float',
		self::FIELD_T_VALUE => 'float',
		self::FIELD_USEFULNESS_VOTE => 'int',
		self::FIELD_USER_ERROR_MESSAGE => 'string',
		self::FIELD_USER_ID => 'int',
		self::FIELD_VALUE_PREDICTING_HIGH_OUTCOME => 'float',
		self::FIELD_VALUE_PREDICTING_LOW_OUTCOME => 'float',
		self::FIELD_WP_POST_ID => 'int',
		self::FIELD_Z_SCORE => 'float',	];
	protected array $rules = [
		self::FIELD_AGGREGATED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_AGGREGATE_CORRELATION_ID => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_ANALYSIS_ENDED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_REQUESTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_STARTED_AT => 'required|date',
		self::FIELD_AVERAGE_DAILY_HIGH_CAUSE => 'required|numeric',
		self::FIELD_AVERAGE_DAILY_LOW_CAUSE => 'required|numeric',
		self::FIELD_AVERAGE_EFFECT => 'required|numeric',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE => 'required|numeric',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE => 'required|numeric',
		self::FIELD_AVERAGE_FORWARD_PEARSON_CORRELATION_OVER_ONSET_DELAYS => 'nullable|numeric',
		self::FIELD_AVERAGE_REVERSE_PEARSON_CORRELATION_OVER_ONSET_DELAYS => 'nullable|numeric',
		self::FIELD_BORING => 'nullable|boolean',
		self::FIELD_CAUSALITY_VOTE => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_CAUSE_BASELINE_AVERAGE_PER_DAY => 'required|numeric',
		self::FIELD_CAUSE_BASELINE_AVERAGE_PER_DURATION_OF_ACTION => 'required|numeric',
		self::FIELD_CAUSE_CHANGES => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_CAUSE_FILLING_VALUE => 'nullable|numeric',
		self::FIELD_CAUSE_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_CAUSE_NUMBER_OF_RAW_MEASUREMENTS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_CAUSE_TREATMENT_AVERAGE_PER_DAY => 'required|numeric',
		self::FIELD_CAUSE_TREATMENT_AVERAGE_PER_DURATION_OF_ACTION => 'required|numeric',
		self::FIELD_CAUSE_UNIT_ID => 'nullable|integer|min:0|max:65535',
		self::FIELD_CAUSE_USER_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_CAUSE_VARIABLE_CATEGORY_ID => 'required|boolean',
		self::FIELD_CAUSE_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_CONFIDENCE_INTERVAL => 'required|numeric',
		self::FIELD_CONFIDENCE_LEVEL => 'required',
		self::FIELD_CORRELATIONS_OVER_DELAYS => 'required|max:65535',
		self::FIELD_CORRELATIONS_OVER_DURATIONS => 'required|max:65535',
		self::FIELD_CRITICAL_T_VALUE => 'required|numeric',
		self::FIELD_DATA_SOURCE_NAME => 'nullable|max:255',
		self::FIELD_DELETION_REASON => 'nullable|max:280',
		self::FIELD_DURATION_OF_ACTION => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_EARLIEST_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_EFFECT_BASELINE_AVERAGE => 'nullable|numeric',
		self::FIELD_EFFECT_BASELINE_RELATIVE_STANDARD_DEVIATION => 'required|numeric',
		self::FIELD_EFFECT_BASELINE_STANDARD_DEVIATION => 'nullable|numeric',
		self::FIELD_EFFECT_CHANGES => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_EFFECT_FILLING_VALUE => 'nullable|numeric',
		self::FIELD_EFFECT_FOLLOW_UP_AVERAGE => 'required|numeric',
		self::FIELD_EFFECT_FOLLOW_UP_PERCENT_CHANGE_FROM_BASELINE => 'required|numeric',
		self::FIELD_EFFECT_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_EFFECT_NUMBER_OF_RAW_MEASUREMENTS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_EFFECT_USER_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_EFFECT_VARIABLE_CATEGORY_ID => 'required|boolean',
		self::FIELD_EFFECT_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_EXPERIMENT_END_AT => 'required|date',
		self::FIELD_EXPERIMENT_START_AT => 'required|date',
		self::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_FORWARD_SPEARMAN_CORRELATION_COEFFICIENT => 'required|numeric',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME => 'required|numeric',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME => 'required|numeric',
		self::FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR => 'required|boolean',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'nullable|max:65535',
		self::FIELD_IS_PUBLIC => 'nullable|boolean',
		self::FIELD_LATEST_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_NEWEST_DATA_AT => 'required|date',
		self::FIELD_NUMBER_OF_DAYS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_DOWN_VOTES => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_PAIRS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_UP_VOTES => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_OBVIOUS => 'nullable|boolean',
		self::FIELD_ONSET_DELAY => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_ONSET_DELAY_WITH_STRONGEST_PEARSON_CORRELATION => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_OPTIMAL_PEARSON_PRODUCT => 'nullable|numeric',
		self::FIELD_OUTCOME_IS_GOAL => 'nullable|boolean',
		self::FIELD_PEARSON_CORRELATION_WITH_NO_ONSET_DELAY => 'nullable|numeric',
		self::FIELD_PLAUSIBLY_CAUSAL => 'nullable|boolean',
		self::FIELD_PREDICTIVE_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_PREDICTOR_IS_CONTROLLABLE => 'nullable|boolean',
		self::FIELD_PREDICTS_HIGH_EFFECT_CHANGE => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_PREDICTS_LOW_EFFECT_CHANGE => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_PUBLISHED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_P_VALUE => 'nullable|numeric',
		self::FIELD_QM_SCORE => 'nullable|numeric',
		self::FIELD_REASON_FOR_ANALYSIS => 'required|max:255',
		self::FIELD_RECORD_SIZE_IN_KB => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_RELATIONSHIP => 'required',
		self::FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_SORT_ORDER => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_STATISTICAL_SIGNIFICANCE => 'nullable|numeric',
		self::FIELD_STATUS => 'nullable|max:25',
		self::FIELD_STRENGTH_LEVEL => 'required',
		self::FIELD_STRONGEST_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_T_VALUE => 'nullable|numeric',
		self::FIELD_USEFULNESS_VOTE => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_USER_ERROR_MESSAGE => 'nullable|max:65535',
		self::FIELD_USER_ID => 'required|numeric|min:0',
		self::FIELD_VALUE_PREDICTING_HIGH_OUTCOME => 'nullable|numeric',
		self::FIELD_VALUE_PREDICTING_LOW_OUTCOME => 'nullable|numeric',
		self::FIELD_WP_POST_ID => 'nullable|numeric|min:0',
		self::FIELD_Z_SCORE => 'nullable|numeric',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_USER_ID => '',
		self::FIELD_CAUSE_VARIABLE_ID => '',
		self::FIELD_EFFECT_VARIABLE_ID => '',
		self::FIELD_QM_SCORE => 'A number representative of the relative importance of the relationship based on the strength,
                    usefulness, and plausible causality.  The higher the number, the greater the perceived importance.
                    This value can be used for sorting relationships by importance.  ',
		self::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT => 'Pearson correlation coefficient between cause and effect measurements',
		self::FIELD_VALUE_PREDICTING_HIGH_OUTCOME => 'cause value that predicts an above average effect value (in default unit for cause variable)',
		self::FIELD_VALUE_PREDICTING_LOW_OUTCOME => 'cause value that predicts a below average effect value (in default unit for cause variable)',
		self::FIELD_PREDICTS_HIGH_EFFECT_CHANGE => 'The percent change in the outcome typically seen when the predictor value is closer to the predictsHighEffect value. ',
		self::FIELD_PREDICTS_LOW_EFFECT_CHANGE => 'The percent change in the outcome from average typically seen when the predictor value is closer to the predictsHighEffect value.',
		self::FIELD_AVERAGE_EFFECT => 'The average effect variable measurement value used in analysis in the common unit. ',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE => 'The average effect variable measurement value following an above average cause value (in the common unit). ',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE => 'The average effect variable measurement value following a below average cause value (in the common unit). ',
		self::FIELD_AVERAGE_DAILY_LOW_CAUSE => 'The average of below average cause values (in the common unit). ',
		self::FIELD_AVERAGE_DAILY_HIGH_CAUSE => 'The average of above average cause values (in the common unit). ',
		self::FIELD_AVERAGE_FORWARD_PEARSON_CORRELATION_OVER_ONSET_DELAYS => '',
		self::FIELD_AVERAGE_REVERSE_PEARSON_CORRELATION_OVER_ONSET_DELAYS => '',
		self::FIELD_CAUSE_CHANGES => 'The number of times the cause measurement value was different from the one preceding it. ',
		self::FIELD_CAUSE_FILLING_VALUE => '',
		self::FIELD_CAUSE_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => '',
		self::FIELD_CAUSE_NUMBER_OF_RAW_MEASUREMENTS => '',
		self::FIELD_CAUSE_UNIT_ID => 'Unit ID of Cause',
		self::FIELD_CONFIDENCE_INTERVAL => 'A margin of error around the effect size.  Wider confidence intervals reflect greater uncertainty about the true value of the correlation.',
		self::FIELD_CRITICAL_T_VALUE => 'Value of t from lookup table which t must exceed for significance.',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DATA_SOURCE_NAME => '',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_DURATION_OF_ACTION => 'Time over which the cause is expected to produce a perceivable effect following the onset delay',
		self::FIELD_EFFECT_CHANGES => 'The number of times the effect measurement value was different from the one preceding it. ',
		self::FIELD_EFFECT_FILLING_VALUE => '',
		self::FIELD_EFFECT_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => '',
		self::FIELD_EFFECT_NUMBER_OF_RAW_MEASUREMENTS => '',
		self::FIELD_FORWARD_SPEARMAN_CORRELATION_COEFFICIENT => 'Predictive spearman correlation of the lagged pair data. While the Pearson correlation assesses linear relationships, the Spearman correlation assesses monotonic relationships (whether linear or not).',
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
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME => 'A realistic daily value (not a fraction from averaging) that typically precedes below average outcome values. ',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME => 'A realistic daily value (not a fraction from averaging) that typically precedes below average outcome values. ',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_PUBLISHED_AT => 'datetime',
		self::FIELD_WP_POST_ID => '',
		self::FIELD_STATUS => '',
		self::FIELD_CAUSE_VARIABLE_CATEGORY_ID => '',
		self::FIELD_EFFECT_VARIABLE_CATEGORY_ID => '',
		self::FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR => 'True if the combination of cause and effect variable categories are generally interesting.  For instance, treatment cause variables paired with symptom effect variables are interesting. ',
		self::FIELD_NEWEST_DATA_AT => 'The time the source data was last updated. This indicated whether the analysis is stale and should be performed again. ',
		self::FIELD_ANALYSIS_REQUESTED_AT => 'datetime',
		self::FIELD_REASON_FOR_ANALYSIS => 'The reason analysis was requested.',
		self::FIELD_ANALYSIS_STARTED_AT => 'datetime',
		self::FIELD_ANALYSIS_ENDED_AT => 'datetime',
		self::FIELD_USER_ERROR_MESSAGE => '',
		self::FIELD_INTERNAL_ERROR_MESSAGE => '',
		self::FIELD_CAUSE_USER_VARIABLE_ID => '',
		self::FIELD_EFFECT_USER_VARIABLE_ID => '',
		self::FIELD_LATEST_MEASUREMENT_START_AT => 'datetime',
		self::FIELD_EARLIEST_MEASUREMENT_START_AT => 'datetime',
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
		self::FIELD_EXPERIMENT_END_AT => 'The latest data used in the analysis. ',
		self::FIELD_EXPERIMENT_START_AT => 'The earliest data used in the analysis. ',
		self::FIELD_AGGREGATE_CORRELATION_ID => '',
		self::FIELD_AGGREGATED_AT => 'datetime',
		self::FIELD_USEFULNESS_VOTE => 'The opinion of the data owner on whether or not knowledge of this relationship is useful.
                        -1 corresponds to a down vote. 1 corresponds to an up vote. 0 corresponds to removal of a
                        previous vote.  null corresponds to never having voted before.',
		self::FIELD_CAUSALITY_VOTE => 'The opinion of the data owner on whether or not there is a plausible mechanism of action
                        by which the predictor variable could influence the outcome variable.',
		self::FIELD_DELETION_REASON => 'The reason the variable was deleted.',
		self::FIELD_RECORD_SIZE_IN_KB => '',
		self::FIELD_CORRELATIONS_OVER_DURATIONS => 'Pearson user_variable_relationships calculated with various duration of action lengths. This can be used to compare short and long term effects. ',
		self::FIELD_CORRELATIONS_OVER_DELAYS => 'Pearson user_variable_relationships calculated with various onset delay lags used to identify reversed causality or asses the significant of a correlation with a given lag parameters. ',
		self::FIELD_IS_PUBLIC => '',
		self::FIELD_SORT_ORDER => '',
		self::FIELD_BORING => 'The relationship is boring if it is obvious, the predictor is not controllable, the outcome is not a goal, the relationship could not be causal, or the confidence is low. ',
		self::FIELD_OUTCOME_IS_GOAL => 'The effect of a food on the severity of a symptom is useful because you can control the predictor directly. However, the effect of a symptom on the foods you eat is not very useful.  The foods you eat are not generally an objective end in themselves. ',
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
		'global_variable_relationship' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => GlobalVariableRelationship::class,
			'foreignKeyColumnName' => 'global_variable_relationship_id',
			'foreignKey' => UserVariableRelationship::FIELD_AGGREGATE_CORRELATION_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => GlobalVariableRelationship::FIELD_ID,
			'ownerKeyColumnName' => 'global_variable_relationship_id',
			'ownerKey' => UserVariableRelationship::FIELD_AGGREGATE_CORRELATION_ID,
			'methodName' => 'global_variable_relationship',
		],
		'cause_unit' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Unit::class,
			'foreignKeyColumnName' => 'cause_unit_id',
			'foreignKey' => UserVariableRelationship::FIELD_CAUSE_UNIT_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Unit::FIELD_ID,
			'ownerKeyColumnName' => 'cause_unit_id',
			'ownerKey' => UserVariableRelationship::FIELD_CAUSE_UNIT_ID,
			'methodName' => 'cause_unit',
		],
		'cause_variable_category' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => VariableCategory::class,
			'foreignKeyColumnName' => 'cause_variable_category_id',
			'foreignKey' => UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => VariableCategory::FIELD_ID,
			'ownerKeyColumnName' => 'cause_variable_category_id',
			'ownerKey' => UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
			'methodName' => 'cause_variable_category',
		],
		'cause_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'cause_variable_id',
			'foreignKey' => UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'cause_variable_id',
			'ownerKey' => UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID,
			'methodName' => 'cause_variable',
		],
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => UserVariableRelationship::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => UserVariableRelationship::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'effect_variable_category' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => VariableCategory::class,
			'foreignKeyColumnName' => 'effect_variable_category_id',
			'foreignKey' => UserVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => VariableCategory::FIELD_ID,
			'ownerKeyColumnName' => 'effect_variable_category_id',
			'ownerKey' => UserVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID,
			'methodName' => 'effect_variable_category',
		],
		'effect_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'effect_variable_id',
			'foreignKey' => UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'effect_variable_id',
			'ownerKey' => UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID,
			'methodName' => 'effect_variable',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => UserVariableRelationship::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => UserVariableRelationship::FIELD_USER_ID,
			'methodName' => 'user',
		],
		'cause_user_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => UserVariable::class,
			'foreignKeyColumnName' => 'cause_user_variable_id',
			'foreignKey' => UserVariableRelationship::FIELD_CAUSE_USER_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => UserVariable::FIELD_ID,
			'ownerKeyColumnName' => 'cause_user_variable_id',
			'ownerKey' => UserVariableRelationship::FIELD_CAUSE_USER_VARIABLE_ID,
			'methodName' => 'cause_user_variable',
		],
		'effect_user_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => UserVariable::class,
			'foreignKeyColumnName' => 'effect_user_variable_id',
			'foreignKey' => UserVariableRelationship::FIELD_EFFECT_USER_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => UserVariable::FIELD_ID,
			'ownerKeyColumnName' => 'effect_user_variable_id',
			'ownerKey' => UserVariableRelationship::FIELD_EFFECT_USER_VARIABLE_ID,
			'methodName' => 'effect_user_variable',
		],
		'wp_post' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => WpPost::class,
			'foreignKeyColumnName' => 'wp_post_id',
			'foreignKey' => UserVariableRelationship::FIELD_WP_POST_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => WpPost::FIELD_ID,
			'ownerKeyColumnName' => 'wp_post_id',
			'ownerKey' => UserVariableRelationship::FIELD_WP_POST_ID,
			'methodName' => 'wp_post',
		],
		'correlation_causality_votes' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CorrelationCausalityVote::class,
			'foreignKey' => CorrelationCausalityVote::FIELD_CORRELATION_ID,
			'localKey' => CorrelationCausalityVote::FIELD_ID,
			'methodName' => 'correlation_causality_votes',
		],
		'correlation_usefulness_votes' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CorrelationUsefulnessVote::class,
			'foreignKey' => CorrelationUsefulnessVote::FIELD_CORRELATION_ID,
			'localKey' => CorrelationUsefulnessVote::FIELD_ID,
			'methodName' => 'correlation_usefulness_votes',
		],
		'user_variables_where_best_user_variable_relationship' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariable::class,
			'foreignKey' => UserVariable::FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID,
			'localKey' => UserVariable::FIELD_ID,
			'methodName' => 'user_variables_where_best_user_variable_relationship',
		],
		'votes' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Vote::class,
			'foreignKey' => Vote::FIELD_CORRELATION_ID,
			'localKey' => Vote::FIELD_ID,
			'methodName' => 'votes',
		],
	];
	public function global_variable_relationship(): BelongsTo{
		return $this->belongsTo(GlobalVariableRelationship::class,
			[self::FIELD_CAUSE_VARIABLE_ID, self::FIELD_EFFECT_VARIABLE_ID],
			[self::FIELD_CAUSE_VARIABLE_ID, self::FIELD_EFFECT_VARIABLE_ID]);
	}
	public function cause_unit(): BelongsTo{
		return $this->belongsTo(Unit::class, UserVariableRelationship::FIELD_CAUSE_UNIT_ID, Unit::FIELD_ID,
			UserVariableRelationship::FIELD_CAUSE_UNIT_ID);
	}
	public function cause_variable_category(): BelongsTo{
		return $this->belongsTo(VariableCategory::class, UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
			VariableCategory::FIELD_ID, UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID);
	}
	public function cause_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID, Variable::FIELD_ID,
			UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID);
	}
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, UserVariableRelationship::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			UserVariableRelationship::FIELD_CLIENT_ID);
	}
	public function effect_variable_category(): BelongsTo{
		return $this->belongsTo(VariableCategory::class, UserVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID,
			VariableCategory::FIELD_ID, UserVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID);
	}
	public function effect_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID, Variable::FIELD_ID,
			UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, UserVariableRelationship::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			UserVariableRelationship::FIELD_USER_ID);
	}
	public function cause_user_variable(): BelongsTo{
		return $this->belongsTo(UserVariable::class, UserVariableRelationship::FIELD_CAUSE_USER_VARIABLE_ID, UserVariable::FIELD_ID,
			UserVariableRelationship::FIELD_CAUSE_USER_VARIABLE_ID);
	}
	public function effect_user_variable(): BelongsTo{
		return $this->belongsTo(UserVariable::class, UserVariableRelationship::FIELD_EFFECT_USER_VARIABLE_ID, UserVariable::FIELD_ID,
			UserVariableRelationship::FIELD_EFFECT_USER_VARIABLE_ID);
	}
	public function wp_post(): BelongsTo{
		return $this->belongsTo(WpPost::class, UserVariableRelationship::FIELD_WP_POST_ID, WpPost::FIELD_ID,
			UserVariableRelationship::FIELD_WP_POST_ID);
	}
	public function correlation_causality_votes(): HasMany{
		return $this->hasMany(CorrelationCausalityVote::class, CorrelationCausalityVote::FIELD_CORRELATION_ID,
			static::FIELD_ID);
	}
	public function correlation_usefulness_votes(): HasMany{
		return $this->hasMany(CorrelationUsefulnessVote::class, CorrelationUsefulnessVote::FIELD_CORRELATION_ID,
			static::FIELD_ID);
	}
	public function user_variables_where_best_user_variable_relationship(): HasMany{
		return $this->hasMany(UserVariable::class, UserVariable::FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID, static::FIELD_ID);
	}
	public function votes(): HasMany{
		return $this->hasMany(Vote::class, [self::FIELD_CAUSE_VARIABLE_ID, self::FIELD_EFFECT_VARIABLE_ID],
			[self::FIELD_CAUSE_VARIABLE_ID, self::FIELD_EFFECT_VARIABLE_ID]);
	}
}
