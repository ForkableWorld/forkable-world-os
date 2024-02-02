<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highstock\General\IntradayArea;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseRangeSelector;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseIntradayArea extends HighchartConfig {
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
	 * @var BaseRangeSelector
	 * @link https://api.highcharts.com/highcharts/rangeSelector
	 */
	public $rangeSelector;
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
		$this->rangeSelector = new BaseRangeSelector();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->title->text = "AAPL stock price by minute";
		$this->xAxis->gapGridLineWidth = 0;
		$this->rangeSelector->buttons = [
			[
				'type' => "hour",
				'count' => 1,
				'text' => "1h",
			],
			[
				'type' => "day",
				'count' => 1,
				'text' => "1D",
			],
			[
				'type' => "all",
				'count' => 1,
				'text' => "All",
			],
		];
		$this->rangeSelector->selected = 1;
		$this->rangeSelector->inputEnabled = false;
		$this->series[] = [
			'name' => "AAPL",
			'type' => "area",
			'data' => new HighchartJsExpr("data"),
			'gapSize' => 5,
			'tooltip' => [
				'valueDecimals' => 2,
			],
			'fillColor' => [
				'linearGradient' => [
					'x1' => 0,
					'y1' => 0,
					'x2' => 0,
					'y2' => 1,
				],
				'stops' => [
					[
						0,
						new HighchartJsExpr("Highcharts.getOptions().colors[0]"),
					],
					[
						1,
						"rgba(0,0,0,0)",
					],
				],
			],
			'threshold' => null,
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highstock/general/intraday_area.php');
	}
}
