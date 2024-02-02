<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\MoreChartTypes\VuMeter;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BasePane;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use App\Charts\QMHighcharts\Options\PlotOptions;
class BaseVuMeter extends HighchartConfig {
	/**
	 * @var BaseChart
	 * @link https://api.highcharts.com/highcharts/chart
	 */
	public $chart;
	/**
	 * @var BaseTitle
	 * @link https://api.highcharts.com/highcharts/title
	 */
	public $title;
	/**
	 * @var BasePane[]
	 * @link https://api.highcharts.com/highcharts/pane
	 */
	public $pane;
	/**
	 * @var BaseYAxis[]
	 * @link https://api.highcharts.com/highcharts/yAxis
	 */
	public $yAxis;
	/**
	 * @var PlotOptions
	 * @link https://api.highcharts.com/highcharts/plotOptions
	 */
	public $plotOptions;
	/**
	 * @var BaseSeries[]
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->title = new BaseTitle();
		$this->pane = [];
		$this->yAxis = [];
		$this->plotOptions = new PlotOptions();
		$this->series = [];
		$this->includeExtraScripts();
		$this->chart = [
			'type' => 'gauge',
			'plotBorderWidth' => 1,
			'plotBackgroundColor' => [
				'linearGradient' => [
					'x1' => 0,
					'y1' => 0,
					'x2' => 0,
					'y2' => 1,
				],
				'stops' => [
					[0, '#FFF4C6'],
					[0.3, '#FFFFFF'],
					[1, '#FFF4C6'],
				],
			],
			'plotBackgroundImage' => null,
			'height' => 200,
		];
		$this->title->text = 'VU meter';
		$this->pane = [
			[
				'startAngle' => -45,
				'endAngle' => 45,
				'background' => null,
				'center' => ['25%', '145%'],
				'size' => 300,
			],
			[
				'startAngle' => -45,
				'endAngle' => 45,
				'background' => null,
				'center' => ['75%', '145%'],
				'size' => 300,
			],
		];
		$this->yAxis = [
			[
				'min' => -20,
				'max' => 6,
				'minorTickPosition' => 'outside',
				'tickPosition' => 'outside',
				'labels' => [
					'rotation' => 'auto',
					'distance' => 20,
				],
				'plotBands' => [
					[
						'from' => 0,
						'to' => 6,
						'color' => '#C02316',
						'innerRadius' => '100%',
						'outerRadius' => '105%',
					],
				],
				'pane' => 0,
				'title' => [
					'text' => 'VU<br/><span style="font-size:8px">Channel A</span>',
					'y' => -40,
				],
			],
			[
				'min' => -20,
				'max' => 6,
				'minorTickPosition' => 'outside',
				'tickPosition' => 'outside',
				'labels' => [
					'rotation' => 'auto',
					'distance' => 20,
				],
				'plotBands' => [
					[
						'from' => 0,
						'to' => 6,
						'color' => '#C02316',
						'innerRadius' => '100%',
						'outerRadius' => '105%',
					],
				],
				'pane' => 1,
				'title' => [
					'text' => 'VU<br/><span style="font-size:8px">Channel B</span>',
					'y' => -40,
				],
			],
		];
		$this->plotOptions->gauge->dataLabels->enabled = false;
		$this->plotOptions->gauge->dial->radius = '100%';
		$this->series = [
			[
				'data' => [-20],
				'yAxis' => 0,
			],
			[
				'data' => [-20],
				'yAxis' => 1,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/more_chart_types/vu_meter.php');
	}
}
