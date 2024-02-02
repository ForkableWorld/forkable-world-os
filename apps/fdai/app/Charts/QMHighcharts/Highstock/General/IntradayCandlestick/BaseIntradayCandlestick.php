<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highstock\General\IntradayCandlestick;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseRangeSelector;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseIntradayCandlestick extends HighchartConfig {
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
		$this->rangeSelector = new BaseRangeSelector();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->title->text = "AAPL stock price by minute";
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
			'type' => "candlestick",
			'data' => new HighchartJsExpr("data"),
			'tooltip' => [
				'valueDecimals' => 2,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highstock/general/intraday_candlestick.php');
	}
}
