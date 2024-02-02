<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Line\TimeDataWithIrregularIntervals;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseSubtitle;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseTimeDataWithIrregularIntervals extends HighchartConfig {
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
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->type = "spline";
		$this->title->text = "Snow depth in the Vikjafjellet mountain, Norway";
		$this->subtitle->text = "An example of irregular time data in Highcharts JS";
		$this->xAxis->type = "datetime";
		$this->xAxis->dateTimeLabelFormats->month = "%e. %b";
		$this->xAxis->dateTimeLabelFormats->year = "%b";
		$this->yAxis->title->text = "Snow depth (m)";
		$this->yAxis->min = 0;
		$this->tooltip->formatter = new HighchartJsExpr("function() {
		        return '<b>'+ this.series.name +'</b><br/>'+
		        Highcharts.dateFormat('%e. %b', this.x) +': '+ this.y +' m';
		    }");
		$this->series[]->name = "Winter 2007-2008";
		$this->series[0]->data = [
			[
				new HighchartJsExpr("Date.UTC(1970,  9, 27)"),
				0,
			],
			[
				new HighchartJsExpr("Date.UTC(1970, 10, 10)"),
				0.6,
			],
			[
				new HighchartJsExpr("Date.UTC(1970, 10, 18)"),
				0.7,
			],
			[
				new HighchartJsExpr("Date.UTC(1970, 11, 2)"),
				0.8,
			],
			[
				new HighchartJsExpr("Date.UTC(1970, 11, 9)"),
				0.6,
			],
			[
				new HighchartJsExpr("Date.UTC(1970, 11, 16)"),
				0.6,
			],
			[
				new HighchartJsExpr("Date.UTC(1970, 11, 28)"),
				0.67,
			],
			[
				new HighchartJsExpr("Date.UTC(1971, 0, 1)"),
				0.81,
			],
			[
				new HighchartJsExpr("Date.UTC(1971, 0, 8)"),
				0.78,
			],
			[
				new HighchartJsExpr("Date.UTC(1971, 0, 12)"),
				0.98,
			],
			[
				new HighchartJsExpr("Date.UTC(1971, 0, 27)"),
				1.84,
			],
			[
				new HighchartJsExpr("Date.UTC(1971, 1, 10)"),
				1.80,
			],
			[
				new HighchartJsExpr("Date.UTC(1971, 1, 18)"),
				1.80,
			],
			[
				new HighchartJsExpr("Date.UTC(1971, 1, 24)"),
				1.92,
			],
			[
				new HighchartJsExpr("Date.UTC(1971, 2, 4)"),
				2.49,
			],
			[
				new HighchartJsExpr("Date.UTC(1971, 2, 11)"),
				2.79,
			],
			[
				new HighchartJsExpr("Date.UTC(1971, 2, 15)"),
				2.73,
			],
			[
				new HighchartJsExpr("Date.UTC(1971, 2, 25)"),
				2.61,
			],
			[
				new HighchartJsExpr("Date.UTC(1971, 3, 2)"),
				2.76,
			],
			[
				new HighchartJsExpr("Date.UTC(1971, 3, 6)"),
				2.82,
			],
			[
				new HighchartJsExpr("Date.UTC(1971, 3, 13)"),
				2.8,
			],
			[
				new HighchartJsExpr("Date.UTC(1971, 4, 3)"),
				2.1,
			],
			[
				new HighchartJsExpr("Date.UTC(1971, 4, 26)"),
				1.1,
			],
			[
				new HighchartJsExpr("Date.UTC(1971, 5, 9)"),
				0.25,
			],
			[
				new HighchartJsExpr("Date.UTC(1971, 5, 12)"),
				0,
			],
		];
		$this->series[]->name = "Winter 2008-2009";
		$this->series[1]->data = [
			[
				new HighchartJsExpr("Date.UTC(1970,  9, 18)"),
				0,
			],
			[
				new HighchartJsExpr("Date.UTC(1970,  9, 26)"),
				0.2,
			],
			[
				new HighchartJsExpr("Date.UTC(1970, 11,  1)"),
				0.47,
			],
			[
				new HighchartJsExpr("Date.UTC(1970, 11, 11)"),
				0.55,
			],
			[
				new HighchartJsExpr("Date.UTC(1970, 11, 25)"),
				1.38,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  0,  8)"),
				1.38,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  0, 15)"),
				1.38,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  1,  1)"),
				1.38,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  1,  8)"),
				1.48,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  1, 21)"),
				1.5,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  2, 12)"),
				1.89,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  2, 25)"),
				2.0,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  3,  4)"),
				1.94,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  3,  9)"),
				1.91,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  3, 13)"),
				1.75,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  3, 19)"),
				1.6,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  4, 25)"),
				0.6,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  4, 31)"),
				0.35,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  5,  7)"),
				0,
			],
		];
		$this->series[]->name = "Winter 2009-2010";
		$this->series[2]->data = [
			[
				new HighchartJsExpr("Date.UTC(1970,  9,  9)"),
				0,
			],
			[
				new HighchartJsExpr("Date.UTC(1970,  9, 14)"),
				0.15,
			],
			[
				new HighchartJsExpr("Date.UTC(1970, 10, 28)"),
				0.35,
			],
			[
				new HighchartJsExpr("Date.UTC(1970, 11, 12)"),
				0.46,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  0,  1)"),
				0.59,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  0, 24)"),
				0.58,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  1,  1)"),
				0.62,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  1,  7)"),
				0.65,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  1, 23)"),
				0.77,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  2,  8)"),
				0.77,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  2, 14)"),
				0.79,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  2, 24)"),
				0.86,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  3,  4)"),
				0.8,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  3, 18)"),
				0.94,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  3, 24)"),
				0.9,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  4, 16)"),
				0.39,
			],
			[
				new HighchartJsExpr("Date.UTC(1971,  4, 21)"),
				0,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/line/time_data_with_irregular_intervals.php');
	}
}
