<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highstock\General\FiveTwoZeroZeroZeroPointsWithDataGrouping;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseRangeSelector;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseSubtitle;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseFiveTwoZeroZeroZeroPointsWithDataGrouping extends HighchartConfig {
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
	 * @var BaseTitle
	 * @link https://api.highcharts.com/highcharts/title
	 */
	public $title;
	/**
	 * @var BaseSubtitle
	 * @link https://api.highcharts.com/highcharts/subtitle
	 */
	public $subtitle;
	/**
	 * @var BaseSeries[]
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->rangeSelector = new BaseRangeSelector();
		$this->yAxis = new BaseYAxis();
		$this->title = new BaseTitle();
		$this->subtitle = new BaseSubtitle();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->events->load = new HighchartJsExpr("function(chart) {
		    this.setTitle(null, {
		        text: 'Built chart at '+ (new Date() - start) +'ms' }); }");
		$this->chart->zoomType = "x";
		$this->rangeSelector->buttons[] = [
			'type' => "day",
			'count' => 3,
			'text' => "3d",
		];
		$this->rangeSelector->buttons[] = [
			'type' => "week",
			'count' => 1,
			'text' => "1w",
		];
		$this->rangeSelector->buttons[] = [
			'type' => "month",
			'count' => 1,
			'text' => "1m",
		];
		$this->rangeSelector->buttons[] = [
			'type' => "month",
			'count' => 6,
			'text' => "6m",
		];
		$this->rangeSelector->buttons[] = [
			'type' => "year",
			'count' => 1,
			'text' => "1y",
		];
		$this->rangeSelector->buttons[] = [
			'type' => "all",
			'text' => "All",
		];
		$this->rangeSelector->selected = 3;
		$this->yAxis->title->text = "Temperature (°C)";
		$this->title->text = "Hourly temperatures in Vik i Sogn, Norway, 2004-2010";
		$this->subtitle->text = "Built chart at...";
		$this->series[] = [
			'name' => "Temperature",
			'data' => new HighchartJsExpr("data"),
			'pointStart' => new HighchartJsExpr("Date.UTC(2004, 3, 1)"),
			'pointInterval' => 3600 * 1000,
			'tooltip' => [
				'valueDecimals' => 1,
				'valueSuffix' => "°C",
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highstock/general/52000_points_with_data_grouping.php');
	}
}
