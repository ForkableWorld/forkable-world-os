<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Actions\ActionEvent;
use App\Astral\Actions\FavoriteAction;
use App\Astral\Actions\LikeAction;
use App\Astral\Lenses\CorrelationsWithNoCauseMeasurementsLens;
use App\Astral\Lenses\CorrelationsWithNoChangeLens;
use App\Astral\Lenses\FavoritesLens;
use App\Astral\Lenses\LikesLens;
use App\Buttons\RelationshipButtons\Correlation\CorrelationGlobalVariableRelationshipButton;
use App\Buttons\RelationshipButtons\Correlation\CorrelationCauseUserVariableButton;
use App\Buttons\RelationshipButtons\Correlation\CorrelationEffectUserVariableButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Charts\ChartGroup;
use App\Charts\CorrelationCharts\CorrelationChartGroup;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidStringException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoIdException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NotEnoughMeasurementsForCorrelationException;
use App\Exceptions\NotEnoughOverlappingDataException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\UnauthorizedException;
use App\Fields\Avatar;
use App\Logging\QMLog;
use App\Menus\QMMenu;
use App\Models\Base\BaseUserVariableRelationship;
use App\Properties\Base\BaseEffectFollowUpPercentChangeFromBaselineProperty;
use App\Properties\Base\BasePostStatusProperty;
use App\Properties\UserVariableRelationship\CorrelationExperimentEndAtProperty;
use App\Properties\UserVariableRelationship\CorrelationExperimentStartAtProperty;
use App\Properties\UserVariableRelationship\CorrelationGroupedCauseValueClosestToValuePredictingLowOutcomeProperty;
use App\Properties\UserVariableRelationship\CorrelationStatusProperty;
use App\Properties\UserVariableRelationship\CorrelationValuePredictingHighOutcomeProperty;
use App\Properties\User\UserIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Storage\S3\S3Private;
use App\Studies\PairOfAverages;
use App\Studies\QMPopulationStudy;
use App\Studies\QMStudy;
use App\Studies\QMUserStudy;
use App\Studies\StudyHtml;
use App\Studies\StudyImages;
use App\Studies\StudyLinks;
use App\Studies\StudyText;
use App\Tables\QMTable;
use App\Traits\AnalyzableTrait;
use App\Traits\HasButton;
use App\Traits\HasCharts;
use App\Traits\HasCorrelationCoefficient;
use App\Traits\HasDBModel;
use App\Traits\HasErrors;
use App\Traits\HasFiles;
use App\Traits\HasModel\HasUser;
use App\Traits\HasModel\HasUserCauseAndEffect;
use App\Traits\HasName;
use App\Traits\HasProperty\HasOnsetAndDuration;
use App\Traits\HasVotes;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\CssHelper;
use App\UI\FontAwesome;
use App\UI\HtmlHelper;
use App\UI\ImageHelper;
use App\UI\ImageUrls;
use App\UI\IonIcon;
use App\UI\QMColor;
use App\Utils\Env;
use App\Utils\Stats;
use App\Utils\UrlHelper;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\TreatmentsCommonVariables\BupropionSrCommonVariable;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use LogicException;
use OpenApi\Annotations as OA;
use Overtrue\LaravelFavorite\Traits\Favoriteable;
use Overtrue\LaravelLike\Traits\Likeable;
use Spatie\MediaLibrary\HasMedia;
use Titasgailius\SearchRelations\SearchesRelations;
/** App\Models\UserVariableRelationship
 * @OA\Schema (
 *      definition="UserVariableRelationship",
 *      required={"timestamp", "user_id", "correlation", "cause_variable_id", "effect_variable_id", "onset_delay",
 *     "duration_of_action",
 *     "number_of_pairs", "value_predicting_high_outcome", "value_predicting_low_outcome", "optimal_pearson_product",
 *     "vote", "statistical_significance", "cause_unit", "cause_unit_id", "cause_changes", "effect_changes",
 *     "qm_score", "reverse_pearson_correlation_coefficient", "predictive_pearson_correlation_coefficient"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="timestamp",
 *          description="Time at which correlation was calculated",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="user_id",
 *          description="ID of user that owns this correlation",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="correlation",
 *          description="Pearson correlation coefficient between cause and effect measurements",
 *          type="number",
 *          format="float"
 *      ),
 *      @OA\Property(
 *          property="cause_variable_id",
 *          description="variable ID of the cause variable for which the user desires user_variable_relationships",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="effect_variable_id",
 *          description="variable ID of the effect variable for which the user desires user_variable_relationships",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="onset_delay",
 *          description="User estimated or default time after cause measurement before a perceivable effect is
 *     observed", type="integer", format="int32"
 *      ),
 *      @OA\Property(
 *          property="duration_of_action",
 *          description="Time over which the cause is expected to produce a perceivable effect following the onset
 *     delay", type="integer", format="int32"
 *      ),
 *      @OA\Property(
 *          property="number_of_pairs",
 *          description="Number of points that went into the correlation calculation",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="value_predicting_high_outcome",
 *          description="cause value that predicts an above average effect value (in default unit for cause variable)",
 *          type="number",
 *          format="float"
 *      ),
 *      @OA\Property(
 *          property="value_predicting_low_outcome",
 *          description="cause value that predicts a below average effect value (in default unit for cause variable)",
 *          type="number",
 *          format="float"
 *      ),
 *      @OA\Property(
 *          property="optimal_pearson_product",
 *          description="Optimal Pearson Product",
 *          type="number",
 *          format="float"
 *      ),
 *      @OA\Property(
 *          property="vote",
 *          description="Vote",
 *          type="number",
 *          format="float"
 *      ),
 *      @OA\Property(
 *          property="statistical_significance",
 *          description="A function of the effect size and sample size",
 *          type="number",
 *          format="float"
 *      ),
 *      @OA\Property(
 *          property="cause_unit",
 *          description="Unit of the predictor variable",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="cause_unit_id",
 *          description="Unit ID of the predictor variable",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="cause_changes",
 *          description="Cause changes",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="effect_changes",
 *          description="Effect changes",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="qm_score",
 *          description="QM Score",
 *          type="number",
 *          format="float"
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="When the record was first created. Use ISO 8601 datetime format",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="When the record in the database was last updated. Use ISO 8601 datetime format",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="reverse_pearson_correlation_coefficient",
 *          description="UserVariableRelationship when cause and effect are reversed. For any causal relationship, the forward
 *     correlation should exceed the reverse correlation", type="number", format="float"
 *      ),
 *      @OA\Property(
 *          property="predictive_pearson_correlation_coefficient",
 *          description="Predictive Pearson UserVariableRelationship Coefficient",
 *          type="number",
 *          format="float"
 *      )
 * )
 * @property integer $id
 * @property integer $timestamp Time at which correlation was calculated
 * @property integer $user_id ID of user that owns this correlation
 * @property float $correlation Pearson correlation coefficient between cause and effect measurements
 * @property integer $cause_variable_id variable ID of the cause variable for which the user desires user_variable_relationships
 * @property integer $effect_variable_id variable ID of the effect variable for which the user desires user_variable_relationships
 * @property integer $onset_delay User estimated or default time after cause measurement before a perceivable effect is
 *     observed
 * @property integer $duration_of_action Time over which the cause is expected to produce a perceivable effect
 *     following the onset delay
 * @property integer $number_of_pairs Number of points that went into the correlation calculation
 * @property float $value_predicting_high_outcome cause value that predicts an above average effect value (in default
 *     unit for cause variable)
 * @property float $value_predicting_low_outcome cause value that predicts a below average effect value (in default
 *     unit for cause variable)
 * @property float $optimal_pearson_product Optimal Pearson Product
 * @property float $vote Vote
 * @property float $statistical_significance A function of the effect size and sample size
 * @property integer $cause_unit_id Unit ID of the predictor variable
 * @property integer $cause_changes Cause changes
 * @property integer $effect_changes Effect changes
 * @property float $qm_score QM Score
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property array $correlations_over_durations
 * @property array $correlations_over_delays
 * @property float $reverse_pearson_correlation_coefficient UserVariableRelationship when cause and effect are reversed. For any
 *     causal relationship, the forward correlation should exceed the reverse correlation
 * @property float $predictive_pearson_correlation_coefficient Predictive Pearson UserVariableRelationship Coefficient
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereId($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereTimestamp($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereCorrelation($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereOnsetDelay($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereDurationOfAction($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereNumberOfPairs($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereValuePredictingHighOutcome($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereValuePredictingLowOutcome($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereOptimalPearsonProduct($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereVote($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereStatisticalSignificance($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereCauseUnit($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereCauseUnitId($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereCauseChanges($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereEffectChanges($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereQmScore($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariableRelationship whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserVariableRelationship
 *     whereReversePearsonCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserVariableRelationship
 *     wherePredictivePearsonCorrelationCoefficient($value)
 * @property float $forward_pearson_correlation_coefficient Pearson correlation coefficient between cause and effect
 *     measurements
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserVariableRelationship
 *     whereForwardPearsonCorrelationCoefficient($value)
 * @property int|null $predicts_high_effect_change The percent change in the outcome typically seen when the predictor
 *     value is closer to the predictsHighEffect value.
 * @property int|null $predicts_low_effect_change The percent change in the outcome from average typically seen when
 *     the predictor value is closer to the predictsHighEffect value.
 * @property float|null $average_effect
 * @property float|null $average_effect_following_high_cause
 * @property float|null $average_effect_following_low_cause
 * @property float|null $average_daily_low_cause
 * @property float|null $average_daily_high_cause
 * @property float|null $average_forward_pearson_correlation_over_onset_delays
 * @property float|null $average_reverse_pearson_correlation_over_onset_delays
 * @property float|null $cause_filling_value
 * @property int $cause_number_of_processed_daily_measurements
 * @property int $cause_number_of_raw_measurements
 * @property float|null $confidence_interval A margin of error around the effect size.  Wider confidence intervals
 *     reflect greater uncertainty about the value of the correlation.
 * @property float|null $critical_t_value Value of t from lookup table which t must exceed for significance.
 * @property string|null $data_source
 * @property string|null $deleted_at
 * @property float|null $effect_filling_value
 * @property int $effect_number_of_processed_daily_measurements
 * @property int $effect_number_of_raw_measurements
 * @property float|null $forward_spearman_correlation_coefficient
 * @property int $number_of_days
 * @property int|null $onset_delay_with_strongest_pearson_correlation
 * @property float|null $p_value The measure of statistical significance. A value less than 0.05 means that a
 *     correlation is statistically significant or consistent enough that it is unlikely to be a coincidence.
 * @property float|null $pearson_correlation_with_no_onset_delay
 * @property float|null $strongest_pearson_correlation_coefficient
 * @property float|null $t_value Function of correlation and number of samples.
 * @property float|null $grouped_cause_value_closest_to_value_predicting_low_outcome
 * @property float|null $grouped_cause_value_closest_to_value_predicting_high_outcome
 * @property string|null $client_id
 * @property string|null $published_at
 * @property int|null $wp_post_id
 * @method static Builder|UserVariableRelationship newModelQuery()
 * @method static Builder|UserVariableRelationship newQuery()
 * @method static Builder|UserVariableRelationship query()
 * @method static Builder|UserVariableRelationship whereAverageDailyHighCause($value)
 * @method static Builder|UserVariableRelationship whereAverageDailyLowCause($value)
 * @method static Builder|UserVariableRelationship whereAverageEffect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariableRelationship
 *     whereAverageEffectFollowingHighCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariableRelationship
 *     whereAverageEffectFollowingLowCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariableRelationship
 *     whereAverageForwardPearsonCorrelationOverOnsetDelays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariableRelationship
 *     whereAverageReversePearsonCorrelationOverOnsetDelays($value)
 * @method static Builder|UserVariableRelationship whereCauseFillingValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariableRelationship
 *     whereCauseNumberOfProcessedDailyMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariableRelationship
 *     whereCauseNumberOfRawMeasurements($value)
 * @method static Builder|UserVariableRelationship whereClientId($value)
 * @method static Builder|UserVariableRelationship whereConfidenceInterval($value)
 * @method static Builder|UserVariableRelationship whereCriticalTValue($value)
 * @method static Builder|UserVariableRelationship whereDataSource($value)
 * @method static Builder|UserVariableRelationship whereDeletedAt($value)
 * @method static Builder|UserVariableRelationship whereEffectFillingValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariableRelationship
 *     whereEffectNumberOfProcessedDailyMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariableRelationship
 *     whereEffectNumberOfRawMeasurements($value)
 * @method static Builder|UserVariableRelationship whereExperimentEndTime($value)
 * @method static Builder|UserVariableRelationship whereExperimentStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariableRelationship
 *     whereForwardSpearmanCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariableRelationship
 *     whereGroupedCauseValueClosestToValuePredictingHighOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariableRelationship
 *     whereGroupedCauseValueClosestToValuePredictingLowOutcome($value)
 * @method static Builder|UserVariableRelationship whereNumberOfDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariableRelationship
 *     whereOnsetDelayWithStrongestPearsonCorrelation($value)
 * @method static Builder|UserVariableRelationship wherePValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariableRelationship
 *     wherePearsonCorrelationWithNoOnsetDelay($value)
 * @method static Builder|UserVariableRelationship wherePredictsHighEffectChange($value)
 * @method static Builder|UserVariableRelationship wherePredictsLowEffectChange($value)
 * @method static Builder|UserVariableRelationship wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariableRelationship
 *     whereStrongestPearsonCorrelationCoefficient($value)
 * @method static Builder|UserVariableRelationship whereTValue($value)
 * @method static Builder|UserVariableRelationship whereWpPostId($value)
 * @mixin Eloquent
 * @property string|null $data_source_name
 * @property string|null $status
 * @property int|null $cause_variable_category_id
 * @property int|null $effect_variable_category_id
 * @property int|null $interesting_variable_category_pair
 * @method static Builder|UserVariableRelationship whereCauseVariableCategoryId($value)
 * @method static Builder|UserVariableRelationship whereDataSourceName($value)
 * @method static Builder|UserVariableRelationship whereEffectVariableCategoryId($value)
 * @method static Builder|UserVariableRelationship whereInterestingVariableCategoryPair($value)
 * @method static Builder|UserVariableRelationship whereJsonEncoded($value)
 * @method static Builder|UserVariableRelationship whereStatus($value)
 * @property string|null $newest_data_at
 * @property Carbon|string|null $analysis_ended_at
 * @property string|null $analysis_requested_at
 * @property string|null $reason_for_analysis
 * @property string|null $analysis_started_at
 * @property string|null $user_error_message
 * @property string|null $internal_error_message
 * @property int|null $cause_user_variable_id
 * @property int|null $effect_user_variable_id
 * @method static Builder|UserVariableRelationship whereAnalysisEndedAt($value)
 * @method static Builder|UserVariableRelationship whereAnalysisRequestedAt($value)
 * @method static Builder|UserVariableRelationship whereAnalysisStartedAt($value)
 * @method static Builder|UserVariableRelationship whereCauseUserVariableId($value)
 * @method static Builder|UserVariableRelationship whereEffectUserVariableId($value)
 * @method static Builder|UserVariableRelationship whereInternalErrorMessage($value)
 * @method static Builder|UserVariableRelationship whereNewestDataAt($value)
 * @method static Builder|UserVariableRelationship whereReasonForAnalysis($value)
 * @method static Builder|UserVariableRelationship whereUserErrorMessage($value)
 * @property string|null $latest_measurement_start_at
 * @property string|null $earliest_measurement_start_at
 * @property float|null $cause_baseline_average_per_day Predictor Average at Baseline (The average low non-treatment
 *     value of the predictor per day)
 * @property float|null $cause_baseline_average_per_duration_of_action Predictor Average at Baseline (The average low
 *     non-treatment value of the predictor per duration of action)
 * @property float|null $cause_treatment_average_per_day Predictor Average During Treatment (The average high value of
 *     the predictor per day considered to be the treatment dosage)
 * @property float|null $cause_treatment_average_per_duration_of_action Predictor Average During Treatment (The average
 *     high value of the predictor per duration of action considered to be the treatment dosage)
 * @property float|null $effect_baseline_average Outcome Average at Baseline (The normal value for the outcome seen
 *     without treatment during the previous duration of action time span)
 * @property float|null $effect_baseline_relative_standard_deviation Outcome Average at Baseline (The average value
 *     seen for the outcome without treatment during the previous duration of action time span)
 * @property float|null $effect_baseline_standard_deviation Outcome Relative Standard Deviation at Baseline (How much
 *     the outcome value normally fluctuates without treatment during the previous duration of action time span)
 * @property float|null $effect_follow_up_average Outcome Average at Follow-Up (The average value seen for the outcome
 *     during the duration of action following the onset delay of the treatment)
 * @property float|null $effect_follow_up_percent_change_from_baseline Outcome Average at Follow-Up (The average value
 *     seen for the outcome during the duration of action following the onset delay of the treatment)
 * @property float|null $z_score The absolute value of the change over duration of action following the onset delay of
 *     treatment divided by the baseline outcome relative standard deviation. A.K.A The number of standard deviations
 *     from the mean. A zScore > 2 means pValue < 0.05 and is typically considered statistically significant.
 * @method static Builder|UserVariableRelationship whereCauseBaselineAveragePerDay($value)
 * @method static Builder|UserVariableRelationship whereCauseBaselineAveragePerDurationOfAction($value)
 * @method static Builder|UserVariableRelationship whereCauseTreatmentAveragePerDay($value)
 * @method static Builder|UserVariableRelationship whereCauseTreatmentAveragePerDurationOfAction($value)
 * @method static Builder|UserVariableRelationship whereCauseVariableId($value)
 * @method static Builder|UserVariableRelationship whereEarliestMeasurementStartAt($value)
 * @method static Builder|UserVariableRelationship whereEffectBaselineAverage($value)
 * @method static Builder|UserVariableRelationship whereEffectBaselineRelativeStandardDeviation($value)
 * @method static Builder|UserVariableRelationship whereEffectBaselineStandardDeviation($value)
 * @method static Builder|UserVariableRelationship whereEffectFollowUpAverage($value)
 * @method static Builder|UserVariableRelationship whereEffectFollowUpPercentChangeFromBaseline($value)
 * @method static Builder|UserVariableRelationship whereEffectVariableId($value)
 * @method static Builder|UserVariableRelationship whereLatestMeasurementStartAt($value)
 * @method static Builder|UserVariableRelationship whereZScore($value)
 * @property-read OAClient|null $oa_client
 * @property-read User $user
 * @property-read UserVariable $user_variable
 * @property-read Variable $variable
 * @property-read UserVariable $cause_user_variable
 * @property-read Variable $cause_variable
 * @property-read VariableCategory|null $cause_variable_category
 * @property-read UserVariable $effect_user_variable
 * @property-read Variable $effect_variable
 * @property-read VariableCategory|null $effect_variable_category
 * @property-read Unit|null $unit
 * @property-read WpPost $wp_post
 * @property \Illuminate\Support\Carbon|null $experiment_start_at
 * @property \Illuminate\Support\Carbon|null $experiment_end_at
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|UserVariableRelationship whereExperimentEndAt($value)
 * @method static Builder|UserVariableRelationship whereExperimentStartAt($value)
 * @property int|null $global_variable_relationship_id
 * @property string|null $aggregated_at
 * @method static Builder|UserVariableRelationship whereGlobalVariableRelationshipId($value)
 * @method static Builder|UserVariableRelationship whereAggregatedAt($value)
 * @property int|null $usefulness_vote The opinion of the data owner on whether or not knowledge of this relationship
 *     is useful.
 *                         -1 corresponds to a down vote. 1 corresponds to an up vote. 0 corresponds to removal of a
 *                         previous vote.  null corresponds to never having voted before.
 * @property int|null $causality_vote The opinion of the data owner on whether or not there is a plausible mechanism of
 *     action by which the predictor variable could influence the outcome variable.
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|UserVariableRelationship whereCausalityVote($value)
 * @method static Builder|UserVariableRelationship whereUsefulnessVote($value)*@method getCauseVaribleName()
 * @property-read GlobalVariableRelationship|null $global_variable_relationship
 * @property-read Unit|null $cause_unit
 * @property-read \Illuminate\Database\Eloquent\Collection|CorrelationCausalityVote[] $correlation_causality_votes
 * @property-read int|null $correlation_causality_votes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|CorrelationUsefulnessVote[] $correlation_usefulness_votes
 * @property-read int|null $correlation_usefulness_votes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|UserVariable[] $user_variables_where_best_user_variable_relationship
 * @property-read int|null $user_variables_where_best_user_variable_relationship_count
 * @property-read \Illuminate\Database\Eloquent\Collection|ActionEvent[] $actions
 * @property-read int|null $actions_count
 * @property string|null $deletion_reason The reason the variable was deleted.
 * @property int|null $record_size_in_kb
 * @method static Builder|UserVariableRelationship whereDeletionReason($value)
 * @method static Builder|UserVariableRelationship whereRecordSizeInKb($value)
 */
