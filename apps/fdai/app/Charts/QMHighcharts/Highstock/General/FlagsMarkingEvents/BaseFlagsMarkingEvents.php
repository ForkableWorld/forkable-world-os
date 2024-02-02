<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highstock\General\FlagsMarkingEvents;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseRangeSelector;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseFlagsMarkingEvents extends HighchartConfig {
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
	 * @var BaseTooltip
	 * @link https://api.highcharts.com/highcharts/tooltip
	 */
	public $tooltip;
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
		$this->tooltip = new BaseTooltip();
		$this->yAxis = new BaseYAxis();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->rangeSelector->selected = 1;
		$this->title->text = "USD to EUR exchange rate";
		$this->tooltip->style->width = "200px";
		$this->tooltip->valueDecimals = 4;
		$this->yAxis->title->text = "Exchange rate";
		$this->series[] = [
			'name' => "USD to EUR",
			'data' => new HighchartJsExpr("data"),
			'id' => "dataseries",
		];
		$this->series[1]->type = "flags";
		$this->series[1]->data[] = [
			'x' => new HighchartJsExpr("Date.UTC(2011, 3, 25)"),
			'title' => "H",
			'text' => "Euro Contained by Channel Resistance",
		];
		$this->series[1]->data[] = [
			'x' => new HighchartJsExpr("Date.UTC(2011, 3, 28)"),
			'title' => "G",
			'text' => "EURUSD: Bulls Clear Path to 1.50 Figure",
		];
		$this->series[1]->data[] = [
			'x' => new HighchartJsExpr("Date.UTC(2011, 4, 4)"),
			'title' => "F",
			'text' => "EURUSD: Rate Decision to End Standstill",
		];
		$this->series[1]->data[] = [
			'x' => new HighchartJsExpr("Date.UTC(2011, 4, 5)"),
			'title' => "E",
			'text' => "EURUSD: Enter Short on Channel Break",
		];
		$this->series[1]->data[] = [
			'x' => new HighchartJsExpr("Date.UTC(2011, 4, 6)"),
			'title' => "D",
			'text' => "Forex: U.S. Non-Farm Payrolls Expand 244K, U.S. Dollar Rally Cut Short By Risk Appetite",
		];
		$this->series[1]->data[] = [
			'x' => new HighchartJsExpr("Date.UTC(2011, 4, 6)"),
			'title' => "C",
			'text' => "US Dollar: Is This the Long-Awaited Recovery or a Temporary Bounce?",
		];
		$this->series[1]->data[] = [
			'x' => new HighchartJsExpr("Date.UTC(2011, 4, 9)"),
			'title' => "B",
			'text' => "EURUSD: Bearish Trend Change on Tap?",
		];
		$this->series[1]->onSeries = "dataseries";
		$this->series[1]->shape = "circlepin";
		$this->series[1]->width = 16;
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highstock/general/flags_marking_events.php');
	}
}
