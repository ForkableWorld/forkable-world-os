<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\MoreChartTypes\WindRose;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseData;
use App\Charts\QMHighcharts\Options\BaseLegend;
use App\Charts\QMHighcharts\Options\BasePane;
use App\Charts\QMHighcharts\Options\BaseSubtitle;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use App\Charts\QMHighcharts\Options\PlotOptions;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseWindRose extends HighchartConfig {
	/**
	 * @var BaseChart
	 * @link https://api.highcharts.com/highcharts/chart
	 */
	public $chart;
	/**
	 * @var BaseData
	 * @link https://api.highcharts.com/highcharts/data
	 */
	public $data;
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
	 * @var BasePane
	 * @link https://api.highcharts.com/highcharts/pane
	 */
	public $pane;
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
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->data = new BaseData();
		$this->title = new BaseTitle();
		$this->subtitle = new BaseSubtitle();
		$this->pane = new BasePane();
		$this->legend = new BaseLegend();
		$this->xAxis = new BaseXAxis();
		$this->yAxis = new BaseYAxis();
		$this->tooltip = new BaseTooltip();
		$this->plotOptions = new PlotOptions();
		$this->includeExtraScripts();
		$this->chart->renderTo = 'container';
		$this->data->table = 'freq';
		$this->data->startRow = 1;
		$this->data->endRow = 17;
		$this->data->endColumn = 7;
		$this->chart->polar = true;
		$this->chart->type = 'column';
		$this->title->text = 'Wind rose for South Shore Met Station, Oregon';
		$this->subtitle->text = 'Source: or.water.usgs.gov';
		$this->pane->size = '85%';
		$this->legend->reversed = true;
		$this->legend->align = 'right';
		$this->legend->verticalAlign = 'top';
		$this->legend->y = 100;
		$this->legend->layout = 'vertical';
		$this->xAxis->tickmarkPlacement = 'on';
		$this->yAxis->min = 0;
		$this->yAxis->endOnTick = false;
		$this->yAxis->showLastLabel = true;
		$this->yAxis->title->text = 'Frequency (%)';
		$this->yAxis->labels->formatter = new HighchartJsExpr("function () { return this.value + '%'; }");
		$this->tooltip->valueSuffix = '%';
		$this->tooltip->followPointer = true;
		$this->plotOptions->series->stacking = 'normal';
		$this->plotOptions->series->shadow = false;
		$this->plotOptions->series->groupPadding = 0;
		$this->plotOptions->series->pointPlacement = 'on';
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/more_chart_types/wind_rose.php');
	}
}
