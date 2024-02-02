<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highstock\Flags\FlagsPlacement;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseRangeSelector;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseFlagsPlacement extends HighchartConfig {
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
		$this->series[] = [
			'name' => "USD to EUR",
			'data' => new HighchartJsExpr("data"),
			'id' => "dataseries",
			'tooltip' => [
				'valueDecimals' => 4,
			],
		];
		$this->series[] = [
			'type' => "flags",
			'name' => "Flags on series",
			'data' => [
				[
					'x' => new HighchartJsExpr("Date.UTC(2011, 1, 14)"),
					'title' => "On series",
				],
				[
					'x' => new HighchartJsExpr("Date.UTC(2011, 3, 28)"),
					'title' => "On series",
				],
			],
			'onSeries' => "dataseries",
			'shape' => "squarepin",
		];
		$this->series[] = [
			'type' => "flags",
			'name' => "Flags on axis",
			'data' => [
				[
					'x' => new HighchartJsExpr("Date.UTC(2011, 2, 1)"),
					'title' => "On axis",
				],
				[
					'x' => new HighchartJsExpr("Date.UTC(2011, 3, 1)"),
					'title' => "On axis",
				],
			],
			'shape' => "squarepin",
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highstock/flags/flags_placement.php');
	}
}
