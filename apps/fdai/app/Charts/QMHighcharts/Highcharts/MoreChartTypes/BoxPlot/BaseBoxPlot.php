<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\MoreChartTypes\BoxPlot;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseLegend;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseBoxPlot extends HighchartConfig {
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
	 * @var BaseSeries[]
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->title = new BaseTitle();
		$this->legend = new BaseLegend();
		$this->xAxis = new BaseXAxis();
		$this->yAxis = new BaseYAxis();
		$this->series = [];
		$this->includeExtraScripts();
		$this->chart = [
			'renderTo' => 'container',
			'type' => 'boxplot',
		];
		$this->title->text = 'Highcharts Box Plot Example';
		$this->legend->enabled = false;
		$this->xAxis = [
			'categories' => ['1', '2', '3', '4', '5'],
			'title' => [
				'text' => 'Experiment No.',
			],
		];
		$this->yAxis = [
			'title' => [
				'text' => 'Observations',
			],
			'plotLines' => [
				[
					'value' => 932,
					'color' => 'red',
					'width' => 1,
					'label' => [
						'text' => 'Theoretical mean: 932',
						'align' => 'center',
						'style' => [
							'color' => 'gray',
						],
					],
				],
			],
		];
		$this->series[] = [
			'name' => 'Observations',
			'data' => [
				[760, 801, 848, 895, 965],
				[733, 853, 939, 980, 1080],
				[714, 762, 817, 870, 918],
				[724, 802, 806, 871, 950],
				[834, 836, 864, 882, 910],
			],
			'tooltip' => [
				'headerFormat' => '<em>Experiment No {point.key}</em><br/>',
			],
		];
		$this->series[] = [
			'name' => 'Outlier',
			'color' => new HighchartJsExpr('Highcharts.getOptions().colors[0]'),
			'type' => 'scatter',
			'data' => [
				[0, 644],
				[4, 718],
				[4, 718],
				[4, 969],
			],
			'marker' => [
				'fillColor' => 'white',
				'lineWidth' => 1,
				'lineColor' => new HighchartJsExpr('Highcharts.getOptions().colors[0]'),
			],
			'tooltip' => [
				'pointFormat' => 'Observation: {point.y}',
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/more_chart_types/box_plot.php');
	}
}
