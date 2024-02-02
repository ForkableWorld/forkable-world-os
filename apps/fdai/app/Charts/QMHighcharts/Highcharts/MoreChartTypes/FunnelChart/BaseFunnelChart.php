<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\MoreChartTypes\FunnelChart;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseLegend;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\PlotOptions;
class BaseFunnelChart extends HighchartConfig {
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
	 * @var PlotOptions
	 * @link https://api.highcharts.com/highcharts/plotOptions
	 */
	public $plotOptions;
	/**
	 * @var BaseLegend
	 * @link https://api.highcharts.com/highcharts/legend
	 */
	public $legend;
	/**
	 * @var BaseSeries[]
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->title = new BaseTitle();
		$this->plotOptions = new PlotOptions();
		$this->legend = new BaseLegend();
		$this->series = [];
		$this->includeExtraScripts();
		$this->chart = [
			'renderTo' => 'container',
			'type' => 'funnel',
			'marginRight' => 100,
		];
		$this->title = [
			'text' => 'Sales funnel',
			'x' => -50,
		];
		$this->plotOptions->series = [
			'dataLabels' => [
				'enabled' => true,
				'format' => '<b>{point.name}</b> ({point.y:,.0f})',
				'color' => 'black',
				'softConnector' => true,
			],
			'neckWidth' => '30%',
			'neckHeight' => '25%',
		];
		$this->legend->enabled = false;
		$this->series = [
			[
				'name' => 'Unique users',
				'data' => [
					['Website visits', 15654],
					['Downloads', 4064],
					['Requested price list', 1987],
					['Invoice sent', 976],
					['Finalized', 846],
				],
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/more_chart_types/funnel_chart.php');
	}
}
