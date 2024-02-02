<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highstock\ChartTypes\Area;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseRangeSelector;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseArea extends HighchartConfig {
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
	 * @var BaseSeries[]
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->rangeSelector = new BaseRangeSelector();
		$this->title = new BaseTitle();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->rangeSelector->selected = 1;
		$this->title->text = "AAPL Stock Price";
		$this->series[0]->name = "AAPL Stock Price";
		$this->series[0]->data = new HighchartJsExpr("data");
		$this->series[0]->type = "area";
		$this->series[0]->threshold = null;
		$this->series[0]->tooltip->valueDecimals = 2;
		$this->series[0]->fillColor->linearGradient->x1 = 0;
		$this->series[0]->fillColor->linearGradient->y1 = 0;
		$this->series[0]->fillColor->linearGradient->x2 = 0;
		$this->series[0]->fillColor->linearGradient->y2 = 1;
		$this->series[0]->fillColor->stops = [
			[
				0,
				new HighchartJsExpr("Highcharts.getOptions().colors[0]"),
			],
			[
				1,
				"rgba(0,0,0,0)",
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highstock/chart_types/area.php');
	}
}
