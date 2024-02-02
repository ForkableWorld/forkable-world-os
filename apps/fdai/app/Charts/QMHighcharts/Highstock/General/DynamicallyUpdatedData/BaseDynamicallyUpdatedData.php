<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highstock\General\DynamicallyUpdatedData;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseExporting;
use App\Charts\QMHighcharts\Options\BaseRangeSelector;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseDynamicallyUpdatedData extends HighchartConfig {
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
	 * @var BaseExporting
	 * @link https://api.highcharts.com/highcharts/exporting
	 */
	public $exporting;
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
		$this->exporting = new BaseExporting();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->events->load = new HighchartJsExpr("function() {
		    var series = this.series[0];
		    setInterval(function() {
		        var x = (new Date()).getTime(), // current time
		        y = Math.round(Math.random() * 100);
		        series.addPoint([x, y], true, true);
		    }, 1000); }");
		$this->rangeSelector->buttons = [
			[
				'type' => "minute",
				'count' => 1,
				'text' => "1M",
			],
			[
				'type' => "minute",
				'count' => 5,
				'text' => "5M",
			],
			[
				'type' => "all",
				'text' => "All",
			],
		];
		$this->rangeSelector->inputEnabled = false;
		$this->rangeSelector->selected = 0;
		$this->title->text = "Live random data";
		$this->exporting->enabled = false;
		$this->series[] = [
			'name' => "Random data",
			'data' => new HighchartJsExpr("(function() {
		    var data = [], time = (new Date()).getTime(), i;
		    for( i = -999; i <= 0; i++) {
		        data.push([
		          time + i * 1000,
		          Math.round(Math.random() * 100)
		        ]);
		    }
		    return data;
		  })()"),
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highstock/general/dynamically_updated_data.php');
	}
}
