<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\MoreChartTypes\AngularGauge;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseBackground;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BasePane;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseYAxis;
class BaseAngularGauge extends HighchartConfig {
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
	 * @var BasePane
	 * @link https://api.highcharts.com/highcharts/pane
	 */
	public $pane;
	/**
	 * @var BaseBackground[]
	 * @link https://api.highcharts.com/highcharts/background
	 */
	public $background;
	/**
	 * @var BaseYAxis
	 * @link https://api.highcharts.com/highcharts/yAxis
	 */
	public $yAxis;
	/**
	 * @var BaseSeries[]
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->title = new BaseTitle();
		$this->pane = new BasePane();
		$this->background = [];
		$this->yAxis = new BaseYAxis();
		$this->series = [];
		$this->includeExtraScripts();
		$this->chart = [
			'type' => 'gauge',
			'plotBackgroundColor' => null,
			'plotBackgroundImage' => null,
			'plotBorderWidth' => 0,
			'plotShadow' => false,
		];
		$this->title->text = 'Speedometer';
		$this->pane->startAngle = -150;
		$this->pane->endAngle = 150;
		$this->background = [
			[
				'backgroundColor' => [
					'linearGradient' => [
						'x1' => 0,
						'y1' => 0,
						'x2' => 0,
						'y2' => 1,
					],
					'stops' => [
						[0, '#FFF'],
						[1, '#333'],
					],
				],
				'borderWidth' => 0,
				'outerRadius' => '109%',
			],
			[
				'backgroundColor' => [
					'linearGradient' => [
						'x1' => 0,
						'y1' => 0,
						'x2' => 0,
						'y2' => 1,
					],
					'stops' => [
						[0, '#333'],
						[1, '#FFF'],
					],
				],
				'borderWidth' => 1,
				'outerRadius' => '107%',
			],
			[
				'backgroundColor' => '#DDD',
				'borderWidth' => 0,
				'outerRadius' => '105%',
				'innerRadius' => '103%',
			],
		];
		$this->yAxis = [
			'min' => 0,
			'max' => 200,
			'minorTickInterval' => 'auto',
			'minorTickWidth' => 1,
			'minorTickLength' => 10,
			'minorTickPosition' => 'inside',
			'minorTickColor' => '#666',
			'tickPixelInterval' => 30,
			'tickWidth' => 2,
			'tickPosition' => 'inside',
			'tickLength' => 10,
			'tickColor' => '#666',
			'labels' => [
				'step' => 2,
				'rotation' => 'auto',
			],
			'title' => [
				'text' => 'km/h',
			],
			'plotBands' => [
				[
					'from' => 0,
					'to' => 120,
					'color' => '#55BF3B',
				],
				[
					'from' => 120,
					'to' => 160,
					'color' => '#DDDF0D',
				],
				[
					'from' => 160,
					'to' => 200,
					'color' => '#DF5353',
				],
			],
		];
		$this->series[] = [
			'name' => 'Speed',
			'data' => [80],
			'tooltip' => [
				'valueSuffix' => 'km/h',
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/more_chart_types/angular_gauge.php');
	}
}
