<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highstock\General\TwoPanesCandlestickAndVolume;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseRangeSelector;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseTwoPanesCandlestickAndVolume extends HighchartConfig {
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
	 * @var BaseYAxis[]
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
		$this->yAxis = [];
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->alignTicks = false;
		$this->rangeSelector->selected = 1;
		$this->title->text = "AAPL Historical";
		$leftYaxis = new BaseYAxis();
		$leftYaxis->title->text = "OHLC";
		$leftYaxis->height = 200;
		$leftYaxis->lineWidth = 2;
		$rightYaxis = new BaseYAxis();
		$rightYaxis->title->text = "Volume";
		$rightYaxis->top = 300;
		$rightYaxis->height = 100;
		$rightYaxis->offset = 0;
		$rightYaxis->lineWidth = 2;
		$this->yAxis = [
			$leftYaxis,
			$rightYaxis,
		];
		$this->series[] = [
			'type' => "candlestick",
			'name' => "AAPL",
			'data' => new HighchartJsExpr("ohlc"),
			'dataGrouping' => [
				'units' => new HighchartJsExpr("groupingUnits"),
			],
		];
		$this->series[] = [
			'type' => "column",
			'name' => "Volume",
			'data' => new HighchartJsExpr("volume"),
			'yAxis' => 1,
			'dataGrouping' => [
				'units' => new HighchartJsExpr("groupingUnits"),
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highstock/general/two_panes_candlestick_and_volume.php');
	}
}
