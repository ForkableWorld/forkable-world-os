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
use App\Models\Measurement;
use App\Models\ThirdPartyCorrelation;
use App\Models\Unit;
use App\Models\UserVariable;
use App\Models\UserVariableOutcomeCategory;
use App\Models\UserVariablePredictorCategory;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Models\VariableOutcomeCategory;
use App\Models\VariablePredictorCategory;
use App\Models\WpPost;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseVariableCategory
 * @property int $id
 * @property string $name
 * @property float $filling_value
 * @property float $maximum_allowed_value
 * @property float $minimum_allowed_value
 * @property int $duration_of_action
 * @property int $onset_delay
 * @property string $combination_operation
 * @property bool $cause_only
 * @property bool $outcome
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $image_url
 * @property int $default_unit_id
 * @property Carbon $deleted_at
 * @property bool $manual_tracking
 * @property int $minimum_allowed_seconds_between_measurements
 * @property int $average_seconds_between_measurements
 * @property int $median_seconds_between_measurements
 * @property int $wp_post_id
 * @property string $filling_type
 * @property int $number_of_outcome_population_studies
 * @property int $number_of_predictor_population_studies
 * @property int $number_of_outcome_case_studies
 * @property int $number_of_predictor_case_studies
 * @property int $number_of_measurements
 * @property int $number_of_user_variables
 * @property int $number_of_variables
 * @property bool $is_public
 * @property array $synonyms
 * @property string $amazon_product_category
 * @property bool $boring
 * @property bool $effect_only
 * @property bool $predictor
 * @property string $font_awesome
 * @property string $ion_icon
 * @property string $more_info
 * @property string $valence
 * @property string $name_singular
 * @property int $sort_order
 * @property string $is_goal
 * @property string $controllable
 * @property Unit $default_unit
 * @property WpPost $wp_post
 * @property Collection|GlobalVariableRelationship[] $global_variable_relationships_where_cause_variable_category
 * @property Collection|GlobalVariableRelationship[] $global_variable_relationships_where_effect_variable_category
 * @property Collection|UserVariableRelationship[] $correlations_where_cause_variable_category
 * @property Collection|UserVariableRelationship[] $correlations_where_effect_variable_category
 * @property Collection|Measurement[] $measurements
 * @property Collection|ThirdPartyCorrelation[] $third_party_correlations_where_cause_variable_category
 * @property Collection|ThirdPartyCorrelation[] $third_party_correlations_where_effect_variable_category
 * @property Collection|UserVariableOutcomeCategory[] $user_variable_outcome_categories
 * @property Collection|UserVariablePredictorCategory[] $user_variable_predictor_categories
 * @property Collection|UserVariable[] $user_variables
 * @property Collection|VariableOutcomeCategory[] $variable_outcome_categories
 * @property Collection|VariablePredictorCategory[] $variable_predictor_categories
 * @property Collection|Variable[] $variables
 * @package App\Models\Base
 * @property-read int|null $global_variable_relationships_where_cause_variable_category_count
 * @property-read int|null $global_variable_relationships_where_effect_variable_category_count
 * @property-read int|null $correlations_where_cause_variable_category_count
 * @property-read int|null $correlations_where_effect_variable_category_count
 * @property mixed $raw
 * @property-read int|null $measurements_count
 * @property-read int|null $third_party_correlations_where_cause_variable_category_count
 * @property-read int|null $third_party_correlations_where_effect_variable_category_count
 * @property-read int|null $user_variables_count
 * @property-read int|null $variables_count
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory newQuery()
 * @method static \Illuminate\Database\Query\Builder|BaseVariableCategory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory
 *     whereAverageSecondsBetweenMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereCauseOnly($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereCombinationOperation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereDefaultUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereDurationOfAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereFillingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereFillingValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereManualTracking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereMaximumAllowedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory
 *     whereMedianSecondsBetweenMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory
 *     whereMinimumAllowedSecondsBetweenMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereMinimumAllowedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereNumberOfMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereNumberOfOutcomeCaseStudies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory
 *     whereNumberOfOutcomePopulationStudies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereNumberOfPredictorCaseStudies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory
 *     whereNumberOfPredictorPopulationStudies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereNumberOfUserVariables($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereNumberOfVariables($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereOnsetDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory wherePublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseVariableCategory whereWpPostId($value)
 * @method static \Illuminate\Database\Query\Builder|BaseVariableCategory withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BaseVariableCategory withoutTrashed()
 * @mixin \Eloquent
 */
abstract class BaseVariableCategory extends BaseModel {
	use SoftDeletes;
	public const FIELD_AMAZON_PRODUCT_CATEGORY = 'amazon_product_category';
	public const FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS = 'average_seconds_between_measurements';
	public const FIELD_BORING = 'boring';
	public const FIELD_CAUSE_ONLY = 'cause_only';
	public const FIELD_COMBINATION_OPERATION = 'combination_operation';
	public const FIELD_CONTROLLABLE = 'controllable';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DEFAULT_UNIT_ID = 'default_unit_id';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_DURATION_OF_ACTION = 'duration_of_action';
	public const FIELD_EFFECT_ONLY = 'effect_only';
	public const FIELD_FILLING_TYPE = 'filling_type';
	public const FIELD_FILLING_VALUE = 'filling_value';
	public const FIELD_FONT_AWESOME = 'font_awesome';
	public const FIELD_ID = 'id';
	public const FIELD_IMAGE_URL = 'image_url';
	public const FIELD_ION_ICON = 'ion_icon';
	public const FIELD_IS_GOAL = 'is_goal';
	public const FIELD_IS_PUBLIC = 'is_public';
	public const FIELD_MANUAL_TRACKING = 'manual_tracking';
	public const FIELD_MAXIMUM_ALLOWED_VALUE = 'maximum_allowed_value';
	public const FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS = 'median_seconds_between_measurements';
	public const FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = 'minimum_allowed_seconds_between_measurements';
	public const FIELD_MINIMUM_ALLOWED_VALUE = 'minimum_allowed_value';
	public const FIELD_MORE_INFO = 'more_info';
	public const FIELD_NAME = 'name';
	public const FIELD_NAME_SINGULAR = 'name_singular';
	public const FIELD_NUMBER_OF_MEASUREMENTS = 'number_of_measurements';
	public const FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES = 'number_of_outcome_case_studies';
	public const FIELD_NUMBER_OF_OUTCOME_POPULATION_STUDIES = 'number_of_outcome_population_studies';
	public const FIELD_NUMBER_OF_PREDICTOR_CASE_STUDIES = 'number_of_predictor_case_studies';
	public const FIELD_NUMBER_OF_PREDICTOR_POPULATION_STUDIES = 'number_of_predictor_population_studies';
	public const FIELD_NUMBER_OF_USER_VARIABLES = 'number_of_user_variables';
	public const FIELD_NUMBER_OF_VARIABLES = 'number_of_variables';
	public const FIELD_ONSET_DELAY = 'onset_delay';
	public const FIELD_OUTCOME = 'outcome';
	public const FIELD_PREDICTOR = 'predictor';
	public const FIELD_SLUG = 'slug';
	public const FIELD_SORT_ORDER = 'sort_order';
	public const FIELD_SYNONYMS = 'synonyms';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_VALENCE = 'valence';
	public const FIELD_WP_POST_ID = 'wp_post_id';
	public const TABLE = 'variable_categories';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_AMAZON_PRODUCT_CATEGORY => 'string',
		self::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS => 'int',
		self::FIELD_BORING => 'bool',
		self::FIELD_CAUSE_ONLY => 'bool',
		self::FIELD_COMBINATION_OPERATION => 'string',
		self::FIELD_CONTROLLABLE => 'string',
		self::FIELD_DEFAULT_UNIT_ID => 'int',
		self::FIELD_DURATION_OF_ACTION => 'int',
		self::FIELD_EFFECT_ONLY => 'bool',
		self::FIELD_FILLING_TYPE => 'string',
		self::FIELD_FILLING_VALUE => 'float',
		self::FIELD_FONT_AWESOME => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_IMAGE_URL => 'string',
		self::FIELD_ION_ICON => 'string',
		self::FIELD_IS_GOAL => 'string',
		self::FIELD_IS_PUBLIC => 'bool',
		self::FIELD_MANUAL_TRACKING => 'bool',
		self::FIELD_MAXIMUM_ALLOWED_VALUE => 'float',
		self::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS => 'int',
		self::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => 'int',
		self::FIELD_MINIMUM_ALLOWED_VALUE => 'float',
		self::FIELD_MORE_INFO => 'string',
		self::FIELD_NAME => 'string',
		self::FIELD_NAME_SINGULAR => 'string',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'int',
		self::FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES => 'int',
		self::FIELD_NUMBER_OF_OUTCOME_POPULATION_STUDIES => 'int',
		self::FIELD_NUMBER_OF_PREDICTOR_CASE_STUDIES => 'int',
		self::FIELD_NUMBER_OF_PREDICTOR_POPULATION_STUDIES => 'int',
		self::FIELD_NUMBER_OF_USER_VARIABLES => 'int',
		self::FIELD_NUMBER_OF_VARIABLES => 'int',
		self::FIELD_ONSET_DELAY => 'int',
		self::FIELD_OUTCOME => 'bool',
		self::FIELD_PREDICTOR => 'bool',
		self::FIELD_SORT_ORDER => 'int',
		self::FIELD_SYNONYMS => 'array',
		self::FIELD_VALENCE => 'string',
		self::FIELD_WP_POST_ID => 'int',	];
	protected array $rules = [
		self::FIELD_AMAZON_PRODUCT_CATEGORY => 'required|max:100',
		self::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_BORING => 'nullable|boolean',
		self::FIELD_CAUSE_ONLY => 'required|boolean',
		self::FIELD_COMBINATION_OPERATION => 'required',
		self::FIELD_CONTROLLABLE => 'required',
		self::FIELD_DEFAULT_UNIT_ID => 'nullable|integer|min:0|max:65535',
		self::FIELD_DURATION_OF_ACTION => 'required|integer|min:0|max:2147483647',
		self::FIELD_EFFECT_ONLY => 'nullable|boolean',
		self::FIELD_FILLING_TYPE => 'nullable',
		self::FIELD_FILLING_VALUE => 'nullable|numeric',
		self::FIELD_FONT_AWESOME => 'nullable|max:100',
		self::FIELD_IMAGE_URL => 'nullable|max:255',
		self::FIELD_ION_ICON => 'nullable|max:100',
		self::FIELD_IS_GOAL => 'required',
		self::FIELD_IS_PUBLIC => 'nullable|boolean',
		self::FIELD_MANUAL_TRACKING => 'required|boolean',
		self::FIELD_MAXIMUM_ALLOWED_VALUE => 'nullable|numeric',
		self::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_MINIMUM_ALLOWED_VALUE => 'nullable|numeric',
		self::FIELD_MORE_INFO => 'nullable|max:255',
		self::FIELD_NAME => 'required|max:64',
		self::FIELD_NAME_SINGULAR => 'required|max:255',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_OUTCOME_POPULATION_STUDIES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_PREDICTOR_CASE_STUDIES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_PREDICTOR_POPULATION_STUDIES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_USER_VARIABLES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_VARIABLES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_ONSET_DELAY => 'required|integer|min:0|max:2147483647',
		self::FIELD_OUTCOME => 'nullable|boolean',
		self::FIELD_PREDICTOR => 'nullable|boolean',
		self::FIELD_SORT_ORDER => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_SYNONYMS => 'required|max:600',
		self::FIELD_VALENCE => 'required',
		self::FIELD_WP_POST_ID => 'nullable|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_NAME => 'Name of the category',
		self::FIELD_FILLING_VALUE => 'Value for replacing null measurements',
		self::FIELD_MAXIMUM_ALLOWED_VALUE => 'Maximum recorded value of this category',
		self::FIELD_MINIMUM_ALLOWED_VALUE => 'Minimum recorded value of this category',
		self::FIELD_DURATION_OF_ACTION => 'How long the effect of a measurement in this variable lasts',
		self::FIELD_ONSET_DELAY => 'How long it takes for a measurement in this variable to take effect',
		self::FIELD_COMBINATION_OPERATION => 'How to combine values of this variable (for instance, to see a summary of the values over a month) SUM or MEAN',
		self::FIELD_CAUSE_ONLY => 'A value of 1 indicates that this category is generally a cause in a causal relationship.  An example of a causeOnly category would be a category such as Work which would generally not be influenced by the behaviour of the user',
		self::FIELD_OUTCOME => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_IMAGE_URL => 'Image URL',
		self::FIELD_DEFAULT_UNIT_ID => 'ID of the default unit for the category',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_MANUAL_TRACKING => 'Should we include in manual tracking searches?',
		self::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => '',
		self::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS => '',
		self::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS => '',
		self::FIELD_WP_POST_ID => '',
		self::FIELD_FILLING_TYPE => '',
		self::FIELD_NUMBER_OF_OUTCOME_POPULATION_STUDIES => 'Number of Global Population Studies for this Cause Variable Category.
                [Formula:
                    update variable_categories
                        left join (
                            select count(id) as total, cause_variable_category_id
                            from global_variable_relationships
                            group by cause_variable_category_id
                        )
                        as grouped on variable_categories.id = grouped.cause_variable_category_id
                    set variable_categories.number_of_outcome_population_studies = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_PREDICTOR_POPULATION_STUDIES => 'Number of Global Population Studies for this Effect Variable Category.
                [Formula:
                    update variable_categories
                        left join (
                            select count(id) as total, effect_variable_category_id
                            from global_variable_relationships
                            group by effect_variable_category_id
                        )
                        as grouped on variable_categories.id = grouped.effect_variable_category_id
                    set variable_categories.number_of_predictor_population_studies = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES => 'Number of Individual Case Studies for this Cause Variable Category.
                [Formula:
                    update variable_categories
                        left join (
                            select count(id) as total, cause_variable_category_id
                            from user_variable_relationships
                            group by cause_variable_category_id
                        )
                        as grouped on variable_categories.id = grouped.cause_variable_category_id
                    set variable_categories.number_of_outcome_case_studies = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_PREDICTOR_CASE_STUDIES => 'Number of Individual Case Studies for this Effect Variable Category.
                [Formula:
                    update variable_categories
                        left join (
                            select count(id) as total, effect_variable_category_id
                            from user_variable_relationships
                            group by effect_variable_category_id
                        )
                        as grouped on variable_categories.id = grouped.effect_variable_category_id
                    set variable_categories.number_of_predictor_case_studies = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'Number of Measurements for this Variable Category.
                    [Formula: update variable_categories
                        left join (
                            select count(id) as total, variable_category_id
                            from measurements
                            group by variable_category_id
                        )
                        as grouped on variable_categories.id = grouped.variable_category_id
                    set variable_categories.number_of_measurements = count(grouped.total)]',
		self::FIELD_NUMBER_OF_USER_VARIABLES => 'Number of User Variables for this Variable Category.
                    [Formula: update variable_categories
                        left join (
                            select count(id) as total, variable_category_id
                            from user_variables
                            group by variable_category_id
                        )
                        as grouped on variable_categories.id = grouped.variable_category_id
                    set variable_categories.number_of_user_variables = count(grouped.total)]',
		self::FIELD_NUMBER_OF_VARIABLES => 'Number of Variables for this Variable Category.
                    [Formula: update variable_categories
                        left join (
                            select count(id) as total, variable_category_id
                            from variables
                            group by variable_category_id
                        )
                        as grouped on variable_categories.id = grouped.variable_category_id
                    set variable_categories.number_of_variables = count(grouped.total)]',
		self::FIELD_IS_PUBLIC => '',
		self::FIELD_SYNONYMS => 'The primary name and any synonyms for it. This field should be used for non-specific searches.',
		self::FIELD_AMAZON_PRODUCT_CATEGORY => 'The Amazon equivalent product category.',
		self::FIELD_BORING => 'If boring, the category should be hidden by default.',
		self::FIELD_EFFECT_ONLY => 'effect_only is true if people would never be interested in the effects of most variables in the category.',
		self::FIELD_PREDICTOR => 'Predictor is true if people would like to know the effects of most variables in the category.',
		self::FIELD_FONT_AWESOME => '',
		self::FIELD_ION_ICON => '',
		self::FIELD_MORE_INFO => 'More information displayed when the user is adding reminders and going through the onboarding process. ',
		self::FIELD_VALENCE => 'Set the valence positive if more is better for all the variables in the category, negative if more is bad, and neutral if none of the variables have such a valence. Valence is null if there is not a consistent valence for all variables in the category. ',
		self::FIELD_NAME_SINGULAR => 'The singular version of the name.',
		self::FIELD_SORT_ORDER => '',
		self::FIELD_IS_GOAL => 'The effect of a food on the severity of a symptom is useful because you can control the predictor directly. However, the effect of a symptom on the foods you eat is not very useful.  The foods you eat are not generally an objective end in themselves. ',
		self::FIELD_CONTROLLABLE => 'The effect of a food on the severity of a symptom is useful because you can control the predictor directly. However, the effect of a symptom on the foods you eat is not very useful.  Symptom severity is not directly controllable. ',
	];
	protected array $relationshipInfo = [
		'default_unit' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Unit::class,
			'foreignKeyColumnName' => 'default_unit_id',
			'foreignKey' => VariableCategory::FIELD_DEFAULT_UNIT_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Unit::FIELD_ID,
			'ownerKeyColumnName' => 'default_unit_id',
			'ownerKey' => VariableCategory::FIELD_DEFAULT_UNIT_ID,
			'methodName' => 'default_unit',
		],
		'wp_post' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => WpPost::class,
			'foreignKeyColumnName' => 'wp_post_id',
			'foreignKey' => VariableCategory::FIELD_WP_POST_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => WpPost::FIELD_ID,
			'ownerKeyColumnName' => 'wp_post_id',
			'ownerKey' => VariableCategory::FIELD_WP_POST_ID,
			'methodName' => 'wp_post',
		],
		'global_variable_relationships_where_cause_variable_category' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => GlobalVariableRelationship::class,
			'foreignKey' => GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
			'localKey' => GlobalVariableRelationship::FIELD_ID,
			'methodName' => 'global_variable_relationships_where_cause_variable_category',
		],
		'global_variable_relationships_where_effect_variable_category' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => GlobalVariableRelationship::class,
			'foreignKey' => GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID,
			'localKey' => GlobalVariableRelationship::FIELD_ID,
			'methodName' => 'global_variable_relationships_where_effect_variable_category',
		],
		'correlations_where_cause_variable_category' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariableRelationship::class,
			'foreignKey' => UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
			'localKey' => UserVariableRelationship::FIELD_ID,
			'methodName' => 'correlations_where_cause_variable_category',
		],
		'correlations_where_effect_variable_category' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariableRelationship::class,
			'foreignKey' => UserVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID,
			'localKey' => UserVariableRelationship::FIELD_ID,
			'methodName' => 'correlations_where_effect_variable_category',
		],
		'measurements' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Measurement::class,
			'foreignKey' => Measurement::FIELD_VARIABLE_CATEGORY_ID,
			'localKey' => Measurement::FIELD_ID,
			'methodName' => 'measurements',
		],
		'third_party_correlations_where_cause_variable_category' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => ThirdPartyCorrelation::class,
			'foreignKey' => ThirdPartyCorrelation::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
			'localKey' => ThirdPartyCorrelation::FIELD_ID,
			'methodName' => 'third_party_correlations_where_cause_variable_category',
		],
		'third_party_correlations_where_effect_variable_category' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => ThirdPartyCorrelation::class,
			'foreignKey' => ThirdPartyCorrelation::FIELD_EFFECT_VARIABLE_CATEGORY_ID,
			'localKey' => ThirdPartyCorrelation::FIELD_ID,
			'methodName' => 'third_party_correlations_where_effect_variable_category',
		],
		'user_variable_outcome_categories' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariableOutcomeCategory::class,
			'foreignKey' => UserVariableOutcomeCategory::FIELD_VARIABLE_CATEGORY_ID,
			'localKey' => UserVariableOutcomeCategory::FIELD_ID,
			'methodName' => 'user_variable_outcome_categories',
		],
		'user_variable_predictor_categories' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariablePredictorCategory::class,
			'foreignKey' => UserVariablePredictorCategory::FIELD_VARIABLE_CATEGORY_ID,
			'localKey' => UserVariablePredictorCategory::FIELD_ID,
			'methodName' => 'user_variable_predictor_categories',
		],
		'user_variables' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariable::class,
			'foreignKey' => UserVariable::FIELD_VARIABLE_CATEGORY_ID,
			'localKey' => UserVariable::FIELD_ID,
			'methodName' => 'user_variables',
		],
		'variable_outcome_categories' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => VariableOutcomeCategory::class,
			'foreignKey' => VariableOutcomeCategory::FIELD_VARIABLE_CATEGORY_ID,
			'localKey' => VariableOutcomeCategory::FIELD_ID,
			'methodName' => 'variable_outcome_categories',
		],
		'variable_predictor_categories' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => VariablePredictorCategory::class,
			'foreignKey' => VariablePredictorCategory::FIELD_VARIABLE_CATEGORY_ID,
			'localKey' => VariablePredictorCategory::FIELD_ID,
			'methodName' => 'variable_predictor_categories',
		],
		'variables' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Variable::class,
			'foreignKey' => Variable::FIELD_VARIABLE_CATEGORY_ID,
			'localKey' => Variable::FIELD_ID,
			'methodName' => 'variables',
		],
	];
	public function default_unit(): BelongsTo{
		return $this->belongsTo(Unit::class, VariableCategory::FIELD_DEFAULT_UNIT_ID, Unit::FIELD_ID,
			VariableCategory::FIELD_DEFAULT_UNIT_ID);
	}
	public function wp_post(): BelongsTo{
		return $this->belongsTo(WpPost::class, VariableCategory::FIELD_WP_POST_ID, WpPost::FIELD_ID,
			VariableCategory::FIELD_WP_POST_ID);
	}
	public function global_variable_relationships_where_cause_variable_category(): HasMany{
		return $this->hasMany(GlobalVariableRelationship::class, GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
			static::FIELD_ID);
	}
	public function global_variable_relationships_where_effect_variable_category(): HasMany{
		return $this->hasMany(GlobalVariableRelationship::class, GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID,
			static::FIELD_ID);
	}
	public function correlations_where_cause_variable_category(): HasMany{
		return $this->hasMany(UserVariableRelationship::class, UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID, static::FIELD_ID);
	}
	public function correlations_where_effect_variable_category(): HasMany{
		return $this->hasMany(UserVariableRelationship::class, UserVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID, static::FIELD_ID);
	}
	public function measurements(): HasMany{
		return $this->hasMany(Measurement::class, Measurement::FIELD_VARIABLE_CATEGORY_ID, static::FIELD_ID);
	}
	public function third_party_correlations_where_cause_variable_category(): HasMany{
		return $this->hasMany(ThirdPartyCorrelation::class, ThirdPartyCorrelation::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
			static::FIELD_ID);
	}
	public function third_party_correlations_where_effect_variable_category(): HasMany{
		return $this->hasMany(ThirdPartyCorrelation::class, ThirdPartyCorrelation::FIELD_EFFECT_VARIABLE_CATEGORY_ID,
			static::FIELD_ID);
	}
	public function user_variable_outcome_categories(): HasMany{
		return $this->hasMany(UserVariableOutcomeCategory::class,
			UserVariableOutcomeCategory::FIELD_VARIABLE_CATEGORY_ID, static::FIELD_ID);
	}
	public function user_variable_predictor_categories(): HasMany{
		return $this->hasMany(UserVariablePredictorCategory::class,
			UserVariablePredictorCategory::FIELD_VARIABLE_CATEGORY_ID, static::FIELD_ID);
	}
	public function user_variables(): HasMany{
		return $this->hasMany(UserVariable::class, UserVariable::FIELD_VARIABLE_CATEGORY_ID, static::FIELD_ID);
	}
	public function variable_outcome_categories(): HasMany{
		return $this->hasMany(VariableOutcomeCategory::class, VariableOutcomeCategory::FIELD_VARIABLE_CATEGORY_ID,
			static::FIELD_ID);
	}
	public function variable_predictor_categories(): HasMany{
		return $this->hasMany(VariablePredictorCategory::class, VariablePredictorCategory::FIELD_VARIABLE_CATEGORY_ID,
			static::FIELD_ID);
	}
	public function variables(): HasMany{
		return $this->hasMany(Variable::class, Variable::FIELD_VARIABLE_CATEGORY_ID, static::FIELD_ID);
	}
}
