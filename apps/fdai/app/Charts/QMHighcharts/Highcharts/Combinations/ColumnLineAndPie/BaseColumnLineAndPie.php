<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Combinations\ColumnLineAndPie;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseLabels;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseColumnLineAndPie extends HighchartConfig {
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
	 * @var BaseTooltip
	 * @link https://api.highcharts.com/highcharts/tooltip
	 */
	public $tooltip;
	/**
	 * @var BaseLabels
	 * @link https://api.highcharts.com/highcharts/labels
	 */
	public $labels;
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
		$this->tooltip = new BaseTooltip();
		$this->labels = new BaseLabels();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->title->text = "Combination chart";
		$this->xAxis->categories = [
			'Apples',
			'Oranges',
			'Pears',
			'Bananas',
			'Plums',
		];
		$this->tooltip->formatter = new HighchartJsExpr("function() {
		    var s;
		    if (this.point.name) { // the pie chart
		        s = ''+
		        this.point.name +': '+ this.y +' fruits';
		    } else {
		        s = ''+
		        this.x  +': '+ this.y;
		    }
		    return s; }");
		$this->labels->items = [
			[
				'html' => "Total fruit consumption",
				'style' => [
					'left' => "40px",
					'top' => "8px",
					'color' => "black",
				],
			],
		];
		$this->series[] = [
			'type' => "column",
			'name' => "Jane",
			'data' => [
				3,
				2,
				1,
				3,
				4,
			],
		];
		$this->series[] = [
			'type' => "column",
			'name' => "John",
			'data' => [
				2,
				3,
				5,
				7,
				6,
			],
		];
		$this->series[] = [
			'type' => "column",
			'name' => "Joe",
			'data' => [
				4,
				3,
				3,
				9,
				0,
			],
		];
		$this->series[] = [
			'type' => "spline",
			'name' => "Average",
			'data' => [
				3,
				2.67,
				3,
				6.33,
				3.33,
			],
		];
		$this->series[] = [
			'type' => "pie",
			'name' => "Total consumption",
			'data' => [
				[
					'name' => "Jane",
					'y' => 13,
					'color' => "#4572A7",
				],
				[
					'name' => "John",
					'y' => 23,
					'color' => "#AA4643",
				],
				[
					'name' => "Joe",
					'y' => 19,
					'color' => "#89A54E",
				],
			],
			'center' => [
				100,
				80,
			],
			'size' => 100,
			'showInLegend' => false,
			'dataLabels' => [
				'enabled' => false,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/combinations/column_line_and_pie.php');
	}
}
