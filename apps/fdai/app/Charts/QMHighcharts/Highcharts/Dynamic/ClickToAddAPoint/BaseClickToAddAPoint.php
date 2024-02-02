<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Dynamic\ClickToAddAPoint;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseExporting;
use App\Charts\QMHighcharts\Options\BaseLegend;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseSubtitle;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use App\Charts\QMHighcharts\Options\PlotOptions;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseClickToAddAPoint extends HighchartConfig {
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
		$this->legend = new BaseLegend();
		$this->exporting = new BaseExporting();
		$this->plotOptions = new PlotOptions();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->type = "scatter";
		$this->chart->margin = [
			70,
			50,
			60,
			80,
		];
		$this->chart->events->click = new HighchartJsExpr("function(e) {
		        var x = e.xAxis[0].value,
		            y = e.yAxis[0].value,
		            series = this.series[0];
		            series.addPoint([x, y]);
		    }");
		$this->title->text = "User supplied data";
		$this->subtitle->text = "Click the plot area to add a point. Click a point to remove it.";
		$this->xAxis->minPadding = 0.2;
		$this->xAxis->maxPadding = 0.2;
		$this->xAxis->maxZoom = 60;
		$this->yAxis->title->text = "Value";
		$this->yAxis->minPadding = 0.2;
		$this->yAxis->maxPadding = 0.2;
		$this->yAxis->maxZoom = 60;
		$this->yAxis->plotLines[] = [
			'value' => 0,
			'width' => 1,
			'color' => "#808080",
		];
		$this->legend->enabled = false;
		$this->exporting->enabled = false;
		$this->plotOptions->series->lineWidth = 1;
		$this->plotOptions->series->point->events->click = new HighchartJsExpr("function() {
		    if (this.series.data.length > 1) this.remove(); }");
		$this->series[]->data = [
			[
				20,
				20,
			],
			[
				80,
				80,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/dynamic/click_to_add_a_point.php');
	}
}
