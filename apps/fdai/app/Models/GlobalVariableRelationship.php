<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Exceptions\AccessTokenExpiredException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Buttons\RelationshipButtons\GlobalVariableRelationship\GlobalVariableRelationshipCauseVariableButton;
use App\Buttons\RelationshipButtons\GlobalVariableRelationship\GlobalVariableRelationshipCorrelationsButton;
use App\Buttons\RelationshipButtons\GlobalVariableRelationship\GlobalVariableRelationshipEffectVariableButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Cards\QMCard;
use App\Cards\StudyCard;
use App\Charts\GlobalVariableRelationshipCharts\GlobalVariableRelationshipChartGroup;
use App\Charts\ChartGroup;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\VariableRelationships\QMVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\CommonVariableNotFoundException;
use App\Exceptions\DuplicateFailedAnalysisException;
use App\Exceptions\InsufficientVarianceException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\StupidVariableNameException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Logging\QMLog;
use App\Menus\QMMenu;
use App\Models\Base\BaseGlobalVariableRelationship;
use App\Astral\Actions\AnalyzeAction;
use App\Astral\Actions\FavoriteAction;
use App\Astral\Actions\LikeAction;
use App\Astral\Actions\PHPUnitAction;
use App\Astral\Lenses\GlobalVariableRelationshipsWithNoUserVariableRelationshipsLens;
use App\Astral\Lenses\GlobalVariableRelationshipsWithNullChangeLens;
use App\Astral\Lenses\FavoritesLens;
use App\Astral\Lenses\LikesLens;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipDurationOfActionProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipEffectFollowUpPercentChangeFromBaselineProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipGroupedCauseValueClosestToValuePredictingLowOutcomeProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipNumberOfUsersProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipOnsetDelayProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipPredictsHighEffectChangeProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipPredictsLowEffectChangeProperty;
use App\Properties\Base\BaseEffectFollowUpPercentChangeFromBaselineProperty;
use App\Properties\Study\StudyTypeProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\Variable\VariableIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Slim\Model\QMUnit;
use App\Storage\S3\S3Public;
use App\Studies\PairOfAverages;
use App\Studies\QMPopulationStudy;
use App\Studies\StudyHtml;
use App\Studies\StudyLinks;
use App\Tables\QMTable;
use App\Traits\AnalyzableTrait;
use App\Traits\HasButton;
use App\Traits\HasCharts;
use App\Traits\HasCorrelationCoefficient;
use App\Traits\HasDBModel;
use App\Traits\HasErrors;
use App\Traits\HasFiles;
use App\Traits\HasMany\HasManyCorrelations;
use App\Traits\HasModel\HasGlobalVariableRelationship;
use App\Traits\HasProperty\HasOnsetAndDuration;
use App\Traits\HasVotes;
use App\Traits\PostableTrait;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\CssHelper;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\IonIcon;
use App\UI\QMColor;
use App\Utils\AppMode;
use App\Utils\Stats;
use App\Variables\QMVariable;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Actions\ActionEvent;
use Overtrue\LaravelFavorite\Traits\Favoriteable;
use Overtrue\LaravelLike\Like;
use Overtrue\LaravelLike\Traits\Likeable;
use Spatie\MediaLibrary\HasMedia;
use Tests\TestGenerators\StagingJobTestFile;
/**
 * App\Models\AggregatedCorrelation
 * @property integer $id
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
 * @property integer $number_of_users Number of Users by which correlation is aggregated
 * @property integer $number_of_correlations Number of VariableRelationships by which correlation is aggregated
 * @property float $statistical_significance A function of the effect size and sample size
 * @property integer $cause_unit_id Unit ID of the predictor variable
 * @property integer $cause_changes Cause changes
 * @property integer $effect_changes Effect changes
 * @property float $aggregate_qm_score QM Score
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $status Status
 * @property float $reverse_pearson_correlation_coefficient UserVariableRelationship when cause and effect are reversed. For any
 *     causal relationship, the forward correlation should exceed the reverse correlation
 * @property float $predictive_pearson_correlation_coefficient Predictive Pearson UserVariableRelationship Coefficient
 * @property string $data_source Source of data for this correlation
 * @method static \Illuminate\Database\Query\Builder|GlobalVariableRelationship whereId($value)
 * @method static \Illuminate\Database\Query\Builder|GlobalVariableRelationship whereCorrelation($value)
 * @method static \Illuminate\Database\Query\Builder|GlobalVariableRelationship whereCauseId($value)
 * @method static \Illuminate\Database\Query\Builder|GlobalVariableRelationship whereEffectId($value)
 * @method static \Illuminate\Database\Query\Builder|GlobalVariableRelationship whereOnsetDelay($value)
 * @method static \Illuminate\Database\Query\Builder|GlobalVariableRelationship whereDurationOfAction($value)
 * @method static \Illuminate\Database\Query\Builder|GlobalVariableRelationship whereNumberOfPairs($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AggregatedCorrelation
 *     whereValuePredictingHighOutcome($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AggregatedCorrelation
 *     whereValuePredictingLowOutcome($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AggregatedCorrelation
 *     whereOptimalPearsonProduct($value)
 * @method static \Illuminate\Database\Query\Builder|GlobalVariableRelationship whereVote($value)
 * @method static \Illuminate\Database\Query\Builder|GlobalVariableRelationship whereNumberOfUsers($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AggregatedCorrelation
 *     whereNumberOfCorrelations($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AggregatedCorrelation
 *     whereStatisticalSignificance($value)
 * @method static \Illuminate\Database\Query\Builder|GlobalVariableRelationship whereCauseUnit($value)
 * @method static \Illuminate\Database\Query\Builder|GlobalVariableRelationship whereCauseUnitId($value)
 * @method static \Illuminate\Database\Query\Builder|GlobalVariableRelationship whereCauseChanges($value)
 * @method static \Illuminate\Database\Query\Builder|GlobalVariableRelationship whereEffectChanges($value)
 * @method static \Illuminate\Database\Query\Builder|GlobalVariableRelationship whereAggregateQmScore($value)
 * @method static \Illuminate\Database\Query\Builder|GlobalVariableRelationship whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|GlobalVariableRelationship whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|GlobalVariableRelationship whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AggregatedCorrelation
 *     whereLastSuccessfulUpdateTime($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AggregatedCorrelation
 *     whereReversePearsonCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AggregatedCorrelation
 *     wherePredictivePearsonCorrelationCoefficient($value)
 * @property-read Variable $cause
 * @property-read Variable $effect
 * @method static \Illuminate\Database\Query\Builder|GlobalVariableRelationship whereDataSource($value)
 * @property float $forward_pearson_correlation_coefficient Pearson correlation coefficient between cause and effect
 *     measurements
 * @property float|null $average_vote Vote
 * @property int|null $predicts_high_effect_change The percent change in the outcome typically seen when the predictor
 *     value is closer to the predictsHighEffect value.
 * @property int|null $predicts_low_effect_change The percent change in the outcome from average typically seen when
 *     the predictor value is closer to the predictsHighEffect value.
 * @property float|null $p_value The measure of statistical significance. A value less than 0.05 means that a
 *     correlation is statistically significant or consistent enough that it is unlikely to be a coincidence.
 * @property float|null $t_value Function of correlation and number of samples.
 * @property float|null $critical_t_value Value of t from lookup table which t must exceed for significance.
 * @property float|null $confidence_interval A margin of error around the effect size.  Wider confidence intervals
 *     reflect greater uncertainty about the true value of the correlation.
 * @property string|null $deleted_at
 * @property float|null $average_effect
 * @property float|null $average_effect_following_high_cause
 * @property float|null $average_effect_following_low_cause
 * @property float|null $average_daily_low_cause
 * @property float|null $average_daily_high_cause
 * @property float|null $population_trait_pearson_correlation_coefficient
 * @property float|null $grouped_cause_value_closest_to_value_predicting_low_outcome
 * @property float|null $grouped_cause_value_closest_to_value_predicting_high_outcome
 * @property string|null $client_id
 * @property string|null $published_at
 * @property int|null $wp_post_id
 * @method static Builder|GlobalVariableRelationship newModelQuery()
 * @method static Builder|GlobalVariableRelationship newQuery()
 * @method static Builder|GlobalVariableRelationship query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AggregatedCorrelation
 *     whereAverageDailyHighCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AggregatedCorrelation
 *     whereAverageDailyLowCause($value)
 * @method static Builder|GlobalVariableRelationship whereAverageEffect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AggregatedCorrelation
 *     whereAverageEffectFollowingHighCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AggregatedCorrelation
 *     whereAverageEffectFollowingLowCause($value)
 * @method static Builder|GlobalVariableRelationship whereAverageVote($value)
 * @method static Builder|GlobalVariableRelationship whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AggregatedCorrelation
 *     whereConfidenceInterval($value)
 * @method static Builder|GlobalVariableRelationship whereCriticalTValue($value)
 * @method static Builder|GlobalVariableRelationship whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AggregatedCorrelation
 *     whereForwardPearsonCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AggregatedCorrelation
 *     whereGroupedCauseValueClosestToValuePredictingHighOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AggregatedCorrelation
 *     whereGroupedCauseValueClosestToValuePredictingLowOutcome($value)
 * @method static Builder|GlobalVariableRelationship wherePValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AggregatedCorrelation
 *     wherePopulationTraitPearsonCorrelationCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AggregatedCorrelation
 *     wherePredictsHighEffectChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AggregatedCorrelation
 *     wherePredictsLowEffectChange($value)
 * @method static Builder|GlobalVariableRelationship wherePublishedAt($value)
 * @method static Builder|GlobalVariableRelationship whereTValue($value)
 * @method static Builder|GlobalVariableRelationship whereWpPostId($value)
 * @mixin Eloquent
 * @property string|null $data_source_name
 * @property int|null $cause_variable_category_id
 * @property int|null $effect_variable_category_id
 * @property int|null $interesting_variable_category_pair
 * @method static Builder|GlobalVariableRelationship whereCauseVariableCategoryId($value)
 * @method static Builder|GlobalVariableRelationship whereDataSourceName($value)
 * @method static Builder|GlobalVariableRelationship whereEffectVariableCategoryId($value)
 * @method static Builder|GlobalVariableRelationship whereInterestingVariableCategoryPair($value)
 * @property string|null $newest_data_at
 * @property string|null $analysis_ended_at
 * @property string|null $analysis_requested_at
 * @property string|null $reason_for_analysis
 * @property string|null $analysis_started_at
 * @property string|null $user_error_message
 * @property string|null $internal_error_message
 * @property-read Variable $variable
 * @method static Builder|GlobalVariableRelationship whereAnalysisEndedAt($value)
 * @method static Builder|GlobalVariableRelationship whereAnalysisRequestedAt($value)
 * @method static Builder|GlobalVariableRelationship whereAnalysisStartedAt($value)
 * @method static Builder|GlobalVariableRelationship whereInternalErrorMessage($value)
 * @method static Builder|GlobalVariableRelationship whereNewestDataAt($value)
 * @method static Builder|GlobalVariableRelationship whereReasonForAnalysis($value)
 * @method static Builder|GlobalVariableRelationship whereUserErrorMessage($value)
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
 * @property-read OAClient|null $oa_client
 * @method static Builder|GlobalVariableRelationship whereCauseBaselineAveragePerDay($value)
 * @method static Builder|GlobalVariableRelationship whereCauseBaselineAveragePerDurationOfAction($value)
 * @method static Builder|GlobalVariableRelationship whereCauseTreatmentAveragePerDay($value)
 * @method static Builder|GlobalVariableRelationship whereCauseTreatmentAveragePerDurationOfAction($value)
 * @method static Builder|GlobalVariableRelationship whereCauseVariableId($value)
 * @method static Builder|GlobalVariableRelationship whereEffectBaselineAverage($value)
 * @method static Builder|GlobalVariableRelationship whereEffectBaselineRelativeStandardDeviation($value)
 * @method static Builder|GlobalVariableRelationship whereEffectBaselineStandardDeviation($value)
 * @method static Builder|GlobalVariableRelationship whereEffectFollowUpAverage($value)
 * @method static Builder|GlobalVariableRelationship whereEffectFollowUpPercentChangeFromBaseline($value)
 * @method static Builder|GlobalVariableRelationship whereEffectVariableId($value)
 * @method static Builder|GlobalVariableRelationship whereZScore($value)
 * @property-read QMUnit|null $unit
 * @property-read VariableCategory|null $variable_category
 * @property-read WpPost $wp_post
 * @property-read VariableCategory|null $cause_variable_category
 * @property-read VariableCategory|null $effect_variable_category
 * @property array|null $charts
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|GlobalVariableRelationship whereCharts($value)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read Unit|null $cause_unit
 * @property-read Variable $cause_variable
 * @property-read Collection|CorrelationCausalityVote[] $correlation_causality_votes
 * @property-read int|null $correlation_causality_votes_count
 * @property-read Collection|CorrelationUsefulnessVote[] $correlation_usefulness_votes
 * @property-read int|null $correlation_usefulness_votes_count
 * @property-read Collection|UserVariableRelationship[] $user_variable_relationships
 * @property-read int|null $correlations_count
 * @property-read Variable $effect_variable
 * @property-read Collection|Variable[] $variables_where_best_global_variable_relationship
 * @property-read int|null $variables_where_best_global_variable_relationship_count
 * @property int|null $number_of_variables_where_best_global_variable_relationship Number of Variables for this Best Aggregate
 *     UserVariableRelationship.
 *                     [Formula: update global_variable_relationships
 *                         left join (
 *                             select count(id) as total, best_global_variable_relationship_id
 *                             from variables
 *                             group by best_global_variable_relationship_id
 *                         )
 *                         as grouped on global_variable_relationships.id = grouped.best_global_variable_relationship_id
 *                     set global_variable_relationships.number_of_variables_where_best_global_variable_relationship =
 *     count(grouped.total)]
 * @method static Builder|GlobalVariableRelationship whereNumberOfVariablesWhereBestGlobalVariableRelationship($value)
 * @property-read Collection|ActionEvent[] $actions
 * @property-read int|null $actions_count
 * @property string|null $deletion_reason The reason the variable was deleted.
 * @property int|null $record_size_in_kb
 * @method static Builder|GlobalVariableRelationship whereDeletionReason($value)
 * @method static Builder|GlobalVariableRelationship whereRecordSizeInKb($value)
 * @property-read int|null $votes_count
 * @property mixed $raw
 * @property-read Collection|Vote[] $votes
 * @property bool $is_public
 * @property bool|null $boring The relationship is boring if it is obvious, the predictor is not controllable, or the
 *     outcome is not a goal, the relationship could not be causal, or the confidence is low.
 * @property bool|null $outcome_is_a_goal The effect of a food on the severity of a symptom is useful because you can
 *     control the predictor directly. However, the effect of a symptom on the foods you eat is not very useful.  The
 *     foods you eat are not generally an objective end in themselves.
 * @property bool|null $predictor_is_controllable The effect of a food on the severity of a symptom is useful because
 *     you can control the predictor directly. However, the effect of a symptom on the foods you eat is not very
 *     useful.  Symptom severity is not directly controllable.
 * @property bool|null $plausibly_causal The effect of aspirin on headaches is plausibly causal. The effect of aspirin
 *     on precipitation does not have a plausible causal relationship.
 * @property bool|null $obvious The effect of aspirin on headaches is obvious. The effect of aspirin on productivity is
 *     not obvious.
 * @property int $number_of_up_votes Number of people who feel this relationship is plausible and useful.
 * @property int $number_of_down_votes Number of people who feel this relationship is implausible or not useful.
 * @property string $strength_level Strength level describes magnitude of the change in outcome observed following
 *     changes in the predictor.
 * @property string $confidence_level Describes the confidence that the strength level will remain consist in the
 *     future.  The more data there is, the lesser the chance that the findings are a spurious correlation.
 * @property string $relationship If higher predictor values generally precede HIGHER outcome values, the relationship
 *     is considered POSITIVE.  If higher predictor values generally precede LOWER outcome values, the relationship is
 *     considered NEGATIVE.
 * @method static Builder|GlobalVariableRelationship whereBoring($value)
 * @method static Builder|GlobalVariableRelationship whereConfidenceLevel($value)
 * @method static Builder|GlobalVariableRelationship whereIsPublic($value)
 * @method static Builder|GlobalVariableRelationship whereNumberOfDownVotes($value)
 * @method static Builder|GlobalVariableRelationship whereNumberOfUpVotes($value)
 * @method static Builder|GlobalVariableRelationship whereObvious($value)
 * @method static Builder|GlobalVariableRelationship whereOutcomeIsAGoal($value)
 * @method static Builder|GlobalVariableRelationship wherePlausiblyCausal($value)
 * @method static Builder|GlobalVariableRelationship wherePredictorIsControllable($value)
 * @method static Builder|GlobalVariableRelationship whereRelationship($value)
 * @method static Builder|GlobalVariableRelationship whereStrengthLevel($value)
 * @property string|null $slug The slug is the part of a URL that identifies a page in human-readable keywords.
 * @property-read OAClient|null $client
 * @property-read Collection|User[] $favoriters
 * @property-read int|null $favoriters_count
 * @property-read Collection|\Overtrue\LaravelFavorite\Favorite[] $favorites
 * @property-read int|null $favorites_count
 * @property-read Collection|User[] $likers
 * @property-read int|null $likers_count
 * @property-read Collection|Like[] $likes
 * @property-read int|null $likes_count
 * @method static Builder|GlobalVariableRelationship whereSlug($value)
 */
