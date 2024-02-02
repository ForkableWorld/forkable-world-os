<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
/** @noinspection ArgumentEqualsDefaultValueInspection */
/** @noinspection NullPointerExceptionInspection */
/** @noinspection SlowArrayOperationsInLoopInspection */
namespace App\VariableRelationships;
use App\Buttons\States\VariableSettingsStateButton;
use App\Charts\ChartGroup;
use App\Charts\CorrelationCharts\CorrelationChartGroup;
use App\Charts\QMHighcharts\CorrelationsOverDurationsOfActionHighchart;
use App\Charts\QMHighcharts\CorrelationsOverOnsetDelaysHighchart;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Computers\ThisComputer;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\AnalysisException;
use App\Exceptions\DuplicateFailedAnalysisException;
use App\Exceptions\InsufficientMemoryException;
use App\Exceptions\InsufficientVarianceException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoDeviceTokensException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NotEnoughMeasurementsForCorrelationException;
use App\Exceptions\NotEnoughOverlappingDataException;
use App\Exceptions\NoUserVariableRelationshipsToAggregateException;
use App\Exceptions\StupidVariableException;
use App\Exceptions\StupidVariableNameException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\UserVariableRelationshipNotFoundException;
use App\Files\FileHelper;
use App\Logging\QMClockwork;
use App\Logging\QMLog;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Models\Study;
use App\Models\UserVariable;
use App\Models\Vote;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipDataSourceNameProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipStatusProperty;
use App\Properties\UserVariableRelationship\CorrelationAverageDailyHighCauseProperty;
use App\Properties\UserVariableRelationship\CorrelationAverageDailyLowCauseProperty;
use App\Properties\UserVariableRelationship\CorrelationAverageEffectFollowingHighCauseProperty;
use App\Properties\UserVariableRelationship\CorrelationAverageEffectFollowingLowCauseProperty;
use App\Properties\UserVariableRelationship\CorrelationAverageEffectProperty;
use App\Properties\UserVariableRelationship\CorrelationAverageForwardPearsonCorrelationOverOnsetDelaysProperty;
use App\Properties\UserVariableRelationship\CorrelationAverageReversePearsonCorrelationOverOnsetDelaysProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseBaselineAveragePerDayProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseBaselineAveragePerDurationOfActionProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseChangesProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseNumberOfProcessedDailyMeasurementsProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseNumberOfRawMeasurementsProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseTreatmentAveragePerDayProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseTreatmentAveragePerDurationOfActionProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseUnitIdProperty;
use App\Properties\UserVariableRelationship\CorrelationConfidenceIntervalProperty;
use App\Properties\UserVariableRelationship\CorrelationConfidenceLevelProperty;
use App\Properties\UserVariableRelationship\CorrelationCorrelationsOverDelaysProperty;
use App\Properties\UserVariableRelationship\CorrelationCorrelationsOverDurationsProperty;
use App\Properties\UserVariableRelationship\CorrelationEffectChangesProperty;
use App\Properties\UserVariableRelationship\CorrelationEffectNumberOfProcessedDailyMeasurementsProperty;
use App\Properties\UserVariableRelationship\CorrelationExperimentEndAtProperty;
use App\Properties\UserVariableRelationship\CorrelationExperimentStartAtProperty;
use App\Properties\UserVariableRelationship\CorrelationForwardPearsonCorrelationCoefficientProperty;
use App\Properties\UserVariableRelationship\CorrelationForwardSpearmanCorrelationCoefficientProperty;
use App\Properties\UserVariableRelationship\CorrelationGroupedCauseValueClosestToValuePredictingHighOutcomeProperty;
use App\Properties\UserVariableRelationship\CorrelationGroupedCauseValueClosestToValuePredictingLowOutcomeProperty;
use App\Properties\UserVariableRelationship\CorrelationNumberOfDaysProperty;
use App\Properties\UserVariableRelationship\CorrelationNumberOfPairsProperty;
use App\Properties\UserVariableRelationship\CorrelationOnsetDelayWithStrongestPearsonCorrelationProperty;
use App\Properties\UserVariableRelationship\CorrelationOptimalPearsonProductProperty;
use App\Properties\UserVariableRelationship\CorrelationPearsonCorrelationWithNoOnsetDelayProperty;
use App\Properties\UserVariableRelationship\CorrelationPredictivePearsonCorrelationCoefficientProperty;
use App\Properties\UserVariableRelationship\CorrelationPredictsHighEffectChangeProperty;
use App\Properties\UserVariableRelationship\CorrelationPredictsLowEffectChangeProperty;
use App\Properties\UserVariableRelationship\CorrelationQmScoreProperty;
use App\Properties\UserVariableRelationship\CorrelationRelationshipProperty;
use App\Properties\UserVariableRelationship\CorrelationReversePearsonCorrelationCoefficientProperty;
use App\Properties\UserVariableRelationship\CorrelationStatisticalSignificanceProperty;
use App\Properties\UserVariableRelationship\CorrelationStrengthLevelProperty;
use App\Properties\UserVariableRelationship\CorrelationStrongestPearsonCorrelationCoefficientProperty;
use App\Properties\UserVariableRelationship\CorrelationValuePredictingHighOutcomeProperty;
use App\Properties\UserVariableRelationship\CorrelationValuePredictingLowOutcomeProperty;
use App\Properties\UserVariableRelationship\CorrelationZScoreProperty;
use App\Properties\Measurement\MeasurementStartTimeProperty;
use App\Properties\Study\StudyTypeProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\UserVariable\UserVariableBestCauseVariableIdProperty;
use App\Properties\UserVariable\UserVariableBestEffectVariableIdProperty;
use App\Properties\UserVariable\UserVariableOptimalValueMessageProperty;
use App\Repos\ResponsesRepo;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Measurement\AnonymousMeasurement;
use App\Slim\Model\Measurement\DailyMeasurement;
use App\Slim\Model\Measurement\Pair;
use App\Slim\Model\Measurement\ProcessedQMMeasurement;
use App\Slim\Model\Notifications\CorrelationPushNotificationData;
use App\Slim\Model\QMUnit;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\Pair\GetPairRequest;
use App\Slim\View\Request\QMRequest;
use App\Slim\View\Request\Variable\SearchVariableRequest;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Storage\QueryBuilderHelper;
use App\Studies\QMStudy;
use App\Studies\QMUserStudy;
use App\Tables\TableCell;
use App\Traits\HasModel\HasGlobalVariableRelationship;
use App\Traits\HasModel\HasUserCauseAndEffect;
use App\Traits\HasProperty\HasOnsetAndDuration;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\HtmlHelper;
use App\UI\QMColor;
use App\Utils\APIHelper;
use App\Utils\AppMode;
use App\Utils\QMAPIValidator;
use App\Utils\Stats;
use App\VariableCategories\EconomicIndicatorsVariableCategory;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use App\Variables\QMVariableCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use LogicException;
use Tests\TestGenerators\StagingJobTestFile;
use Throwable;
/** Class UserVariableRelationship
 * @package App\Slim\Model
 */
