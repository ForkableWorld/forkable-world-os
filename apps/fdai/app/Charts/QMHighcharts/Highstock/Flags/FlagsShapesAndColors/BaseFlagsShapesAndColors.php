<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highstock\Flags\FlagsShapesAndColors;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseRangeSelector;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseFlagsShapesAndColors extends HighchartConfig {
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
			'data' => [
				[
					'x' => new HighchartJsExpr("Date.UTC(2011, 1, 14)"),
					'title' => "A",
					'text' => "Shape: \"squarepin\"",
				],
				[
					'x' => new HighchartJsExpr("Date.UTC(2011, 3, 28)"),
					'title' => "A",
					'text' => "Shape: \"squarepin\"",
				],
			],
			'onSeries' => "dataseries",
			'shape' => "squarepin",
			'width' => 16,
		];
		$this->series[] = [
			'type' => "flags",
			'data' => [
				[
					'x' => new HighchartJsExpr("Date.UTC(2011, 2, 1)"),
					'title' => "B",
					'text' => "Shape: \"circlepin\"",
				],
				[
					'x' => new HighchartJsExpr("Date.UTC(2011, 3, 1)"),
					'title' => "B",
					'text' => "Shape: \"circlepin\"",
				],
			],
			'shape' => "circlepin",
			'width' => 16,
		];
		$this->series[] = [
			'type' => "flags",
			'data' => [
				[
					'x' => new HighchartJsExpr("Date.UTC(2011, 2, 10)"),
					'title' => "C",
					'text' => "Shape: \"flag\"",
				],
				[
					'x' => new HighchartJsExpr("Date.UTC(2011, 3, 11)"),
					'title' => "C",
					'text' => "Shape: \"flag\"",
				],
			],
			'color' => "#5F86B3",
			'fillColor' => "#5F86B3",
			'onSeries' => "dataseries",
			'width' => 16,
			'style' => [
				'color' => "white",
			],
			'states' => [
				'hover' => [
					'fillColor' => "#395C84",
				],
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highstock/flags/flags_shapes_and_colors.php');
	}
}
