<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\ScatterAndBubble\ThreeDBubbles;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
class BaseThreeDBubbles extends HighchartConfig {
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
	 * @var BaseSeries[]
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->title = new BaseTitle();
		$this->xAxis = new BaseXAxis();
		$this->yAxis = new BaseYAxis();
		$this->series = [];
		$this->includeExtraScripts();
		$this->chart->renderTo = 'container';
		$this->chart->type = 'bubble';
		$this->chart->plotBorderWidth = 1;
		$this->chart->zoomType = 'xy';
		$this->title->text = 'Highcharts bubbles with radial gradient fill';
		$this->xAxis->gridLineWidth = 1;
		$this->yAxis->startOnTick = false;
		$this->yAxis->endOnTick = false;
		$this->series = [
			[
				'data' => [
					[9, 81, 63],
					[98, 5, 89],
					[51, 50, 73],
					[41, 22, 14],
					[58, 24, 20],
					[78, 37, 34],
					[55, 56, 53],
					[18, 45, 70],
					[42, 44, 28],
					[3, 52, 59],
					[31, 18, 97],
					[79, 91, 63],
					[93, 23, 23],
					[44, 83, 22],
				],
				'marker' => [
					'fillColor' => [
						'radialGradient' => [
							'cx' => 0.4,
							'cy' => 0.3,
							'r' => 0.7,
						],
						'stops' => [
							[0, 'rgba(255,255,255,0.5)'],
							[1, 'rgba(69,114,167,0.5)'],
						],
					],
				],
			],
			[
				'data' => [
					[42, 38, 20],
					[6, 18, 1],
					[1, 93, 55],
					[57, 2, 90],
					[80, 76, 22],
					[11, 74, 96],
					[88, 56, 10],
					[30, 47, 49],
					[57, 62, 98],
					[4, 16, 16],
					[46, 10, 11],
					[22, 87, 89],
					[57, 91, 82],
					[45, 15, 98],
				],
				'marker' => [
					'fillColor' => [
						'radialGradient' => [
							'cx' => 0.4,
							'cy' => 0.3,
							'r' => 0.7,
						],
						'stops' => [
							[0, 'rgba(255,255,255,0.5)'],
							[1, 'rgba(170,70,67,0.5)'],
						],
					],
				],
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/scatter_and_bubble/3d_bubbles.php');
	}
}
