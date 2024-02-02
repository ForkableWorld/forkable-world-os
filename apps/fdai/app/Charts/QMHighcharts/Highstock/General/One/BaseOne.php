<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highstock\General\One;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseNavigator;
use App\Charts\QMHighcharts\Options\BaseRangeSelector;
use App\Charts\QMHighcharts\Options\BaseScrollbar;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseSubtitle;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseOne extends HighchartConfig {
	/**
	 * @var BaseChart
	 * @link https://api.highcharts.com/highcharts/chart
	 */
	public $chart;
	/**
	 * @var BaseNavigator
	 * @link https://api.highcharts.com/highcharts/navigator
	 */
	public $navigator;
	/**
	 * @var BaseScrollbar
	 * @link https://api.highcharts.com/highcharts/scrollbar
	 */
	public $scrollbar;
	/**
	 * @var BaseTitle
	 * @link https://api.highcharts.com/highcharts/title
	 */
	public $title;
	/**
	 * @var BaseSubtitle
	 * @link https://api.highcharts.com/highcharts/subtitle
	 */
	public $subtitle;
	/**
	 * @var BaseRangeSelector
	 * @link https://api.highcharts.com/highcharts/rangeSelector
	 */
	public $rangeSelector;
	/**
	 * @var BaseXAxis
	 * @link https://api.highcharts.com/highcharts/xAxis
	 */
	public $xAxis;
	/**
	 * @var BaseSeries[]
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->navigator = new BaseNavigator();
		$this->scrollbar = new BaseScrollbar();
		$this->title = new BaseTitle();
		$this->subtitle = new BaseSubtitle();
		$this->rangeSelector = new BaseRangeSelector();
		$this->xAxis = new BaseXAxis();
		$this->series = [];
		$this->includeExtraScripts();
		$this->chart->type = 'candlestick';
		$this->chart->zoomType = 'x';
		$this->navigator->adaptToUpdatedData = false;
		$this->navigator->series->data = new HighchartJsExpr('data');
		$this->scrollbar->liveRedraw = false;
		$this->title->text = 'AAPL history by the minute from 1998 to 2011';
		$this->subtitle->text = 'Displaying 1.7 million data points in Highcharts Stock by async server loading';
		$this->rangeSelector->buttons = [
			[
				'type' => 'hour',
				'count' => 1,
				'text' => '1h',
			],
			[
				'type' => 'day',
				'count' => 1,
				'text' => '1d',
			],
			[
				'type' => 'month',
				'count' => 1,
				'text' => '1m',
			],
			[
				'type' => 'year',
				'count' => 1,
				'text' => '1y',
			],
			[
				'type' => 'all',
				'text' => 'All',
			],
		];
		$this->rangeSelector->inputEnabled = false;
		$this->rangeSelector->selected = 4;
		$this->xAxis->events->afterSetExtremes = new HighchartJsExpr('afterSetExtremes');
		$this->xAxis->minRange = 3600 * 1000;
		$this->series[] = [
			'data' => new HighchartJsExpr('data'),
			'dataGrouping' => ['enabled' => false],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highstock/general/1.7_million_points_with_async_loading.php');
	}
}
