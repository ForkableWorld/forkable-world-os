<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\VariableRelationships;
use App\Cards\QMCard;
use App\Cards\StudyCard;
use App\Charts\GlobalVariableRelationshipCharts\GlobalVariableRelationshipChartGroup;
use App\Charts\ChartGroup;
use App\Charts\CorrelationCharts\CorrelationChartGroup;
use App\Exceptions\InsufficientVarianceException;
use App\Exceptions\InvalidVariableValueException;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\GlobalVariableRelationship;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Models\Study;
use App\Models\Vote;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipDataSourceNameProperty;
use App\Properties\Base\BaseEffectFollowUpPercentChangeFromBaselineProperty;
use App\Properties\Base\BaseFillingValueProperty;
use App\Properties\UserVariableRelationship\CorrelationAverageEffectProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Reports\StudyReport;
use App\Slim\Model\DBModel;
use App\Slim\Model\QMUnit;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\QMQB;
use App\Storage\QueryBuilderHelper;
use App\Studies\QMPopulationStudy;
use App\Studies\QMStudy;
use App\Studies\QMUserStudy;
use App\Studies\StudyHtml;
use App\Studies\StudyImages;
use App\Studies\StudyLinks;
use App\Studies\StudyText;
use App\Traits\HasCauseAndEffect;
use App\Traits\HasCorrelationCoefficient;
use App\Traits\QMAnalyzableTrait;
use App\Types\BoolHelper;
use App\Types\ObjectHelper;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\ImageHelper;
use App\Utils\APIHelper;
use App\Utils\AppMode;
use App\Utils\Stats;
use App\VariableCategories\ActivitiesVariableCategory;
use App\VariableCategories\GoalsVariableCategory;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use App\Variables\QMVariableCategory;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use LogicException;
/** Class UserVariableRelationship
 * @package App\Slim\Model
 */
