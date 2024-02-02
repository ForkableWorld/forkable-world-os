<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Exceptions\AccessTokenExpiredException;
use App\Files\Json\JsonFile;
use App\Properties\VariableCategory\VariableCategoryControllableProperty;
use App\Properties\VariableCategory\VariableCategoryIsGoalProperty;
use App\Slim\Middleware\QMAuth;
use App\Traits\HasJsonFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Buttons\QMButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Buttons\RelationshipButtons\VariableCategory\VariableCategoryGlobalVariableRelationshipsWhereCauseVariableCategoryButton;
use App\Buttons\RelationshipButtons\VariableCategory\VariableCategoryGlobalVariableRelationshipsWhereEffectVariableCategoryButton;
use App\Buttons\RelationshipButtons\VariableCategory\VariableCategoryVariablesButton;
use App\Buttons\States\OnboardingStateButton;
use App\Buttons\VariableButton;
use App\Menus\JournalMenu;
use App\Menus\QMMenu;
use App\Models\Base\BaseVariableCategory;
use App\Astral\Actions\GenerateHardCodedModelAction;
use App\Astral\VariableBaseAstralResource;
use App\Slim\Model\DBModel;
use App\Storage\S3\S3Public;
use App\Traits\HasButton;
use App\Traits\HasDBModel;
use App\Traits\HasFiles;
use App\Traits\HasModel\HasUnit;
use App\Traits\HasModel\HasWpPost;
use App\Traits\HasName;
use App\Traits\HasOptions;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\HtmlHelper;
use App\UI\ImageHelper;
use App\UI\QMColor;
use App\Utils\AppMode;
use App\Utils\EnvOverride;
use App\VariableCategories\BooksVariableCategory;
use App\VariableCategories\CausesOfIllnessVariableCategory;
use App\VariableCategories\CognitivePerformanceVariableCategory;
use App\VariableCategories\ConditionsVariableCategory;
use App\VariableCategories\EmotionsVariableCategory;
use App\VariableCategories\EnvironmentVariableCategory;
use App\VariableCategories\FoodsVariableCategory;
use App\VariableCategories\GoalsVariableCategory;
use App\VariableCategories\LocationsVariableCategory;
use App\VariableCategories\MiscellaneousVariableCategory;
use App\VariableCategories\NutrientsVariableCategory;
use App\VariableCategories\PaymentsVariableCategory;
use App\VariableCategories\PhysicalActivityVariableCategory;
use App\VariableCategories\PhysiqueVariableCategory;
use App\VariableCategories\SleepVariableCategory;
use App\VariableCategories\SocialInteractionsVariableCategory;
use App\VariableCategories\SoftwareVariableCategory;
use App\VariableCategories\SymptomsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;
use App\VariableCategories\VitalSignsVariableCategory;
use App\Variables\QMVariableCategory;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use App\Fields\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\Tags\Tag;
/**
 * App\Models\VariableCategory
 * @OA\Schema (
 *      definition="VariableCategory",
 *      required={"name"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer"
 *      ),
 *      @OA\Property(
 *          property="name",
 *          description="Name of the category",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="filling_value",
 *          description="Value for replacing null measurements",
 *          type="number",
 *          format="float"
 *      ),
 *      @OA\Property(
 *          property="maximum_allowed_value",
 *          description="Maximum recorded value of this category",
 *          type="number",
 *          format="float"
 *      ),
 *      @OA\Property(
 *          property="minimum_allowed_value",
 *          description="Minimum recorded value of this category",
 *          type="number",
 *          format="float"
 *      ),
 *      @OA\Property(
 *          property="duration_of_action",
 *          description="How long the effect of a measurement in this variable lasts",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="onset_delay",
 *          description="How long it takes for a measurement in this variable to take effect",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="combination_operation",
 *          description="How to combine values of this variable (for instance, to see a summary of the values over a
 *     month) 0 for sum OR 1 for mean", type="string"
 *      ),
 * @OA\Property(
 *          property="cause_only",
 *          description="A value of 1 indicates that this category is generally a cause in a causal relationship.  An
 *     example of a causeOnly category would be a category such as Work which would generally not be influenced by the
 *     behaviour of the user", type="boolean"
 *      ),
 * @OA\Property(
 *          property="public",
 *          description="Is category public",
 *          type="integer",
 *          format="int32"
 *      ),
 * @OA\Property(
 *          property="outcome",
 *          description="outcome",
 *          type="boolean"
 *      ),
 * @OA\Property(
 *          property="created_at",
 *          description="When the record was first created. Use ISO 8601 datetime format",
 *          type="string",
 *          format="date-time"
 *      ),
 * @OA\Property(
 *          property="updated_at",
 *          description="When the record in the database was last updated. Use ISO 8601 datetime format",
 *          type="string",
 *          format="date-time"
 *      ),
 * @OA\Property(
 *          property="image_url",
 *          description="Image URL",
 *          type="string"
 *      ),
 * @OA\Property(
 *          property="default_unit_id",
 *          description="ID of the default unit for the category",
 *          type="integer",
 *          format="int32"
 *      )
 * )
 * @property boolean $id
 * @property string $name Name of the category
 * @property float $filling_value Value for replacing null measurements
 * @property float $maximum_allowed_value Maximum recorded value of this category
 * @property float $minimum_allowed_value Minimum recorded value of this category
 * @property integer $duration_of_action How long the effect of a measurement in this variable lasts
 * @property integer $onset_delay How long it takes for a measurement in this variable to take effect
 * @property boolean $combination_operation How to combine values of this variable (for instance, to see a summary of
 *     the values over a month) 0 for sum OR 1 for mean
 * @property boolean $cause_only A value of 1 indicates that this category is generally a cause in a causal
 *     relationship.  An example of a causeOnly category would be a category such as Work which would generally not be
 *     influenced by the behaviour of the user
 * @property integer $public Is category public
 * @property boolean $outcome
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $image_url Image URL
 * @property integer $default_unit_id ID of the default unit for the category
 * @method static \Illuminate\Database\Query\Builder|VariableCategory whereId($value)
 * @method static \Illuminate\Database\Query\Builder|VariableCategory whereName($value)
 * @method static \Illuminate\Database\Query\Builder|VariableCategory whereFillingValue($value)
 * @method static \Illuminate\Database\Query\Builder|VariableCategory whereMaximumAllowedValue($value)
 * @method static \Illuminate\Database\Query\Builder|VariableCategory whereMinimumAllowedValue($value)
 * @method static \Illuminate\Database\Query\Builder|VariableCategory whereDurationOfAction($value)
 * @method static \Illuminate\Database\Query\Builder|VariableCategory whereOnsetDelay($value)
 * @method static \Illuminate\Database\Query\Builder|VariableCategory whereCombinationOperation($value)
 * @method static \Illuminate\Database\Query\Builder|VariableCategory whereUpdated($value)
 * @method static \Illuminate\Database\Query\Builder|VariableCategory whereCauseOnly($value)
 * @method static \Illuminate\Database\Query\Builder|VariableCategory wherePublic($value)
 * @method static \Illuminate\Database\Query\Builder|VariableCategory whereOutcome($value)
 * @method static \Illuminate\Database\Query\Builder|VariableCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|VariableCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|VariableCategory whereImageUrl($value)
 * @method static \Illuminate\Database\Query\Builder|VariableCategory whereDefaultUnitId($value)
 * @property-read Unit $defaultUnit
 * @property string|null $deleted_at
 * @property int $manual_tracking Should we include in manual tracking searches?
 * @property string|null $client_id
 * @property int|null $minimum_allowed_seconds_between_measurements
 * @property int|null $average_seconds_between_measurements
 * @property int|null $median_seconds_between_measurements
 * @method static Builder|VariableCategory newModelQuery()
 * @method static Builder|VariableCategory newQuery()
 * @method static Builder|VariableCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VariableCategory
 *     whereAverageSecondsBetweenMeasurements($value)
 * @method static Builder|VariableCategory whereClientId($value)
 * @method static Builder|VariableCategory whereDeletedAt($value)
 * @method static Builder|VariableCategory whereManualTracking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VariableCategory
 *     whereMedianSecondsBetweenMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VariableCategory
 *     whereMinimumAllowedSecondsBetweenMeasurements($value)
 * @mixin Eloquent
 * @property int|null $wp_post_id
 * @property-read \Illuminate\Database\Eloquent\Collection|Variable[] $variables
 * @property-read int|null $variables_count
 * @method static Builder|VariableCategory whereWpPostId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|GlobalVariableRelationship[] $global_variable_relationships
 * @property-read int|null $global_variable_relationships_count
 * @property-read \Illuminate\Database\Eloquent\Collection|UserVariableRelationship[] $user_variable_relationships
 * @property-read int|null $correlations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Measurement[] $measurements
 * @property-read int|null $measurements_count
 * @property-read \Illuminate\Database\Eloquent\Collection|ThirdPartyCorrelation[] $third_party_correlations
 * @property-read int|null $third_party_correlations_count
 * @property-read Unit|null $unit
 * @property-read \Illuminate\Database\Eloquent\Collection|UserVariable[] $user_variables
 * @property-read int|null $user_variables_count
 * @property-read WpPost $wp_post
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @property string|null $filling_type
 * @method static Builder|VariableCategory whereFillingType($value)
 * @property int|null $number_of_outcome_population_studies Number of Global Population Studies for this Cause Variable
 *     Category.
 *                 [Formula:
 *                     update variable_categories
 *                         left join (
 *                             select count(id) as total, cause_variable_category_id
 *                             from global_variable_relationships
 *                             group by cause_variable_category_id
 *                         )
 *                         as grouped on variable_categories.id = grouped.cause_variable_category_id
 *                     set variable_categories.number_of_outcome_population_studies = count(grouped.total)
 *                 ]
 * @property int|null $number_of_predictor_population_studies Number of Global Population Studies for this Effect
 *     Variable Category.
 *                 [Formula:
 *                     update variable_categories
 *                         left join (
 *                             select count(id) as total, effect_variable_category_id
 *                             from global_variable_relationships
 *                             group by effect_variable_category_id
 *                         )
 *                         as grouped on variable_categories.id = grouped.effect_variable_category_id
 *                     set variable_categories.number_of_predictor_population_studies = count(grouped.total)
 *                 ]
 * @property int|null $number_of_outcome_case_studies Number of Individual Case Studies for this Cause Variable
 *     Category.
 *                 [Formula:
 *                     update variable_categories
 *                         left join (
 *                             select count(id) as total, cause_variable_category_id
 *                             from user_variable_relationships
 *                             group by cause_variable_category_id
 *                         )
 *                         as grouped on variable_categories.id = grouped.cause_variable_category_id
 *                     set variable_categories.number_of_outcome_case_studies = count(grouped.total)
 *                 ]
 * @property int|null $number_of_predictor_case_studies Number of Individual Case Studies for this Effect Variable
 *     Category.
 *                 [Formula:
 *                     update variable_categories
 *                         left join (
 *                             select count(id) as total, effect_variable_category_id
 *                             from user_variable_relationships
 *                             group by effect_variable_category_id
 *                         )
 *                         as grouped on variable_categories.id = grouped.effect_variable_category_id
 *                     set variable_categories.number_of_predictor_case_studies = count(grouped.total)
 *                 ]
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|VariableCategory whereNumberOfOutcomeCaseStudies($value)
 * @method static Builder|VariableCategory whereNumberOfOutcomePopulationStudies($value)
 * @method static Builder|VariableCategory whereNumberOfPredictorCaseStudies($value)
 * @method static Builder|VariableCategory whereNumberOfPredictorPopulationStudies($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|GlobalVariableRelationship[]
 *     $global_variable_relationships_where_cause_variable_category
 * @property-read int|null $global_variable_relationships_where_cause_variable_category_count
 * @property-read \Illuminate\Database\Eloquent\Collection|GlobalVariableRelationship[]
 *     $global_variable_relationships_where_effect_variable_category
 * @property-read int|null $global_variable_relationships_where_effect_variable_category_count
 * @property-read \Illuminate\Database\Eloquent\Collection|UserVariableRelationship[] $correlations_where_cause_variable_category
 * @property-read int|null $correlations_where_cause_variable_category_count
 * @property-read \Illuminate\Database\Eloquent\Collection|UserVariableRelationship[] $correlations_where_effect_variable_category
 * @property-read int|null $correlations_where_effect_variable_category_count
 * @property-read Unit|null $default_unit
 * @property-read \Illuminate\Database\Eloquent\Collection|ThirdPartyCorrelation[]
 *     $third_party_correlations_where_cause_variable_category
 * @property-read int|null $third_party_correlations_where_cause_variable_category_count
 * @property-read \Illuminate\Database\Eloquent\Collection|ThirdPartyCorrelation[]
 *     $third_party_correlations_where_effect_variable_category
 * @property-read int|null $third_party_correlations_where_effect_variable_category_count
 * @property int|null $number_of_measurements Number of Measurements for this Variable Category.
 *                     [Formula: update variable_categories
 *                         left join (
 *                             select count(id) as total, variable_category_id
 *                             from measurements
 *                             group by variable_category_id
 *                         )
 *                         as grouped on variable_categories.id = grouped.variable_category_id
 *                     set variable_categories.number_of_measurements = count(grouped.total)]
 * @property int|null $number_of_user_variables Number of User Variables for this Variable Category.
 *                     [Formula: update variable_categories
 *                         left join (
 *                             select count(id) as total, variable_category_id
 *                             from user_variables
 *                             group by variable_category_id
 *                         )
 *                         as grouped on variable_categories.id = grouped.variable_category_id
 *                     set variable_categories.number_of_user_variables = count(grouped.total)]
 * @property int|null $number_of_variables Number of Variables for this Variable Category.
 *                     [Formula: update variable_categories
 *                         left join (
 *                             select count(id) as total, variable_category_id
 *                             from variables
 *                             group by variable_category_id
 *                         )
 *                         as grouped on variable_categories.id = grouped.variable_category_id
 *                     set variable_categories.number_of_variables = count(grouped.total)]
 * @method static Builder|VariableCategory whereNumberOfMeasurements($value)
 * @method static Builder|VariableCategory whereNumberOfUserVariables($value)
 * @method static Builder|VariableCategory whereNumberOfVariables($value)
 * @property bool|null $is_public
 * @property array $synonyms The primary name and any synonyms for it. This field should be used for non-specific
 *     searches.
 * @property string $amazon_product_category The Amazon equivalent product category.
 * @property int|null $boring If boring, the category should be hidden by default.
 * @property int|null $effect_only effect_only is true if people would never be interested in the effects of most
 *     variables in the category.
 * @property int|null $predictor Predictor is true if people would like to know the effects of most variables in the
 *     category.
 * @property string|null $font_awesome
 * @property string|null $ion_icon
 * @property string|null $more_info More information displayed when the user is adding reminders and going through the
 *     onboarding process.
 * @property string $valence Set the valence positive if more is better for all the variables in the category, negative
 *     if more is bad, and neutral if none of the variables have such a valence. Valence is null if there is not a
 *     consistent valence for all variables in the category.
 * @property string $name_singular The singular version of the name.
 * @property int $sort_order
 * @property string $is_goal The effect of a food on the severity of a symptom is useful because you can control the
 *     predictor directly. However, the effect of a symptom on the foods you eat is not very useful.  The foods you eat
 *     are not generally an objective end in themselves.
 * @property string $controllable The effect of a food on the severity of a symptom is useful because you can control
 *     the predictor directly. However, the effect of a symptom on the foods you eat is not very useful.  Symptom
 *     severity is not directly controllable.
 * @property \Illuminate\Database\Eloquent\Collection|Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|UserVariableOutcomeCategory[]
 *     $user_variable_outcome_categories
 * @property-read int|null $user_variable_outcome_categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|UserVariablePredictorCategory[]
 *     $user_variable_predictor_categories
 * @property-read int|null $user_variable_predictor_categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|VariableOutcomeCategory[] $variable_outcome_categories
 * @property-read int|null $variable_outcome_categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|VariablePredictorCategory[] $variable_predictor_categories
 * @property-read int|null $variable_predictor_categories_count
 * @method static Builder|VariableCategory whereAmazonProductCategory($value)
 * @method static Builder|VariableCategory whereBoring($value)
 * @method static Builder|VariableCategory whereControllable($value)
 * @method static Builder|VariableCategory whereEffectOnly($value)
 * @method static Builder|VariableCategory whereFontAwesome($value)
 * @method static Builder|VariableCategory whereIonIcon($value)
 * @method static Builder|VariableCategory whereIsGoal($value)
 * @method static Builder|VariableCategory whereIsPublic($value)
 * @method static Builder|VariableCategory whereMoreInfo($value)
 * @method static Builder|VariableCategory whereNameSingular($value)
 * @method static Builder|VariableCategory wherePredictor($value)
 * @method static Builder|VariableCategory whereSortOrder($value)
 * @method static Builder|VariableCategory whereSynonyms($value)
 * @method static Builder|VariableCategory whereValence($value)
 * @method static Builder|VariableCategory withAllTags($tags, $type = null)
 * @method static Builder|VariableCategory withAllTagsOfAnyType($tags)
 * @method static Builder|VariableCategory withAnyTags($tags, $type = null)
 * @method static Builder|VariableCategory withAnyTagsOfAnyType($tags)
 * @property string|null $slug The slug is the part of a URL that identifies a page in human-readable keywords.
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 * @method static Builder|VariableCategory whereSlug($value)
 */
