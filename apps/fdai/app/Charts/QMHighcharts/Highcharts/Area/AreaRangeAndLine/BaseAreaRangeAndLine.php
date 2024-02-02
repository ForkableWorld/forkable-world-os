<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Area\AreaRangeAndLine;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseAreaRangeAndLine extends HighchartConfig {
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
	 * @var BaseYAxis
	 * @link https://api.highcharts.com/highcharts/yAxis
	 */
	public $yAxis;
	/**
	 * @var BaseTooltip
	 * @link https://api.highcharts.com/highcharts/tooltip
	 */
	public $tooltip;
	/**
	 * @var object
	 * @link https://api.highcharts.com/highcharts/baseAreaRangeAndLine.legend
	 */
	public $legend;
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
		$this->yAxis = new BaseYAxis();
		$this->tooltip = new BaseTooltip();
		$this->series = [];
		$this->includeExtraScripts();
		$this->chart->renderTo = "container";
		$this->title->text = "July temperatures";
		$this->xAxis->type = "datetime";
		$this->yAxis->title->text = null;
		$this->tooltip = [
			'crosshairs' => true,
			'shared' => true,
			'valueSuffix' => 'ºC',
		];
		$this->legend = new stdClass();
		$this->series[] = [
			'name' => 'Temperatures',
			'data' => $averages,
			'zIndex' => 1,
			'marker' => [
				'fillColor' => 'white',
				'lineWidth' => 2,
				'lineColor' => new HighchartJsExpr("Highcharts.getOptions().colors[0]"),
			],
		];
		$this->series[] = [
			'name' => 'Range',
			'data' => $ranges,
			'type' => 'arearange',
			'lineWidth' => 0,
			'linkedTo' => ':previous',
			'color' => new HighchartJsExpr("Highcharts.getOptions().colors[0]"),
			'fillOpacity' => 0.3,
			'zIndex' => 0,
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/area/area_range_and_line.php');
	}
}
