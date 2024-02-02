<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Charts\QMHighcharts\Highcharts\ColumnAndBar\StackedColumn;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseLegend;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use App\Charts\QMHighcharts\Options\PlotOptions;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseStackedColumn extends HighchartConfig {
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
		$this->xAxis = new BaseXAxis();
		$this->yAxis = new BaseYAxis();
		$this->legend = new BaseLegend();
		$this->tooltip = new BaseTooltip();
		$this->plotOptions = new PlotOptions();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->type = "column";
		$this->title->text = "Stacked column chart";
		$this->xAxis->categories = [
			'Apples',
			'Oranges',
			'Pears',
			'Grapes',
			'Bananas',
		];
		$this->yAxis->min = 0;
		$this->yAxis->title->text = "Total fruit consumption";
		$this->yAxis->stackLabels->enabled = 1;
		$this->yAxis->stackLabels->style->fontWeight = "bold";
		$this->yAxis->stackLabels->style->color =
			new HighchartJsExpr("(Highcharts.theme && Highcharts.theme.textColor) || 'gray'");
		$this->legend->align = "right";
		$this->legend->x = -100;
		$this->legend->verticalAlign = "top";
		$this->legend->y = 20;
		$this->legend->floating = 1;
		$this->legend->backgroundColor =
			new HighchartJsExpr("(Highcharts.theme && Highcharts.theme.legendBackgroundColorSolid) || 'white'");
		$this->legend->borderColor = "#CCC";
		$this->legend->borderWidth = 1;
		$this->legend->shadow = false;
		$this->tooltip->formatter = new HighchartJsExpr("function() {
		    return '<b>'+ this.x +'</b><br/>'+
		    this.series.name +': '+ this.y +'<br/>'+
		    'Total: '+ this.point.stackTotal;}");
		$this->plotOptions->column->stacking = "normal";
		$this->plotOptions->column->dataLabels->enabled = 1;
		$this->plotOptions->column->dataLabels->color =
			new HighchartJsExpr("(Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'");
		$this->series[] = [
			'name' => "John",
			'data' => [
				5,
				3,
				4,
				7,
				2,
			],
		];
		$this->series[] = [
			'name' => "Jane",
			'data' => [
				2,
				2,
				3,
				2,
				1,
			],
		];
		$this->series[] = [
			'name' => "Joe",
			'data' => [
				3,
				4,
				4,
				2,
				5,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/column_and_bar/stacked_column.php');
	}
}
