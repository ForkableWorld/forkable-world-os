<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\CorrelationCharts;
use App\Charts\QMHighcharts\BaseHighstock;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\HighstockSeries;
use App\Charts\QMHighcharts\PairsOverTimeHighstock;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\InvalidSourceDataException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Logging\QMLog;
use App\Slim\Model\Measurement\Pair;
use App\Studies\QMUserStudy;
use App\Utils\AppMode;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
class PairsOverTimeLineChart extends CorrelationChart {
	public const DYNAMIC_LINE_COLOR = "Blue represents";
	public const EXPORT_LINE_COLOR = "Black represents";
	protected $pairs;
	/**
	 * @var array
	 */
	public $yAxis;
	/**
	 * @param QMUserVariableRelationship|QMUserStudy|null $c
	 */
	public function __construct($c = null){
		if(!$c){
			return;
		}
		try {
			$c = $c->getQMUserVariableRelationship();
		} catch (NotEnoughDataException $e) {
			return;
		}
		$this->sourceObject = $c; // Must be done first so getCauseName works
		$this->setExplanation(self::DYNAMIC_LINE_COLOR . " the " .
			strtolower($c->getOrSetCauseQMVariable()->getOrSetCombinationOperation()) . " of " . $this->getCauseName() .
			" over the previous " . $c->getDurationOfActionHumanString());
		try {
			parent::__construct($c, $this->getCauseName() . ' & ' . $this->getEffectName() . ' Lagged Over Time');
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			parent::__construct($c, $this->getCauseName() . ' & ' . $this->getEffectName() . ' Lagged Over Time');
		}
	}
	/**
	 * @return \stdClass|HighchartConfig
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 */
	public function getExportableConfig(): \stdClass{
		$config = parent::getExportableConfig();
		return $config;
	}
	/**
	 * @return string
	 */
	protected function getCauseCombinationOperation(): string{
		return $this->getCauseQMUserVariable()->getOrSetCombinationOperation();
	}
	/**
	 * @return string
	 */
	protected function getEffectCombinationOperation(): string{
		return $this->getEffectQMUserVariable()->getOrSetCombinationOperation();
	}
	/**
	 * @return BaseHighstock
	 * @throws InvalidSourceDataException
	 */
	public function generateHighchartConfig(): HighchartConfig{
		$c = $this->getCorrelation();
		$config = new PairsOverTimeHighstock($c, $this);
		$config->setTitle($this->getTitleAttribute());
		$config->setSubtitle($this->getSubtitleAttribute());
		$predictor = new HighstockSeries($this->getCauseCombinationOperation() . ' ' . $this->getCauseName(), $config,
			$this->getCauseUnitAbbreviatedName());
		$predictor->setColor($config->colors[0]);
		$outcome = new HighstockSeries($this->getEffectCombinationOperation() . ' ' . $this->getEffectName(), $config,
			$this->getEffectUnitAbbreviatedName());
		$outcome->setColor($config->colors[1]);
		$cause = $this->getCauseQMUserVariable();
		$minCause = $cause->getVariable()->minimum_allowed_value;
		foreach($this->getPairs() as $pair){
			if($minCause !== null && !$this->validCauseValue($pair, $minCause, $predictor, $cause)){
				le("Invalid cause value $pair->causeMeasurementValue $pair->causeUnitAbbreviatedName");
			}
			$predictor->addDataPoint([
				$pair->timestamp * 1000,
				$pair->causeMeasurementValue,
			]);
			$outcome->addDataPoint([
				$pair->timestamp * 1000,
				$pair->effectMeasurementValue,
			]);
		}
		$config->addSeriesAndYAxis($predictor);
		$outcome->setYAxisIndex(1);
		$config->setYAxes([$predictor->getYAxis(), $outcome->getYAxis()]);
		$config->addSeriesAndYAxis($outcome);
		$num = count($config->series);
		if($num !== 2){
			le("$num series on " . __CLASS__, $config);
		}
		$config->validate();
		return $this->setHighchartConfig($config);
	}
	/**
	 * @param Pair $pair
	 * @param float|null $minCause
	 * @param HighstockSeries $predictorSeries
	 * @param QMUserVariable $cause
	 * @return bool
	 * @throws InvalidSourceDataException
	 */
	private function validCauseValue(Pair $pair, float $minCause, HighstockSeries $predictorSeries,
		QMVariable $cause): bool{
		if($minCause !== null && $pair->causeMeasurementValue < $minCause){
			$url = $cause->getCleanupUrl();
			$message = "Cause $predictorSeries->name value $pair->causeMeasurementValue too small!
                   $url";
			if(AppMode::isUnitOrStagingUnitTest()){
				throw new InvalidSourceDataException($message, $cause->getUrl(), $cause);
			} else{
				QMLog::error($message);
			}
			return false;
		}
		return true;
	}
	public function getTitleAttribute(): string{
		$t = parent::getTitleAttribute();
		$this->validateTitle($t);
		return $t;
	}
	public function getHtml(): string{
		return parent::getHtml();
	}
	public function getShowContent(bool $inlineJs = false): string{
		return parent::getShowContent();
	}
}
