<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables;
use App\Buttons\QMButton;
use App\Buttons\States\StudyCreationStateButton;
use App\Buttons\States\StudyStateButton;
use App\Buttons\States\VariableStates\MeasurementAddVariableStateButton;
use App\Buttons\States\VariableStates\PredictorsAllStateButton;
use App\Buttons\States\VariableStates\ReminderAddStateButton;
use App\Buttons\Tracking\NoNotificationButton;
use App\Buttons\Tracking\NotificationButton;
use App\Buttons\Tracking\RatingNotificationButton;
use App\Buttons\Tracking\YesNotificationButton;
use App\Cards\StudyCard;
use App\Cards\TrackingInstructionsQMCard;
use App\Cards\VariableSettingsCard;
use App\Charts\ChartGroup;
use App\Charts\UserVariableCharts\UserVariableChartGroup;
use App\Charts\VariableCharts\VariableChartChartGroup;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\VariableRelationships\QMVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\DataSources\Connectors\QuantiModoConnector;
use App\DataSources\Connectors\RescueTimeConnector;
use App\DataSources\QMDataSource;
use App\DevOps\XDebug;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InsufficientMemoryException;
use App\Exceptions\InvalidStringException;
use App\Exceptions\InvalidTagCategoriesException;
use App\Exceptions\InvalidVariableNameException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NotFoundException;
use App\Exceptions\StupidVariableNameException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\UserVariableNotFoundException;
use App\Exceptions\VariableCategoryNotFoundException;
use App\Logging\QMLog;
use App\Logging\QMLogLevel;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Models\WpPost;
use App\Products\Product;
use App\Products\ProductHelper;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseEarliestTaggedMeasurementStartAtProperty;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Properties\Base\BaseFillingValueProperty;
use App\Properties\Base\BaseLatestTaggedMeasurementStartAtProperty;
use App\Properties\Base\BaseValenceProperty;
use App\Properties\UserVariable\UserVariableNumberOfChangesProperty;
use App\Properties\UserVariable\UserVariableStatusProperty;
use App\Properties\Variable\VariableDescriptionProperty;
use App\Properties\Variable\VariableIdProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Properties\Variable\VariableOptimalValueMessageProperty;
use App\Properties\Variable\VariableOutcomeProperty;
use App\Properties\VariableCategory\VariableCategoryIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Measurement\AnonymousMeasurement;
use App\Slim\Model\Measurement\DailyAnonymousMeasurement;
use App\Slim\Model\Measurement\DailyMeasurement;
use App\Slim\Model\Measurement\FillerMeasurement;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\QMUnit;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\View\Request\Variable\GetCommonVariablesRequest;
use App\Slim\View\Request\Variable\GetUserVariableRequest;
use App\Slim\View\Request\Variable\GetVariableRequest;
use App\Storage\DB\QMQB;
use App\Studies\QMPopulationStudy;
use App\Studies\QMStudy;
use App\Studies\QMUserStudy;
use App\Traits\HasCauseAndEffect;
use App\Traits\HasMany\HasManyMeasurements;
use App\Traits\QMAnalyzableTrait;
use App\Types\ObjectHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UnitCategories\MiscellanyUnitCategory;
use App\UnitCategories\RatingUnitCategory;
use App\Units\OneToFiveRatingUnit;
use App\Units\OneToTenRatingUnit;
use App\Units\YesNoUnit;
use App\Utils\APIHelper;
use App\Utils\AppMode;
use App\Utils\Stats;
use App\Utils\UrlHelper;
use App\Utils\WikiHelper;
use App\VariableCategories\EmotionsVariableCategory;
use App\VariableCategories\FoodsVariableCategory;
use App\VariableCategories\SoftwareVariableCategory;
use App\VariableCategories\SymptomsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Variables\CommonVariables\NutrientsCommonVariables\CaloricIntakeCommonVariable;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\WalkOrRunDistanceCommonVariable;
use App\Variables\CommonVariables\PhysiqueCommonVariables\BodyFatCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\SleepDurationCommonVariable;
use Dialogflow\Action\Surface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Jupitern\Table\Table;
use LogicException;
use stdClass;
/** Class QMVariable
 * @package App\Slim\Model
 */
