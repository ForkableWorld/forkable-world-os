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
use App\Models\Application;
use App\Models\BaseModel;
use App\Models\CommonTag;
use App\Models\UserVariableRelationship;
use App\Models\CorrelationCausalityVote;
use App\Models\CorrelationUsefulnessVote;
use App\Models\CtgCondition;
use App\Models\CtgIntervention;
use App\Models\CtSideEffect;
use App\Models\CtTreatmentSideEffect;
use App\Models\Measurement;
use App\Models\OAClient;
use App\Models\Study;
use App\Models\ThirdPartyCorrelation;
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
use App\Models\VariableOutcomeCategory;
use App\Models\VariablePredictorCategory;
use App\Models\Vote;
use App\Models\WpPost;
use App\Properties\User\UserIdProperty;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseVariable
 * @property int $id
 * @property string $name
 * @property int $number_of_user_variables
 * @property int $variable_category_id
 * @property int $default_unit_id
 * @property float $default_value
 * @property bool $cause_only
 * @property string $client_id
 * @property string $combination_operation
 * @property string $common_alias
 * @property Carbon $created_at
 * @property string $description
 * @property int $duration_of_action
 * @property float $filling_value
 * @property string $image_url
 * @property string $informational_url
 * @property string $ion_icon
 * @property float $kurtosis
 * @property float $maximum_allowed_value
 * @property float $maximum_recorded_value
 * @property float $mean
 * @property float $median
 * @property float $minimum_allowed_value
 * @property float $minimum_recorded_value
 * @property int $number_of_global_variable_relationships_as_cause
 * @property int $most_common_original_unit_id
 * @property float $most_common_value
 * @property int $number_of_global_variable_relationships_as_effect
 * @property int $number_of_unique_values
 * @property int $onset_delay
 * @property bool $outcome
 * @property int $parent_id
 * @property float $price
 * @property string $product_url
 * @property float $second_most_common_value
 * @property float $skewness
 * @property float $standard_deviation
 * @property string $status
 * @property float $third_most_common_value
 * @property Carbon $updated_at
 * @property float $variance
 * @property int $most_common_connector_id
 * @property string $synonyms
 * @property string $wikipedia_url
 * @property string $brand_name
 * @property string $valence
 * @property string $wikipedia_title
 * @property int $number_of_tracking_reminders
 * @property string $upc_12
 * @property string $upc_14
 * @property int $number_common_tagged_by
 * @property int $number_of_common_tags
 * @property Carbon $deleted_at
 * @property string $most_common_source_name
 * @property array $data_sources_count
 * @property string $optimal_value_message
 * @property int $best_cause_variable_id
 * @property int $best_effect_variable_id
 * @property float $common_maximum_allowed_daily_value
 * @property float $common_minimum_allowed_daily_value
 * @property float $common_minimum_allowed_non_zero_value
 * @property int $minimum_allowed_seconds_between_measurements
 * @property int $average_seconds_between_measurements
 * @property int $median_seconds_between_measurements
 * @property int $number_of_raw_measurements_with_tags_joins_children
 * @property array $additional_meta_data
 * @property bool $manual_tracking
 * @property Carbon $analysis_settings_modified_at
 * @property Carbon $newest_data_at
 * @property Carbon $analysis_requested_at
 * @property string $reason_for_analysis
 * @property Carbon $analysis_started_at
 * @property Carbon $analysis_ended_at
 * @property string $user_error_message
 * @property string $internal_error_message
 * @property Carbon $latest_tagged_measurement_start_at
 * @property Carbon $earliest_tagged_measurement_start_at
 * @property Carbon $latest_non_tagged_measurement_start_at
 * @property Carbon $earliest_non_tagged_measurement_start_at
 * @property int $wp_post_id
 * @property int $number_of_soft_deleted_measurements
 * @property array $charts
 * @property int $creator_user_id
 * @property int $best_global_variable_relationship_id
 * @property string $filling_type
 * @property int $number_of_outcome_population_studies
 * @property int $number_of_predictor_population_studies
 * @property int $number_of_applications_where_outcome_variable
 * @property int $number_of_applications_where_predictor_variable
 * @property int $number_of_common_tags_where_tag_variable
 * @property int $number_of_common_tags_where_tagged_variable
 * @property int $number_of_outcome_case_studies
 * @property int $number_of_predictor_case_studies
 * @property int $number_of_measurements
 * @property int $number_of_studies_where_cause_variable
 * @property int $number_of_studies_where_effect_variable
 * @property int $number_of_tracking_reminder_notifications
 * @property int $number_of_user_tags_where_tag_variable
 * @property int $number_of_user_tags_where_tagged_variable
 * @property int $number_of_variables_where_best_cause_variable
 * @property int $number_of_variables_where_best_effect_variable
 * @property int $number_of_votes_where_cause_variable
 * @property int $number_of_votes_where_effect_variable
 * @property int $number_of_users_where_primary_outcome_variable
 * @property string $deletion_reason
 * @property float $maximum_allowed_daily_value
 * @property int $record_size_in_kb
 * @property int $number_of_common_joined_variables
 * @property int $number_of_common_ingredients
 * @property int $number_of_common_foods
 * @property int $number_of_common_children
 * @property int $number_of_common_parents
 * @property int $number_of_user_joined_variables
 * @property int $number_of_user_ingredients
 * @property int $number_of_user_foods
 * @property int $number_of_user_children
 * @property int $number_of_user_parents
 * @property bool $is_public
 * @property int $sort_order
 * @property bool $is_goal
 * @property bool $controllable
 * @property bool $boring
 * @property GlobalVariableRelationship $best_global_variable_relationship
 * @property Variable $best_cause_variable
 * @property Variable $best_effect_variable
 * @property OAClient $oa_client
 * @property Unit $default_unit
 * @property VariableCategory $variable_category
 * @property WpPost $wp_post
 * @property Collection|GlobalVariableRelationship[] $global_variable_relationships_where_cause_variable
 * @property Collection|GlobalVariableRelationship[] $global_variable_relationships_where_effect_variable
 * @property Collection|Application[] $applications_where_outcome_variable
 * @property Collection|Application[] $applications_where_predictor_variable
 * @property Collection|CommonTag[] $common_tags_where_tag_variable
 * @property Collection|CommonTag[] $common_tags_where_tagged_variable
 * @property Collection|CorrelationCausalityVote[] $correlation_causality_votes_where_cause_variable
 * @property Collection|CorrelationCausalityVote[] $correlation_causality_votes_where_effect_variable
 * @property Collection|CorrelationUsefulnessVote[] $correlation_usefulness_votes_where_cause_variable
 * @property Collection|CorrelationUsefulnessVote[] $correlation_usefulness_votes_where_effect_variable
 * @property Collection|UserVariableRelationship[] $correlations_where_cause_variable
 * @property Collection|UserVariableRelationship[] $correlations_where_effect_variable
 * @property CtSideEffect $ct_side_effect
 * @property Collection|CtTreatmentSideEffect[] $ct_treatment_side_effects_where_side_effect_variable
 * @property Collection|CtTreatmentSideEffect[] $ct_treatment_side_effects_where_treatment_variable
 * @property CtgCondition $ctg_condition
 * @property CtgIntervention $ctg_intervention
 * @property Collection|Measurement[] $measurements
 * @property Collection|Study[] $studies_where_cause_variable
 * @property Collection|Study[] $studies_where_effect_variable
 * @property Collection|ThirdPartyCorrelation[] $third_party_correlations
 * @property Collection|TrackingReminderNotification[] $tracking_reminder_notifications
 * @property Collection|TrackingReminder[] $tracking_reminders
 * @property Collection|UserTag[] $user_tags_where_tag_variable
 * @property Collection|UserTag[] $user_tags_where_tagged_variable
 * @property Collection|UserVariableClient[] $user_variable_clients
 * @property Collection|UserVariableOutcomeCategory[] $user_variable_outcome_categories
 * @property Collection|UserVariablePredictorCategory[] $user_variable_predictor_categories
 * @property Collection|UserVariable[] $user_variables
 * @property Collection|VariableOutcomeCategory[] $variable_outcome_categories
 * @property Collection|VariablePredictorCategory[] $variable_predictor_categories
 * @property Collection|Variable[] $variables_where_best_cause_variable
 * @property Collection|Variable[] $variables_where_best_effect_variable
 * @property Collection|Vote[] $votes_where_cause_variable
 * @property Collection|Vote[] $votes_where_effect_variable
 * @property Collection|\App\Models\User[] $users_where_primary_outcome_variable
 * @package App\Models\Base
 * @property-read int|null $global_variable_relationships_where_cause_variable_count
 * @property-read int|null $global_variable_relationships_where_effect_variable_count
 * @property-read int|null $applications_where_outcome_variable_count
 * @property-read int|null $applications_where_predictor_variable_count
 * @property-read int|null $common_tags_where_tag_variable_count
 * @property-read int|null $common_tags_where_tagged_variable_count
 * @property-read int|null $correlation_causality_votes_where_cause_variable_count
 * @property-read int|null $correlation_causality_votes_where_effect_variable_count
 * @property-read int|null $correlation_usefulness_votes_where_cause_variable_count
 * @property-read int|null $correlation_usefulness_votes_where_effect_variable_count
 * @property-read int|null $correlations_where_cause_variable_count
 * @property-read int|null $correlations_where_effect_variable_count
 * @property-read int|null $ct_treatment_side_effects_where_side_effect_variable_count
 * @property-read int|null $ct_treatment_side_effects_where_treatment_variable_count
 * @property mixed $raw
 * @property-read int|null $measurements_count
 * @property-read int|null $studies_where_cause_variable_count
 * @property-read int|null $studies_where_effect_variable_count
 * @property-read int|null $third_party_correlations_count
 * @property-read int|null $tracking_reminder_notifications_count
 * @property-read int|null $tracking_reminders_count
 * @property-read int|null $user_tags_where_tag_variable_count
 * @property-read int|null $user_tags_where_tagged_variable_count
 * @property-read int|null $user_variable_clients_count
 * @property-read int|null $user_variables_count
 * @property-read int|null $users_where_primary_outcome_variable_count
 * @property-read int|null $variables_where_best_cause_variable_count
 * @property-read int|null $variables_where_best_effect_variable_count
 * @property-read int|null $votes_where_cause_variable_count
 * @property-read int|null $votes_where_effect_variable_count
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable newQuery()
 * @method static \Illuminate\Database\Query\Builder|BaseVariable onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereAdditionalMetaData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereAnalysisEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereAnalysisRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereAnalysisSettingsModifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereAnalysisStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereAverageSecondsBetweenMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereBestGlobalVariableRelationshipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereBestCauseVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereBestEffectVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereBrandName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereCauseOnly($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereCharts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereCombinationOperation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereCommonAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereCommonMaximumAllowedDailyValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereCommonMinimumAllowedDailyValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereCommonMinimumAllowedNonZeroValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereCreatorUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereDataSourcesCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereDefaultUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereDefaultValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereDeletionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereDurationOfAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereEarliestNonTaggedMeasurementStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereEarliestTaggedMeasurementStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereFillingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereFillingValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereInformationalUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereInternalErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereIonIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereKurtosis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereLatestNonTaggedMeasurementStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereLatestTaggedMeasurementStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereManualTracking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereMaximumAllowedDailyValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereMaximumAllowedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereMaximumRecordedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereMean($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereMedian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereMedianSecondsBetweenMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable
 *     whereMinimumAllowedSecondsBetweenMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereMinimumAllowedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereMinimumRecordedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereMostCommonConnectorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereMostCommonOriginalUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereMostCommonSourceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereMostCommonValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNewestDataAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberCommonTaggedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfGlobalVariableRelationshipsAsCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfGlobalVariableRelationshipsAsEffect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable
 *     whereNumberOfApplicationsWhereOutcomeVariable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable
 *     whereNumberOfApplicationsWherePredictorVariable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfCommonChildren($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfCommonFoods($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfCommonIngredients($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfCommonJoinedVariables($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfCommonParents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfCommonTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfCommonTagsWhereTagVariable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfCommonTagsWhereTaggedVariable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfOutcomeCaseStudies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfOutcomePopulationStudies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfPredictorCaseStudies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfPredictorPopulationStudies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable
 *     whereNumberOfRawMeasurementsWithTagsJoinsChildren($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfSoftDeletedMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfStudiesWhereCauseVariable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfStudiesWhereEffectVariable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfTrackingReminderNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfTrackingReminders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfUniqueValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfUserChildren($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfUserFoods($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfUserIngredients($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfUserJoinedVariables($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfUserParents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfUserTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfUserTagsWhereTagVariable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfUserTagsWhereTaggedVariable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfUserVariables($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable
 *     whereNumberOfUsersWherePrimaryOutcomeVariable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable
 *     whereNumberOfVariablesWhereBestCauseVariable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable
 *     whereNumberOfVariablesWhereBestEffectVariable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfVotesWhereCauseVariable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberOfVotesWhereEffectVariable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereNumberUserTaggedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereOnsetDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereOptimalValueMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereProductUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable wherePublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereReasonForAnalysis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereRecordSizeInKb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereSecondMostCommonValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereSkewness($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereStandardDeviation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereSynonyms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereThirdMostCommonValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereUpc12($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereUpc14($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereUserErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereValence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereVariableCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereVariance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereWikipediaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereWikipediaUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariable whereWpPostId($value)
 * @method static \Illuminate\Database\Query\Builder|BaseVariable withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BaseVariable withoutTrashed()
 * @mixin \Eloquent
 */
abstract class BaseVariable extends BaseModel {
	use SoftDeletes;
	public const FIELD_ADDITIONAL_META_DATA = 'additional_meta_data';
	public const FIELD_ANALYSIS_ENDED_AT = 'analysis_ended_at';
	public const FIELD_ANALYSIS_REQUESTED_AT = 'analysis_requested_at';
	public const FIELD_ANALYSIS_SETTINGS_MODIFIED_AT = 'analysis_settings_modified_at';
	public const FIELD_ANALYSIS_STARTED_AT = 'analysis_started_at';
	public const FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS = 'average_seconds_between_measurements';
	public const FIELD_BEST_AGGREGATE_CORRELATION_ID = 'best_global_variable_relationship_id';
	public const FIELD_BEST_CAUSE_VARIABLE_ID = 'best_cause_variable_id';
	public const FIELD_BEST_EFFECT_VARIABLE_ID = 'best_effect_variable_id';
	public const FIELD_BORING = 'boring';
	public const FIELD_BRAND_NAME = 'brand_name';
	public const FIELD_CAUSE_ONLY = 'cause_only';
	public const FIELD_CHARTS = 'charts';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_COMBINATION_OPERATION = 'combination_operation';
	public const FIELD_COMMON_ALIAS = 'common_alias';
	public const FIELD_COMMON_MAXIMUM_ALLOWED_DAILY_VALUE = 'common_maximum_allowed_daily_value';
	public const FIELD_COMMON_MINIMUM_ALLOWED_DAILY_VALUE = 'common_minimum_allowed_daily_value';
	public const FIELD_COMMON_MINIMUM_ALLOWED_NON_ZERO_VALUE = 'common_minimum_allowed_non_zero_value';
	public const FIELD_CONTROLLABLE = 'controllable';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_CREATOR_USER_ID = 'creator_user_id';
	public const FIELD_DATA_SOURCES_COUNT = 'data_sources_count';
	public const FIELD_DEFAULT_UNIT_ID = 'default_unit_id';
	public const FIELD_DEFAULT_VALUE = 'default_value';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_DELETION_REASON = 'deletion_reason';
	public const FIELD_DESCRIPTION = 'description';
	public const FIELD_DURATION_OF_ACTION = 'duration_of_action';
	public const FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT = 'earliest_non_tagged_measurement_start_at';
	public const FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT = 'earliest_tagged_measurement_start_at';
	public const FIELD_FILLING_TYPE = 'filling_type';
	public const FIELD_FILLING_VALUE = 'filling_value';
	public const FIELD_ID = 'id';
	public const FIELD_IMAGE_URL = 'image_url';
	public const FIELD_INFORMATIONAL_URL = 'informational_url';
	public const FIELD_INTERNAL_ERROR_MESSAGE = 'internal_error_message';
	public const FIELD_ION_ICON = 'ion_icon';
	public const FIELD_IS_GOAL = 'is_goal';
	public const FIELD_IS_PUBLIC = 'is_public';
	public const FIELD_KURTOSIS = 'kurtosis';
	public const FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT = 'latest_non_tagged_measurement_start_at';
	public const FIELD_LATEST_TAGGED_MEASUREMENT_START_AT = 'latest_tagged_measurement_start_at';
	public const FIELD_MANUAL_TRACKING = 'manual_tracking';
	public const FIELD_MAXIMUM_ALLOWED_DAILY_VALUE = 'maximum_allowed_daily_value';
	public const FIELD_MAXIMUM_ALLOWED_VALUE = 'maximum_allowed_value';
	public const FIELD_MAXIMUM_RECORDED_VALUE = 'maximum_recorded_value';
	public const FIELD_MEAN = 'mean';
	public const FIELD_MEDIAN = 'median';
	public const FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS = 'median_seconds_between_measurements';
	public const FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = 'minimum_allowed_seconds_between_measurements';
	public const FIELD_MINIMUM_ALLOWED_VALUE = 'minimum_allowed_value';
	public const FIELD_MINIMUM_RECORDED_VALUE = 'minimum_recorded_value';
	public const FIELD_MOST_COMMON_CONNECTOR_ID = 'most_common_connector_id';
	public const FIELD_MOST_COMMON_ORIGINAL_UNIT_ID = 'most_common_original_unit_id';
	public const FIELD_MOST_COMMON_SOURCE_NAME = 'most_common_source_name';
	public const FIELD_MOST_COMMON_VALUE = 'most_common_value';
	public const FIELD_NAME = 'name';
	public const FIELD_NEWEST_DATA_AT = 'newest_data_at';
	public const FIELD_NUMBER_COMMON_TAGGED_BY = 'number_common_tagged_by';
	public const FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_CAUSE = 'number_of_global_variable_relationships_as_cause';
	public const FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_EFFECT = 'number_of_global_variable_relationships_as_effect';
	public const FIELD_NUMBER_OF_APPLICATIONS_WHERE_OUTCOME_VARIABLE = 'number_of_applications_where_outcome_variable';
	public const FIELD_NUMBER_OF_APPLICATIONS_WHERE_PREDICTOR_VARIABLE = 'number_of_applications_where_predictor_variable';
	public const FIELD_NUMBER_OF_COMMON_CHILDREN = 'number_of_common_children';
	public const FIELD_NUMBER_OF_COMMON_FOODS = 'number_of_common_foods';
	public const FIELD_NUMBER_OF_COMMON_INGREDIENTS = 'number_of_common_ingredients';
	public const FIELD_NUMBER_OF_COMMON_JOINED_VARIABLES = 'number_of_common_joined_variables';
	public const FIELD_NUMBER_OF_COMMON_PARENTS = 'number_of_common_parents';
	public const FIELD_NUMBER_OF_COMMON_TAGS = 'number_of_common_tags';
	public const FIELD_NUMBER_OF_COMMON_TAGS_WHERE_TAG_VARIABLE = 'number_of_common_tags_where_tag_variable';
	public const FIELD_NUMBER_OF_COMMON_TAGS_WHERE_TAGGED_VARIABLE = 'number_of_common_tags_where_tagged_variable';
	public const FIELD_NUMBER_OF_MEASUREMENTS = 'number_of_measurements';
	public const FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES = 'number_of_outcome_case_studies';
	public const FIELD_NUMBER_OF_OUTCOME_POPULATION_STUDIES = 'number_of_outcome_population_studies';
	public const FIELD_NUMBER_OF_PREDICTOR_CASE_STUDIES = 'number_of_predictor_case_studies';
	public const FIELD_NUMBER_OF_PREDICTOR_POPULATION_STUDIES = 'number_of_predictor_population_studies';
	public const FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN = 'number_of_raw_measurements_with_tags_joins_children';
	public const FIELD_NUMBER_OF_SOFT_DELETED_MEASUREMENTS = 'number_of_soft_deleted_measurements';
	public const FIELD_NUMBER_OF_STUDIES_WHERE_CAUSE_VARIABLE = 'number_of_studies_where_cause_variable';
	public const FIELD_NUMBER_OF_STUDIES_WHERE_EFFECT_VARIABLE = 'number_of_studies_where_effect_variable';
	public const FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS = 'number_of_tracking_reminder_notifications';
	public const FIELD_NUMBER_OF_TRACKING_REMINDERS = 'number_of_tracking_reminders';
	public const FIELD_NUMBER_OF_UNIQUE_VALUES = 'number_of_unique_values';
	public const FIELD_NUMBER_OF_USER_CHILDREN = 'number_of_user_children';
	public const FIELD_NUMBER_OF_USER_FOODS = 'number_of_user_foods';
	public const FIELD_NUMBER_OF_USER_INGREDIENTS = 'number_of_user_ingredients';
	public const FIELD_NUMBER_OF_USER_JOINED_VARIABLES = 'number_of_user_joined_variables';
	public const FIELD_NUMBER_OF_USER_PARENTS = 'number_of_user_parents';
	public const FIELD_NUMBER_OF_USER_TAGS_WHERE_TAG_VARIABLE = 'number_of_user_tags_where_tag_variable';
	public const FIELD_NUMBER_OF_USER_TAGS_WHERE_TAGGED_VARIABLE = 'number_of_user_tags_where_tagged_variable';
	public const FIELD_NUMBER_OF_USER_VARIABLES = 'number_of_user_variables';
	public const FIELD_NUMBER_OF_USERS_WHERE_PRIMARY_OUTCOME_VARIABLE = 'number_of_users_where_primary_outcome_variable';
	public const FIELD_NUMBER_OF_VARIABLES_WHERE_BEST_CAUSE_VARIABLE = 'number_of_variables_where_best_cause_variable';
	public const FIELD_NUMBER_OF_VARIABLES_WHERE_BEST_EFFECT_VARIABLE = 'number_of_variables_where_best_effect_variable';
	public const FIELD_NUMBER_OF_VOTES_WHERE_CAUSE_VARIABLE = 'number_of_votes_where_cause_variable';
	public const FIELD_NUMBER_OF_VOTES_WHERE_EFFECT_VARIABLE = 'number_of_votes_where_effect_variable';
	public const FIELD_ONSET_DELAY = 'onset_delay';
	public const FIELD_OPTIMAL_VALUE_MESSAGE = 'optimal_value_message';
	public const FIELD_OUTCOME = 'outcome';
	public const FIELD_PARENT_ID = 'parent_id';
	public const FIELD_PRICE = 'price';
	public const FIELD_PRODUCT_URL = 'product_url';
	public const FIELD_REASON_FOR_ANALYSIS = 'reason_for_analysis';
	public const FIELD_RECORD_SIZE_IN_KB = 'record_size_in_kb';
	public const FIELD_SECOND_MOST_COMMON_VALUE = 'second_most_common_value';
	public const FIELD_SKEWNESS = 'skewness';
	public const FIELD_SLUG = 'slug';
	public const FIELD_SORT_ORDER = 'sort_order';
	public const FIELD_STANDARD_DEVIATION = 'standard_deviation';
	public const FIELD_STATUS = 'status';
	public const FIELD_SYNONYMS = 'synonyms';
	public const FIELD_THIRD_MOST_COMMON_VALUE = 'third_most_common_value';
	public const FIELD_UPC_12 = 'upc_12';
	public const FIELD_UPC_14 = 'upc_14';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ERROR_MESSAGE = 'user_error_message';
	public const FIELD_VALENCE = 'valence';
	public const FIELD_VARIABLE_CATEGORY_ID = 'variable_category_id';
	public const FIELD_VARIANCE = 'variance';
	public const FIELD_WIKIPEDIA_TITLE = 'wikipedia_title';
	public const FIELD_WIKIPEDIA_URL = 'wikipedia_url';
	public const FIELD_WP_POST_ID = 'wp_post_id';
	public const TABLE = 'variables';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT => 'datetime',
        self::FIELD_NEWEST_DATA_AT => 'datetime',
        self::FIELD_ANALYSIS_REQUESTED_AT => 'datetime',
        self::FIELD_ANALYSIS_STARTED_AT => 'datetime',
        self::FIELD_ANALYSIS_ENDED_AT => 'datetime',
        self::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT => 'datetime',
        self::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT => 'datetime',
        self::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT => 'datetime',
        self::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT => 'datetime',
		self::FIELD_ADDITIONAL_META_DATA => 'json',
		self::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS => 'int',
		self::FIELD_BEST_AGGREGATE_CORRELATION_ID => 'int',
		self::FIELD_BEST_CAUSE_VARIABLE_ID => 'int',
		self::FIELD_BEST_EFFECT_VARIABLE_ID => 'int',
		self::FIELD_BORING => 'bool',
		self::FIELD_BRAND_NAME => 'string',
		self::FIELD_CAUSE_ONLY => 'bool',
		self::FIELD_CHARTS => 'json',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_COMBINATION_OPERATION => 'string',
		self::FIELD_COMMON_ALIAS => 'string',
		self::FIELD_COMMON_MAXIMUM_ALLOWED_DAILY_VALUE => 'float',
		self::FIELD_COMMON_MINIMUM_ALLOWED_DAILY_VALUE => 'float',
		self::FIELD_COMMON_MINIMUM_ALLOWED_NON_ZERO_VALUE => 'float',
		self::FIELD_CONTROLLABLE => 'bool',
		self::FIELD_CREATOR_USER_ID => 'int',
		self::FIELD_DATA_SOURCES_COUNT => 'json',
		self::FIELD_DEFAULT_UNIT_ID => 'int',
		self::FIELD_DEFAULT_VALUE => 'float',
		self::FIELD_DELETION_REASON => 'string',
		self::FIELD_DESCRIPTION => 'string',
		self::FIELD_DURATION_OF_ACTION => 'int',
		self::FIELD_FILLING_TYPE => 'string',
		self::FIELD_FILLING_VALUE => 'float',
		self::FIELD_ID => 'int',
		self::FIELD_IMAGE_URL => 'string',
		self::FIELD_INFORMATIONAL_URL => 'string',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'string',
		self::FIELD_ION_ICON => 'string',
		self::FIELD_IS_GOAL => 'bool',
		self::FIELD_IS_PUBLIC => 'bool',
		self::FIELD_KURTOSIS => 'float',
		self::FIELD_MANUAL_TRACKING => 'bool',
		self::FIELD_MAXIMUM_ALLOWED_DAILY_VALUE => 'float',
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
		self::FIELD_MOST_COMMON_SOURCE_NAME => 'string',
		self::FIELD_MOST_COMMON_VALUE => 'float',
		self::FIELD_NAME => 'string',
		self::FIELD_NUMBER_COMMON_TAGGED_BY => 'int',
		self::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_CAUSE => 'int',
		self::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_EFFECT => 'int',
		self::FIELD_NUMBER_OF_APPLICATIONS_WHERE_OUTCOME_VARIABLE => 'int',
		self::FIELD_NUMBER_OF_APPLICATIONS_WHERE_PREDICTOR_VARIABLE => 'int',
		self::FIELD_NUMBER_OF_COMMON_CHILDREN => 'int',
		self::FIELD_NUMBER_OF_COMMON_FOODS => 'int',
		self::FIELD_NUMBER_OF_COMMON_INGREDIENTS => 'int',
		self::FIELD_NUMBER_OF_COMMON_JOINED_VARIABLES => 'int',
		self::FIELD_NUMBER_OF_COMMON_PARENTS => 'int',
		self::FIELD_NUMBER_OF_COMMON_TAGS => 'int',
		self::FIELD_NUMBER_OF_COMMON_TAGS_WHERE_TAGGED_VARIABLE => 'int',
		self::FIELD_NUMBER_OF_COMMON_TAGS_WHERE_TAG_VARIABLE => 'int',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'int',
		self::FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES => 'int',
		self::FIELD_NUMBER_OF_OUTCOME_POPULATION_STUDIES => 'int',
		self::FIELD_NUMBER_OF_PREDICTOR_CASE_STUDIES => 'int',
		self::FIELD_NUMBER_OF_PREDICTOR_POPULATION_STUDIES => 'int',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN => 'int',
		self::FIELD_NUMBER_OF_SOFT_DELETED_MEASUREMENTS => 'int',
		self::FIELD_NUMBER_OF_STUDIES_WHERE_CAUSE_VARIABLE => 'int',
		self::FIELD_NUMBER_OF_STUDIES_WHERE_EFFECT_VARIABLE => 'int',
		self::FIELD_NUMBER_OF_TRACKING_REMINDERS => 'int',
		self::FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS => 'int',
		self::FIELD_NUMBER_OF_UNIQUE_VALUES => 'int',
		self::FIELD_NUMBER_OF_USERS_WHERE_PRIMARY_OUTCOME_VARIABLE => 'int',
		self::FIELD_NUMBER_OF_USER_CHILDREN => 'int',
		self::FIELD_NUMBER_OF_USER_FOODS => 'int',
		self::FIELD_NUMBER_OF_USER_INGREDIENTS => 'int',
		self::FIELD_NUMBER_OF_USER_JOINED_VARIABLES => 'int',
		self::FIELD_NUMBER_OF_USER_PARENTS => 'int',
		self::FIELD_NUMBER_OF_USER_TAGS_WHERE_TAGGED_VARIABLE => 'int',
		self::FIELD_NUMBER_OF_USER_TAGS_WHERE_TAG_VARIABLE => 'int',
		self::FIELD_NUMBER_OF_USER_VARIABLES => 'int',
		self::FIELD_NUMBER_OF_VARIABLES_WHERE_BEST_CAUSE_VARIABLE => 'int',
		self::FIELD_NUMBER_OF_VARIABLES_WHERE_BEST_EFFECT_VARIABLE => 'int',
		self::FIELD_NUMBER_OF_VOTES_WHERE_CAUSE_VARIABLE => 'int',
		self::FIELD_NUMBER_OF_VOTES_WHERE_EFFECT_VARIABLE => 'int',
		self::FIELD_ONSET_DELAY => 'int',
		self::FIELD_OPTIMAL_VALUE_MESSAGE => 'string',
		self::FIELD_OUTCOME => 'bool',
		self::FIELD_PARENT_ID => 'int',
		self::FIELD_PRICE => 'float',
		self::FIELD_PRODUCT_URL => 'string',
		self::FIELD_REASON_FOR_ANALYSIS => 'string',
		self::FIELD_RECORD_SIZE_IN_KB => 'int',
		self::FIELD_SECOND_MOST_COMMON_VALUE => 'float',
		self::FIELD_SKEWNESS => 'float',
		self::FIELD_SORT_ORDER => 'int',
		self::FIELD_STANDARD_DEVIATION => 'float',
		self::FIELD_STATUS => 'string',
		self::FIELD_SYNONYMS => 'string',
		self::FIELD_THIRD_MOST_COMMON_VALUE => 'float',
		self::FIELD_UPC_12 => 'string',
		self::FIELD_UPC_14 => 'string',
		self::FIELD_USER_ERROR_MESSAGE => 'string',
		self::FIELD_VALENCE => 'string',
		self::FIELD_VARIABLE_CATEGORY_ID => 'int',
		self::FIELD_VARIANCE => 'float',
		self::FIELD_WIKIPEDIA_TITLE => 'string',
		self::FIELD_WIKIPEDIA_URL => 'string',
		self::FIELD_WP_POST_ID => 'int',	];
	protected array $rules = [
		self::FIELD_ADDITIONAL_META_DATA => 'nullable|max:65535',
		self::FIELD_ANALYSIS_ENDED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_REQUESTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_STARTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_BEST_AGGREGATE_CORRELATION_ID => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_BEST_CAUSE_VARIABLE_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_BEST_EFFECT_VARIABLE_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_BORING => 'nullable|boolean',
		self::FIELD_BRAND_NAME => 'nullable|max:125',
		self::FIELD_CAUSE_ONLY => 'nullable|boolean',
		self::FIELD_CHARTS => 'nullable|json',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_COMBINATION_OPERATION => 'nullable',
		self::FIELD_COMMON_ALIAS => 'nullable|max:125',
		self::FIELD_COMMON_MAXIMUM_ALLOWED_DAILY_VALUE => 'nullable|numeric',
		self::FIELD_COMMON_MINIMUM_ALLOWED_DAILY_VALUE => 'nullable|numeric',
		self::FIELD_COMMON_MINIMUM_ALLOWED_NON_ZERO_VALUE => 'nullable|numeric',
		self::FIELD_CONTROLLABLE => 'nullable|boolean',
		self::FIELD_CREATOR_USER_ID => 'required|numeric|min:0',
		self::FIELD_DATA_SOURCES_COUNT => 'nullable|max:65535',
		self::FIELD_DEFAULT_UNIT_ID => 'required|integer|min:0|max:65535',
		self::FIELD_DEFAULT_VALUE => 'nullable|numeric',
		self::FIELD_DELETION_REASON => 'nullable|max:280',
		self::FIELD_DESCRIPTION => 'nullable|max:65535',
		self::FIELD_DURATION_OF_ACTION => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_FILLING_TYPE => 'nullable',
		self::FIELD_FILLING_VALUE => 'nullable|numeric',
		self::FIELD_IMAGE_URL => 'nullable|max:2083',
		self::FIELD_INFORMATIONAL_URL => 'nullable|max:2083',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'nullable|max:65535',
		self::FIELD_ION_ICON => 'nullable|max:40',
		self::FIELD_IS_GOAL => 'nullable|boolean',
		self::FIELD_IS_PUBLIC => 'nullable|boolean',
		self::FIELD_KURTOSIS => 'nullable|numeric',
		self::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_MANUAL_TRACKING => 'nullable|boolean',
		self::FIELD_MAXIMUM_ALLOWED_DAILY_VALUE => 'nullable|numeric',
		self::FIELD_MAXIMUM_ALLOWED_VALUE => 'nullable|numeric',
		self::FIELD_MAXIMUM_RECORDED_VALUE => 'nullable|numeric',
		self::FIELD_MEAN => 'nullable|numeric',
		self::FIELD_MEDIAN => 'nullable|numeric',
		self::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_MINIMUM_ALLOWED_VALUE => 'nullable|numeric',
		self::FIELD_MINIMUM_RECORDED_VALUE => 'nullable|numeric',
		self::FIELD_MOST_COMMON_CONNECTOR_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_MOST_COMMON_ORIGINAL_UNIT_ID => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_MOST_COMMON_SOURCE_NAME => 'nullable|max:255',
		self::FIELD_MOST_COMMON_VALUE => 'nullable|numeric',
		self::FIELD_NAME => 'required|max:125|unique:variables,name',
		self::FIELD_NEWEST_DATA_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_NUMBER_COMMON_TAGGED_BY => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_CAUSE => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_EFFECT => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_APPLICATIONS_WHERE_OUTCOME_VARIABLE => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_APPLICATIONS_WHERE_PREDICTOR_VARIABLE => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_COMMON_CHILDREN => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_COMMON_FOODS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_COMMON_INGREDIENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_COMMON_JOINED_VARIABLES => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_COMMON_PARENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_COMMON_TAGS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_COMMON_TAGS_WHERE_TAGGED_VARIABLE => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_COMMON_TAGS_WHERE_TAG_VARIABLE => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_OUTCOME_POPULATION_STUDIES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_PREDICTOR_CASE_STUDIES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_PREDICTOR_POPULATION_STUDIES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_SOFT_DELETED_MEASUREMENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_STUDIES_WHERE_CAUSE_VARIABLE => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_STUDIES_WHERE_EFFECT_VARIABLE => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_TRACKING_REMINDERS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_UNIQUE_VALUES => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_USERS_WHERE_PRIMARY_OUTCOME_VARIABLE => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_USER_CHILDREN => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_USER_FOODS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_USER_INGREDIENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_USER_JOINED_VARIABLES => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_USER_PARENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_USER_TAGS_WHERE_TAGGED_VARIABLE => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_USER_TAGS_WHERE_TAG_VARIABLE => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_USER_VARIABLES => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_VARIABLES_WHERE_BEST_CAUSE_VARIABLE => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_VARIABLES_WHERE_BEST_EFFECT_VARIABLE => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_VOTES_WHERE_CAUSE_VARIABLE => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_VOTES_WHERE_EFFECT_VARIABLE => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_ONSET_DELAY => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_OPTIMAL_VALUE_MESSAGE => 'nullable|max:500',
		self::FIELD_OUTCOME => 'nullable|boolean',
		self::FIELD_PARENT_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_PRICE => 'nullable|numeric',
		self::FIELD_PRODUCT_URL => 'nullable|max:2083',
		self::FIELD_REASON_FOR_ANALYSIS => 'nullable|max:255',
		self::FIELD_RECORD_SIZE_IN_KB => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_SECOND_MOST_COMMON_VALUE => 'nullable|numeric',
		self::FIELD_SKEWNESS => 'nullable|numeric',
		self::FIELD_SORT_ORDER => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_STANDARD_DEVIATION => 'nullable|numeric',
		self::FIELD_STATUS => 'required|max:25',
		self::FIELD_SYNONYMS => 'nullable|max:600',
		self::FIELD_THIRD_MOST_COMMON_VALUE => 'nullable|numeric',
		self::FIELD_UPC_12 => 'nullable|max:255',
		self::FIELD_UPC_14 => 'nullable|max:255',
		self::FIELD_USER_ERROR_MESSAGE => 'nullable|max:65535',
		self::FIELD_VALENCE => 'nullable',
		self::FIELD_VARIABLE_CATEGORY_ID => 'required|boolean',
		self::FIELD_VARIANCE => 'nullable|numeric',
		self::FIELD_WIKIPEDIA_TITLE => 'nullable|max:100',
		self::FIELD_WIKIPEDIA_URL => 'nullable|max:2083',
		self::FIELD_WP_POST_ID => 'nullable|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_NAME => 'User-defined variable display name',
		self::FIELD_NUMBER_OF_USER_VARIABLES => 'Number of variables',
		self::FIELD_VARIABLE_CATEGORY_ID => 'Variable category ID',
		self::FIELD_DEFAULT_UNIT_ID => 'ID of the default unit for the variable',
		self::FIELD_DEFAULT_VALUE => '',
		self::FIELD_CAUSE_ONLY => 'A value of 1 indicates that this variable is generally a cause in a causal relationship.  An example of a causeOnly variable would be a variable such as Cloud Cover which would generally not be influenced by the behaviour of the user',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_COMBINATION_OPERATION => 'How to combine values of this variable (for instance, to see a summary of the values over a month) SUM or MEAN',
		self::FIELD_COMMON_ALIAS => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DESCRIPTION => '',
		self::FIELD_DURATION_OF_ACTION => 'How long the effect of a measurement in this variable lasts',
		self::FIELD_FILLING_VALUE => 'Value for replacing null measurements',
		self::FIELD_IMAGE_URL => '',
		self::FIELD_INFORMATIONAL_URL => '',
		self::FIELD_ION_ICON => '',
		self::FIELD_KURTOSIS => 'Kurtosis',
		self::FIELD_MAXIMUM_ALLOWED_VALUE => 'Maximum reasonable value for a single measurement for this variable in the default unit. ',
		self::FIELD_MAXIMUM_RECORDED_VALUE => 'Maximum recorded value of this variable',
		self::FIELD_MEAN => 'Mean',
		self::FIELD_MEDIAN => 'Median',
		self::FIELD_MINIMUM_ALLOWED_VALUE => 'Minimum reasonable value for this variable (uses default unit)',
		self::FIELD_MINIMUM_RECORDED_VALUE => 'Minimum recorded value of this variable',
		self::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_CAUSE => 'Number of global variable relationships for which this variable is the cause variable',
		self::FIELD_MOST_COMMON_ORIGINAL_UNIT_ID => 'Most common Unit ID',
		self::FIELD_MOST_COMMON_VALUE => 'Most common value',
		self::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_EFFECT => 'Number of global variable relationships for which this variable is the effect variable',
		self::FIELD_NUMBER_OF_UNIQUE_VALUES => 'Number of unique values',
		self::FIELD_ONSET_DELAY => 'How long it takes for a measurement in this variable to take effect',
		self::FIELD_OUTCOME => 'Outcome variables (those with `outcome` == 1) are variables for which a human would generally want to identify the influencing factors.  These include symptoms of illness, physique, mood, cognitive performance, etc.  Generally correlation calculations are only performed on outcome variables.',
		self::FIELD_PARENT_ID => 'ID of the parent variable if this variable has any parent',
		self::FIELD_PRICE => 'Price',
		self::FIELD_PRODUCT_URL => 'Product URL',
		self::FIELD_SECOND_MOST_COMMON_VALUE => '',
		self::FIELD_SKEWNESS => 'Skewness',
		self::FIELD_STANDARD_DEVIATION => 'Standard Deviation',
		self::FIELD_STATUS => 'status',
		self::FIELD_THIRD_MOST_COMMON_VALUE => '',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_VARIANCE => 'Variance',
		self::FIELD_MOST_COMMON_CONNECTOR_ID => '',
		self::FIELD_SYNONYMS => 'The primary variable name and any synonyms for it. This field should be used for non-specific variable searches.',
		self::FIELD_WIKIPEDIA_URL => '',
		self::FIELD_BRAND_NAME => '',
		self::FIELD_VALENCE => '',
		self::FIELD_WIKIPEDIA_TITLE => '',
		self::FIELD_NUMBER_OF_TRACKING_REMINDERS => '',
		self::FIELD_UPC_12 => '',
		self::FIELD_UPC_14 => '',
		self::FIELD_NUMBER_COMMON_TAGGED_BY => '',
		self::FIELD_NUMBER_OF_COMMON_TAGS => '',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_MOST_COMMON_SOURCE_NAME => '',
		self::FIELD_DATA_SOURCES_COUNT => 'Array of connector or client measurement data source names as key with number of users as value',
		self::FIELD_OPTIMAL_VALUE_MESSAGE => '',
		self::FIELD_BEST_CAUSE_VARIABLE_ID => '',
		self::FIELD_BEST_EFFECT_VARIABLE_ID => '',
		self::FIELD_COMMON_MAXIMUM_ALLOWED_DAILY_VALUE => '',
		self::FIELD_COMMON_MINIMUM_ALLOWED_DAILY_VALUE => '',
		self::FIELD_COMMON_MINIMUM_ALLOWED_NON_ZERO_VALUE => '',
		self::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => '',
		self::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS => '',
		self::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS => '',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN => '',
		self::FIELD_ADDITIONAL_META_DATA => '',
		self::FIELD_MANUAL_TRACKING => '',
		self::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT => 'datetime',
		self::FIELD_NEWEST_DATA_AT => 'datetime',
		self::FIELD_ANALYSIS_REQUESTED_AT => 'datetime',
		self::FIELD_REASON_FOR_ANALYSIS => '',
		self::FIELD_ANALYSIS_STARTED_AT => 'datetime',
		self::FIELD_ANALYSIS_ENDED_AT => 'datetime',
		self::FIELD_USER_ERROR_MESSAGE => '',
		self::FIELD_INTERNAL_ERROR_MESSAGE => '',
		self::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT => 'datetime',
		self::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT => 'datetime',
		self::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT => 'datetime',
		self::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT => 'datetime',
		self::FIELD_WP_POST_ID => '',
		self::FIELD_NUMBER_OF_SOFT_DELETED_MEASUREMENTS => 'Formula: update variables v
                inner join (
                    select measurements.variable_id, count(measurements.id) as number_of_soft_deleted_measurements
                    from measurements
                    where measurements.deleted_at is not null
                    group by measurements.variable_id
                    ) m on v.id = m.variable_id
                set v.number_of_soft_deleted_measurements = m.number_of_soft_deleted_measurements
            ',
		self::FIELD_CHARTS => '',
		self::FIELD_CREATOR_USER_ID => '',
		self::FIELD_BEST_AGGREGATE_CORRELATION_ID => '',
		self::FIELD_FILLING_TYPE => '',
		self::FIELD_NUMBER_OF_OUTCOME_POPULATION_STUDIES => 'Number of Global Population Studies for this Cause Variable.
                [Formula:
                    update variables
                        left join (
                            select count(id) as total, cause_variable_id
                            from global_variable_relationships
                            group by cause_variable_id
                        )
                        as grouped on variables.id = grouped.cause_variable_id
                    set variables.number_of_outcome_population_studies = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_PREDICTOR_POPULATION_STUDIES => 'Number of Global Population Studies for this Effect Variable.
                [Formula:
                    update variables
                        left join (
                            select count(id) as total, effect_variable_id
                            from global_variable_relationships
                            group by effect_variable_id
                        )
                        as grouped on variables.id = grouped.effect_variable_id
                    set variables.number_of_predictor_population_studies = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_APPLICATIONS_WHERE_OUTCOME_VARIABLE => 'Number of Applications for this Outcome Variable.
                [Formula:
                    update variables
                        left join (
                            select count(id) as total, outcome_variable_id
                            from applications
                            group by outcome_variable_id
                        )
                        as grouped on variables.id = grouped.outcome_variable_id
                    set variables.number_of_applications_where_outcome_variable = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_APPLICATIONS_WHERE_PREDICTOR_VARIABLE => 'Number of Applications for this Predictor Variable.
                [Formula:
                    update variables
                        left join (
                            select count(id) as total, predictor_variable_id
                            from applications
                            group by predictor_variable_id
                        )
                        as grouped on variables.id = grouped.predictor_variable_id
                    set variables.number_of_applications_where_predictor_variable = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_COMMON_TAGS_WHERE_TAG_VARIABLE => 'Number of Common Tags for this Tag Variable.
                [Formula:
                    update variables
                        left join (
                            select count(id) as total, tag_variable_id
                            from common_tags
                            group by tag_variable_id
                        )
                        as grouped on variables.id = grouped.tag_variable_id
                    set variables.number_of_common_tags_where_tag_variable = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_COMMON_TAGS_WHERE_TAGGED_VARIABLE => 'Number of Common Tags for this Tagged Variable.
                [Formula:
                    update variables
                        left join (
                            select count(id) as total, tagged_variable_id
                            from common_tags
                            group by tagged_variable_id
                        )
                        as grouped on variables.id = grouped.tagged_variable_id
                    set variables.number_of_common_tags_where_tagged_variable = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES => 'Number of Individual Case Studies for this Cause Variable.
                [Formula:
                    update variables
                        left join (
                            select count(id) as total, cause_variable_id
                            from user_variable_relationships
                            group by cause_variable_id
                        )
                        as grouped on variables.id = grouped.cause_variable_id
                    set variables.number_of_outcome_case_studies = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_PREDICTOR_CASE_STUDIES => 'Number of Individual Case Studies for this Effect Variable.
                    [Formula: update variables
                        left join (
                            select count(id) as total, effect_variable_id
                            from user_variable_relationships
                            group by effect_variable_id
                        )
                        as grouped on variables.id = grouped.effect_variable_id
                    set variables.number_of_predictor_case_studies = count(grouped.total)]',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'Number of Measurements for this Variable.
                    [Formula: update variables
                        left join (
                            select count(id) as total, variable_id
                            from measurements
                            group by variable_id
                        )
                        as grouped on variables.id = grouped.variable_id
                    set variables.number_of_measurements = count(grouped.total)]',
		self::FIELD_NUMBER_OF_STUDIES_WHERE_CAUSE_VARIABLE => 'Number of Studies for this Cause Variable.
                    [Formula: update variables
                        left join (
                            select count(id) as total, cause_variable_id
                            from studies
                            group by cause_variable_id
                        )
                        as grouped on variables.id = grouped.cause_variable_id
                    set variables.number_of_studies_where_cause_variable = count(grouped.total)]',
		self::FIELD_NUMBER_OF_STUDIES_WHERE_EFFECT_VARIABLE => 'Number of Studies for this Effect Variable.
                    [Formula: update variables
                        left join (
                            select count(id) as total, effect_variable_id
                            from studies
                            group by effect_variable_id
                        )
                        as grouped on variables.id = grouped.effect_variable_id
                    set variables.number_of_studies_where_effect_variable = count(grouped.total)]',
		self::FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS => 'Number of Tracking Reminder Notifications for this Variable.
                    [Formula: update variables
                        left join (
                            select count(id) as total, variable_id
                            from tracking_reminder_notifications
                            group by variable_id
                        )
                        as grouped on variables.id = grouped.variable_id
                    set variables.number_of_tracking_reminder_notifications = count(grouped.total)]',
		self::FIELD_NUMBER_OF_USER_TAGS_WHERE_TAG_VARIABLE => 'Number of User Tags for this Tag Variable.
                    [Formula: update variables
                        left join (
                            select count(id) as total, tag_variable_id
                            from user_tags
                            group by tag_variable_id
                        )
                        as grouped on variables.id = grouped.tag_variable_id
                    set variables.number_of_user_tags_where_tag_variable = count(grouped.total)]',
		self::FIELD_NUMBER_OF_USER_TAGS_WHERE_TAGGED_VARIABLE => 'Number of User Tags for this Tagged Variable.
                    [Formula: update variables
                        left join (
                            select count(id) as total, tagged_variable_id
                            from user_tags
                            group by tagged_variable_id
                        )
                        as grouped on variables.id = grouped.tagged_variable_id
                    set variables.number_of_user_tags_where_tagged_variable = count(grouped.total)]',
		self::FIELD_NUMBER_OF_VARIABLES_WHERE_BEST_CAUSE_VARIABLE => 'Number of Variables for this Best Cause Variable.
                    [Formula: update variables
                        left join (
                            select count(id) as total, best_cause_variable_id
                            from variables
                            group by best_cause_variable_id
                        )
                        as grouped on variables.id = grouped.best_cause_variable_id
                    set variables.number_of_variables_where_best_cause_variable = count(grouped.total)]',
		self::FIELD_NUMBER_OF_VARIABLES_WHERE_BEST_EFFECT_VARIABLE => 'Number of Variables for this Best Effect Variable.
                    [Formula: update variables
                        left join (
                            select count(id) as total, best_effect_variable_id
                            from variables
                            group by best_effect_variable_id
                        )
                        as grouped on variables.id = grouped.best_effect_variable_id
                    set variables.number_of_variables_where_best_effect_variable = count(grouped.total)]',
		self::FIELD_NUMBER_OF_VOTES_WHERE_CAUSE_VARIABLE => 'Number of Votes for this Cause Variable.
                    [Formula: update variables
                        left join (
                            select count(id) as total, cause_variable_id
                            from votes
                            group by cause_variable_id
                        )
                        as grouped on variables.id = grouped.cause_variable_id
                    set variables.number_of_votes_where_cause_variable = count(grouped.total)]',
		self::FIELD_NUMBER_OF_VOTES_WHERE_EFFECT_VARIABLE => 'Number of Votes for this Effect Variable.
                    [Formula: update variables
                        left join (
                            select count(id) as total, effect_variable_id
                            from votes
                            group by effect_variable_id
                        )
                        as grouped on variables.id = grouped.effect_variable_id
                    set variables.number_of_votes_where_effect_variable = count(grouped.total)]',
		self::FIELD_NUMBER_OF_USERS_WHERE_PRIMARY_OUTCOME_VARIABLE => 'Number of Users for this Primary Outcome Variable.
                    [Formula: update variables
                        left join (
                            select count(ID) as total, primary_outcome_variable_id
                            from wp_users
                            group by primary_outcome_variable_id
                        )
                        as grouped on variables.id = grouped.primary_outcome_variable_id
                    set variables.number_of_users_where_primary_outcome_variable = count(grouped.total)]',
		self::FIELD_DELETION_REASON => 'The reason the variable was deleted.',
		self::FIELD_MAXIMUM_ALLOWED_DAILY_VALUE => 'The maximum allowed value in the default unit for measurements aggregated over a single day. ',
		self::FIELD_RECORD_SIZE_IN_KB => '',
		self::FIELD_NUMBER_OF_COMMON_JOINED_VARIABLES => 'Joined variables are duplicate variables measuring the same thing. ',
		self::FIELD_NUMBER_OF_COMMON_INGREDIENTS => 'Measurements for this variable can be used to synthetically generate ingredient measurements. ',
		self::FIELD_NUMBER_OF_COMMON_FOODS => 'Measurements for this ingredient variable can be synthetically generate by food measurements. ',
		self::FIELD_NUMBER_OF_COMMON_CHILDREN => 'Measurements for this parent category variable can be synthetically generated by measurements from its child variables. ',
		self::FIELD_NUMBER_OF_COMMON_PARENTS => 'Measurements for this parent category variable can be synthetically generated by measurements from its child variables. ',
		self::FIELD_NUMBER_OF_USER_JOINED_VARIABLES => 'Joined variables are duplicate variables measuring the same thing. This only includes ones created by users. ',
		self::FIELD_NUMBER_OF_USER_INGREDIENTS => 'Measurements for this variable can be used to synthetically generate ingredient measurements. This only includes ones created by users. ',
		self::FIELD_NUMBER_OF_USER_FOODS => 'Measurements for this ingredient variable can be synthetically generate by food measurements. This only includes ones created by users. ',
		self::FIELD_NUMBER_OF_USER_CHILDREN => 'Measurements for this parent category variable can be synthetically generated by measurements from its child variables. This only includes ones created by users. ',
		self::FIELD_NUMBER_OF_USER_PARENTS => 'Measurements for this parent category variable can be synthetically generated by measurements from its child variables. This only includes ones created by users. ',
		self::FIELD_IS_PUBLIC => '',
		self::FIELD_SORT_ORDER => '',
		self::FIELD_IS_GOAL => 'The effect of a food on the severity of a symptom is useful because you can control the predictor directly. However, the effect of a symptom on the foods you eat is not very useful.  The foods you eat are not generally an objective end in themselves. ',
		self::FIELD_CONTROLLABLE => 'You can control the foods you eat directly. However, symptom severity or weather is not directly controllable. ',
		self::FIELD_BORING => 'The variable is boring if the average person would not be interested in its causes or effects. ',
	];
	protected array $relationshipInfo = [
		'best_global_variable_relationship' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => GlobalVariableRelationship::class,
			'foreignKeyColumnName' => 'best_global_variable_relationship_id',
			'foreignKey' => Variable::FIELD_BEST_AGGREGATE_CORRELATION_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => GlobalVariableRelationship::FIELD_ID,
			'ownerKeyColumnName' => 'best_global_variable_relationship_id',
			'ownerKey' => Variable::FIELD_BEST_AGGREGATE_CORRELATION_ID,
			'methodName' => 'best_global_variable_relationship',
		],
		'best_cause_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'best_cause_variable_id',
			'foreignKey' => Variable::FIELD_BEST_CAUSE_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'best_cause_variable_id',
			'ownerKey' => Variable::FIELD_BEST_CAUSE_VARIABLE_ID,
			'methodName' => 'best_cause_variable',
		],
		'best_effect_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'best_effect_variable_id',
			'foreignKey' => Variable::FIELD_BEST_EFFECT_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'best_effect_variable_id',
			'ownerKey' => Variable::FIELD_BEST_EFFECT_VARIABLE_ID,
			'methodName' => 'best_effect_variable',
		],
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => Variable::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => Variable::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'default_unit' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Unit::class,
			'foreignKeyColumnName' => 'default_unit_id',
			'foreignKey' => Variable::FIELD_DEFAULT_UNIT_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Unit::FIELD_ID,
			'ownerKeyColumnName' => 'default_unit_id',
			'ownerKey' => Variable::FIELD_DEFAULT_UNIT_ID,
			'methodName' => 'default_unit',
		],
		'variable_category' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => VariableCategory::class,
			'foreignKeyColumnName' => 'variable_category_id',
			'foreignKey' => Variable::FIELD_VARIABLE_CATEGORY_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => VariableCategory::FIELD_ID,
			'ownerKeyColumnName' => 'variable_category_id',
			'ownerKey' => Variable::FIELD_VARIABLE_CATEGORY_ID,
			'methodName' => 'variable_category',
		],
		'wp_post' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => WpPost::class,
			'foreignKeyColumnName' => 'wp_post_id',
			'foreignKey' => Variable::FIELD_WP_POST_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => WpPost::FIELD_ID,
			'ownerKeyColumnName' => 'wp_post_id',
			'ownerKey' => Variable::FIELD_WP_POST_ID,
			'methodName' => 'wp_post',
		],
		'global_variable_relationships_where_cause_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => GlobalVariableRelationship::class,
			'foreignKey' => GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID,
			'localKey' => GlobalVariableRelationship::FIELD_ID,
			'methodName' => 'global_variable_relationships_where_cause_variable',
		],
		'global_variable_relationships_where_effect_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => GlobalVariableRelationship::class,
			'foreignKey' => GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID,
			'localKey' => GlobalVariableRelationship::FIELD_ID,
			'methodName' => 'global_variable_relationships_where_effect_variable',
		],
		'applications_where_outcome_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Application::class,
			'foreignKey' => Application::FIELD_OUTCOME_VARIABLE_ID,
			'localKey' => Application::FIELD_ID,
			'methodName' => 'applications_where_outcome_variable',
		],
		'applications_where_predictor_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Application::class,
			'foreignKey' => Application::FIELD_PREDICTOR_VARIABLE_ID,
			'localKey' => Application::FIELD_ID,
			'methodName' => 'applications_where_predictor_variable',
		],
		'common_tags_where_tag_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CommonTag::class,
			'foreignKey' => CommonTag::FIELD_TAG_VARIABLE_ID,
			'localKey' => CommonTag::FIELD_ID,
			'methodName' => 'common_tags_where_tag_variable',
		],
		'common_tags_where_tagged_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CommonTag::class,
			'foreignKey' => CommonTag::FIELD_TAGGED_VARIABLE_ID,
			'localKey' => CommonTag::FIELD_ID,
			'methodName' => 'common_tags_where_tagged_variable',
		],
		'correlation_causality_votes_where_cause_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CorrelationCausalityVote::class,
			'foreignKey' => CorrelationCausalityVote::FIELD_CAUSE_VARIABLE_ID,
			'localKey' => CorrelationCausalityVote::FIELD_ID,
			'methodName' => 'correlation_causality_votes_where_cause_variable',
		],
		'correlation_causality_votes_where_effect_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CorrelationCausalityVote::class,
			'foreignKey' => CorrelationCausalityVote::FIELD_EFFECT_VARIABLE_ID,
			'localKey' => CorrelationCausalityVote::FIELD_ID,
			'methodName' => 'correlation_causality_votes_where_effect_variable',
		],
		'correlation_usefulness_votes_where_cause_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CorrelationUsefulnessVote::class,
			'foreignKey' => CorrelationUsefulnessVote::FIELD_CAUSE_VARIABLE_ID,
			'localKey' => CorrelationUsefulnessVote::FIELD_ID,
			'methodName' => 'correlation_usefulness_votes_where_cause_variable',
		],
		'correlation_usefulness_votes_where_effect_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CorrelationUsefulnessVote::class,
			'foreignKey' => CorrelationUsefulnessVote::FIELD_EFFECT_VARIABLE_ID,
			'localKey' => CorrelationUsefulnessVote::FIELD_ID,
			'methodName' => 'correlation_usefulness_votes_where_effect_variable',
		],
		'correlations_where_cause_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariableRelationship::class,
			'foreignKey' => UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID,
			'localKey' => UserVariableRelationship::FIELD_ID,
			'methodName' => 'correlations_where_cause_variable',
		],
		'correlations_where_effect_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariableRelationship::class,
			'foreignKey' => UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID,
			'localKey' => UserVariableRelationship::FIELD_ID,
			'methodName' => 'correlations_where_effect_variable',
		],
		'ct_side_effect' => [
			'relationshipType' => 'HasOne',
			'qualifiedUserClassName' => CtSideEffect::class,
			'foreignKey' => CtSideEffect::FIELD_VARIABLE_ID,
			'localKey' => CtSideEffect::FIELD_ID,
			'methodName' => 'ct_side_effect',
		],
		'ct_treatment_side_effects_where_side_effect_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CtTreatmentSideEffect::class,
			'foreignKey' => CtTreatmentSideEffect::FIELD_SIDE_EFFECT_VARIABLE_ID,
			'localKey' => CtTreatmentSideEffect::FIELD_ID,
			'methodName' => 'ct_treatment_side_effects_where_side_effect_variable',
		],
		'ct_treatment_side_effects_where_treatment_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CtTreatmentSideEffect::class,
			'foreignKey' => CtTreatmentSideEffect::FIELD_TREATMENT_VARIABLE_ID,
			'localKey' => CtTreatmentSideEffect::FIELD_ID,
			'methodName' => 'ct_treatment_side_effects_where_treatment_variable',
		],
		'ctg_condition' => [
			'relationshipType' => 'HasOne',
			'qualifiedUserClassName' => CtgCondition::class,
			'foreignKey' => CtgCondition::FIELD_VARIABLE_ID,
			'localKey' => CtgCondition::FIELD_ID,
			'methodName' => 'ctg_condition',
		],
		'ctg_intervention' => [
			'relationshipType' => 'HasOne',
			'qualifiedUserClassName' => CtgIntervention::class,
			'foreignKey' => CtgIntervention::FIELD_VARIABLE_ID,
			'localKey' => CtgIntervention::FIELD_ID,
			'methodName' => 'ctg_intervention',
		],
		'measurements' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Measurement::class,
			'foreignKey' => Measurement::FIELD_VARIABLE_ID,
			'localKey' => Measurement::FIELD_ID,
			'methodName' => 'measurements',
		],
		'studies_where_cause_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Study::class,
			'foreignKey' => Study::FIELD_CAUSE_VARIABLE_ID,
			'localKey' => Study::FIELD_ID,
			'methodName' => 'studies_where_cause_variable',
		],
		'studies_where_effect_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Study::class,
			'foreignKey' => Study::FIELD_EFFECT_VARIABLE_ID,
			'localKey' => Study::FIELD_ID,
			'methodName' => 'studies_where_effect_variable',
		],
		'third_party_correlations' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => ThirdPartyCorrelation::class,
			'foreignKey' => ThirdPartyCorrelation::FIELD_EFFECT_ID,
			'localKey' => ThirdPartyCorrelation::FIELD_ID,
			'methodName' => 'third_party_correlations',
		],
		'tracking_reminder_notifications' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => TrackingReminderNotification::class,
			'foreignKey' => TrackingReminderNotification::FIELD_VARIABLE_ID,
			'localKey' => TrackingReminderNotification::FIELD_ID,
			'methodName' => 'tracking_reminder_notifications',
		],
		'tracking_reminders' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => TrackingReminder::class,
			'foreignKey' => TrackingReminder::FIELD_VARIABLE_ID,
			'localKey' => TrackingReminder::FIELD_ID,
			'methodName' => 'tracking_reminders',
		],
		'user_tags_where_tag_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserTag::class,
			'foreignKey' => UserTag::FIELD_TAG_VARIABLE_ID,
			'localKey' => UserTag::FIELD_ID,
			'methodName' => 'user_tags_where_tag_variable',
		],
		'user_tags_where_tagged_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserTag::class,
			'foreignKey' => UserTag::FIELD_TAGGED_VARIABLE_ID,
			'localKey' => UserTag::FIELD_ID,
			'methodName' => 'user_tags_where_tagged_variable',
		],
		'user_variable_clients' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariableClient::class,
			'foreignKey' => UserVariableClient::FIELD_VARIABLE_ID,
			'localKey' => UserVariableClient::FIELD_ID,
			'methodName' => 'user_variable_clients',
		],
		'user_variable_outcome_categories' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariableOutcomeCategory::class,
			'foreignKey' => UserVariableOutcomeCategory::FIELD_VARIABLE_ID,
			'localKey' => UserVariableOutcomeCategory::FIELD_ID,
			'methodName' => 'user_variable_outcome_categories',
		],
		'user_variable_predictor_categories' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariablePredictorCategory::class,
			'foreignKey' => UserVariablePredictorCategory::FIELD_VARIABLE_ID,
			'localKey' => UserVariablePredictorCategory::FIELD_ID,
			'methodName' => 'user_variable_predictor_categories',
		],
		'user_variables' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariable::class,
			'foreignKey' => UserVariable::FIELD_VARIABLE_ID,
			'localKey' => UserVariable::FIELD_ID,
			'methodName' => 'user_variables',
		],
		'variable_outcome_categories' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => VariableOutcomeCategory::class,
			'foreignKey' => VariableOutcomeCategory::FIELD_VARIABLE_ID,
			'localKey' => VariableOutcomeCategory::FIELD_ID,
			'methodName' => 'variable_outcome_categories',
		],
		'variable_predictor_categories' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => VariablePredictorCategory::class,
			'foreignKey' => VariablePredictorCategory::FIELD_VARIABLE_ID,
			'localKey' => VariablePredictorCategory::FIELD_ID,
			'methodName' => 'variable_predictor_categories',
		],
		'variables_where_best_cause_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Variable::class,
			'foreignKey' => Variable::FIELD_BEST_CAUSE_VARIABLE_ID,
			'localKey' => Variable::FIELD_ID,
			'methodName' => 'variables_where_best_cause_variable',
		],
		'variables_where_best_effect_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Variable::class,
			'foreignKey' => Variable::FIELD_BEST_EFFECT_VARIABLE_ID,
			'localKey' => Variable::FIELD_ID,
			'methodName' => 'variables_where_best_effect_variable',
		],
		'votes_where_cause_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Vote::class,
			'foreignKey' => Vote::FIELD_CAUSE_VARIABLE_ID,
			'localKey' => Vote::FIELD_ID,
			'methodName' => 'votes_where_cause_variable',
		],
		'votes_where_effect_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Vote::class,
			'foreignKey' => Vote::FIELD_EFFECT_VARIABLE_ID,
			'localKey' => Vote::FIELD_ID,
			'methodName' => 'votes_where_effect_variable',
		],
		'users_where_primary_outcome_variable' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKey' => \App\Models\User::FIELD_PRIMARY_OUTCOME_VARIABLE_ID,
			'localKey' => \App\Models\User::FIELD_ID,
			'methodName' => 'users_where_primary_outcome_variable',
		],
	];
	public function best_global_variable_relationship(): BelongsTo{
		return $this->belongsTo(GlobalVariableRelationship::class, Variable::FIELD_BEST_AGGREGATE_CORRELATION_ID,
			GlobalVariableRelationship::FIELD_ID, Variable::FIELD_BEST_AGGREGATE_CORRELATION_ID);
	}
	public function best_cause_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, Variable::FIELD_BEST_CAUSE_VARIABLE_ID, Variable::FIELD_ID,
			Variable::FIELD_BEST_CAUSE_VARIABLE_ID);
	}
	public function best_effect_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, Variable::FIELD_BEST_EFFECT_VARIABLE_ID, Variable::FIELD_ID,
			Variable::FIELD_BEST_EFFECT_VARIABLE_ID);
	}
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, Variable::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			Variable::FIELD_CLIENT_ID);
	}
	public function default_unit(): BelongsTo{
		return $this->belongsTo(Unit::class, Variable::FIELD_DEFAULT_UNIT_ID, Unit::FIELD_ID,
			Variable::FIELD_DEFAULT_UNIT_ID);
	}
	public function variable_category(): BelongsTo{
		return $this->belongsTo(VariableCategory::class, Variable::FIELD_VARIABLE_CATEGORY_ID,
			VariableCategory::FIELD_ID, Variable::FIELD_VARIABLE_CATEGORY_ID);
	}
	public function wp_post(): BelongsTo{
		return $this->belongsTo(WpPost::class, Variable::FIELD_WP_POST_ID, WpPost::FIELD_ID,
			Variable::FIELD_WP_POST_ID);
	}
	public function global_variable_relationships_where_cause_variable(): HasMany{
		return $this->hasMany(GlobalVariableRelationship::class, GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID,
			static::FIELD_ID);
	}
	public function global_variable_relationships_where_effect_variable(): HasMany{
		return $this->hasMany(GlobalVariableRelationship::class, GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID,
			static::FIELD_ID);
	}
	public function applications_where_outcome_variable(): HasMany{
		return $this->hasMany(Application::class, Application::FIELD_OUTCOME_VARIABLE_ID, static::FIELD_ID);
	}
	public function applications_where_predictor_variable(): HasMany{
		return $this->hasMany(Application::class, Application::FIELD_PREDICTOR_VARIABLE_ID, static::FIELD_ID);
	}
	public function common_tags_where_tag_variable(): HasMany{
		return $this->hasMany(CommonTag::class, CommonTag::FIELD_TAG_VARIABLE_ID, static::FIELD_ID);
	}
	public function common_tags_where_tagged_variable(): HasMany{
		return $this->hasMany(CommonTag::class, CommonTag::FIELD_TAGGED_VARIABLE_ID, static::FIELD_ID);
	}
	public function correlation_causality_votes_where_cause_variable(): HasMany{
		return $this->hasMany(CorrelationCausalityVote::class, CorrelationCausalityVote::FIELD_CAUSE_VARIABLE_ID,
			static::FIELD_ID);
	}
	public function correlation_causality_votes_where_effect_variable(): HasMany{
		return $this->hasMany(CorrelationCausalityVote::class, CorrelationCausalityVote::FIELD_EFFECT_VARIABLE_ID,
			static::FIELD_ID);
	}
	public function correlation_usefulness_votes_where_cause_variable(): HasMany{
		return $this->hasMany(CorrelationUsefulnessVote::class, CorrelationUsefulnessVote::FIELD_CAUSE_VARIABLE_ID,
			static::FIELD_ID);
	}
	public function correlation_usefulness_votes_where_effect_variable(): HasMany{
		return $this->hasMany(CorrelationUsefulnessVote::class, CorrelationUsefulnessVote::FIELD_EFFECT_VARIABLE_ID,
			static::FIELD_ID);
	}
	public function correlations_where_cause_variable(): HasMany{
		return $this->hasMany(UserVariableRelationship::class, UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID, static::FIELD_ID);
	}
	public function correlations_where_effect_variable(): HasMany{
		return $this->hasMany(UserVariableRelationship::class, UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID, static::FIELD_ID);
	}
	public function ct_side_effect(): HasOne{
		return $this->hasOne(CtSideEffect::class, CtSideEffect::FIELD_VARIABLE_ID, static::FIELD_ID);
	}
	public function ct_treatment_side_effects_where_side_effect_variable(): HasMany{
		return $this->hasMany(CtTreatmentSideEffect::class, CtTreatmentSideEffect::FIELD_SIDE_EFFECT_VARIABLE_ID,
			static::FIELD_ID);
	}
	public function ct_treatment_side_effects_where_treatment_variable(): HasMany{
		return $this->hasMany(CtTreatmentSideEffect::class, CtTreatmentSideEffect::FIELD_TREATMENT_VARIABLE_ID,
			static::FIELD_ID);
	}
	public function ctg_condition(): HasOne{
		return $this->hasOne(CtgCondition::class, CtgCondition::FIELD_VARIABLE_ID, static::FIELD_ID);
	}
	public function ctg_intervention(): HasOne{
		return $this->hasOne(CtgIntervention::class, CtgIntervention::FIELD_VARIABLE_ID, static::FIELD_ID);
	}
	public function measurements(): HasMany{
		return $this->hasMany(Measurement::class, Measurement::FIELD_VARIABLE_ID, static::FIELD_ID);
	}
	public function studies_where_cause_variable(): HasMany{
		return $this->hasMany(Study::class, Study::FIELD_CAUSE_VARIABLE_ID, static::FIELD_ID);
	}
	public function studies_where_effect_variable(): HasMany{
		return $this->hasMany(Study::class, Study::FIELD_EFFECT_VARIABLE_ID, static::FIELD_ID);
	}
	public function third_party_correlations(): HasMany{
		return $this->hasMany(ThirdPartyCorrelation::class, ThirdPartyCorrelation::FIELD_EFFECT_ID, static::FIELD_ID);
	}
	public function tracking_reminder_notifications(): HasMany{
		return $this->hasMany(TrackingReminderNotification::class, TrackingReminderNotification::FIELD_VARIABLE_ID,
			static::FIELD_ID);
	}
	public function tracking_reminders(): HasMany{
		return $this->hasMany(TrackingReminder::class, TrackingReminder::FIELD_VARIABLE_ID, static::FIELD_ID);
	}
	public function user_tags_where_tag_variable(): HasMany{
		return $this->hasMany(UserTag::class, UserTag::FIELD_TAG_VARIABLE_ID, static::FIELD_ID);
	}
	public function user_tags_where_tagged_variable(): HasMany{
		return $this->hasMany(UserTag::class, UserTag::FIELD_TAGGED_VARIABLE_ID, static::FIELD_ID);
	}
	public function user_variable_clients(): HasMany{
		return $this->hasMany(UserVariableClient::class, UserVariableClient::FIELD_VARIABLE_ID, static::FIELD_ID);
	}
	public function user_variable_outcome_categories(): HasMany{
		return $this->hasMany(UserVariableOutcomeCategory::class, UserVariableOutcomeCategory::FIELD_VARIABLE_ID,
			static::FIELD_ID);
	}
	public function user_variable_predictor_categories(): HasMany{
		return $this->hasMany(UserVariablePredictorCategory::class, UserVariablePredictorCategory::FIELD_VARIABLE_ID,
			static::FIELD_ID);
	}
	public function user_variables(bool $excludeDeletedAndTestUsers): HasMany{
		$qb = $this->hasMany(UserVariable::class, UserVariable::FIELD_VARIABLE_ID, static::FIELD_ID);
		if($excludeDeletedAndTestUsers){
			UserIdProperty::excludeDeletedAndTestUsers($qb);
		}
		return $qb;
	}
	public function variable_outcome_categories(): HasMany{
		return $this->hasMany(VariableOutcomeCategory::class, VariableOutcomeCategory::FIELD_VARIABLE_ID,
			static::FIELD_ID);
	}
	public function variable_predictor_categories(): HasMany{
		return $this->hasMany(VariablePredictorCategory::class, VariablePredictorCategory::FIELD_VARIABLE_ID,
			static::FIELD_ID);
	}
	public function variables_where_best_cause_variable(): HasMany{
		return $this->hasMany(Variable::class, Variable::FIELD_BEST_CAUSE_VARIABLE_ID, static::FIELD_ID);
	}
	public function variables_where_best_effect_variable(): HasMany{
		return $this->hasMany(Variable::class, Variable::FIELD_BEST_EFFECT_VARIABLE_ID, static::FIELD_ID);
	}
	public function votes_where_cause_variable(): HasMany{
		return $this->hasMany(Vote::class, Vote::FIELD_CAUSE_VARIABLE_ID, static::FIELD_ID);
	}
	public function votes_where_effect_variable(): HasMany{
		return $this->hasMany(Vote::class, Vote::FIELD_EFFECT_VARIABLE_ID, static::FIELD_ID);
	}
	public function users_where_primary_outcome_variable(): HasMany{
		return $this->hasMany(\App\Models\User::class, \App\Models\User::FIELD_PRIMARY_OUTCOME_VARIABLE_ID,
			static::FIELD_ID);
	}
}
