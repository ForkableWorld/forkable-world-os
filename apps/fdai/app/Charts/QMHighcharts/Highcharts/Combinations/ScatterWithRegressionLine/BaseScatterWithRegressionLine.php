<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Combinations\ScatterWithRegressionLine;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
class BaseScatterWithRegressionLine extends HighchartConfig {
	/**
	 * @var BaseChart
	 * @link https://api.highcharts.com/highcharts/chart
	 */
	public $chart;
	/**
	 * @var BaseXAxis
	 * @link https://api.highcharts.com/highcharts/xAxis
	 */
	public $xAxis;
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
	 * @var BaseSeries[]
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->xAxis = new BaseXAxis();
		$this->yAxis = new BaseYAxis();
		$this->title = new BaseTitle();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->xAxis->min = -0.5;
		$this->xAxis->max = 5.5;
		$this->yAxis->min = 0;
		$this->title->text = "Scatter plot with regression line";
		$this->series[] = [
			'type' => "line",
			'name' => "Regression Line",
			'data' => [
				[
					0,
					1.11,
				],
				[
					5,
					4.51,
				],
			],
			'marker' => [
				'enabled' => false,
			],
			'states' => [
				'hover' => [
					'lineWidth' => 0,
				],
			],
			'enableMouseTracking' => false,
		];
		$this->series[] = [
			'type' => "scatter",
			'name' => "Observations",
			'data' => [
				1,
				1.5,
				2.8,
				3.5,
				3.9,
				4.2,
			],
			'marker' => [
				'radius' => 4,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/combinations/scatter_with_regression_line.php');
	}
}
