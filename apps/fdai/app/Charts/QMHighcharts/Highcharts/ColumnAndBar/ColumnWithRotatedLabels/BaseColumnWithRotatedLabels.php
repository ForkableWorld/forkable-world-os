<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\ColumnAndBar\ColumnWithRotatedLabels;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseLegend;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseColumnWithRotatedLabels extends HighchartConfig {
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
	 * @var BaseLegend
	 * @link https://api.highcharts.com/highcharts/legend
	 */
	public $legend;
	/**
	 * @var BaseTooltip
	 * @link https://api.highcharts.com/highcharts/tooltip
	 */
	public $tooltip;
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
		$this->legend = new BaseLegend();
		$this->tooltip = new BaseTooltip();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->type = "column";
		$this->chart->margin = [
			50,
			50,
			100,
			80,
		];
		$this->title->text = "World's largest cities per 2008";
		$this->xAxis->categories = [
			'Tokyo',
			'Jakarta',
			'New York',
			'Seoul',
			'Manila',
			'Mumbai',
			'Sao Paulo',
			'Mexico City',
			'Dehli',
			'Osaka',
			'Cairo',
			'Kolkata',
			'Los Angeles',
			'Shanghai',
			'Moscow',
			'Beijing',
			'Buenos Aires',
			'Guangzhou',
			'Shenzhen',
			'Istanbul',
		];
		$this->xAxis->labels->rotation = -45;
		$this->xAxis->labels->align = "right";
		$this->xAxis->labels->style->font = "normal 13px Verdana, sans-serif";
		$this->yAxis->min = 0;
		$this->yAxis->title->text = "Population (millions)";
		$this->legend->enabled = false;
		$this->tooltip->formatter = new HighchartJsExpr("function() {
		    return '<b>'+ this.x +'</b><br/>'+
		    'Population in 2008: '+ Highcharts.numberFormat(this.y, 1) +
		    ' millions';}");
		$this->series[] = [
			'name' => 'Population',
			'data' => [
				34.4,
				21.8,
				20.1,
				20,
				19.6,
				19.5,
				19.1,
				18.4,
				18,
				17.3,
				16.8,
				15,
				14.7,
				14.5,
				13.3,
				12.8,
				12.4,
				11.8,
				11.7,
				11.2,
			],
			'dataLabels' => [
				'enabled' => true,
				'rotation' => -90,
				'color' => '#FFFFFF',
				'align' => 'right',
				'x' => -3,
				'y' => 10,
				'formatter' => new HighchartJsExpr("function() {
		                                                  return this.y;}"),
				'style' => [
					'font' => 'normal 13px Verdana, sans-serif',
				],
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/column_and_bar/column_with_rotated_labels.php');
	}
}
