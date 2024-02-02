<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Models;
use App\Actions\ActionEvent;
use App\Astral\Actions\CorrelateAction;
use App\Astral\Actions\FavoriteAction;
use App\Astral\Actions\UnFavoriteAction;
use App\Astral\Lenses\FavoritesLens;
use App\Astral\Lenses\StrategyUserVariablesLens;
use App\Astral\MeasurementBaseAstralResource;
use App\Buttons\Analyzable\CorrelateButton;
use App\Buttons\QMButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Buttons\RelationshipButtons\UserVariable\UserVariableCorrelationsWhereCauseUserVariableButton;
use App\Buttons\RelationshipButtons\UserVariable\UserVariableCorrelationsWhereEffectUserVariableButton;
use App\Buttons\RelationshipButtons\UserVariable\UserVariableMeasurementsButton;
use App\Buttons\RelationshipButtons\UserVariable\UserVariableTrackingRemindersButton;
use App\Buttons\RelationshipButtons\UserVariable\UserVariableUserButton;
use App\Buttons\RelationshipButtons\UserVariable\UserVariableVariableButton;
use App\Buttons\RelationshipButtons\UserVariable\UserVariableVariableCategoryButton;
use App\Buttons\Tracking\NotificationButton;
use App\Buttons\VariableButton;
use App\Cards\FacesRatingQMCard;
use App\Cards\HelpQMCard;
use App\Cards\VariableStatisticsCard;
use App\Charts\ChartGroup;
use App\Charts\UserVariableCharts\UserVariableChartGroup;
use App\VariableRelationships\QMUserVariableRelationship;
use App\DataSources\QMDataSource;
use App\Exceptions\BadRequestException;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\InvalidStringException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoChangesException;
use App\Exceptions\NoEmailAddressException;
use App\Exceptions\NoIdException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NotEnoughVariablesToCorrelateWithException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\UnauthorizedException;
use App\Http\Parameters\IncludeTagsParam;
use App\Http\Resources\UserVariableResource;
use App\Logging\QMLog;
use App\Mail\QMSendgrid;
use App\Mail\TooManyEmailsException;
use App\Menus\JournalMenu;
use App\Menus\QMMenu;
use App\Metrics\Trend;
use App\Models\Base\BaseUserVariable;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Properties\Base\BaseFillingValueProperty;
use App\Properties\Base\BaseNumberOfDaysProperty;
use App\Properties\Base\BaseValenceProperty;
use App\Properties\BaseProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseChangesProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseNumberOfProcessedDailyMeasurementsProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseNumberOfRawMeasurementsProperty;
use App\Properties\Measurement\MeasurementClientIdProperty;
use App\Properties\Measurement\MeasurementConnectorIdProperty;
use App\Properties\Measurement\MeasurementOriginalStartAtProperty;
use App\Properties\Measurement\MeasurementOriginalUnitIdProperty;
use App\Properties\Measurement\MeasurementOriginalValueProperty;
use App\Properties\Measurement\MeasurementSourceNameProperty;
use App\Properties\Measurement\MeasurementStartAtProperty;
use App\Properties\Measurement\MeasurementUnitIdProperty;
use App\Properties\Measurement\MeasurementValueProperty;
use App\Properties\TrackingReminder\TrackingReminderClientIdProperty;
use App\Properties\TrackingReminder\TrackingReminderReminderFrequencyProperty;
use App\Properties\TrackingReminder\TrackingReminderReminderStartTimeProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\UserVariable\UserVariableDefaultUnitIdProperty;
use App\Properties\UserVariable\UserVariableFillingTypeProperty;
use App\Properties\UserVariable\UserVariableFillingValueProperty;
use App\Properties\UserVariable\UserVariableIdProperty;
use App\Properties\UserVariable\UserVariableIsPublicProperty;
use App\Properties\UserVariable\UserVariableLastCorrelatedAtProperty;
use App\Properties\UserVariable\UserVariableMaximumAllowedValueProperty;
use App\Properties\UserVariable\UserVariableMinimumAllowedValueProperty;
use App\Properties\UserVariable\UserVariableNumberOfMeasurementsProperty;
use App\Properties\UserVariable\UserVariableStatusProperty;
use App\Properties\UserVariable\UserVariableUserIdProperty;
use App\Properties\UserVariable\UserVariableValenceProperty;
use App\Properties\UserVariable\UserVariableVariableCategoryIdProperty;
use App\Properties\UserVariable\UserVariableVariableIdProperty;
use App\Properties\Variable\VariableIdProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Properties\VariableCategory\VariableCategoryIdProperty;
use App\Reports\AnalyticalReport;
use App\Reports\RootCauseAnalysis;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Slim\Model\Measurement\AnonymousMeasurement;
use App\Slim\Model\Measurement\DailyMeasurement;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Notifications\IndividualPushNotificationData;
use App\Slim\Model\Notifications\QMDeviceToken;
use App\Slim\Model\QMUnit;
use App\Storage\DB\Adminer;
use App\Storage\DB\QMQB;
use App\Storage\S3\S3Private;
use App\Tables\QMTable;
use App\Traits\AnalyzableTrait;
use App\Traits\HasButton;
use App\Traits\HasCharts;
use App\Traits\HasDBModel;
use App\Traits\HasErrors;
use App\Traits\HasFiles;
use App\Traits\HasModel\HasUnit;
use App\Traits\HasModel\HasUserVariable;
use App\Traits\HasModel\HasVariableCategory;
use App\Traits\HasName;
use App\Traits\HasOutcomesAndPredictors;
use App\Traits\HasProperty\HasOnsetAndDuration;
use App\Traits\IsEditable;
use App\Traits\PropertyTraits\UserHyperParameterTrait;
use App\Traits\SavesToRepo;
use App\Traits\VariableValueTraits\UserVariableValuePropertyTrait;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\Alerter;
use App\UI\CssHelper;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\QMColor;
use App\Units\PercentUnit;
use App\Utils\AppMode;
use App\Utils\IonicHelper;
use App\Utils\Stats;
use App\VariableCategories\InvestmentStrategiesVariableCategory;
use App\VariableCategories\MiscellaneousVariableCategory;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\QMUserVariable;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Overtrue\LaravelFavorite\Traits\Favoriteable;
use App\Nfts\Traits\Tokenizable;
use SendGrid\Mail\TypeException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\QueryBuilder\QueryBuilder;
use Titasgailius\SearchRelations\SearchesRelations;
/**
 * @mixin QMUserVariable
 * App\Models\UserVariable
 * @property integer $parent_id ID of the parent variable if this variable has any parent
 * @property string $client_id
 * @property integer $user_id User ID
 * @property integer $variable_id ID of variable
 * @property integer $default_unit_id ID of unit to use for this variable
 * @property float $minimum_allowed_value Minimum reasonable value for this variable (uses default unit)
 * @property float $maximum_allowed_value Maximum reasonable value for this variable (uses default unit)
 * @property float $filling_value Value for replacing null measurements
 * @property integer $join_with The Variable this Variable should be joined with. If the variable is joined with some
 *     other variable then it is not shown to user in the list of variables
 * @property integer $onset_delay How long it takes for a measurement in this variable to take effect
 * @property integer $duration_of_action Estimated duration of time following the onset delay in which a stimulus
 *     produces a perceivable effect
 * @property integer $variable_category_id ID of variable category
 * @property integer $updated
 * @property integer $public Is variable public
 * @property boolean $cause_only A value of 1 indicates that this variable is generally a cause in a causal
 *     relationship.  An example of a causeOnly variable would be a variable such as Cloud Cover which would generally
 *     not be influenced by the behaviour of the user
 * @property string $filling_type 0 -> No filling, 1 -> Use filling-value
 * @property integer $number_of_measurements Number of measurements
 * @property integer $number_of_processed_daily_measurements Number of processed measurements
 * @property integer $measurements_at_last_analysis Number of measurements at last analysis
 * @property integer $last_unit_id ID of last Unit
 * @property integer $last_original_unit_id ID of last original Unit
 * @property float $last_value Last Value
 * @property integer $last_original_value Last original value which is stored
 * @property integer $number_of_correlations Number of user_variable_relationships for this variable
 * @property string $status
 * @property string $error_message
 * @property float $standard_deviation Standard deviation
 * @property float $variance Variance
 * @property float $minimum_recorded_daily_value Minimum recorded daily value of this variable
 * @property float $maximum_recorded_daily_value Maximum recorded daily value of this variable
 * @property float $mean Mean
 * @property float $median Median
 * @property integer $most_common_original_unit_id Most common Unit ID
 * @property float $most_common_value Most common value
 * @property float $number_of_unique_daily_values Number of unique values
 * @property integer $number_of_changes Number of changes
 * @property float $skewness Skewness
 * @property float $kurtosis Kurtosis
 * @property float $latitude Latitude
 * @property float $longitude Longitude
 * @property string $location Location
 * @property Carbon $experiment_start_time
 * @property Carbon $experiment_end_time
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property boolean $outcome Outcome variables (those with `outcome` == 1) are variables for which a human would
 *     generally want to identify the influencing factors.  These include symptoms of illness, physique, mood,
 *     cognitive performance, etc.  Generally correlation calculations are only performed on outcome variables
 * @property string $sources Comma-separated list of source names to limit variables to those sources
 * @property integer $earliest_filling_time Earliest filling time
 * @property integer $latest_filling_time Latest filling time
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereParentId($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereClientId($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereVariableId($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereDefaultUnitId($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereMinimumAllowedValue($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereMaximumAllowedValue($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereFillingValue($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereJoinWith($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereOnsetDelay($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereDurationOfAction($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereUpdated($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable wherePublic($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereCauseOnly($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereFillingType($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereNumberOfMeasurements($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserVariable
 *     whereNumberOfProcessedMeasurements($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereMeasurementsAtLastAnalysis($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereLastUnitId($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereLastOriginalUnitId($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereLastValue($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereLastOriginalValue($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereLastSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereNumberOfCorrelations($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereErrorMessage($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereLastSuccessfulUpdateTime($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereStandardDeviation($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereVariance($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereMinimumRecordedValue($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereMaximumRecordedValue($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereMean($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereMedian($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereMostCommonUnitId($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereMostCommonValue($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereNumberOfUniqueValues($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereNumberOfChanges($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereSkewness($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereKurtosis($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereLatitude($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereLongitude($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereLocation($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereOutcome($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereSources($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereEarliestFillingTime($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereLatestFillingTime($value)
 * @property-read Variable $variable
 * @property-read Unit $defaultUnit
 * @property-read VariableCategory $category
 * @property-read Unit $lastUnit
 * @property-read Unit $lastOriginalUnit
 * @property-read Unit $mostCommonUnit
 * @property-read Source $lastSource
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereMinimumRecordedDailyValue($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereMaximumRecordedDailyValue($value)
 * @method static \Illuminate\Database\Query\Builder|UserVariable whereNumberOfUniqueDailyValues($value)
 * @property float $minimum_recorded_value Minimum recorded value of this variable
 * @property float $maximum_recorded_value Maximum recorded value of this variable
 * @property int|null $number_of_unique_values Number of unique values
 * @property string|null $data_sources_count Array of connector or client measurement data source names as key and
 *     number of measurements as value
 * @property float|null $last_processed_daily_value Last value for user after daily aggregation and filling
 * @property int|null $outcome_of_interest
 * @property int|null $predictor_of_interest
 * @property string|null $description
 * @property string|null $alias
 * @property string|null $deleted_at
 * @property float|null $second_to_last_value
 * @property float|null $third_to_last_value
 * @property int|null $number_of_user_variable_relationships_as_effect Number of user variable relationships for which this variable is the
 *     effect variable
 * @property int|null $number_of_user_variable_relationships_as_cause Number of user variable relationships for which this variable is the
 *     cause variable
 * @property string|null $combination_operation How to combine values of this variable (for instance, to see a summary
 *     of the values over a month) SUM or MEAN
 * @property int $is_public Should data for this variable be publicly available?
 * @property string|null $informational_url Wikipedia url
 * @property int|null $most_common_connector_id
 * @property string|null $valence
 * @property string|null $wikipedia_title
 * @property int $number_of_tracking_reminders
 * @property int|null $number_of_raw_measurements_with_tags_joins_children
 * @property string|null $most_common_source_name
 * @property string|null $optimal_value_message
 * @property int|null $best_cause_variable_id
 * @property int|null $best_effect_variable_id
 * @property string|null $best_user_variable_relationship_id
 * @property QMUserVariableRelationship $best_user_variable_relationship
 * @property float|null $user_maximum_allowed_daily_value
 * @property float|null $user_minimum_allowed_daily_value
 * @property float|null $user_minimum_allowed_non_zero_value
 * @property int|null $minimum_allowed_seconds_between_measurements
 * @property int|null $average_seconds_between_measurements
 * @property int|null $median_seconds_between_measurements
 * @method static Builder|UserVariable newModelQuery()
 * @method static Builder|UserVariable newQuery()
 * @method static Builder|UserVariable query()
 * @method static Builder|UserVariable whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariable
 *     whereAverageSecondsBetweenMeasurements($value)
 * @method static Builder|UserVariable whereBestCauseVariableId($value)
 * @method static Builder|UserVariable whereBestEffectVariableId($value)
 * @method static Builder|UserVariable whereBestUserVariableRelationship($value)
 * @method static Builder|UserVariable whereCombinationOperation($value)
 * @method static Builder|UserVariable whereDataSourcesCount($value)
 * @method static Builder|UserVariable whereDeletedAt($value)
 * @method static Builder|UserVariable whereDescription($value)
 * @method static Builder|UserVariable whereExperimentEndTime($value)
 * @method static Builder|UserVariable whereExperimentStartTime($value)
 * @method static Builder|UserVariable whereInformationalUrl($value)
 * @method static Builder|UserVariable whereLastProcessedDailyValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariable
 *     whereMedianSecondsBetweenMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariable
 *     whereMinimumAllowedSecondsBetweenMeasurements($value)
 * @method static Builder|UserVariable whereMostCommonConnectorId($value)
 * @method static Builder|UserVariable whereMostCommonOriginalUnitId($value)
 * @method static Builder|UserVariable whereMostCommonSourceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariable
 *     whereNumberOfProcessedDailyMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariable
 *     whereNumberOfRawMeasurementsWithTagsJoinsChildren($value)
 * @method static Builder|UserVariable whereNumberOfTrackingReminders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariable
 *     whereNumberOfUserVariableRelationshipsAsCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariable
 *     whereNumberOfUserVariableRelationshipsAsEffect($value)
 * @method static Builder|UserVariable whereOptimalValueMessage($value)
 * @method static Builder|UserVariable whereOutcomeOfInterest($value)
 * @method static Builder|UserVariable wherePredictorOfInterest($value)
 * @method static Builder|UserVariable whereSecondToLastValue($value)
 * @method static Builder|UserVariable whereShareUserMeasurements($value)
 * @method static Builder|UserVariable whereThirdToLastValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariable
 *     whereUserMaximumAllowedDailyValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariable
 *     whereUserMinimumAllowedDailyValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariable
 *     whereUserMinimumAllowedNonZeroValue($value)
 * @method static Builder|UserVariable whereValence($value)
 * @method static Builder|UserVariable whereWikipediaTitle($value)
 * @mixin Eloquent
 * @method static Builder|UserVariable whereAdditionalMetaData($value)
 * @property int $id
 * @property string|null $last_correlated_at
 * @property int|null $number_of_measurements_with_tags_at_last_correlation
 * @property string|null $analysis_settings_modified_at
 * @property string|null $newest_data_at
 * @property string|null $analysis_ended_at
 * @property string|null $analysis_requested_at
 * @property string|null $reason_for_analysis
 * @property string|null $analysis_started_at
 * @property string|null $user_error_message
 * @property string|null $internal_error_message
 * @method static Builder|UserVariable whereAnalysisEndedAt($value)
 * @method static Builder|UserVariable whereAnalysisRequestedAt($value)
 * @method static Builder|UserVariable whereAnalysisSettingsModifiedAt($value)
 * @method static Builder|UserVariable whereAnalysisStartedAt($value)
 * @method static Builder|UserVariable whereId($value)
 * @method static Builder|UserVariable whereInternalErrorMessage($value)
 * @method static Builder|UserVariable whereLastCorrelatedAt($value)
 * @method static Builder|UserVariable whereNewestDataAt($value)
 * @method static Builder|UserVariable whereNumberOfMeasurementsWithTagsAtLastCorrelation($value)
 * @method static Builder|UserVariable whereReasonForAnalysis($value)
 * @method static Builder|UserVariable whereUserErrorMessage($value)
 * @property \Illuminate\Support\Carbon|null $earliest_source_measurement_start_at
 * @property \Illuminate\Support\Carbon|null $latest_source_measurement_start_at
 * @property \Illuminate\Support\Carbon|null $latest_tagged_measurement_start_at
 * @property \Illuminate\Support\Carbon|null $earliest_tagged_measurement_start_at
 * @property \Illuminate\Support\Carbon|null $latest_non_tagged_measurement_start_at
 * @property \Illuminate\Support\Carbon|null $earliest_non_tagged_measurement_start_at
 * @property-read OAClient|null $oa_client
 * @property-read Collection|UserVariableRelationship[] $user_variable_relationships
 * @property-read int|null $correlations_count
 * @property-read Collection|Measurement[] $measurements
 * @property-read int|null $measurements_count
 * @property-read Collection|TrackingReminder[] $tracking_reminders
 * @property-read int|null $tracking_reminders_count
 * @property-read User $user
 * @property-read int|null $variable_user_sources_count
 * @method static Builder|UserVariable whereEarliestNonTaggedMeasurementStartAt($value)
 * @method static Builder|UserVariable whereEarliestSourceMeasurementStartAt($value)
 * @method static Builder|UserVariable whereEarliestTaggedMeasurementStartAt($value)
 * @method static Builder|UserVariable whereLatestNonTaggedMeasurementStartAt($value)
 * @method static Builder|UserVariable whereLatestSourceMeasurementStartAt($value)
 * @method static Builder|UserVariable whereLatestTaggedMeasurementStartAt($value)
 * @property int|null $wp_post_id
 * @method static Builder|UserVariable whereWpPostId($value)
 * @property int|null $number_of_soft_deleted_measurements Formula: update user_variables v
 *                 inner join (
 *                     select measurements.user_variable_id, count(measurements.id) as
 *     number_of_soft_deleted_measurements from measurements where measurements.deleted_at is not null group by
 *     measurements.user_variable_id
 *                     ) m on v.id = m.user_variable_id
 *                 set v.number_of_soft_deleted_measurements = m.number_of_soft_deleted_measurements
 * @method static Builder|UserVariable whereNumberOfSoftDeletedMeasurements($value)
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @property-read Unit|null $default_unit
 * @property-read Unit|null $last_unit
 * @property-read Collection|TrackingReminderNotification[] $tracking_reminder_notifications
 * @property-read int|null $tracking_reminder_notifications_count
 * @property-read Collection|UserVariableClient[] $user_variable_clients
 * @property-read int|null $user_variable_clients_count
 * @property-read VariableCategory|null $variable_category
 * @property-read WpPost|null $wp_post
 * @method static Builder|UserVariable whereBestUserVariableRelationshipId($value)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read Collection|UserVariableRelationship[] $correlations_where_cause_user_variable
 * @property-read int|null $correlations_where_cause_user_variable_count
 * @property-read Collection|UserVariableRelationship[] $correlations_where_effect_user_variable
 * @property-read int|null $correlations_where_effect_user_variable_count
 * @property int|null $number_of_outcome_case_studies Number of Individual Case Studies for this Cause User Variable.
 *                     [Formula: update user_variables
 *                         left join (
 *                             select count(id) as total, cause_user_variable_id
 *                             from user_variable_relationships
 *                             group by cause_user_variable_id
 *                         )
 *                         as grouped on user_variables.id = grouped.cause_user_variable_id
 *                     set user_variables.number_of_outcome_case_studies = count(grouped.total)]
 * @property int|null $number_of_tracking_reminder_notifications Number of Tracking Reminder Notifications for this
 *     User Variable.
 *                     [Formula: update user_variables
 *                         left join (
 *                             select count(id) as total, user_variable_id
 *                             from tracking_reminder_notifications
 *                             group by user_variable_id
 *                         )
 *                         as grouped on user_variables.id = grouped.user_variable_id
 *                     set user_variables.number_of_tracking_reminder_notifications = count(grouped.total)]
 * @method static Builder|UserVariable whereNumberOfOutcomeCaseStudies($value)
 * @method static Builder|UserVariable whereNumberOfPredictorCaseStudies($value)
 * @method static Builder|UserVariable whereNumberOfTrackingReminderNotifications($value)
 * @method static Builder|UserVariable whereNumberOfTrackingRemindersWhereVariable($value)
 * @property-read Collection|ActionEvent[] $actions
 * @property-read int|null $actions_count
 * @property string|null $deletion_reason The reason the variable was deleted.
 * @property int|null $record_size_in_kb
 * @method static Builder|UserVariable whereDeletionReason($value)
 * @method static Builder|UserVariable whereRecordSizeInKb($value)
 * @property int|null $number_of_common_tags Number of categories, joined variables, or ingredients for this variable
 *     that use this variables measurements to generate synthetically derived measurements.
 * @property int|null $number_common_tagged_by Number of children, joined variables or foods that this use has
 *     measurements for which are to be used to generate synthetic measurements for this variable.
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
 * @property int|null $number_of_user_tags Number of categories, joined variables, or ingredients for this variable
 *     that use this variables measurements to generate synthetically derived measurements. This only includes ones
 *     created by the user.
 * @property int|null $number_user_tagged_by Number of children, joined variables or foods that this use has
 *     measurements for which are to be used to generate synthetic measurements for this variable. This only includes
 *     ones created by the user.
 * @property int|null $number_of_user_joined_variables Joined variables are duplicate variables measuring the same
 *     thing. This only includes ones created by the user.
 * @property int|null $number_of_user_ingredients Measurements for this variable can be used to synthetically generate
 *     ingredient measurements. This only includes ones created by the user.
 * @property int|null $number_of_user_foods Measurements for this ingredient variable can be synthetically generate by
 *     food measurements. This only includes ones created by the user.
 * @property int|null $number_of_user_children Measurements for this parent category variable can be synthetically
 *     generated by measurements from its child variables. This only includes ones created by the user.
 * @property int|null $number_of_user_parents Measurements for this parent category variable can be synthetically
 *     generated by measurements from its child variables. This only includes ones created by the user.
 * @property-read Collection|User[] $favoriters
 * @property-read int|null $favoriters_count
 * @property-read Collection|\Overtrue\LaravelFavorite\Favorite[] $favorites
 * @property-read int|null $favorites_count
 * @property mixed $raw
 * @property-read mixed $raw_variable
 * @method static Builder|UserVariable whereEarliestMeasurementTime($value)
 * @method static Builder|UserVariable whereEarliestNonTaggedMeasurementTime($value)
 * @method static Builder|UserVariable whereEarliestSourceTime($value)
 * @method static Builder|UserVariable whereEarliestTaggedMeasurementTime($value)
 * @method static Builder|UserVariable whereLatestMeasurementTime($value)
 * @method static Builder|UserVariable whereLatestNonTaggedMeasurementTime($value)
 * @method static Builder|UserVariable whereLatestSourceTime($value)
 * @method static Builder|UserVariable whereLatestTaggedMeasurementTime($value)
 * @method static Builder|UserVariable whereNumberCommonTaggedBy($value)
 * @method static Builder|UserVariable whereNumberOfCommonChildren($value)
 * @method static Builder|UserVariable whereNumberOfCommonFoods($value)
 * @method static Builder|UserVariable whereNumberOfCommonIngredients($value)
 * @method static Builder|UserVariable whereNumberOfCommonJoinedVariables($value)
 * @method static Builder|UserVariable whereNumberOfCommonParents($value)
 * @method static Builder|UserVariable whereNumberOfCommonTags($value)
 * @method static Builder|UserVariable whereNumberOfUserChildren($value)
 * @method static Builder|UserVariable whereNumberOfUserFoods($value)
 * @method static Builder|UserVariable whereNumberOfUserIngredients($value)
 * @method static Builder|UserVariable whereNumberOfUserJoinedVariables($value)
 * @method static Builder|UserVariable whereNumberOfUserParents($value)
 * @method static Builder|UserVariable whereNumberOfUserTags($value)
 * @method static Builder|UserVariable whereNumberUserTaggedBy($value)
 * @property int|null $is_goal The effect of a food on the severity of a symptom is useful because you can control the
 *     predictor directly. However, the effect of a symptom on the foods you eat is not very useful.  The foods you eat
 *     are not generally an objective end in themselves.
 * @property int|null $controllable You can control the foods you eat directly. However, symptom severity or weather is
 *     not directly controllable.
 * @property int|null $boring The user variable is boring if the owner would not be interested in its causes or
 *     effects.
 * @property-read Variable|null $best_cause_variable
 * @property-read Variable|null $best_effect_variable
 * @property-read Connector|null $most_common_connector
 * @property-read Collection|UserVariableRelationship[] $outcomes
 * @property-read int|null $outcomes_count
 * @property-read Collection|UserVariableRelationship[] $predictors
 * @property-read int|null $predictors_count
 * @method static Builder|UserVariable whereBoring($value)
 * @method static Builder|UserVariable whereControllable($value)
 * @method static Builder|UserVariable whereIsGoal($value)
 * @method static Builder|UserVariable whereIsPublic($value)
 * @property string|null $slug The slug is the part of a URL that identifies a page in human-readable keywords.
 * @property int|null $predictor predictor is true if the variable is a factor that could influence an outcome of
 *     interest
 * @property-read Collection|UserVariableRelationship[] $best_correlations_where_cause_user_variable
 * @property-read int|null $best_correlations_where_cause_user_variable_count
 * @property-read Collection|UserVariableRelationship[] $best_correlations_where_effect_user_variable
 * @property-read int|null $best_correlations_where_effect_user_variable_count
 * @property-read OAClient|null $client
 * @property-read Collection|UserTag[] $user_tags_where_tag_user_variable
 * @property-read int|null $user_tags_where_tag_user_variable_count
 * @property-read Collection|UserTag[] $user_tags_where_tagged_user_variable
 * @property-read int|null $user_tags_where_tagged_user_variable_count
 * @property-read Collection|UserVariableOutcomeCategory[] $user_variable_outcome_categories
 * @property-read int|null $user_variable_outcome_categories_count
 * @property-read Collection|UserVariablePredictorCategory[] $user_variable_predictor_categories
 * @property-read int|null $user_variable_predictor_categories_count
 * @method static Builder|UserVariable wherePredictor($value)
 * @method static Builder|UserVariable whereSlug($value)
 */
