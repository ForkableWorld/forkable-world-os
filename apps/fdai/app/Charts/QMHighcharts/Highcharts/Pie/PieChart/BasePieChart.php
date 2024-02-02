<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Pie\PieChart;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\PlotOptions;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BasePieChart extends HighchartConfig {
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
		$this->plotOptions = new PlotOptions();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->plotBackgroundColor = null;
		$this->chart->plotBorderWidth = null;
		$this->chart->plotShadow = false;
		$this->title->text = "Browser market shares at a specific website, 2010";
		$this->tooltip->formatter = new HighchartJsExpr("function() {
		    return '<b>'+ this.point.name +'</b>: '+ this.percentage +' %';}");
		$this->plotOptions->pie->allowPointSelect = 1;
		$this->plotOptions->pie->cursor = "pointer";
		$this->plotOptions->pie->dataLabels->enabled = 1;
		$this->plotOptions->pie->dataLabels->color = "#000000";
		$this->plotOptions->pie->dataLabels->connectorColor = "#000000";
		$this->plotOptions->pie->dataLabels->formatter = new HighchartJsExpr("function() {
		    return '<b>'+ this.point.name +'</b>: '+ this.percentage +' %'; }");
		$this->series[] = [
			'type' => "pie",
			'name' => "Browser share",
			'data' => [
				[
					"Firefox",
					45,
				],
				[
					"IE",
					26.8,
				],
				[
					'name' => 'Chrome',
					'y' => 12.8,
					'sliced' => true,
					'selected' => true,
				],
				[
					"Safari",
					8.5,
				],
				[
					"Opera",
					6.2,
				],
				[
					"Others",
					0.7,
				],
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/pie/pie_chart.php');
	}
}
