<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highstock\VariousFeatures\PlotLinesOnYAxis;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseRangeSelector;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BasePlotLinesOnYAxis extends HighchartConfig {
	/**
	 * @var BaseChart
	 * @link https://api.highcharts.com/highcharts/chart
	 */
	public $chart;
	/**
	 * @var BaseRangeSelector
	 * @link https://api.highcharts.com/highcharts/rangeSelector
	 */
	public $rangeSelector;
	/**
	 * @var BaseTitle
	 * @link https://api.highcharts.com/highcharts/title
	 */
	public $title;
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
		$this->rangeSelector = new BaseRangeSelector();
		$this->title = new BaseTitle();
		$this->yAxis = new BaseYAxis();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->rangeSelector->selected = 1;
		$this->title->text = "USD to EUR exchange rate";
		$this->yAxis->title->text = "Exchange rate";
		$this->yAxis->plotLines[] = [
			'value' => 0.6738,
			'color' => "green",
			'dashStyle' => "shortdash",
			'width' => 2,
			'label' => [
				'text' => "Last quarter minimum",
			],
		];
		$this->yAxis->plotLines[] = [
			'value' => 0.7419,
			'color' => "red",
			'dashStyle' => "shortdash",
			'width' => 2,
			'label' => [
				'text' => "Last quarter maximum",
			],
		];
		$this->series[] = [
			'name' => "USD to EUR",
			'data' => new HighchartJsExpr("data"),
			'tooltip' => [
				'valueDecimals' => 4,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highstock/various_features/plot_lines_on_y_axis.php');
	}
}
