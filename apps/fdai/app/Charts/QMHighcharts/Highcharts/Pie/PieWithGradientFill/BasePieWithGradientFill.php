<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Pie\PieWithGradientFill;
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
class BasePieWithGradientFill extends HighchartConfig {
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
	 * @var BaseTooltip
	 * @link https://api.highcharts.com/highcharts/tooltip
	 */
	public $tooltip;
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
	 * @var BaseSeries[]
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->title = new BaseTitle();
		$this->tooltip = new BaseTooltip();
		$this->subtitle = new BaseSubtitle();
		$this->xAxis = new BaseXAxis();
		$this->yAxis = new BaseYAxis();
		$this->plotOptions = new PlotOptions();
		$this->series = [];
		$this->includeExtraScripts();
		$this->chart->renderTo = "container";
		$this->chart->plotBackgroundColor = null;
		$this->chart->plotBorderWidth = null;
		$this->chart->plotShadow = false;
		$this->title->text = "Browser market shares at a specific website, 2010";
		$this->tooltip->pointFormat = '{series.name}: <b>{point.percentage:.1f}%</b>';
		$this->subtitle->text = "Observed in Vik i Sogn, Norway, 2009";
		$this->xAxis->categories = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
		$this->yAxis->title->text = 'Temperature ( °C )';
		$this->plotOptions->pie = [
			'allowPointSelect' => true,
			'cursor' => 'pointer',
			'dataLabels' => [
				'enabled' => true,
				'color' => '#000000',
				'connectorColor' => '#000000',
				'formatter' => new HighchartJsExpr("function () {
		            return '<b>'+ this.point.name +'</b>: '+ this.percentage +' %'; }"),
			],
		];
		$this->series[] = [
			'type' => 'pie',
			'name' => 'Browser share',
			'data' => [
				['Firefox', 45.0],
				['IE', 26.8],
				[
					'name' => 'Chrome',
					'y' => 12.8,
					'sliced' => true,
					'selected' => true,
				],
				['Safari', 8.5],
				['Opera', 6.2],
				['Others', 0.7],
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/pie/pie_with_gradient_fill.php');
	}
}
