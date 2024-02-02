<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Area\AreaSpline;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseCredits;
use App\Charts\QMHighcharts\Options\BaseLegend;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use App\Charts\QMHighcharts\Options\PlotOptions;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseAreaSpline extends HighchartConfig {
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
	 * @var BaseCredits
	 * @link https://api.highcharts.com/highcharts/credits
	 */
	public $credits;
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
		$this->legend = new BaseLegend();
		$this->xAxis = new BaseXAxis();
		$this->yAxis = new BaseYAxis();
		$this->tooltip = new BaseTooltip();
		$this->credits = new BaseCredits();
		$this->plotOptions = new PlotOptions();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->type = "areaspline";
		$this->title->text = "Average fruit consumption during one week";
		$this->legend->layout = "vertical";
		$this->legend->align = "left";
		$this->legend->verticalAlign = "top";
		$this->legend->x = 150;
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
		$this->xAxis->plotBands = [
			'from' => 4.5,
			'to' => 6.5,
			'color' => "rgba(68, 170, 213, .2)",
		];
		$this->yAxis->title->text = "Fruit units";
		$this->tooltip->formatter = new HighchartJsExpr("function() { return ''+ this.x +': '+ this.y +' units'; }");
		$this->credits->enabled = true;
		$this->plotOptions->areaspline->fillOpacity = 0.5;
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
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/area/area_spline.php');
	}
}
