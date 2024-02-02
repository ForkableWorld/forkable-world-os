<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\ColumnAndBar\BarWithNegativeStack;
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
class BaseBarWithNegativeStack extends HighchartConfig {
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
	 * @var BaseXAxis[]
	 * @link https://api.highcharts.com/highcharts/xAxis
	 */
	public $xAxis;
	/**
	 * @var BaseYAxis
	 * @link https://api.highcharts.com/highcharts/yAxis
	 */
	public $yAxis;
	/**
	 * @var PlotOptions
	 * @link https://api.highcharts.com/highcharts/plotOptions
	 */
	public $plotOptions;
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
		$this->xAxis = [];
		$this->yAxis = new BaseYAxis();
		$this->plotOptions = new PlotOptions();
		$this->tooltip = new BaseTooltip();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->type = "bar";
		$this->title->text = "Population pyramid for Germany, midyear 2010";
		$this->subtitle->text = "Source: www.census.gov";
		// xAxis can be an array of non-associative arrays.
		// It can also be an array of objects (associative arrays), for
		// an example with both scenarios see
		// demos/highcharts/ajax_loaded_data_clickable_points.php
		$this->xAxis = [
			[
				'categories' => new HighchartJsExpr("categories"),
				'reversed' => false,
			],
			[
				'opposite' => true,
				'reversed' => false,
				'categories' => new HighchartJsExpr("categories"),
				'linkedTo' => 0,
			],
		];
		$this->yAxis->title->text = null;
		$this->yAxis->labels->formatter = new HighchartJsExpr("function(){
		    return (Math.abs(this.value) / 1000000) + 'M';}");
		$this->yAxis->min = -4000000;
		$this->yAxis->max = 4000000;
		$this->plotOptions->series->stacking = "normal";
		$this->tooltip->formatter = new HighchartJsExpr("function() {
		    return '<b>'+ this.series.name +', age '+ this.point.category +'</b><br/>'+
		    'Population: '+ Highcharts.numberFormat(Math.abs(this.point.y), 0);}");
		$this->series[] = [
			'name' => "Female",
			'data' => [
				-1746181,
				-1884428,
				-2089758,
				-2222362,
				-2537431,
				-2507081,
				-2443179,
				-2664537,
				-3556505,
				-3680231,
				-3143062,
				-2721122,
				-2229181,
				-2227768,
				-2176300,
				-1329968,
				-836804,
				-354784,
				-90569,
				-28367,
				-3878,
			],
		];
		$this->series[] = [
			'name' => "Male",
			'data' => [
				1656154,
				1787564,
				1981671,
				2108575,
				2403438,
				2366003,
				2301402,
				2519874,
				3360596,
				3493473,
				3050775,
				2759560,
				2304444,
				2426504,
				2568938,
				1785638,
				1447162,
				1005011,
				330870,
				130632,
				21208,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/column_and_bar/bar_with_negative_stack.php');
	}
}
