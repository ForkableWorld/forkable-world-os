<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\UserVariableCharts;
use App\Charts\ChartGroup;
use App\Charts\CorrelationCharts\CorrelationsNetworkGraphQMChart;
use App\Charts\CorrelationCharts\CorrelationsSankeyQMChart;
use App\Charts\DistributionColumnChart;
use App\Charts\MonthlyColumnChart;
use App\Charts\QMChart;
use App\Charts\WeekdayColumnChart;
use App\Charts\YearlyColumnChart;
use App\Models\UserVariableRelationship;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
/** Class UserVariableChartGroup
 * @package App\Charts
 */
class UserVariableChartGroup extends ChartGroup {
	public $lineChartWithSmoothing;
	public $distributionColumnChart;
	public $weekdayColumnChart;
	public $monthlyColumnChart;
	public $yearlyColumnChart;
	/**
	 * @var CorrelationsSankeyQMChart
	 */
	public $correlationsSankeyChart;
	public $correlationsNetworkGraphChart;
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
		$this->correlationsSankeyChart = new CorrelationsSankeyQMChart($variable, null, UserVariableRelationship::MAX_LIMIT);
		$this->correlationsNetworkGraphChart =
			new CorrelationsNetworkGraphQMChart($variable, null, UserVariableRelationship::MAX_LIMIT);
		$this->lineChartWithSmoothing = new MeasurementsLineChart($variable);
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
		$charts[] = $this->getLineChartWithSmoothing();
		$charts[] = $this->getDistributionColumnChart();
		$charts[] = $this->getWeekdayColumnChart();
		$charts[] = $this->getMonthlyColumnChart();
		$charts[] = $this->getYearlyColumnChart();
		$charts[] = $this->getCorrelationsSankeyChart();
		$charts[] = $this->getCorrelationsNetworkGraphChart();
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
	 * @return CorrelationsSankeyQMChart
	 */
	public function getCorrelationsSankeyChart(): CorrelationsSankeyQMChart{
		if($this->correlationsSankeyChart){
			$c = CorrelationsSankeyQMChart::instantiateIfNecessary($this->correlationsSankeyChart);
		} else{
			$c = new CorrelationsSankeyQMChart($this->getSourceObject());
		}
		return $this->correlationsSankeyChart = $c;
	}
	/**
	 * @return CorrelationsNetworkGraphQMChart
	 */
	public function getCorrelationsNetworkGraphChart(): CorrelationsNetworkGraphQMChart{
		if($this->correlationsNetworkGraphChart){
			$c = CorrelationsNetworkGraphQMChart::instantiateIfNecessary($this->correlationsNetworkGraphChart);
		} else{
			$c = new CorrelationsNetworkGraphQMChart($this->getSourceObject());
		}
		return $this->correlationsNetworkGraphChart = $c;
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
	/**
	 * @return MeasurementsLineChart
	 */
	public function getLineChartWithSmoothing(): MeasurementsLineChart{
		if($this->lineChartWithSmoothing){
			$c = MeasurementsLineChart::instantiateIfNecessary($this->lineChartWithSmoothing);
		} else{
			$c = new MeasurementsLineChart($this->getSourceObject());
		}
		return $this->lineChartWithSmoothing = $c;
	}
	public function getQMUserVariable(): QMUserVariable{
		return $this->getSourceObject();
	}
}
