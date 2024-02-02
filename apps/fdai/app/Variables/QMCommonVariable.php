<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables;
use App\Charts\ChartGroup;
use App\Charts\VariableCharts\VariableChartChartGroup;
use App\CodeGenerators\Swagger\SwaggerDefinition;
use App\Computers\ThisComputer;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\VariableRelationships\QMVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\BadRequestException;
use App\Exceptions\CommonVariableNotFoundException;
use App\Exceptions\DuplicateFailedAnalysisException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InsufficientMemoryException;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NoUserVariableRelationshipsToAggregateException;
use App\Exceptions\QMException;
use App\Exceptions\StupidVariableNameException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\VariableCategoryNotFoundException;
use App\Files\FileHelper;
use App\Http\Parameters\SortParam;
use App\Logging\QMLog;
use App\Models\GlobalVariableRelationship;
use App\Models\BaseModel;
use App\Models\CommonTag;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\Study;
use App\Models\TrackingReminder;
use App\Models\UserVariable;
use App\Models\UserVariableClient;
use App\Models\Variable;
use App\Models\Vote;
use App\Models\WpPost;
use App\PhpUnitJobs\Cleanup\ModelGeneratorJob;
use App\Products\AmazonHelper;
use App\Products\ProductHelper;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseSynonymsProperty;
use App\Properties\Measurement\MeasurementStartTimeProperty;
use App\Properties\Unit\UnitIdProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\UserVariable\UserVariableStatusProperty;
use App\Properties\Variable\VariableBrandNameProperty;
use App\Properties\Variable\VariableClientIdProperty;
use App\Properties\Variable\VariableDataSourcesCountProperty;
use App\Properties\Variable\VariableDefaultUnitIdProperty;
use App\Properties\Variable\VariableFillingValueProperty;
use App\Properties\Variable\VariableIdProperty;
use App\Properties\Variable\VariableImageUrlProperty;
use App\Properties\Variable\VariableIsPublicProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Properties\Variable\VariableNumberCommonTaggedByProperty;
use App\Properties\Variable\VariableNumberOfGlobalVariableRelationshipsAsCauseProperty;
use App\Properties\Variable\VariableNumberOfGlobalVariableRelationshipsAsEffectProperty;
use App\Properties\Variable\VariableNumberOfCommonTagsProperty;
use App\Properties\Variable\VariableNumberOfMeasurementsProperty;
use App\Properties\Variable\VariableNumberOfTrackingRemindersProperty;
use App\Properties\Variable\VariableOutcomeProperty;
use App\Properties\Variable\VariablePriceProperty;
use App\Properties\Variable\VariableProductUrlProperty;
use App\Properties\Variable\VariableSynonymsProperty;
use App\Properties\Variable\VariableUpcOneFourProperty;
use App\Properties\Variable\VariableVariableCategoryIdProperty;
use App\Slim\Controller\Variable\PostVariableController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Slim\Model\Measurement\AnonymousMeasurement;
use App\Slim\Model\Measurement\DailyAnonymousMeasurement;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\QMUnit;
use App\Slim\View\Request\QMRequest;
use App\Slim\View\Request\Variable\GetCommonVariablesRequest;
use App\Slim\View\Request\Variable\GetUserVariableRequest;
use App\Slim\View\Request\Variable\SearchVariableRequest;
use App\Storage\DB\QMDB;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Traits\HardCodable;
use App\Traits\HasModel\HasVariable;
use App\Types\ObjectHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\HtmlHelper;
use App\Units\CountUnit;
use App\Units\PercentUnit;
use App\Units\YesNoUnit;
use App\Utils\APIHelper;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\EnvOverride;
use App\Utils\UrlHelper;
use App\VariableCategories\ElectronicsVariableCategory;
use App\VariableCategories\MiscellaneousVariableCategory;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnBusinessActivitiesCommonVariable;
use App\Variables\CommonVariables\VitalSignsCommonVariables\BloodPressureDiastolicBottomNumberCommonVariable;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use ReflectionClass;
use RuntimeException;
use stdClass;
/**
 * @mixin Variable
 */
