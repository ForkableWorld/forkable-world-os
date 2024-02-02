<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\ColumnAndBar\DataDefinedHtmlTable;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
use Ghunti\HighchartsPHP\HighchartOption;
class BaseDataDefinedHtmlTable extends HighchartConfig {
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
	 * @var object
	 * @link https://api.highcharts.com/highcharts/baseDataDefinedHtmlTable.xAxis
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
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->title = new BaseTitle();
		$this->yAxis = new BaseYAxis();
		$this->tooltip = new BaseTooltip();
		$this->chart->renderTo = "container";
		$this->chart->type = "column";
		$this->title->text = "Data extracted from a HTML table in the page";
		$this->xAxis = new HighchartOption(); // We need it to be empty to avoid js
		// errors
		$this->yAxis->title->text = "Units";
		$this->tooltip->formatter = new HighchartJsExpr("function() {
		    return '<b>'+ this.series.name +'</b><br/>'+
		    this.y +' '+ this.x.toLowerCase();}");
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/column_and_bar/data_defined_html_table.php');
	}
}
