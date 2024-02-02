<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Line\SplineWithInvertedAxes;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseLegend;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseSubtitle;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use App\Charts\QMHighcharts\Options\PlotOptions;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseSplineWithInvertedAxes extends HighchartConfig {
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
		$this->legend = new BaseLegend();
		$this->tooltip = new BaseTooltip();
		$this->plotOptions = new PlotOptions();
		$this->series = [];
		$this->chart->renderTo = 'container';
		$this->chart->type = 'spline';
		$this->chart->inverted = true;
		$this->chart->width = 500;
		$this->chart->style->margin = '0 auto';
		$this->title->text = 'Atmosphere Temperature by Altitude';
		$this->subtitle->text = 'According to the Standard Atmosphere Model';
		$this->xAxis->reversed = false;
		$this->xAxis->title->enabled = true;
		$this->xAxis->title->text = 'Altitude';
		$this->xAxis->labels->formatter = new HighchartJsExpr("function() { return this.value +'km'; }");
		$this->xAxis->maxPadding = 0.05;
		$this->xAxis->showLastLabel = true;
		$this->yAxis->title->text = 'Temperature';
		$this->yAxis->labels->formatter = new HighchartJsExpr("function() { return this.value + '°'; }");
		$this->yAxis->lineWidth = 2;
		$this->yAxis->showFirstLabel = false;
		$this->legend->enabled = false;
		$this->tooltip->formatter = new HighchartJsExpr("function() { return ''+ this.x +' km: '+ this.y +'°C';}");
		$this->plotOptions->spline->marker->enable = false;
		$this->series[]->name = 'Temperature';
		$this->series[0]->data = [
			[
				0,
				15,
			],
			[
				10,
				-50,
			],
			[
				20,
				-56.5,
			],
			[
				30,
				-46.5,
			],
			[
				40,
				-22.1,
			],
			[
				50,
				-2.5,
			],
			[
				60,
				-27.7,
			],
			[
				70,
				-55.7,
			],
			[
				80,
				-76.5,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/line/spline_with_inverted_axes.php');
	}
}
