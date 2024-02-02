<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Exceptions\AccessTokenExpiredException;
use App\Files\Json\JsonFile;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Properties\Unit\UnitScaleProperty;
use App\Traits\HasJsonFile;
use App\VariableCategories\MiscellaneousVariableCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueException;
use App\Models\Base\BaseUnit;
use App\Astral\VariableBaseAstralResource;
use App\Properties\Unit\UnitAbbreviatedNameProperty;
use App\Properties\Unit\UnitIdProperty;
use App\Properties\UnitCategory\UnitCategoryIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Slim\Model\QMUnit;
use App\Slim\Model\QMUnitCategory;
use App\Storage\S3\S3Public;
use App\Traits\HasDBModel;
use App\Traits\ModelTraits\UnitTrait;
use App\Traits\QMAnalyzableTrait;
use App\Types\QMArr;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UnitCategories\CountUnitCategory;
use App\UnitCategories\CurrencyUnitCategory;
use App\UnitCategories\DurationUnitCategory;
use App\UnitCategories\RatingUnitCategory;
use App\UnitCategories\WeightUnitCategory;
use App\Units\InternationalUnitsUnit;
use App\Units\YesNoUnit;
use App\Utils\AppMode;
use App\Utils\EnvOverride;
use App\Variables\QMUserVariable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Fields\Avatar;
use App\Fields\HasMany;
use App\Fields\Text;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\Tags\Tag;
use Str;
/**
 * App\Models\Unit
 * @SWG\Definition (
 *      definition="Unit",
 *      required={""},
 *      @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="name",
 *          description="Unit name",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="abbreviated_name",
 *          description="Unit abbreviation",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="unit_category_id",
 *          description="Unit category ID",
 *          type="boolean"
 *      ),
 *      @SWG\Property(
 *          property="minimum_value",
 *          description="Unit minimum value",
 *          type="number",
 *          format="number"
 *      ),
 *      @SWG\Property(
 *          property="maximum_value",
 *          description="Unit maximum value",
 *          type="number",
 *          format="number"
 *      ),
 *      @SWG\Property(
 *          property="created_at",
 *          description="created_at",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="updated_at",
 *          description="updated_at",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="deleted_at",
 *          description="deleted_at",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="filling_type",
 *          description="filling_type",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="number_of_outcome_population_studies",
 *          description="Number of Global Population Studies for this Cause Unit.
 * [Formula:
 * update units
 * left join (
 * select count(id) as total, cause_unit_id
 * from global_variable_relationships
 * group by cause_unit_id
 * )
 * as grouped on units.id = grouped.cause_unit_id
 * set units.number_of_outcome_population_studies = count(grouped.total)
 * ]
 * ",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="number_of_common_tags_where_tag_variable_unit",
 *          description="Number of Common Tags for this Tag Variable Unit.
 * [Formula:
 * update units
 * left join (
 * select count(id) as total, tag_variable_unit_id
 * from common_tags
 * group by tag_variable_unit_id
 * )
 * as grouped on units.id = grouped.tag_variable_unit_id
 * set units.number_of_common_tags_where_tag_variable_unit = count(grouped.total)
 * ]
 * ",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="number_of_common_tags_where_tagged_variable_unit",
 *          description="Number of Common Tags for this Tagged Variable Unit.
 * [Formula:
 * update units
 * left join (
 * select count(id) as total, tagged_variable_unit_id
 * from common_tags
 * group by tagged_variable_unit_id
 * )
 * as grouped on units.id = grouped.tagged_variable_unit_id
 * set units.number_of_common_tags_where_tagged_variable_unit = count(grouped.total)
 * ]
 * ",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="number_of_outcome_case_studies",
 *          description="Number of Individual Case Studies for this Cause Unit.
 * [Formula:
 * update units
 * left join (
 * select count(id) as total, cause_unit_id
 * from user_variable_relationships
 * group by cause_unit_id
 * )
 * as grouped on units.id = grouped.cause_unit_id
 * set units.number_of_outcome_case_studies = count(grouped.total)
 * ]
 * ",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="number_of_measurements",
 *          description="Number of Measurements for this Unit.
 * [Formula: update units
 * left join (
 * select count(id) as total, unit_id
 * from measurements
 * group by unit_id
 * )
 * as grouped on units.id = grouped.unit_id
 * set units.number_of_measurements = count(grouped.total)]",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="number_of_user_variables_where_default_unit",
 *          description="Number of User Variables for this Default Unit.
 * [Formula: update units
 * left join (
 * select count(id) as total, default_unit_id
 * from user_variables
 * group by default_unit_id
 * )
 * as grouped on units.id = grouped.default_unit_id
 * set units.number_of_user_variables_where_default_unit = count(grouped.total)]",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="number_of_variable_categories_where_default_unit",
 *          description="Number of Variable Categories for this Default Unit.
 * [Formula: update units
 * left join (
 * select count(id) as total, default_unit_id
 * from variable_categories
 * group by default_unit_id
 * )
 * as grouped on units.id = grouped.default_unit_id
 * set units.number_of_variable_categories_where_default_unit = count(grouped.total)]",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="number_of_variables_where_default_unit",
 *          description="Number of Variables for this Default Unit.
 * [Formula: update units
 * left join (
 * select count(id) as total, default_unit_id
 * from variables
 * group by default_unit_id
 * )
 * as grouped on units.id = grouped.default_unit_id
 * set units.number_of_variables_where_default_unit = count(grouped.total)]",
 *          type="integer",
 *          format="int32"
 *      )
 * )
 * @property int $id
 * @property string $name Unit name
 * @property string $abbreviated_name Unit abbreviation
 * @property bool $unit_category_id Unit category ID
 * @property float|null $minimum_value Unit minimum value
 * @property float|null $maximum_value Unit maximum value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $filling_type
 * @property int|null $number_of_outcome_population_studies Number of Global Population Studies for this Cause Unit.
 *                 [Formula:
 *                     update units
 *                         left join (
 *                             select count(id) as total, cause_unit_id
 *                             from global_variable_relationships
 *                             group by cause_unit_id
 *                         )
 *                         as grouped on units.id = grouped.cause_unit_id
 *                     set units.number_of_outcome_population_studies = count(grouped.total)
 *                 ]
 * @property int|null $number_of_common_tags_where_tag_variable_unit Number of Common Tags for this Tag Variable Unit.
 *                 [Formula:
 *                     update units
 *                         left join (
 *                             select count(id) as total, tag_variable_unit_id
 *                             from common_tags
 *                             group by tag_variable_unit_id
 *                         )
 *                         as grouped on units.id = grouped.tag_variable_unit_id
 *                     set units.number_of_common_tags_where_tag_variable_unit = count(grouped.total)
 *                 ]
 * @property int|null $number_of_common_tags_where_tagged_variable_unit Number of Common Tags for this Tagged Variable
 *     Unit.
 *                 [Formula:
 *                     update units
 *                         left join (
 *                             select count(id) as total, tagged_variable_unit_id
 *                             from common_tags
 *                             group by tagged_variable_unit_id
 *                         )
 *                         as grouped on units.id = grouped.tagged_variable_unit_id
 *                     set units.number_of_common_tags_where_tagged_variable_unit = count(grouped.total)
 *                 ]
 * @property int|null $number_of_outcome_case_studies Number of Individual Case Studies for this Cause Unit.
 *                 [Formula:
 *                     update units
 *                         left join (
 *                             select count(id) as total, cause_unit_id
 *                             from user_variable_relationships
 *                             group by cause_unit_id
 *                         )
 *                         as grouped on units.id = grouped.cause_unit_id
 *                     set units.number_of_outcome_case_studies = count(grouped.total)
 *                 ]
 * @property int|null $number_of_measurements Number of Measurements for this Unit.
 *                     [Formula: update units
 *                         left join (
 *                             select count(id) as total, unit_id
 *                             from measurements
 *                             group by unit_id
 *                         )
 *                         as grouped on units.id = grouped.unit_id
 *                     set units.number_of_measurements = count(grouped.total)]
 * @property int|null $number_of_user_variables_where_default_unit Number of User Variables for this Default Unit.
 *                     [Formula: update units
 *                         left join (
 *                             select count(id) as total, default_unit_id
 *                             from user_variables
 *                             group by default_unit_id
 *                         )
 *                         as grouped on units.id = grouped.default_unit_id
 *                     set units.number_of_user_variables_where_default_unit = count(grouped.total)]
 * @property int|null $number_of_variable_categories_where_default_unit Number of Variable Categories for this Default
 *     Unit.
 *                     [Formula: update units
 *                         left join (
 *                             select count(id) as total, default_unit_id
 *                             from variable_categories
 *                             group by default_unit_id
 *                         )
 *                         as grouped on units.id = grouped.default_unit_id
 *                     set units.number_of_variable_categories_where_default_unit = count(grouped.total)]
 * @property int|null $number_of_variables_where_default_unit Number of Variables for this Default Unit.
 *                     [Formula: update units
 *                         left join (
 *                             select count(id) as total, default_unit_id
 *                             from variables
 *                             group by default_unit_id
 *                         )
 *                         as grouped on units.id = grouped.default_unit_id
 *                     set units.number_of_variables_where_default_unit = count(grouped.total)]
 * @property-read Collection|GlobalVariableRelationship[] $aggregateCorrelations
 * @property-read int|null $global_variable_relationships_count
 * @property-read Collection|GlobalVariableRelationship[] $global_variable_relationships_where_cause_unit
 * @property-read int|null $global_variable_relationships_where_cause_unit_count
 * @property-read Collection|CommonTag[] $commonTags
 * @property-read int|null $common_tags_count
 * @property-read Collection|CommonTag[] $common_tags_where_tag_variable_unit
 * @property-read int|null $common_tags_where_tag_variable_unit_count
 * @property-read Collection|CommonTag[] $common_tags_where_tagged_variable_unit
 * @property-read int|null $common_tags_where_tagged_variable_unit_count
 * @property-read Collection|UserVariableRelationship[] $user_variable_relationships
 * @property-read int|null $correlations_count
 * @property-read Collection|UserVariableRelationship[] $correlations_where_cause_unit
 * @property-read int|null $correlations_where_cause_unit_count
 * @property-read Collection|Measurement[] $measurements
 * @property-read int|null $measurements_count
 * @property-read Collection|Measurement[] $measurements_where_original_unit
 * @property-read int|null $measurements_where_original_unit_count
 * @property-read Collection|UserVariable[] $userVariables
 * @property-read int|null $user_variables_count
 * @property-read Collection|UserVariable[] $user_variables_where_default_unit
 * @property-read int|null $user_variables_where_default_unit_count
 * @property-read Collection|UserVariable[] $user_variables_where_last_unit
 * @property-read int|null $user_variables_where_last_unit_count
 * @property-read Collection|VariableCategory[] $variableCategories
 * @property-read int|null $variable_categories_count
 * @property-read Collection|VariableCategory[] $variable_categories_where_default_unit
 * @property-read int|null $variable_categories_where_default_unit_count
 * @property-read Collection|Variable[] $variables
 * @property-read int|null $variables_count
 * @property-read Collection|Variable[] $variables_where_default_unit
 * @property-read int|null $variables_where_default_unit_count
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|Unit newModelQuery()
 * @method static Builder|Unit newQuery()
 * @method static \Illuminate\Database\Query\Builder|Unit onlyTrashed()
 * @method static Builder|Unit query()
 * @method static Builder|Unit whereAbbreviatedName($value)
 * @method static Builder|Unit whereCategoryId($value)
 * @method static Builder|Unit whereCreatedAt($value)
 * @method static Builder|Unit whereDeletedAt($value)
 * @method static Builder|Unit whereFillingType($value)
 * @method static Builder|Unit whereId($value)
 * @method static Builder|Unit whereMaximumValue($value)
 * @method static Builder|Unit whereMinimumValue($value)
 * @method static Builder|Unit whereName($value)
 * @method static Builder|Unit whereNumberOfCommonTagsWhereTagVariableUnit($value)
 * @method static Builder|Unit whereNumberOfCommonTagsWhereTaggedVariableUnit($value)
 * @method static Builder|Unit whereNumberOfMeasurements($value)
 * @method static Builder|Unit whereNumberOfOutcomeCaseStudies($value)
 * @method static Builder|Unit whereNumberOfOutcomePopulationStudies($value)
 * @method static Builder|Unit whereNumberOfUserVariablesWhereDefaultUnit($value)
 * @method static Builder|Unit whereNumberOfVariableCategoriesWhereDefaultUnit($value)
 * @method static Builder|Unit whereNumberOfVariablesWhereDefaultUnit($value)
 * @method static Builder|Unit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Unit withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Unit withoutTrashed()
 * @mixin \Eloquent
 * @property int $advanced Advanced units are rarely used and should generally be hidden or at the bottom of selector
 *     lists
 * @property int $manual_tracking Include manual tracking units in selector when manually recording a measurement.
 * @property float|null $filling_value The filling value is substituted used when data is missing if the filling type
 *     is set to value.
 * @property string $scale
 * Ordinal is used to simply depict the order of variables and not the difference between each of the variables.
 *     Ordinal scales are generally used to depict non-mathematical ideas such as frequency, satisfaction, happiness, a
 *     degree of pain etc. Ratio Scale not only produces the order of variables but also makes the difference between
 *     variables known along with information on the value of true zero. Interval scale contains all the properties of
 *     ordinal scale, in addition to which, it offers a calculation of the difference between variables. The main
 *     characteristic of this scale is the equidistant difference between objects. Interval has no pre-decided starting
 *     point or a true zero value. Nominal, also called the categorical variable scale, is defined as a scale used for
 *     labeling variables into distinct classifications and doesn’t involve a quantitative value or order.
 * @property mixed|null $conversion_steps An array of mathematical operations, each containing a operation and value
 *     field to apply to the value in the current unit to convert it to the default unit for the unit category.
 * @property float|null $maximum_daily_value The maximum aggregated measurement value over a single day.
 * @method static Builder|Unit whereAdvanced($value)
 * @method static Builder|Unit whereConversionSteps($value)
 * @method static Builder|Unit whereFillingValue($value)
 * @method static Builder|Unit whereManualTracking($value)
 * @method static Builder|Unit whereMaximumDailyValue($value)
 * @method static Builder|Unit whereScale($value)
 * @property mixed $raw
 * @method static Builder|Unit whereUnitCategoryId($value)
 * @property int $sort_order
 * @property array $synonyms
 * @property Collection|Tag[] $tags
 * @property-read int|null $tags_count
 * @method static Builder|Unit whereSortOrder($value)
 * @method static Builder|Unit withAllTags($tags, $type = null)
 * @method static Builder|Unit withAllTagsOfAnyType($tags)
 * @method static Builder|Unit withAnyTags($tags, $type = null)
 * @method static Builder|Unit withAnyTagsOfAnyType($tags)
 * @property string|null $slug The slug is the part of a URL that identifies a page in human-readable keywords.
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 * @method static Builder|Unit whereSlug($value)
 */