class QMUserVariableRelationship extends QMVariableRelationship {
    use HasGlobalVariableRelationship, HasOnsetAndDuration, HasUserCauseAndEffect;
    //private $user;  // Just get global user instead, otherwise there will be duplicates and we can use the user as a storage object
    private $aggregatedCorrelation;
    private $causeMeasurements;
    private $causeValues;
    private $effectMeasurements;
    private $effectValuesExpectedToBeHigherThanAverage;
    private $effectValuesExpectedToBeLowerThanAverage;
    private $highEffectCutoff;
    private $lowEffectCutoff;
    private $maximumEffectValue;
    private $minimumEffectValue;
    private $pairs;
    private $pairsBasedOnDailyCauseValues;
    private static $storageQueue;
    protected $boring;
    protected $numberOfDownVotes;
    protected $numberOfUpVotes;
    protected $obvious;
    protected $outcomeIsGoal;
    protected $plausiblyCausal;
    protected $predictorIsControllable;
    protected $relationship;
    protected $sortOrder;
    public $aggregateCorrelationId;
    public $aggregatedAt;
    public $allPairsSignificance;
    public $causalityFactor;
    public $causalityVote;
    public $causeChanges;
    public $causeChangesStatisticalSignificance;
    public $causeFillingValue;
    public $causeLatestTaggedMeasurementTime;
    public $causeNumberOfProcessedDailyMeasurements;
    public $causeNumberOfRawMeasurements;
    public $causeUnitId;
    public $causeUserVariableId;
    public $causeVariableUserUnit;
    public $causeVariableUserUnitId;
    public float $correlationCoefficient;
    public $correlationsOverDelays;
    public $correlationsOverDurations;
    public $durationOfAction;
    public $earliestMeasurementStartAt;
    public $effectChanges;
    public $effectFillingValue;
    public $effectNumberOfProcessedDailyMeasurements;
    public $effectNumberOfRawMeasurements;
    public $effectUserVariableId;
    public $effectValueSpread;
    public $effectVariableIsOutcomeOfInterest;
    public $effectVariableUserUnitId;
    public $experimentEndAt;
    public $experimentStartAt;
    public float $forwardSpearmanCorrelationCoefficient;
    public $lastProcessedDailyValueForCauseInCommonUnit;
    public $lastProcessedDailyValueForCauseInUserUnit;
    public $latestMeasurementStartAt;
    public $numberOfDays;
    public $numberOfDaysSignificance;
    public $numberOfPairs;
    public $onsetDelay;
    public $optimalChangeSpread;
    public $optimalChangeSpreadSignificance;
    public float $predictivePearsonCorrelationCoefficient;
    public $qmScore;
    public $rawCauseMeasurementSignificance;
    public $rawEffectMeasurementSignificance;
    public $usefulnessVote;
    public $userId;
    public $voteStatisticalSignificance;
    public const ALGORITHM_MODIFIED_AT = "2021-03-21";
    public const CALCULATE_REVERSE_CORRELATION = false;
    public const FIELD_AGGREGATE_CORRELATION_ID = UserVariableRelationship::FIELD_AGGREGATE_CORRELATION_ID;
    public const FIELD_ANALYSIS_ENDED_AT = 'analysis_ended_at';
    public const FIELD_ANALYSIS_REQUESTED_AT = 'analysis_requested_at';
    public const FIELD_ANALYSIS_STARTED_AT = 'analysis_started_at';
    public const FIELD_AVERAGE_DAILY_HIGH_CAUSE = 'average_daily_high_cause';
    public const FIELD_AVERAGE_DAILY_LOW_CAUSE = 'average_daily_low_cause';
    public const FIELD_AVERAGE_EFFECT = 'average_effect';
    public const FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE = 'average_effect_following_high_cause';
    public const FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE = 'average_effect_following_low_cause';
    public const FIELD_AVERAGE_FORWARD_PEARSON_CORRELATION_OVER_ONSET_DELAYS = 'average_forward_pearson_correlation_over_onset_delays';
    public const FIELD_AVERAGE_REVERSE_PEARSON_CORRELATION_OVER_ONSET_DELAYS = 'average_reverse_pearson_correlation_over_onset_delays';
    public const FIELD_CAUSE_BASELINE_AVERAGE_PER_DAY = 'cause_baseline_average_per_day';
    public const FIELD_CAUSE_BASELINE_AVERAGE_PER_DURATION_OF_ACTION = 'cause_baseline_average_per_duration_of_action';
    public const FIELD_CAUSE_CHANGES = 'cause_changes';
    public const FIELD_CAUSE_FILLING_VALUE = 'cause_filling_value';
    public const FIELD_CAUSE_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS = 'cause_number_of_processed_daily_measurements';
    public const FIELD_CAUSE_NUMBER_OF_RAW_MEASUREMENTS = 'cause_number_of_raw_measurements';
    public const FIELD_CAUSE_TREATMENT_AVERAGE_PER_DAY = 'cause_treatment_average_per_day';
    public const FIELD_CAUSE_TREATMENT_AVERAGE_PER_DURATION_OF_ACTION = 'cause_treatment_average_per_duration_of_action';
    public const FIELD_CAUSE_UNIT_ID = 'cause_unit_id';
    public const FIELD_CAUSE_USER_VARIABLE_ID = 'cause_user_variable_id';
    public const FIELD_CAUSE_VARIABLE_CATEGORY_ID = 'cause_variable_category_id';
    public const FIELD_CAUSE_VARIABLE_ID = 'cause_variable_id';
    public const FIELD_CLIENT_ID = 'client_id';
    public const FIELD_CONFIDENCE_INTERVAL = 'confidence_interval';
    public const FIELD_CREATED_AT = 'created_at';
    public const FIELD_CRITICAL_T_VALUE = 'critical_t_value';
    public const FIELD_DATA_SOURCE_NAME = 'data_source_name';
    public const FIELD_DELETED_AT = 'deleted_at';
    public const FIELD_DURATION_OF_ACTION = 'duration_of_action';
    public const FIELD_EARLIEST_MEASUREMENT_START_AT = 'earliest_measurement_start_at';
    public const FIELD_EFFECT_BASELINE_AVERAGE = 'effect_baseline_average';
    public const FIELD_EFFECT_BASELINE_RELATIVE_STANDARD_DEVIATION = 'effect_baseline_relative_standard_deviation';
    public const FIELD_EFFECT_BASELINE_STANDARD_DEVIATION = 'effect_baseline_standard_deviation';
    public const FIELD_EFFECT_CHANGES = 'effect_changes';
    public const FIELD_EFFECT_FILLING_VALUE = 'effect_filling_value';
    public const FIELD_EFFECT_FOLLOW_UP_AVERAGE = 'effect_follow_up_average';
    public const FIELD_EFFECT_FOLLOW_UP_PERCENT_CHANGE_FROM_BASELINE = 'effect_follow_up_percent_change_from_baseline';
    public const FIELD_EFFECT_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS = 'effect_number_of_processed_daily_measurements';
    public const FIELD_EFFECT_NUMBER_OF_RAW_MEASUREMENTS = 'effect_number_of_raw_measurements';
    public const FIELD_EFFECT_USER_VARIABLE_ID = 'effect_user_variable_id';
    public const FIELD_EFFECT_VARIABLE_CATEGORY_ID = 'effect_variable_category_id';
    public const FIELD_EFFECT_VARIABLE_ID = 'effect_variable_id';
    public const FIELD_EXPERIMENT_END_AT = 'experiment_end_at';
    public const FIELD_EXPERIMENT_START_AT = 'experiment_start_at';
    public const FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT = 'forward_pearson_correlation_coefficient';
    public const FIELD_FORWARD_SPEARMAN_CORRELATION_COEFFICIENT = 'forward_spearman_correlation_coefficient';
    public const FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME = 'grouped_cause_value_closest_to_value_predicting_high_outcome';
    public const FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME = 'grouped_cause_value_closest_to_value_predicting_low_outcome';
    public const FIELD_ID = 'id';
    public const FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR = 'interesting_variable_category_pair';
    public const FIELD_INTERNAL_ERROR_MESSAGE = 'internal_error_message';
    public const FIELD_LATEST_MEASUREMENT_START_AT = 'latest_measurement_start_at';
    public const FIELD_NEWEST_DATA_AT = 'newest_data_at';
    public const FIELD_NUMBER_OF_DAYS = 'number_of_days';
    public const FIELD_NUMBER_OF_PAIRS = 'number_of_pairs';
    public const FIELD_ONSET_DELAY = 'onset_delay';
    public const FIELD_ONSET_DELAY_WITH_STRONGEST_PEARSON_CORRELATION = 'onset_delay_with_strongest_pearson_correlation';
    public const FIELD_OPTIMAL_PEARSON_PRODUCT = 'optimal_pearson_product';
    public const FIELD_P_VALUE = 'p_value';
    public const FIELD_PEARSON_CORRELATION_WITH_NO_ONSET_DELAY = 'pearson_correlation_with_no_onset_delay';
    public const FIELD_PREDICTIVE_PEARSON_CORRELATION_COEFFICIENT = 'predictive_pearson_correlation_coefficient';
    public const FIELD_PREDICTS_HIGH_EFFECT_CHANGE = 'predicts_high_effect_change';
    public const FIELD_PREDICTS_LOW_EFFECT_CHANGE = 'predicts_low_effect_change';
    public const FIELD_PUBLISHED_AT = 'published_at';
    public const FIELD_QM_SCORE = 'qm_score';
    public const FIELD_REASON_FOR_ANALYSIS = 'reason_for_analysis';
    public const FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT = 'reverse_pearson_correlation_coefficient';
    public const FIELD_STATISTICAL_SIGNIFICANCE = 'statistical_significance';
    public const FIELD_STATUS = 'status';
    public const FIELD_STRONGEST_PEARSON_CORRELATION_COEFFICIENT = 'strongest_pearson_correlation_coefficient';
    public const FIELD_T_VALUE = 't_value';
    public const FIELD_UPDATED_AT = 'updated_at';
    public const FIELD_USER_ERROR_MESSAGE = 'user_error_message';
    public const FIELD_USER_ID = 'user_id';
    public const FIELD_VALUE_PREDICTING_HIGH_OUTCOME = 'value_predicting_high_outcome';
    public const FIELD_VALUE_PREDICTING_LOW_OUTCOME = 'value_predicting_low_outcome';
    public const FIELD_WP_POST_ID = 'wp_post_id';
    public const FIELD_Z_SCORE = 'z_score';
    public const LARAVEL_CLASS = UserVariableRelationship::class;
    // Keep this low so at least we get a chart on studies that have little data.  We should just include disclaimers about accuracy
    public const PERCENT_DISTANCE_FROM_MEDIAN_TO_BE_CONSIDERED_HIGH_OR_LOW_EFFECT = 25;
    public const REQUIRED_NEW_MEASUREMENT_PERCENT_FOR_CORRELATION = 10;
    public const SIGNIFICANT_CHANGE_SPREAD = 3;
    public const TABLE = 'user_variable_relationships';
    public const DB_FIELD_NAME_TO_PROPERTY_NAME_MAP = [
        //self::FIELD_VALUE_PREDICTING_HIGH_OUTCOME => 'avgDailyValuePredictingHighOutcome',
        //self::FIELD_VALUE_PREDICTING_LOW_OUTCOME  => 'avgDailyValuePredictingLowOutcome',
        self::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT  => 'correlationCoefficient',
    ];
    /**
     * @var QMUserVariableRelationship[]
     */
    protected $correlationsByHyperParameters;
    /**
     * @var array
     */
    private $baselinePairs;
    /**
     * @var array
     */
    private $followupPairs;
    /**
     * UserVariableRelationship constructor.
     * @param null $l
     * @param QMUserVariable|null $causeVariable
     * @param QMUserVariable|null $effectVariable
     * @param int|null $onsetDelay
     * @param int|null $durationOfAction
     * @param float|null $voteStatisticalSignificance
     */
    public function __construct($l = null, QMUserVariable $causeVariable = null, QMUserVariable $effectVariable = null,
                                int $onsetDelay = null, int $durationOfAction = null, float $voteStatisticalSignificance = null){
        $this->setType(StudyTypeProperty::TYPE_INDIVIDUAL);
        if($l){
			foreach($l as $key => $value){
				if($value === null){continue;}
				$this->$key = $value;
			}
            $this->populate($l);
            parent::__construct($l);
            $this->setFillingValues();
            $this->calculateIsPublic();
            $this->addLegacyProperties();
            if(!$this->dataSourceName){$this->setDataSourceName(GlobalVariableRelationshipDataSourceNameProperty::DATA_SOURCE_NAME_USER);}
            if ($this->predictsLowEffectChange === null) {
                $this->logError("No predictsLowEffectChange");
            }
            //StudyText::addAllStudySectionsToACorrelation($this);
            //$this->generateCauseAndEffectVariablesFromCorrelationPropertiesOrGlobals($row);  // Don't use this because it doesn't have all properties sometimes
            return;
        }
        if(!$causeVariable){
            //if($row){$this->generateCauseAndEffectVariablesFromCorrelationPropertiesOrGlobals($row);}   // Don't use this because it doesn't have all properties sometimes
            return;
        }
        $this->setCauseVariable($causeVariable);
        $this->setEffectVariable($effectVariable);
        $this->onsetDelay = $onsetDelay;
        $this->durationOfAction = $durationOfAction;
        $this->voteStatisticalSignificance = $voteStatisticalSignificance;
    }
	public function logUrl(){$this->l()->logUrl();}
	/**
     * @param array $params
     * @param QMQB $qb
     * @return array
     */
    private static function addEffectIdOrNameClause(array $params, QMQB $qb): array {
        $effectName = QMArr::getValue($params, ['effect_variable_name'], null);
        $effectId = QMArr::getValue($params, ['effectVariableId', self::FIELD_EFFECT_VARIABLE_ID], null);
        if ($effectName && !$effectId) {
            $effectId = QMVariable::getIdByNameIdOrSynonym($effectName);  // Searching by name is slow!
            if ($effectId) {
                $params['effectVariableId'] = $effectId;
                unset($params['effectVariableName']);
            }else{
                $effectName = SearchVariableRequest::stripAstrix($effectName);
                $operator = 'LIKE';
                $qb->whereRaw('evars.name ' . \App\Storage\DB\ReadonlyDB::like() . ' "%' . $effectName . '%"');
            }
        }
        return $params;
    }
    /**
     * @param array $params
     * @param QMQB $qb
     * @return array
     */
    private static function addCauseIdOrNameClause(array $params, QMQB $qb): array {
        $causeName = QMArr::getValue($params, ['causeVariableName'], null);
        $causeId = QMArr::getValue($params, ['causeVariableId', self::FIELD_CAUSE_VARIABLE_ID], null);
        if ($causeName && !$causeId) {
            $causeId = QMVariable::getIdByNameIdOrSynonym($causeName);  // Searching by name is slow!
            if ($causeId) {
                $params['causeVariableId'] = $causeId;
                unset($params['causeVariableName']);
            }else{
                $causeName = SearchVariableRequest::stripAstrix($causeName);
                $qb->whereLike('cvars.name', '%' . $causeName . '%');
            }
        }
        return $params;
    }
    /**
     * @param $data
     */
    private function populate($data){
        if(isset($data->causeId)){$this->causeVariableId = $data->causeId;}
        if(isset($data->effectId)){$this->effectVariableId = $data->effectId;}
        // This includes non-declared fields such as dataSourcesCount which has invalid mongo keys so let's use populateFieldsByArrayOrObject
        //foreach(ObjectHelper::getNonNullValuesWithCamelKeys($data) as $key => $value){$this->$key = $value;}
        $this->populateFieldsByArrayOrObject($data);
        if(!isset($this->causeVariable)){
            $cause = QMUserVariable::findInMemoryByVariableId($this->getUserId(), $this->getCauseVariableId());
            if($cause){
                $this->setCauseVariable($cause);
            }
        }
        if(!isset($this->effectVariable)){
            $effect = QMUserVariable::findInMemoryByVariableId(
				$this->getUserId(), $this->getEffectVariableId());
            if($effect){
                $this->setEffectVariable($effect);
            }
        }
    }
    /**
     * @param bool $instantiate
     * @return QMQB
     */
    public static function qb(bool $instantiate = false): QMQB {
        $qb = self::readonly()
            ->select(self::getSelectColumns())
            ->join('variables AS cvars', self::TABLE.'.cause_variable_id', '=', 'cvars.id')
            ->join('variables AS evars', self::TABLE.'.effect_variable_id', '=', 'evars.id');
        if($instantiate){$qb->class = self::class;}
        return $qb;
    }
    /**
     * @param array $params
     * @return QMQB
     */
    public static function getUserVariableRelationshipsQB(array $params): QMQB {
        $userId = QMArr::getValue($params, [self::FIELD_USER_ID], null);
        $qb = self::qb();
        if ($userId) {
            $qb->where(self::TABLE.'.' . self::FIELD_USER_ID, $userId);
        }
        if (!isset($params['sort'])) {
            $qb->orderByRaw(self::TABLE.'.qm_score DESC');
        }
        // where('euv.outcome_of_interest') adds over 25s to query length
        // Add an index for outcome_of_interest if it's necessary
        //if(isset($requestParams['userId']) && self::shouldWeLimitToOutcomesOfInterest($requestParams)){$qb->where('euv.outcome_of_interest', 1);}
        $params = self::addEffectIdOrNameClause($params, $qb);
        $params = self::addCauseIdOrNameClause($params, $qb);
        QueryBuilderHelper::applyFilterParamsIfExist($qb, self::getFilterParameterMap(), $params);
        if ($userId) {
            static::joinUserVoteData($qb, $userId);
        } // joinUserVoteData adds very little delay to query time (< 1s)
        static::joinUserVariables($qb, $params); // joinUserVariables adds very little delay to query time (< 1s)
        //static::joinAverageVoteData($qb);
        QMVariableRelationship::applyOffsetLimitSort($qb, $params);
        /** @var QMUserVariableRelationship[] $correlations */
        $qb->whereNull(self::TABLE.'.' . self::FIELD_DELETED_AT);
        return $qb;
    }
    /**
     * @param array $params
     * @return QMUserVariableRelationship[]
     */
    private static function getUserVariableRelationshipsQBAndFormatParams(array $params): array{
        $qb = self::getUserVariableRelationshipsQB($params);
        $correlations = self::getUserVariableRelationships($params);
        return [
            $qb,
            $params,
            $correlations
        ];
    }
    /**
     * @param array $params
     * @return QMUserVariableRelationship[]
     */
    public static function get(array $params = []): array {
        $correlations = self::getUserVariableRelationships($params);
        return $correlations;
    }
    /**
     * @param int $userId
     * @param int $effectId
     * @param int $limit
     * @param string|null $causeVariableCategoryName
     * @return QMUserVariableRelationship[]
     */
    public static function getUpVotedCorrelationsByEffect(int $userId, int $effectId, int $limit = 0,
                                                          string $causeVariableCategoryName = null): array {
        $qb = self::getUserVariableRelationshipsQB([
            self::FIELD_USER_ID => $userId,
            self::FIELD_EFFECT_VARIABLE_ID => $effectId,
            'limit' => $limit
        ]);
        $qb->where(Vote::TABLE . '.' . Vote::FIELD_VALUE, '>', 0);
        if($causeVariableCategoryName){
            $cat = QMVariableCategory::find($causeVariableCategoryName);
            $qb->where(self::TABLE.'.'.self::FIELD_CAUSE_VARIABLE_CATEGORY_ID, $cat->id);
        }
        $qb->whereNotNull(self::TABLE.'.'.self::FIELD_ANALYSIS_ENDED_AT);
        $rows = $qb->getArray();
        $correlations = self::instantiateNonDBRows($rows);
        return $correlations;
    }
    /**
     * @param int $userId
     * @param int $effectId
     * @param int $limit
     * @param string|null $causeVariableCategoryName
     * @return QMUserVariableRelationship[]
     */
    public static function getNonVotedCorrelationsByEffect(int $userId, int $effectId, int $limit = 0,
                                                          string $causeVariableCategoryName = null): array {
        $qb = self::getUserVariableRelationshipsQB([
            self::FIELD_USER_ID => $userId,
            self::FIELD_EFFECT_VARIABLE_ID => $effectId,
            'limit' => $limit
        ]);
        $qb->whereNull(Vote::TABLE . '.' . Vote::FIELD_VALUE);
        if($causeVariableCategoryName){
            $cat = QMVariableCategory::find($causeVariableCategoryName);
            $qb->where(self::TABLE.'.'.self::FIELD_CAUSE_VARIABLE_CATEGORY_ID, $cat->id);
        }
        $qb->whereNotNull(self::TABLE.'.'.self::FIELD_ANALYSIS_ENDED_AT);
        $rows = $qb->getArray();
        $correlations = self::instantiateNonDBRows($rows);
        return $correlations;
    }
    /**
     * @param string|null $tableAlias
     * @return array
     */
    public static function getSelectColumns(string $tableAlias = null): array{
        $arr = [
            'cvars.name AS causeVariableName',
            'evars.name AS effectVariableName',
            self::TABLE.'.cause_variable_id as causeVariableId',
            self::TABLE.'.effect_variable_id as effectVariableId',
            self::TABLE.'.forward_pearson_correlation_coefficient AS correlationCoefficient',
            self::TABLE.'.value_predicting_high_outcome AS avgDailyValuePredictingHighOutcome',
            self::TABLE.'.value_predicting_low_outcome AS avgDailyValuePredictingLowOutcome',
            'cvars.combination_operation AS causeVariableCombinationOperation',
            'cvars.common_alias AS causeVariableCommonAlias',
            'cvars.common_alias AS causeVariableDisplayName',
            'cvars.default_unit_id AS causeVariableDefaultUnitId',
            'cvars.default_unit_id AS causeVariableCommonUnitId',
            'cvars.image_url AS causeVariableImageUrl',
            'cvars.informational_url AS causeVariableInformationalUrl',
            'cvars.ion_icon AS causeVariableIonIcon',
            'cvars.most_common_connector_id AS causeVariableMostCommonConnectorId',
            'cvars.product_url AS causeVariableProductUrl',
            'cvars.variable_category_id AS causeVariableCategoryId',
            'cvars.outcome AS causeVariableIsOutcome',
            'evars.combination_operation AS effectVariableCombinationOperation',
            'evars.common_alias AS effectVariableCommonAlias',
            'evars.common_alias AS effectVariableDisplayName',
            'evars.default_unit_id AS effectVariableDefaultUnitId',
            'evars.default_unit_id AS effectVariableCommonUnitId',
            'evars.image_url AS effectVariableImageUrl',
            'evars.informational_url AS effectVariableInformationalUrl',
            'evars.ion_icon AS effectVariableIonIcon',
            'evars.most_common_connector_id AS effectVariableMostCommonConnectorId',
            'evars.product_url AS effectVariableProductUrl',
            'evars.valence AS effectVariableValence',
            'evars.variable_category_id AS effectVariableCategoryId'
        ];
        $arr = static::addSelectFields($arr, self::TABLE);
        return $arr;
    }
    /**
     * @param int $userId
     * @param int $causeId
     * @param int $effectId
     * @return QMUserVariableRelationship|null
     */
    public static function findByIds(int $userId, int $causeId, int $effectId): ?QMUserVariableRelationship {
        $c =
            UserVariableRelationship::whereCauseVariableId($causeId)
                ->where(UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID, $effectId)
                ->where(UserVariableRelationship::FIELD_USER_ID, $userId)
                ->first();
        if(!$c){
            return null;
        }
        return $c->getDBModel();
    }
    /**
     * @param int $userId
     * @param $causeVariableNameOrId
     * @param $effectVariableNameOrId
     * @return QMUserVariableRelationship
     * @throws \App\Exceptions\UserVariableNotFoundException
     */
    public static function findOrCreate(int $userId, $causeVariableNameOrId, $effectVariableNameOrId): QMUserVariableRelationship {
        return self::getOrCreateUserVariableRelationship($userId, $causeVariableNameOrId,
            $effectVariableNameOrId);
    }
    /**
     * @param string $reason
     * @throws InsufficientVarianceException
     * @throws InvalidVariableValueException
     * @throws NotEnoughDataException
     * @throws NotEnoughMeasurementsForCorrelationException
     * @throws NotEnoughOverlappingDataException
     * @throws StupidVariableNameException
     * @throws TooSlowToAnalyzeException
     * @throws \App\Exceptions\StupidVariableException
     */
    public function analyzePartially(string $reason): void{
		$start = microtime(true);
        $this->logAnalysisParameters();
        $this->setDataSourceName(GlobalVariableRelationshipDataSourceNameProperty::DATA_SOURCE_NAME_USER);
        $this->exceptionIfStupidVariable();
        $this->setCauseMeasurementsAndAnalyze();
        $this->setEffectMeasurementsAndAnalyze();
        $this->setMeasurements();
        $this->setNumberOfDaysFromVariables();
        $this->setPairs();
        CorrelationForwardPearsonCorrelationCoefficientProperty::calculate($this);
        CorrelationForwardSpearmanCorrelationCoefficientProperty::calculate($this);
        $this->calculateOutcomeBaselineStatistics();
        $this->validateMeasurements();
        $this->setEffectValueStatistics();
        $this->setCauseValueStatistics();
        if ($this->avgDailyValuePredictingHighOutcome === $this->avgDailyValuePredictingLowOutcome) {
            $this->predictsLowEffectChange = $this->predictsHighEffectChange = 0; // Can't calculate this
        } else {
            $this->calculateChangeFromAvgEffectPredictorValues();
        }
        $this->setOptimalPearsonProduct($this->causeValues);
        CorrelationStatisticalSignificanceProperty::calculate($this);
        $this->setOptimalChangeSpreadStatistics();
        CorrelationQmScoreProperty::calculate($this);
		CorrelationConfidenceIntervalProperty::calculate($this);
	    CorrelationConfidenceLevelProperty::calculate($this);
	    CorrelationStrengthLevelProperty::calculate($this);
		CorrelationCauseUnitIdProperty::calculate($this);
        $this->validateCalculation();
        $this->isInteresting();
        $this->calculateNewestDataAt();
        CorrelationGroupedCauseValueClosestToValuePredictingHighOutcomeProperty::calculate($this);
        CorrelationRelationshipProperty::calculate($this);
        if(APIHelper::apiVersionIsBelow(3)){$this->addLegacyProperties();}
        parent::__construct();
        $this->validateAnalysis();
	    QMClockwork::logDuration(__FUNCTION__.": $this", $start, time());
    }
    /**
     * @return QMUnit
     */
    public function getCauseVariableUserUnit(): QMUnit{
        return $this->causeVariableUserUnit = QMUnit::getUnitById($this->getCauseVariableUserUnitId());
    }
    /**
     * @return int
     */
    public function getCauseVariableUserUnitId(): int{
        if(!$this->causeVariableUserUnitId){
            $this->causeVariableUserUnitId = $this->getCauseVariableCommonUnitId();
        }
        return $this->causeVariableUserUnitId;
    }
    /**
     * @return bool
     * @throws NotEnoughDataException
     */
    private function lastProcessedDailyValueIsValid(): bool{
        $cause = $this->getOrSetCauseQMVariable();
        try {
            $cause->validateValueForCommonVariableAndUnit($this->getLastProcessedDailyValueForCauseInCommonUnit(),
                'LastProcessedDailyValueForCauseInCommonUnit');
        } catch (InvalidVariableValueException $e) {
            $this->logError("LastProcessedDailyValueForCauseInCommonUnit not valid for variable: ".$this->getLastProcessedDailyValueForCauseInCommonUnit());
            return false;
        }
        return true;
    }
    /**
     * @return float
     * @throws NotEnoughDataException
     */
    public function getLastProcessedDailyValueForCauseInCommonUnit(): ?float {
        $val = $this->lastProcessedDailyValueForCauseInCommonUnit;
        if($val !== null){return $val;}
        if(!$this->causeVariable){
            if($this->analysisEndedAt){
                $this->logError("lastProcessedDailyValueForCauseInCommonUnit is null and cause variable is not set to get it!");
            }
            return null;
        }
        $cause = $this->getOrSetCauseQMVariable();
        $val = $cause->getLastProcessedDailyValueInCommonUnit();
        if($val === null){
            $this->validateMeasurements();
            $this->logError("lastProcessedDailyValueForCauseInCommonUnit from cause variable is null!");
        }
        return $this->lastProcessedDailyValueForCauseInCommonUnit = $val;
    }
    /**
     * @return string
     */
    public function getTimeSinceLastCauseMeasurementString(): string {
        if($this->causeVariable){
            return TimeHelper::timeSinceHumanString($this->getOrSetCauseQMVariable()->getLatestTaggedMeasurementAt());
        }
        return TimeHelper::timeSinceHumanString($this->causeLatestTaggedMeasurementTime);
    }
    /**
     * @return mixed
     */
    public function getEffectVariableIsOutcomeOfInterest(){
        if($this->effectVariableIsOutcomeOfInterest === null){
            $this->setEffectVariableIsOutcomeOfInterest();
        }
        return $this->effectVariableIsOutcomeOfInterest;
    }
    /**
     * @return bool
     */
    public function setEffectVariableIsOutcomeOfInterest(): bool{
        if($this->effectVariable){
            return $this->effectVariableIsOutcomeOfInterest = $this->getOrSetEffectQMVariable()->outcomeOfInterest;
        }
        return $this->effectVariableIsOutcomeOfInterest;
    }
    public function validateAnalysis(): void{
        //$this->validateAttributes(); // This is done before saving
    }
    /**
     * @param int $delay
     * @param int $duration
     * @param Throwable $e
     */
    public function addSkippingHyperParametersWarning(int $delay, int $duration, Throwable $e): void{
        $delay = TimeHelper::convertSecondsToHumanString($delay);
        $duration = TimeHelper::convertSecondsToHumanString($duration);
        $this->addWarning("Analysis with the hyper-parameters onset delay $delay and ".
            "duration of action $duration was skipped because because ".
            $e->getMessage());
    }
    /**
     * @param string $filename
     * @param string $dir
     * @throws TooSlowToAnalyzeException
     * @throws \App\Exceptions\InvalidFilePathException
     */
    public function saveStudyHtmlToResponsesRepo(string $filename, string $dir){
        if(!str_contains($filename, '.html')){$filename.='.html';}
        $s = $this->findInMemoryOrNewQMStudy();
        $sh = $s->getStudyHtml();
        $html = $sh->setWithEmbeddedCharts();
        ResponsesRepo::writeToFile($dir."/".$filename, $html);
    }
    /**
     * @return string
     */
    public function getStudyId(): string{
        return QMUserStudy::generateStudyId($this->getCauseVariableId(),
            $this->getEffectVariableId(),
            $this->getUserId(), StudyTypeProperty::TYPE_INDIVIDUAL);
    }
    /**
     * @return string
     * @throws NotEnoughOverlappingDataException
     */
    public function calculateExperimentEndAt(): string {
        return CorrelationExperimentEndAtProperty::calculate($this);
    }
    /**
     * @return string
     * @throws NotEnoughMeasurementsForCorrelationException
     */
    public function getExperimentStartAt(): string {
        if($start = $this->experimentStartAt){return $start;}
        return $this->calculateExperimentStartAt();
    }
	/**
	 * @return string
	 * @throws NotEnoughOverlappingDataException
	 */
	public function getExperimentEndAt(): string {
		if($end = $this->experimentEndAt){return $end;}
		return $this->calculateExperimentEndAt();
	}
    /**
     * @return string
     * @throws NotEnoughMeasurementsForCorrelationException
     */
    public function calculateExperimentStartAt(): string {
        return CorrelationExperimentStartAtProperty::calculate($this);
    }
    /**
     * @return string
     * @throws NotEnoughDataException
     */
    public function getExperimentTimeRangeString():string {
        $days = $this->getOrCalculateNumberOfDays();
        $start = $this->getExperimentStartAt();
        $end = $this->getExperimentEndAt();
        if($start === $end){
            QMLog::exceptionIfTesting("ExperimentStartAt equals ExperimentEndAt: $start");
        }
        return "$days days from $start to $end";
    }
    /**
     * @return string
     */
    public function getChangesVarianceSentence(): string {
        $causeChanges = $this->causeChanges;
        if($causeChanges === null){$causeChanges = "an unknown number of";}
        $effectChanges = $this->effectChanges;
        if($effectChanges === null){$effectChanges = "an unknown number of";}
        try {
            $message =
                "There are $causeChanges changes in the value of the $this->causeVariableName ".
                "and $effectChanges changes in $this->effectVariableName during the ".
                $this->getExperimentTimeRangeString().".  ";
        } catch (NotEnoughDataException $e) {
            le($e);
        }
        return $message;
    }
    /**
     * @return int
     * @throws NotEnoughMeasurementsForCorrelationException
     * @throws NotEnoughOverlappingDataException
     */
    private function setNumberOfDaysFromVariables(): int{
        $cause = $this->getOrSetCauseQMVariable();
        $effect = $this->getOrSetEffectQMVariable();
        $causeLatest = $cause->calculateLatestTaggedMeasurementAt();
        $effectLatest = $effect->calculateLatestTaggedMeasurementAt();
        $causeEarliest = $cause->calculateEarliestTaggedMeasurementAt();
        $effectEarliest = $effect->calculateEarliestTaggedMeasurementAt();
        if($causeLatest < $effectEarliest){
            $problemDetails = "The latest cause measurement is before the earliest effect measurement. \n".
                $cause->getLatestTaggedMeasurementAtSentence(true)."\n".
                $effect->getEarliestTaggedMeasurementAtSentence(true);
            $this->setInternalErrorMessage($problemDetails);
            $this->numberOfDays = 0;
            throw new NotEnoughMeasurementsForCorrelationException($problemDetails, $this,
                $this->getOrSetCauseQMVariable(), $this->getOrSetEffectQMVariable());
        }
        if($effectLatest < $causeEarliest){
            $problemDetails = "The latest effect measurement is before the earliest cause measurement. \n".
                $effect->getLatestTaggedMeasurementAtSentence(true).
                $cause->getEarliestTaggedMeasurementAtSentence(true);
            $this->setInternalErrorMessage($problemDetails);
            $this->numberOfDays = 0;
            throw new NotEnoughMeasurementsForCorrelationException($problemDetails, $this,
                $this->getOrSetCauseQMVariable(), $this->getOrSetEffectQMVariable());
        }
        $end = $this->getExperimentEndAt();
        $start = $this->getExperimentStartAt();
        $numberOfDays = round((strtotime($end) - strtotime($start)) / 86400);
        if($numberOfDays < 0){
            $cause->logTimes();
            $effect->logTimes();
            le("Experiment start $start and experiment end $end is wrong for $this");
        }
        if($numberOfDays < 10){
            $this->setInternalErrorMessage("There are only $numberOfDays days of overlapping measurements.  ".
                $cause->getTaggedMeasurementRangeSentence() . $effect->getTaggedMeasurementRangeSentence());
        }
        return $this->numberOfDays = (int)$numberOfDays;
    }
    /**
     * @return float[]
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getCauseValues(): array {
        $arr = [];
        $pairs = $this->getPairs();
        foreach($pairs as $pair){
            $arr[] = $pair->causeMeasurementValue;
        }
        return $this->causeValues = $arr;
    }
    /**
     * @return float[]
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getEffectValues(): array {
        $arr = [];
        $pairs = $this->getPairs();
        foreach($pairs as $pair){
            $arr[] = $pair->effectMeasurementValue;
        }
        return $arr;
    }
    /**
     * @return Pair[]
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function setPairs(): array {
        $cause = $this->getOrSetCauseQMVariable();
        if($cause->hasFillingValue()){
            $this->pairs = $this->setPairsBasedOnDailyEffectValues();
        }else{
            $this->pairs = $this->setPairsBasedOnDailyCauseValues();
        }
        CorrelationNumberOfPairsProperty::calculate($this);
        CorrelationNumberOfDaysProperty::calculate($this);
        CorrelationCauseChangesProperty::calculate($this);
        CorrelationEffectChangesProperty::calculate($this);
        $this->calculateAverageEffect();
        return $this->pairs;
    }
    /**
     * @return Pair[]
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getPairs(): array {
        if($this->pairs === null){
            $this->setPairs();
        }
        return $this->pairs;
    }
    /**
     * @return Pair[]
     * @throws NotEnoughDataException
     */
    public function getPairsBasedOnDailyCauseValues(): array {
        if($pairs = $this->pairsBasedOnDailyCauseValues){return $pairs;}
        return $this->setPairsBasedOnDailyCauseValues();
    }
    /**
     * @return Pair[]
     * @throws NotEnoughDataException
     */
    public function setPairsBasedOnDailyCauseValues(): array {
        $effect = $this->getOrSetEffectQMVariable();
        $causeMeasurements = $this->getCauseMeasurements();
        $effectMeasurements = $this->getEffectMeasurements();
	    $req = new GetPairRequest($this->getOrSetCauseQMVariable(), $effect);
	    $req->setDurationOfAction($this->getDurationOfAction());
	    $req->setOnsetDelay($this->getOnsetDelay());
	    $req->setCauseDailyMeasurements($causeMeasurements);
	    $req->setEffectDailyMeasurements($effectMeasurements);
        return $this->pairs = $this->pairsBasedOnDailyCauseValues = $req->createAbsolutePairs();
    }
    /**
     * @return ProcessedQMMeasurement[]|DailyMeasurement[]
     * @throws NotEnoughDataException
     */
    public function getCauseMeasurements(): array {
        if($this->causeMeasurements === null){
            $this->setMeasurements();
        }
        return $this->causeMeasurements;
    }
    /**
     * @return Pair[]
     * @throws NotEnoughDataException
     */
    public function setPairsBasedOnDailyEffectValues(): array {
        $causeMeasurements = $this->getCauseMeasurements();
        $effectMeasurements = $this->getEffectMeasurements();
        if(!$causeMeasurements){
            $this->logError("No cause ProcessedDailyMeasurementsWithTagsJoinsChildrenInCommonUnit for pairs!");
            return [];
        }
        if(!$effectMeasurements){
            $this->logError("No effect ProcessedDailyMeasurementsWithTagsJoinsChildrenInCommonUnit for pairs!");
            return [];
        }
	    $req = new GetPairRequest($this->getOrSetCauseQMVariable(), $this->getOrSetEffectQMVariable());
	    $req->setDurationOfAction($this->getDurationOfAction());
	    $req->setOnsetDelay($this->getOnsetDelay());
	    $req->setCauseDailyMeasurements($this->getCauseMeasurements());
	    $req->setEffectDailyMeasurements($this->getEffectMeasurements());
        $pairs = $req->createPairForEachEffectMeasurement();
        return $this->pairs = $pairs;
    }
    /**
     * @throws TooSlowToAnalyzeException
     */
    public function setEffectMeasurementsAndAnalyze(): void {
        $effect = $this->getOrSetEffectQMVariable();
        $effect->analyzeFullyIfNecessary(__FUNCTION__);
        $this->setEffectProperties($effect);
        $this->effectNumberOfRawMeasurements = $effect->getNumberOfRawMeasurementsWithTagsJoinsChildren();
    }
    /**
     * @throws TooSlowToAnalyzeException
     */
    public function setCauseMeasurementsAndAnalyze(): void {
        $cause = $this->getOrSetCauseQMVariable();
        $this->setUserId($cause->userId);
        $cause->analyzeFullyIfNecessary(__FUNCTION__);
        $this->setCauseProperties($cause);
        CorrelationCauseNumberOfRawMeasurementsProperty::calculate($this);
    }
    /**
     * @throws NotEnoughMeasurementsForCorrelationException
     * @throws NotEnoughOverlappingDataException|\App\Exceptions\NotEnoughDataException
     */
    private function setOptimalChangeSpreadStatistics(): void {
        if($this->predictsHighEffectChange){
            // Filter out user_variable_relationships with very little change
            // We can't use predictsHighEffectChange/predictsLowEffectChange directly because effects with zero value outcomes like Number of Zits produce absurdly high percent changes
            $this->optimalChangeSpread = abs($this->predictsHighEffectChange - $this->predictsLowEffectChange);
            if(!$this->optimalChangeSpread){
                $this->optimalChangeSpread = 1; // We don't want QM Score to be 0
            }
            $this->optimalChangeSpreadSignificance = 1 - exp(-$this->optimalChangeSpread / self::SIGNIFICANT_CHANGE_SPREAD);
            // Let's not do this because it leads to statistically significant user_variable_relationships with small effects getting
            // artificially low significance
            //$this->statisticalSignificance *= $this->optimalChangeSpreadSignificance;
            MeasurementStartTimeProperty::verifyChronologicalOrder($this->getEffectMeasurements());
        }
    }
    /**
     * @param QMQB $qb
     * @param int $userId
     * @internal param int $userId
     */
    public static function joinUserVoteData(QMQB $qb, int $userId) {
        $db = ReadonlyDB::db();
        if ($userId) {
            $qb->columns[] = 'votes.value AS userVote';
            $qb->leftJoin('votes', static function(JoinClause $join) use ($userId){
                $join->on(self::TABLE.'.cause_variable_id', '=', 'votes.cause_variable_id')
                    ->on(self::TABLE.'.effect_variable_id', '=', 'votes.effect_variable_id')
                    ->where('votes.user_id', '=', $userId);
            });
        }else{
            $qb->columns[] = $db->raw('NULL AS userVote');
        }
    }
    /**
     * @return array
     */
    public static function getFilterParameterMap(): array{
        $params = [
            'causeVariableId'          => self::TABLE.'.cause_variable_id',
            //'causeVariableName' => 'cvars.name',
            'causeVariableCategoryId'  => self::TABLE.'.'.self::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
            'correlationCoefficient'   => self::TABLE.'.forward_pearson_correlation_coefficient',
            'createdAt'                => self::TABLE.'.created_at',
            'deletedAt'                => self::TABLE.'.deleted_at',
            'durationOfAction'         => self::TABLE.'.duration_of_action',
            'effectVariableId'         => self::TABLE.'.effect_variable_id',
            'effectVariableCategoryId' => self::TABLE.'.'.self::FIELD_EFFECT_VARIABLE_CATEGORY_ID,
            //'effectVariableName' => 'evars.name',
            'lastUpdated'              => self::TABLE.'.updated_at',
            'onsetDelay'               => self::TABLE.'.onset_delay',
            'updateAt'                 => self::TABLE.'.updated_at',
        ];
        $fields = self::getColumns();
        foreach ($fields as $field) {
            $params[$field] = self::TABLE.'.' . $field;
        }
        return $params;
    }
    /**
     * @return string
     */
    public function getVerificationStatusHTML(): string {
        $str = "Unverified";
        if($this->userUpVoted()){$str = "Verified";}
        if($this->userDownVoted()){$str = "Flawed";}
        return HtmlHelper::getLinkAnchorHtml($this->getThumbImageHtml()."".$str,
            $this->getStudyLinks()->getStudyUrlDynamic(), true);
    }
    /**
     * @return TableCell
     */
    public function getVerificationStatusCell(): TableCell {
        $str = "Unverified";
        $color = QMColor::HEX_GOOGLE_YELLOW;
        if($this->userUpVoted()){
            $color = QMColor::HEX_GOOGLE_GREEN;
            $str = "Verified";
        }
        if($this->userDownVoted()){
            $color = QMColor::HEX_GOOGLE_RED;
            $str = "Flawed";
        }
        $links = $this->getStudyLinks();
        $cell = new TableCell($str, $color, $links->getStudyLinkStatic());
        $cell->setTooltip($this->getThumbTooltip());
        return $cell;
    }
    /**
     * @return string
     */
    private function getThumbImagePath():string {
        $url = Vote::THUMB_UP_WHITE_IMAGE_16;
        if($this->userUpVoted()){$url = Vote::THUMB_UP_BLACK_IMAGE_16;}
        if($this->userDownVoted()){$url = Vote::THUMB_DOWN_BLACK_IMAGE_16;}
        $url = str_replace('https://static.quantimo.do/img/thumbs/',
            FileHelper::absPath('public/img/thumbs/'), $url);
        return $url;
    }
    /**
     * @return string
     */
    public function getThumbImageHtml(): string {
        $url = $this->getThumbImagePath();
        $tooltip = $this->getThumbTooltip();
        return HtmlHelper::getImageHtml($url, $tooltip);
    }
    /**
     * @return TableCell
     */
    private function getStudyLinkCell(): TableCell{
        $cell = new TableCell("Click to See Study");
        $cell->setUrl($this->getStudyLinks()->getStudyLinkStatic());
        $cell->setTooltip("Full Analysis");
        return $cell;
    }
    /**
     * @return array
     * @throws NotEnoughDataException
     */
    public function getTableRow():array {
        $row['Predictor'] = $this->getCauseVariableName();
        $row['Outcome'] = $this->getEffectVariableName();
        $row['Change'] = $this->getChangeCell(true); // Use numeric so we can sort table
        $row['Correlation Coefficient'] = $row['Predictive Coefficient'] = $this->getForwardPearsonCorrelationCoefficient();
        $row['Association'] = $this->getStrengthLevel();
        //$row['Confidence'] = $this->getConfidenceLevelCell();
        $row['Data Points'] = $this->getNumberOfPairs();
        $row['Review'] = $this->getVerificationStatusCell();
        $row['Outcome RSD at Baseline'] = $this->getEffectBaselineRelativeStandardDeviation()."%";
        //$row['Source'] = $this->getStudyLinkCell();
        //$row['Last Analysis'] = TimeHelper::YYYYmmddd($this->getUpdatedAt());
        return $row;
    }
    /**
     * @param int|null $precision
     * @return float
     * @throws AlreadyAnalyzedException
     * @throws InsufficientVarianceException
     * @throws InvalidVariableValueException
     * @throws NotEnoughDataException
     * @throws NotEnoughMeasurementsForCorrelationException
     * @throws NotEnoughOverlappingDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getOrCalculatePercentEffectChangeFromLowCauseToHighCause(int $precision = null): float {
        if($this->shouldUseChangeFromBaseline()){
            return $this->getOrCalculateFollowUpPercentChangeFromBaseline();
        }
        return parent::getOrCalculatePercentEffectChangeFromLowCauseToHighCause($precision);
    }
    /**
     * @return int
     * @throws NotEnoughDataException
     */
    public function getOrCalculateNumberOfDays(): int{
        $start = $this->getExperimentStartAt();
        $end = $this->getExperimentEndAt();
        $days = (strtotime($end) - strtotime($start))/86400;
        if(!$days){
            $startC = $this->calculateExperimentStartAt();
            $endC = $this->calculateExperimentEndAt();
            $daysC = (strtotime($endC) - strtotime($startC))/86400;
            return $this->numberOfDays = (int)$daysC;
        }
	    return $this->numberOfDays = (int)$days;
    }
    /**
     * @return array
     */
    public function getSpreadsheetRow():array {
        $row = [
            'Predictor'              => $this->getCauseVariableName(),
            'Outcome'                => $this->getEffectVariableName(),
            'Interactive Study'      => $this->getStudyLinkCell(),
            'Predictive Coefficient' => $this->getForwardPearsonCorrelationCoefficient(),
            'Change'                 => $this->getChangeCell(true),
        ];
        $row = array_merge($row, $this->getStatisticsArray());
        foreach($this as $key => $value){
            if(is_array($value) || is_object($value)){continue;}
            $header = QMStr::camelToTitle($key);
            if(!isset($row[$header])){
                $row[$header] = $value;
            }
        }
        return $row;
    }
    public function getQMUserVariableRelationship(): QMUserVariableRelationship {
        return $this;
    }
    /**
     * @param int $value
     * @param string $paramType
     * @throws TooSlowToAnalyzeException
     */
    protected function checkMemoryAndTimeLimit(int $value, string $paramType): void{
        ThisComputer::outputMemoryUsageIfEnabledOrDebug(__FUNCTION__.": $paramType is $value");
        if(ThisComputer::excessiveMemoryUsage()){
            throw new InsufficientMemoryException("There is not enough memory to calculate user_variable_relationships over $paramType for $this");
        }
        if(APIHelper::timeLimitExceeded()){
            throw new TooSlowToAnalyzeException(__FUNCTION__,
                $this,
                "It's taking a while to analyze user_variable_relationships over various $paramType for $this, so I'll notify you when it's done.");
        }
    }
    /**
     * @return array
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getBaselineEffectValues(): array{
        $baselinePairs = $this->getBaselinePairs();
        $baselineEffectValues = Arr::pluck($baselinePairs, 'effectMeasurementValue');
        return $baselineEffectValues;
    }
    /**
     * @return QMUserVariableRelationship[]
     */
    public function getCorrelationsByHyperParameters(): ?array{
        return $this->correlationsByHyperParameters;
    }
    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @return TableCell
     */
    private function getCauseVariableSettingsCell(): TableCell{
        $cell = new TableCell($this->getCauseVariableName(). " Settings");
        $cell->setUrl(VariableSettingsStateButton::getVariableSettingsUrlForVariableId($this->getCauseVariableId(), [
	                                                                                                                  'fromUrl' => $this->getStudyLinks()
	                                                                                                                                    ->getStudyLinkStatic()
                                                                                                                  ]));
        $cell->setTooltip("Analysis Settings for ".$this->getCauseVariableName());
        return $cell;
    }
    /**
     * @return array
     */
    public static function getAllowedParametersFromDatabaseFieldMap(): array{
        $allowedParameters = [];
        $filterParameterMap = self::getFilterParameterMap();
        foreach($filterParameterMap as $parameterName => $databaseField){
            $allowedParameters[] = $parameterName;
        }
        return $allowedParameters;
    }
    /**
     * @return array
     */
    public static function getAllowedRequestParameters(): array{
        $additionalAllowedParams = [
            'aggregated',
            'causeOrEffect',
            'causeVariableId',
            'causeVariableName',
            'correlationCoefficient',
            'createdAt',
            'doNotCreate',
            'doNotGroup',
            'downvoted',
            'durationOfAction',
            'effectVariableId',
            'effectVariableName',
            'fallbackToAggregatedCorrelations',
            'fallbackToStudyForCauseAndEffect',
            'forwardPearsonCorrelationCoefficient',
            QMRequest::PARAM_INCLUDE_CHARTS,
            'includeProcessedMeasurements',
            'includeThirdParty',
            'includeWikipediaExtract',
            'lastUpdated',
            'numberOfUsers',
            'open',
            'created',
            'onsetDelay',
            'outcomesOfInterest',
            'recalculate',
            'updatedAt',
        ];
        return array_merge($additionalAllowedParams, self::getAllowedParametersFromDatabaseFieldMap());
    }
    /**
     * @param $requestParams
     * @return bool
     */
    private static function shouldWeLimitToOutcomesOfInterest(array $requestParams): bool {
        if(isset($requestParams['limit']) && $requestParams['limit'] === 0){
            return false;
        }
        if(isset($requestParams['outcomesOfInterest'])){
            return $requestParams['outcomesOfInterest'];
        }
        if(!isset($requestParams['causeVariableName']) && !isset($requestParams['effectVariableName']) &&
            !isset($requestParams['causeVariableId']) && !isset($requestParams['effectVariableId'])){
            return true;
        }
        return false;
    }
    /**
     * @param int $userId
     * @param string|int $causeNameOrId
     * @param string|int $effectNameOrId
     * @return QMUserVariableRelationship
     */
    public static function findByNamesOrIds(int $userId, $causeNameOrId, $effectNameOrId): ?QMUserVariableRelationship{
        if(is_int($causeNameOrId)){
            return self::findByIds($userId, $causeNameOrId, $effectNameOrId);
        }else{
            $correlations = self::getUserVariableRelationships([
                'causeVariableName'  => $causeNameOrId,
                'effectVariableName' => $effectNameOrId,
                'userId'             => $userId,
                QMRequest::PARAM_LIMIT           => 1 // Need LIMIT to make sure we get charts
            ]);
        }
        return $correlations[0] ?? null;
    }
    /**
     * @param int $userId
     * @param string|int $causeVariableNameOrId
     * @param string|int $effectVariableNameOrId
     * @return QMUserVariableRelationship
     * @throws \App\Exceptions\UserVariableNotFoundException
     */
    public static function getOrCreateUserVariableRelationship(int $userId, $causeVariableNameOrId, $effectVariableNameOrId): QMUserVariableRelationship{
        $c = self::findByNamesOrIds($userId, $causeVariableNameOrId, $effectVariableNameOrId);
        if(!$c){
            $cause = QMUserVariable::getByNameOrId($userId, $causeVariableNameOrId);
            $effect = QMUserVariable::getByNameOrId($userId, $effectVariableNameOrId);
            $c = new QMUserVariableRelationship(null, $cause, $effect);
            try {
                $c->analyzeFullyOrQueue(__FUNCTION__);
            } catch (NotEnoughDataException | StupidVariableNameException | AnalysisException $e) {
                $c->addException($e);
            }
        }
        return $c;
    }
    /**
     * @param array $params
     * @param bool $excludeTestUsers
     * @return QMUserVariableRelationship[]
     */
    public static function getUserVariableRelationships(array $params, bool $excludeTestUsers = true): array {
        \App\Logging\ConsoleLog::info("Getting user variable relationships with params: ".\App\Logging\QMLog::print_r($params, true)."...");
        $qb = self::getUserVariableRelationshipsQB($params);
        $userId = QMArr::getValue($params,self::FIELD_USER_ID);
        if($excludeTestUsers && !$userId){$qb = self::excludeUnAnalyzableUsers($qb);}
        if($userId === UserIdProperty::USER_ID_POPULATION){le("Why are we getting user_variable_relationships for population user?");}
        $rows = $qb->getArray();
        $correlations = [];
        if (AppMode::isApiRequest() && count($rows) > 200) {
            QMLog::error("Got more than 200 user_variable_relationships!");
        }
        if ($rows) {
            foreach ($rows as $row) {
                $correlation = new self($row);
                $correlations[] = $correlation;
            }
        }
        return $correlations;
    }
    /**
     * @return float
     * @internal param bool $round
     */
    public function getDailyValuePredictingHighOutcome(): ?float {
        $val = $this->getAttribute(UserVariableRelationship::FIELD_VALUE_PREDICTING_HIGH_OUTCOME);
        if($val === null && $this->laravelModel){
            return $this->valuePredictingHighOutcome = $this->l()->getAttribute(UserVariableRelationship::FIELD_VALUE_PREDICTING_HIGH_OUTCOME);
        }
        return $val;
    }
    /**
     * @return float
     * @internal param bool $round
     */
    public function getDailyValuePredictingLowOutcome(): ?float {
        $val = $this->getAttribute(UserVariableRelationship::FIELD_VALUE_PREDICTING_LOW_OUTCOME);
        if($val === null && $this->laravelModel){
            return $this->valuePredictingLowOutcome = $this->l()->getAttribute(UserVariableRelationship::FIELD_VALUE_PREDICTING_LOW_OUTCOME);
        }
        return $val;
    }
    /**
     * @param array $params
     * @return QMUserVariableRelationship[]|QMGlobalVariableRelationship[]
     * @throws AlreadyAnalyzedException
     * @throws AlreadyAnalyzingException
     * @throws DuplicateFailedAnalysisException
     * @throws NoUserVariableRelationshipsToAggregateException
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public static function getOrCreateUserOrGlobalVariableRelationships(array $params): array{
        $params = QMStr::properlyFormatRequestParams($params, self::getLegacyRequestParameters());
        QMAPIValidator::validateParams(self::getAllowedRequestParameters(), array_keys($params),
            'user_variable_relationships/correlations_get');
        if (isset($params['aggregated'])) {
            return QMGlobalVariableRelationship::getOrCreateGlobalVariableRelationships($params);
        }
        /** @var array $params */
        [
            $qb,
            $params,
            $correlations
        ] = self::getUserVariableRelationshipsQBAndFormatParams($params);
        $effectVariableName = $params['effectVariableName'] ?? null;
        $causeVariableName = $params['causeVariableName'] ?? null;
        if (!$correlations && self::shouldWeLimitToOutcomesOfInterest($params)) {
            $params['outcomesOfInterest'] = false;
            /** @var QMQB $qb */
            if($qb->whereClausesContainString(UserVariable::FIELD_OUTCOME_OF_INTEREST)){
                $correlations = self::getOrCreateUserOrGlobalVariableRelationships($params); // No need to do this if we weren't already restricting
            }
        }
        if (!$correlations &&
            isset($params['fallbackToAggregatedCorrelations']) &&
            $params['fallbackToAggregatedCorrelations']) {
            unset($params['fallbackToAggregatedCorrelations']);
            $correlations = QMGlobalVariableRelationship::getOrCreateGlobalVariableRelationships($params);
        }
        if ((isset($params['effectVariableName']) || isset($params['effectVariableId'])) &&
            (isset($params['causeVariableName']) || isset($params['causeVariableId'])) &&
            isset($params['fallbackToStudyForCauseAndEffect']) &&
            !count($correlations)) {
            $correlations = [QMStudy::getUserStudyAndFallbackToPopulationStudy()];
        }
		if($causeVariableName && $effectVariableName){
			return QMVariableRelationship::putExactMatchFirst($causeVariableName, $effectVariableName, $correlations);
		}
		return $correlations;
    }
    /**
     * @param QMQB $qb
     * @param array $params
     * @internal param int $userId
     */
    public static function joinUserVariables(QMQB $qb, array $params){
        $db = ReadonlyDB::db();
        $userId = QMArr::getValue($params, self::FIELD_USER_ID);
        if($userId){
            $qb->columns[] = 'cuv.'. UserVariable::FIELD_LAST_PROCESSED_DAILY_VALUE." as lastProcessedDailyValueForCauseInCommonUnit";
            $qb->columns[] = 'cuv.'. UserVariable::FIELD_DEFAULT_UNIT_ID." as causeUserUnitId";
            $qb->columns[] = 'cuv.'.UserVariable::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT." as causeLatestTaggedMeasurementStartAt";
            $qb->columns[] = 'cuv.is_public AS causeVariableIsPublic';
            $qb->columns[] = 'euv.is_public AS effectVariableIsPublic';
            $qb->columns[] = 'euv.outcome_of_interest AS effectVariableIsOutcomeOfInterest';
            $qb->columns[] = $db->raw('COALESCE(cuv.minimum_allowed_value, cvars.minimum_allowed_value) as predictorMinimumAllowedValue');
            $qb->columns[] = $db->raw('COALESCE(euv.minimum_allowed_value, evars.minimum_allowed_value) as outcomeMinimumAllowedValue');
            $qb->columns[] = $db->raw('COALESCE(cuv.maximum_allowed_value, cvars.maximum_allowed_value) as predictorMaximumAllowedValue');
            $qb->columns[] = $db->raw('COALESCE(euv.maximum_allowed_value, evars.maximum_allowed_value) as outcomeMaximumAllowedValue');
            $qb->columns[] = $db->raw('CASE
                WHEN cuv.filling_value != -1 THEN cuv.filling_value
                WHEN cvars.filling_value != -1 THEN cvars.filling_value
                ELSE null
                END as predictorFillingValue');
            $qb->columns[] = $db->raw('CASE
                WHEN euv.filling_value != -1 THEN euv.filling_value
                WHEN evars.filling_value != -1 THEN evars.filling_value
                ELSE null
                END as outcomeFillingValue');
            $qb->leftJoin('user_variables AS cuv', static function(JoinClause $join) use ($userId){
                $join->on(self::TABLE.'.cause_variable_id', '=', 'cuv.variable_id')->where('cuv.user_id', '=', $userId);
            });
            $qb->leftJoin('user_variables AS euv', static function(JoinClause $join) use ($userId){
                $join->on(self::TABLE.'.effect_variable_id', '=', 'euv.variable_id')->where('euv.user_id', '=', $userId);
            });
        }else{
            $qb->columns[] = $db->raw('cvars.minimum_allowed_value as predictorMinimumAllowedValue');
            $qb->columns[] = $db->raw('evars.minimum_allowed_value as outcomeMinimumAllowedValue');
            $qb->columns[] = $db->raw('cvars.maximum_allowed_value as predictorMaximumAllowedValue');
            $qb->columns[] = $db->raw('evars.maximum_allowed_value as outcomeMaximumAllowedValue');
            $qb->columns[] = $db->raw('CASE
                WHEN cvars.filling_value != -1 THEN cvars.filling_value
                ELSE null
                END as predictorFillingValue');
            $qb->columns[] = $db->raw('CASE
                WHEN evars.filling_value != -1 THEN evars.filling_value
                ELSE null
                END as outcomeFillingValue');
        }
    }
    /**
     * @param QMUserVariable $cause
     * @param QMUserVariable $effect
     * @return bool
     */
    public function noNewMeasurements(QMUserVariable $cause, QMUserVariable $effect): bool{
        if(\App\Utils\Env::get('DEBUG_VARIABLE_NAME')){
            return false;
        }
        if(\App\Utils\Env::get('DEBUG_CAUSE_VARIABLE_NAME')){
            return false;
        }
        if(\App\Utils\Env::get('DEBUG_EFFECT_VARIABLE_NAME')){
            return false;
        }
        $c = $cause->getNumberOfRawMeasurementsWithTagsJoinsChildren();
        $e = $effect->getNumberOfRawMeasurementsWithTagsJoinsChildren();
        $lastE = $this->effectNumberOfRawMeasurements;
        $lastC = $this->causeNumberOfRawMeasurements;
        if($lastC === $c && $lastE === $e){
            QMLog::info("No new measurements since last correlation calculation (".$this->getTimeSinceLastAnalyzedHumanString().
                ") for $cause->name and $effect->name");
            return true;
        }
        if($lastC * 1.1 > $c && $lastE * 1.1 > $e){
            QMLog::info("there are not enough new measurements since last correlation calculation (".$this->getTimeSinceLastAnalyzedHumanString().
                ") for $cause->name and $effect->name");
            return true;
        }
        return false;
    }
    /**
     * @return bool
     */
    public function algorithmModifiedSinceLastAnalysis(): bool {
        $updated = $this->getUpdatedAt();
        if(!$updated){
            $this->logInfo("Never updated before");
            return true;
        }
        $result = strtotime(self::ALGORITHM_MODIFIED_AT) > strtotime($updated);
        if($result){
            $this->logInfo("ALGORITHM_MODIFIED_AT ".self::ALGORITHM_MODIFIED_AT." and updated_at is $updated");
        }
        return $result;
    }
    /**
     * @param QMUserVariable $cause
     * @param QMUserVariable $effect
     * @return bool
     */
    private function deletedMeasurementsSinceLastCalculation(QMUserVariable $cause, QMUserVariable $effect): bool{
        if($this->causeNumberOfRawMeasurements > $cause->getNumberOfRawMeasurementsWithTagsJoinsChildren()){
            QMLog::error("$cause->name has " . $cause->getNumberOfRawMeasurementsWithTagsJoinsChildren() .
                " measurements but " . "had " . $this->causeNumberOfRawMeasurements . " at last calculation");
            return true;
        }
        if($this->effectNumberOfRawMeasurements > $effect->getNumberOfRawMeasurementsWithTagsJoinsChildren()){
            QMLog::error("$effect->name has " . $effect->getNumberOfRawMeasurementsWithTagsJoinsChildren() .
                " measurements but had $this->effectNumberOfRawMeasurements at last calculation");
            return true;
        }
        return false;
    }
    /**
     * @param QMUserVariable $cause
     * @param QMUserVariable $effect
     * @return bool
     */
    public static function shouldWeCalculate(QMUserVariable $cause, QMUserVariable $effect): bool{
        if(self::stupidCategoryPair($cause->getVariableCategoryName(),
            $effect->getVariableCategoryName())){
            QMLog::info("Not correlating $cause with $effect because it's a stupidCategoryPair");
            return false;
        }
        if($cause->variableCategoryId !== EconomicIndicatorsVariableCategory::ID && $cause->variableId === $effect->variableId){
            QMLog::info("Not correlating $cause->name with itself");
            return false;
        }
        if($c = Memory::getNewlyCalculatedUserVariableRelationship($cause->userId, $cause->variableId, $effect->variableId)){
            QMLog::info("Already calculated correlation for $cause->name and $effect->name for ".$effect->getQMUser().": $c");
            return false;
        }
        if($effect->isOutcome() === false){
            $effect->logInfo("Not going to calculate As Effect because isOutcome is false");
            return false;
        }
        $existingCorrelation = $cause->getQMUser() ? $cause->getQMUser()->getExistingCorrelation($cause->variableId, $effect->variableId) : null;
        if($existingCorrelation){
            $causeDuration = $cause->getDurationOfAction();
            if ($existingCorrelation->durationOfAction !== $cause->getDurationOfAction()) {
                $existingCorrelation->logError("Recalculating because duration of action changed from " .
                    "$existingCorrelation->durationOfAction seconds to $causeDuration seconds");
                return true;
            }
            if ($existingCorrelation->algorithmModifiedSinceLastAnalysis()) {
                return true;
            }
            if($existingCorrelation->deletedMeasurementsSinceLastCalculation($cause, $effect)){
                return true;
            }
            if($existingCorrelation->noNewMeasurements($cause, $effect)){
                return false;
            }
        }
        return true;
    }
    /**
     * @return array
     */
    public static function getLegacyProperties(): array{
        // Legacy => Current
        return [];
    }
    /**
     * @return array
     */
    public static function getLegacyRequestParameters(): array{
        // Legacy => Current
        $legacyRequestParameters = [
            'cause'                           => 'causeVariableName',
            'causeId'                         => 'causeVariableId',
            'causeUnit'                       => 'causeVariableDefaultUnitAbbreviatedName',
            'correlation'                     => 'correlationCoefficient',
            'effect'                          => 'effectVariableName',
            'effectId'                        => 'effectVariableId',
            'effectUnit'                      => 'effectVariableDefaultUnitAbbreviatedName',
            'fallbackToGlobalVariableRelationships' => 'fallbackToAggregatedCorrelations',
            'lastUpdated'                     => 'updatedAt'
        ];
        return array_merge($legacyRequestParameters, self::getLegacyProperties());
    }
    /**
     * @return string
     */
    public function getLogMetaDataString(): string {
        $str = "";
        if($this->causeVariableId){$str .= "\ncause: ".$this->getCauseVariableName()."\n";}
        if($this->effectVariableId){$str .= "effect: ".$this->getEffectVariableName()."\n";}
        if($this->userId){$str .= "user: ".$this->getUser()->getLoginNameAndIdString()."\n";}
        return $str;
    }
    /**
     * @return bool
     */
    public function userDownVoted(): bool {
        return $this->getUserVoteValue() === 0;
    }
    /**
     * @return bool
     */
    public function userUpVoted(): bool {
        return $this->getUserVoteValue() === 1;
    }
    /**
     * @return bool
     */
    public function userDidNotVote(): bool {
        $vote = $this->getUserVoteValue();
        return $vote === null || $vote === false;
    }
    /**
     * @param int|null $userId
     * @return int|null
     */
    public function getUserVoteValue(int $userId = null):?int {
        if($this->userVote === null){
	        return $this->userVote = $this->getCorrelation()->getUserVoteValue();
        }
        return $this->userVote;
    }
    /**
     * @return bool
     */
    public function qmScoreIsBelowAverage(): bool{
        return $this->qmScore < $this->getUser()->getAverageQmScore();
    }
    /**
     * @return bool
     * @throws NotEnoughDataException
     */
    private function lastValueIsTheSameAsThatPredictingDesiredOutcome(): bool{
        $last = $this->getLastProcessedDailyValueForCauseInCommonUnit();
        $predicts = $this->getValuePredictingDesiredOutcome();
        return $last === $predicts;
    }
    /**
     * @return bool
     * @throws NotEnoughDataException
     */
    public function isActionableInsight(): bool{
        if($this->userDownVoted()){
            return false;
        }
        if($this->userUpVoted()){
            return true;
        }
        if($this->getCauseVariableIsOutcome()){
            return false;
        }  // Most efficient first
        if(!$this->getEffectVariableIsOutcomeOfInterest()){
            return false;
        }
        if($this->qmScoreIsBelowAverage()){
            return false;
        }  // Very slow to average user_variable_relationships
        if(!$this->lastProcessedDailyValueIsValid()){
            return false;
        }
        if($this->lastValueIsTheSameAsThatPredictingDesiredOutcome()){
            return false;
        }
        if(!$this->getQMAggregatedCorrelation()){
            return false;
        }
        if($this->getCorrelationIsContradictoryToOptimalValues()){
            return false;
        }
        return true;
    }
    /**
     * @param bool $force
     * @return array
     * @throws NoDeviceTokensException
     * @throws TooSlowToAnalyzeException
     */
    public function sendPushNotification(bool $force = false): array {
        if(!$force && AppMode::isApiRequest()){return [];}
        if(!$force && $this->userId !== UserIdProperty::USER_ID_MIKE){return [];}
        $u = $this->getUser();
        return $u->notifyByPushData(new CorrelationPushNotificationData($this));
    }
    /**
     * @return array
     * @throws NoDeviceTokensException
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function sendPushNotificationIfActionableInsight(): array{
        if(!$this->isActionableInsight()){
            return [];
        }
        return $this->sendPushNotification();
    }
    /**
     * @return bool
     */
    public function savePartialAnalysis(): bool{
        $this->truncateInternalErrorMessage();
        try {
            return $this->save();
        } catch (ModelValidationException $e) {
            le($e);
        }
    }
    /**
     * @param string|null $reason
     * @return bool
     * @throws \App\Exceptions\ModelValidationException
     */
    public function saveAnalysis(string $reason = null): bool{
        $this->logInfo(__METHOD__);
        unset(self::$storageQueue[$this->getUniqueIndexIdsSlug()]);  // Don't want outdated studies cached
        Memory::addNewlyCalculatedUserVariableRelationship($this);
        //$this->sendPushNotificationIfActionableInsight();  // This causes infinite loop
        $this->setAnalysisEndedAtAndStatusUpdated();
        $this->setDbRow(clone $this);
        $this->validateAnalysisBeforeSave();
        $result = $this->save();
        $this->updateOptimalValueSentencesIfNecessary();
        try {
            $ac = GlobalVariableRelationship::findByVariableNamesOrIds(
				$this->causeVariableId, $this->effectVariableId);
			if($ac){
				if($ac->id !== $this->aggregateCorrelationId){
					$this->updateDbRow([UserVariableRelationship::FIELD_AGGREGATE_CORRELATION_ID => $ac->id]);
				}
				$ac->newest_data_at = db_date(time());
				$ac->status = GlobalVariableRelationshipStatusProperty::STATUS_WAITING;
				$ac->save();
			}
        } catch (NoUserVariableRelationshipsToAggregateException $e) {
            le($e);
        }
        $this->getCauseQMVariable()->unsetOutcomes();
        $this->getEffectQMVariable()->unsetPredictors();
        return $result;
    }
    /**
     * @param int $causeId
     * @param int $effectId
     * @param float $correlationCoefficient
     * @return bool|int
     */
    public static function makeCorrelation(int $causeId, int $effectId, float $correlationCoefficient){
        $user = QMAuth::getQMUser(null, true);
        $cause = QMUserVariable::getOrCreateById($user->id, $causeId);
        $effect = QMUserVariable::getOrCreateById($user->id, $effectId);
        $qb = self::writable()->where(self::FIELD_CAUSE_VARIABLE_ID, $causeId)
            ->where(self::FIELD_EFFECT_VARIABLE_ID, $effectId)
            ->where(self::FIELD_USER_ID, $user->id);
        return Writable::insertOrUpdate($qb, [
            'user_id'                                        => $user->id,
            'cause_variable_id'                                       => $causeId,
            'effect_variable_id'                                      => $effectId,
            'forward_pearson_correlation_coefficient'        => $correlationCoefficient,
            'created_at'                                     => date('Y-m-d H:i:s'),
            UserVariableRelationship::FIELD_CAUSE_USER_VARIABLE_ID  => $cause->getUserVariableId(),
            UserVariableRelationship::FIELD_EFFECT_USER_VARIABLE_ID => $effect->getUserVariableId(),
            UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID => $cause->variableCategoryId,
            UserVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID => $effect->variableCategoryId,
        ]);
    }
    /**
     * @param int $userId
     * @param int $causeVariableId
     * @param int $effectVariableId
     * @param string $reason
     * @return QMUserVariableRelationship
     */
    public static function deleteByIds(int $userId, int $causeVariableId, int $effectVariableId, string $reason): QMUserVariableRelationship{
        $c = self::findByNamesOrIds($userId, $causeVariableId, $effectVariableId);
        if(!$c){
            throw new UserVariableRelationshipNotFoundException("Can't delete user variable relationship because userId $userId, ".
                " causeVariableId $causeVariableId, effectVariableId $effectVariableId not found!");
        }
        $c->softDelete([], $reason);
        return $c;
    }
    public function updateOptimalValueSentencesIfNecessary(): void {
        $this->logInfo(__METHOD__);
        $cause = $this->getOrSetCauseQMVariable();
        $oldBest = $cause->getBestUserVariableRelationship();
        $newScoreMinusBuffer = $this->getQmScore() - 0.0001;
        if(!$oldBest || $oldBest->qm_score < $newScoreMinusBuffer){$cause->updateBestCorrelationAsCause($this);}
        $effect = $this->getOrSetEffectQMVariable();
        $oldBest = $effect->getBestUserVariableRelationship();
        if(!$oldBest || $oldBest->qm_score < $newScoreMinusBuffer){$effect->updateBestCorrelationAsEffect($this);}
	    $isPublic = $this->getIsPublic();
	    if($isPublic || $this->l()->global_variable_relationship_id){
            try {
                $agg = $this->getOrCreateQMGlobalVariableRelationship();
                if(!$agg->effectVariableId){
                    $agg = $this->getOrCreateQMGlobalVariableRelationship();
                    le("No effect id on $agg");
                }
            } catch (NoUserVariableRelationshipsToAggregateException $e) {
                le($e);
            }
            $agg->updateOptimalValueSentencesIfNecessary();
        }
	    $cause = $this->getCauseUserVariable();
	    $effect = $this->getEffectUserVariable();
	    UserVariableBestEffectVariableIdProperty::calculate($cause);
	    UserVariableBestCauseVariableIdProperty::calculate($effect);
	    UserVariableOptimalValueMessageProperty::calculate($cause);
	    UserVariableOptimalValueMessageProperty::calculate($effect);
		$cause->save();
		$effect->save();
    }
    /**
     * Calculate Optimal Pearson Product
     * @param array $causeMeasurementValueArrayFromPairs
     */
    public function setOptimalPearsonProduct(array $causeMeasurementValueArrayFromPairs){
        $this->optimalPearsonProduct =
            CorrelationOptimalPearsonProductProperty::calculateOptimalPearsonProduct($causeMeasurementValueArrayFromPairs,
                $this);
    }
    /**
     * @return QMUserVariableRelationship[]
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getOverDelays(): array {
        return CorrelationCorrelationsOverDelaysProperty::pluckOrCalculate($this);
    }
    /**
     * @return QMUserVariableRelationship[]
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getOverDurations(): array {
        return CorrelationCorrelationsOverDurationsProperty::pluckOrCalculate($this);
    }
    /**
     * @return HighchartConfig
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function calculateCorrelationsOverOnsetDelaysAndGenerateChartConfig(): HighchartConfig {
        $this->logInfo(__METHOD__);
        $this->validateMeasurements();
        $coefficients = $this->getCoefficientsByOnsetDelay();
        $this->saveOverDelays($coefficients);
        CorrelationOnsetDelayWithStrongestPearsonCorrelationProperty::calculate($this);
        CorrelationStrongestPearsonCorrelationCoefficientProperty::calculate($this);
        CorrelationPearsonCorrelationWithNoOnsetDelayProperty::calculate($this);
        CorrelationAverageForwardPearsonCorrelationOverOnsetDelaysProperty::calculate($this);
        try {
            CorrelationAverageReversePearsonCorrelationOverOnsetDelaysProperty::calculate($this);
        } catch (NotEnoughDataException $e) {
            $this->logInfo(__METHOD__.": ".$e->getMessage());
        }
        $this->correlationsOverOnsetDelaysChartConfig = $config = new CorrelationsOverOnsetDelaysHighchart($this);
        $config->populate($coefficients);
        $this->charts = $charts = $this->charts ?? new CorrelationChartGroup();
        $charts->getCorrelationsOverOnsetDelaysLineChart()->setHighchartConfig($config);
        try {
            CorrelationPredictivePearsonCorrelationCoefficientProperty::calculate($this);
        } catch (NotEnoughDataException $e) {
            $this->logInfo(__METHOD__.": ".$e->getMessage());
        }
        return $config;
    }
    /**
     * @return QMGlobalVariableRelationship
     */
    public function setQMAggregatedCorrelation(): ?QMGlobalVariableRelationship  {
        $this->aggregatedCorrelation = $c = QMGlobalVariableRelationship::getByIds(
            $this->getCauseVariableId(), $this->getEffectVariableId());
        if(!$c){
            $this->aggregatedCorrelation = false;
            return null;
        }
        if(!$c->effectVariableId){
            $c = QMGlobalVariableRelationship::getByIds($this->getCauseVariableId(),
                $this->getEffectVariableId());
            le("No effect id on $c");
        }
        return $c;
    }
    /**
     * @return QMGlobalVariableRelationship
     */
    public function getQMAggregatedCorrelation(): ?QMGlobalVariableRelationship {
        $c = $this->aggregatedCorrelation;
        if($c === false){return null;}
        if($c){return $c;}
        return $this->setQMAggregatedCorrelation();
    }
    /**
     * @return QMGlobalVariableRelationship
     */
    public function getOrCreateQMGlobalVariableRelationship(): QMGlobalVariableRelationship {
        if($this->aggregatedCorrelation){return $this->aggregatedCorrelation;}
        $c = QMGlobalVariableRelationship::getOrCreateByIds($this->getCauseVariableId(),
            $this->getEffectVariableId());
		if(!$c->durationOfAction){le('!$c->durationOfAction');}
        if(!$c->effectVariableId){
            le("No effect id on $c");
        }
        return $this->aggregatedCorrelation = $c;
    }

	/**
	 * @return float
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 */
	protected function calculateAverageEffect(): float {
		return CorrelationAverageEffectProperty::calculate($this);
	}
    /**
     * @return HighchartConfig
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function calculateCorrelationOverDurationsOfActionAndGenerateChartConfig(): HighchartConfig {
        $this->logInfo(__METHOD__);
        $this->validateMeasurements();
        $config = new CorrelationsOverDurationsOfActionHighchart($this);
        if($correlations_over_durations = $this->getCoefficientsByDuration()){
            $config->populate($correlations_over_durations);
        }
        $this->charts = $charts = $this->charts ?? new CorrelationChartGroup();
        $charts->getCorrelationsOverDurationsChart()->setHighchartConfig($config);
        return $this->correlationsOverDurationsOfActionChartConfig = $config;
    }
    /**
     * @param string $reason
     * @throws AlreadyAnalyzedException
     * @throws InsufficientVarianceException
     * @throws NotEnoughDataException
     * @throws NotEnoughMeasurementsForCorrelationException
     * @throws NotEnoughOverlappingDataException
     * @throws StupidVariableNameException
     * @throws TooSlowToAnalyzeException
     */
    public function analyzeFully(string $reason): void {
		if($this->getUser()->isDemoUser()){
		    $this->logError( "Why are we analyzing for demo user?  These should just be copied!");
		}
		$this->throwExceptionIfCauseAndEffectAreSameButHaveDifferentProperties($this->getEffectQMVariable());
        try {
            $this->beforeAnalysis($reason);
        } catch (AlreadyAnalyzingException $e) {
            $this->logInfo(__METHOD__.": ".$e->getMessage());
            return;
            le($e);
        }
        try {
            $this->analyzePartially($reason);
        } catch (InvalidVariableValueException $e) {
            le($e);
        }
        $this->savePartialAnalysis();
        $charts = $this->getOrSetCharts();
        $charts->setHighchartConfigs();
		if(!$charts->highchartsPopulated()){le("!charts->highchartsPopulated()");}
        $this->setUserAndInternalErrorMessage(null); // Set error null after hyper-parameter calculations
	    if(!isset($this->reversePearsonCorrelationCoefficient)){
		    CorrelationReversePearsonCorrelationCoefficientProperty::calculate($this);
	    }
	    if(!isset($this->reversePearsonCorrelationCoefficient)){
		    le("Why is reversePearsonCorrelationCoefficient not set?");
	    }
        try {
            $this->saveAnalysis($reason);
        } catch (ModelValidationException $e) {
            le($e);
        }
    }
    /**
     * @return bool
     */
    public function calculateIsPublic(): bool {
        $cause = $this->causeVariableIsPublic;
        $effect = $this->effectVariableIsPublic;
        $public = $cause && $effect;
        if(isset($this->causeVariable, $this->effectVariable) && !isset($this->isPublic)){
            $public = $this->causeVariable->getIsPublic() && $this->effectVariable->getIsPublic();
        }
        $this->setIsPublic($public);
        return $public;
    }
    /**
     * @param QMUserVariable $v
     */
    private function setCauseProperties(QMUserVariable $v){
        $this->causeUserVariableId = $v->getUserVariableId();
        $this->causeVariable = $v;
        $this->causeVariableCategory = $v->getQMVariableCategory();
        $this->causeVariableCategoryId = $v->getQMVariableCategory()->getId();
        $this->causeVariableCategoryName = $v->getQMVariableCategory()->getNameAttribute();
        $this->causeVariableCommonUnitAbbreviatedName = $v->getCommonUnit()->abbreviatedName;
        $this->causeVariableCommonUnitId = $v->getCommonUnit()->id;
        $this->causeVariableId = $v->getVariableIdAttribute();
        $this->causeVariableName = $v->name;
        $this->userId = $v->getUserId();
    }
    /**
     * @param QMUserVariable $v
     */
    private function setEffectProperties(QMUserVariable $v){
        $this->setUserId($v->getUserId());
        $this->effectUserVariableId = $v->getUserVariableId();
        $this->effectVariable = $v;
        $this->effectVariableCategory = $v->getQMVariableCategory();
        $this->effectVariableCategoryId = $v->variableCategoryId;
        $this->effectVariableCategoryName = $v->getQMVariableCategory()->getNameAttribute();
        $this->effectVariableCommonUnitAbbreviatedName = $v->getCommonUnit()->abbreviatedName;
        $this->effectVariableCommonUnitId = $v->getCommonUnit()->id;
        $this->effectVariableId = $v->variableId;
        $this->effectVariableName = $v->name;
    }
    private function setFillingValues(){
        $this->setPredictorFillingValue();
        $this->setOutcomeFillingValue();
    }
    /**
     * @return int
     */
    public function getEffectNumberOfMeasurements(): int{
        return $this->effectNumberOfRawMeasurements;
    }
    /**
     * @return int
     */
    public function getCauseChanges(): int{
        return $this->causeChanges;
    }
    /**
     * @return int
     */
    public function getEffectChanges(): int{
        return $this->effectChanges;
    }
    /**
     * @return int
     */
    public function getUserId(): ?int {
        if(isset($this->causeVariable)){
            $this->userId = $this->getOrSetCauseQMVariable()->userId;
        }
        return (int)$this->userId;
    }
    /**
     * @param int $userId
     */
    public function setUserId(int $userId){
        $this->userId = $userId;
    }
    /**
     * @return DailyMeasurement[]
     * @throws NotEnoughDataException
     */
    public function getEffectMeasurements(): array {
        if($this->effectMeasurements === null){
            $this->setMeasurements();
        }
        return $this->effectMeasurements;
    }
    /**
     * @throws NotEnoughDataException
     */
    public function setMeasurements(): void {
        CorrelationCauseNumberOfProcessedDailyMeasurementsProperty::calculate($this);
        CorrelationEffectNumberOfProcessedDailyMeasurementsProperty::calculate($this);
    }
    /**
     * @return DailyMeasurement[]|ProcessedQMMeasurement[]
     * @throws NotEnoughMeasurementsForCorrelationException
     * @throws NotEnoughOverlappingDataException
     */
    public function getMeasurementsFromCauseVariable(): array{
        $cause = $this->getOrSetCauseQMVariable();
        $startAt = $this->calculateExperimentStartAt();
        $endAt = $this->calculateExperimentEndAt();
        if($startAt > $endAt){throw new NotEnoughOverlappingDataException($this);}
        $cMeasurements = $cause->getDailyMeasurementsWithTagsAndFillingInTimeRange($startAt, $endAt);
        if(!$cMeasurements){
            throw new NotEnoughMeasurementsForCorrelationException(
                "No $cause->name measurements found between $startAt and $endAt. ", $this);
        }
        MeasurementStartTimeProperty::verifyChronologicalOrder($cMeasurements);
        return $this->causeMeasurements = $cMeasurements;
    }
    /**
     * @return DailyMeasurement[]|ProcessedQMMeasurement[]
     * @throws NotEnoughMeasurementsForCorrelationException
     * @throws NotEnoughOverlappingDataException
     */
    public function getMeasurementsFromEffectVariable(): array{
        $effect = $this->getOrSetEffectQMVariable();
        $startAt = $this->calculateExperimentStartAt();
        $endAt = $this->calculateExperimentEndAt();
        if($startAt > $endAt){throw new NotEnoughOverlappingDataException($this);}
        $eMeasurements = $effect->getDailyMeasurementsWithTagsAndFillingInTimeRange($startAt, $endAt);
        if(!$eMeasurements){
            throw new NotEnoughMeasurementsForCorrelationException(
                "No $effect->name measurements found between $startAt and $endAt. ", $this);
        }
        MeasurementStartTimeProperty::verifyChronologicalOrder($eMeasurements);
        return $this->effectMeasurements = $eMeasurements;
    }


    /**
     * @return float
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    private function getAverageCause(): float {
        return Stats::average($this->getCauseValues());
    }
    /**
     * @param int|null $userId
     * @return QMUserVariableRelationship
     */
    public static function getMostRecent(int $userId = null): ?QMUserVariableRelationship{
        $params = [
            'sort'  => '-updatedAt',
            'limit' => 1
        ];
        if($userId){
            $params['userId'] = $userId;
        }
        $userVariableRelationships = self::getUserVariableRelationships($params);
        return $userVariableRelationships[0] ?? null;
    }
    /**
     * @param QMUserVariable|int|string $causeVariableNameOrId
     * @return QMUserVariable
     */
    public function setCauseVariable($causeVariableNameOrId) {
        if(!$causeVariableNameOrId instanceof QMUserVariable){
            $causeVariableNameOrId = QMUserVariable::getOrCreateById($this->getUserId(), $causeVariableNameOrId->getId());
        }
        $this->setCauseProperties($causeVariableNameOrId);
        return $causeVariableNameOrId;
    }

    /**
     * @param float $valueInCommonUnit
     * @param string $type
     */
    private function validateCauseValue(float $valueInCommonUnit, string $type) {
        $cause = $this->getOrSetCauseQMVariable();
        try {
            $cause->validateValueForCommonVariableAndUnit($valueInCommonUnit, $type, $this->getDurationOfAction());
        } catch (InvalidVariableValueException $e) {
            $message = "$type value $valueInCommonUnit not valid for $cause";
            $this->logErrorOrInfoIfTesting($message);
            if(!AppMode::isApiRequest()){ // Might want to do this in API requests, too, at some point
                //$this->deleteFromDatabase(false, $message); // This gets rid of old screwed up analyses
                $this->updateDbRow([
                    self::FIELD_INTERNAL_ERROR_MESSAGE => $message,
                    self::FIELD_USER_ERROR_MESSAGE => $message,
                ]);
            }
        }
    }
    public function validateValuesWithinAllowedRangeForCommonVariableAndUnit(){
        if($this->causeVariable){
            $val = $this->getDailyValuePredictingHighOutcome();
            $this->validateCauseValue($val, 'avgDailyValuePredictingHighOutcome');
            $val = $this->getDailyValuePredictingLowOutcome();
            $this->validateCauseValue($val, 'avgDailyValuePredictingLowOutcome');
        }
    }
    /**
     * @param QMUserVariable|int|string $v
     * @return QMUserVariable
     */
    public function setEffectVariable($v){
        if(!$v instanceof QMUserVariable){
            $v = QMUserVariable::getOrCreateById($this->getUserId(), $v);
        }
        $this->setEffectProperties($v);
        return $v;
    }
    /**
     * @return QMUser
     */
    public function getUser(): ?QMUser {
        return QMUser::find($this->getUserId());
    }
	/**
	 * @return QMUserStudy
	 */
	public function findInMemoryOrNewQMStudy(): QMStudy{
		$study = $this->findQMStudyInMemory();
		if(!$study){
			$study = QMUserStudy::findInMemoryOrNewQMStudy($this->getCauseVariableName(),
				$this->getEffectVariableName(),
				$this->getUserId(),
				StudyTypeProperty::TYPE_INDIVIDUAL);
			$study->setStatistics($this);
		}
		return $study;
	}
	/**
	 * @return \App\Studies\QMStudy|null
	 */
	protected function findQMStudyInMemory(): ?QMStudy{
		$id = $this->getStudyId();
		$s = Study::findInMemoryOrDB($id);
		if($s){
			return $s->getDBModel();
		}
		return null;
	}
    /**
     * @return bool
     */
    private function getCorrelationIsContradictoryToOptimalValues(): bool{
        $this->correlationIsContradictoryToOptimalValues = false;
        if($this->avgDailyValuePredictingLowOutcome === null || $this->avgDailyValuePredictingHighOutcome === null){
            return $this->correlationIsContradictoryToOptimalValues;
        }
        if($this->correlationCoefficient > 0 && $this->avgDailyValuePredictingHighOutcome < $this->avgDailyValuePredictingLowOutcome){
            $this->correlationIsContradictoryToOptimalValues = true;
            //$this->valuePredictingHighOutcomeExplanation = null;
            //$this->valuePredictingLowOutcomeExplanation = null;
            if(abs($this->correlationCoefficient) > 0.2 && !QMUser::isTestUserByIdOrEmail($this->userId)){
                // TODO: Deal with UserVariableRelationship is positive but valuePredictingHighOutcome is less than valuePredictingLowOutcome
                $this->logAndSetCorrelationError('User Variable Relationship is positive but valuePredictingHighOutcome is less than valuePredictingLowOutcome!');
            }
        }
        if($this->correlationCoefficient < 0 && $this->avgDailyValuePredictingHighOutcome > $this->avgDailyValuePredictingLowOutcome){
            $this->correlationIsContradictoryToOptimalValues = true;
            //$this->valuePredictingHighOutcomeExplanation = null;
            //$this->valuePredictingLowOutcomeExplanation = null;
            $abs = abs($this->correlationCoefficient);
            if($abs > 0.2 && !QMUser::isTestUserByIdOrEmail($this->userId)){
                // TODO: Deal with UserVariableRelationship is positive but valuePredictingHighOutcome is less than valuePredictingLowOutcome
                $this->logAndSetCorrelationError('User Variable Relationship is negative but valuePredictingHighOutcome is greater than valuePredictingLowOutcome!');
            }
        }
        return $this->correlationIsContradictoryToOptimalValues;
    }
    /**
     * @param int $userId
     * @param int $causeVariableId
     * @param int $effectVariableId
     * @return bool|QMUserVariableRelationship
     */
    public static function getExistingUserVariableRelationshipByVariableIds(int $userId, int $causeVariableId, int $effectVariableId){
        $fromMemory = Memory::getNewlyCalculatedUserVariableRelationship($userId, $causeVariableId, $effectVariableId);
        if($fromMemory !== null){return $fromMemory;}
        $row = UserVariableRelationship::where(self::FIELD_USER_ID, $userId)
            ->where(self::FIELD_CAUSE_VARIABLE_ID, $causeVariableId)
            ->where(self::FIELD_EFFECT_VARIABLE_ID, $effectVariableId)
            ->first();
        if($row){
            return new QMUserVariableRelationship($row);
        }
        return false;
    }
    /**
     * @throws InsufficientVarianceException
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    private function setEffectValuesExpectedToBeHigherLowerThanAverage(){
        $lower = [];
        $higher = [];
        $pairs = $this->getPairs();
        foreach ($pairs as $pair) {
            $highDiff = abs($this->avgDailyValuePredictingHighOutcome - $pair->causeMeasurementValue);
            $lowDiff = abs($this->avgDailyValuePredictingLowOutcome - $pair->causeMeasurementValue);
            if($highDiff > $lowDiff){
                $lower[] = $pair->effectMeasurementValue;
            }else{
                $higher[] = $pair->effectMeasurementValue;
            }
        }
        if(!$lower || !$higher){
            throw new InsufficientVarianceException($this, "There is not enough variance in ".
                "the $this->causeVariableName cause measurements to calculate values producing optimal outcomes. ");
        }
        $this->effectValuesExpectedToBeLowerThanAverage = $lower;
        $this->effectValuesExpectedToBeHigherThanAverage = $higher;
    }
    /**
     * @return float[]
     * @throws InsufficientVarianceException
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getEffectValuesExpectedToBeHigherThanAverage(): array {
        if($this->effectValuesExpectedToBeHigherThanAverage === null){
            $this->setEffectValuesExpectedToBeHigherLowerThanAverage();
        }
        return $this->effectValuesExpectedToBeHigherThanAverage;
    }
    /**
     * @return float[]
     * @throws InsufficientVarianceException
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getEffectValuesExpectedToBeLowerThanAverage(): array{
        if($this->effectValuesExpectedToBeLowerThanAverage === null){
            $this->setEffectValuesExpectedToBeHigherLowerThanAverage();
        }
        return $this->effectValuesExpectedToBeLowerThanAverage;
    }
    /**
     * @throws InsufficientVarianceException
     * @throws NotEnoughDataException
     * @throws NotEnoughMeasurementsForCorrelationException
     * @throws TooSlowToAnalyzeException
     */
    public function calculateChangeFromAvgEffectPredictorValues(): void {
        if(!count($this->getEffectValuesExpectedToBeLowerThanAverage())){
            $this->logAndSetCorrelationError("There are not enough data or variance to determine optimal values. ");
            return;
        }
        try {
            $this->calculatePValue();
            // Don't let 0 standard error in effect values stop us from saving correlation.
            // There are situations where other useful data such as: https://app.quantimo.do/datalab/correlations/118987419
        } catch (InsufficientVarianceException $e){
           $this->addException($e);
           $this->addWarning("Could not determine P value because:\n".$e->getMessage());
        }
        CorrelationPredictsLowEffectChangeProperty::calculate($this);
        CorrelationPredictsHighEffectChangeProperty::calculate($this);
    }
    /**
     * @return float
     * @throws InsufficientVarianceException
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function calculatePValue(): ?float {
        $effectValuesExpectedToBeHigherThanAverage = $this->getEffectValuesExpectedToBeHigherThanAverage();
        $effectValuesExpectedToBeLowerThanAverage = $this->getEffectValuesExpectedToBeLowerThanAverage();
        if(!$effectValuesExpectedToBeLowerThanAverage){
            throw new InsufficientVarianceException($this);
        }
        $avgEffectExpectedToBeHigh = Stats::average($effectValuesExpectedToBeHigherThanAverage);
        //function to compute standard deviation
        $stdDevOfEffectExpectedToBeHigh = Stats::stdDev($effectValuesExpectedToBeHigherThanAverage);
        //variance of data set 1
        $varianceOfEffectExpectedToBeHigh = $stdDevOfEffectExpectedToBeHigh * $stdDevOfEffectExpectedToBeHigh;
        //number of data for 1st data set
        $numberOfEffectExpectedToBeHigh = count($effectValuesExpectedToBeHigherThanAverage);
        //variance over number of data
        $stdErrOfEffectExpectedToBeHigh = $varianceOfEffectExpectedToBeHigh / $numberOfEffectExpectedToBeHigh;
        ////////////////////////////////
        //ANALYSIS FOR 2nd DATA SETS///
        ///////////////////////////////
        $avgOfEffectValuesExpectedToBeLow = Stats::average($effectValuesExpectedToBeLowerThanAverage);
        //function to compute standard deviation
        $stdDevOfEffectValuesExpectedToBeLow = Stats::stdDev($effectValuesExpectedToBeLowerThanAverage);
        //variance of data set 2
        $varianceOfEffectValuesExpectedToBeLow = $stdDevOfEffectValuesExpectedToBeLow * $stdDevOfEffectValuesExpectedToBeLow;
        //number of data for 2nd data set
        $numberOfEffectValuesExpectedToBeLow = count($effectValuesExpectedToBeLowerThanAverage);
        //variance over number of data
        $stdErrorOfEffectValuesExpectedToBeLow = $varianceOfEffectValuesExpectedToBeLow / $numberOfEffectValuesExpectedToBeLow;
        $sumOfErrorOfEffectValues = $stdErrOfEffectExpectedToBeHigh + $stdErrorOfEffectValuesExpectedToBeLow;
        $standardErrorOfEffectValues = sqrt($sumOfErrorOfEffectValues);
        $diffBetweenAvgOfEffectExpectedToBeHighOrLow = $avgEffectExpectedToBeHigh - $avgOfEffectValuesExpectedToBeLow;
        $absValOfDiffBetweenAverageOfEffectValuesExpectedToBeHighOrLow = abs($diffBetweenAvgOfEffectExpectedToBeHighOrLow);
        if($standardErrorOfEffectValues == 0){
            throw new InsufficientVarianceException($this,
                'Cannot calculate tValue or confidence interval because standard error is 0. ');
        }
		$l = $this->l();
        $l->t_value = $this->tValue = $absValOfDiffBetweenAverageOfEffectValuesExpectedToBeHighOrLow /
	                           $standardErrorOfEffectValues;
        $this->degreesOfFreedom = $numberOfEffectExpectedToBeHigh + $numberOfEffectValuesExpectedToBeLow - 1;
        if($this->degreesOfFreedom > 200){
            $this->degreesOfFreedom = 200;
        }
	    $this->minimumProbability = 0.05;
        $this->criticalTValue = Stats::getCriticalTValue($this->degreesOfFreedom, $this->minimumProbability);
		$l->critical_t_value = $this->criticalTValue;
        $this->confidenceInterval = $standardErrorOfEffectValues * $this->criticalTValue;
		$l->confidence_interval = $this->confidenceInterval;
        $this->pValue = (1 / sqrt(2 * M_PI)) * exp(-0.5 * ($this->tValue ** 2));
        if($this->pValue < 0.001){
            $this->pValue = 0.001; // DB can't hold super small numbers
        }
		$l->p_value = $this->pValue;
        //$correlationObject->pValue = round($correlationObject->pValue, 3);  // Why round this?  It always makes it 0!
        $this->significantDifference = false;
        return $this->pValue;
    }
    /**
     * @param Pair[] $pairs
     * @return float|bool
     * @internal param OptimalDailyValues $correlationObject
     */
    public static function calculateAverageCauseForPairSubset(array $pairs) {
        $causeMeasurementValues = array_map(static function($o){
            return $o->causeMeasurementValue;
        }, $pairs);
        $numberOfCauseMeasurements = count($causeMeasurementValues);
        if($numberOfCauseMeasurements < 1){
            QMLog::info('There are not enough causeMeasurements for to get average cause.', ['pairs' => $pairs]);
            return false;
        }
        return Stats::average($causeMeasurementValues);
    }
    /**
     * @return Pair[] array
     * @throws InsufficientVarianceException
     * @throws NotEnoughDataException
     * @throws NotEnoughOverlappingDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getPairsWithEffectBetween(): array{
        $excludedPairs = $this->getPairs();
	    $highEffectMinimum = $this->getHighEffectCutoffMinimumValue();
	    $excludedPairs =
            CorrelationValuePredictingHighOutcomeProperty::getPairsWithEffectValueAbove($highEffectMinimum,
                                                                                        $excludedPairs,
                                                                                        true);
	    $lowEffectMaximum = $this->getLowEffectCutoffMaximumValue();
	    $excludedPairs =
            CorrelationValuePredictingLowOutcomeProperty::getPairsWithEffectValueBelow($lowEffectMaximum,
                                                                                       $excludedPairs,
                                                                                       true);
        if(count($excludedPairs)){
			$effectValues = array_map(static function($o){return $o->effectMeasurementValue;}, $excludedPairs);
	        $message = count($excludedPairs)." pairs were excluded from optimal value calculations because the effect value was below 
                            $highEffectMinimum or above $lowEffectMaximum";
	        $this->logError($message."\nEffect Values:\n".QMLog::print_r($effectValues, true));
        }
        return $excludedPairs;
    }
    /**
     * @param Pair[] $pairs
     * @param string $type
     * @param QMUserVariable $userVariable
     * @return int
     * @throws NotEnoughOverlappingDataException
     */
    private function calculateNumberOfPairs(array $pairs, string $type, QMUserVariable $userVariable): int{
        $numberOfPairs = count($pairs);
        if($numberOfPairs < 1){
            $message = "There are not enough $type pairs for $userVariable->name to average. ";
            $this->logAndSetCorrelationError($message);
            throw new NotEnoughOverlappingDataException($this);
        }
        return $numberOfPairs;
    }
    /**
     * @return float
     */
    private function getHighEffectCutoffMinimumValueForBinaryDistributions(): float{
        $highEffectCutoff = $this->getOrCalculateAverageEffect();
        return $this->highEffectCutoff = $highEffectCutoff;
    }
    /**
     * @return float
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    private function getHighEffectCutoffMinimumValueForNonBinaryDistributions(): float{
        // TODO:  Why was this necessary?
        //$highEffectCutoff = (0.5 + $this->getPercentDistanceFromMedianToBeHighOrLowEffect() / 100) * $this->getEffectValueSpread() + $this->getMinimumEffectValue();
        $highEffectCutoff = $this->getOrCalculateAverageEffect();
        return $this->highEffectCutoff = $highEffectCutoff;
    }
    /**
     * @return float
     * @throws InsufficientVarianceException
     * @throws NotEnoughDataException
     * @throws NotEnoughOverlappingDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getHighEffectCutoffMinimumValue(): float{
        if($this->getOrCalculatedNumberOfUniqueEffectValues() > 2){
            return $this->getHighEffectCutoffMinimumValueForNonBinaryDistributions();
        }
        return $this->getHighEffectCutoffMinimumValueForBinaryDistributions();
    }
    /**
     * @return float
     */
    private function getLowEffectCutoffMaximumValueForBinaryDistributions(): float{
        $lowEffectCutoff = $this->getOrCalculateAverageEffect();
        return $this->lowEffectCutoff = $lowEffectCutoff;
    }
    /**
     * @return float
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    private function getLowEffectCutoffMaximumValueForNonBinaryDistributions(): float{
        // TODO:  Why was this necessary?
        //$lowEffectCutoff = (0.5 - $this->getPercentDistanceFromMedianToBeHighOrLowEffect() / 100) * $this->getEffectValueSpread() + $this->getMinimumEffectValue();
        $lowEffectCutoff = $this->getOrCalculateAverageEffect();
        return $this->lowEffectCutoff = $lowEffectCutoff;
    }
    /**
     * @return float
     * @throws InsufficientVarianceException
     * @throws NotEnoughDataException
     * @throws NotEnoughOverlappingDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getLowEffectCutoffMaximumValue(): float{
        if($this->getOrCalculatedNumberOfUniqueEffectValues() > 2){
            return $this->getLowEffectCutoffMaximumValueForNonBinaryDistributions();
        }
        return $this->getLowEffectCutoffMaximumValueForBinaryDistributions();
    }
    /**
     * @return Pair[]
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function setHighEffectPairs(): array{
        $HighEffectCutoffMinimumValue = $this->getHighEffectCutoffMinimumValue();
        $pairs = CorrelationValuePredictingHighOutcomeProperty::getPairsWithEffectValueAbove(
            $HighEffectCutoffMinimumValue, $this->getPairs());
        $this->numberOfHighEffectPairs = $this->calculateNumberOfPairs($pairs, 'highEffectPairs',
            $this->getOrSetEffectQMVariable());
        return $pairs;
    }
    /**
     * @return Pair[]
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function setLowEffectPairs(): array {
        $lowEffectPairs = CorrelationValuePredictingLowOutcomeProperty::getPairsWithEffectValueBelow(
            $this->getLowEffectCutoffMaximumValue(), $this->getPairs());
        $this->numberOfLowEffectPairs = $this->calculateNumberOfPairs($lowEffectPairs, 'lowEffectPairs', $this->getOrSetEffectQMVariable());
        return $lowEffectPairs;
    }
    /**
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function setEffectValueStatistics(){
        $values = $this->getEffectValues();
        $this->setNumberOfUniqueEffectValuesForOptimalValues();
        $this->numberOfEffectChangesForOptimalValues = $this->calculateNumberOfChanges($values, $this->getOrSetEffectQMVariable());
        $this->setEffectValueSpread();
        CorrelationValuePredictingHighOutcomeProperty::calculate($this);
        CorrelationValuePredictingLowOutcomeProperty::calculate($this);
        CorrelationGroupedCauseValueClosestToValuePredictingLowOutcomeProperty::calculate($this);
        $this->getPairsWithEffectBetween();
    }
    /**
     * @throws InsufficientVarianceException
     * @throws NotEnoughDataException
     * @throws NotEnoughOverlappingDataException
     * @throws TooSlowToAnalyzeException
     */
    private function setNumberOfUniqueEffectValuesForOptimalValues(){
        $values = $this->getEffectValues();
        $this->numberOfUniqueEffectValuesForOptimalValues = $this->calculateNumberOfUniqueValues($values, $this->getOrSetEffectQMVariable());
    }
    /**
     * @return mixed
     * @throws InsufficientVarianceException
     * @throws NotEnoughDataException
     * @throws NotEnoughOverlappingDataException
     * @throws TooSlowToAnalyzeException
     */
    private function getOrCalculatedNumberOfUniqueEffectValues(){
        if(!isset($this->numberOfUniqueEffectValuesForOptimalValues)){
            $this->setNumberOfUniqueEffectValuesForOptimalValues();
        }
        return $this->numberOfUniqueEffectValuesForOptimalValues;
    }
    /**
     * @return float
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    private function setEffectValueSpread(): float{
        return $this->effectValueSpread = $this->getMaximumEffectValueFromPairs() - $this->getOrCalculateMinimumEffectValueFromPairs();
    }
    /**
     * @return float
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getMaximumEffectValueFromPairs(): float{
        return $this->maximumEffectValue ?: $this->maximumEffectValue = max($this->getEffectValues());
    }
    /**
     * @return float
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getOrCalculateMinimumEffectValueFromPairs(): float{
        if($this->minimumEffectValue === null){
            $values = $this->getEffectValues();
            $this->minimumEffectValue = min($values);
        }
        return $this->minimumEffectValue;
    }
    /**
     * @return bool
     * @throws ModelValidationException
     */
    public function save(): bool{
        if($this->numberOfDays){$this->numberOfDays = (int)$this->numberOfDays;}
        $res = parent::save();
        if($this->id === 0){le("ID is 0!");}
        if(!$this->id){le("No id!");}
        return $res;
    }
	/**
     * @param array $valuesFromPairs
     * @param QMUserVariable $v
     * @return int
     * @throws InsufficientVarianceException
     * @throws NotEnoughOverlappingDataException
     */
    private function calculateNumberOfUniqueValues(array $valuesFromPairs, QMUserVariable $v): int{
        $count = count($valuesFromPairs);
        if($count < 2){
            $this->setInternalErrorMessage("There are not enough $v->name for to get average. ");
            throw new NotEnoughOverlappingDataException($this);
        }
        $numberOfUniqueValues = count(array_unique($valuesFromPairs));
        if($numberOfUniqueValues < 2){
            $this->setInternalErrorMessage("We cannot calculate optimal daily values because the overlapping ".
                $v->name." measurements do not vary and are all ".$valuesFromPairs[0].". ");
            throw new InsufficientVarianceException($this, $this->internalErrorMessage,
                "$v->name Admin: ".$v->getUrl());
        }
        return $numberOfUniqueValues;
    }
    /**
     * @param array $allValues
     * @param QMUserVariable $v
     * @return int
     */
    private function calculateNumberOfChanges(array $allValues, QMUserVariable $v): int{
        $numberOfChanges = Stats::countChanges($allValues);
        if($numberOfChanges < CorrelationCauseChangesProperty::MINIMUM_CHANGES){
            $this->setInternalErrorMessage('Cannot calculate optimal daily values due to insufficient changes in '
                .$v->name.' measurements');
            throw new LogicException($this->internalErrorMessage);
        }
        return $numberOfChanges;
    }
    /**
     * @throws InsufficientVarianceException
     * @throws InvalidVariableValueException
     * @throws NotEnoughDataException
     * @throws NotEnoughOverlappingDataException
     * @throws TooSlowToAnalyzeException
     */
    public function setCauseValueStatistics(){
        $causeValues = $this->getCauseValues();
        $cause = $this->getOrSetCauseQMVariable();
        $this->numberOfUniqueCauseValuesForOptimalValues = $this->calculateNumberOfUniqueValues(
            $causeValues, $cause);
        $this->numberOfCauseChangesForOptimalValues = $this->calculateNumberOfChanges($causeValues, $cause);
        CorrelationAverageEffectFollowingLowCauseProperty::calculate($this);
        CorrelationAverageEffectFollowingHighCauseProperty::calculate($this);
        CorrelationAverageDailyLowCauseProperty::calculate($this);
        CorrelationAverageDailyHighCauseProperty::calculate($this);
    }
    /**
     * @throws NotEnoughMeasurementsForCorrelationException
     * @throws NotEnoughOverlappingDataException
     */
    private function makeSureProcessedDailyMeasurementsAreChronological(){
        MeasurementStartTimeProperty::verifyChronologicalOrder($this->getCauseMeasurements());
        MeasurementStartTimeProperty::verifyChronologicalOrder($this->getEffectMeasurements());
    }
	/**
	 * @param array $processedDailyMeasurements
	 * @param $userVariable
	 * @throws \App\Exceptions\NotEnoughMeasurementsForCorrelationException
	 */
    private function checkMinimumProcessedDailyMeasurements(array $processedDailyMeasurements, $userVariable){
        if(count($processedDailyMeasurements) < 2){
            throw new NotEnoughMeasurementsForCorrelationException('There are not enough '.
                $userVariable->name.' cause measurements for optimal daily value calculation.',$this,
                $this->getOrSetCauseQMVariable(), $this->getOrSetEffectQMVariable());
        }
    }
    /**
     * @throws NotEnoughDataException
     */
    private function validateMeasurements(){
        $this->makeSureProcessedDailyMeasurementsAreChronological();
        $this->checkMinimumProcessedDailyMeasurements($this->getCauseMeasurements(),
            $this->getOrSetCauseQMVariable());
        $this->checkMinimumProcessedDailyMeasurements($this->getEffectMeasurements(),
            $this->getOrSetEffectQMVariable());
    }
    /**
     * @return string
     */
    public function getPHPUnitTestUrl(): string {
        return self::generatePHPUnitTestUrlForAnalyze($this->getCauseUserVariable(), $this->getEffectUserVariable());
    }
    /**
     * @param UserVariable|QMVariable $cause
     * @param UserVariable|QMVariable $effect
     * @return string
     */
    public static function generatePHPUnitTestUrlForAnalyze($cause, $effect): string {
        return StagingJobTestFile::getUrl('UserVariableRelationshipAnalysis' .
                                          QMStr::toClassName($cause->getNameAttribute()." ".$effect->getNameAttribute()),
            '$c = QMUserVariableRelationship::getOrCreateUserVariableRelationship('.$cause->getUserId().', '.
            $cause->getVariableIdAttribute().', '.$effect->getVariableIdAttribute().");
        \$c->analyzeFully('we are testing');",
            \App\VariableRelationships\QMUserVariableRelationship::class);
    }
    /**
     * @return bool
     */
    public function getIsPublic(): ?bool {
        if(isset($this->causeVariable)){
            $this->causeVariableIsPublic = $this->getCauseQMVariable()->getIsPublic();
        }
	    if(isset($this->effectVariable)){
            $this->effectVariableIsPublic = $this->getEffectQMVariable()->getIsPublic();
        }
        if($this->causeVariableIsPublic && $this->effectVariableIsPublic){
            return true;
        }
        if($this->getUser()->getShareAllData()){
            return true;
        }
        return false;
    }
    private function validateCalculation(): void{
        $this->getCorrelationIsContradictoryToOptimalValues();
        $coefficient = $this->correlationCoefficient;
        $this->logInfo("UserVariableRelationship: $coefficient \n ".
            "Onset Delay: ".$this->getOnsetDelayHumanString()." \n".
            "Duration of Action: ".$this->getDurationOfActionHumanString()." \n".
            "Last analyzed: ".$this->getTimeSinceLastAnalyzedHumanString()."\n"
        );
        if($coefficient === null){
            $this->logError("Correlation Coefficient should not be null!");
            le("User Variable Relationship coefficient should not be null!");
        }
//		$required = static::getRequiredAnalysisFields();
//		foreach($required as $field){
//			$field = QMStr::camelize($field);
//			if($this->$field === null){
//				$this->logError("UserVariableRelationship $field should not be null!");
//				le("UserVariableRelationship $field should not be null!");
//			}
//		}
    }
    /**
     * @return string
     */
    private function getThumbTooltip(): string{
        $tooltip = "Needs Review";
        if($this->userUpVoted()){
            $tooltip = "Up-Voted";
        }
        if($this->userDownVoted()){
            $tooltip = "Down-Voted";
        }
        return $tooltip;
    }
    /**
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function calculateOutcomeBaselineStatistics(){
        $this->validateVariance();
        CorrelationZScoreProperty::calculate($this);
        CorrelationCauseTreatmentAveragePerDayProperty::calculate($this);
        CorrelationCauseTreatmentAveragePerDurationOfActionProperty::calculate($this);
        CorrelationCauseBaselineAveragePerDayProperty::calculate($this);
        CorrelationCauseBaselineAveragePerDurationOfActionProperty::calculate($this);
    }
    /**
     * @return float
     * @throws AlreadyAnalyzedException
     * @throws InsufficientVarianceException
     * @throws InvalidVariableValueException
     * @throws NotEnoughDataException
     * @throws NotEnoughMeasurementsForCorrelationException
     * @throws NotEnoughOverlappingDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getOrCalculateFollowUpPercentChangeFromBaseline(): float {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $lastAnalyzed = $this->getUpdatedAt();
        /** @noinspection PhpUnusedLocalVariableInspection */
        $algorithmChangedAt = self::ALGORITHM_MODIFIED_AT;
        if($this->getChangeFromBaseline() === null || $this->getZScore() === null){
            $this->analyzeFully(__FUNCTION__);  // Make sure it gets saved to DB
            // Make sure we throw not enough baseline pairs exception if necessary because it's caught in analyze function
            $this->calculateOutcomeBaselineStatistics();
        } else if($this->pairs){
            $this->calculateOutcomeBaselineStatistics();
        }
        return $this->effectFollowUpPercentChangeFromBaseline;
    }
    /**
     * @return Pair[]
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getBaselinePairs():array{
        if($pairs = $this->baselinePairs){return $pairs;}
        $this->generateBaselineAndFollowupPairs();
        $min = CorrelationCauseChangesProperty::MINIMUM_CHANGES;
        $pairs = $this->baselinePairs;
        if(count($pairs) < $min){
            $this->generateBaselineAndFollowupPairs();
            throw new InsufficientVarianceException($this,
                "Not Enough Baseline Treatment Outcome Data Pairs",
                "There are only ".count($pairs).
                " outcome measurement following the treatment BASELINE and we need at least $min to be able to compare with the outcome values seen AFTER treatment. ");
        }
        return $pairs;
    }
    /**
     * @return Pair[]
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getFollowupPairs():array{
        if($pairs = $this->followupPairs){return $pairs;}
        $this->generateBaselineAndFollowupPairs();
        $min = CorrelationCauseChangesProperty::MINIMUM_CHANGES;
        $pairs = $this->followupPairs;
        if(count($pairs) < $min){
            $this->generateBaselineAndFollowupPairs();
            throw new InsufficientVarianceException($this, "Not Enough Follow-Up Treatment Outcome Data Pairs",
                "There are only ".count($pairs).
                " outcome measurement following treatment and we need at least $min to be able to compare with the typical outcome values BEFORE treatment. ");
        }
        return $pairs;
    }
    /**
     * @return void
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function generateBaselineAndFollowupPairs(): void {
        $pairs = $this->getPairs();
        /** @noinspection PhpUnusedLocalVariableInspection */
        $causeMean = $this->getAverageCause();
        $avgCauseFromPairs = $this->getAverageCause();
        foreach($pairs as $pair){
            if($pair->causeMeasurementValue < $avgCauseFromPairs){
                $this->baselinePairs[] = $pair;
            } else {
                $this->followupPairs[] = $pair;
            }
        }
    }
    /**
     * @return mixed
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getInterventionMeanOfCumulativeValueOverDurationOfAction(): float {
        if($this->causeTreatmentAveragePerDurationOfAction === null){
            $this->calculateOutcomeBaselineStatistics();
        }
        return $this->causeTreatmentAveragePerDurationOfAction;
    }
    /**
     * @return bool
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    protected function shouldUseChangeFromBaseline(): bool {
        if($this->effectBaselineRelativeStandardDeviation){
            return true;
        }
        if(!$this->pairs && AppMode::isApiRequest()){return false;}
        $cause = $this->getOrSetCauseQMVariable();
        $this->logInfo("Have to get $cause to decide shouldUseChangeFromBaseline");
        $fillingValue = $cause->getFillingValueAttribute();
        if($fillingValue === (float)0){return true;}
        if($cause->minimumAllowedValue === 0){return true;}
        try {
            $this->logInfo("Have to get getBaselinePairs to decide shouldUseChangeFromBaseline");
            $this->generateBaselineAndFollowupPairs();
        } catch (NotEnoughMeasurementsForCorrelationException $e) { // I think this just happens during testCorrelationPushNotification
            $this->logError(__METHOD__.": ".$e->getMessage());
            return false;
        }
        $baselinePairs = $this->getBaselinePairs();
        $fractionOfBaseline = count($baselinePairs)/count($this->pairs);
        return $fractionOfBaseline > 0.1;
    }
	public function getTagLine(): string{
		return $this->l()->getTagLine();
	}
    /**
     * @param array $data
     * @param string|null $reason
     * @return int
     */
    public function softDelete(array $data = [], string $reason = null): int {
        if($reason){
            $this->logError("Deleting correlation...");
            $data[self::FIELD_INTERNAL_ERROR_MESSAGE] = $data[self::FIELD_USER_ERROR_MESSAGE] = QMStr::truncate($reason, 254);
        }
        return parent::softDelete($data, $reason);
    }
    /**
     * @throws InsufficientVarianceException
     */
    private function validateVariance(): void{
        $causeChanges = $this->getCauseChanges();
        if($causeChanges < CorrelationCauseChangesProperty::MINIMUM_CHANGES){
            throw new InsufficientVarianceException($this);
        }
        $effectChanges = $this->getEffectChanges();
        if($effectChanges < CorrelationCauseChangesProperty::MINIMUM_CHANGES){
            throw new InsufficientVarianceException($this);
        }
    }
    /**
     * @param array $arr
     * @param string|null $reason
     * @return int
     * @deprecated Use Eloquent model save directly
     */
    public function updateDbRow(array $arr, string $reason = null): int{
        if(isset($arr[self::FIELD_EXPERIMENT_END_AT])){
            $arr[self::FIELD_EXPERIMENT_END_AT] = db_date($arr[self::FIELD_EXPERIMENT_END_AT]);
        }
        if(isset($arr[self::FIELD_EXPERIMENT_START_AT])){
            $arr[self::FIELD_EXPERIMENT_START_AT] = db_date($arr[self::FIELD_EXPERIMENT_START_AT]);
        }
        return parent::updateDbRow($arr, $reason);
    }
    /**
     * @return QMUserVariable[]
     */
    public function getSourceObjects(): array{
        return [
            $this->getOrSetCauseQMVariable(),
            $this->getOrSetEffectQMVariable(),
        ];
    }
    /**
     * @return array
     */
    public static function getRequiredAnalysisFields(): array{
        $fields[] = static::FIELD_EFFECT_FOLLOW_UP_PERCENT_CHANGE_FROM_BASELINE;
		$fields[] = static::FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT;
        // If we don't have variance in effectValuesExpectedToBeHigherThanAverage we can't calculate FIELD_CONFIDENCE_INTERVAL
        // $fields[] = static::FIELD_CONFIDENCE_INTERVAL;
        return $fields;
    }
    public function shrink(){
        parent::shrink();
        if(isset($this->causeVariable)){$this->getOrSetCauseQMVariable()->shrink();}
        if(isset($this->effectVariable)){$this->getOrSetEffectQMVariable()->shrink();}
    }
    /**
     * @return CorrelationChartGroup
     */
    public function getOrSetCharts(): ChartGroup {
        if($charts = $this->charts){
            $c = CorrelationChartGroup::instantiateIfNecessary($charts);
            $c->setSourceObject($this);
            return $c;
        }
        return $this->setCharts();
    }
    /**
     * @return CorrelationChartGroup
     */
    public function setCharts(): ChartGroup {
        $charts = new CorrelationChartGroup($this);
        $charts->getOrSetHighchartConfigs();
        return $this->charts = $charts;
    }
    /**
     * @return UserVariableRelationship
     */
    public function l(): UserVariableRelationship{
        return parent::l();
    }
    /**
     * @return Pair[]
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getLowCausePairs(): array{
        $min = $this->getMinimumCauseValue();
        $spread = $this->getCauseValueSpread();
        $lowCauseMaximum = 0.49 * $spread + $min;
        return CorrelationAverageDailyLowCauseProperty::getLowCausePairs($lowCauseMaximum, $this->getPairs());
    }
    /**
     * @return Pair[]
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getHighCausePairs(): array{
        $min = $this->getMinimumCauseValue();
        $spread = $this->getCauseValueSpread();
        $highCauseMinimum = 0.51 * $spread + $min;
        return CorrelationAverageDailyHighCauseProperty::getHighCausePairs(
            $highCauseMinimum, $this->getPairs());
    }
    /**
     * @return float
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getMinimumCauseValue(): float {
        return $this->minimumCauseValue = min($this->getCauseValues());
    }
    /**
     * @return float
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getMaximumCauseValue(): float {
        return $this->maximumCauseValue = max($this->getCauseValues());
    }
    /**
     * @return float
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function getCauseValueSpread(): float {
        return $this->causeValueSpread = $this->getMaximumCauseValue() - $this->getMinimumCauseValue();
    }
    private function logAnalysisParameters(): void{
        $m = //"Browser Analysis: {$this->getDebugUrl()}\n".
    //PHPUnit: {$this->getPHPUnitTestUrl()}
    "Cause: {$this->getCauseVariableName()} {$this->getCauseUrl()}
    Effect: {$this->getEffectVariableName()} {$this->getEffectUrl()}
    Onset Delay: ".$this->getOnsetDelayHumanString()." & Duration Of Action: ".$this->getDurationOfActionHumanString();
        ThisComputer::outputMemoryUsageIfEnabledOrDebug();
        if(AppMode::isApiRequest()){$m .= "\nRequest Duration: ".QMRequest::getDuration()." seconds";}
        QMLog::info($m);
    }
    public function getCauseUserVariableId():int{
        return $this->causeUserVariableId;
    }
    public function getEffectUserVariable(): UserVariable {
        return UserVariable::findInMemoryOrDB($this->getEffectUserVariableId());
    }
    /**
     * @return mixed
     */
    public function getDurationOfAction(): int {
        $duration = $this->durationOfAction;
        if(!$duration){
            $v = $this->getCauseUserVariable();
            $duration = $v->getDurationOfActionAttribute();
		if(!$duration){
		    le( "No duration of action from $v", ['duration' => $v->attributesToArray()]);}
        }
        return $this->durationOfAction = $duration;
    }
    /**
     * @return AnonymousMeasurement[]
     */
    public function getInvalidMeasurements(): array{
        $cause = $this->getCauseQMVariable();
        $measurements = $cause->getInvalidMeasurements();
        $effect = $this->getEffectQMVariable();
        $measurements = array_merge($measurements, $effect->getInvalidMeasurements());
        return $measurements;
    }
    /**
     * @return AnonymousMeasurement[]
     */
    public function getInvalidSourceData(): array{
        return $this->invalidSourceData = $this->getInvalidMeasurements();
    }
    public function getStudyType(): string{
        return $this->type = StudyTypeProperty::TYPE_INDIVIDUAL;
    }
    public function getEffectUserVariableId(): int{
        return $this->effectUserVariableId;
    }
    /**
     * @return GlobalVariableRelationship
     */
    public function getGlobalVariableRelationship(): GlobalVariableRelationship {
        /** @var QMGlobalVariableRelationship $ac */
        if($ac = $this->aggregatedCorrelation){
            return $ac->l();
        }
        $l = $this->l();
        return $l->getGlobalVariableRelationship();
    }
	/**
	 * @param float $delay
	 * @param int $duration
	 * @return QMUserVariableRelationship|null
	 * @throws \App\Exceptions\InsufficientVarianceException
	 * @throws \App\Exceptions\NotEnoughDataException
	 * @throws \App\Exceptions\NotEnoughMeasurementsForCorrelationException
	 * @throws \App\Exceptions\NotEnoughOverlappingDataException
	 * @throws \App\Exceptions\TooSlowToAnalyzeException
	 */
    public function correlateByHyperParams(float $delay, int $duration): QMUserVariableRelationship {
        $cause = $this->getOrSetCauseQMVariable();
        $effect = $this->getOrSetEffectQMVariable();
        $this->checkMemoryAndTimeLimit($delay, "delays");
        $c = new QMUserVariableRelationship(null, $cause, $effect,
            $delay, $duration, $this->voteStatisticalSignificance);
        try {
            $c->analyzePartially($this->reasonForAnalysis ?? __FUNCTION__);
        } catch (InvalidVariableValueException | StupidVariableNameException | TooSlowToAnalyzeException | StupidVariableException $e) {
            le($e);
        }
	    return $this->correlationsByHyperParameters[$c->hyperParamSlug()] = $c;
    }
    /**
     * @return array
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    private function getCoefficientsByDuration(): array{
        $byDuration = [];
        $correlations = $this->getOverDurations();
        foreach($correlations as $c){
            $byDuration[$c->durationOfAction] = $c->correlationCoefficient;
        }
        if($byDuration){
            $this->saveOverDurations($byDuration);
        }
        return $byDuration;
    }
    /**
     * @return array
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    private function getCoefficientsByOnsetDelay(): array{
        $coefficients = [];
        $correlations = $this->getOverDelays();
        if(count($correlations) < 2){
            throw new NotEnoughDataException($this,
                'Could not calculate correlation over various onset delays. ',
                'This is probably due to insufficient measurements');
        }
        foreach($correlations as $c){
            $delay = $c->onsetDelay;
            $coefficient = $c->correlationCoefficient;
            $coefficients[$delay] = $coefficient;
        }
        return $coefficients;
    }
    /**
     * @param array $coefficients
     * @return void
     */
    private function saveOverDelays(array $coefficients): void {
        $l = $this->l();
        if(count($coefficients) < 2){
            le("Not going to ".__FUNCTION__." because there aren't enough user_variable_relationships.  We should have thrown "
                ."NotEnoughDataException");
        }
        $l->setCorrelationsOverDelays($coefficients);
        try {
            $l->save();
        } catch (ModelValidationException $e) {
            $this->logError("Could not save correlations_over_delays because ".$e->getMessage());
        }
    }
    /**
     * @param array $byDuration
     */
    private function saveOverDurations(array $byDuration): void{
        $l = $this->l();
        $l->setCorrelationsOverDurations($byDuration);
        try {
            $l->save();
        } catch (ModelValidationException $e) {
            $this->logError("Could not save correlations_over_delays because ".$e->getMessage());
        }
    }
    /**
     * @return QMUserVariable
     */
    public function getOrSetCauseQMVariable(): QMVariable{
        if(isset($this->causeVariable)){return $this->causeVariable;}
        return $this->causeVariable = QMUserVariable::findOrCreateByNameOrId(
            $this->getUserId(), $this->getCauseVariableId());
    }
    /**
     * @return QMUserVariable
     */
    public function getOrSetEffectQMVariable(): QMVariable{
	    if(isset($this->effectVariable)){return $this->effectVariable;}
        return $this->effectVariable = QMUserVariable::findOrCreateByNameOrId(
            $this->getUserId(), $this->getEffectVariableId());
    }
    public function findGlobalVariableRelationship(): ?GlobalVariableRelationship{
        if($this->aggregateCorrelationId || $this->aggregatedCorrelation){
            return $this->getGlobalVariableRelationship();
        }
        return null;
    }
	/**
	 * @return \App\Models\UserVariableRelationship
	 */
	public function getCorrelation(): UserVariableRelationship{
		return $this->l();
	}
	/**
	 * @return QMVariable|null
	 */
	public function getEffectQMVariable(): ?QMVariable{
		return $this->effectVariable = $this->getEffectUserVariable()->getQMUserVariable();
	}
	/**
	 * @return QMVariable|null
	 */
	public function getCauseQMVariable(): ?QMVariable{
		return $this->causeVariable = $this->getCauseUserVariable()->getQMUserVariable();
	}
	public function getShowContentView(array $params = []): View{
		return $this->getCorrelation()->getShowContentView($params);
	}
	/**
	 * @return \App\Models\UserVariableRelationship|Builder
	 */
	public static function wherePostable(){
		return UserVariableRelationship::wherePostable();
	}
	public function getDataQuantitySentence():string{
		return $this->getCauseUserVariable()->getMeasurementQuantitySentence()."\n\n".
			$this->getEffectUserVariable()->getMeasurementQuantitySentence();
	}
	/**
	 * @return string|null
	 */
	public function getParentCategoryName(): ?string{
		return $this->getCorrelation()->getParentCategoryName();
	}
}
