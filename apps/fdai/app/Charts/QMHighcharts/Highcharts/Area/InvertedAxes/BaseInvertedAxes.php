<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Area\InvertedAxes;
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
class BaseInvertedAxes extends HighchartConfig {
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
	 * @var BaseLegend
	 * @link https://api.highcharts.com/highcharts/legend
	 */
	public $legend;
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
		$this->legend = new BaseLegend();
		$this->xAxis = new BaseXAxis();
		$this->yAxis = new BaseYAxis();
		$this->tooltip = new BaseTooltip();
		$this->plotOptions = new PlotOptions();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->type = "area";
		$this->chart->inverted = 1;
		$this->title->text = "Average fruit consumption during one week";
		$this->subtitle->style->position = "absolute";
		$this->subtitle->style->right = "0px";
		$this->subtitle->style->bottom = "10px";
		$this->legend->layout = "vertical";
		$this->legend->align = "right";
		$this->legend->verticalAlign = "top";
		$this->legend->x = -150;
		$this->legend->y = 100;
		$this->legend->floating = 1;
		$this->legend->borderWidth = 1;
		$this->legend->backgroundColor = "#FFFFFF";
		$this->xAxis->categories = [
			'Monday',
			'Tuesday',
			'Wednesday',
			'Thursday',
			'Friday',
			'Saturday',
			'Sunday',
		];
		$this->yAxis->title->text = "Number of units";
		$this->yAxis->labels->formatter = new HighchartJsExpr("function() { return this.value; }");
		$this->yAxis->min = 0;
		$this->tooltip->formatter = new HighchartJsExpr("function() { return ''+ this.x +': '+ this.y; }");
		$this->plotOptions->area->fillOpacity = 0.5;
		$this->series[] = [
			'name' => 'John',
			'data' => [
				3,
				4,
				3,
				5,
				4,
				10,
				12,
			],
		];
		$this->series[] = [
			'name' => 'Jane',
			'data' => [
				1,
				3,
				4,
				3,
				3,
				5,
				4,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/area/inverted_axes.php');
	}
}
