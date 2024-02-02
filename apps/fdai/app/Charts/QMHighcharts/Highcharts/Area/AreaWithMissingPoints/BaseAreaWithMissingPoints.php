<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Area\AreaWithMissingPoints;
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
class BaseAreaWithMissingPoints extends HighchartConfig {
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
		$this->legend = new BaseLegend();
		$this->xAxis = new BaseXAxis();
		$this->yAxis = new BaseYAxis();
		$this->tooltip = new BaseTooltip();
		$this->plotOptions = new PlotOptions();
		$this->credits = new BaseCredits();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->type = "area";
		$this->chart->spacingBottom = 30;
		$this->title->text = "Fruit consumption *";
		$this->subtitle->text = "* Jane's banana consumption is unknown";
		$this->subtitle->floating = true;
		$this->subtitle->align = "right";
		$this->subtitle->verticalAlign = "bottom";
		$this->subtitle->y = 15;
		$this->legend->layout = 'vertical';
		$this->legend->align = 'left';
		$this->legend->verticalAlign = 'top';
		$this->legend->x = 150;
		$this->legend->y = 100;
		$this->legend->floating = true;
		$this->legend->borderWidth = 1;
		$this->legend->backgroundColor = '#FFFFFF';
		$this->xAxis->categories = [
			'Apples',
			'Pears',
			'Oranges',
			'Bananas',
			'Grapes',
			'Plums',
			'Strawberries',
			'Raspberries',
		];
		$this->yAxis->title->text = "Y-Axis";
		$this->yAxis->labels->formatter = new HighchartJsExpr("function() { return this.value; }");
		$this->tooltip->formatter = new HighchartJsExpr("function() {
		                              return '<b>'+ this.series.name +'</b><br/>'+
		                              this.x +': '+ this.y; }");
		$this->plotOptions->area->fillOpacity = 0.5;
		$this->credits->enabled = false;
		$this->series[] = [
			'name' => 'John',
			'data' => [
				0,
				1,
				4,
				4,
				5,
				2,
				3,
				7,
			],
		];
		$this->series[] = [
			'name' => 'Jane',
			'data' => [
				1,
				0,
				3,
				null,
				3,
				1,
				2,
				1,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/area/area_with_missing_points.php');
	}
}
