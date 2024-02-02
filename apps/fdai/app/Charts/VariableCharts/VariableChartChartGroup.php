<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\VariableCharts;
use App\Charts\GlobalVariableRelationshipCharts\GlobalVariableRelationshipsNetworkGraphQMChart;
use App\Charts\GlobalVariableRelationshipCharts\GlobalVariableRelationshipsSankeyQMChart;
use App\Charts\ChartGroup;
use App\Charts\DistributionColumnChart;
use App\Charts\MonthlyColumnChart;
use App\Charts\QMChart;
use App\Charts\WeekdayColumnChart;
use App\Charts\YearlyColumnChart;
use App\Models\UserVariableRelationship;
use App\Variables\QMVariable;
class VariableChartChartGroup extends ChartGroup {
	public $distributionColumnChart;
	public $weekdayColumnChart;
	public $monthlyColumnChart;
	public $yearlyColumnChart;
	/**
	 * @var GlobalVariableRelationshipsSankeyQMChart
	 */
	public $aggregateCorrelationsSankeyChart;
	public $aggregateCorrelationsNetworkGraphChart;
	/**
	 * CommonVariableCharts constructor.
	 * @param QMVariable|null $variable
	 */
	public function __construct($variable = null){
		if(!$variable){
			return;
		} // Probably instantiating Mongo document
		$this->sourceObject = $variable;
		$this->distributionColumnChart = new DistributionColumnChart($variable);
		$this->weekdayColumnChart = new WeekdayColumnChart($variable);
		$this->monthlyColumnChart = new MonthlyColumnChart($variable);
		$this->yearlyColumnChart = new YearlyColumnChart($variable);
		$this->aggregateCorrelationsSankeyChart =
			new GlobalVariableRelationshipsSankeyQMChart($variable, null, UserVariableRelationship::MAX_LIMIT);
		$this->aggregateCorrelationsNetworkGraphChart =
			new GlobalVariableRelationshipsNetworkGraphQMChart($variable, null, UserVariableRelationship::MAX_LIMIT);
		parent::__construct($variable);
	}
	public function unsetPrivateAndProtectedProperties(){
		foreach($this as $key => $value){
			/** @var QMChart $value */
			if($value){
				$value->unsetPrivateAndProtectedProperties();
			}
		}
	}
	/**
	 * @return bool|QMChart[]
	 */
	public function getChartsArray(): array{
		$charts = [];
		$charts[] = $this->getDistributionColumnChart();
		$charts[] = $this->getWeekdayColumnChart();
		$charts[] = $this->getMonthlyColumnChart();
		$charts[] = $this->getYearlyColumnChart();
		$charts[] = $this->getGlobalVariableRelationshipsSankeyChart();
		$charts[] = $this->getGlobalVariableRelationshipsNetworkGraphChart();
		return $charts;
	}
	/**
	 * @return DistributionColumnChart
	 */
	public function getDistributionColumnChart(): DistributionColumnChart{
		if($this->distributionColumnChart){
			$c = DistributionColumnChart::instantiateIfNecessary($this->distributionColumnChart);
		} else{
			$c = new DistributionColumnChart($this->getSourceObject());
		}
		return $this->distributionColumnChart = $c;
	}
	/**
	 * @return WeekdayColumnChart
	 */
	public function getWeekdayColumnChart(): WeekdayColumnChart{
		if($this->weekdayColumnChart){
			$c = WeekdayColumnChart::instantiateIfNecessary($this->weekdayColumnChart);
		} else{
			$c = new WeekdayColumnChart($this->getSourceObject());
		}
		return $this->weekdayColumnChart = $c;
	}
	/**
	 * @return GlobalVariableRelationshipsSankeyQMChart
	 */
	public function getGlobalVariableRelationshipsSankeyChart(): GlobalVariableRelationshipsSankeyQMChart{
		if($this->aggregateCorrelationsSankeyChart){
			$c = GlobalVariableRelationshipsSankeyQMChart::instantiateIfNecessary($this->aggregateCorrelationsSankeyChart);
		} else{
			$c = new GlobalVariableRelationshipsSankeyQMChart($this->getSourceObject());
		}
		return $this->aggregateCorrelationsSankeyChart = $c;
	}
	/**
	 * @return GlobalVariableRelationshipsNetworkGraphQMChart
	 */
	public function getGlobalVariableRelationshipsNetworkGraphChart(): GlobalVariableRelationshipsNetworkGraphQMChart{
		if($this->aggregateCorrelationsNetworkGraphChart){
			$c =
				GlobalVariableRelationshipsNetworkGraphQMChart::instantiateIfNecessary($this->aggregateCorrelationsNetworkGraphChart);
		} else{
			$c = new GlobalVariableRelationshipsNetworkGraphQMChart($this->getSourceObject());
		}
		return $this->aggregateCorrelationsNetworkGraphChart = $c;
	}
	/**
	 * @return MonthlyColumnChart
	 */
	public function getMonthlyColumnChart(): MonthlyColumnChart{
		if($this->monthlyColumnChart){
			$c = MonthlyColumnChart::instantiateIfNecessary($this->monthlyColumnChart);
		} else{
			$c = new MonthlyColumnChart($this->getSourceObject());
		}
		return $this->monthlyColumnChart = $c;
	}
	/**
	 * @return YearlyColumnChart
	 */
	public function getYearlyColumnChart(): YearlyColumnChart{
		if($this->yearlyColumnChart){
			$c = YearlyColumnChart::instantiateIfNecessary($this->yearlyColumnChart);
		} else{
			$c = new YearlyColumnChart($this->getSourceObject());
		}
		return $this->yearlyColumnChart = $c;
	}
	public function getHtmlWithDynamicCharts(bool $includeJS): string{
		return parent::getHtmlWithDynamicCharts($includeJS);
	}
	public static function generateDescriptiveCharts($v): string{
		return DistributionColumnChart::generateInline($v) . WeekdayColumnChart::generateInline($v) .
			MonthlyColumnChart::generateInline($v) . YearlyColumnChart::generateInline($v);
	}
}
