<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\MoreChartTypes\Waterfall;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseLegend;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseWaterfall extends HighchartConfig {
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
	 * @var BaseXAxis
	 * @link https://api.highcharts.com/highcharts/xAxis
	 */
	public $xAxis;
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
	 * @var BaseSeries[]
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->title = new BaseTitle();
		$this->xAxis = new BaseXAxis();
		$this->legend = new BaseLegend();
		$this->tooltip = new BaseTooltip();
		$this->series = [];
		$this->includeExtraScripts();
		$this->chart = [
			'renderTo' => 'container',
			'type' => 'waterfall',
		];
		$this->title->text = 'Highcharts Waterfall';
		$this->xAxis->type = 'category';
		$this->xAxis->title->text = 'USD';
		$this->legend->enabled = false;
		$this->tooltip->pointFormat = '<b>${point.y:,.2f}</b> USD';
		$this->series = [
			[
				'upColor' => new HighchartJsExpr('Highcharts.getOptions().colors[2]'),
				'color' => new HighchartJsExpr('Highcharts.getOptions().colors[3]'),
				'data' => [
					[
						'name' => 'Start',
						'y' => 120000,
					],
					[
						'name' => 'Product Revenue',
						'y' => 569000,
					],
					[
						'name' => 'Service Revenue',
						'y' => 231000,
					],
					[
						'name' => 'Positive Balance',
						'isIntermediateSum' => true,
						'color' => new HighchartJsExpr('Highcharts.getOptions().colors[1]'),
					],
					[
						'name' => 'Fixed Costs',
						'y' => -342000,
					],
					[
						'name' => 'Variable Costs',
						'y' => -233000,
					],
					[
						'name' => 'Balance',
						'isSum' => true,
						'color' => new HighchartJsExpr('Highcharts.getOptions().colors[1]'),
					],
				],
				'dataLabels' => [
					'enabled' => true,
					'formatter' => new HighchartJsExpr("function () {
		                    return Highcharts.numberFormat(this.y / 1000, 0, ',') + 'k';
		                }"),
					'style' => [
						'color' => '#FFFFFF',
						'fontWeight' => 'bold',
						'textShadow' => '0px 0px 3px black',
					],
				],
				'pointPadding' => 0,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/more_chart_types/waterfall.php');
	}
}
