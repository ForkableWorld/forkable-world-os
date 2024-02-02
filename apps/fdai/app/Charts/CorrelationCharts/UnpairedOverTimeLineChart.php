<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\CorrelationCharts;
use App\Charts\QMHighcharts\BaseHighstock;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\MultivariateHighstock;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Studies\QMUserStudy;
use App\Variables\QMUserVariable;
class UnpairedOverTimeLineChart extends PairsOverTimeLineChart {
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
		parent::__construct($c);
		$this->setExplanation("Raw measurements for the predictor and outcome variable as well as measurements from tagged, duplicate, or child variables.");
		$this->setTitleAndId("Raw " . $this->getCauseName() . ' & ' . $this->getEffectName() .
			' Measurements Over Time');
		$this->validate();
	}
	/**
	 * @return BaseHighstock
	 */
	public function generateHighchartConfig(): HighchartConfig{
		$cause = $this->getCauseQMUserVariable();
		$effect = $this->getEffectQMUserVariable();
		$config = new MultivariateHighstock([$cause->id, $effect->id], $this);
		$config->setTitle($this->getTitleAttribute());
		$config->setSubtitle($this->getSubtitleAttribute());
		$variables[] = $cause;
		$variables = array_merge($variables, $cause->getCommonAndUserTaggedVariables());
		$variables[] = $effect;
		$variables = array_merge($variables, $effect->getCommonAndUserTaggedVariables());
		/** @var QMUserVariable $v */
		foreach($variables as $v){
			$name = $v->name; // Don't use display name so we can differentiate
			$measurements = $v->getValidDailyMeasurementsWithTagsAndFilling();
			if($measurements){
				if(count($measurements) > 1000){ // Exporter can't handle any more
					$measurements = $v->getDailyMeasurementsWithoutTagsOrFilling();
					if(!$measurements){
						$measurements = $v->getValidDailyMeasurementsWithTags();
					}
				}
				$indexed = QMMeasurement::indexMeasurementsByStartAt($measurements);
				$config->addVariableSeries($v, $indexed, $name);
			} else{
				$this->logInfo("No $v measurements for " . __CLASS__);
			}
		}
		return $this->setHighchartConfig($config);
	}
	/**
	 * @return string
	 */
	public function getHtml(): string{
		return parent::getHtml();
	}
	/**
	 * @return string
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 */
	public function getShowContent(bool $inlineJs = false): string{
		return parent::getShowContent();
	}
}