class QMCommonVariable extends QMVariable {
	use HardCodable, HasVariable;
    private $alreadyGeneratedChildModelCode;
    private $numberOfGlobalVariableRelationships;
    private $qmUserVariables;
    private $userVariables;
    private $variableDataFromUserVariables;
    private static $getAmazonProductForNewVariables = true;
    protected $boring;
    protected $commonMaximumAllowedDailyValue;
    protected $commonMaximumAllowedValueInCommonUnit;
    protected $commonMinimumAllowedDailyValue;
    protected $commonMinimumAllowedNonZeroValue;
    protected $commonMinimumAllowedValueInCommonUnit;
    protected $controllable;
    protected $creatorUserId;
    protected $deletionReason;
    protected $isGoal;
    protected $lastSuccessfulUpdateTime;
    protected $metaData;
    protected $mostCommonOriginalUnitId;
    protected $slug;
    protected $upc12;
    public $additionalMetaData;
    public $brandName;
    public $commonAdditionalMetaData;
    public $defaultValue;
    public $earliestMeasurementTime;
    //public $isPublic;
    public $joinedVariables = [];
    public $joinWith;
    public $numberOfApplicationsWhereOutcomeVariable;
    public $numberOfApplicationsWherePredictorVariable;
    public $numberOfCommonChildren;
    public $numberOfCommonFoods;
    public $numberOfCommonIngredients;
    public $numberOfCommonJoinedVariables;
    public $numberOfCommonParents;
    public $numberOfCommonTagsWhereTaggedVariable;
    public $numberOfCommonTagsWhereTagVariable;
    public $numberOfMeasurements;
    public $numberOfOutcomeCaseStudies;
    public $numberOfOutcomePopulationStudies;
    public $numberOfPredictorCaseStudies;
    public $numberOfPredictorPopulationStudies;
    public $numberOfSoftDeletedMeasurements;
    public $numberOfStudiesWhereCauseVariable;
    public $numberOfStudiesWhereEffectVariable;
    public $numberOfTrackingReminderNotifications;
    public $numberOfUserChildren;
    public $numberOfUserFoods;
    public $numberOfUserIngredients;
    public $numberOfUserJoinedVariables;
    public $numberOfUserParents;
    public $numberOfUsersWherePrimaryOutcomeVariable;
    public $numberOfUserTagsWhereTaggedVariable;
    public $numberOfUserTagsWhereTagVariable;
    public $numberOfVariablesWhereBestCauseVariable;
    public $numberOfVariablesWhereBestEffectVariable;
    public $numberOfVotesWhereCauseVariable;
    public $numberOfVotesWhereEffectVariable;
    public $optimalValueMessage;
    public $public;
    public $tagConversionFactor;
    public $taggedVariables;
    public $trackingInstructions;
    public $wpPostId;
    public const ALGORITHM_MODIFIED_AT = "10-21-19";
    public const DEFAULT_SORT_FIELD = '-number_of_user_variables';
    public const ERROR_NO_VARIABLE_FOUND = 'Could not find this variable';
    public const FIELD_ADDITIONAL_META_DATA = 'additional_meta_data';
    public const FIELD_ANALYSIS_ENDED_AT = 'analysis_ended_at';
    public const FIELD_ANALYSIS_REQUESTED_AT = 'analysis_requested_at';
    public const FIELD_ANALYSIS_SETTINGS_MODIFIED_AT = 'analysis_settings_modified_at';
    public const FIELD_ANALYSIS_STARTED_AT = 'analysis_started_at';
    public const FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS = 'average_seconds_between_measurements';
    public const FIELD_BEST_AGGREGATE_CORRELATION_ID = 'best_global_variable_relationship_id';
    public const FIELD_BEST_CAUSE_VARIABLE_ID = 'best_cause_variable_id';
    public const FIELD_BEST_EFFECT_VARIABLE_ID = 'best_effect_variable_id';
    public const FIELD_BRAND_NAME = 'brand_name';
    public const FIELD_CAUSE_ONLY = 'cause_only';
    public const FIELD_CLIENT_ID = 'client_id';
    public const FIELD_COMBINATION_OPERATION = 'combination_operation';
    public const FIELD_COMMON_ALIAS = 'common_alias';
    public const FIELD_COMMON_MAXIMUM_ALLOWED_DAILY_VALUE = 'common_maximum_allowed_daily_value';
    public const FIELD_COMMON_MINIMUM_ALLOWED_DAILY_VALUE = 'common_minimum_allowed_daily_value';
    public const FIELD_COMMON_MINIMUM_ALLOWED_NON_ZERO_VALUE = 'common_minimum_allowed_non_zero_value';
    public const FIELD_CREATED_AT = 'created_at';
    public const FIELD_CREATOR_USER_ID = 'creator_user_id';
    public const FIELD_DATA_SOURCES_COUNT = 'data_sources_count';
    public const FIELD_DEFAULT_UNIT_ID = 'default_unit_id';
    public const FIELD_DEFAULT_VALUE = 'default_value';
    public const FIELD_DELETED_AT = 'deleted_at';
    public const FIELD_DELETION_REASON = 'deletion_reason';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DURATION_OF_ACTION = 'duration_of_action';
    public const FIELD_FILLING_VALUE = 'filling_value';
    public const FIELD_ID = 'id';
    public const FIELD_IMAGE_URL = 'image_url';
    public const FIELD_INFORMATIONAL_URL = 'informational_url';
    public const FIELD_INTERNAL_ERROR_MESSAGE = 'internal_error_message';
    public const FIELD_ION_ICON = 'ion_icon';
    public const FIELD_KURTOSIS = 'kurtosis';
    public const FIELD_MANUAL_TRACKING = 'manual_tracking';
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
    public const FIELD_NUMBER_OF_COMMON_TAGS = 'number_of_common_tags';
    public const FIELD_NUMBER_OF_MEASUREMENTS = 'number_of_measurements';
    public const FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN = 'number_of_raw_measurements_with_tags_joins_children';
    public const FIELD_NUMBER_OF_TRACKING_REMINDERS = 'number_of_tracking_reminders';
    public const FIELD_NUMBER_OF_UNIQUE_VALUES = 'number_of_unique_values';
    public const FIELD_NUMBER_OF_USER_VARIABLES = 'number_of_user_variables';
    public const FIELD_ONSET_DELAY = 'onset_delay';
    public const FIELD_OPTIMAL_VALUE_MESSAGE = 'optimal_value_message';
    public const FIELD_OUTCOME = 'outcome';
    public const FIELD_PARENT_ID = 'parent_id';
    public const FIELD_PRICE = 'price';
    public const FIELD_PRODUCT_URL = 'product_url';
    public const FIELD_IS_PUBLIC = 'is_public';
    public const FIELD_REASON_FOR_ANALYSIS = 'reason_for_analysis';
    public const FIELD_SECOND_MOST_COMMON_VALUE = 'second_most_common_value';
    public const FIELD_SKEWNESS = 'skewness';
    public const FIELD_STANDARD_DEVIATION = 'standard_deviation';
    public const FIELD_STATUS = 'status';
    public const FIELD_SYNONYMS = 'synonyms';
    public const FIELD_THIRD_MOST_COMMON_VALUE = 'third_most_common_value';
    public const FIELD_UPC = 'upc_14';
    public const FIELD_UPC_12 = 'upc_12';
    public const FIELD_UPC_14 = 'upc_14';
    public const FIELD_UPDATED_AT = 'updated_at';
    public const FIELD_USER_ERROR_MESSAGE = 'user_error_message';
    public const FIELD_VALENCE = 'valence';
    public const FIELD_VARIABLE_CATEGORY_ID = 'variable_category_id';
    public const FIELD_VARIABLE_ID = self::FIELD_ID;
    public const FIELD_VARIANCE = 'variance';
    public const FIELD_WIKIPEDIA_TITLE = 'wikipedia_title';
    public const FIELD_WIKIPEDIA_URL = 'wikipedia_url';
    public const ID = null;
    public const LARAVEL_CLASS = Variable::class;
    public const TABLE = 'variables';
    public const variableCategoryName = 'variableCategoryName';
    public const DB_FIELD_NAME_TO_PROPERTY_NAME_MAP = [
        'id' => 'variableId',
        self::FIELD_DEFAULT_UNIT_ID => 'commonUnitId',
        self::FIELD_UPC             => 'upc',
        Variable::FIELD_ADDITIONAL_META_DATA => 'commonAdditionalMetaData',
        Variable::FIELD_BEST_CAUSE_VARIABLE_ID => 'commonBestCauseVariableId',
        Variable::FIELD_BEST_EFFECT_VARIABLE_ID => 'commonBestEffectVariableId',
        Variable::FIELD_MAXIMUM_ALLOWED_VALUE => 'commonMaximumAllowedValueInCommonUnit',
        Variable::FIELD_MINIMUM_ALLOWED_VALUE => 'commonMinimumAllowedValueInCommonUnit',
        Variable::FIELD_OPTIMAL_VALUE_MESSAGE => 'commonOptimalValueMessage',
        Variable::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_CAUSE => 'numberOfCorrelationsAsCause',
        Variable::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_EFFECT => 'numberOfCorrelationsAsEffect',
    ];
    public const PURCHASE_DURATION_OF_ACTION = 30 * 86400;
    public static $purchasesAndSpendingConstants = [
        self::FIELD_ONSET_DELAY        => 0,
        self::FIELD_DURATION_OF_ACTION => self::PURCHASE_DURATION_OF_ACTION,
        self::FIELD_FILLING_VALUE      => 0,
    ];
    public static $sqlCalculatedFields = [
        self::FIELD_BEST_CAUSE_VARIABLE_ID => [
            'table'       => GlobalVariableRelationship::TABLE,
            'foreign_key' => GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID,
            'duration'    => 0,
            'sql'         => 'select cause_variable_id as calculatedValue
                from global_variable_relationships ac
                where cause_variable_id = $this->id
                    and ac.deleted_at is null
                order by ac.aggregate_qm_score desc
                limit 1',
        ],
        self::FIELD_BEST_EFFECT_VARIABLE_ID                             => [
            'table'       => GlobalVariableRelationship::TABLE,
            'foreign_key' => GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID,
            'duration'    => 0,
            'sql'         => 'select effect_variable_id as calculatedValue
                    from global_variable_relationships ac
                    where cause_variable_id = $this->id
                        and ac.deleted_at is null
                    order by ac.aggregate_qm_score desc
                    limit 1',
        ],
        self::FIELD_MAXIMUM_RECORDED_VALUE                              => [
            'table'       => Measurement::TABLE,
            'foreign_key' => Measurement::FIELD_VARIABLE_ID,
            'sql'         => 'max('.Measurement::FIELD_VALUE.')',
            'duration'    => 0
        ],
        self::FIELD_MEAN                                                => [
            'table'       => Measurement::TABLE,
            'foreign_key' => Measurement::FIELD_VARIABLE_ID,
            'sql'         => 'avg('.Measurement::FIELD_VALUE.')',
            'duration'    => 0
        ],
        self::FIELD_MINIMUM_RECORDED_VALUE                              => [
            'table'       => Measurement::TABLE,
            'foreign_key' => Measurement::FIELD_VARIABLE_ID,
            'sql'         => 'min('.Measurement::FIELD_VALUE.')',
            'duration'    => 0
        ],
        self::FIELD_NUMBER_COMMON_TAGGED_BY                             => [
            'table'       => QMCommonTag::TABLE,
            'foreign_key' => QMCommonTag::FIELD_TAG_VARIABLE_ID,
            'sql'         => 'count('.QMCommonTag::FIELD_TAGGED_VARIABLE_ID.')',
            'duration'    => 15
        ],
        self::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_CAUSE           => [
            'table'       => GlobalVariableRelationship::TABLE,
            'foreign_key' => GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID,
            'sql'         => 'count('.GlobalVariableRelationship::FIELD_ID.')',
            'duration'    => 16
        ],
        self::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_EFFECT          => [
            'table'       => GlobalVariableRelationship::TABLE,
            'foreign_key' => GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID,
            'sql'         => 'count('.GlobalVariableRelationship::FIELD_ID.')',
            'duration'    => 16
        ],
        self::FIELD_NUMBER_OF_COMMON_TAGS                               => [
            'table'       => QMCommonTag::TABLE,
            'foreign_key' => QMCommonTag::FIELD_TAGGED_VARIABLE_ID,
            'sql'         => 'count('.QMCommonTag::FIELD_TAG_VARIABLE_ID.')',
            'duration'    => 17  // Keep \App\Storage\DB\QMQB::MAX_WORKER_QUERY_DURATION above this number
        ],
        self::FIELD_NUMBER_OF_MEASUREMENTS                          => [
            'table'       => Measurement::TABLE,
            'foreign_key' => Measurement::FIELD_VARIABLE_ID,
            'sql'         => 'count('.Measurement::FIELD_ID.')',
            'duration'    => 0
        ],
        self::FIELD_NUMBER_OF_TRACKING_REMINDERS                        => [
            'table'       => TrackingReminder::TABLE,
            'foreign_key' => TrackingReminder::FIELD_VARIABLE_ID,
            'sql'         => 'count('.TrackingReminder::FIELD_ID.')',
            'duration'    => 22
        ],
        self::FIELD_NUMBER_OF_USER_VARIABLES                            => [
            'table'       => UserVariable::TABLE,
            'foreign_key' => UserVariable::FIELD_VARIABLE_ID,
            'sql'         => 'count('.UserVariable::FIELD_ID.')',
            'duration'    => 26
        ],
        self::FIELD_NEWEST_DATA_AT                                      => [
            'table'       => Measurement::TABLE,
            'foreign_key' => Measurement::FIELD_VARIABLE_ID,
            'sql'         => 'max('.Measurement::UPDATED_AT.')',
            'duration'    => 0
        ],
        self::FIELD_NUMBER_OF_SOFT_DELETED_MEASUREMENTS                 => [
            'table'       => Measurement::TABLE,
            'foreign_key' => Measurement::FIELD_VARIABLE_ID,
            'sql'         => 'update variables v
                inner join (
                    select measurements.variable_id, count(measurements.id) as number_of_soft_deleted_measurements
                    from measurements
                    where measurements.deleted_at is not null
                    group by measurements.variable_id
                    ) m on v.id = m.variable_id
                set v.number_of_soft_deleted_measurements = m.number_of_soft_deleted_measurements;
            ',
            'duration'    => 120
        ],
        self::FIELD_ADDITIONAL_META_DATA                                => 'php',
        self::FIELD_ANALYSIS_ENDED_AT                                   => 'php',
        self::FIELD_ANALYSIS_REQUESTED_AT                               => 'php',
        self::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT                       => 'php',
        self::FIELD_ANALYSIS_STARTED_AT                                 => 'php',
        self::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS                => 'php',
        self::FIELD_BEST_AGGREGATE_CORRELATION_ID                          => 'php',
        self::FIELD_BRAND_NAME                                          => 'php',
        self::FIELD_CAUSE_ONLY                                          => 'const',
        self::FIELD_CLIENT_ID                                           => 'const',
        self::FIELD_COMBINATION_OPERATION                               => 'const',
        self::FIELD_COMMON_ALIAS                                        => 'php',
        self::FIELD_COMMON_MAXIMUM_ALLOWED_DAILY_VALUE                  => 'const',
        self::FIELD_COMMON_MINIMUM_ALLOWED_DAILY_VALUE                  => 'const',
        self::FIELD_COMMON_MINIMUM_ALLOWED_NON_ZERO_VALUE               => 'const',
        self::FIELD_DATA_SOURCES_COUNT                                  => 'php',
        self::FIELD_DEFAULT_UNIT_ID                                     => 'const',
        self::FIELD_DEFAULT_VALUE                                       => 'const',
        self::FIELD_DESCRIPTION                                         => 'php',
        self::FIELD_DURATION_OF_ACTION                                  => 'const',
        self::FIELD_FILLING_VALUE                                       => 'const',
        self::FIELD_ID                                                  => 'const',
        self::FIELD_IMAGE_URL                                           => 'php',
        self::FIELD_INFORMATIONAL_URL                                   => 'php',
        self::FIELD_INTERNAL_ERROR_MESSAGE                              => 'php',
        self::FIELD_ION_ICON                                            => 'php',
        self::FIELD_KURTOSIS                                            => 'php',
        self::FIELD_MANUAL_TRACKING                                     => 'const',
        self::FIELD_MAXIMUM_ALLOWED_VALUE                               => 'const',
        self::FIELD_MEDIAN                                              => 'php',
        self::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS                 => 'php',
        self::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS        => 'const',
        self::FIELD_MINIMUM_ALLOWED_VALUE                               => 'const',
        self::FIELD_MOST_COMMON_CONNECTOR_ID                            => 'php',
        self::FIELD_MOST_COMMON_ORIGINAL_UNIT_ID                        => 'php',
        self::FIELD_MOST_COMMON_SOURCE_NAME                             => 'php',
        self::FIELD_MOST_COMMON_VALUE                                   => 'php',
        self::FIELD_NAME                                                => 'const',
        self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN => 'php',
        self::FIELD_NUMBER_OF_UNIQUE_VALUES                             => 'php',
        self::FIELD_ONSET_DELAY                                         => 'const',
        self::FIELD_OPTIMAL_VALUE_MESSAGE                               => 'php',
        self::FIELD_OUTCOME                                             => 'const',
        self::FIELD_PARENT_ID                                           => 'const',
        self::FIELD_PRICE                                               => 'const',
        self::FIELD_PRODUCT_URL                                         => 'php',
        self::FIELD_IS_PUBLIC                                              => 'const',
        self::FIELD_REASON_FOR_ANALYSIS                                 => 'php',
        self::FIELD_SECOND_MOST_COMMON_VALUE                            => 'php',
        self::FIELD_SKEWNESS                                            => 'php',
        self::FIELD_STANDARD_DEVIATION                                  => 'php',
        self::FIELD_STATUS                                              => 'php',
        self::FIELD_SYNONYMS                                            => 'const',
        self::FIELD_THIRD_MOST_COMMON_VALUE                             => 'php',
        self::FIELD_UPC_12                                              => 'php',
        self::FIELD_UPC_14                                              => 'php',
        self::FIELD_USER_ERROR_MESSAGE                                  => 'php',
        self::FIELD_CREATOR_USER_ID                                             => 'const',
        self::FIELD_VALENCE                                             => 'php',
        self::FIELD_VARIABLE_CATEGORY_ID                                => 'const',
        self::FIELD_VARIANCE                                            => 'php',
        self::FIELD_WIKIPEDIA_TITLE                                     => 'php',
        self::FIELD_WIKIPEDIA_URL                                       => 'php',
    ];
    public const MYSQL_COLUMN_TYPES = [
        self::FIELD_NUMBER_OF_SOFT_DELETED_MEASUREMENTS => QMDB::TYPE_INT
    ];
    /**
     * @var array|Collection|mixed|null
     */
    private $userVariableIds;
    /**
     * CommonVariable constructor.
     * @param object|null $row
     * @param string|null $commonVariableName
     * @param array $requestParams
     */
    public function __construct($row = null, string $commonVariableName = null, array $requestParams = []){
        if($this->id && !$this->variableId){$this->variableId = $this->id;}
        if(!$row){
            if($name = $commonVariableName ?? $this->name){$this->setName($name);}
            return;
        }
        parent::__construct($row);
        QMVariableCategory::addVariableCategoryNamesToObject($this);
        $this->getFillingValueAttribute();
        if(isset($requestParams['includeTags']) && $requestParams['includeTags']){
            $this->getAllCommonTagVariableTypes();
        }
        QMUnit::addUnitProperties($this);
        $this->getCommonUnit();
        $this->getNumberOfCorrelations();
        $this->checkCommonValues();
        $this->addToMemory();
        $this->getOrSetVariableDisplayName();
        TimeHelper::convertAllDateTimeValuesToRFC3339($this);
        $this->getCommonAdditionalMetaData();
        $l = $this->l();
        $this->setMinimumAllowedValue($l->minimum_allowed_value);
        $this->setMinimumAllowedValue($l->maximum_allowed_value);
        if(!$this->imageUrl){$this->imageUrl = $this->getQMVariableCategory()->getImageUrl();}
        $this->variableId = $this->id;
        $this->getNumberOfGlobalVariableRelationships();
        $this->setNumberOfMeasurements($l->getNumberOfMeasurementsAttribute());
    }
    /**
     * @param int|null $number
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function setNumberOfMeasurements(?int $number): void{
        parent::setNumberOfMeasurements($number);
    }
    private function checkCommonValues(){
        if(!isset($this->secondMostCommonValue) && isset($this->thirdMostCommonValue)){
            $this->logError("secondMostCommonValue is null but thirdMostCommonValue is $this->thirdMostCommonValue");
            $this->secondMostCommonValue = $this->thirdMostCommonValue;
            $this->thirdMostCommonValue = null;
        }
    }
    /**
     * @param bool $instantiate
     * @return QMQB
     */
    public static function qb(bool $instantiate = false): QMQB {
        $qb = GetCommonVariablesRequest::getBaseQB();
        $qb->class = self::class;
        return $qb;
    }
    /**
     * @param array|GetCommonVariablesRequest $paramsOrReq
     * @param bool $useReadOnlyConnection
     * @return QMCommonVariable[]
     */
    public static function getCommonVariables($paramsOrReq, bool $useReadOnlyConnection = true): array{
        $req = self::convertRequestParamsToVariableRequestIfNecessary($paramsOrReq);
        $req->setUseWritableConnection(!$useReadOnlyConnection);
        $variables = $req->getCommonVariables();
        if(!count($variables) && $req->getSearchPhrase()){
            $variables = $req->getVariablesWithMatchingSynonyms();
        }
        if($req->getSearchPhrase()){
            $variables = QMVariable::putExactMatchFirst($variables, $req->getSearchPhrase());
        }
	    SwaggerDefinition::addOrUpdateSwaggerDefinition($variables, "CommonVariable");
        ObjectHelper::addLegacyPropertiesToObjectsInArray($variables);
        $variables = QMVariable::filterByUnitCategoryName($variables, $req);
        if($req->getRemoveAdvancedProperties()){
            self::removeAdvancedProperties($variables);
        }
        return $variables;
    }
    /**
     * @return array
     */
    public static function getLegacyRequestParameters(): array{
        // Legacy => Current
        return [
            'defaultUnitAbbreviatedName'             => 'abbreviatedUnitName',
            'defaultUnit'                            => 'unitId',
            'fallbackToGlobalVariableRelationships'        => 'fallbackToAggregatedCorrelations',
            'taggedVariableId'                       => 'userTaggedVariableId',
            'tagVariableId'                          => 'userTagVariableId',
            'defaultUnitId'                          => 'unitId',
            'numberOfAggregatedCorrelationsAsCause'  => 'numberOfCorrelationsAsCause',
            'numberOfAggregatedCorrelationsAsEffect' => 'numberOfCorrelationsAsEffect',
            'numberOfGlobalVariableRelationshipsAsCause'   => 'numberOfCorrelationsAsCause',
            'numberOfGlobalVariableRelationshipsAsEffect'  => 'numberOfCorrelationsAsEffect',
            'numberOfUserVariableRelationshipsAsCause'        => 'numberOfCorrelationsAsCause',
            'numberOfUserVariableRelationshipsAsEffect'       => 'numberOfCorrelationsAsEffect',
            'categoryName'                           => 'variableCategoryName',
        ];
    }
    public function save(): bool{
        if($this->bestGlobalVariableRelationship && is_string($this->bestGlobalVariableRelationship)){
            // Make sure it's not string so we don't double json_encode
            $this->bestGlobalVariableRelationship = json_decode($this->bestGlobalVariableRelationship);
        }
        $path = $this->getHardCodedFilePath();
        return parent::save();
    }

    /**
     * @param array $providedParams
     * @param string $originalVariableName
     * @return array
     * @throws UnauthorizedException
     */
    public static function formatNewVariableData(array $providedParams, string $originalVariableName): array
    {
        $variableCategory =
            VariableVariableCategoryIdProperty::getVariableCategoryFromNewVariableParams($providedParams,
                $originalVariableName);
        $unit = VariableDefaultUnitIdProperty::getDefaultUnitFromNewVariableParams($originalVariableName,
            $providedParams,
            $variableCategory);
        $data = [
            self::FIELD_PARENT_ID => $providedParams['parentVariableId'] ?? null,
            self::FIELD_VARIABLE_CATEGORY_ID => $variableCategory->getId(),
            self::FIELD_DEFAULT_UNIT_ID => $unit->id,
            self::FIELD_COMBINATION_OPERATION =>
                BaseCombinationOperationProperty::getCombinationOperationFromNewVariableParams($providedParams,
                    $unit),
            self::FIELD_CREATED_AT => date('Y-m-d H:i:s'),
            self::FIELD_UPDATED_AT => date('Y-m-d H:i:s'),
        ];
        $fields = static::getColumns();
        $data = VariableClientIdProperty::setClientIdInNewVariableArray($providedParams, $data);
        if (mb_strlen($originalVariableName) > 125) {
            $data['description'] = $originalVariableName;
        }
        $name = $data[Variable::FIELD_NAME] = VariableNameProperty::sanitizeSlow($originalVariableName, $unit);
        // This makes 460 default for Nature Made Super B Complex Tablets, VarietySize Pack of 460 Count
        //$newVariable[self::FIELD_DEFAULT_VALUE] = self::getDefaultValueFromNewVariableParams($providedParams, $unit, $originalVariableName);
        $data[self::FIELD_PRODUCT_URL] =
            VariableProductUrlProperty::getProductUrlFromNewVariableParams($providedParams);
        $data[self::FIELD_IMAGE_URL] = VariableImageUrlProperty::getImageUrlForNewVariableArray($providedParams);
        $data = VariablePriceProperty::setPriceInNewVariableArray($providedParams, $data);
        $data = VariableUpcOneFourProperty::setUpcInNewVariableArray($providedParams, $data);
        $data = VariableIsPublicProperty::setPublicNewVariableField(
            $providedParams, $data, $unit, $variableCategory, $originalVariableName);
        $data = VariableBrandNameProperty::setBrandInNewVariableArray($providedParams, $data);
        $data[self::FIELD_CREATOR_USER_ID] = QMArr::getValueForSnakeOrCamelCaseKey($providedParams,
            UserVariable::FIELD_USER_ID) ?: QMAuth::id(false) ?: 1;
        $data[self::FIELD_SYNONYMS] = VariableSynonymsProperty::setNewVariableSynonyms($providedParams, $data,
            $originalVariableName, $unit);
        $data = VariableOutcomeProperty::setOutcomeInNewVariableArray($providedParams, $data, $variableCategory);
        $data = VariableFillingValueProperty::setFillingValueInNewVariableArray($data);
        $providedParams = QMArr::snakize($providedParams);
        foreach ($providedParams as $key => $value) {
            if (in_array($key, $fields)) {
                if (!isset($data[$key])) {
                    $data[$key] = $value;
                }
            } else {
                QMLog::debug("$key provided in new variable params is not a common variable field!");
            }
        }
        $data = VariableNameProperty::validateNewSpendingVariable($data);
        $data[Variable::FIELD_SLUG] = QMStr::slugify($name);
        return $data;
    }