class VariableCategory extends BaseVariableCategory implements HasMedia {
    use HasFactory;

    use HasJsonFile;
	use HasUnit, HasWpPost, HasDBModel;
	use HasFiles;
	use HasButton,
		HasName, HasOptions;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = VariableCategory::FIELD_NAME;
	public static $group = VariableCategory::CLASS_CATEGORY;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [VariableCategory::FIELD_NAME];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [];
	/**
	 * The per-page options used the resource index.
	 * @var array
	 */
	public static $perPageOptions = [100];
	/**
	 * Indicates if the resource should be globally searchable.
	 * @var bool
	 */
	public static $globallySearchable = false;
	// TODO: use Cachable;
	public const CLASS_DESCRIPTION = "Categories of of trackable variables include Treatments, Emotions, Symptoms, and Foods.";
	public const COLOR = QMColor::HEX_FUCHSIA;
	public const DEFAULT_IMAGE = ImageHelper::PUBLIC_IMG_VARIABLE_CATEGORIES;
	public const DEFAULT_LIMIT = 50;
	public const DEFAULT_SEARCH_FIELD = self::FIELD_NAME;
	public const DEFAULT_ORDERINGS = [self::FIELD_NAME => self::ORDER_DIRECTION_ASC];
	public const FONT_AWESOME = FontAwesome::TAG_SOLID;
	public static function getSlimClass(): string{ return QMVariableCategory::class; }
	const CLASS_CATEGORY = Variable::CLASS_CATEGORY;
	protected array $rules = [
		self::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_CAUSE_ONLY => 'required|boolean',
		self::FIELD_COMBINATION_OPERATION => 'required',
		self::FIELD_DEFAULT_UNIT_ID => 'nullable|integer|min:1|max:65535',
		self::FIELD_DURATION_OF_ACTION => 'required|integer|min:0|max:2147483647',
		self::FIELD_FILLING_VALUE => 'nullable|numeric',
		self::FIELD_IMAGE_URL => 'nullable|max:255',
		self::FIELD_MANUAL_TRACKING => 'required|boolean',
		self::FIELD_MAXIMUM_ALLOWED_VALUE => 'nullable|numeric',
		self::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_MINIMUM_ALLOWED_VALUE => 'nullable|numeric',
		self::FIELD_NAME => 'required|max:64',
		self::FIELD_ONSET_DELAY => 'required|integer|min:0|max:2147483647',
		self::FIELD_OUTCOME => 'nullable|boolean',
		self::FIELD_IS_PUBLIC => 'required|bool',
		self::FIELD_WP_POST_ID => 'nullable|numeric|min:1',
	];
	/**
	 * The attributes that should be casted to native types.
	 * @var array
	 */
	protected $casts = [
		self::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS => 'int',
		self::FIELD_CAUSE_ONLY => 'bool',
		self::FIELD_COMBINATION_OPERATION => 'string',
		self::FIELD_DEFAULT_UNIT_ID => 'int',
		self::FIELD_DURATION_OF_ACTION => 'int',
		self::FIELD_FILLING_TYPE => 'string',
		self::FIELD_FILLING_VALUE => 'float',
		self::FIELD_ID => 'int',
		self::FIELD_IMAGE_URL => 'string',
		self::FIELD_MANUAL_TRACKING => 'bool',
		self::FIELD_MAXIMUM_ALLOWED_VALUE => 'float',
		self::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS => 'int',
		self::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => 'int',
		self::FIELD_MINIMUM_ALLOWED_VALUE => 'float',
		self::FIELD_NAME => 'string',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'int',
		self::FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES => 'int',
		self::FIELD_NUMBER_OF_OUTCOME_POPULATION_STUDIES => 'int',
		self::FIELD_NUMBER_OF_PREDICTOR_CASE_STUDIES => 'int',
		self::FIELD_NUMBER_OF_PREDICTOR_POPULATION_STUDIES => 'int',
		self::FIELD_NUMBER_OF_USER_VARIABLES => 'int',
		self::FIELD_NUMBER_OF_VARIABLES => 'int',
		self::FIELD_ONSET_DELAY => 'int',
		self::FIELD_OUTCOME => 'bool',
		self::FIELD_IS_PUBLIC => 'int',
		self::FIELD_WP_POST_ID => 'int',
	];
	/**
	 * @return BelongsTo
	 */
	public function defaultUnit(): BelongsTo{
		return $this->belongsTo(Unit::class, 'default_unit_id');
	}
	public function getSubtitleAttribute(): string{
		if(!$this->hasId()){
			return static::CLASS_DESCRIPTION;
		}
		return $this->getQMVariableCategory()->getSubtitleAttribute();
	}
	public function getDBModel(): DBModel{
		return QMVariableCategory::find($this->id);
	}
	/**
	 * @param VariableCategory[] $columns
	 * @return Collection|VariableCategory[]
	 */
	public static function all($columns = ['*']): Collection{
		if($cached = static::getFromClassMemory(__FUNCTION__)){
			return $cached;
		}
		return static::setInClassMemory(__FUNCTION__, parent::all());
	}
	public function getUnitIdAttribute(): ?int{
		return $this->attributes[self::FIELD_DEFAULT_UNIT_ID] ?? null;
	}
	public function getAvatar(): string{
		return $this->getImage();
	}
	public function save(array $options = []): bool{
		//$this->updateNumberOfRelated();
		$res = parent::save($options);
		return $res;
	}
	public function getFields(): array{
		$fields = parent::getFields();
		$fields[] = VariableBaseAstralResource::hasMany("Variables");
		return $fields;
	}
	public function updateDBModel(): void{
		if(EnvOverride::isLocal() && AppMode::isAstral()){
			$this->saveHardCodedModel();
		}
	}
    public function canWriteMe($writer = null): bool
    {
        try {
            return QMAuth::isAdmin();
        } catch (AccessTokenExpiredException $e) {
            return false;
        }
    }

