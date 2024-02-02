<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\CorrelationCharts;
use App\Charts\DistributionColumnChart;
use App\Charts\QMHighcharts\ColumnHighchartConfig;
use App\Charts\QMHighcharts\HighchartConfig;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\NotEnoughDataException;
use App\Studies\QMUserStudy;
use App\Types\QMStr;
use App\Utils\Stats;
class PredictorDistributionColumnChart extends CorrelationChart {
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
		$this->getSubtitleAttribute();
		parent::__construct($c, $this->getTitleAttribute());
	}
	public function getTitleAttribute(): string{
		$this->setTitleAndId($t = 'Average ' . $this->getEffectVariableDisplayName() . ' by Previous ' .
			$this->getCauseVariableDisplayName());
		return $t;
	}
	public function getSubtitleAttribute(): string{
		$c = $this->getQMUserVariableRelationship();
		$str = "Typical values for " . $this->getEffectVariableDisplayName() . " following a given amount of " .
			$this->getCauseVariableDisplayName() . " over the previous " . $c->getDurationOfActionHumanString() . ". ";
		$this->setExplanation($str);
		return $str;
	}
	/**
	 * @return ColumnHighchartConfig
	 * @throws NotEnoughDataException
	 */
	public function generateHighchartConfig(): HighchartConfig{
		$config = new ColumnHighchartConfig(ColumnHighchartConfig::DEFAULT_MIN_MAX_BUFFER, $this);
		$config->setTitle($this->getTitleAttribute());
		$config->setSubtitle($this->getSubtitleAttribute());
		$c = $this->getQMUserVariableRelationship();
		$cuv = $c->getCauseQMVariable();
		$euv = $c->getEffectQMVariable();
		$config->addSeriesArray('Average ' . $this->getEffectVariableDisplayName() . ' Following ' .
			$this->getCauseVariableDisplayName(), $this->generateSeriesData());
		$causeNameUnit = $cuv->getDisplayNameWithUserUnitInParenthesis();
		$effectNameUnit = $euv->getDisplayNameWithUserUnitInParenthesis();
		$config->setXAxisTitleText('Average Daily ' . $causeNameUnit);
		$config->setYAxisTitle('Average Daily ' . $effectNameUnit);
		$this->setTooltipOnConfig($config);
		return $this->setHighchartConfig($config);
	}
	private function getQMUserVariableRelationship(): QMUserVariableRelationship{
		return $this->sourceObject;
	}
	/**
	 * @return array
	 * @throws NotEnoughDataException
	 */
	protected function generateSeriesData(): array{
		$correlation = $this->getQMUserVariableRelationship();
		$pairs = $correlation->getPairsBasedOnDailyCauseValues();
		$effectValuesByCauseValue = [];
		foreach($pairs as $pair){
			$val = $pair->getCauseValueInUserUnit();
			$valueLabel = DistributionColumnChart::getDistributionValueLabel($val,
				OutcomeDistributionColumnChart::SIGNIFICANT_FIGURES);
			$effectValuesByCauseValue[$valueLabel][] = $pair->getEffectValueInUserUnit();
		}
		$avgEffectByCauseValue = [];
		foreach($effectValuesByCauseValue as $causeValueLabel => $effectValues){
			// $label = $causeValueLabel." (".count($effectValues)." measurements)"; // TODO: figure out how to show this with datalabels
			$label = $causeValueLabel;
			$avgEffectByCauseValue[$label] = Stats::average($effectValues);
		}
		if(!$avgEffectByCauseValue){
			throw new NotEnoughDataException($this->sourceObject, $this->getTitleAttribute(),
				"There is no average effect for each daily cause value available to create " . $this->getTitleAttribute() .
				" chart. ");
		}
		ksort($avgEffectByCauseValue);
		return $avgEffectByCauseValue;
	}
	/**
	 * @param ColumnHighchartConfig $config
	 */
	private function setTooltipOnConfig(ColumnHighchartConfig $config): void{
		$c = $this->getQMUserVariableRelationship();
		$cuv = $c->getCauseQMVariable();
		$euv = $c->getEffectQMVariable();
		$causeName = $cuv->getDisplayNameAttribute();
		$effectName = $euv->getDisplayNameAttribute();
		$duration = $c->getDurationOfActionHumanString();
		$delay = $c->getOnsetDelayHumanString();
		$causeUnit = $cuv->getUserUnit()->abbreviatedName;
		$effectUnit = $euv->getUserUnit()->abbreviatedName;
		$effectName = QMStr::escapeSingleQuotes($effectName);
		$causeName = QMStr::escapeSingleQuotes($causeName);
		$config->setTooltipFormatter("
            return this.y +'$effectUnit $effectName <br/> is typically observed after<br/>'+
            this.x+'$causeUnit $causeName<br/>over the previous<br/>$duration following an onset delay of $delay.';
        ");
	}
	public function getDynamicHtml(bool $includeJS = true): string{
		return parent::getDynamicHtml($includeJS);
	}
}
