<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Models;
use App\Actions\ActionEvent;
use App\Astral\Actions\CorrelateAction;
use App\Astral\Actions\DeleteWithRelationsAction;
use App\Astral\Actions\FavoriteAction;
use App\Astral\Actions\SetPublicAction;
use App\Astral\Actions\UnFavoriteAction;
use App\Astral\GlobalVariableRelationshipBaseAstralResource;
use App\Astral\Lenses\EconomicIndicatorsLens;
use App\Astral\Lenses\FavoritesLens;
use App\Astral\Lenses\StrategyVariablesLens;
use App\Buttons\Analyzable\CorrelateButton;
use App\Buttons\QMButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Buttons\RelationshipButtons\Variable\VariableDefaultUnitButton;
use App\Buttons\RelationshipButtons\Variable\VariableMeasurementsButton;
use App\Buttons\RelationshipButtons\Variable\VariablePopulationCauseStudiesButton;
use App\Buttons\RelationshipButtons\Variable\VariablePopulationEffectStudiesButton;
use App\Buttons\RelationshipButtons\Variable\VariableUserVariablesButton;
use App\Buttons\RelationshipButtons\Variable\VariableVariableCategoryButton;
use App\Buttons\States\OnboardingStateButton;
use App\Buttons\VariableButton;
use App\Cards\VariableStatisticsCard;
use App\Charts\ChartGroup;
use App\Charts\VariableCharts\VariableChartChartGroup;
use App\VariableRelationships\CorrelationsAndExplanationResponseBody;
use App\VariableRelationships\QMUserVariableRelationship;
use App\DataSources\QMDataSource;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\BadRequestException;
use App\Exceptions\CommonVariableNotFoundException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\HighchartExportException;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidTagCategoriesException;
use App\Exceptions\InvalidVariableNameException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\StupidVariableNameException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\VariableCategoryNotFoundException;
use App\Fields\Field;
use App\Files\JavaScript\VariableShowJavaScriptFile;
use App\Logging\QMLog;
use App\Menus\JournalMenu;
use App\Menus\QMMenu;
use App\Models\Base\BaseVariable;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipIsPublicProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Properties\Base\BaseFillingValueProperty;
use App\Properties\Base\BaseUnitIdProperty;
use App\Properties\Base\BaseUserIdProperty;
use App\Properties\BaseProperty;
use App\Properties\Measurement\MeasurementConnectorIdProperty;
use App\Properties\Measurement\MeasurementSourceNameProperty;
use App\Properties\Measurement\MeasurementStartAtProperty;
use App\Properties\Measurement\MeasurementVariableIdProperty;
use App\Properties\Unit\UnitNameProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\UserVariable\UserVariableDefaultUnitIdProperty;
use App\Properties\Variable\VariableCommonMaximumAllowedDailyValueProperty;
use App\Properties\Variable\VariableCommonMinimumAllowedDailyValueProperty;
use App\Properties\Variable\VariableDescriptionProperty;
use App\Properties\Variable\VariableFillingTypeProperty;
use App\Properties\Variable\VariableIsPublicProperty;
use App\Properties\Variable\VariableManualTrackingProperty;
use App\Properties\Variable\VariableMinimumAllowedSecondsBetweenMeasurementsProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Properties\Variable\VariableStatusProperty;
use App\Properties\Variable\VariableSynonymsProperty;
use App\Properties\Variable\VariableVariableCategoryIdProperty;
use App\Properties\VariableCategory\VariableCategoryIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Slim\Model\Measurement\AnonymousMeasurement;
use App\Slim\Model\QMUnit;
use App\Slim\Model\User\QMUser;
use App\Storage\DB\Writable;
use App\Storage\S3\S3Public;
use App\Tables\QMTable;
use App\Traits\AnalyzableTrait;
use App\Traits\HasButton;
use App\Traits\HasCharts;
use App\Traits\HasDBModel;
use App\Traits\HasErrors;
use App\Traits\HasExceptions;
use App\Traits\HasFiles;
use App\Traits\HasMany\HasManyMeasurements;
use App\Traits\HasModel\HasUnit;
use App\Traits\HasModel\HasVariable;
use App\Traits\HasModel\HasVariableCategory;
use App\Traits\HasName;
use App\Traits\HasOutcomesAndPredictors;
use App\Traits\HasProperty\HasOnsetAndDuration;
use App\Traits\HasSynonyms;
use App\Traits\IsEditable;
use App\Traits\LoggerTrait;
use App\Traits\PostableTrait;
use App\Traits\SavesToRepo;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\FontAwesome;
use App\UI\HtmlHelper;
use App\UI\ImageUrls;
use App\UI\QMColor;
use App\Utils\AppMode;
use App\Utils\EnvOverride;
use App\Utils\Stats;
use App\VariableCategories\CausesOfIllnessVariableCategory;
use App\VariableCategories\EmotionsVariableCategory;
use App\VariableCategories\FoodsVariableCategory;
use App\VariableCategories\NutrientsVariableCategory;
use App\VariableCategories\SoftwareVariableCategory;
use App\VariableCategories\SymptomsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\DailyStepCountCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\SleepDurationCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\SleepEfficiencyFromFitbitCommonVariable;
use App\Variables\CommonVariables\VitalSignsCommonVariables\RestingHeartRatePulseCommonVariable;
use App\Variables\QMCommonTag;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use App\Variables\QMVariableCategory;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Database\Seeders\VariableSeeder;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Overtrue\LaravelFavorite\Traits\Favoriteable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Tags\Tag;
/**
 * @mixin QMCommonVariable
 * App\Models\Variable
 * @property integer $id
 * @property string $client_id
 * @property string $name User-defined variable display name
 * @property integer $variable_category_id Variable category ID
 * @property integer $default_unit_id ID of the default unit for the variable
 * @property string $combination_operation How to combine values of this variable (for instance, to see a summary of
 *     the values over a month) SUM or MEAN
 * @property float $filling_value Value for replacing null measurements
 * @property float $maximum_allowed_value Minimum reasonable value for this variable (uses default unit)
 * @property float $minimum_allowed_value Maximum reasonable value for this variable (uses default unit)
 * @property integer $onset_delay How long it takes for a measurement in this variable to take effect
 * @property integer $duration_of_action How long the effect of a measurement in this variable lasts
 * @property integer $public Is variable public
 * @property boolean $cause_only A value of 1 indicates that this variable is generally a cause in a causal
 *     relationship.  An example of a causeOnly variable would be a variable such as Cloud Cover which would generally
 *     not be influenced by the behaviour of the user
 * @property float $most_common_value Most common value
 * @property integer $most_common_original_unit_id Most common Unit
 * @property float $standard_deviation Standard Deviation
 * @property float $variance Variance
 * @property float $mean Mean
 * @property float $median Median
 * @property float $number_of_raw_measurements Number of measurements
 * @property float $number_of_unique_values Number of unique values
 * @property float $skewness Skewness
 * @property float $kurtosis Kurtosis
 * @property ?string $status status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $product_url Product URL
 * @property string $image_url Image URL
 * @property float $price Price
 * @property integer $number_of_user_variables Number of variables
 * @property boolean $outcome Outcome variables (those with `outcome` == 1) are variables for which a human would
 *     generally want to identify the influencing factors.  These include symptoms of illness, physique, mood,
 *     cognitive performance, etc.  Generally correlation calculations are only performed on outcome variables.
 * @property float $minimum_recorded_value Minimum recorded value of this variable
 * @property float $maximum_recorded_value Maximum recorded value of this variable
 * @property-read VariableCategory $category
 * @property-read Unit $defaultUnit
 * @property-read Unit $mostCommonUnit
 * @method static \Illuminate\Database\Query\Builder|Variable whereId($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereClientId($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereParentId($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereVariableCategoryId($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereDefaultUnitId($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereCombinationOperation($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereFillingValue($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereMaximumAllowedValue($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereMinimumAllowedValue($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereOnsetDelay($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereDurationOfAction($value)
 * @method static \Illuminate\Database\Query\Builder|Variable wherePublic($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereCauseOnly($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereMostCommonValue($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereMostCommonUnitId($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereStandardDeviation($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereVariance($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereMean($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereMedian($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereNumberOfMeasurements($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereNumberOfUniqueValues($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereSkewness($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereKurtosis($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereErrorMessage($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereLastSuccessfulUpdateTime($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereProductUrl($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereImageUrl($value)
 * @method static \Illuminate\Database\Query\Builder|Variable wherePrice($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereNumberOfUserVariables($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereOutcome($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereMinimumRecordedValue($value)
 * @method static \Illuminate\Database\Query\Builder|Variable whereMaximumRecordedValue($value)
 * @property float|null $default_value
 * @property string|null $common_alias
 * @property string|null $description
 * @property string|null $informational_url
 * @property string|null $ion_icon
 * @property int|null $number_of_global_variable_relationships_as_cause Number of global variable relationships for which this
 *     variable is the cause variable
 * @property int|null $number_of_global_variable_relationships_as_effect Number of global variable relationships for which this
 *     variable is the effect variable
 * @property float|null $second_most_common_value
 * @property float|null $third_most_common_value
 * @property string|null $abbreviatedUnitName
 * @property int|null $most_common_connector_id
 * @property array $synonyms
 * @property string|null $wikipedia_url
 * @property string|null $brand_name
 * @property string|null $valence
 * @property string|null $wikipedia_title
 * @property int|null $number_of_tracking_reminders
 * @property string|null $upc_12
 * @property string|null $upc_14
 * @property int|null $number_common_tagged_by
 * @property int|null $number_of_common_tags
 * @property string|null $meta_data
 * @property string|null $deleted_at
 * @property string|null $most_common_source_name
 * @property array $data_sources_count Array of connector or client measurement data source names as key with
 *     number of users as value
 * @property string|null $optimal_value_message
 * @property int|null $best_cause_variable_id
 * @property int|null $best_effect_variable_id
 * @property int|null $best_global_variable_relationship_id
 * @property float|null $common_maximum_allowed_daily_value
 * @property float|null $common_minimum_allowed_daily_value
 * @property float|null $common_minimum_allowed_non_zero_value
 * @property int|null $minimum_allowed_seconds_between_measurements
 * @property int|null $average_seconds_between_measurements
 * @property int|null $median_seconds_between_measurements
 * @method static Builder|Variable newModelQuery()
 * @method static Builder|Variable newQuery()
 * @method static Builder|Variable query()
 * @method static Builder|Variable whereAbbreviatedUnitName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Variable
 *     whereAverageSecondsBetweenMeasurements($value)
 * @method static Builder|Variable whereBestGlobalVariableRelationship($value)
 * @method static Builder|Variable whereBestCauseVariableId($value)
 * @method static Builder|Variable whereBestEffectVariableId($value)
 * @method static Builder|Variable whereBrandName($value)
 * @method static Builder|Variable whereCommonAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Variable
 *     whereCommonMaximumAllowedDailyValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Variable
 *     whereCommonMinimumAllowedDailyValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Variable
 *     whereCommonMinimumAllowedNonZeroValue($value)
 * @method static Builder|Variable whereDataSourcesCount($value)
 * @method static Builder|Variable whereDefaultValue($value)
 * @method static Builder|Variable whereDeletedAt($value)
 * @method static Builder|Variable whereDescription($value)
 * @method static Builder|Variable whereInformationalUrl($value)
 * @method static Builder|Variable whereIonIcon($value)
 * @method static Builder|Variable whereMedianSecondsBetweenMeasurements($value)
 * @method static Builder|Variable whereMetaData($value)
 * @method static Builder|Variable whereMinimumAllowedSecondsBetweenMeasurements($value)
 * @method static Builder|Variable whereMostCommonConnectorId($value)
 * @method static Builder|Variable whereMostCommonOriginalUnitId($value)
 * @method static Builder|Variable whereMostCommonSourceName($value)
 * @method static Builder|Variable whereNumberCommonTaggedBy($value)
 * @method static Builder|Variable whereNumberOfGlobalVariableRelationshipsAsCause($value)
 * @method static Builder|Variable whereNumberOfGlobalVariableRelationshipsAsEffect($value)
 * @method static Builder|Variable whereNumberOfCommonTags($value)
 * @method static Builder|Variable whereNumberOfRawMeasurements($value)
 * @method static Builder|Variable whereNumberOfTrackingReminders($value)
 * @method static Builder|Variable whereOptimalValueMessage($value)
 * @method static Builder|Variable whereSecondMostCommonValue($value)
 * @method static Builder|Variable whereSynonyms($value)
 * @method static Builder|Variable whereThirdMostCommonValue($value)
 * @method static Builder|Variable whereUpc12($value)
 * @method static Builder|Variable whereUpc14($value)
 * @method static Builder|Variable whereValence($value)
 * @method static Builder|Variable whereWikipediaTitle($value)
 * @method static Builder|Variable whereWikipediaUrl($value)
 * @mixin Eloquent
 * @property int|null $number_of_raw_measurements_with_tags_joins_children
 * @property string|null $additional_meta_data
 * @method static Builder|Variable whereAdditionalMetaData($value)
 * @method static Builder|Variable whereNumberOfRawMeasurementsWithTagsJoinsChildren($value)
 * @property int|null $manual_tracking
 * @property string|null $analysis_settings_modified_at
 * @property string|null $newest_data_at
 * @property string|null $analysis_ended_at
 * @property string|null $analysis_requested_at
 * @property string|null $reason_for_analysis
 * @property string|null $analysis_started_at
 * @property string|null $user_error_message
 * @property string|null $internal_error_message
 * @method static Builder|Variable whereAnalysisEndedAt($value)
 * @method static Builder|Variable whereAnalysisRequestedAt($value)
 * @method static Builder|Variable whereAnalysisSettingsModifiedAt($value)
 * @method static Builder|Variable whereAnalysisStartedAt($value)
 * @method static Builder|Variable whereInternalErrorMessage($value)
 * @method static Builder|Variable whereManualTracking($value)
 * @method static Builder|Variable whereNewestDataAt($value)
 * @method static Builder|Variable whereReasonForAnalysis($value)
 * @method static Builder|Variable whereUserErrorMessage($value)
 * @property string|null $latest_tagged_measurement_start_at
 * @property string|null $earliest_tagged_measurement_start_at
 * @property string|null $latest_non_tagged_measurement_start_at
 * @property string|null $earliest_non_tagged_measurement_start_at
 * @property-read Collection|GlobalVariableRelationship[] $global_variable_relationships
 * @property-read int|null $global_variable_relationships_count
 * @property-read OAClient|null $oa_client
 * @property-read Collection|CommonTag[] $common_tags
 * @property-read int|null $common_tags_count
 * @property-read Collection|UserVariableRelationship[] $user_variable_relationships
 * @property-read int|null $correlations_count
 * @property-read Collection|Measurement[] $measurements
 * @property-read int|null $measurements_count
 * @property-read Collection|Study[] $studies
 * @property-read int|null $studies_count
 * @property-read Collection|ThirdPartyCorrelation[] $third_party_correlations
 * @property-read int|null $third_party_correlations_count
 * @property-read Collection|TrackingReminder[] $tracking_reminders
 * @property-read int|null $tracking_reminders_count
 * @property-read Collection|UserTag[] $user_tags
 * @property-read int|null $user_tags_count
 * @property-read Collection|UserVariable[] $user_variables
 * @property-read int|null $user_variables_count
 * @property-read Collection|User[] $users
 * @property-read int|null $users_count
 * @property-read Collection|Vote[] $votes
 * @property-read int|null $votes_count
 * @method static Builder|Variable whereEarliestNonTaggedMeasurementStartAt($value)
 * @method static Builder|Variable whereEarliestTaggedMeasurementStartAt($value)
 * @method static Builder|Variable whereLatestNonTaggedMeasurementStartAt($value)
 * @method static Builder|Variable whereLatestTaggedMeasurementStartAt($value)
 * @method static Builder mostNonTaggedMeasurements()
 * @property int|null $wp_post_id
 * @method static Builder|Variable whereWpPostId($value)
 * @property int|null $number_of_soft_deleted_measurements Formula: update variables v
 *                 inner join (
 *                     select measurements.variable_id, count(measurements.id) as number_of_soft_deleted_measurements
 *                     from measurements
 *                     where measurements.deleted_at is not null
 *                     group by measurements.variable_id
 *                     ) m on v.id = m.variable_id
 *                 set v.number_of_soft_deleted_measurements = m.number_of_soft_deleted_measurements
 * @method static Builder|Variable whereNumberOfSoftDeletedMeasurements($value)
 * @property mixed|null $charts
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|Variable whereCharts($value)
 * @method static most_tags() get variables with most tags
 * @property int|null $parent_id ID of the parent variable if this variable has any parent
 * @property int $creator_user_id
 * @property string|null $filling_type
 * @property-read Variable|null $best_cause_variable
 * @property-read Variable|null $best_effect_variable
 * @property-read Collection|CommonTag[] $common_tagged_by
 * @property-read int|null $common_tagged_by_count
 * @property-read Collection|UserVariableRelationship[] $individual_cause_studies
 * @property-read int|null $individual_cause_studies_count
 * @property-read Collection|UserVariableRelationship[] $individual_effect_studies
 * @property-read int|null $individual_effect_studies_count
 * @property-read Collection|GlobalVariableRelationship[] $population_cause_studies
 * @property-read int|null $population_cause_studies_count
 * @property-read Collection|GlobalVariableRelationship[] $population_effect_studies
 * @property-read int|null $population_effect_studies_count
 * @property-read Collection|TrackingReminderNotification[] $tracking_reminder_notifications
 * @property-read int|null $tracking_reminder_notifications_count
 * @property-read Unit $unit
 * @property-read Collection|UserTag[] $user_tagged_by
 * @property-read int|null $user_tagged_by_count
 * @property-read Collection|UserVariableClient[] $user_variable_clients
 * @property-read int|null $user_variable_clients_count
 * @property-read VariableCategory $variable_category
 * @property-read int|null $variable_user_sources_count
 * @property-read Collection|Variable[] $variables
 * @property-read int|null $variables_count
 * @property-read Collection|Vote[] $votes_where_cause
 * @property-read int|null $votes_where_cause_count
 * @property-read Collection|Vote[] $votes_where_effect
 * @property-read int|null $votes_where_effect_count
 * @property-read WpPost|null $wp_post
 * @method static Builder|Variable mostTags()
 * @method static Builder|Variable whereBestGlobalVariableRelationshipId($value)
 * @method static Builder|Variable whereCreatorUserId($value)
 * @method static Builder|Variable whereFillingType($value)
 * @property int|null $number_of_outcome_population_studies Number of Global Population Studies for this Cause
 *     Variable.
 *                 [Formula:
 *                     update variables
 *                         left join (
 *                             select count(id) as total, cause_variable_id
 *                             from global_variable_relationships
 *                             group by cause_variable_id
 *                         )
 *                         as grouped on variables.id = grouped.cause_variable_id
 *                     set variables.number_of_outcome_population_studies = count(grouped.total)
 *                 ]
 * @property int|null $number_of_predictor_population_studies Number of Global Population Studies for this Effect
 *     Variable.
 *                 [Formula:
 *                     update variables
 *                         left join (
 *                             select count(id) as total, effect_variable_id
 *                             from global_variable_relationships
 *                             group by effect_variable_id
 *                         )
 *                         as grouped on variables.id = grouped.effect_variable_id
 *                     set variables.number_of_predictor_population_studies = count(grouped.total)
 *                 ]
 * @property int|null $number_of_applications_where_outcome_variable Number of Applications for this Outcome Variable.
 *                 [Formula:
 *                     update variables
 *                         left join (
 *                             select count(id) as total, outcome_variable_id
 *                             from applications
 *                             group by outcome_variable_id
 *                         )
 *                         as grouped on variables.id = grouped.outcome_variable_id
 *                     set variables.number_of_applications_where_outcome_variable = count(grouped.total)
 *                 ]
 * @property int|null $number_of_applications_where_predictor_variable Number of Applications for this Predictor
 *     Variable.
 *                 [Formula:
 *                     update variables
 *                         left join (
 *                             select count(id) as total, predictor_variable_id
 *                             from applications
 *                             group by predictor_variable_id
 *                         )
 *                         as grouped on variables.id = grouped.predictor_variable_id
 *                     set variables.number_of_applications_where_predictor_variable = count(grouped.total)
 *                 ]
 * @property int|null $number_of_common_tags_where_tag_variable Number of Common Tags for this Tag Variable.
 *                 [Formula:
 *                     update variables
 *                         left join (
 *                             select count(id) as total, tag_variable_id
 *                             from common_tags
 *                             group by tag_variable_id
 *                         )
 *                         as grouped on variables.id = grouped.tag_variable_id
 *                     set variables.number_of_common_tags_where_tag_variable = count(grouped.total)
 *                 ]
 * @property int|null $number_of_common_tags_where_tagged_variable Number of Common Tags for this Tagged Variable.
 *                 [Formula:
 *                     update variables
 *                         left join (
 *                             select count(id) as total, tagged_variable_id
 *                             from common_tags
 *                             group by tagged_variable_id
 *                         )
 *                         as grouped on variables.id = grouped.tagged_variable_id
 *                     set variables.number_of_common_tags_where_tagged_variable = count(grouped.total)
 *                 ]
 * @property int|null $number_of_outcome_case_studies Number of Individual Case Studies for this Cause Variable.
 *                 [Formula:
 *                     update variables
 *                         left join (
 *                             select count(id) as total, cause_variable_id
 *                             from user_variable_relationships
 *                             group by cause_variable_id
 *                         )
 *                         as grouped on variables.id = grouped.cause_variable_id
 *                     set variables.number_of_outcome_case_studies = count(grouped.total)
 *                 ]
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|Variable whereNumberOfApplicationsWhereOutcomeVariable($value)
 * @method static Builder|Variable whereNumberOfApplicationsWherePredictorVariable($value)
 * @method static Builder|Variable whereNumberOfCommonTagsWhereTagVariable($value)
 * @method static Builder|Variable whereNumberOfCommonTagsWhereTaggedVariable($value)
 * @method static Builder|Variable whereNumberOfOutcomeCaseStudies($value)
 * @method static Builder|Variable whereNumberOfOutcomePopulationStudies($value)
 * @method static Builder|Variable whereNumberOfPredictorPopulationStudies($value)
 * @property-read Collection|GlobalVariableRelationship[] $global_variable_relationships_where_cause_variable
 * @property-read int|null $global_variable_relationships_where_cause_variable_count
 * @property-read Collection|GlobalVariableRelationship[] $global_variable_relationships_where_effect_variable
 * @property-read int|null $global_variable_relationships_where_effect_variable_count
 * @property-read Collection|Application[] $applications_where_outcome_variable
 * @property-read int|null $applications_where_outcome_variable_count
 * @property-read Collection|Application[] $applications_where_predictor_variable
 * @property-read int|null $applications_where_predictor_variable_count
 * @property-read Collection|CommonTag[] $common_tags_where_tag_variable
 * @property-read int|null $common_tags_where_tag_variable_count
 * @property-read Collection|CommonTag[] $common_tags_where_tagged_variable
 * @property-read int|null $common_tags_where_tagged_variable_count
 * @property-read Collection|CorrelationCausalityVote[] $correlation_causality_votes_where_cause_variable
 * @property-read int|null $correlation_causality_votes_where_cause_variable_count
 * @property-read Collection|CorrelationCausalityVote[] $correlation_causality_votes_where_effect_variable
 * @property-read int|null $correlation_causality_votes_where_effect_variable_count
 * @property-read Collection|CorrelationUsefulnessVote[] $correlation_usefulness_votes_where_cause_variable
 * @property-read int|null $correlation_usefulness_votes_where_cause_variable_count
 * @property-read Collection|CorrelationUsefulnessVote[] $correlation_usefulness_votes_where_effect_variable
 * @property-read int|null $correlation_usefulness_votes_where_effect_variable_count
 * @property-read Collection|UserVariableRelationship[] $correlations_where_cause_variable
 * @property-read int|null $correlations_where_cause_variable_count
 * @property-read Collection|UserVariableRelationship[] $correlations_where_effect_variable
 * @property-read int|null $correlations_where_effect_variable_count
 * @property-read Unit $default_unit
 * @property-read Collection|Study[] $studies_where_cause_variable
 * @property-read int|null $studies_where_cause_variable_count
 * @property-read Collection|Study[] $studies_where_effect_variable
 * @property-read int|null $studies_where_effect_variable_count
 * @property-read Collection|UserTag[] $user_tags_where_tag_variable
 * @property-read int|null $user_tags_where_tag_variable_count
 * @property-read Collection|UserTag[] $user_tags_where_tagged_variable
 * @property-read int|null $user_tags_where_tagged_variable_count
 * @property-read Collection|User[] $users_where_primary_outcome_variable
 * @property-read int|null $users_where_primary_outcome_variable_count
 * @property-read Collection|Variable[] $variables_where_best_cause_variable
 * @property-read int|null $variables_where_best_cause_variable_count
 * @property-read Collection|Variable[] $variables_where_best_effect_variable
 * @property-read int|null $variables_where_best_effect_variable_count
 * @property-read Collection|Vote[] $votes_where_cause_variable
 * @property-read int|null $votes_where_cause_variable_count
 * @property-read Collection|Vote[] $votes_where_effect_variable
 * @property-read int|null $votes_where_effect_variable_count
 * @property int|null $number_of_predictor_case_studies Number of Individual Case Studies for this Effect Variable.
 *                     [Formula: update variables
 *                         left join (
 *                             select count(id) as total, effect_variable_id
 *                             from user_variable_relationships
 *                             group by effect_variable_id
 *                         )
 *                         as grouped on variables.id = grouped.effect_variable_id
 *                     set variables.number_of_predictor_case_studies = count(grouped.total)]
 * @property int|null $number_of_measurements Number of Measurements for this Variable.
 *                     [Formula: update variables
 *                         left join (
 *                             select count(id) as total, variable_id
 *                             from measurements
 *                             group by variable_id
 *                         )
 *                         as grouped on variables.id = grouped.variable_id
 *                     set variables.number_of_measurements = count(grouped.total)]
 * @property int|null $number_of_studies_where_cause_variable Number of Studies for this Cause Variable.
 *                     [Formula: update variables
 *                         left join (
 *                             select count(id) as total, cause_variable_id
 *                             from studies
 *                             group by cause_variable_id
 *                         )
 *                         as grouped on variables.id = grouped.cause_variable_id
 *                     set variables.number_of_studies_where_cause_variable = count(grouped.total)]
 * @property int|null $number_of_studies_where_effect_variable Number of Studies for this Effect Variable.
 *                     [Formula: update variables
 *                         left join (
 *                             select count(id) as total, effect_variable_id
 *                             from studies
 *                             group by effect_variable_id
 *                         )
 *                         as grouped on variables.id = grouped.effect_variable_id
 *                     set variables.number_of_studies_where_effect_variable = count(grouped.total)]
 * @property int|null $number_of_tracking_reminder_notifications Number of Tracking Reminder Notifications for this
 *     Variable.
 *                     [Formula: update variables
 *                         left join (
 *                             select count(id) as total, variable_id
 *                             from tracking_reminder_notifications
 *                             group by variable_id
 *                         )
 *                         as grouped on variables.id = grouped.variable_id
 *                     set variables.number_of_tracking_reminder_notifications = count(grouped.total)]
 * @property int|null $number_of_user_tags_where_tag_variable Number of User Tags for this Tag Variable.
 *                     [Formula: update variables
 *                         left join (
 *                             select count(id) as total, tag_variable_id
 *                             from user_tags
 *                             group by tag_variable_id
 *                         )
 *                         as grouped on variables.id = grouped.tag_variable_id
 *                     set variables.number_of_user_tags_where_tag_variable = count(grouped.total)]
 * @property int|null $number_of_user_tags_where_tagged_variable Number of User Tags for this Tagged Variable.
 *                     [Formula: update variables
 *                         left join (
 *                             select count(id) as total, tagged_variable_id
 *                             from user_tags
 *                             group by tagged_variable_id
 *                         )
 *                         as grouped on variables.id = grouped.tagged_variable_id
 *                     set variables.number_of_user_tags_where_tagged_variable = count(grouped.total)]
 * @property int|null $number_of_variables_where_best_cause_variable Number of Variables for this Best Cause Variable.
 *                     [Formula: update variables
 *                         left join (
 *                             select count(id) as total, best_cause_variable_id
 *                             from variables
 *                             group by best_cause_variable_id
 *                         )
 *                         as grouped on variables.id = grouped.best_cause_variable_id
 *                     set variables.number_of_variables_where_best_cause_variable = count(grouped.total)]
 * @property int|null $number_of_variables_where_best_effect_variable Number of Variables for this Best Effect
 *     Variable.
 *                     [Formula: update variables
 *                         left join (
 *                             select count(id) as total, best_effect_variable_id
 *                             from variables
 *                             group by best_effect_variable_id
 *                         )
 *                         as grouped on variables.id = grouped.best_effect_variable_id
 *                     set variables.number_of_variables_where_best_effect_variable = count(grouped.total)]
 * @property int|null $number_of_votes_where_cause_variable Number of Votes for this Cause Variable.
 *                     [Formula: update variables
 *                         left join (
 *                             select count(id) as total, cause_variable_id
 *                             from votes
 *                             group by cause_variable_id
 *                         )
 *                         as grouped on variables.id = grouped.cause_variable_id
 *                     set variables.number_of_votes_where_cause_variable = count(grouped.total)]
 * @property int|null $number_of_votes_where_effect_variable Number of Votes for this Effect Variable.
 *                     [Formula: update variables
 *                         left join (
 *                             select count(id) as total, effect_variable_id
 *                             from votes
 *                             group by effect_variable_id
 *                         )
 *                         as grouped on variables.id = grouped.effect_variable_id
 *                     set variables.number_of_votes_where_effect_variable = count(grouped.total)]
 * @property int|null $number_of_users_where_primary_outcome_variable Number of Users for this Primary Outcome
 *     Variable.
 *                     [Formula: update variables
 *                         left join (
 *                             select count(ID) as total, primary_outcome_variable_id
 *                             from wp_users
 *                             group by primary_outcome_variable_id
 *                         )
 *                         as grouped on variables.id = grouped.primary_outcome_variable_id
 *                     set variables.number_of_users_where_primary_outcome_variable = count(grouped.total)]
 * @method static Builder|Variable whereNumberOfPredictorCaseStudies($value)
 * @method static Builder|Variable whereNumberOfStudiesWhereCauseVariable($value)
 * @method static Builder|Variable whereNumberOfStudiesWhereEffectVariable($value)
 * @method static Builder|Variable whereNumberOfTrackingReminderNotifications($value)
 * @method static Builder|Variable whereNumberOfUserTagsWhereTagVariable($value)
 * @method static Builder|Variable whereNumberOfUserTagsWhereTaggedVariable($value)
 * @method static Builder|Variable whereNumberOfUsersWherePrimaryOutcomeVariable($value)
 * @method static Builder|Variable whereNumberOfVariablesWhereBestCauseVariable($value)
 * @method static Builder|Variable whereNumberOfVariablesWhereBestEffectVariable($value)
 * @method static Builder|Variable whereNumberOfVotesWhereCauseVariable($value)
 * @method static Builder|Variable whereNumberOfVotesWhereEffectVariable($value)
 * @property string|null $deletion_reason The reason the variable was deleted.
 * @method static Builder|Variable whereDeletionReason($value)
 * @property float|null $maximum_allowed_daily_value The maximum allowed value in the default unit for measurements
 *     aggregated over a single day.
 * @property-read Collection|ActionEvent[] $actions
 * @property-read int|null $actions_count
 * @method static Builder|Variable whereMaximumAllowedDailyValue($value)
 * @property int|null $record_size_in_kb
 * @method static Builder|Variable whereRecordSizeInKb($value)
 * @property int|null $number_of_common_joined_variables Joined variables are duplicate variables measuring the same
 *     thing.
 * @property int|null $number_of_common_ingredients Measurements for this variable can be used to synthetically
 *     generate ingredient measurements.
 * @property int|null $number_of_common_foods Measurements for this ingredient variable can be synthetically generate
 *     by food measurements.
 * @property int|null $number_of_common_children Measurements for this parent category variable can be synthetically
 *     generated by measurements from its child variables.
 * @property int|null $number_of_common_parents Measurements for this parent category variable can be synthetically
 *     generated by measurements from its child variables.
 * @property int|null $number_of_user_joined_variables Joined variables are duplicate variables measuring the same
 *     thing. This only includes ones created by users.
 * @property int|null $number_of_user_ingredients Measurements for this variable can be used to synthetically generate
 *     ingredient measurements. This only includes ones created by users.
 * @property int|null $number_of_user_foods Measurements for this ingredient variable can be synthetically generate by
 *     food measurements. This only includes ones created by users.
 * @property int|null $number_of_user_children Measurements for this parent category variable can be synthetically
 *     generated by measurements from its child variables. This only includes ones created by users.
 * @property int|null $number_of_user_parents Measurements for this parent category variable can be synthetically
 *     generated by measurements from its child variables. This only includes ones created by users.
 * @property-read GlobalVariableRelationship|null $best_global_variable_relationship
 * @property-read Collection|User[] $favoriters
 * @property-read int|null $favoriters_count
 * @property-read Collection|\Overtrue\LaravelFavorite\Favorite[] $favorites
 * @property-read int|null $favorites_count
 * @property mixed $raw
 * @method static Builder|Variable whereEarliestMeasurementTime($value)
 * @method static Builder|Variable whereEarliestNonTaggedMeasurementTime($value)
 * @method static Builder|Variable whereEarliestTaggedMeasurementTime($value)
 * @method static Builder|Variable whereLatestMeasurementTime($value)
 * @method static Builder|Variable whereLatestNonTaggedMeasurementTime($value)
 * @method static Builder|Variable whereLatestTaggedMeasurementTime($value)
 * @method static Builder|Variable whereNumberOfCommonChildren($value)
 * @method static Builder|Variable whereNumberOfCommonFoods($value)
 * @method static Builder|Variable whereNumberOfCommonIngredients($value)
 * @method static Builder|Variable whereNumberOfCommonJoinedVariables($value)
 * @method static Builder|Variable whereNumberOfCommonParents($value)
 * @method static Builder|Variable whereNumberOfUserChildren($value)
 * @method static Builder|Variable whereNumberOfUserFoods($value)
 * @method static Builder|Variable whereNumberOfUserIngredients($value)
 * @method static Builder|Variable whereNumberOfUserJoinedVariables($value)
 * @method static Builder|Variable whereNumberOfUserParents($value)
 * @method static Builder|Variable whereNumberOfUserTags($value)
 * @method static Builder|Variable whereNumberUserTaggedBy($value)
 * @property-read Collection|CtConditionTreatment[] $condition_treatments
 * @property-read int|null $condition_treatments_count
 * @property-read Collection|CtTreatmentSideEffect[] $ct_treatment_side_effects_where_side_effect_variable
 * @property-read int|null $ct_treatment_side_effects_where_side_effect_variable_count
 * @property-read Collection|CtTreatmentSideEffect[] $ct_treatment_side_effects_where_treatment_variable
 * @property-read int|null $ct_treatment_side_effects_where_treatment_variable_count
 * @property-read CtgCondition|null $ctg_condition
 * @property-read CtgIntervention|null $ctg_intervention
 * @property-read Collection|Variable[] $side_effect_variables
 * @property-read int|null $side_effect_variables_count
 * @property-read Collection|CtTreatmentSideEffect[] $side_effects
 * @property-read int|null $side_effects_count
 * @property-read Collection|CtConditionCause[] $condition_causes_where_cause
 * @property-read int|null $condition_causes_where_cause_count
 * @property-read Collection|CtConditionCause[] $condition_causes_where_condition
 * @property-read int|null $condition_causes_where_condition_count
 * @property-read Collection|CtConditionTreatment[] $condition_treatments_where_condition
 * @property-read int|null $condition_treatments_where_condition_count
 * @property-read Collection|CtConditionTreatment[] $condition_treatments_where_treatment
 * @property-read int|null $condition_treatments_where_treatment_count
 * @property-read Collection|CtTreatmentSideEffect[] $treatment_side_effects_where_treatment
 * @property-read int|null $treatment_side_effects_where_treatment_count
 * @property bool|null $is_public
 * @property int $sort_order
 * @property int|null $is_goal The effect of a food on the severity of a symptom is useful because you can control the
 *     predictor directly. However, the effect of a symptom on the foods you eat is not very useful.  The foods you eat
 *     are not generally an objective end in themselves.
 * @property int|null $controllable You can control the foods you eat directly. However, symptom severity or weather is
 *     not directly controllable.
 * @property int|null $boring The variable is boring if the average person would not be interested in its causes or
 *     effects.
 * @property string|null $slug The slug is the part of a URL that identifies a page in human-readable keywords.
 * @property int|null $canonical_variable_id If a variable duplicates another but with a different name, set the
 *     canonical variable id to match the variable with the more appropriate name.  Then only the canonical variable
 *     will be displayed and all data for the duplicate variable will be included when fetching data for the canonical
 *     variable.
 * @property int|null $predictor predictor is true if the variable is a factor that could influence an outcome of
 *     interest
 * @property string|null $source_url URL for the website related to the database containing the info that was used to
 *     create this variable such as https://world.openfoodfacts.org or https://dsld.od.nih.gov/dsld
 * @property-read OAClient|null $client
 * @property-read User $creator_user
 * @property-read CtSideEffect|null $ct_side_effect
 * @property-read Connector|null $most_common_connector
 * @property-read Collection|GlobalVariableRelationship[] $outcomes
 * @property-read int|null $outcomes_count
 * @property-read Collection|GlobalVariableRelationship[] $predictors
 * @property-read int|null $predictors_count
 * @property-read Collection|GlobalVariableRelationship[] $publicOutcomes
 * @property-read int|null $public_outcomes_count
 * @property-read Collection|GlobalVariableRelationship[] $publicPredictors
 * @property-read int|null $public_predictors_count
 * @property Collection|Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read Collection|GlobalVariableRelationship[] $upVotedPublicOutcomes
 * @property-read int|null $up_voted_public_outcomes_count
 * @property-read Collection|GlobalVariableRelationship[] $upVotedPublicPredictors
 * @property-read int|null $up_voted_public_predictors_count
 * @property-read Collection|UserVariableOutcomeCategory[] $user_variable_outcome_categories
 * @property-read int|null $user_variable_outcome_categories_count
 * @property-read Collection|UserVariablePredictorCategory[] $user_variable_predictor_categories
 * @property-read int|null $user_variable_predictor_categories_count
 * @property-read Collection|UserVariable[] $user_variables_excluding_test_users
 * @property-read int|null $user_variables_excluding_test_users_count
 * @property-read Collection|VariableOutcomeCategory[] $variable_outcome_categories
 * @property-read int|null $variable_outcome_categories_count
 * @property-read Collection|VariablePredictorCategory[] $variable_predictor_categories
 * @property-read int|null $variable_predictor_categories_count
 * @method static Builder|Variable whereBoring($value)
 * @method static Builder|Variable whereCanonicalVariableId($value)
 * @method static Builder|Variable whereControllable($value)
 * @method static Builder|Variable whereIsGoal($value)
 * @method static Builder|Variable whereIsPublic($value)
 * @method static Builder|Variable wherePredictor($value)
 * @method static Builder|Variable whereSlug($value)
 * @method static Builder|Variable whereSortOrder($value)
 * @method static Builder|Variable whereSourceUrl($value)
 * @method static Builder|Variable withAllTags($tags, $type = null)
 * @method static Builder|Variable withAllTagsOfAnyType($tags)
 * @method static Builder|Variable withAnyTags($tags, $type = null)
 * @method static Builder|Variable withAnyTagsOfAnyType($tags)
 */