abstract class QMVariableRelationship extends DBModel {
	use HasCorrelationCoefficient, QMAnalyzableTrait;
    const SIG_FIGS = 3;
    protected $calculationStartTime;
    protected $correlations;
    protected $dataSourceName;
    protected $deletionReason;
    protected $recordSizeInKb;
    protected $slug;
    protected $votes;
    protected $weightedAverageVote;
    protected QMVariable  $effectVariable;
    protected QMVariable $causeVariable;
    public $allPairsSignificance;
    public $analysisEndedAt;
    public $analysisRequestedAt;
    public $analysisStartedAt;
    public $avgDailyValuePredictingHighOutcome; // Average cause value when the effect is above average
    public $avgDailyValuePredictingLowOutcome; // Average cause value when the effect is below average
    public $causalityFactor;
    public $causeBaselineAveragePerDay;
    public $causeBaselineAveragePerDurationOfAction;
    public $causeChanges;
    public $causeChangesStatisticalSignificance;
    public $causeDataSource;
    public $causeNumberOfProcessedDailyMeasurements;
    public $causeNumberOfRawMeasurements;
    public $causeTreatmentAveragePerDay;
    public $causeTreatmentAveragePerDurationOfAction;
    public $causeValueSpread;
    public $causeVariableCategory;
    public $causeVariableCategoryId;
    public $causeVariableCategoryName;
    public $causeVariableCombinationOperation;
    public $causeVariableCommonUnitAbbreviatedName;
    public $causeVariableCommonUnitId; // Unit Id of measurement for valuePredictingHighOutcome valuePredictingLowOutcome
    public $causeVariableCommonUnitName;
    public $causeVariableDisplayName;
    public $causeVariableDisplayNameWithCategoryOrUnitSuffix;
    public $causeVariableDisplayNameWithoutCategoryOrUnitSuffix;
    public $causeVariableId;
    public $causeVariableIsOutcome;
    public $causeVariableIsPredictor;
    public $causeVariableIsPublic;
    public $causeVariableMostCommonConnectorId;
    public $causeVariableName;
    public $charts;
    public $correlationIsContradictoryToOptimalValues;
    public $correlationsOverDurationsOfActionChartConfig;
    public $correlationsOverOnsetDelaysChartConfig;
    public $createdAt; // created time
    public $criticalTValue;
    public $dataAnalysis;
    public $dataPoints;
    public $dataSourcesParagraphForCause;
    public $dataSourcesParagraphForEffect;
    public $degreesOfFreedom;
    public $direction;
    public $durationOfAction; // duration of effect
    public $durationOfActionInHours;
    public $effectBaselineAverage;
    public $effectBaselineRelativeStandardDeviation;
    public $effectBaselineStandardDeviation;
    public $effectChanges;
    public $effectDataSource;
    public $effectFollowUpAverage;
    public $effectFollowUpPercentChangeFromBaseline;
    public $effectNumberOfProcessedDailyMeasurements;
    public $effectNumberOfRawMeasurements;
    public $effectSize;
    public $effectVariableCategory; // Avoid recursion json encoding error
    public $effectVariableCategoryId;
    public $effectVariableCategoryName; // The variable category of the effect
    public $effectVariableCommonUnitAbbreviatedName;
    public $effectVariableCommonUnitId;
    public $effectVariableCommonUnitName;
    public $effectVariableDisplayName;
    public $effectVariableDisplayNameWithCategoryOrUnitSuffix;
    public $effectVariableDisplayNameWithoutCategoryOrUnitSuffix;
    public $effectVariableId;
    public $effectVariableIsOutcome;
    public $effectVariableIsPredictor;
    public $effectVariableIsPublic;
    public $effectVariableMostCommonConnectorId;
    public $effectVariableName;
    public $effectVariableValence;
    public $experimentEndAt;
    public $experimentStartAt;
    public $gaugeImage;
    public $groupedCauseValueClosestToValuePredictingHighOutcome;
    public $groupedCauseValueClosestToValuePredictingLowOutcome;
    public $instructionsForCause;
    public $instructionsForEffect;
    public $interestingVariableCategoryPair;
    public $isPublic;
    public $maximumCauseValue;
    public $medianOfLowerHalfOfEffectMeasurements;
    public $medianOfUpperHalfOfEffectMeasurements;
    public $minimumCauseValue;
    public $minimumProbability;
    public $newestDataAt;
    public $numberOfCauseChangesForOptimalValues;
    public $numberOfDataPoints;
    public $numberOfDays;
    public $numberOfDaysSignificance;
    public $numberOfEffectChangesForOptimalValues;
    public $numberOfHighEffectPairs;
    public $numberOfLowEffectPairs;
	public $numberOfPairs; // Number of Pairs used to produce this correlation
    public $numberOfUniqueCauseValuesForOptimalValues;
    public $numberOfUniqueEffectValuesForOptimalValues;
    public $numberOfUsers;
    public $onsetDelay; // time delay between cause and effect
    public $onsetDelayInHours;
    public $onsetDelayWithStrongestPearsonCorrelation;
    public $onsetDelayWithStrongestPearsonCorrelationInHours;
    public $optimalChangeSpread;
    public $optimalChangeSpreadSignificance;
    public $outcomeDataSources;
    public $outcomeFillingValue;
    public $outcomeMaximumAllowedValue;
    public $outcomeMinimumAllowedValue;
    public $pairsOverTimeChartConfig;
    public $predictorExplanation;
    public $predictorExplanationSentence;
    public $predictorFillingValue;
    public $predictorMaximumAllowedValue;
    public $predictorMinimumAllowedValue;
    public $predictsHighEffectChange;
    public $predictsLowEffectChange;
    public $principalInvestigator;
    public $publishedAt;
    public $pValue;
    public $qmScore;
    public $rawCauseMeasurementSignificance;
    public $rawEffectMeasurementSignificance;
    public $reasonForAnalysis;
    public $reversePairsCount;
    public $reversePearsonCorrelationCoefficient;
    public $sharingDescription;
    public $sharingTitle;
    public $significantDifference;
    public $status;
    public $strengthLevel;
    public $studyAbstract;
    public $studyDesign;
    public $studyImages;
    public $studyLinks;
    public $studyObjective;
    public $studyText;
    public $studyTitle;
    public $timestamp;
    public $tValue;
    public $type;
    public $updatedAt; // updated time
    public $userErrorMessage;
    public $userVote; // 1 (thumbs up), 0 (thumbs down), or 0.5 (no thumbs - default)
    public $valuePredictingHighOutcome;
    public $valuePredictingHighOutcomeExplanation;
    public $valuePredictingLowOutcome;
    public $valuePredictingLowOutcomeExplanation;
    public $voteCount;
    public $voteStatisticalSignificance;
    public $voteSum;
    public $warnings;
    public $wpPostId;
    public $zScore;
    public ?float $averageDailyHighCause = null;
    public ?float $averageDailyLowCause = null;
    public ?float $averageEffect = null;
    public ?float $averageEffectFollowingHighCause = null;
    public ?float $averageEffectFollowingLowCause = null;
    public ?float $averageVote = null; // Average of all user votes
    public ?float $confidenceInterval = null;
    public ?float $optimalPearsonProduct = null; // Optimal Pearson Product
    public ?float $statisticalSignificance = null;
    public ?float $strongestPearsonCorrelationCoefficient = null;
    public ?string $analysisSettingsModifiedAt = null;
    public ?string $confidenceLevel = null;
    public const DEFAULT_CORRELATION_LIMIT = 10;
    public const DIRECTION_HIGHER = 'higher';
    public const DIRECTION_LOWER = 'lower';
    public const FIELD_CHARTS = 'charts';
    public const GAUGE_IMAGE_PATH = 'variable_categories_gauges_background/';
    public const S3_IMAGE_PATH = ImageHelper::STATIC_BASE_URL;
    public const VARIABLE_CATEGORIES_COMBINED_ROBOT = 'variable_categories_combined_robot/';
    public const VARIABLE_CATEGORIES_COMBINED_ROBOT_BACKGROUND = 'variable_categories_combined_robot_background/';
    public const VARIABLE_CATEGORIES_COMBINED_ROBOT_SMALL = 'variable_categories_combined_robot-600-315/';
    public float $averageForwardPearsonCorrelationOverOnsetDelays;
    public float $averagePearsonCorrelationCoefficientOverOnsetDelays;
    public float $averageReversePearsonCorrelationOverOnsetDelays;
    public float $correlationCoefficient; // Pearson correlation coefficient
    public float $forwardPearsonCorrelationCoefficient;
    public float $forwardSpearmanCorrelationCoefficient;
    public float $pearsonCorrelationWithNoOnsetDelay;
    public float $predictivePearsonCorrelationCoefficient;
    protected static $stupidCategoryPairs = [
        ['cause' => ActivitiesVariableCategory::NAME, 'effect' => GoalsVariableCategory::NAME],// Avoids Time Spent on Entertainment and Time Spent Unproductively
        ['cause' => GoalsVariableCategory::NAME, 'effect' => ActivitiesVariableCategory::NAME]// Avoids Time Spent on Entertainment and Time Spent Unproductively
    ];
	/**
	 * @var \App\Studies\StudyHtml|null
	 */
	protected ?StudyHtml $studyHtml = null;
	/**
     * UserVariableRelationship constructor.
     * @param UserVariableRelationship|GlobalVariableRelationship|null $l
     */
    public function __construct($l = null){
		if(!$l){return;}
        if($l instanceof BaseModel){
			$this->setLaravelModel($l);
			$this->effectVariableValence = $l->getEffectVariable()->getValence();
		} else {
			foreach($l as $key => $value){
				if($value !== null && property_exists($this, $key)){
					$this->$key = $value;
				}
			}
        }
		if(!$this->causeVariableId){le('!$this->causeVariableId');}
		if(!$this->effectVariableId){le('!$this->effectVariableId');}
        $this->setHoursProperties();
        if($this->getCorrelationCoefficient() !== null){
            $this->setStrengthLevel();
            $this->getCorrelationCoefficient();
            $this->setEffectSize();
            $this->setConfidenceLevel();
            $this->setPredictorExplanationSentence();
        }
        $this->causeVariableName = ucwords($this->causeVariableName);
        $this->effectVariableName = ucwords($this->effectVariableName);
        $this->addUnitNames();
        $this->addVariableCategoryInfo();
        TimeHelper::convertAllDateTimeValuesToRFC3339($this);
        $this->addToMemory();
    }
	/**
	 * @return array
	 */
	public function getWarnings(): array{
		return $this->warnings ?? [];
	}
	/**
	 * @param array|object $arrayOrObject
	 * @return void
	 */
	public function populateFieldsByArrayOrObject(array|object $arrayOrObject): void {
        parent::populateFieldsByArrayOrObject($arrayOrObject);
        if(isset($this->forwardPearsonCorrelationCoefficient) && !isset($this->correlationCoefficient)){
            $this->correlationCoefficient = $this->forwardPearsonCorrelationCoefficient;
        }
    }
    /**
     * @return string
     */
    public function getColor(): string{
        if($this->effectFollowUpPercentChangeFromBaseline === null){
            return static::COLOR;
        }
        $change = $this->getChangeFromBaseline();
        return BaseEffectFollowUpPercentChangeFromBaselineProperty::generateColor($change,
            $this->getEffectVariableValence());
    }
    /**
     * @return int
     */
    public function getCauseNumberOfRawMeasurementsWhenCorrelated(): int {
        return $this->causeNumberOfRawMeasurements;
    }
    /**
     * @return int
     */
    public function getEffectNumberOfRawMeasurementsWhenCorrelated(): int {
        return $this->effectNumberOfRawMeasurements;
    }
    /**
     * @return QMVariable|null
     */
    abstract public function getEffectQMVariable(): ?QMVariable;
    /**
     * @return QMVariable|null
     */
    abstract public function getCauseQMVariable(): ?QMVariable;
    /**
     * @param float $cc
     * @return float
     */
    public function setCorrelationCoefficient(float $cc): float {
        return $this->forwardPearsonCorrelationCoefficient = $this->correlationCoefficient = $cc;
    }
    /**
     * @param float $daily
     */
    public function setAvgDailyValuePredictingHighOutcome(float $daily): void{
        try {
			$v = $this->getCauseVariable();
	        $v->validateDailyValue($daily, __FUNCTION__);
        } catch (InvalidVariableValueException $e){
            $this->logError(__METHOD__.": ".$e->getMessage());
        }
        $this->avgDailyValuePredictingHighOutcome = $daily;
    }
    /**
     * @return mixed
     */
    public function getStudyDesign(): string {
        return $this->studyDesign = $this->getStudyText()->getStudyDesign();
    }
    /**
     * @return mixed
     */
    public function getStudyObjective(): string {
        return $this->studyObjective = $this->getStudyText()->getStudyObjective();
    }
    /**
     * @return string
     */
    public function getGaugeImage(): string {
        return $this->gaugeImage = $this->getStudyImages()->getGaugeImage();
    }
    public function setStudyProperties(): void{
		//$this->findInMemoryOrNewQMStudy();
        $this->getPredictorExplanationTitle();
        $this->setPredictorExplanationSentence();
        $this->getStudyAbstract();
        $this->getStudyDesign();
        $this->getStudyObjective();
        $this->getStudyTitle();
        $this->getGaugeImage();
    }
	abstract protected function calculateAverageEffect(): float;
	/**
	 * @return float
	 */
	public function getOrCalculateAverageEffect(): float {
		if(!isset($this->averageEffect)){
			$this->calculateAverageEffect();
		}
		return $this->averageEffect;
	}
    /**
     * @param int|null $precision
     * @return float
     */
    public function getEffectBaselineRelativeStandardDeviation(?int $precision = null): float{
        $val = $this->effectBaselineRelativeStandardDeviation;
        if($precision){
            return Stats::roundByNumberOfSignificantDigits($val, $precision);
        }
        return $val;
    }
    public function getChangeFromBaselineString():string{
        $change = $this->getChangeFromBaseline();
        return BaseEffectFollowUpPercentChangeFromBaselineProperty::generateString($change);
    }
    /**
     * @param int|null $precision
     * @return float
     */
    public function getChangeFromBaseline(?int $precision = null): ?float{
        $val = $this->effectFollowUpPercentChangeFromBaseline;
        if($precision){
            return Stats::roundByNumberOfSignificantDigits($val, $precision);
        }
        return $val;
    }
    /**
     * @return float
     */
    public function getOrCalculateZScore(): float {
        if($this->zScore === null){
            $this->calculateOutcomeBaselineStatistics();
            if ($this->zScore === null) {
                $this->calculateOutcomeBaselineStatistics();
            }
        }
        return $this->zScore;
    }
	/**
	 * @return mixed
	 */
	abstract public function calculateOutcomeBaselineStatistics();
    public function addStudyHtmlChartsImages(){
        $this->getStudyImages();
        $this->setStudyText();
        $this->setStudyLinks();
        $this->formatNumbers();
        //$this->setStudyHtml();
    }
    /**
     * @param string $causeVariableName
     * @param string $effectVariableName
     * @param QMGlobalVariableRelationship[]|QMVariableRelationship[]|QMUserVariableRelationship[] $correlations
     * @return QMVariableRelationship[]|QMUserVariableRelationship[]|QMGlobalVariableRelationship[]
     */
    protected static function putExactMatchFirst(string $causeVariableName, string $effectVariableName, array $correlations): array{
        $sorted = [];
        foreach($correlations as $correlation){
            if(strtolower($correlation->causeVariableName) === strtolower($causeVariableName) && strtolower($correlation->effectVariableName) === strtolower($effectVariableName)){
                $exactMatch = $correlation;
            }else{
                $sorted[] = $correlation;
            }
        }
        if(isset($exactMatch)){
            return array_merge([$exactMatch], $sorted);
        }
        return $sorted;
    }
    /**
     * @return mixed
     */
    public function getTValue(): ?float {
        return $this->tValue;
    }
    /**
     * @param float $daily
     */
    public function setAvgDailyValuePredictingLowOutcome(float $daily): void{
        try {
            $this->getCauseQMVariable()->validateValueForCommonVariableAndUnit(
                $daily, __FUNCTION__, 86400);
        } catch (InvalidVariableValueException $e){
            $this->logError(__METHOD__.": ".$e->getMessage());
        }
        $this->avgDailyValuePredictingLowOutcome = $daily;
    }
    /**
     * @return string
     */
    public function setDirection(): string {
        return $this->direction = $this->getDirection();
    }
    /**
     * @return StudyHtml
     */
    public function getStudyHtml(): StudyHtml{
		if(!$this->studyHtml){
            $this->studyHtml = new StudyHtml($this);
        }
        return $this->studyHtml;
    }
    /**
     * @return float|mixed
     */
    public function setOutcomeFillingValue(): ?float{
        //if($this->effectVariable){$this->outcomeFillingValue = $this->getEffectVariable()->getFillingValueAttribute();}
        return $this->outcomeFillingValue = BaseFillingValueProperty::getFillingValueOrFallback(
            $this->outcomeFillingValue,
            $this->getEffectVariableCommonUnit());
    }
    /**
     * @return mixed
     */
    public function getPredictorFillingValue(){
        /** @noinspection TypeUnsafeComparisonInspection */
        if($this->predictorFillingValue == -1){
            $this->setPredictorFillingValue();
        }
        return $this->predictorFillingValue;
    }
    /**
     * @return float|mixed
     */
    public function setPredictorFillingValue(): ?float{
        //if($this->causeVariable){$this->predictorFillingValue = $this->getCauseVariable()->getFillingValueAttribute();}
        return $this->predictorFillingValue = BaseFillingValueProperty::getFillingValueOrFallback(
            $this->predictorFillingValue,
            $this->getCauseVariableCommonUnit());
    }
    /**
     * @return int
     */
    public function getPredictsHighEffectChange(): ?float {
        if($this->predictsHighEffectChange !== null){
            $this->predictsHighEffectChange = (int)$this->predictsHighEffectChange;
        }
        return $this->predictsHighEffectChange;
    }
    /**
     * @return float
     */
    public function getQmScore(): float {
		if($this->qmScore !== null){return $this->qmScore;}
        return $this->qmScore = $this->l()->getQmScore();
    }
    abstract public function getTagLine(): string;
    /**
     * @return float
     */
    public function getForwardPearsonCorrelationCoefficient(): float {
        if(!isset($this->forwardPearsonCorrelationCoefficient)){
            $this->forwardPearsonCorrelationCoefficient = $this->correlationCoefficient;
        }
        return $this->forwardPearsonCorrelationCoefficient;
    }
    /**
     * @return int
     */
    public function getVoteCount(): int {
        if($this->voteCount === null){
            $this->setVoteCount();
        }
        return $this->voteCount;
    }
    /**
     * @return int
     */
    public function setVoteCount(): int {
		if(!$this->hasId()){
			return $this->voteCount = 0;
		}
        return $this->voteCount = count($this->getVotes());
    }
    /**
     * @return int
     */
    public function getNumberOfPairs(): int {
	    $val = intval($this->numberOfPairs);
        return $this->numberOfPairs;
    }
    /**
     * @return int
     */
    public function getPredictsLowEffectChange(): ?float {
        if($this->predictsLowEffectChange !== null){
            $this->predictsLowEffectChange = (int)$this->predictsLowEffectChange;
        }
        return $this->predictsLowEffectChange;
    }
    /**
     * @return QMCommonVariable|QMUserVariable
     */
    abstract public function getOrSetCauseQMVariable(): QMVariable;
    /**
     * @return float
     * @throws InsufficientVarianceException
     */
    public function getConfidenceInterval(): float {
        if($this->confidenceInterval === null){
            $this->calculateConfidenceInterval();
        }
        return $this->confidenceInterval;
    }
    /**
     * @return float
     * @throws InsufficientVarianceException
     */
    public function calculateConfidenceInterval(): float {
        return $this->calculatePValue();
    }
    /**
     * @return float
     */
    public function getCriticalTValue(): ?float {
        if($this->criticalTValue === null){
            $this->calculatePValue();
        }
        return $this->criticalTValue;
    }
    /**
     * @return float
     */
    public function getOptimalPearsonProduct(): float{
        return $this->optimalPearsonProduct;
    }
    /**
     * @return string
     */
    abstract public function getStudyType(): string;
    /**
     * @return string
     */
    public function getDataSourceName(): string {
        if(!$this->dataSourceName){
            $this->dataSourceName = GlobalVariableRelationshipDataSourceNameProperty::DATA_SOURCE_NAME_USER;
            $this->logDebug("No data source name so setting to user!");
        }
        return $this->dataSourceName;
    }
    /**
     * @param string $dataSource
     */
    public function setDataSourceName(string $dataSource): void{
        $this->dataSourceName = $dataSource;
    }
    /**
     * @return StudyLinks
     */
    private function setStudyLinks(): StudyLinks{
        $this->studyLinks = new StudyLinks($this);
        if(APIHelper::apiVersionIsBelow(4)){
            ObjectHelper::copyPublicPropertiesFromOneObjectToAnother($this->studyLinks, $this);
        }
        return $this->studyLinks;
    }
    /**
     * @return StudyLinks
     */
    public function getStudyLinks(): StudyLinks{
        if(!$this->studyLinks){
            $this->setStudyLinks();
        }
        $links = StudyLinks::instantiateIfNecessary($this->studyLinks);
        $links->setHasCauseAndEffect($this);
        return $this->studyLinks = $links;
    }
    /**
     * @return StudyImages
     */
    public function getStudyImages(): StudyImages{
        $this->studyImages = new StudyImages($this);
        if(APIHelper::apiVersionIsBelow(4)){
            ObjectHelper::copyPublicPropertiesFromOneObjectToAnother($this->studyImages, $this);
        }
        return $this->studyImages;
    }
    /**
     * @param HasCauseAndEffect|QMStudy $correlationObject
     */
    public static function addEffectUnit(QMVariableRelationship $correlationObject){
        if(isset($correlationObject->effectVariableCommonUnitId)){
            $correlationObject->effectVariableCommonUnitAbbreviatedName = QMUnit::getUnitById($correlationObject->effectVariableCommonUnitId)->abbreviatedName;
        }
    }
    /**
     * @return string
     */
    private function setStrengthLevel(): string{
        return $this->strengthLevel = $this->getStrengthLevel();
    }
    /**
     * @return string
     */
    private function setConfidenceLevel(): string {
		if($this->laravelModel){
			return $this->confidenceLevel = $this->l()->getConfidenceLevel();
		}
        return $this->confidenceLevel = $this->getConfidenceLevel();
    }
    /**
     * @return string
     */
    private function setEffectSize(): string {
        $c = $this->getCorrelationCoefficient();
        if($c === null){
            return "unknown";
        }
        return $this->effectSize = $this->generateEffectSize();
    }
    /**
     * @return string
     */
    public function getLogMetaDataString(): string {
        return " for cause: ".$this->getCauseVariableName()." and effect:".$this->getEffectVariableName();
            //." ".$this->getDebugStudyLink();
    }
    /**
     * @param array|null $meta
     * @return array
     */
    public function getLogMetaData(?array $meta = []):array {
        $meta[$this->getCauseAndEffectString().' DebugStudyUrl'] = $this->getDebugUrl();
        if($this->getId()){
            $meta['SHOW '.$this->getCauseAndEffectString()." Study"] = $this->getUrl();
        }
		try {
			$meta[$this->getCauseAndEffectString().' Study PHPUNIT_TEST'] = $this->getPHPUnitTestUrl();
		} catch (\Throwable $e){
		    ConsoleLog::info(__METHOD__.": Could not get Study PHPUNIT_TEST because: ".$e->getMessage());
		}
        return $meta;
    }
    private function setHoursProperties(){
	    $this->durationOfActionInHours = $this->getDurationOfAction() / 3600;
	    $this->onsetDelayInHours = $this->getOnsetDelay() / 3600;
        if(isset($this->onsetDelayWithStrongestPearsonCorrelation)){
	        $this->onsetDelayWithStrongestPearsonCorrelationInHours = $this->onsetDelayWithStrongestPearsonCorrelation / 3600;
        }
    }
    private function addUnitNames(){
        $unitsIndexedById = QMUnit::getUnitsIndexedById();
        if(!isset($unitsIndexedById[$this->getCauseVariableCommonUnitId()])){
            QMLog::error("No unit with id ".$this->causeVariableCommonUnitId);
        }
        $this->causeVariableCommonUnitAbbreviatedName = $unitsIndexedById[$this->getCauseVariableCommonUnitId()]->abbreviatedName;
        $this->effectVariableCommonUnitAbbreviatedName = $unitsIndexedById[$this->getEffectVariableCommonUnitId()]->abbreviatedName;
        $this->causeVariableCommonUnitName = $unitsIndexedById[$this->getCauseVariableCommonUnitId()]->name;
        $this->effectVariableCommonUnitName = $unitsIndexedById[$this->getEffectVariableCommonUnitId()]->name;
    }
    private function addVariableCategoryInfo(){
        $variableCategoriesIndexedById = QMVariableCategory::getVariableCategoriesIndexedById();
        if(!isset($variableCategoriesIndexedById[$this->effectVariableCategoryId])){
            $effect = $this->getOrSetEffectQMVariable();
            if(!$effect){
                $this->getOrSetEffectQMVariable();
            }
            $this->effectVariableCategoryName = $effect->getVariableCategoryName();
        }else{
            $this->effectVariableCategoryName = $variableCategoriesIndexedById[$this->effectVariableCategoryId]->name;
        }
        if(!isset($variableCategoriesIndexedById[$this->causeVariableCategoryId])){
            $this->causeVariableCategoryName = $this->getOrSetCauseQMVariable()->getVariableCategoryName();
        }else{
            $this->causeVariableCategoryName = $variableCategoriesIndexedById[$this->causeVariableCategoryId]->name;
        }
    }
    /**
     * @return bool
     */
    public static function correlateOverTime(): bool {
        if (!AppMode::isApiRequest()) {
            return true;
        }
        if(BoolHelper::isTruthy(QMRequest::getQueryParam('correlateOverTime'))){
            return true;
        }
        return false;
    }
    /**
     * @return QMUserVariable|QMCommonVariable
     */
    abstract public function getOrSetEffectQMVariable(): QMVariable;
    /**
     * @param int|string|QMVariable $v
     * @return QMVariable
     * @noinspection PhpMissingReturnTypeInspection
     */
    abstract public function setEffectVariable($v);
	/**
	 * @return float
	 */
	public function getReversePearsonCorrelationCoefficient(): ?float {
		return $this->reversePearsonCorrelationCoefficient;
	}
	/**
	 * @return float
	 */
	public function getPredictivePearsonCorrelationCoefficient(): float{
		if(!isset($this->predictivePearsonCorrelationCoefficient)){
			$this->predictivePearsonCorrelationCoefficient = $this->getForwardPearsonCorrelationCoefficient() 
				- $this->getReversePearsonCorrelationCoefficient();
		}
		return $this->predictivePearsonCorrelationCoefficient;
	}
	/**
     * @param string $causeOrEffect
     * @param $row
     * @return array
     */
    protected function getVariableProperties(string $causeOrEffect, $row): array{
        $variable = [];
        foreach($row as $key => $value){
            if(stripos($key, $causeOrEffect) !== false){
                $key = str_replace($causeOrEffect.'Variable_', '', $key);
                if($key === $causeOrEffect.'Changes'){
                    $key = 'numberOfChanges';
                }
                $key = str_replace([
                    'For'.ucfirst($causeOrEffect),
                    $causeOrEffect.'Variable'
                ], '', $key);
                $key = str_ireplace($causeOrEffect, '', $key);
                if(empty($key)){
                    continue;
                }
                $key = QMStr::camelize($key);
                $key = str_replace('category', 'variableCategory', $key);
                //if($value !== null){$variable[$key] = $value;}
                if(!empty($key)){
                    $variable[$key] = $value;
                }
            }
        }
        return $variable;
    }
    /**
     * @param $row
     * @return array
     */
    protected function getCauseVariableProperties($row): array{
        $properties = $this->getVariableProperties('cause', $row);
        $properties = array_merge($properties, $this->getVariableProperties('predictor', $row));
        return $properties;
    }
    /**
     * @param $row
     * @return array
     */
    protected function getEffectVariableProperties($row): array{
        $properties = $this->getVariableProperties('effect', $row);
        $properties = array_merge($properties, $this->getVariableProperties('outcome', $row));
        return $properties;
    }
    /**
     * @return Vote[]|Collection
     */
    public function getVotes(): Collection {
        return $this->votes = $this->l()->getVotes();
    }
    /**
     * @return float
     */
    public function getVoteSum(): float {
	    return $this->voteSum = $this->l()->getVoteSum();
    }
    /**
     * @return float|int
     */
    public function calculateWeightedAverageVote(): float {
        return $this->weightedAverageVote = (1 + $this->getVoteSum()) / (1 + $this->getVoteCount());
    }
    /**
     * @param QMVariable|int|string $causeVariableNameOrId
     * @return QMUserVariable|QMCommonVariable
     */
    abstract public function setCauseVariable($causeVariableNameOrId);
    public function unsetVariables(){
        $this->causeVariable = $this->effectVariable = null;
    }
    /**
     * @return string
     */
    public function getEffectSize(): string{
        //return $this->effectSize ?: $this->setEffectSize();
        return $this->setEffectSize();
    }
    /**
     * @return int
     */
    public function getCauseVariableId(): int{
        if(!empty($this->causeId)){
            $this->causeVariableId = $this->causeId;
        }
        if(!$this->causeVariableId && $this->causeVariable){
            $this->causeVariableId = $this->getOrSetCauseQMVariable()->getVariableIdAttribute();
        }
        if(!$this->causeVariableId){le("Provide cause variable id");}
        return $this->causeVariableId;
    }
    /**
     * @return int
     */
    public function getEffectVariableId(): int{
        if(!empty($this->effectId)){
            $this->effectVariableId = $this->effectId;
        }
        if(!$this->effectVariableId && $this->effectVariable){
            $this->effectVariableId = $this->getOrSetEffectQMVariable()->getVariableIdAttribute();
        }
        if(!$this->effectVariableId){
            le("No effectVariableId!");
        }
        return $this->effectVariableId;
    }
    /**
     * @return StudyText
     */
    public function getStudyText(): StudyText {
        if (!isset($this->studyText)) {
            $this->setStudyText();
        }
        return $this->studyText = StudyText::instantiateIfNecessary($this->studyText);
    }
    /**
     * @return StudyText
     */
    public function setStudyText(): StudyText{
        $this->studyText = new StudyText($this);
        if(APIHelper::apiVersionIsBelow(4)){
            ObjectHelper::copyPublicPropertiesFromOneObjectToAnother($this->studyText, $this);
        }
        return $this->studyText;
    }
    /**
     * @return bool
     */
    public function getIsPublic(): ?bool{
        if($this->isPublic !== null){
            return $this->isPublic;
        }
        return $this->isPublic = $this->findInMemoryOrNewQMStudy()->getIsPublic();
    }
    /**
     * @return QMUserStudy|QMPopulationStudy
     */
    abstract public function findInMemoryOrNewQMStudy(): QMStudy;
    /**
     * @return string
     */
    public function getCauseVariableName(): string{
        if($this->causeVariableName){return $this->causeVariableName;}
        return $this->causeVariableName = $this->getCauseVariable()->name;
    }
    /**
     * @param QMVariable $effect
     */
    protected function throwExceptionIfCauseAndEffectAreSameButHaveDifferentProperties(QMVariable $effect){
        if(!$this->causeVariable){return;}
        $cause = $this->getOrSetCauseQMVariable();
        if($cause->variableId === $effect->variableId){
            foreach($cause as $key => $causeValue){
                $effectValue = $effect->$key;
                if($effectValue !== $causeValue){
                    le("Same variables cause and effect\n$effect->name\nbut $key doesn't match!
    cause: $causeValue
    effect: $effectValue
");
                }
            }
        }
    }
    /**
     * @return string
     */
    public function getEffectVariableName(): string{
        if($this->effectVariableName){return $this->effectVariableName;}
        return $this->effectVariableName = $this->getEffectVariable()->name;
    }
    /**
     * @return string
     */
    public function getCauseVariableCommonUnitAbbreviatedName(): string{
        if($this->causeVariableCommonUnitAbbreviatedName){
            return $this->causeVariableCommonUnitAbbreviatedName;
        }
        return $this->setCauseVariableCommonUnitAbbreviatedName();
    }
    /**
     * @return string
     */
    public function setCauseVariableCommonUnitAbbreviatedName(): string{
        return $this->causeVariableCommonUnitAbbreviatedName = $this->getCauseVariableCommonUnit()->abbreviatedName;
    }
    /**
     * @return string
     */
    public function getEffectVariableCommonUnitAbbreviatedName(): string{
        if($this->effectVariableCommonUnitAbbreviatedName){
            return $this->effectVariableCommonUnitAbbreviatedName;
        }
        return $this->setEffectVariableCommonUnitAbbreviatedName();
    }
    /**
     * @return string
     */
    public function setEffectVariableCommonUnitAbbreviatedName(): string{
        return $this->effectVariableCommonUnitAbbreviatedName = $this->getEffectVariableCommonUnit()->abbreviatedName;
    }
    /**
     * @return QMUnit
     */
    public function getEffectVariableCommonUnit(): QMUnit {
        return QMUnit::getUnitById($this->getEffectVariableCommonUnitId());
    }
    /**
     * @return QMUnit
     */
    public function getCauseVariableCommonUnit(): QMUnit {
        return QMUnit::getUnitById($this->getCauseVariableCommonUnitId());
    }
    /**
     * @return int
     */
    public function getCauseVariableCommonUnitId(): int{
        if($this->causeVariableCommonUnitId){
            return $this->causeVariableCommonUnitId;
        }
        if($this->causeVariableCommonUnitName){
            return $this->causeVariableCommonUnitId = QMUnit::getByNameOrId($this->causeVariableCommonUnitName)->id;
        }
        if(isset($this->causeVariable)){
            return $this->causeVariableCommonUnitId = $this->getOrSetCauseQMVariable()->unitId;
        }
        /** @var GlobalVariableRelationship $l */
        $l = $this->l();
        return $this->causeVariableCommonUnitId = $l->getCauseVariable()->default_unit_id;
    }
    /**
     * @return int
     */
    public function getEffectVariableCommonUnitId(): int{
        if($this->effectVariableCommonUnitId){
            return $this->effectVariableCommonUnitId;
        }
        if($this->effectVariableCommonUnitName){
            return $this->effectVariableCommonUnitId = QMUnit::getByNameOrId($this->effectVariableCommonUnitName)->id;
        }
        if(isset($this->effectVariable)){
            return $this->effectVariableCommonUnitId = $this->getOrSetEffectQMVariable()->unitId;
        }
        /** @var GlobalVariableRelationship $l */
        $l = $this->l();
        return $this->effectVariableCommonUnitId = $l->getEffectVariable()->default_unit_id;
    }
    /**
     * @return int|string
     */
    public function getCauseVariableNameOrId(){
        $id = $this->getCauseVariableId();
        if($id){
            return $id;
        }
        $name = $this->getCauseVariableName();
        if($name){
            return $name;
        }
        return null;
    }
    /**
     * @return int|string
     */
    public function getEffectVariableNameOrId(){
        $id = $this->getEffectVariableId();
        if($id){
            return $id;
        }
        $name = $this->getEffectVariableName();
        if($name){
            return $name;
        }
        return null;
    }
    protected function formatNumbers(){
        $intProperties = [
            'causeChanges',
            'effectChanges',
        ];
        foreach($intProperties as $property){
            if(!empty($this->$property)){
                $this->$property = (int)$this->$property;
            }
        }
        $floatProperties = [
            'predictsHighEffectChange',
            'predictsLowEffectChange',
        ];
        foreach($floatProperties as $property){
            if(!empty($this->$property)){
                $this->$property = (float)$this->$property;
            }
        }
    }
    /**
     * @return QMVariableCategory
     */
    public function setCauseQMVariableCategory(): QMVariableCategory {
        $category = $this->causeVariableCategory;
        if($category){
            return $this->causeVariableCategory = QMVariableCategory::instantiateIfNecessary($category);
        }
        $id = $this->getCauseVariableCategoryId();
        return $this->causeVariableCategory = QMVariableCategory::find($id);
    }
    /**
     * @return QMVariableCategory
     */
    public function setEffectQMVariableCategory(): QMVariableCategory {
        $category = $this->effectVariableCategory;
        if($category){
            return $this->effectVariableCategory = QMVariableCategory::instantiateIfNecessary($category);
        }
        $id = $this->getEffectVariableCategoryId();
        return $this->effectVariableCategory = QMVariableCategory::find($id);
    }
    /**
     * @param int|null $sigFigs
     * @return float
     */
    public function getPValue(int $sigFigs = null): ?float {
        if($this->pValue === null){
            $this->logError("p value is null!");
            return null;
        }
        if($this->pValue === 0){
            $this->logDebug("p value is 0!");
            return 0;
        }
        if($sigFigs){ // Don't round! It usually rounds to zero for some reason
            return $this->round($this->pValue, $sigFigs);
        }
        return $this->pValue;
    }
    /**
     * @return bool
     */
    public function getCauseVariableIsOutcome(): ?bool {
        if($this->causeVariableIsOutcome === null){
            $this->setCauseVariableIsOutcome();
        }
        return $this->causeVariableIsOutcome;
    }
    /**
     * @return bool
     */
    public function setCauseVariableIsOutcome(): ?bool {
        $this->causeVariableIsOutcome = $this->getCauseQMVariableCategory()->outcome;
        if($this->causeVariable){
            $this->causeVariableIsOutcome = $this->getOrSetCauseQMVariable()->outcome;
        }
        return $this->causeVariableIsOutcome;
    }
    /**
     * @return bool
     */
    public function getCauseVariableIsPredictor(): ?bool {
        if($this->causeVariableIsPredictor === null){
            $this->setCauseVariableIsPredictor();
        }
        return $this->causeVariableIsPredictor;
    }
    /**
     * @return bool
     */
    public function setCauseVariableIsPredictor(): ?bool {
        $this->causeVariableIsPredictor = $this->getCauseQMVariableCategory()->getPredictor();
        if($this->causeVariable){
            $this->causeVariableIsPredictor = $this->getOrSetCauseQMVariable()->isPredictor();
        }
        return $this->causeVariableIsPredictor;
    }
    /**
     * @return bool
     */
    public function getEffectVariableIsOutcome(): ?bool {
        if($this->effectVariableIsOutcome === null){
            $this->setEffectVariableIsOutcome();
        }
        return $this->effectVariableIsOutcome;
    }
    /**
     * @return bool
     */
    public function setEffectVariableIsOutcome(): ?bool {
        $this->effectVariableIsOutcome = $this->getEffectQMVariableCategory()->outcome;
        if($this->effectVariable){
            $this->effectVariableIsOutcome = $this->getOrSetEffectQMVariable()->outcome;
        }
        return $this->effectVariableIsOutcome;
    }
    /**
     * @return int|null
     */
    abstract public function getUserId(): ?int;
    /**
     * @return $this
     */
    public function getHasCorrelationCoefficient() {
        return $this;
    }
    /**
     * @param string $type
     */
    public function setType(string $type): void{
        $this->type = $type;
    }
    /**
     * @param int|null $precision
     * @return float
     */
    public function getCorrelationCoefficient(int $precision = null): ?float {
	    $c = $this->correlationCoefficient ?? $this->forwardPearsonCorrelationCoefficient ?? null;
        if($c === null){
			if($this->laravelModel){
				$l = $this->l();
				$c = $l->forward_pearson_correlation_coefficient;
				if($c !== null){
					$this->setCorrelationCoefficient($c);
				}
			}
			if($c === null){
				return null;
			}
        }
	    $this->setCorrelationCoefficient($c);
        if($precision){
            return Stats::roundByNumberOfSignificantDigits($c, $precision);
        }
        return $c;
    }
    /**
     * @param int|null $precision
     * @return float
     */
    public function getReverseCorrelationCoefficient(int $precision = null): ?float {
	    $c = $this->reversePearsonCorrelationCoefficient;
	    if($c === null){
		    if($this->laravelModel){
			    $l = $this->l();
			    $c = $l->reverse_pearson_correlation_coefficient;
			    $this->reversePearsonCorrelationCoefficient = $c;
		    }
		    if($c === null){
			    return null;
		    }
	    }
        if($precision && $c !== null){
            return $this->round($c, $precision);
        }
        return $c;
    }
    /**
     * @param int|null $precision
     * @return float
     */
    public function getAverageEffectFollowingHighCause(int $precision = null): ?float {
        $val = $this->averageEffectFollowingHighCause;
        if($precision && $val !== null){
            return $this->round($this->averageEffectFollowingHighCause, $precision);
        }
        return $val;
    }
    /**
     * @param int|null $precision
     * @return float
     */
    public function getAverageEffectFollowingLowCause(int $precision = null): ?float {
        $val = $this->averageEffectFollowingLowCause;
        if($precision){
            return $this->round($val, $precision);
        }
        return $val;
    }
    /**
     * @param int|null $precision
     * @return float
     */
    public function getAverageDailyHighCause(int $precision = null): ?float {
        $val = $this->averageDailyHighCause;
        if($precision && $val !== null){
            return $this->round($val, $precision);
        }
        return $val;
    }
    /**
     * @param int|null $precision
     * @return float
     */
    public function getAverageDailyLowCause(int $precision = null): ?float {
        $val = $this->averageDailyLowCause;
        if($precision && $val !== null){
            return $this->round($val, $precision);
        }
        return $val;
    }
    /**
     * @return int
     */
    public function setOnsetDelayInHours(): int {
        $onsetDelay = round($this->getOnsetDelay() / 3600, 1);
        return $this->onsetDelayInHours = (int)$onsetDelay;
    }
    /**
     * @return int
     */
    public function setDurationOfActionInHours(): float {
        $duration = round($this->getDurationOfAction() / 3600, 1);
        return $this->durationOfActionInHours = $duration;
    }
    /**
     * @return int
     */
    public function getOnsetDelay(): int {
        $delay = $this->onsetDelay;
        if($delay === null){$delay = $this->getOrSetCauseQMVariable()->getOnsetDelay();}
		if($delay === null){le( "onsetDelay null!");}
        return $this->onsetDelay = $delay;
    }
    /**
     * @return mixed
     */
    public function getDurationOfAction(): int {
        $duration = $this->durationOfAction;
        if(!$duration){
            $v = $this->getOrSetCauseQMVariable();
            $duration = $v->getDurationOfAction();
		if(!$duration){le( "No duration of action from $v", ['v' => $v]);}
        }
        return $this->durationOfAction = $duration;
    }
    /**
     * @return string
     */
    public function getCauseNameWithoutCategoryOrUnit(): string {
        if($this->causeVariableDisplayNameWithoutCategoryOrUnitSuffix){return $this->causeVariableDisplayNameWithoutCategoryOrUnitSuffix;}
        if($this->causeVariableDisplayName){return $this->causeVariableDisplayNameWithoutCategoryOrUnitSuffix = $this->causeVariableDisplayName;}
        $original = $this->getCauseVariableName();
        return $this->causeVariableDisplayName = $this->causeVariableDisplayNameWithoutCategoryOrUnitSuffix =
            VariableNameProperty::removeSuffix($original,
                $this->getCauseVariableCommonUnit(),
                true);
    }
    /**
     * @param Builder|QMQB|\Illuminate\Database\Eloquent\Builder $qb
     * @param array $requestParams
     */
    protected static function applyOffsetLimitSort($qb, array $requestParams){
        if(!isset($requestParams['limit'])){
            $requestParams['limit'] = self::DEFAULT_CORRELATION_LIMIT;
        }
        QueryBuilderHelper::applyOffsetLimitSort($qb, $requestParams);
    }
    /**
     * @return string
     */
    public function setPredictorExplanationSentence(): string {
        return $this->predictorExplanationSentence =  $this->predictorExplanation = $this->generatePredictorExplanationSentence();
    }
    /**
     * @return StudyCard
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function getOptionsListCard(){
        $s = $this->findInMemoryOrNewQMStudy();
        return $s->getOptionsListCard();
    }
    /**
     * @return GlobalVariableRelationship|UserVariableRelationship
     */
    public function l(){
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return parent::l();
    }
    abstract public function updateOptimalValueSentencesIfNecessary(): void;
    /**
     * @return QMGlobalVariableRelationship
     */
    abstract public function getOrCreateQMGlobalVariableRelationship(): QMGlobalVariableRelationship ;
    /**
     * @return string
     */
    public function getPublishedAt():? string {
        return $this->publishedAt;
    }
    /**
     * @param mixed $publishedAt
     */
    public function setPublishedAtAttribute(?string $publishedAt){
        /** @var QMStudy $s */
        if($s = $this->findStudyInMemory()){$s->published_at = $publishedAt;}
        $this->publishedAt = $publishedAt;
    }
    /**
     * @return float
     */
    public function getStatisticalSignificance(): float{
        return $this->statisticalSignificance;
    }
    /**
     * @return bool
     */
    public function isInteresting(): bool{
        if(!$this->getCauseVariableIsPredictor()){
            return $this->interestingVariableCategoryPair = false;
        }
        if(!$this->getEffectVariableIsOutcome()){
            return $this->interestingVariableCategoryPair = false;
        }
        return $this->interestingVariableCategoryPair = true;
    }
    /**
     * @param string $causeVariableCategoryName
     * @param string $effectVariableCategoryName
     * @return bool
     */
    public static function stupidCategoryPair(string $causeVariableCategoryName, string $effectVariableCategoryName): bool{
        foreach(self::$stupidCategoryPairs as $pair){
            if($causeVariableCategoryName === $pair['cause'] && $effectVariableCategoryName === $pair['effect']){
                return true;
            }
        }
        return false;
    }
    /**
     * @param int $effectId
     * @param string $reason
     * @return int
     */
    public static function deleteByEffectId(int $effectId, string $reason): int {
        $qb = QMUserVariableRelationship::writable()->where(UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID, $effectId);
        $c = $qb->count();
        QMLog::error("Deleting $c user variable relationships where effect...");
        $deletedUser = $qb->hardDelete($reason, true);
        $qb = QMGlobalVariableRelationship::writable()->where(GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID, $effectId);
        $c = $qb->count();
        QMLog::error("Deleting $c global variable relationships where effect $effectId...");
        $deletedAggregate = $qb->hardDelete($reason, true);
        return $deletedAggregate + $deletedUser;
    }
    /**
     * @return float
     */
    public function getAbsoluteFollowupChange():float {
        return $this->effectFollowUpAverage - $this->effectBaselineAverage;
    }
//    /**
//     * @return string
//     * @throws \App\Exceptions\InvalidS3PathException
//     */
//    public function getS3BucketAndFolderPath(): string{
//        $study = $this->getOrSetStudy();
//        $s3 = $study->getS3BucketAndFolderPath();
//        S3Helper::validateS3BucketAndPath($s3);
//        return $s3;
//    }
    /**
     * @return StudyReport
     */
    public function getReport(): StudyReport {
        return $this->findInMemoryOrNewQMStudy()->getReport();
    }
    /**
     * @return string
     */
    abstract public function getStudyId(): string;
    /**
     * @return string
     */
    public function getNewestDataAt(): ?string {
        $cause = $this->getOrSetCauseQMVariable();
        $cTime = $cause->getNewestDataAt();
        $effect = $this->getOrSetEffectQMVariable();
        $eTime = $effect->getNewestDataAt();
        return max([$eTime, $cTime]);
    }
    /**
     * @param string $key
     * @return mixed
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function getAttribute($key){
        if($key === UserVariableRelationship::FIELD_USER_ID && $this instanceof QMGlobalVariableRelationship){return UserIdProperty::USER_ID_SYSTEM;}
        if($key === UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID && $this->causeVariableId){return $this->causeVariableId;}
        if($key === UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID && $this->effectVariableId){return $this->effectVariableId;}
        return parent::getAttribute($key);
    }
    public function removeRecursion(){
        parent::removeRecursion();
        foreach($this as $key => $value){
            if($value instanceof QMVariable){$value->unsetCorrelations();}
        }
    }
    /**
     * @return CorrelationChartGroup|GlobalVariableRelationshipChartGroup
     */
    abstract public function getOrSetCharts(): ChartGroup;
    /**
     * @return CorrelationChartGroup|GlobalVariableRelationshipChartGroup
     */
    abstract public function setCharts(): ChartGroup;
    public function getChartGroup(): ChartGroup {
        return $this->getOrSetCharts();
    }
    public function cleanup(){
        throw new LogicException(__FUNCTION__." not implemented for ".static::class);
    }
    public function setIsPublic(bool $val){
        $this->isPublic = $val;
    }
    abstract public function getDataQuantitySentence():string;
    public function getCard(): QMCard{
        return $this->findInMemoryOrNewQMStudy()->getCard();
    }
    public function getCauseVariableCategoryName(): string {
        return $this->causeVariableCategoryName = $this->getCauseQMVariableCategory()->name;
    }
    /**
     * @return string
     */
    public function getEffectVariableCategoryName(): string {
        return $this->effectVariableCategoryName = $this->getEffectQMVariableCategory()->name;
    }
	/**
	 * @param int|null $userId
	 * @return mixed
	 */
	abstract public function getUserVoteValue(int $userId = null);
    public function populateDefaultFields(){
        parent::populateDefaultFields();
        $this->getCorrelationCoefficient();
        $this->getCauseVariableCategoryName();
        $this->getEffectVariableCategoryName();
        $this->getCauseVariableName();
        $this->setDirection();
        $this->getCauseVariableCommonUnitAbbreviatedName();
        $this->getEffectVariableCommonUnitAbbreviatedName();
        $this->getStrengthLevel();
        $this->setDurationOfActionInHours();
        $this->setOnsetDelayInHours();
        $this->getQmScore();
        $this->generatePredictorExplanationSentence();
        $this->setEffectQMVariableCategory();
        $this->setCauseQMVariableCategory();
        if($this->valuePredictingLowOutcome !== null){
            $this->setAvgDailyValuePredictingLowOutcome($this->valuePredictingLowOutcome);
        }
        if($this->valuePredictingHighOutcome !== null){
            $this->setAvgDailyValuePredictingHighOutcome($this->valuePredictingHighOutcome);
        }
        $this->setEffectSize();
        $this->setConfidenceLevel();
        $this->setStrengthLevel();
    }
    public function getCauseVariableCategoryId(): int {
        $id = $this->causeVariableCategoryId;
        if($id){return $id;}
        /** @var GlobalVariableRelationship $l */
        $l = $this->l();
        if(!$l && $this->causeVariable){
            return $this->causeVariableCategoryId = $this->getOrSetCauseQMVariable()->variableCategoryId;
        }
        $id = $l->getCauseVariable()->variable_category_id;
        return $this->causeVariableCategoryId = $id;
    }
    public function getEffectVariableCategoryId(): int {
        $id = $this->effectVariableCategoryId;
        if($id){return $id;}
        /** @var GlobalVariableRelationship $l */
        $l = $this->l();
        if(!$l && $this->effectVariable){
            return $this->effectVariableCategoryId = $this->getOrSetEffectQMVariable()->variableCategoryId;
        }
        $id = $l->getEffectVariable()->variable_category_id;
        return $this->effectVariableCategoryId = $id;
    }
    abstract public function findGlobalVariableRelationship(): ?GlobalVariableRelationship;
    public function getGlobalVariableRelationship(): GlobalVariableRelationship{
        return $this->getOrCreateQMGlobalVariableRelationship()->l();
    }
	/**
	 * @return \App\Models\Study|null
	 */
	public function findStudyInMemory(): ?Study{
		return Study::findInMemory($this->getStudyId());
	}
}