    public function patientGrantedAccess(string $accessType, User $accessor = null): bool{ return true; }
	/**
	 * @param null $reader
	 * @return bool
	 */
	public function canReadMe($reader = null): bool{ return true; }
	/**
	 * Get the actions available for the resource.
	 * @param Request $request
	 * @return array
	 */
	public function actions(Request $request): array{
		return [
			//new DeleteTestUsersAction($request),
			new GenerateHardCodedModelAction($request),
		];
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
	 * @return array
	 */
	protected static function getIndexDBModels(): array{
		return [
			BooksVariableCategory::instance(),
			CausesOfIllnessVariableCategory::instance(),
			CognitivePerformanceVariableCategory::instance(),
			ConditionsVariableCategory::instance(),
			//ElectronicsVariableCategory::instance(),
			EmotionsVariableCategory::instance(),
			EnvironmentVariableCategory::instance(),
			FoodsVariableCategory::instance(),
			GoalsVariableCategory::instance(),
			NutrientsVariableCategory::instance(),
			PhysicalActivityVariableCategory::instance(),
			PhysiqueVariableCategory::instance(),
			SleepVariableCategory::instance(),
			SocialInteractionsVariableCategory::instance(),
			SymptomsVariableCategory::instance(),
			TreatmentsVariableCategory::instance(),
			VitalSignsVariableCategory::instance(),
		];
	}
	public function getShowContentView(array $params = []): View{
		return view('variable-category-content', $this->getShowParams($params));
	}
	public function getNotFoundButtons(): array{
		return [
			OnboardingStateButton::instance(),
			Variable::getSearchAllIndexButton(),
		];
	}
	public function getIcon(): string{
		return $this->getImage();
	}
	public function getShowPageView(array $params = []): View{
		$params['model'] = $params['category'] = $this;
		return view('variable-category', $params);
	}
	/**
	 * @return Collection|Variable[]
	 */
	public function getVariablesOrButtons(){
		return $this->getQMVariableCategory()->getVariablesOrButtons();
	}
	public function getPlaceholder(): string{
		$name = $this->getNameSingular();
		if(empty($name)){
			le("no name!");
		}
		return "Search for a " . strtolower($name) . "...";
	}
	/**
	 * @return Builder|HasMany
	 */
	public function indexVariablesQB(){
		$dbm = $this->getQMVariableCategory();
		return $dbm->indexVariablesQB();
	}
	public function publicStudyVariablesQB(): Builder{
		$qb = Variable::indexQBWithCorrelations();
		$qb->where(Variable::FIELD_IS_PUBLIC, true);
		$qb->orderByDesc(Variable::FIELD_NUMBER_OF_USER_VARIABLES);
		$qb->where(Variable::FIELD_NUMBER_OF_USER_VARIABLES, ">", 1);
		return $qb->where(Variable::FIELD_VARIABLE_CATEGORY_ID, $this->getId());
	}
	public function getQMVariableCategory(): QMVariableCategory{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->getDBModel();
	}
	/**
	 * @return View
	 */
	public function getVariablesIndex(): View{
		return view('variable-category', [
			'c' => $this,
		]);
	}
	public function getVariableChipSearch(): string{
		if($this->getNumberOfOutcomePopulationStudies() > 20 || $this->getNumberOfPredictorPopulationStudies()){
			return VariableButton::chipSearchForCategoryWithStudies($this->getId());
		}
		return VariableButton::chipSearchForCategory($this->getId());
	}

	public function variableChipsHtml(): ?string{
		$buttons = $this->getVariableButtons();
		if(!$buttons){
			return null;
		}
		return VariableButton::toChipSearch($buttons, "Search for a " . $this->getNameSingular() . "...", $this->name);
	}
	public static function getIndexPageView(): View{
		$name = static::FIELD_NAME;
		return view('variable-categories-index', [
			'heading' => $name,
			'buttons' => static::getIndexButtons(),
		]);
	}
	public function getNewVariableData(): array{
		$data = [];
		$creator = $this->getDefaultCreatorUser();
		if($creator){
			$data[Variable::FIELD_CREATOR_USER_ID] = $creator->id;
		}
		if($clientId = $this->getDefaultClientId()){
			$data[Variable::FIELD_CLIENT_ID] = $clientId;
		}
		$data[Variable::FIELD_VARIABLE_CATEGORY_ID] = $this->getId();
		$data[Variable::FIELD_DEFAULT_UNIT_ID] = $this->getDefaultUnitId();
		$data[Variable::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS] =
			$this->getMinimumAllowedSecondsBetweenMeasurements();
		$data[Variable::FIELD_MINIMUM_ALLOWED_VALUE] = $this->getMinimumAllowedValueAttribute();
		$data[Variable::FIELD_MAXIMUM_ALLOWED_VALUE] = $this->getMaximumAllowedValueAttribute();
		$data[Variable::FIELD_FILLING_TYPE] = $this->getMaximumAllowedValueAttribute();
		return $data;
	}
	public function getDefaultCreatorUser(): ?User{
		return null;
	}
	public function getDefaultClientId(): ?string{
		return null;
	}
	public function getUrl(array $params = []): string{
		return qm_url($this->getShowFolderPath());
	}
//	public static function getIndexPath(): string{
//		return "categories";
//	}
	public function getAverageSecondsBetweenMeasurements(): ?int{
		return $this->attributes[VariableCategory::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS] ?? null;
	}
	public function getCauseOnly(): ?bool{
		return $this->attributes[VariableCategory::FIELD_CAUSE_ONLY] ?? null;
	}
	public function setCauseOnly(bool $causeOnly): void{
		$this->setAttribute(VariableCategory::FIELD_CAUSE_ONLY, $causeOnly);
	}
	public function setCombinationOperation(string $combinationOperation): void{
		$this->setAttribute(VariableCategory::FIELD_COMBINATION_OPERATION, $combinationOperation);
	}
	public function setDefaultUnitId(int $defaultUnitId): void{
		$this->setAttribute(VariableCategory::FIELD_DEFAULT_UNIT_ID, $defaultUnitId);
	}
	public function getDeletedAt(): ?string{
		return $this->attributes[VariableCategory::FIELD_DELETED_AT] ?? null;
	}
	public function setDeletedAt(string $deletedAt): void{
		$this->setAttribute(VariableCategory::FIELD_DELETED_AT, $deletedAt);
	}
	public function setDurationOfAction(int $durationOfAction): void{
		$this->setAttribute(VariableCategory::FIELD_DURATION_OF_ACTION, $durationOfAction);
	}
	public function setFillingTypeAttribute(string $fillingType): void{
		$this->attributes[VariableCategory::FIELD_FILLING_TYPE] = $fillingType;
	}
	public function setFillingValue(float $fillingValue): void{
		$this->setAttribute(VariableCategory::FIELD_FILLING_VALUE, $fillingValue);
	}
	public function setImageUrl(string $imageUrl): void{
		$this->setAttribute(VariableCategory::FIELD_IMAGE_URL, $imageUrl);
	}
	public function getIsPublic(): ?bool{
		return $this->attributes[VariableCategory::FIELD_IS_PUBLIC] ?? null;
	}
	public function setIsPublic(bool $isPublic): void{
		$this->setAttribute(VariableCategory::FIELD_IS_PUBLIC, $isPublic);
	}
	public function setManualTracking(bool $manualTracking): void{
		$this->setAttribute(VariableCategory::FIELD_MANUAL_TRACKING, $manualTracking);
	}
	public function setMaximumAllowedValue(float $maximumAllowedValue): void{
		$this->setAttribute(VariableCategory::FIELD_MAXIMUM_ALLOWED_VALUE, $maximumAllowedValue);
	}
	public function getMedianSecondsBetweenMeasurements(): ?int{
		return $this->attributes[VariableCategory::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS] ?? null;
	}


	public function setMinimumAllowedValue(float $minimumAllowedValue): void{
		$this->setAttribute(VariableCategory::FIELD_MINIMUM_ALLOWED_VALUE, $minimumAllowedValue);
	}
	public function setName(string $name): void{
		$this->setAttribute(VariableCategory::FIELD_NAME, $name);
	}
	public function getNumberOfMeasurements(): ?int{
		return $this->attributes[VariableCategory::FIELD_NUMBER_OF_MEASUREMENTS] ?? null;
	}
	public function setNumberOfMeasurements(?int $numberOfMeasurements): void{
		$this->setAttribute(VariableCategory::FIELD_NUMBER_OF_MEASUREMENTS, $numberOfMeasurements);
	}
	public function getNumberOfOutcomeCaseStudies(): ?int{
		return $this->attributes[VariableCategory::FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES] ?? null;
	}
	public function setNumberOfOutcomeCaseStudies(int $numberOfOutcomeCaseStudies): void{
		$this->setAttribute(VariableCategory::FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES, $numberOfOutcomeCaseStudies);
	}
	public function getNumberOfOutcomePopulationStudies(): ?int{
		return $this->attributes[VariableCategory::FIELD_NUMBER_OF_OUTCOME_POPULATION_STUDIES] ?? null;
	}

	public function getNumberOfPredictorCaseStudies(): ?int{
		return $this->attributes[VariableCategory::FIELD_NUMBER_OF_PREDICTOR_CASE_STUDIES] ?? null;
	}
	public function setNumberOfPredictorCaseStudies(int $numberOfPredictorCaseStudies): void{
		$this->setAttribute(VariableCategory::FIELD_NUMBER_OF_PREDICTOR_CASE_STUDIES, $numberOfPredictorCaseStudies);
	}
	public function getNumberOfPredictorPopulationStudies(): ?int{
		return $this->attributes[VariableCategory::FIELD_NUMBER_OF_PREDICTOR_POPULATION_STUDIES] ?? null;
	}

	public function getNumberOfUserVariables(): ?int{
		return $this->attributes[VariableCategory::FIELD_NUMBER_OF_USER_VARIABLES] ?? null;
	}
	public function setNumberOfUserVariables(int $numberOfUserVariables): void{
		$this->setAttribute(VariableCategory::FIELD_NUMBER_OF_USER_VARIABLES, $numberOfUserVariables);
	}
	public function getNumberOfVariables(): ?int{
		return $this->attributes[VariableCategory::FIELD_NUMBER_OF_VARIABLES] ?? null;
	}
	public function setNumberOfVariables(int $numberOfVariables): void{
		$this->setAttribute(VariableCategory::FIELD_NUMBER_OF_VARIABLES, $numberOfVariables);
	}
	public function setOnsetDelay(int $onsetDelay): void{
		$this->setAttribute(VariableCategory::FIELD_ONSET_DELAY, $onsetDelay);
	}
	public function getOutcome(): ?bool{
		return $this->attributes[VariableCategory::FIELD_OUTCOME] ?? null;
	}
	public function setOutcome(bool $outcome): void{
		$this->setAttribute(VariableCategory::FIELD_OUTCOME, $outcome);
	}
	public function getWpPostId(): ?int{
		return $this->attributes[VariableCategory::FIELD_WP_POST_ID] ?? null;
	}
	public function setWpPostId(int $wpPostId): void{
		$this->setAttribute(VariableCategory::FIELD_WP_POST_ID, $wpPostId);
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		$arr = [];
		$arr[] = new VariableCategoryGlobalVariableRelationshipsWhereCauseVariableCategoryButton($this);
		$arr[] = new VariableCategoryGlobalVariableRelationshipsWhereEffectVariableCategoryButton($this);
		$arr[] = new VariableCategoryVariablesButton($this);
		return $arr;
	}
	public function getTopMenu(): QMMenu{
		return JournalMenu::instance();
	}
	/**
	 * @return QMButton[]
	 */
	public static function getIndexButtons(): array{
		return static::toButtons(VariableCategory::all());
	}
	/**
	 * @param $models
	 * @return QMButton[]
	 */
	public static function toButtons($models): array{
		return QMButton::toButtons($models);
	}
	public function getKeyWords(): array{
		return $this->getSynonymsAttribute();
	}

	/**
	 * @param $slug
	 * @return string
	 */
	public static function fromSlug($slug): string{
		return str_replace("_", " ", $slug);
	}
	/**
	 * @param string $name
	 * @return string
	 */
	public static function toSlug(string $name): string{
		return str_replace(" ", "_", $name);
	}
	public function getSlug(): string{
		return Variable::toSlug($this->getNameAttribute());
	}
	public function getSortingScore(): float{
		return $this->getNumberOfUserVariables() ?? 0;
	}
	public static function getS3Bucket(): string{ return S3Public::getBucketName(); }
	public function getAmazonProductCategory(): ?string{
		return $this->attributes[VariableCategory::FIELD_AMAZON_PRODUCT_CATEGORY] ?? null;
	}
	public function setAmazonProductCategory(string $amazonProductCategory): void{
		$this->setAttribute(VariableCategory::FIELD_AMAZON_PRODUCT_CATEGORY, $amazonProductCategory);
	}
	public function getBoring(): ?bool{
		return $this->attributes[VariableCategory::FIELD_BORING] ?? null;
	}
	public function setBoring(bool $boring): void{
		$this->setAttribute(VariableCategory::FIELD_BORING, $boring);
	}
	public function getCombinationOperation(): ?string{
		return $this->attributes[VariableCategory::FIELD_COMBINATION_OPERATION] ?? null;
	}
	public function getCreatedAt(): ?string{
		return $this->attributes[VariableCategory::FIELD_CREATED_AT] ?? null;
	}
	public function getDefaultUnitId(): ?int{
		return $this->attributes[VariableCategory::FIELD_DEFAULT_UNIT_ID] ?? null;
	}
	public function getDurationOfAction(): ?int{
		return $this->attributes[VariableCategory::FIELD_DURATION_OF_ACTION] ?? null;
	}
	public function getEffectOnly(): ?bool{
		return $this->attributes[VariableCategory::FIELD_EFFECT_ONLY] ?? null;
	}
	public function setEffectOnly(bool $effectOnly): void{
		$this->setAttribute(VariableCategory::FIELD_EFFECT_ONLY, $effectOnly);
	}
	public function getFillingType(): ?string{
		return $this->attributes[VariableCategory::FIELD_FILLING_TYPE] ?? null;
	}
	public function getFillingValueAttribute(): ?float{
		return $this->attributes[VariableCategory::FIELD_FILLING_VALUE] ?? null;
	}
	public function setFontAwesome(string $fontAwesome): void{
		$this->setAttribute(VariableCategory::FIELD_FONT_AWESOME, $fontAwesome);
	}
	public function getImageUrl(): ?string{
		return $this->attributes[VariableCategory::FIELD_IMAGE_URL] ?? null;
	}
	public function getIonIcon(): ?string{
		return $this->attributes[VariableCategory::FIELD_ION_ICON] ?? null;
	}
	public function setIonIcon(string $ionIcon): void{
		$this->setAttribute(VariableCategory::FIELD_ION_ICON, $ionIcon);
	}
	public function getManualTracking(): ?bool{
		return $this->attributes[VariableCategory::FIELD_MANUAL_TRACKING] ?? null;
	}
	public function getMaximumAllowedValueAttribute(): ?float{
		return $this->attributes[VariableCategory::FIELD_MAXIMUM_ALLOWED_VALUE] ?? null;
	}
	public function getMinimumAllowedSecondsBetweenMeasurements(): ?int{
		return $this->attributes[VariableCategory::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS] ?? null;
	}
	public function getMinimumAllowedValueAttribute(): ?float{
		return $this->attributes[VariableCategory::FIELD_MINIMUM_ALLOWED_VALUE] ?? null;
	}
	public function getMoreInfo(): ?string{
		return $this->attributes[VariableCategory::FIELD_MORE_INFO] ?? null;
	}
	public function setMoreInfo(string $moreInfo): void{
		$this->setAttribute(VariableCategory::FIELD_MORE_INFO, $moreInfo);
	}
	public function getNameSingular(): string{
		$name = $this->attributes[VariableCategory::FIELD_NAME_SINGULAR] ?? null;
		if(empty($name)){
			$name = $this->attributes[VariableCategory::FIELD_NAME_SINGULAR] = QMStr::singularize($this->getNameAttribute());
		}
		return $name;
	}
	public function setNameSingular(string $nameSingular): void{
		$this->setAttribute(VariableCategory::FIELD_NAME_SINGULAR, $nameSingular);
	}
	public function getOnsetDelay(): ?int{
		return $this->attributes[VariableCategory::FIELD_ONSET_DELAY] ?? null;
	}
	public function getPredictor(): bool{
		if(property_exists($this, 'attributes') && $this->attributes){
			$val = $this->attributes[VariableCategory::FIELD_PREDICTOR];
		} else{
			/** @var QMVariableCategory $this */
			$val = $this->predictor;
		}
		if($val === null){
			le("$this", $this);
		}
		return $val;
	}
	public function setPredictor(bool $predictor): void{
		$this->setAttribute(VariableCategory::FIELD_PREDICTOR, $predictor);
	}
	public function getSynonymsAttribute(): array{
		$val =  $this->attributes[VariableCategory::FIELD_SYNONYMS] ?? [];
        if(!is_array($val)){
            $val = json_decode($val, true);
        }
		if($val === null){$val = [];}
        return $this->attributes[VariableCategory::FIELD_SYNONYMS] = $val;
	}
	public function setSynonyms(array $synonyms): void{
		$this->setAttribute(VariableCategory::FIELD_SYNONYMS, $synonyms);
	}
	public function getUpdatedAt(): ?string{
		return $this->attributes[VariableCategory::FIELD_UPDATED_AT] ?? null;
	}
	public function getValence(): ?string{
		return $this->attributes[VariableCategory::FIELD_VALENCE] ?? null;
	}
	public function setValence(string $valence): void{
		$this->setAttribute(VariableCategory::FIELD_VALENCE, $valence);
	}
	public static function getIndexContentView(): View{
		return view('variable-categories-index-content', ['categories' => self::getIndexModels()]);
	}
	public static function getIndexModels(): Collection{
		if($mem = static::getFromClassMemory(__FUNCTION__)){
			return $mem;
		}
		$coll = static::query()->whereIn(static::FIELD_ID, VariableCategory::getInterestingCategoryIds())->get();
		return static::setInClassMemory(__FUNCTION__, $coll);
	}
	/**
	 * @return QMButton
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getButton(){
		$b = static::toButton($this);
		$b->setBadgeText($this->getNumberOfVariables());
		$b->setTooltip($this->getNumberOfVariables() . " " . $this->getNameAttribute() . " Examined");
		return $b;
	}
	/**
	 * @return Variable[]|\Illuminate\Database\Eloquent\Collection
	 * @noinspection PhpReturnDocTypeMismatchInspection
	 */
	public function getAllVariables(){
		return $this->getAllPublicVariablesQB()->get();
	}
	public function getAllPublicVariablesQB(): Builder{
		return Variable::indexSelectQB()->where(Variable::FIELD_NUMBER_OF_USER_VARIABLES, ">", 1)
			->orderBy(VariableCategory::FIELD_NUMBER_OF_USER_VARIABLES, 'desc')->where(Variable::FIELD_IS_PUBLIC, true)
			->where(Variable::FIELD_VARIABLE_CATEGORY_ID, $this->getId());
	}
	public function getBadgeText(): ?string{
		return $this->getNumberOfUserVariables();
	}
	public static function getInterestingCategories(): array{
		$arr = [];
		foreach(self::all() as $category){
			if(!$category->isBoring()){
				$arr[] = $category;
			}
		}
		return $arr;
	}
	public static function getInterestingCategoryIds(): array{
		$ids = [];
		foreach(static::getInterestingCategories() as $category){
			$ids[] = $category->getId();
		}
		return $ids;
	}
	public static function getInterestingCategoryNames(): array{
		$ids = [];
		foreach(self::all() as $category){
			if(!$category->isBoring()){
				$ids[] = $category->getNameAttribute();
			}
		}
		return $ids;
	}
	/**
	 * @return bool
	 */
	public function isBoring(): bool{
		return (bool)$this->boring;
	}
	/**
	 * @return QMVariableCategory[]
	 */
	public static function getBoringVariableCategories(): array{
		$categories = self::all();
		$boring = [];
		foreach($categories as $category){
			if($category->isBoring()){
				$boring[] = $category;
			}
		}
		return $boring;
	}
	/**
	 * @return QMVariableCategory[]
	 */
	public static function getBoringVariableCategoryIds(): array{
		$categories = self::getBoringVariableCategories();
		$boring = [];
		foreach($categories as $category){
			$boring[] = $category->getId();
		}
		return $boring;
	}
	/**
	 * @return array
	 */
	public static function getStupidCategoryNames(): array{
		return [
			PaymentsVariableCategory::NAME,
			MiscellaneousVariableCategory::NAME,
			CausesOfIllnessVariableCategory::NAME,
		];
	}
	public static function getAppsLocationsWebsiteIds(): array{
		return [
			SoftwareVariableCategory::ID,
			LocationsVariableCategory::ID,
			PaymentsVariableCategory::ID,
		];
	}
	public static function getOutcomeIds(): array{
		return [
			SymptomsVariableCategory::ID,
			EmotionsVariableCategory::ID,
			ConditionsVariableCategory::ID,
			GoalsVariableCategory::ID,
			CognitivePerformanceVariableCategory::ID,
			SleepVariableCategory::ID,
			VitalSignsVariableCategory::ID,
		];
	}
	/**
	 * @return string
	 */
	public function getImageHTML(): string{
		return HtmlHelper::getImageHtml($this->getImageUrl(), $this->getNameAttribute());
	}

	public function getAverageVariableValue(string $column): ?float{
		return $this->getVariableQB()->average($column);
	}
	public function getVariableValues(string $column): Collection{
		return $this->getVariableQB()->pluck($column);
	}
	/**
	 * @return Variable|\Illuminate\Database\Query\Builder
	 */
	public function getVariableQB(){
		return Variable::whereVariableCategoryId($this->getId());
	}
	public static function getNames(): array{
		return QMVariableCategory::getVariableCategoryNames();
	}
	/**
	 * @return Builder|\Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function allPublicVariablesQB(){
		return $this->l()->variables()->where(Variable::FIELD_IS_PUBLIC, true);
	}
	/**
	 * @param $nameOrId
	 * @return \App\Models\VariableCategory|null
	 */
	public static function findByNameIdOrSynonym($nameOrId): ?VariableCategory{
		$dbm = QMVariableCategory::findByNameIdOrSynonym($nameOrId);
		return ($dbm) ? $dbm->getVariableCategory() : null;
	}
	/**
	 * @param int|string $idOrUniqueIndex
	 * @return \App\Models\VariableCategory|null
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public static function findInMemory($idOrUniqueIndex): ?VariableCategory {
		$mem = parent::findInMemory($idOrUniqueIndex);
		if($mem){return $mem;}
		$dmb = QMVariableCategory::findByNameIdOrSynonym($idOrUniqueIndex);
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $dmb->attachedOrNewLaravelModel();
	}
    /**
     * Exclude an array of elements from the result.
     * @param Builder|QueryBuilder $query
     * @return mixed
     */
    protected function applyRequestPermissions($query): Builder{
        //$this->restrictQueryBasedOnPermissions($query->getQuery());
        return $query;
    }
	public static function importVincenzoCategories(): void{
		$ucum = JsonFile::getArray('data/OCVariableCategory.json');
		foreach($ucum as $one){
			//		"Code": "%",
			//    "Descriptive_Name": "Percent [Most Common Healthcare Units]",
			//    "Code_System": "PH_UnitsOfMeasure_UCUM_Expression",
			//    "Definition": null,
			//    "Date_Created": "7/1/2004",
			//    "Synonym": "%",
			//    "Status": "Active",
			//    "Kind_of_Quantity": "Most Common Healthcare Units",
			//    "Date_Revised": "12/8/2005",
			//    "ConceptID": "Percent",
			//    "Dimension": "1",
			$model = self::whereName($one['name'])->first();
			if(!$model){
				$model = new static();
			}
			foreach($one as $key => $value){
				if($value === null){continue;}
				if(isset($map[$key])){$key = $map[$key];}
				if($key === 'id'){continue;}
				$existing = $model->attributes[$key] ?? null;
				if($existing === null){
					$model->setAttribute($key, $value);
				}
			}
			if(!$model->exists){
				$model->boring = true;
			}
			if(!$model->amazon_product_category){
				$model->amazon_product_category = "Unknown";
			}
			if(!$model->name_singular){
				$model->name_singular = QMStr::singularize($model->name);
			}
			if(!$model->is_goal){
				$model->is_goal = VariableCategoryIsGoalProperty::SOMETIMES;
			}
			if(!$model->controllable){
				$model->controllable = VariableCategoryControllableProperty::SOMETIMES;
			}
			$model->save();
		}
		self::saveJson();
	}
}
