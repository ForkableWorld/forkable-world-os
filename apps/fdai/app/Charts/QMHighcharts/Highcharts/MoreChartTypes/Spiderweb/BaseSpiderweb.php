<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\MoreChartTypes\Spiderweb;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseLegend;
use App\Charts\QMHighcharts\Options\BasePane;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
class BaseSpiderweb extends HighchartConfig {
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
	 * @var BasePane
	 * @link https://api.highcharts.com/highcharts/pane
	 */
	public $pane;
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
	 * @var BaseLegend
	 * @link https://api.highcharts.com/highcharts/legend
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
		$this->pane = new BasePane();
		$this->xAxis = new BaseXAxis();
		$this->yAxis = new BaseYAxis();
		$this->tooltip = new BaseTooltip();
		$this->legend = new BaseLegend();
		$this->series = [];
		$this->includeExtraScripts();
		$this->chart->renderTo = 'container';
		$this->chart->polar = true;
		$this->chart->type = 'line';
		$this->title->text = 'Budget vs spending';
		$this->title->x = -80;
		$this->pane->size = '80%';
		$this->pane->endAngle = 360;
		$this->xAxis->categories = [
			'Sales',
			'Marketing',
			'Development',
			'Customer Support',
			'Information Technology',
			'Administration',
		];
		$this->xAxis->tickmarkPlacement = 'on';
		$this->xAxis->lineWidth = 0;
		$this->yAxis->gridLineInterpolation = 'polygon';
		$this->yAxis->lineWidth = 0;
		$this->yAxis->min = 0;
		$this->tooltip->shared = true;
		$this->tooltip->pointFormat = '<span style="color:{series.color}">{series.name}: <b>${point.y:,.0f}</b><br/>';
		$this->legend->align = 'right';
		$this->legend->verticalAlign = 'top';
		$this->legend->y = 70;
		$this->legend->layout = 'vertical';
		$this->series = [
			[
				'name' => 'Allocated Budget',
				'data' => [43000, 19000, 60000, 35000, 17000, 10000],
				'pointPlacement' => 'on',
			],
			[
				'name' => 'Actual Spending',
				'data' => [50000, 39000, 42000, 31000, 26000, 14000],
				'pointPlacement' => 'on',
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/more_chart_types/spiderweb.php');
	}
}
