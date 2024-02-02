<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\MoreChartTypes\PolarChart;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BasePane;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use App\Charts\QMHighcharts\Options\PlotOptions;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BasePolarChart extends HighchartConfig {
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
	 * @var BasePane
	 * @link https://api.highcharts.com/highcharts/pane
	 */
	public $pane;
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
	 * @var BaseSeries[]
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->title = new BaseTitle();
		$this->pane = new BasePane();
		$this->xAxis = new BaseXAxis();
		$this->yAxis = new BaseYAxis();
		$this->plotOptions = new PlotOptions();
		$this->series = [];
		$this->includeExtraScripts();
		$this->chart->renderTo = 'container';
		$this->chart->polar = true;
		$this->title->text = 'Highcharts Polar Chart';
		$this->pane->startAngle = 0;
		$this->pane->endAngle = 360;
		$this->xAxis->tickInterval = 45;
		$this->xAxis->min = 0;
		$this->xAxis->max = 360;
		$this->xAxis->labels->formatter = new HighchartJsExpr("function () { return this.value + '°'; }");
		$this->yAxis->min = 0;
		$this->plotOptions->series->pointStart = 0;
		$this->plotOptions->series->pointInterval = 45;
		$this->plotOptions->column->pointPadding = 0;
		$this->plotOptions->column->groupPadding = 0;
		$this->series = [
			[
				'type' => 'column',
				'name' => 'Column',
				'data' => [8, 7, 6, 5, 4, 3, 2, 1],
				'pointPlacement' => 'between',
			],
			[
				'type' => 'line',
				'name' => 'Line',
				'data' => [1, 2, 3, 4, 5, 6, 7, 8],
			],
			[
				'type' => 'area',
				'name' => 'Area',
				'data' => [1, 8, 2, 7, 3, 6, 4, 5],
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/more_chart_types/polar_chart.php');
	}
}
