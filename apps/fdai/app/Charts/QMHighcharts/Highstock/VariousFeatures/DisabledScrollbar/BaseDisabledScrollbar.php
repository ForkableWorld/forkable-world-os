<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highstock\VariousFeatures\DisabledScrollbar;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseRangeSelector;
use App\Charts\QMHighcharts\Options\BaseScrollbar;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseDisabledScrollbar extends HighchartConfig {
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
	 * @var BaseScrollbar
	 * @link https://api.highcharts.com/highcharts/scrollbar
	 */
	public $scrollbar;
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
		$this->scrollbar = new BaseScrollbar();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->rangeSelector->selected = 1;
		$this->title->text = "AAPL Stock Price";
		$this->scrollbar->enabled = false;
		$this->series[] = [
			'name' => "AAPL Stock Price",
			'data' => new HighchartJsExpr("data"),
			'tooltip' => [
				'valueDecimals' => 2,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highstock/various_features/disabled_scrollbar.php');
	}
}