class Variable extends BaseVariable implements HasMedia {
    use HasFactory;
    use HasExceptions;
	use AnalyzableTrait;
	use HasErrors;
	use Favoriteable, HasDBModel;
	use HasCharts, HasFiles, PostableTrait;
	use HasVariable, HasVariableCategory, HasOutcomesAndPredictors, HasUnit, HasManyMeasurements; // Has Model(s)
	use HasName, HasSynonyms, HasOnsetAndDuration; // Has Attributes(s)
	use HasButton, IsEditable, LoggerTrait, SavesToRepo;
	public const ANALYZABLE           = true;
	public const CLASS_CATEGORY       = "Variables";
	public const CLASS_DESCRIPTION    = "Variable overviews with statistics, analysis settings, and data visualizations and likely outcomes or predictors based on the anonymously aggregated donated data. ";
	public const COLOR                = QMColor::HEX_BLUE;
	public const DEFAULT_IMAGE        = ImageUrls::SCIENCE_FLASK;
	public const DEFAULT_LIMIT        = 20;
	public const DEFAULT_ORDERINGS    = [self::FIELD_NUMBER_OF_USER_VARIABLES => self::ORDER_DIRECTION_DESC];
	public const DEFAULT_SEARCH_FIELD = self::FIELD_NAME;
	//use Cachable;
	// TODO: Synonym index and verify that name is always present public const DEFAULT_SEARCH_FIELD = self::FIELD_SYNONYMS;
	public const FONT_AWESOME         = 'fab fa-vimeo-v';
	public const IMPORTANT_FIELDS = self::FIELD_ID . ',' . self::FIELD_BEST_AGGREGATE_CORRELATION_ID . ',' .
	self::FIELD_BEST_CAUSE_VARIABLE_ID . ',' . self::FIELD_BEST_EFFECT_VARIABLE_ID . ',' . self::FIELD_CAUSE_ONLY .
	',' . self::FIELD_COMBINATION_OPERATION . ',' . self::FIELD_COMMON_ALIAS . ',' .
	self::FIELD_COMMON_MINIMUM_ALLOWED_DAILY_VALUE . ',' . self::FIELD_COMMON_MINIMUM_ALLOWED_NON_ZERO_VALUE . ',' .
	self::FIELD_DATA_SOURCES_COUNT . ',' . self::FIELD_DEFAULT_UNIT_ID . ',' . self::FIELD_DURATION_OF_ACTION . ',' .
	self::FIELD_DESCRIPTION . ',' . self::FIELD_FILLING_TYPE . ',' . self::FIELD_FILLING_VALUE . ',' .
	self::FIELD_IMAGE_URL . ',' . self::FIELD_INFORMATIONAL_URL . ',' . self::FIELD_ION_ICON . ',' .
	self::FIELD_MANUAL_TRACKING . ',' . self::FIELD_MAXIMUM_ALLOWED_DAILY_VALUE . ',' .
	self::FIELD_MAXIMUM_ALLOWED_VALUE . ',' . self::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS . ',' .
	self::FIELD_MINIMUM_ALLOWED_VALUE . ',' . self::FIELD_MINIMUM_RECORDED_VALUE . ',' . self::FIELD_NAME . ',' .
	self::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_CAUSE . ',' .
	self::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_EFFECT . ',' . self::FIELD_NUMBER_OF_MEASUREMENTS . ',' .
	self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN . ',' . self::FIELD_NUMBER_OF_USER_VARIABLES . ',' .
	self::FIELD_OPTIMAL_VALUE_MESSAGE . ',' . self::FIELD_ONSET_DELAY . ',' . self::FIELD_OUTCOME . ',' .
	self::FIELD_IS_PUBLIC . ',' . self::FIELD_SYNONYMS . ',' . self::FIELD_VALENCE . ',' .
	self::FIELD_VARIABLE_CATEGORY_ID;
	public const LARGE_FIELDS     = [
		self::FIELD_CHARTS,
		self::FIELD_ADDITIONAL_META_DATA,
	];
    const APPENDED_TITLE = 'title';
    /**
	 * The number of results to display in the global search.
	 * @var int
	 */
	public static $globalSearchResults = 10;
	/**
	 * Indicates if the resource should be globally searchable.
	 * @var bool
	 */
	public static $globallySearchable = true;
	public static $group = Variable::CLASS_CATEGORY;
	/**
	 * The per-page options used the resource index.
	 * @var array
	 */
	public static $perPageOptions = [10, 25, 50, 100];
	/**
	 * The number of resources to show per page via relationships.
	 * @var int
	 */
	public static $perPageViaRelationship = 20;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [
		Variable::FIELD_NAME,
	];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [];
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = Variable::FIELD_NAME;
	public $hidden = [
        self::FIELD_CREATOR_USER_ID,
        self::FIELD_CLIENT_ID,
    ];
	protected $casts = [
		self::FIELD_ADDITIONAL_META_DATA => 'array',
		self::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS => 'int',
		self::FIELD_BEST_CAUSE_VARIABLE_ID => 'int',
		self::FIELD_BEST_EFFECT_VARIABLE_ID => 'int',
		self::FIELD_BEST_AGGREGATE_CORRELATION_ID => 'int',
		self::FIELD_CAUSE_ONLY => 'bool',
		self::FIELD_CHARTS => 'object',
		self::FIELD_COMMON_MAXIMUM_ALLOWED_DAILY_VALUE => 'float',
		self::FIELD_COMMON_MINIMUM_ALLOWED_DAILY_VALUE => 'float',
		self::FIELD_COMMON_MINIMUM_ALLOWED_NON_ZERO_VALUE => 'float',
		self::FIELD_DATA_SOURCES_COUNT => 'array',
		self::FIELD_DEFAULT_UNIT_ID => 'int',
		self::FIELD_DEFAULT_VALUE => 'float',
		self::FIELD_DURATION_OF_ACTION => 'int',
		self::FIELD_FILLING_VALUE => 'float',
		self::FIELD_ID => 'int',
		self::FIELD_KURTOSIS => 'float',
		self::FIELD_MANUAL_TRACKING => 'bool',
		self::FIELD_MAXIMUM_ALLOWED_VALUE => 'float',
		self::FIELD_MAXIMUM_RECORDED_VALUE => 'float',
		self::FIELD_MEAN => 'float',
		self::FIELD_MEDIAN => 'float',
		self::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS => 'int',
		self::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => 'int',
		self::FIELD_MINIMUM_ALLOWED_VALUE => 'float',
		self::FIELD_MINIMUM_RECORDED_VALUE => 'float',
		self::FIELD_MOST_COMMON_CONNECTOR_ID => 'int',
		self::FIELD_MOST_COMMON_ORIGINAL_UNIT_ID => 'int',
		self::FIELD_MOST_COMMON_VALUE => 'float',
		self::FIELD_NUMBER_COMMON_TAGGED_BY => 'int',
		self::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_CAUSE => 'int',
		self::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_EFFECT => 'int',
		self::FIELD_NUMBER_OF_COMMON_TAGS => 'int',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'int',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN => 'int',
		self::FIELD_NUMBER_OF_TRACKING_REMINDERS => 'int',
		self::FIELD_NUMBER_OF_UNIQUE_VALUES => 'int',
		self::FIELD_NUMBER_OF_USER_VARIABLES => 'int',
		self::FIELD_ONSET_DELAY => 'int',
		self::FIELD_OUTCOME => 'bool',
		self::FIELD_PARENT_ID => 'int',
		self::FIELD_PRICE => 'float',
		self::FIELD_IS_PUBLIC => 'int',
		self::FIELD_SECOND_MOST_COMMON_VALUE => 'float',
		self::FIELD_SKEWNESS => 'float',
		self::FIELD_STANDARD_DEVIATION => 'float',
		self::FIELD_THIRD_MOST_COMMON_VALUE => 'float',
		self::FIELD_CREATOR_USER_ID => 'int',
		self::FIELD_VARIABLE_CATEGORY_ID => 'int',
		self::FIELD_VARIANCE => 'float',
		self::FIELD_SYNONYMS => 'array',
	];
	protected array $openApiSchema = [
		self::FIELD_ADDITIONAL_META_DATA => ['type' => 'object'],
		self::FIELD_DATA_SOURCES_COUNT => ['type' => 'array', 'items' => ['type' => 'object']],
		self::FIELD_SYNONYMS => ['type' => 'array', 'items' => ['type' => 'string']],
	];
	protected array $rules = [
		'analysis_ended_at' => 'nullable|datetime',
		'analysis_requested_at' => 'nullable|datetime',
		'analysis_settings_modified_at' => 'nullable|datetime',
		'analysis_started_at' => 'nullable|datetime',
		'average_seconds_between_measurements' => 'nullable|integer|min:-2147483648|max:2147483647',
		'best_cause_variable_id' => 'nullable|integer|min:1|max:2147483647',
		'best_effect_variable_id' => 'nullable|integer|min:1|max:2147483647',
		'brand_name' => 'nullable|max:125',
		'cause_only' => 'nullable|boolean',
		// Validation refuses to check as string // self::FIELD_CHARTS => 'nullable|max:204800',
		'client_id' => 'nullable|max:80',
		'combination_operation' => 'nullable',
		'common_alias' => 'nullable|max:125',
		'common_maximum_allowed_daily_value' => 'nullable|numeric',
		'common_minimum_allowed_daily_value' => 'nullable|numeric',
		'common_minimum_allowed_non_zero_value' => 'nullable|numeric',
		'default_value' => 'nullable|numeric',
		'description' => 'nullable|max:65535',
		'earliest_non_tagged_measurement_start_at' => 'nullable|datetime',
		'earliest_tagged_measurement_start_at' => 'nullable|datetime',
		'filling_value' => 'nullable|numeric',
		'informational_url' => 'nullable|max:2083',
		'internal_error_message' => 'nullable|max:255',
		'ion_icon' => 'nullable|max:40',
		'kurtosis' => 'nullable|numeric',
		'latest_non_tagged_measurement_start_at' => 'nullable|datetime',
		'latest_tagged_measurement_start_at' => 'nullable|datetime',
		'manual_tracking' => 'nullable|boolean',
		'maximum_allowed_value' => 'nullable|numeric',
		'maximum_recorded_value' => 'nullable|numeric',
		'mean' => 'nullable|numeric',
		'median' => 'nullable|numeric',
		'median_seconds_between_measurements' => 'nullable|integer|min:-2147483648|max:2147483647',
		'minimum_allowed_seconds_between_measurements' => 'nullable|integer|min:-2147483648|max:2147483647',
		'minimum_allowed_value' => 'nullable|numeric',
		'minimum_recorded_value' => 'nullable|numeric',
		'most_common_connector_id' => 'nullable|integer|min:0|max:2147483647',
		'most_common_original_unit_id' => 'nullable|integer|min:1|max:2147483647',
		'most_common_source_name' => 'nullable|max:255',
		'most_common_value' => 'nullable|numeric',
		'newest_data_at' => 'nullable|datetime',
		'number_common_tagged_by' => 'nullable|integer|min:0|max:2147483647',
		'number_of_global_variable_relationships_as_cause' => 'nullable|integer|min:0|max:2147483647',
		'number_of_global_variable_relationships_as_effect' => 'nullable|integer|min:0|max:2147483647',
		'number_of_common_tags' => 'nullable|integer|min:0|max:2147483647',
		'number_of_measurements' => 'nullable|integer|min:-2147483648|max:2147483647',
		'number_of_raw_measurements_with_tags_joins_children' => 'nullable|integer|min:0|max:2147483647',
		'number_of_tracking_reminders' => 'nullable|integer|min:-2147483648|max:2147483647',
		'number_of_unique_values' => 'nullable|integer|min:-2147483648|max:2147483647',
		'number_of_user_variables' => 'integer|min:0|max:2147483647',
		'onset_delay' => 'nullable|integer|min:0|max:2147483647',
		'optimal_value_message' => 'nullable|max:500',
		'outcome' => 'nullable|boolean',
		'parent_id' => 'nullable|integer|min:0|max:2147483647',
		'price' => 'nullable|numeric',
		'product_url' => 'nullable|max:2083',
		// Can't validate URL because it's false sometimes to avoid redundant checks
		self::FIELD_IS_PUBLIC => 'nullable|integer|min:0|max:1',
		'reason_for_analysis' => 'nullable|max:255',
		'second_most_common_value' => 'nullable|numeric',
		'skewness' => 'nullable|numeric',
		'standard_deviation' => 'nullable|numeric',
		'status' => 'nullable|max:25',
		'synonyms' => 'max:900',
		'third_most_common_value' => 'nullable|numeric',
		'upc_12' => 'nullable|max:255',
		'upc_14' => 'nullable|max:255',
		'user_error_message' => 'nullable|max:255',
		'valence' => 'nullable',
		'variable_category_id' => 'required|integer|min:1|max:300',
		'variance' => 'nullable|numeric',
		'wikipedia_title' => 'nullable|max:100',
		'wikipedia_url' => 'nullable|max:2083|url',
		//'image_url' => 'nullable|max:2083|url', // TODO: Stop using false in URL's when we can't get image from Amazon.  Maybe search other sources
		self::FIELD_BEST_AGGREGATE_CORRELATION_ID => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_DEFAULT_UNIT_ID => 'required|min:1|numeric',
		self::FIELD_DURATION_OF_ACTION => 'nullable|integer|min:0|max:7776000',
		self::FIELD_NAME => 'required|max:125|min:3',
		//|unique:variables,name', // Unique checks too slow
	];
	public static function chipSearchForCategory(int $variableCategoryId): string{
		$cat = QMVariableCategory::find($variableCategoryId);
		$qb = static::indexQBWithCorrelations();
		$qb->where(Variable::FIELD_VARIABLE_CATEGORY_ID, $variableCategoryId);
		$models = $qb->get();
		$b = static::getSearchAllIndexButton();
		$view = view('chip-search', [
			'buttons' => $models,
			'heading' => $heading ?? null,
			'placeholder' => "Search for a {$cat->getNameSingular()}...",
			'searchId' => $cat->getNameSingular(),
			'notFoundButtons' => [
				$b,
				OnboardingStateButton::instance(),
			],
		]);
		return HtmlHelper::renderView($view);
	}
	public static function getSearchAllIndexButton(): QMButton{
		$b = static::getIndexButton();
		$b->setTextAndTitle("Search All Variables");
		$b->setFontAwesome(FontAwesome::SEARCH_SOLID);
		$b->setFontAwesome(ImageUrls::BASIC_FLAT_ICONS_SEARCH);
		return $b;
	}
	/**
	 * @param string|int $nameOrId
	 * @return static|null
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function findByNameIdOrSynonym($nameOrId){
		$v = static::findByNameOrId($nameOrId);
		if(!$v && is_string($nameOrId)){
			$v = self::findBySynonym($nameOrId);
		}
		return $v;
	}
	/**
	 * @param array|null $variableCategoryIds
	 * @return QMButton[]
	 */
	public static function generateIndexButtonsForCategories(array $variableCategoryIds = null): array{
		return VariableButton::getWithStudies($variableCategoryIds);
	}
	/**
	 * @param Builder|\Illuminate\Database\Query\Builder $qb
	 */
	public static function interestingCategories($qb): void{
		self::onlyInterestingCategories($qb);
	}
	/**
	 * @param Builder|\Illuminate\Database\Query\Builder $qb
	 */
	public static function onlyInterestingCategories($qb): void{
		$qb->whereIn(Variable::FIELD_VARIABLE_CATEGORY_ID, VariableCategory::getInterestingCategoryIds());
	}
	/**
	 * @param $qb
	 * @param string $key
	 * @param $value
	 */
	public static function updateAttributeWhereNecessary($qb, string $key, $value){
		/** @var Variable[] $variables */
		$variables = $qb->where($key, "<>", $value)->get();
		foreach($variables as $v){
			$existing = $v->getAttribute($key);
			if($existing === $value){
				le('$existing === $value');
			}
			$v->logInfo("Changing $key from $existing to $value");
			$v->setAttribute($key, $value);
			try {
				$v->save();
			} catch (ModelValidationException $e) {
				le($e);
			}
		}
	}
	public function save(array $options = []): bool{
		if(!$this->synonyms){$this->calculateSynonyms();}
		$changes = $this->getDirty();
		if(!$this->status){
			$this->status = VariableStatusProperty::STATUS_WAITING;
		}
		if(isset($changes["name"])){ // Not sure why we're able to assign an empty string?
			try {
				VariableNameProperty::validateNew($changes["name"], $this->getQMUnit(), $this);
			} catch (InvalidVariableNameException | StupidVariableNameException $e) {
				le($e);
			}
		}
		$res = parent::save($options);
		if($changes){ // TODO: Do this for all BaseModels once tests are passing
			if($mem = $this->getDBModelFromMemory()){
				foreach($changes as $key => $value){
					/** @var BaseProperty $prop */
					if($prop = $this->getPropertyModel($key)){
						$value = $prop->decodeIfNecessary($value);
					}
					$mem->setAttribute($key, $value);
				}
			}
			if(EnvOverride::isLocal() && AppMode::isAstral()){
				$this->saveHardCodedModel();
			}
		}
		return $res;
	}

