<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Dynamic\MasterDetailChart;
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
class BaseMasterDetailChart extends HighchartConfig {
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
		$masterChart->chart->renderTo = "master-container";
		$masterChart->chart->reflow = false;
		$masterChart->chart->borderWidth = 0;
		$masterChart->chart->backgroundColor = null;
		$masterChart->chart->marginLeft = 50;
		$masterChart->chart->marginRight = 20;
		$masterChart->chart->zoomType = "x";
		$masterChart->chart->events->selection = new HighchartJsExpr("function(event) {
		    var extremesObject = event.xAxis[0],
		        min = extremesObject.min,
		        max = extremesObject.max,
		        detailData = [],
		        xAxis = this.xAxis[0];
		    jQuery.each(this.series[0].data, function(i, point) {
		        if (point.x > min && point.x < max) {
		            detailData.push({
		              x: point.x,
		              y: point.y
		            });
		        }
		    });
		    xAxis.removePlotBand('mask-before');
		    xAxis.addPlotBand({
		        id: 'mask-before',
		        from: Date.UTC(2006, 0, 1),
		        to: min,
		        color: 'rgba(0, 0, 0, 0.2)'
		    });
		    xAxis.removePlotBand('mask-after');
		    xAxis.addPlotBand({
		        id: 'mask-after',
		        from: max,
		        to: Date.UTC(2008, 11, 31),
		        color: 'rgba(0, 0, 0, 0.2)'
		    });
		    detailChart.series[0].setData(detailData);
		    return false; }");
		$masterChart->title->text = null;
		$masterChart->xAxis->type = "datetime";
		$masterChart->xAxis->showLastTickLabel = 1;
		$masterChart->xAxis->maxZoom = 14 * 24 * 3600000;
		$masterChart->xAxis->plotBands[] = [
			'id' => "mask-before",
			'from' => new HighchartJsExpr("Date.UTC(2006, 0, 1)"),
			'to' => new HighchartJsExpr("Date.UTC(2008, 7, 1)"),
			'color' => "rgba(0, 0, 0, 0.2)",
		];
		$masterChart->xAxis->title->text = null;
		$masterChart->yAxis->gridLineWidth = 0;
		$masterChart->yAxis->labels->enabled = false;
		$masterChart->yAxis->title->text = null;
		$masterChart->yAxis->min = 0.6;
		$masterChart->yAxis->showFirstLabel = false;
		$masterChart->tooltip->formatter = new HighchartJsExpr("function() {
		    return false; }");
		$masterChart->legend->enabled = false;
		$masterChart->credits->enabled = false;
		$masterChart->plotOptions->series->fillColor->linearGradient = [
			0,
			0,
			0,
			70,
		];
		$masterChart->plotOptions->series->fillColor->stops[] = [
			0,
			'#4572A7',
		];
		$masterChart->plotOptions->series->fillColor->stops[] = [
			1,
			'rgba(0,0,0,0)',
		];
		$masterChart->plotOptions->series->lineWidth = 1;
		$masterChart->plotOptions->series->marker->enabled = false;
		$masterChart->plotOptions->series->shadow = false;
		$masterChart->plotOptions->series->states->hover->lineWidth = 1;
		$masterChart->plotOptions->series->enableMouseTracking = false;
		$masterChart->series[] = [
			'type' => "area",
			'name' => "USD to EUR",
			'pointInterval' => 24 * 3600 * 1000,
			'pointStart' => new HighchartJsExpr("Date.UTC(2006, 0, 01)"),
			'data' => new HighchartJsExpr("data"),
		];
		$masterChart->exporting->enabled = false;
		$detailChart = new Highchart();
		$detailChart->chart->marginBottom = 120;
		$detailChart->chart->renderTo = "detail-container";
		$detailChart->chart->reflow = false;
		$detailChart->chart->marginLeft = 50;
		$detailChart->chart->marginRight = 20;
		$detailChart->chart->style->position = "absolute";
		$detailChart->credits->enabled = false;
		$detailChart->title->text = "Historical USD to EUR Exchange Rate";
		$detailChart->subtitle->text = "Select an area by dragging across the lower chart";
		$detailChart->xAxis->type = "datetime";
		$detailChart->yAxis->title->text = null;
		$detailChart->yAxis->maxZoom = 0.1;
		$detailChart->tooltip->formatter = new HighchartJsExpr("function() {
		        var point = this.points[0];
		        return '<b>'+ point.series.name +'</b><br/>'+
		            Highcharts.dateFormat('%A %B %e %Y', this.x) + ':<br/>'+
		            '1 USD = '+ Highcharts.numberFormat(point.y, 2) +' EUR';
		    }");
		$detailChart->tooltip->shared = 1;
		$detailChart->legend->enabled = false;
		$detailChart->plotOptions->series->marker->enabled = false;
		$detailChart->plotOptions->series->marker->states->hover->enabled = 1;
		$detailChart->plotOptions->series->marker->states->hover->radius = 3;
		$detailChart->series[] = [
			'name' => "USD to EUR",
			'pointStart' => new HighchartJsExpr("detailStart"),
			'pointInterval' => 24 * 3600 * 1000,
			'data' => new HighchartJsExpr("detailData"),
		];
		$detailChart->exporting->enabled = false;
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/dynamic/master_detail_chart.php');
	}
}
