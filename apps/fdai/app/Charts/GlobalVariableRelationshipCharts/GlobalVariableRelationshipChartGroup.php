<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\GlobalVariableRelationshipCharts;
use App\Charts\ChartGroup;
use App\Charts\QMChart;
use App\VariableRelationships\QMGlobalVariableRelationship;
class GlobalVariableRelationshipChartGroup extends ChartGroup {
	public $populationTraitScatterPlot;
	/**
	 * PopulationStudyCharts constructor.
	 * @param QMGlobalVariableRelationship|null $aggregatedCorrelation
	 */
	public function __construct(QMGlobalVariableRelationship $aggregatedCorrelation = null){
		parent::__construct($aggregatedCorrelation);
	}
	/**
	 * @return PopulationTraitCorrelationScatterPlot|QMChart
	 */
	public function getPopulationTraitScatterPlot(){
		if(!$this->populationTraitScatterPlot){
			$this->setPopulationTraitScatterPlot();
		}
		$c = PopulationTraitCorrelationScatterPlot::instantiateIfNecessary($this->populationTraitScatterPlot);
		return $this->populationTraitScatterPlot = $c;
	}
	public function setPopulationTraitScatterPlot(){
		$c = $this->getSourceObject();
		$this->populationTraitScatterPlot = new PopulationTraitCorrelationScatterPlot($c);
	}
	/**
	 * @return bool|QMChart[]
	 */
	public function getChartsArray(): array{
		$charts = [];
		$charts[] = $this->getPopulationTraitScatterPlot();
		return $charts;
	}
}
