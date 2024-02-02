<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Area\BasicArea;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseSubtitle;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use App\Charts\QMHighcharts\Options\PlotOptions;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseBasicArea extends HighchartConfig {
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
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->type = "area";
		$this->title->text = "US and USSR nuclear stockpiles";
		$this->subtitle->text =
			"Source: <a href=\"http://thebulletin.metapress.com/content/c4120650912x74k7/fulltext.pdf\">thebulletin.metapress.com</a>";
		$this->xAxis->labels->formatter = new HighchartJsExpr("function() { return this.value;}");
		$this->yAxis->title->text = "Nuclear weapon states";
		$this->yAxis->labels->formatter = new HighchartJsExpr("function() { return this.value / 1000 +'k';}");
		$this->tooltip->formatter = new HighchartJsExpr("function() {
		                              return this.series.name +' produced <b>'+
		                              Highcharts.numberFormat(this.y, 0) +'</b><br/>warheads in '+ this.x;}");
		$this->plotOptions->area->pointStart = 1940;
		$this->plotOptions->area->marker->enabled = false;
		$this->plotOptions->area->marker->symbol = "circle";
		$this->plotOptions->area->marker->radius = 2;
		$this->plotOptions->area->marker->states->hover->enabled = true;
		$this->series[] = [
			'name' => 'USA',
			'data' => [
				null,
				null,
				null,
				null,
				null,
				6,
				11,
				32,
				110,
				235,
				369,
				640,
				1005,
				1436,
				2063,
				3057,
				4618,
				6444,
				9822,
				15468,
				20434,
				24126,
				27387,
				29459,
				31056,
				31982,
				32040,
				31233,
				29224,
				27342,
				26662,
				26956,
				27912,
				28999,
				28965,
				27826,
				25579,
				25722,
				24826,
				24605,
				24304,
				23464,
				23708,
				24099,
				24357,
				24237,
				24401,
				24344,
				23586,
				22380,
				21004,
				17287,
				14747,
				13076,
				12555,
				12144,
				11009,
				10950,
				10871,
				10824,
				10577,
				10527,
				10475,
				10421,
				10358,
				10295,
				10104,
			],
		];
		$this->series[] = [
			'name' => 'USSR/Russia',
			'data' => [
				null,
				null,
				null,
				null,
				null,
				null,
				null,
				null,
				null,
				null,
				5,
				25,
				50,
				120,
				150,
				200,
				426,
				660,
				869,
				1060,
				1605,
				2471,
				3322,
				4238,
				5221,
				6129,
				7089,
				8339,
				9399,
				10538,
				11643,
				13092,
				14478,
				15915,
				17385,
				19055,
				21205,
				23044,
				25393,
				27935,
				30062,
				32049,
				33952,
				35804,
				37431,
				39197,
				45000,
				43000,
				41000,
				39000,
				37000,
				35000,
				33000,
				31000,
				29000,
				27000,
				25000,
				24000,
				23000,
				22000,
				21000,
				20000,
				19000,
				18000,
				18000,
				17000,
				16000,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/area/basic_area.php');
	}
}
