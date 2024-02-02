<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\ColumnAndBar\ColumnWithDrilldown;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseExporting;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseSubtitle;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use App\Charts\QMHighcharts\Options\PlotOptions;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseColumnWithDrilldown extends HighchartConfig {
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
	/**
	 * @var BaseExporting
	 * @link https://api.highcharts.com/highcharts/exporting
	 */
	public $exporting;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->title = new BaseTitle();
		$this->subtitle = new BaseSubtitle();
		$this->xAxis = new BaseXAxis();
		$this->yAxis = new BaseYAxis();
		$this->plotOptions = new PlotOptions();
		$this->tooltip = new BaseTooltip();
		$this->series = [];
		$this->exporting = new BaseExporting();
		$this->chart->renderTo = "container";
		$this->chart->type = "column";
		$this->title->text = "Browser market share, April, 2011";
		$this->subtitle->text = "Click the columns to view versions. Click again to view brands.";
		$this->xAxis->categories = new HighchartJsExpr("categories");
		$this->yAxis->title->text = "Total percent market share";
		$this->plotOptions->column->cursor = "pointer";
		$this->plotOptions->column->point->events->click = new HighchartJsExpr("function() {
		    var drilldown = this.drilldown;
		    if (drilldown) { // drill down
		      setChart(drilldown.name, drilldown.categories, drilldown.data, drilldown.color);
		    } else { // restore
		      setChart(name, categories, data);
		    }}");
		$this->plotOptions->column->dataLabels->enabled = 1;
		$this->plotOptions->column->dataLabels->color = new HighchartJsExpr("colors[0]");
		$this->plotOptions->column->dataLabels->style->fontWeight = "bold";
		$this->plotOptions->column->dataLabels->formatter = new HighchartJsExpr("function() {
		    return this.y +'%';}");
		$this->tooltip->formatter = new HighchartJsExpr("function() {
		    var point = this.point,
		        s = this.x +':<b>'+ this.y +'% market share</b><br/>';
		    if (point.drilldown) {
		      s += 'Click to view '+ point.category +' versions';
		    } else {
		      s += 'Click to return to browser brands';
		    }
		    return s;}");
		$this->series[] = [
			'name' => new HighchartJsExpr("name"),
			'data' => new HighchartJsExpr("data"),
			'color' => 'white',
		];
		$this->exporting->enabled = false;
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/column_and_bar/column_with_drilldown.php');
	}
}
