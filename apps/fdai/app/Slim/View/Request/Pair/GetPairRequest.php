<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\View\Request\Pair;
use App\Exceptions\BadRequestException;
use App\Exceptions\InvalidVariableValueException;
use App\Logging\QMLog;
use App\Slim\Model\Measurement\DailyMeasurement;
use App\Slim\Model\Measurement\Pair;
use App\Slim\QMSlim;
use App\Slim\View\Request\Request;
use App\Utils\APIHelper;
use App\Utils\Env;
use App\Utils\Stats;
use App\Variables\QMUserVariable;
/** Holds parameters for the get pairs API request
 * @package App\Slim\Controller\Requests
 */
class GetPairRequest extends Request {
	const USE_USER_DEFINED_EXPERIMENT_TIMES = false;
	private array $causeDailyMeasurements;
	/**
	 * @var QMUserVariable
	 */
	private $causeQMUserVariable;
	private array $effectDailyMeasurements;
	/**
	 * @var QMUserVariable
	 */
	private $effectQMUserVariable;
	/**
	 * @var int
	 */
	private $durationOfAction;
	public string $effectUnitAbbreviatedName;
	public string $causeUnitAbbreviatedName;
	/**
	 * @var int
	 */
	private $onsetDelay;
	private array $pairs;
	/**
	 * @var int
	 */
	private $startTime;
	/**
	 * @var int
	 */
	private $endTime;
	/**
	 * GetPairRequest constructor.
	 * @param \App\Variables\QMUserVariable $cause
	 * @param \App\Variables\QMUserVariable $effect
	 * @param int|null $onsetDelay
	 * @param int|null $durationOfAction
	 */
	public function __construct(QMUserVariable $cause, QMUserVariable $effect, int $onsetDelay = null, int $durationOfAction = null){
		$this->setCauseQMUserVariable($cause);
		$this->setEffectQMUserVariable($effect);
		$this->onsetDelay = $onsetDelay ?? $this->causeQMUserVariable->onsetDelay;
		$this->durationOfAction = $durationOfAction ?? $this->causeQMUserVariable->durationOfAction;
	}
	/**
	 * @return string
	 */
	public function getCauseUnitAbbreviatedName(): string {
		if(isset($this->causeUnitAbbreviatedName)){return $this->causeUnitAbbreviatedName;}
		$causeMeasurements = $this->getCauseDailyMeasurements();
		$causeUnit = self::getUnitAbbreviatedNameFromMeasurements($causeMeasurements);
		return $this->causeUnitAbbreviatedName = $causeUnit;
	}
	/**
	 * @return string
	 */
	public function getEffectUnitAbbreviatedName(): string {
		if(isset($this->effectUnitAbbreviatedName)){return $this->effectUnitAbbreviatedName;}
		$effectMeasurements = $this->getEffectDailyMeasurements();
		$effectUnit = self::getUnitAbbreviatedNameFromMeasurements($effectMeasurements);
		return $this->effectUnitAbbreviatedName = $effectUnit;
	}
	/**
	 * Populate this request's properties from an Application instance.
	 * @param QMSlim $app The Application to parse parameters out of.
	 * @throws BadRequestException
	 */
	public function populate(QMSlim $app){
		$this->setApplication($app);
		$causeVariableName = $this->getParam('cause', null);
		if(!$causeVariableName){
			$causeVariableName = $this->getParamRequired('causeVariableName', 'Unknown cause variable');
		}
		$effectVariableName = $this->getParam('effect', null);
		if(!$effectVariableName){
			$effectVariableName = $this->getParamRequired('effectVariableName', 'Unknown cause variable');
		}
		$this->setCauseQMUserVariable($causeVariableName);
		$this->setEffectQMUserVariable($effectVariableName);
		$this->setOnsetDelay($this->getParam('delay', $this->getCauseQMUserVariable()->onsetDelay));
		$this->setDurationOfAction($this->getParam('duration', $this->getCauseQMUserVariable()->durationOfAction));
		$this->setStartTime($this->getParam('startTime', null));
		$this->setEndTime($this->getParam('endTime', null));
	}
	/**
	 * @param string|int|QMUserVariable $cause
	 * @return void
	 */
	private function setCauseQMUserVariable(QMUserVariable $cause): void{
		$this->causeQMUserVariable = $cause;
	}
	/**
	 * @param string|int|QMUserVariable $effect
	 * @return void
	 */
	private function setEffectQMUserVariable(QMUserVariable $effect): void{
		$this->effectQMUserVariable = $effect;
	}
	/**
	 * @param int $delay
	 * @return void
	 */
	public function setOnsetDelay(int $delay): void{
		$this->onsetDelay = $delay;
	}
	/**
	 * @param int $duration
	 * @return void
	 */
	public function setDurationOfAction(int $duration): void{
		$this->durationOfAction = $duration;
	}
	/**
	 * @param int $startTime
	 * @return void
	 */
	public function setStartTime(int $startTime): void{
		$this->startTime = $startTime;
	}
	/**
	 * @param int $endTime
	 * @return void
	 */
	public function setEndTime(int $endTime): void{
		$this->endTime = $endTime;
	}
	/**
	 * @return QMUserVariable
	 */
	public function getCauseQMUserVariable(): QMUserVariable{
		return $this->causeQMUserVariable;
	}
	/**
	 * @return QMUserVariable
	 */
	public function getEffectQMUserVariable(): QMUserVariable{
		return $this->effectQMUserVariable;
	}
	/**
	 * Create pairs for the causes and measurements arrays. The pairs are displayed on the scatter plot graph.
	 * Normally the pair (instance of Pair class) is created for every cause and every effect measurements.
	 * For every effect measurements we aggregate all possible causes, it means all causes which potentially might
	 * ve causes by the effect. Thy should have timestamp between cause timestamp + delay
	 * and cause timestamps + delay + duration. We use cause variable combination operation to aggregate values.
	 * If no cause found for the effect we use cause variable filling value if any instead
	 * of aggregated value. Finally if no causes are found for the effect and cause variable does not have
	 * filling value we don't create a pair for the effect.
	 * For every cause we measurement we aggregate all possible effects which might effect the cause.
	 * We find all effects with timestamp preceding the cause timestamp in the interval
	 * between cause timestamp - delay - duration and cause timestamp - delay. We use effect variable combination
	 * operation to aggregate values of the found effects. If no effect found for the cause we use effect filling
	 * variable value instead of aggregated value. Finally if no effects are found for the cause and effect variable
	 * does not have filling value we don't create a pair for the cause.
	 * Both cause and effect measurements are filtered, we check that they should have allowed values based
	 * on the cause and effect variables minimum and maximum allowed values.
	 * If we have less than Pair::MINIMUM_MEASUREMENTS values for effect or cause measurements after measurements are
	 * filtered we return empty array.
	 * @return Pair[]
	 */
	public function createAbsolutePairs(): array{
		$cause = $this->getCauseQMUserVariable();
		if($cause->hasFillingValue()){
			// If we have filling value, we need to create pairs using effect measurements as base or filling values
			// are not used for pairs
			return $this->createPairForEachEffectMeasurement();
		} else{
			return $this->createPairForEachCauseMeasurement();
		}
	}
	/**
	 * Create a pair for each raw effect measurement (that made it through the filter)
	 * Populate cause element of each pair with an aggregated cause value
	 * Test: Total pairs should the $numberOfEffectMeasurements
	 * @return Pair[]
	 */
	public function createPairForEachEffectMeasurement(): array{
		$pairs = [];
		$causeMeasurements = $this->getCauseDailyMeasurements();
		$effectMeasurements = $this->getEffectDailyMeasurements();
		$cause = $this->getCauseQMUserVariable();
		$earliestFillingAt = $cause->getEarliestFillingAt();
		$earliestFillingTime = strtotime($earliestFillingAt);
		$causeHasFillingValue = $cause->hasFillingValue();
		$causeFillingValue = $cause->getFillingValueAttribute();
		$delay = $this->getOnsetDelay();
		$duration = $this->getDurationOfAction();
		foreach($effectMeasurements as $em){
			if($em->value !== null){
				$groupEndTime = $em->startTime - $delay;
				$groupStartTime = $groupEndTime - $duration + 1;
				if($causeHasFillingValue && $groupEndTime < $earliestFillingTime){
					continue;
				}
				$avgCause = self::averageValues($cause, $causeMeasurements, $groupStartTime, $groupEndTime);
				if($avgCause !== null){
					$pair = new Pair($em->startTime, $avgCause, $em->value, $this);
				} elseif($causeHasFillingValue){
					$pair = new Pair($em->startTime, $causeFillingValue, $em->value, $this);
				} else{
					continue;
				}
				$pairs[$pair->startAt] = $pair;
			}
		}
		return $this->pairs = $pairs;
	}
	/**
	 * Create a pair for each cause measurement and populate effect value with grouped effectValues with
	 * $timestamp > ($pairTimestamp + $delay)  &&  $timestamp < ($pairTimestamp + $delay + $duration)
	 * or default filling value if null
	 * @return Pair[]
	 */
	public function createPairForEachCauseMeasurement(): array{
		$pairs = [];
		$e = $this->getEffectQMUserVariable();
		$latestFillingAt = $e->getLatestFillingAt();
		$latestFillingTime = strtotime($latestFillingAt);
		$effectHasFilling = $e->hasFillingValue();
		$onsetDelay = $this->getOnsetDelay();
		$durationOfAction = $this->getDurationOfAction();
		$causeMeasurements = $this->getCauseDailyMeasurements();
		$effectMeasurements = $this->getEffectDailyMeasurements();
		foreach($causeMeasurements as $cm){
			if($cm->value !== null){
				$groupStart = $cm->startTime + $onsetDelay;
				$groupEnd = $groupStart + $durationOfAction - 1;
				if($groupStart > $latestFillingTime && $effectHasFilling){continue;}
				if(empty($effectMeasurements)){continue;}
				$aggregatedValue = self::averageValues($e, $effectMeasurements,
					$groupStart, $groupEnd);
				if($aggregatedValue !== null){
					$pair = new Pair($cm->startTime, $cm->value, $aggregatedValue, $this);
				} elseif($effectHasFilling){
					$pair = new Pair($cm->startTime, $cm->value, $e->fillingValue, $this);
				} else{
					continue;
				}
				$pairs[$pair->startAt] = $pair;
			}
		}
		return $this->pairs = $pairs;
	}
	/**
	 * @param array $dailyMeasurements
	 * @param int $windowStartTime
	 * @param int $windowEndTime
	 * @return float[]
	 */
	private static function getMeasurementsInWindow(array $dailyMeasurements, int $windowStartTime,
		int $windowEndTime): array{
		$inWindow = [];
		foreach($dailyMeasurements as $m){
			if($m->startTime >= $windowStartTime){
				if($m->startTime <= $windowEndTime){
					$inWindow[$m->startAt] = $m->value;
				} else{
					// Break out early, no way later measurements are going to be within the specified bounds.
					break;
				}
			}
		}
		return $inWindow;
	}
	/**
	 * Filter measurements from the $measurements list by $startTime and $endTime and aggregate filtered measurements
	 * using variable combination operation. If no measurements returned when filter is applied use variable filling
	 * value if any exists.
	 * Return null if everything fails - no measurements in the time interval and $variable does not have filling value.
	 * @param QMUserVariable $v
	 * @param DailyMeasurement[] $dailyMeasurements
	 * @param int $windowStartTime
	 * @param int $windowEndTime
	 * @return double The aggregated value, the specified filling value, or null if neither.
	 */
	public static function averageValues(QMUserVariable $v, array $dailyMeasurements, int $windowStartTime,
		int $windowEndTime): ?float{
		$fillingValue = $v->getFillingValueAttribute();
		$inWindow = self::getMeasurementsInWindow($dailyMeasurements, $windowStartTime, $windowEndTime);
		if(!$inWindow){
			if($fillingValue !== null && $fillingValue != -1){
				return $fillingValue;
			}
			return null;
		}
		$avg = Stats::average($inWindow);
		if(Env::isTestingOrDevelopment()){
			$start = db_date($windowStartTime);
			$end = db_date($windowEndTime);
			try {
				$v->validateValueForCommonVariableAndUnit($avg, __METHOD__);
			} catch (InvalidVariableValueException $e) {
				le("We should only be creating pairs when we have enough valid values in the window. ".$e->getMessage());
			}
		}
		return $avg;
	}
	/**
	 * @return Pair[]
	 */
	public function createPairsFromDailyMeasurementsWithTagsAndFilling(): array{
		$cause = $this->getCauseQMUserVariable();
		$effect = $this->getEffectQMUserVariable();
		$this->setCauseDailyMeasurements($cause->getValidDailyMeasurementsWithTagsAndFilling());
		$this->setEffectDailyMeasurements($effect->getValidDailyMeasurementsWithTagsAndFilling());
		$allPairs = $this->createAbsolutePairs();
		return $allPairs;
	}
	/**
	 * @param $allPairs
	 * @param QMUserVariable $cause
	 * @param QMUserVariable $effect
	 */
	public function toCsv(): void{
		$cause = $this->getCauseQMUserVariable();
		$effect = $this->getEffectQMUserVariable();
		$header[] = 'Date';
		$header[] = $cause->name;
		$header[] = $effect->name;
		$filename = "$cause->name (" . $cause->getUserOrCommonUnit()->abbreviatedName . ") $effect->name (" .
			$effect->getUserOrCommonUnit()->abbreviatedName . ") Pairs from QuantiModo.txt";
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename="' . $filename . '";');
		// open raw memory as file so no temp files needed, you might run out of memory though
		$fp = fopen('php://output', 'wb');
		fputcsv($fp, $header, "\t");
		foreach($this->pairs as $pair){
			$csvArray = null;
			$csvArray[] = gmdate('Y-m-d', $pair->timestamp);
			$csvArray[] = $pair->causeMeasurementValue;
			$csvArray[] = $pair->effectMeasurementValue;
			fputcsv($fp, $csvArray, "\t");
		}
	}
	/**
	 * @return array
	 */
	public static function getLegacyPropertiesToReplace(): array{
		// legacy => current
		switch(APIHelper::getApiVersion()) {
			case 1:
			case 2:
			case 0:
				return [];
			default:
				return [
					'timestamp' => 'eventAtUnixTime',
					'startTimeString' => 'eventAt',
					'causeMeasurement' => 'causeMeasurementValue',
					'effectMeasurement' => 'effectMeasurementValue',
				];
		}
	}
	/**
	 * @param DailyMeasurement[] $measurements
	 * @return string
	 */
	public static function getUnitAbbreviatedNameFromMeasurements(array $measurements): string{
		$m = DailyMeasurement::getFirst($measurements);
		return $m->getUnitAbbreviatedName();
	}
	/**
	 * @return int
	 */
	public function getDurationOfAction(): ?int{
		return $this->durationOfAction;
	}
	/**
	 * @return int
	 */
	public function getOnsetDelay(): ?int{
		return $this->onsetDelay;
	}
	/**
	 * @return int
	 */
	public function getStartTime(): ?int{
		return $this->startTime;
	}
	/**
	 * @return int
	 */
	public function getEndTime(): ?int{
		return $this->endTime;
	}
	public function setCauseDailyMeasurements(array $causeDailyMeasurements){
		$this->causeDailyMeasurements = $causeDailyMeasurements;
	}
	public function setEffectDailyMeasurements(array $effectDailyMeasurements){
		$this->effectDailyMeasurements = $effectDailyMeasurements;
	}
	/**
	 * @return DailyMeasurement[]
	 */
	private function getCauseDailyMeasurements(): array{
		return $this->causeDailyMeasurements ?? $this->causeQMUserVariable->getValidDailyMeasurementsWithTags();
	}
	/**
	 * @return DailyMeasurement[]
	 */
	private function getEffectDailyMeasurements(): array{
		return $this->effectDailyMeasurements ?? $this->effectQMUserVariable->getValidDailyMeasurementsWithTags();
	}
}
