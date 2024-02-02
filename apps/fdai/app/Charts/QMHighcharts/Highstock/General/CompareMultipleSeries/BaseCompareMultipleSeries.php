<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highstock\General\CompareMultipleSeries;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseRangeSelector;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use App\Charts\QMHighcharts\Options\PlotOptions;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseCompareMultipleSeries extends HighchartConfig {
	/**
	 * @var BaseChart
	 * @link https://api.highcharts.com/highcharts/chart
	 */
	public $chart;
	/**
	 * @var BaseRangeSelector
	 * @link https://api.highcharts.com/highcharts/rangeSelector
	 */
	public $rangeSelector;
	/**
	 * @var BaseYAxis
	 * @link https://api.highcharts.com/highcharts/yAxis
	 */
	public $yAxis;
	/**
	 * @var PlotOptions
	 * @link https://api.highcharts.com/highcharts/plotOptions
	 */
	public $plotOptions;
	/**
	 * @var BaseTooltip
	 * @link https://api.highcharts.com/highcharts/tooltip
	 */
	public $tooltip;
	/**
	 * @var object
	 * @link https://api.highcharts.com/highcharts/baseCompareMultipleSeries.series
	 */
	public $series;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->rangeSelector = new BaseRangeSelector();
		$this->yAxis = new BaseYAxis();
		$this->plotOptions = new PlotOptions();
		$this->tooltip = new BaseTooltip();
		$this->chart->renderTo = "container";
		$this->rangeSelector->selected = 4;
		$this->yAxis->labels->formatter = new HighchartJsExpr("function() {
		    return (this.value > 0 ? '+' : '') + this.value + '%'; }");
		$this->yAxis->plotLines[] = [
			'value' => 0,
			'width' => 2,
			'color' => "silver",
		];
		$this->plotOptions->series->compare = "percent";
		$this->tooltip->pointFormat =
			"<span style=\"color:{series.color}\">{series.name}</span>: <b>{point.y}</b> ({point.change}%)<br/>";
		$this->tooltip->valueDecimals = 2;
		$this->series = new HighchartJsExpr("seriesOptions");
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highstock/general/compare_multiple_series.php');
	}
}