abstract class QMVariable extends VariableSearchResult {
	use QMAnalyzableTrait, HasManyMeasurements;
	const ANY_CATEGORY = "Any Category";
	private static $commonVariableButtons;
	private static $stupidVariables;
	protected $anonymousMeasurements;
	protected $availableUnits;
	protected $bestGlobalVariableRelationship;
	protected ?int $commonBestCauseVariableId = null;
	protected ?int $commonBestEffectVariableId = null;
	protected $commonDataSources;
	protected $commonDataSourcesCount;
	protected float $commonNumberOfRawMeasurements;
	protected $commonTaggedMeasurements;
	protected $commonTaggedRows;
	protected $commonTagRows;
	protected $correlationsForPredictorCategory;
	protected $dailyMeasurements;
	protected $dailyMeasurementsWithTags;
	protected $dataQuantitySentence;
	protected $deletionReason;
	protected $measurements;
	protected $measurementsWithTags; // Make public to enable caching to Mongo avoiding measurement queries
	protected $numberOfActiveTrackingReminders;
	protected $sortOrder;
	protected $recordSizeInKb;
	protected $unit;
	protected $userVariableRelationshipsAsCause;
	protected $userVariableRelationshipsAsEffect;
	protected $values;
	protected $variableCategory;
	protected $variableSettingsUrl;
	public $actionArray;
	public string $analysisEndedAt;
	public $analysisRequestedAt;
	public ?string $analysisSettingsModifiedAt = null;
	public $analysisStartedAt;
	public ?string $newestDataAt = null;
	public string $reasonForAnalysis;
	public $userErrorMessage;
	public $availableUnitNames;
	public ?float $averageSecondsBetweenMeasurements = null;
	public ?int $bestGlobalVariableRelationshipId = null;
	public $bestCauseVariableId;
	public $bestEffectVariableId;
	public $bestPopulationStudy;
	public $bestPopulationStudyCard;
	public $bestPopulationStudyLink;
	public $bestStudyCard;
	public $bestStudyLink;
	public $buttons;
	public $card;
	public $charts;
	public $childCommonTagVariables;
	public $combinationOperation;
	public $commonAlias;
	public ?int $commonMostCommonConnectorId = null;
	public $commonTaggedVariables;
	public $commonTagVariables;
	public $commonUnitId;
	public $connectorAndQMDataSourceNames;
	public $createdAt;
	public $dataSources;
	public $dataSourcesCount; // Provided source name, client id, connector name, or QMDataSource name should be in measurements.source_name column
	public $description;
	public $durationOfAction;
	public $durationOfActionInHours;
	public $earliestNonTaggedMeasurementStartAt;
	public $earliestNonTaggedMeasurementTime;
	public $earliestTaggedMeasurementAt;
	public $earliestTaggedMeasurementStartAt;
	public $earliestTaggedMeasurementTime;
	public $fillingType;
	public ?float $fillingValue = null;
	public $informationalUrl;
	public $ingredientCommonTagVariables;
	public $ingredientOfCommonTagVariables;
	public $joinedCommonTagVariableIds;
	public $joinedCommonTagVariables;
	public $kurtosis;
	public $latestMeasurementTime; // TODO: Delete me once clients stop using me
	public $latestNonTaggedMeasurementStartAt;
	public $latestNonTaggedMeasurementTime;
	public $listCard;
	public $longQuestion;
	public ?float $maximumAllowedDailyValue = null;
	public ?float $secondMostCommonValue = null;
	public ?float $thirdMostCommonValue = null;
	public $maximumAllowedValue;
	public $maximumRecordedValue;
	public $mean;
	public $median;
	public $medianSecondsBetweenMeasurements;
	public $mergeOverlappingMeasurements = false;
	public $minimumAllowedSecondsBetweenMeasurements;
	public $minimumAllowedValue;
	public $minimumRecordedValue;
	public $mostCommonConnector;
	public $mostCommonConnectorId;
	public $mostCommonSourceName;
	public $mostCommonValue;
	public $numberOfChanges;
	public $numberOfCorrelations;
	public $numberOfMeasurements;
	public $numberOfRawMeasurements;
	public $numberOfRawMeasurementsWithTagsJoinsChildren;
	public $numberOfUniqueDailyValues;
	public $onsetDelay;
	public $onsetDelayInHours;
	public $optimalValueMessage;
	public $parentCommonTagVariables;
	public $parentId;
	public float $price;
	public $question;
	public $skewness;
	public $standardDeviation;
	public string $status;
	public $strongestGlobalVariableRelationshipAsCause;
	public $strongestGlobalVariableRelationshipAsEffect;
	public $tagConversionFactor;
	public $tagDisplayText;
	public $taggedVariableId;
	public $tagVariableId;
	public $trackingInstructions;
	public $trackingInstructionsCard;
	public $unitId;
	public $unitName;
	public $upc;
	public $updatedAt;
	public $valence;
	public $variableCategoryId;
	public $variableName;
	public $variableSettingsCard;
	public $variance;
	public $wikipediaExtract;
	public $wikipediaTitle;
	public ?string $wikipediaUrl;
	public const    CLASS_PARENT_CATEGORY                                     = Variable::CLASS_CATEGORY;
	public const    doNotCreateNewVariableWithNameInUnit                      = 'doNotCreateNewVariableWithNameInUnit';
	public const    FIELD_ANALYSIS_ENDED_AT                                   = 'analysis_ended_at';
	public const    FIELD_ANALYSIS_REQUESTED_AT                               = 'analysis_requested_at';
	public const    FIELD_ANALYSIS_SETTINGS_MODIFIED_AT                       = 'analysis_settings_modified_at';
	public const    FIELD_ANALYSIS_STARTED_AT                                 = 'analysis_started_at';
	public const    FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS                = 'average_seconds_between_measurements';
	public const    FIELD_BEST_CAUSE_VARIABLE_ID                              = 'best_cause_variable_id';
	public const    FIELD_BEST_EFFECT_VARIABLE_ID                             = 'best_effect_variable_id';
	public const    FIELD_CAUSE_ONLY                                          = 'cause_only';
	public const    FIELD_CHARTS                                              = 'charts';
	public const    FIELD_CLIENT_ID                                           = 'client_id';
	public const    FIELD_COMBINATION_OPERATION                               = 'combination_operation';
	public const    FIELD_CREATED_AT                                          = 'created_at';
	public const    FIELD_DATA_SOURCES_COUNT                                  = 'data_sources_count';
	public const    FIELD_DEFAULT_UNIT_ID                                     = 'default_unit_id';
	public const    FIELD_DELETED_AT                                          = 'deleted_at';
	public const    FIELD_DESCRIPTION                                         = 'description';
	public const    FIELD_DURATION_OF_ACTION                                  = 'duration_of_action';
	public const    FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT            = 'earliest_non_tagged_measurement_start_at';
	public const    FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT                = 'earliest_tagged_measurement_start_at';
	public const    FIELD_FILLING_VALUE                                       = 'filling_value';
	public const    FIELD_INFORMATIONAL_URL                                   = 'informational_url';
	public const    FIELD_INTERNAL_ERROR_MESSAGE                              = 'internal_error_message';
	public const    FIELD_KURTOSIS                                            = 'kurtosis';
	public const    FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT              = 'latest_non_tagged_measurement_start_at';
	public const    FIELD_LATEST_TAGGED_MEASUREMENT_START_AT                  = 'latest_tagged_measurement_start_at';
	public const    FIELD_MAXIMUM_ALLOWED_VALUE                               = 'maximum_allowed_value';
	public const    FIELD_MAXIMUM_RECORDED_VALUE                              = 'maximum_recorded_value';
	public const    FIELD_MEAN                                                = 'mean';
	public const    FIELD_MEDIAN                                              = 'median';
	public const    FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS                 = 'median_seconds_between_measurements';
	public const    FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS        = 'minimum_allowed_seconds_between_measurements';
	public const    FIELD_MINIMUM_ALLOWED_VALUE                               = 'minimum_allowed_value';
	public const    FIELD_MOST_COMMON_CONNECTOR_ID                            = 'most_common_connector_id';
	public const    FIELD_MOST_COMMON_ORIGINAL_UNIT_ID                        = 'most_common_original_unit_id';
	public const    FIELD_MOST_COMMON_SOURCE_NAME                             = 'most_common_source_name';
	public const    FIELD_MOST_COMMON_VALUE                                   = 'most_common_value';
	public const    FIELD_NEWEST_DATA_AT                                      = 'newest_data_at';
	public const    FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN = 'number_of_raw_measurements_with_tags_joins_children';
	public const    FIELD_NUMBER_OF_SOFT_DELETED_MEASUREMENTS                 = 'number_of_soft_deleted_measurements';
	public const    FIELD_NUMBER_OF_TRACKING_REMINDERS                        = 'number_of_tracking_reminders';
	public const    FIELD_NUMBER_OF_UNIQUE_VALUES                             = 'number_of_unique_values';
	public const    FIELD_ONSET_DELAY                                         = 'onset_delay';
	public const    FIELD_OPTIMAL_VALUE_MESSAGE                               = 'optimal_value_message';
	public const    FIELD_PARENT_ID                                           = 'parent_id';
	public const    FIELD_IS_PUBLIC                                           = 'is_public';
	public const    FIELD_REASON_FOR_ANALYSIS                                 = 'reason_for_analysis';
	public const    FIELD_SKEWNESS                                            = 'skewness';
	public const    FIELD_STANDARD_DEVIATION                                  = 'standard_deviation';
	public const    FIELD_STATUS                                              = 'status';
	public const    FIELD_UPDATED_AT                                          = 'updated_at';
	public const    FIELD_USER_ERROR_MESSAGE                                  = 'user_error_message';
	public const    FIELD_VALENCE                                             = 'valence';
	public const    FIELD_VARIABLE_CATEGORY_ID                                = 'variable_category_id';
	public const    FIELD_VARIABLE_ID                                         = null;
	public const    FIELD_VARIANCE                                            = 'variance';
	public const    FIELD_WIKIPEDIA_TITLE                                     = 'wikipedia_title';
	public const    FIELD_WP_POST_ID                                          = 'wp_post_id';
	protected const ONE_TO_FIVE_RATING_VALUES                                 = [
		1,
		3,
		5,
		4,
		2,
	];
	protected const ONE_TO_TEN_RATING_VALUES                                  = [
		1,
		5,
		10,
		2,
		3,
		4,
		6,
		7,
		8,
		9,
	];
	public const    DEFAULT_LIMIT                                             = Variable::DEFAULT_LIMIT;
	protected static $variableConstants;
	protected static $variableCategoryConstants = [
		SymptomsVariableCategory::NAME => [
			Variable::FIELD_DURATION_OF_ACTION => 86400,
			Variable::FIELD_ONSET_DELAY => 0,
		],
		EmotionsVariableCategory::NAME => [
			Variable::FIELD_DURATION_OF_ACTION => 86400,
			Variable::FIELD_ONSET_DELAY => 0,
		],
		FoodsVariableCategory::NAME => [
			Variable::FIELD_DURATION_OF_ACTION => 86400 * 14,
			Variable::FIELD_ONSET_DELAY => 30 * 60,
		],
		TreatmentsVariableCategory::NAME => [
			Variable::FIELD_DURATION_OF_ACTION => 86400 * 21,
			Variable::FIELD_ONSET_DELAY => 30 * 60,
		],
	];
	// After updating run CommonVariableCleanupJobTest::testDeleteStupidBoringVariables
	// Keep private because getStupidVariableNames merges connector stupid variables
	// After updating run CommonVariableCleanupJobTest::testDeleteStupidBoringVariables
	/**
	 * @var array|mixed|null
	 */
	protected $valuesWithTags;
	/**
	 * @var array|mixed|null
	 */
	protected $movingAverages;
	/**
	 * @var array|mixed|null
	 */
	protected $dailyValues;
	protected $boring;
	protected $controllable;
	protected $isGoal;
	/** @noinspection MagicMethodsValidityInspection */
	/** @noinspection PhpMissingParentConstructorInspection */
	/**
	 * QMVariable constructor.
	 * @param QMVariable|array $row
	 */
	public function __construct($row = null){
		if($row){
			$this->setDbRow($row);
			if(is_array($row)){
				$row = json_decode(json_encode($row), false);
			}
			foreach($this as $key => $value){
				if($key === "name" && isset($this->name)){
					continue;
				}
				if(property_exists($row, $key) && isset($row->$key)){
					$this->$key = $row->$key;
				}
			}
			if(isset($row->unit)){
				$this->setUserUnit(new QMUnit($row->unit));
			}
		}
		try {
			$this->populateDefaultFields();
		} catch (\Throwable $e) {
			$this->populateDefaultFields();
		}
	}
	public function populateDefaultFields(){
		parent::populateDefaultFields();
		if(AppMode::isApiRequest()){
			$this->getAvailableUnits();
		}
		$this->setVariableCategoryByNameOrId();
		$this->getOrSetCombinationOperation();
		$this->getFillingTypeAttribute();
		$this->getDataSourcesCount();
		if(AppMode::isApiRequest() &&
			QMStudy::weShouldGenerateFullStudyWithChartsCssAndInstructions()){ // TODO: Why do we need this all the time?
			$this->getTrackingInstructionCard(); // Don't need this internally and it's slow
		}
		$this->numberOfCorrelations =
			$this->getNumberOfCorrelationsAsCause() + $this->getNumberOfCorrelationsAsEffect();
		$this->setBestStudyLink();
		$this->getOnsetDelay();
		$this->getDurationOfAction();
		if(!$this->numberOfRawMeasurements){
			$this->setNumberOfMeasurements($this->numberOfMeasurements);
		}
		$this->isPredictor();
		$this->isOutcome();
		if(!$this->unitName){
			$this->unitName = $this->getQMUnit()->getNameAttribute();
		}
	}
	/**
	 * @return QMVariableCategory
	 * @throws VariableCategoryNotFoundException
	 */
	private function setVariableCategoryByNameOrId(): QMVariableCategory{
		if(isset($this->variableCategoryName)){
			$this->variableCategory = QMVariableCategory::findByNameOrSynonym($this->variableCategoryName);
		} elseif(isset($this->variableCategoryId)){
			$this->variableCategory = QMVariableCategory::find($this->variableCategoryId);
		}
		return $this->variableCategory;
	}
	/**
	 * @param string $upc
	 * @return QMCommonVariable[]|QMUserVariable[]
	 */
	public static function getCommonOrUserVariablesFromUpc(string $upc = '028400064057'): ?array{
		$variablesFromDb = self::getVariablesFromDbByUpc($upc);
		if($variablesFromDb){
			return $variablesFromDb;
		}
		$product = ProductHelper::getByUpc($upc);
		if($product){
			if(QMAuth::getQMUserIfSet()){
				$variables[] = $product->getUserVariable(QMAuth::id());
				$variables[] = $product->getUserVariable(QMAuth::id());
			} else{
				$variables[] = $product->getQMCommonVariableWithActualProductName();
				$variables[] = $product->getCommonPaymentVariable();
			}
			return $variables;
		}
		return null;
	}
	/**
	 * @param string $upc
	 * @return QMCommonVariable[]|QMUserVariable[]
	 */
	public static function getVariablesFromDbByUpc(string $upc): ?array{
		$nonPaymentFound = false;
		$rows = QMCommonVariable::readonly()->where(Variable::FIELD_UPC_14, $upc)->getArray();
		if(!empty($rows)){
			$variables = [];
			foreach($rows as $row){
				if(QMAuth::getQMUserIfSet()){
					$variable = QMUserVariable::findOrCreateByNameOrId(QMAuth::id(), $row->id);
				} else{
					$variable = QMCommonVariable::find($row->id);
				}
				if(!$variable->isPaymentVariable()){
					$nonPaymentFound = true;
				} else{
					$paymentVariable = $variable;
				}
				$variables[] = $variable;
			}
			if(!$nonPaymentFound && isset($paymentVariable)){
				$nonPaymentVariable = $paymentVariable->getNonPaymentVariable();
				$variables[] = $nonPaymentVariable;
			}
			return $variables;
		}
		return null;
	}
	/**
	 * @param Builder|\Illuminate\Database\Eloquent\Builder $qb
	 * @param array $additionalCategoryIds
	 */
	public static function excludeAppsPaymentsWebsitesTestVariablesAndLocations($qb,
		array $additionalCategoryIds = []){
		$ids = array_values(array_unique(array_merge($additionalCategoryIds,
			VariableCategory::getAppsLocationsWebsiteIds())));
		$qb->whereNotIn(Variable::TABLE . '.' . Variable::FIELD_VARIABLE_CATEGORY_ID, $ids);
        QMQB::notLike($qb, Variable::TABLE . '.' . Variable::FIELD_NAME, '%test%');
	}
	/**
	 * @param bool $noCache
	 * @return QMCommonVariable[]
	 */
	public static function getStupidVariables(bool $noCache = false): array{
		if(!$noCache && self::$stupidVariables !== null){
			return self::$stupidVariables;
		}
		$variables = [];
		foreach(VariableNameProperty::getStupid() as $name){
			$variable = QMCommonVariable::find($name);
			if($variable){
				$variables[$name] = $variable;
			}
		}
		$stupidFragments = VariableNameProperty::STUPID_VARIABLE_NAMES_LIKE;
		foreach($stupidFragments as $fragment){
			$variablesLike = GetCommonVariablesRequest::getWithNameContainingExactString($fragment);
			if(!$variablesLike){
				continue;
			}
			foreach($variablesLike as $variable){
				$variables[$variable->name] = $variable;
			}
		}
		return self::$stupidVariables = $variables;
	}
	/**
	 * @return QMVariable|null
	 * @throws UserVariableNotFoundException
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function fromRequest(){
		$nameOrId = VariableIdProperty::nameOrIdFromRequest();
		if(!$nameOrId){
			return null;
		}
		if($user = QMAuth::getQMUser()){
			return QMUserVariable::getByNameOrId($user->id, $nameOrId);
		}
		return QMCommonVariable::findByNameOrId($nameOrId);
	}
	/**
	 * @param string $userMessage
	 * @throws StupidVariableNameException
	 */
	public function exceptionIfStupidVariable(string $userMessage){
		if($this->isStupidVariable()){
			throw new StupidVariableNameException($this->name, $userMessage, $this);
		}
	}
	/**
	 * @return bool
	 */
	public function isStupidVariable(): bool{
		return VariableNameProperty::isStupid($this->getVariableName());
	}
	/**
	 * @param QMCommonVariable[]|QMUserVariable[] $variables
	 * @param GetVariableRequest $variableRequest
	 * @return QMCommonVariable[]|QMUserVariable[]
	 */
	protected static function filterByUnitCategoryName(array $variables, GetVariableRequest $variableRequest): array{
		if(!$variableRequest->getUnitCategoryName()){
			return $variables;
		}
		$filteredVariables = [];
		$notEqualTo = (strpos($variableRequest->getUnitCategoryName(), '(ne)') !== false) ? str_replace('(ne)', '',
			$variableRequest->getUnitCategoryName()) : null;
		if($notEqualTo){
			foreach($variables as $userVariable){
				if($userVariable->getCommonUnit()->categoryName !== $notEqualTo){
					$filteredVariables[] = $userVariable;
				}
			}
			return $variables;
		}
		foreach($variables as $userVariable){
			if($variableRequest->getUnitCategoryName() === $userVariable->getCommonUnit()->categoryName){
				$filteredVariables[] = $userVariable;
			}
		}
		return $filteredVariables;
	}
	/**
	 * @param string $searchPhrase
	 * @param string $variableName
	 * @return bool
	 */
	protected static function isExactMatch(string $searchPhrase, string $variableName): bool{
		return strtolower(str_replace('%', '', $searchPhrase)) === strtolower($variableName);
	}
	/**
	 * @param QMCommonVariable[]|QMUserVariable[]|QMVariable[] $variableObjects
	 * @param string $searchPhrase
	 * @return QMVariable[]|QMCommonVariable[]|QMUserVariable[]
	 */
	public static function putExactMatchFirst(array $variableObjects, string $searchPhrase): array{
		if(!isset($variableObjects[0]->name)){
			return $variableObjects;
		}
		if(self::isExactMatch($searchPhrase, $variableObjects[0]->name)){
			return $variableObjects;
		}
		$exactMatchVariableObjects = [];
		$notExactMatchVariableObjects = [];
		foreach($variableObjects as $variableObject){
			if(self::isExactMatch($searchPhrase, $variableObject->name)){
				$exactMatchVariableObjects[] = $variableObject;
			} else{
				$notExactMatchVariableObjects[] = $variableObject;
			}
		}
		$variableObjects = array_merge($exactMatchVariableObjects, $notExactMatchVariableObjects);
		return $variableObjects;
	}
	/**
	 * @param array $variables
	 * @param string $searchPhrase
	 * @return QMVariable
	 */
	public static function getExactMatchFromArray(array $variables, string $searchPhrase): ?QMVariable{
		foreach($variables as $v){
			if(self::isExactMatch($searchPhrase, $v->name)){
				return $v;
			}
		}
		return null;
	}
	/**
	 * @param array|GetUserVariableRequest $variableRequest
	 * @return array
	 */
	protected static function convertVariableRequestToRequestParamsIfNecessary($variableRequest){
		if(!is_array($variableRequest)){
			$requestParams = json_decode(json_encode($variableRequest), true);
		} else{
			$requestParams = $variableRequest;
		}
		return $requestParams;
	}
	/**
	 * @param int $id
	 * @return int
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function setId($id){
		return $this->id = $id;
	}
	public function setFillingTypeAttribute(string $type): void {
		$this->l()->setFillingTypeAttribute($type);
		$this->fillingType = $type;
	}
	/**
	 * @return QMUnit
	 */
	public function getCommonUnit(): QMUnit{
		if($this->commonUnit ?? false){
			return $this->commonUnit;
		}
		$id = $this->getCommonUnitId();
		if(!$id){
			$id = $this->getCommonUnitId();
		}
		return $this->commonUnit = QMUnit::getByNameOrId($id);
	}
	/**
	 * @return int
	 */
	abstract public function getDurationOfAction(): int;
	/**
	 * @return int
	 */
	abstract public function getOnsetDelay(): int;
	/**
	 * @return QMDataSource[]
	 */
	public function getDataSources(): array{
		return $this->dataSources = $this->getVariable()->getDataSources();
	}
	/**
	 * @return string[]
	 */
	public function getDataSourceDisplayNames(): array{
		return array_keys($this->getDataSourcesCount());
	}
	/**
	 * @return QMDataSource[]
	 */
	public function getCommonDataSources(): array{
		if($this->commonDataSources !== null){
			return $this->commonDataSources;
		}
		if(!$this->commonDataSourcesCount){
			$this->getDataSources();
		}
		return $this->setCommonDataSources();
	}
	/**
	 * @return QMDataSource[]
	 */
	public function setDataSources(): array{
		return $this->dataSources = $this->getVariable()->getDataSources();
	}
	/**
	 * @return QMDataSource[]
	 */
	public function setCommonDataSources(): array{
		return $this->commonDataSources = $this->getVariable()->getDataSources();
	}
	/**
	 * @return string[]
	 */
	public function getDataSourceNames(): array{
		$dataSourcesCount = $this->getDataSourcesCount();
		$dataSourceNames = array_keys($dataSourcesCount);
		return $dataSourceNames;
	}
	/**
	 * @return array
	 */
	public function getDataSourcesCount(): array{
		$dsc = $this->dataSourcesCount;
		if(is_object($dsc) || is_string($dsc)){
			return $this->dataSourcesCount = QMArr::toArray($dsc);
		}
		$val = $this->dataSourcesCount ?? $this->l()->data_sources_count;
		if($val !== null){
			$val = QMStr::jsonDecodeIfNecessary($val, true);
			return QMArr::toArray($val);
		}
		/** @var Variable $l */
		$l = $this->l();
		return $this->dataSourcesCount = $l->data_sources_count;
	}
	/**
	 * @return array
	 */
	public function getCommonDataSourcesCount(): array{
		return $this->commonDataSourcesCount = $this->getVariable()->data_sources_count;
	}
	/**
	 * @param DailyMeasurement[] $daily
	 */
	public function setDailyMeasurementsWithTags(?array $daily): void{
		if($daily){
			$this->validateDailyMeasurements($daily);
		}
		$this->dailyMeasurementsWithTags = $daily;
	}
	/**
	 * @param string|int $timeAt
	 */
	public function setLatestMeasurementTime($timeAt): void{
		$this->latestMeasurementTime = time_or_null($timeAt);
	}
	/**
	 * @param string|int $timeAt
	 */
	public function setEarliestMeasurementTime($timeAt): void{
		$this->earliestMeasurementTime = time_or_null($timeAt);
	}
	/**
	 * @param string $unitName
	 */
	public function setUnitName(string $unitName): void{
		$this->unitName = $unitName;
	}
	/**
	 * @param QMUnit|int|string $unit
	 * @return QMUnit
	 */
	public function setUserUnit($unit){
		if(is_int($unit) || is_string($unit)){
			$unit = QMUnit::find($unit);
		}
		$this->unit = $unit;
		if(!$unit->name){
			le("No unit!", $unit);
		}
		$this->setUnitName($unit->name);
		$this->setUnitAbbreviatedName($unit->abbreviatedName);
		$this->unitId = $unit->id;
		return $unit;
	}
	/**
	 * @param int|string|QMVariableCategory $cat
	 */
	public function setVariableCategory($cat): void{
		$cat = QMVariableCategory::find($cat);
		$this->variableCategoryId = $cat->id;
		$this->variableCategoryName = $cat->name;
		$this->variableCategory = $cat;
	}
	/**
	 * @return string
	 */
	public function getInputType(): string{
		if(!$this->isRating()){
			$num = $this->getNumberOfUniqueValues();
			if($num > 10){
				$this->inputType = QMUnit::INPUT_TYPE_value;
			}
		}
		return $this->inputType;
	}
	/**
	 * @return float
	 */
	public function getSecondMostCommonValue(): ?float{
		return $this->secondMostCommonValue;
	}
	/**
	 * @return float
	 */
	public function getThirdMostCommonValue(): ?float{
		return $this->thirdMostCommonValue;
	}
	/**
	 * @return string
	 */
	public function getLatestNonTaggedMeasurementStartAt(): ?string{
		$at = $this->l()->latest_non_tagged_measurement_start_at;
		if($this->measurements){
			$lastMeasurement = $this->getLastRawNonTaggedMeasurement();
			if($lastMeasurement && $at !== $lastMeasurement->getStartAt()){
				$at = $lastMeasurement->getStartAt();
			}
		}
		$this->setAttribute(self::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT, $at);
		return $at;
	}
	/**
	 * @return bool
	 */
	public function highchartsPopulated(): bool{
		$chartGroup = $this->getChartGroup();
		return $chartGroup->highchartsPopulated();
	}
	/**
	 * @param string $type
	 * @param bool $embedImages
	 * @param string|null $fromUrl
	 * @return string
	 * @throws \App\Exceptions\DuplicateFailedAnalysisException
	 * @throws \App\Exceptions\HighchartExportException
	 * @throws \App\Exceptions\ModelValidationException
	 * @throws \App\Exceptions\NotEnoughDataException
	 * @throws \App\Exceptions\StupidVariableNameException
	 * @throws \App\Exceptions\TooSlowToAnalyzeException
	 */
	public function getChartsPostAndSettingsButton(string $type, bool $embedImages = false,
		string $fromUrl = null): string{
		$html = '';
		if($embedImages){
			$this->generateAndSaveChartsIfNecessary();
			$causeCharts = $this->getChartGroup();
			$html .= $causeCharts->getChartHtmlWithEmbeddedImageOrReasonForFailure($type);
		} else{
			//$post = $this->getOrCreateWpPost();
			//$html .= $post->getRoundedImageButtonHtml($this->getVariableDisplayName()." Charts");
			$html .= $this->getChartsButtonHtml();
		}
		$html .= $this->getSettingsButtonHtml($fromUrl);
		return $html;
	}
	abstract public function getChartsButtonHtml(): string;
	abstract public function getSettingsButtonHtml(string $fromUrl = null): string;
	/**
	 * @return Variable|UserVariable
	 */
	public function firstOrNewLaravelModel(){
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return parent::firstOrNewLaravelModel();
	}
	/**
	 * @return string
	 */
	abstract public function getEarliestNonTaggedMeasurementStartAt(): ?string;
	abstract public function getEarliestTaggedMeasurementDate(): ?string;
	abstract public function getLatestTaggedMeasurementDate(): ?string;
	/**
	 * @param float|null $minimumAllowedValue
	 * @return float|null
	 */
	public function setMinimumAllowedValue(?float $minimumAllowedValue): ?float{
		return $this->minimumAllowedValue = $minimumAllowedValue;
	}
	/**
	 * @param float|null $fillingValue
	 * @return float|null
	 */
	public function setFillingValue(?float $fillingValue): ?float{
		if($fillingValue == -1){
			le('$fillingValue == -1');
		}
		//$this->validateAttribute(self::FIELD_FILLING_VALUE, $fillingValue);
		return $this->fillingValue = $fillingValue;
	}
	/**
	 * @return array
	 */
	public static function getCalculatedRawValueFields(): array{
		$valueFields = [];
		$allFields = static::getColumns();
		foreach($allFields as $field){
			if(strpos($field, '_value') && strpos($field, 'values') === false && strpos($field, 'message') === false &&
				strpos($field, 'original') === false && strpos($field, 'filling') === false &&
				strpos($field, 'processed') === false && strpos($field, 'allowed') === false){
				$valueFields[] = $field;
			}
		}
		return $valueFields;
	}
	/**
	 * @return bool
	 */
	public function isOutcome(): bool{
		return $this->outcome = VariableOutcomeProperty::isOutcome($this);
	}
	/**
	 * @return bool
	 */
	public function isPredictor(): bool{
		return $this->predictor  = $this->getVariable()->isPredictor();
	}
	/**
	 * @return bool
	 */
	public function getIsPublic(): ?bool{
		return $this->public;
	}
	/**
	 * @return int
	 */
	public function getNumberOfUniqueValues(): ?int{
		$num = $this->getAttribute(self::FIELD_NUMBER_OF_UNIQUE_VALUES);
		if(!$num){
			if($new = $this->getCombinedNewQMMeasurements()){
				$num = collect($new)->unique('value')->count();
			}
		}
		return $this->numberOfUniqueValues = $num;
	}
	/**
	 * @return bool
	 */
	public function isOneToFiveRating(): bool{
		return $this->getUserOrCommonUnit()->abbreviatedName === OneToFiveRatingUnit::ABBREVIATED_NAME;
	}
	/**
	 * @return bool
	 */
	public function isYesNo(): bool{
		return $this->getUserOrCommonUnit()->name == YesNoUnit::NAME;
	}
	public function logTimeSinceLatestMeasurement(){
		$this->logInfo("Last measurement was " .
			TimeHelper::timeSinceHumanString($this->getLatestTaggedMeasurementAt()));
	}
	/**
	 * @param int|string $newUnitNameOrId
	 * @return bool
	 */
	public function unitIsIncompatible($newUnitNameOrId): bool{
		$currentUnit = $this->getUserOrCommonUnit();
		return QMUnit::unitIsIncompatible($currentUnit->id, $newUnitNameOrId);
	}
	/**
	 * @param string $newName
	 * @param string $reason
	 * @throws InvalidVariableNameException
	 */
	public function rename(string $newName, string $reason): void{
		$this->logError("Renaming " . $this->getVariableName() . " to $newName");
		$existing = QMCommonVariable::readonly()->where(Variable::FIELD_NAME, $newName)->first();
		if($existing){
			VariableIdProperty::replaceEverywhere($existing->id, $this->getVariableIdAttribute(), $reason);
			$this->softDelete([], $reason);
			return;
		}
		$this->getUserOrCommonUnit()->validateVariableNameForUnit($newName);
		$this->addSynonym($this->getVariableName());
		$this->setName($newName);
		try {
			$this->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
	}

	/**
	 * @return string
	 */
	public function getOrSetCombinationOperation(): string{
		$op = $this->combinationOperation;
		if(!$op){
			$op = $this->getCombinationOperation();
		}
		return $this->combinationOperation = $op;
	}
	public function isMeanCombinationOperation(): bool{
		return strtolower($this->getOrSetCombinationOperation()) ===
			strtolower(BaseCombinationOperationProperty::COMBINATION_MEAN);
	}
	/**
	 * @return int
	 */
	public function getMostCommonConnectorId(): ?int{
		if($this->mostCommonConnectorId === QuantiModoConnector::ID){
			$this->mostCommonConnectorId = null;
		}
		if($this->mostCommonConnectorId === null && !empty($this->mostCommonSourceName)){
			$src = QMDataSource::getDataSourceByNameOrIdOrSynonym($this->mostCommonSourceName);
			if($src){
				$this->mostCommonConnectorId = $src->id;
			}
		}
		return $this->mostCommonConnectorId;
	}
	/**
	 * @param int $mostCommonConnectorId
	 * @return int
	 */
	public function setMostCommonConnectorId(int $mostCommonConnectorId): int{
		return $this->mostCommonConnectorId = $mostCommonConnectorId;
	}
	/**
	 * @return QMDataSource
	 */
	public function getMostCommonConnector(): ?QMDataSource{
		$c = $this->mostCommonConnector;
		if($c === null){
			$c = $this->setMostCommonConnector();
		}
		if(!$c){
			return null;
		}
		return $c;
	}
	/**
	 * @return QMDataSource
	 */
	public function setMostCommonConnector(): ?QMDataSource{
		$c = $this->getVariable()->getMostCommonConnector();
		if(!$c){
			$this->mostCommonConnector = false;
			return null;
		}
		return $this->mostCommonConnector = $c;
	}
	/**
	 * @return int
	 */
	public function getNumberOfMeasurements(): ?int{
		$raw = $this->measurements;
		if($raw){
			$this->numberOfMeasurements = count($raw);
		}
		return $this->numberOfRawMeasurements = $this->numberOfMeasurements;
	}
	/**
	 * @param bool $includeDeleted
	 * @return int
	 */
	abstract public function calculateNumberOfMeasurements(bool $includeDeleted = false): int;
	/**
	 * @return int
	 */
	public function getOrCalculateNumberOfMeasurements(): int{
		$raw = $this->getNumberOfMeasurements();
		if($raw === null){
			$this->numberOfMeasurements = $this->calculateNumberOfMeasurements();
		}
		return $this->numberOfRawMeasurements = $this->numberOfMeasurements;
	}
	/**
	 * @return int
	 */
	public function getOrCalculateNumberOfTrackingReminders(): int{
		if($this->numberOfTrackingReminders !== null){
			return $this->numberOfTrackingReminders;
		}
		return $this->numberOfTrackingReminders = $this->calculateNumberOfTrackingReminders();
	}
	/**
	 * @return int
	 */
	public function calculateNumberOfTrackingReminders(): int{
		return $this->l()->tracking_reminders()->count();
	}
	/**
	 * @return Variable|UserVariable
	 */
	public function l(){
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return parent::l();
	}
	/**
	 * @return \Illuminate\Database\Query\Builder
	 */
	abstract public function trackingReminderQb(): Builder;
	/**
	 * @return string
	 */
	public function getUpc(): ?string{
		return $this->upc;
	}
	/**
	 * @return bool
	 */
	public function isSum(): bool{
		$op = $this->getOrSetCombinationOperation();
		$sum = $op === BaseCombinationOperationProperty::COMBINATION_SUM;
		return $sum;
	}
	/**
	 * @param float|null $max
	 * @return float|null
	 */
	public function setMaximumAllowedValue(?float $max): ?float{
		return $this->maximumAllowedValue = $max;
	}
	/**
	 * @param array $valuesInADay
	 * @return float
	 * @throws \App\Exceptions\InvalidVariableValueException
	 */
	public function aggregateDailyValues(array $valuesInADay): float{
		if($this->isSum()){
			$val = Stats::sum($valuesInADay);
		} else{
			$val = Stats::average($valuesInADay);
		}
		$this->validateDailyValue($val, __FUNCTION__);
		return $val;
	}
	/**
	 * @param int $onsetDelay
	 * @return int
	 */
	public function setOnsetDelay(int $onsetDelay): int{
		$this->onsetDelayInHours = $onsetDelay / 3600;
		return $this->onsetDelay = $onsetDelay;
	}
	/**
	 * @param int $durationOfAction
	 * @return int
	 */
	public function setDurationOfAction(int $durationOfAction): int{
		$this->durationOfActionInHours = $durationOfAction / 3600;
		return $this->durationOfAction = $durationOfAction;
	}
	public function years(): float{
		if(!$this->earliestTaggedMeasurementAt){
			return 0;
		}
		if(!$this->latestTaggedMeasurementStartAt){
			return 0;
		}
		return $this->getNumberOfDaysBetweenEarliestAndLatestTaggedMeasurement() / 365;
	}
	/**
	 * @return float
	 */
	public function getMean(): ?float{
		if($values = $this->values){
			return $this->mean = Stats::average($values);
		}
		return $this->mean;
	}
	/**
	 * @return float
	 */
	public function getMedian(): ?float{
		return $this->median;
	}
	/**
	 * @return int
	 */
	public function getNumberOfChanges(): ?int{
		if($this->measurements){
			$this->numberOfChanges = $this->calculateNumberOfChanges();
		}   
		return $this->numberOfChanges;
	}
	/**
	 * @return mixed
	 */
	public function getStandardDeviation(): ?float{
		return $this->standardDeviation;
	}
	/**
	 * @return string
	 */
	public function getStatus(): ?string{
		return $this->status ?? null;
	}
	/**
	 * @return float
	 */
	public function getVariance(): ?float{
		return $this->variance;
	}
	/**
	 * @return float
	 */
	public function getKurtosis(): ?float{
		return $this->kurtosis;
	}
	/**
	 * @param string $newUrl
	 * @return string
	 * @throws \App\Exceptions\InvalidUrlException
	 */
	public function setProductUrl(string $newUrl): string{
		$currentUrl = $this->getProductUrl();
		if($currentUrl === $newUrl){
			$this->logInfo("productUrl already set to $this->productUrl so not setting to provided productUrl $newUrl");
			return $this->productUrl;
		}
		if($newUrl !== false){ // Avoid making redundant requests to Amazon
			$newUrl = QMStr::validateUrlAndAddHttpsIfNecessary($newUrl, true);
		}
		$this->updateCommonVariableProperty(Variable::FIELD_PRODUCT_URL, $newUrl, __METHOD__);
		return $this->productUrl;
	}
	/**
	 * @param string $price
	 * @return int
	 */
	public function setPrice(string $price): int{
		if(!empty($this->price)){
			QMLog::debug("price for $this->name already set to $this->price so not setting to provided price $price");
			return $this->price;
		}
		$result = $this->updateCommonVariableProperty(Variable::FIELD_PRICE, $price, __METHOD__);
		return $result;
	}
	/**
	 * @param string|[] $upc
	 * @return int
	 */
	public function updateUpcIfNecessary($upc){
		if(is_object($upc)){
			$upc = json_decode(json_encode($upc), true);
		}
		if($upc && is_float($upc) && strpos((string)$upc, '.') !== false){
			$upc = QMStr::before(".", (string)$upc);  // Needed for USDA spreadsheet that has trailing .0
		}
		if(is_array($upc) && isset($upc['upc'])){
			$upc = $upc['upc'];
		}
		if(is_string($upc) || $upc === false){
			return $this->setUpc($upc);
		}
		return false;
	}
	/**
	 * @param string|false $upc
	 * @return int
	 */
	public function setUpc(string $upc){
		$existingUpc = $this->getUpc();
		if(!empty($existingUpc) && $existingUpc === $upc){
			$this->logInfo("UPC for $this->name already set to $existingUpc so not setting to provided upc $upc");
			return $existingUpc;
		}
		if($upc !== "0" && $upc !== false && strlen($upc) < 5){
			$this->logError("UPC should not be less than 5 characters but is: $upc!  Setting to false so we do not check again");
			$upc = false;
		}
		if(!empty($existingUpc) && $upc && $upc !== $existingUpc){
			le("Why are we changing $this UPC from existing $existingUpc to new $upc? Maybe need a new variable with brand in name?");
		}
		$result = $this->updateCommonVariableProperty(Variable::FIELD_UPC_14, $upc, __METHOD__);
		return $result;
	}
	/**
	 * @param string|object|[] $imageUrl
	 * @param bool $updateIfDifferent
	 * @return void
	 */
	public function updateImageUrlIfNecessary($imageUrl, bool $updateIfDifferent = false): void{
		if(is_object($imageUrl)){
			$imageUrl = json_decode(json_encode($imageUrl), true);
		}
		if(is_array($imageUrl) && isset($imageUrl['imageUrl'])){
			$imageUrl = $imageUrl['imageUrl'];
		}
		if(is_string($imageUrl)){
			try {
				$this->setImageUrl($imageUrl, $updateIfDifferent);
			} catch (InvalidStringException $e) {
				le($e);
			}
		}
	}
	/**
	 * @param string|[] $productUrl
	 * @return int
	 */
	public function updateProductUrlIfNecessary($productUrl){
		if(is_object($productUrl)){
			$productUrl = json_decode(json_encode($productUrl), true);
		}
		if(is_array($productUrl) && isset($productUrl['productUrl'])){
			$productUrl = $productUrl['productUrl'];
		}
		if(is_string($productUrl) || $productUrl === false){
			if($this->productUrl != $productUrl){
				return $this->setProductUrl($productUrl);
			}
		}
		return false;
	}
	/**
	 * @param string|[] $price
	 * @return int
	 */
	public function updatePriceIfNecessary($price){
		if(is_object($price)){
			$price = json_decode(json_encode($price), true);
		}
		if(is_array($price) && isset($price['price'])){
			$price = $price['price'];
		}
		if(!is_array($price)){
			return $this->setPrice($price);
		}
		return false;
	}
	/**
	 * @param string $string
	 * @return bool
	 */
	public function isNameOrSynonym(string $string): bool{
		return $this->inSynonyms($string);
	}
	/**
	 * @return QMUnit[]
	 */
	public function getAvailableUnits(): array{
		if($this->availableUnits){
			return $this->availableUnits;
		}
		$u = $this->getUserOrCommonUnit();
		$compatible = $u->getCompatibleUnits();
		foreach($compatible as $one){
			$this->availableUnitNames[] = $one->name;
		}
		sort($this->availableUnitNames);
		return $this->availableUnits = $compatible;
	}
	/**
	 * @param string $reason
	 * @param int|null $variableId
	 */
	public function deleteCommonTags(string $reason, int $variableId){
		try {
			QMCommonTag::delete($variableId, $this->getVariableIdAttribute(), $reason);
		} catch (NotFoundException $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
		}
		try {
			QMCommonTag::delete($this->getVariableIdAttribute(), $variableId, $reason);
		} catch (NotFoundException $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
		}
	}
	/**
	 * @param string $reason
	 */
	public function deleteAllCommonTaggedByMe(string $reason): void{
		$tags = $this->getCommonTaggedVariables();
		$number = count($tags);
		if(!$number){
			return;
		}
		$this->logError("Deleting $number tags because $reason");
		foreach($tags as $tag){
			$this->deleteCommonTags($reason, $tag->getId());
		}
	}
	/**
	 ** Returns an array of all tag_variable variables of the given variable
	 * @return array|QMVariable[]
	 */
	abstract public function setCommonTaggedVariables(): array;
	/**
	 * @return string
	 */
	public function getDataQuantityOrTrackingInstructionsHTML(): string{
		return $this->getTrackingInstructionsHtml();
	}
	/**
	 * @return string
	 */
	public function setValence(): ?string{
		if(empty($this->valence)){
			$this->valence = BaseValenceProperty::generate($this);
		}
		return $this->valence;
	}
	/**
	 * @return string
	 */
	public function getValence(): ?string{
		return $this->valence ?: $this->setValence();
	}
	/**
	 * @return bool
	 */
	public function valenceIsPositive(): bool{
		return $this->getValence() === BaseValenceProperty::VALENCE_POSITIVE;
	}
	/**
	 * @return bool
	 */
	public function valenceIsNegative(): bool{
		return $this->getValence() === BaseValenceProperty::VALENCE_NEGATIVE;
	}
	/**
	 * @return string
	 */
	public function getHowToTrackTitle(): string{
		return "How to Track " . $this->getOrSetVariableDisplayName();
	}
	/**
	 * @param bool $abbreviatedUnit
	 * @return string
	 */
	public function getDisplayNameWithUserUnitInParenthesis(bool $abbreviatedUnit = true): string{
		if($abbreviatedUnit){ // Full unit name makes axis labels too long!
			$unit = $this->getUserUnit()->abbreviatedName;
		} else{
			$unit = $this->getUserUnit()->name;
		}
		return $this->getOrSetVariableDisplayName() . " (" . $unit . ")";
	}
	/**
	 * @return bool
	 */
	public function isRating(): bool{
		return $this->getUserOrCommonUnit()->categoryName === RatingUnitCategory::NAME;
	}
	/**
	 * @param string $parentVariableName
	 * @param array $newParentVariableParameters
	 * @return int
	 * @throws InvalidTagCategoriesException
	 */
	public function addParentCommonTag(string $parentVariableName, array $newParentVariableParameters = []): ?int{
		$newParentVariableParameters[self::doNotCreateNewVariableWithNameInUnit] = true;
		$parentVariable = QMCommonVariable::findOrCreateByName($parentVariableName, $newParentVariableParameters);
		if($this->variableId === $parentVariable->getVariableIdAttribute()){
			$this->logInfo("Not creating tag for $parentVariableName because the variable with got with that " .
				"term has the same id as the current variable.  Might want to debug this at some point.");
			return null;
		}
		$result = QMCommonTag::updateOrInsert($this->variableId, $parentVariable->getVariableIdAttribute(), 1);
		if($result){
			$this->unsetAllTagTypes();
		}
		return $result;
	}
	/**
	 * @return bool
	 */
	public function isTreatment(): bool{
		return $this->getVariableCategoryName() === TreatmentsVariableCategory::NAME;
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
	public function isEmotion(): bool{
		return $this->getVariableCategoryName() === EmotionsVariableCategory::NAME;
	}
	/**
	 * @return bool
	 */
	public function isSymptom(): bool{
		return $this->getVariableCategoryName() === SymptomsVariableCategory::NAME;
	}
	/**
	 * @return bool|mixed
	 */
	protected function searchAmazonForProductUrlIfNecessary(): bool{
		if(AppMode::isTestingOrStaging()){
			return false;
		}
		if(AppMode::isApiRequest()){
			return false;
		}
		if(!$this->getQMVariableCategory()->getAmazonProductCategory()){
			return false;
		}
		if($this->isAddress()){
			return false;
		}
		if($this->isAppOrWebsite()){
			return false;
		}
		if($this->isTestVariable()){
			return false;
		}
		if($this->getProductUrl() === null){
			$this->getProductAndUpdateProperties();
			return true;
		}
		//        if($this->imageUrl === null){
		//            // Don't think we need to search for images
		//            $this->getProductAndUpdateProperties();
		//            return true;
		//        }
		// We search Amazon too much
		//        if($this->getUpc() === null){
		//            $this->getProductAndUpdateProperties();
		//            return true;
		//        }
		return false;
	}
	/**
	 * @return bool|int
	 */
	public function isAppOrWebsite(){
		if($this->getVariableCategoryName() === SoftwareVariableCategory::NAME){
			return true;
		}
		return stripos($this->getVariableName(), RescueTimeConnector::APP_OR_WEBSITE_SUFFIX) !== false;
	}
	/**
	 * @return bool|int
	 */
	public function isTestVariable(){
		return stripos($this->getVariableName(), 'test ') !== false;
	}
	/**
	 * @return float
	 */
	public function getInterestingFactor(){
		$factor = 1;
		if($this->isStupidVariable()){
			return 0;
		}
		if($this->isAppOrWebsite()){
			$factor /= 2;
		}
		if($this->isTestVariable()){
			$factor /= 2;
		}
		if($this->isAddress()){
			$factor /= 2;
		}
		return $factor;
	}
	/**
	 * @param Product $product
	 */
	protected function updatePropertiesWithDataFromProduct(Product $product){
		$this->updateProductUrlIfNecessary($product->getProductUrl());
		$this->updateImageUrlIfNecessary($product->getImageUrl());
		$this->updatePriceIfNecessary($product->getPrice());
		$this->updateUpcIfNecessary($product->getBarcode());
	}
	protected function getProductAndUpdateProperties(){
		$product = ProductHelper::getByKeyword($this->getOrSetVariableDisplayName(), $this->getVariableCategoryName());
		if(!$product){
			$this->updateProductUrlIfNecessary(false);
		} else{
			$this->updatePropertiesWithDataFromProduct($product);
		}
	}
	/**
	 * @param array|null $meta
	 * @return array
	 */
	public function getLogMetaData(?array $meta = []): array{
		//$metaData['variable'] = $this; // Too much memory to json_encode all the time
//		if($this->hasId() && $this->name){ // Sometimes this is called before constructor is finished populating
//			$meta[$this->getTitleAttribute() . 'debug_url'] = UrlHelper::getLocalUrl("api/v1/variables?variableId=" . $this->id);
//			$meta[$this->getTitleAttribute() . 'variable_settings'] = $this->getVariableSettingsUrl();
//			$meta['EDIT' . $this->getTitleAttribute()] = $this->getEditUrl();
//			$meta['SHOW' . $this->getTitleAttribute()] = $this->getUrl();
//			if(QMLogLevel::isDebug()){
//				$meta[$this->getTitleAttribute() . 'PHPUNIT_TEST'] = $this->getPHPUnitTestUrl();
//			}
//		}
		return $meta ?? [];
	}
	public function getEditUrl(): string{
		return $this->getSettingsUrl();
	}
	public function getUrlParams(): array{
		return [
			UserVariable::FIELD_VARIABLE_ID => $this->getVariableIdAttribute(),
			Variable::FIELD_VARIABLE_CATEGORY_ID => $this->getVariableCategoryId(),
			Variable::FIELD_DEFAULT_UNIT_ID => $this->getUnitIdAttribute(),
		];
	}
	/**
	 * @return string
	 */
	public function __toString(){
		// return $this->getVariableName()." ($this->variableId)";
		return $this->getVariableName();
	}
	/**
	 * @return bool
	 */
	protected function getAndCheckOutcome(): bool{
		$outcome = $this->isOutcome();
		/** @noinspection TypeUnsafeComparisonInspection */
		if($outcome != $this->getQMVariableCategory()->outcome){
			QMLog::error("Setting " . $this->getVariableName() .
				" outcome to $outcome even though variable category outcome is " .
				$this->getQMVariableCategory()->outcome);
		}
		return $outcome;
	}
	/**
	 * @return float
	 */
	abstract public function getMostCommonValue(): float;
	/**
	 * @return bool
	 */
	public function isAddress(): bool{
		return QMStr::isAddress($this->getVariableName());
	}
	/**
	 * @param int $userId
	 * @param string|null $variableCategoryName
	 * @return QMUserVariable
	 */
	public function getExistingNonPaymentUserVariableOrCreateNewWithReminderFromAmazonVariableParams(int $userId,
		string $variableCategoryName = null): QMUserVariable{
		$nonPaymentName = VariableNameProperty::stripSpendingPurchasePayments($this->getVariableName());
		$userVariable =
			QMUserVariable::findOrCreateWithReminderFromAmazon($nonPaymentName, $userId, $variableCategoryName);
		return $userVariable;
	}
	/**
	 * @return bool
	 */
	public function isPaymentVariable(): bool{
		return $this->getUserOrCommonUnit()->isCurrency();
	}
	public function unsetAllTagTypes(){
		$this->setNumberOfCommonTags(null);
		$this->setNumberCommonTaggedBy(null);
		foreach($this as $key => $value){
			if($key === 'joinedVariables' || stripos($key, 'TagVariables') !== false ||
				stripos($key, 'TaggedVariables') !== false || stripos($key, 'TaggedRows') !== false ||
				stripos($key, 'TagRows') !== false){
				$this->$key = null;
			}
		}
	}
	/**
	 * @return QMCommonVariable[]
	 * @throws InsufficientMemoryException
	 */
	public function setJoinedCommonTagVariables(): array{
		$this->joinedCommonTagVariables = [];
		$tagVariables = $this->getCommonTagVariables();
		$taggedVariables = $this->getCommonTaggedVariables();
		if($tagVariables && $taggedVariables){
			/** @var QMUserVariable $tag */
			foreach($tagVariables as $tag){
				if($tag instanceof stdClass){
					le('$tag instanceof stdClass');
				}
				/** @var QMUserVariable $tagged */
				foreach($taggedVariables as $tagged){
					if($tagged instanceof stdClass){
						le('$tagged instanceof stdClass');
					}
					/** @noinspection TypeUnsafeComparisonInspection */
					if($tag->tagConversionFactor == 1 && $tagged->tagConversionFactor == 1 &&
						$tagged->getVariableIdAttribute() === $tag->getVariableIdAttribute()){
						$this->joinedCommonTagVariables[] = $tag;
						$this->joinedCommonTagVariableIds[] = $tag->getVariableIdAttribute();
					}
				}
			}
		}
		return $this->joinedCommonTagVariables;
	}
	/**
	 * @return QMCommonVariable[]
	 */
	public function getParentCommonTagVariables(): array{
		if($this->parentCommonTagVariables === null){
			$this->setIngredientAndParentCommonTagVariables();
		}
		return $this->parentCommonTagVariables;
	}
	public function unsetMeasurements(): void{
		$this->setDailyMeasurementsWithTags(null);
		$this->measurements = $this->measurementsWithTags =
		$this->anonymousMeasurements = $this->dailyMeasurements = null;
	}
	/**
	 * @return QMCommonVariable[]
	 */
	public function getChildCommonTagVariables(): array{
		if($this->childCommonTagVariables === null){
			$this->setIngredientOfAndChildCommonTagVariables();
		}
		return $this->childCommonTagVariables;
	}
	/**
	 * @return QMCommonVariable[]
	 */
	public function getIngredientOfCommonTagVariables(): array{
		if($this->ingredientOfCommonTagVariables === null){
			$this->setIngredientOfAndChildCommonTagVariables();
		}
		return $this->ingredientOfCommonTagVariables;
	}
	private function setIngredientOfAndChildCommonTagVariables(): void{
		$this->ingredientOfCommonTagVariables = $this->childCommonTagVariables = [];
		$ids = $this->setJoinedCommonTagVariableIds();
		/** @var QMVariable $variable */
		foreach($this->commonTaggedVariables as $variable){
			if(in_array($variable->getVariableIdAttribute(), $ids, true)){
				continue;
			}
			if($variable->tagConversionFactor == 1){
				$this->childCommonTagVariables[] = $variable;
			} else{
				$this->ingredientOfCommonTagVariables[] = $variable;
			}
		}
	}
	/**
	 * @return QMCommonVariable[]
	 * @throws InsufficientMemoryException
	 */
	public function getIngredientCommonTagVariables(): array{
		if($this->ingredientCommonTagVariables === null){
			$this->setIngredientAndParentCommonTagVariables();
		}
		return $this->ingredientCommonTagVariables;
	}
	/**
	 * @throws InsufficientMemoryException
	 */
	private function setIngredientAndParentCommonTagVariables(): void{
		$this->ingredientCommonTagVariables = $this->parentCommonTagVariables = [];
		$ids = $this->setJoinedCommonTagVariableIds();
		$variables = $this->getCommonTagVariables();
		foreach($variables as $v){
			if(in_array($v->variableId, $ids, true)){
				continue;
			}
			if($v->tagConversionFactor == 1){
				$this->parentCommonTagVariables[] = $v;
			} else{
				$this->ingredientCommonTagVariables[] = $v;
			}
		}
	}
	/**
	 * @throws InsufficientMemoryException
	 */
	public function getAllCommonTagVariableTypes(){
		$this->getCommonTagVariables();
		$this->getCommonTaggedVariables();
		$this->getJoinedCommonTagVariables();
		$this->getIngredientOfCommonTagVariables();
		$this->getIngredientCommonTagVariables();
		$this->getParentCommonTagVariables();
		$this->getChildCommonTagVariables();
	}
	public function setAllCommonTagVariableTypes(){
		$this->setCommonTagVariables();
		$this->setCommonTaggedVariables();
		$this->setJoinedCommonTagVariables();
		$this->setIngredientAndParentCommonTagVariables();
		$this->setIngredientOfAndChildCommonTagVariables();
	}
	/**
	 * @return QMCommonVariable[]
	 */
	public function getJoinedCommonTagVariables(): array{
		if($this->joinedCommonTagVariables === null){
			$this->setJoinedCommonTagVariables();
		}
		return $this->joinedCommonTagVariables;
	}
	/**
	 * @return array
	 * @throws InsufficientMemoryException
	 */
	private function setJoinedCommonTagVariableIds(): array{
		$this->joinedCommonTagVariableIds = [];
		$joinedCommonTagVariables = $this->setJoinedCommonTagVariables();
		foreach($joinedCommonTagVariables as $variable){
			$this->joinedCommonTagVariableIds[] = $variable->getVariableIdAttribute();
		}
		return $this->joinedCommonTagVariableIds;
	}
	/**
	 * @param QMVariable $tagVariable
	 */
	protected function setTagDisplayTextOnTagVariable(QMVariable $tagVariable){
		$tagVariable->tagDisplayText =
			$tagVariable->getUserUnit()->getValueAndUnitString($tagVariable->tagConversionFactor, true) . ' ' .
			$tagVariable->getVariableName() . ' per ' . $this->getUserUnit()->getValueAndUnitString(1, true) . ' ' .
			$this->getVariableName();
		if($tagVariable->getUserUnit()->abbreviatedName === '/5'){
			$tagVariable->tagDisplayText =
				$this->getVariableName() . ' is tagged with ' . $tagVariable->getVariableName();
		}
	}
	/**
	 * @param QMVariable $taggedVariable
	 */
	protected function setTagDisplayTextOnTaggedVariable(QMVariable $taggedVariable){
		$taggedUnit = $taggedVariable->getUserUnit();
		$valueUnit = $taggedUnit->getValueAndUnitString($taggedVariable->tagConversionFactor, true);
		$tagUnit = $this->getUserUnit();
		$tagValueUnit = $tagUnit->getValueAndUnitString(1, true);
		$taggedVariable->tagDisplayText =
			$valueUnit . ' ' . $taggedVariable->getVariableName() . ' per ' . $tagValueUnit . ' ' .
			$this->getVariableName();
		if($taggedVariable->getUserUnit()->abbreviatedName === '/5'){
			$taggedVariable->tagDisplayText =
				$taggedVariable->getVariableName() . ' is tagged with ' . $this->getVariableName();
		}
	}
	/**
	 * @return string|null
	 * @throws \App\Exceptions\InvalidStringException
	 */
	public function setWikipediaExtract(): ?string{
		if(!$this->imageUrl){
			$this->setImageUrl(WikiHelper::getImage($this->getOrSetVariableDisplayName()));
		}
		return $this->wikipediaExtract = WikiHelper::getExtract($this->getOrSetVariableDisplayName());
	}
	/**
	 * @return bool|string
	 */
	public function getWikipediaExtract(){
		if($this->wikipediaExtract === null){
			$this->setWikipediaExtract();
		}
		return $this->wikipediaExtract;
	}
	/**
	 * @param array $urlParams
	 * @return string
	 */
	public function setTrackingInstructionsHtml(array $urlParams = []): string{
		$dataSource = $this->getBestDataSource();
		$this->trackingInstructions = $dataSource->setInstructionsHtml($this, $urlParams);
		return $this->trackingInstructions;
	}
	/**
	 * @return TrackingInstructionsQMCard
	 */
	public function getTrackingInstructionCard(): TrackingInstructionsQMCard{
		if($card = $this->trackingInstructionsCard){
			return $card;
		}
		$card = new TrackingInstructionsQMCard($this);
		return $this->trackingInstructionsCard = $card;
	}
	/**
	 * @param array $urlParams
	 * @return string
	 */
	public function getTrackingInstructionsHtml(array $urlParams = []): string{
		return $this->trackingInstructions ?: $this->setTrackingInstructionsHtml($urlParams);
	}

	/**
	 * Provided source name, client id, connector name, or QMDataSource name should be in measurements.source_name
	 * column
	 * @return string[]
	 */
	public function getOrCalculateDataSourceNames(): array{
		$dataSourcesCount = $this->getOrCalculateDataSourcesCount();
		return array_keys($dataSourcesCount);
	}
	/**
	 * Provided source name, client id, connector name, or QMDataSource name should be in measurements.source_name
	 * column
	 * @return array
	 */
	abstract public function calculateDataSourcesCount(): array;
	/**
	 * @return int[]
	 */
	abstract public function calculateNonUniqueDataSourceNames(): array;
	/**
	 * @param array $daily
	 */
	protected function validateDailyMeasurements(array $daily): void{
		$first = QMArr::first($daily);
		if(!$first instanceof DailyMeasurement && !$first instanceof FillerMeasurement &&
			!$first instanceof DailyAnonymousMeasurement &&
			!$first instanceof AnonymousMeasurement){ // TODO: Use DailyAnonymousMeasurement
			le("should be a DailyMeasurement, DailyAnonymousMeasurement or FillerMeasurement but got: " .
				get_class($first), $first);
		}
	}
	/**
	 * @return string
	 */
	public function getImageUrl(): string{
		if($i = $this->imageUrl){
			return $i;
		}
		if($i = $this->pngUrl){
			return $this->setImageUrl($i);
		}
		if($this->svgUrl){
			return $this->setImageUrl($this->svgUrl);
		}
		return $this->setImageUrl($this->getQMVariableCategory()->getImageUrl());
	}
	/**
	 * @return string
	 * @throws \App\Exceptions\InvalidStringException
	 */
	public function getImage(): string{
		return $this->getImageUrl();
	}
	/**
	 * @return QMButton[]
	 */
	public function setDefaultButtons(): array{
		$buttons = $this->buttons ?: [];
		if(!is_array($buttons)){
			$buttons = [];
		}  // Probably Mongo cached format
		$buttons = array_merge($buttons, $this->getCommonVariableButtons(false));
		return $this->buttons = $buttons;
	}
	/**
	 * @param bool $excludeAddReminder
	 * @return QMButton[]
	 */
	public function getCommonVariableButtons(bool $excludeAddReminder): array{
		if(isset(self::$commonVariableButtons[$this->getVariableIdAttribute()][$excludeAddReminder])){
			return self::$commonVariableButtons[$this->getVariableIdAttribute()][$excludeAddReminder];
		}
		$buttons[] = new MeasurementAddVariableStateButton($this);
		if(!$excludeAddReminder){
			$buttons[] = new ReminderAddStateButton($this);
		}
		$buttons[] = new StudyCreationStateButton($this);
		if($this->outcome && $this->numberOfCorrelationsAsEffect){
			$buttons[] = new PredictorsAllStateButton($this, ['effectVariableName' => $this->name]);
		}
		if(!$this->outcome && $this->numberOfCorrelationsAsCause){
			$buttons[] = new PredictorsAllStateButton($this, ['causeVariableName' => $this->name]);
		}
		self::$commonVariableButtons[$this->getVariableIdAttribute()][$excludeAddReminder] = $buttons;
		return $buttons;
	}
	/**
	 * @return NotificationButton[]
	 */
	public function getNotificationActionButtons(): array{
		$buttons = $this->actionArray;
		if($this->isOneToFiveRating()){
			$buttons = $this->setOneToFiveActionArray();
		} elseif($this->isOneToTenRating()){
			$buttons = $this->setOneToTenActionArray();
		} elseif($this->isYesNo()){
			$buttons = $this->setYesNoActionArray();
		}
		if(!is_array($buttons)){
			le("Buttons should be an array!");
		}
		return $this->actionArray = $buttons;
	}
	/**
	 * @return bool
	 */
	public function isOneToTenRating(): bool{
		return $this->getUserUnit()->abbreviatedName === OneToTenRatingUnit::ABBREVIATED_NAME;
	}
	/**
	 * @return NotificationButton[] $actionArray
	 */
	public function setYesNoActionArray(): array{
		$actionArray[0] = new YesNotificationButton($this);
		$actionArray[1] = new NoNotificationButton($this);
		$this->setInputType(QMUnit::INPUT_TYPE_yesOrNo);
		return $this->actionArray = $actionArray;
	}
	/**
	 * @param string $inputType
	 */
	public function setInputType(string $inputType): void{
		$this->inputType = $inputType;
	}
	/**
	 * @return NotificationButton[] $actionArray
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function setOneToFiveActionArray(): array{
		$actionArray = [];
		$ratingValues = self::ONE_TO_FIVE_RATING_VALUES;
		$u = $this->getUserUnit();
		foreach($ratingValues as $value){
			/** @noinspection PhpUnhandledExceptionInspection */
			$u->validateValue($value);
			$actionArray[] = new RatingNotificationButton($value, $this);
		}
		return $this->actionArray = $actionArray;
	}
	/**
	 * @return NotificationButton[] $actionArray
	 */
	public function setOneToTenActionArray(): array{
		$actionArray = [];
		$ratingValues = self::ONE_TO_TEN_RATING_VALUES;
		foreach($ratingValues as $value){
			try {
				$this->getUserUnit()->validateValue($value);
				$actionArray[] = new NotificationButton(QMUnit::getUnitByAbbreviatedName("/10"), $value . '/10', $value,
					QMTrackingReminderNotification::TRACK, $this);
			} catch (InvalidVariableValueException $e) {
			}
		}
		return $this->actionArray = $actionArray;
	}
	/**
	 * @return string
	 */
	public function getListCardTitle(): string{
		return $this->getOrSetVariableDisplayName();
	}
	/**
	 * @return string
	 */
	public function getQuestion(): string{
		return $this->question ?: $this->setQuestion();
	}
	/**
	 * @return string
	 */
	public function setQuestion(): string{
		$when = $this->getWhen();
		$isWas = $this->getIsWas();
		$question = $this->getOrSetVariableDisplayName() . " (" . $this->getUserUnit()->abbreviatedName . ")";
		if($this->isRating()){
			$question = "How $isWas your " . $this->getOrSetVariableDisplayName() . "$when?";
		}
		if($this->isYesNo()){
			$question = $this->getOrSetVariableDisplayName() . "$when?";
		}
		return $this->question = $question;
	}
	/**
	 * @return string
	 */
	protected function getIsWas(): string{
		$when = $this->getWhen();
		$isWas = (stripos($when, "today") !== false || $when === "") ? "is" : "was";
		return $isWas;
	}
	/**
	 * @return string
	 */
	protected function getWhen(): string{
		$when = " today";
		if(method_exists($this, 'getTrackingReminderNotificationTimeLocalHumanString')){
			$when = " " . $this->getTrackingReminderNotificationTimeLocalHumanString();
		}
		return $when;
	}
	/**
	 * @param Surface|null $surface
	 * @return string
	 */
	public function getLongQuestion(Surface $surface = null): string{
		$when = $this->getWhen();
		$isWas = $this->getIsWas();
		if($this->isRating()){
			$question = "How";
			$unit = $this->getUserUnit();
			if($this->valenceIsNegative() && stripos($this->getOrSetVariableDisplayName(), 'sever') === false){
				$question .= " severe";
			}
			$min = $unit->getMinimumValue(true);
			$max = $unit->getMaximumRawValue(true);
			if($min === null){
				le("No min!");
			}
			if($when === " today"){
				$when = "";
			}
			return $this->longQuestion = "$question $isWas your " . strtolower($this->getOrSetVariableDisplayName()) .
				"$when on a scale of $min to $max?";
		}
		$sentence = "How many ";
		if(!$this->getUserUnit()->isCount()){
			$sentence .= strtolower($this->getUserUnit()->name) . " ";
		}
		$sentence .= $this->getOrSetVariableDisplayName() . " did you have$when?";
		if($this->isYesNo()){
			$sentence = $this->getYesNoLongQuestion();
		}
		$sentence = str_ireplace('number of ', '', $sentence);
		if($surface && !$surface->hasScreen() && $this->getUserUnit()->isWeightCategoryOrInternationalUnits()){
			foreach($this->getVoiceOptions(true) as $value){
				$sentence .= " $value " . $this->getUserUnit()->name . ", ";
			}
			$sentence .= " or a different value?";
		}
		return $this->longQuestion = $sentence;
	}
	/**
	 * @param bool $onlyNumeric
	 * @return array
	 */
	public function getVoiceOptions(bool $onlyNumeric = false): array{
		$array = [];
		$buttons = $this->getVoiceOptionButtons();
		foreach($buttons as $button){
			if($button->modifiedValue !== null){
				try {
					$this->validateValueForCommonVariableAndUnit($button->modifiedValue, 'buttonValue');
				} catch (InvalidVariableValueException $e) {
					$this->logError(__METHOD__.": ".$e->getMessage());
					continue;
				}
				$valueOrTitle = $button->modifiedValue;
				if($button->modifiedValue === 1 && $this->isYesNo()){
					$valueOrTitle = "Yes";
				}
				if($button->modifiedValue === 0 && $this->isYesNo()){
					$valueOrTitle = "No";
				}
				$array[] = $valueOrTitle;
			} elseif(!$onlyNumeric){
				$array[] = $button->text;
			}
		}
		return $array;
	}
	/**
	 * @return string
	 */
	protected function getYesNoLongQuestion(): string{
		$when = $this->getWhen();
		$sentence = "Did you have " . $this->getOrSetVariableDisplayName() . "$when?";
		$sentence = str_ireplace('number of ', '', $sentence);
		return $sentence;
	}
	/**
	 * @return string
	 */
	public function getOptimalValueMessage(): ?string{
		return $this->getCommonOptimalValueMessage();
	}
	/**
	 * @return string
	 */
	public function getCommonOptimalValueMessage(): ?string{
		if($this->commonOptimalValueMessage === null){
			$this->generateCommonOptimalValueMessage();
		}
		return $this->commonOptimalValueMessage;
	}
	/**
	 * @return GlobalVariableRelationship
	 */
	public function getBestGlobalVariableRelationship(): ?GlobalVariableRelationship{
		$c = $this->bestGlobalVariableRelationship;
		if($c){
			return $c;
		}
		if($c === false){
			return null;
		}
		$c = $this->setBestGlobalVariableRelationship();
		if(!$c){
			$this->bestGlobalVariableRelationship = false;
			return null;
		}
		return $this->bestGlobalVariableRelationship = $c;
	}
	/**
	 * @return GlobalVariableRelationship
	 */
	public function setBestGlobalVariableRelationship(): ?GlobalVariableRelationship{
		$c = null;
		if($this->isOutcome()){
			$correlations = $this->getGlobalVariableRelationshipsAsEffect(1);
			if($c = $correlations->first()){
				$this->setCommonBestCauseVariableId($c->getCauseVariableId());
				return $this->strongestGlobalVariableRelationshipAsEffect = $c;
			}
		}
		if(!$c){
			$correlations = $this->getGlobalVariableRelationshipsAsCause(1);
			if(!$correlations){
				return null;
			}
			if($c = $correlations->first()){
				$this->setCommonBestEffectVariableId($c->getEffectVariableId());
				return $this->strongestGlobalVariableRelationshipAsCause = $c;
			}
		}
		if($c && $c->typeIsIndividual()){
			le('$c && $c->typeIsIndividual()');
		}
		return $this->bestGlobalVariableRelationship = $c;
	}
	/**
	 * @return string
	 */
	public function generateCommonOptimalValueMessage(): ?string{
		$msg = VariableOptimalValueMessageProperty::generate($this);
		return $this->commonOptimalValueMessage = $msg;
	}
	/**
	 * @return int
	 */
	public function getCommonBestCauseVariableId(): ?int{
		return $this->commonBestCauseVariableId;
	}
	/**
	 * @return mixed
	 */
	public function getCommonBestEffectVariableId(): ?int{
		return $this->commonBestEffectVariableId;
	}
	/**
	 * @return string
	 */
	public function setBestPopulationStudyLink(){
		$params = $this->getCommonBestStudyParams();
		if(!$params){
			return $this->bestPopulationStudyLink = false;
		}
		return $this->bestPopulationStudyLink = StudyStateButton::getStudyUrl($params);
	}
	/**
	 * @return StudyCard
	 */
	public function getBestPopulationStudyCard(): StudyCard{
		if($this->bestPopulationStudyCard === null){
			$this->setBestPopulationStudyCard();
		}
		return $this->bestPopulationStudyCard;
	}
	/**
	 * @return StudyCard
	 */
	public function setBestPopulationStudyCard(): ?StudyCard{
		$correlation = $this->getBestGlobalVariableRelationship();
		if(!$correlation){
			$this->bestPopulationStudyCard = false;
			return null;
		}
		/** @var QMUserVariableRelationship $correlation */
		return $this->bestPopulationStudyCard = $correlation->getOptionsListCard();
	}
	/**
	 * @param int|null $limit
	 * @return StudyCard[]
	 */
	public function getBestStudyCards(int $limit = null): array{
		$cards = [];
		$correlations = $this->getOutcomesOrPredictors($limit);
		if(!$correlations){
			return [];
		}
		foreach($correlations as $c){
			/** @var QMGlobalVariableRelationship $c */
			$cards[] = $c->getStudyCard();
		}
		return $cards;
	}
	/**
	 * @param int $commonBestCauseVariableId
	 */
	public function setCommonBestCauseVariableId(int $commonBestCauseVariableId){
		$this->commonBestCauseVariableId = $commonBestCauseVariableId;
	}
	/**
	 * @param int $commonBestEffectVariableId
	 */
	public function setCommonBestEffectVariableId(int $commonBestEffectVariableId){
		$this->commonBestEffectVariableId = $commonBestEffectVariableId;
	}
	/**
	 * @return array
	 */
	protected function getCommonBestStudyParams(){
		if($this->isOutcome() && $this->getCommonBestCauseVariableId()){
			$params = [
				'causeVariableId' => $this->getCommonBestCauseVariableId(),
				'effectVariableId' => $this->getVariableIdAttribute(),
			];
		} elseif(!$this->isOutcome() && $this->getCommonBestEffectVariableId()){
			$params = [
				'causeVariableId' => $this->getVariableIdAttribute(),
				'effectVariableId' => $this->getCommonBestEffectVariableId(),
			];
		} elseif($this->getCommonBestCauseVariableId()){
			$params = [
				'causeVariableId' => $this->getCommonBestCauseVariableId(),
				'effectVariableId' => $this->getVariableIdAttribute(),
			];
		} elseif($this->getCommonBestEffectVariableId()){
			$params = [
				'causeVariableId' => $this->getVariableIdAttribute(),
				'effectVariableId' => $this->getCommonBestEffectVariableId(),
			];
		} else{
			$params = false;
		}
		return $params;
	}
	/**
	 * @return string
	 */
	public function setBestStudyLink(){
		return $this->bestStudyLink = $this->setBestPopulationStudyLink();
	}
	/**
	 * @return StudyCard
	 */
	public function setBestStudyCard(): ?StudyCard{
		return $this->bestStudyCard = $this->getBestPopulationStudyCard();
	}
	/**
	 * @return AnonymousMeasurement[]
	 */
	public function getInvalidMeasurements(): array{
		$measurements = $this->getTooSmallMeasurements();
		$measurements = array_merge($measurements, $this->getTooBigMeasurements());
		return $measurements;
	}
	/**
	 * @return AnonymousMeasurement[]
	 */
	public function getInvalidSourceData(): array{
		return $this->invalidSourceData = $this->getInvalidMeasurements();
	}
	public function getTooBigMeasurements(): array{
		$max = $this->getMaximumAllowedValueAttribute();
		if(!$max){
			return [];
		}
		return Measurement::whereVariableId($this->getVariableIdAttribute())->where(Measurement::FIELD_VALUE, ">", $max)->get()
			->all();
	}
	public function getTooSmallMeasurements(): array{
		$min = $this->getMinimumAllowedValueAttribute();
		if(!$min){
			return [];
		}
		return Measurement::whereVariableId($this->getVariableIdAttribute())->where(Measurement::FIELD_VALUE, "<", $min)->get()
			->all();
	}
	/**
	 * @return string
	 */
	abstract public function getPHPUnitJobTest(): ?string;
	/**
	 * @param int $commonUnitId
	 */
	public function setCommonUnitId(int $commonUnitId): void{
		$this->commonUnitId = $commonUnitId;
	}
	/**
	 * @param int|null $limit
	 * @param string|null $causeCategory
	 * @return GlobalVariableRelationship[]|Collection
	 */
	public function getGlobalVariableRelationshipsAsEffect(int $limit = null, string $causeCategory = null): ?Collection{
		$correlations = $this->getVariable()
			->getGlobalVariableRelationshipsAsEffect($limit ?? \App\Utils\Env::get('CORRELATION_LIMIT'), $causeCategory);
		if(!$causeCategory && !$correlations->count()){
			$this->numberOfGlobalVariableRelationshipsAsCause = 0;
		}
		if($causeCategory){
			return HasCauseAndEffect::filterByCauseCategory($correlations, $causeCategory);
		}
		return $correlations;
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return GlobalVariableRelationship[]|Collection
	 */
	public function getGlobalVariableRelationshipsAsCause(int $limit = null, string $variableCategoryName = null): Collection{
		if($env = \App\Utils\Env::get('CORRELATION_LIMIT')){$limit = $env;}
		$correlations = $this->getVariable()
			->getGlobalVariableRelationshipsAsCause($limit, $variableCategoryName);
		if(!$variableCategoryName && !$correlations->count()){
			$this->numberOfGlobalVariableRelationshipsAsCause = 0;
		}
		if($variableCategoryName){
			return HasCauseAndEffect::filterByCauseCategory($correlations, $variableCategoryName);
		}
		return $correlations;
	}
	/**
	 * @param int|null $limit
	 * @return QMUserStudy[]|QMPopulationStudy[]
	 */
	public function getBestStudies(int $limit = null): array{
		$correlations = $this->getOutcomesOrPredictors($limit);
		if(!$correlations->count()){
			return [];
		}
		$studies = [];
		foreach($correlations as $correlation){
			$studies[] = $correlation->findInMemoryOrNewQMStudy();
		}
		return $studies;
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return GlobalVariableRelationship[]|UserVariableRelationship[]|Collection
	 */
	abstract public function getOutcomesOrPredictors(int $limit = null,
		string $variableCategoryName = null): ?Collection;
	abstract public function getCorrelationDataRequirementAndCurrentDataQuantityString(): string;
	/**
	 * @return GlobalVariableRelationship[]|Collection
	 */
	public function getCorrelationsListTitle(): string{
		if($this->isOutcome()){
			return "Strongest Predictors of " . $this->getTitleAttribute();
		} else{
			return "Outcomes Most Likely Influenced by " . $this->getTitleAttribute();
		}
	}
	/**
	 * @return GlobalVariableRelationship[]|Collection
	 */
	public function getCorrelationsChartSubTitle(): string{
		if($this->isOutcome()){
			return "These are the strongest predictors of " . $this->getTitleAttribute() . " based on our current data. ";
		} else{
			return "These are the outcomes most likely influenced by " . $this->getTitleAttribute() .
				" based on our current data. ";
		}
	}
	/**
	 * @return VariableSettingsCard
	 */
	public function getVariableSettingsCard(): VariableSettingsCard{
		if($this->variableSettingsCard){
			return $this->variableSettingsCard;
		}
		$card = new VariableSettingsCard($this);
		return $this->variableSettingsCard = $card;
	}
	/**
	 * @throws InsufficientMemoryException
	 */
	public function getAllCommonAndUserTagVariableTypes(){
		$this->getAllCommonTagVariableTypes();
	}
	/**
	 * @param string $reason
	 * @param bool $hardDelete
	 * @return int
	 */
	public function deleteCommonVariableAndAllAssociatedRecords(string $reason, bool $hardDelete): int{
		return $this->getCommonVariable()->deleteCommonVariableAndAllAssociatedRecords($reason, $hardDelete);
	}
	/**
	 * @return QMMeasurement[]
	 */
	abstract public function getQMMeasurements(): array;
	/**
	 * @param bool $dryRun
	 * @param bool $hardDelete
	 */
	public function deleteMeasurementsOutSideAllowedRange(bool $dryRun, bool $hardDelete): void{
		if(!$dryRun && !XDebug::active()){
			$this->logError("Setting to dry run because xDebug's not active and this is very dangerous");
			$dryRun = true;
			$hardDelete = false;
		}
		if($hardDelete && !XDebug::active()){
			$this->logError("Setting hardDelete to false because xDebug's not active and this is very dangerous");
			$hardDelete = false;
		}
		$tooBigDeleted = $this->deleteMeasurementsThatAreTooBig($dryRun, $hardDelete);
		$tooSmallDeleted = $this->deleteMeasurementsThatAreTooSmall($dryRun, $hardDelete);
		$userIds = array_unique(array_merge($tooSmallDeleted, $tooBigDeleted));
		if($userIds){
			$common = $this->getCommonVariable();
			/** @var QMMeasurement $measurement */
			foreach($userIds as $userId){
				try {
					$userVariable = QMUserVariable::getByNameOrId($userId, $this->getVariableIdAttribute());
					$userVariable->analyzeFully(__FUNCTION__, true);
				} catch (AlreadyAnalyzingException | UserVariableNotFoundException $e) {
					$this->logError(__METHOD__.": ".$e->getMessage());
				} catch (AlreadyAnalyzedException | ModelValidationException | TooSlowToAnalyzeException $e) {
					le($e);
				}
			}
			$common->setStatus(UserVariableStatusProperty::STATUS_WAITING); // Don't re-analyze here because we'll have an infinite loop
			le("Stopping after deleting measurements to avoid infinite loop");
		}
	}
	/**
	 * @return bool
	 */
	public function allTaggedMeasurementsAreSet(): bool{
		return isset($this->measurementsWithTags);
	}
	/**
	 * @return bool
	 */
	public function measurementsAreSet(): bool{
		return isset($this->measurements);
	}
	/**
	 * @return QMMeasurement[]
	 */
	abstract public function setMeasurementsWithTags(): array;
	/**
	 * @return QMMeasurement[]
	 */
	abstract protected function generateValidDailyMeasurementsWithTags(): array;
	/**
	 * @return DailyMeasurement[]
	 * @throws InsufficientMemoryException
	 */
	public function getDailyMeasurementsWithoutTagsOrFilling(): array{
		if($daily = $this->dailyMeasurements){
			return $daily;
		}
		$measurements = $this->setDailyMeasurements();
		$this->validateDailyMeasurementIndexing($measurements);
		return $measurements;
	}
	/**
	 * @param array $measurements
	 */
	protected function validateDailyMeasurementIndexing(array $measurements): void{
		if(isset($measurements[0])){
			le("Should be indexed by date");
		}
        $first = QMArr::first($measurements);
        if(is_array($first)){
            le("should not be an array!  Analyze here: ".$this->getAnalyzeUrl());
        }
		foreach($measurements as $date => $m){
			if(strlen($date) !== 10){
				le("should be indexed by date but got $date");
			}
			break;
		}
	}
	/**
	 * @param float $factor
	 * @param QMMeasurement[]|AnonymousMeasurement[] $taggedVariableMeasurements
	 * @param QMUserVariable|QMCommonVariable $taggedVariable
	 * @return array
	 * @throws InvalidVariableValueException
	 * @throws IncompatibleUnitException
	 */
	protected function convertTaggedMeasurements(float $factor, array $taggedVariableMeasurements,
		$taggedVariable): array{
		$convertedMeasurements = [];
		$unit = $this->getCommonUnit();
		$tagUnit = $taggedVariable->getCommonUnit();
		$sameUnitCategory = $unit->getUnitCategory()->getNameAttribute() === $tagUnit->getUnitCategory()->getNameAttribute();
		$sameUnitCategoryAndNotMisc =
			$sameUnitCategory && $unit->getUnitCategory()->getNameAttribute() !== MiscellanyUnitCategory::NAME;
		/** @noinspection TypeUnsafeComparisonInspection */
		$factorOne = $factor == 1;
		$isJoinedDuplicate = $sameUnitCategoryAndNotMisc && $factorOne;
		$commonUnit = $this->getCommonUnit();
		$thisIsCountButTaggedIsNotAndFactorIsOne =
			$factorOne && $commonUnit->isCountCategory() && !$tagUnit->isCountCategory();
		/** @var QMMeasurement $taggedMeasurement */
		foreach($taggedVariableMeasurements as $taggedMeasurement){
			// Need to clone taggedVariableMeasurement or we screw up measurements on global variable
			$m = clone $taggedMeasurement;
			$m->setTaggedVariableMeasurement($taggedMeasurement);
			$m->setTaggedVariable($taggedVariable);
			if($this instanceof QMUserVariable){
				$m->setUserVariable($this);
			}
			$message = "derived from $m->value $taggedVariable->unitAbbreviatedName $taggedVariable->name measurement";
			if($thisIsCountButTaggedIsNotAndFactorIsOne){
				$value = 1;
			} elseif($isJoinedDuplicate){
				$value = QMUnit::convertValueByUnitIds($m->value, $tagUnit->id, $commonUnit->id, $this);
			} else{
				$value = $m->value * $factor;
			}
			$m->valueInCommonUnit = $m->value = $value;
			if(isset($m->variableName)){
				//$m->variableName = $this->name . " ($message)";
				$m->variableName = $this->name;
				$m->getAdditionalMetaData()->addMetaData('tagExplanation', $message);
			}
			$m->unitId = $unit->id;
			$m->unitAbbreviatedName = $unit->abbreviatedName;
			try {
				$this->validateValueForCommonVariableAndUnit($m->value,
					"Converted tag measurement from $taggedVariable->name", $this->durationOfAction, $taggedVariable);
			} catch (InvalidVariableValueException $e) {
				$this->logError(__METHOD__.": ".$e->getMessage());
				continue;
			}
			$convertedMeasurements[] = $m;
		}
		return $convertedMeasurements;
	}
	/**
	 * @return string
	 */
	public function getLatestTaggedMeasurementAt(): ?string{
		$at = $this->l()->latest_tagged_measurement_start_at;
		return $this->latestTaggedMeasurementStartAt = $at;
	}
	/**
	 * @param string|null $reason
	 * @throws AlreadyAnalyzingException
	 */
	abstract public function analyzeFully(string $reason);
	/**
	 * @return QMMeasurement[]
	 */
	public function getValidMeasurementsWithTags(): array{
		return $this->removeInvalidMeasurements($this->getMeasurementsWithTags());
	}
	/**
	 * @param QMMeasurement[] $all
	 * @return QMMeasurement[]
	 */
	public function removeInvalidMeasurements(array $all): array{
		$keep = [];
		$min = $this->getMinimumAllowedValueAttribute();
		$max = $this->getMaximumAllowedValueAttribute();
		foreach($all as $date => $m){
			if($min !== null && $m->value < $min){
				$tooSmall[] = $m;
				continue;
			}
			if($max !== null && $m->value > $max){
				$tooBig[] = $m;
				continue;
			}
			$keep[$date] = $m;
		}
		return $keep;
	}
	/**
	 * @return QMMeasurement[]
	 */
	public function getMeasurementsWithTags(): array{
		$measurements = $this->measurementsWithTags;
		if($measurements !== null){
			return $measurements;
		}
		try {
			return $this->setMeasurementsWithTags();
		} catch (InsufficientMemoryException $e) {
			$this->logError("Returning daily measurements because: " . $e->getMessage());
			return $this->getValidDailyMeasurementsWithTags();
		}
	}
	/**
	 * @return int
	 */
	public function getNumberOfRawMeasurementsWithTagsJoinsChildren(): ?int{
		$all = $this->measurementsWithTags;
		if($all !== null){
			$num = count($all);
			$this->setAttribute(self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN, $num);
			return $this->numberOfRawMeasurementsWithTagsJoinsChildren = count($all);
		}
		if($this->getNumberOfMeasurements() > $this->numberOfRawMeasurementsWithTagsJoinsChildren){
			$this->logError("Why is numberOfMeasurements {$this->getNumberOfMeasurements()} > numberOfRawMeasurementsWithTagsJoinsChildren $this->numberOfRawMeasurementsWithTagsJoinsChildren");
			return $this->getNumberOfMeasurements();  // TODO:  Remove once this has been calculated for all user variables
		}
		return $this->numberOfRawMeasurementsWithTagsJoinsChildren;
	}
	/**
	 * @return int
	 */
	public function getOrCalculateNumberOfRawMeasurementsWithTagsJoinsChildren(): int{
		$number = $this->numberOfRawMeasurementsWithTagsJoinsChildren;
		if($number === null){
			$number = $this->calculateNumberOfRawMeasurementsWithTagsJoinsChildren();
		}
		return $this->numberOfRawMeasurementsWithTagsJoinsChildren = $number;
	}
	/**
	 * @return DailyMeasurement[]
	 */
	public function getValidDailyMeasurementsWithTags(): array{
		$measurements = $this->dailyMeasurementsWithTags;
		if($measurements !== null){
			$this->makeSureWeDontHaveMoreDailyThanRawMeasurements();
			return $measurements;
		}
		return $this->generateValidDailyMeasurementsWithTags();
	}
	/**
	 * @return float[]
	 */
	public function getValidValues(): array{
		$measurements = $this->getQMMeasurements();
		$measurements = $this->removeInvalidMeasurements($measurements);
		$values = [];
		foreach($measurements as $m){
			$values[$m->getStartAt()] = $m->value;
		}
		return $values;
	}
	/**
	 * @return float[]
	 */
	public function getValues(): array{
		$values = $this->values;
		if($values !== null){
			return $values;
		}
		$measurements = $this->getQMMeasurements();
		$values = [];
		foreach($measurements as $m){
			$values[$m->getStartAt()] = $m->value;
		}
		return $this->values = $values;
	}
	/**
	 * @return float[]
	 */
	public function getValidValuesWithTags(): array{
		$measurements = $this->getValidMeasurementsWithTags();
		$values = [];
		foreach($measurements as $m){
			// Can't index by startAt because we have duplicates with tags $startAt = $m->getStartAt();
			//if(isset($values[$startAt])){le('isset($values[$startAt])');}
			$values[] = $m->value;
		}
		return $this->valuesWithTags = $values;
	}
	/**
	 * @return float[]
	 */
	public function getValuesWithTags(): array{
		$values = $this->valuesWithTags;
		if($values !== null){
			return $values;
		}
		$measurements = $this->getMeasurementsWithTags();
		$values = [];
		foreach($measurements as $m){
			// Can't index by startAt because we have duplicates with tags $startAt = $m->getStartAt();
			//if(isset($values[$startAt])){le('isset($values[$startAt])');}
			$values[] = $m->value;
		}
		return $this->valuesWithTags = $values;
	}
	/**
	 * @param $nameOrId
	 * @return int
	 */
	public static function getIdByNameIdOrSynonym($nameOrId): ?int{
		if(!$nameOrId){
			le("No$nameOrId given");
		}
		if(is_numeric($nameOrId)){
			return $nameOrId;
		}
		$userVariable = QMUserVariable::findInMemoryForAnyUserWhereNameOrSynonym($nameOrId);
		if($userVariable){
			return $userVariable->getVariableIdAttribute();
		}
		$variable = QMCommonVariable::findByNameIdOrSynonym($nameOrId);
		if(!$variable){
			return null;
		}
		return $variable->getVariableIdAttribute();
	}
	/**
	 * @param int $id
	 * @param string $reason
	 * @return int
	 */
	public static function setStatusWaitingByVariableId(int $id, string $reason): int{
		return static::writable()->where(static::FIELD_VARIABLE_ID, $id)->update([
			UserVariable::FIELD_STATUS => UserVariableStatusProperty::STATUS_WAITING,
			UserVariable::FIELD_REASON_FOR_ANALYSIS => $reason,
			UserVariable::FIELD_ANALYSIS_REQUESTED_AT => now_at(),
		]);
	}
	/**
	 * @return array
	 */
	public function getMostCommonValuesInCommonUnit(): array{
		$arr = [
			$this->getMostCommonValue(),
			$this->getSecondMostCommonValue(),
			$this->getThirdMostCommonValue(),
		];
		$arr = array_filter($arr, static function($value){
			return $value !== null;
		});
		return $arr;
	}
	/**
	 * @return QMButton[]
	 */
	public function getVoiceOptionButtons(): array{
		$buttons = $this->getNotificationActionButtons();
		if($buttons){
			return $buttons;
		}
		$buttons = $this->getButtons();
		return Arr::where($buttons, static function($button){
			/** @var QMButton $button */
			return !isset($button->stateName);
		});
	}
	/**
	 * @return string
	 */
	public function getEarliestTaggedMeasurementAt(): ?string{
		return $this->earliestTaggedMeasurementAt;
	}
	/**
	 * @param array $values
	 * @return float
	 */
	public function combineValues(array $values): float{
		$sum = $this->isSum();
		if($sum){
			$combined = Stats::sum($values);
		} else{
			$combined = Stats::average($values);
		}
		return $combined;
	}
	/**
	 * @param bool $dryRun
	 * @param bool $hardDelete
	 * @return array
	 */
	private function deleteMeasurementsThatAreTooBig(bool $dryRun, bool $hardDelete): array{
		$userIds = [];
		$max = $this->getMaximumAllowedValueAttribute();
		if($max !== null){
			$qb = QMMeasurement::writable()->where(Measurement::FIELD_VARIABLE_ID, $this->getVariableIdAttribute())
				->where(Measurement::FIELD_VALUE, ">", $max);
			$result = $qb->getArray();
			foreach($result as $row){
				$userIds[] = $row->user_id;
				$this->logInfoWithoutObfuscation("Value $row->value created $row->created_at is too big!");
			}
			if(!$dryRun && $userIds){
				$reason = "Max for $this->name is $max " . $this->getUnitAbbreviatedName();
				if($hardDelete){
					$result = $qb->hardDelete($reason, true);
				} else{
					$result = $qb->softDelete([], $reason);
				}
			}
		}
		return array_unique($userIds);
	}
	/**
	 * @param bool $dryRun
	 * @param bool $hardDelete
	 * @return array
	 */
	private function deleteMeasurementsThatAreTooSmall(bool $dryRun, bool $hardDelete): array{
		$userIds = [];
		$min = $this->getMinimumAllowedValueAttribute();
		if($min !== null){
			$qb = QMMeasurement::writable()->where(Measurement::FIELD_VARIABLE_ID, $this->getVariableIdAttribute())
				->where(Measurement::FIELD_VALUE, "<", $min);
			$result = $qb->getArray();
			foreach($result as $row){
				$userIds[] = $row->user_id;
				$this->logInfoWithoutObfuscation("Value $row->value created $row->created_at is too small!");
			}
			if(!$dryRun && $userIds){
				$reason = "Min for $this->name is $min " . $this->getUnitAbbreviatedName();
				if($hardDelete){
					$qb->hardDelete($reason, true);
				} else{
					$qb->softDelete([], $reason);
				}
			}
		}
		return array_unique($userIds);
	}
	/**
	 * @return UserVariableChartGroup|VariableChartChartGroup
	 */
	abstract public function setCharts(): ChartGroup;
	/**
	 * @return UserVariableChartGroup|VariableChartChartGroup
	 */
	abstract public function getChartGroup(): ChartGroup;
	/**
	 * @param array $arr
	 * @param string|null $reason
	 * @return int
	 * @deprecated Use Eloquent model save directly
	 */
	public function updateDbRow(array $arr, string $reason = null): int{
		$fields = static::getAnalysisSettingsFields();
		foreach($fields as $field){
			if(isset($arr[$field])){
				$arr[self::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT] = now_at();
				if(!AppMode::isApiRequest() && $this instanceof QMUserVariable){
					le("Why are we updating analysis settings in a non-API request? Reason: $reason");
				}
			}
		}
		$res = parent::updateDbRow($arr, $reason);
		return $res;
	}
	/**
	 * @return array
	 */
	public static function getAnalysisSettingsProperties(): array{
		$properties = (new static())->getPropertyModels();
		return Arr::where($properties, function($p){
			return $p->isHyperParameter ?? false;
		});
	}
	/**
	 * @return array
	 */
	public static function getAnalysisSettingsFields(): array{
		return [ // TODO: get from getAnalysisSettingsProperties
			self::FIELD_CAUSE_ONLY,
			self::FIELD_COMBINATION_OPERATION,
			self::FIELD_DURATION_OF_ACTION,
			self::FIELD_FILLING_VALUE,
			self::FIELD_MAXIMUM_ALLOWED_VALUE,
			self::FIELD_MINIMUM_ALLOWED_VALUE,
			self::FIELD_ONSET_DELAY,
		];
	}
	/**
	 * @return string
	 */
	public function getNewestDataAt(): ?string{
		$at = $this->getLatestTaggedMeasurementAt();
		if(!$at){
			return null;
		}
		return $at;
	}
	/**
	 * @return array
	 */
	public function getSpreadsheetRows(): array{
		$measurements = $this->getMeasurementsWithTags();
		$rows = [];
		foreach($measurements as $measurement){
			$row = $measurement->toSpreadsheetRow();
			$row["Variable"] = $this->getVariableName();
			$row["Unit"] = $this->getUnitAbbreviatedName();
			$rows[] = $row;
		}
		return $rows;
	}
	/**
	 * @param float $value
	 * @return bool
	 */
	public function skipInvalidZeroValueMeasurements(float $value): bool{
		if($value === (float)0){
			if(in_array($this->name, [
				WalkOrRunDistanceCommonVariable::NAME,
				CaloricIntakeCommonVariable::NAME,
				BodyFatCommonVariable::NAME,
				SleepDurationCommonVariable::NAME,
			])){
				return true;
			}
		}
		return false;
	}
	/**
	 * @return string
	 */
	public function calculateLatestTaggedMeasurementAt(): ?string{
		return BaseLatestTaggedMeasurementStartAtProperty::calculate($this);
	}
	/**
	 * @return string
	 */
	public function calculateEarliestTaggedMeasurementAt(): ?string{
		return BaseEarliestTaggedMeasurementStartAtProperty::calculate($this);
	}
	/**
	 * @return AnonymousMeasurement|null
	 */
	public function getLastRawNonTaggedMeasurement(): ?AnonymousMeasurement{
		$measurements = $this->getQMMeasurements();
		if(!$measurements){
			return null;
		}
		$last = end($measurements);
		return $last;
	}
	/**
	 * @param int $connectorId
	 * @return QMMeasurement[]
	 */
	public function getRawMeasurementsForConnector(int $connectorId): array{
		$all = $this->getQMMeasurements();
		return Arr::where($all, function($m) use ($connectorId){
			/** @var QMMeasurement $m */
			return $m->connectorId === $connectorId;
		});
	}
	/**
	 * @param string $predictorVariableCategoryName
	 * @param int $limit
	 * @return Collection|UserVariableRelationship[]
	 */
	public function getCorrelationsForPredictorCategory(string $predictorVariableCategoryName,
		int $limit = 0): Collection{
		if(isset($this->correlationsForPredictorCategory[$predictorVariableCategoryName][$limit])){
			return $this->correlationsForPredictorCategory[$predictorVariableCategoryName][$limit];
		}
		return $this->setCorrelationsForPredictorCategory($predictorVariableCategoryName, $limit);
	}
	/**
	 * @param string $predictorVariableCategoryName
	 * @param int $limit
	 * @return Collection|UserVariableRelationship[]
	 */
	abstract protected function setCorrelationsForPredictorCategory(string $predictorVariableCategoryName,
		int $limit = 0): Collection;
	/**
	 * @inheritDoc
	 */
	public function getSourceObjects(): array{
		return $this->getMeasurementsWithTags();
	}
	protected function makeSureWeDontHaveMoreDailyThanRawMeasurements(): void{
		$daily = $this->dailyMeasurementsWithTags;
		$hasFilling = $this->hasFillingValue();
		if($daily && !$hasFilling){
			$raw = $this->measurementsWithTags;
			if($raw !== null){
				$numberRaw = count($raw);
				$numberDaily = count($daily);
				if($numberRaw < $numberDaily){
					le("We have $numberDaily daily measurements but only $numberRaw raw measurements");
				}
			}
		}
	}
	abstract public function hasFillingValue(): bool;
	/**
	 * @param null $apiVersionNumber
	 * @return array
	 */
	public static function getLegacyPropertiesToAdd($apiVersionNumber = null): array{
		// legacy => current
		if(!isset($apiVersionNumber)){
			$apiVersionNumber = APIHelper::getApiVersion();
		}
		if($apiVersionNumber < 3){
			return [ // Legacy => Current
				'earliestMeasurementTime' => 'earliestTaggedMeasurementTime',
				'latestMeasurementTime' => 'latestTaggedMeasurementTime',
				'category' => 'variableCategoryName',
				'originalName' => 'name',
				'abbreviatedUnitName' => 'unitAbbreviatedName',
			];
		}
		return [];
	}
	/**
	 * @param $categoryNameOrId
	 * @return QMQB
	 */
	public static function whereCategory($categoryNameOrId): QMQB{
		$c = QMVariableCategory::find($categoryNameOrId);
		return static::qb()->where(Variable::TABLE . '.' . Variable::FIELD_VARIABLE_CATEGORY_ID,
			$c->id);
	}
	/**
	 * @param bool $embedCharts
	 * @return string
	 * @throws \App\Exceptions\HighchartExportException
	 */
	public function getChartAndTableHTML(bool $embedCharts): string{
		$c = $this->getChartGroup();
		if($embedCharts){
			try {
				$html = $c->getChartHtmlWithEmbeddedImages();
			} catch (NotEnoughDataException $e) {
				$html = "
                <h4 class=\"text-2xl font-semibold\">$this->displayName Charts</h4>
                " . ExceptionHandler::renderHtml($e);
				QMLog::error(__METHOD__.": ".$e->getMessage());
			}
		} else{
			$html = "<div>" . $c->getChartHtmlWithLinkedImages() . "</div>";
		}
		$html .= "
            <div id=\"variable-statistics-table\" style=\"text-align: center;\">
                " . $this->getStatisticsTableHtml() . "
            </div>";
		return $html;
	}
	/**
	 * @return string
	 */
	public function getReportTitleAttribute(): string{
		return $this->getOrSetVariableDisplayName() . " Overview for Entire Population";
	}
	/**
	 * @return string
	 */
	public function getSubtitleAttribute(): string{
		$str = VariableDescriptionProperty::generateVariableDescription($this);
		return $str;
	}
	/**
	 * @return string
	 */
	public function getCategoryDescription(): string{
		return Variable::CLASS_DESCRIPTION;
	}
	public function logMeasurementTable(){
		$measurements = $this->getQMMeasurements();
		QMLog::table($measurements, "All Raw Measurements");
	}
	/**
	 * @inheritDoc
	 */
	public function getParentCategoryName(): ?string{
		return WpPost::PARENT_CATEGORY_VARIABLE_OVERVIEWS;
	}
	/**
	 * @return string
	 */
	abstract public function getDataQuantityHTML(): string;
	/**
	 * @return string
	 */
	public function getMeasurementQuantitySentence(): string{
		$sentence = strtoupper($this->getOrSetVariableDisplayName()) . " Data Quantity:\n";
		$numberTagged = $this->getOrCalculateNumberOfRawMeasurementsWithTagsJoinsChildren();
		if(!$numberTagged){
			$sentence .= $this->getNumberOfTaggedMeasurementsSentence();
		} else{
			$sentence .= $this->getNumberOfRawMeasurementsSentence() . "\n";
			$sentence .= $this->getNumberOfTaggedMeasurementsSentence() . "\n";
		}
		if($numberTagged){
			$sentence .= $this->getNumberOfChangesSentence();
		}
		return $this->dataQuantitySentence = $sentence;
	}
	public function unsetCorrelations(){
		foreach($this as $key => $value){
			if(stripos($key, 'correlation') !== false && stripos($key, 'number') === false){
				if($value !== null){
					$this->logInfo("Unsetting $key...");
					$this->$key = null;
				}
			}
		}
		$l = $this->l();
		$relations = $l->getRelations();
		foreach($relations as $key => $value){
			if(stripos($key, 'correlation') !== false){
				if($value !== null){
					$l->logInfo("Unsetting $key...");
					$l->setRelation($key, null);
				}
			}
		}
		if($this->userVariableRelationshipsAsEffect){
			le('$this->userVariableRelationshipsAsEffect');
		}
	}
	public function removeRecursion(){
		parent::removeRecursion();
		foreach($this as $key => $value){
			if(is_array($value) && isset($value[0])){
				$value = $value[0];
			}
			if($value instanceof QMVariableRelationship){
				$value->unsetVariables();
			}
		}
	}
	public function getUserUnitAbbreviatedName(): string{
		return $this->getuserUnit()->abbreviatedName;
	}
	public function getUserUnit(): ?QMUnit{
		return $this->getUserOrCommonUnit();
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return UserVariableRelationship[]|Collection
	 */
	public function getCorrelationsAsEffect(int $limit = null, string $variableCategoryName = null){
		$correlations = $this->userVariableRelationshipsAsEffect;
		if($correlations === null){
			$correlations = $this->setUserVariableRelationshipsAsEffect($limit, $variableCategoryName);
		}
		if($variableCategoryName){
			return HasCauseAndEffect::filterByCauseCategory($correlations, $variableCategoryName);
		}
		return $this->userVariableRelationshipsAsEffect = $correlations;
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return UserVariableRelationship[]|Collection
	 */
	public function getCorrelationsAsCause(int $limit = null, string $variableCategoryName = null){
		$correlations = $this->userVariableRelationshipsAsCause;
		if($correlations === null){
			$correlations = $this->setUserVariableRelationshipsAsCause();
		}
		if($variableCategoryName){
			return HasCauseAndEffect::filterByEffectCategory($correlations, $variableCategoryName);
		}
		return $this->userVariableRelationshipsAsCause = $correlations;
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return UserVariableRelationship[]|Collection
	 */
	public function setUserVariableRelationshipsAsCause(int $limit = null, string $variableCategoryName = null){
		$qb = UserVariableRelationship::whereUserId($this->getUserId())
			->where(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID, $this->getVariableIdAttribute())->limit(0);
		if($variableCategoryName){
			$qb->where(UserVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID, VariableCategoryIdProperty::findByName($variableCategoryName));
		}
		// Slows down query UserVariableRelationship::applyDefaultOrderings($qb);
		$correlations = $qb->get();
		$correlations = $correlations->sortByDesc(UserVariableRelationship::FIELD_QM_SCORE);
		return $this->userVariableRelationshipsAsCause = $correlations;
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return UserVariableRelationship[]|Collection
	 */
	public function setUserVariableRelationshipsAsEffect(int $limit = null, string $variableCategoryName = null){
		$this->logInfo(__FUNCTION__ . " limit $limit");
		$qb = UserVariableRelationship::whereUserId($this->getUserId())
			->where(UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID, $this->getVariableIdAttribute())->limit($limit);
		if($variableCategoryName){
			$qb->where(UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID, VariableCategoryIdProperty::findByName($variableCategoryName));
		}
		// Slows down query UserVariableRelationship::applyDefaultOrderings($qb);
		$correlations = $qb->get();
		$correlations = $correlations->sortByDesc(UserVariableRelationship::FIELD_QM_SCORE);
		return $this->userVariableRelationshipsAsEffect = $correlations;
	}
	/**
	 * @return UserVariableRelationship[]|Collection
	 */
	public function getUserVariableRelationships(){
		$cause = $this->getCorrelationsAsCause();
		$effect = $this->getCorrelationsAsEffect();
		return $cause->merge($effect);
	}
	/**
	 * @return ChartGroup|null
	 * @throws \App\Exceptions\DuplicateFailedAnalysisException
	 * @throws \App\Exceptions\ModelValidationException
	 * @throws \App\Exceptions\NotEnoughDataException
	 * @throws \App\Exceptions\StupidVariableNameException
	 * @throws \App\Exceptions\TooSlowToAnalyzeException
	 */
	public function generateAndSaveChartsIfNecessary(): ?ChartGroup{
		if(!$this->getNumberOfRawMeasurementsWithTagsJoinsChildren()){
			$this->logInfo("No Measurements for charts!");
			return null;
		}
		if(!$this->highchartsPopulated()){
			try {
				$this->analyzeFullyAndSave("Need charts");
			} catch (AlreadyAnalyzingException | AlreadyAnalyzedException $e) {
				$this->logError(__METHOD__.": ".$e->getMessage());
			}
		}
		if(!$this->highchartsPopulated()){
			le("Charts still not populated!");
		}
		return $this->getChartGroup();
	}
	public function cleanup(){
		throw new LogicException(__FUNCTION__ . " not implemented for " . static::class);
	}
	/**
	 * @param int $newVariableCategoryId
	 * @return mixed
	 */
	abstract public function changeVariableCategory(int $newVariableCategoryId);
	abstract public function getDeleteSmallMeasurementsUrl(): string;
	abstract public function getDeleteLargeMeasurementsUrl(): string;
	abstract public function getMeasurementsUrl(): string;
	/** @noinspection PhpUnused */
	public function renderCorrelationsTable(): string{
		$tableId = 'data-table-id';
		$correlations = $this->getOutcomesOrPredictors(100);
		$me = $this;
		$html = Table::instance()->setData($correlations)->attr('table', 'id', $tableId)
				->attr('table', 'class', 'table table-bordered table-striped table-hover')
				->attr('table', 'cellspacing', '0')->attr('table', 'width', '100%')
				//->attr('table', 'style', 'width: 100%; table-layout: fixed;')
				->column()->filter()->title('Variable')->value(function($row) use ($me){
					/** @var QMVariableRelationship $row */
					if($row->causeVariableId === $me->id){
						$name = $row->effectNameWithSuffix();
						$id = $row->effectVariableId;
					} else{
						$name = $row->causeNameWithSuffix();
						$id = $row->causeVariableId;
					}
					/** @var QMVariableRelationship $row */
					$url = Variable::generateShowUrl($id);
					return '<a href="' . $url . '"
                        style="cursor: pointer;">' . $name . '</a>';
				})->attr('td', 'style', 'width: 75%;')->css('td', 'width', '80%')->attr('td', 'width', '80%')->add()
				->column()->title('Effect')->value(function($row) use ($me){
					/** @var QMVariableRelationship $row */
					return $row->getEffectSizeLinkToStudyWithExplanation();
				})
				//->css('td', 'color', 'red')
				->css('td', 'width', '5%')->attr('td', 'width', '5%')->add()->column()->title('effectSize')
				->value('effectSize')
				//->css('td', 'color', 'red')
				->css('td', 'width', '5%')->attr('td', 'width', '5%')->add()
				//            ->column()
				//            ->title('causeVariableName')
				//            ->value('causeVariableName')
				//            ->css('td', 'width', '5%')
				//            ->attr('td', 'width', '5%')
				//            ->add()
				//            ->column()
				//            ->title('effectVariableName')
				//            ->value('effectVariableName')
				//            ->css('td', 'width', '5%')
				//            ->attr('td', 'width', '5%')
				//            ->add()
				// TODO: Maybe implement explain
				->column()
				//                ->value(function ($row) {
				//                    return '<a href="https://local.quantimo.do/sql/explain?sql='.
				//                        urlencode($row->sql_text).
				//                        '">Full Query</a>';
				//                })
				->value(function($row) use ($tableId){
					/** @var QMVariableRelationship $row */
					$url = $row->getInteractiveStudyUrl();
					return '<a href="' . $url . '"
                        style="cursor: pointer;">Full Study</a>';
				})->css('td', 'color', 'blue')->css('td', 'width', '5%')->attr('td', 'width', '5%')->add()
				->render(true) . "

                <script>
                    $(document).ready( function () {
                        $('#data-table-id').DataTable({
                            \"pageLength\": 50,
                            \"order\": [[ 0, \"desc\" ]] // Descending duration
                        });
                    } );
                </script>

            ";
		return $html;
	}
	public function isCause(): bool{
		return $this->isPredictor();
	}
	abstract public function getMaximumDailyValue(): float;
	abstract public function getMinimumDailyValue(): float;
	public function getSpread(): float{
		$max = $this->getMaximumDailyValue();
		$min = $this->getMinimumDailyValue();
		$spread = $max - $min;
		if(!$spread){
			le("No diff between min: $min and max: $max");
		}
		return $spread;
	}
	abstract public function getFillingValueAttribute(): ?float;
	public function getFillingTypeAttribute(): string{
		if($t = $this->fillingType){
			return $t;
		}
		$type = $this->getAttributeFromVariableOrUnit(Variable::FIELD_FILLING_TYPE);
		if($type === null){
			$type = BaseFillingTypeProperty::fromValue($this->fillingValue);
		}
		$val = BaseFillingValueProperty::fromType($type);
		$this->setFillingValue($val);
		return $this->fillingType = $type;
	}
	/**
	 * @param string $attribute
	 * @return mixed|null
	 * Don't rename this or laravel will try to parse as an accessor
	 */
	public function getAttributeFromVariableOrCategory(string $attribute){
		$val = $this->getAttributeFromCommonVariable($attribute);
		if($val !== null){
			return $val;
		}
		return $this->getQMVariableCategory()->getAttribute($attribute);
	}
	/**
	 * @param string $attribute
	 * @return mixed|null
	 * Don't rename this or laravel will try to parse as an accessor
	 */
	public function getAttributeFromVariableOrUnit(string $attribute){
		$val = $this->getAttributeFromCommonVariable($attribute);
		if($val !== null){
			return $val;
		}
		return $this->getCommonUnit()->getAttribute($attribute);
	}
	/**
	 * @param string $attribute
	 * @return mixed|null
	 * Don't rename this or laravel will try to parse as an accessor
	 */
	public function getAttributeFromCommonVariable(string $attribute){
		$cv = ObjectHelper::get($this, ['commonVariable']);
		if($this instanceof QMCommonVariable){
			$cv = $this;
		}
		if($cv){
			return $cv->getAttribute($attribute);
		}
		if($this instanceof QMUserVariable && $this->laravelModel){
			$l = $this->l();
			if(!$l->relationLoaded('variable')){
				if($v = $l->getVariable()){
					return $v->getAttribute($attribute);
				}
			}
		}
		return null;
	}
	/**
	 * @return array
	 */
	abstract public function setCommonTaggedRows(): array;
	/**
	 * @return array
	 */
	public function getCommonTaggedRows(): array{
		$rows = $this->commonTaggedRows;
		if($rows !== null){
			return $rows;
		}
		return $this->setCommonTaggedRows();
	}
	/**
	 * @return array
	 */
	public function getCommonTagRows(): array{
		if($this->commonTagRows !== null){
			return $this->commonTagRows;
		}
		return $this->setCommonTagRows();
	}
	/**
	 * @return QMCommonVariable[]|QMVariable[]|QMUserVariable[]
	 */
	public function getCommonTagVariables(): array{
		$tagVars = $this->commonTagVariables;
		if($tagVars === null){
			$tagVars = $this->setCommonTagVariables();
		}
		$this->setNumberOfCommonTags(count($tagVars));
		$clones = [];
		/** @var QMVariable $v */
		foreach($tagVars as $v){
			if($fixRecursionInController = true){
				$clone = $v;
			} else{
				$clone = $v->cloneAndRemoveRecursion();
			}
			$this->setTagDisplayTextOnTagVariable($clone);
			$clones[] = $clone;
		}
		$this->verifyJsonEncodableAndNonRecursive();
		$this->commonTagVariables = $clones;
		$this->verifyJsonEncodableAndNonRecursive();
		return $clones;
	}
	/**
	 * @return QMVariable[]
	 */
	abstract public function setCommonTagVariables(): array;
	/**
	 * @return QMUserVariable[]|QMCommonVariable[]
	 */
	public function getCommonTaggedVariables(): array{
		$taggedVars = $this->commonTaggedVariables;
		if($taggedVars !== null){
			return $taggedVars;
		}
		$taggedVars = $this->setCommonTaggedVariables();
		$this->setNumberCommonTaggedBy(count($taggedVars));
		$formatted = [];
		foreach($taggedVars as $tagged){
			if($tagged->tagConversionFactor === null){
				le("tagConversionFactor not set on $tagged");
			}
			$tagged = static::instantiateIfNecessary($tagged);
			if($tagged->tagConversionFactor === null){
				le("tagConversionFactor not set on $tagged");
			}
			$this->setTagDisplayTextOnTaggedVariable($tagged);
			$formatted[] = $tagged;
		}
		return $this->commonTaggedVariables = $formatted;
	}
	/**
	 * @return array
	 */
	public function setCommonTagRows(): array{
		$rows = QMCommonTag::readonly()->whereRaw(QMCommonTag::FIELD_TAG_VARIABLE_ID . ' <> ' . $this->variableId)
			->where(QMCommonTag::FIELD_TAGGED_VARIABLE_ID, $this->variableId)->getArray();
		if(!$rows){
			$rows = [];
		}
		return $this->commonTagRows = $rows;
	}
	/**
	 * @return float
	 */
	public function getMinimumAllowedValueAttribute(): ?float{
		if($this->minimumAllowedValue !== null){
			return $this->minimumAllowedValue;
		}
		$max = $this->getVariable()->getMinimumAllowedValueAttribute();
		return $this->setMinimumAllowedValue($max);
	}
	/**
	 * @return float
	 */
	public function getMaximumAllowedValueAttribute(): ?float{
		if($this->maximumAllowedValue !== null){
			return $this->maximumAllowedValue;
		}
		$max = $this->getVariable()->getMaximumAllowedValueAttribute();
		return $this->setMaximumAllowedValue($max);
	}
	/** @noinspection PhpUnused */
	public function setEarliestTaggedMeasurementStartAtAttribute($value){
		$this->earliestTaggedMeasurementTime = time_or_null($value);
		$at = date_or_null($value);
		$this->earliestTaggedMeasurementAt = $at;
		$this->earliestTaggedMeasurementStartAt = $at;
		$this->setAttribute(self::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT, $at);
	}
	/** @noinspection PhpUnused */
	public function setLatestTaggedMeasurementStartAtAttribute($value){
		$this->latestTaggedMeasurementTime = time_or_null($value);
		$at = date_or_null($value);
		$this->latestTaggedMeasurementStartAt = $at;
		$this->latestTaggedMeasurementStartAt = $at;
		$this->setAttribute(self::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT, $at);
	}
	/** @noinspection PhpUnused */
	public function setEarliestNonTaggedMeasurementStartAtAttribute($value){
		$this->earliestNonTaggedMeasurementTime = time_or_null($value);
		$at = date_or_null($value);
		$this->setAttribute(self::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT, $at);
	}
	/** @noinspection PhpUnused */
	public function setLatestNonTaggedMeasurementStartAtAttribute($value){
		$this->latestNonTaggedMeasurementTime = time_or_null($value);
		$at = date_or_null($value);
		$this->setAttribute(self::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT, $at);
	}
	/**
	 * @return float[]
	 */
	public function getDailyValues(): array{
		if($this->dailyValues){
			return $this->dailyValues;
		}
		$measurements = $this->getDailyMeasurementsWithoutTagsOrFilling();
		$values = [];
		foreach($measurements as $m){
			$values[$m->getDate()] = $m->value;
		}
		return $this->dailyValues = $values;
	}
	public function getAverage(): float{
		$val = $this->getMean();
		if($val === null){
			le('$val === null');
		}
		return $val;
	}
	/**
	 * @param int $periodInDays
	 * @return array
	 */
	public function getMovingAverage(int $periodInDays): array{
		if($maByDate = $this->movingAverages[$periodInDays] ?? []){
			return $maByDate;
		}
		$values = $this->getDailyValues();
		$dates = array_keys($values);
		$ma = Stats::movingAverage($values, $periodInDays);
		foreach($dates as $i => $date){
			if($i < $periodInDays){
				continue;
			}
			$date = TimeHelper::YYYYmmddd($date);
			$maByDate[$date] = $ma[$i - $periodInDays];
		}
		return $this->movingAverages[$periodInDays] = $maByDate;
	}
	/**
	 * @param int $periodInDays
	 * @param int|string $timeAt
	 * @return float
	 */
	public function getMovingAverageValue(int $periodInDays, $timeAt): ?float{
		$date = TimeHelper::YYYYmmddd($timeAt);
		$ma = $this->getMovingAverage($periodInDays);
		return $ma[$date] ?? null;
	}
	/**
	 * @return string[]
	 */
	public function getKeyWords(): array{
		return $this->getSynonymsAttribute();
	}
	public function unsetOutcomes(){
		$this->aggregateCorrelationsAsCause = null;
	}
	public function unsetPredictors(){
		$this->aggregateCorrelationsAsEffect = null;
		$this->correlationsForPredictorCategory = null;
	}
	public function getNumberOfUsers(): int{
		return $this->getVariable()->getNumberOfUserVariables();
	}
	/**
	 * @param float $valueInCommonUnit
	 * @param string $type
	 * @throws \App\Exceptions\InvalidVariableValueException
	 */
	public function validateDailyValue(float $valueInCommonUnit, string $type): void {
		$this->validateValueForCommonVariableAndUnit($valueInCommonUnit, $type, 86400, $this);
	}
	private function calculateNumberOfChanges(){
		return UserVariableNumberOfChangesProperty::calculate($this);
	}
}
