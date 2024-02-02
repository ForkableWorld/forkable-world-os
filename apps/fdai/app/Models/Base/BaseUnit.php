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
use App\Models\CommonTag;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Models\VariableCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseUnit
 * @property int $id
 * @property string $name
 * @property string $abbreviated_name
 * @property int $unit_category_id
 * @property float $minimum_value
 * @property float $maximum_value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property string $filling_type
 * @property int $number_of_outcome_population_studies
 * @property int $number_of_common_tags_where_tag_variable_unit
 * @property int $number_of_common_tags_where_tagged_variable_unit
 * @property int $number_of_outcome_case_studies
 * @property int $number_of_measurements
 * @property int $number_of_user_variables_where_default_unit
 * @property int $number_of_variable_categories_where_default_unit
 * @property int $number_of_variables_where_default_unit
 * @property bool $advanced
 * @property bool $manual_tracking
 * @property float $filling_value
 * @property string $scale
 * @property array $conversion_steps
 * @property float $maximum_daily_value
 * @property int $sort_order
 * @property Collection|GlobalVariableRelationship[] $global_variable_relationships_where_cause_unit
 * @property Collection|CommonTag[] $common_tags_where_tag_variable_unit
 * @property Collection|CommonTag[] $common_tags_where_tagged_variable_unit
 * @property Collection|UserVariableRelationship[] $correlations_where_cause_unit
 * @property Collection|Measurement[] $measurements_where_original_unit
 * @property Collection|Measurement[] $measurements
 * @property Collection|UserVariable[] $user_variables_where_default_unit
 * @property Collection|UserVariable[] $user_variables_where_last_unit
 * @property Collection|VariableCategory[] $variable_categories_where_default_unit
 * @property Collection|Variable[] $variables_where_default_unit
 * @package App\Models\Base
 * @property-read int|null $global_variable_relationships_where_cause_unit_count
 * @property-read int|null $common_tags_where_tag_variable_unit_count
 * @property-read int|null $common_tags_where_tagged_variable_unit_count
 * @property-read int|null $correlations_where_cause_unit_count
 * @property-read int|null $measurements_count
 * @property-read int|null $measurements_where_original_unit_count
 * @property-read int|null $user_variables_where_default_unit_count
 * @property-read int|null $user_variables_where_last_unit_count
 * @property-read int|null $variable_categories_where_default_unit_count
 * @property-read int|null $variables_where_default_unit_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUnit onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit whereAbbreviatedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit whereAdvanced($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit whereConversionSteps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit whereFillingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit whereFillingValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit whereManualTracking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit whereMaximumDailyValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit whereMaximumValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit whereMinimumValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit
 *     whereNumberOfCommonTagsWhereTagVariableUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit
 *     whereNumberOfCommonTagsWhereTaggedVariableUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit whereNumberOfMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit
 *     whereNumberOfOutcomeCaseStudies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit
 *     whereNumberOfOutcomePopulationStudies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit
 *     whereNumberOfUserVariablesWhereDefaultUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit
 *     whereNumberOfVariableCategoriesWhereDefaultUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit
 *     whereNumberOfVariablesWhereDefaultUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit whereScale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUnit withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUnit withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 * @method static \Illuminate\Database\Eloquent\Builder|BaseUnit whereUnitCategoryId($value)
 */
abstract class BaseUnit extends BaseModel {
	use SoftDeletes;
	public const FIELD_ABBREVIATED_NAME = 'abbreviated_name';
	public const FIELD_ADVANCED = 'advanced';
	public const FIELD_CONVERSION_STEPS = 'conversion_steps';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_FILLING_TYPE = 'filling_type';
	public const FIELD_FILLING_VALUE = 'filling_value';
	public const FIELD_ID = 'id';
	public const FIELD_MANUAL_TRACKING = 'manual_tracking';
	public const FIELD_MAXIMUM_DAILY_VALUE = 'maximum_daily_value';
	public const FIELD_MAXIMUM_VALUE = 'maximum_value';
	public const FIELD_MINIMUM_VALUE = 'minimum_value';
	public const FIELD_NAME = 'name';
	public const FIELD_NUMBER_OF_COMMON_TAGS_WHERE_TAG_VARIABLE_UNIT = 'number_of_common_tags_where_tag_variable_unit';
	public const FIELD_NUMBER_OF_COMMON_TAGS_WHERE_TAGGED_VARIABLE_UNIT = 'number_of_common_tags_where_tagged_variable_unit';
	public const FIELD_NUMBER_OF_MEASUREMENTS = 'number_of_measurements';
	public const FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES = 'number_of_outcome_case_studies';
	public const FIELD_NUMBER_OF_OUTCOME_POPULATION_STUDIES = 'number_of_outcome_population_studies';
	public const FIELD_NUMBER_OF_USER_VARIABLES_WHERE_DEFAULT_UNIT = 'number_of_user_variables_where_default_unit';
	public const FIELD_NUMBER_OF_VARIABLE_CATEGORIES_WHERE_DEFAULT_UNIT = 'number_of_variable_categories_where_default_unit';
	public const FIELD_NUMBER_OF_VARIABLES_WHERE_DEFAULT_UNIT = 'number_of_variables_where_default_unit';
	public const FIELD_SCALE = 'scale';
	public const FIELD_SLUG = 'slug';
	public const FIELD_SORT_ORDER = 'sort_order';
	public const FIELD_UNIT_CATEGORY_ID = 'unit_category_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const TABLE = 'units';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_ABBREVIATED_NAME => 'string',
		self::FIELD_ADVANCED => 'bool',
		self::FIELD_CONVERSION_STEPS => 'json',
		self::FIELD_FILLING_TYPE => 'string',
		self::FIELD_FILLING_VALUE => 'float',
		self::FIELD_ID => 'int',
		self::FIELD_MANUAL_TRACKING => 'bool',
		self::FIELD_MAXIMUM_DAILY_VALUE => 'float',
		self::FIELD_MAXIMUM_VALUE => 'float',
		self::FIELD_MINIMUM_VALUE => 'float',
		self::FIELD_NAME => 'string',
		self::FIELD_NUMBER_OF_COMMON_TAGS_WHERE_TAGGED_VARIABLE_UNIT => 'int',
		self::FIELD_NUMBER_OF_COMMON_TAGS_WHERE_TAG_VARIABLE_UNIT => 'int',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'int',
		self::FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES => 'int',
		self::FIELD_NUMBER_OF_OUTCOME_POPULATION_STUDIES => 'int',
		self::FIELD_NUMBER_OF_USER_VARIABLES_WHERE_DEFAULT_UNIT => 'int',
		self::FIELD_NUMBER_OF_VARIABLES_WHERE_DEFAULT_UNIT => 'int',
		self::FIELD_NUMBER_OF_VARIABLE_CATEGORIES_WHERE_DEFAULT_UNIT => 'int',
		self::FIELD_SCALE => 'string',
		self::FIELD_SORT_ORDER => 'int',
		self::FIELD_UNIT_CATEGORY_ID => 'int',
		];
	protected array $rules = [
		self::FIELD_ABBREVIATED_NAME => 'required|max:40|unique:units,abbreviated_name',
		self::FIELD_ADVANCED => 'required|boolean',
		self::FIELD_CONVERSION_STEPS => 'nullable|json',
		self::FIELD_FILLING_TYPE => 'required',
		self::FIELD_FILLING_VALUE => 'nullable|numeric',
		self::FIELD_MANUAL_TRACKING => 'required|boolean',
		self::FIELD_MAXIMUM_DAILY_VALUE => 'nullable|numeric',
		self::FIELD_MAXIMUM_VALUE => 'nullable|numeric',
		self::FIELD_MINIMUM_VALUE => 'nullable|numeric',
		self::FIELD_NAME => 'required|max:64|unique:units,name',
		self::FIELD_NUMBER_OF_COMMON_TAGS_WHERE_TAGGED_VARIABLE_UNIT => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_COMMON_TAGS_WHERE_TAG_VARIABLE_UNIT => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_OUTCOME_POPULATION_STUDIES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_USER_VARIABLES_WHERE_DEFAULT_UNIT => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_VARIABLES_WHERE_DEFAULT_UNIT => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_VARIABLE_CATEGORIES_WHERE_DEFAULT_UNIT => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_SCALE => 'required',
		self::FIELD_SORT_ORDER => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_UNIT_CATEGORY_ID => 'required|boolean',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_NAME => 'Unit name',
		self::FIELD_ABBREVIATED_NAME => 'Unit abbreviation',
		self::FIELD_UNIT_CATEGORY_ID => 'Unit category ID',
		self::FIELD_MINIMUM_VALUE => 'The minimum value for a single measurement. ',
		self::FIELD_MAXIMUM_VALUE => 'The maximum value for a single measurement',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_FILLING_TYPE => 'The filling type specifies how periods of missing data should be treated. ',
		self::FIELD_NUMBER_OF_OUTCOME_POPULATION_STUDIES => 'Number of Global Population Studies for this Cause Unit.
                [Formula:
                    update units
                        left join (
                            select count(id) as total, cause_unit_id
                            from global_variable_relationships
                            group by cause_unit_id
                        )
                        as grouped on units.id = grouped.cause_unit_id
                    set units.number_of_outcome_population_studies = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_COMMON_TAGS_WHERE_TAG_VARIABLE_UNIT => 'Number of Common Tags for this Tag Variable Unit.
                [Formula:
                    update units
                        left join (
                            select count(id) as total, tag_variable_unit_id
                            from common_tags
                            group by tag_variable_unit_id
                        )
                        as grouped on units.id = grouped.tag_variable_unit_id
                    set units.number_of_common_tags_where_tag_variable_unit = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_COMMON_TAGS_WHERE_TAGGED_VARIABLE_UNIT => 'Number of Common Tags for this Tagged Variable Unit.
                [Formula:
                    update units
                        left join (
                            select count(id) as total, tagged_variable_unit_id
                            from common_tags
                            group by tagged_variable_unit_id
                        )
                        as grouped on units.id = grouped.tagged_variable_unit_id
                    set units.number_of_common_tags_where_tagged_variable_unit = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_OUTCOME_CASE_STUDIES => 'Number of Individual Case Studies for this Cause Unit.
                [Formula:
                    update units
                        left join (
                            select count(id) as total, cause_unit_id
                            from user_variable_relationships
                            group by cause_unit_id
                        )
                        as grouped on units.id = grouped.cause_unit_id
                    set units.number_of_outcome_case_studies = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'Number of Measurements for this Unit.
                    [Formula: update units
                        left join (
                            select count(id) as total, unit_id
                            from measurements
                            group by unit_id
                        )
                        as grouped on units.id = grouped.unit_id
                    set units.number_of_measurements = count(grouped.total)]',
		self::FIELD_NUMBER_OF_USER_VARIABLES_WHERE_DEFAULT_UNIT => 'Number of User Variables for this Default Unit.
                    [Formula: update units
                        left join (
                            select count(id) as total, default_unit_id
                            from user_variables
                            group by default_unit_id
                        )
                        as grouped on units.id = grouped.default_unit_id
                    set units.number_of_user_variables_where_default_unit = count(grouped.total)]',
		self::FIELD_NUMBER_OF_VARIABLE_CATEGORIES_WHERE_DEFAULT_UNIT => 'Number of Variable Categories for this Default Unit.
                    [Formula: update units
                        left join (
                            select count(id) as total, default_unit_id
                            from variable_categories
                            group by default_unit_id
                        )
                        as grouped on units.id = grouped.default_unit_id
                    set units.number_of_variable_categories_where_default_unit = count(grouped.total)]',
		self::FIELD_NUMBER_OF_VARIABLES_WHERE_DEFAULT_UNIT => 'Number of Variables for this Default Unit.
                    [Formula: update units
                        left join (
                            select count(id) as total, default_unit_id
                            from variables
                            group by default_unit_id
                        )
                        as grouped on units.id = grouped.default_unit_id
                    set units.number_of_variables_where_default_unit = count(grouped.total)]',
		self::FIELD_ADVANCED => 'Advanced units are rarely used and should generally be hidden or at the bottom of selector lists',
		self::FIELD_MANUAL_TRACKING => 'Include manual tracking units in selector when manually recording a measurement. ',
		self::FIELD_FILLING_VALUE => 'The filling value is substituted used when data is missing if the filling type is set to value.',
		self::FIELD_SCALE => '
Ordinal is used to simply depict the order of variables and not the difference between each of the variables. Ordinal scales are generally used to depict non-mathematical ideas such as frequency, satisfaction, happiness, a degree of pain etc.

Ratio Scale not only produces the order of variables but also makes the difference between variables known along with information on the value of true zero.

Interval scale contains all the properties of ordinal scale, in addition to which, it offers a calculation of the difference between variables. The main characteristic of this scale is the equidistant difference between objects. Interval has no pre-decided starting point or a true zero value.

Nominal, also called the categorical variable scale, is defined as a scale used for labeling variables into distinct classifications and doesn’t involve a quantitative value or order.
',
		self::FIELD_CONVERSION_STEPS => 'An array of mathematical operations, each containing a operation and value field to apply to the value in the current unit to convert it to the default unit for the unit category. ',
		self::FIELD_MAXIMUM_DAILY_VALUE => 'The maximum aggregated measurement value over a single day.',
		self::FIELD_SORT_ORDER => '',
	];
	protected array $relationshipInfo = [
		'global_variable_relationships_where_cause_unit' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => GlobalVariableRelationship::class,
			'foreignKey' => GlobalVariableRelationship::FIELD_CAUSE_UNIT_ID,
			'localKey' => GlobalVariableRelationship::FIELD_ID,
			'methodName' => 'global_variable_relationships_where_cause_unit',
		],
		'common_tags_where_tag_variable_unit' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CommonTag::class,
			'foreignKey' => CommonTag::FIELD_TAG_VARIABLE_UNIT_ID,
			'localKey' => CommonTag::FIELD_ID,
			'methodName' => 'common_tags_where_tag_variable_unit',
		],
		'common_tags_where_tagged_variable_unit' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CommonTag::class,
			'foreignKey' => CommonTag::FIELD_TAGGED_VARIABLE_UNIT_ID,
			'localKey' => CommonTag::FIELD_ID,
			'methodName' => 'common_tags_where_tagged_variable_unit',
		],
		'correlations_where_cause_unit' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariableRelationship::class,
			'foreignKey' => UserVariableRelationship::FIELD_CAUSE_UNIT_ID,
			'localKey' => UserVariableRelationship::FIELD_ID,
			'methodName' => 'correlations_where_cause_unit',
		],
		'measurements_where_original_unit' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Measurement::class,
			'foreignKey' => Measurement::FIELD_ORIGINAL_UNIT_ID,
			'localKey' => Measurement::FIELD_ID,
			'methodName' => 'measurements_where_original_unit',
		],
		'measurements' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Measurement::class,
			'foreignKey' => Measurement::FIELD_UNIT_ID,
			'localKey' => Measurement::FIELD_ID,
			'methodName' => 'measurements',
		],
		'user_variables_where_default_unit' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariable::class,
			'foreignKey' => UserVariable::FIELD_DEFAULT_UNIT_ID,
			'localKey' => UserVariable::FIELD_ID,
			'methodName' => 'user_variables_where_default_unit',
		],
		'user_variables_where_last_unit' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariable::class,
			'foreignKey' => UserVariable::FIELD_LAST_UNIT_ID,
			'localKey' => UserVariable::FIELD_ID,
			'methodName' => 'user_variables_where_last_unit',
		],
		'variable_categories_where_default_unit' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => VariableCategory::class,
			'foreignKey' => VariableCategory::FIELD_DEFAULT_UNIT_ID,
			'localKey' => VariableCategory::FIELD_ID,
			'methodName' => 'variable_categories_where_default_unit',
		],
		'variables_where_default_unit' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Variable::class,
			'foreignKey' => Variable::FIELD_DEFAULT_UNIT_ID,
			'localKey' => Variable::FIELD_ID,
			'methodName' => 'variables_where_default_unit',
		],
	];
	public function global_variable_relationships_where_cause_unit(): HasMany{
		return $this->hasMany(GlobalVariableRelationship::class, GlobalVariableRelationship::FIELD_CAUSE_UNIT_ID, static::FIELD_ID);
	}
	public function common_tags_where_tag_variable_unit(): HasMany{
		return $this->hasMany(CommonTag::class, CommonTag::FIELD_TAG_VARIABLE_UNIT_ID, static::FIELD_ID);
	}
	public function common_tags_where_tagged_variable_unit(): HasMany{
		return $this->hasMany(CommonTag::class, CommonTag::FIELD_TAGGED_VARIABLE_UNIT_ID, static::FIELD_ID);
	}
	public function correlations_where_cause_unit(): HasMany{
		return $this->hasMany(UserVariableRelationship::class, UserVariableRelationship::FIELD_CAUSE_UNIT_ID, static::FIELD_ID);
	}
	public function measurements_where_original_unit(): HasMany{
		return $this->hasMany(Measurement::class, Measurement::FIELD_ORIGINAL_UNIT_ID, static::FIELD_ID);
	}
	public function measurements(): HasMany{
		return $this->hasMany(Measurement::class, Measurement::FIELD_UNIT_ID, static::FIELD_ID);
	}
	public function user_variables_where_default_unit(): HasMany{
		return $this->hasMany(UserVariable::class, UserVariable::FIELD_DEFAULT_UNIT_ID, static::FIELD_ID);
	}
	public function user_variables_where_last_unit(): HasMany{
		return $this->hasMany(UserVariable::class, UserVariable::FIELD_LAST_UNIT_ID, static::FIELD_ID);
	}
	public function variable_categories_where_default_unit(): HasMany{
		return $this->hasMany(VariableCategory::class, VariableCategory::FIELD_DEFAULT_UNIT_ID, static::FIELD_ID);
	}
	public function variables_where_default_unit(): HasMany{
		return $this->hasMany(Variable::class, Variable::FIELD_DEFAULT_UNIT_ID, static::FIELD_ID);
	}
}
