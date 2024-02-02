<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\ColumnAndBar\BasicColumn;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseLegend;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseSubtitle;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use App\Charts\QMHighcharts\Options\PlotOptions;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseBasicColumn extends HighchartConfig {
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
	 * @var BaseTooltip
	 * @link https://api.highcharts.com/highcharts/tooltip
	 */
	public $tooltip;
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
		$this->subtitle = new BaseSubtitle();
		$this->xAxis = new BaseXAxis();
		$this->yAxis = new BaseYAxis();
		$this->legend = new BaseLegend();
		$this->tooltip = new BaseTooltip();
		$this->plotOptions = new PlotOptions();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->type = "column";
		$this->title->text = "Monthly Average Rainfall";
		$this->subtitle->text = "Source: WorldClimate.com";
		$this->xAxis->categories = [
			"Jan",
			"Feb",
			"Mar",
			"Apr",
			"May",
			"Jun",
			"Jul",
			"Aug",
			"Sep",
			"Oct",
			"Nov",
			"Dec",
		];
		$this->yAxis->min = 0;
		$this->yAxis->title->text = "Rainfall (mm)";
		$this->legend->layout = "vertical";
		$this->legend->backgroundColor = "#FFFFFF";
		$this->legend->align = "left";
		$this->legend->verticalAlign = "top";
		$this->legend->x = 100;
		$this->legend->y = 70;
		$this->legend->floating = 1;
		$this->legend->shadow = 1;
		$this->tooltip->formatter = new HighchartJsExpr("function() {
		    return '' + this.x +': '+ this.y +' mm';}");
		$this->plotOptions->column->pointPadding = 0.2;
		$this->plotOptions->column->borderWidth = 0;
		$this->series[] = [
			'name' => "Tokyo",
			'data' => [
				49.9,
				71.5,
				106.4,
				129.2,
				144.0,
				176.0,
				135.6,
				148.5,
				216.4,
				194.1,
				95.6,
				54.4,
			],
		];
		$this->series[] = [
			'name' => "New York",
			'data' => [
				83.6,
				78.8,
				98.5,
				93.4,
				106.0,
				84.5,
				105.0,
				104.3,
				91.2,
				83.5,
				106.6,
				92.3,
			],
		];
		$this->series[] = [
			'name' => "London",
			'data' => [
				48.9,
				38.8,
				39.3,
				41.4,
				47.0,
				48.3,
				59.0,
				59.6,
				52.4,
				65.2,
				59.3,
				51.2,
			],
		];
		$this->series[] = [
			'name' => "Berlin",
			'data' => [
				42.4,
				33.2,
				34.5,
				39.7,
				52.6,
				75.5,
				57.4,
				60.4,
				47.6,
				39.1,
				46.8,
				51.1,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/column_and_bar/basic_column.php');
	}
}