class UserVariable extends BaseUserVariable implements HasMedia {
    use HasFactory;
	use Tokenizable;
	use AnalyzableTrait;
	use HasErrors;
	use HasUnit;
	use Favoriteable;
	use HasDBModel;
	use SearchesRelations;
	use HasOutcomesAndPredictors;
	use HasCharts, HasFiles;
	use HasButton, HasName, HasOnsetAndDuration, HasOutcomesAndPredictors, HasUserVariable, HasVariableCategory, IsEditable, SavesToRepo;
	public const ANALYZABLE           = true;
	public const CLASS_CATEGORY       = Variable::CLASS_CATEGORY;
	public const CLASS_DESCRIPTION    = "Variable statistics, analysis settings, and overviews with data visualizations and likely outcomes or predictors based on data for a specific individual";
	public const COLOR                = QMColor::HEX_GREEN; // Overridden with \App\Astral\UserVariableResource::searchableColumns to exclude id
	public const DEFAULT_IMAGE        = ImageUrls::BASIC_FLAT_ICONS_USER;
	public const DEFAULT_LIMIT        = 20;
	public const DEFAULT_ORDERINGS    = [self::FIELD_NUMBER_OF_MEASUREMENTS => self::ORDER_DIRECTION_DESC];
	public const DEFAULT_SEARCH_FIELD = 'variable.' . Variable::FIELD_NAME;
	public const FONT_AWESOME         = FontAwesome::VIMEO;
	public const IMPORTANT_FIELDS     = self::FIELD_ID . ',' . self::FIELD_BEST_CAUSE_VARIABLE_ID . ',' .
	self::FIELD_BEST_EFFECT_VARIABLE_ID . ',' . self::FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID . ',' .
	self::FIELD_COMBINATION_OPERATION . ',' . self::FIELD_DEFAULT_UNIT_ID . ',' . self::FIELD_EARLIEST_FILLING_TIME .
	',' . self::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT . ',' .
	self::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT . ',' . self::FIELD_EARLIEST_SOURCE_MEASUREMENT_START_AT . ',' .
	self::FIELD_LAST_VALUE . ',' . self::FIELD_LATEST_FILLING_TIME . ',' .
	self::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT . ',' . self::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT . ',' .
	self::FIELD_LATEST_SOURCE_MEASUREMENT_START_AT . ',' . self::FIELD_MAXIMUM_ALLOWED_VALUE . ',' .
	self::FIELD_MAXIMUM_RECORDED_VALUE . ',' . self::FIELD_MEAN . ',' . self::FIELD_MEASUREMENTS_AT_LAST_ANALYSIS .
	',' . self::FIELD_MEDIAN . ',' . self::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS . ',' .
	self::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS . ',' . self::FIELD_MINIMUM_ALLOWED_VALUE . ',' .
	self::FIELD_MINIMUM_RECORDED_VALUE . ',' . self::FIELD_MOST_COMMON_CONNECTOR_ID . ',' .
	self::FIELD_MOST_COMMON_ORIGINAL_UNIT_ID . ',' . self::FIELD_MOST_COMMON_VALUE . ',' .
	self::FIELD_NUMBER_OF_CHANGES . ',' . self::FIELD_NUMBER_OF_CORRELATIONS . ',' .
	self::FIELD_NUMBER_OF_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION . ',' .
	self::FIELD_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS . ',' . self::FIELD_NUMBER_OF_MEASUREMENTS . ',' .
	self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN . ',' . self::FIELD_NUMBER_OF_TRACKING_REMINDERS .
	',' . self::FIELD_NUMBER_OF_UNIQUE_DAILY_VALUES . ',' . self::FIELD_NUMBER_OF_UNIQUE_VALUES . ',' .
	self::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_CAUSE . ',' . self::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_EFFECT . ',' .
	self::FIELD_OPTIMAL_VALUE_MESSAGE . ',' . self::FIELD_OUTCOME . ',' . self::FIELD_IS_PUBLIC . ',' .
	self::FIELD_SECOND_TO_LAST_VALUE . ',' . self::FIELD_THIRD_TO_LAST_VALUE . ',' . self::FIELD_USER_ID . ',' .
	self::FIELD_VALENCE . ',' . self::FIELD_VARIABLE_CATEGORY_ID . ',' . self::FIELD_VARIABLE_ID;
	// TODO: use Cachable;
	const TABLE = 'user_variables';
	/**
	 * The relationship columns that should be searched globally.
	 * @var array
	 */
	public static array $globalSearchRelations = [
		'variable' => [Variable::FIELD_NAME],
	];
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
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [];
	public static $searchRelations = [
		'variable' => [Variable::FIELD_NAME],
	];
	/**
	 * Determine if relations should be searched globally.
	 * @var array
	 */
	public static $searchRelationsGlobally = true;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = Variable::FIELD_NAME;
	public $guarded = [];
	public $hidden = [];
	public $table = "user_variables";
	protected $appends = ['subtitle', 'title', 'charts'];
	protected $casts = [
		self::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS => 'int',
		self::FIELD_BEST_CAUSE_VARIABLE_ID => 'int',
		self::FIELD_BEST_EFFECT_VARIABLE_ID => 'int',
		self::FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID => 'int',
		self::FIELD_CAUSE_ONLY => 'bool',
		self::FIELD_DATA_SOURCES_COUNT => 'array',
		self::FIELD_DEFAULT_UNIT_ID => 'int',
		self::FIELD_DURATION_OF_ACTION => 'int',
		self::FIELD_EARLIEST_FILLING_TIME => 'int',
		self::FIELD_FILLING_VALUE => 'float',
		self::FIELD_ID => 'int',
		self::FIELD_JOIN_WITH => 'int',
		self::FIELD_KURTOSIS => 'float',
		self::FIELD_LAST_ORIGINAL_UNIT_ID => 'int',
		self::FIELD_LAST_ORIGINAL_VALUE => 'float',
		self::FIELD_LAST_PROCESSED_DAILY_VALUE => 'float',
		self::FIELD_LAST_UNIT_ID => 'int',
		self::FIELD_LAST_VALUE => 'float',
		self::FIELD_LATEST_FILLING_TIME => 'int',
		self::FIELD_LATITUDE => 'float',
		self::FIELD_LONGITUDE => 'float',
		self::FIELD_MAXIMUM_ALLOWED_VALUE => 'float',
		self::FIELD_MAXIMUM_RECORDED_VALUE => 'float',
		self::FIELD_MEAN => 'float',
		self::FIELD_MEASUREMENTS_AT_LAST_ANALYSIS => 'int',
		self::FIELD_MEDIAN => 'float',
		self::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS => 'int',
		self::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => 'int',
		self::FIELD_MINIMUM_ALLOWED_VALUE => 'float',
		self::FIELD_MINIMUM_RECORDED_VALUE => 'float',
		self::FIELD_MOST_COMMON_CONNECTOR_ID => 'int',
		self::FIELD_MOST_COMMON_ORIGINAL_UNIT_ID => 'int',
		self::FIELD_MOST_COMMON_VALUE => 'float',
		self::FIELD_NUMBER_OF_CHANGES => 'int',
		self::FIELD_NUMBER_OF_CORRELATIONS => 'int',
		self::FIELD_NUMBER_OF_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION => 'int',
		self::FIELD_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => 'int',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'int',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN => 'int',
		self::FIELD_NUMBER_OF_TRACKING_REMINDERS => 'int',
		self::FIELD_NUMBER_OF_UNIQUE_DAILY_VALUES => 'int',
		self::FIELD_NUMBER_OF_UNIQUE_VALUES => 'int',
		self::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_CAUSE => 'int',
		self::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_EFFECT => 'int',
		self::FIELD_ONSET_DELAY => 'int',
		self::FIELD_OUTCOME => 'bool',
		self::FIELD_OUTCOME_OF_INTEREST => 'bool',
		self::FIELD_PARENT_ID => 'int',
		self::FIELD_PREDICTOR_OF_INTEREST => 'bool',
		self::FIELD_SECOND_TO_LAST_VALUE => 'float',
		self::FIELD_IS_PUBLIC => 'bool',
		self::FIELD_SKEWNESS => 'float',
		self::FIELD_STANDARD_DEVIATION => 'float',
		self::FIELD_THIRD_TO_LAST_VALUE => 'float',
		self::FIELD_USER_ID => 'int',
		self::FIELD_USER_MAXIMUM_ALLOWED_DAILY_VALUE => 'float',
		self::FIELD_USER_MINIMUM_ALLOWED_DAILY_VALUE => 'float',
		self::FIELD_USER_MINIMUM_ALLOWED_NON_ZERO_VALUE => 'float',
		self::FIELD_VARIABLE_CATEGORY_ID => 'int',
		self::FIELD_VARIABLE_ID => 'int',
		self::FIELD_VARIANCE => 'float',
	];
	protected array $openApiSchema = [
		self::FIELD_DATA_SOURCES_COUNT => ['type' => 'array', 'items' => ['type' => 'object']],
	];
	//protected $primaryKey = [self::FIELD_VARIABLE_ID, self::FIELD_USER_ID];
	protected $primaryKey = self::FIELD_ID;
	protected array $rules = [
		self::FIELD_ALIAS => 'nullable|max:125',
		self::FIELD_ANALYSIS_ENDED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_REQUESTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_STARTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_BEST_CAUSE_VARIABLE_ID => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_BEST_EFFECT_VARIABLE_ID => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_CAUSE_ONLY => 'nullable|boolean',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_COMBINATION_OPERATION => 'nullable',
		self::FIELD_DEFAULT_UNIT_ID => 'nullable|integer|min:1|max:1000',
		self::FIELD_DESCRIPTION => 'nullable|max:65535',
		self::FIELD_DURATION_OF_ACTION => 'max:7776000|numeric|nullable',
		self::FIELD_EARLIEST_FILLING_TIME => 'nullable|integer|min:946684801|max:2147483647',
		self::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_EARLIEST_SOURCE_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_EXPERIMENT_END_TIME => 'nullable|date',
		self::FIELD_EXPERIMENT_START_TIME => 'nullable|date',
		self::FIELD_FILLING_TYPE => 'nullable|in:none,zero',
		self::FIELD_FILLING_VALUE => 'nullable|numeric',
		self::FIELD_INFORMATIONAL_URL => 'nullable|max:2000',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'nullable|max:1000',
		self::FIELD_JOIN_WITH => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_KURTOSIS => 'nullable|numeric',
		self::FIELD_LAST_CORRELATED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LAST_ORIGINAL_UNIT_ID => 'nullable|integer|min:1|max:65535',
		self::FIELD_LAST_ORIGINAL_VALUE => 'nullable|numeric',
		self::FIELD_LAST_PROCESSED_DAILY_VALUE => 'nullable|numeric',
		self::FIELD_LAST_UNIT_ID => 'nullable|integer|min:1|max:65535',
		self::FIELD_LAST_VALUE => 'nullable|numeric',
		self::FIELD_LATEST_FILLING_TIME => 'nullable|integer|min:946684801|max:2147483647',
		self::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LATEST_SOURCE_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LATITUDE => 'nullable|numeric',
		self::FIELD_LOCATION => 'nullable|max:255',
		self::FIELD_LONGITUDE => 'nullable|numeric',
		self::FIELD_MAXIMUM_ALLOWED_VALUE => 'nullable|numeric',
		self::FIELD_MAXIMUM_RECORDED_VALUE => 'nullable|numeric',
		self::FIELD_MEAN => 'nullable|numeric',
		self::FIELD_MEASUREMENTS_AT_LAST_ANALYSIS => 'required|integer|min:0|max:300000',
		self::FIELD_MEDIAN => 'nullable|numeric',
		self::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_MINIMUM_ALLOWED_VALUE => 'nullable|numeric',
		self::FIELD_MINIMUM_RECORDED_VALUE => 'nullable|numeric',
		self::FIELD_MOST_COMMON_CONNECTOR_ID => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_MOST_COMMON_ORIGINAL_UNIT_ID => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_MOST_COMMON_SOURCE_NAME => 'nullable|max:255',
		self::FIELD_MOST_COMMON_VALUE => 'nullable|numeric',
		self::FIELD_NEWEST_DATA_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_NUMBER_OF_CHANGES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_CORRELATIONS => 'nullable|integer|min:0|max:10000',
		self::FIELD_NUMBER_OF_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => 'nullable|integer|min:0|max:7000',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'nullable|integer|min:0|max:300000',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_TRACKING_REMINDERS => 'required|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_UNIQUE_DAILY_VALUES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_UNIQUE_VALUES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_CAUSE => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_EFFECT => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_ONSET_DELAY => 'nullable|integer|min:0|max:8640000',
		self::FIELD_OPTIMAL_VALUE_MESSAGE => 'nullable|max:500',
		self::FIELD_OUTCOME => 'nullable|boolean',
		self::FIELD_OUTCOME_OF_INTEREST => 'nullable|boolean',
		self::FIELD_PARENT_ID => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_PREDICTOR_OF_INTEREST => 'nullable|boolean',
		self::FIELD_IS_PUBLIC => 'nullable|boolean',
		self::FIELD_REASON_FOR_ANALYSIS => 'nullable|max:255',
		self::FIELD_SECOND_TO_LAST_VALUE => 'nullable|numeric',
		self::FIELD_SKEWNESS => 'nullable|numeric',
		self::FIELD_STANDARD_DEVIATION => 'nullable|numeric',
		self::FIELD_STATUS => 'nullable|max:25',
		self::FIELD_THIRD_TO_LAST_VALUE => 'nullable|numeric',
		self::FIELD_USER_ERROR_MESSAGE => 'nullable|max:500',
		self::FIELD_USER_ID => 'required|numeric|min:1|max:1000000',
		self::FIELD_USER_MAXIMUM_ALLOWED_DAILY_VALUE => 'nullable|numeric',
		self::FIELD_USER_MINIMUM_ALLOWED_DAILY_VALUE => 'nullable|numeric',
		self::FIELD_USER_MINIMUM_ALLOWED_NON_ZERO_VALUE => 'nullable|numeric',
		self::FIELD_VALENCE => 'nullable',
		self::FIELD_VARIABLE_CATEGORY_ID => 'nullable|integer|min:1|max:300',
		self::FIELD_VARIABLE_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_VARIANCE => 'nullable|numeric',
		self::FIELD_WIKIPEDIA_TITLE => 'nullable|max:100',
		self::FIELD_WP_POST_ID => 'nullable|numeric|min:1',
	];
	/**
	 * @var array The relationships that should always be loaded.
	 */
	protected $with = [// Relationships cause too much duplicate complexity 'variable:'.Variable::IMPORTANT_FIELDS
	];
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        //$this->table = config('database.connections.mysql.database').'.variable_descriptions';
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
		if(parent::canReadMe($reader)){
			return true;
		}
		return $this->getUser()->share_all_data;
	}
	/**
	 * @return array
	 * @throws TooSlowToAnalyzeException
	 */
	public static function correlateNeverCorrelated(): array{
		/** @var UserVariable[] $uvs */
		$uvs = self::whereNeverCorrelated()->limit(100)->get();
		$correlations = [];
		foreach($uvs as $uv){
			$correlations[$uv->getSlug()] = $uv->correlate();
		}
		return $correlations;
	}
	/**
	 * @return BaseModel|Builder
	 */
	public static function whereNeverCorrelated(){
		return UserVariableLastCorrelatedAtProperty::whereNull()
			->whereNotIn(static::FIELD_USER_ID, UserIdProperty::getTestSystemAndDeletedUserIds())
			->orderBy(UserVariable::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN, 'desc')
			->where(UserVariable::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN, ">",
				CorrelationCauseNumberOfRawMeasurementsProperty::MINIMUM_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN)
			->where(UserVariable::FIELD_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS, ">",
				CorrelationCauseNumberOfProcessedDailyMeasurementsProperty::MINIMUM_PROCESSED_DAILY_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN);
	}
	/**
	 * @param array $userVariableIds
	 * @return QMUserVariableRelationship[]
	 * @throws TooSlowToAnalyzeException
	 */
	public function correlate(array $userVariableIds = []): array{
		$dbm = $this->getQMUserVariable();
		try {
			return $dbm->correlate($userVariableIds);
		} catch (TooSlowToAnalyzeException $e) {
			$this->queueCorrelation();
			throw $e;
		}
	}
	public function getQMUserVariable(): QMUserVariable{
		try {
			$dbm = $this->getDBModel();
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			$dbm = $this->getDBModel();
		}
		return $dbm;
	}
	/**
	 * @return QMUserVariable
	 */
	public function getDBModel(): DBModel{
		$vId = $this->getVariableIdAttribute();
		/** @var QMUserVariable $dbm */
		if($dbm = $this->getDBModelFromMemory()){
			$dbm->setUserUnit($this->getUnitIdAttribute());
			if($dbm->variableId !== $vId){
				le("dbm->variableId $dbm->variableId !== variableId $vId");
			}
			$dbm->setLaravelModel($this);
			return $dbm;
		}
		$dbm = new QMUserVariable();
		$dbm->populateByLaravelModel($this);
		if($dbm->variableId !== $vId){
			le("dbm->variableId $dbm->variableId !== variableId $vId");
		}
		try {
			$dbm->populateDefaultFields();
		} catch (\Throwable $e) {
			$dbm->populateDefaultFields();
		}
		if($dbm->variableId !== $vId){
			le("dbm->variableId $dbm->variableId !== variableId $vId");
		}
		if($this->hasId()){
			$dbm->addToMemory();
		}
		if($dbm->variableId !== $vId){
			le("dbm->variableId $dbm->variableId !== variableId $vId");
		}
		$dbm->validateId();
		if($dbm->variableId !== $vId){
			le("dbm->variableId $dbm->variableId !== variableId $vId");
		}
		return $dbm;
	}
	public function getVariableIdAttribute(): ?int{
		return $this->attributes[self::FIELD_VARIABLE_ID] ?? null;
	}
	/**
	 * @return \App\Models\UserVariableRelationship|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function setBestCorrelationAsEffect(): mixed{
		if($this->relationLoaded('best_correlations_where_effect_user_variable')){
			$c = $this->best_correlations_where_effect_user_variable->first();
		} else{
			$c = $this->best_correlations_where_effect_user_variable()->first();
		}
		$this->setRelation(__FUNCTION__, $c ?? false);
		return $c;
	}
	/**
	 * @return \App\Models\UserVariableRelationship|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|mixed|null|object
	 */
	public function setBestCorrelationAsCause(): mixed{
		if($this->relationLoaded('best_correlations_where_cause_user_variable')){
			$c = $this->best_correlations_where_cause_user_variable->first();
		} else{
			/** @var UserVariableRelationship|Builder $qb */
			$qb = $this->best_correlations_where_cause_user_variable();
			$c = $qb->first();
		}
		$this->setRelation(__FUNCTION__, $c ?? false);
		return $c;
	}
	public function getOrCreateTrackingReminder(): TrackingReminder{
		return $this->firstOrCreateTrackingReminder();
	}
	/**
	 * @param string $attr
	 * @return mixed
	 */
	public function getAttributeOrVariableFallback(string $attr){
		if(!$this->attributes){
			return null; // This happens at https://local.quantimo.do/astral-api/user-variables/34133 for some reason
		}
		$val = $this->attributes[$attr] ?? null;
		if($val === null){
			$v = $this->getVariable();
			$val = $v->getAttribute($attr);
		}
		return $val;
	}
	private function queueCorrelation(){
		Alerter::toast("User Variable Relationship queued.  I'll email you when it's complete");
	}
	public static function getSlimClass(): string{ return QMUserVariable::class; }
	/**
	 * @param \Illuminate\Support\Collection|UserVariable[] $baseModels
	 * @return QMUserVariable[]
	 */
	public static function toDBModels($baseModels): array{
		/** @var QMUserVariable[] $arr */
		$arr = parent::toDBModels($baseModels);
		if(IncludeTagsParam::includeTags()){
			foreach($arr as $uv){
				$uv->getAllCommonAndUserTagVariableTypes();
			}
		}
		return $arr;
	}
	/**
	 * @param array $attributes
	 * @param array $values
	 * @return BaseModel|UserVariable|null
	 */
	public static function firstOrCreate(array $attributes, array $values = []){
		$variableId = UserVariableVariableIdProperty::pluckOrDefault($attributes);
		$userId = UserVariableUserIdProperty::pluckOrDefault($attributes);
		if($variableId && $userId){
			$v = Variable::firstOrCreateByForeignData($attributes);
			$attributes[self::FIELD_VARIABLE_ID] = $v->id;
			$attributes[self::FIELD_USER_ID] = $userId;
		} else{
			$id = UserVariableIdProperty::pluckOrDefault($attributes);
			return self::findInMemoryOrDB($id);
		}
		return parent::firstOrCreate($attributes, $values);
	}
	/**
	 * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $qb
	 * @param array $additionalCategoryIds
	 */
	public static function excludeAppsPaymentsWebsitesTestVariablesAndLocations($qb, array $additionalCategoryIds = []){
		$ids = array_values(array_unique(array_merge($additionalCategoryIds,
			VariableCategory::getAppsLocationsWebsiteIds())));
		$qb->whereNotIn(Variable::TABLE . '.' . Variable::FIELD_VARIABLE_CATEGORY_ID, $ids);
        //debugger("");
        QMQB::notLike($qb, Variable::TABLE . '.' . Variable::FIELD_NAME, "% test%");
	}
	/**
	 * @param $data
	 * @return BaseModel
	 */
	public static function firstOrCreateByForeignData($data): BaseModel{
		$uv = parent::firstOrCreateByForeignData($data);
		//if($uv->client_id === BaseClientIdProperty::CLIENT_ID_QUANTIMODO){le('$uv->client_id === BaseClientIdProperty::CLIENT_ID_QUANTIMODO');}
		return $uv;
	}
	/**
	 * @param int $userId
	 * @param null $variableIdOrName
	 * @return \App\Models\UserVariable
	 */
	public static function findByNameOrId(int $userId, $variableIdOrName = null): ?self{
		$variableId = VariableIdProperty::pluckOrDefault($variableIdOrName);
		$uv = static::findInMemoryOrDBWhere([
			self::FIELD_USER_ID => $userId,
			self::FIELD_VARIABLE_ID => $variableId,
		]);
		return $uv;
	}
	/**
	 * @param $data
	 * @return UserVariable
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public static function fromForeignData($data): BaseModel{
		if($data instanceof DBModel){
			$data = $data->toNonNullArray();
		}
		if($data instanceof BaseModel){
			$data = $data->toNonNullArray();
		}
		if(!is_array($data)){
			$data = json_decode(json_encode($data), true);
		}
        $userId = $data[Variable::FIELD_CREATOR_USER_ID] = UserVariableUserIdProperty::pluckOrDefault($data);
		unset($data['id']);
		unset($data['combinationOperation']); // Avoids error if we submit the wrong combination operation with a measurement i.e. MEAN with YES/NO unit
		$v = Variable::fromForeignData($data);
		if(!$v->id){
			le("");
		}
		$pluckedUnit = UserVariableDefaultUnitIdProperty::pluckOrDefault($data);
        $fillable = [
			self::FIELD_VALENCE => UserVariableValenceProperty::pluckOrDefault($data),
			self::FIELD_VARIABLE_CATEGORY_ID => UserVariableVariableCategoryIdProperty::pluckOrDefault($data),
			self::FIELD_DEFAULT_UNIT_ID => $pluckedUnit,
			self::FIELD_VARIABLE_ID => $v->id,
			self::FIELD_USER_ID => $userId,
		];
		$unitFromData = $data["unitId"] ?? null;
		if($unitFromData && $unitFromData !== $pluckedUnit){
			$pluckedUnit = UserVariableDefaultUnitIdProperty::pluckOrDefault($data);
			le("Wrong plucked unit $pluckedUnit");
		}
		return self::upsertOne($fillable);
	}
	/**
	 * @param int|string $variableIdOrName
	 * @param int|null $userId
	 * @return UserVariable
	 */
	public static function findByVariableIdOrName($variableIdOrName, int $userId): ?UserVariable{
		if(is_int($variableIdOrName)){
			return static::findByVariableId($variableIdOrName, $userId);
		}
		return static::findByName($variableIdOrName, $userId);
	}
	/**
	 * @param $data
	 * @param bool $fallback
	 * @return UserVariable
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public static function upsertOne($data, bool $fallback = false): BaseModel{
		$v = Variable::fromForeignData($data);
		$data[self::FIELD_VARIABLE_ID] = $v->id;
		$uv = static::findByData($data);
		if(!$uv){
			$uv = self::new($data);
		}
		$uv->populateHyperParameters($data);
		$unitIdFromData = UserVariableDefaultUnitIdProperty::pluckOrDefault($data);
		if($unitIdFromData && $uv->getUnitIdAttribute()){
			$uv->setDefaultUnitIdAttribute($unitIdFromData);
		}
		$catIdFromData = UserVariableVariableCategoryIdProperty::pluckOrDefault($data);
		if($catIdFromData && $uv->getVariableCategoryId()){
			$catIdFromData = UserVariableVariableCategoryIdProperty::pluckOrDefault($data);
			$uv->setVariableCategoryIdAttribute($catIdFromData);
		}
		if(UserVariableMinimumAllowedValueProperty::keyExists($data)){
			$min = UserVariableMinimumAllowedValueProperty::pluckOrDefault($data);
			$inCommonUnit = $uv->toCommonUnit($min);
			$uv->setMinimumAllowedValueAttribute($inCommonUnit);
		}
		if(UserVariableIsPublicProperty::keyExists($data)){
			$val = UserVariableIsPublicProperty::pluckOrDefault($data);
			$uv->setIsPublic($val);
		}
		if(UserVariableMaximumAllowedValueProperty::keyExists($data)){
			$max = UserVariableMaximumAllowedValueProperty::pluckOrDefault($data);
			$inCommonUnit = $uv->toCommonUnit($max);
			$uv->setMaximumAllowedValueAttribute($inCommonUnit);
		}
		if(UserVariableValenceProperty::keyExists($data)){
			$valence = UserVariableValenceProperty::pluckOrDefault($data);
			$uv->setValenceAttribute($valence);
		}
		if(UserVariableFillingValueProperty::keyExists($data)){
			$valueInUserUnit = UserVariableFillingValueProperty::pluckOrDefault($data);
			$uv->filling_value = $uv->toCommonUnit($valueInUserUnit);
			$uv->filling_type = UserVariableFillingTypeProperty::fromValue($valueInUserUnit);
		}
        if(!$uv->number_of_tracking_reminders){
            $uv->number_of_tracking_reminders = 0;
        }
		try {
			$uv->save();
		} catch (ModelValidationException $e) {
			le($e);
		} catch (QueryException $e) {
			if(str_contains($e->getMessage(), 'duplicate key')){
				$builder = UserVariable::query()
					->withTrashed()
                   ->where(UserVariable::FIELD_USER_ID, $uv->user_id)
                   ->where(UserVariable::FIELD_VARIABLE_ID, $uv->variable_id);
				$restored = $builder->update([self::FIELD_DELETED_AT => null]);
				return $builder->first();
			}
		}
		return $uv;
	}
	/**
	 * @param int $variableId
	 * @param int|null $userId
	 * @return UserVariable|null
	 * @throws UnauthorizedException
	 */
	public static function findByVariableId(int $variableId, int $userId = null): ?UserVariable{
		if(!$userId){
			$userId = QMAuth::id();
		}
		if($uv = static::findInMemoryByUUID([
			self::FIELD_VARIABLE_ID => $variableId,
			self::FIELD_USER_ID => $userId,
		])){
			return $uv;
		}
		$qb = static::whereVariableId($variableId)->where(self::FIELD_USER_ID, $userId);
		/** @var UserVariable $uv */
		$uv = $qb->first();
		if($uv){
			$uv->addToMemory();
		}
		return $uv;
	}
	/**
	 * @param $data
	 * @param bool $fallback
	 * @return UserVariable
	 */
	public static function findByData($data, bool $fallback = true): ?BaseModel{
		if($id = UserVariableIdProperty::pluckOrDefault($data)){
			if($fromMemory = UserVariable::findInMemoryOrDB($id)){return $fromMemory;}
		}
		$userId = UserVariableUserIdProperty::pluckOrDefault($data);
		$v = Variable::findOrCreate($data);
		if(!$v){le("Please provide variableId to " . __METHOD__);}
		if(!$userId){le("Please provide user id to " . __METHOD__);}
		return UserVariable::findByNameOrId($userId, $v->id);
	}
	/**
	 * @param string $name
	 * @param int|null $userId
	 * @return UserVariable
	 */
	public static function findByName(string $name, int $userId = null): ?UserVariable{
		if(!$userId){
			$userId = QMAuth::id();
		}
		$fromMemory = static::getAllFromMemoryIndexedByUuidAndId();
		foreach($fromMemory as $uv){
			if($uv->getNameAttribute() === $name && $uv->getUserId() === $userId){
				return $uv;
			}
		}
		$v = Variable::findByName($name);
		if(!$v){return null;}
		$uv = $v->findUserVariable($userId);
		if(!$uv){return null;}
		$uv->addToMemory();
		return $uv;
	}
	/**
	 * @param $provided
	 * @return array
	 */
	public function populateHyperParameters($provided): array{
		$hyper = $this->getHyperParameterProperties();
		$changesBefore = $this->getChangeList();
		$v = $this->getVariable();
		/** @var BaseProperty $one */
		foreach($hyper as $one){
			if(isset($changesBefore[$one->name])){
				continue;
			}
			if(!$one->keyExists($provided)){
				continue;
			}
			if(method_exists($one, 'pluckInCommonUnit')){
				$val = $one->pluckInCommonUnit($provided);
			} else{
				$val = $one->pluckOrDefault($provided);
			}
			$common = $v->getAttribute($one->name);
			if($val === $common){
				$one->setValue(null);
			} else{
				$one->setValue($val);
			}
		}
		$changesAfter = $this->getChangeList();
		return $changesAfter;
	}
	public function getNameAttribute(): string{
		if(!$this->getAttribute(self::FIELD_VARIABLE_ID)){
			return (new \ReflectionClass(static::class))->getShortName();
		}
		return $this->getVariable()->name;
	}
	public function getHyperParameterProperties(): array{
		$all = $this->getPropertyModels();
		$hyper = [];
		/** @var UserHyperParameterTrait $one */
		foreach($all as $one){
			if(property_exists($one, 'isHyperParameter')){
				if($one->isHyperParameter){
					$hyper[$one->name] = $one;
				}
			}
		}
		return $hyper;
	}
	/**
	 * @param array|string $key
	 * @return mixed|null
	 */
	public function getAttribute($key){
		$prop = $this->getPropertyModel($key);
		if($prop && method_exists($prop, 'getHyperParameter')){
			if($this->hasGetMutator($key)){
				return $this->getAttributeValue($key);
			} else{
				return $prop->getHyperParameter($key);
			}
		}
		$res = parent::getAttribute($key);
		return $res;
	}
	/**
	 * @param $new
	 */
	public function setDefaultUnitIdAttribute($new){
		$this->setAttributeIfNotSameAsVariable(self::FIELD_DEFAULT_UNIT_ID, $new);
	}
	public function getUserId(): ?int{
		return $this->attributes[UserVariable::FIELD_USER_ID];
	}
	/**
	 * @param string $attr
	 * @param mixed $new
	 */
	public function setAttributeIfNotSameAsVariable(string $attr, $new){
        if(!$this->attributes){
            $this->attributes[$attr] = $new;
            return;
        }
		$v = $this->getVariable();
		$existing = $v->getAttribute($attr);
		if($new === $existing){
			$this->attributes[$attr] = null;
		} else{
			$this->attributes[$attr] = $new;
		}
	}
	/**
	 * @param string $name
	 * @return Builder
	 */
	public static function whereName(string $name): Builder{
		$qb = static::whereHas('variable', function($query) use ($name){
			/** @var Builder $query */
			return $query->where(Variable::FIELD_NAME, '=', $name);
		});
		return $qb;
	}
	/**
	 * @param $id
	 */
	public function setVariableCategoryIdAttribute($id){
		if($id === MiscellaneousVariableCategory::ID){
			$id = null;
		}
		if(!is_int($id) && !is_null($id)){
			$message = "this is not a valid category id: " . \App\Logging\QMLog::print_r($id, true);
			QMLog::exceptionIfNotProduction($message);
			return;
		}
		$this->setAttributeIfNotSameAsVariable(self::FIELD_VARIABLE_CATEGORY_ID, $id);
	}
	public static function getS3Bucket(): string{ return S3Private::getBucketName(); }
	/**
	 * @param float|null|string $inUserUnit
	 * @return float
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function toCommonUnit($inUserUnit): ?float{
		$userUnitId = $this->attributes[self::FIELD_DEFAULT_UNIT_ID] ?? null;
		$commonUnitId = $this->getCommonUnitId();
		return QMUnit::convertValue($inUserUnit, $userUnitId, $commonUnitId, $this);
	}
	public function getShowContentView(array $params = []): View{
		return view('user-variable-content', $this->getShowParams($params));
	}
	/**
	 * @param $inCommonUnit
	 */
	public function setMinimumAllowedValueAttribute($inCommonUnit){
		$this->setAttributeIfNotSameAsVariable(self::FIELD_MINIMUM_ALLOWED_VALUE, $inCommonUnit);
	}
	public static function findByVariableName(string $name, int $userId): ?UserVariable{
		return QMUserVariable::findUserVariableByNameIdOrSynonym($userId, $name)->l();
	}
	/**
	 * @param $new
	 */
	public function setMaximumAllowedValueAttribute($new){
		$this->setAttributeIfNotSameAsVariable(self::FIELD_MAXIMUM_ALLOWED_VALUE, $new);
	}
	public static function findOrCreateByNameOrId(int $userId, int|string $nameOrVariableId, array $data = []): 
	UserVariable{
		return self::findOrCreateByNameOrVariableId($userId, $nameOrVariableId, $data);
	}
	/**
	 * @param $new
	 */
	public function setValenceAttribute($new){
		$this->setAttributeIfNotSameAsVariable(self::FIELD_VALENCE, $new);
	}
	/**
	 * Returns variable information specific for a user, not including joined variables.
	 * Use $variable->hasMeasurements() to see if a user has measurements for this variable.
	 * @param int $userId
	 * @param string|int $nameOrVariableId
	 * @param array $newVariableParams
	 * @return UserVariable
	 */
	public static function findOrCreateByNameOrVariableId(int $userId, $nameOrVariableId,
		array $newVariableParams = []): UserVariable{
		if(is_string($nameOrVariableId)){
			$v = Variable::findOrCreateByName($nameOrVariableId, $newVariableParams);
		} else{
			$v = Variable::findInMemoryOrDB($nameOrVariableId);
		}
		return $v->getOrCreateUserVariable($userId, $newVariableParams);
	}
	public static function getIndexPageView(): View{
		return view('variables-index', [
			'buttons' => static::generateIndexButtons(),
		]);
	}
	public static function getUniqueIndexColumns(): array{
		return [self::FIELD_USER_ID, self::FIELD_VARIABLE_ID];
	}
	/** @noinspection PhpUnused */
	public static function generateIndexButtons(): array{
		$u = QMAuth::getQMUser();
		$uvs = UserVariable::whereUserId($u->getId())->get();
		return VariableButton::toButtons($uvs);
	}
	public static function newFake(int $userId = UserIdProperty::USER_ID_TEST_USER): BaseModel{
		$m = parent::newFake();
		if($m->id){
			le('$m->id');
		}
		$m->variable_id = OverallMoodCommonVariable::ID;
		$m->user_id = UserIdProperty::USER_ID_DEMO;
		/** @noinspection PhpUnhandledExceptionInspection */
		$m->validateAttribute(self::FIELD_DEFAULT_UNIT_ID);
		return $m;
	}
	/** @noinspection PhpUnused */
	/**
	 * @param string $q
	 * @param int|null $userId
	 * @return Builder
	 */
	public static function whereNameLike(string $q, int $userId = null): Builder{
		$qb = static::whereHas('variable', function($query) use ($q){
			$query->where('name', \App\Storage\DB\ReadonlyDB::like(), '%' . $q . '%');
		})->with([
			'variable' => function($query) use ($q){
				$query->where('name', \App\Storage\DB\ReadonlyDB::like(), '%' . $q . '%');
			},
		]);
		if($userId){
			$qb->where(static::FIELD_USER_ID, $userId);
		}
		return $qb;
	}
	/**
	 * Get the searchable columns for the resource.
	 * @return array
	 */
	public static function searchableColumns(): array{
		//$parent = parent::searchableColumns();
		return []; // Prevents returning id field
	}
	/** @noinspection PhpUnused */
	/**
	 * @return \App\Models\UserVariable|\Illuminate\Database\Eloquent\Builder
	 */
	public static function wherePostable(){
		$qb = static::query();
		$qb->whereNotIn(static::TABLE . '.' . static::FIELD_USER_ID, UserIdProperty::getTestSystemAndDeletedUserIds());
		$qb->where(static::TABLE . '.' . static::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN, ">", 1);
		return $qb;
	}
	/** @noinspection PhpUnused */
	/**
	 * @param array $valuesInADay
	 * @return float
	 */
	public function aggregateDailyValues(array $valuesInADay): float{
		if($this->isSum()){
			return Stats::sum($valuesInADay);
		} else{
			return Stats::average($valuesInADay);
		}
	}
	/**
	 * @param BaseModel|DBModel $relatedObj
	 * @return BaseModel|UserVariable
	 */
	public static function upsertByRelated($relatedObj){
		$v = Variable::fromForeignData($relatedObj);
		$uv = static::findByData([
			self::FIELD_VARIABLE_ID => $v->id,
			self::FIELD_USER_ID => UserVariableUserIdProperty::pluckOrDefault($relatedObj),
		]);
		if(!$uv){
			return parent::upsertByRelated($relatedObj);
		}
		$changed = false;
		if($unitIdFromData = UserVariableDefaultUnitIdProperty::pluckOrDefault($relatedObj)){
			$existing = $uv->default_unit_id;
			if($unitIdFromData !== $existing){
				$changed = true;
				$uv->setDefaultUnitIdAttribute($unitIdFromData);
			}
		}
		if($catIdFromData = UserVariableVariableCategoryIdProperty::pluckOrDefault($relatedObj)){
			$existing = $uv->variable_category_id;
			if($catIdFromData !== $existing){
				$changed = true;
				$uv->setVariableCategoryIdAttribute($catIdFromData);
			}
		}
		if($valence = UserVariableValenceProperty::pluckOrDefault($relatedObj)){
			$existing = $uv->valence;
			if($valence !== $existing){
				$changed = true;
				$uv->setValenceAttribute($valence);
			}
		}
		if($changed){
			try {
				$uv->save();
			} catch (ModelValidationException $e) {
				le($e);
			}
		}
		return $uv;
	}
	private function isSum(): bool{
		return $this->combination_operation === BaseCombinationOperationProperty::COMBINATION_SUM;
	}
	public function aggregateValues(array $values): float{
		if($this->isSum()){
			return Stats::sum($values);
		}
		return Stats::average($values);
	}
	/**
	 * @param $timeAt
	 * @return QMMeasurement|null
	 */
	public function alreadyHaveData($timeAt): ?QMMeasurement{
		$dbm = $this->getQMUserVariable();
		$m = $dbm->alreadyHaveData($timeAt);
		if(!$this->measurementsAreSet()){
			le('!$this->measurementsAreSet()');
		}
		return $m;
	}
	public function measurementsAreSet(): bool{
		if($dbm = $this->getDBModelFromMemory()){
			return $dbm->measurementsAreSet();
		}
		return false;
	}
	/** @noinspection PhpUnused */
	/**
	 * @param string $reason
	 * @throws TooSlowToAnalyzeException
	 */
	public function analyzeFullyIfNecessary(string $reason){
		$this->getQMUserVariable()->analyzeFullyIfNecessary($reason);
	}
	/** @noinspection PhpUnused */
	/**
	 * @param string $reason
	 * @throws TooSlowToAnalyzeException
	 */
	public function analyzeIfNecessary(string $reason): void{
		$dbm = $this->getQMUserVariable();
		$dbm->analyzeIfNecessary($reason);
		if($dbm->measurementsAreSet()){
			$measurements = $dbm->getQMMeasurements();
			if($measurements && $this->mean === null){
				$dbm->analyzeIfNecessary($reason);
				le("Mean should not be null");
			}
		}
	}
	/**
	 * @param int $id
	 * @return BaseUserVariable|UserVariable|Builder
	 */
	public static function whereVariableCategoryId(int $id){
		$qb = static::whereHas('variable', function($query) use ($id){
			/** @var Builder $query */
			return $query->where(Variable::FIELD_VARIABLE_CATEGORY_ID, '=', $id);
		});
		return $qb;
	}
	/** @noinspection PhpUnused */
	/**
	 * @param string $reason
	 * @throws TooSlowToAnalyzeException
	 */
	public function analyzeUserAndCommonIfNecessary(string $reason): void{
		$dbm = $this->getQMUserVariable();
		$dbm->analyzeIfNecessary($reason);
		$v = $this->getVariable();
		$v->analyze();
	}
	/** @noinspection PhpUnused */
	public function analyzedInLastXHours(int $int): bool{
		$at = $this->analysis_ended_at;
		return TimeHelper::inLastXHours($int, $at);
	}
	/** @noinspection PhpUnused */
	public function best_cause_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, UserVariable::FIELD_BEST_CAUSE_VARIABLE_ID, Variable::FIELD_ID,
			UserVariable::FIELD_BEST_CAUSE_VARIABLE_ID);
	}
	/** @noinspection PhpUnused */
	public function best_correlations_where_cause_user_variable(): HasMany{
		return $this->correlations_where_cause_user_variable()
			->where(UserVariableRelationship::FIELD_EFFECT_USER_VARIABLE_ID, "<>", $this->getId())
			->orderBy(UserVariableRelationship::FIELD_QM_SCORE, BaseModel::ORDER_DIRECTION_DESC);
	}
	/** @noinspection PhpUnused */
	public function correlations_where_cause_user_variable(): HasMany{
		return parent::correlations_where_cause_user_variable()->with([
			'effect_user_variable',
			'effect_variable',
		]);
	}
	public function getId(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			$id = $this->attributes[UserVariable::FIELD_ID] ?? null;
			if(!$id){
				throw new NoIdException($this, "No User Variable Id");
			}
			return $id;
		} else{
			/** @var QMUserVariable $this */
			return $this->id;
		}
	}
	/**
	 * @return string[]
	 */
	public function getKeyWords(): array{
		return $this->getVariable()->getSynonymsAttribute();
	}
	public function best_correlations_where_effect_user_variable(): HasMany{
		return $this->correlations_where_effect_user_variable()
			->where(UserVariableRelationship::FIELD_CAUSE_USER_VARIABLE_ID, "<>", $this->getId())
			->orderBy(UserVariableRelationship::FIELD_QM_SCORE, BaseModel::ORDER_DIRECTION_DESC);
	}
	public function correlations_where_effect_user_variable(): HasMany{
		return parent::correlations_where_effect_user_variable()->with([
			'cause_user_variable',
			'cause_variable',
		]);
	}
	public function best_effect_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, UserVariable::FIELD_BEST_EFFECT_VARIABLE_ID, Variable::FIELD_ID,
			UserVariable::FIELD_BEST_EFFECT_VARIABLE_ID);
	}
	/**
	 * @param array $values
	 * @return Measurement[]
	 */
	public function bulkMeasurementInsert(array $values): array{
		return Measurement::bulkInsert($values, $this);
	}
	/**
	 * @return QMUserVariableRelationship[]
	 */
	public function calculateCorrelationsIfNecessary(): ?array{
		$dbm = $this->getQMUserVariable();
		try {
			return $dbm->calculateCorrelationsIfNecessary();
		} catch (TooSlowToAnalyzeException $e) {
			le($e);
		}
		/** @var \LogicException $e */
		throw $e;
	}
	/**
	 * @param bool $includeDeleted
	 * @return int
	 */
	public function calculateNumberOfMeasurements(bool $includeDeleted = false): int{
		if($includeDeleted){
			return Measurement::readonly()->where(Measurement::FIELD_USER_VARIABLE_ID, $this->getUserVariableId())
				->count();
		}
		return UserVariableNumberOfMeasurementsProperty::calculate($this);
	}
	/**
	 * @param float $inUserUnit
	 * @return float|int|null
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function convertToCommonUnit(float $inUserUnit): float{
		return QMUnit::convertValueByUnitIds($inUserUnit, $this->getUserUnitId(), $this->getCommonUnitId(), $this,
			null);
	}
	/**
	 * @return QMUserVariableRelationship[]
	 * @throws TooSlowToAnalyzeException
	 */
	public function correlateAll(): array{
		$u = $this->getUser();
		$userVariableIds = $u->user_variables()->pluck(self::FIELD_ID)->all();
		return $this->correlate($userVariableIds);
	}
	/**
	 * @return QMUserVariableRelationship[]
	 * @throws TooSlowToAnalyzeException
	 */
	public function correlateAsEffect(): array{
		$dbm = $this->getQMUserVariable();
		$newCorrelations = $dbm->correlateAsEffect();  // Includes self-correlation
		$get = $this->getCorrelations(); // Excludes self correlation
		if(count($newCorrelations) > ($get->count()+1)){ // Excludes self correlation
			le("too many new user_variable_relationships");
		}
		return $newCorrelations;
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return UserVariableRelationship[]|\Illuminate\Support\Collection
	 */
	public function getCorrelations(int $limit = null,
		string $variableCategoryName = null): ?\Illuminate\Support\Collection{
		$dbm = $this->getQMUserVariable();
		return $dbm->getOutcomesOrPredictors($limit, $variableCategoryName);
	}
	/**
	 * @return QMUserVariableRelationship[]
	 * @throws \App\Exceptions\TooSlowToAnalyzeException
	 */
	public function correlateIfNecessary(): array{
		if($this->weShouldCalculateCorrelations()){
			try {
				return $this->correlate();
			} catch (NotEnoughVariablesToCorrelateWithException $e) {
				$this->logInfo(__METHOD__.": ".$e->getMessage());
			}
		}
		return [];
	}
	/**
	 * @return bool
	 */
	public function weShouldCalculateCorrelations(): bool{
		$percentChangeRequired = \App\Utils\Env::get('REQUIRED_NEW_MEASUREMENT_PERCENT_FOR_CORRELATION');
		if(!$percentChangeRequired){
			$percentChangeRequired = QMUserVariableRelationship::REQUIRED_NEW_MEASUREMENT_PERCENT_FOR_CORRELATION;
		}
		$currentRaw = $this->getOrCalculateNumberOfMeasurements();
		$currentRawWithTags = $this->getOrCalculateNumberOfRawMeasurementsWithTagsJoinsChildren();
		$atLastCorrelation = $this->getNumberOfMeasurementsWithTagsAtLastCorrelation();
		if($currentRawWithTags < $atLastCorrelation){
			$this->logInfo("Need to calculate user_variable_relationships user_variable_relationships because deleted measurements. " .
				"We only have $currentRaw RawMeasurements with tags and had $currentRawWithTags rawMeasurements with tags AtLastAnalysis");
			return true;
		}
		if($this->getStatusAttribute() === UserVariableStatusProperty::STATUS_CORRELATE){
			$this->logInfo("Need to calculate user_variable_relationships user_variable_relationships because CORRELATE_STATUS");
			return true;
		}
		if($currentRawWithTags <
			CorrelationCauseNumberOfRawMeasurementsProperty::MINIMUM_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN){
			$this->logInfo("Don't need to calculate user_variable_relationships because we only have $currentRawWithTags RawMeasurementsWithTagsJoinsChildren");
			return false;
		}
		$numberProcessed = $this->getNumberOfProcessedDailyMeasurements();
		if($numberProcessed !== null && $numberProcessed <
			CorrelationCauseNumberOfProcessedDailyMeasurementsProperty::MINIMUM_PROCESSED_DAILY_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN){
			$message =
				"Don't need to calculate user_variable_relationships because we only have $numberProcessed ProcessedDailyMeasurements";
			$this->logInfo($message);
			return false;
			//throw new NotEnoughMeasurementsException(null,null, $message);
		}
		$lastCorrelated = $this->getLastCorrelatedAt();
		$lastCorrelatedString = "last correlated " . TimeHelper::timeSinceHumanString($lastCorrelated);
		if(strtotime($lastCorrelated) < strtotime(QMUserVariableRelationship::ALGORITHM_MODIFIED_AT)){
			$this->logInfo("Need to calculate user_variable_relationships because $lastCorrelatedString and UserVariableRelationship::ALGORITHM_MODIFIED_AT was " .
				QMUserVariableRelationship::ALGORITHM_MODIFIED_AT);
			return true;
		}
		$requiredNewRawMeasurements = (1 + $percentChangeRequired / 100) * $atLastCorrelation;
		if($currentRawWithTags < $requiredNewRawMeasurements){
			$this->logInfo("Don't need to calculate user_variable_relationships because we only have $currentRawWithTags RawMeasurements with tags " .
				"and had $atLastCorrelation numberOfMeasurementsWithTagsAtLastCorrelation.  Last correlated: " .
				TimeHelper::timeSinceHumanString($lastCorrelated));
			return false;
		}
		// We should generally correlate with non-outcomes as well because if a user gets a ton of mood measurements,
		// for instance,  the measurement count will rarely reach the 10% change threshold to calculate with new
		// predictors they've recently started tracking
		if(!$this->outcome){
			$this->logInfo("Don't need to calculate user_variable_relationships because ONLY_CORRELATE_OUTCOMES");
			return false;
		}
		return true;
	}
	public function getNumberOfMeasurementsWithTagsAtLastCorrelation(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION] ?? null;
	}
	public function getStatusAttribute(): ?string{
		return $this->attributes[UserVariable::FIELD_STATUS] ?? null;
	}
	/** @noinspection PhpUnused */
	public function getNumberOfProcessedDailyMeasurements(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS] ?? null;
	}
	public function getLastCorrelatedAt(): ?string{
		return $this->attributes[UserVariable::FIELD_LAST_CORRELATED_AT] ?? null;
	}
	/**
	 * @return Builder
	 */
	public function debugCorrelationsQB(): Builder{
		$qb = $this->userVariableIdsToCorrelateWithQB();
		if(!$qb->count()){
			$originalAdminer = Adminer::getUrl($qb->toSql());
			$originalSQL = QMQB::toSimpleSql($qb->toSql());
			foreach($qb->wheres as $i => $where){
				unset($qb->wheres[$i]);
				if($qb->count()){
					//$qb->wheres = array_values($qb->wheres);
					$sql = QMQB::toSimpleSql($qb->toSql());
					$works = Adminer::getUrl($qb->toSql());
					$index = static::generateDataLabIndexUrl([self::FIELD_USER_ID => $this->getUserId()]);
					die("\nUser Variable Index: " . $index . "\n" . "\nWorking query: " . $works . "\n" .
						"\nOriginal query: " . $originalAdminer . "\n" . "\nOriginal SQL: \n" . $originalSQL . "\n" .
						"\nWorking SQL: \n" . $sql . "\n" .
						"\nClick original query link and delete where clauses until you get a result");
				}
			}
			if($qb->wheres){
				le("should have be unset!");
			}
		}
		return $qb;
	}
	/**
	 * @return \Illuminate\Database\Query\Builder
	 */
	public function userVariableIdsToCorrelateWithQB(): \Illuminate\Database\Query\Builder{
		$qb = UserVariable::correlatableUserVariableIds();
		$qb->where(self::TABLE . '.' . self::FIELD_USER_ID, $this->getUserId());
		$latest = $this->getLatestFillingAt();
		$earliest = $this->getEarliestFillingAt();
		$qb->where(self::TABLE . '.' . self::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT, '<',
			db_date(strtotime($latest) - BaseNumberOfDaysProperty::MINIMUM_NUMBER_OF_DAYS_IN_COMMON / 2 * 86400));
		$minLatest = (strtotime($earliest) + BaseNumberOfDaysProperty::MINIMUM_NUMBER_OF_DAYS_IN_COMMON / 2 * 86400);
		$qb->where(self::TABLE . '.' . self::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT, '>', db_date($minLatest));
		if($this->getVariableCategoryId() !== InvestmentStrategiesVariableCategory::ID){
			$qb->where(self::TABLE . '.' . self::FIELD_VARIABLE_ID, '<>', $this->variable_id);
		}
		$ids = [];
		if($this->isOutcome() === false){ // Don't correlate definitely non-outcomes with other definitely non-outcomes
			$ids[] = $this->variable_category_id; // i.e. Don't correlate emotions with emotions
			$qb->where(Variable::TABLE . '.' . Variable::FIELD_OUTCOME, "<>", 0);
		}
		UserVariable::excludeAppsPaymentsWebsitesTestVariablesAndLocations($qb,
			$ids);  // Maybe we should use below strategy in cause the user only has apps and websites?
		//$qb->logWhereString(__FILE__);
		//$qb->explain(__FILE__);
		return $qb;
	}
	public static function correlatableUserVariableIds(): \Illuminate\Database\Query\Builder{
		$qb = DB::table(self::TABLE) // Don't use Eloquent for this, or it overwrites memory models with ids
			->join(Variable::TABLE, self::TABLE . '.' . self::FIELD_VARIABLE_ID, '=',
				Variable::TABLE . '.' . Variable::FIELD_ID);
		$qb->where(UserVariable::TABLE . '.' .
			UserVariable::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN, '>',
			CorrelationCauseNumberOfRawMeasurementsProperty::MINIMUM_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN - 1);
		$qb->where(UserVariable::TABLE . '.' .
			UserVariable::FIELD_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS, '>',
			CorrelationCauseNumberOfProcessedDailyMeasurementsProperty::MINIMUM_PROCESSED_DAILY_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN -
			1);
		$qb->where(UserVariable::TABLE . '.' . UserVariable::FIELD_NUMBER_OF_CHANGES, '>',
			CorrelationCauseChangesProperty::MINIMUM_CHANGES - 1);
		$qb->where(UserVariable::TABLE . '.' .
			UserVariable::FIELD_NUMBER_OF_UNIQUE_DAILY_VALUES, '>', 1);
		$qb->columns[] = self::TABLE.'.'.self::FIELD_ID." as id";
		$qb->columns[] = self::TABLE.'.'.self::FIELD_VARIABLE_ID." as variable_id";
		$qb->columns[] = Variable::TABLE.'.'.Variable::FIELD_NAME." as name";
		return $qb;
	}
	/**
	 * @return string|null
	 */
	public function getLatestFillingAt(): ?string{
        $t = $this->getLatestFillingTime();
        if(!$t){return null;}
        return db_date($t);
	}
	public function getLatestFillingTime(): ?int{
		return $this->attributes[UserVariable::FIELD_LATEST_FILLING_TIME] ?? null;
	}
	/**
	 * @return string|null
	 */
	public function getEarliestFillingAt(): ?string{
		$t = $this->getEarliestFillingTime();
		if(!$t){return null;}
		return db_date($t);
	}
	public function getSubtitleAttribute(): string{
		$parts = [];
		if(!$this->hasVariableId()){
			return static::CLASS_DESCRIPTION;
		}
		if($this->getNumberOfMeasurements()){
			$parts[] = "{$this->getNumberOfMeasurements()} measurements";
			if($latestAt = $this->getLatestTaggedMeasurementAt()){
				$parts[] = "Recorded " . TimeHelper::timeSinceHumanString($latestAt);
			}
		}
		if($n = $this->getNumberOfTrackingReminders()){
			$parts[] = "$n reminders set";
		}
		if($msg = $this->optimal_value_message){
			$parts[] = $msg;
		}
		if(!$parts){
			$parts[] = "No measurements or reminders set";
		}
		return implode(" | ", $parts);
	}
	public function getEarliestFillingTime(): ?int{
		return $this->attributes[UserVariable::FIELD_EARLIEST_FILLING_TIME] ?? null;
	}
	/**
	 * @return QMButton[]
	 */
	public function getActionButtons(): array{
		$arr = parent::getActionButtons();
		$arr[] = $this->getCorrelateButton();
		return $arr;
	}
	public function isOutcome(): bool{
		return $this->getVariable()->isOutcome();
	}
	public function getCorrelateButton(): CorrelateButton{
		return new CorrelateButton($this);
	}
	/**
	 * @param bool $generatePDF
	 * @return QMSendgrid
	 * @throws InvalidEmailException
	 */
	public function email(bool $generatePDF = false): QMSendgrid{
		$html = $this->getEmailBody();
		$a = $this->getRootCauseAnalysis();
		$email = new QMSendgrid(230, $a->getTitleAttribute(), null,$this);
		$email->setHtmlContent($html);
		if($generatePDF){
			$a->generateHtmlPdfXlsAndUploadToS3AndPostIfNecessary();
			$email->attachPdfFromReport($a);
		}
		try {
			$email->setRecipientAndEmailFromUserId(230);
		} catch (NoEmailAddressException $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());
		}
		try {
			$email->send();
		} catch (TooManyEmailsException | TypeException $e) {
			le($e);
		}
		return $email;
	}
	/**
	 * @return string
	 */
	public function getEmailBody(): string{
		return $this->getRootCauseEmailBody();
	}
	/**
	 * @param RootCauseAnalysis|null $a
	 * @return string
	 */
	public function getRootCauseEmailBody(RootCauseAnalysis $a = null): string{
		if(!$a){
			$a = $this->getRootCauseAnalysis();
		}
		$u = $this->getQMUser();
		$maxWidth = CssHelper::GLOBAL_MAX_POST_CONTENT_WIDTH;
		$html =
			"<div style=\"max-width: " . $maxWidth . "px; font-family: 'Source Sans Pro', sans-serif; margin: auto;\">";
		if($this->isRating()){
			$url = IonicHelper::getSettingsUrl();
			$footer = "If you'd prefer to optimize sometime besides " . $this->getDisplayNameAttribute() .
				" you can <a href=\"$url\">update your primary outcome variable here</a>.";
			$html .= FacesRatingQMCard::getFacesRatingCardHtml($this->getQuestion(), $footer);
		}
		$html .= $u->getDataQuantityListRoundedButtonsHTML();
		$html .= $a->getDemoOrUserFactorListForEmailHtml();
		$html .= HelpQMCard::instance()->getHtml(QMColor::HEX_DARK_GRAY);
		$html .= "</div>";
		$this->validateEmailHtml($html);
		return $html;
	}
	/**
	 * @param string $html
	 */
	private function validateEmailHtml(string $html): void{
		try {
			QMStr::assertStringContains($html, [
                "users/" . $this->getUser()->getSlug(),
                $this->getVariableName(),
            ], __FUNCTION__);
		} catch (InvalidStringException $e) {
			le($e);
		}
		try {
			RootCauseAnalysis::validateFactorsList($html);
		} catch (InvalidStringException $e) {
			le($e);
		}
	}
	/**
	 * @throws NotEnoughDataException
	 */
	public function exceptionIfWeShouldNotPost(): void{
		if($this->isStupidVariable()){
			le("Not posting stupid variable $this");
		}
		if($this->getNumberOfUserVariableRelationships()){
			return;
		}
		if($this->getNumberOfRawMeasurementsWithTagsJoinsChildren()){
			return;
		}
		throw new NotEnoughDataException($this, "Not Enough Data to Publish",
			"Not posting because no user_variable_relationships or measurements for $this");
	}
	public function getNumberOfRawMeasurementsWithTagsJoinsChildren(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN] ?? null;
	}
	/** @noinspection PhpUnused */
	/**
	 * @param $timeAt
	 * @return QMMeasurement|null
	 */
	public function findMeasurementIfSet($timeAt): ?QMMeasurement{
		if($this->measurementsAreSet()){
			return $this->findMeasurement($timeAt);
		}
		return null;
	}
	/**
	 * @param $timeAt
	 * @return QMMeasurement|null
	 */
	public function findMeasurement($timeAt): ?QMMeasurement{
		$startAt = db_date($timeAt);
		$measurements = $this->getQMMeasurementsIndexedByStartAt();
		return $measurements[$startAt] ?? null;
	}
	/**
	 * @return QMMeasurement[]
	 */
	public function getQMMeasurementsIndexedByStartAt(): array{
		return QMMeasurement::indexMeasurementsByStartAt($this->getQMMeasurements());
	}
	/** @noinspection PhpUnused */
	/**
	 * @return QMMeasurement[]
	 */
	public function getQMMeasurements(): array{
		return $this->getDBModel()->getQMMeasurements();
	}
	/** @noinspection PhpUnused */
	/**
	 * @param $timeAt
	 * @return float|null
	 */
	public function findValue($timeAt): ?float{
		$byAt = $this->getQMMeasurements();
		$at = db_date($timeAt);
		if(isset($byAt[$at])){
			return $byAt[$at]->value;
		}
		$rounded = $this->roundStartTime($timeAt);
		$at = db_date($rounded);
		if(isset($byAt[$at])){
			return $byAt[$at]->value;
		}
		return null;
	}
	/** @noinspection PhpUnused */
	/**
	 * @param int|string $startTimeAt
	 * @return int
	 */
	public function roundStartTime($startTimeAt): int{
		$startTime = time_or_exception($startTimeAt);
		$min = $this->getMinimumAllowedSecondsBetweenMeasurements();
		if($min < 2){
			return $startTime;
		}
		return Stats::roundToNearestMultipleOf($startTime, $min);
	}
	/** @noinspection PhpUnused */
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
		return $actions;
	}
	/** @noinspection PhpUnused */
	/**
	 * Get the lenses available for the resource.
	 * @param Request $request
	 * @return array
	 */
	public function getLenses(Request $request): array{
		$lenses = parent::getLenses($request);
		$lenses[] = new FavoritesLens();
		if(QMAuth::isAdmin()){
			$lenses[] = new StrategyUserVariablesLens();
		}
		return $lenses;
	}
	public function getAdminMeasurementsUrl(): string{
		return Measurement::generateDataLabIndexUrl([Measurement::FIELD_USER_VARIABLE_ID => $this->getUserVariableId()]);
	}
	public function getAggregatedMeasurements(int $seconds): array{
		$dbm = $this->getQMUserVariable();
		return $dbm->getAggregatedValues($seconds);
	}
	public function getTitleAttribute(): string{
		if(!$this->attributes){
			return static::getClassNameTitle();
		}
		$v = $this->getVariable();
		if(!$v){
			return static::getClassNameTitle();
		}
		return $v->getTitleAttribute();
	}
	public function getAlias(): ?string{
		return $this->attributes[UserVariable::FIELD_ALIAS] ?? null;
	}
	/** @noinspection PhpUnused */
	public function getAnalysisRequestedAt(): ?string{
		return $this->attributes[UserVariable::FIELD_ANALYSIS_REQUESTED_AT] ?? null;
	}
	public function getAnalysisSettingsModifiedAt(): ?string{
		return $this->attributes[UserVariable::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT] ?? null;
	}
	public function getAnalysisStartedAt(): ?string{
		return $this->attributes[UserVariable::FIELD_ANALYSIS_STARTED_AT] ?? null;
	}
	public function getAverage(): float{
		return $this->getDBModel()->getAverage();
	}
	/**
	 * Get the fields displayed by the resource.
	 * @return array
	 */
	public function getFields(): array{
		$fields = [];
		$fields[] = $this->imageField();
		$fields[] = $this->getNameDetailsLink();
		$fields = array_merge($fields, $this->getShowablePropertyFields());
		$fields[] = MeasurementBaseAstralResource::hasMany();
		return $fields;
	}
	public function getAverageSecondsBetweenMeasurements(): ?int{
		return $this->attributes[UserVariable::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS] ?? null;
	}
	public function getBadgeText(): ?string{
		return $this->getNumberOfUserVariableRelationships();
	}
	public function getBestCauseVariableId(): ?int{
		return $this->attributes[UserVariable::FIELD_BEST_CAUSE_VARIABLE_ID] ?? null;
	}
	public function getImage(): string{
		if($this->attributes){
			try {
				return $this->attributes[Variable::FIELD_IMAGE_URL] ?? $this->getQMVariableCategory()->getImage();
			} catch (InvalidStringException $e) {
				$this->logError(__METHOD__.": ".$e->getMessage());
			}
		}
		return static::DEFAULT_IMAGE;
	}
	/**
	 * @return GlobalVariableRelationship|UserVariableRelationship
     */
	public function getCorrelation(): ?BaseModel{
		$best = $this->getBestUserVariableRelationship();
		if(!$best){
			$best = $this->getBestGlobalVariableRelationship();
		}
		$best->validateVariableIds();
		return $best;
	}
	/** @noinspection PhpUnused */
	public function getBestEffectVariableId(): ?int{
		return $this->attributes[UserVariable::FIELD_BEST_EFFECT_VARIABLE_ID] ?? null;
	}
	/** @noinspection PhpUnused */
	public function getBestUserVariableRelationshipId(): ?int{
		return $this->attributes[UserVariable::FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID] ?? null;
	}
	/**
	 * @return string
	 */
	public function getBestUserVariableRelationshipLink(): string{
		$id = $this->best_user_variable_relationship_id;
		if(!$id){
			return "N/A";
		}
		$url = UserVariableRelationship::generateDataLabShowLink($id, $this->optimal_value_message);
		return $url;
	}
	public function getCauseOnly(): ?bool{
		return $this->attributes[UserVariable::FIELD_CAUSE_ONLY] ?? null;
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function getCauseVariablesToCorrelateWith(): array{
		$uv = $this->getQMUserVariable();
		return $uv->getCauseVariablesToCorrelateWith();
	}
	/**
	 * @return UserVariableChartGroup
	 */
	public function getChartGroup(): ChartGroup{
		$dbm = $this->getDBModel();
		return $dbm->getChartGroup();
	}
	public function getChartsUrl(): string{
		return $this->getUrl();
	}
	/**
	 * @return \Illuminate\Database\Eloquent\Collection|UserVariableClient[]
	 */
	public function getUserVariableClients(): \Illuminate\Support\Collection {
		$this->loadMissing('user_variable_clients');
		$uvcs = $this->user_variable_clients;
		return $uvcs;
	}
	public function getClientIds(): array{
		$clients = $this->getUserVariableClients();
		$plucked = $clients->pluck(UserVariableClient::FIELD_CLIENT_ID);
		return $plucked->all();
	}
	/**
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function getCombinationOperationAttribute(): string{
		$value = $this->attributes[self::FIELD_COMBINATION_OPERATION] ?? null;
		if(!$value){
			$value = $this->getVariable()->combination_operation;
		}
		return $value;
	}
	public function getCorrelateUrl(): string{
		return $this->getUrl(['correlate' => true]);
	}
	public function getUrl(array $params = []): string{
		return $this->getShowUrl($params);
	}
	/**
	 * @return string
	 */
	public function getShowFolderPath(): string{
		$folderPath = "users/{$this->getUserId()}/variables/{$this->getVariableIdAttribute()}";
		$folderPath = "user-variables/{$this->getId()}";
		//if (AppMode::isTestingOrStaging()) {$folderPath = "testing/$folderPath";}
		return $folderPath;
	}
	/** @noinspection PhpUnused */
	/**
	 * @param array $params
	 * @return View
	 */
	public function getShowPageView(array $params = []): View{
		$params['content'] = $this->getShowContent();
		return view('user-variable', $this->getShowParams($params));
	}
	/**
	 * @return string|null
	 */
	public function getSlugWithNames(): string{
		$u = $this->getUser();
		$uName = $u->getUrlSafeNiceName();
		$variable = QMStr::slugify($this->getDisplayNameAttribute());
		return "$variable-for-$uName";
	}
	/** @noinspection PhpUnused */
	/**
	 * @param string|int $timeAt
	 * @return AnonymousMeasurement
	 */
	public function getDailyMeasurement($timeAt): ?AnonymousMeasurement{
		$date = TimeHelper::YYYYmmddd($timeAt);
		$measurements = $this->getDailyMeasurementsWithoutTagsOrFilling();
		return $measurements[$date] ?? null;
	}
	/**
	 * @return AnonymousMeasurement[]
	 */
	public function getDailyMeasurementsWithoutTagsOrFilling(): array{
		$v = $this->getDBModel();
		$measurements = $v->getDailyMeasurementsWithoutTagsOrFilling();
		return $measurements;
	}
	/**
	 * @return DailyMeasurement[]
	 */
	public function getDailyMeasurementsWithTagsInUserUnit(): array{
		return $this->getQMUserVariable()->getDailyMeasurementsWithTagsInUserUnit();
	}
	public function getValidDailyMeasurementsWithTagsInUserUnit(): array{
		return $this->getQMUserVariable()->getValidDailyMeasurementsWithTagsInUserUnit();
	}
	public function getDailyValues(): array{
		return $this->getDBModel()->getDailyValues();
	}
	/**
	 * @return string
	 */
	public function getDataQuantityHTML(): string{
		$num = $this->getOrCalculateNumberOfMeasurements();
		$html = "
            <h3>" . $this->getDisplayNameAttribute() . " Data Quantity</h3>
            <p>There are currently $num raw measurements";
		if($num){
			$html .= " with " . $this->getNumberOfChanges() . " changes spanning " .
				$this->getNumberOfDaysBetweenEarliestAndLatestTaggedMeasurement() . " days from " .
				$this->getEarliestTaggedMeasurementDate() . " to " . $this->getLatestTaggedMeasurementDate();
		}
		$html .= ".
            </p>";
		$html .= $this->getChartsButtonHtml();
		return $html;
	}
	public function getNumberOfChanges(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_CHANGES] ?? null;
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
	/**
	 * @param null $value
	 * @return array
	 */
	public function getDataSourcesCountAttribute($value = null): array{
		$value = $value ?? $this->attributes[self::FIELD_DATA_SOURCES_COUNT] ?? [];
		return QMArr::toArray($value);
	}
	public function getDeletedAt(): ?string{
		return $this->attributes[UserVariable::FIELD_DELETED_AT] ?? null;
	}
	public function getDeletionReason(): ?string{
		return $this->attributes[UserVariable::FIELD_DELETION_REASON] ?? null;
	}
	/**
	 * @return string
	 * Much faster than getUniqueNamesSlug
	 */
	public function getSlugFromMemory(): string{
		if($this->getUserFromMemory()){
			if($this->getVariableFromMemory()){
				return $this->getUniqueNamesSlug();
			}
		}
		return $this->getUniqueIndexIdsSlug();
	}
	/**
	 * @param null $value
	 * @return int
	 */
	public function getDurationOfActionAttribute($value = null): int{
		if($value){
			return $value;
		}
		if($value = $this->attributes[self::FIELD_DURATION_OF_ACTION] ?? null){
			return $value;
		}
		return $this->getVariable()->getDurationOfActionAttribute();
	}
	public function getUniqueNamesSlug(): string{
		return QMStr::slugify($this->getVariableName()) . "-" . $this->getUser()->getUniqueNamesSlug();
	}
	/**
	 * @param $value
	 * @return int|null
	 */
	public function getEarliestFillingTimeAttribute($value): ?int{
		if($value){
			return time_or_null($value);
		}
		return null;
	}
	public function getEarliestNonTaggedMeasurementStartAt(): ?string{
		return $this->attributes[UserVariable::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT] ?? null;
	}
	public function getEarliestSourceMeasurementStartAt(): ?string{
		return $this->attributes[UserVariable::FIELD_EARLIEST_SOURCE_MEASUREMENT_START_AT] ?? null;
	}
	public function getEarliestSourceMeasurementStartAtAttribute(): ?string{
		if($at = $this->attributes[self::FIELD_EARLIEST_SOURCE_MEASUREMENT_START_AT] ?? null){
			return $at;
		}
		return null;
	}
	public function getEarliestTaggedMeasurementStartAt(): ?string{
		return $this->attributes[UserVariable::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT] ?? null;
	}
	public function getEditButton(): QMButton{
		return $this->getSettingsButton();
	}
	public function getExperimentEndTime(): ?string{
		return $this->attributes[UserVariable::FIELD_EXPERIMENT_END_TIME] ?? null;
	}
	public function getExperimentStartTime(): ?string{
		return $this->attributes[UserVariable::FIELD_EXPERIMENT_START_TIME] ?? null;
	}
	/**
	 * @return float|null
	 * @throws \App\Exceptions\IncompatibleUnitException
	 * @throws \App\Exceptions\InvalidVariableValueException
	 */
	public function getFillingValueInUserUnit(): ?float{
		$val = $this->getFillingValueAttribute();
		if($val === null){
			return null;
		}
		return $this->convertToUserUnit($val);
	}
	/**
	 * @param float $inCommonUnit
	 * @return float|int|null
	 * @throws \App\Exceptions\IncompatibleUnitException
	 * @throws \App\Exceptions\InvalidVariableValueException
	 */
	public function convertToUserUnit(float $inCommonUnit): float{
		return QMUnit::convertValueByUnitIds($inCommonUnit, $this->getCommonUnitId(), $this->getUserUnitId(), $this,
			null);
	}
	public function getUserUnitId(): int{
		return $this->getUnitIdAttribute();
	}
	public function getFontAwesome(): string{
		if(!$this->hasVariableCategoryId()){
			return UserVariable::FONT_AWESOME;
		}
		return $this->getQMVariableCategory()->getFontAwesome();
	}
	public function hasVariableCategoryId(): bool{
		return $this->attributes[self::FIELD_VARIABLE_ID] ?? false;
	}
	/**
	 * @return string
	 */
	public function getHistoryUrl(): string{
		return IonicHelper::getHistoryUrl([
			'doNotProcess' => true,
			'variableName' => $this->getVariableName(),
			'userId' => $this->getUserId(),
		]);
	}
	/**
	 * @param QMDeviceToken|null $dt
	 * @return IndividualPushNotificationData
	 */
	public function getIndividualPushNotificationData(QMDeviceToken $dt = null): ?IndividualPushNotificationData{
		$d = $this->createIndividualPushNotificationData($dt);
		return $d;
	}
	/**
	 * @param QMDeviceToken|null $deviceToken
	 * @return IndividualPushNotificationData
	 */
	protected function createIndividualPushNotificationData(QMDeviceToken $deviceToken = null): IndividualPushNotificationData{
		$d = new IndividualPushNotificationData($deviceToken, $this);
		return $d;
	}
	public function getInformationalUrl(): ?string{
		return $this->attributes[UserVariable::FIELD_INFORMATIONAL_URL] ?? null;
	}
	/**
	 * @param string $reason
	 * @return bool|null
	 */
	public function hardDeleteWithRelations(string $reason): ?bool{
		$this->logError("Hard deleting because $reason");
		Measurement::whereUserVariableId($this->id)->forceDelete();
		TrackingReminderNotification::whereUserVariableId($this->id)->forceDelete();
		TrackingReminder::whereUserVariableId($this->id)->forceDelete();
		UserVariableRelationship::whereCauseUserVariableId($this->id)->forceDelete();
		UserVariableRelationship::whereEffectUserVariableId($this->id)->forceDelete();
		UserVariableClient::whereUserVariableId($this->id)->forceDelete();
		$post = $this->wp_post;
		if($post){
			$post->hardDeleteWithRelations($reason);
		}
		$result = $this->forceDelete();
		return $result;
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		$arr = [
			$this->getNumberOfMeasurementsButton(),
			$this->getNumberOfTrackingRemindersButton(),
			$this->getCommonVariableButton(),
		];
		if($this->isOutcome() || $this->getNumberOfUserVariableRelationshipsAsEffectAttribute()){
			$arr[] = $this->getNumberOfUserVariableRelationshipsWhereEffectButton();
		}
		if($this->isPredictor() || $this->getNumberOfUserVariableRelationshipsAsCauseAttribute()){
			$arr[] = $this->getNumberOfUserVariableRelationshipsWhereCauseButton();
		}
		$u = QMAuth::getQMUser();
		if(!$u || $u->getId() !== $this->getUserId()){
			$b = new UserVariableUserButton($this);
			$arr[] = $b;
		}
		if($this->getRawAttribute(UserVariable::FIELD_VARIABLE_CATEGORY_ID)){
			$arr[] = new UserVariableVariableCategoryButton($this);
		} else{
			$arr[] = $this->getVariable()->getVariableCategoryButton();
		}
		return $arr;
	}
	public function forceDelete(): ?bool{
		$this->user_variable_clients()->forceDelete();
		return parent::forceDelete();
	}
	public function getNumberOfMeasurementsButton(): QMButton{
		$number = $this->getNumberOfRawMeasurementsWithTagsJoinsChildren();
		if($number === null){
			$number = "N/A";
		}
		$b = new UserVariableMeasurementsButton($this);
		$b->setBadgeText($number);
		return $b;
	}
	public function getNumberOfTrackingRemindersButton(): QMButton{
		$number = $this->number_of_tracking_reminders;
		if($number === null){
			$number = "N/A";
		}
		$b = new UserVariableTrackingRemindersButton($this);
		$b->setBadgeText($number);
		return $b;
	}
	public function getCommonVariableButton(): QMButton{
		$b = new UserVariableVariableButton($this);
		return $b;
	}
	public function getNumberOfUserVariableRelationshipsWhereEffectButton(): QMButton{
		$number = $this->number_of_user_variable_relationships_as_effect;
		if($number === null){
			$number = "N/A";
		}
		$b = new UserVariableCorrelationsWhereEffectUserVariableButton($this);
		$b->setBadgeText($number);
		return $b;
	}
	/**
	 * @return bool
	 */
	public function isPredictor(): ?bool{
		if($this->cause_only){
			return false;
		}
		return $this->getQMVariableCategory()->predictor;
	}
	public function getNumberOfUserVariableRelationshipsWhereCauseButton(): QMButton{
		$number = $this->number_of_user_variable_relationships_as_cause;
		if($number === null){
			$number = "N/A";
		}
		$b = new UserVariableCorrelationsWhereCauseUserVariableButton($this);
		$b->setBadgeText($number);
		return $b;
	}
	public function getInternalErrorMessage(): ?string{
		return $this->attributes[UserVariable::FIELD_INTERNAL_ERROR_MESSAGE] ?? null;
	}
	/**
	 * @param $data
	 */
	public function populateForeignKeys($data){
		if($variableId = UserVariableVariableIdProperty::pluckOrDefault($data)){
			$this->variable_id = $variableId;
		}
		if($catId = UserVariableVariableCategoryIdProperty::pluckOrDefault($data)){
			$this->variable_category_id = $catId;
		}
		if($userId = UserVariableUserIdProperty::pluckOrDefault($data)){
			$this->user_id = $userId;
		}
		if($unitId = UserVariableDefaultUnitIdProperty::pluckOrDefault($data)){
			$this->default_unit_id = $unitId; // Must be set before populating values
		}
		parent::populateForeignKeys($data);
	}
	/**
	 * @param array $params
	 * @param bool $forAdmin
	 * @return string
	 */
	public function getIonicChartsUrl(array $params = [], bool $forAdmin = false): string{
		$subDomain = ($forAdmin) ? IonicHelper::PATIENTS_SUB_DOMAIN : null;
		$params = array_merge($params, $this->getUrlParams());
		return IonicHelper::getChartsUrl($params, $subDomain);
	}
	public function getUrlParams(): array{
		return [
			'user_variable_id' => $this->getId(),
			UserVariable::FIELD_VARIABLE_ID => $this->getVariableIdAttribute(),
			UserVariable::FIELD_VARIABLE_CATEGORY_ID => $this->getVariableCategoryId(),
			UserVariable::FIELD_DEFAULT_UNIT_ID => $this->getUnitIdAttribute(),
			UserVariable::FIELD_USER_ID => $this->getUserId(),
			'variable_name' => $this->getVariableName(),
		];
	}
	public function getIsPublic(): ?bool{
		return $this->getAttribute(UserVariable::FIELD_IS_PUBLIC);
	}
	public function getJoinWith(): ?int{
		return $this->attributes[UserVariable::FIELD_JOIN_WITH] ?? null;
	}
	/**
	 * @return QMMeasurement
	 */
	public function getLastDailyMeasurementWithTagsAndFilling(): ?QMMeasurement{
		$measurements = $this->getDailyMeasurementsWithTagsAndFilling();
		if(!$measurements){
			return null;
		}
		$unIndexed = array_values($measurements);
		$total = count($unIndexed);
		/** @var QMMeasurement $last */
		$last = $unIndexed[$total - 1];
		if($last->value == 0 && $this->hasFillingValue() && $last->wasLessThan24HoursAgo() && count($unIndexed) > 1){
			$last = $unIndexed[$total - 2];  // Sometimes we prematurely use filling value
		}
		if(is_array($last)){
			le("should be a measurement not array!", $last);
		}
		return $last;
	}
	public function hasFillingValue(): bool{
		return BaseFillingValueProperty::hasFillingValue($this);
	}
	/**
	 * @return QMMeasurement
	 */
	public function getLastDailyNonZeroQMMeasurement(): ?QMMeasurement{
		$measurements = $this->getDailyMeasurementsWithTagsAndFilling();
		return collect($measurements)->sortByDesc('startTime')->filter(function($m){
			return (float)$m->value !== (float)0;
		})->first();
	}
	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function setAttribute($key, $value){
		// if($key === self::FIELD_CLIENT_ID && $value === BaseClientIdProperty::CLIENT_ID_QUANTIMODO){le('$key === self::FIELD_CLIENT_ID && $value === BaseClientIdProperty::CLIENT_ID_QUANTIMODO');}
		parent::setAttribute($key, $value);
	}
	/**
	 * @return DailyMeasurement[]
	 */
	public function getDailyMeasurementsWithTagsAndFilling(): array{
		return $this->getDBModel()->getValidDailyMeasurementsWithTagsAndFilling();
	}
	public function getLastOriginalUnitId(): ?int{
		return $this->attributes[UserVariable::FIELD_LAST_ORIGINAL_UNIT_ID] ?? null;
	}
	public function getLastOriginalValue(): ?float{
		return $this->attributes[UserVariable::FIELD_LAST_ORIGINAL_VALUE] ?? null;
	}
	public function getLastProcessedDailyValue(): ?float{
		return $this->attributes[UserVariable::FIELD_LAST_PROCESSED_DAILY_VALUE] ?? null;
	}
	public function getLastUnitId(): ?int{
		return $this->attributes[UserVariable::FIELD_LAST_UNIT_ID] ?? null;
	}
	public function getLastValue(): ?float{
		return $this->attributes[UserVariable::FIELD_LAST_VALUE] ?? null;
	}
	public function getVariableCategoryId(): int{
		return $this->attributes[self::FIELD_VARIABLE_CATEGORY_ID] ?? $this->getVariable()->getVariableCategoryId();
	}
	public function getLatestNonTaggedMeasurementStartAt(): ?string{
		return $this->attributes[UserVariable::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT] ?? null;
	}
	public function getLatestSourceMeasurementStartAt(): ?string{
		return $this->attributes[UserVariable::FIELD_LATEST_SOURCE_MEASUREMENT_START_AT] ?? null;
	}
	public function getLatestSourceMeasurementStartAtAttribute(): ?string{
		if($at = $this->attributes[self::FIELD_LATEST_SOURCE_MEASUREMENT_START_AT] ?? null){
			return $at;
		}
		return null;
	}
	public function getLatitude(): ?float{
		return $this->attributes[UserVariable::FIELD_LATITUDE] ?? null;
	}
	public function getLocation(): ?string{
		return $this->attributes[UserVariable::FIELD_LOCATION] ?? null;
	}
	public function getLogMetaDataString(): string{
		if(!$this->hasVariableId()){
			return __CLASS__;
		}
		return $this->getVariableName();
	}
	public function getVariableName(): ?string{
		return $this->getVariable()->getNameAttribute();
	}
	public function getLongitude(): ?float{
		return $this->attributes[UserVariable::FIELD_LONGITUDE] ?? null;
	}
	public function getMaximumAllowedDailyValue(): ?float{ return $this->getVariable()->getMaximumAllowedDailyValue(); }
	/**
	 * @return float|null
	 * @noinspection PhpUnused
	 */
	public function getMaximumAllowedValueInUserUnit(): ?float{
		return $this->getAttributeInUserUnit(self::FIELD_MAXIMUM_ALLOWED_VALUE);
	}
	public function getAttributeInUserUnit(string $key): ?float{
		/** @var UserVariableValuePropertyTrait $inCommonUnit */
		$inCommonUnit = $this->getPropertyModel($key);
		try {
			return $inCommonUnit->inUserUnit();
		} catch (IncompatibleUnitException | InvalidVariableValueException $e) {
			le($e);
		}
	}
	public function getMaximumDailyValue(): ?float{
		$dbm = $this->getDBModel();
		return $dbm->getMaximumDailyValue();
	}
	public function getMaximumRecordedValue(): ?float{
		return $this->attributes[UserVariable::FIELD_MAXIMUM_RECORDED_VALUE] ?? null;
	}
	/**
	 * @param string $startAt
	 * @return QMMeasurement
	 */
	public function getMeasurementByStartAt(string $startAt): ?QMMeasurement{
		$measurements = $this->getNewAndExistingMeasurementsIndexedByStartAt();
		return $measurements[$startAt] ?? null;
	}
	/**
	 * @return QMMeasurement[]
	 */
	public function getNewAndExistingMeasurementsIndexedByStartAt(): array{
		$combined = $this->getCombinedNewQMMeasurements();
		$byStartTime = [];
		$raw = $this->getQMMeasurements();
		foreach($raw as $m){
			$byStartTime[$m->getStartAt()] = $m;
		}
		foreach($combined as $m){
			$byStartTime[$m->getStartAt()] = $m;
		}
		return $byStartTime;
	}
	public function getMeasurementsAtLastAnalysis(): ?int{
		return $this->attributes[UserVariable::FIELD_MEASUREMENTS_AT_LAST_ANALYSIS] ?? null;
	}
	/**
	 * @param string $fromAt
	 * @param string $endAt
	 * @return QMMeasurement[]
	 */
	public function getMeasurementsBetween(string $fromAt, string $endAt): array{
		$measurements = $this->getQMMeasurements();
		$between = collect($measurements)->where('startTime', '>', strtotime($fromAt))
			->where('startTime', '<', strtotime($endAt))->all();
		return $between;
	}
	/**
	 * @return Measurement[]
	 */
	public function getMeasurementsIndexedByStartAt(): array{
		$measurements = $this->getMeasurementsFromMemory();
		if(!$measurements){
			$dbm = $this->getQMUserVariable();
			if($dbm->measurementsAreSet()){
				$measurements = $dbm->getMeasurements();
			}
		}
		if(!$measurements){
			$measurements = $this->measurements()->get();
		}
		$byAt = [];
		foreach($measurements as $m){
			$byAt[$m->getStartAtAttribute()] = $m;
		}
		return $byAt;
	}
	public function getMeasurementsFromMemory(): array{
		return Measurement::fromMemoryWhere(Measurement::FIELD_USER_VARIABLE_ID, $this->id);
	}
	public function subtitle(): string{
		$m = $this->getUserVariable();
		return $m->getNumberOfSubtitle();
	}
	public function getMedianSecondsBetweenMeasurements(): ?int{
		return $this->attributes[UserVariable::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS] ?? null;
	}
	/**
	 * @return float|null
	 * @noinspection PhpUnused
	 */
	public function getMinimumAllowedValueInUserUnit(): ?float{
		return $this->getAttributeInUserUnit(self::FIELD_MINIMUM_ALLOWED_VALUE);
	}
	public function getMinimumDailyValue(): ?float{
		$dbm = $this->getDBModel();
		return $dbm->getMinimumDailyValue();
	}
	/**
	 * @return int
	 */
	public function getCommonUnitId(): int{
		$commonUnitId = $this->getVariable()->getUnitIdAttribute();
		return $commonUnitId;
	}
	public function getMinimumRecordedValue(): ?float{
		return $this->attributes[UserVariable::FIELD_MINIMUM_RECORDED_VALUE] ?? null;
	}
	public function getMostCommonConnectorId(): ?int{
		return $this->attributes[UserVariable::FIELD_MOST_COMMON_CONNECTOR_ID] ?? null;
	}
	/**
	 * @param array $options
	 * @return bool
	 * @throws ModelValidationException
	 */
	public function save(array $options = []): bool{
        if(!$this->number_of_tracking_reminders){
            $this->number_of_tracking_reminders = 0;
        }
        if(!$this->number_of_measurements){
            $this->number_of_measurements = 0;
        }
		try {
			$res = parent::save($options);
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			$res = parent::save($options);
		}
		$this->addToMemory();
		return $res;
	}
	public function getMostCommonOriginalUnitId(): ?int{
		return $this->attributes[UserVariable::FIELD_MOST_COMMON_ORIGINAL_UNIT_ID] ?? null;
	}
	public function getMostCommonSourceName(): ?string{
		return $this->attributes[UserVariable::FIELD_MOST_COMMON_SOURCE_NAME] ?? null;
	}
	public function getMostCommonValue(): ?float{
		return $this->attributes[UserVariable::FIELD_MOST_COMMON_VALUE] ?? null;
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
		return $this->attributes[UserVariable::FIELD_NEWEST_DATA_AT] ?? null;
	}
	/**
	 * @return NotificationButton[]
	 */
	public function getNotificationActionButtons(): array{
		return $this->getDBModel()->getNotificationActionButtons();
	}
	public function getNumberCommonTaggedBy(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_COMMON_TAGGED_BY] ?? null;
	}
	public function getNumberOfCommonChildren(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_COMMON_CHILDREN] ?? null;
	}
	public function getNumberOfCommonFoods(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_COMMON_FOODS] ?? null;
	}
	public function getNumberOfCommonIngredients(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_COMMON_INGREDIENTS] ?? null;
	}
	public function getNumberOfCommonJoinedVariables(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_COMMON_JOINED_VARIABLES] ?? null;
	}
	public function getNumberOfCommonParents(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_COMMON_PARENTS] ?? null;
	}
	public function getNumberOfCommonTags(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_COMMON_TAGS] ?? null;
	}
	public function getNumberOfSoftDeletedMeasurements(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_SOFT_DELETED_MEASUREMENTS] ?? null;
	}
	public function getNumberOfTrackingReminderNotifications(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS] ?? null;
	}
	public function getNumberOfUniqueDailyValues(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_UNIQUE_DAILY_VALUES] ?? null;
	}
	public function getNumberOfUniqueValues(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_UNIQUE_VALUES] ?? null;
	}
	public function getNumberOfUserChildren(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_USER_CHILDREN] ?? null;
	}
	public function getNumberOfUserFoods(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_USER_FOODS] ?? null;
	}
	public function getNumberOfUserIngredients(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_USER_INGREDIENTS] ?? null;
	}
	public function getNumberOfUserJoinedVariables(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_USER_JOINED_VARIABLES] ?? null;
	}
	public function getNumberOfUserParents(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_USER_PARENTS] ?? null;
	}
	public function getNumberOfUserTags(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_USER_TAGS] ?? null;
	}
	public function getNumberUserTaggedBy(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_USER_TAGGED_BY] ?? null;
	}
	/**
	 * @param $value
	 * @return int
	 */
	public function getOnsetDelayAttribute($value): int{
		if($value !== null){
			return $value;
		}
		return $this->getHyperParameter(self::FIELD_ONSET_DELAY);
	}
	/**
	 * @param string $attribute
	 * @return mixed
	 */
	public function getHyperParameter(string $attribute){
		$val = $this->getRawAttribute($attribute);
		if($val !== null){
			return $val;
		}
		/** @var UserHyperParameterTrait $prop */
		$prop = $this->getPropertyModel($attribute);
		return $prop->getHyperParameter($attribute);
	}
	public function getOptimalValueMessage(): ?string{
		return $this->attributes[UserVariable::FIELD_OPTIMAL_VALUE_MESSAGE] ?? null;
	}
	/**
	 * @return TrackingReminder
	 */
	public function firstOrCreateTrackingReminder(): TrackingReminder{
		if($this->isManualTracking() === false){
			$this->exceptionIfNotProductionAPI("Not creating reminder for non-manual tracking variable: " .
				$this->getVariableName());
		}
		$relationship = $this->tracking_reminders();
		/** @var TrackingReminder $r */
		if($r = $relationship->first()){
			return $r;
		}
		$data = [
			TrackingReminder::FIELD_REMINDER_START_TIME => TrackingReminderReminderStartTimeProperty::DEFAULT_LOCAL_REMINDER_TIME,
			TrackingReminder::FIELD_VARIABLE_ID => $this->variable_id,
			TrackingReminder::FIELD_CLIENT_ID => TrackingReminderClientIdProperty::fromRequestJobOrSystem(),
			TrackingReminder::FIELD_USER_ID => $this->user_id,
			TrackingReminder::FIELD_USER_VARIABLE_ID => $this->id,
			TrackingReminder::FIELD_REMINDER_FREQUENCY => TrackingReminderReminderFrequencyProperty::getDefault(),
		];
		try {
			/** @var TrackingReminder $r */
			$r = $relationship->create($data);
		} catch (\Throwable $e) {
			$relationship->first();
			/** @var TrackingReminder $r */
			$r = $relationship->create($data);
		}
		if($r->isActive()){
			$r->firstOrCreateNotification();
		}
		return $r;
	}
	public function isManualTracking(): ?bool{
		return $this->getVariable()->manual_tracking;
	}
	public function getOutcomeAttribute(): ?bool{
		$val = $this->getAttributeOrVariableFallback(self::FIELD_OUTCOME);
		if($val === null){
			return null;
		}
		return (bool)$val;
	}
	public function getOutcomeOfInterest(): ?bool{
		return $this->attributes[UserVariable::FIELD_OUTCOME_OF_INTEREST] ?? null;
	}
	public function getParentId(): ?int{
		return $this->attributes[UserVariable::FIELD_PARENT_ID] ?? null;
	}
	public function getPredictorOfInterest(): ?bool{
		return $this->attributes[UserVariable::FIELD_PREDICTOR_OF_INTEREST] ?? null;
	}
	/**
	 * @return mixed|null
	 */
	public function getPublicAttribute(){
		return $this->getAttributeOrVariableFallback(self::FIELD_IS_PUBLIC);
	}
	/**
	 * @return QMDataSource[]
	 */
	public function getQMDataSources(): array{
		/** @var UserVariableClient[] $uvcs */
		$uvcs = $this->user_variable_clients()->get();
		$sources = [];
		foreach($uvcs as $uvc){
			if($ds = $uvc->getQMDataSource()){
				$sources[$uvc->client_id] = $ds;
			}
		}
		return $sources;
	}
	/**
	 * @return QMMeasurement[]
	 */
	public function getQMMeasurementsWithTags(): array{
		return $this->getQMUserVariable()->getMeasurementsWithTags();
	}
	/**
	 * @return QMUnit
	 */
	public function getQMUnit(): QMUnit{
		if($id = $this->default_unit_id){
			return QMUnit::getByNameOrId($id);
		}
		return $this->getVariable()->getCommonUnit();
	}
	/**
	 * @param string $attribute
	 * @return mixed|null
	 */
	public function getRawVariableAttribute(string $attribute){
		$l = $this->getVariable();
		return $l->getRawAttribute($attribute);
	}
	public function getReasonForAnalysis(): ?string{
		return $this->attributes[UserVariable::FIELD_REASON_FOR_ANALYSIS] ?? null;
	}
	public function getRecordSizeInKb(): ?int{
		return $this->attributes[UserVariable::FIELD_RECORD_SIZE_IN_KB] ?? null;
	}
	/**
	 * @return AnalyticalReport
	 */
	public function getReport(): AnalyticalReport{
		return $this->getRootCauseAnalysis();
	}
	/**
	 * @return RootCauseAnalysis
	 */
	public function getRootCauseAnalysis(): RootCauseAnalysis{
		$dbm = $this->getQMUserVariable();
		return $dbm->getRootCauseAnalysis();
	}
	public function getSecondToLastValue(): ?float{
		return $this->attributes[UserVariable::FIELD_SECOND_TO_LAST_VALUE] ?? null;
	}
	public function getSkewness(): ?float{
		return $this->attributes[UserVariable::FIELD_SKEWNESS] ?? null;
	}
	public function getSpread(): float{
		$dbm = $this->getDBModel();
		return $dbm->getSpread();
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
			"Duration of Action" => TimeHelper::convertSecondsToHumanString($this->getDurationOfAction()),
			"Filling Value" => $this->getFillingValueAttribute(),
			"Kurtosis" => $this->getKurtosis(),
			"Maximum Allowed Value" => $u->getValueAndUnitString($this->getMaximumAllowedValueAttribute()),
			"Mean" => $u->getValueAndUnitString($this->getMean()),
			"Median" => $u->getValueAndUnitString($this->getMedian()),
			"Minimum Allowed Value" => $u->getValueAndUnitString($this->getMinimumAllowedValueAttribute()),
			"Number of Changes" => $this->getNumberOfChanges(),
			"Number of VariableRelationships" => $this->getNumberOfUserVariableRelationships(),
			"Number of Measurements" => $this->getNumberOfMeasurements(),
			"Onset Delay" => TimeHelper::convertSecondsToHumanString($this->getOnsetDelay()),
			"Standard Deviation" => $this->getStandardDeviationAttribute(),
			"Unit" => $u->getNameAttribute(),
			"UPC" => $this->getUpc(),
			"Variable Category" => $this->getQMVariableCategory(),
			"Variable ID" => $this->getVariableIdAttribute(),
			"Variance" => $this->getVariance(),
		];
		$title = $this->getDisplayNameAttribute()." Info";
		$html = QMTable::convertObjectToVerticalPropertyValueTableHtml($table, $title);
		return $html;
	}
	/**
	 * @return QMUnit
	 */
	public function getUserOrCommonUnit(): QMUnit{
		if($this->default_unit_id){
			return QMUnit::find($this->default_unit_id);
		}
		return $this->getCommonUnit();
	}
	public function setIsPublic(?bool $isPublic): void{
		$this->setAttribute(UserVariable::FIELD_IS_PUBLIC, $isPublic);
	}
	public function getCombinationOperation(): ?string{
		return $this->attributes[UserVariable::FIELD_COMBINATION_OPERATION] ?? null;
	}
	public function getAnalysisEndedAt(): ?string{
		return $this->attributes[UserVariable::FIELD_ANALYSIS_ENDED_AT] ?? null;
	}
	public function getDurationOfAction(): int{
		return $this->attributes[self::FIELD_DURATION_OF_ACTION] ??
			$this->getVariable()->getDurationOfActionAttribute();
	}
	public function getFillingValueAttribute(): ?float{
		$val = $this->attributes[self::FIELD_FILLING_VALUE] ?? null;
		if((float)$val === -1.0){
			$val = null;
		}
		$type = $this->getFillingTypeAttribute();
		$val = BaseFillingTypeProperty::toValue($type, $val);
		if($val == -1){
			le('$val == -1');
		}
		//return BaseFillingValueProperty::fromType($this->getFillingType(), $this->getRawAttribute(Variable::FIELD_FILLING_VALUE));
		return $val;
	}
	public function getFillingTypeAttribute(): string{
		if($type = $this->getRawAttribute(self::FIELD_FILLING_TYPE)){
			return $type;
		}
		$v = $this->getVariable();
		return $v->getFillingTypeAttribute();
	}
	public function getKurtosis(): ?float{
		return $this->attributes[UserVariable::FIELD_KURTOSIS] ?? null;
	}
	public function getMaximumAllowedValueAttribute(): ?float{
		$fromUserVariable = $this->getRawAttribute(self::FIELD_MAXIMUM_ALLOWED_VALUE);
		$commonUnit = $this->getCommonUnit();
		$unitMax = $commonUnit->maximumValue;
		if($commonUnit->isRating() && $unitMax !== null){
			return $unitMax;
		}
		$v = $this->getVariable();
		$fromVariable = $v->getMaximumAllowedValueAttribute();
		if($fromVariable === null){
			return $fromUserVariable;
		}
		if($fromUserVariable === null){
			return $fromVariable;
		}
		return min($fromUserVariable, $fromVariable);
	}
	public function getMean(): ?float{
		return $this->attributes[UserVariable::FIELD_MEAN] ?? null;
	}
	public function getMinimumAllowedValueAttribute(): ?float{
		$fromUserVariable = $this->getRawAttribute(self::FIELD_MINIMUM_ALLOWED_VALUE);
		$commonUnit = $this->getCommonUnit();
		$fromUnit = $commonUnit->minimumValue;
		if($commonUnit->isRating() && $fromUnit !== null){
			return $fromUnit;
		}
		$v = $this->getVariable();
		$fromVariable = $v->getMinimumAllowedValueAttribute();
		if($fromVariable === null){
			return $fromUserVariable;
		}
		if($fromUserVariable === null){
			return $fromVariable;
		}
		return max($fromUserVariable, $fromVariable);
	}
	public function getOnsetDelay(): int{
		return $this->attributes[self::FIELD_ONSET_DELAY] ?? $this->getVariable()->getDurationOfActionAttribute();
	}
	public function getStandardDeviationAttribute(): ?float{
		return $this->attributes[UserVariable::FIELD_STANDARD_DEVIATION] ?? null;
	}
	public function getVariance(): ?float{
		return $this->attributes[UserVariable::FIELD_VARIANCE] ?? null;
	}
	/**
	 * @return UserVariable
	 */
	public function getStrongestEffect(): UserVariable{
		$c = $this->getBestCorrelationAsCause();
		return $c->getEffectUserVariable();
	}
	/**
	 * @return UserVariable
	 */
	public function getStrongestPredictor(): UserVariable{
		$c = $this->getBestCorrelationAsEffect();
		return $c->getCauseUserVariable();
	}
	public function getTags(): array{
		return $this->getVariable()->getTags();
	}
	public function getTestChartsUrl(): string{
		return $this->getUrl();
	}
	public function getThirdToLastValue(): ?float{
		return $this->attributes[UserVariable::FIELD_THIRD_TO_LAST_VALUE] ?? null;
	}
	public function getTopMenu(): QMMenu{
		return JournalMenu::instance();
	}
	public function getLatestTaggedMeasurementStartAt(): ?string{
		return $this->attributes[UserVariable::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT] ?? null;
	}
	public function getTrendMetric(): Trend{
		return $this->trendMetric();
	}
	public function trendMetric(): Trend{
		$m = MeasurementValueProperty::trendMetric($this->getId());
		return $m;
	}
	public function getUserErrorMessage(): ?string{
		return $this->attributes[UserVariable::FIELD_USER_ERROR_MESSAGE] ?? null;
	}
	public function getUserMaximumAllowedDailyValueAttribute(): ?float{
		return $this->attributes[UserVariable::FIELD_USER_MAXIMUM_ALLOWED_DAILY_VALUE] ?? null;
	}
	public function getUserMinimumAllowedDailyValueAttribute(): ?float{
		return $this->attributes[UserVariable::FIELD_USER_MINIMUM_ALLOWED_DAILY_VALUE] ?? null;
	}
	public function getUserMinimumAllowedNonZeroValue(): ?float{
		return $this->attributes[UserVariable::FIELD_USER_MINIMUM_ALLOWED_NON_ZERO_VALUE] ?? null;
	}
	/**
	 * @return QMUnit
	 */
	public function getUserUnit(): QMUnit{
		return QMUnit::find($this->getAttribute(UserVariable::FIELD_DEFAULT_UNIT_ID));
	}
	public function getUserVariableIdsToCorrelateWith(): array{
		$dbm = $this->getQMUserVariable();
		$ids = $dbm->getUserVariableIdsToCorrelateWith();
        return $ids;
	}
	public function getValence(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[UserVariable::FIELD_VALENCE] ?? BaseValenceProperty::generate($this);
		} else{
			if(empty($this->valence)){
				$this->valence = BaseValenceProperty::generate($this);
			}
			/** @var QMUserVariable $this */
			return $this->valence;
		}
	}
	/**
	 * @return mixed|null
	 */
	public function getValenceAttribute(){
		return $this->getAttributeOrVariableFallback(self::FIELD_VALENCE);
	}
	public function getValidValues(): array{
		return $this->getQMUserVariable()->getValidValues();
	}
	/**
	 * @param $timeAt
	 * @return float|null
	 */
	public function getValue($timeAt): ?float{
		$m = $this->getMeasurement($timeAt);
		if(!$m){
			return null;
		}
		return $m->value;
	}
	/**
	 * @param $timeAt
	 * @return Measurement|null
	 */
	private function getMeasurement($timeAt): ?Measurement{
		$measurements = $this->getMeasurements();
		$at = db_date($timeAt);
		return $measurements[$at] ?? null;
	}
	/**
	 * @return Measurement[]
	 */
	public function getMeasurements(): array{
		$dbm = $this->getQMUserVariable();
		return $dbm->getMeasurements();
	}
	public function getValues(): array{
		return $this->getQMUserVariable()->getValues();
	}
	/**
	 * @return mixed|null
	 */
	public function getVariableCategoryIdAttribute(){
		return $this->getAttributeOrVariableFallback(self::FIELD_VARIABLE_CATEGORY_ID);
	}
	/**
	 * @return VariableStatisticsCard
	 */
	public function getVariableStatisticsCard(): VariableStatisticsCard{
		$card = new VariableStatisticsCard($this);
		return $card;
	}
	public function getWikipediaTitle(): ?string{
		return $this->attributes[UserVariable::FIELD_WIKIPEDIA_TITLE] ?? null;
	}
	public function getWpPostId(): ?int{
		return $this->attributes[UserVariable::FIELD_WP_POST_ID] ?? null;
	}
	/**
	 * @param string|null $reason
	 * @return int
	 */
	public function hardDeleteSoftDeletedMeasurements(string $reason = null): int{
		return QMMeasurement::writable()->where(Measurement::FIELD_USER_ID, $this->getUserId())
			->where(Measurement::FIELD_VARIABLE_ID, $this->getVariableIdAttribute())->whereNotNull(Measurement::FIELD_DELETED_AT)
			->hardDelete($reason, true);
	}
	public function isTestVariable(): bool{
		return $this->getVariable()->isTestVariable();
	}
	public function measurementsWithTagsAreSet(): bool{
		return $this->getQMUserVariable()->measurementsWithTagsAreSet();
	}
	/**
	 * @param string $key
	 * @return string|int|float
	 */
	public function mostCommonFromMeasurementsWithTags(string $key){
		$names = $this->pluckFromMeasurementsWithTags($key);
		$mostCommon = ($names) ? Stats::mostCommonValue($names) : null;
		return $mostCommon;
	}
	public function pluckFromMeasurementsWithTags(string $key): array{
		$camel = QMStr::camelize($key);
		$measurements = $this->getMeasurementsWithTags();
		return QMArr::pluckColumn($measurements, $camel);
	}
	public function most_common_connector(): BelongsTo{
		return $this->belongsTo(Connector::class, UserVariable::FIELD_MOST_COMMON_CONNECTOR_ID, Connector::FIELD_ID,
			UserVariable::FIELD_MOST_COMMON_CONNECTOR_ID);
	}
	public function needToCorrelate(): bool{
		return $this->weShouldCalculateCorrelations();
	}
	/**
	 * @param $timeAt
	 * @param $value
	 * @param QMUnit|int|string $unit
	 * @param array $data
	 * @return Measurement
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 * @throws NoChangesException
	 */
	public function saveMeasurementByValueTime($timeAt, $value, $unit, array $data = []): Measurement{
		$m = $this->newMeasurementByValueTime($timeAt, $value, $unit, $data);
		$m->save();
		return $m;
	}
	/**
	 * @param int|string $timeAt
	 * @param float|string $value
	 * @param QMUnit|Unit|int|string $unit
	 * @param array $additional
	 * @return Measurement
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function newMeasurementByValueTime($timeAt, $value, $unit = null, array $additional = []): Measurement{
		if(!$unit){
			$unit = $this->getUnit();
		}
		if($value === null){
			le('$value === null');
		}
		if($timeAt === null){
			le('$timeAt === null');
		}
		if($unit === null){
			le('$unit === null');
		}
		$data = $this->newMeasurementDataByValueTime($timeAt, $value, $unit, $additional);
		$m = new Measurement($data);
		$m->forceFill($data);
		return $m;
	}
	/**
	 * @param int|string $originalTimeAt
	 * @param float|null $value
	 * @param QMUnit|int|string $unit
	 * @param array|object $additional
	 * @return array
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function newMeasurementDataByValueTime($originalTimeAt, ?float $value, $unit, $additional = []): array{
		$v = $this->getVariable();
		$roundedAt = $this->getRoundedStartAt($originalTimeAt);
		$roundedTime = strtotime($roundedAt);
		$originalAt = db_date($originalTimeAt);
		if($value === null){
			throw new BadRequestException("Please provide measurement value!");
		}
		if(AppMode::isApiRequest()){
			$clientId = BaseClientIdProperty::fromRequest(true);
		} else{
            if($additional){
                $clientId = MeasurementClientIdProperty::pluckOrDefault($additional) ?? BaseClientIdProperty::fromMemory();
            } else{
                $clientId = BaseClientIdProperty::fromMemory();
            }
		}
		$data = [
			Measurement::CREATED_AT => now_at(),
			Measurement::FIELD_CLIENT_ID => $clientId,
			Measurement::FIELD_ORIGINAL_UNIT_ID => QMUnit::find($unit)->id,
			Measurement::FIELD_ORIGINAL_VALUE => $value,
			Measurement::FIELD_START_TIME => $roundedTime,
			Measurement::FIELD_START_AT => $roundedAt,
			Measurement::FIELD_ORIGINAL_START_AT => $originalAt,
			Measurement::FIELD_UNIT_ID => $v->getUnitIdAttribute(),
			Measurement::FIELD_USER_ID => $this->user_id,
			Measurement::FIELD_USER_VARIABLE_ID => $this->id,
			Measurement::FIELD_VALUE => $v->convertToCommonUnit($value, $unit),
			Measurement::FIELD_VARIABLE_CATEGORY_ID => $this->getVariableCategoryId(),
			Measurement::FIELD_VARIABLE_ID => $v->id,
		];
        if($additional){
            $data = Measurement::populateOptionalMeasurementData($data, $additional);
        }
		$notNull = QMArr::removeNulls($data);
		if(!isset($notNull[Measurement::FIELD_VARIABLE_CATEGORY_ID])){
			le('!isset($notNull[Measurement::FIELD_VARIABLE_CATEGORY_ID])');
		}
		return $notNull;
	}
	/**
	 * @param int|string $timeAt
	 * @return string
	 */
	public function getRoundedStartAt($timeAt): string{
		$time = $this->roundStartTime($timeAt);
		return db_date($time);
	}
	/**
	 * @param Measurement[] $measurements
	 * @return Measurement[]
	 * @throws NoChangesException
	 */
	public function saveMeasurements(array $measurements): array{
		foreach($measurements as $m){
            $m->user_id = $this->user_id;
			$m->save();
		}
		$this->updateFromMeasurements($measurements);
		return $measurements;
	}
	/**
	 * @param Measurement[] $measurements
	 * Keep this to avoid creating duplicate function
	 */
	public function updateFromMeasurements(array $measurements){
		QMLog::logStartOfProcess(__METHOD__);
		$this->logInfo("Updating $this from measurements...");
		$qmUV = $this->getQMUserVariable();
		$byDate = MeasurementStartAtProperty::indexAscending($measurements);
		$valuesChronological = QMArr::pluckColumn($byDate, 'value');
		$unique = array_values(array_unique($valuesChronological));
		$max = max($valuesChronological);
		$min = min($valuesChronological);
		$number = count($measurements);
		/** @var Measurement $last */
		$last = end($byDate);
		$first = reset($byDate);
		$latestAt = MeasurementStartAtProperty::pluck($last);
		if(empty($latestAt)){
			le('empty($latestAt)');
		}
		$earliestAt = MeasurementStartAtProperty::pluck($first);
		if(empty($earliestAt)){
			le('empty($earliestAt)');
		}
		$this->setIfGreaterThanExisting(self::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT,
			$latestAt);  // Pluck goes through rounding process again
		$this->setIfLessThanExisting(self::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT,
			$earliestAt);  // Pluck goes through rounding process again
		//$this->setIfGreaterThanExisting(self::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT, $latestAt);  // Pluck goes through rounding process again
		//$this->setIfLessThanExisting(self::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT, $earliestAt);
		$this->updateLastValues($valuesChronological);
		$this->setAttributeIfNull(self::FIELD_MOST_COMMON_CONNECTOR_ID,
			MeasurementConnectorIdProperty::pluckOrDefault($last));
		if($dataSource = MeasurementSourceNameProperty::pluckOrDefault($last)){
			$this->addDataSource($dataSource);
		}
		$this->last_unit_id = $this->last_original_unit_id = MeasurementOriginalUnitIdProperty::pluckOrDefault($last);
		$this->last_original_value = MeasurementOriginalValueProperty::pluckOrDefault($last);
		$previousNumber = $this->number_of_measurements;
		$daily = DailyMeasurement::aggregateDaily($measurements, $qmUV);
		$dailyValues = QMArr::pluckColumn($daily, 'value');
		$uniqueDaily = array_unique($dailyValues);
		$dailyChanges = Stats::countChanges($dailyValues);
		if($previousNumber && $number < $previousNumber){ // Incremental analysis
			$this->setIfGreaterThanExisting(self::FIELD_MAXIMUM_RECORDED_VALUE, $max);
			$this->setIfLessThanExisting(self::FIELD_MINIMUM_RECORDED_VALUE, $min);
			$this->setIfGreaterThanExisting(self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN, $number);
			$this->setIfGreaterThanExisting(self::FIELD_NUMBER_OF_MEASUREMENTS, $number);
			$this->setIfGreaterThanExisting(self::FIELD_NUMBER_OF_UNIQUE_VALUES, count($unique));
			$this->setIfGreaterThanExisting(self::FIELD_NUMBER_OF_UNIQUE_DAILY_VALUES, count($uniqueDaily));
			$this->setIfGreaterThanExisting(self::FIELD_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS, count($daily));
		} else{ // Full analysis
			$this->setAttribute(self::FIELD_MAXIMUM_RECORDED_VALUE, $max);
			$this->setAttribute(self::FIELD_MINIMUM_RECORDED_VALUE, $min);
			$this->setAttribute(self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN, $number);
			$this->setAttribute(self::FIELD_NUMBER_OF_MEASUREMENTS, $number);
			$this->setAttribute(self::FIELD_NUMBER_OF_UNIQUE_VALUES, count($unique));
			$this->setAttribute(self::FIELD_NUMBER_OF_UNIQUE_DAILY_VALUES, count($uniqueDaily));
			$this->setAttribute(self::FIELD_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS, count($daily));
		}
		$this->setIfGreaterThanExisting(self::FIELD_NUMBER_OF_CHANGES, $dailyChanges);
		$this->reason_for_analysis = "New measurements";
		$this->analysis_requested_at = now_at();
		$this->newest_data_at = now_at();
		$this->status = UserVariableStatusProperty::STATUS_WAITING;
		try {
			$this->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
		$variable = $this->getVariable();
		try {
			$variable->updateFromMeasurements($measurements);
		} catch (ModelValidationException $e) {
			le($e);
		}
		UserVariableClient::updateByMeasurements($measurements);
		UserClient::updateFromMeasurements($this->user_id, $last->client_id, $earliestAt, $latestAt,
			$this->number_of_measurements);
		QMLog::logEndOfProcess(__METHOD__);
	}
	/**
	 * @param array $newValuesInChronologicalOrder
	 */
	private function updateLastValues(array $newValuesInChronologicalOrder): void{
		$reversed = array_reverse($newValuesInChronologicalOrder);
		$previousLastValues = $this->getLastValuesInCommonUnit();
		$merged = array_merge($reversed, $previousLastValues);
		$unique = array_values(array_unique($merged));
		$this->attributes[self::FIELD_LAST_VALUE] = $unique[0];
		$this->attributes[self::FIELD_SECOND_TO_LAST_VALUE] = $unique[1] ?? null;
		$this->attributes[self::FIELD_THIRD_TO_LAST_VALUE] = $unique[2] ?? null;
	}
	/**
	 * @return array
	 */
	public function getLastValuesInCommonUnit(): array{
		$arr = [];
		$val = $this->last_value;
		if($val !== null){
			$arr[] = $val;
		}
		$val = $this->second_to_last_value;
		if($val !== null){
			$arr[] = $val;
		}
		$val = $this->third_to_last_value;
		if($val !== null){
			$arr[] = $val;
		}
		return $arr;
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
	 * @param mixed $provided
	 * @return Measurement
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 * @throws NoChangesException
	 */
	public function saveNewMeasurement($provided): Measurement{
		$m = $this->newMeasurement($provided);
		$m->logInfo("Saving $m->value " . $m->getUnitAbbreviatedName() . " " . $m->getVariableName() .
			" for user $m->user_id");
		$m->save();
		return $m;
	}
	/**
	 * @param mixed $provided
	 * @return Measurement
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function newMeasurement($provided): Measurement{
		$data = $this->newMeasurementData($provided);
		$m = new Measurement();
		$m->forceFill($data);
		$this->newest_data_at = now_at();
		$this->status = UserVariableStatusProperty::STATUS_WAITING;
		return $m;
	}
	/**
	 * @param mixed $provided
	 * @return array
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function newMeasurementData($provided): array{
		$originalUnitId = MeasurementOriginalUnitIdProperty::pluckOrDefault($provided);
		if(!$originalUnitId){
			$originalUnitId = MeasurementUnitIdProperty::pluckOrDefault($provided);
		}
		if(!$originalUnitId){
			$originalUnitId = $this->default_unit_id;
		}
		$originalValue = MeasurementOriginalValueProperty::pluckOrDefault($provided);
		if(!$originalUnitId){
			le("Please provide originalUnitId to " . __METHOD__, $provided);
		}
		if(is_array($provided)){
			$provided[self::FIELD_VARIABLE_ID] = $this->variable_id;
		}
		$originalAt = MeasurementOriginalStartAtProperty::pluckOrDefault($provided);
		if(!$originalAt){
			$originalAt = MeasurementStartAtProperty::pluckOrDefault($provided);
		}
		$notNull = $this->newMeasurementDataByValueTime($originalAt, $originalValue, $originalUnitId, $provided);
		//$notNull['user_variable'] = $this;
		return $notNull;
	}
	public function setAlias(string $alias): void{
		$this->setAttribute(UserVariable::FIELD_ALIAS, $alias);
	}
	public function getNumberOfMeasurements(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_MEASUREMENTS] ?? null;
	}
	public function setAnalysisRequestedAt(string $analysisRequestedAt): void{
		$this->setAttribute(UserVariable::FIELD_ANALYSIS_REQUESTED_AT, $analysisRequestedAt);
	}
	public function setAnalysisStartedAt(string $analysisStartedAt): void{
		$this->setAttribute(UserVariable::FIELD_ANALYSIS_STARTED_AT, $analysisStartedAt);
		$this->getAttribute(UserVariable::FIELD_ANALYSIS_STARTED_AT);
	}

	public function setBestCauseVariableId(int $bestCauseVariableId): void{
		$this->setAttribute(UserVariable::FIELD_BEST_CAUSE_VARIABLE_ID, $bestCauseVariableId);
	}
	/**
	 * @return GlobalVariableRelationship|UserVariableRelationship
     */
	public function setBestCorrelation(){
		$best = $this->setBestUserVariableRelationship();
		if($best){
			return $best;
		}
		$best = $this->setBestGlobalVariableRelationship();
		return $best;
	}
	public function setBestEffectVariableId(int $bestEffectVariableId): void{
		$this->setAttribute(UserVariable::FIELD_BEST_EFFECT_VARIABLE_ID, $bestEffectVariableId);
	}
	public function setBestUserVariableRelationshipId(int $bestUserVariableRelationshipId): void{
		$this->setAttribute(UserVariable::FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID, $bestUserVariableRelationshipId);
	}
	public function setCauseOnly(bool $causeOnly): void{
		$this->setAttribute(UserVariable::FIELD_CAUSE_ONLY, $causeOnly);
	}
	public function setClientId(string $clientId): void{
		$this->setAttribute(UserVariable::FIELD_CLIENT_ID, $clientId);
	}
	public function setClientIdAttribute(string $clientId){
		if($clientId === BaseClientIdProperty::CLIENT_ID_QUANTIMODO){
			$this->logError("Why are we setting client id to $clientId?");
			return;
		}
		$this->attributes[self::FIELD_CLIENT_ID] = $clientId;
	}
	public function setCombinationOperation(string $combinationOperation): void{
		$this->setAttribute(UserVariable::FIELD_COMBINATION_OPERATION, $combinationOperation);
	}
	/**
	 * @param $value
	 */
	public function setCombinationOperationAttribute($value){
		$this->setAttributeIfNotEqualToCommonVariable(self::FIELD_COMBINATION_OPERATION, $value);
	}
	/**
	 * @param string $key
	 * @param $value
	 */
	public function setAttributeIfNotEqualToCommonVariable(string $key, $value){
		if($value !== null && isset($this->attributes[self::FIELD_VARIABLE_ID])){
			$variable = $this->getVariable();
			$common = $variable->getAttribute($key);
			if($common === $value){
				$value = null;
			}
		}
		$this->attributes[self::FIELD_COMBINATION_OPERATION] = $value;
	}
	public function setDataSourcesCount(string $dataSourcesCount): void{
		$this->setAttribute(UserVariable::FIELD_DATA_SOURCES_COUNT, $dataSourcesCount);
	}
	public function setDefaultUnitId(int $defaultUnitId): void{
		$this->setAttribute(UserVariable::FIELD_DEFAULT_UNIT_ID, $defaultUnitId);
	}
	public function setDeletedAt(string $deletedAt): void{
		$this->setAttribute(UserVariable::FIELD_DELETED_AT, $deletedAt);
	}
	public function setDeletionReason(string $deletionReason): void{
		$this->setAttribute(UserVariable::FIELD_DELETION_REASON, $deletionReason);
	}
	public function setDescription(string $description): void{
		$this->setAttribute(UserVariable::FIELD_DESCRIPTION, $description);
	}
	public function setEarliestFillingTime(int $earliestFillingTime): void{
		$this->setAttribute(UserVariable::FIELD_EARLIEST_FILLING_TIME, $earliestFillingTime);
	}
	public function getNumberOfUserVariableRelationshipsAsCauseAttribute(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_CAUSE] ?? null;
	}
	/**
	 * @param $value
	 */
	public function setEarliestFillingTimeAttribute($value){
		$this->attributes[self::FIELD_EARLIEST_FILLING_TIME] = time_or_null($value);
	}

	public function getNumberOfUserVariableRelationshipsAsEffectAttribute(): ?int{
		return $this->attributes[UserVariable::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_EFFECT] ?? null;
	}
	/**
	 * @param $value
	 */
	public function setEarliestNonTaggedMeasurementStartAtAttribute($value){
		$this->processAndSetAttribute(self::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT, $value);
		$this->setIfLessThanExisting(self::FIELD_EARLIEST_SOURCE_MEASUREMENT_START_AT, $value);
		$this->setIfLessThanExisting(self::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT, $value);
		$this->setIfLessThanExisting(self::FIELD_EARLIEST_FILLING_TIME, $value);
	}

	/**
	 * @param $value
	 */
	public function setEarliestSourceMeasurementStartAtAttribute($value){
		$nonTagged = $this->attributes[self::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT] ?? null;
		if($nonTagged && $nonTagged < $value){
			$measurements = $this->getQMMeasurementsIndexedByStartAt();
			$this->errorOrLogicExceptionIfTesting("EARLIEST_NON_TAGGED_MEASUREMENT_START_AT property ($nonTagged)\n" .
				"is less than value provided to setEarliestSourceMeasurementStartAtAttribute ($value)\n" .
				"Earliest raw measurement is " .
				QMMeasurement::getFirst($measurements)->getStartAt()//."Setting to source to measurement time.. "
			);
			//$value = $nonTagged;
		}
		$this->processAndSetAttribute(self::FIELD_EARLIEST_SOURCE_MEASUREMENT_START_AT, $value);
	}

	/**
	 * @param $value
	 */
	public function setEarliestTaggedMeasurementStartAtAttribute($value){
		$this->processAndSetAttribute(self::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT, $value);
		$this->setIfLessThanExisting(self::FIELD_EARLIEST_SOURCE_MEASUREMENT_START_AT, $value);
	}
	public function setExperimentEndTime(string $experimentEndTime): void{
		$this->setAttribute(UserVariable::FIELD_EXPERIMENT_END_TIME, $experimentEndTime);
	}
	public function setExperimentStartTime(string $experimentStartTime): void{
		$this->setAttribute(UserVariable::FIELD_EXPERIMENT_START_TIME, $experimentStartTime);
	}
	public function setFillingTypeAttribute(string $type): void{
		$this->setAttributeIfNotSameAsVariable(self::FIELD_FILLING_TYPE, $type);
	}
	public function setId(int $id): void{
		$this->setAttribute(UserVariable::FIELD_ID, $id);
	}
	public function setInformationalUrl(string $informationalUrl): void{
		$this->setAttribute(UserVariable::FIELD_INFORMATIONAL_URL, $informationalUrl);
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
	public function setJoinWith(int $joinWith): void{
		$this->setAttribute(UserVariable::FIELD_JOIN_WITH, $joinWith);
	}
	public function setKurtosis(float $kurtosis): void{
		$this->setAttribute(UserVariable::FIELD_KURTOSIS, $kurtosis);
	}
	public function setLastCorrelatedAt(string $lastCorrelatedAt): void{
		$this->setAttribute(UserVariable::FIELD_LAST_CORRELATED_AT, $lastCorrelatedAt);
	}
	public function setLastOriginalUnitId(int $lastOriginalUnitId): void{
		$this->setAttribute(UserVariable::FIELD_LAST_ORIGINAL_UNIT_ID, $lastOriginalUnitId);
	}
	public function setLastOriginalValue(float $lastOriginalValue): void{
		$this->setAttribute(UserVariable::FIELD_LAST_ORIGINAL_VALUE, $lastOriginalValue);
	}
	public function setLastProcessedDailyValue(float $lastProcessedDailyValue): void{
		$this->setAttribute(UserVariable::FIELD_LAST_PROCESSED_DAILY_VALUE, $lastProcessedDailyValue);
	}
	public function setLastUnitId(int $lastUnitId): void{
		$this->setAttribute(UserVariable::FIELD_LAST_UNIT_ID, $lastUnitId);
	}
	public function setLastValue(float $lastValue): void{
		$this->setAttribute(UserVariable::FIELD_LAST_VALUE, $lastValue);
	}
	public function setLatestFillingTime(int $latestFillingTime): void{
		$this->setAttribute(UserVariable::FIELD_LATEST_FILLING_TIME, $latestFillingTime);
	}
	/**
	 * @param $value
	 */
	public function setLatestFillingTimeAttribute($value){
		$this->attributes[self::FIELD_LATEST_FILLING_TIME] = time_or_null($value);
	}

	/**
	 * @param $value
	 */
	public function setLatestNonTaggedMeasurementStartAtAttribute($value){
		$this->processAndSetAttribute(self::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT, $value);
		$this->setIfGreaterThanExisting(self::FIELD_LATEST_SOURCE_MEASUREMENT_START_AT, $value);
		$this->setIfGreaterThanExisting(self::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT, $value);
		$this->setIfGreaterThanExisting(self::FIELD_LATEST_FILLING_TIME, $value);
	}
	public function setLatestSourceMeasurementStartAt(string $latestSourceMeasurementStartAt): void{
		$this->setAttribute(UserVariable::FIELD_LATEST_SOURCE_MEASUREMENT_START_AT, $latestSourceMeasurementStartAt);
	}
	/**
	 * @param $value
	 */
	public function setLatestSourceMeasurementStartAtAttribute($value){
		$nonTagged = $this->attributes[self::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT] ?? null;
		if($nonTagged && $nonTagged > $value){
			$this->logError("Latest measurement ($nonTagged) is greater than latest source ($value)!
            Setting to source to measurement time.. ");
			$value = $nonTagged;
		}
		$this->processAndSetAttribute(self::FIELD_LATEST_SOURCE_MEASUREMENT_START_AT, $value);
	}
	public function setLatestTaggedMeasurementStartAt(string $latestTaggedMeasurementStartAt): void{
		$this->setAttribute(UserVariable::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT, $latestTaggedMeasurementStartAt);
	}
	/**
	 * @param $value
	 */
	public function setLatestTaggedMeasurementStartAtAttribute($value){
		$this->processAndSetAttribute(self::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT, $value);
		$this->setIfGreaterThanExisting(self::FIELD_LATEST_SOURCE_MEASUREMENT_START_AT, $value);
	}
	public function setLatitude(float $latitude): void{
		$this->setAttribute(UserVariable::FIELD_LATITUDE, $latitude);
	}
	public function setLocation(string $location): void{
		$this->setAttribute(UserVariable::FIELD_LOCATION, $location);
	}
	public function setLongitude(float $longitude): void{
		$this->setAttribute(UserVariable::FIELD_LONGITUDE, $longitude);
	}
	public function setMaximumAllowedValue(float $maximumAllowedValue): void{
		$this->setAttribute(UserVariable::FIELD_MAXIMUM_ALLOWED_VALUE, $maximumAllowedValue);
	}
	public function setMaximumRecordedValue(float $maximumRecordedValue): void{
		$this->setAttribute(UserVariable::FIELD_MAXIMUM_RECORDED_VALUE, $maximumRecordedValue);
	}
	public function setMean(float $mean): void{
		$this->setAttribute(UserVariable::FIELD_MEAN, $mean);
	}
	public function setMeasurementsAtLastAnalysis(int $measurementsAtLastAnalysis): void{
		$this->setAttribute(UserVariable::FIELD_MEASUREMENTS_AT_LAST_ANALYSIS, $measurementsAtLastAnalysis);
	}
	public function setMedian(float $median): void{
		$this->setAttribute(UserVariable::FIELD_MEDIAN, $median);
	}
	public function setMedianSecondsBetweenMeasurements(int $medianSecondsBetweenMeasurements): void{
		$this->setAttribute(UserVariable::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS, $medianSecondsBetweenMeasurements);
	}

	public function setMinimumAllowedValue(float $minimumAllowedValue): void{
		$this->setAttribute(UserVariable::FIELD_MINIMUM_ALLOWED_VALUE, $minimumAllowedValue);
	}
	public function setMinimumRecordedValue(float $minimumRecordedValue): void{
		$this->setAttribute(UserVariable::FIELD_MINIMUM_RECORDED_VALUE, $minimumRecordedValue);
	}
	/**
	 * @param $value
	 * @noinspection PhpUnused
	 */
	public function setMinimumRecordedValueAttribute($value){
		$this->attributes[static::FIELD_MINIMUM_RECORDED_VALUE] = $value;
	}
	public function setMostCommonOriginalUnitId(int $mostCommonOriginalUnitId): void{
		$this->setAttribute(UserVariable::FIELD_MOST_COMMON_ORIGINAL_UNIT_ID, $mostCommonOriginalUnitId);
	}
	public function setMostCommonSourceName(string $mostCommonSourceName): void{
		$this->setAttribute(UserVariable::FIELD_MOST_COMMON_SOURCE_NAME, $mostCommonSourceName);
	}
	public function setMostCommonValue(float $mostCommonValue): void{
		$this->setAttribute(UserVariable::FIELD_MOST_COMMON_VALUE, $mostCommonValue);
	}
	public function setNewestDataAt(string $newestDataAt): void{
		$this->setAttribute(UserVariable::FIELD_NEWEST_DATA_AT, $newestDataAt);
	}
	public function setNumberOfChanges(int $numberOfChanges): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_CHANGES, $numberOfChanges);
	}
	public function setNumberOfCommonChildren(int $numberOfCommonChildren): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_COMMON_CHILDREN, $numberOfCommonChildren);
	}
	public function setNumberOfCommonFoods(int $numberOfCommonFoods): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_COMMON_FOODS, $numberOfCommonFoods);
	}
	public function getLatestTaggedMeasurementAt(): ?string{
		return $this->getUserVariable()->latest_tagged_measurement_start_at;
	}
	public function setNumberOfCommonIngredients(int $numberOfCommonIngredients): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_COMMON_INGREDIENTS, $numberOfCommonIngredients);
	}
	public function setNumberOfCommonJoinedVariables(int $numberOfCommonJoinedVariables): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_COMMON_JOINED_VARIABLES, $numberOfCommonJoinedVariables);
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return \App\Models\GlobalVariableRelationship[]|UserVariableRelationship[]|\Illuminate\Support\Collection
	 */
	public function getOutcomesOrPredictors(int $limit = null, string $variableCategoryName = null): Collection{
		if($this->isOutcome()){
			$correlations = $this->getUserOrGlobalVariableRelationshipsAsEffect($limit, $variableCategoryName);
		} else{
			$correlations = $this->getUserOrGlobalVariableRelationshipsAsCause($limit, $variableCategoryName);
		}
		return $correlations;
	}
	public function setNumberOfCommonParents(int $numberOfCommonParents): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_COMMON_PARENTS, $numberOfCommonParents);
	}
	/**
	 * @param int|null $limit
	 * @param string|int|null $causeCategory
	 * @return GlobalVariableRelationship[]|UserVariableRelationship[]|Collection
	 */
	public function getUserOrGlobalVariableRelationshipsAsEffect(int $limit = null, $causeCategory = null): Collection{
		$catId = (!$causeCategory) ? null : VariableCategoryIdProperty::pluck($causeCategory);
		$correlations = $this->relations[__FUNCTION__]["$catId-$limit"] ?? null;
		if($correlations !== null){return $correlations;}
		$correlations = $this->getCorrelationsAsEffect($limit, $causeCategory);
		if($correlations->count()){return $this->relations[__FUNCTION__]["$catId-$limit"] = $correlations;}
		return $this->relations[__FUNCTION__]["$catId-$limit"] = $this->getVariable()
			->getGlobalVariableRelationshipsAsEffect($limit, $causeCategory);
	}
	/**
	 * @param int|null $limit
	 * @param string|null $causeCategory
	 * @return UserVariableRelationship[]|\Illuminate\Support\Collection
	 */
	public function getCorrelationsAsEffect(int $limit = null, string $causeCategory = null): Collection{
		$catId = (!$causeCategory) ? null : VariableCategoryIdProperty::pluck($causeCategory);
		$correlations = $this->relations[__FUNCTION__]["$catId-$limit"] ?? null;
		if($correlations !== null){return $correlations;}
		$qb = $this->best_correlations_where_effect_user_variable();
		if($catId){$qb->where(UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID, $catId);}
		if($limit){$qb->limit($limit);}
        $qb->setEagerLoads([]);
		$correlations = $qb->get();
		$count = $correlations->count();
        $variableIds = $correlations->pluck(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID);
        $variables = Variable::findInMemoryOrDB($variableIds);
        $correlations->each(function(UserVariableRelationship $correlation) use ($variables){
            $id = $correlation->getCauseVariableId();
            $correlation->setCauseVariable($variables[$id]);
        });
        $userVariableIds = $correlations->pluck(UserVariableRelationship::FIELD_CAUSE_USER_VARIABLE_ID);
        $userVariables = UserVariable::findInMemoryOrDB($userVariableIds);
        $correlations->each(function(UserVariableRelationship $correlation) use ($userVariables){
            $id = $correlation->getCauseUserVariableId();
            $correlation->setCauseUserVariable($userVariables[$id]);
        });
		if($this->number_of_correlations < $count){
			$this->number_of_correlations = $count + $this->number_of_user_variable_relationships_as_cause;
		}
		if(!$this->number_of_user_variable_relationships_as_effect < $count){$this->number_of_user_variable_relationships_as_effect = $count;}
		return $this->relations[__FUNCTION__]["$catId-$limit"] = $correlations;
	}
	/**
	 * @param int|null $limit
	 * @param string|null $effectCategory
	 * @return UserVariableRelationship[]|\Illuminate\Support\Collection
	 */
	public function getCorrelationsAsCause(int $limit = null, string $effectCategory = null): Collection {
		$catId = (!$effectCategory) ? null : VariableCategoryIdProperty::pluck($effectCategory);
		$correlations = $this->relations[__FUNCTION__]["$catId-$limit"] ?? null;
		if($correlations !== null){return $correlations;}
		$qb = $this->best_correlations_where_cause_user_variable();
		if($catId){$qb->where(UserVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID, $catId);}
		if($limit){$qb->limit($limit);}
        $qb->setEagerLoads([]);
        $correlations = $qb->get();
        $count = $correlations->count();
        $variableIds = $correlations->pluck(UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID);
        $variables = Variable::findInMemoryOrDB($variableIds);
        $correlations->each(function(UserVariableRelationship $correlation) use ($variables){
            $id = $correlation->getEffectVariableId();
	        /** @var UserVariableRelationship $correlation */
	        $correlation->setEffectVariable($variables[$id]);
        });
        $userVariableIds = $correlations->pluck(UserVariableRelationship::FIELD_EFFECT_USER_VARIABLE_ID);
        $userVariables = UserVariable::findInMemoryOrDB($userVariableIds);
        $correlations->each(function(UserVariableRelationship $correlation) use ($userVariables){
            $id = $correlation->getEffectUserVariableId();
            $correlation->setEffectUserVariable($userVariables[$id]);
        });
		$count = $correlations->count();
		if($this->number_of_correlations < $count){
			$this->number_of_correlations = $count + $this->number_of_user_variable_relationships_as_effect;
		}
		if(!$this->number_of_user_variable_relationships_as_cause < $count){$this->number_of_user_variable_relationships_as_cause = $count;}
		return $this->relations[__FUNCTION__]["$catId-$limit"] = $correlations;
	}
	public function setNumberOfCorrelations(int $numberOfCorrelations): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_CORRELATIONS, $numberOfCorrelations);
	}
	/**
	 * @param int|null $limit
	 * @param string|int|null $effectCategory
	 * @return GlobalVariableRelationship[]|UserVariableRelationship[]|Collection
	 */
	public function getUserOrGlobalVariableRelationshipsAsCause(int $limit = null, $effectCategory = null): Collection{
		$catId = (!$effectCategory) ? null : VariableCategoryIdProperty::pluck($effectCategory);
		$correlations = $this->relations[__FUNCTION__]["$catId-$limit"] ?? null;
		if($correlations !== null){return $correlations;}
		$correlations = $this->getCorrelationsAsCause($limit, $effectCategory);
		if($correlations->count()){
			return $this->relations[__FUNCTION__]["$catId-$limit"] = $correlations;
		}
		return $this->relations[__FUNCTION__]["$catId-$limit"] = $this->getVariable()
			->getGlobalVariableRelationshipsAsEffect($limit, $effectCategory);
	}

	public function setNumberOfMeasurements(?int $numberOfMeasurements): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_MEASUREMENTS, $numberOfMeasurements);
	}



	/**
	 * @param int $val
	 * @noinspection PhpUnused
	 */
	public function setNumberOfRawMeasurementsWithTagsJoinsChildrenAttribute(int $val){
		if($dbm = $this->getDBModelFromMemory()){
			if($dbm->measurementsWithTagsAreSet()){
				$withTags = $dbm->getMeasurementsWithTags();
				if(count($withTags) !== $val){
					$this->logError("wrong " . __FUNCTION__ . " Got $val but getMeasurementsWithTags returned " .
						count($withTags));
				}
			}
		}
		$this->attributes[self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN] = $val;
	}
	public function setNumberOfSoftDeletedMeasurements(int $numberOfSoftDeletedMeasurements): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_SOFT_DELETED_MEASUREMENTS, $numberOfSoftDeletedMeasurements);
	}

	public function setNumberOfTrackingReminders(int $numberOfTrackingReminders): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_TRACKING_REMINDERS, $numberOfTrackingReminders);
	}
	public function setNumberOfUniqueDailyValues(int $numberOfUniqueDailyValues): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_UNIQUE_DAILY_VALUES, $numberOfUniqueDailyValues);
	}
	public function setNumberOfUniqueValues(int $numberOfUniqueValues): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_UNIQUE_VALUES, $numberOfUniqueValues);
	}
	public function setNumberOfUserChildren(int $numberOfUserChildren): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_USER_CHILDREN, $numberOfUserChildren);
	}
	public function setNumberOfUserVariableRelationshipsAsCause(int $numberOfUserVariableRelationshipsWhereCause): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_CAUSE, $numberOfUserVariableRelationshipsWhereCause);
	}

	public function setNumberOfUserFoods(int $numberOfUserFoods): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_USER_FOODS, $numberOfUserFoods);
	}
	public function setNumberOfUserIngredients(int $numberOfUserIngredients): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_USER_INGREDIENTS, $numberOfUserIngredients);
	}
	public function setNumberOfUserJoinedVariables(int $numberOfUserJoinedVariables): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_USER_JOINED_VARIABLES, $numberOfUserJoinedVariables);
	}
	public function setNumberOfUserParents(int $numberOfUserParents): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_USER_PARENTS, $numberOfUserParents);
	}
	public function getAvatar(): string{
		return $this->getImage();
	}
	public function setNumberOfUserTags(int $numberOfUserTags): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_OF_USER_TAGS, $numberOfUserTags);
	}
	public function getIcon(): string{
		return $this->getAvatar();
	}
	public function setNumberUserTaggedBy(int $numberUserTaggedBy): void{
		$this->setAttribute(UserVariable::FIELD_NUMBER_USER_TAGGED_BY, $numberUserTaggedBy);
	}
	public function setOptimalValueMessage(string $optimalValueMessage): void{
		$this->setAttribute(UserVariable::FIELD_OPTIMAL_VALUE_MESSAGE, $optimalValueMessage);
	}
	/**
	 * @param $value
	 */
	public function setOutcomeAttribute($value){
		$this->attributes[self::FIELD_OUTCOME] = $value;
	}
	public function setOutcomeOfInterest(bool $outcomeOfInterest): void{
		$this->setAttribute(UserVariable::FIELD_OUTCOME_OF_INTEREST, $outcomeOfInterest);
	}
	public function setParentId(int $parentId): void{
		$this->setAttribute(UserVariable::FIELD_PARENT_ID, $parentId);
	}
	public function setPredictorOfInterest(bool $predictorOfInterest): void{
		$this->setAttribute(UserVariable::FIELD_PREDICTOR_OF_INTEREST, $predictorOfInterest);
	}
	public function setReasonForAnalysis(string $reasonForAnalysis): void{
		$this->setAttribute(UserVariable::FIELD_REASON_FOR_ANALYSIS, $reasonForAnalysis);
	}
	public function setRecordSizeInKb(int $recordSizeInKb): void{
		$this->setAttribute(UserVariable::FIELD_RECORD_SIZE_IN_KB, $recordSizeInKb);
	}
	public function setSecondToLastValue(float $secondToLastValue): void{
		$this->setAttribute(UserVariable::FIELD_SECOND_TO_LAST_VALUE, $secondToLastValue);
	}
	/**
	 * @param bool $val
	 * @return bool
	 */
	public function setSharing(bool $val): bool{
		self::flushAllFromMemory();
		$this->setIsPublic($val);
		return $this->updateDbRow([
			UserVariable::FIELD_IS_PUBLIC => $val,
		]);
	}
	public function setSkewness(float $skewness): void{
		$this->setAttribute(UserVariable::FIELD_SKEWNESS, $skewness);
	}
	public function setStandardDeviation(float $standardDeviation): void{
		$this->setAttribute(UserVariable::FIELD_STANDARD_DEVIATION, $standardDeviation);
	}
	public function setStatus(string $status): void{
		$this->setAttribute(UserVariable::FIELD_STATUS, $status);
	}
	public function setThirdToLastValue(float $thirdToLastValue): void{
		$this->setAttribute(UserVariable::FIELD_THIRD_TO_LAST_VALUE, $thirdToLastValue);
	}
	/**
	 * @param $value
	 */
	public function setThirdToLastValueAttribute($value){
		$second = $this->second_to_last_value;
		if($second !== null && $second === $value){
			le('$second !== null && $second === $value');
		}
		$this->attributes[self::FIELD_THIRD_TO_LAST_VALUE] = $value;
	}
	public function setUserId(int $userId): void{
		$this->setAttribute(UserVariable::FIELD_USER_ID, $userId);
	}
	public function setUserMaximumAllowedDailyValue(float $userMaximumAllowedDailyValue): void{
		$this->setAttribute(UserVariable::FIELD_USER_MAXIMUM_ALLOWED_DAILY_VALUE, $userMaximumAllowedDailyValue);
	}
	public function setUserMinimumAllowedDailyValue(float $userMinimumAllowedDailyValue): void{
		$this->setAttribute(UserVariable::FIELD_USER_MINIMUM_ALLOWED_DAILY_VALUE, $userMinimumAllowedDailyValue);
	}
	public function setUserMinimumAllowedNonZeroValue(float $userMinimumAllowedNonZeroValue): void{
		$this->setAttribute(UserVariable::FIELD_USER_MINIMUM_ALLOWED_NON_ZERO_VALUE, $userMinimumAllowedNonZeroValue);
	}
	public function setValence(string $valence): void{
		$this->setAttribute(UserVariable::FIELD_VALENCE, $valence);
	}
	public function setVariableCategoryId(int $variableCategoryId): void{
		$this->setAttribute(UserVariable::FIELD_VARIABLE_CATEGORY_ID, $variableCategoryId);
	}
	public function setVariableId(int $variableId): void{
		$this->setAttribute(UserVariable::FIELD_VARIABLE_ID, $variableId);
	}
	public function setVariance(float $variance): void{
		$this->setAttribute(UserVariable::FIELD_VARIANCE, $variance);
	}
	public function setWikipediaTitle(string $wikipediaTitle): void{
		$this->setAttribute(UserVariable::FIELD_WIKIPEDIA_TITLE, $wikipediaTitle);
	}
	public function setWpPostId(int $wpPostId): void{
		$this->setAttribute(UserVariable::FIELD_WP_POST_ID, $wpPostId);
	}
	/**
	 * Get the value that should be displayed to represent the resource.
	 * @return string
	 */
	public function title(): string{
		$uv = $this->getUserVariable();
		return $uv->getVariableName();
	}
	public function getSortingScore(): float{
		return strtotime($this->getLatestTaggedMeasurementStartAt()) + $this->getNumberOfMeasurements();
	}
	/**
	 * @param float|null|string $inCommonUnit
	 * @return float
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function toUserUnit($inCommonUnit, bool $validate = false): ?float{
		$userUnitId = $this->attributes[self::FIELD_DEFAULT_UNIT_ID];
		$commonUnitId = $this->getCommonUnitId();
		return QMUnit::convertValue($inCommonUnit, $commonUnitId, $userUnitId, $this, null, $validate);
	}
	public function unsetUserUnitIfInvalid(){
		$userUnitId = $this->attributes[self::FIELD_DEFAULT_UNIT_ID] ?? null;
		if($userUnitId){
			$commonUnit = $this->getCommonUnit();
			if(QMUnit::unitIsIncompatible($commonUnit->id, $userUnitId)){
				$userUnit = QMUnit::find($userUnitId);
				$this->logError("Unsetting user unit $userUnit because it's not compatible with common unit $commonUnit. ");
				$this->default_unit_id = null;
				try {
					$this->save();
				} catch (ModelValidationException $e) {
					le($e);
				}
			}
		}
	}
	/**
	 * @param string|int $startTime
	 */
	public function updateEarliestAttributes($startTime): void{
		$fields = static::getColumns();
		foreach($fields as $field){
			if(stripos($field, 'earliest_') !== false){
				$this->setIfLessThanExisting($field, $startTime);
			}
		}
	}
	public function getNumberOfSubtitle(): string{
		if($num = $this->getNumberOfUserVariableRelationships()){
			return "$num Studies";
		}
		return $this->getNumberOfMeasurements() . " Measurements";
	}
	/**
	 * @param string|int $startTime
	 */
	public function updateLatestAttributes($startTime){
		$fields = static::getColumns();
		foreach($fields as $field){
			if(stripos($field, 'latest_') !== false){
				$this->setIfGreaterThanExisting($field, $startTime);
			}
		}
	}
	public function getNumberOfUserVariableRelationships(): int{
		return $this->getNumberOfUserVariableRelationshipsAsCauseAttribute() + $this->getNumberOfUserVariableRelationshipsAsEffectAttribute();
	}
	public function getClientId(): ?string{
		return $this->attributes[UserVariable::FIELD_CLIENT_ID] ?? null;
	}
	public function getCreatedAt(): ?string{
		return $this->attributes[UserVariable::FIELD_CREATED_AT] ?? null;
	}
	public function getNumberOfTrackingReminders(): ?int{
		return $this->number_of_tracking_reminders;
	}
	public function getUpdatedAt(): ?string{
		return $this->attributes[UserVariable::FIELD_UPDATED_AT] ?? null;
	}
	public function predictors(): HasMany{
		return $this->l()->correlations_where_effect_user_variable();
	}
	public function outcomes(): HasMany{
		return $this->l()->correlations_where_cause_user_variable();
	}
	public function getMinimumAllowedDailyValue(): ?float{ return $this->getVariable()->getMinimumAllowedDailyValue(); }
	public function getUserVariable(): UserVariable{ return $this; }
	/**
	 * @return int
	 */
	public function getOrCalculateNumberOfMeasurements(): int{
		$num = $this->getNumberOfMeasurements();
		if($num === null){
			$num = $this->calculateNumberOfMeasurements();
			$this->setNumberOfMeasurements($num);
		}
		return $num;
	}
	/**
	 * @return UserVariableRelationship|null
	 */
	public function getBestCorrelationAsEffect(): ?UserVariableRelationship
    {
		$c = $this->relations[__FUNCTION__] ?? null;
		if($c === false){return null;}
		return $this->setBestCorrelationAsEffect();
	}
	/**
	 * @return UserVariableRelationship|null
	 */
	public function getBestCorrelationAsCause(): ?UserVariableRelationship
    {
		$c = $this->relations[__FUNCTION__] ?? null;
		if($c === false){return null;}
		return $this->setBestCorrelationAsCause();
	}
	/**
	 * @return Vote[]
	 */
	public function getDownVotesAsCause(): array {
		return $this->getUser()
			->getVotes()
			->where('cause_variable_id', $this->getVariableIdAttribute())
			->where('value', 0)
			->all();
	}
	/**
	 * @return Vote[]
	 */
	public function getDownVotesAsEffect(): array{
		return $this->getUser()
			->getVotes()
			->where('effect_variable_id', $this->getVariableIdAttribute())
			->where('value', 0)
			->all();
	}
	/**
	 * @return Vote[]
	 */
	public function getAllVotes(): array{
		return array_merge($this->getDownVotesAsCause(), $this->getDownVotesAsEffect());
	}
	/**
	 * @param QMUserVariable $effect
	 * @return bool
	 */
	public function downVotedAsEffect(QMUserVariable $effect): bool{
		foreach($this->getAllVotes() as $vote){
			if($effect->getVariableIdAttribute() === $vote->getEffectVariableId()){
				$this->logInfo('Not calculating user_variable_relationships with variable B ' . $effect->name .
					' because user down voted it.');
				return true;
			}
		}
		return false;
	}
	/**
	 * @param QMUserVariable $cause
	 * @return bool
	 */
	public function downVotedAsCause(QMUserVariable $cause): bool{
		foreach($this->getAllVotes() as $vote){
			if($cause->getVariableIdAttribute() === $vote->getCauseVariableId()){
				$this->logInfo('Not calculating user_variable_relationships with ' . $cause->name .
					' as cause because user down voted it');
				return true;
			}
		}
		return false;
	}
    /**
     * Exclude an array of elements from the result.
     * @param Builder $query
     * @param Request|\Illuminate\Http\Request $request
     * @return QueryBuilder
     * USAGE: $medicines = \App\Medicine::exclude('description')->get();
     */
    public function scopeApplyRequestParams($query, $request): QueryBuilder{
        $name = VariableNameProperty::fromRequest(false);
        if($name){
            static::applyRelationSearch($query, $name);
        }
        $query = parent::scopeApplyRequestParams($query, $request);
        return $query;
    }
    /**
     * Get all the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return parent::getAttributes();
    }
    public function getChartsAttribute(): ?UserVariableChartGroup {
        $charts = $this->appends['charts'] ?? null;
        return $charts;
    }
    public function setChartsAttribute($value): ?UserVariableChartGroup {
        $this->appends['charts'] = $value;
        return $value;
    }
    public function generateCharts(): ?UserVariableChartGroup {
        $charts = $this->appends['charts'] = new UserVariableChartGroup($this);
        return $charts;
    }
	/**
	 * @param float|int $value
	 * @param int|string|CarbonInterface $startTimeAt
	 * @param Unit|QMUnit|string|int $unit
	 * @return Measurement
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 * @throws NoChangesException
	 */
	public function saveMeasurement(float $value, $startTimeAt = null, $unit = null){
		if(!$startTimeAt){$startTimeAt = Carbon::now();}
		$measurement = $this->newMeasurementByValueTime($startTimeAt, $value, $unit);
		$measurement->save();
		return $measurement;
	}
	public function getIngredientIds(){
		$ingredients = $this->getCommonTagRows();
		$ids = [];
		foreach($ingredients as $ingredient){
			$ids[] = $ingredient->getTagVariableId();
		}
		$userIngredients = $this->user_tags_where_tag_user_variable()->get();
		foreach($userIngredients as $ingredient){
			$ids[] = $ingredient->getTagVariableId();
		}
		return array_unique($ids);
	}
	public function getIngredientContainerIds(){
		$ingredientContainers = $this->getCommonTaggedRows();
		$ids = [];
		foreach($ingredientContainers as $ingredientContainer){
			$ids[] = $ingredientContainer->getTaggedVariableId();
		}
		$userIngredientContainers = $this->user_tags_where_tagged_user_variable()->get();
		foreach($userIngredientContainers as $ingredientContainer){
			$ids[] = $ingredientContainer->getTaggedVariableId();
		}
		return array_unique($ids);
	}
	public function searchEligibleIngredients(string $q){
		$existing = $this->getIngredientIds();
		$existing[] = $this->getVariableId();
		$variables = Variable::searchQB($q)
			->select(['id', 'name'])
			->whereNotIn('id', $existing)
			->get();
		return $variables;
	}
	public function searchEligibleIngredientContainers(string $q){
		$existing = $this->getIngredientContainerIds();
		$existing[] = $this->getVariableId();
		$variables = Variable::searchQB($q)
		                     ->select(['id', 'name'])
		                     ->whereNotIn('id', $existing)
		                     ->get();
		return $variables;
	}
	/**
	 * @return DailyMeasurement[]
	 */
	public function getValidDailyMeasurementsWithTagsAndFilling(): array{
		$dbm = $this->getQMUserVariable();
		$measurements = $dbm->getValidDailyMeasurementsWithTagsAndFilling();
		return $measurements;
	}
	public function getOpenSeaAttribute():array{
		$attributes['trait_type'] = $this->getVariableName();
		$variable = $this->getVariable();
		$commonMaximumAllowedDailyValue = $variable->getCommonMaximumAllowedDailyValue();
		$maxRecorded = $this->getMaximumRecordedValue();
		$max = $commonMaximumAllowedDailyValue ?? $maxRecorded;
		if($max){
			$attributes['max_value'] = $max;
		}
		if($this->getUnit()->id === PercentUnit::ID){
			$attributes['display_type'] = 'boost_percentage';
		} else {
			$attributes['display_type'] = "boost_number";
		}
		$lastProcessedDailyValue = $this->getLastProcessedDailyValue();
		$name = $variable->name;
		if($lastProcessedDailyValue === null){
			try {
				$this->analyze(__FUNCTION__);
			} catch (NotEnoughDataException $e) {
				$this->logError("Not enough data to analyze for open sea");
			} catch (TooSlowToAnalyzeException $e) {
				$this->logError("Too slow to analyze for open sea");
			}
			$lastProcessedDailyValue = $this->getLastProcessedDailyValue();
			if(!$lastProcessedDailyValue){
				$this->logError("Could not get last processed daily value for variable " . $this->getVariableName());
			}
		}
		$attributes['value'] = $lastProcessedDailyValue;
		return $attributes;
	}
	public function generateNftMetadata(): array{
		$metadata = [];
		foreach($this->attributes as $key => $value){
			$metadata[$key] = $value;
		}
		return $metadata;
	}
}
