<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Dynamic\SplineUpdatingEachSecond;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseExporting;
use App\Charts\QMHighcharts\Options\BaseLegend;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
use Ghunti\HighchartsPHP\HighchartOption;
class BaseSplineUpdatingEachSecond extends HighchartConfig {
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
	 * @var BaseTooltip
	 * @link https://api.highcharts.com/highcharts/tooltip
	 */
	public $tooltip;
	/**
	 * @var BaseLegend
	 * @link https://api.highcharts.com/highcharts/legend
	 */
	public $legend;
	/**
	 * @var BaseExporting
	 * @link https://api.highcharts.com/highcharts/exporting
	 */
	public $exporting;
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
		$this->tooltip = new BaseTooltip();
		$this->legend = new BaseLegend();
		$this->exporting = new BaseExporting();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->type = "spline";
		$this->chart->marginRight = 10;
		$this->chart->events->load = new HighchartJsExpr("function() {
		    var series = this.series[0];
		    setInterval(function() {
		        var x = (new Date()).getTime(),
		            y = Math.random();
		            series.addPoint([x, y], true, true);
		    }, 1000); }");
		$this->title->text = "Live random data";
		$this->xAxis->type = "datetime";
		$this->xAxis->tickPixelInterval = 150;
		$this->yAxis->title->text = "Value";
		$this->yAxis->plotLines[] = [
			'value' => 0,
			'width' => 1,
			'color' => "#808080",
		];
		$this->tooltip->formatter = new HighchartJsExpr("function() {
		    return '<b>'+ this.series.name +'</b><br/>'+
		    Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', this.x) +'<br/>'+
		    Highcharts.numberFormat(this.y, 2); }");
		$this->legend->enabled = false;
		$this->exporting->enabled = false;
		$this->series[0]->name = "Random data";
		$this->series[0]->data = new HighchartJsExpr("(function() {
		    var data = [],
		        time = (new Date()).getTime(),
		        i;
		    for (i = -19; i <= 0; i++) {
		        data.push({
		            x: time + i * 1000,
		            y: Math.random()
		        });
		    }
		    return data; })()");
		$globalOptions = new HighchartOption();
		$globalOptions->global->useUTC = false;
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/dynamic/spline_updating_each_second.php');
	}
}
