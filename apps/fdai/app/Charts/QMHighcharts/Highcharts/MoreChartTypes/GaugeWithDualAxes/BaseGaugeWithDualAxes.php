<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\MoreChartTypes\GaugeWithDualAxes;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BasePane;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseGaugeWithDualAxes extends HighchartConfig {
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
	 * @var BaseYAxis[]
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
		$this->yAxis = [];
		$this->series = [];
		$this->includeExtraScripts();
		$this->chart = [
			'type' => 'gauge',
			'alignTicks' => false,
			'plotBackgroundColor' => null,
			'plotBackgroundImage' => null,
			'plotBorderWidth' => 0,
			'plotShadow' => false,
		];
		$this->title->text = 'Speedometer with dual axes';
		$this->pane->startAngle = -150;
		$this->pane->endAngle = 150;
		$this->yAxis = [
			[
				'min' => 0,
				'max' => 200,
				'lineColor' => '#339',
				'tickColor' => '#339',
				'minorTickColor' => '#339',
				'offset' => -25,
				'lineWidth' => 2,
				'labels' => [
					'distance' => -20,
					'rotation' => 'auto',
				],
				'tickLength' => 5,
				'minorTickLength' => 5,
				'endOnTick' => 'false',
			],
			[
				'min' => 0,
				'max' => 124,
				'tickPosition' => 'outside',
				'lineColor' => '#933',
				'lineWidth' => 2,
				'minorTickPosition' => 'outside',
				'tickColor' => '#933',
				'minorTickColor' => '#933',
				'tickLength' => 5,
				'minorTickLength' => 5,
				'labels' => [
					'distance' => 12,
					'rotation' => 'auto',
				],
				'offset' => -20,
				'endOnTick' => 'false',
			],
		];
		$this->series[] = [
			'name' => 'Speed',
			'data' => [80],
			'dataLabels' => [
				'formatter' => new HighchartJsExpr("function () {
		            var kmh = this.y,
		                mph = Math.round(kmh * 0.621);
		            return '<span style=\"color:#339\">'+ kmh + ' km/h</span><br/>' +
		                '<span style=\"color:#933\">' + mph + ' mph</span>'; }"),
				'backgroundColor' => [
					'linearGradient' => [
						'x1' => 0,
						'y1' => 0,
						'x2' => 0,
						'y2' => 1,
					],
					'stops' => [
						[0, '#DDD'],
						[1, '#FFF'],
					],
				],
			],
			'tooltip' => [
				'valueSuffix' => 'km/h',
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/more_chart_types/gauge_with_dual_axes.php');
	}
}
