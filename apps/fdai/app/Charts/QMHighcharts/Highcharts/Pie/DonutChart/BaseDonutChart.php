<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Pie\DonutChart;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use App\Charts\QMHighcharts\Options\PlotOptions;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseDonutChart extends HighchartConfig {
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
	 * @var BaseSeries[]
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->title = new BaseTitle();
		$this->yAxis = new BaseYAxis();
		$this->plotOptions = new PlotOptions();
		$this->tooltip = new BaseTooltip();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->type = "pie";
		$this->title->text = "Browser market share, April, 2011";
		$this->yAxis->title->text = "Total percent market share";
		$this->plotOptions->pie->shadow = false;
		$this->tooltip->formatter = new HighchartJsExpr("function() {
		    return '<b>'+ this.point.name +'</b>: '+ this.y +' %'; }");
		$this->series[] = [
			'name' => "Browsers",
			'data' => new HighchartJsExpr("browserData"),
			'size' => "60%",
			'dataLabels' => [
				'formatter' => new HighchartJsExpr("function() {
		    return this.y > 5 ? this.point.name : null; }"),
				'color' => 'white',
				'distance' => -30,
			],
		];
		$this->series[1]->name = "Versions";
		$this->series[1]->data = new HighchartJsExpr("versionsData");
		$this->series[1]->innerSize = "60%";
		$this->series[1]->dataLabels->formatter = new HighchartJsExpr("function() {
		    return this.y > 1 ? '<b>'+ this.point.name +':</b> '+ this.y +'%'  : null;}");
		// We can also use Highchart library to produce any kind of javascript
		// structures
		$thisData = new Highchart();
		$thisData[0]->y = 55.11;
		$thisData[0]->color = new HighchartJsExpr("colors[0]");
		$thisData[0]->drilldown->name = "MSIE versions";
		$thisData[0]->drilldown->categories = [
			'MSIE 6.0',
			'MSIE 7.0',
			'MSIE 8.0',
			'MSIE 9.0',
		];
		$thisData[0]->drilldown->data = [
			10.85,
			7.35,
			33.06,
			2.81,
		];
		$thisData[0]->drilldown->color = new HighchartJsExpr("colors[0]");
		$thisData[1]->y = 21.63;
		$thisData[1]->color = new HighchartJsExpr("colors[1]");
		$thisData[1]->drilldown->name = "Firefox versions";
		$thisData[1]->drilldown->categories = [
			'Firefox 2.0',
			'Firefox 3.0',
			'Firefox 3.5',
			'Firefox 3.6',
			'Firefox 4.0',
		];
		$thisData[1]->drilldown->data = [
			0.20,
			0.83,
			1.58,
			13.12,
			5.43,
		];
		$thisData[1]->drilldown->color = new HighchartJsExpr("colors[1]");
		$thisData[2]->y = 11.94;
		$thisData[2]->color = new HighchartJsExpr("colors[2]");
		$thisData[2]->drilldown->name = "Chrome versions";
		$thisData[2]->drilldown->categories = [
			'Chrome 5.0',
			'Chrome 6.0',
			'Chrome 7.0',
			'Chrome 8.0',
			'Chrome 9.0',
			'Chrome 10.0',
			'Chrome 11.0',
			'Chrome 12.0',
		];
		$thisData[2]->drilldown->data = [
			0.12,
			0.19,
			0.12,
			0.36,
			0.32,
			9.91,
			0.50,
			0.22,
		];
		$thisData[2]->drilldown->color = new HighchartJsExpr("colors[2]");
		$thisData[3]->y = 7.15;
		$thisData[3]->color = new HighchartJsExpr("colors[3]");
		$thisData[3]->drilldown->name = "Safari versions";
		$thisData[3]->drilldown->categories = [
			'Safari 5.0',
			'Safari 4.0',
			'Safari Win 5.0',
			'Safari 4.1',
			'Safari/Maxthon',
			'Safari 3.1',
			'Safari 4.1',
		];
		$thisData[3]->drilldown->data = [
			4.55,
			1.42,
			0.23,
			0.21,
			0.20,
			0.19,
			0.14,
		];
		$thisData[3]->drilldown->color = new HighchartJsExpr("colors[3]");
		$thisData[4]->y = 2.14;
		$thisData[4]->color = new HighchartJsExpr("colors[4]");
		$thisData[4]->drilldown->name = "Opera versions";
		$thisData[4]->drilldown->categories = [
			'Opera 9.x',
			'Opera 10.x',
			'Opera 11.x',
		];
		$thisData[4]->drilldown->data = [
			0.12,
			0.37,
			1.65,
		];
		$thisData[4]->drilldown->color = new HighchartJsExpr("colors[4]");
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/pie/donut_chart.php');
	}
}