    /**
     * @param int $id
     * @param string $reason
     * @return string
     */
    public static function getDeletionUrl(int $id, string $reason): string {
        return UrlHelper::getApiUrlForPath('v1/variables/delete',
            ['variableId' => $id, 'hardDelete' => true, 'reason' => $reason]);
    }
    /**
     * @param string $reason
     * @return string
     */
    public function getHardDeletionUrl(string $reason = null): string {
        return self::getDeletionUrl($this->getId(), $reason);
    }
    private static function updateSpendingAndPurchasesConstantsInDbFromHardCodedData(): void{
        QMLog::infoWithoutContext("Updating spending and purchase variable constants...");
        self::writable()
            ->whereRaw(self::FIELD_NAME . " " . \App\Storage\DB\ReadonlyDB::like() . " '" .
                VariableNameProperty::SPENDING_ON_VARIABLE_DISPLAY_NAME_PREFIX. "%'")
            ->update(self::$purchasesAndSpendingConstants);
        self::writable()
            ->whereRaw(self::FIELD_NAME . " " . \App\Storage\DB\ReadonlyDB::like() . " '" .
                VariableNameProperty::PURCHASES_OF_VARIABLE_DISPLAY_NAME_PREFIX. "%'")
            ->update(self::$purchasesAndSpendingConstants);
    }
    /**
     * @param string $variableName
     * @param array $newVariableData
     * @return QMCommonVariable
     */
    public static function updateOrCreate(string $variableName, array $newVariableData = []){
        $existingVariable = self::findByNameOrId($variableName);
        if($existingVariable){
            $existingVariable->updateDbRow($newVariableData, __METHOD__);
            return $existingVariable;
        }
        return self::findOrCreateByName($variableName, $newVariableData);
    }
    /**
     * @param string|int $nameIdOrSynonym
     * @param array $newVariableData
     * @return QMCommonVariable
     */
    public static function findInMemoryByNameIdOrSynonym($nameIdOrSynonym, array $newVariableData = []): ?QMCommonVariable{
        $v = self::findInMemoryByNameOrId($nameIdOrSynonym, $newVariableData);
        if($v){
            $v->validateId();
            return $v;
        }
        return self::findBySynonymInMemory($nameIdOrSynonym, $newVariableData);
    }
    /**
     * @param string|int $nameOrId
     * @param array $newVariableData
     * @return QMCommonVariable
     */
    public static function findInMemoryByNameOrId($nameOrId, array $newVariableData = []): ?QMCommonVariable{
        $globals = self::getAllFromMemoryIndexedByUuidAndId();
        if(is_int($nameOrId)){
            foreach($globals as $commonVariable){
                if($commonVariable->getVariableIdAttribute() === $nameOrId){
                    return self::createNewVariableWithUnitInNameIfNecessary($commonVariable, $newVariableData);
                }
            }
            return null;
        }
        $nameOrId = strtolower($nameOrId);
        if(isset($globals[$nameOrId])){
            return self::createNewVariableWithUnitInNameIfNecessary($globals[$nameOrId], $newVariableData);
        }
        return null;
    }
    /**
     * @param string $synonym
     * @param array $newVariableData
     * @return null|QMCommonVariable
     */
    private static function findBySynonymInMemory(string $synonym, array $newVariableData = []): ?QMCommonVariable {
        if($v = Variable::findBySynonymInMemory($synonym)){
            $dbm = $v->getDBModel();
            $dbm = self::createNewVariableWithUnitInNameIfNecessary($dbm, $newVariableData);
            if($dbm && !$dbm->variableId){le($dbm);}
            return $dbm;
        }
        return null;
    }
    /**
     * @param $rows
     * @return QMCommonVariable[]
     */
    public static function convertRowsToVariables($rows): array{
        $variables = [];
        $concise = QMRequest::urlContains('/variables') && QMRequest::getParam('concise');
        foreach($rows as $row){
            $variable = $concise ? new VariableSearchResult($row) : new QMCommonVariable($row, $row->name);
            $variable->variableId = $variable->id;
            $variables[] = $variable;
        }
        return $variables;
    }
    /**
     * @param bool $getAmazonProductForNewVariables
     */
    public static function setGetAmazonProductForNewVariables(bool $getAmazonProductForNewVariables){
        self::$getAmazonProductForNewVariables = $getAmazonProductForNewVariables;
    }
    /**
     * @param int|null $num
     * @return int|null
     */
    public function setNumberCommonTaggedBy(?int $num): ?int {
        parent::setNumberCommonTaggedBy($num);
        $this->setAttribute(Variable::FIELD_NUMBER_COMMON_TAGGED_BY, $num);
        return $num;
    }
    /**
     * @param int|null $num
     * @return int|null
     */
    public function setNumberOfCommonTags(?int $num): ?int {
        parent::setNumberOfCommonTags($num);
        $this->setAttribute(Variable::FIELD_NUMBER_OF_COMMON_TAGS, $num);
        return $num;
    }
    /**
     * @return int
     */
    public function getNumberCommonTaggedBy(): ?int {
        $rows = $this->getCommonTaggedRows();
        $num = count($rows);
        $this->setNumberCommonTaggedBy($num);
        return $num;
    }
    /**
     * @return int
     */
    public function getNumberOfCommonTags(): ?int{
        $rows = $this->getCommonTagRows();
        $num = count($rows);
        $this->setNumberOfCommonTags($num);
        return $num;
    }
    /**
     * @return array
     */
    public static function getRelatedDbFields(): array{
        $array = [];
        $array[] = [
            'table' => 'measurements',
            'field' => 'variable_id'
        ];
        $array[] = [
            'table' => 'user_variable_relationships',
            'field' => 'cause_variable_id'
        ];
        $array[] = [
            'table' => 'user_variable_relationships',
            'field' => 'effect_variable_id'
        ];
        $array[] = [
            'table' => 'global_variable_relationships',
            'field' => 'cause_variable_id'
        ];
        $array[] = [
            'table' => 'global_variable_relationships',
            'field' => 'effect_variable_id'
        ];
        $array[] = [
            'table' => UserVariableClient::TABLE,
            'field' => UserVariableClient::FIELD_VARIABLE_ID,
        ];
        $array[] = [
            'table' => 'variable_user_sources',
            'field' => 'variable_id'
        ];
        $array[] = [
            'table' => 'tracking_reminders',
            'field' => 'variable_id'
        ];
        $array[] = [
            'table' => 'user_tags',
            'field' => 'tagged_variable_id'
        ];
        $array[] = [
            'table' => 'user_tags',
            'field' => 'tag_variable_id'
        ];
        $array[] = [
            'table' => 'common_tags',
            'field' => 'tagged_variable_id'
        ];
        $array[] = [
            'table' => 'common_tags',
            'field' => 'tag_variable_id'
        ];
        $array[] = [
            'table' => Study::TABLE,
            'field' => Study::FIELD_CAUSE_VARIABLE_ID
        ];
        $array[] = [
            'table' => Study::TABLE,
            'field' => Study::FIELD_EFFECT_VARIABLE_ID
        ];
        $array[] = [
            'table' => Vote::TABLE,
            'field' => Vote::FIELD_CAUSE_VARIABLE_ID
        ];
        $array[] = [
            'table' => Vote::TABLE,
            'field' => Vote::FIELD_EFFECT_VARIABLE_ID
        ];
        $array[] = [
            'table' => 'user_variables', // Must be last to avoid foreign key issues when deleting
            'field' => 'variable_id'
        ];
        return $array;
    }
    /**
     * @param array $variables
     * @return array
     * @throws UnauthorizedException
     * @throws VariableCategoryNotFoundException
     */
    public static function createMultipleVariables(array $variables): array{
        if(!$variables){
            throw new QMException(QMException::CODE_BAD_REQUEST, PostVariableController::ERROR_NO_VARIABLES_GIVEN);
        }
        if(!isset($variables[0])){$variables = [$variables];}
        $numVariables = count($variables);
        $existingVariables = [];
        foreach($variables as $variable){
            $name = $variable['name'];
            if(!$name){throw new BadRequestException("Please provide variable name");}
            $dbVariable = VariableIdProperty::fromName($name);
            if($dbVariable){
                $existingVariables[] = $name;
                continue;
            }
            self::add($name, $variable);
        }
        $existingVariablesCount = count($existingVariables);
        return [
            $numVariables,
            $existingVariables,
            $existingVariablesCount
        ];
    }
    /**
     * @param int $variableId
     * @param bool $hardDelete
     * @param string $reason
     */
    public static function deleteRelatedRecords(int $variableId, bool $hardDelete, string $reason): void{
        $variableTableFields = self::getRelatedDbFields();
        $writableConnection = Writable::db();
        foreach($variableTableFields as $key => $array){
            $table = $array['table'];
            $field = $array['field'];
            $qb = $writableConnection->table($table)->where($field, $variableId);
            if(!$hardDelete){$qb->whereNull(QMDB::FIELD_DELETED_AT);}
            $count = $qb->count();
            if(!$count){
                if($hardDelete){
                    QMLog::info("No records (including soft-deleted ones) in $table for variable $variableId");
                } else {
                    QMLog::info("No non-soft-deleted records in $table");
                }
                continue;
            }
            if($hardDelete){
                QMLog::error("HARD deleting $count $table records (including soft-deleted ones) because $reason");
                $qb->hardDelete($reason, true);
            }else{
                QMLog::error("Soft deleting $count $table records (not including already soft-deleted ones) because $reason");
                $qb->update([self::FIELD_DELETED_AT => date('Y-m-d H:i:s')]);
            }
        }
    }
    /**
     * @param int $id
     */
    public static function getCommonVariableRowAndLogDeletionUrl(int $id){
        $variableRow = Variable::findByNameOrId($id);
        if($variableRow){
            $reason = "Variable $variableRow->name could not be gotten from getByVariableId!  deleted at is: $variableRow->deleted_at. ";
        } else {
            $reason = "id $id not found in variables table! Not even a deleted row exists! ";
        }
        $url = self::getDeletionUrl($id, $reason);
        throw new CommonVariableNotFoundException($reason. " Hard delete related records at $url");
    }
	/**
	 * @param string $variableName
	 * @return QMCommonVariable
	 */
    public static function findByName(string $variableName): ?QMCommonVariable {
        if($v = Variable::findByNameOrId($variableName)){
            return $v->getDBModel();
        }
        return null;
    }
    /**
     * @throws VariableCategoryNotFoundException
     */
    private static function updateDbFromCategoryConstants(): void{
        QMLog::infoWithoutContext("Updating variables with category constants...");
        $categories = QMVariableCategory::getVariableCategoriesIndexedByName();
        foreach($categories as $cat){
            $cat->updateVariableAttributesIfNotNull(Variable::FIELD_OUTCOME);
            $cat->updateVariableAttributesIfNotNull(Variable::FIELD_MANUAL_TRACKING);
        }
    }
    /**
     * @param QMDB $db
     */
    public static function updateDbFromUnitConstants(QMDB $db = null): void{
        if(!$db){$db = Writable::db();}
        QMLog::infoWithoutContext("Removing max for Yes/No unit variables");
        $db->table(self::TABLE)
            ->where(self::FIELD_DEFAULT_UNIT_ID, YesNoUnit::ID)
            ->update([
                self::FIELD_COMMON_MAXIMUM_ALLOWED_DAILY_VALUE => null,
                self::FIELD_MAXIMUM_ALLOWED_VALUE => null,
                self::FIELD_MINIMUM_ALLOWED_VALUE => 0,
            ]);
        QMLog::infoWithoutContext("Updating common variables from unit constants...");
        $countUnits = QMUnit::getCountCategoryUnits();
        foreach($countUnits as $u){
            $qb = Variable::whereDefaultUnitId($u->getId())
                ->where(Variable::FIELD_COMBINATION_OPERATION, BaseCombinationOperationProperty::COMBINATION_MEAN);
            Variable::updateAttributeWhereNecessary($qb, Variable::FIELD_COMBINATION_OPERATION,
                BaseCombinationOperationProperty::COMBINATION_SUM);
        }
    }
    /**
     * @return array
     */
    private static function updateDbFromVariableConstants(): array{
        QMLog::info("Updating variables with individual variable constants...");
        $hardCodedVariables = QMCommonVariable::getHardCodedVariables();
        $allChanges = [];
        foreach ($hardCodedVariables as $hardCoded) {
            if($hardCoded->id !== BloodPressureDiastolicBottomNumberCommonVariable::ID){continue;}
            if($changes = $hardCoded->updateDBFromConstants()){
                $allChanges[$hardCoded->getNameAttribute()] = $changes;
            }
        }
        QMLog::info(count($allChanges)." variables changed: ".\App\Logging\QMLog::print_r($allChanges, true));
        return $allChanges;
    }
    /**
     * @return int
     */
    public function getCommonUnitId(): int{
        if(!$this->commonUnitId){
            $this->commonUnitId = $this->unitId;
        }
        return $this->commonUnitId;
    }
    /**
     * @throws RuntimeException
     * @param string $combinationOperation
     * @return self
     */
    public function setCombinationOperation(string $combinationOperation): QMCommonVariable{
        $combinationOperation = strtoupper($combinationOperation);
        if(array_key_exists($combinationOperation, BaseCombinationOperationProperty::$combinationOperationsMap)){
            $this->combinationOperation = BaseCombinationOperationProperty::$combinationOperationsMap[$combinationOperation];
        }else{
            QMLog::error('Invalid combination operation!  Falling back to default MEAN', ['provided combinationOperation' => $combinationOperation]);
            $this->combinationOperation = BaseCombinationOperationProperty::COMBINATION_MEAN;
            //throw new RuntimeException('Invalid variable combination operation: ' . $combinationOperation);
        }
        return $this;
    }
    /**
     * @param array $joinedVariables
     * @return self
     */
    public function setJoinedVariables(array $joinedVariables): QMCommonVariable{
        if(!$joinedVariables){
            $this->joinedVariables = [];
        }else{
            $this->joinedVariables = $joinedVariables;
        }
        return $this;
    }
    /**
     * @param $providedParams
     * @param $variableCategory
     * @param $defaultUnitId
     * @param $variableName
     * @return bool|QMCommonVariable
     */
    private static function createNewVariableFromAmazonProduct(array $providedParams,
                                                               QMVariableCategory $variableCategory, int $defaultUnitId,
                                                               string $variableName){
        if(!self::$getAmazonProductForNewVariables){
            return false;
        }
        if(AppMode::isTestingOrStaging()){
            self::setGetAmazonProductForNewVariables(false);
        } // Only once to avoid rate limits
        if(!VariableProductUrlProperty::getProductUrlFromNewVariableParams($providedParams)){
            $amazonCategory = AmazonHelper::getMostSimilarAmazonCategory($variableCategory->name, false);
            if($amazonCategory){
                $providedParams[self::FIELD_DEFAULT_UNIT_ID] = $defaultUnitId;
                try {
                    $amazonProduct = ProductHelper::getByKeyword($variableName,
                        $variableCategory->name, $providedParams);
                    if($amazonProduct){
                        $amazonVariable = $amazonProduct->getQMCommonVariableWithActualProductName();
                        if($amazonVariable->name !== $variableName){  // Otherwise we create reminders with the wrong variable name
                            $amazonVariable->setVariableName($variableName);
                            $amazonVariableParams = json_decode(json_encode($amazonVariable), true);
                            $providedParamsWithFallbackToAmazonParams = array_replace($amazonVariableParams, $providedParams);
                            return self::add($variableName, $providedParamsWithFallbackToAmazonParams);
                        }
                    }
                } catch (Exception $e) {
                    ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
                }
            }
        }
        return false;
    }
    /**
     * Creates a new variable. Method fails with SQL exception if variable with the same name already exists.
     * @param string $originalVariableName Original variable name
     * @param array $providedParams product_url, image_url and price, added for Slice
     * @return QMCommonVariable Newly created variable id
     * @throws UnauthorizedException
     * @throws VariableCategoryNotFoundException
     */
    public static function add(string $originalVariableName, array $providedParams) {
        $variableCategory =
            VariableVariableCategoryIdProperty::getVariableCategoryFromNewVariableParams($providedParams,
                $originalVariableName);
        $unit = VariableDefaultUnitIdProperty::getDefaultUnitFromNewVariableParams($originalVariableName,
                $providedParams,
                $variableCategory);
	    if(Env::getFormatted('FETCH_AMAZON_PRODUCTS')){
		    if(!AppMode::isApiRequest() && $variableCategory->getAmazonProductCategory()){
			    $variableFromAmazon = self::createNewVariableFromAmazonProduct($providedParams,
				    $variableCategory, $unit->id, $originalVariableName);
			    if($variableFromAmazon){return $variableFromAmazon;}
		    }
	    }
	    $providedParams['name'] = $originalVariableName;
	    $providedParams['variable_category_id'] = $variableCategory->id;
	    $providedParams['default_unit_id'] = $unit->id;
	    $v = Variable::create($providedParams);
        return $v->getDBModel();
    }
    /**
     * Returns an array of all public variables
     * @return QMCommonVariable[]
     */
    public static function getAllPublic(): array{
        $params['limit'] = 200;
        $params['sort'] = '-numberOfUserVariables';
        $variables = self::getCommonVariables($params);
        return $variables;
    }
    /**
     * @param string|int $nameOrId
     * @param array $newVariableData
     * @return QMCommonVariable
     */
    public static function findByNameOrId($nameOrId, array $newVariableData = []): ?QMCommonVariable {
        if(is_numeric($nameOrId)){
            $v = self::find($nameOrId);
		if($v && !$v->variableId){le('!$v->variableId, $v');}
        }else{
            $v = self::findByName($nameOrId);
            if (!$v) {
                $v = VariableNameProperty::getVariableByFormattedNameIfDifferentFromProvided($nameOrId,
                    $newVariableData);
            }
        }
        if ($v) {
            $v->validateId();
			if($newVariableData){
				$v = self::createNewVariableWithUnitInNameIfNecessary($v, $newVariableData);
			}
	        if(!$v->variableId){le($v);}
            return $v;
        }
        QMLog::debug(self::ERROR_NO_VARIABLE_FOUND, ['name' => $nameOrId]);
        return null;
    }
    /**
     * @param string|int $variableNameOrId
     * @return QMCommonVariable
     */
    public static function findByNameIdOrSynonym($variableNameOrId): ?QMCommonVariable{
        $v = self::findByNameOrId($variableNameOrId);
        if(!$v && is_string($variableNameOrId)){$v = self::findBySynonym($variableNameOrId);}
        if(!$v){QMLog::debug(self::ERROR_NO_VARIABLE_FOUND, ['name' => $variableNameOrId]);}
        return $v;
    }
    /**
     * @param string $synonym
     * @param array $newVariableData
     * @return null|QMCommonVariable
     */
    private static function findBySynonym(string $synonym, array $newVariableData = []): ?QMCommonVariable {
        $variable = Variable::findBySynonym($synonym);
        if($variable){
            $dbm = $variable->getDBModel();
            $dbm = self::createNewVariableWithUnitInNameIfNecessary($dbm, $newVariableData);
            return $dbm;
        }
        return null;
    }
    public function findLaravelModel(): ?BaseModel {
        $l = parent::findLaravelModel();
        return $l;
    }
    /**
     * @param QMCommonVariable[]|QMUserVariable[] $variables
     */
    public static function removeAdvancedProperties(array $variables){
        $advancedProperties = [
            'availableUnits',
            'category',
            'causeOnly',
            'commonAlias',
            'createdAt',
            'defaultUnitCategoryId',
            'description',
            'durationOfAction',
            'durationOfActionInHours',
            'fillingValue',
            'imageUrl',
            'informationalUrl',
            'kurtosis',
            'maximumAllowedValue',
            'mean',
            'median',
            'minimumAllowedValue',
            'mostCommonConnectorId',
            'mostCommonOriginalUnitId',
            'mostCommonValue',
            'numberOfMeasurements',
            'numberOfRawMeasurements',
            'numberOfUniqueValues',
            'onsetDelay',
            'onsetDelayInHours',
            self::FIELD_OUTCOME,
            'parentId',
            'pngPath',
            'productUrl',
            self::FIELD_IS_PUBLIC,
            'secondMostCommonValue',
            'shareUserMeasurements',
            'skewness',
            'standardDeviation',
            'thirdMostCommonValue',
            'unitAbbreviatedName',
            'unitCategoryId',
            'unitCategoryName',
            'unitId',
            'unitName',
            'updatedAt',
            'userUnitAbbreviatedName',
            'userUnitCategoryId',
            'userUnitCategoryName',
            'userUnitId',
            'userUnitName',
            'userVariableVariableCategoryId',
            'userVariableVariableCategoryName',
            'variableCategoryId',
            'variableCategoryImageUrl',
            'variableName',
            'variance',
            'wikipediaTitle',
        ];
        foreach($variables as $variable){
            foreach($advancedProperties as $advancedProperty){
                unset($variable->$advancedProperty);
            }
        }
    }
    /**
     * Search public variables
     * @param SearchVariableRequest $searchVariableRequest
     * @param bool $exactMatch
     * @return array|QMCommonVariable[]
     */
    public static function searchPublicVariables(SearchVariableRequest $searchVariableRequest, bool $exactMatch =
    false): array{
        $parameters['searchPhrase'] = $searchVariableRequest->getSearch();
        $parameters['effectOrCause'] = $searchVariableRequest->getEffectOrCause();
        $parameters['publicEffectOrCause'] = $searchVariableRequest->getPublicEffectOrCause();
        $parameters['categoryName'] = $searchVariableRequest->getVariableCategoryName();
        $parameters['exactMatch'] = $exactMatch;
        $parameters['sort'] = '-numberOfUserVariables';
        $parameters['limit'] = 10;
        $parameters['manualTracking'] = $searchVariableRequest->getManualTracking();
        $variableObjects = self::getCommonVariables($parameters);
        if($variableObjects === null || $variableObjects == 'null'){
            $variableObjects = [];
        }
        return $variableObjects;
    }
    /**
     * @param string $newValence
     * @param string|null $reason
     * @return int
     */
    public function updateValence(string $newValence, string $reason = null): int{
        $this->logError("Setting valence to $newValence");
	    UserVariable::whereVariableId($this->getVariableIdAttribute())
	        ->whereNotNull(UserVariable::FIELD_VALENCE)
	        ->where(UserVariable::FIELD_VALENCE, "<>", $newValence)
	        ->update([UserVariable::FIELD_VALENCE => $newValence]);
	    return $this->updateDbRow([self::FIELD_VALENCE => $newValence], $reason);
    }
    /**
     * @param string $reason
     * @return int
     */
    public function hardDeleteCorrelationsWhereOutcome(string $reason): int {
        return QMVariableRelationship::deleteByEffectId($this->getVariableIdAttribute(), $reason);
    }
    /**
     * @return stdClass
     */
    public function getCommonAdditionalMetaData(): stdClass {
        return $this->commonAdditionalMetaData = ObjectHelper::toStdClassIfNecessary($this->commonAdditionalMetaData);
    }
    /**
     * @param array $newMetaData
     * @return bool
     */
    public function updateCommonAdditionalMetaDataIfNecessary(array $newMetaData){
        $existingMetaData = $this->getCommonAdditionalMetaData();
        $needToSave = false;
        foreach($newMetaData as $key => $newValue){
            if(!isset($existingMetaData->$key) || $existingMetaData->$key !== $newValue){
                $existingMetaData->$key = $newValue;
                $needToSave = true;
            }
        }
        if($needToSave){
            $this->updateDbRow([self::FIELD_ADDITIONAL_META_DATA => $existingMetaData], "updated meta data");
        }
        return $needToSave;
    }
    /**
     * @param string|null $tableAlias
     * @return array
     */
    protected static function getSelectColumns(string $tableAlias = null): array {
        return GetCommonVariablesRequest::getCommonVariableColumnsArray(ReadonlyDB::db());
    }
    /**
     * @param string|null $reason
     * @param bool $updateTags
     * @throws AlreadyAnalyzedException
     * @throws AlreadyAnalyzingException
     * @throws ModelValidationException
     */
    public function analyzeFully(string $reason = null, bool $updateTags = true){
        if(!$this->creatorUserId){$this->creatorUserId = UserIdProperty::USER_ID_SYSTEM;}
        $this->beforeAnalysis($reason);
        Memory::setCurrentTask(Memory::TASK_COMMON_VARIABLE_ANALYSIS);
        $this->analyzeUserVariablesIfNecessary();
        try {
            $calculated = $this->calculateAttributes();
        } catch (InvalidAttributeException $e) {
            le($e);
        }
        $l = $this->l();
        $l->internal_error_message = null;
        $l->status = UserVariableStatusProperty::STATUS_UPDATED;
        $charts = $this->setChartsIfPossible();
        if(EnvOverride::isLocal() && !AppMode::isUnitOrStagingUnitTest()){
            $this->saveShowHtml();
        }
        $dirty = $l->getChangeList();
        $this->logInfo("Saving full common variable update", $dirty);
        $l->analysis_ended_at = $this->setAnalysisEndedAtAndStatusUpdated();
        $l->save();
	    $valid = $this->getValidDailyMeasurementsWithTags();
	    $highchartsPopulated = $this->highchartsPopulated();
	    if($valid && !$highchartsPopulated){
		    le('$this->getNumberOfMeasurements() && !$this->highchartsPopulated()'.$this->getAnalyzeUrl());
		}
		if($this->measurements === null){le('$this->measurements === null: '.$this->getAnalyzeUrl());}
    }
    /**
     * @return int
     */
    public function calculateNumberOfCommonTags(): int{
        return VariableNumberOfCommonTagsProperty::calculate($this);
    }
    /**
     * @return int
     */
    public function calculateNumberCommonTaggedBy(): int{
        return VariableNumberCommonTaggedByProperty::calculate($this);
    }
    /**
     * @return int
     */
    public function getOrCalculateNumberCommonTaggedBy(): int{
        $number = $this->getNumberCommonTaggedBy();
        if($number === null){
            $number = $this->calculateNumberCommonTaggedBy();
        }
        return $number;
    }
    /**
     * @param string $reason
     * @param bool $hardDelete
     * @param bool $force
     * @return int
     */
    public function deleteCommonVariableAndAllAssociatedRecords(string $reason, bool $hardDelete, bool $force = false): int {
        if(!$force && $this->numberOfUserVariables > 2 && !$this->isTestVariable()){
            le("$this $this->numberOfUserVariables numberOfUserVariables.  Set force true if you really want to delete");
        }
        $this->logInfo("numberOfUserVariables: $this->numberOfUserVariables");
        if(!$force && $this->getNumberOfMeasurements() > 10 && !$this->isTestVariable()){
            le("$this {$this->getNumberOfMeasurements()} numberOfMeasurements.  Set force true if you really want to delete");
        }
        $this->logInfo("numberOfMeasurements: {$this->getNumberOfMeasurements()}");
        if(!$force && $this->numberOfCorrelations > 10 && !$this->isTestVariable()){
            le("$this $this->numberOfCorrelations numberOfCorrelations.  Set force true if you really want to delete");
        }
        $this->logInfo("numberOfCorrelations: $this->numberOfCorrelations");
        $this->logError("Deleting CommonVariable created ".$this->timeSinceCreatedAtHumanString().
            " And All Associated Records because $reason");
        return self::deleteEverywhere($this->id, $reason, $hardDelete);
    }
    /**
     * @param string $name
     * @param array $newVariableData
     * @return QMCommonVariable
     */
    public static function findOrCreateByName(string $name, array $newVariableData = []){
        $variableName = QMStr::removeDiamondWithQuestionMark($name);
        if($v = self::findInMemoryByNameIdOrSynonym($variableName, $newVariableData)){
            return $v;
        }
        $newVariableData = APIHelper::replaceNamesWithIdsInArray($newVariableData);
        $name = QMStr::replaceDoubleParenthesis($name);
        $name = QMStr::removeDiamondWithQuestionMark($name);
        $v = self::findByNameOrId($name, $newVariableData);
        if($v){$v = self::createNewVariableWithUnitInNameIfNecessary($v, $newVariableData);}
        if(!$v){
            try {
                $v = self::add($name, $newVariableData);
            } catch (\Throwable $e) {
	            $v = self::add($name, $newVariableData);
            }
        }
        return $v;
    }
    public function getDurationOfAction(): int{
        if($this->durationOfAction){
            return $this->durationOfAction;
        }
        return $this->setDurationOfAction($this->getQMVariableCategory()->getDurationOfAction());
    }
    public function getOnsetDelay(): int{
        $od = $this->onsetDelay;
        if($od !== null){
            return $od;
        }
        return $this->setOnsetDelay($this->getQMVariableCategory()->getOnsetDelay());
    }
    /**
     * @param QMCommonVariable $v
     * @param array $newVariableData
     * @return QMCommonVariable
     */
    private static function createNewVariableWithUnitInNameIfNecessary(QMCommonVariable $v, array $newVariableData = []): QMCommonVariable{
        if(isset($newVariableData[self::doNotCreateNewVariableWithNameInUnit])){return $v;}
		if(!$newVariableData){return $v;}
        $unitId = UnitIdProperty::pluckNameOrIdDirectly($newVariableData);
        if($unitId){
            $incompatible = $v->unitIsIncompatible($unitId);
            if($incompatible){
	            $nameWithUnit =
	                VariableNameProperty::withUnit($v->name, QMUnit::getByNameOrId($unitId));
	            $v = self::findByNameOrId($nameWithUnit);
                if(!$v){
	                $v = self::add($nameWithUnit, $newVariableData);
                }
            }
        }
        return $v;
    }
    /**
     * @return string
     */
    public function __toString(){
        return $this->name ?? $this->variableName ?? "Unknown variable name";
    }
    /**
     * @param int $variableId
     * @param string $reason
     * @param bool $hardDelete
     * @return bool
     */
    public static function deleteEverywhere(int $variableId, string $reason, bool $hardDelete): bool{
        $variable = self::find($variableId);
        if(!$variable){
            QMLog::error("Could not get variable with id $variableId");
            return false;
        }
        self::flushAllFromMemory();
        QMUserVariable::flushAllFromMemory();
        $variable->logError('Deleting all associated records because '.$reason);
        self::deleteRelatedRecords($variableId, $hardDelete, $reason);
        return true;
        //TODO: Fix this query
        //        $writableConnection->table('tracking_reminder_notifications')
        //            ->join('tracking_reminder_notifications.tracking_reminder_id', 'tracking_reminders.id')
        //            ->where('tracking_reminders.variable_id', $variableId)
        //            ->delete();
    }
    /**
     * @param [] $requestParams
     * @return QMCommonVariable[]
     */
    public static function getCommonVariablesExactMatchOrFallbackToNonExact($params): array{
        $params['exactMatch'] = true;
        $variables = self::getCommonVariables($params);
        if(count($variables) < 1){
            $params['exactMatch'] = false;
            $variables = self::getCommonVariables($params);
            return $variables;
        }
        if(!$variables || $variables == 'null'){
            $variables = [];
        }
        return $variables;
    }
    /**
     * @param QMCommonVariable[] $combinedVariables
     * @param QMCommonVariable[] $publicVariables
     * @return QMCommonVariable[]
     */
    public static function mergeAndRemoveDuplicateNames(array $combinedVariables, array $publicVariables): array{
        if(is_array($combinedVariables) && is_array($publicVariables)){
            foreach($publicVariables as $publicVariable){
                $duplicate = false;
                foreach($combinedVariables as $variable){
                    if($variable->name === $publicVariable->name){
                        $duplicate = true;
                        continue;
                    }
                }
                if(!$duplicate){
                    $combinedVariables[] = $publicVariable;
                }
            }
            return $combinedVariables;
        }
        if(count($combinedVariables) < 1){
            $combinedVariables = $publicVariables;
            return $combinedVariables;
        }
        if(!$combinedVariables || $combinedVariables === 'null'){$combinedVariables = [];}
        return $combinedVariables;
    }
    /**
     * @param GetUserVariableRequest $commonVariableRequest
     * @return QMCommonVariable[]
     * @internal param SearchVariableRequest $requestParams
     */
    public static function getCommonVariablesWithoutCategoryFilter(GetUserVariableRequest $commonVariableRequest): array{
        $commonVariableRequest->setVariableCategoryId(null);
        $combinedVariables = self::getCommonVariables($commonVariableRequest->getCommonVariableRequest());
        return $combinedVariables;
    }
    /**
     * @param GetUserVariableRequest $req
     * @param QMCommonVariable[] $combinedVariables
     * @return QMCommonVariable[]
     */
    public static function backFillWithCommonVariablesPrivateExactMatchOrWithoutCategoryFilter(GetUserVariableRequest $req, array $combinedVariables): array{
        $publicVariables = self::getCommonVariables($req->getCommonVariableRequest());
        $combinedVariables = self::mergeAndRemoveDuplicateNames($combinedVariables, $publicVariables);
        if(count($combinedVariables) < 1){
            $combinedVariables = self::getCommonVariablesWithoutCategoryFilter($req);
        }
        if(count($combinedVariables) < 1 && $req->getSearchPhrase()){
            $combinedVariables = self::getExactMatch($req->getSearchPhrase());
        }
        return $combinedVariables;
    }
    /**
     * @param string $searchPhrase
     * @return QMCommonVariable[]
     */
    public static function getExactMatch(string $searchPhrase): array{
        $name = str_replace([
            '%',
            '*'
        ], '', $searchPhrase);
        return self::getCommonVariables(['name' => $name]);
    }
    /**
     * @param SearchVariableRequest $request
     * @return QMCommonVariable[]
     */
    public static function searchPublicVariablesAndPrivateExactMatches(SearchVariableRequest $request): array{
        $variables = self::searchPublicVariables($request);
        $containsExactMatch = false;
        foreach($variables as $variable){
            if(QMVariable::isExactMatch($request->getSearch(), $variable->name)){
                $containsExactMatch = true;
            }
        }
        if(!$containsExactMatch){
            $exactMatchVariable = self::searchPublicVariables($request, true);
            if($exactMatchVariable){
                $variables = array_merge($exactMatchVariable, $variables);
                return $variables;
            }
            return $variables;
        }
        return $variables;
    }
    /***
     * @return AnonymousMeasurement[]
     * @throws InsufficientMemoryException
     */
    public function setAllRawMeasurementsInCommonUnitInChronologicalOrder(): array {
        $number = $this->numberOfRawMeasurementsWithTagsJoinsChildren ?: $this->getNumberOfMeasurements();
        $dateGrouping = null;
        if ($number && AppMode::isApiRequest()) {
            try {
                ThisComputer::exceptionIfInsufficientMemoryForArray($number, AnonymousMeasurement::BYTES_PER_INSTANCE);
            } catch (InsufficientMemoryException $e) {
                $this->logError("We have to group measurements by date because " . $e->getMessage());
                $dateGrouping = '%Y-%m-%d';
            }
        }
        $combOp = ($this->isMeanCombinationOperation()) ? "AVG" : "SUM";
        $measurements = AnonymousMeasurement::getByVariableId($this->getVariableIdAttribute(), null, $dateGrouping, $combOp);
        return $this->measurements = $measurements;
    }
    /**
     * @return AnonymousMeasurement[]
     * @throws InsufficientMemoryException
     */
    public function getQMMeasurements(): array {
        if($this->measurements === null){
            $this->setAllRawMeasurementsInCommonUnitInChronologicalOrder();
        }
        return $this->measurements;
    }
    /***
     * @return AnonymousMeasurement[]
     * @throws InsufficientMemoryException
     */
    public function setDailyMeasurements(): array {
        if($all = $this->measurements){
            $daily = DailyAnonymousMeasurement::aggregateDaily($all, $this);
            $this->validateDailyMeasurementIndexing($daily);
            return $this->dailyMeasurements = $daily;
        }
        $measurements = DailyAnonymousMeasurement::getAveragedByDate($this->getVariableIdAttribute());
        $this->validateDailyMeasurementIndexing($measurements);
        return $this->dailyMeasurements = $measurements;
    }
    /**
     * @return QMUserVariable[]
     */
    public function getSourceObjects(): array{
        return $this->getQMUserVariables();
    }
    /**
     * @return AnonymousMeasurement[]
     * @throws InvalidVariableValueException
     * @throws IncompatibleUnitException
     */
    public function generateValidDailyMeasurementsWithTags(): array {
        $thisVariable = $this->getDailyMeasurementsWithoutTagsOrFilling();
        $taggedVariables = $this->getCommonTaggedVariables();
        $allConverted = [];
        foreach($taggedVariables as $taggedVariable){
            $fromTaggedMeasurements = $taggedVariable->getDailyMeasurementsWithoutTagsOrFilling();
            $converted = $this->convertTaggedMeasurements($taggedVariable->tagConversionFactor, 
                                                          $fromTaggedMeasurements,
                                                          $taggedVariable);
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $allConverted = array_merge($allConverted, $converted);
        }
        if(!$allConverted){
			$this->setDailyMeasurementsWithTags($thisVariable);
            return $thisVariable;
        }
        $measurements = array_merge($thisVariable, $allConverted);
        $measurements = MeasurementStartTimeProperty::sortMeasurementsChronologically($measurements);
	    $this->setDailyMeasurementsWithTags($measurements);
        return $measurements;
    }
    /**
     * @return VariableChartChartGroup
     */
    public function setCharts(): ChartGroup {
        $charts = new VariableChartChartGroup($this); // Only image urls
        return $this->charts = $charts;
    }
    /**
     * @param array|GetCommonVariablesRequest $requestParams
     * @return GetCommonVariablesRequest
     */
    private static function convertRequestParamsToVariableRequestIfNecessary($requestParams){
        if(is_array($requestParams)){
            $getCommonVariablesRequest = new GetCommonVariablesRequest($requestParams);
        }else{
            $getCommonVariablesRequest = $requestParams;
        }
        return $getCommonVariablesRequest;
    }
    /**
     * @return int
     */
    public function calculateNumberOfGlobalVariableRelationshipsAsCause(): int {
        return VariableNumberOfGlobalVariableRelationshipsAsCauseProperty::calculate($this);
    }
    /**
     * @return int
     */
    public function calculateNumberOfGlobalVariableRelationshipsAsEffect(): int {
        return VariableNumberOfGlobalVariableRelationshipsAsEffectProperty::calculate($this);
    }
    /**
     * @return int
     */
    public function calculateNumberOfAggregatedCorrelations(): int{
        return $this->numberOfGlobalVariableRelationships = $this->calculateNumberOfGlobalVariableRelationshipsAsCause() + $this->calculateNumberOfGlobalVariableRelationshipsAsEffect();
    }
    /**
     * @param bool $includeDeleted
     * @return int
     */
    public function calculateNumberOfMeasurements(bool $includeDeleted = false): int {
        return VariableNumberOfMeasurementsProperty::calculate($this);
    }
    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function trackingReminderQb(): Builder{
        return TrackingReminder::query()
            ->whereNull(TrackingReminder::FIELD_DELETED_AT)
            ->where(TrackingReminder::FIELD_VARIABLE_ID, $this->id);
    }
    /**
     * @param string|null $reason
     * @return bool
     */
    public function deleteIfNoAggregatedCorrelationsAndNoMeasurements(string $reason = null): bool{
        if(VariableNumberOfTrackingRemindersProperty::calculate($this)){
            QMLog::error("Not deleting $this->name because we have $this->numberOfTrackingReminders TrackingReminders");
            return false;
        }
        if(Measurement::whereVariableId($this->getVariableIdAttribute())->withTrashed()->count()){
            QMLog::error("Not deleting $this->name because we have {$this->getNumberOfMeasurements()} ".
                "RawMeasurements including deleted ones");
            return false;
        }
        if($this->calculateNumberOfAggregatedCorrelations()){
            QMLog::error("Not deleting $this->name because we have $this->numberOfGlobalVariableRelationships AggregatedCorrelations");
            return false;
        }
        if ($taggedBy = $this->calculateNumberCommonTaggedBy()) {
            QMLog::error("Not deleting $this->name because we have $taggedBy NumberOfCommonTaggedBy");
            return false;
        }
        if($num = $this->calculateNumberOfCommonTags()){
            QMLog::error("Not deleting $this->name because we have $num numberOfCommonTags");
            return false;
        }
        $reason = "we have no measurements, common tags, reminders or AggregatedCorrelations and $reason";
        QMLog::error("Deleting $this->name because we have no $reason");
        $this->deleteCommonVariableAndAllAssociatedRecords($reason, true);
        return true;
    }
    /**
     * @throws AlreadyAnalyzedException
     * @throws AlreadyAnalyzingException
     * @throws ModelValidationException
     * @throws NoUserVariableRelationshipsToAggregateException
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     * @throws DuplicateFailedAnalysisException
     * @throws StupidVariableNameException
     */
    public function recalculateAllCorrelationsWithInvalidValues(){
        $userVariableRelationships = QMUserVariableRelationship::getOrCreateUserOrGlobalVariableRelationships([
            'causeVariableId' => $this->id,
            'limit'           => 0
        ]);
        foreach($userVariableRelationships as $c){$c->validateOrReAnalyze();}
    }
    /**
     * @return QMUserVariable[]
     */
    public function getQMUserVariables(): array {
        $dms = $this->qmUserVariables;
        if($dms === null){
            $userVariables = $this->getUserVariables();
            $dms = QMUserVariable::toDBModels($userVariables);
        }
        return $this->qmUserVariables = $dms;
    }
    /**
     * @return UserVariable[]
     */
    public function getUserVariables(bool $excludeDeletedAndTestUsers = true): array {
        $ids = $this->userVariableIds;
        $userVariables = [];
        if($ids === null){
            $v = $this->l();
            $qb = $v->user_variables($excludeDeletedAndTestUsers);
            $userVariables = $qb->get();
            /** @var UserVariable $uv */
            foreach($userVariables as $uv){
                $uv->setRelationAndAddToMemory('variable', $v);
                $uv->addToMemory();
            }
            $this->userVariableIds = $userVariables->pluck('id');
            return $userVariables->all();
        }
        foreach($ids as $id){$userVariables[$id] = UserVariable::findInMemoryOrDB($id);}
        return $userVariables;
    }
    /**
     * @return QMQB
     */
    public static function getMeasurementJoinedQb(): QMQB{
        return QMMeasurement::writable()
            ->join(self::TABLE, self::TABLE.'.'.self::FIELD_ID, '=',
                Measurement::TABLE.'.'. Measurement::FIELD_VARIABLE_ID);
    }
    /**
     * @param array $arr
     * @param string|null $reason
     * @return int
     * @deprecated Use Eloquent model save directly
     */
    public function updateDbRow(array $arr, string $reason = null): int{
        $this->logInfoWithoutObfuscation("Updating common variable row $this->name because $reason. ",
            ['update_row' => $arr]);
        if(isset($arr[self::FIELD_OUTCOME])){
            $this->logDebug("Setting $this->name outcome to ".$arr[self::FIELD_OUTCOME]."because $reason. ");
        }
        $this->validateValuesInUpdateArray($arr);
        return parent::updateDbRow($arr, $reason);
    }
    /**
     **    Returns an array of all tag_variable variables of the given variable
     * @return array|QMVariable[]
     */
    public function setCommonTagVariables(): array {
        $this->verifyJsonEncodableAndNonRecursive();
        $v = $this->getVariable();
        $tags = $v->common_tags_where_tagged_variable()->get();
        $vars = [];
        /** @var CommonTag $tag */
        foreach($tags as $tag){
            $vars[] = self::find($tag->tag_variable_id);
        }
        $this->verifyJsonEncodableAndNonRecursive();
        $this->setNumberOfCommonTags(count($vars));
        return $this->commonTagVariables = $vars;
    }
    public function setCommonTaggedVariables(): array{
        $this->verifyJsonEncodableAndNonRecursive();
        $tagged = [];
        $num = $this->numberCommonTaggedBy;
        if($num > 100){
            $this->logError("setting $num CommonTaggedVariables...");
        }else{
            $this->logInfo("setting $num CommonTaggedVariables...");
        }
        $taggedRows = $this->setCommonTaggedRows();
        $total = count($taggedRows);
        $i = 0;
        foreach($taggedRows as $row){
            $i++;
            if($i % 10 === 0){
                $this->logInfo("Getting $i of $total tagged common variables.
                    Current ID: ".$row->tagged_variable_id);
            }
            $v = QMCommonVariable::find($row->tagged_variable_id);
            if($v->getVariableCategoryName() === ElectronicsVariableCategory::NAME){
                QMCommonTag::delete($this->getVariableIdAttribute(),
                    $v->getVariableIdAttribute(), "The tag is electronics");
                continue;
            }
            if($v){
                $v->tagConversionFactor = $row->conversion_factor;
                if(AppMode::isApiRequest()){
                    $tagged[] = clone $v;  // Avoid recursion on json_encode
                }else{
                    $tagged[] = $v; // If we clone here, we have to keep getting measurements from database
                }
            }else{
                $this->logError('Could not find tagged variable by id',
                    ['userTagVariableId' => $this->variableId, 'tag' => $row]);
            }
		if($v->tagConversionFactor === null){le( "tagConversionFactor not set on $v");}
        }
        $this->verifyJsonEncodableAndNonRecursive();
        return $this->commonTaggedVariables = $tagged;
    }
    /**
     * @param float $minimumAllowedValueInCommonUnit
     * @return bool
     * @throws InvalidVariableValueException
     */
    public function updateMinimumAllowedValue(float $minimumAllowedValueInCommonUnit){
        if($this->minimumAllowedValue === $minimumAllowedValueInCommonUnit){
            QMLog::info("minimumAllowedValue already set to $minimumAllowedValueInCommonUnit");
            return false;
        }
        $this->setMinimumAllowedValue($minimumAllowedValueInCommonUnit);
        if($minimumAllowedValueInCommonUnit !== null){
            $this->getMeasurementsQb()->where(Measurement::FIELD_VALUE, '<', $minimumAllowedValueInCommonUnit)->update([
                Measurement::FIELD_DELETED_AT => date('Y-m-d H:i:s'),
                Measurement::FIELD_ERROR      => 'too small for variable'
            ]);
        }
        $this->setAssociatedUserVariablesToReCorrelate(__METHOD__);
        return $this->updateDbRow([self::FIELD_MINIMUM_ALLOWED_VALUE => $minimumAllowedValueInCommonUnit], __METHOD__);
    }
    private function scheduleAggregatedCorrelations(string $reason){
        QMGlobalVariableRelationship::scheduleAnalysisWhere(GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID,
            $this->id, $reason);
        QMGlobalVariableRelationship::scheduleAnalysisWhere(GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID,
            $this->id, $reason);
    }
    /**
     * @return QMQB
     */
    public function getMeasurementsQb(): QMQB{
        return QMMeasurement::writable()->where(Measurement::FIELD_VARIABLE_ID, $this->id);
    }
    /**
     * @param int $newCategoryId
     * @param string|null $reason
     * @return bool
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function changeVariableCategory(int $newCategoryId, string $reason = null){
        if($this->variableCategoryId === $newCategoryId){
            $this->logInfo("variableCategoryId already set to $newCategoryId");
            return false;
        }
        $newCategory = QMVariableCategory::find($newCategoryId);
        $existing = $this->getQMVariableCategory();
        if($newCategory->isStupidCategory() && !$existing->isStupidCategory()){
            $this->logError("Not changing from good category ($existing) to stupid category ($newCategory)");
            return false;
        }
        $this->logError(__FUNCTION__." because ".$reason);
        $this->setVariableCategory($newCategoryId);
        $this->getMeasurementsQb()->update([Measurement::FIELD_VARIABLE_CATEGORY_ID => $newCategoryId]);
        UserVariableRelationship::whereCauseVariableId($this->getId())
            ->update([UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID => $newCategoryId]);
        UserVariableRelationship::whereEffectVariableId($this->getId())
            ->update([UserVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID => $newCategoryId]);
        GlobalVariableRelationship::whereCauseVariableId($this->getId())
            ->update([GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID => $newCategoryId]);
        GlobalVariableRelationship::whereEffectVariableId($this->getId())
            ->update([GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID => $newCategoryId]);
        UserVariable::whereVariableId($this->getId())
            ->update([UserVariable::FIELD_VARIABLE_CATEGORY_ID => $newCategoryId]);
	    return $this->updateDbRow([self::FIELD_VARIABLE_CATEGORY_ID => $newCategoryId], __METHOD__);
    }
    /**
     * @param $reason
     * @return int
     */
    private function setAssociatedUserVariablesToReCorrelate($reason): int{
        $reason = "Setting all $this->name user variables to re-correlate because $reason";
        QMLog::errorIfProduction($reason);
        $this->scheduleAggregatedCorrelations($reason);
        return QMUserVariable::setAllUserVariablesToReCorrelate($this->getVariableIdAttribute(), $reason);
    }
    /**
     * @param int $newDefaultUnitId
     * @return int
     */
    public function updateDefaultUnitIdOnlyInDBRow(int $newDefaultUnitId): int{
        return $this->updateDbRow([self::FIELD_DEFAULT_UNIT_ID => $newDefaultUnitId], __METHOD__);
    }
    /**
     * @param int|string $newUnit
     * @param float $multiplyOldUnitValuesByThisFactorToConvertToNewUnit
     * @param float $additionFactor
     * @param int|string $oldUnit
     * @throws InvalidAttributeException
     * @throws ModelValidationException
     * @throws NotEnoughDataException
     */
    public function changeAndConvertToNewDefaultUnitEverywhere($newUnit,
                                                               float $multiplyOldUnitValuesByThisFactorToConvertToNewUnit,
                                                               float $additionFactor, $oldUnit): void {
        $oldUnit = QMUnit::getByNameOrId($oldUnit);
        $newUnit = QMUnit::getByNameOrId($newUnit);
        if($this->unitIsIncompatible($newUnit)){
            le("New unit $newUnit not compatible with current unit $oldUnit!");
        }
        $this->logInfo("Changing default unit from $oldUnit to $newUnit");
        VariableDefaultUnitIdProperty::changeDefaultUnitEverywhere($this->name,
            $newUnit,
            $multiplyOldUnitValuesByThisFactorToConvertToNewUnit,
            $additionFactor,
            $oldUnit);
    }
    public function changeDefaultUnitToFiveRatingFromPercent(): void {
        if(!$this->getCommonUnit()->name == PercentUnit::NAME){
            le("Not percent!");
        }
        if(strpos($this->name, "%")){
            le("Name contains percent!");
        }
        $fiveRating = QMUnit::getOneToFiveRating();
        $this->changeAndConvertToNewDefaultUnitEverywhere($fiveRating->abbreviatedName, 0.04,
            1, PercentUnit::ID);
    }
    /**
     * @return float
     */
    public function getMostCommonValue(): float{
        return $this->mostCommonValue;
    }
    /**
     * @return QMCommonVariable
     */
    public function getSpendingVariable(): QMCommonVariable {
        $spendingVariableName = VariableNameProperty::toSpending($this->name);
        $v = self::find($spendingVariableName);
        if($v){
            $v->throwExceptionIfSpendingVariableDoesNotContainSpending();
            return $v;
        }
        $arr = $this->getDuplicateVariableInsertionArray();
        $arr = array_merge($arr, self::$purchasesAndSpendingConstants);
        $arr[self::FIELD_NAME] = $spendingVariableName;
        $arr[self::FIELD_DEFAULT_UNIT_ID] = QMUnit::getDollars()->id;
        $arr[self::FIELD_SYNONYMS] = [];
        foreach($this->getSynonymsAttribute() as $synonym){
            $arr[self::FIELD_SYNONYMS][] = VariableNameProperty::toSpending($synonym);
        }
        $arr[self::FIELD_SYNONYMS] = json_encode($arr[self::FIELD_SYNONYMS]);
        $this->logInfo("creating spending variable");
        $arr = VariableNameProperty::validateNewSpendingVariable($arr);
        $id = self::writable()->insertGetId($arr);
        $v = self::find($id);
        $v->throwExceptionIfSpendingVariableDoesNotContainSpending();
        return $v;
    }
    /**
     * @return QMCommonVariable
     */
    public function getPurchasesVariable(): QMCommonVariable {
        $purchasesVariableName = VariableNameProperty::toPurchases($this->name);
        $purchasesVariable = self::find($purchasesVariableName);
        if($purchasesVariable){
            $purchasesVariable->throwExceptionIfPurchasesVariableDoesNotContainPurchases();
            return $purchasesVariable;
        }
        $insertionArray = $this->getDuplicateVariableInsertionArray();
        $insertionArray = array_merge($insertionArray, self::$purchasesAndSpendingConstants);
        $insertionArray[self::FIELD_NAME] = $purchasesVariableName;
        $insertionArray[self::FIELD_DEFAULT_UNIT_ID] = CountUnit::ID;
        $insertionArray[self::FIELD_SYNONYMS] = [];
        foreach($this->getSynonymsAttribute() as $synonym){
            $insertionArray[self::FIELD_SYNONYMS][] = VariableNameProperty::toPurchases($synonym);
        }
        $insertionArray[self::FIELD_SYNONYMS] = json_encode($insertionArray[self::FIELD_SYNONYMS]);
        $this->logInfo("creating purchases variable");
        $insertionArray = VariableNameProperty::validateNewPurchasesVariable($insertionArray);
        $id = self::writable()->insertGetId($insertionArray);
        $purchasesVariable = self::find($id);
        $purchasesVariable->throwExceptionIfPurchasesVariableDoesNotContainPurchases();
        return $purchasesVariable;
    }
    private function throwExceptionIfSpendingVariableDoesNotContainSpending(){
        if($this->name !== VariableNameProperty::toSpending($this->name)){
            $message = "Spending variable name $this->name should be ".
                VariableNameProperty::toSpending($this->name);
            $this->logError($message);
            le($message);
        }
    }
    private function throwExceptionIfPurchasesVariableDoesNotContainPurchases(){
        if($this->name !== VariableNameProperty::toPurchases($this->name)){
            $message = "Purchases variable name $this->name should be ".
                VariableNameProperty::toPurchases($this->name);
            $this->logError($message);
            le($message);
        }
    }
    /**
     * @return array
     */
    private function getDuplicateVariableInsertionArray(): array{
        $fields = Writable::getAllColumnsForTable(self::TABLE);
        $array = [];
        foreach($fields as $field){
            $camelField = QMStr::toCamelCase($field);
            if(isset($this->$camelField)){
                if(is_array($this->$camelField)){
                    $array[$field] = json_encode($this->$camelField);
                }else{
                    $array[$field] = $this->$camelField;
                }
            }
        }
        unset($array[self::FIELD_ID], $array[Variable::FIELD_NUMBER_OF_MEASUREMENTS],
	        $array[self::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_CAUSE], $array[self::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_EFFECT], $array[self::FIELD_NUMBER_OF_TRACKING_REMINDERS], $array[self::FIELD_NUMBER_OF_UNIQUE_VALUES], $array[self::FIELD_NUMBER_OF_USER_VARIABLES]);
        $array[self::FIELD_STATUS] = UserVariableStatusProperty::STATUS_WAITING;
        return $array;
    }
    /**
     * @return QMCommonVariable
     */
    public function getOrCreateNonPaymentVariable(){
        $nonPaymentVariableName = VariableNameProperty::stripSpendingPurchasePayments($this->name);
        $nonPaymentVariable = self::find($nonPaymentVariableName);
        if($nonPaymentVariable){
            return $nonPaymentVariable;
        }
        $arr = $this->getDuplicateVariableInsertionArray();
        $arr[self::FIELD_NAME] = $nonPaymentVariableName;
        $arr[self::FIELD_DEFAULT_UNIT_ID] = QMUnit::getCount()->id;
        $this->logInfo("creating non-payment variable");
        $arr = VariableNameProperty::validateNewSpendingVariable($arr);
        $id = self::writable()->insertGetId($arr);
        $nonPaymentVariable = self::find($id);
        return $nonPaymentVariable;
    }
    /**
     * @return string[]
     */
    public function calculateNonUniqueDataSourceNames(): array {
        $l = $this->l();
        $uvcs = $l->user_variable_clients()->get();
        $names = $uvcs->pluck(UserVariableClient::FIELD_CLIENT_ID)->all();
        $m = $this->measurements;
        if(!$names && $m){
            $this->logError("No user_variable_clients even though there are ".count($m)." raw measurements! ");
        }
        return $names;
    }
    /**
     * @param array $params
     * @return self[]
     */
    public static function get(array $params = []): array{
        return self::getCommonVariables($params);
    }
    /**
     * @return AnonymousMeasurement[]
     * @throws InvalidVariableValueException
     * @throws IncompatibleUnitException
     */
    public function setMeasurementsWithTags(): array {
        $thisVariable = $this->getQMMeasurements();
        $taggedVariables = $this->getCommonTaggedVariables();
        $allConverted = [];
        foreach($taggedVariables as $taggedVariable){
            $fromTaggedMeasurements = $taggedVariable->getQMMeasurements();
            $converted = $this->convertTaggedMeasurements($taggedVariable->tagConversionFactor,
                $fromTaggedMeasurements, $taggedVariable);
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $allConverted = array_merge($allConverted, $converted);
        }
        if(!$allConverted){
            return $this->measurementsWithTags = $thisVariable;
        }
        $measurements = array_merge($thisVariable, $allConverted);
        $measurements = MeasurementStartTimeProperty::sortMeasurementsChronologically($measurements, __FUNCTION__, true);
        return $this->measurementsWithTags = $measurements;
    }
    public function fixMinimumAllowedValuesForAllUserVariables(): void{
        $min = $this->minimumAllowedValue;
        if($min !== null){
            $reason = "Removed minimum allowed value";
            UserVariable::whereVariableId($this->getVariableIdAttribute())
                ->whereNotNull(UserVariable::FIELD_MINIMUM_ALLOWED_VALUE)
                ->where(UserVariable::FIELD_MINIMUM_ALLOWED_VALUE, "<", $min)
                ->update([
                    UserVariable::FIELD_MINIMUM_ALLOWED_VALUE => null,
                    UserVariable::FIELD_ANALYSIS_REQUESTED_AT => now_at(),
                    UserVariable::FIELD_STATUS => UserVariableStatusProperty::STATUS_WAITING,
                    UserVariable::FIELD_REASON_FOR_ANALYSIS => $reason,
                    UserVariable::FIELD_NEWEST_DATA_AT => now_at(),
                ]);
        }
    }
    public function deleteSmallMeasurements(): ?bool{
        $min = $this->minimumAllowedValue;
        if($min === null || $min == -1){le("No minimum to delete below!");}
        $unit = $this->getCommonUnit();
        $this->logError("Deleting $this measurements smaller than $min $unit->name");
        return Measurement::whereVariableId($this->id)
            ->where(Measurement::FIELD_UNIT_ID, $unit->id)
            ->where(Measurement::FIELD_VALUE, '<', $min)
            ->forceDelete();
    }
    /**
     * @return int
     */
    protected function setSortingScore(){
        $sort = SortParam::getSort();
        if($sort && stripos(SortParam::getSort(), 'latestTaggedMeasurementTime') === false){
            $score = parent::setSortingScore();
            if($score !== null){
                return $score;
            }
        }
        $userVariables = $this->getNumberOfUserVariables();
        $reminders = $this->getNumberOfTrackingReminders();
        if($reminders){
            $score = $userVariables * $reminders;
        }else{
            $score = $userVariables;
        }
        return $this->sortingScore = $score;
    }
    /**
     * @return VariableChartChartGroup
     */
    public function getChartGroup(): ChartGroup {
        $charts = $this->charts;
        if($charts === null){
            $l = $this->l();
            $plucked = $l->pluckCharts();
            $this->charts = $charts = $plucked;
        }
        if(!$charts){$charts = $this->setCharts();}
        $charts = VariableChartChartGroup::instantiateIfNecessary($charts);
        $charts->setSourceObject($this);
        // Make sure they're all set to avoid duplicate queries do to variable limits in getPredictors
        $this->getPublicOutcomesOrPredictors();
        $charts->getOrSetHighchartConfigs();
        return $this->charts = $charts;
    }
    /**
     * @return QMCommonVariable
     */
    public function getNonPaymentVariable(): ?QMCommonVariable {
        return $this->getOrCreateNonPaymentVariable();
    }
    /**
     * @return string
     */
    public function getPHPUnitJobTest(): string{
        return $this->getPHPUnitTestUrl();
    }
    public static function updateDatabaseTableFromHardCodedConstants(): array {
	    QMLog::logStartOfProcess(static::TABLE . ' ' . __FUNCTION__);
        QMLog::infoWithoutContext("Updating common variables based on category, unit, and variable constants...");
        self::updateDbFromCategoryConstants();
        //self::updateDbFromUnitConstants($db);
        $changes = self::updateDbFromVariableConstants();  // Do most specific last so it overwrites the general category constants
        $v = Variable::find(TimeSpentOnBusinessActivitiesCommonVariable::ID);
        if($v->manual_tracking){
            le("Didn't update manual tracking on $v");
        }
        self::updateSpendingAndPurchasesConstantsInDbFromHardCodedData();
//        $uv = UVIndexCommonVariable::getUserVariableByUserId(230);
//        if($uv->getVariableCategory()->getId() === MiscellaneousVariableCategory::ID){
//            le("What the fuck?");
//        }
        self::assertNoInvalidRecords();
	    QMLog::logEndOfProcess(static::TABLE . ' ' . __FUNCTION__);
        return $changes;
    }
    /**
     * @return VariableSearchResult
     */
    public static function toSearchResult(): VariableSearchResult{
        $searchResult = new VariableSearchResult();
        $me = new static();
        if(!$me->commonUnitId){
            $me->commonUnitId = $me->getUnitIdAttribute();
        }
        if(!$me->commonUnitId){
            le("No unit id!");
        }
        $me->populateDefaultFields();
        $searchResult->populateFieldsByArrayOrObject($me);
        return $searchResult;
    }
    /**
     * @param string $reason
     * @throws AlreadyAnalyzedException
     * @throws AlreadyAnalyzingException
     */
    public function analyzePartially(string $reason){
        $this->analyzeFully($reason);
    }
    /**
     * @return static
     */
    public static function instance(): self {
        if(static::ID){
            return static::find(static::ID);
        }
        return new static();
    }
	public function getUserVariable(int $userId): UserVariable{
		return $this->getVariable()->getOrCreateUserVariable($userId);
	}
    /**
     * @param int $userId
     * @return QMUserVariable
     */
    public static function getUserVariableByUserId(int $userId): QMUserVariable {
        $v = QMUserVariable::getOrCreateById($userId, static::ID);
        return $v;
    }
    /**
     * @param array $arr
     * @param bool $unsetIfInvalid
     * @return array
     */
    protected function validateValuesInUpdateArray(array $arr, bool $unsetIfInvalid = false): array {
        $hardCodedVariable = $this->getHardCodedVariable();
        foreach ($arr as $key => $value) {
            if ($hardCodedVariable) {
                $constValue = $hardCodedVariable->getAttribute($key);
                if($constValue !== null){
                    if (is_array($value)) {$value = json_encode($value);}
                    if (is_array($constValue)) {$constValue = json_encode($constValue);}
                    if ($constValue != $value) {
                        $message =
                            "Unsetting $key because provided $key ($value) does not equal constant $key value: " .
                            QMLog::var_export($constValue, true);
                        $this->logError($message);
                        unset($arr[$key]);
                        // TODO: Uncomment after fixing filling value in analysis
                        //le($message);
                    }
                }
            }
        }
        if($syn = $arr[self::FIELD_SYNONYMS] ?? null){
            BaseSynonymsProperty::makeSureSynonymsDoNotHaveDoubleSlashes($syn);
        }
        return parent::validateValuesInUpdateArray($arr);
    }
    /**
     * @return array
     */
    public static function getAnalysisSettingsFields(): array{
        $fields = parent::getAnalysisSettingsFields();
        $fields[] = self::FIELD_NUMBER_COMMON_TAGGED_BY;
        $fields[] = self::FIELD_NUMBER_OF_COMMON_TAGS;
        return $fields;
    }
    /**
     * @return array
     */
    public static function getColumns(): array{
        return parent::getColumns();
    }
    /**
     * @return array
     */
    public static function getDynamicCalculatedFields(): array {
        $arr = parent::getDynamicCalculatedFields();
        $arr = array_unique(array_merge($arr, [
            Variable::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT,
            Variable::FIELD_ANALYSIS_ENDED_AT,
            Variable::FIELD_DATA_SOURCES_COUNT,
            Variable::FIELD_DELETED_AT,
            Variable::FIELD_KURTOSIS,
            Variable::FIELD_MAXIMUM_RECORDED_VALUE,
            Variable::FIELD_MEAN,
            Variable::FIELD_MEDIAN,
            Variable::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS,
            Variable::FIELD_MINIMUM_RECORDED_VALUE,
            Variable::FIELD_NUMBER_COMMON_TAGGED_BY,
            Variable::FIELD_NUMBER_OF_MEASUREMENTS,
            Variable::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN,
            Variable::FIELD_NUMBER_OF_USER_VARIABLES,
            Variable::FIELD_REASON_FOR_ANALYSIS,
            Variable::FIELD_SKEWNESS,
        ]));
        return $arr;
    }
    /**
     * @param string $predictorVariableCategoryName
     * @param int $limit
     * @return Collection|UserVariableRelationship[]
     */
    protected function setCorrelationsForPredictorCategory(string $predictorVariableCategoryName, int $limit = 0): Collection{
        $l = $this->l();
        $qb = $l->correlations_where_effect_variable();
        $qb->with('cause_variable');
        $cat = QMVariableCategory::find($predictorVariableCategoryName);
        $qb->where(UserVariableRelationship::TABLE.'.'.UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID, $cat->id);
        if($limit){$qb->limit($limit);}
        $qb->whereNotNull(UserVariableRelationship::TABLE.'.'.UserVariableRelationship::FIELD_ANALYSIS_ENDED_AT);
        $correlations = $qb->get();
        return $this->correlationsForPredictorCategory[$predictorVariableCategoryName][$limit] = $correlations;
    }
    public function roundStartTimeAndDeleteExtraMeasurements(){
        $beforeFromV = Measurement::whereVariableId($this->getVariableIdAttribute())->withTrashed()->count();
        $this->logInfo("Measurements calculated by variable before: $beforeFromV");
        $minSeconds = $this->getMinimumAllowedSecondsBetweenMeasurements();
        if($minSeconds < 2){
            $this->logError("Min seconds is $minSeconds");
            return;
        }
        if($minSeconds > 86400){
            $this->logError("Min seconds is $minSeconds");
            return;
        }
        $whereUnRounded = "from measurements where MOD(start_time, $minSeconds) <> 0 and variable_id = $this->id;";
        $count = Writable::selectStatic("select count(*) as number_unrouded $whereUnRounded");
        $this->logInfo("Number that need rounding BEFORE ROUNDING: ".\App\Logging\QMLog::print_r($count, true));
        Writable::statementStatic("update ignore measurements
                set start_time = round(start_time/$minSeconds,0)*$minSeconds where variable_id = $this->id;");
        $count = Writable::selectStatic("select count(*) as number_unrouded $whereUnRounded");
        $this->logInfo("Number that need rounding AFTER ROUNDING: ".\App\Logging\QMLog::print_r($count, true));
        $after = Measurement::whereVariableId($this->getVariableIdAttribute())->withTrashed()->count();
        \App\Logging\ConsoleLog::info("Measurements after ROUNDING: $after");
        $count = Writable::selectStatic("select count(*) as number_unrouded $whereUnRounded");
        QMLog::print($count, "Un-Rounded");
        Writable::statementStatic("delete measurements $whereUnRounded");
        $count = Writable::selectStatic("select count(*) as number_unrouded $whereUnRounded");
        $this->logInfo("Number that need rounding AFTER DELETING: ".\App\Logging\QMLog::print_r($count, true));
        $after = Measurement::whereVariableId($this->getVariableIdAttribute())->withTrashed()->count();
        $this->logInfo("Measurements after delete: $after");
        QMUserVariable::setStatusWaitingByVariableId($this->id, __FUNCTION__);
        //$this->analyzeSourceObjects("Rounded Measurement Times");
    }
    /**
     * @return array
     */
    public function setCommonTaggedRows(): array {
        $rows = QMCommonTag::readonly()
            ->whereRaw(CommonTag::FIELD_TAGGED_VARIABLE_ID.' <> '.$this->variableId)
            ->where(CommonTag::FIELD_TAG_VARIABLE_ID, $this->variableId)
            ->getArray();
        return $this->commonTaggedRows = $rows;
    }
    /**
     * @inheritDoc
     */
    public function getCategoryName(): string{
        return WpPost::CATEGORY_GLOBAL_POPULATION_VARIABLE_OVERVIEWS;
    }
    public function getUserId(): ?int{
        return UserIdProperty::USER_ID_POPULATION;
    }
    /**
     * @param string $reason
     */
    protected function hardDeleteRelated(string $reason){
        $userVariables = $this->getQMUserVariables();
        foreach($userVariables as $v){
            $v->hardDeleteWithRelations($reason);
        }
        parent::hardDeleteRelated($reason);
    }
    public function cleanup(){
        $this->fixMinimumAllowedValuesForAllUserVariables();
    }
    public function getDeleteSmallMeasurementsUrl():string{
        $commonUnit = $this->getCommonUnit();
        $min = $this->minimumAllowedValue;
        if($min === null || $min == -1){le('$min === null || $min == -1');}
        return UrlHelper::getCleanupSelectUrl("select * from measurements",
            "delete measurements from measurements",
            "where variable_id = ".$this->getVariableIdAttribute()." and value < $min and unit = $commonUnit->id",
            "too small");
    }
    public function getDeleteLargeMeasurementsUrl():string{
        $commonUnit = $this->getCommonUnit();
        $max = $this->getMaximumAllowedValueAttribute();
        if($max === null || $max == -1){le('$max === null || $max == -1');}
        return UrlHelper::getCleanupSelectUrl("select * from measurements",
            "delete measurements from measurements",
            "where variable_id = ".$this->getVariableIdAttribute()." and value > $max and unit = $commonUnit->id",
            "too big");
    }
    public function getMeasurementsUrl(): string{
        return Measurement::generateDataLabUrl(null, [Measurement::FIELD_VARIABLE_ID => $this->getVariableIdAttribute()]);
    }
    public function weShouldPost(): bool{
        return $this->getNumberOfCorrelations() > 0 && $this->getIsPublic();
    }
    public function getSourceDataUrl(): string {
        return UserVariable::generateDataLabIndexUrl([
            UserVariable::FIELD_VARIABLE_ID => $this->getVariableIdAttribute(),
        ]);
    }
    /**
     * @param int|string $nameOrId
     * @return QMCommonVariable|QMVariable|null
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function find($nameOrId): ?DBModel {
        if($nameOrId instanceof static){
            return $nameOrId;
        }
        if($fromMemory = self::findInMemoryByNameOrId($nameOrId)){
            $fromMemory->validateId();
            $fromMemory->l()->assertHasStatusAttribute();
            return $fromMemory;
        }
        if(is_int($nameOrId)){
            if($l = Variable::findInMemoryOrDB($nameOrId)){
                return $l->getDBModel();
            } else {
                throw new CommonVariableNotFoundException("Could not find variable with id $nameOrId");
            }
        } else {
            if($l = Variable::findByName($nameOrId)){
                return $l->getDBModel();
            }
        }
        return null;
    }
    /**
     * @return int
     */
    public function getVariableIdAttribute(): ?int{
        $id = $this->variableId ?? $this->id ?? $this->l()->id;
        return $this->variableId = $this->id = $id;
    }
    public function validateId(){
		if(!$this->id){le('!$this->id');}
		if(!$this->variableId){le('!$this->variableId');}
		if($this->variableId !== $this->id){le('$this->variableId !== $this->id');}
    }
    /**
     * @return Variable
     */
    public function l(): Variable{
        /** @var Variable $l */
        $l = parent::l();
        return $l;
    }
    /**
     * Provided source name, client id, connector name, or QMDataSource name should be in measurements.source_name
     * column
     * @return array
     */
    public function calculateDataSourcesCount(): array{
        return VariableDataSourcesCountProperty::calculate($this->l());
    }
    public function addToMemory(): void{
        if($this->laravelModel){
            $l = $this->l();
            $l->assertHasStatusAttribute();
        }
        parent::addToMemory();
    }
    private function setChartsIfPossible(): ?ChartGroup {
        try {
            $charts = $this->getChartGroup();
            $charts->setHighchartConfigs();
            $l = $this->l();
            $l->charts = $charts;
            if(!$charts->highchartsPopulated()){
                $charts->setHighchartConfigs();
            }
		if(!$charts->highchartsPopulated()){le('!$charts->highchartsPopulated()');}
            return $charts;
        } catch (NotEnoughDataException $e) {
            $this->logInfo(__METHOD__.": ".$e->getMessage());
        } catch (TooSlowToAnalyzeException $e) {
            $this->logError(__METHOD__.": ".$e->getMessage());
        }
        return null;
    }
    /**
     * @return QMUserVariable[]
     */
    public function analyzeUserVariablesIfNecessary(): array {
        $userVariables = $this->getUserVariablesThatNeedAnalysis();
        foreach($userVariables as $userVariable){
            try {
                $userVariable->analyzeFullyIfNecessary(__FUNCTION__);
            } catch (TooSlowToAnalyzeException $e) {
                $this->logError(__METHOD__.": ".$e->getMessage());
                continue;
            }
        }
		// This happens randomly in tests if(count($this->getUserVariablesThatNeedAnalysis()) > 0){le('count
	    //($this->getUserVariablesThatNeedAnalysis
	    //()) > 0');}
        return $userVariables;
    }
    public function getUserVariablesThatNeedAnalysis(): array {
        $userVariables = $this->getUserVariables();
        $needAnalysis = [];
        foreach($userVariables as $uv){
            if($uv->needToAnalyze()){
                $needAnalysis[] = $uv;
            }
        }
        return $needAnalysis;
    }
    /**
     * @return string
     */
    public function getEarliestNonTaggedMeasurementStartAt(): ?string{
        if($at = $this->earliestNonTaggedMeasurementStartAt){
            return $at;
        }
        $at = date_or_null($this->earliestNonTaggedMeasurementTime);
        return $this->earliestNonTaggedMeasurementStartAt = $at;
    }
    /**
     * @return Measurement[]
     */
    public function getMeasurements():array{
        return Measurement::fromDBModels($this->getQMMeasurements());
    }
    public function getMaximumDailyValue(): float {
        $userVariables = $this->getUserVariables();
        $values = [];
        foreach($userVariables as $uv){
            $values[] = $uv->getMaximumDailyValue();
        }
        return max($values);
    }
    public function getMinimumDailyValue(): float {
        $userVariables = $this->getUserVariables();
        $values = [];
        foreach($userVariables as $uv){
            $values[] = $uv->getMaximumDailyValue();
        }
        return min($values);
    }
    public function getHtmlContent():string{
        $view = view('variable-content', ['variable' => $this->l()]);
        return HtmlHelper::renderView($view);
    }
    public function getTags():array{
        $arr = $this->getSynonymsAttribute();
        return $arr;
    }
	public function getIsPublic(): ?bool{
        return $this->isPublic;
    }
    public function updateDBFromConstants(): ?array{
        $id = $this->getVariableIdAttribute();
        if (!$id) {le("Please set public const ID; in $this->name constants file");}
        $constants = $this->getHardCodedParametersArray();
        $l = $this->l();
        if($l->name !== $this->name){le('$l->name !== $this->name');}
        foreach($constants as $key => $value){
            if($value === null){continue;}
            $key = strtolower($key);
            if($key === 'status'){le('$key === status');}
			if(!$l->hasAttribute($key)){
				continue;
			}
            $l->setAttributeIfExistsAndDifferent($key, $value);
        }
        $l->creator_user_id = UserIdProperty::USER_ID_SYSTEM;
        $syn = $l->getSynonymsAttribute();
        if(!in_array($l->name, $syn)){
            $syn[] = $l->name;
            $l->synonyms = $syn;
            //$l->synonyms = VariableSynonymsProperty::calculate($l);
        }
        $changes = $l->getChangeList();
        if(!$changes){return $changes;}
        try {$l->save();} catch (ModelValidationException $e) {le($e);}
        $uvUpdateArr = [];
        if(!$l->wasRecentlyCreated){
            $uv = UserVariable::getColumns();
            foreach($changes as $key => $beforeAfter){
                $after = $beforeAfter['after'];
                if(!UserVariable::hasColumn($key)){continue;}
                if(!in_array($key, [
                        UserVariable::FIELD_ID,
                        UserVariable::FIELD_DEFAULT_UNIT_ID,
                        UserVariable::FIELD_DURATION_OF_ACTION,   // Maybe update DURATION_OF_ACTION from constants, too?
                        UserVariable::FIELD_ONSET_DELAY,  // Maybe update onset delay from constants, too?
                    ]) && in_array($key, $uv)){
                    if(is_object($after)||is_array($after)){
                        $after = json_encode($after);
                    }
                    $uvUpdateArr[$key] = $after;
                }
            }
            if(empty($uvUpdateArr)){return $changes;}
            QMLog::info("Updating all $l->name user variables:\n".
                        QMLog::var_export($uvUpdateArr, true));
            UserVariable::whereVariableId($id)->update($uvUpdateArr);
        }
        return $changes;
    }
	/** @noinspection PhpUnused */
	public function generateChildModelCode(): ?string{
		if($this->alreadyGeneratedChildModelCode){
			return null;
		}
		if(!EnvOverride::isLocal()){
			return null;
		}
		ModelGeneratorJob::$variableNamesToGenerate[] = $this->name;
		//\App\Logging\ConsoleLog::info("to generate: " . var_export(array_unique(ModelGeneratorTest::$variableNamesToGenerate), true));
		$content = $this->generateFileContentOfHardCodedModel();
		$directory = $this->getHardCodedCategoryDirectory();
		$this->alreadyGeneratedChildModelCode = true;
		return FileHelper::writeByDirectoryAndFilename($directory, $this->getHardCodedFileName(), $content);
	}
	/**
	 * @return string
	 */
	protected function generateFileContentOfHardCodedModel(): string{
		$reflection = new ReflectionClass(static::class);
		$pluralParentClass = static::getPluralParentClassName();
		$childClassName = $this->getHardCodedShortClassName();
		$unit = $unitClass = null;
		$categoryName = $this->getClassCategoryName();
		if($categoryName === MiscellaneousVariableCategory::NAME){
			$this->generateChildModelCode();
			le("Why are we using MiscellaneousVariableCategory? Try running CommonVariableCleanupJobTest::testUpdateConstants");
		}
		if(method_exists($this, 'getCommonUnit')){
			$unit = $this->getCommonUnit();
			$unitClass = 'App\Units\\' . QMStr::toClassName($unit->getNameAttribute()) . "Unit";
		}
		$content = '<?php' . PHP_EOL;
		$nameSpace = $reflection->getNamespaceName() . '\\' . $pluralParentClass;
		if($categoryName){
			$nameSpace =
				$reflection->getNamespaceName() . '\\' . $pluralParentClass . '\\' . $categoryName . $pluralParentClass;
		}
		$content .= 'namespace ' . $nameSpace . ';' . PHP_EOL;
		$parentClassName = $this->getShortClassName();
		$content .= 'use ' . $reflection->getNamespaceName() . '\\' . $parentClassName . ';' . PHP_EOL;
		if($categoryName){
			$content .= 'use App\VariableCategories\\' . $categoryName . 'VariableCategory;' . PHP_EOL;
		}
		if($unitClass){
			$content .= 'use ' . $unitClass . ';' . PHP_EOL;
		}
		$content .= 'class ' . $childClassName . ' extends ' . $parentClassName . ' {' . PHP_EOL;
		$params = $this->getHardCodedParametersArray();
		$categoryId = $params[Variable::FIELD_VARIABLE_CATEGORY_ID] ?? null;
		if($categoryId){
			$c = QMVariableCategory::find($categoryId);
			$params[Variable::FIELD_VARIABLE_CATEGORY_ID] = $c->getConstantReferenceToPropertyOfChildClass('ID');
		}
		$unitId = $params[Variable::FIELD_DEFAULT_UNIT_ID] ?? null;
		if($unitId){
			$u = QMUnit::find($unitId);
			$params[Variable::FIELD_DEFAULT_UNIT_ID] = $u->getConstantReferenceToPropertyOfChildClass('ID');
		}
		$exclude = [
			Variable::FIELD_WP_POST_ID,
		];
		$alwaysHardCode = [
			Variable::FIELD_CAUSE_ONLY,
			Variable::FIELD_COMBINATION_OPERATION,
			Variable::FIELD_COMMON_ALIAS,
			Variable::FIELD_DEFAULT_UNIT_ID,
			//Variable::FIELD_DEFAULT_VALUE,
			Variable::FIELD_DESCRIPTION,
			Variable::FIELD_DURATION_OF_ACTION,
			Variable::FIELD_FILLING_TYPE,
			Variable::FIELD_INFORMATIONAL_URL,
			Variable::FIELD_IMAGE_URL,
			Variable::FIELD_MANUAL_TRACKING,
			Variable::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS,
			//Variable::FIELD_MAXIMUM_ALLOWED_VALUE,
			//Variable::FIELD_MINIMUM_ALLOWED_VALUE,
			Variable::FIELD_ONSET_DELAY,
			Variable::FIELD_OUTCOME,
			Variable::FIELD_OUTCOME,
			Variable::FIELD_PRICE,
			Variable::FIELD_PRODUCT_URL,
			Variable::FIELD_IS_PUBLIC,
		];
		foreach($params as $fieldName => $value){
			if($fieldName === "public"){
				continue;
			}
			$propertyName = static::getPropertyNameForDbField($fieldName);
			unset($params[$fieldName]);
			if(in_array($fieldName, $alwaysHardCode)){
				$params[$propertyName] = $value;
				continue;
			}
			if(in_array($fieldName, $exclude)){
				continue;
			}
			if(is_object($value)){
				continue;
			}
			if(isset($unit) && isset($unit->$propertyName) && $unit->$propertyName === $value){
				continue;
			}
			if(isset($c) && isset($c->$propertyName) && $c->$propertyName === $value){
				continue;
			}
			if($propertyName === "maximumAllowedValue" && $unit && $unit->maximumValue == $value){
				continue;
			}
			if($propertyName === "minimumAllowedValue" && $unit && $unit->minimumValue == $value){
				continue;
			}
			if($value === null){
				continue;
			}
			if($value === "0"){
				continue;
			}
			$params[$propertyName] = $value;
		}
		$content = ModelGeneratorJob::addPropertiesWithValues($content, $params);
		$content .= '}';
		return $content;
	}
	/**
	 * @return string
	 */
	protected function getHardCodedShortClassName(): string{
		return QMStr::toShortClassName($this->getNameAttribute()) . "CommonVariable";
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
	public function getVariableId():int{
		return $this->variableId;
	}
	/**
	 * @return array
	 */
	public function getHardCodedParametersArray(): array{
		$constants = [];
		$arr = parent::getHardCodedParametersArray();
		$var = $this->getHardCodedVariable();
		$class = get_class($var);
		/** @noinspection PhpUnhandledExceptionInspection */
		$r = new ReflectionClass($class);
		$allConstants = $r->getConstants();
		foreach($arr as $key => $value){
			$constName = strtoupper($key);
			if(isset($allConstants[$constName])){ // Don't use array_key_exists because it will override the images I
				// set in the database
				$constants[$key] = $allConstants[$constName];
			}
        }
		if(empty($constants[self::FIELD_DEFAULT_UNIT_ID])){
			$arr = parent::getHardCodedParametersArray();
			le("No unit id! " . QMLog::var_export($arr, true));
		}
		if(empty($constants[self::FIELD_SYNONYMS])){
			$arr = parent::getHardCodedParametersArray();
			le("No synonyms!" . QMLog::var_export($arr, true));
		}
		if(empty($constants[self::FIELD_NAME])){
			$arr = parent::getHardCodedParametersArray();
			le("No name!" . QMLog::var_export($arr, true));
		}
		$constants[self::FIELD_IS_PUBLIC] = true;
		return $constants;
	}
	/**
	 * @return string
	 */
	public function getHardCodedCategoryDirectory(): string{
		$directory = static::getHardCodedDirectory();
		if($categoryName = $this->getClassCategoryName()){
			$directory = $directory . DIRECTORY_SEPARATOR . $categoryName . "CommonVariables";
		}
		return $directory;
	}
	/**
	 * @return string
	 */
	public static function getHardCodedDirectory(): string{
		return FileHelper::absPath("app/Variables/CommonVariables");
	}
	public function generateHardCodedModel(): ?string{
		return QMCommonVariable::find($this->getVariableIdAttribute())->generateChildModelCode();
	}
	/**
	 * @return QMCommonVariable[]
	 */
	public static function getHardCodedVariables(): array{
		if(!self::$variableConstants){
			$path = self::getHardCodedDirectory();
			$constants = ObjectHelper::instantiateAllModelsInFolder('CommonVariable', $path);
			foreach($constants as $constant){
				$constant->isPublic = true;
			}
			self::$variableConstants = $constants;
		}
		return self::$variableConstants;
	}
	public function getChartsButtonHtml(): string{
		return $this->l()->getChartsButtonHtml();
	}
	public function getSettingsButtonHtml(string $fromUrl = null): string{
		return $this->l()->getSettingsButtonHtml();
	}
	public function getEarliestTaggedMeasurementDate(): ?string{
		return $this->l()->getEarliestTaggedMeasurementDate() ?? null;
	}
	public function getLatestTaggedMeasurementDate(): ?string{
		return $this->l()->getLatestTaggedMeasurementDate();
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return GlobalVariableRelationship[]|UserVariableRelationship[]|Collection
	 */
	public function getOutcomesOrPredictors(int $limit = null, string $variableCategoryName = null): ?Collection{
		return $this->l()->getOutcomesOrPredictors($limit, $variableCategoryName) ?? null;
	}
	public function getCorrelationDataRequirementAndCurrentDataQuantityString(): string{
		return $this->l()->getCorrelationDataRequirementAndCurrentDataQuantityString();
	}
	/**
	 * @return string
	 */
	public function getDataQuantityHTML(): string{
		return $this->l()->getDataQuantityHTML();
	}
	public function hasFillingValue(): bool{
		return $this->getVariable()->hasFillingValue();
	}
	public function getFillingValueAttribute(): ?float{
		return $this->fillingValue = $this->getVariable()->getFillingValueAttribute();
	}
	public function getSlug(): string{
		return Variable::toSlug($this->getNameAttribute());
	}
	private function getNumberOfValidDailyMeasurementsWithTags(): int {
		return count($this->getValidDailyMeasurementsWithTags());
	}
}