class Unit extends BaseUnit {
    use HasFactory;

    use HasJsonFile;
	use UnitTrait;
	use SoftDeletes, HasDBModel;
	// TODO: use Cachable;
	const CLASS_CATEGORY = "Units";
	public $table = self::TABLE;
    public $with = ['unit_category'];
	public static function getSlimClass(): string{ return QMUnit::class; }
	public const CLASS_DESCRIPTION = 'Units of measurement such as milligrams or a one-to-five rating scale.';
	public const DEFAULT_IMAGE = ImageUrls::FITNESS_MEASURING_TAPE;
	public const FONT_AWESOME = FontAwesome::RULER_COMBINED_SOLID;
	public const DEFAULT_ORDERINGS = [
		self::FIELD_ADVANCED => self::ORDER_DIRECTION_ASC,
		self::FIELD_NAME => self::ORDER_DIRECTION_ASC,
	];
	public const DEFAULT_LIMIT = 200;
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 **/
	public function aggregateCorrelations(): \Illuminate\Database\Eloquent\Relations\HasMany{
		return $this->hasMany(GlobalVariableRelationship::class, 'cause_unit_id');
	}
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 **/
	public function commonTags(): \Illuminate\Database\Eloquent\Relations\HasMany{
		return $this->hasMany(CommonTag::class, 'tag_variable_unit_id');
	}
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 **/
	public function correlations(): \Illuminate\Database\Eloquent\Relations\HasMany{
		return $this->hasMany(UserVariableRelationship::class, 'cause_unit_id');
	}
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 **/
	public function userVariables(): \Illuminate\Database\Eloquent\Relations\HasMany{
		return $this->hasMany(UserVariable::class, 'default_unit_id');
	}
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 **/
	public function variableCategories(): \Illuminate\Database\Eloquent\Relations\HasMany{
		return $this->hasMany(VariableCategory::class, 'default_unit_id');
	}
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 **/
	public function variables(): \Illuminate\Database\Eloquent\Relations\HasMany{
		return $this->hasMany(Variable::class, 'default_unit_id');
	}
	public function getSubtitleAttribute(): string{
		if(!$this->hasId()){
			return static::getClassDescription();
		}
		return $this->getDBModel()->getSubtitleAttribute();
	}
	public function getFontAwesome(): string{
		if(!$this->hasId()){
			return static::FONT_AWESOME;
		}
		return $this->getDBModel()->getFontAwesome();
	}
	public function getImage(): string{
		if(!$this->hasId()){
			return static::DEFAULT_IMAGE;
		}
		return $this->getDBModel()->getImage();
	}
	/**
	 * @return DBModel|QMUnit
	 */
	public function getDBModel(): DBModel{
		return QMUnit::find($this->id);
	}
	public function getCategoryLink(): string{
		return $this->getUnitCategory()->getDataLabDisplayNameLink();
	}
	public function getUnitCategory(): QMUnitCategory{
		return $this->getDBModel()->getUnitCategory();
	}
	/**
	 * @param array|mixed|string[] $columns
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public static function all($columns = ['*']): Collection {
        if($cached = static::getFromClassMemory(__FUNCTION__)){
            return $cached;
        }
        return static::setInClassMemory(__FUNCTION__, parent::all());
	}
	/**
	 * @param float $value
	 * @param int $toUnitId
	 * @param QMAnalyzableTrait $v
	 * @param int|null $durationInSeconds
	 * @return float
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function convertTo(float $value, int $toUnitId, $v, int $durationInSeconds = null): float{
		return QMUnit::convertValueByUnitIds($value, $this->id, $toUnitId, $v, $durationInSeconds);
	}
	/**
	 * @return bool
	 */
	public function isCountCategory(): bool{
		return $this->unit_category_id == CountUnitCategory::ID;
	}
	/**
	 * @return bool
	 */
	public function isDurationCategory(): bool{
		return $this->unit_category_id === DurationUnitCategory::ID;
	}
	/**
	 * @return bool
	 */
	public function isWeightCategory(): bool{
		return $this->unit_category_id === WeightUnitCategory::ID;
	}
	/**
	 * @return bool
	 */
	public function isWeightCategoryOrInternationalUnits(): bool{
		return $this->isWeightCategory() || $this->name === InternationalUnitsUnit::NAME;
	}
	/**
	 * @return bool
	 */
	public function isCurrency(): bool{
		return $this->unit_category_id === CurrencyUnitCategory::ID;
	}
	/**
	 * @return bool
	 */
	public function isRating(): bool{
		return $this->unit_category_id === RatingUnitCategory::ID;
	}
	/**
	 * @return bool
	 */
	public function isYesNo(): bool{
		return $this->id === YesNoUnit::ID;
	}
	/**
	 * @param string $key
	 * @param $new
	 */
	public function setAttributeIfDifferentFromAccessor(string $key, $new){
		if($key === self::FIELD_CONVERSION_STEPS){
			$existing = $this->conversion_steps;
			$a = json_decode(json_encode($new), true);
			$b = json_decode(json_encode($existing), true);
			if(QMArr::arraysAreEqual($a, $b)){
				return;
			}
		}
		parent::setAttributeIfDifferentFromAccessor($key, $new);
	}
	/**
	 * @param $nameOrId
	 * @return Unit|null
	 */
	public static function findByNameIdOrSynonym($nameOrId): ?Unit{
		$u = QMUnit::findByNameIdOrSynonym($nameOrId);
		if(!$u){
			return null;
		}
		return $u->l();
	}
	public function updateDBModel(): void{
		if(EnvOverride::isLocal() && AppMode::isAstral()){
			$this->saveHardCodedModel();
		}
	}
	/**
	 * Get the fields displayed by the resource.
	 * @param Request $request
	 * @return array
	 */
	public function fields(Request $request): array{
		return [
			Avatar::make(str_repeat(' ', 8), function(){
				/** @var VariableCategory $this */
				return $this->getImage();
			})->disk(S3Public::DISK_NAME)->path('images/' . Unit::TABLE)->maxWidth(50)->squared()->disableDownload()
				->thumbnail(function(){
					/** @var Unit $this */
					return $this->getImage();
				})->preview(function(){
					/** @var VariableCategory $this */
					return $this->getImage();
				}),
			Text::make('Name', Unit::FIELD_NAME)->sortable()->readonly()->detailLink()->rules('required'),
			UnitAbbreviatedNameProperty::field(null, null),
			//            Text::make('Abbreviated Name', Unit::FIELD_ABBREVIATED_NAME)
			//                ->sortable()
			//                ->readonly()
			//                ->rules('required'),
			UnitCategoryIdProperty::field(null, null),
			//            Text::make('Category', Unit::FIELD_UNIT_CATEGORY_ID, function(){
			//                /** @var Unit $this */
			//                return $this->getUnitCategory()->name;
			//            })
			//                ->sortable()
			//                ->readonly()
			//                ->rules('required'),
			UnitIdProperty::field(null, null),
			HasMany::make('Variables', 'variables', VariableBaseAstralResource::class),
		];
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{ return true; }
	public function unit_category(): BelongsTo{
		return $this->belongsTo(UnitCategory::class, self::FIELD_UNIT_CATEGORY_ID,
			UnitCategory::FIELD_ID, self::FIELD_UNIT_CATEGORY_ID);
	}
	public function getTitleAttribute():string{
		return $this->getNameAttribute();
	}
	/**
	 * @param int|string $nameOrId
	 * @return \App\Models\Unit|null
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public static function findInMemory($nameOrId): ?BaseModel{
		if(!$nameOrId){le("no nameOrId");}
		$mem = parent::findInMemory($nameOrId);
		if($mem){return $mem;}
		$mem = QMUnit::find($nameOrId);
		return $mem->attachedOrNewLaravelModel();
	}
    /**
     * @param User|QMUserVariable|null $writer
     * @return bool
     */
    public function canWriteMe($writer = null): bool{
        if(!$writer){
            $writer = QMAuth::getQMUser();
        }
        return $writer && $writer->isAdmin();
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
    /**
     * @param null $writer
     * @return bool
     * @throws AccessTokenExpiredException
     */
    public function canCreateMe($writer = null): bool{
        return QMAuth::isAdmin();
    }
	public static function importUCUMUnits(): void{
		$ucum = JsonFile::getArray('data/unified_code_for_units_of_measure.json');
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
			$unit = Unit::whereAbbreviatedName($one['Code'])->first();
			if(!$unit){
				$unit = Unit::whereName($one["ConceptID"])->first();
			}
			if(!$unit){
				$unit = Unit::whereAbbreviatedName($one["Synonym"])->first();
			}
			if(!$unit){
				$unit = new Unit();
			}
			$map = [
				'ConceptID' => Unit::FIELD_NAME,
				'Code' => Unit::FIELD_ABBREVIATED_NAME,
				'Date_Created' => Unit::FIELD_CREATED_AT,
				'Date_Revised' => Unit::FIELD_UPDATED_AT,
			];
			foreach($one as $key => $value){
				if($value === null){continue;}
				if($key === 'Code'){$unit->setAttribute('code', $value);}
				if($key === 'ConceptID'){$unit->setAttribute('concept_id', $value);}
				if(isset($map[$key])){$key = $map[$key];}
				$key = Str::snake($key);
				$key = str_replace('__', '_', $key);
				if($key === 'id'){continue;}
				$existing = $unit->attributes[$key] ?? null;
				if($existing === null){
					$unit->setAttribute($key, $value);
				}
			}
			$cats = UnitCategory::all();
			foreach($cats as $cat){
				if(Str::contains($one['Kind_of_Quantity'], $cat->name)){
					$unit->setAttribute(Unit::FIELD_UNIT_CATEGORY_ID, $cat->id);
					break;
				}
			}
			if(!isset($unit->attributes[Unit::FIELD_UNIT_CATEGORY_ID])){
				$unit->setAttribute(Unit::FIELD_UNIT_CATEGORY_ID, MiscellaneousVariableCategory::ID);
			}
			if(!isset($unit->attributes[Unit::FIELD_FILLING_TYPE])){
				$unit->setAttribute(Unit::FIELD_FILLING_TYPE, BaseFillingTypeProperty::FILLING_TYPE_NONE);
			}
			if(!isset($unit->attributes[Unit::FIELD_ADVANCED])){
				$unit->setAttribute(Unit::FIELD_ADVANCED, true);
			}
			if(!isset($unit->attributes[Unit::FIELD_MANUAL_TRACKING])){
				$unit->setAttribute(Unit::FIELD_MANUAL_TRACKING, false);
			}
			if(!isset($unit->attributes[Unit::FIELD_SCALE])){
				$unit->setAttribute(Unit::FIELD_SCALE, UnitScaleProperty::RATIO);
			}
			$unit->save();

		}
		self::saveJson();
	}
}
