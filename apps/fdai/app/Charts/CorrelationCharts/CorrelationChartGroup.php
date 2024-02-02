<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\CorrelationCharts;
use App\Charts\ChartGroup;
use App\Charts\QMChart;
use App\VariableRelationships\QMVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\NotEnoughDataException;
use App\Studies\QMUserStudy;
use App\Variables\QMVariable;
class CorrelationChartGroup extends ChartGroup {
	public static $REGENERATE_DELAY_DURATION_CHARTS = false;
	public $pairsOverTimeLineChart;
	public $unpairedOverTimeLineChart;
	public $correlationScatterPlot;
	/**
	 * @var PredictorDistributionColumnChart $predictorDistributionColumnChart
	 */
	public $predictorDistributionColumnChart;
	public $outcomeDistributionColumnChart;
	/**
	 * @var CorrelationsOverDurationsOfActionChart $predictorDistributionColumnChart
	 */
	public $correlationsOverDurationsOfActionLineChart;
	/**
	 * @var CorrelationsOverOnsetDelaysChart $predictorDistributionColumnChart
	 */
	public $correlationsOverOnsetDelaysLineChart;
	public const CHART_SPEARMAN = false;
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
		$this->setCorrelationScatterPlot();
		$this->setPairsOverTime();
		$this->setPredictorDistribution();
		$this->setOutcomeDistribution();
		$this->setCorrelationsOverDelaysChart();
		$this->setCorrelationsOverDurationsChart();
	}
	/**
	 * @return UserVariableRelationshipScatterPlot
	 */
	protected function setCorrelationScatterPlot(): UserVariableRelationshipScatterPlot{
		return $this->correlationScatterPlot = new UserVariableRelationshipScatterPlot($this->getSourceObject());
	}
	/**
	 * @return PairsOverTimeLineChart
	 */
	protected function setPairsOverTime(): PairsOverTimeLineChart{
		$chart = new PairsOverTimeLineChart($this->getSourceObject());
		return $this->pairsOverTimeLineChart = $chart;
	}
	/**
	 * @return PredictorDistributionColumnChart
	 */
	protected function setPredictorDistribution(): PredictorDistributionColumnChart{
		return $this->predictorDistributionColumnChart = new PredictorDistributionColumnChart($this->getSourceObject());
	}
	/**
	 * @return OutcomeDistributionColumnChart
	 */
	protected function setOutcomeDistribution(): OutcomeDistributionColumnChart{
		return $this->outcomeDistributionColumnChart = new OutcomeDistributionColumnChart($this->getSourceObject());
	}
	/**
	 * @return UserVariableRelationshipScatterPlot
	 */
	public function getCorrelationScatterPlot(): UserVariableRelationshipScatterPlot{
		if(!$this->correlationScatterPlot){
			$this->setCorrelationScatterPlot();
		}
		return $this->correlationScatterPlot =
			UserVariableRelationshipScatterPlot::instantiateIfNecessary($this->correlationScatterPlot);
	}
	/**
	 * @return CorrelationsOverOnsetDelaysChart
	 */
	public function setCorrelationsOverDelaysChart(): CorrelationsOverOnsetDelaysChart{
		return $this->correlationsOverOnsetDelaysLineChart =
			new CorrelationsOverOnsetDelaysChart($this->getSourceObject());
	}
	/**
	 * @return CorrelationsOverDurationsOfActionChart
	 */
	public function setCorrelationsOverDurationsChart(): CorrelationsOverDurationsOfActionChart{
		$chart = $this->correlationsOverDurationsOfActionLineChart =
			new CorrelationsOverDurationsOfActionChart($this->getSourceObject());
		return $chart;
	}
	/**
	 * @return CorrelationsOverOnsetDelaysChart
	 */
	public function getCorrelationsOverOnsetDelaysLineChart(): CorrelationsOverOnsetDelaysChart{
		if(!$this->correlationsOverOnsetDelaysLineChart){
			$this->setCorrelationsOverDelaysChart();
		}
		$c = CorrelationsOverOnsetDelaysChart::instantiateIfNecessary($this->correlationsOverOnsetDelaysLineChart);
		return $this->correlationsOverOnsetDelaysLineChart = $c;
	}
	/**
	 * @return CorrelationsOverDurationsOfActionChart
	 */
	public function getCorrelationsOverDurationsChart(): CorrelationsOverDurationsOfActionChart{
		if(!$this->correlationsOverDurationsOfActionLineChart){
			$this->setCorrelationsOverDurationsChart();
		}
		$c =
			CorrelationsOverDurationsOfActionChart::instantiateIfNecessary($this->correlationsOverDurationsOfActionLineChart);
		return $this->correlationsOverDurationsOfActionLineChart = $c;
	}
	/**
	 * @return QMChart[]
	 */
	public function getChartsArray(): array{
		$charts = [];
		$charts[] = $this->getUnpairedOverTimeLineChart();
		$charts[] = $this->getPredictorDistributionColumnChart();
		$charts[] = $this->getOutcomeDistributionColumnChart();
		$charts[] = $this->getPairsOverTimeLineChart();
		$charts[] = $this->getCorrelationScatterPlot();
		$charts[] = $this->getCorrelationsOverOnsetDelaysLineChart();
		$charts[] = $this->getCorrelationsOverDurationsChart();
		return $charts;
	}
	/**
	 * @return PairsOverTimeLineChart
	 */
	public function getPairsOverTimeLineChart(): PairsOverTimeLineChart{
		$before = $this->pairsOverTimeLineChart;
		if(!$before){
			$new = $this->setPairsOverTime();
			if($new->highchartConfig){
				$new->getHighchartConfig()->validate();
			}
			return $new;
		}
		if(get_class($before) === PairsOverTimeLineChart::class){
			return $before;
		}
		$instantiated = PairsOverTimeLineChart::instantiateIfNecessary($before);
		$instantiated->getHighchartConfig()->validate();
		return $this->pairsOverTimeLineChart = $instantiated;
	}
	/**
	 * @return PredictorDistributionColumnChart
	 */
	public function getPredictorDistributionColumnChart(): PredictorDistributionColumnChart{
		if(!$this->predictorDistributionColumnChart){
			$this->setPredictorDistribution();
		}
		$c = PredictorDistributionColumnChart::instantiateIfNecessary($this->predictorDistributionColumnChart);
		return $this->predictorDistributionColumnChart = $c;
	}
	/**
	 * @return OutcomeDistributionColumnChart
	 */
	public function getOutcomeDistributionColumnChart(): OutcomeDistributionColumnChart{
		if(!$this->outcomeDistributionColumnChart){
			$this->setOutcomeDistribution();
		}
		$c = OutcomeDistributionColumnChart::instantiateIfNecessary($this->outcomeDistributionColumnChart);
		return $this->outcomeDistributionColumnChart = $c;
	}
	/**
	 * @return UnpairedOverTimeLineChart
	 */
	public function getUnpairedOverTimeLineChart(): UnpairedOverTimeLineChart{
		$c = $before = $this->unpairedOverTimeLineChart;
		if(!$c){
			$c = $this->setUnpairedOverTime();
			$c->validate();
		}
		if(!$c instanceof UnpairedOverTimeLineChart){
			$c = UnpairedOverTimeLineChart::instantiateIfNecessary($c);
			$c->validate();
		}
		return $this->unpairedOverTimeLineChart = $c;
	}
	/**
	 * @return UnpairedOverTimeLineChart
	 */
	protected function setUnpairedOverTime(): UnpairedOverTimeLineChart{
		$chart = new UnpairedOverTimeLineChart($this->getSourceObject());
		$chart->validate();
		return $this->unpairedOverTimeLineChart = $chart;
	}
	/**
	 * @param array|object $arrayOrObject
	 * @return static
	 */
	public static function instantiateIfNecessary(array|object|string $arrayOrObject): self {
		return parent::instantiateIfNecessary($arrayOrObject);
	}
	protected const INCLUDE_VARIABLE_CHARTS = false; // Kind of slow
	public function getHtmlWithDynamicCharts(bool $includeJS): string{
		$html = "";
		$causeVariable = $this->getCauseVariable();
		$effectVariable = $this->getEffectVariable();
		$variableButtons = $causeVariable->getChartsButtonHtml().
		                   $effectVariable->getChartsButtonHtml();
		if(!static::INCLUDE_VARIABLE_CHARTS){$html .= $variableButtons;}
		$html .= parent::getHtmlWithDynamicCharts($includeJS);
		if(static::INCLUDE_VARIABLE_CHARTS){
			$html .= $causeVariable->getChartGroup()->getHtmlWithDynamicCharts($includeJS);
			$html .= $effectVariable->getChartGroup()->getHtmlWithDynamicCharts($includeJS);
		} else {
			$html .= $variableButtons;
		}
		return $html;
	}
	protected function getCauseVariable(): QMVariable{
		return $this->getCorrelation()->getCauseQMVariable();
	}
	protected function getEffectVariable(): QMVariable{
		return $this->getCorrelation()->getEffectQMVariable();
	}
	protected function getCorrelation(): QMVariableRelationship{
		return $this->sourceObject;
	}
	public static function generateNonTemporalCharts($c): string{
		return UserVariableRelationshipScatterPlot::generateInline($c) .
			CorrelationsOverDurationsOfActionChart::generateInline($c) .
			CorrelationsOverOnsetDelaysChart::generateInline($c) . OutcomeDistributionColumnChart::generateInline($c) .
			PredictorDistributionColumnChart::generateInline($c);
	}
}
