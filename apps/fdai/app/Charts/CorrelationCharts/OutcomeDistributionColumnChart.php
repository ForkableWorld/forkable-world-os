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
use App\Exceptions\NotEnoughMeasurementsForCorrelationException;
use App\Studies\QMUserStudy;
use App\Types\QMStr;
use App\Utils\Stats;
class OutcomeDistributionColumnChart extends CorrelationChart {
	const SIGNIFICANT_FIGURES = 2; // Results in a maximum of 10 bins, I think?
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
		$this->setTitleAndId($title =
			'Average ' . $this->getCauseVariableDisplayName() . ' Preceding ' . $this->getEffectVariableDisplayName());
		$this->setExplanation("Typical " . $this->getCauseVariableDisplayName() . " seen over the previous " .
			$c->getDurationOfActionHumanString() . " preceding the given " . $this->getEffectVariableDisplayName() .
			" value.");
		parent::__construct($c, $title);
	}
	/**
	 * @return ColumnHighchartConfig
	 * @throws NotEnoughDataException
	 * @throws NotEnoughMeasurementsForCorrelationException
	 */
	public function generateHighchartConfig(): HighchartConfig{
		$config = new ColumnHighchartConfig(ColumnHighchartConfig::DEFAULT_MIN_MAX_BUFFER, $this);
		$config->setTitle($this->getTitleAttribute());
		$config->setSubtitle($this->getSubtitleAttribute());
		$causeNameUnit = $this->getCauseQMUserVariable()->getDisplayNameWithUserUnitInParenthesis();
		$effectNameUnit = $this->getEffectQMUserVariable()->getDisplayNameWithUserUnitInParenthesis();
		$config->addSeriesArray('Avg Daily ' . $this->getCauseVariableDisplayName() . ' Preceding ' .
			$this->getEffectVariableDisplayName(), $this->generateSeriesData());
		$config->setXAxisTitleText('Avg Daily ' . $effectNameUnit);
		$config->setYAxisTitle('Avg Daily ' . $causeNameUnit);
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
		$c = $this->getQMUserVariableRelationship();
		$pairs = $c->setPairsBasedOnDailyEffectValues();
		$causeValuesByEffectValue = [];
		foreach($pairs as $pair){
			$val = $pair->getEffectValueInUserUnit();
			$valueLabel = DistributionColumnChart::getDistributionValueLabel($val, self::SIGNIFICANT_FIGURES);
			$causeValuesByEffectValue[$valueLabel][] = $pair->getCauseValueInUserUnit();
		}
		$avgCauseValueByEffectValue = [];
		foreach($causeValuesByEffectValue as $effectValueLabel => $causeValues){
			//$label = $effectValueLabel." (".count($causeValues)." measurements)"; // TODO: figure out how to show this with datalabels
			$label = $effectValueLabel;
			$avgCauseValueByEffectValue[$label] = Stats::average($causeValues);
		}
		ksort($avgCauseValueByEffectValue);
		if(!$avgCauseValueByEffectValue){
			throw new NotEnoughDataException($c, "Not enough data to generate " . $this->getTitleAttribute(),
				$c->getDataQuantitySentence());
		}
		return $avgCauseValueByEffectValue;
	}
	/**
	 * @param ColumnHighchartConfig $config
	 */
	public function setTooltipOnConfig(ColumnHighchartConfig $config): void{
		$c = $this->getQMUserVariableRelationship();
		$cuv = $c->getCauseQMVariable();
		$euv = $c->getEffectQMVariable();
		$causeName = $cuv->getDisplayNameAttribute();
		$effectName = $euv->getDisplayNameAttribute();
		$duration = $c->getDurationOfActionHumanString();
		$causeUnit = $cuv->getUserUnit()->abbreviatedName;
		$effectUnit = $euv->getUserUnit()->abbreviatedName;
		$effectName = QMStr::escapeSingleQuotes($effectName);
		$causeName = QMStr::escapeSingleQuotes($causeName);
		$config->setTooltipFormatter("
            return this.y +'$causeUnit $causeName<br/>over $duration <br/>is typically followed by<br/>'+
            this.x+'$effectUnit $effectName';
        ");
	}
}
