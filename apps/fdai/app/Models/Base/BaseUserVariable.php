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
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\OAClient;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\Unit;
use App\Models\UserTag;
use App\Models\UserVariable;
use App\Models\UserVariableClient;
use App\Models\UserVariableOutcomeCategory;
use App\Models\UserVariablePredictorCategory;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Models\WpPost;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseUserVariable
 * @property int $id
 * @property int $parent_id
 * @property string $client_id
 * @property int $user_id
 * @property int $variable_id
 * @property int $default_unit_id
 * @property float $minimum_allowed_value
 * @property float $maximum_allowed_value
 * @property float $filling_value
 * @property int $join_with
 * @property int $onset_delay
 * @property int $duration_of_action
 * @property int $variable_category_id
 * @property bool $cause_only
 * @property string $filling_type
 * @property int $number_of_processed_daily_measurements
 * @property int $measurements_at_last_analysis
 * @property int $last_unit_id
 * @property int $last_original_unit_id
 * @property float $last_value
 * @property float $last_original_value
 * @property int $number_of_correlations
 * @property string $status
 * @property float $standard_deviation
 * @property float $variance
 * @property float $minimum_recorded_value
 * @property float $maximum_recorded_value
 * @property float $mean
 * @property float $median
 * @property int $most_common_original_unit_id
 * @property float $most_common_value
 * @property int $number_of_unique_daily_values
 * @property int $number_of_unique_values
 * @property int $number_of_changes
 * @property float $skewness
 * @property float $kurtosis
 * @property float $latitude
 * @property float $longitude
 * @property string $location
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property bool $outcome
 * @property array $data_sources_count
 * @property int $earliest_filling_time
 * @property int $latest_filling_time
 * @property float $last_processed_daily_value
 * @property bool $outcome_of_interest
 * @property bool $predictor_of_interest
 * @property Carbon $experiment_start_time
 * @property Carbon $experiment_end_time
 * @property string $description
 * @property string $alias
 * @property Carbon $deleted_at
 * @property float $second_to_last_value
 * @property float $third_to_last_value
 * @property int $number_of_user_variable_relationships_as_effect
 * @property int $number_of_user_variable_relationships_as_cause
 * @property string $combination_operation
 * @property string $informational_url
 * @property int $most_common_connector_id
 * @property string $valence
 * @property string $wikipedia_title
 * @property int $number_of_tracking_reminders
 * @property int $number_of_raw_measurements_with_tags_joins_children
 * @property string $most_common_source_name
 * @property string $optimal_value_message
 * @property int $best_cause_variable_id
 * @property int $best_effect_variable_id
 * @property float $user_maximum_allowed_daily_value
 * @property float $user_minimum_allowed_daily_value
 * @property float $user_minimum_allowed_non_zero_value
 * @property int $minimum_allowed_seconds_between_measurements
 * @property int $average_seconds_between_measurements
 * @property int $median_seconds_between_measurements
 * @property Carbon $last_correlated_at
 * @property int $number_of_measurements_with_tags_at_last_correlation
 * @property Carbon $analysis_settings_modified_at
 * @property Carbon $newest_data_at
 * @property Carbon $analysis_requested_at
 * @property string $reason_for_analysis
 * @property Carbon $analysis_started_at
 * @property Carbon $analysis_ended_at
 * @property string $user_error_message
 * @property string $internal_error_message
 * @property Carbon $earliest_source_measurement_start_at
 * @property Carbon $latest_source_measurement_start_at
 * @property Carbon $latest_tagged_measurement_start_at
 * @property Carbon $earliest_tagged_measurement_start_at
 * @property Carbon $latest_non_tagged_measurement_start_at
 * @property Carbon $earliest_non_tagged_measurement_start_at
 * @property int $wp_post_id
 * @property int $number_of_soft_deleted_measurements
 * @property int $best_user_variable_relationship_id
 * @property int $number_of_measurements
 * @property int $number_of_tracking_reminder_notifications
 * @property string $deletion_reason
 * @property int $record_size_in_kb
 * @property int $number_of_common_tags
 * @property int $number_common_tagged_by
 * @property int $number_of_common_joined_variables
 * @property int $number_of_common_ingredients
 * @property int $number_of_common_foods
 * @property int $number_of_common_children
 * @property int $number_of_common_parents
 * @property int $number_of_user_tags
 * @property int $number_user_tagged_by
 * @property int $number_of_user_joined_variables
 * @property int $number_of_user_ingredients
 * @property int $number_of_user_foods
 * @property int $number_of_user_children
 * @property int $number_of_user_parents
 * @property bool $is_public
 * @property bool $is_goal
 * @property bool $controllable
 * @property bool $boring
 * @property OAClient $oa_client
 * @property UserVariableRelationship $best_user_variable_relationship
 * @property Unit $default_unit
 * @property Unit $last_unit
 * @property \App\Models\User $user
 * @property VariableCategory $variable_category
 * @property Variable $variable
 * @property WpPost $wp_post
 * @property Collection|UserVariableRelationship[] $correlations_where_cause_user_variable
 * @property Collection|UserVariableRelationship[] $correlations_where_effect_user_variable
 * @property Collection|Measurement[] $measurements
 * @property Collection|TrackingReminderNotification[] $tracking_reminder_notifications
 * @property Collection|TrackingReminder[] $tracking_reminders
 * @property Collection|UserTag[] $user_tags_where_tag_user_variable
 * @property Collection|UserTag[] $user_tags_where_tagged_user_variable
 * @property Collection|UserVariableClient[] $user_variable_clients
 * @property Collection|UserVariableOutcomeCategory[] $user_variable_outcome_categories
 * @property Collection|UserVariablePredictorCategory[] $user_variable_predictor_categories
 * @package App\Models\Base
 * @property-read int|null $correlations_where_cause_user_variable_count
 * @property-read int|null $correlations_where_effect_user_variable_count
 * @property mixed $raw
 * @property-read int|null $measurements_count
 * @property-read int|null $tracking_reminder_notifications_count
 * @property-read int|null $tracking_reminders_count
 * @property-read int|null $user_variable_clients_count
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable newQuery()
 * @method static \Illuminate\Database\Query\Builder|BaseUserVariable onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereAnalysisEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereAnalysisRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereAnalysisSettingsModifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereAnalysisStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereAverageSecondsBetweenMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereBestCauseVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereBestEffectVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereBestUserVariableRelationshipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereCauseOnly($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereCombinationOperation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereDataSourcesCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereDefaultUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereDeletionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereDurationOfAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereEarliestFillingTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereEarliestMeasurementTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable
 *     whereEarliestNonTaggedMeasurementStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereEarliestNonTaggedMeasurementTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereEarliestSourceMeasurementStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereEarliestSourceTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereEarliestTaggedMeasurementStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereEarliestTaggedMeasurementTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereExperimentEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereExperimentStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereFillingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereFillingValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereInformationalUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereInternalErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereJoinWith($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereKurtosis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereLastCorrelatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereLastOriginalUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereLastOriginalValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereLastProcessedDailyValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereLastSuccessfulUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereLastUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereLastValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereLatestFillingTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereLatestMeasurementTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereLatestNonTaggedMeasurementStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereLatestNonTaggedMeasurementTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereLatestSourceMeasurementStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereLatestSourceTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereLatestTaggedMeasurementStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereLatestTaggedMeasurementTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereMaximumAllowedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereMaximumRecordedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereMean($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereMeasurementsAtLastAnalysis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereMedian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereMedianSecondsBetweenMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable
 *     whereMinimumAllowedSecondsBetweenMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereMinimumAllowedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereMinimumRecordedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereMostCommonConnectorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereMostCommonOriginalUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereMostCommonSourceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereMostCommonValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNewestDataAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberCommonTaggedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfChanges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfCommonChildren($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfCommonFoods($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfCommonIngredients($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfCommonJoinedVariables($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfCommonParents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfCommonTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfCorrelations($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable
 *     whereNumberOfMeasurementsWithTagsAtLastCorrelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfOutcomeCaseStudies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfPredictorCaseStudies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable
 *     whereNumberOfProcessedDailyMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable
 *     whereNumberOfRawMeasurementsWithTagsJoinsChildren($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfSoftDeletedMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable
 *     whereNumberOfTrackingReminderNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfTrackingReminders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable
 *     whereNumberOfTrackingRemindersWhereVariable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfUniqueDailyValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfUniqueValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfUserChildren($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfUserVariableRelationshipsAsCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfUserVariableRelationshipsAsEffect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfUserFoods($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfUserIngredients($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfUserJoinedVariables($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfUserParents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberOfUserTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereNumberUserTaggedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereOnsetDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereOptimalValueMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereOutcomeOfInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable wherePredictorOfInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable wherePublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereReasonForAnalysis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereRecordSizeInKb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereSecondToLastValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereShareUserMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereSkewness($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereStandardDeviation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereThirdToLastValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereUserErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereUserMaximumAllowedDailyValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereUserMinimumAllowedDailyValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereUserMinimumAllowedNonZeroValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereValence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereVariableCategoryId($id)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereVariance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereWikipediaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUserVariable whereWpPostId($value)
 * @method static \Illuminate\Database\Query\Builder|BaseUserVariable withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BaseUserVariable withoutTrashed()
 * @mixin \Eloquent
 */
abstract class BaseUserVariable extends BaseModel {
	use SoftDeletes;
	public const FIELD_ALIAS = 'alias';
	public const FIELD_ANALYSIS_ENDED_AT = 'analysis_ended_at';
	public const FIELD_ANALYSIS_REQUESTED_AT = 'analysis_requested_at';
	public const FIELD_ANALYSIS_SETTINGS_MODIFIED_AT = 'analysis_settings_modified_at';
	public const FIELD_ANALYSIS_STARTED_AT = 'analysis_started_at';
	public const FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS = 'average_seconds_between_measurements';
	public const FIELD_BEST_CAUSE_VARIABLE_ID = 'best_cause_variable_id';
	public const FIELD_BEST_EFFECT_VARIABLE_ID = 'best_effect_variable_id';
	public const FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID = 'best_user_variable_relationship_id';
	public const FIELD_BORING = 'boring';
	public const FIELD_CAUSE_ONLY = 'cause_only';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_COMBINATION_OPERATION = 'combination_operation';
	public const FIELD_CONTROLLABLE = 'controllable';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DATA_SOURCES_COUNT = 'data_sources_count';
	public const FIELD_DEFAULT_UNIT_ID = 'default_unit_id';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_DELETION_REASON = 'deletion_reason';
	public const FIELD_DESCRIPTION = 'description';
	public const FIELD_DURATION_OF_ACTION = 'duration_of_action';
	public const FIELD_EARLIEST_FILLING_TIME = 'earliest_filling_time';
	public const FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT = 'earliest_non_tagged_measurement_start_at';
	public const FIELD_EARLIEST_SOURCE_MEASUREMENT_START_AT = 'earliest_source_measurement_start_at';
	public const FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT = 'earliest_tagged_measurement_start_at';
	public const FIELD_EXPERIMENT_END_TIME = 'experiment_end_time';
	public const FIELD_EXPERIMENT_START_TIME = 'experiment_start_time';
	public const FIELD_FILLING_TYPE = 'filling_type';
	public const FIELD_FILLING_VALUE = 'filling_value';
	public const FIELD_ID = 'id';
	public const FIELD_INFORMATIONAL_URL = 'informational_url';
	public const FIELD_INTERNAL_ERROR_MESSAGE = 'internal_error_message';
	public const FIELD_IS_GOAL = 'is_goal';
	public const FIELD_IS_PUBLIC = 'is_public';
	public const FIELD_JOIN_WITH = 'join_with';
	public const FIELD_KURTOSIS = 'kurtosis';
	public const FIELD_LAST_CORRELATED_AT = 'last_correlated_at';
	public const FIELD_LAST_ORIGINAL_UNIT_ID = 'last_original_unit_id';
	public const FIELD_LAST_ORIGINAL_VALUE = 'last_original_value';
	public const FIELD_LAST_PROCESSED_DAILY_VALUE = 'last_processed_daily_value';
	public const FIELD_LAST_UNIT_ID = 'last_unit_id';
	public const FIELD_LAST_VALUE = 'last_value';
	public const FIELD_LATEST_FILLING_TIME = 'latest_filling_time';
	public const FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT = 'latest_non_tagged_measurement_start_at';
	public const FIELD_LATEST_SOURCE_MEASUREMENT_START_AT = 'latest_source_measurement_start_at';
	public const FIELD_LATEST_TAGGED_MEASUREMENT_START_AT = 'latest_tagged_measurement_start_at';
	public const FIELD_LATITUDE = 'latitude';
	public const FIELD_LOCATION = 'location';
	public const FIELD_LONGITUDE = 'longitude';
	public const FIELD_MAXIMUM_ALLOWED_VALUE = 'maximum_allowed_value';
	public const FIELD_MAXIMUM_RECORDED_VALUE = 'maximum_recorded_value';
	public const FIELD_MEAN = 'mean';
	public const FIELD_MEASUREMENTS_AT_LAST_ANALYSIS = 'measurements_at_last_analysis';
	public const FIELD_MEDIAN = 'median';
	public const FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS = 'median_seconds_between_measurements';
	public const FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = 'minimum_allowed_seconds_between_measurements';
	public const FIELD_MINIMUM_ALLOWED_VALUE = 'minimum_allowed_value';
	public const FIELD_MINIMUM_RECORDED_VALUE = 'minimum_recorded_value';
	public const FIELD_MOST_COMMON_CONNECTOR_ID = 'most_common_connector_id';
	public const FIELD_MOST_COMMON_ORIGINAL_UNIT_ID = 'most_common_original_unit_id';
	public const FIELD_MOST_COMMON_SOURCE_NAME = 'most_common_source_name';
	public const FIELD_MOST_COMMON_VALUE = 'most_common_value';
	public const FIELD_NEWEST_DATA_AT = 'newest_data_at';
	public const FIELD_NUMBER_COMMON_TAGGED_BY = 'number_common_tagged_by';
	public const FIELD_NUMBER_OF_CHANGES = 'number_of_changes';
	public const FIELD_NUMBER_OF_COMMON_CHILDREN = 'number_of_common_children';
	public const FIELD_NUMBER_OF_COMMON_FOODS = 'number_of_common_foods';
	public const FIELD_NUMBER_OF_COMMON_INGREDIENTS = 'number_of_common_ingredients';
	public const FIELD_NUMBER_OF_COMMON_JOINED_VARIABLES = 'number_of_common_joined_variables';
	public const FIELD_NUMBER_OF_COMMON_PARENTS = 'number_of_common_parents';
	public const FIELD_NUMBER_OF_COMMON_TAGS = 'number_of_common_tags';
	public const FIELD_NUMBER_OF_CORRELATIONS = 'number_of_correlations';
	public const FIELD_NUMBER_OF_MEASUREMENTS = 'number_of_measurements';
	public const FIELD_NUMBER_OF_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION = 'number_of_measurements_with_tags_at_last_correlation';
	public const FIELD_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS = 'number_of_processed_daily_measurements';
	public const FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN = 'number_of_raw_measurements_with_tags_joins_children';
	public const FIELD_NUMBER_OF_SOFT_DELETED_MEASUREMENTS = 'number_of_soft_deleted_measurements';
	public const FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS = 'number_of_tracking_reminder_notifications';
	public const FIELD_NUMBER_OF_TRACKING_REMINDERS = 'number_of_tracking_reminders';
	public const FIELD_NUMBER_OF_UNIQUE_DAILY_VALUES = 'number_of_unique_daily_values';
	public const FIELD_NUMBER_OF_UNIQUE_VALUES = 'number_of_unique_values';
	public const FIELD_NUMBER_OF_USER_CHILDREN = 'number_of_user_children';
	public const FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_CAUSE = 'number_of_user_variable_relationships_as_cause';
	public const FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_EFFECT = 'number_of_user_variable_relationships_as_effect';
	public const FIELD_NUMBER_OF_USER_FOODS = 'number_of_user_foods';
	public const FIELD_NUMBER_OF_USER_INGREDIENTS = 'number_of_user_ingredients';
	public const FIELD_NUMBER_OF_USER_JOINED_VARIABLES = 'number_of_user_joined_variables';
	public const FIELD_NUMBER_OF_USER_PARENTS = 'number_of_user_parents';
	public const FIELD_NUMBER_OF_USER_TAGS = 'number_of_user_tags';
	public const FIELD_NUMBER_USER_TAGGED_BY = 'number_user_tagged_by';
	public const FIELD_ONSET_DELAY = 'onset_delay';
	public const FIELD_OPTIMAL_VALUE_MESSAGE = 'optimal_value_message';
	public const FIELD_OUTCOME = 'outcome';
	public const FIELD_OUTCOME_OF_INTEREST = 'outcome_of_interest';
	public const FIELD_PARENT_ID = 'parent_id';
	public const FIELD_PREDICTOR_OF_INTEREST = 'predictor_of_interest';
	public const FIELD_REASON_FOR_ANALYSIS = 'reason_for_analysis';
	public const FIELD_RECORD_SIZE_IN_KB = 'record_size_in_kb';
	public const FIELD_SECOND_TO_LAST_VALUE = 'second_to_last_value';
	public const FIELD_SKEWNESS = 'skewness';
	public const FIELD_STANDARD_DEVIATION = 'standard_deviation';
	public const FIELD_STATUS = 'status';
	public const FIELD_THIRD_TO_LAST_VALUE = 'third_to_last_value';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ERROR_MESSAGE = 'user_error_message';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_USER_MAXIMUM_ALLOWED_DAILY_VALUE = 'user_maximum_allowed_daily_value';
	public const FIELD_USER_MINIMUM_ALLOWED_DAILY_VALUE = 'user_minimum_allowed_daily_value';
	public const FIELD_USER_MINIMUM_ALLOWED_NON_ZERO_VALUE = 'user_minimum_allowed_non_zero_value';
	public const FIELD_VALENCE = 'valence';
	public const FIELD_VARIABLE_CATEGORY_ID = 'variable_category_id';
	public const FIELD_VARIABLE_ID = 'variable_id';
	public const FIELD_VARIANCE = 'variance';
	public const FIELD_WIKIPEDIA_TITLE = 'wikipedia_title';
	public const FIELD_WP_POST_ID = 'wp_post_id';
	public const TABLE = 'user_variables';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_EXPERIMENT_START_TIME => 'datetime',
        self::FIELD_EXPERIMENT_END_TIME => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_LAST_CORRELATED_AT => 'datetime',
        self::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT => 'datetime',
        self::FIELD_NEWEST_DATA_AT => 'datetime',
        self::FIELD_ANALYSIS_REQUESTED_AT => 'datetime',
        self::FIELD_ANALYSIS_STARTED_AT => 'datetime',
        self::FIELD_ANALYSIS_ENDED_AT => 'datetime',
        self::FIELD_EARLIEST_SOURCE_MEASUREMENT_START_AT => 'datetime',
        self::FIELD_LATEST_SOURCE_MEASUREMENT_START_AT => 'datetime',
        self::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT => 'datetime',
        self::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT => 'datetime',
        self::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT => 'datetime',
        self::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT => 'datetime',
		self::FIELD_ALIAS => 'string',
		self::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS => 'int',
		self::FIELD_BEST_CAUSE_VARIABLE_ID => 'int',
		self::FIELD_BEST_EFFECT_VARIABLE_ID => 'int',
		self::FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID => 'int',
		self::FIELD_BORING => 'bool',
		self::FIELD_CAUSE_ONLY => 'bool',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_COMBINATION_OPERATION => 'string',
		self::FIELD_CONTROLLABLE => 'bool',
		self::FIELD_DATA_SOURCES_COUNT => 'json',
		self::FIELD_DEFAULT_UNIT_ID => 'int',
		self::FIELD_DELETION_REASON => 'string',
		self::FIELD_DESCRIPTION => 'string',
		self::FIELD_DURATION_OF_ACTION => 'int',
		self::FIELD_EARLIEST_FILLING_TIME => 'int',
		self::FIELD_FILLING_TYPE => 'string',
		self::FIELD_FILLING_VALUE => 'float',
		self::FIELD_ID => 'int',
		self::FIELD_INFORMATIONAL_URL => 'string',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'string',
		self::FIELD_IS_GOAL => 'bool',
		self::FIELD_IS_PUBLIC => 'bool',
		self::FIELD_JOIN_WITH => 'int',
		self::FIELD_KURTOSIS => 'float',
		self::FIELD_LAST_ORIGINAL_UNIT_ID => 'int',
		self::FIELD_LAST_ORIGINAL_VALUE => 'float',
		self::FIELD_LAST_PROCESSED_DAILY_VALUE => 'float',
		self::FIELD_LAST_UNIT_ID => 'int',
		self::FIELD_LAST_VALUE => 'float',
		self::FIELD_LATEST_FILLING_TIME => 'int',
		self::FIELD_LATITUDE => 'float',
		self::FIELD_LOCATION => 'string',
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
		self::FIELD_MOST_COMMON_SOURCE_NAME => 'string',
		self::FIELD_MOST_COMMON_VALUE => 'float',
		self::FIELD_NUMBER_COMMON_TAGGED_BY => 'int',
		self::FIELD_NUMBER_OF_CHANGES => 'int',
		self::FIELD_NUMBER_OF_COMMON_CHILDREN => 'int',
		self::FIELD_NUMBER_OF_COMMON_FOODS => 'int',
		self::FIELD_NUMBER_OF_COMMON_INGREDIENTS => 'int',
		self::FIELD_NUMBER_OF_COMMON_JOINED_VARIABLES => 'int',
		self::FIELD_NUMBER_OF_COMMON_PARENTS => 'int',
		self::FIELD_NUMBER_OF_COMMON_TAGS => 'int',
		self::FIELD_NUMBER_OF_CORRELATIONS => 'int',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'int',
		self::FIELD_NUMBER_OF_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION => 'int',
		self::FIELD_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => 'int',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN => 'int',
		self::FIELD_NUMBER_OF_SOFT_DELETED_MEASUREMENTS => 'int',
		self::FIELD_NUMBER_OF_TRACKING_REMINDERS => 'int',
		self::FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS => 'int',
		self::FIELD_NUMBER_OF_UNIQUE_DAILY_VALUES => 'int',
		self::FIELD_NUMBER_OF_UNIQUE_VALUES => 'int',
		self::FIELD_NUMBER_OF_USER_CHILDREN => 'int',
		self::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_CAUSE => 'int',
		self::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_EFFECT => 'int',
		self::FIELD_NUMBER_OF_USER_FOODS => 'int',
		self::FIELD_NUMBER_OF_USER_INGREDIENTS => 'int',
		self::FIELD_NUMBER_OF_USER_JOINED_VARIABLES => 'int',
		self::FIELD_NUMBER_OF_USER_PARENTS => 'int',
		self::FIELD_NUMBER_OF_USER_TAGS => 'int',
		self::FIELD_NUMBER_USER_TAGGED_BY => 'int',
		self::FIELD_ONSET_DELAY => 'int',
		self::FIELD_OPTIMAL_VALUE_MESSAGE => 'string',
		self::FIELD_OUTCOME => 'bool',
		self::FIELD_OUTCOME_OF_INTEREST => 'bool',
		self::FIELD_PARENT_ID => 'int',
		self::FIELD_PREDICTOR_OF_INTEREST => 'bool',
		self::FIELD_REASON_FOR_ANALYSIS => 'string',
		self::FIELD_RECORD_SIZE_IN_KB => 'int',
		self::FIELD_SECOND_TO_LAST_VALUE => 'float',
		self::FIELD_SKEWNESS => 'float',
		self::FIELD_STANDARD_DEVIATION => 'float',
		self::FIELD_STATUS => 'string',
		self::FIELD_THIRD_TO_LAST_VALUE => 'float',
		self::FIELD_USER_ERROR_MESSAGE => 'string',
		self::FIELD_USER_ID => 'int',
		self::FIELD_USER_MAXIMUM_ALLOWED_DAILY_VALUE => 'float',
		self::FIELD_USER_MINIMUM_ALLOWED_DAILY_VALUE => 'float',
		self::FIELD_USER_MINIMUM_ALLOWED_NON_ZERO_VALUE => 'float',
		self::FIELD_VALENCE => 'string',
		self::FIELD_VARIABLE_CATEGORY_ID => 'int',
		self::FIELD_VARIABLE_ID => 'int',
		self::FIELD_VARIANCE => 'float',
		self::FIELD_WIKIPEDIA_TITLE => 'string',
		self::FIELD_WP_POST_ID => 'int',	];
	protected array $rules = [
		self::FIELD_ALIAS => 'nullable|max:125',
		self::FIELD_ANALYSIS_ENDED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_REQUESTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_STARTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_BEST_CAUSE_VARIABLE_ID => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_BEST_EFFECT_VARIABLE_ID => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_BORING => 'nullable|boolean',
		self::FIELD_CAUSE_ONLY => 'nullable|boolean',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_COMBINATION_OPERATION => 'nullable',
		self::FIELD_CONTROLLABLE => 'nullable|boolean',
		self::FIELD_DATA_SOURCES_COUNT => 'nullable|max:65535',
		self::FIELD_DEFAULT_UNIT_ID => 'nullable|integer|min:0|max:65535',
		self::FIELD_DELETION_REASON => 'nullable|max:280',
		self::FIELD_DESCRIPTION => 'nullable|max:65535',
		self::FIELD_DURATION_OF_ACTION => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_EARLIEST_FILLING_TIME => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_EARLIEST_SOURCE_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_EXPERIMENT_END_TIME => 'nullable|date',
		self::FIELD_EXPERIMENT_START_TIME => 'nullable|date',
		self::FIELD_FILLING_TYPE => 'nullable',
		self::FIELD_FILLING_VALUE => 'nullable|numeric',
		self::FIELD_INFORMATIONAL_URL => 'nullable|max:2000',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'nullable|max:65535',
		self::FIELD_IS_GOAL => 'nullable|boolean',
		self::FIELD_IS_PUBLIC => 'nullable|boolean',
		self::FIELD_JOIN_WITH => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_KURTOSIS => 'nullable|numeric',
		self::FIELD_LAST_CORRELATED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LAST_ORIGINAL_UNIT_ID => 'nullable|integer|min:0|max:65535',
		self::FIELD_LAST_ORIGINAL_VALUE => 'nullable|numeric',
		self::FIELD_LAST_PROCESSED_DAILY_VALUE => 'nullable|numeric',
		self::FIELD_LAST_UNIT_ID => 'nullable|integer|min:0|max:65535',
		self::FIELD_LAST_VALUE => 'nullable|numeric',
		self::FIELD_LATEST_FILLING_TIME => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LATEST_SOURCE_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LATITUDE => 'nullable|numeric',
		self::FIELD_LOCATION => 'nullable|max:255',
		self::FIELD_LONGITUDE => 'nullable|numeric',
		self::FIELD_MAXIMUM_ALLOWED_VALUE => 'nullable|numeric',
		self::FIELD_MAXIMUM_RECORDED_VALUE => 'nullable|numeric',
		self::FIELD_MEAN => 'nullable|numeric',
		self::FIELD_MEASUREMENTS_AT_LAST_ANALYSIS => 'required|integer|min:0|max:2147483647',
		self::FIELD_MEDIAN => 'nullable|numeric',
		self::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_MINIMUM_ALLOWED_VALUE => 'nullable|numeric',
		self::FIELD_MINIMUM_RECORDED_VALUE => 'nullable|numeric',
		self::FIELD_MOST_COMMON_CONNECTOR_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_MOST_COMMON_ORIGINAL_UNIT_ID => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_MOST_COMMON_SOURCE_NAME => 'nullable|max:255',
		self::FIELD_MOST_COMMON_VALUE => 'nullable|numeric',
		self::FIELD_NEWEST_DATA_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_NUMBER_COMMON_TAGGED_BY => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_CHANGES => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_COMMON_CHILDREN => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_COMMON_FOODS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_COMMON_INGREDIENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_COMMON_JOINED_VARIABLES => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_COMMON_PARENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_COMMON_TAGS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_CORRELATIONS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_SOFT_DELETED_MEASUREMENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_TRACKING_REMINDERS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_UNIQUE_DAILY_VALUES => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_UNIQUE_VALUES => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_USER_CHILDREN => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_CAUSE => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_EFFECT => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_USER_FOODS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_USER_INGREDIENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_USER_JOINED_VARIABLES => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_USER_PARENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_USER_TAGS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_USER_TAGGED_BY => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_ONSET_DELAY => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_OPTIMAL_VALUE_MESSAGE => 'nullable|max:500',
		self::FIELD_OUTCOME => 'nullable|boolean',
		self::FIELD_OUTCOME_OF_INTEREST => 'nullable|boolean',
		self::FIELD_PARENT_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_PREDICTOR_OF_INTEREST => 'nullable|boolean',
		self::FIELD_REASON_FOR_ANALYSIS => 'nullable|max:255',
		self::FIELD_RECORD_SIZE_IN_KB => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_SECOND_TO_LAST_VALUE => 'nullable|numeric',
		self::FIELD_SKEWNESS => 'nullable|numeric',
		self::FIELD_STANDARD_DEVIATION => 'nullable|numeric',
		self::FIELD_STATUS => 'nullable|max:25',
		self::FIELD_THIRD_TO_LAST_VALUE => 'nullable|numeric',
		self::FIELD_USER_ERROR_MESSAGE => 'nullable|max:65535',
		self::FIELD_USER_ID => 'required|numeric|min:0',
		self::FIELD_USER_MAXIMUM_ALLOWED_DAILY_VALUE => 'nullable|numeric',
		self::FIELD_USER_MINIMUM_ALLOWED_DAILY_VALUE => 'nullable|numeric',
		self::FIELD_USER_MINIMUM_ALLOWED_NON_ZERO_VALUE => 'nullable|numeric',
		self::FIELD_VALENCE => 'nullable',
		self::FIELD_VARIABLE_CATEGORY_ID => 'nullable|boolean',
		self::FIELD_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_VARIANCE => 'nullable|numeric',
		self::FIELD_WIKIPEDIA_TITLE => 'nullable|max:100',
		self::FIELD_WP_POST_ID => 'nullable|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_PARENT_ID => 'ID of the parent variable if this variable has any parent',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_USER_ID => '',
		self::FIELD_VARIABLE_ID => 'ID of variable',
		self::FIELD_DEFAULT_UNIT_ID => 'ID of unit to use for this variable',
		self::FIELD_MINIMUM_ALLOWED_VALUE => 'Minimum reasonable value for this variable (uses default unit)',
		self::FIELD_MAXIMUM_ALLOWED_VALUE => 'Maximum reasonable value for this variable (uses default unit)',
		self::FIELD_FILLING_VALUE => 'Value for replacing null measurements',
		self::FIELD_JOIN_WITH => 'The Variable this Variable should be joined with. If the variable is joined with some other variable then it is not shown to user in the list of variables',
		self::FIELD_ONSET_DELAY => 'How long it takes for a measurement in this variable to take effect',
		self::FIELD_DURATION_OF_ACTION => 'Estimated duration of time following the onset delay in which a stimulus produces a perceivable effect',
		self::FIELD_VARIABLE_CATEGORY_ID => 'ID of variable category',
		self::FIELD_CAUSE_ONLY => 'A value of 1 indicates that this variable is generally a cause in a causal relationship.  An example of a causeOnly variable would be a variable such as Cloud Cover which would generally not be influenced by the behaviour of the user',
		self::FIELD_FILLING_TYPE => '0 -> No filling, 1 -> Use filling-value',
		self::FIELD_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => 'Number of processed measurements',
		self::FIELD_MEASUREMENTS_AT_LAST_ANALYSIS => 'Number of measurements at last analysis',
		self::FIELD_LAST_UNIT_ID => 'ID of last Unit',
		self::FIELD_LAST_ORIGINAL_UNIT_ID => 'ID of last original Unit',
		self::FIELD_LAST_VALUE => 'Last Value',
		self::FIELD_LAST_ORIGINAL_VALUE => 'Last original value which is stored',
		self::FIELD_NUMBER_OF_CORRELATIONS => 'Number of user_variable_relationships for this variable',
		self::FIELD_STATUS => '',
		self::FIELD_STANDARD_DEVIATION => 'Standard deviation',
		self::FIELD_VARIANCE => 'Variance',
		self::FIELD_MINIMUM_RECORDED_VALUE => 'Minimum recorded value of this variable',
		self::FIELD_MAXIMUM_RECORDED_VALUE => 'Maximum recorded value of this variable',
		self::FIELD_MEAN => 'Mean',
		self::FIELD_MEDIAN => 'Median',
		self::FIELD_MOST_COMMON_ORIGINAL_UNIT_ID => 'Most common Unit ID',
		self::FIELD_MOST_COMMON_VALUE => 'Most common value',
		self::FIELD_NUMBER_OF_UNIQUE_DAILY_VALUES => 'Number of unique daily values',
		self::FIELD_NUMBER_OF_UNIQUE_VALUES => 'Number of unique values',
		self::FIELD_NUMBER_OF_CHANGES => 'Number of changes',
		self::FIELD_SKEWNESS => 'Skewness',
		self::FIELD_KURTOSIS => 'Kurtosis',
		self::FIELD_LATITUDE => '',
		self::FIELD_LONGITUDE => '',
		self::FIELD_LOCATION => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_OUTCOME => 'Outcome variables (those with `outcome` == 1) are variables for which a human would generally want to identify the influencing factors.  These include symptoms of illness, physique, mood, cognitive performance, etc.  Generally correlation calculations are only performed on outcome variables',
		self::FIELD_DATA_SOURCES_COUNT => 'Array of connector or client measurement data source names as key and number of measurements as value',
		self::FIELD_EARLIEST_FILLING_TIME => 'Earliest filling time',
		self::FIELD_LATEST_FILLING_TIME => 'Latest filling time',
		self::FIELD_LAST_PROCESSED_DAILY_VALUE => 'Last value for user after daily aggregation and filling',
		self::FIELD_OUTCOME_OF_INTEREST => '',
		self::FIELD_PREDICTOR_OF_INTEREST => '',
		self::FIELD_EXPERIMENT_START_TIME => '',
		self::FIELD_EXPERIMENT_END_TIME => '',
		self::FIELD_DESCRIPTION => '',
		self::FIELD_ALIAS => '',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_SECOND_TO_LAST_VALUE => '',
		self::FIELD_THIRD_TO_LAST_VALUE => '',
		self::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_EFFECT => 'Number of user variable relationships for which this variable is the effect variable',
		self::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_CAUSE => 'Number of user variable relationships for which this variable is the cause variable',
		self::FIELD_COMBINATION_OPERATION => 'How to combine values of this variable (for instance, to see a summary of the values over a month) SUM or MEAN',
		self::FIELD_INFORMATIONAL_URL => 'Wikipedia url',
		self::FIELD_MOST_COMMON_CONNECTOR_ID => '',
		self::FIELD_VALENCE => '',
		self::FIELD_WIKIPEDIA_TITLE => '',
		self::FIELD_NUMBER_OF_TRACKING_REMINDERS => '',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN => '',
		self::FIELD_MOST_COMMON_SOURCE_NAME => '',
		self::FIELD_OPTIMAL_VALUE_MESSAGE => '',
		self::FIELD_BEST_CAUSE_VARIABLE_ID => '',
		self::FIELD_BEST_EFFECT_VARIABLE_ID => '',
		self::FIELD_USER_MAXIMUM_ALLOWED_DAILY_VALUE => '',
		self::FIELD_USER_MINIMUM_ALLOWED_DAILY_VALUE => '',
		self::FIELD_USER_MINIMUM_ALLOWED_NON_ZERO_VALUE => '',
		self::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => '',
		self::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS => '',
		self::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS => '',
		self::FIELD_LAST_CORRELATED_AT => 'datetime',
		self::FIELD_NUMBER_OF_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION => '',
		self::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT => 'datetime',
		self::FIELD_NEWEST_DATA_AT => 'datetime',
		self::FIELD_ANALYSIS_REQUESTED_AT => 'datetime',
		self::FIELD_REASON_FOR_ANALYSIS => '',
		self::FIELD_ANALYSIS_STARTED_AT => 'datetime',
		self::FIELD_ANALYSIS_ENDED_AT => 'datetime',
		self::FIELD_USER_ERROR_MESSAGE => '',
		self::FIELD_INTERNAL_ERROR_MESSAGE => '',
		self::FIELD_EARLIEST_SOURCE_MEASUREMENT_START_AT => 'datetime',
		self::FIELD_LATEST_SOURCE_MEASUREMENT_START_AT => 'datetime',
		self::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT => 'datetime',
		self::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT => 'datetime',
		self::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT => 'datetime',
		self::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT => 'datetime',
		self::FIELD_WP_POST_ID => '',
		self::FIELD_NUMBER_OF_SOFT_DELETED_MEASUREMENTS => 'Formula: update user_variables v
                inner join (
                    select measurements.user_variable_id, count(measurements.id) as number_of_soft_deleted_measurements
                    from measurements
                    where measurements.deleted_at is not null
                    group by measurements.user_variable_id
                    ) m on v.id = m.user_variable_id
                set v.number_of_soft_deleted_measurements = m.number_of_soft_deleted_measurements
            ',
		self::FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID => '',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'Number of Measurements for this User Variable.
                    [Formula: update user_variables
                        left join (
                            select count(id) as total, user_variable_id
                            from measurements
                            group by user_variable_id
                        )
                        as grouped on user_variables.id = grouped.user_variable_id
                    set user_variables.number_of_measurements = count(grouped.total)]',
		self::FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS => 'Number of Tracking Reminder Notifications for this User Variable.
                    [Formula: update user_variables
                        left join (
                            select count(id) as total, user_variable_id
                            from tracking_reminder_notifications
                            group by user_variable_id
                        )
                        as grouped on user_variables.id = grouped.user_variable_id
                    set user_variables.number_of_tracking_reminder_notifications = count(grouped.total)]',
		self::FIELD_DELETION_REASON => 'The reason the variable was deleted.',
		self::FIELD_RECORD_SIZE_IN_KB => '',
		self::FIELD_NUMBER_OF_COMMON_TAGS => 'Number of categories, joined variables, or ingredients for this variable that use this variables measurements to generate synthetically derived measurements. ',
		self::FIELD_NUMBER_COMMON_TAGGED_BY => 'Number of children, joined variables or foods that this use has measurements for which are to be used to generate synthetic measurements for this variable. ',
		self::FIELD_NUMBER_OF_COMMON_JOINED_VARIABLES => 'Joined variables are duplicate variables measuring the same thing. ',
		self::FIELD_NUMBER_OF_COMMON_INGREDIENTS => 'Measurements for this variable can be used to synthetically generate ingredient measurements. ',
		self::FIELD_NUMBER_OF_COMMON_FOODS => 'Measurements for this ingredient variable can be synthetically generate by food measurements. ',
		self::FIELD_NUMBER_OF_COMMON_CHILDREN => 'Measurements for this parent category variable can be synthetically generated by measurements from its child variables. ',
		self::FIELD_NUMBER_OF_COMMON_PARENTS => 'Measurements for this parent category variable can be synthetically generated by measurements from its child variables. ',
		self::FIELD_NUMBER_OF_USER_TAGS => 'Number of categories, joined variables, or ingredients for this variable that use this variables measurements to generate synthetically derived measurements. This only includes ones created by the user. ',
		self::FIELD_NUMBER_USER_TAGGED_BY => 'Number of children, joined variables or foods that this use has measurements for which are to be used to generate synthetic measurements for this variable. This only includes ones created by the user. ',
		self::FIELD_NUMBER_OF_USER_JOINED_VARIABLES => 'Joined variables are duplicate variables measuring the same thing. This only includes ones created by the user. ',
		self::FIELD_NUMBER_OF_USER_INGREDIENTS => 'Measurements for this variable can be used to synthetically generate ingredient measurements. This only includes ones created by the user. ',
		self::FIELD_NUMBER_OF_USER_FOODS => 'Measurements for this ingredient variable can be synthetically generate by food measurements. This only includes ones created by the user. ',
		self::FIELD_NUMBER_OF_USER_CHILDREN => 'Measurements for this parent category variable can be synthetically generated by measurements from its child variables. This only includes ones created by the user. ',
		self::FIELD_NUMBER_OF_USER_PARENTS => 'Measurements for this parent category variable can be synthetically generated by measurements from its child variables. This only includes ones created by the user. ',
		self::FIELD_IS_PUBLIC => '',
		self::FIELD_IS_GOAL => 'The effect of a food on the severity of a symptom is useful because you can control the predictor directly. However, the effect of a symptom on the foods you eat is not very useful.  The foods you eat are not generally an objective end in themselves. ',
		self::FIELD_CONTROLLABLE => 'You can control the foods you eat directly. However, symptom severity or weather is not directly controllable. ',
		self::FIELD_BORING => 'The user variable is boring if the owner would not be interested in its causes or effects. ',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => UserVariable::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => UserVariable::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'best_user_variable_relationship' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => UserVariableRelationship::class,
			'foreignKeyColumnName' => 'best_user_variable_relationship_id',
			'foreignKey' => UserVariable::FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => UserVariableRelationship::FIELD_ID,
			'ownerKeyColumnName' => 'best_user_variable_relationship_id',
			'ownerKey' => UserVariable::FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID,
			'methodName' => 'best_user_variable_relationship',
		],
		'default_unit' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Unit::class,
			'foreignKeyColumnName' => 'default_unit_id',
			'foreignKey' => UserVariable::FIELD_DEFAULT_UNIT_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Unit::FIELD_ID,
			'ownerKeyColumnName' => 'default_unit_id',
			'ownerKey' => UserVariable::FIELD_DEFAULT_UNIT_ID,
			'methodName' => 'default_unit',
		],
		'last_unit' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Unit::class,
			'foreignKeyColumnName' => 'last_unit_id',
			'foreignKey' => UserVariable::FIELD_LAST_UNIT_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Unit::FIELD_ID,
			'ownerKeyColumnName' => 'last_unit_id',
			'ownerKey' => UserVariable::FIELD_LAST_UNIT_ID,
			'methodName' => 'last_unit',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => UserVariable::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => UserVariable::FIELD_USER_ID,
			'methodName' => 'user',
		],
		'variable_category' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => VariableCategory::class,
			'foreignKeyColumnName' => 'variable_category_id',
			'foreignKey' => UserVariable::FIELD_VARIABLE_CATEGORY_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => VariableCategory::FIELD_ID,
			'ownerKeyColumnName' => 'variable_category_id',
			'ownerKey' => UserVariable::FIELD_VARIABLE_CATEGORY_ID,
			'methodName' => 'variable_category',
		],
		'variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'variable_id',
			'foreignKey' => UserVariable::FIELD_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'variable_id',
			'ownerKey' => UserVariable::FIELD_VARIABLE_ID,
			'methodName' => 'variable',
		],
		'wp_post' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => WpPost::class,
			'foreignKeyColumnName' => 'wp_post_id',
			'foreignKey' => UserVariable::FIELD_WP_POST_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => WpPost::FIELD_ID,
			'ownerKeyColumnName' => 'wp_post_id',
			'ownerKey' => UserVariable::FIELD_WP_POST_ID,
			'methodName' => 'wp_post',
		],
		'correlations_where_cause_user_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariableRelationship::class,
			'foreignKey' => UserVariableRelationship::FIELD_CAUSE_USER_VARIABLE_ID,
			'localKey' => UserVariableRelationship::FIELD_ID,
			'methodName' => 'correlations_where_cause_user_variable',
		],
		'correlations_where_effect_user_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariableRelationship::class,
			'foreignKey' => UserVariableRelationship::FIELD_EFFECT_USER_VARIABLE_ID,
			'localKey' => UserVariableRelationship::FIELD_ID,
			'methodName' => 'correlations_where_effect_user_variable',
		],
		'measurements' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Measurement::class,
			'foreignKey' => Measurement::FIELD_USER_VARIABLE_ID,
			'localKey' => Measurement::FIELD_ID,
			'methodName' => 'measurements',
		],
		'tracking_reminder_notifications' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => TrackingReminderNotification::class,
			'foreignKey' => TrackingReminderNotification::FIELD_USER_VARIABLE_ID,
			'localKey' => TrackingReminderNotification::FIELD_ID,
			'methodName' => 'tracking_reminder_notifications',
		],
		'tracking_reminders' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => TrackingReminder::class,
			'foreignKey' => TrackingReminder::FIELD_USER_VARIABLE_ID,
			'localKey' => TrackingReminder::FIELD_ID,
			'methodName' => 'tracking_reminders',
		],
		'user_tags_where_tag_user_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserTag::class,
			'foreignKey' => UserTag::FIELD_TAG_USER_VARIABLE_ID,
			'localKey' => UserTag::FIELD_ID,
			'methodName' => 'user_tags_where_tag_user_variable',
		],
		'user_tags_where_tagged_user_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserTag::class,
			'foreignKey' => UserTag::FIELD_TAGGED_USER_VARIABLE_ID,
			'localKey' => UserTag::FIELD_ID,
			'methodName' => 'user_tags_where_tagged_user_variable',
		],
		'user_variable_clients' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariableClient::class,
			'foreignKey' => UserVariableClient::FIELD_USER_VARIABLE_ID,
			'localKey' => UserVariableClient::FIELD_ID,
			'methodName' => 'user_variable_clients',
		],
		'user_variable_outcome_categories' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariableOutcomeCategory::class,
			'foreignKey' => UserVariableOutcomeCategory::FIELD_USER_VARIABLE_ID,
			'localKey' => UserVariableOutcomeCategory::FIELD_ID,
			'methodName' => 'user_variable_outcome_categories',
		],
		'user_variable_predictor_categories' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariablePredictorCategory::class,
			'foreignKey' => UserVariablePredictorCategory::FIELD_USER_VARIABLE_ID,
			'localKey' => UserVariablePredictorCategory::FIELD_ID,
			'methodName' => 'user_variable_predictor_categories',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, UserVariable::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			UserVariable::FIELD_CLIENT_ID);
	}
	public function best_user_variable_relationship(): BelongsTo{
		return $this->belongsTo(UserVariableRelationship::class, UserVariable::FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID, UserVariableRelationship::FIELD_ID,
			UserVariable::FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID);
	}
	public function default_unit(): BelongsTo{
		return $this->belongsTo(Unit::class, UserVariable::FIELD_DEFAULT_UNIT_ID, Unit::FIELD_ID,
			UserVariable::FIELD_DEFAULT_UNIT_ID);
	}
	public function last_unit(): BelongsTo{
		return $this->belongsTo(Unit::class, UserVariable::FIELD_LAST_UNIT_ID, Unit::FIELD_ID,
			UserVariable::FIELD_LAST_UNIT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, UserVariable::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			UserVariable::FIELD_USER_ID);
	}
	public function variable_category(): BelongsTo{
		return $this->belongsTo(VariableCategory::class, UserVariable::FIELD_VARIABLE_CATEGORY_ID,
			VariableCategory::FIELD_ID, UserVariable::FIELD_VARIABLE_CATEGORY_ID);
	}
	public function variable(): BelongsTo{
		return $this->belongsTo(Variable::class, UserVariable::FIELD_VARIABLE_ID, static::FIELD_ID,
			UserVariable::FIELD_VARIABLE_ID);
	}
	public function wp_post(): BelongsTo{
		return $this->belongsTo(WpPost::class, UserVariable::FIELD_WP_POST_ID, WpPost::FIELD_ID,
			UserVariable::FIELD_WP_POST_ID);
	}
	public function correlations_where_cause_user_variable(): HasMany{
		return $this->hasMany(UserVariableRelationship::class, UserVariableRelationship::FIELD_CAUSE_USER_VARIABLE_ID, static::FIELD_ID);
	}
	public function correlations_where_effect_user_variable(): HasMany{
		return $this->hasMany(UserVariableRelationship::class, UserVariableRelationship::FIELD_EFFECT_USER_VARIABLE_ID, static::FIELD_ID);
	}
	public function measurements(): HasMany{
		return $this->hasMany(Measurement::class, Measurement::FIELD_USER_VARIABLE_ID, static::FIELD_ID);
	}
	public function tracking_reminder_notifications(): HasMany{
		return $this->hasMany(TrackingReminderNotification::class, TrackingReminderNotification::FIELD_USER_VARIABLE_ID,
			static::FIELD_ID);
	}
	public function tracking_reminders(): HasMany{
		return $this->hasMany(TrackingReminder::class, TrackingReminder::FIELD_USER_VARIABLE_ID, static::FIELD_ID);
	}
	public function user_tags_where_tag_user_variable(): HasMany{
		return $this->hasMany(UserTag::class, UserTag::FIELD_TAG_USER_VARIABLE_ID, static::FIELD_ID);
	}
	public function user_tags_where_tagged_user_variable(): HasMany{
		return $this->hasMany(UserTag::class, UserTag::FIELD_TAGGED_USER_VARIABLE_ID, static::FIELD_ID);
	}
	public function user_variable_clients(): HasMany{
		return $this->hasMany(UserVariableClient::class, UserVariableClient::FIELD_USER_VARIABLE_ID, static::FIELD_ID);
	}
	public function user_variable_outcome_categories(): HasMany{
		return $this->hasMany(UserVariableOutcomeCategory::class, UserVariableOutcomeCategory::FIELD_USER_VARIABLE_ID,
			static::FIELD_ID);
	}
	public function user_variable_predictor_categories(): HasMany{
		return $this->hasMany(UserVariablePredictorCategory::class,
			UserVariablePredictorCategory::FIELD_USER_VARIABLE_ID, static::FIELD_ID);
	}
}
