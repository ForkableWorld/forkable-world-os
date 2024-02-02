<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Line\BasicLine;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseLegend;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseSubtitle;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseBasicLine extends HighchartConfig {
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
	 * @var BaseSubtitle
	 * @link https://api.highcharts.com/highcharts/subtitle
	 */
	public $subtitle;
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
	 * @var BaseLegend
	 * @link https://api.highcharts.com/highcharts/legend
	 */
	public $legend;
	/**
	 * @var BaseSeries[]
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	/**
	 * @var BaseTooltip
	 * @link https://api.highcharts.com/highcharts/tooltip
	 */
	public $tooltip;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->title = new BaseTitle();
		$this->subtitle = new BaseSubtitle();
		$this->xAxis = new BaseXAxis();
		$this->yAxis = new BaseYAxis();
		$this->legend = new BaseLegend();
		$this->series = [];
		$this->tooltip = new BaseTooltip();
		$this->chart = [
			'renderTo' => 'container',
			'type' => 'line',
			'marginRight' => 130,
			'marginBottom' => 25,
		];
		$this->title = [
			'text' => 'Monthly Average Temperature',
			'x' => -20,
		];
		$this->subtitle = [
			'text' => 'Source: WorldClimate.com',
			'x' => -20,
		];
		$this->xAxis->categories = [
			'Jan',
			'Feb',
			'Mar',
			'Apr',
			'May',
			'Jun',
			'Jul',
			'Aug',
			'Sep',
			'Oct',
			'Nov',
			'Dec',
		];
		$this->yAxis = [
			'title' => [
				'text' => 'Temperature (°C)',
			],
			'plotLines' => [
				[
					'value' => 0,
					'width' => 1,
					'color' => '#808080',
				],
			],
		];
		$this->legend = [
			'layout' => 'vertical',
			'align' => 'right',
			'verticalAlign' => 'top',
			'x' => -10,
			'y' => 100,
			'borderWidth' => 0,
		];
		$this->series[] = [
			'name' => 'Tokyo',
			'data' => [
				7.0,
				6.9,
				9.5,
				14.5,
				18.2,
				21.5,
				25.2,
				26.5,
				23.3,
				18.3,
				13.9,
				9.6,
			],
		];
		$this->series[] = [
			'name' => 'New York',
			'data' => [
				-0.2,
				0.8,
				5.7,
				11.3,
				17.0,
				22.0,
				24.8,
				24.1,
				20.1,
				14.1,
				8.6,
				2.5,
			],
		];
		$this->series[] = [
			'name' => 'Berlin',
			'data' => [
				-0.9,
				0.6,
				3.5,
				8.4,
				13.5,
				17.0,
				18.6,
				17.9,
				14.3,
				9.0,
				3.9,
				1.0,
			],
		];
		$this->series[] = [
			'name' => 'London',
			'data' => [
				3.9,
				4.2,
				5.7,
				8.5,
				11.9,
				15.2,
				17.0,
				16.6,
				14.2,
				10.3,
				6.6,
				4.8,
			],
		];
		$this->tooltip->formatter =
			new HighchartJsExpr("function() { return '<b>'+ this.series.name +'</b><br/>'+ this.x +': '+ this.y +'°C';}");
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/line/basic_line.php');
	}
}