    /**
     * @return \Illuminate\Support\Collection|string[]
     */
    public static function names(): \Illuminate\Support\Collection
    {
        return static::select('name')->get()->pluck('name');
    }

    /**
	 * @param string $synonym
	 * @return void
	 */
	public function addSynonym(string $synonym): void {
		$synonyms = $this->getSynonymsAttribute();
		$synonyms[] = $synonym;
		$synonyms = array_unique($synonyms);
		$this->synonyms = $synonyms;
	}
	/**
	 * @param array|string $synonyms
	 * @return void
	 */
	public function setSynonymsAttribute($synonyms): void {
		$this->attributes[self::FIELD_SYNONYMS] = $this->asJson($synonyms);
		/** @var QMCommonVariable $dbm */
		$dbm = $this->getDBModelFromMemory();
		if($dbm){$dbm->synonyms = $synonyms;}
	}
	/**
	 * @return array
	 */
	public function calculateSynonyms(): array{  // Too slow to singularize all the time
		$toKeep = VariableSynonymsProperty::calculate($this);
		$this->setSynonymsAttribute($toKeep);
		return $toKeep;
	}
	public function getDirty(): array{
		if(isset($this->attributes[self::FIELD_STATUS]) && $this->attributes[self::FIELD_STATUS] === ""){
			$this->attributes[self::FIELD_STATUS] = null;
		}
		if(isset($this->original[self::FIELD_STATUS]) && $this->original[self::FIELD_STATUS] === ""){
			$this->original[self::FIELD_STATUS] = null;
		}
		return parent::getDirty();
	}
	/**
	 * @return Field[]
	 */
	public function getFields(): array{
		$fields = [];
		$fields[] = $this->imageField();
		$fields[] = $this->nameLinkToShowField();
		$fields = array_merge($fields, $this->getShowablePropertyFields());
		if($this->hasId()){
			if($this->isPredictor() !== false){
				$fields[] =
					GlobalVariableRelationshipBaseAstralResource::hasMany("Outcomes", 'global_variable_relationships_where_cause_variable');
			}
			if($this->isOutcome() !== false){
				$fields[] =
					GlobalVariableRelationshipBaseAstralResource::hasMany("Predictors", 'global_variable_relationships_where_effect_variable');
			}
		}
		return $fields;
	}
	/**
	 * @return bool
	 */
	public function isPredictor(): bool{
        $causeOnly = $this->getCauseOnly();
        if($causeOnly === true){
            return true;
        }
        $cat = $this->getQMVariableCategory();
        return $cat->getPredictor();
	}
	public function getCauseOnly(): ?bool{
		return $this->getAttributeFromVariableOrCategory(Variable::FIELD_CAUSE_ONLY);
	}
	/**
	 * @param string $attribute
	 * @return mixed|null
	 * Don't rename this or laravel will try to parse as an accessor
	 */
	public function getAttributeFromVariableOrCategory(string $attribute){
		$val = $this->getAttribute($attribute);
		if($val !== null){
			return $val;
		}
		return $this->getQMVariableCategory()->getAttribute($attribute);
	}
	public function getUUID(): ?string{
		return $this->attributes[self::FIELD_NAME] ?? null;
	}
	/**
	 * @param string|null $reason
	 * @return bool|null
	 */
	public function hardDeleteWithRelations(string $reason = null): ?bool{
		if(!$reason){
			$reason = __FUNCTION__;
		}
		$this->logError("Hard deleting because $reason");
		$userVariables = $this->user_variables;
		$userVariables->each(function($v) use ($reason){
			/** @var UserVariable $v */
			$v->hardDeleteWithRelations($reason);
		});
		GlobalVariableRelationship::whereCauseVariableId($this->id)->forceDelete();
		GlobalVariableRelationship::whereEffectVariableId($this->id)->forceDelete();
		Vote::whereCauseVariableId($this->id)->forceDelete();
		Vote::whereEffectVariableId($this->id)->forceDelete();
		Study::whereCauseVariableId($this->id)->forceDelete();
		Study::whereEffectVariableId($this->id)->forceDelete();
		if($post = $this->wp_post){
			$post->hardDeleteWithRelations($reason);
		}
		return parent::forceDelete();
	}
	public function getUniqueNamesSlug(): string{
		return QMStr::slugify($this->name);
	}
	/**
	 * @param null $reason
	 * @return bool|null
	 */
	public function forceDelete($reason = null): ?bool{
		try {
			return $this->hardDeleteWithRelations($reason);
		} catch (\Throwable $e) {
			if(AppMode::isAstral()){ // Don't throw in astral so at least other deletions can complete if possible
				$msg = "Could not delete $this->name because:\n" . $e->getMessage();
				QMLog::error($msg);
				return false;
			} else{
				le($e, $this);
				throw new \LogicException();
			}
		}
	}
	public function getUpdatedAt(): ?string{
		return $this->attributes[Variable::FIELD_UPDATED_AT] ?? null;
	}
	/**
	 * Get the indexable data array for the model.
	 * @return array
	 */
	public function toSearchableArray(): array{
		$arr = parent::toSearchableArray();
		$arr[self::FIELD_IS_PUBLIC] = $this->public;
		$arr[self::FIELD_COMMON_ALIAS] = $this->common_alias;
		$arr[self::FIELD_SYNONYMS] = $this->synonyms_string();
		return $arr;
	}
	public function synonyms_string(): string{
		$s = $this->getSynonymsAttribute();
		if(!$s){
			$this->logError("No synonyms!  We should at least have one for the variable name for search purposes!");
			$s = $this->addSynonymsAndSave([
				$this->name,
				$this->getTitleAttribute(),
				$this->common_alias,
			]);
		}
		return implode(", ", $s);
	}
	public static function whereNameLike(string $pattern): Builder{
        if(!str_contains($pattern, '%')){
            $pattern = "%$pattern%";
        }
		return static::whereLike(self::FIELD_NAME, $pattern);
	}
	/**
	 * @param string $synonym
	 * @return Variable|Builder
	 */
	public static function whereSynonymsLike(string $synonym){
		return self::query()->where(self::FIELD_SYNONYMS, \App\Storage\DB\ReadonlyDB::like(), '%' . $synonym . '%');
	}
	/**
	 * @return Builder
	 */
	public static function withTheMostNonTaggedMeasurements(): Builder{
		$qb = self::query()->mostNonTaggedMeasurements();
		return $qb;
	}
	public static function assertNoInvalidRecords(): void{
		VariableManualTrackingProperty::assertNoInvalidRecords();
		VariableIsPublicProperty::assertNoInvalidRecords();
	}
	/**
	 * @return string
	 */
	public function __toString(){
		return $this->name;
	}
	/**
	 * @param array|null $meta
	 * @return array
	 */
	public function getLogMetaData(?array $meta = []): array{
		$meta['Name'] = $this->name;
		$meta['Measurements'] = $this->number_of_measurements;
		$meta['Users'] = $this->number_of_user_variables;
		$meta['Deleted'] = $this->deleted_at;
//		if($this->hasValidId()){
//			$meta['EDIT'] = $this->getEditUrl();
//			$meta['SHOW'] = $this->getUrl();
//		}
		return $meta;
	}
	public function getEditUrl(): string{
		return $this->getSettingsButton()->getUrl();
	}
	public function getUrlParams(): array{
		return [
			UserVariable::FIELD_VARIABLE_ID => $this->getVariableId(),
			Variable::FIELD_DEFAULT_UNIT_ID => $this->getUnitIdAttribute(),
			Variable::FIELD_VARIABLE_CATEGORY_ID => $this->getVariableCategoryId(),
			'image' => $this->getImage(),
			'display_name' => $this->getTitleAttribute(),
			'variable_name' => $this->getNameAttribute(),
		];
	}
	public function getVariableId(): int{
		return $this->id;
	}
	public function getImage(): string{
		if($this->attributes){
			$img = $this->attributes[Variable::FIELD_IMAGE_URL] ?? $this->getQMVariableCategory()->getImage();
		} else{
			$img = static::DEFAULT_IMAGE;
		}
		return $img;
	}
	/**
	 * @param string $ingredientName
	 * @param float $ingredientValue
	 * @param string $ingredientVariableCategoryName
	 * @param string|null $ingredientUnitName
	 * @param string|null $clientId
	 * @throws InvalidTagCategoriesException
	 * @throws InvalidVariableValueException
	 * @throws IncompatibleUnitException
	 */
	public function addIngredientTag(string $ingredientName, float $ingredientValue,
		string $ingredientVariableCategoryName, string $ingredientUnitName = null, string $clientId = null){
		if(!$ingredientUnitName){
			$ingredientUnitName = UnitNameProperty::fromString($ingredientName);
		}
		$ingredientUnit = QMUnit::getByNameOrId($ingredientUnitName);
		$ingredientName = VariableNameProperty::removeValueAndUnit($ingredientName);
		$ingredientName = VariableNameProperty::sanitizeSlow($ingredientName, $ingredientUnit);
		$ingredientVariable = self::findOrCreateByName($ingredientName,
			['variableCategoryName' => $ingredientVariableCategoryName, 'unitName' => $ingredientUnitName]);
		if($ingredientVariable->getVariableId() === $this->id){
			$this->logError("Not adding $ingredientVariable as an ingredient because it's the same variable");
			return;
		}
		if($ingredientVariable->getCommonUnit()->getAbbreviatedName() !== $ingredientUnitName){
			$ingredientValue = $ingredientVariable->convertValueToDefaultUnit($ingredientValue, $ingredientUnitName);
		}
		$ingredientVariable->updateVariableCategoryIfCurrentIsStupidAndDifferent($ingredientVariableCategoryName);
		if($ingredientVariableCategoryName === FoodsVariableCategory::NAME ||
			$ingredientVariableCategoryName === NutrientsVariableCategory::NAME){
			$this->updateVariableCategoryIfCurrentIsStupidAndDifferent(FoodsVariableCategory::NAME);
		}
		$existingIngredients = $this->getIngredientCommonTagVariables();
		foreach($existingIngredients as $existingIngredient){
			if($existingIngredient->getId() === $ingredientVariable->getVariableId()){
				$this->logInfo("$existingIngredient Already tagged with $ingredientValue $ingredientUnitName per " .
					$this->getUnitAbbreviatedName());
				return;
			}
		}
		$this->logInfo("Adding ingredient $ingredientVariable with $ingredientValue $ingredientUnitName per " .
			$this->getUnitAbbreviatedName());
		try {
			$result = QMCommonTag::addIngredientTag($ingredientVariable->getVariableId(), $this->getVariableId(),
				$ingredientValue, $clientId);
		} catch (InvalidTagCategoriesException $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());
			$ingredientVariable->updateVariableCategoryIfCurrentIsStupidAndDifferent($ingredientVariableCategoryName);
			$result = QMCommonTag::addIngredientTag($ingredientVariable->getVariableId(), $this->getVariableId(),
				$ingredientValue, $clientId);
		}
		if($result && $this->getDBModel()->commonTagVariables !== null){
			le("commonTagVariables should have been unset!");
		}
	}
	public static function findOrCreateByName(string $name, array $data): Variable{
		$v = static::findByName($name);
		if(!$v){
			$data[self::FIELD_NAME] = $name;
            if(!isset($data[self::FIELD_CREATOR_USER_ID])){
                try {
                    $data[self::FIELD_CREATOR_USER_ID] = QMAuth::id();
                } catch (\Throwable $e) {
                    $data[self::FIELD_CREATOR_USER_ID] = UserIdProperty::USER_ID_SYSTEM;
                }
            }
			$v = static::upsertOne($data);
		}
		return $v;
	}
	/**
	 * @param $value
	 * @param int|string $currentUnitNameOrId
	 * @return float
	 * @throws InvalidVariableValueException
	 * @throws IncompatibleUnitException
	 */
	public function convertValueToDefaultUnit($value, $currentUnitNameOrId): float{
		$currentUnit = QMUnit::getByNameOrId($currentUnitNameOrId);
		return QMUnit::convertValueByUnitIds($value, $currentUnit->getId(), $this->getUnitIdAttribute(), $this);
	}
	/**
	 * @param int|string $nameOrId
	 * @return Variable|null
	 * @throws CommonVariableNotFoundException
	 */
	public static function findByNameOrId($nameOrId): ?Variable{
		if(is_numeric($nameOrId)){
			/** @var Variable $variable */
			$variable = Variable::findInMemoryOrDB($nameOrId);
		} elseif(is_string($nameOrId)){
			$variable = Variable::findByName($nameOrId);
		} else{
			throw new \LogicException("findByNameOrId requires string or int!  got: " . \App\Logging\QMLog::print_r($nameOrId, true));
		}
		if($variable){
			$variable->addToMemory();
		}
		return $variable;
	}
	/**
	 * @param string $submittedVariableCategoryName
	 * @throws VariableCategoryNotFoundException
	 */
	public function updateVariableCategoryIfCurrentIsStupidAndDifferent(string $submittedVariableCategoryName){
		$submitted = QMVariableCategory::find($submittedVariableCategoryName);
		$current = $this->getQMVariableCategory();
		$currentName = $current->getNameAttribute();
		if($currentName === CausesOfIllnessVariableCategory::NAME){
			$this->logInfo("Causes of illness");
		}
		if($currentName !== $submittedVariableCategoryName && $current->isStupidCategory()){
			$this->changeVariableCategory($submitted->getId(), "$currentName is stupid");
		}
	}
	/**
	 * @param $nameOrId
	 * @return Variable|null
	 */
	public static function findByNameLikeOrId($nameOrId): ?Variable{
		$v = static::findByNameOrId($nameOrId);
		if(!$v && is_string($nameOrId)){
			$v = Variable::whereLike(Variable::FIELD_NAME, '%' . $nameOrId . '%')->first();
		}
		return $v;
	}
	/**
	 * @throws TooSlowToAnalyzeException
	 */
	public function analyzeCommonAndUserVariablesIfNecessary(){
		$this->analyzeUserVariables();
		$this->analyze();
	}
	/**
	 * @param bool $excludeDeletedAndTestUsers
	 * @return Collection
	 * @throws TooSlowToAnalyzeException
	 */
	public function analyzeUserVariables(bool $excludeDeletedAndTestUsers = true): Collection{
		$arr = $this->user_variables($excludeDeletedAndTestUsers)->get();
		/** @var UserVariable $uv */
		foreach($arr as $uv){
			$uv->analyzeIfNecessary(__FUNCTION__);
		}
		return $arr;
	}
	public function user_variables(bool $excludeDeletedAndTestUsers = true): HasMany{
		$qb = $this->hasMany(UserVariable::class, UserVariable::FIELD_VARIABLE_ID, UserVariable::FIELD_ID);
		if($excludeDeletedAndTestUsers){
			UserIdProperty::excludeDeletedAndTestUsers($qb);
		}
		return $qb;
	}
	public function assertHasStatusAttribute(){
		$attr = $this->attributes;
		if($this->exists && isset($attr[self::FIELD_ID]) && !array_key_exists(static::FIELD_STATUS, $attr)){
			le("$this does not have status attribute! ");
		}
	}
	/**
	 * @return BelongsTo
	 */
	public function category(): BelongsTo{
		return $this->belongsTo(VariableCategory::class, 'variable_category_id');
	}
	/**
	 * @return Variable[]
	 */
	public function cause_variables_where_condition(): array{
		$variables = [];
		/** @var CtConditionCause[] $condition_causes */
		$condition_causes = $this->condition_causes_where_condition()->get();
		foreach($condition_causes as $ct){
			$v = $ct->cause_variable;
			$variables[$v->name] = $v;
		}
		return $variables;
	}
	/**
	 * @return HasMany
	 */
	public function condition_causes_where_condition(): HasMany{
		return $this->hasMany(CtConditionCause::class, CtConditionCause::FIELD_CONDITION_VARIABLE_ID,
			Variable::FIELD_ID)->orderBy(CtConditionCause::FIELD_VOTES_PERCENT, 'desc')->with('cause_variable');
	}
	public function changeName(string $newName){
		QMLog::error("Changing variable name from $this->name to $newName");
		$this->addSynonymsAndSave($this->name);
		$this->name = $newName;
		try {
			$this->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
	}
	public function cleanup(): array{
		$v = $this->getDBModel();
		$v->cleanup();
		return $this->analyze();
	}
	/** @noinspection PhpUnused */
	/**
	 * @return QMCommonVariable
	 */
	public function getDBModel(): DBModel{
		if($dbm = $this->getDBModelFromMemory()){
			//if($dirty = $this->getDirty()){$this->logDebug("Should we be populating changes here?");}
			return $dbm;
		}
		$dbm = new QMCommonVariable();
		$dbm->populateByLaravelModel($this);
		$dbm->setVariableCategory($this->getVariableCategoryId());
		if(is_string($dbm->synonyms)){
			le("synonyms is string");
		}
		$dbm->populateDefaultFields();
		if($this->hasId()){
			$attributes = $this->attributes;
			$fields = static::getColumns();
			if(count($attributes) >= count($fields)){ // Don't add partials to memory
				$dbm->addToMemory();
			}
		}
		return $dbm;
	}
	public function getVariableCategoryId(): int{
		if(!isset($this->attributes[self::FIELD_VARIABLE_CATEGORY_ID])){
			le("no cat");
		}
		return $this->attributes[self::FIELD_VARIABLE_CATEGORY_ID];
	}
	/**
	 * @return array
	 */
	public function analyze(): array{
		$v = $this->getDBModel();
		try {
			$v->analyzeFully(__FUNCTION__);
		} catch (AlreadyAnalyzedException | AlreadyAnalyzingException | ModelValidationException $e) {
			le($e);
		}
		return $v->getAnalysisResults();
	}
	/**
	 * @param string $primaryModelTable
	 * @param bool $implode
	 * @return array|string
	 */
	public static function getColumnsForSmallFieldsNotIn(string $primaryModelTable, bool $implode){
		$arr = parent::getColumnsForSmallFieldsNotIn($primaryModelTable, false);
		$arr[] = self::FIELD_VARIABLE_CATEGORY_ID;
		if($implode){
			return implode(',', $arr);
		}
		return $arr;
	}
	public static function getSlimClass(): string{ return QMCommonVariable::class; }
	/**
	 * Get the actions available for the resource.
	 * @param Request $request
	 * @return array
	 */
	public function getActions(Request $request): array{
		$actions = parent::getActions($request);
		$actions[] = new CorrelateAction($request);
		$actions[] = new FavoriteAction($request);
		$actions[] = new UnFavoriteAction($request);
		if(QMAuth::isAdmin()){
			$actions[] = new SetPublicAction($request);
			$actions[] = new DeleteWithRelationsAction($request);
		}
		return $actions;
	}
	public function getDescriptionHtml(): string{
		if(QMAuth::getQMUser()){
			$name = $this->getTitleAttribute();
			return "
                <div>
                    This is aggregated data. See your $name data at
                </div>
            ";
		}
		return parent::getDescriptionHtml();
	}
	/**
	 * Get the lenses available for the resource.
	 * @param Request $request
	 * @return array
	 */
	public function getLenses(Request $request): array{
		$lenses = parent::getLenses($request);
		if(QMAuth::isAdmin()){
			$lenses[] = new EconomicIndicatorsLens($this);
			$lenses[] = new StrategyVariablesLens($this);
		}
		$lenses[] = new FavoritesLens();
		return $lenses;
	}
	public function common_tagged_by(): HasMany{
		return $this->hasMany(CommonTag::class, CommonTag::FIELD_TAG_VARIABLE_ID);
	}
	public function common_tags(): HasMany{
		return $this->hasMany(CommonTag::class, CommonTag::FIELD_TAGGED_VARIABLE_ID);
	}
	public function condition_variables_where_cause(): array{
		$variables = [];
		/** @var CtConditionCause[] $condition_causes */
		$condition_causes = $this->condition_causes_where_cause()->get();
		foreach($condition_causes as $ct){
			$v = $ct->condition_variable;
			$variables[$v->name] = $v;
		}
		return $variables;
	}
	public static function newFake(int $userId = UserIdProperty::USER_ID_TEST_USER): BaseModel{
		return OverallMoodCommonVariable::instance()->l();
	}
	/**
	 * @return HasMany
	 */
	public function condition_causes_where_cause(): HasMany{
		return $this->hasMany(CtConditionCause::class, CtConditionCause::FIELD_CAUSE_VARIABLE_ID, Variable::FIELD_ID)
			->orderBy(CtConditionCause::FIELD_VOTES_PERCENT, 'desc')->with('condition_variable');
	}
	public function condition_variables_where_treatment(): array{
		$variables = [];
		/** @var CtConditionTreatment[] $condition_treatments */
		$condition_treatments = $this->condition_treatments_where_treatment()->get();
		foreach($condition_treatments as $ct){
			$v = $ct->condition_variable;
			$variables[$v->name] = $v;
		}
		return $variables;
	}
	/**
	 * @return HasMany
	 */
	public function condition_treatments_where_treatment(): HasMany{
		return $this->hasMany(CtConditionTreatment::class, CtConditionTreatment::FIELD_TREATMENT_VARIABLE_ID,
			Variable::FIELD_ID)->with('condition_variable');
	}
	/**
	 * @param $value
	 * @param QMUnit|string|int $unit
	 * @return float|null
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function convertToCommonUnit($value, $unit): float{
		$commonUnit = $this->getCommonUnit();
		$converted = QMUnit::convertValue($value, $unit, $this->getQMUnit(), $this);
		if($converted === null){
			$exp = \App\Logging\QMLog::print_r($value, true);
			le("Could not convert $exp from $unit to $commonUnit");
		}
		return $converted;
	}
	public function getCommonUnit(): QMUnit{
		return $this->getQMUnit();
	}
	/**
	 * @return QMUserVariableRelationship[]
	 * @throws TooSlowToAnalyzeException
	 */
	public function correlate(): array{
		$dbm = $this->getQMUserVariables();
		$all = [];
		foreach($dbm as $uv){
			$correlations = $all[$uv->getUserId()] = $uv->correlate();
			foreach($correlations as $c){
				$c->getOrCreateQMGlobalVariableRelationship();
			}
		}
		return $all;
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function getQMUserVariables(): array{
		return $this->getDBModel()->getQMUserVariables();
	}
	public function creator_user(): BelongsTo{
		return $this->belongsTo(User::class, Variable::FIELD_CREATOR_USER_ID, User::FIELD_ID,
			Variable::FIELD_CREATOR_USER_ID);
	}
	public function data_sources_count_string(): string{
		$count = $this->data_sources_count;
		if(!$count){
			return "No Data Sources Found";
		}
		return implode(", ", $count);
	}
	/**
	 * @return BelongsTo
	 */
	public function defaultUnit(): BelongsTo{
		return $this->belongsTo(Unit::class, 'default_unit_id');
	}
	public function exceptionIfWeShouldNotPost(): void{
		if($this->isStupidVariable()){
			le("Not posting stupid variable " . $this->getTitleAttribute());
		}
		if($this->getNumberOfGlobalVariableRelationships()){
			return;
		}
		if($this->getNumberOfRawMeasurementsWithTagsJoinsChildren()){
			return;
		}
		le("Not posting because no user_variable_relationships or measurements for " . $this->getTitleAttribute());
	}
	/**
	 * @return int
	 */
	public function getNumberOfGlobalVariableRelationships(): ?int{
		return $this->getNumberOfGlobalVariableRelationshipsAsCause() + $this->getNumberOfGlobalVariableRelationshipsAsEffect();
	}
	/**
	 * @return int
	 */
	public function getNumberOfGlobalVariableRelationshipsAsCause(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_CAUSE] ?? null;
	}
	/**
	 * @return int
	 */
	public function getNumberOfGlobalVariableRelationshipsAsEffect(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_EFFECT] ?? null;
	}
	/**
	 * @param $data
	 * @return static
	 */
	public static function new($data): self {
		$m = parent::new($data);
        if(!$m->creator_user_id) {
            if (AppMode::isApiRequest()) {
                $m->creator_user_id = QMAuth::id();
            } else {
                $m->creator_user_id = UserIdProperty::USER_ID_SYSTEM;
            }
        }
		$m->calculateSynonyms();
		return $m;
	}
	/**
	 * @return string[]
	 */
	public function getSynonymsAttribute(): array{
		$str = $this->attributes[self::FIELD_SYNONYMS] ?? [];
		if(!$str){return [];}
		return VariableSynonymsProperty::decodeOrFallbackToName($str, $this);
	}
	/**
	 * @param $data
	 * @return Variable
	 */
	public static function fromForeignData($data): BaseModel{
		$id = MeasurementVariableIdProperty::pluck($data);
		if(!$id){
			$name = VariableNameProperty::pluckOrDefault($data);
			if(!$name){
				throw new BadRequestException("Please provide variable name or id.");
			}
		}
		/** @var Variable $v */
		$v = self::findByData($data);
		if(!$v){
			$model = static::new($data);
			try {
				$model->save();
			} catch (ModelValidationException $e) {
				le($e);
			}
			return $model;
		}
		if($unitId = UserVariableDefaultUnitIdProperty::pluckOrDefault($data)){
			if(QMUnit::unitIsIncompatible($v->default_unit_id, $unitId)){
				return self::withUnitInName($data, $v);
			}
		}
		return $v;
	}
	public function getNumberOfRawMeasurementsWithTagsJoinsChildren(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN] ?? null;
	}
	/**
	 * @param array|object $body
	 * @param Variable $v
	 * @return Variable
	 */
	public static function withUnitInName($body, Variable $v): Variable{
		QMUserVariable::flushAllFromMemory();
		QMCommonVariable::flushAllFromMemory();
		$newUnit = BaseUnitIdProperty::pluckParentDBModel($body);
		$nameWithUnit = VariableNameProperty::withUnit($v->name, $newUnit);
		if($existingVariableWithUnitInName = Variable::findByName($nameWithUnit)){
			return $existingVariableWithUnitInName;
		}
		$v = new Variable();
		$v->client_id = BaseClientIdProperty::fromDataOrRequest($body);
		$v->creator_user_id = BaseUserIdProperty::pluckOrDefault($body);
		$v->default_unit_id = $newUnit->id;
		$v->number_of_user_variables = 1;
		$v->name = $nameWithUnit;
		$v->is_public = 0;
		$v->variable_category_id = VariableVariableCategoryIdProperty::pluckOrDefault($body);
		try {
			$v->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
		return $v;
	}
	/** @noinspection PhpUnused */
	public function findUserVariable(int $userId): ?UserVariable{
		return UserVariable::findByVariableId($this->getId(), $userId);
	}

	public function getAdditionalMetaData(): ?string{
		return $this->attributes[Variable::FIELD_ADDITIONAL_META_DATA] ?? null;
	}
	/**
	 * @return array
	 */
	public function getAdminButtons(): array{
		$buttons = parent::getDataLabModelButtons();
		$currentValue = $this->getAttribute(self::FIELD_IMAGE_URL);
		if($currentValue !== null){
			$buttons[] = $this->getDeleteValueButton(self::FIELD_IMAGE_URL);
		}
		$buttons[] = $this->getChangeValueButton(self::FIELD_IMAGE_URL);
		return $buttons;
	}
	public function getAnalysisRequestedAt(): ?string{
		return $this->attributes[Variable::FIELD_ANALYSIS_REQUESTED_AT] ?? null;
	}
	public function getAnalysisStartedAt(): ?string{
		return $this->attributes[Variable::FIELD_ANALYSIS_STARTED_AT] ?? null;
	}
	public function getAverage(): float{
		return $this->getDBModel()->getAverage();
	}
	public function getAverageSecondsBetweenMeasurements(): ?int{
		return $this->attributes[Variable::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS] ?? null;
	}
	/**
	 * @param $data
	 * @return BaseModel|null
	 */
	public static function findByData($data): ?BaseModel{
		$name = VariableNameProperty::pluck($data);
		if($name){
			if($fromMem = self::findInMemoryByName($name)){
				return $fromMem;
			}
		}
		return parent::findByData($data);
	}
	public function getBadgeText(): ?string{
		return $this->getNumberOfGlobalVariableRelationships();
	}
	public static function findInMemoryByName(string $name): ?Variable{
		return static::getFromClassMemory($name);
	}
	/**
	 * @return GlobalVariableRelationship
	 */
	public function getBestGlobalVariableRelationshipAsCause(): ?GlobalVariableRelationship{
		$correlations = $this->getGlobalVariableRelationshipsAsCause(1);
		if(!$correlations){
			return null;
		}
		return $correlations->first();
	}
	public function addToMemory(): void{
		parent::addToMemory();
		static::setInClassMemory($this->getNameAttribute(), $this);
	}
	/**
	 * @return GlobalVariableRelationship
	 */
	public function getBestGlobalVariableRelationshipAsEffect(): ?GlobalVariableRelationship{
		return $this->getGlobalVariableRelationshipsAsEffect(1)->first();
	}
    public static function whereName(string $value): Builder{
        // Case-insensitive
        return static::query()->where(self::FIELD_NAME, Writable::like(),  $value);
    }
	/**
	 * @param string $name
	 * @return Variable|null
     */
	public static function findByName(string $name): ?Variable
    {
		$name = Variable::fromSlug($name);
		$v = static::findInMemoryByName($name);
		if(!$v){
			$qb = Variable::whereName($name);
			$v = $qb->first();
		}
		if($v){
			$v->addToMemory();
		}
		return $v;
	}
	/**
	 * @param int|null $limit
	 * @param string|int|null $causeCategory
	 * @return GlobalVariableRelationship[]|\Illuminate\Support\Collection
	 */
	public function getGlobalVariableRelationshipsAsEffect(int $limit = null, string $causeCategory = null): Collection{
		$catId = (!$causeCategory) ? null : VariableCategoryIdProperty::pluck($causeCategory);
		$correlations = $this->relations[__FUNCTION__]["$catId-$limit"] ?? null;
		if($correlations !== null){return $correlations;}
		$qb = $this->global_variable_relationships_where_effect_variable();
		if($catId){$qb->where(UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID, $catId);}
		if($limit){$qb->limit($limit);}
		if(!$limit && !$causeCategory){
			$count = $qb->count();
			if($count > 1000){
				$qb->whereIn(UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
					VariableCategory::getInterestingCategoryIds());
			}
		}
		GlobalVariableRelationship::applyDefaultOrderings($qb);
		$correlations = $qb->get();
		$count = $correlations->count();
		if($this->number_of_global_variable_relationships_as_effect < $count){
			$this->number_of_global_variable_relationships_as_effect = $count;
		}
		return $this->relations[__FUNCTION__]["$catId-$limit"] = $correlations;
	}
	/**
	 * @param int|null $limit
	 * @param string|int|null $effectCategory
	 * @return GlobalVariableRelationship[]|\Illuminate\Support\Collection
	 */
	public function getGlobalVariableRelationshipsAsCause(int $limit = null, $effectCategory = null): Collection{
		$catId = (!$effectCategory) ? null : VariableCategoryIdProperty::pluck($effectCategory);
		$correlations = $this->relations[__FUNCTION__]["$catId-$limit"] ?? null;
		if($correlations !== null){return $correlations;}
		$qb = $this->global_variable_relationships_where_cause_variable();
		if($catId){$qb->where(UserVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID, $catId);}
		if($limit){$qb->limit($limit);}
		GlobalVariableRelationship::applyDefaultOrderings($qb);
		$correlations = $qb->get();
		$count = $correlations->count();
		if($this->number_of_global_variable_relationships_as_cause < $count){
			$this->number_of_global_variable_relationships_as_cause = $count;
		}
		return $this->relations[__FUNCTION__]["$catId-$limit"] = $correlations;
	}
	public function getBestGlobalVariableRelationshipId(): ?int{
		return $this->attributes[Variable::FIELD_BEST_AGGREGATE_CORRELATION_ID] ?? null;
	}
	public function getBestCauseVariableId(): ?int{
		return $this->attributes[Variable::FIELD_BEST_CAUSE_VARIABLE_ID] ?? null;
	}
	public function getBestEffectVariableId(): ?int{
		return $this->attributes[Variable::FIELD_BEST_EFFECT_VARIABLE_ID] ?? null;
	}
	public function getBrandName(): ?string{
		return $this->attributes[Variable::FIELD_BRAND_NAME] ?? null;
	}
	/**
	 * @return QMButton
	 */
	public function getButton(){
		return new VariableButton($this);
	}
	public function getFontAwesome(): string{
		if(!$this->hasVariableCategoryId()){
			return Variable::FONT_AWESOME;
		}
		$c = $this->getQMVariableCategory();
		return $c->getFontAwesome();
	}
	public function getDisplayNameAttribute(): string{
		return $this->getTitleAttribute();
	}
	public function hasVariableCategoryId(): bool{
		return $this->attributes[self::FIELD_VARIABLE_CATEGORY_ID] ?? false;
	}
	public function getCategoryLink(array $params = []): string{
		return $this->getVariableCategoryLink($params);
	}
	/**
	 * @return VariableChartChartGroup
	 */
	public function getChartGroup(): ChartGroup{
		return $this->getDBModel()->getChartGroup();
	}
	public function getChartsUrl(): string{
		return $this->getUrl();
	}
	/**
	 * @param array $attributes
	 * @param array $values
	 * @return Variable|null
	 */
	public static function firstOrCreate(array $attributes, array $values = []): ?Variable{
		return parent::firstOrCreate($attributes, $values);
	}
	/**
	 * @return string
	 */
	public function getCombinationOperationAttribute(): string{
		$value = $this->attributes[self::FIELD_COMBINATION_OPERATION] ?? null;
		if(!$value){
			$value = $this->getQMUnit()->getCombinationOperation();
		}
		return $value;
	}
	public function getCommonAlias(): ?string{
		return $this->attributes[Variable::FIELD_COMMON_ALIAS] ?? null;
	}
	public function getCommonMaximumAllowedDailyValue(): ?float{
		return $this->attributes[Variable::FIELD_COMMON_MAXIMUM_ALLOWED_DAILY_VALUE] ?? null;
	}
	public function getCommonMinimumAllowedDailyValue(): ?float{
		return $this->attributes[Variable::FIELD_COMMON_MINIMUM_ALLOWED_DAILY_VALUE] ?? null;
	}
	public function getCommonMinimumAllowedNonZeroValue(): ?float{
		return $this->attributes[Variable::FIELD_COMMON_MINIMUM_ALLOWED_NON_ZERO_VALUE] ?? null;
	}
	public function getCorrelateUrl(): string{
		return $this->getUrl(['correlate' => true]);
	}
	public function getCreatorUserId(): ?int{
		return $this->attributes[Variable::FIELD_CREATOR_USER_ID] ?? null;
	}
	public function getDailyValues(): array{
		return $this->getDBModel()->getDailyValues();
	}
	/**
	 * @return string
	 */
	public function getDataQuantityHTML(): string{
		$num = $this->getOrCalculateNumberOfRawMeasurementsWithTagsJoinsChildren();
		$html = "
            <h3>" . $this->getDisplayNameAttribute() . " Data Quantity</h3>
            <p>
                There are currently $num raw measurements (including those from variables tagged by this variable)
                    from $this->numberOfUserVariables participants.
            </p>
        ";
		$html .= $this->getChartsButtonHtml();
		return $html;
	}
	public function getDataSourcesCountAttribute(): array{
		$value = $this->attributes[self::FIELD_DATA_SOURCES_COUNT] ?? [];
		return QMArr::toArray($value);
	}
	public function getDefaultValue(): ?float{
		return $this->attributes[Variable::FIELD_DEFAULT_VALUE] ?? null;
	}
	public function getDeletedAt(): ?string{
		return $this->attributes[Variable::FIELD_DELETED_AT] ?? null;
	}
	public function getDeletionReason(): ?string{
		return $this->attributes[Variable::FIELD_DELETION_REASON] ?? null;
	}
	/**
	 * @return string
	 */
	public function getSubtitleAttribute(): string{
		if(!$this->hasId()){
			return static::CLASS_DESCRIPTION;
		}
		return VariableDescriptionProperty::generateVariableDescription($this);
	}
    /**
     * @return string
     */
    public function getOrSetVariableDisplayName(): string{
        return $this->getTitleAttribute();
    }
	/**
	 * @return string
	 */
	public function getDisplayNameWithCategoryOrUnitSuffix(): string{
		return VariableNameProperty::addSuffix($this->getTitleAttribute(), $this->getQMUnit(), true,
			$this->attributes[self::FIELD_VARIABLE_CATEGORY_ID]);
	}
	public function getDurationOfAction(): int{
		return $this->attributes[self::FIELD_DURATION_OF_ACTION] ?? $this->getVariableCategory()->getDurationOfAction();
	}
	public function getCorrelateButton(): CorrelateButton{
		return new CorrelateButton($this);
	}
	public function getOnsetDelay(): int{
		return $this->attributes[self::FIELD_ONSET_DELAY] ?? $this->getVariableCategory()->getOnsetDelay();
	}
	/**
	 * @return QMButton[]
	 */
	public function getActionButtons(): array{
		$arr = parent::getActionButtons();
		$arr[] = $this->getCorrelateButton();
		return $arr;
	}
	/**
	 * @param null $value
	 * @return int
	 */
	public function getDurationOfActionAttribute($value = null): int{
		if($value){
			if(is_string($value) && strpos($value, ",") !== false){
				return str_replace(",", "", $value);
			}
            $value = (int)$value;
			if(!is_int($value)){
				le(__FUNCTION__ . " should return int but is " . \App\Logging\QMLog::print_r($value, true));
			}
			return $value;
		}
		if($value = $this->attributes[self::FIELD_DURATION_OF_ACTION] ?? null){
			return $value;
		}
		$cat = $this->getQMVariableCategory();
		$fromCat = $cat->durationOfAction;
		if(!$fromCat){
			le("No duration of action from $cat");
		}
		return $fromCat;
	}
	public function getEarliestMeasurementDate(): string{
		return TimeHelper::YYYYmmddd($this->earliest_non_tagged_measurement_start_at);
	}
	public function getEarliestNonTaggedMeasurementStartAt(): ?string{
		return $this->attributes[Variable::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT] ?? null;
	}
	/**
	 * @return string
	 */
	public function getEarliestTaggedMeasurementDate(): ?string{
		$time = $this->getEarliestTaggedMeasurementAt();
		if(!$time){
			return null;
		}
		return TimeHelper::YYYYmmddd($time);
	}
	public function getEarliestTaggedMeasurementAt(): ?string{
		return $this->attributes[self::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT] ?? null;
	}
	public function getEarliestTaggedMeasurementStartAt(): ?string{
		return $this->attributes[Variable::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT] ?? null;
	}
	public function getEditButton(): QMButton{
		return $this->getSettingsButton();
	}
	/**
	 * @return float|int|null
	 */
	public function getFillingValueAttribute(): ?float {
		$value = $this->getRawAttribute(self::FIELD_FILLING_VALUE);
		if($type = $this->getFillingTypeAttribute()){
			$filling = BaseFillingTypeProperty::toValue($type, $value);
		} else{
			$unit = $this->getQMUnit();
			$filling = BaseFillingValueProperty::getFillingValueOrFallback($value, $unit);
		}
		if($filling === null){
			return null;
		}
		if((float)$filling === (float)0){
			return $filling;
		}
		$min = $this->minimum_allowed_value;
		if($min && $min > $filling){
			$filling = null;
		}
		return $filling;
	}
	public function getFillingTypeAttribute(): string{
		$type = $this->getRawAttribute(Variable::FIELD_FILLING_TYPE);
		if($type && $type !== VariableFillingTypeProperty::FILLING_TYPE_UNDEFINED){
			return $type;
		}
		$unit = $this->getQMUnit();
		$type = $unit->getFillingType();
		if($type && $type !== VariableFillingTypeProperty::FILLING_TYPE_UNDEFINED){
			return $type;
		}
		// Variable categories aren't a good place for filling value setting.  We could have a rating
		// Social Interaction variable and hours from RescuetimeVariable
		//$category = $v->getQMVariableCategory();
		//$type = $category->getFillingType();
		if(!$type){
			le('!$type');
		}
		return $type;
	}
	public function getHardCodedVariable(): ?QMCommonVariable{
		$vars = QMCommonVariable::getHardCodedVariables();
		foreach($vars as $v){
			if($v->getVariableIdAttribute() === $this->getVariableId()){
				return $v;
			}
		}
		return null;
	}
	public function getImageUrl(): ?string{
		return $this->attributes[Variable::FIELD_IMAGE_URL] ?? null;
	}
	public function getImageUrlAttribute(): ?string{
		if(!empty($this->attributes["image_url"])){
			return $this->attributes["image_url"] ?? null;
		}
		if(!$this->hasId()){
			return static::DEFAULT_IMAGE;
		}
		$url = $this->attributes[self::FIELD_IMAGE_URL] ?? null;
		if(!$url || stripos($url, ".") === false){
			$url = $this->getAttribute(self::FIELD_INFORMATIONAL_URL);
			if($url && stripos($url, ".") !== false){
				$origin = parse_url($url)['host'];
				$url = "http://www.google.com/s2/favicons?domain=$origin";
			}
		}
		if(!$url || stripos($url, "http") === false){
			$catId = $this->variable_category_id;
			if(!$catId){
				throw new \LogicException("No variable category id!");
			}
			$url = $this->getQMVariableCategory()->getImageUrl();
		}
		return $url;
	}
	public function getInformationalUrl(): ?string{
		return $this->attributes[Variable::FIELD_INFORMATIONAL_URL] ?? null;
	}
	public function getInformationalUrlAttribute(): ?string{
		$url = $this->attributes[self::FIELD_INFORMATIONAL_URL] ?? null;
		if($url && stripos($url, "http") !== false){
			return $url;
		}
		if($this->variable_category_id === SoftwareVariableCategory::ID){
			$name = $this->name;
			if(stripos($name, '.') !== false){
				$url = QMStr::before(" ", $name);
				if($url && stripos($url, '.') !== false){
					return "https://" . $url;
				}
			}
		}
		return $url;
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		$arr = [];
		$arr[] = new VariableDefaultUnitButton($this);
		$arr[] = new VariableVariableCategoryButton($this);
		if($this->isOutcome()){
			$arr[] = new VariablePopulationCauseStudiesButton($this);
		}
		if($this->isPredictor()){
			$arr[] = new VariablePopulationEffectStudiesButton($this);
		}
		if($this->getNumberOfUserVariables()){
			$arr[] = new VariableUserVariablesButton($this);
		}
		if($this->getNumberOfRawMeasurementsWithTagsJoinsChildren()){
			$b = new VariableMeasurementsButton($this);
			$b->getUrl();
			$arr[] = $b;
		}
		return $arr;
	}
	public function getInternalErrorMessage(): ?string{
		return $this->attributes[Variable::FIELD_INTERNAL_ERROR_MESSAGE] ?? null;
	}
	/**
	 * @return string
	 */
	public function getIonIcon(): string{
		$icon = $this->getAttribute(Variable::FIELD_ION_ICON);
		if(!$icon){
			$icon = $this->getQMVariableCategory()->ionIcon;
		}
		return $icon;
	}
	/**
	 * @return string
	 */
	public function getIonicChartsUrl(): string{
		return $this->getUrl();
		//return $this->getWpPostUrl();
	}
	public function getLatestMeasurementDate(): string{
		return TimeHelper::YYYYmmddd($this->latest_non_tagged_measurement_start_at);
	}
	public function getLatestNonTaggedMeasurementStartAt(): ?string{
		return $this->attributes[Variable::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT] ?? null;
	}
	/**
	 * @return string
	 */
	public function getLatestTaggedMeasurementDate(): ?string{
		$at = $this->getLatestTaggedMeasurementAt();
		if(!$at){
			return null;
		}
		return TimeHelper::YYYYmmddd($at);
	}
	public function getLatestTaggedMeasurementAt(): ?string{
		if($at = $this->latest_tagged_measurement_start_at){
			return $at;
		}
		if($at = $this->latest_non_tagged_measurement_start_at){
			return $at;
		}
		return null;
	}
	public function getLatestTaggedMeasurementStartAt(): ?string{
		return $this->attributes[Variable::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT] ?? null;
	}
	public function getLogMetaDataString(): string{
		return $this->name ?? "Variable with no name";
	}
	public function getManualTrackingAttribute(): ?bool{
		$val = $this->getAttributeFromSelfUnitOrCategory(self::FIELD_MANUAL_TRACKING);
		if($val !== null){
			return (bool)$val;
		}
		return null;
	}
	/**
	 * @param string $key
	 * @return mixed
	 */
	private function getAttributeFromSelfUnitOrCategory(string $key){
		$val = $this->getRawAttribute($key);
		if($val !== null){
			return $val;
		}
		$unit = $this->getQMUnit();
		$fromUnit = $unit->getAttribute($key);
		if($fromUnit !== null){
			return $fromUnit;
		}
		$cat = $this->getQMVariableCategory();
		$fromCat = $cat->getAttribute($key);
		if($fromCat !== null){
			return $fromCat;
		}
		return null;
	}
	public function getMaximumAllowedDailyValue(): ?float{
		return VariableCommonMaximumAllowedDailyValueProperty::calculate($this);
	}
	public function getMaximumRecordedValue(): ?float{
		return $this->attributes[Variable::FIELD_MAXIMUM_RECORDED_VALUE] ?? null;
	}
	/**
	 * @param string|int $timeAt
	 * @return AnonymousMeasurement
	 */
	public function getMeasurementByDate($timeAt): ?AnonymousMeasurement{
		$date = db_date($timeAt);
		$measurements = $this->getDailyMeasurementsWithoutTagsOrFilling();
		return $measurements[$date] ?? null;
	}
	/**
	 * @return AnonymousMeasurement[]
	 */
	public function getDailyMeasurementsWithoutTagsOrFilling(): array{
		$v = $this->getDBModel();
		return $v->getDailyMeasurementsWithoutTagsOrFilling();
	}
	/**
	 * @return Collection|Measurement[]
	 */
	public function getMeasurements(): Collection{
		return $this->measurements()->get();
	}
	public function getMedianSecondsBetweenMeasurements(): ?int{
		return $this->attributes[Variable::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS] ?? null;
	}
	public function getMinimumAllowedDailyValue(): ?float{
		return VariableCommonMinimumAllowedDailyValueProperty::calculate($this);
	}
	public function getMinimumRecordedValue(): ?float{
		return $this->attributes[Variable::FIELD_MINIMUM_RECORDED_VALUE] ?? null;
	}
	public function getMostCommonConnectorId(): ?int{
		return $this->attributes[Variable::FIELD_MOST_COMMON_CONNECTOR_ID] ?? null;
	}
	/**
	 * @param $value
	 * @return null
	 */
	public function getMostCommonConnectorIdAttribute($value){
		if($value){
			return $value;
		}
		return null;
	}
	public function getMostCommonOriginalUnitId(): ?int{
		return $this->attributes[Variable::FIELD_MOST_COMMON_ORIGINAL_UNIT_ID] ?? null;
	}
	public function getMostCommonSourceName(): ?string{
		return $this->attributes[Variable::FIELD_MOST_COMMON_SOURCE_NAME] ?? null;
	}
	public function getMostCommonValue(): ?float{
		return $this->attributes[Variable::FIELD_MOST_COMMON_VALUE] ?? null;
	}
	/**
	 * @param int $periodInDays
	 * @param int|string $timeAt
	 * @return float
	 */
	public function getMovingAverageValue(int $periodInDays, $timeAt): ?float{
		return $this->getDBModel()->getMovingAverageValue($periodInDays, $timeAt);
	}
	public function getNewestDataAt(): ?string{
		return $this->attributes[Variable::FIELD_NEWEST_DATA_AT] ?? null;
	}
	public function getNumberCommonTaggedBy(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_COMMON_TAGGED_BY] ?? null;
	}
		public function getNumberOfAggregateCausesButton(array $params = []): QMButton{
		$params[GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID] = $this->id;
		$number = $this->number_of_global_variable_relationships_as_effect;
		if($number === null){
			$number = "N/A";
		}
		return GlobalVariableRelationship::getAstralIndexButton($params, $number, "Population Causes",
			UserVariableRelationship::FONT_AWESOME_EFFECTS,
			"Population-Level Studies on Potential Causes of " . $this->getTitleAttribute() .
			" Based on Anonymously Aggregated Data");
	} // Let's use actual name instead of display name so it's differentiated on index page
	public function getNumberOfApplicationsWhereOutcomeVariable(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_APPLICATIONS_WHERE_OUTCOME_VARIABLE] ?? null;
	}
	public function getNumberOfApplicationsWherePredictorVariable(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_APPLICATIONS_WHERE_PREDICTOR_VARIABLE] ?? null;
	}
	public function getNumberOfCommonChildren(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_COMMON_CHILDREN] ?? null;
	}
	public function getNumberOfCommonFoods(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_COMMON_FOODS] ?? null;
	}
	public function getNumberOfCommonIngredients(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_COMMON_INGREDIENTS] ?? null;
	}
	public function getCategoryNames(): array{
		return [$this->getVariableCategoryName()];
	}
	public function getNumberOfCommonJoinedVariables(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_COMMON_JOINED_VARIABLES] ?? null;
	}
	public function getNumberOfCommonParents(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_COMMON_PARENTS] ?? null;
	}
	public function getNumberOfCommonTagsWhereTagVariable(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_COMMON_TAGS_WHERE_TAG_VARIABLE] ?? null;
	}
	public function getNumberOfCommonTagsWhereTaggedVariable(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_COMMON_TAGS_WHERE_TAGGED_VARIABLE] ?? null;
	}
	public function getNumberOfEffectsButton(array $params = []): QMButton{
		$params[GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID] = $this->id;
		$number = $this->number_of_global_variable_relationships_as_cause;
		if($number === null){
			$number = "N/A";
		}
		return GlobalVariableRelationship::getAstralIndexButton($params, $number, "Population Effects",
			UserVariableRelationship::FONT_AWESOME_EFFECTS,
			"Population-Level Studies on Potential Effects of " . $this->getTitleAttribute() .
			" Based on Anonymously Aggregated Data");
	}
	public function getNumberOfMeasurementsButton(array $params = []): QMButton{
		$params[Measurement::FIELD_VARIABLE_ID] = $this->id;
		$number = $this->number_of_measurements;
		if($number === null){
			$number = "N/A";
		}
		return Measurement::getAstralIndexButton($params, $number, "Measurements", Measurement::FONT_AWESOME,
			"See history of " . $this->getTitleAttribute() . " measurements recorded");
	}
	public function getTitleAttribute(): string{
		if(empty($this->attributes[self::FIELD_NAME])){
			return static::getClassNameTitle();
		}
        $commonAlias = $this->attributes[self::FIELD_COMMON_ALIAS] ?? null;
        if($commonAlias){
            return $commonAlias;
        }
        $variableToDisplayName = VariableNameProperty::variableToDisplayName($this);
        return $this->attributes[self::FIELD_COMMON_ALIAS] = $variableToDisplayName;
	}
	public function getIcon(): string{
		return $this->getImage();
	}
	public static function getIndexContentView(): View{
		return view('variables-index-content', ['buttons' => static::getIndexModels()]);
	}
	/**
	 * @return Collection|static[]
	 */
	public static function getIndexModels(): Collection{
		if($models = static::getFromClassMemory(__FUNCTION__)){
			return $models;
		}
		$qb = static::indexQBWithCorrelations();
		self::onlyInterestingCategories($qb);
		$models = $qb->get();
		$sorted = $models->sortByDesc(function($v){
			/** @var Variable $v */
			return $v->getNumberOfGlobalVariableRelationships();
		});
		return static::setInClassMemory(__FUNCTION__, $sorted);
	}
	public static function indexQBWithCorrelations(): Builder{
		$qb = static::indexSelectQB();
		return $qb->where(Variable::FIELD_NUMBER_OF_USER_VARIABLES, ">", 2)
			->where(Variable::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN, ">", 5)
			->whereRaw("variables.number_of_global_variable_relationships_as_cause + " .
				"variables.number_of_global_variable_relationships_as_effect > 0");
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return string
	 */
	public function getCorrelationGaugesListHtml(int $limit = null, string $variableCategoryName = null): string{
		$correlations = $this->getPublicOutcomesOrPredictors($limit, $variableCategoryName);
		if(!$correlations->count()){
			return $this->getNoCorrelationsDataRequirementAndCurrentDataQuantityHtml();
		}
		if($this->isOutcome()){
			$params['effectVariableName'] = $this->getDisplayNameAttribute();
		} else{
			$params['causeVariableName'] = $this->getDisplayNameAttribute();
		}
		$response = new CorrelationsAndExplanationResponseBody($correlations, $params);
		return $response->getHtml();
	}
	public static function indexSelectQB(): Builder{
		// getIndexColumns is too complicated to keep track of whether it should be added to memory
		// $qb = static::select(static::getIndexColumns());
		$qb = static::query();
		VariableNameProperty::whereNotTestVariable($qb);
		return $qb;
	}
	/**
	 * @return string
	 * @throws HighchartExportException
	 */
	public function generatePostContent(): string{
		$html = $this->getChartAndTableHTML(true);
		$html .= $this->getCorrelationGaugesListHtml();
		//$html .= $this->renderCorrelationsTable();
		return $html;
	}
	/**
	 * @return View
	 */
	public static function getIndexPageView(): View{
		return view('variables-index', ['buttons' => static::getIndexModels()]);
	}
	public function getCategoryName(): string{
		return $this->getVariableCategoryName();
	}
	public function getSortingScore(): float{
		return $this->getNumberOfGlobalVariableRelationships() + $this->getNumberOfUserVariables();
	}
	public function getTooltip(): string{
		return $this->getNumberOfGlobalVariableRelationships() . " studies on the causes or effects of " .
			$this->getTitleAttribute();
	}
	public function getNumberOfOutcomeCaseStudies(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES] ?? null;
	}
	public function getNumberOfOutcomePopulationStudies(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_OUTCOME_POPULATION_STUDIES] ?? null;
	}
	public function getNumberOfPredictorCaseStudies(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_PREDICTOR_CASE_STUDIES] ?? null;
	}
	public function getNumberOfPredictorPopulationStudies(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_PREDICTOR_POPULATION_STUDIES] ?? null;
	}
	public function getNumberOfSoftDeletedMeasurements(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_SOFT_DELETED_MEASUREMENTS] ?? null;
	}
	public function getNumberOfStudiesWhereCauseVariable(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_STUDIES_WHERE_CAUSE_VARIABLE] ?? null;
	}
	public function getNumberOfStudiesWhereEffectVariable(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_STUDIES_WHERE_EFFECT_VARIABLE] ?? null;
	}
	public function getNumberOfSubtitle(): string{
		if($num = $this->getNumberOfGlobalVariableRelationships()){
			return "$num Studies";
		}
		return $this->getNumberOfUserVariables() . " Participants";
	}
	public function getNumberOfTrackingReminderNotifications(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS] ?? null;
	}
	public function getNumberOfTrackingReminders(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_TRACKING_REMINDERS] ?? null;
	}
	public function getNumberOfUniqueValues(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_UNIQUE_VALUES] ?? null;
	}
	public function getNumberOfUserChildren(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_USER_CHILDREN] ?? null;
	}
	public function getNumberOfUserFoods(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_USER_FOODS] ?? null;
	}
	public function getNumberOfUserIngredients(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_USER_INGREDIENTS] ?? null;
	}
	public function getNumberOfUserJoinedVariables(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_USER_JOINED_VARIABLES] ?? null;
	}
	public function getNumberOfUserParents(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_USER_PARENTS] ?? null;
	}
	public function getNumberOfUserTagsWhereTagVariable(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_USER_TAGS_WHERE_TAG_VARIABLE] ?? null;
	}
	public function getNumberOfUserTagsWhereTaggedVariable(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_USER_TAGS_WHERE_TAGGED_VARIABLE] ?? null;
	}
	public function getNumberOfUserVariablesButton(array $params = []): QMButton{
		$params[UserVariable::FIELD_VARIABLE_ID] = $this->id;
		$number = $this->number_of_user_variables;
		if($number === null){
			$number = "N/A";
		}
		return UserVariable::getAstralIndexButton($params, $number, "User Variables");
	}
	public function getNumberOfUsersWherePrimaryOutcomeVariable(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_USERS_WHERE_PRIMARY_OUTCOME_VARIABLE] ?? null;
	}
	public function getNumberOfVariablesWhereBestCauseVariable(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_VARIABLES_WHERE_BEST_CAUSE_VARIABLE] ?? null;
	}
	public function getNumberOfVariablesWhereBestEffectVariable(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_VARIABLES_WHERE_BEST_EFFECT_VARIABLE] ?? null;
	}
	public function getNumberOfVotesWhereCauseVariable(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_VOTES_WHERE_CAUSE_VARIABLE] ?? null;
	}
	public function getNumberOfVotesWhereEffectVariable(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_VOTES_WHERE_EFFECT_VARIABLE] ?? null;
	}
	public function getOnsetDelayAttribute(): int{
		try {
			return $this->getAttributeFromSelfOrCategory(self::FIELD_ONSET_DELAY);
		} catch (\Throwable $e) {
			return $this->getAttributeFromSelfOrCategory(self::FIELD_ONSET_DELAY);
		}
	}
	/**
	 * @param string $key
	 * @return mixed
	 */
	private function getAttributeFromSelfOrCategory(string $key){
		$val = $this->getRawAttribute($key);
		if($val !== null){
			return $val;
		}
		$cat = $this->getQMVariableCategory();
		$fromCat = $cat->getAttribute($key);
		if($fromCat !== null){
			return $fromCat;
		}
		return null;
	}
	public function getOptimalValueMessage(): ?string{
		return $this->getAttribute(Variable::FIELD_OPTIMAL_VALUE_MESSAGE);
	}
	public function getOrCreateUserVariable(int $userId, array $newVariableData = []): UserVariable{
		if($uv = UserVariable::findByVariableId($this->id, $userId)){
			return $uv;
		}
        $newVariableData[UserVariable::FIELD_NUMBER_OF_TRACKING_REMINDERS] = 0;
		$newVariableData[UserVariable::FIELD_USER_ID] = $userId;
		$newVariableData[UserVariable::FIELD_VARIABLE_ID] = $this->id;
		try {
			return UserVariable::upsertOne($newVariableData);
		} catch (IncompatibleUnitException|InvalidVariableValueException|QueryException $e) {
			le($e);
		}
	}
	public function getOutcome(): ?bool{
		return $this->attributes[Variable::FIELD_OUTCOME] ?? null;
	}
	public function getOutcomeSearchHtml(): string{
		$html = HtmlHelper::renderView(view('alpine-bars-images', ['table' => 'outcomes']));
		return $html;
	}
	public function getParentId(): ?int{
		return $this->attributes[Variable::FIELD_PARENT_ID] ?? null;
	}
	public function getPredictorSearchHtml(): string{
		$html = HtmlHelper::renderView(view('alpine-bars-images', ['table' => 'predictors']));
		return $html;
	}
	public function getPrice(): ?float{
		return $this->attributes[Variable::FIELD_PRICE] ?? null;
	}
	public function getProductUrl(): ?string{
		return $this->attributes[Variable::FIELD_PRODUCT_URL] ?? null;
	}
	/**
	 * @return Collection|GlobalVariableRelationship[]
	 */
	public function getPublicOutcomes(): Collection{
		if($mem = $this->getFromModelMemory(__FUNCTION__)){
			return $mem;
		}
		$outcomes = $this->publicOutcomes()->get();
		return $this->setInModelMemory(__FUNCTION__, $outcomes);
	}
	public function publicOutcomes(): HasMany{
		return GlobalVariableRelationshipIsPublicProperty::restrict($this->outcomes())
			->whereIn(GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID, VariableCategory::getOutcomeIds())
			->whereNotIn(GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID,
				VariableCategory::getAppsLocationsWebsiteIds());
	}
	public function outcomes(): HasMany{
		$l = $this->l();
		return $l->global_variable_relationships_where_cause_variable()
			->orderByDesc(GlobalVariableRelationship::FIELD_AGGREGATE_QM_SCORE);
	}
	public function global_variable_relationships_where_cause_variable(): HasMany{
		return parent::global_variable_relationships_where_cause_variable()->with([
			'effect_variable',
		]);
	}
	/**
	 * @return Collection|GlobalVariableRelationship[]
	 */
	public function getPublicPredictors(): Collection{
		if($mem = $this->getFromModelMemory(__FUNCTION__)){
			return $mem;
		}
		$predictors = $this->publicPredictors()->get();
		return $this->setInModelMemory(__FUNCTION__, $predictors);
	}
	public function publicPredictors(): HasMany{
		return GlobalVariableRelationshipIsPublicProperty::restrict($this->predictors())
			->whereNotIn(GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
				VariableCategory::getAppsLocationsWebsiteIds());
	}
	public function predictors(): HasMany{
		$l = $this->l();
		return $l->global_variable_relationships_where_effect_variable()
			->orderByDesc(GlobalVariableRelationship::FIELD_AGGREGATE_QM_SCORE);
	}
	public function global_variable_relationships_where_effect_variable(): HasMany{
		return parent::global_variable_relationships_where_effect_variable()->with([
			'cause_variable',
		]);
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{
		return true;
	}
	/**
	 * @param null $reader
	 * @return bool
	 */
	public function canReadMe($reader = null): bool{
		if($this->is_public){
			return true;
		}
		if($this->client_id === BaseClientIdProperty::CLIENT_ID_CURE_TOGETHER){
			return true;
		}
		if(QMAuth::isAdmin()){
			return true;
		}
		if(is_int($reader)){
			$reader = QMUser::find($reader);
		}
		if(!$reader){
			$reader = QMAuth::getQMUser();
		}
		if(!$reader){
			return false;
		}
		$uv = $reader->findUserVariable($this->getId());
		return (bool)$uv;
	}
	/**
	 * @param User|QMUserVariable|null $writer
	 * @return bool
	 */
	public function canWriteMe($writer = null): bool{
		if(!$writer){
			$writer = QMAuth::getQMUser();
		}
        $creatorUserId = $this->getCreatorUserId();
        if($writer->getId() === $creatorUserId){
            return true;
        }
		return $writer && $writer->isAdmin();
	}
	public function clone(array $new = []): BaseModel{
		if(!isset($new[self::FIELD_SYNONYMS])){
			$new[self::FIELD_SYNONYMS] = [$new[self::FIELD_NAME]];
		}
		if(!isset($new[self::FIELD_COMMON_ALIAS])){
			$new[self::FIELD_COMMON_ALIAS] = $new[self::FIELD_NAME];
		}
		if(!isset($new[self::FIELD_CREATOR_USER_ID])){
			$new[self::FIELD_CREATOR_USER_ID] = QMAuth::id();
		}
		return parent::clone($new);
	}
	public function correlations_where_cause_variable(): HasMany{
		return parent::correlations_where_cause_variable()->with([
			'effect_variable',
		]);
	}
	public function correlations_where_effect_variable(): HasMany{
		return parent::correlations_where_effect_variable()->with([
			'cause_variable',
		]);
	}
	public function ct_treatment_side_effects_where_side_effect_variable(): HasMany{
		return $this->hasMany(CtTreatmentSideEffect::class, CtTreatmentSideEffect::FIELD_SIDE_EFFECT_VARIABLE_ID,
			CtTreatmentSideEffect::FIELD_ID)->with('treatment_variable');
	}
	public function ct_treatment_side_effects_where_treatment_variable(): HasMany{
		return $this->hasMany(CtTreatmentSideEffect::class, CtTreatmentSideEffect::FIELD_TREATMENT_VARIABLE_ID,
			CtTreatmentSideEffect::FIELD_ID)->with('side_effect_variable');
	}
	public static function fakeFromPropertyModels(int $userId = UserIdProperty::USER_ID_TEST_USER): BaseModel{
		return OverallMoodCommonVariable::instance()->l();
	}
	/**
	 * @return Variable[]
	 * @noinspection PhpDocSignatureInspection
	 */
	public function getPublicUserVariables(): \Illuminate\Support\Collection {
		return $this->getUserVariables()->where(UserVariable::FIELD_IS_PUBLIC, true);
	}
	/**
	 * @return UserVariable[]|Collection
	 */
	public function getUserVariables(): Collection {
		$this->loadMissing('user_variables');
		return $this->user_variables;
	}
	/**
	 * @return QMDataSource[]
	 */
	public function getQMDataSources(): array{
		$clientIds = $this->getClientIds();
		$sources = [];
		foreach($clientIds as $clientId){
			if($ds = QMDataSource::find($clientId)){
				$sources[$clientId] = $ds;
			}
		}
		return $sources;
	}
	/**
	 * @return string[]
	 */
	public function getClientIds(): array{
		$rel = $this->user_variable_clients();
		$all = $rel->pluck(UserVariableClient::FIELD_CLIENT_ID)->all();
		$unique = array_unique($all);
		return $unique;
	}
	/**
	 * @return AnonymousMeasurement[]
	 */
	public function getQMMeasurements(): array{
		return $this->getDBModel()->getQMMeasurements();
	}
	public function getQMUserVariable(int $userId): QMUserVariable{
		return QMUserVariable::getOrCreateById($userId, $this->id);
	}
	public function getQMVariable(): QMVariable{
		return $this->getDBModel();
	}
	public function getReasonForAnalysis(): ?string{
		return $this->attributes[Variable::FIELD_REASON_FOR_ANALYSIS] ?? null;
	}
	public function getRecordSizeInKb(): ?int{
		return $this->attributes[Variable::FIELD_RECORD_SIZE_IN_KB] ?? null;
	}
	public function getRelationshipButtons(): array{
		$buttons = [];
		$n = $this->number_of_global_variable_relationships_as_cause;
		if($n){
			$buttons[] =
				GlobalVariableRelationship::getAstralIndexButton([GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID => $this->getId()],
					$n, "Population Effects", UserVariableRelationship::FONT_AWESOME_EFFECTS,
					"Global Population Studies on the Effects of " . $this->getTitleAttribute() .
					" based on Anonymously Aggregated Data from Many Users");
		}
		$n = $this->number_of_global_variable_relationships_as_effect;
		if($n){
			$buttons[] =
				GlobalVariableRelationship::getAstralIndexButton([GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID => $this->getId()],
					$n, "Population Causes", UserVariableRelationship::FONT_AWESOME_EFFECTS,
					"Global Population Studies on the Causes of " . $this->getTitleAttribute() .
					" based on Anonymously Aggregated Data from Many Users");
		}
		if($this->isOutcome()){
			$buttons[] = UserVariableRelationship::getAstralIndexButton([UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID => $this->getId()], null,
				"Effects for Individuals", FontAwesome::SIGN_OUT_ALT_SOLID,
				"Individual N1 Case Study Investigations into the Potential Effects of " . $this->getNameAttribute(),
				QMColor::HEX_RED);
		}
		$buttons[] = UserVariableRelationship::getAstralIndexButton([UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID => $this->getId()], null,
			"Causes for Individuals", FontAwesome::SIGN_IN_ALT_SOLID,
			"Individual N1 Case Study Investigations into the Causes of " . $this->getNameAttribute(), QMColor::HEX_RED);
		$buttons[] = UserVariable::getAstralIndexButton([UserVariable::FIELD_VARIABLE_ID => $this->getId()],
			$this->number_of_user_variables, "User Variables", null, null, QMColor::HEX_RED);
		$buttons[] = Measurement::getAstralIndexButton([Measurement::FIELD_VARIABLE_ID => $this->getId()],
			$this->number_of_measurements, "Measurements", null, null, QMColor::HEX_RED);
		$buttons[] = TrackingReminder::getAstralIndexButton([TrackingReminder::FIELD_VARIABLE_ID => $this->getId()],
			$this->number_of_tracking_reminders, "Reminders", null, null, QMColor::HEX_RED);
		return $buttons;
	}
	public function getId(): ?int{
		return $this->attributes[Variable::FIELD_ID] ?? null;
	}
	/**
	 * @return bool
	 */
	public function isOutcome(): bool{
		$outcome = $this->outcome;
		if($outcome === null){
			$outcome = $this->getQMVariableCategory()->outcome;
		}
		if($outcome === null){
			$outcome = true;
		}
		if(property_exists($this, 'outcome')){
			$this->outcome = $outcome;
		}
		return $outcome;
	}
	public function getSecondMostCommonValue(): ?float{
		return $this->attributes[Variable::FIELD_SECOND_MOST_COMMON_VALUE] ?? null;
	}
	public function getShowPath(): string{
		$path = static::getIndexPath();
		$slug = $this->getSlug();
		$url = static::getIndexUrl() . "/" . $slug;
		if(filter_var($url, FILTER_VALIDATE_URL) === false){
			return $path . "/" . $this->getId();
		}
		return $path . "/" . $slug;
	}
    /**
     * @param array $params
     * @return string
     */
    public static function getIndexUrl(array $params = []): string{
        return qm_url(static::getIndexPath(), $params);
    }
	public function getSlug(): string{
		return static::toSlug($this->getNameAttribute());
	}
	/**
	 * @param string $name
	 * @return array|string|string[]
	 */
	public static function toSlug(string $name){
		$slug = str_replace(" ", "_", $name);
		$slug = str_replace('"', "", $slug);
		return $slug;
	}
	public function getTags(): array{
		return $this->getOrGenerateSynonyms();
	}
	/**
	 * @return string[]
	 */
	public function getOrGenerateSynonyms(): array{
		return $this->synonyms;
	}
	public static function getUniqueIndexColumns(): array{
		return [self::FIELD_NAME];
	}
	public function publish(){
		$this->getDBModel()->publish();
	}
	/**
	 * @return Variable|Builder
	 */
	public static function wherePostable(){
		$qb = static::query();
		$qb->where(static::TABLE . '.' . static::FIELD_NUMBER_OF_USER_VARIABLES, ">", 1);
		return $qb;
	}
	public function getSkewness(): ?float{
		return $this->attributes[Variable::FIELD_SKEWNESS] ?? null;
	}
	/**
	 * @return string
	 */
	public function getStatisticsTableHtml(): string{
		$u = $this->getUserOrCommonUnit();
		$table = [
			"Variable Name" => $this->getVariableName(),
			"Aggregation Method" => $this->getCombinationOperation(),
			"Analysis Performed At" => TimeHelper::YYYYmmddd($this->getAnalysisEndedAt()),
			"Duration of Action" => $this->getDurationOfActionHumanString(),
			"Filling Value" => $this->getFillingValueAttribute(),
			"Kurtosis" => $this->getKurtosis(),
			"Maximum Allowed Value" => $u->getValueAndUnitString($this->getMaximumAllowedValueAttribute()),
			"Mean" => $u->getValueAndUnitString($this->getMean()),
			"Median" => $u->getValueAndUnitString($this->getMedian()),
			"Minimum Allowed Value" => $u->getValueAndUnitString($this->getMinimumAllowedValueAttribute()),
			"Number of Aggregate Predictors" => $this->getNumberOfGlobalVariableRelationshipsAsEffect(),
			"Number of Aggregate Outcomes" => $this->getNumberOfGlobalVariableRelationshipsAsCause(),
			"Number of Measurements" => $this->getNumberOfMeasurementsAttribute(),
			"Number of Measurements (including those generated by tagged, joined, or child variables)" => $this->getNumberOfRawMeasurementsWithTagsJoinsChildren(),
			"Public" => ($this->getIsPublic()) ? "true" : "false",
			"Onset Delay" => $this->getOnsetDelayHumanString(),
			"Standard Deviation" => $this->getStandardDeviation(),
			"Unit" => $u->getNameAttribute(),
			"User Variables" => $this->getNumberOfUserVariables(),
			"UPC" => $this->getUpc(),
			"Variable Category" => $this->getQMVariableCategory()->getNameAttribute(),
			"Variable ID" => $this->getVariableId(),
			"Variance" => $this->getVariance(),
		];
		$html =
			QMTable::convertObjectToVerticalPropertyValueTableHtml($table, $this->getDisplayNameAttribute() . " Info");
		return $html;
	}
	/**
	 * @return QMUnit
	 */
	public function getUserOrCommonUnit(): QMUnit{
		return $this->getCommonUnit();
	}
	public function getVariableName(): ?string{
		return $this->attributes[self::FIELD_NAME] ?? null;
	}
	public function getCombinationOperation(): string{
		return $this->combination_operation;
	}
	public function getFillingType(): string{
		return $this->getFillingTypeAttribute();
	}
	/**
	 * @return float
	 */
	public function getKurtosis(): ?float{
		return $this->kurtosis;
	}
	public function getMaximumAllowedValueAttribute(): ?float{
		$fromVariable = $this->getRawAttribute(self::FIELD_MAXIMUM_ALLOWED_VALUE);
		$unit = $this->getCommonUnit();
		$fromUnit = $unit->getMaximumAggregatedValue();
		if($fromUnit !== null && $fromVariable !== null &&
			(float)$fromUnit < (float)$fromVariable){ // Body temp var max 115 is lower than F max 214
			$this->logError("max from variable is $fromVariable but max from unit is $fromUnit");
			return $fromUnit;
		}
		if($fromUnit !== null && $unit->isRating()){
			return $fromUnit;
		}
		if(stripos($this->name, "Grade") !== false && $unit->isPercent()){
			return (float)200; // We can get extra credit on assignments
		}
		if($fromUnit === null){
			return $fromVariable;
		}
		if($fromVariable === null){
			return $fromUnit;
		}
		return min([$fromVariable, $fromUnit]);
	}
	/**
	 * @return float
	 */
	public function getMean(): ?float{
		return $this->mean;
	}
	/**
	 * @return float
	 */
	public function getMedian(): ?float{
		return $this->median;
	}
	public function getMinimumAllowedValueAttribute(): ?float{
		//$fromVar = $this->getAttributeFromHardCodedVariableIfNecessary( Variable::FIELD_MINIMUM_ALLOWED_VALUE);
		$fromVar = $this->attributes[self::FIELD_MINIMUM_ALLOWED_VALUE] ?? null;
		$unit = $this->getQMUnit();
		$fromUnit = $unit->getMinimumValue(); // Percent is null
		if($fromUnit !== null && $fromVar !== null && $fromUnit > (float)$fromVar){
			$this->logError("min from variable is $fromVar but min from unit is $fromUnit");
			return $fromUnit;
		}
		if($fromUnit !== null && $unit->isRating()){
			return $fromUnit;
		}
		if(stripos($this->name, "Grade") !== false && $unit->isPercent()){
			return 0.0; // We can get extra credit on assignments
		}
		if($fromUnit === null){
			return $fromVar;
		}
		if($fromVar === null){
			return $fromUnit;
		}
		if($fromUnit > $fromVar){
			return $fromUnit;
		}
		return $fromVar;
	}
	public function getNumberOfMeasurementsAttribute(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_MEASUREMENTS] ?? null;
	}
	public function getIsPublic(): ?bool{
		return $this->getAttribute(Variable::FIELD_IS_PUBLIC);
	}
	/**
	 * @return mixed
	 */
	public function getStandardDeviation(): ?float{
		return $this->getAttribute(Variable::FIELD_STANDARD_DEVIATION);
	}
	public function getNumberOfUserVariables(): ?int{
		return $this->attributes[Variable::FIELD_NUMBER_OF_USER_VARIABLES] ?? null;
	}
	/**
	 * @return string
	 */
	public function getUpc(): ?string{
		return $this->attributes[Variable::FIELD_UPC_12] ?? $this->attributes[Variable::FIELD_UPC_14] ?? null;
	}
	/**
	 * @return float
	 */
	public function getVariance(): ?float{
		return $this->variance;
	}
	/**
	 * @return string[]
	 */
	public function getKeyWords(): array{
		$arr = $this->getSynonymsAttribute();
		$arr[] = $this->getVariableCategoryName();
		return $arr;
	}
	public function getParentCategoryName(): ?string{
		return Variable::CLASS_CATEGORY;
	}
	public static function getS3Bucket(): string{ return S3Public::getBucketName(); }
	public function getShowContentView(array $params = []): View{
		return view('variable-content', $this->getShowParams($params));
	}
	public function getShowJsData(): array{
		return (new VariableShowJavaScriptFile($this->l()))->getData();
	}
	/**
	 * @param array $params
	 * @return View
	 */
	public function getShowPageView(array $params = []): View{
		return view('variable-page', $this->getShowParams($params));
	}
	/**
	 * @return string
	 */
	public function getStatus(): ?string{
		return $this->status;
	}
	/**
	 * @param $value
	 * @return mixed|null
	 */
	public function getStatusAttribute($value){
		//if($value === ""){le('$value === ""');}
		if(empty($value)){
			$value = null;
		}
		return $value;
	}
	public function getThirdMostCommonValue(): ?float{
		return $this->attributes[Variable::FIELD_THIRD_MOST_COMMON_VALUE] ?? null;
	}
	public function getTopMenu(): QMMenu{
		return JournalMenu::instance();
	}
	public function getUnitIdAttribute(): ?int{
		//if(!isset($this->attributes[self::FIELD_DEFAULT_UNIT_ID])){le("no unit id");}
		$id = $this->attributes[self::FIELD_DEFAULT_UNIT_ID] ?? null;
		if(!$id){
			le("no unit id");
		}
		return $id;
	}
	/**
	 * @return Collection|GlobalVariableRelationship[]
	 */
	public function getUpVotedPublicOutcomes(): Collection{
		$predictors = $this->upVotedPublicOutcomes()->get();
		return $predictors;
	}
	public function upVotedPublicOutcomes(): HasMany{
		return $this->publicOutcomes()->where(GlobalVariableRelationship::FIELD_AVERAGE_VOTE, ">", 0);
	}
	/**
	 * @return Collection|GlobalVariableRelationship[]
	 */
	public function getUpVotedPublicPredictors(): Collection{
		$predictors = $this->upVotedPublicPredictors()->get();
		return $predictors;
	}
	public function upVotedPublicPredictors(): HasMany{
		return $this->publicPredictors()->where(GlobalVariableRelationship::FIELD_AVERAGE_VOTE, ">", 0);
	}
	public function getUpc12(): ?string{
		return $this->attributes[Variable::FIELD_UPC_12] ?? null;
	}
	public function getUpc14(): ?string{
		return $this->attributes[Variable::FIELD_UPC_14] ?? null;
	}
	public function getUrlSubPath(): string{
		return static::toSlug($this->getNameAttribute());
	}
	public function getUserErrorMessage(): ?string{
		return $this->attributes[Variable::FIELD_USER_ERROR_MESSAGE] ?? null;
	}
	public function getUserVariableDataLabLink(int $userId): string{
		return UserVariable::generateDataLabShowLink(null, "Your " . $this->getTitleAttribute() . " Data", [
			UserVariable::FIELD_USER_ID => $userId,
			UserVariable::FIELD_VARIABLE_ID => $this->getId(),
		]);
	}
	public function getUserVariableDataLabUrl(int $userId): string{
		return UserVariable::generateDataLabShowUrl(null, [
			UserVariable::FIELD_USER_ID => $userId,
			UserVariable::FIELD_VARIABLE_ID => $this->getId(),
		]);
	}
	public function getValence(): ?string{
		return $this->attributes[Variable::FIELD_VALENCE] ?? null;
	}
	public function getValenceAttribute(): ?string{
		return $this->attributes[self::FIELD_VALENCE] ?? null;
	}
	/**
	 * @return float[]
	 */
	public function getValidValues(): array{
		return $this->getQMCommonVariable()->getValidValues();
	}
	/**
	 * @return float[]
	 */
	public function getValues(): array{
		return $this->getQMCommonVariable()->getValues();
	}
	public function getQMCommonVariable(): QMCommonVariable{
		return $this->getDBModel();
	}
	public function getVariable(): Variable{
		return $this;
	}
	public function getVariableImageNameLink(array $params = [], string $style = null): string{
		return $this->getDataLabImageNameLink($params, $style);
	}
	/**
	 * @return VariableStatisticsCard
	 */
	public function getVariableStatisticsCard(): VariableStatisticsCard{
		$card = new VariableStatisticsCard($this);
		return $card;
	}
	public function getWikipediaExtract(): string{
		return $this->getDBModel()->getWikipediaExtract();
	}
	public function getWikipediaTitle(): ?string{
		return $this->attributes[Variable::FIELD_WIKIPEDIA_TITLE] ?? null;
	}
	public function getWikipediaUrl(): ?string{
		return $this->attributes[Variable::FIELD_WIKIPEDIA_URL] ?? null;
	}
	public function getWpPostId(): ?int{
		return $this->attributes[Variable::FIELD_WP_POST_ID] ?? null;
	}
	public function hasPrivateName(): bool{
		$name = $this->getNameAttribute();
		foreach(VariableNameProperty::PRIVATE_NAMES_LIKE as $needle){
			if(strpos($name, $needle) !== false){
				return true;
			}
		}
		return false;
	}
	public function individual_cause_studies(): HasMany{
		return $this->hasMany(UserVariableRelationship::class, UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID);
	}
	public function individual_effect_studies(): HasMany{
		return $this->hasMany(UserVariableRelationship::class, UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID);
	}
	/**
	 * @return bool
	 */
	public function isEmotion(): bool{
		return $this->getVariableCategoryName() === EmotionsVariableCategory::NAME;
	}
	/**
	 * @return bool
	 */
	public function isFood(): bool{
		return $this->getVariableCategoryName() === FoodsVariableCategory::NAME;
	}
	/**
	 * @return bool
	 */
	public function isSymptom(): bool{
		return $this->getVariableCategoryName() === SymptomsVariableCategory::NAME;
	}
	public function isTestVariable(): bool{
		return VariableNameProperty::isTest($this->getNameAttribute());
	}
	/**
	 * @return bool
	 */
	public function isTreatment(): bool{
		return $this->getVariableCategoryName() === TreatmentsVariableCategory::NAME;
	}
	/**
	 * @param string $key
	 * @param bool $keepNulls
	 * @return string|float
	 */
	public function mostCommonFromUserVariables(string $key, bool $keepNulls = false){
		$arr = $this->pluckFromUserVariables($key, $keepNulls);
		$val = ($arr) ? Stats::mostCommonValue($arr) : null;
		return $val;
	}
	/**
	 * @param string $key
	 * @param bool $keepNulls
	 * @return array
	 */
	public function pluckFromUserVariables(string $key, bool $keepNulls = false): array{
		$userVariables = $this->getUserVariables();
		$values = [];
		foreach($userVariables as $uv){
			$val = $uv->getAttribute($key);
			if($keepNulls || $val !== null){
				$values[] = $val;
			}
		}
		return $values;
	}
	/**
	 * @param string $key
	 * @param bool $keepNulls
	 * @return array
	 */
	public function pluckValidValueFromUserVariables(string $key, bool $keepNulls = false): array{
		$userVariables = $this->getUserVariables();
		$values = [];
		foreach($userVariables as $uv){
			$val = $uv->getAttribute($key);
			if($keepNulls || $val !== null){
				try {
					$this->validateValueForCommonVariableAndUnit($val, $key);
				} catch (\Throwable $e) {
				    ExceptionHandler::dumpOrNotify($e);
					continue;
				}
				$values[] = $val;
			}
		}
		return $values;
	}
	/**
	 * @param string $key
	 * @param bool $keepNulls
	 * @return string|float
	 */
	public function mostCommonFromUserVariablesBasedOnNumberOfMeasurements(string $key, bool $keepNulls = false){
		$userVariables = $this->getUserVariables();
		$byCount = [];
		foreach($userVariables as $uv){
			$val = $uv->getAttribute($key);
			if(!$keepNulls && $val === null){
				continue;
			}
			if(!isset($byCount[$val])){
				$byCount[$val] = 0;
			}
			$byCount[$val] += $uv->getNumberOfMeasurements();
		}
		if(!$byCount){
			return null;
		}
		$val = QMArr::keyOfBiggestValue($byCount);
		return $val;
	}
	/**
	 * @return BelongsTo
	 */
	public function mostCommonUnit(): BelongsTo{
		return $this->belongsTo(Unit::class, 'most_common_original_unit_id');
	}
	public function most_common_connector(): BelongsTo{
		return $this->belongsTo(Connector::class, Variable::FIELD_MOST_COMMON_CONNECTOR_ID, Connector::FIELD_ID,
			Variable::FIELD_MOST_COMMON_CONNECTOR_ID);
	}
	public function population_cause_studies(): HasMany{
		return $this->hasMany(GlobalVariableRelationship::class, GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID);
	}
	public function population_effect_studies(): HasMany{
		return $this->hasMany(GlobalVariableRelationship::class, GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID);
	}
	public function rename(string $newName){
		$originalName = $this->name;
		$this->name = $newName;
		$this->addSynonymsAndSave([$originalName, $newName]);
		try {
			$this->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
	}
	/**
	 * @param array|string $newSynonyms
	 * @return array
	 */
	public function addSynonymsAndSave($newSynonyms): array{
		if(!is_array($newSynonyms)){
			$newSynonyms = [$newSynonyms];
		}
		$previous = $this->synonyms ?? [];
		$this->synonyms = $merged = QMArr::mergeRemoveEmptyAndDuplicates($newSynonyms, $previous);
		QMLog::print($merged, "Synonyms for $this->name");
		if(!$this->creator_user_id){
			$this->creator_user_id = UserIdProperty::USER_ID_SYSTEM;
		}
		try {
			$this->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
		return $merged;
	}
	/**
	 * @param $time
	 * @return int
	 */
	public function roundStartTime($time): int{
		$time = TimeHelper::universalConversionToUnixTimestamp($time);
		$min = $this->getMinimumAllowedSecondsBetweenMeasurementsAttribute();
		$rounded = Stats::roundToNearestMultipleOf($time, $min);
		return $rounded;
	}
	/**
	 * @return int
	 */
	public function getMinimumAllowedSecondsBetweenMeasurementsAttribute(): int{
		$min = $this->attributes[self::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS] ?? null;
		if($min){
			return $min;
		}
		return VariableMinimumAllowedSecondsBetweenMeasurementsProperty::pluckOrDefault($this);
	}
	/**
	 * Scope a query to only include popular users.
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeMostNonTaggedMeasurements(Builder $query): Builder{
		return $query->orderBy(static::FIELD_NUMBER_OF_MEASUREMENTS, 'desc');
	}
	/**
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeMostTags(Builder $query): Builder{
		return $query->orderBy(self::FIELD_NUMBER_OF_COMMON_TAGS, 'desc');
	}
	public function setAdditionalMetaData(string $additionalMetaData): void{
		$this->setAttribute(Variable::FIELD_ADDITIONAL_META_DATA, $additionalMetaData);
	}
	public function setAnalysisRequestedAt(string $analysisRequestedAt): void{
		$this->setAttribute(Variable::FIELD_ANALYSIS_REQUESTED_AT, $analysisRequestedAt);
	}
	public function setAnalysisStartedAt(string $analysisStartedAt): void{
		$this->setAttribute(Variable::FIELD_ANALYSIS_STARTED_AT, $analysisStartedAt);
	}
	public function setAverageSecondsBetweenMeasurements(int $averageSecondsBetweenMeasurements): void{
		$this->setAttribute(Variable::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS, $averageSecondsBetweenMeasurements);
	}
	public function setBestGlobalVariableRelationshipId(int $bestGlobalVariableRelationshipId): void{
		$this->setAttribute(Variable::FIELD_BEST_AGGREGATE_CORRELATION_ID, $bestGlobalVariableRelationshipId);
	}
	public function setBestCauseVariableId(int $bestCauseVariableId): void{
		$this->setAttribute(Variable::FIELD_BEST_CAUSE_VARIABLE_ID, $bestCauseVariableId);
	}
	public function setBestEffectVariableId(int $bestEffectVariableId): void{
		$this->setAttribute(Variable::FIELD_BEST_EFFECT_VARIABLE_ID, $bestEffectVariableId);
	}
	public function setBrandName(string $brandName): void{
		$this->setAttribute(Variable::FIELD_BRAND_NAME, $brandName);
	}
	public function setCauseOnly(bool $causeOnly): void{
		$this->setAttribute(Variable::FIELD_CAUSE_ONLY, $causeOnly);
	}
	public function setCharts(ChartGroup $charts): void{
		$this->setAttribute(Variable::FIELD_CHARTS, $charts);
	}
	public function setClientId(string $clientId): void{
		$this->setAttribute(Variable::FIELD_CLIENT_ID, $clientId);
	}
	public function setCombinationOperation(string $combinationOperation): void{
		$this->setAttribute(Variable::FIELD_COMBINATION_OPERATION, $combinationOperation);
	}
	public function setCommonAlias(string $commonAlias): void{
		$this->setAttribute(Variable::FIELD_COMMON_ALIAS, $commonAlias);
	}
	public function setCommonMaximumAllowedDailyValue(float $commonMaximumAllowedDailyValue): void{
		$this->setAttribute(Variable::FIELD_COMMON_MAXIMUM_ALLOWED_DAILY_VALUE, $commonMaximumAllowedDailyValue);
	}
	public function setCommonMinimumAllowedDailyValue(float $commonMinimumAllowedDailyValue): void{
		$this->setAttribute(Variable::FIELD_COMMON_MINIMUM_ALLOWED_DAILY_VALUE, $commonMinimumAllowedDailyValue);
	}
	public function setCommonMinimumAllowedNonZeroValue(float $commonMinimumAllowedNonZeroValue): void{
		$this->setAttribute(Variable::FIELD_COMMON_MINIMUM_ALLOWED_NON_ZERO_VALUE, $commonMinimumAllowedNonZeroValue);
	}
	public function setCreatorUserId(int $creatorUserId): void{
		$this->setAttribute(Variable::FIELD_CREATOR_USER_ID, $creatorUserId);
	}
	public function setDataSourcesCount(string $dataSourcesCount): void{
		$this->setAttribute(Variable::FIELD_DATA_SOURCES_COUNT, $dataSourcesCount);
	}
	/**
	/**
	 * @param $value
	 */
	public function setDataSourcesCountAttribute($value){
		if(is_array($value)){
			$value = json_encode($value);
		}
		$this->attributes[self::FIELD_DATA_SOURCES_COUNT] = $value;
	}
	public function setDefaultUnitId(int $defaultUnitId): void{
		$this->setAttribute(Variable::FIELD_DEFAULT_UNIT_ID, $defaultUnitId);
	}
	public function setDefaultValue(float $defaultValue): void{
		$this->setAttribute(Variable::FIELD_DEFAULT_VALUE, $defaultValue);
	}
	public function setDeletedAt(string $deletedAt): void{
		$this->setAttribute(Variable::FIELD_DELETED_AT, $deletedAt);
	}
	public function setDeletionReason(string $deletionReason): void{
		$this->setAttribute(Variable::FIELD_DELETION_REASON, $deletionReason);
	}
	public function setDescription(string $description): void{
		$this->setAttribute(Variable::FIELD_DESCRIPTION, $description);
	}
	/**
	 * @param $val
	 */
	public function setDurationOfActionAttribute($val){
		if($this->variable_category_id){
			$cat = $this->getQMVariableCategory();
			if($cat->durationOfAction === $val){
				//le('$cat->durationOfAction === $val');
			}
		}
		$this->attributes[self::FIELD_DURATION_OF_ACTION] = $val;
	}

	public function setEarliestTaggedMeasurementStartAt(string $earliestTaggedMeasurementStartAt): void{
		$this->setAttribute(Variable::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT, $earliestTaggedMeasurementStartAt);
	}
	public function setInformationalUrl(string $informationalUrl): void{
		$this->setAttribute(Variable::FIELD_INFORMATIONAL_URL, $informationalUrl);
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
	public function setIsPublic(?bool $isPublic): void{
		$this->setAttribute(Variable::FIELD_IS_PUBLIC, $isPublic);
	}
	public static function fromSlug(string $slug): string{
		return trim(str_replace("_", " ", $slug));
	}
	public function setKurtosis(float $kurtosis): void{
		$this->setAttribute(Variable::FIELD_KURTOSIS, $kurtosis);
	}
	public function setLatestNonTaggedMeasurementStartAt(string $latestNonTaggedMeasurementStartAt): void{
		$this->setAttribute(Variable::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT, $latestNonTaggedMeasurementStartAt);
	}
	public function setLatestTaggedMeasurementStartAt(string $latestTaggedMeasurementStartAt): void{
		$this->setAttribute(Variable::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT, $latestTaggedMeasurementStartAt);
	}
	public function setManualTracking(bool $manualTracking): void{
		$this->setAttribute(Variable::FIELD_MANUAL_TRACKING, $manualTracking);
	}
	public function setMaximumAllowedDailyValue(float $maximumAllowedDailyValue): void{
		$this->setAttribute(Variable::FIELD_MAXIMUM_ALLOWED_DAILY_VALUE, $maximumAllowedDailyValue);
	}
	public function setMaximumRecordedValue(float $maximumRecordedValue): void{
		$this->setAttribute(Variable::FIELD_MAXIMUM_RECORDED_VALUE, $maximumRecordedValue);
	}
	public function setMean(float $mean): void{
		$this->setAttribute(Variable::FIELD_MEAN, $mean);
	}
	public function getAvatar(): string{
		return $this->getImage();
	}
	public function setMedian(float $median): void{
		$this->setAttribute(Variable::FIELD_MEDIAN, $median);
	}
	public function setMedianSecondsBetweenMeasurements(int $medianSecondsBetweenMeasurements): void{
		$this->setAttribute(Variable::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS, $medianSecondsBetweenMeasurements);
	}

	public function setMinimumRecordedValue(float $minimumRecordedValue): void{
		$this->setAttribute(Variable::FIELD_MINIMUM_RECORDED_VALUE, $minimumRecordedValue);
	}
	/**
	 * @param $value
	 */
	public function setMostCommonConnectorIdAttribute($value){
		if($value !== null && empty($value)){
			le('$value !== null && empty($value)');
		}
		$this->attributes[self::FIELD_MOST_COMMON_CONNECTOR_ID] = $value;
	}
	public function setMostCommonOriginalUnitId(int $mostCommonOriginalUnitId): void{
		$this->setAttribute(Variable::FIELD_MOST_COMMON_ORIGINAL_UNIT_ID, $mostCommonOriginalUnitId);
	}
	public function setMostCommonSourceName(string $mostCommonSourceName): void{
		$this->setAttribute(Variable::FIELD_MOST_COMMON_SOURCE_NAME, $mostCommonSourceName);
	}
	/**
	 * @param $val
	 */
	public function setMostCommonSourceNameAttribute($val){
		$this->attributes[self::FIELD_MOST_COMMON_SOURCE_NAME] = $val;
	}
	public function setMostCommonValue(float $mostCommonValue): void{
		$this->setAttribute(Variable::FIELD_MOST_COMMON_VALUE, $mostCommonValue);
	}
	public function setNewestDataAt(string $newestDataAt): void{
		$this->setAttribute(Variable::FIELD_NEWEST_DATA_AT, $newestDataAt);
	}
	public function setNumberCommonTaggedBy(int $numberCommonTaggedBy): void{
		$this->setAttribute(Variable::FIELD_NUMBER_COMMON_TAGGED_BY, $numberCommonTaggedBy);
	}




	public function setNumberOfCommonChildren(int $numberOfCommonChildren): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_COMMON_CHILDREN, $numberOfCommonChildren);
	}
	public function setNumberOfCommonFoods(int $numberOfCommonFoods): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_COMMON_FOODS, $numberOfCommonFoods);
	}
	public function setNumberOfCommonIngredients(int $numberOfCommonIngredients): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_COMMON_INGREDIENTS, $numberOfCommonIngredients);
	}
	public function setNumberOfCommonJoinedVariables(int $numberOfCommonJoinedVariables): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_COMMON_JOINED_VARIABLES, $numberOfCommonJoinedVariables);
	}
	public function setNumberOfCommonParents(int $numberOfCommonParents): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_COMMON_PARENTS, $numberOfCommonParents);
	}


	public function setNumberOfMeasurements(?int $numberOfMeasurements): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_MEASUREMENTS, $numberOfMeasurements);
	}
	public function setNumberOfOutcomeCaseStudies(int $numberOfOutcomeCaseStudies): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES, $numberOfOutcomeCaseStudies);
	}
	public function setNumberOfOutcomePopulationStudies(int $numberOfOutcomePopulationStudies): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_OUTCOME_POPULATION_STUDIES, $numberOfOutcomePopulationStudies);
	}
	public function setNumberOfPredictorCaseStudies(int $numberOfPredictorCaseStudies): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_PREDICTOR_CASE_STUDIES, $numberOfPredictorCaseStudies);
	}


	public function setNumberOfSoftDeletedMeasurements(int $numberOfSoftDeletedMeasurements): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_SOFT_DELETED_MEASUREMENTS, $numberOfSoftDeletedMeasurements);
	}
	public function setNumberOfStudiesWhereCauseVariable(int $numberOfStudiesWhereCauseVariable): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_STUDIES_WHERE_CAUSE_VARIABLE, $numberOfStudiesWhereCauseVariable);
	}


	public function setNumberOfTrackingReminders(int $numberOfTrackingReminders): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_TRACKING_REMINDERS, $numberOfTrackingReminders);
	}
	public function setNumberOfUniqueValues(int $numberOfUniqueValues): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_UNIQUE_VALUES, $numberOfUniqueValues);
	}
	public function setNumberOfUserChildren(int $numberOfUserChildren): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_USER_CHILDREN, $numberOfUserChildren);
	}
	public function setNumberOfUserFoods(int $numberOfUserFoods): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_USER_FOODS, $numberOfUserFoods);
	}
	public function setNumberOfUserIngredients(int $numberOfUserIngredients): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_USER_INGREDIENTS, $numberOfUserIngredients);
	}
	public function setNumberOfUserJoinedVariables(int $numberOfUserJoinedVariables): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_USER_JOINED_VARIABLES, $numberOfUserJoinedVariables);
	}
	public function setNumberOfUserParents(int $numberOfUserParents): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_USER_PARENTS, $numberOfUserParents);
	}
	public function setNumberOfUserTagsWhereTagVariable(int $numberOfUserTagsWhereTagVariable): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_USER_TAGS_WHERE_TAG_VARIABLE, $numberOfUserTagsWhereTagVariable);
	}

	public function setNumberOfUserVariables(int $numberOfUserVariables): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_USER_VARIABLES, $numberOfUserVariables);
	}



	public function setNumberOfVotesWhereCauseVariable(int $numberOfVotesWhereCauseVariable): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_VOTES_WHERE_CAUSE_VARIABLE, $numberOfVotesWhereCauseVariable);
	}
	public function setNumberOfVotesWhereEffectVariable(int $numberOfVotesWhereEffectVariable): void{
		$this->setAttribute(Variable::FIELD_NUMBER_OF_VOTES_WHERE_EFFECT_VARIABLE, $numberOfVotesWhereEffectVariable);
	}
	/**
	 * @param $val
	 */
	public function setOnsetDelayAttribute($val){
		if($this->variable_category_id){
			$cat = $this->getQMVariableCategory();
			if($cat->onsetDelay === $val){
				//le('$cat->onsetDelay === $val');
			}
		}
		$this->attributes[self::FIELD_ONSET_DELAY] = $val;
	}
	public function setOptimalValueMessage(string $optimalValueMessage): void{
		$this->setAttribute(Variable::FIELD_OPTIMAL_VALUE_MESSAGE, $optimalValueMessage);
	}
	public function setParentId(int $parentId): void{
		$this->setAttribute(Variable::FIELD_PARENT_ID, $parentId);
	}
	/**
	 * @param $value
	 */
	public function setProductUrlAttribute($value){
		if($value === false){
			$value = "0";
		}
		$this->attributes[self::FIELD_PRODUCT_URL] = $value;
	}
	public function setReasonForAnalysis(string $reasonForAnalysis): void{
		$this->setAttribute(Variable::FIELD_REASON_FOR_ANALYSIS, $reasonForAnalysis);
	}
	public function setRecordSizeInKb(int $recordSizeInKb): void{
		$this->setAttribute(Variable::FIELD_RECORD_SIZE_IN_KB, $recordSizeInKb);
	}
	public function setSecondMostCommonValue(float $secondMostCommonValue): void{
		$this->setAttribute(Variable::FIELD_SECOND_MOST_COMMON_VALUE, $secondMostCommonValue);
	}
	public function setSkewness(float $skewness): void{
		$this->setAttribute(Variable::FIELD_SKEWNESS, $skewness);
	}
	public function setStandardDeviation(float $standardDeviation): void{
		$this->setAttribute(Variable::FIELD_STANDARD_DEVIATION, $standardDeviation);
	}
	public function getClientId(): ?string{
		return $this->attributes[Variable::FIELD_CLIENT_ID] ?? null;
	}
	public function setStatus(string $status): void{
		$this->setAttribute(Variable::FIELD_STATUS, $status);
	}
	/**
	 * @param $value
	 */
	public function setStatusAttribute($value){
		if(empty($value)){
			le('empty($value)');
		}
		if(empty($value)){
			$value = null;
		}
		$this->attributes[self::FIELD_STATUS] = $value;
	}
	public function setThirdMostCommonValue(float $thirdMostCommonValue): void{
		$this->setAttribute(Variable::FIELD_THIRD_MOST_COMMON_VALUE, $thirdMostCommonValue);
	}
	public function getCreatedAt(): ?string{
		return $this->attributes[Variable::FIELD_CREATED_AT] ?? null;
	}
	public function setUpc12(string $upc12): void{
		$this->setAttribute(Variable::FIELD_UPC_12, $upc12);
	}
	public function setUpc14(string $upc14): void{
		$this->setAttribute(Variable::FIELD_UPC_14, $upc14);
	}
	public function setVariableCategoryId(int $variableCategoryId): void{
		$this->setAttribute(Variable::FIELD_VARIABLE_CATEGORY_ID, $variableCategoryId);
	}
	public function setVariance(float $variance): void{
		$this->setAttribute(Variable::FIELD_VARIANCE, $variance);
	}
	public function setWikipediaTitle(string $wikipediaTitle): void{
		$this->setAttribute(Variable::FIELD_WIKIPEDIA_TITLE, $wikipediaTitle);
	}
	public function setWikipediaUrl(string $wikipediaUrl): void{
		$this->setAttribute(Variable::FIELD_WIKIPEDIA_URL, $wikipediaUrl);
	}
	public function setWpPostId(int $wpPostId): void{
		$this->setAttribute(Variable::FIELD_WP_POST_ID, $wpPostId);
	}
	/**
	 * @return Variable[]
	 */
	public function side_effect_variables(): array{
		$variables = [];
		/** @var CtTreatmentSideEffect[] $condition_treatments */
		$condition_treatments = $this->treatment_side_effects_where_treatment()->get();
		foreach($condition_treatments as $ct){
			$v = $ct->side_effect_variable;
			$variables[$v->name] = $v;
		}
		return $variables;
	}
	public function treatment_side_effects_where_treatment(): HasMany{
		return $this->hasMany(CtTreatmentSideEffect::class, CtTreatmentSideEffect::FIELD_TREATMENT_VARIABLE_ID,
			Variable::FIELD_ID)->with('side_effect_variable');
	}
	/**
	 * @return array
	 */
	public function toNamesArray(): array{
		$variable = $this->toArray();
		if(!empty($variable['category'])){
			$variable['category'] = $variable['category']['name'];
		} else{
			$variable['category'] = null;
		}
		if(!empty($variable['default_unit'])){
			$variable['default_unit'] = $variable['default_unit']['name'];
		} else{
			$variable['default_unit'] = null;
		}
		if(!empty($variable['most_common_unit'])){
			$variable['most_common_unit'] = $variable['most_common_unit']['name'];
		} else{
			$variable['most_common_unit'] = null;
		}
		return $variable;
	}
	/**
	 * @return CtTreatment|null
	 */
	public function treatment(): ?CtTreatment{
		return CtTreatment::whereVariableId($this->id)->first();
	}
	/**
	 * @return Variable[]
	 */
	public function treatment_variables_where_condition(): array{
		$variables = [];
		/** @var CtConditionTreatment[] $condition_treatments */
		$condition_treatments = $this->condition_treatments_where_condition()->get();
		foreach($condition_treatments as $ct){
			$v = $ct->treatment_variable;
			$variables[$v->name] = $v;
		}
		return $variables;
	}
	/**
	 * @return HasMany
	 */
	public function condition_treatments_where_condition(): HasMany{
		return $this->hasMany(CtConditionTreatment::class, CtConditionTreatment::FIELD_CONDITION_VARIABLE_ID,
			Variable::FIELD_ID)->with('treatment_variable');
	}
	/**
	 * @param Measurement[] $measurements
	 * Keep this to avoid creating duplicate function
	 * @throws ModelValidationException
	 */
	public function updateFromMeasurements(array $measurements){
		$byDate = MeasurementStartAtProperty::indexAscending($measurements);
		$values = QMArr::pluckColumn($byDate, 'value');
		$max = max($values);
		$min = min($values);
		$number = count($measurements);
		$uniqueReversed = array_values(array_unique(array_reverse($values)));
		$last = end($byDate);
		$first = reset($byDate);
		$latestAt = MeasurementStartAtProperty::pluck($last);
		$earliestAt = MeasurementStartAtProperty::pluck($first);
		$this->setAttributeIfNull(self::FIELD_MOST_COMMON_CONNECTOR_ID,
			MeasurementConnectorIdProperty::pluckOrDefault($last));
		if($dataSource = MeasurementSourceNameProperty::pluckOrDefault($last)){
			$this->addDataSource($dataSource);
		}
		$this->updateLatestAttributes($latestAt); // Pluck goes through rounding process again
		$this->updateEarliestAttributes($earliestAt);  // Pluck goes through rounding process again
		if($number < $this->number_of_measurements){ // Incremental analysis
			$this->setIfGreaterThanExisting(self::FIELD_MAXIMUM_RECORDED_VALUE, $max);
			$this->setIfLessThanExisting(self::FIELD_MINIMUM_RECORDED_VALUE, $min);
			$this->setIfGreaterThanExisting(self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN, $number);
			$this->setIfGreaterThanExisting(self::FIELD_NUMBER_OF_MEASUREMENTS, $number);
			$this->setIfGreaterThanExisting(self::FIELD_NUMBER_OF_UNIQUE_VALUES, count($uniqueReversed));
		} else{ // Full analysis
			$this->setAttribute(self::FIELD_MAXIMUM_RECORDED_VALUE, $max);
			$this->setAttribute(self::FIELD_MINIMUM_RECORDED_VALUE, $min);
			$this->setAttribute(self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN, $number);
			$this->setAttribute(self::FIELD_NUMBER_OF_MEASUREMENTS, $number);
			$this->setAttribute(self::FIELD_NUMBER_OF_UNIQUE_VALUES, count($uniqueReversed));
		}
		$this->save();
	}
	/**
	 * @param string $dataSource
	 */
	public function addDataSource(string $dataSource): void{
		$dsc = $this->data_sources_count ?? [];
		if(!isset($dsc[$dataSource])){
			try {
				$dsc[$dataSource] = 1;
			} catch (\Throwable $e) {
				$dsc = $this->data_sources_count ?? [];
			}
			$this->data_sources_count = $dsc;
		}
	}
	/**
	 * @param string|int $timeAt
	 */
	public function updateLatestAttributes($timeAt){
		$fields = static::getColumns();
		foreach($fields as $key){
			if(stripos($key, 'latest_') !== false){
				if(TimeHelper::isCarbon($timeAt)){
					$timeAt = (strpos($key, '_at')) ? db_date($timeAt) : $timeAt->timestamp;
				}
				$this->setIfGreaterThanExisting($key, $timeAt);
			}
		}
	}
	/**
	 * @param string|int $timeAt
	 */
	public function updateEarliestAttributes($timeAt): void{
		$fields = static::getColumns();
		foreach($fields as $key){
			if(strpos($key, 'earliest_') !== false){
				if(TimeHelper::isCarbon($timeAt)){
					/** @var CarbonInterface $timeAt */
					$timeAt = (strpos($key, '_at')) ? db_date($timeAt) : $timeAt->timestamp;
				}
				$this->setIfLessThanExisting($key, $timeAt);
			}
		}
	}
	public function updateFromUserVariable(UserVariable $uv): bool{
		$this->setAttributeIfNull(self::FIELD_DATA_SOURCES_COUNT, $uv->data_sources_count);
		$this->setIfGreaterThanExisting(self::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT,
			$uv->latest_non_tagged_measurement_start_at);
		$this->setIfGreaterThanExisting(self::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT,
			$uv->latest_tagged_measurement_start_at);
		$this->setIfGreaterThanExisting(self::FIELD_NUMBER_OF_MEASUREMENTS, $uv->number_of_measurements);
		$this->setIfGreaterThanExisting(self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN,
			$uv->number_of_raw_measurements_with_tags_joins_children);
		$this->setIfGreaterThanExisting(self::FIELD_NUMBER_OF_USER_VARIABLES, 1);
		$this->setIfLessThanExisting(self::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT,
			$uv->earliest_non_tagged_measurement_start_at);
		$this->setIfLessThanExisting(self::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT,
			$uv->earliest_tagged_measurement_start_at);
		$changes = $this->getDirty();
		if($changes){
			$this->newest_data_at = now_at();
			try {
				$this->save();
			} catch (ModelValidationException $e) {
				le($e);
			}
		}
		return false;
	}
	public function user_tagged_by(): HasMany{
		return $this->hasMany(UserTag::class, UserTag::FIELD_TAG_VARIABLE_ID);
	}
	public function user_variables_excluding_test_users(): HasMany{
		return $this->user_variables()->whereNotIn(UserVariable::FIELD_USER_ID, BaseUserIdProperty::getTestUserIds());
	}
	public function votes_where_cause(): HasMany{
		return $this->hasMany(Vote::class, Vote::FIELD_EFFECT_VARIABLE_ID);
	}
	public function votes_where_effect(): HasMany{
		return $this->hasMany(Vote::class, Vote::FIELD_EFFECT_VARIABLE_ID);
	}
	/**
	 * @param string $attr
	 * @return array|mixed|null
	 */
	protected function getAttributeFromHardCodedVariableIfNecessary(string $attr){
		$fromVar = $this->getRawAttribute(Variable::FIELD_MINIMUM_ALLOWED_VALUE);
		if($fromVar === null && $this->id){
			$hard = $this->getHardCodedVariable();
			if($hard){
				$camel = QMStr::camelize($attr);
				$fromVar = $hard->$camel ?? null;
				if($fromVar !== null){
					$this->setAttribute($attr, $fromVar);
				}
			}
		}
		return $fromVar;
	}
	public function hasFillingValue(): bool{
		return BaseFillingValueProperty::hasFillingValue($this);
	}
	public function getUrl(array $params = []): string{
		return $this->getShowUrl($params);
	}
	public function getVariableIdAttribute():?int{
		return $this->attributes[self::FIELD_ID] ?? null;
	}
	public function isSum(): bool{
		return $this->combination_operation === BaseCombinationOperationProperty::COMBINATION_SUM;
	}
	/**
	 * @return int
	 */
	public function getNumberOfChanges(): ?int{
		if($this->relationLoaded('user_variables')){
			$uv = $this->user_variables;
		} else{
			$uv = $this->user_variables();
		}
		return $uv->pluck(UserVariable::FIELD_NUMBER_OF_CHANGES)->sum();
	}
	/**
	 * @return string
	 */
	public function getMeasurementQuantitySentence(): string{
		$sentence = strtoupper($this->getDisplayNameAttribute()) . " Data Quantity:\n";
		$numberTagged = $this->getOrCalculateNumberOfRawMeasurementsWithTagsJoinsChildren();
		if(!$numberTagged){
			$sentence .= $this->getNumberOfTaggedMeasurementsSentence();
		} else{
			$sentence .= $this->getNumberOfRawMeasurementsSentence() . "\n";
			$sentence .= $this->getNumberOfTaggedMeasurementsSentence() . "\n";
		}
		return $sentence;
	}
    /**
     * Save a new model and return the instance.
     * @param array $attributes
     * @return Model|$this
     * @static
     */
    public static function create(array $attributes = []){
        $data = QMCommonVariable::formatNewVariableData($attributes,
            VariableNameProperty::pluck($attributes));
        Variable::unguard(true);
        $v = parent::create($data);
        Variable::unguard(false);
        return $v;
        //return parent::create($attributes);
    }
	public static function search(string $searchTerm = '', $callback = null){
		$results = parent::search($searchTerm, $callback);
		if(!$results->count()){ // Search exact match without regard for is_public
			$qb = static::setEagerLoads([])
			            ->select(static::getMinimalFields());
			$results = $qb->where(static::FIELD_NAME,$searchTerm)
			              ->get();
		}
		return $results;
	}
	public function getOutcomesLabelHtml(): string{
		$label = HtmlHelper::renderView(view('nutrition-label-outcomes', [
			'js' => $this->getShowJsTag(true),
		]));
		return $label;
	}
	/**
	 * @return \Illuminate\Support\Collection|int[]
	 */
	public static function getScoreVariableIds(): \Illuminate\Support\Collection {
		$builder = static::whereNameLike(" score");
		$builder->orWhere(Variable::FIELD_NAME, SleepDurationCommonVariable::NAME);
		$builder->orWhere(Variable::FIELD_NAME, SleepEfficiencyFromFitbitCommonVariable::NAME);
		$builder->orWhere(Variable::FIELD_NAME, RestingHeartRatePulseCommonVariable::NAME);
		$builder->orWhere(Variable::FIELD_NAME, DailyStepCountCommonVariable::NAME);
		$vars = $builder->pluck(static::FIELD_ID);
		return $vars;
	}
}
