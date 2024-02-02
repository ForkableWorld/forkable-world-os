<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Area\PercentageArea;
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
class BasePercentageArea extends HighchartConfig {
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
		$this->title->text = "Historic and Estimated Worldwide Population Distribution by Region";
		$this->subtitle->text = "Source: Wikipedia.org";
		$this->xAxis->categories = [
			'1750',
			'1800',
			'1850',
			'1900',
			'1950',
			'1999',
			'2050',
		];
		$this->xAxis->tickmarkPlacement = "on";
		$this->xAxis->title->enabled = false;
		$this->yAxis->title->text = "Percent";
		$this->tooltip->formatter = new HighchartJsExpr("function() {
		        return ''+
		        this.x +': '+ Highcharts.numberFormat(this.percentage, 1) +'% ('+
		        Highcharts.numberFormat(this.y, 0, ',') +' millions)';
		    }");
		$this->plotOptions->area->stacking = "percent";
		$this->plotOptions->area->lineColor = "#ffffff";
		$this->plotOptions->area->lineWidth = 1;
		$this->plotOptions->area->marker->lineWidth = 1;
		$this->plotOptions->area->marker->lineColor = "#ffffff";
		$this->series[] = [
			'name' => "Asia",
			'data' => [
				502,
				635,
				809,
				947,
				1402,
				3634,
				5268,
			],
		];
		$this->series[] = [
			'name' => "Africa",
			'data' => [
				106,
				107,
				111,
				133,
				221,
				767,
				1766,
			],
		];
		$this->series[] = [
			'name' => "Europe",
			'data' => [
				163,
				203,
				276,
				408,
				547,
				729,
				628,
			],
		];
		$this->series[] = [
			'name' => "America",
			'data' => [
				18,
				31,
				54,
				156,
				339,
				818,
				1201,
			],
		];
		$this->series[] = [
			'name' => "Oceania",
			'data' => [
				2,
				2,
				2,
				6,
				13,
				30,
				46,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/area/percentage_area.php');
	}
}