class GlobalVariableRelationship extends BaseGlobalVariableRelationship implements HasMedia {
    use HasFactory;

	use AnalyzableTrait, HasDBModel, HasErrors, HasVotes;
	use HasButton, HasCorrelationCoefficient, HasManyCorrelations, HasOnsetAndDuration, HasGlobalVariableRelationship,
		HasCharts, HasFiles;
	use PostableTrait;
	use Favoriteable;
	use Likeable;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = 'id';
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [//'id',
	];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'cause_variable' => ['name'],
		'effect_variable' => ['name'],
	];
	public static $group = GlobalVariableRelationship::CLASS_CATEGORY;
	public const ANALYZABLE = true;
	public const CLASS_DESCRIPTION = "Examination of the likely effects of a predictor variable on an outcome variable on average for the entire population";
	public const CLASS_CATEGORY = Study::CLASS_CATEGORY;
	public const COLOR = QMColor::HEX_FUCHSIA;
	public const DEFAULT_IMAGE = ImageUrls::SCIENCE_FLASKS;
	public const DEFAULT_LIMIT = 10;
	public const DEFAULT_ORDER_DIRECTION = 'desc';
	public const DEFAULT_SEARCH_FIELD = 'effect_variable.' . Variable::FIELD_NAME;
	public const DEFAULT_ORDERINGS = [self::FIELD_AGGREGATE_QM_SCORE => self::ORDER_DIRECTION_DESC];
	public const FONT_AWESOME = FontAwesome::VIALS_SOLID;
	public static function getSlimClass(): string{ return QMGlobalVariableRelationship::class; }
	public static function getUniqueIndexColumns(): array{
		return [
			self::FIELD_CAUSE_VARIABLE_ID,
			self::FIELD_EFFECT_VARIABLE_ID,
		];
	}
	protected $with = [
		//'cause_variable:'.Variable::IMPORTANT_FIELDS,
		//'effect_variable:'.Variable::IMPORTANT_FIELDS,
	];
	protected array $rules = [
		self::FIELD_AGGREGATE_QM_SCORE => 'nullable|numeric',
		self::FIELD_ANALYSIS_ENDED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_REQUESTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_STARTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_AVERAGE_DAILY_HIGH_CAUSE => 'nullable|numeric',
		self::FIELD_AVERAGE_DAILY_LOW_CAUSE => 'nullable|numeric',
		self::FIELD_AVERAGE_EFFECT => 'nullable|numeric',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE => 'nullable|numeric',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE => 'nullable|numeric',
		self::FIELD_AVERAGE_VOTE => 'nullable|numeric',
		self::FIELD_CAUSE_BASELINE_AVERAGE_PER_DAY => 'required|numeric',
		self::FIELD_CAUSE_BASELINE_AVERAGE_PER_DURATION_OF_ACTION => 'required|numeric',
		self::FIELD_CAUSE_CHANGES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_CAUSE_TREATMENT_AVERAGE_PER_DAY => 'required|numeric',
		self::FIELD_CAUSE_TREATMENT_AVERAGE_PER_DURATION_OF_ACTION => 'required|numeric',
		self::FIELD_CAUSE_UNIT_ID => 'nullable|integer|min:1|max:65535',
		self::FIELD_CAUSE_VARIABLE_CATEGORY_ID => 'required|integer|min:1|max:300',
		self::FIELD_CAUSE_VARIABLE_ID => 'nullable|integer|min:1|max:2147483647',
		// Validation refuses to check as string // self::FIELD_CHARTS => 'nullable|max:204800',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_CONFIDENCE_INTERVAL => 'nullable|numeric',
		self::FIELD_CRITICAL_T_VALUE => 'nullable|numeric',
		self::FIELD_DATA_SOURCE_NAME => 'nullable|max:255',
		self::FIELD_DURATION_OF_ACTION => 'nullable|integer|min:60|max:8640000',
		self::FIELD_EFFECT_BASELINE_AVERAGE => 'required|numeric',
		self::FIELD_EFFECT_BASELINE_RELATIVE_STANDARD_DEVIATION => 'required|numeric',
		self::FIELD_EFFECT_BASELINE_STANDARD_DEVIATION => 'required|numeric',
		self::FIELD_EFFECT_CHANGES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_EFFECT_FOLLOW_UP_AVERAGE => 'required|numeric',
		self::FIELD_EFFECT_FOLLOW_UP_PERCENT_CHANGE_FROM_BASELINE => 'required|numeric',
		self::FIELD_EFFECT_VARIABLE_CATEGORY_ID => 'required|integer|min:1|max:300',
		self::FIELD_EFFECT_VARIABLE_ID => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT => 'required|numeric',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME => 'nullable|numeric',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME => 'nullable|numeric',
		self::FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR => 'nullable|boolean',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'nullable|max:1000',
		self::FIELD_NEWEST_DATA_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_NUMBER_OF_CORRELATIONS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_PAIRS => 'nullable|numeric|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_USERS => 'required|integer|min:0|max:2147483647',
		self::FIELD_ONSET_DELAY => 'nullable|integer|min:0|max:8640000',
		self::FIELD_OPTIMAL_PEARSON_PRODUCT => 'nullable|numeric',
		self::FIELD_P_VALUE => 'nullable|numeric',
		self::FIELD_POPULATION_TRAIT_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_PREDICTIVE_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_PREDICTS_HIGH_EFFECT_CHANGE => 'nullable|numeric|min:-2147483648|max:2147483647',
		self::FIELD_PREDICTS_LOW_EFFECT_CHANGE => 'nullable|numeric|min:-2147483648|max:2147483647',
		self::FIELD_PUBLISHED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_REASON_FOR_ANALYSIS => 'nullable|max:255',
		self::FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_STATISTICAL_SIGNIFICANCE => 'nullable|numeric',
		self::FIELD_STATUS => 'nullable|max:25',
		self::FIELD_T_VALUE => 'nullable|numeric',
		self::FIELD_USER_ERROR_MESSAGE => 'nullable|max:500',
		self::FIELD_VALUE_PREDICTING_HIGH_OUTCOME => 'nullable|numeric',
		self::FIELD_VALUE_PREDICTING_LOW_OUTCOME => 'nullable|numeric',
		self::FIELD_WP_POST_ID => 'nullable|integer|min:1',
		self::FIELD_Z_SCORE => 'required|numeric',
	];
	/**
	 * @param $causeNameOrId
	 * @param $effectNameOrId
	 * @return GlobalVariableRelationship
	 */
	public static function findByVariableNamesOrIds($causeNameOrId, $effectNameOrId): ?self {
		$cause = VariableIdProperty::pluck($causeNameOrId);
		if(!$cause){throw new CommonVariableNotFoundException("$causeNameOrId not found");}
		$effect = VariableIdProperty::pluck($effectNameOrId);
		if(!$effect){throw new CommonVariableNotFoundException("$effectNameOrId not found");}
		if($mem = static::findInMemoryByVariableIds($cause, $effect)){return $mem;}
		$ac = self::whereCauseVariableId($cause)
			->where(self::FIELD_EFFECT_VARIABLE_ID, $effect)
			->first();
		if($ac){
			$ac->addToMemory();
			$ac->setRelationAndAddToMemory('cause_variable', Variable::findInMemoryOrDB($cause));
			$ac->setRelationAndAddToMemory('effect_variable', Variable::findInMemoryOrDB($effect));
		}
		return $ac;
	}
	/**
	 * @param $causeNameOrId
	 * @param $effectNameOrId
	 * @return GlobalVariableRelationship|Builder|\Illuminate\Database\Eloquent\Model|null|object
	 */
	public static function findOrCreateByVariableNamesOrIds($causeNameOrId, $effectNameOrId){
		$ac = self::findByVariableNamesOrIds($causeNameOrId, $effectNameOrId);
		if(!$ac){
			$dbm = new QMGlobalVariableRelationship(null, $causeNameOrId, $effectNameOrId);
			try {
				$dbm->analyzeFullyAndSave(__FUNCTION__);
			} catch (AlreadyAnalyzedException $e) {
				le($e);
				throw new \LogicException();
			} catch (AlreadyAnalyzingException | StupidVariableNameException | NotEnoughDataException |
			ModelValidationException | DuplicateFailedAnalysisException $e) {
				QMLog::error(__METHOD__.": ".$e->getMessage());
				$ac = $dbm->l();
			} catch (TooSlowToAnalyzeException $e) {
				$s = QMPopulationStudy::findOrCreateQMStudy($causeNameOrId, $effectNameOrId);
				$s->queue(__METHOD__.": ".$e->getMessage());
			}
		}
		return $ac;
	}
	public function getNameAttribute(): string{
		if(!$this->getAttribute(self::FIELD_CAUSE_VARIABLE_ID)){
			return static::getClassNameTitle();
		}
		return "Relationship Between " . $this->getCauseVariableName() . " and " . $this->getEffectVariableName();
	}
	public function getUserVariableRelationshipsAdminUrl(): string{
		return UserVariableRelationship::generateDataLabIndexUrl([
			UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID => $this->getCauseVariableId(),
			UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID => $this->getEffectVariableId(),
		]);
	}
	public function getUserVariableRelationshipsAdminLink(): string{
		$num = $this->number_of_correlations;
		$url = $this->getUserVariableRelationshipsAdminUrl();
		return "<a href='$url' target='_blank'>$num</a>";
	}
	public function getGaugeLink(array $params = [], int $maxLength = 50,
		string $style = CssHelper::SMALL_IMAGE_STYLE): string{
		$url = $this->getUrl($params);
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
	public function getUserId(): ?int{
		return UserIdProperty::USER_ID_POPULATION;
	}
	/**
	 * @param User|null $reader
	 * @return bool
	 */
	public function canReadMe($reader = null): bool{
		if($this->readerIsOwnerOrAdmin($reader)){
			return true;
		}
		return $this->number_of_correlations >= GlobalVariableRelationshipNumberOfUsersProperty::MIN_FOR_REQUEST;
	}
	/**
	 * @param \Illuminate\Database\Query\Builder|Builder $qb
	 * @param User $user
	 * @return \Illuminate\Database\Query\Builder
	 */
	public function restrictQueryBasedOnPermissions($qb, $user = null): \Illuminate\Database\Query\Builder{
		// TODO: Replace FIELD_NUMBER_OF_CORRELATIONS with IS_PUBLIC $qb = parent::restrictQueryBasedOnPermissions($qb, $user);
		if(!$user){
			$user = QMAuth::getQMUser();
		}
		if($user && $user->isAdmin()){
			return $qb;
		}
		$qb->where(self::FIELD_NUMBER_OF_USERS, ">=", GlobalVariableRelationshipNumberOfUsersProperty::MIN_FOR_REQUEST);
		return $qb;
	}
	/**
	 * @return GlobalVariableRelationship
	 */
	public static function fakeFromPropertyModels(int $userId = UserIdProperty::USER_ID_TEST_USER): BaseModel{
		$c = UserVariableRelationship::firstOrFakeSave();
		return $c->getOrCreateGlobalVariableRelationship();
	}
    /**
     * Execute the query as a "select" statement.
     *
     * @param array|string $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     * @static
     */
    public static function get($columns = []){
        $gotten = parent::get();
        $byIds = [];
        foreach ($gotten as $ac){
            if(isset($byIds[$ac->id])){
                le("Duplicate Global Variable Relationship ID: " . $ac->id);
            }
            $byIds[$ac->getId()] = $ac;
        }
        return $gotten;
    }
	/**
	 * @return QMGlobalVariableRelationship
	 */
	public function getDBModel(): DBModel{
		if($dbm = $this->getDBModelFromMemory()){
			return $dbm;
		}
		$arr = $this->attributes;
		$dbm = new QMGlobalVariableRelationship();
		$dbm->setLaravelModel($this);
		$cv = $this->getCauseVariable();
		$dbm->setCauseVariable($cv->getDBModel());
		$ev = $this->getEffectVariable();
		$dbm->setEffectVariable($ev->getDBModel());
		$dbm->populateFromSnakeCaseArray($arr);
		foreach(QMGlobalVariableRelationship::DB_FIELD_NAME_TO_PROPERTY_NAME_MAP as $db => $prop){
			$dbm->$prop = $this->getAttribute($db);
		}
		$dbm->populateDefaultFields();
		$dbm->setStudyProperties();
		$dbm->getUserVoteValue();
		if(!$dbm->predictorExplanation){le("");}
		if($this->hasId()){
			$dbm->addToMemory();
		}
		return $dbm;
	}
	/**
	 * @return string
	 */
	private function getForwardStatisticsAndAnalysisSettingsString(): string{
		return '(' . $this->getGlobalVariableRelationship()->getPValueDataPointsOrNumberOfParticipantsFragment() . ', ' .
		       $this->getOnsetDelayDurationOfActionString() . ')';
	}
	/**
	 * @return StudyHtml
	 */
	public function getStudyHtml(): StudyHtml{
		return $this->findInMemoryOrNewQMStudy()->getStudyHtml();
	}
	public function typeIsIndividual(): bool{
		return false;
	}
	public static function withVariables(): Builder{
		return self::with(['cause_variable', 'effect_variable']);
	}
	/**
	 * @param array|int $ids
	 * @return GlobalVariableRelationship[]
	 */
	public static function getWithVariables($ids): array{
		if(!is_array($ids)){
			$ids = [$ids];
		}
		if(!$ids){
			le("No ids provided to ".__METHOD__);
        }
		$correlations = self::withVariables()->whereIn(self::FIELD_ID, $ids)->get();
		$byId = [];
		foreach($correlations as $c){
			$byId[$c->id] = $c;
		}
		return $byId;
	}
	/**
	 * @return StudyCard
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getOptionsListCard(){
		$s = $this->findInMemoryOrNewQMStudy();
		return $s->getOptionsListCard();
	}
	/**
	 * @return StudyCard
	 */
	public function getCard(): QMCard{
		return $this->getOptionsListCard();
	}
	/**
	 * @param $value
	 */
	public function setInternalErrorMessageAttribute($value){
		$prev = $this->attributes[self::FIELD_INTERNAL_ERROR_MESSAGE] ?? null;
		if($value && $value !== $prev){
			$this->logError($value);
		}
		$this->attributes[self::FIELD_INTERNAL_ERROR_MESSAGE] = $value;
	}
	public function getQmScore(): float{
		return $this->aggregate_qm_score;
	}
	public function getColor(): string{
		if(!$this->hasId()){
			return static::COLOR;
		}
		if($this->changeFragment() === null){
			return static::COLOR;
		}
		$change = $this->getChangeFromBaseline();
		return BaseEffectFollowUpPercentChangeFromBaselineProperty::generateColor($change,
			$this->getEffectVariableValence());
	}
	/**
	 * @return array
	 */
	public function recalculateUserVariableRelationshipsWithWrongCauseUnitId(): array{
		$correlations = $this->getCorrelations();
		$recalculated = [];
		foreach($correlations as $correlation){
			if($correlation->cause_unit_id && $correlation->cause_unit_id !== $this->getCauseVariable()->default_unit_id){
                $correlation->analyzeFullyAndPostIfNecessary(__FUNCTION__);
                $correlation->save();
				if($correlation->cause_unit_id !== $this->getCauseVariable()->default_unit_id){
					le("User Variable Relationship {$correlation->id} has wrong cause unit id: {$correlation->cause_unit_id}");
				}
				$recalculated[] = $correlation;
            }
		}
		return $recalculated;
	}
	/**
	 * @return array
	 */
	public function recalculateUserVariableRelationships(): array{
		$cause = $this->getCauseVariable();
		$qb = $cause->user_variables(true);
		$qb->whereNotIn(UserVariable::TABLE . '.' . UserVariable::FIELD_USER_ID,
			UserIdProperty::getTestSystemAndDeletedUserIds());
		/** @var UserVariable[] $causeUserVariables */
		$causeUserVariables = $qb->get();
		$correlations = [];
		foreach($causeUserVariables as $causeUV){
			$effectUV = UserVariable::findByNameOrId($causeUV->user_id,
				$this->getEffectVariableId());
			if($effectUV){
				$uc = new QMUserVariableRelationship(null, $causeUV->getDBModel(), $effectUV->getDBModel());
				try {
					$uc->analyze(__FUNCTION__);
				} catch (AlreadyAnalyzedException | AlreadyAnalyzingException | DuplicateFailedAnalysisException |
				TooSlowToAnalyzeException $e) {
					le($e);
				} catch (NotEnoughDataException | StupidVariableNameException $e) {
					$uc->logError($uc->getUrl());
					continue;
				}
				if(!$uc->id){le("No id!");}
				$correlations[$uc->getLogMetaDataString()] = $uc;
			}
		}
		if(!$correlations){
			QMLog::error("No user variable relationships for $this. " . $this->getDataLabDeleteUrl());
		}
		return $correlations;
	}
	/**
	 * @return int
	 */
	public function getEffectVariableId(): int{
		return $this->attributes[self::FIELD_EFFECT_VARIABLE_ID];
	}
	public function getChangeFromBaseline(?int $precision = null): ?float{
		$val = $this->attributes[self::FIELD_EFFECT_FOLLOW_UP_PERCENT_CHANGE_FROM_BASELINE];
		if($val && $precision){
			return Stats::roundByNumberOfSignificantDigits($val, $precision);
		}
		return $val;
	}
	public function save(array $options = []): bool{
		try {
			return parent::save($options);
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			return parent::save($options);
		}
	}
	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function setAttribute($key, $value){
		if($key === "causeVariableDisplayName"){
			le($key);
		}
		parent::setAttribute($key, $value);
	}
	/**
	 * @return int
	 */
	public function getOnsetDelay(): int{
		return $this->attributes[GlobalVariableRelationship::FIELD_ONSET_DELAY] ??
			$this->attributes[GlobalVariableRelationship::FIELD_ONSET_DELAY] =
				GlobalVariableRelationshipOnsetDelayProperty::calculate($this);
	}
	/**
	 * @return int
	 */
	public function getDurationOfAction(): int{
		return $this->attributes[GlobalVariableRelationship::FIELD_DURATION_OF_ACTION] ??
			$this->attributes[GlobalVariableRelationship::FIELD_DURATION_OF_ACTION] =
				GlobalVariableRelationshipDurationOfActionProperty::calculate($this);
	}
	public function getAggregateQMScore(): float{
		return $this->attributes[self::FIELD_AGGREGATE_QM_SCORE];
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
	/**
	 * @param $data
	 * @return self|null
	 */
	public static function findByData($data): ?BaseModel{
		return parent::findByData($data);
	}
	/**
	 * @return float
	 */
	public function getTValue(): ?float{
		return $this->attributes[self::FIELD_T_VALUE] ?? null;
	}
	/**
	 * @return float
	 */
	public function getCriticalTValue(): ?float{
		return $this->attributes[self::FIELD_CRITICAL_T_VALUE] ?? null;
	}
	/**
	 * @return Builder|static
	 */
	public static function queryFromRequest(): Builder{
		$qb = parent::queryFromRequest();
		$qb->where(self::FIELD_NUMBER_OF_USERS, ">=", GlobalVariableRelationshipNumberOfUsersProperty::MIN_FOR_REQUEST);
		$qb->with([
			'cause_variable',
			'effect_variable',
			'votes',
		]);
		return $qb;
	}
	public function getCauseVariableId(): int{
		return $this->attributes[self::FIELD_CAUSE_VARIABLE_ID];
	}
	/**
	 * @param int $causeId
	 * @param int $effectId
	 * @return GlobalVariableRelationship|null
	 */
	public static function findInMemoryByVariableIds(int $causeId, int $effectId): ?GlobalVariableRelationship{
		return self::findInMemory([
			self::FIELD_CAUSE_VARIABLE_ID => $causeId,
			self::FIELD_EFFECT_VARIABLE_ID => $effectId,
		]);
	}
	public function getGlobalVariableRelationship(): ?GlobalVariableRelationship{
		return $this;
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
	 * Get the fields displayed by the resource.
	 * @return array
	 */
	public function getFields(): array{
		$fields = parent::getFields();
		$fields[] = $this->imageField();
		$fields[] = $this->nameLinkToShowField();
		$fields = array_merge($fields, $this->getShowablePropertyFields());
		return $fields;
	}
	/**
	 * @return bool|null
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function forceDelete(){
		$this->correlations()->where(UserVariableRelationship::FIELD_AGGREGATE_CORRELATION_ID, $this->id)
			->update([UserVariableRelationship::FIELD_AGGREGATE_CORRELATION_ID => null]);
		return parent::forceDelete();
	}
	public function correlations(): HasMany{
		return $this->hasMany(UserVariableRelationship::class, [self::FIELD_CAUSE_VARIABLE_ID, self::FIELD_EFFECT_VARIABLE_ID],
			[self::FIELD_CAUSE_VARIABLE_ID, self::FIELD_EFFECT_VARIABLE_ID])
			->whereNotIn(UserVariableRelationship::FIELD_USER_ID, UserIdProperty::getTestSystemAndDeletedUserIds())->with([
				'cause_user_variable',
				'effect_user_variable',
				'user',
			]);
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
	public function getLenses(Request $request): array{
		$lenses = parent::getLenses($request);
		$lenses[] = new GlobalVariableRelationshipsWithNullChangeLens($this);
		$lenses[] = new GlobalVariableRelationshipsWithNoUserVariableRelationshipsLens($this);
		$lenses[] = new FavoritesLens();
		$lenses[] = new LikesLens();
		return $lenses;
	}
	/**
	 * Get the actions available for the resource.
	 * @param Request $request
	 * @return array
	 */
	public function getActions(Request $request): array{
		$actions = parent::getActions($request);
		$actions[] = new AnalyzeAction($request);
		$actions[] = new FavoriteAction($request);
		$actions[] = new LikeAction($request);
		if(QMAuth::isAdmin()){
			$actions[] = new PHPUnitAction($request);
		}
		return $actions;
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{
		return true;
	}
	public function getIonIcon(): string {
		return IonIcon::androidGlobe;
	}
	public static function getS3Bucket(): string{ return S3Public::getBucketName(); }
	public function getCategoryNames(): array{
		return [$this->getCauseVariableCategoryName(), $this->getEffectVariableCategoryName()];
	}
	public function getShowPageView(array $params = []): View{
		return view('population-study', $this->getShowParams($params));
	}
	public function getStudyType(): string{
		return StudyTypeProperty::TYPE_POPULATION;
	}
	/**
	 * @return float
	 */
	public function getOrCalculateFollowUpPercentChangeFromBaseline(): float{
		$val = $this->getChangeFromBaseline();
		if($val !== null){
			return $val;
		}
		$val = GlobalVariableRelationshipEffectFollowUpPercentChangeFromBaselineProperty::calculate($this);
		return $val;
	}
	public function getUrlName(): string{
		return $this->getStudyId();
	}
	public function getStudyId(): string{
		return QMPopulationStudy::generateStudyId($this->getCauseVariableId(), $this->getEffectVariableId());
	}
	public function getUrlSubPath(): string{
		return $this->getStudyId();
	}
	public function getShowContentView(array $params = []): View{
		return view('population-study-content', [
			's' => $this->findInMemoryOrNewQMStudy(),
		]);
	}
	public static function getIndexPageView(): View{
		return QMPopulationStudy::getIndexPageView();
	}
	public static function generateIndexButtons(): array{
		return QMPopulationStudy::generateIndexButtons();
	}
	public static function getUrlFolder(): string{
		return QMPopulationStudy::getUrlFolder();
	}
	/**
	 * @return string
	 */
	public function getTagLine(): string{
		if(!$this->getAverageEffect()){
			return $this->generatePredictorExplanationSentence();
		}
		$change = $this->getOrCalculatePercentEffectChangeFromLowCauseToHighCause(1);
		$changeString = $change . "% average increase";
		if($change < 0){
			$change *= -1;
			$changeString = $change . "% average decrease";
		}
		return "Participants reported a $changeString in " . $this->effectNameWithSuffix() .
			" following above average " . $this->causeNameWithSuffix() . ".";
	}
	protected function getBasedOnString(): string{
		$num = $this->getNumberOfUsers();
		return "<br>based on data from $num participants";
	}
	/**
	 * @return string
	 */
	public function getDataQuantitySentence(): string{
		return "<p>{$this->getNumberOfPairs()} data points from {$this->getNumberOfUsers()} were used in this analysis. ";
	}
	/**
	 * @param float $inCommonUnit
	 * @param int $precision
	 * @return string|null
	 */
	protected function causeValueUserUnit(float $inCommonUnit, int $precision = QMVariableRelationship::SIG_FIGS): string{
		return $this->causeValueCommonUnit($inCommonUnit, $precision);
	}
	/**
	 * @param float $inCommonUnit
	 * @param int $precision
	 * @return string|null
	 */
	protected function effectValueUserUnit(float $inCommonUnit, int $precision = QMVariableRelationship::SIG_FIGS): string{
		return $this->effectValueCommonUnit($inCommonUnit, $precision);
	}
	public function getNumberOfChanges(): int{
		return $this->getAttribute(GlobalVariableRelationship::FIELD_CAUSE_CHANGES);
	}
	/**
	 * @param bool|null $addressingUser
	 * @return string
	 */
	public function getForMostYourOrThisIndividual(bool $addressingUser = null): string{
		return "Based on data from {$this->getNumberOfUsers()} participants, ";
	}
	public function getNumberOfUsers(): int{
		$val = $this->getAttribute(GlobalVariableRelationship::FIELD_NUMBER_OF_USERS);
		if($val === null){
			$val = GlobalVariableRelationshipNumberOfUsersProperty::calculate($this);
		}
		return $val;
	}
	public function getFontAwesome(): string{
		return GlobalVariableRelationship::FONT_AWESOME;
	}
	/**
	 * @inheritDoc
	 */
	public static function wherePostable(){
		$qb = static::query();
		$qb->where(static::TABLE . '.' . static::FIELD_NUMBER_OF_USERS, ">", 1);
		return $qb;
	}
	/**
	 * @inheritDoc
	 * @return string
	 */
	public function generatePostContent(): string{
		return $this->getDBModel()->generatePostContent();
	}
	/**
	 * @inheritDoc
	 */
	public function getCategoryName(): string{
		return GlobalVariableRelationship::CLASS_CATEGORY;
	}
	/**
	 * @inheritDoc
	 */
	public function getParentCategoryName(): ?string{
		return GlobalVariableRelationship::CLASS_CATEGORY;
	}
	/**
	 * @inheritDoc
	 */
	public function exceptionIfWeShouldNotPost(): void{
		$this->getDBModel()->exceptionIfWeShouldNotPost();
	}
	public function weShouldPost(): bool{
		return $this->numberOfCorrelations > 5 || $this->getNumberOfUpVotes() > 0;
	}
	/**
	 * @return string
	 * @throws InsufficientVarianceException
	 */
	public function getStatisticsTableHtml(): string{
		$totalAverage = ($this->getCauseVariable()->isSum()) ? "Total" : "Average";
		$table = [
			"Cause Variable Name" => $this->causeNameWithSuffix(),
			"Effect Variable Name" => $this->effectNameWithSuffix(),
			"Sinn Predictive Coefficient" => $this->getQmScore(),
			"Confidence Level" => $this->getConfidenceLevel(),
			"Confidence Interval" => $this->getConfidenceInterval(),
			"Forward Pearson Predictive Coefficient" => $this->getForwardPearsonCorrelationCoefficient(),
			"Critical T Value" => $this->getCriticalTValue(),
			"$totalAverage " . $this->causeNameWithSuffix() . " Over Previous " .
			$this->getDurationOfActionHumanString() . " Before ABOVE Average " .
			$this->effectNameWithSuffix() => $this->getDailyValuePredictingHighOutcomeString(),
			"$totalAverage " . $this->causeNameWithSuffix() . " Over Previous " .
			$this->getDurationOfActionHumanString() . " Before BELOW Average " .
			$this->effectNameWithSuffix() => $this->getDailyValuePredictingLowOutcomeString(),
			"Duration of Action" => $this->getDurationOfActionHumanString(),
			"Effect Size" => $this->getEffectSize(),
			"Number of Paired Measurements" => $this->getNumberOfPairs(),
			"Optimal Pearson Product" => $this->getOptimalPearsonProduct(),
			"P Value" => $this->getPValue(),
			"Statistical Significance" => $this->getStatisticalSignificance(),
			"Strength of Relationship" => $this->getConfidenceInterval(),
			"Study Type" => $this->getStudyType(),
			"Analysis Performed At" => TimeHelper::YYYYmmddd($this->getUpdatedAt()),
			"Number of Participants" => $this->getNumberOfUsers(),
		];
		$html = QMTable::convertObjectToVerticalPropertyValueTableHtml($table, "Relationship Statistics");
		return $html;
	}
	/**
	 * @param QMVariable|Variable $cause
	 * @param QMVariable|Variable $effect
	 * @return string
	 */
	public static function generatePHPUnitTestUrlForAnalyze($cause, $effect): string{
		return StagingJobTestFile::getUrl('GlobalVariableRelationshipCause' . QMStr::toClassName($cause->getVariableName()) .
			'Effect' . QMStr::toClassName($effect->getVariableName()),
			'$c = QMGlobalVariableRelationship::getOrCreateByIds(' . $cause->getVariableIdAttribute() . ' ,' .
			$effect->getVariableIdAttribute() . ');' . PHP_EOL . "\t\t\$c->analyzeFully('we are testing');",
			\App\VariableRelationships\QMGlobalVariableRelationship::class);
	}
	/**
	 * @param $causeVariableId
	 * @param $effectVariableId
	 * @return string
	 */
	public static function generateStudyId($causeVariableId, $effectVariableId): string{
		return QMPopulationStudy::generateStudyId($causeVariableId, $effectVariableId);
	}
	public function getKeyWords(): array{
		return $this->getStudyKeywords();
	}
	public function getAnalysisStartedAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[GlobalVariableRelationship::FIELD_ANALYSIS_STARTED_AT] ?? null;
		} else{
			/** @var QMGlobalVariableRelationship $this */
			return $this->analysisStartedAt;
		}
	}
	public function getCauseTreatmentAveragePerDurationOfAction(): ?float{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[GlobalVariableRelationship::FIELD_CAUSE_TREATMENT_AVERAGE_PER_DURATION_OF_ACTION] ?? null;
		} else{
			/** @var QMGlobalVariableRelationship $this */
			return $this->causeTreatmentAveragePerDurationOfAction;
		}
	}
	public function getDeletedAt(): ?string{
		return $this->attributes[GlobalVariableRelationship::FIELD_DELETED_AT] ?? null;
	}
	public function setDeletedAt(string $deletedAt): void{
		$this->setAttribute(GlobalVariableRelationship::FIELD_DELETED_AT, $deletedAt);
	}
	public function setDurationOfAction(int $durationOfAction): void{
		$this->setAttribute(GlobalVariableRelationship::FIELD_DURATION_OF_ACTION, $durationOfAction);
	}
	public function getInternalErrorMessage(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[GlobalVariableRelationship::FIELD_INTERNAL_ERROR_MESSAGE] ?? null;
		} else{
			/** @var QMGlobalVariableRelationship $this */
			return $this->internalErrorMessage;
		}
	}
	public function setNewestDataAt(string $newestDataAt): void{
		$this->setAttribute(GlobalVariableRelationship::FIELD_NEWEST_DATA_AT, $newestDataAt);
	}
	public function setOnsetDelay(int $onsetDelay): void{
		$this->setAttribute(GlobalVariableRelationship::FIELD_ONSET_DELAY, $onsetDelay);
	}
	public function getReasonForAnalysis(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[GlobalVariableRelationship::FIELD_REASON_FOR_ANALYSIS] ?? null;
		} else{
			/** @var QMGlobalVariableRelationship $this */
			return $this->reasonForAnalysis;
		}
	}
	public function getUserErrorMessage(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[GlobalVariableRelationship::FIELD_USER_ERROR_MESSAGE] ?? null;
		} else{
			/** @var QMGlobalVariableRelationship $this */
			return $this->userErrorMessage;
		}
	}
	public function getWpPostId(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[GlobalVariableRelationship::FIELD_WP_POST_ID] ?? null;
		} else{
			/** @var QMGlobalVariableRelationship $this */
			return $this->wpPostId;
		}
	}
	/**
	 * @return float
	 */
	protected function calculateGroupedCauseValueClosestToValuePredictingLowOutcome(): ?float{
		return GlobalVariableRelationshipGroupedCauseValueClosestToValuePredictingLowOutcomeProperty::calculate($this);
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		$arr = [
			new GlobalVariableRelationshipCauseVariableButton($this),
			new GlobalVariableRelationshipEffectVariableButton($this),
		];
		try {
			$arr[] = new GlobalVariableRelationshipCorrelationsButton($this);
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			$arr[] = new GlobalVariableRelationshipCorrelationsButton($this);
		}
		return $arr;
	}
	public function getUrlParams(): array{
		return [
			self::FIELD_CAUSE_VARIABLE_ID => $this->getCauseVariableId(),
			self::FIELD_EFFECT_VARIABLE_ID => $this->getEffectVariableId(),
		];
	}
	public function getAvatar(): string{
		return GlobalVariableRelationship::DEFAULT_IMAGE;
	}
	public function getSortingScore(): float{
		return $this->getAggregateQMScore();
	}
	/**
	 * @return mixed
	 */
	public function getPopulationTraitCorrelationPearsonCorrelationCoefficient(): ?float{
		return $this->getAttribute(GlobalVariableRelationship::FIELD_POPULATION_TRAIT_PEARSON_CORRELATION_COEFFICIENT);
	}
	/**
	 * @return PairOfAverages[]
	 */
	public function getPairsOfAveragesForAllUsers(): array{
		return $this->getDBModel()->getPairsOfAveragesForAllUsers();
	}
	public static function getIndexContentView(): View{
		return view('chip-search', self::getIndexViewParams());
	}
	/**
	 * @return UserVariableRelationship[]|\Illuminate\Support\Collection
	 */
	public static function getIndexModels(): \Illuminate\Support\Collection{
		return GlobalVariableRelationship::withUpVotes()->where(GlobalVariableRelationship::FIELD_IS_PUBLIC, true)
			->orderByDesc(GlobalVariableRelationship::FIELD_AGGREGATE_QM_SCORE)->get();
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
	 * @return Collection|UserVariableRelationship[]
	 */
	public function getCorrelations(): Collection{
		if($this->hasId()){
			$this->loadMissing('user_variable_relationships');
		} else {
			if(!$this->relationLoaded('user_variable_relationships')){
				$correlations = UserVariableRelationship::whereCauseVariableId($this->getCauseVariableId())
					->where(UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID, $this->getEffectVariableId())
					->get();
				$this->setRelation('user_variable_relationships', $correlations);
			}
		}
		return $this->correlations;
	}
	/**
	 * @return QMUserVariableRelationship[]
	 */
	public function getQMUserVariableRelationships(): array{
		$correlations = $this->getCorrelations();
		$correlations = QMUserVariableRelationship::toDBModels($correlations);
		return $correlations;
	}
	public function getPublicCorrelations(): Collection{
		return $this->getCorrelations()->where(UserVariableRelationship::FIELD_IS_PUBLIC, true);
	}
	public function hasPublicCorrelation(): bool{
		$correlations = $this->getCorrelations();
		foreach($correlations as $correlation){
			if($correlation->getIsPublic()){
				return true;
			}
		}
		return false;
	}
	/**
	 * @return GlobalVariableRelationshipChartGroup
	 */
	public function getChartGroup(): ChartGroup{
		$charts = $this->charts;
		if(QMStr::isNullString($charts)){
			$charts = null;
		}
		$DBModel = $this->getDBModel();
		if($charts){
			$charts = GlobalVariableRelationshipChartGroup::instantiateIfNecessary($charts);
			$charts->setSourceObject($DBModel);
			return $charts;
		} else{
			return $DBModel->getOrSetCharts();
		}
	}
	/**
	 * @return UserVariableRelationship
     */
	public function getMikesCorrelation(): ?UserVariableRelationship
    {
		$correlations = $this->correlations;
		return $correlations->where(UserVariableRelationship::FIELD_USER_ID, UserIdProperty::USER_ID_MIKE)->first();
	}
	public function getUUID(): ?string{
		if($this instanceof QMGlobalVariableRelationship){
			return $this->id;
		}
		return $this->attributes[self::FIELD_ID] ?? null;
	}
	public function getNumberOfDays(): int{
		return $this->getAttribute(GlobalVariableRelationship::FIELD_NUMBER_OF_PAIRS);
	}
	public function getCauseUrl(): string{
		return $this->getCauseVariable()->getUrl();
	}
	/**
	 * @return float
	 */
	public function getForwardPearsonCorrelationCoefficient(): float{
		return $this->getAttribute(GlobalVariableRelationship::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT);
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
	public function getPredictsHighEffectChange(): ?float {
		if($this->predicts_high_effect_change === null){
			GlobalVariableRelationshipPredictsHighEffectChangeProperty::calculate($this);
		}
		return $this->predicts_high_effect_change;
	}
	/**
	 * @return int
	 */
	public function getPredictsLowEffectChange(): ?float {
		if($this->predicts_low_effect_change === null){
			GlobalVariableRelationshipPredictsLowEffectChangeProperty::calculate($this);
		}
		return $this->predicts_low_effect_change;
	}
	/**
	 * @return string
	 */
	public function getForwardPearsonSentence(): string{
		return "<p>The Forward Pearson Predictive Coefficient was " .
		       $this->getCorrelationCoefficient(QMVariableRelationship::SIG_FIGS) .
		       $this->getForwardStatisticsAndAnalysisSettingsString() . '.
            </p>';
	}
	/**
	 * @return $this
	 */
	public function getHasCorrelationCoefficient(){
		return $this;
	}
	/**
	 * @return string
	 * @throws InsufficientVarianceException
	 */
	public function getReverseStatisticsAndAnalysisSettingsString(): string{
		$string = '(';
		$pValue = $this->getPValue();
		if($pValue){
			$string .= $pValue . ', 95% CI ' .
			           round($this->getReverseCorrelationCoefficient() - $this->getConfidenceInterval(), QMVariableRelationship::SIG_FIGS) .
			           ' to ' .
			           round($this->getReverseCorrelationCoefficient() + $this->getConfidenceInterval(), QMVariableRelationship::SIG_FIGS) .
			           ', ';
		}
		return $string . 'onset delay = -' . $this->getOnsetDelayHumanString() . ', duration of action = -' .
		       $this->getDurationOfActionHumanString() . ')';
	}
	/**
	 * @return bool
	 *
	 */
	public function getJoined(): bool{
		$userId = QMAuth::id(false);
		if(!$userId){return false;}
		$c = $this->findUserVariableRelationshipInMemory($userId);
		return $c !== null;
	}
	public function findUserVariableRelationshipInMemory(int $userId): ?UserVariableRelationship
    {
		return UserVariableRelationship::findInMemoryByIds($userId, $this->cause_variable_id, $this->effect_variable_id);
	}
    /**
     * @param null $writer
     * @return bool
     * @throws AccessTokenExpiredException
     */
    public function canCreateMe($writer = null): bool{
        return QMAuth::isAdmin();
    }
	public function analyzeFully(string $reason): QMGlobalVariableRelationship{
		$correlations = $this->getCorrelations();
		$correlations->each(function(UserVariableRelationship $correlation) use ($reason){
			$correlation->analyzeFully($reason);
		});
		$dbm = $this->getDBModel();
		$dbm->analyzeFully($reason);
		return $dbm;
	}
}