class UserVariableRelationship extends BaseUserVariableRelationship implements HasMedia {
	use HasFactory;
	use HasUserCauseAndEffect;
	use HasUser, HasErrors, AnalyzableTrait, HasDBModel, HasVotes;
	use HasButton, HasName, HasUserCauseAndEffect, HasCorrelationCoefficient, HasOnsetAndDuration;
	use    HasCharts, HasFiles;
	use Favoriteable;
	use Likeable;
	use SearchesRelations;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = UserVariableRelationship::FIELD_ID;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'cause_variable' => [Variable::FIELD_NAME],
		'effect_variable' => [Variable::FIELD_NAME],
		'user' => [User::FIELD_DISPLAY_NAME],
	];
	public static $group = UserVariableRelationship::CLASS_CATEGORY;
	/**
	 * Indicates if the resource should be globally searchable.
	 * @var bool
	 */
	public static $globallySearchable = false;
	/**
	 * The per-page options used the resource index.
	 * @var array
	 */
	public static $perPageOptions = [10, 25, 50, 100];
	// TODO: Figure out cause of could not find driver use Searchable;
	public const ANALYZABLE           = true;
	public const CLASS_DESCRIPTION    = "Examination of the likely effects of a predictor variable on an outcome variable on average for a specific individual";
	public const CLASS_CATEGORY       = Study::CLASS_CATEGORY;
	public const COLOR                = QMColor::HEX_RED;
	public const DEFAULT_IMAGE        = ImageUrls::SCIENCE_FLASK;
	public const DEFAULT_LIMIT        = 10;
	public const DEFAULT_SEARCH_FIELD = 'effect_variable.' . Variable::FIELD_NAME;
	public const DEFAULT_ORDERINGS    = [self::FIELD_QM_SCORE => self::ORDER_DIRECTION_DESC];
	public const FONT_AWESOME         = FontAwesome::VIAL_SOLID;
	public const FONT_AWESOME_CAUSES  = FontAwesome::SIGN_IN_ALT_SOLID;
	public const FONT_AWESOME_EFFECTS = FontAwesome::SIGN_OUT_ALT_SOLID;
	public static function getSlimClass(): string{
		return QMUserVariableRelationship::class;
	}
	public static function getUniqueIndexColumns(): array{
		return [
			self::FIELD_USER_ID,
			self::FIELD_CAUSE_VARIABLE_ID,
			self::FIELD_EFFECT_VARIABLE_ID,
		];
	}
	protected $with = [
		//'cause_variable:'.Variable::IMPORTANT_FIELDS,
		//'effect_variable:'.Variable::IMPORTANT_FIELDS,
	];
	protected array $rules = [
		self::FIELD_ANALYSIS_ENDED_AT => 'nullable|date',
		// Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_REQUESTED_AT => 'nullable|date',
		// Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_STARTED_AT => 'nullable|date',
		// Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_AVERAGE_DAILY_HIGH_CAUSE => 'nullable|numeric',
		self::FIELD_AVERAGE_DAILY_LOW_CAUSE => 'nullable|numeric',
		self::FIELD_AVERAGE_EFFECT => 'nullable|numeric',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE => 'nullable|numeric',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE => 'nullable|numeric',
		self::FIELD_AVERAGE_FORWARD_PEARSON_CORRELATION_OVER_ONSET_DELAYS => 'nullable|numeric',
		self::FIELD_AVERAGE_REVERSE_PEARSON_CORRELATION_OVER_ONSET_DELAYS => 'nullable|numeric',
		self::FIELD_CAUSE_BASELINE_AVERAGE_PER_DAY => 'required|numeric',
		self::FIELD_CAUSE_BASELINE_AVERAGE_PER_DURATION_OF_ACTION => 'required|numeric',
		self::FIELD_CAUSE_CHANGES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_CAUSE_FILLING_VALUE => 'nullable|numeric',
		self::FIELD_CAUSE_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => 'required|integer|min:0|max:2147483647',
		self::FIELD_CAUSE_NUMBER_OF_RAW_MEASUREMENTS => 'required|integer|min:0|max:2147483647',
		self::FIELD_CAUSE_TREATMENT_AVERAGE_PER_DAY => 'required|numeric',
		self::FIELD_CAUSE_TREATMENT_AVERAGE_PER_DURATION_OF_ACTION => 'required|numeric',
		self::FIELD_CAUSE_UNIT_ID => 'nullable|integer|min:1|max:65535',
		self::FIELD_CAUSE_USER_VARIABLE_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_CAUSE_VARIABLE_CATEGORY_ID => 'required|integer|min:1|max:300',
		self::FIELD_CAUSE_VARIABLE_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_CONFIDENCE_INTERVAL => 'nullable|numeric',
		self::FIELD_CRITICAL_T_VALUE => 'nullable|numeric',
		self::FIELD_DATA_SOURCE_NAME => 'nullable|max:255',
		self::FIELD_DURATION_OF_ACTION => 'nullable|integer|min:60|max:8640000',
		self::FIELD_EARLIEST_MEASUREMENT_START_AT => 'nullable|date',
		// Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_EFFECT_BASELINE_AVERAGE => 'required|numeric',
		self::FIELD_EFFECT_BASELINE_RELATIVE_STANDARD_DEVIATION => 'required|numeric',
		self::FIELD_EFFECT_BASELINE_STANDARD_DEVIATION => 'required|numeric',
		self::FIELD_EFFECT_CHANGES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_EFFECT_FILLING_VALUE => 'nullable|numeric',
		self::FIELD_EFFECT_FOLLOW_UP_AVERAGE => 'required|numeric',
		self::FIELD_EFFECT_FOLLOW_UP_PERCENT_CHANGE_FROM_BASELINE => 'required|numeric',
		self::FIELD_EFFECT_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => 'required|integer|min:0|max:2147483647',
		self::FIELD_EFFECT_NUMBER_OF_RAW_MEASUREMENTS => 'required|integer|min:0|max:2147483647',
		self::FIELD_EFFECT_USER_VARIABLE_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_EFFECT_VARIABLE_CATEGORY_ID => 'required|integer|min:1|max:300',
		self::FIELD_EFFECT_VARIABLE_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_EXPERIMENT_END_AT => 'nullable|date',
		// Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_EXPERIMENT_START_AT => 'nullable|date',
		// Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_FORWARD_SPEARMAN_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME => 'nullable|numeric',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME => 'nullable|numeric',
		self::FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR => 'required|boolean',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'nullable|max:1000',
		self::FIELD_LATEST_MEASUREMENT_START_AT => 'nullable|date',
		// Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_NEWEST_DATA_AT => 'nullable|date',
		// Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_NUMBER_OF_DAYS => 'required|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_PAIRS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_ONSET_DELAY => 'nullable|integer|min:0|max:8640000',
		self::FIELD_ONSET_DELAY_WITH_STRONGEST_PEARSON_CORRELATION => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_OPTIMAL_PEARSON_PRODUCT => 'nullable|numeric',
		self::FIELD_P_VALUE => 'nullable|numeric',
		self::FIELD_PEARSON_CORRELATION_WITH_NO_ONSET_DELAY => 'nullable|numeric',
		self::FIELD_PREDICTIVE_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_PREDICTS_HIGH_EFFECT_CHANGE => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_PREDICTS_LOW_EFFECT_CHANGE => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_PUBLISHED_AT => 'nullable|date',
		// Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_QM_SCORE => 'nullable|numeric',
		self::FIELD_REASON_FOR_ANALYSIS => 'nullable|max:255',
		self::FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_STATISTICAL_SIGNIFICANCE => 'nullable|numeric',
		self::FIELD_STATUS => 'nullable|max:25',
		self::FIELD_STRONGEST_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_T_VALUE => 'nullable|numeric',
		self::FIELD_USER_ERROR_MESSAGE => 'nullable|max:500',
		self::FIELD_USER_ID => 'required|numeric|min:1',
		self::FIELD_VALUE_PREDICTING_HIGH_OUTCOME => 'nullable|numeric',
		self::FIELD_VALUE_PREDICTING_LOW_OUTCOME => 'nullable|numeric',
		self::FIELD_WP_POST_ID => 'nullable|numeric|min:1',
		self::FIELD_Z_SCORE => 'required|numeric',
	];
	/**
	 * @return static[]|Collection
	 */
	public static function getMikesUpVotedCorrelations(){
		$qb = UserVariableRelationship::whereUpVoted()->where(UserVariableRelationship::FIELD_USER_ID, UserIdProperty::USER_ID_MIKE);
		return $qb->get();
	}
	/**
	 * @param $userNameOrId
	 * @param $causeNameOrId
	 * @param $effectNameOrId
	 * @return UserVariableRelationship
	 */
	public static function findByVariableNamesOrIds($userNameOrId, $causeNameOrId, $effectNameOrId): ?UserVariableRelationship{
		if($mem = static::findInMemoryByIds($userNameOrId, $causeNameOrId, $effectNameOrId)){
			return $mem;
		}
		$u = User::findByNameIdOrSynonym($userNameOrId);
		$cause = Variable::findByNameIdOrSynonym($causeNameOrId);
		$effect = Variable::findByNameIdOrSynonym($effectNameOrId);
		$uc = self::whereUserId($u->getId())->where(self::FIELD_CAUSE_VARIABLE_ID, $cause->getId())
			->where(self::FIELD_EFFECT_VARIABLE_ID, $effect->getId())->first();
		if($uc){
			$uc->addToMemory();
			$uc->setRelationAndAddToMemory('user', $u);
			$uc->setRelationAndAddToMemory('cause_variable', $cause);
			$uc->setRelationAndAddToMemory('effect_variable', $effect);
		}
		return $uc;
	}
	/**
	 * @param $userNameOrId
	 * @param $causeNameOrId
	 * @param $effectNameOrId
	 * @return UserVariableRelationship|null
	 */
	public static function findInMemoryByIds($userNameOrId, $causeNameOrId, $effectNameOrId): ?UserVariableRelationship{
		$u = User::findByNameIdOrSynonym($userNameOrId);
		$cause = Variable::findByNameIdOrSynonym($causeNameOrId);
		$effect = Variable::findByNameIdOrSynonym($effectNameOrId);
		$uc = self::findInMemoryWhere([
			self::FIELD_CAUSE_VARIABLE_ID => $cause->getId(),
			self::FIELD_EFFECT_VARIABLE_ID => $effect->getId(),
			self::FIELD_USER_ID => $u->getId(),
		]);
		if($uc){
			$uc->setRelationAndAddToMemory('user', $u);
			$uc->setRelationAndAddToMemory('cause_variable', $cause);
			$uc->setRelationAndAddToMemory('effect_variable', $effect);
		}
		return $uc;
	}
	/**
	 * @return UserVariableRelationship|Builder
	 */
	public static function withUpVotes(){
		return static::whereHas('vote', function($query){
			/** @var Builder $query */
			return $query->where(Vote::FIELD_VALUE, 1);
		});
	}
	/**
	 * @return UserVariableRelationship|Builder
	 */
	public static function upVotedForMike(){
		return static::withUpVotes()->where(self::FIELD_USER_ID, UserIdProperty::USER_ID_MIKE);
	}
	/**
	 * @param $userNameOrId
	 * @param $causeNameOrId
	 * @param $effectNameOrId
	 * @return static|null
	 */
	public static function findByIds($userNameOrId, $causeNameOrId, $effectNameOrId): ?self{
		return self::findByVariableNamesOrIds($userNameOrId, $causeNameOrId, $effectNameOrId);
	}
	public function getStudyId(): string{
		return QMUserStudy::generateStudyId($this->getCauseVariableId(), $this->getEffectVariableId(), $this->user_id,
			$this->getStudyType());
	}
	public function getOrCreateQMGlobalVariableRelationship(): QMGlobalVariableRelationship{
		$agg = $this->getGlobalVariableRelationship();
		if($agg){
			return $agg->getDBModel();
		}
		$dbm = $this->getDBModel();
		return $dbm->getOrCreateQMGlobalVariableRelationship();
	}
	public function getGlobalVariableRelationship(): ?GlobalVariableRelationship{
		if($this->relationLoaded('global_variable_relationship')){
			return $this->global_variable_relationship;
		}
		if($this->global_variable_relationship_id){
			return GlobalVariableRelationship::findInMemoryOrDB($this->global_variable_relationship_id);
		}
		return null;
	}
	public function getOrCreateGlobalVariableRelationship(): GlobalVariableRelationship{
		if($agg = $this->getGlobalVariableRelationship()){
			return $agg;
		}
		$quc = $this->getDBModel();
		$qac = $quc->getOrCreateQMGlobalVariableRelationship();
		return $qac->l();
	}
	public function getShowContent(bool $inlineJs = false): string{
		$s = $this->getStudyHtml();
		return $s->getShowContent();
	}
	/**
	 * @return string
	 * @throws TooSlowToAnalyzeException
	 */
	public function getStaticContent(): string{
		$s = $this->findInMemoryOrNewQMStudy();
		return $s->getStaticContent();
	}
	/**
	 * Get the indexable data array for the model.
	 * @return array
	 */
	public function toSearchableArray(): array{
		$arr = parent::toSearchableArray();
		$arr['cause_variable_synonyms'] = ($this->attributes) ? $this->getCauseVariable()->synonyms_string() : null;
		$arr['effect_variable_synonyms'] = ($this->attributes) ? $this->getEffectVariable()->synonyms_string() : null;
		$arr['cause_variable_name'] = ($this->attributes) ? $this->getCauseVariable()->name : null;
		$arr['effect_variable_name'] = ($this->attributes) ? $this->getEffectVariable()->name : null;
		$arr[self::FIELD_IS_PUBLIC] = ($this->attributes) ? $this->isPublic() : null;
		$arr[self::FIELD_QM_SCORE] = $this->qm_score;
		return $arr;
	}
	public function isPublic(): bool{
		if($this->published_at){
			return true;
		} else{
			return false;
		}
	}
	/**
	 * @return int|null
	 */
	public function getUserVoteValue(): ?int{
		$v = $this->getVotes()->firstWhere(self::FIELD_USER_ID, $this->getUserId());
		if(!$v){
			return null;
		}
		return $v->value;
	}
	/**
	 * @return \Illuminate\Support\Collection|Vote[]
	 */
	public function getVotes(): Collection{
		if($this->global_variable_relationship_id){
			$aggregateCorrelation = $this->getGlobalVariableRelationship();
			if(!$aggregateCorrelation){
				le("Could not get global variable relationship for id {$this->global_variable_relationship_id}");
			}
			return $aggregateCorrelation->getVotes();
		}
		$this->loadMissing('votes');
		return $this->votes;
	}
	/**
	 * @return QMUserVariableRelationship
	 */
	public function getDBModel(): DBModel{
		if($dbm = $this->getDBModelFromMemory()){
			return $dbm;
		}
		$dbm = new QMUserVariableRelationship();
		$dbm->setLaravelModel($this);
		$cause = $this->getCauseUserVariable();
		$dbm->setCauseVariable($cause->getDBModel());
		$effect = $this->getEffectUserVariable();
		$dbm->setEffectVariable($effect->getDBModel());
		$dbm->populateByLaravelModel($this);
		$dbm->populateDefaultFields();
		foreach(QMUserVariableRelationship::DB_FIELD_NAME_TO_PROPERTY_NAME_MAP as $db => $prop){
			$dbm->$prop = $this->getAttribute($db);
		}
		$dbm->userVote = $this->getUserVoteValue();
		if($this->hasId()){
			$dbm->addToMemory();
		}
		//$this->getOrSetStudy();
		return $dbm;
	}
	protected static function newFake(int $userId = UserIdProperty::USER_ID_TEST_USER): BaseModel{
		$m = parent::newFake($userId);
		$cause = BupropionSrCommonVariable::instance();
		$causeUserVariable = $cause->getOrCreateUserVariable($userId);
		$effect = OverallMoodCommonVariable::instance();
		$effectUserVariable = $effect->getOrCreateUserVariable($userId);
		$m->cause_variable_id = $causeUserVariable->getVariableIdAttribute();
		$m->effect_variable_id = $effectUserVariable->getVariableIdAttribute();
		$m->cause_user_variable_id = $causeUserVariable->getUserVariableId();
		$m->effect_user_variable_id = $effectUserVariable->getUserVariableId();
		$m->status = CorrelationStatusProperty::STATUS_WAITING;
		return $m;
	}
	/**
	 * @return StudyHtml
	 */
	public function getStudyHtml(): StudyHtml{
		return $this->findInMemoryOrNewQMStudy()->getStudyHtml();
	}
	public function typeIsIndividual(): bool{
		return true;
	}
	/**
	 * @param array|int $ids
	 * @return UserVariableRelationship[]
	 */
	public static function getWithVariables($ids): array{
		if(!is_array($ids)){
			$ids = [$ids];
		}
		$correlations = self::withVariables()->whereIn(self::FIELD_ID, $ids)->get();
		$byId = [];
		foreach($correlations as $c){
			$byId[$c->id] = $c;
		}
		return $byId;
	}
	public static function withVariables(): Builder{
		return self::with(['cause_variable', 'cause_user_variable', 'effect_variable', 'effect_user_variable']);
	}
	/**
	 * @return \App\Cards\QMListCard
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getOptionsListCard(){
		$c = $this->getCauseUserVariable()->getDBModel();
		$c = $c->l();
		$c->getVariable();
		$s = $this->findInMemoryOrNewQMStudy();
		return $s->getOptionsListCard();
	}
	/**
	 * @param $value
	 */
	public function setInternalErrorMessageAttribute($value){
		$prev = $this->attributes[self::FIELD_INTERNAL_ERROR_MESSAGE] ?? null;
		if($value && $value !== $prev){
			$this->logInfo($value); // Too spammy to log all NotEnoughData exceptions
		}
		$this->attributes[self::FIELD_INTERNAL_ERROR_MESSAGE] = $value;
	}
	/**
	 * @return mixed|null
	 */
	public function getStatisticalSignificanceAttribute(): ?float {
		$val = $this->attributes[self::FIELD_STATISTICAL_SIGNIFICANCE] ?? null;
		if($val === null){
			le("val === null");
		}
		return $val;
	}
	public function getQmScore(): float{
		$s = $this->qm_score;
		if($s === null){
			le("no qm_score");
		}
		return $s;
	}
	public function getTagLine(): string{
		return $this->aboveAverageSentence();
	}
	public function avatarField(): Avatar{
		return $this->gaugeField();
	}
	public function getFields(): array{
		$fields = [];
		$fields[] = $this->imageField()->hideFromDetail();
		$fields[] = $this->imageField()->stacked()->onlyOnDetail();
		$fields[] = $this->nameLinkToShowField();
		$fields[] = $this->studyLinkField()->hideFromIndex();
		$fields = array_merge($fields, $this->getShowablePropertyFields());
		return $fields;
	}
	public function getHtmlContent(): string{
		$page = $this->getHtmlPage();
		return HtmlHelper::getBody($page);
	}
	public function getExperimentStartAtAttribute(): ?string{
		$val = $this->attributes[self::FIELD_EXPERIMENT_START_AT] ?? null;
		if(TimeHelper::isZeroTime($val)){
			return null;
		}
		return $val;
	}
	/**
	 * @param $val
	 */
	public function setExperimentStartAtAttribute($val){
		$this->attributes[self::FIELD_EXPERIMENT_START_AT] = $val;
	}
	public function getExperimentEndAtAttribute(): ?string{
		$val = $this->attributes[self::FIELD_EXPERIMENT_END_AT] ?? null;
		if(TimeHelper::isZeroTime($val)){
			return null;
		}
		return $val;
	}
	/**
	 * @param $val
	 */
	public function setExperimentEndAtAttribute($val){
		$this->attributes[self::FIELD_EXPERIMENT_END_AT] = $val;
	}
	/**
	 * @return bool
	 */
	public function userDownVoted(): bool{
		return $this->getUserVoteValue() === 0;
	}
	/**
	 * @return bool
	 */
	public function userUpVoted(): bool{
		return $this->getUserVoteValue() === 1;
	}
	/**
	 * @return bool
	 */
	public function userDidNotVote(): bool{
		$vote = $this->getUserVoteValue();
		return $vote === null;
	}
	public function getDurationOfAction(): int{
		return $this->attributes[self::FIELD_DURATION_OF_ACTION] ??
			$this->getCauseUserVariable()->getDurationOfAction();
	}
	public function getOnsetDelay(): int{
		return $this->attributes[self::FIELD_ONSET_DELAY];
	}
	/**
	 * @param int|null $precision
	 * @return float
	 */
	public function getChangeFromBaseline(?int $precision = null): ?float{
		$val = $this->attributes[UserVariableRelationship::FIELD_EFFECT_FOLLOW_UP_PERCENT_CHANGE_FROM_BASELINE] ?? null;
		if($val && $precision){
			return Stats::roundByNumberOfSignificantDigits($val, $precision);
		}
		return $val;
	}
	/**
	 * @param int|null $precision
	 * @return float
	 */
	public function getEffectBaselineRelativeStandardDeviation(?int $precision = null): float{
		$val = $this->getAttribute(UserVariableRelationship::FIELD_EFFECT_BASELINE_RELATIVE_STANDARD_DEVIATION);
		if($precision){
			return Stats::roundByNumberOfSignificantDigits($val, $precision);
		}
		return $val;
	}
	public function getCauseVariableCategoryId(): int{
		return $this->attributes[UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID];
	}
	public function getEffectVariableCategoryId(): int{
		return $this->attributes[UserVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID];
	}
	/**
	 * @param int|null $precision
	 * @return float
	 */
	public function getCorrelationCoefficient(int $precision = null): ?float{
		$c = $this->attributes[UserVariableRelationship::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT] ?? null;
		if($c === null){
			return null;
		}
		if($precision){
			return $this->round($c, $precision);
		}
		return $c;
	}
	public function getCauseUserVariableId(): int{
		return $this->attributes[self::FIELD_CAUSE_USER_VARIABLE_ID];
	}
	public function getEffectUserVariableId(): int{
		return $this->attributes[self::FIELD_EFFECT_USER_VARIABLE_ID];
	}
	/**
	 * @return mixed
	 */
	public function getTValue(): ?float{
		return $this->attributes[self::FIELD_T_VALUE] ?? null;
	}
	/**
	 * @return mixed
	 */
	public function getCriticalTValue(): ?float{
		return $this->attributes[self::FIELD_CRITICAL_T_VALUE] ?? null;
	}
	/**
	 * @return static
	 */
	public static function firstOrFakeSave(): BaseModel{
		try {
			$m = parent::firstOrFakeSave();
			$m->analysis_started_at = now_at();
			$m->getCauseUserVariable();
			$m->getEffectUserVariable();
		} catch (\Throwable $e) {
			$m = static::fakeFromPropertyModels();
			$m->analysis_started_at = now_at();
			$m->getCauseUserVariable();
			$m->getEffectUserVariable();
			try {
				$m->save();
			} catch (ModelValidationException $e) {
				le($e);
			}
		}
		return $m;
	}
	public function getCauseVariableId(): int{
		return $this->attributes[self::FIELD_CAUSE_VARIABLE_ID];
	}
	public function getEffectVariableId(): int{
		return $this->attributes[self::FIELD_EFFECT_VARIABLE_ID];
	}
	public function getFileUrls(): array{
		return [];
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
	public function getCorrelationsOverDelaysAttribute(): ?array{
		$str = $this->attributes[UserVariableRelationship::FIELD_CORRELATIONS_OVER_DELAYS] ?? null;
		if(!$str){
			return null;
		}
		try {
			$arr = QMArr::toArray($str);
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			$arr = QMArr::toArray($str);
		}
		if(count($arr) < 2){
			$this->logError("There should be more than one correlation coefficient in correlationsOverDelays");
			return $this->attributes[UserVariableRelationship::FIELD_CORRELATIONS_OVER_DELAYS] = null;
		}
		return $arr;
	}
	/**
	 * @param array|string $val
	 * @noinspection PhpUnused
	 */
	public function setCorrelationsOverDelaysAttribute($val){
		$this->attributes[UserVariableRelationship::FIELD_CORRELATIONS_OVER_DELAYS] = (is_array($val)) ? json_encode($val) : $val;
	}
	public function getCorrelationsOverDurationsAttribute(): ?array{
		$str = $this->attributes[UserVariableRelationship::FIELD_CORRELATIONS_OVER_DURATIONS] ?? null;
		if(!$str){
			return null;
		}
		$arr = QMArr::toArray($str);
		if(count($arr) < 2){
			$this->logError("There should be more than one correlation coefficient in CORRELATIONS_OVER_DURATIONS");
			return $this->attributes[UserVariableRelationship::FIELD_CORRELATIONS_OVER_DURATIONS] = null;
		}
		return $arr;
	}
	/**
	 * @param array|string|null $val
	 * @noinspection PhpUnused
	 */
	public function setCorrelationsOverDurationsAttribute($val){
		$this->attributes[UserVariableRelationship::FIELD_CORRELATIONS_OVER_DURATIONS] = (is_array($val)) ? json_encode($val) : $val;
	}
	/**
	 * @param null $reader
	 * @return bool
	 */
	public function canReadMe($reader = null): bool{
		if(parent::canReadMe($reader)){
			return true;
		}
		return $this->getUser()->share_all_data;
	}
	/**
	 * @return string
	 */
	public function __toString(){
		if($this->relationLoaded('cause_variable') && $this->relationLoaded('effect_variable')){
			return $this->getTitleAttribute() . " " . static::getClassNameTitle();
		}
		return static::getClassNameTitle() . " with ID " . $this->id;
	}
	public function getIsPublic(): ?bool{
		return $this->is_public;
	}
	/**
	 * Get the lenses available for the resource.
	 * @param Request $request
	 * @return array
	 */
	public function lenses(Request $request): array{
		$lenses = parent::getLenses($request);
		$lenses[] = new FavoritesLens();
		$lenses[] = new LikesLens();
		if(QMAuth::isAdmin()){
			$lenses[] = new CorrelationsWithNoCauseMeasurementsLens($this);
			$lenses[] = new CorrelationsWithNoChangeLens($this);
		}
		return $lenses;
	}
	/**
	 * Get the actions available for the resource.
	 * @param Request $request
	 * @return array
	 */
	public function getActions(Request $request): array{
		$actions = parent::getActions($request);
		$actions[] = new LikeAction($request);
		$actions[] = new FavoriteAction($request);
		return $actions;
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{
		return true;
		// Sometimes user is out of date?  return (bool)QMAuth::getUser()->getNumberOfCorrelations();
	}
	public function getIonIcon(): string{
		return IonIcon::person;
	}
	public function getGaugeLink(array $params = [], int $maxLength = 50,
		string $style = CssHelper::SMALL_IMAGE_STYLE): string{
		$url = UrlHelper::addParams($this->getUrl(), $params);
		$name = $this->getTitleAttribute();
		$name = QMStr::truncate($name, $maxLength);
		$img = $this->getGaugeImageUrl();
		return "
            <a href=\"$url\" target='_self' title=\"See $name Details\"'>
                <img src=\"$img\"
                    alt=\"$name\"
                    style=\"$style\"/>
            </a>
        ";
	}
	public function getImageDropDown(): string{
		return $this->getGaugeDropDown();
	}
	public function getGaugeImageUrl(): string{
		return StudyImages::generateGaugeUrl($this->getEffectSize());
	}
	public function getGaugeDropDown(): string{
		$buttons = $this->getDataLabModelButtons();
		if(!$buttons){
			return "";
		}
		$html = HtmlHelper::generateImageNameDropDown($this->getGaugeImageUrl(), $buttons,
			$this->getNameAttribute() . " Options", $this->getTitleAttribute(), CssHelper::SMALL_IMAGE_STYLE);
		return $html;
	}
	public function getGaugeNameDropDown(): string{
		$buttons = $this->getDataLabModelButtons();
		if(!$buttons){
			return "";
		}
		$html = HtmlHelper::generateImageNameDropDown($this->getGaugeImageUrl(), $buttons,
			$this->getNameAttribute() . " Options", $this->getTitleAttribute(), CssHelper::SMALL_IMAGE_STYLE);
		return $html;
	}
	public function getImage(): string{
		if(!$this->hasId()){
			return static::DEFAULT_IMAGE; // This is an empty model
		}
		$causeCatId = $this->attributes[UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID];
		$effectCatId = $this->attributes[UserVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID];
		if(!$this->getCorrelationCoefficient()){
			return StudyImages::generateVariableCategoriesRobotSharingImageWithBackgroundUrl($causeCatId, $effectCatId);
		}
		$size = $this->getEffectSize();
		if($causeCatId && $effectCatId){
			return StudyImages::generateGaugeSharingImageUrl($size, $causeCatId, $effectCatId);
		}
		return StudyImages::generateGaugeUrl($size);
	}
	public function getIcon(): string{
		if(!$this->getCorrelationCoefficient()){
			return ImageHelper::getRobotPuzzledUrl();
		}
		return ImageHelper::getImageUrl('gauges/200-200/' . StudyImages::getGaugeFilename($this->getEffectSize()) .
			'-200-200.png');
	}
	public function getNameAttribute(): string{
		if(!$this->getAttribute(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID)){
			return static::getClassNameTitle();
		}
		return "Relationship Between " . $this->getCauseVariableName() . " and " . $this->getEffectVariableName();
	}
	public function getEffectSizeLinkToStudyWithExplanation(): string{
		/** @var BaseEffectFollowUpPercentChangeFromBaselineProperty $p */
		$p = $this->getPropertyModel(UserVariableRelationship::FIELD_EFFECT_FOLLOW_UP_PERCENT_CHANGE_FROM_BASELINE);
		return $p->getIndexHtml();
	}
	/**
	 * @param bool|null $addressingUser
	 * @return string
	 */
	public function getForMostYourOrThisIndividual(bool $addressingUser = null): string{
		if(QMAuth::getQMUserIfSet() && QMAuth::getQMUserIfSet()->getId() === $this->user_id){
			$prefix = StudyText::TEXT_YOUR;
		} elseif($addressingUser === true){
			$prefix = StudyText::TEXT_YOUR;
		} else{
			$prefix = StudyText::TEXT_THIS_INDIVIDUAL_S;
		}
		return $prefix;
	}
	public function getSubtitleAttribute(): string{
		if(!$this->getAttribute(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID)){
			return static::getClassDescription();
		}
		$str = $this->optimalValueSentence();
		if(!$str){
			$str = "Analysis still in progress";
		}
		return $str;
	}
	/**
	 * @return QMUserStudy|QMPopulationStudy
	 */
	public function findInMemoryOrNewQMStudy(): QMStudy{
		$dbm = $this->getDBModel();
		try {
			return $dbm->findInMemoryOrNewQMStudy();
		} catch (\Throwable $e) {
			return $dbm->findInMemoryOrNewQMStudy();
		}
	}
	public function getGaugeImage(): string{
		return StudyImages::generateGaugeUrl($this->getEffectSize());
	}
	public function getGaugeImageHtml(string $style = null): string{
		return HtmlHelper::getImageHtml($this->getGaugeImageUrl(), $this->getEffectSize(), $style);
	}
	public function getLogMetaDataString(): string{
		return $this->getNameAttribute();
	}
	public function getLogMetaData(?array $meta = []): array{
		try {
			$meta['cause'] = $this->getCauseVariableName();
		} catch (\Throwable $e) {
			QMLog::info("could not get cause because: " . $e->getMessage());
		}
		try {
			$meta['effect'] = $this->getEffectVariableName();
		} catch (\Throwable $e) {
			QMLog::info("could not get effect because: " . $e->getMessage());
		}
		// Causes infinite loops
//		if($this->hasValidId()){
//			try {
//				$meta['EDIT ' . $this->getTitleAttribute()] = $this->getDataLabEditUrl();
//				$meta['SHOW ' . $this->getTitleAttribute()] = $this->getUrl();
//			} catch (\Throwable $e) {
//				QMLog::debug(__METHOD__.": ".$e->getMessage());
//			}
//		}
		return $meta;
	}
	/**
	 * @return string
	 * @throws NotEnoughMeasurementsForCorrelationException
	 */
	public function calculateExperimentStartAt(): string{
		return CorrelationExperimentStartAtProperty::calculate($this);
	}
	/**
	 * @return string
	 * @throws NotEnoughDataException
	 */
	public function getExperimentTimeRangeString(): string{
		$days = $this->getOrCalculateNumberOfDays();
		return "$days from " . $this->getExperimentStartAt() . " to " . $this->getExperimentEndAt();
	}
	/**
	 * @return int
	 * @throws NotEnoughDataException
	 */
	public function getOrCalculateNumberOfDays(): int{
		$previous = $this->getAttribute(UserVariableRelationship::FIELD_NUMBER_OF_DAYS);
		if($previous > 1){
			return $previous;
		}
		$start = $this->getExperimentStartAt();
		$end = $this->getExperimentEndAt();
		$days = (strtotime($end) - strtotime($start)) / 86400;
		if($days < 1){
			$startC = $this->calculateExperimentStartAt();
			$endC = $this->calculateExperimentEndAt();
			$days = (strtotime($endC) - strtotime($startC)) / 86400;
		}
		$this->setAttribute(UserVariableRelationship::FIELD_NUMBER_OF_DAYS, $days);
		return $days;
	}
	/**
	 * @return string
	 * @throws NotEnoughOverlappingDataException
	 */
	public function getExperimentEndAt(): string{
		if($end = $this->getAttribute(UserVariableRelationship::FIELD_EXPERIMENT_END_AT)){
			return $end;
		}
		return $this->calculateExperimentEndAt();
	}
	/**
	 * @return string
	 * @throws NotEnoughOverlappingDataException
	 */
	public function calculateExperimentEndAt(): string{
		return CorrelationExperimentEndAtProperty::calculate($this);
	}
	/**
	 * @return string
	 * @throws NotEnoughMeasurementsForCorrelationException
	 */
	public function getExperimentStartAt(): string{
		if($start = $this->getAttribute(UserVariableRelationship::FIELD_EXPERIMENT_START_AT)){
			return $start;
		}
		return $this->calculateExperimentStartAt();
	}
	/**
	 * @return array
	 */
	public function getStatisticsArray(): array{
		try {
			$zScore = $this->getOrCalculateZScore();
		} catch (NotEnoughDataException $e) {
			$this->addException($e);
			$zScore = $e->userErrorMessageTitle;
		}
		try {
			$confidenceInterval = $this->getConfidenceInterval();
		} catch (NotEnoughDataException $e) {
			$this->addException($e);
			$confidenceInterval = $e->userErrorMessageTitle;
		}
		try {
			$confidenceLevel = $this->getConfidenceLevel();
		} catch (NotEnoughDataException $e) {
			$this->addException($e);
			$confidenceLevel = $e->userErrorMessageTitle;
		}
		$table = [
			"Cause Variable Name" => $this->causeNameWithSuffix(),
			"Effect Variable Name" => $this->effectNameWithSuffix(),
			"Sinn Predictive Coefficient" => $this->getQmScore(),
			"Confidence Level" => $confidenceLevel,
			"Confidence Interval" => $confidenceInterval,
			"Forward Pearson Predictive Coefficient" => $this->getForwardPearsonCorrelationCoefficient(),
			"Critical T Value" => $this->getCriticalTValue(),
			"Duration of Action" => $this->getDurationOfActionHumanString(),
			"Effect Size" => $this->getEffectSize(),
			"Number of Paired Measurements" => $this->getNumberOfPairs(),
			"Optimal Pearson Product" => $this->getOptimalPearsonProduct(),
			"P Value" => $this->getPValue(),
			"Statistical Significance" => $this->getStatisticalSignificance(),
			"Strength of Relationship" => $this->getConfidenceLevel(),
			"Study Type" => $this->getStudyType(),
			"Analysis Performed At" => TimeHelper::YYYYmmddd($this->getUpdatedAt()),
			'Number of Pairs' => $this->getNumberOfPairs(),
			'Number of Raw Predictor Measurements (Including Tags, Joins, and Children)' => $this->causeNumberOfRawMeasurements,
			'Number of Raw Outcome Measurements' => $this->getEffectNumberOfMeasurements(),
			//'Association' => $this->getStrengthLevel(),
			'Confidence' => $this->getConfidenceLevelCell(),
			'Z Score' => $zScore,
			'Review' => $this->getVerificationStatusCell(),
			'Last Analysis' => TimeHelper::YYYYmmddd($this->getUpdatedAt()),
			'Predictor Filling' => $this->getPredictorFillingValue(),
			'pValue' => $this->getPValue(),
			'Predictor Category' => $this->getCauseQMVariableCategory()->getNameAttribute(),
			'Duration of Action (h)' => $this->setDurationOfActionInHours(),
			'Onset Delay (h)' => $this->setOnsetDelayInHours(),
			//'Unique Predictor Values' => $this->getOrCalculatedNumberOfUniqueEffectValues(),
			'Significance' => $this->getStatisticalSignificance(),
		];
		try {
			$table['Experiment Duration (days)'] = $this->getOrCalculateNumberOfDays();
		} catch (NotEnoughDataException $e) {
			$table['Experiment Duration (days)'] = "Could not calculate because " . $e->getMessage();
		}
		$effectName = $this->effectNameWithSuffix();
		$causeName = $this->causeNameWithSuffix();
		$duration = $this->getDurationOfActionHumanString();
		try {
			$table['Experiment Ended'] = $this->getExperimentEndAt();
		} catch (NotEnoughDataException $e) {
			$table['Experiment Ended'] = "Could not calculate because " . $e->getMessage();
		}
		try {
			$table['Experiment Began'] = $this->getExperimentStartAt();
		} catch (NotEnoughDataException $e) {
			$table['Experiment Began'] = "Could not calculate because " . $e->getMessage();
		}
		try {
			$table["Average Daily $causeName Over Previous $duration Before ABOVE Average $effectName"] =
				$this->getDailyValuePredictingHighOutcomeString();
		} catch (IncompatibleUnitException|InvalidVariableValueException $e) {
			$table["Average Daily $causeName Over Previous $duration Before ABOVE Average $effectName"] =
				"Could not calculate because " . $e->getMessage();
		}
		try {
			$table["Average Daily $causeName Over Previous $duration Before BELOW Average $effectName"] =
				$this->getDailyValuePredictingLowOutcomeString();
		} catch (IncompatibleUnitException|InvalidVariableValueException $e) {
			$table["Average Daily $causeName Over Previous $duration Before BELOW Average $effectName"] =
				"Could not calculate because " . $e->getMessage();
		}
		$baselineStats = [
			"Outcome Relative Standard Deviation at Baseline" => $this->effectBaselineRelativeStandardDeviation,
			"Outcome Standard Deviation at Baseline" => $this->effectBaselineStandardDeviation .
				$this->effectVariableCommonUnitAbbreviatedName,
			"Outcome Mean at Baseline" => $this->effectBaselineAverage . $this->effectVariableCommonUnitAbbreviatedName,
			"Average Followup Change From Baseline" => $this->effectFollowUpPercentChangeFromBaseline . "%",
			"Average Absolute Followup Change From Baseline" => $this->effectFollowUpAverage .
				$this->effectVariableCommonUnitAbbreviatedName,
			"Z-Score" => $zScore,
			"Average Predictor Treatment Value" => $this->causeTreatmentAveragePerDurationOfAction .
				$this->causeVariableCommonUnitAbbreviatedName . " over " . $this->getDurationOfActionHumanString(),
		];
		if($this->effectBaselineAverage){
			$table = array_merge($table, $baselineStats);
		}
		return $table;
	}
	/**
	 * @return string
	 */
	public function getStatisticsTableHtml(): string{
		$arr = $this->getStatisticsArray();
		return QMTable::convertObjectToVerticalPropertyValueTableHtml($arr, "Relationship Statistics");
	}
	/**
	 * @param float $value
	 * @param float|null $minimum
	 * @param float|null $maximum
	 * @return float
	 */
	public function calculateClosestCauseValueGroupedOverDurationOfAction(float $value, float $minimum = null,
		float $maximum = null): float{
		$cause = $this->getCauseUserVariable();
		$duration = $this->getDurationOfAction();
		try {
			return $cause->getNearestGroupedValue($value, $minimum, $maximum, $duration);
		} catch (\Throwable $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());
			/** @var LogicException $e */
			throw $e;
		}
	}
	/**
	 * @param string|null $reason
	 * @return string
	 */
	public function getHardDeletionUrl(string $reason = null): string{
		$url = UrlHelper::getApiUrlForPath('sql', [
			'sql' => 'DELETE ' . self::TABLE . ' from ' . self::TABLE . ' where cause_variable_id =' .
				$this->getCauseVariableId() . ' and effect_variable_id=' . $this->getEffectVariableId() .
				' and user_id = ' . $this->getUserId() . " /*
                $reason
            */  ",
		]);
		return $url;
	}
	/**
	 * @return string
	 */
	public function getDataQuantitySentence(): string{
		return "<p>{$this->getNumberOfPairs()} data points were used in this analysis.  The value for {$this->getCauseVariableDisplayName()} " .
			"changed {$this->getCauseChanges()} times, effectively running " . round($this->getCauseChanges() / 2) .
			' separate natural ' . 'experiments.
            </p>';
	}
	public function getFontAwesome(): string{
		return UserVariableRelationship::FONT_AWESOME;
	}
	/**
	 * @inheritDoc
	 */
	public static function wherePostable(){
		$qb = static::query();
		$qb->whereNotIn(static::TABLE . '.' . static::FIELD_USER_ID, UserIdProperty::getTestSystemAndDeletedUserIds());
		return $qb;
	}
	public function weShouldPost(): bool{
		if($this->findInMemoryOrNewQMStudy()->postStatus === BasePostStatusProperty::PUBLISH){
			return true;
		}
		return $this->getIsPublic() && $this->getNumberOfUpVotes() > 0;
	}
	/**
	 * @param string $content
	 * @throws InvalidStringException
	 */
	public static function validatePostContent(string $content){
		QMStr::assertStringDoesNotContain($content, [//"&amp;effectVariableId=",
		], WpPost::FIELD_POST_CONTENT);
		QMStr::assertStringContains($content, [
			"https://app.quantimo.do/api/v2/study?causeVariableId=",
			"join-study-button",
			"-by-previous-",
			"data:image/png;base64",
			"By Onset Delay",
		], WpPost::FIELD_POST_CONTENT, true);
	}
	public function setGlobalVariableRelationshipId(int $aggregateCorrelationId): void{
		$this->setAttribute(UserVariableRelationship::FIELD_AGGREGATE_CORRELATION_ID, $aggregateCorrelationId);
	}
	public function getAggregatedAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_AGGREGATED_AT] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->aggregatedAt;
		}
	}
	public function setAggregatedAt(string $aggregatedAt): void{
		$this->setAttribute(UserVariableRelationship::FIELD_AGGREGATED_AT, $aggregatedAt);
	}
	public function getAnalysisRequestedAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_ANALYSIS_REQUESTED_AT] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->analysisRequestedAt;
		}
	}
	public function setAnalysisRequestedAt(string $analysisRequestedAt): void{
		$this->setAttribute(UserVariableRelationship::FIELD_ANALYSIS_REQUESTED_AT, $analysisRequestedAt);
	}
	public function getAnalysisStartedAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_ANALYSIS_STARTED_AT] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->analysisStartedAt;
		}
	}
	public function setCauseChanges(int $causeChanges): void{
		$this->setAttribute(UserVariableRelationship::FIELD_CAUSE_CHANGES, $causeChanges);
	}
	public function getCauseFillingValue(): ?float{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_CAUSE_FILLING_VALUE] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->causeFillingValue;
		}
	}
	public function setCauseFillingValue(float $causeFillingValue): void{
		$this->setAttribute(UserVariableRelationship::FIELD_CAUSE_FILLING_VALUE, $causeFillingValue);
	}
	public function getCauseNumberOfProcessedDailyMeasurements(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_CAUSE_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->causeNumberOfProcessedDailyMeasurements;
		}
	}
	public function getCauseNumberOfRawMeasurements(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_CAUSE_NUMBER_OF_RAW_MEASUREMENTS] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->causeNumberOfRawMeasurements;
		}
	}
	public function setCauseNumberOfRawMeasurements(int $causeNumberOfRawMeasurements): void{
		$this->setAttribute(UserVariableRelationship::FIELD_CAUSE_NUMBER_OF_RAW_MEASUREMENTS, $causeNumberOfRawMeasurements);
	}
	public function getUnitId(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_CAUSE_UNIT_ID] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->causeUnitId;
		}
	}
	public function setUnitId(int $causeUnitId): void{
		$this->setAttribute(UserVariableRelationship::FIELD_CAUSE_UNIT_ID, $causeUnitId);
	}
	public function setCauseUserVariableId(int $causeUserVariableId): void{
		$this->setAttribute(UserVariableRelationship::FIELD_CAUSE_USER_VARIABLE_ID, $causeUserVariableId);
	}
	public function setCauseVariableCategoryId(int $causeVariableCategoryId): void{
		$this->setAttribute(UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID, $causeVariableCategoryId);
	}
	public function setCauseVariableId(int $causeVariableId): void{
		$this->setAttribute(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID, $causeVariableId);
	}
	public function setConfidenceInterval(float $confidenceInterval): void{
		$this->setAttribute(UserVariableRelationship::FIELD_CONFIDENCE_INTERVAL, $confidenceInterval);
	}
	public function getCorrelationsOverDelays(): ?array{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->getCorrelationsOverDelaysAttribute();
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->correlationsOverDelays;
		}
	}
	public function setCorrelationsOverDelays(array $correlationsOverDelays): void{
		if(count($correlationsOverDelays) < 2){
			le("Not going to " . __FUNCTION__ . " because there aren't enough user_variable_relationships.  We should have thrown " .
				"NotEnoughDataException");
		}
		$this->setAttribute(UserVariableRelationship::FIELD_CORRELATIONS_OVER_DELAYS, $correlationsOverDelays);
	}
	public function getCorrelationsOverDurations(): ?array{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->getCorrelationsOverDurationsAttribute();
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->correlationsOverDurations;
		}
	}
	public function setCorrelationsOverDurations(array $correlationsOverDurations): void{
		if(count($correlationsOverDurations) < 2){
			le("Not going to " . __FUNCTION__ . " because there aren't enough user_variable_relationships.  We should have thrown " .
				"NotEnoughDataException");
		}
		$this->setAttribute(UserVariableRelationship::FIELD_CORRELATIONS_OVER_DURATIONS, $correlationsOverDurations);
	}
	public function setCriticalTValue(float $criticalTValue): void{
		$this->setAttribute(UserVariableRelationship::FIELD_CRITICAL_T_VALUE, $criticalTValue);
	}
	public function getDeletedAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_DELETED_AT] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->deletedAt;
		}
	}
	public function setDeletedAt(string $deletedAt): void{
		$this->setAttribute(UserVariableRelationship::FIELD_DELETED_AT, $deletedAt);
	}
	public function getDeletionReason(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_DELETION_REASON] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->deletionReason;
		}
	}
	public function setDeletionReason(string $deletionReason): void{
		$this->setAttribute(UserVariableRelationship::FIELD_DELETION_REASON, $deletionReason);
	}
	public function setDurationOfAction(int $durationOfAction): void{
		$this->setAttribute(UserVariableRelationship::FIELD_DURATION_OF_ACTION, $durationOfAction);
	}
	public function getEarliestMeasurementStartAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_EARLIEST_MEASUREMENT_START_AT] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->earliestMeasurementStartAt;
		}
	}
	public function setEarliestMeasurementStartAt(string $earliestMeasurementStartAt): void{
		$this->setAttribute(UserVariableRelationship::FIELD_EARLIEST_MEASUREMENT_START_AT, $earliestMeasurementStartAt);
	}
	public function setEffectBaselineAverage(float $effectBaselineAverage): void{
		$this->setAttribute(UserVariableRelationship::FIELD_EFFECT_BASELINE_AVERAGE, $effectBaselineAverage);
	}
	public function setEffectBaselineStandardDeviation(float $effectBaselineStandardDeviation): void{
		$this->setAttribute(UserVariableRelationship::FIELD_EFFECT_BASELINE_STANDARD_DEVIATION, $effectBaselineStandardDeviation);
	}
	public function setEffectChanges(int $effectChanges): void{
		$this->setAttribute(UserVariableRelationship::FIELD_EFFECT_CHANGES, $effectChanges);
	}
	public function setEffectFillingValue(float $effectFillingValue): void{
		$this->setAttribute(UserVariableRelationship::FIELD_EFFECT_FILLING_VALUE, $effectFillingValue);
	}
	public function getEffectNumberOfRawMeasurements(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_EFFECT_NUMBER_OF_RAW_MEASUREMENTS] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->effectNumberOfRawMeasurements;
		}
	}
	public function setEffectNumberOfRawMeasurements(int $effectNumberOfRawMeasurements): void{
		$this->setAttribute(UserVariableRelationship::FIELD_EFFECT_NUMBER_OF_RAW_MEASUREMENTS, $effectNumberOfRawMeasurements);
	}
	public function setEffectUserVariableId(int $effectUserVariableId): void{
		$this->setAttribute(UserVariableRelationship::FIELD_EFFECT_USER_VARIABLE_ID, $effectUserVariableId);
	}
	public function setEffectVariableCategoryId(int $effectVariableCategoryId): void{
		$this->setAttribute(UserVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID, $effectVariableCategoryId);
	}
	public function setEffectVariableId(int $effectVariableId): void{
		$this->setAttribute(UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID, $effectVariableId);
	}
	public function setExperimentEndAt(string $experimentEndAt): void{
		$this->setAttribute(UserVariableRelationship::FIELD_EXPERIMENT_END_AT, $experimentEndAt);
	}
	public function setExperimentStartAt(string $experimentStartAt): void{
		$this->setAttribute(UserVariableRelationship::FIELD_EXPERIMENT_START_AT, $experimentStartAt);
	}
	public function getInterestingVariableCategoryPair(): ?bool{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->interestingVariableCategoryPair;
		}
	}
	public function setInterestingVariableCategoryPair(bool $interestingVariableCategoryPair): void{
		$this->setAttribute(UserVariableRelationship::FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR, $interestingVariableCategoryPair);
	}
	public function getInternalErrorMessage(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_INTERNAL_ERROR_MESSAGE] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->internalErrorMessage;
		}
	}
	public function getLatestMeasurementStartAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_LATEST_MEASUREMENT_START_AT] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->latestMeasurementStartAt;
		}
	}
	public function setLatestMeasurementStartAt(string $latestMeasurementStartAt): void{
		$this->setAttribute(UserVariableRelationship::FIELD_LATEST_MEASUREMENT_START_AT, $latestMeasurementStartAt);
	}
	public function setNewestDataAt(string $newestDataAt): void{
		$this->setAttribute(UserVariableRelationship::FIELD_NEWEST_DATA_AT, $newestDataAt);
	}
	public function getNumberOfDays(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_NUMBER_OF_DAYS] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->numberOfDays;
		}
	}
	public function setNumberOfDays(int $numberOfDays): void{
		$this->setAttribute(UserVariableRelationship::FIELD_NUMBER_OF_DAYS, $numberOfDays);
	}
	public function setOnsetDelay(int $onsetDelay): void{
		$this->setAttribute(UserVariableRelationship::FIELD_ONSET_DELAY, $onsetDelay);
	}
	public function setPredictsHighEffectChange(int $predictsHighEffectChange): void{
		$this->setAttribute(UserVariableRelationship::FIELD_PREDICTS_HIGH_EFFECT_CHANGE, $predictsHighEffectChange);
	}
	public function setQmScore(float $qmScore): void{
		$this->setAttribute(UserVariableRelationship::FIELD_QM_SCORE, $qmScore);
	}
	public function getReasonForAnalysis(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_REASON_FOR_ANALYSIS] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->reasonForAnalysis;
		}
	}
	public function getRecordSizeInKb(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_RECORD_SIZE_IN_KB] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->recordSizeInKb;
		}
	}
	public function setStatisticalSignificance(float $statisticalSignificance): void{
		$this->setAttribute(UserVariableRelationship::FIELD_STATISTICAL_SIGNIFICANCE, $statisticalSignificance);
	}
	public function getUserErrorMessage(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_USER_ERROR_MESSAGE] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->userErrorMessage;
		}
	}
	public function getWpPostId(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariableRelationship::FIELD_WP_POST_ID] ?? null;
		} else{
			/** @var QMUserVariableRelationship $this */
			return $this->wpPostId;
		}
	}
	public function setWpPostId(int $wpPostId): void{
		$this->setAttribute(UserVariableRelationship::FIELD_WP_POST_ID, $wpPostId);
	}
	public function getShowContentView(array $params = []): View{
		return $this->findInMemoryOrNewQMStudy()->getShowContentView($params);
	}
	public function setGlobalVariableRelationship(GlobalVariableRelationship $l){
		$this->setRelation('global_variable_relationship', $l);
	}
	/**
	 * @param string $reason
	 * @return void
	 * @throws TooSlowToAnalyzeException
	 */
	public function analyzeCauseAndEffectVariableIfNecessary(string $reason): void{
		$cause = $this->getCauseVariable();
		$cause->analyzeIfNecessary($reason);
		$effect = $this->getEffectVariable();
		$effect->analyzeIfNecessary($reason);
	}
	/**
	 * @return float
	 */
	protected function calculateGroupedCauseValueClosestToValuePredictingLowOutcome(): ?float{
		return CorrelationGroupedCauseValueClosestToValuePredictingLowOutcomeProperty::calculate($this);
	}
	/**
	 * @return RelationshipButton[]
	 * Override this in models to only show interesting relationships
	 * or set $interesting in RelationshipButton model
	 */
	public function getInterestingRelationshipButtons(): array{
		$all = [];
		$all[] = new CorrelationCauseUserVariableButton($this);
		$all[] = new CorrelationEffectUserVariableButton($this);
		try {
			$all[] = new CorrelationGlobalVariableRelationshipButton($this, $this->global_variable_relationship());
		} catch (NoIdException $e) {
			le($e);
		}
		return $all;
	}
	public function getAvatar(): string{
		return UserVariableRelationship::DEFAULT_IMAGE;
	}
	public static function getS3Bucket(): string{ return S3Private::getBucketName(); }
	public function getSortingScore(): float{
		return $this->getQmScore();
	}
	protected static function getIndexPageView(): View{
		return view('studies-index', self::getIndexViewParams());
	}
	public static function getIndexContentView(): View{
		return view('chip-search', self::getIndexViewParams());
	}
	/**
	 * @return UserVariableRelationship[]|\Illuminate\Database\Eloquent\Collection
	 */
	public static function getIndexModels(): Collection{
		return UserVariableRelationship::withUpVotes()->where(UserVariableRelationship::FIELD_IS_PUBLIC, true)
		                               ->orderByDesc(UserVariableRelationship::FIELD_QM_SCORE)->get();
	}
	/**
	 * @return array
	 */
	protected static function getIndexViewParams(): array{
		return [
			'heading' => "Studies",
			'searchId' => 'studies',
			'buttons' => static::getIndexModels(),
		];
	}
	/**
	 * @return float
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 */
	public function getValuePredictingHighOutcomeOverDuration(): float{
		$val = $this->getAttribute(UserVariableRelationship::FIELD_VALUE_PREDICTING_HIGH_OUTCOME);
		if($val == null){
			$val = CorrelationValuePredictingHighOutcomeProperty::calculate($this);
		}
		return $val;
	}
	/**
	 * @return CorrelationChartGroup
	 */
	public function getChartGroup(): ChartGroup{
		$c = $this->getDBModel();
		return $c->getOrSetCharts();
	}
	public function getQMUserVariableRelationship(): QMUserVariableRelationship{ return $this->getDBModel(); }
	public function getCauseUrl(): string{
		$url = UserVariable::generateShowUrl($this->getCauseUserVariableId());
		try {
			self::validateVariableLink($url);
		} catch (\Throwable $e) {
			$url = UserVariable::generateShowUrl($this->getCauseUserVariableId());
			$url = UrlHelper::removeDBParamFromUrl($url);
			self::validateVariableLink($url);
		}
		return $url;
	}
	public static function validateVariableLink(string $str){
		if(stripos($str, "?cause") !== false){
			le('stripos($str, "?cause") !== false');
		}
		if(stripos($str, Env::DB_HOST) !== false){
			le('stripos($str, Env::DB_HOST) !== false');
		}
	}
	public function getSideMenus(): array{ return $this->getStudySideMenus(); }
	public function getActionsMenu(): ?QMMenu{ return $this->getStudyActionsMenu(); }
	/**
	 * @return string $studyLinkStatic
	 */
	public function getStudyLinkStatic(array $params = []): string{
		return StudyLinks::generateStudyLinkStatic($this->getStudyId(), $params);
	}
	public function getUrl(array $params = []): string{
		return $this->getStudyLinkStatic($params);
	}
	/**
	 * @return int
	 */
	public function getPredictsHighEffectChange(): ?float{
		return $this->predicts_high_effect_change;
	}
	/**
	 * @return self
	 */
	public function getHasCorrelationCoefficient(): UserVariableRelationship{
		return $this;
	}
	/**
	 * @return bool
	 * @throws UnauthorizedException
	 */
	private function userIdIsLoggedInUser(): bool{
		if(!QMAuth::getQMUser()){
			return false;
		}
		if(QMAuth::getQMUser()->id === $this->getUserId()){
			return true;
		}
		return false;
	}
	/**
	 * @return bool
	 */
	public function getJoined(): bool{
		if($this->userIdIsLoggedInUser()){
			return true;
		}
		return false;
	}
	public function getParentCategoryName(): ?string{
		return static::CLASS_CATEGORY;
	}
	public function getPairOfAverages(): PairOfAverages{
		return new PairOfAverages($this);
	}
	/**
	 * @param mixed $publishedAt
	 */
	public function setPublishedAtAttribute(?string $publishedAt){
		$this->published_at = $publishedAt;
	}
	public function setEffectVariable(Variable $v){
		$this->setRelation('effect_variable', $v);
	}
	public function setCauseVariable(Variable $v){
		$this->setRelation('cause_variable', $v);
	}
	public function analyzeFullyIfNecessaryAndSave(string $reason): void{
		$dbm = $this->getDBModel();
		$dbm->analyzeFullyIfNecessary($reason);
		$dbm->save();
	}
	public function analyzeFully(string $reason): void {
		$dbm = $this->getDBModel();
		$this->analyzeCauseAndEffectVariableIfNecessary($reason);
		$dbm->analyzeFully($reason);
		$this->analyzeCauseAndEffectVariableIfNecessary($reason);
	}
	public function save(array $options = []){
		if($this->global_variable_relationship_id){
			$ac = GlobalVariableRelationship::findInMemoryOrDB($this->global_variable_relationship_id);
			if(!$ac){
				le("Global variable relationship not found: " . $this->global_variable_relationship_id);
			}
		}
		return parent::save($options); 
	}
}
