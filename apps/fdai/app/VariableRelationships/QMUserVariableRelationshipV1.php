<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\VariableRelationships;
use App\Logging\QMLog;
use App\Models\UserVariableRelationship;
class QMUserVariableRelationshipV1 {
    public const MINIMUM_CHANGES = 3;
    public const MINIMUM_NUMBER_OF_DAYS_IN_COMMON = 10;
    public const MINIMUM_PAIRS = 10;
    public const MINIMUM_RAW_MEASUREMENTS = 10;
    public const PERCENT_DISTANCE_FROM_MEDIAN_TO_BE_CONSIDERED_HIGH_OR_LOW_EFFECT = 25;
    public const REQUIRED_NEW_MEASUREMENT_PERCENT_FOR_CORRELATION = 10;
    public const SIGNIFICANT_CHANGE_SPREAD = 3;
    public const USE_SIMPLIFIED_QM_SCORE = true;
    public $allPairsSignificance;
    public float $averageDailyHighCause;
    public float $averageDailyLowCause;
    public float $averageEffect;
    public float $averageEffectFollowingHighCause;
    public float $averageEffectFollowingHighCauseExplanation;
    public float $averageEffectFollowingLowCause;
    public float $averageEffectFollowingLowCauseExplanation;
    public float $averageForwardPearsonCorrelationOverOnsetDelays;
    public float $averagePearsonCorrelationCoefficientOverOnsetDelays;
    public float $averageReversePearsonCorrelationOverOnsetDelays;
    public float $averageVote; // Average of all user votes
    public $calculationStartTime;
    public $causalityFactor;
    public $causeChanges;
    public $causeChangesStatisticalSignificance;
    public $causeDataSource;
    public $causeNumberOfProcessedDailyMeasurements;
    public $causeNumberOfRawMeasurements;
    public $causeProcessedDailyMeasurements;
    public $causeVariableIsPublic;
    public $causeValueSpread;
    public $causeVariable;
    public $causeVariableCategoryId;
    public $causeVariableCategoryName;
    public $causeVariableCombinationOperation;
    public $causeVariableDefaultUnitAbbreviatedName;
    public $causeVariableDefaultUnitId; // Unit Id of measurement for valuePredictingHighOutcome valuePredictingLowOutcome
    public $causeVariableDefaultUnitName;
    public $causeVariableId;
    public $causeVariableMostCommonConnectorId;
    public $causeVariableName;
    public $charts;
	public ?float $confidenceInterval = null;
    public $confidenceLevel;
    public float $correlationCoefficient; // Pearson correlation coefficient
    public $correlationIsContradictoryToOptimalValues;
    public $correlationsOverDurationsOfAction;
    public $correlationsOverDurationsOfActionChartConfig;
    public $correlationsOverOnsetDelaysChartConfig;
    public $createdAt; // created time
    public $criticalTValue;
    public $dataAnalysis;
    public $dataPoints;
    public $dataSources;
    public $dataSourcesParagraphForCause;
    public $dataSourcesParagraphForEffect;
    public $degreesOfFreedom;
    public $direction;
    public $durationOfAction; // duration of effect
    public $durationOfActionInHours;
    public $effectChanges;
    public $effectDataSource;
    public $effectNumberOfProcessedDailyMeasurements;
    public $effectNumberOfRawMeasurements;
    public $effectProcessedDailyMeasurements;
    public $effectSize;
    public $effectVariableIsPublic;
    public $effectValueSpread;
    public $effectVariable;
    public $effectVariableCategoryId;
    public $effectVariableCategoryName; // The variable category of the effect
    public $effectVariableDefaultUnitAbbreviatedName;
    public $effectVariableDefaultUnitId;
    public $effectVariableDefaultUnitName;
    public $effectVariableId;
    public $effectVariableMostCommonConnectorId;
    public $effectVariableName;
    public $internalErrorMessage;
    public $experimentEndTime;
    public $experimentStartTime;
    public float $forwardSpearmanCorrelationCoefficient;
    public $gaugeImage;
    public $gaugeImageSquare;
    public $imageUrl;
    public $instructionsForCause;
    public $instructionsForEffect;
    public $maximumCauseValue;
    public $maximumEffectValue;
    public $medianOfLowerHalfOfEffectMeasurements;
    public $medianOfUpperHalfOfEffectMeasurements;
    public $minimumCauseValue;
    public $minimumEffectValue;
    public $minimumProbability;
    public $numberOfCauseChangesForOptimalValues;
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
    public float $optimalPearsonProduct; // Optimal Pearson Product
    public $outcomeDataSources;
    public $outcomeFillingValue;
    public $outcomeMaximumAllowedValue;
    public $pairsOverTimeChartConfig;
    public float $pearsonCorrelationWithNoOnsetDelay;
    public $perDaySentenceFragment;
    public float $predictivePearsonCorrelationCoefficient;
    public $predictorExplanation;
    public $predictorFillingValue;
    public $predictorMaximumAllowedValue;
    public $predictsHighEffectChange;
    public $predictsHighEffectChangeSentenceFragment;
    public $predictsLowEffectChange;
    public $predictsLowEffectChangeSentenceFragment;
    public $principalInvestigator;
    public $pValue;
    public $qmScore;
    public $rawCauseMeasurementSignificance;
    public $rawEffectMeasurementSignificance;
    public $reversePairsCount;
    public float $reversePearsonCorrelationCoefficient;
    public $isPublic;
    public $significanceExplanation;
    public $significantDifference;
    public float $statisticalSignificance;
    public $strengthLevel;
    public float $strongestPearsonCorrelationCoefficient;
    public $studyAbstract;
    public $studyBackground;
    public $studyDesign;
    public $studyLimitations;
    public $studyLinkDynamic;
    public $studyLinkEmail;
    public $studyLinkFacebook;
    public $studyLinkGoogle;
    public $studyLinkStatic;
    public $studyLinkTwitter;
    public $studyObjective;
    public $studyResults;
    public $studyTitle;
    public $timestamp;
    public $tValue;
    public $updatedAt; // updated time
    public $userId;
    public $userVote; // 1 (thumbs up), 0 (thumbs down), or 0.5 (no thumbs - default)
    public $avgDailyValuePredictingHighOutcome; // Average cause value when the effect is above average
    public $valuePredictingHighOutcomeExplanation;
    public $avgDailyValuePredictingLowOutcome; // Average cause value when the effect is below average
    public $valuePredictingLowOutcomeExplanation;
    public $voteStatisticalSignificance;
    public $chartHtml;
    /**
     * todo : fix it, use setters instead, cause no one in the world can remember the proper order for 15 parameters!
     * @param float $correlationCoefficient is value between -1 and 1
     * @param string $causeVariableName is the original name of the cause
     * @param string $causeVariableCategoryName is the name of the category this cause variable belongs to -
     * @param string $effectVariableName is effect variable original name - string
     * @param string $effectVariableCategoryName is the name of the category this effect variable belongs to - string
     * @param int $onsetDelay - time until cause exerts observable effect
     * @param int $durationOfAction - time period after onset delay over which effect is observed
     * @param int $numberOfPairs - sum of the number of changes in the effect signal and the cause signal
     * @param int $timestamp - time at which correlation was calculated
     * @param float $valuePredictingHighOutcome - cause value that predicts an above average effect value (in default
     *     unit for cause variable)
     * @param float $valuePredictingLowOutcome - cause value that predicts a below average effect value (in default
     *     unit for cause variable)
     * @param float $optimalPearsonProduct - Optimal Pearson Product
     * @param float $reversePearsonCorrelationCoefficient
     * @param float $predictivePearsonCorrelationCoefficient
     * @param float $causalityFactor - TODO
     * @param float $averageVote
     * @param float $userVote
     * @param null $statisticalSignificance - TODO
     * @param null $causeVariableDefaultUnitAbbreviatedName
     * @param float $causeVariableDefaultUnitId
     * @param null $causeChanges
     * @param null $effectChanges
     * @param float $qmScore
     * @param string $error
     * @param string $createdAt
     * @param string $updatedAt
     * @param float $predictsHighEffectChange
     * @param float $predictsLowEffectChange
     * @param float $pValue
     * @param float $tValue
     * @param float $criticalTValue
     * @param float $confidenceInterval
     * @param string $experimentStartTime
     * @param string $experimentEndTime
     * @internal param null $average_vote
     * @internal param int|null $vote - user submitted evaluation of plausibility - 1 (thumbs up), 0 (thumbs down), or
     *     default null
     */
    public function __construct($correlationCoefficient = null, $causeVariableName = null, $causeVariableCategoryName = null, $effectVariableName = null, $effectVariableCategoryName = null, $onsetDelay = null, $durationOfAction = null, $numberOfPairs = null, $timestamp = null, $valuePredictingHighOutcome = null, $valuePredictingLowOutcome = null, $optimalPearsonProduct = null, $reversePearsonCorrelationCoefficient = null, $predictivePearsonCorrelationCoefficient = null, $causalityFactor = null, $averageVote = null, $userVote = null, $statisticalSignificance = null, $causeVariableDefaultUnitAbbreviatedName = null, $causeVariableDefaultUnitId = null, $causeChanges = null, $effectChanges = null, $qmScore = null, $error = null, $createdAt = null, $updatedAt = null, $predictsHighEffectChange = null, $predictsLowEffectChange = null, $pValue = null, $tValue = null, $criticalTValue = null, $confidenceInterval = null, $experimentStartTime = null, $experimentEndTime = null){
		if($correlationCoefficient === null){
			return;
		}
	    $this->correlationCoefficient = $correlationCoefficient;
        $this->causeVariableName = $causeVariableName;
        $this->causeVariableCategoryName = $causeVariableCategoryName;
        $this->effectVariableName = $effectVariableName;
        $this->effectVariableCategoryName = $effectVariableCategoryName;
        $this->onsetDelay = $onsetDelay;
        $this->durationOfAction = $durationOfAction;
        $this->numberOfPairs = $numberOfPairs;
        $this->timestamp = $timestamp;
        $this->avgDailyValuePredictingHighOutcome = $valuePredictingHighOutcome;
        $this->avgDailyValuePredictingLowOutcome = $valuePredictingLowOutcome;
		if($optimalPearsonProduct !== null){
			$this->optimalPearsonProduct = $optimalPearsonProduct;
		}
		if($reversePearsonCorrelationCoefficient !== null){
			$this->reversePearsonCorrelationCoefficient = $reversePearsonCorrelationCoefficient;
		}
		if($predictivePearsonCorrelationCoefficient !== null){
			$this->predictivePearsonCorrelationCoefficient = $predictivePearsonCorrelationCoefficient;
		}
        $this->causalityFactor = $causalityFactor;
        $this->averageVote = $averageVote;
        $this->userVote = $userVote;
        $this->statisticalSignificance = $statisticalSignificance;
        $this->causeVariableDefaultUnitAbbreviatedName = $causeVariableDefaultUnitAbbreviatedName;
        $this->causeVariableDefaultUnitId = $causeVariableDefaultUnitId;
        $this->causeChanges = $causeChanges;
        $this->effectChanges = $effectChanges;
        $this->qmScore = $qmScore;
        $this->internalErrorMessage = $error;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->predictsHighEffectChange = $predictsHighEffectChange;
        $this->predictsLowEffectChange = $predictsLowEffectChange;
        $this->pValue = $pValue;
        $this->tValue = $tValue;
        $this->criticalTValue = $criticalTValue;
        $this->confidenceInterval = $confidenceInterval;
        $this->experimentStartTime = $experimentStartTime;
        $this->experimentEndTime = $experimentEndTime;
    }
    /**
     * @return mixed
     */
    public function getEffectProcessedDailyMeasurements(){
        return $this->effectProcessedDailyMeasurements;
    }
    /**
     * @param mixed $effectProcessedDailyMeasurements
     */
    public function setEffectProcessedDailyMeasurements($effectProcessedDailyMeasurements){
        $this->effectProcessedDailyMeasurements = $effectProcessedDailyMeasurements;
    }
    /**
     * @return mixed
     */
    public function getCauseProcessedDailyMeasurements(){
        return $this->causeProcessedDailyMeasurements;
    }
    /**
     * @param mixed $causeProcessedDailyMeasurements
     */
    public function setCauseProcessedDailyMeasurements($causeProcessedDailyMeasurements){
        $this->causeProcessedDailyMeasurements = $causeProcessedDailyMeasurements;
    }
    public function getCauseVariable(){
        return $this->causeVariable;
    }
    public function getEffectVariable(){
        return $this->causeVariable;
    }
    public function deleteFromDatabase(){
        QMLog::debug("Deleting user_variable_relationships between $this->causeVariableName and $this->effectVariableName");
        QMUserVariableRelationship::writable()
            ->where(UserVariableRelationship::FIELD_USER_ID, $this->userId)
            ->where(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID, $this->causeVariableId)
            ->where(UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID, $this->effectVariableId)
            ->delete();
    }
    /**
     * @return string
     */
    public function getChartHtml(){
        return $this->chartHtml;
    }
}
