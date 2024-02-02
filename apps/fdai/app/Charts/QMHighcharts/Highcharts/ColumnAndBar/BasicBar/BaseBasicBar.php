<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\ColumnAndBar\BasicBar;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseCredits;
use App\Charts\QMHighcharts\Options\BaseLegend;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseSubtitle;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use App\Charts\QMHighcharts\Options\PlotOptions;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseBasicBar extends HighchartConfig {
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
	 * @var BaseLegend
	 * @link https://api.highcharts.com/highcharts/legend
	 */
	public $legend;
	/**
	 * @var BaseCredits
	 * @link https://api.highcharts.com/highcharts/credits
	 */
	public $credits;
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
		$this->tooltip = new BaseTooltip();
		$this->plotOptions = new PlotOptions();
		$this->legend = new BaseLegend();
		$this->credits = new BaseCredits();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->type = "bar";
		$this->title->text = "Historic World Population by Region";
		$this->subtitle->text = "Source: Wikipedia.org";
		$this->xAxis->categories = [
			'Africa',
			'America',
			'Asia',
			'Europe',
			'Oceania',
		];
		$this->xAxis->title->text = null;
		$this->yAxis->min = 0;
		$this->yAxis->title->text = "Population (millions)";
		$this->yAxis->title->align = "high";
		$this->tooltip->formatter = new HighchartJsExpr("function() {
		    return '' + this.series.name +': '+ this.y +' millions';}");
		$this->plotOptions->bar->dataLabels->enabled = 1;
		$this->legend->layout = "vertical";
		$this->legend->align = "right";
		$this->legend->verticalAlign = "top";
		$this->legend->x = -100;
		$this->legend->y = 100;
		$this->legend->floating = 1;
		$this->legend->borderWidth = 1;
		$this->legend->backgroundColor = "#FFFFFF";
		$this->legend->shadow = 1;
		$this->credits->enabled = false;
		$this->series[] = [
			'name' => "Year 1800",
			'data' => [
				107,
				31,
				635,
				203,
				2,
			],
		];
		$this->series[] = [
			'name' => "Year 1900",
			'data' => [
				133,
				156,
				947,
				408,
				6,
			],
		];
		$this->series[] = [
			'name' => "Year 2008",
			'data' => [
				973,
				914,
				4054,
				732,
				34,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/column_and_bar/basic_bar.php');
	}
}
