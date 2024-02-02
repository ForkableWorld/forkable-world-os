<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Line\SplineWithPlotBands;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseNavigation;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseSubtitle;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use App\Charts\QMHighcharts\Options\PlotOptions;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseSplineWithPlotBands extends HighchartConfig {
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
	 * @var BaseSubtitle
	 * @link https://api.highcharts.com/highcharts/subtitle
	 */
	public $subtitle;
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
	 * @var PlotOptions
	 * @link https://api.highcharts.com/highcharts/plotOptions
	 */
	public $plotOptions;
	/**
	 * @var BaseSeries[]
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	/**
	 * @var BaseNavigation
	 * @link https://api.highcharts.com/highcharts/navigation
	 */
	public $navigation;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->title = new BaseTitle();
		$this->subtitle = new BaseSubtitle();
		$this->xAxis = new BaseXAxis();
		$this->yAxis = new BaseYAxis();
		$this->tooltip = new BaseTooltip();
		$this->plotOptions = new PlotOptions();
		$this->series = [];
		$this->navigation = new BaseNavigation();
		$this->chart->renderTo = "container";
		$this->chart->type = "spline";
		$this->title->text = "Wind speed during two days";
		$this->subtitle->text = "October 6th and 7th 2009 at two locations in Vik i Sogn, Norway";
		$this->xAxis->type = "datetime";
		$this->yAxis->title->text = "Wind speed (m/s)";
		$this->yAxis->min = 0;
		$this->yAxis->minorGridLineWidth = 0;
		$this->yAxis->gridLineWidth = 0;
		$this->yAxis->alternateGridColor = false;
		$this->yAxis->plotBands[] = [
			'from' => 0.3,
			'to' => 1.5,
			'color' => "rgba(68, 170, 213, 0.1)",
			'label' => [
				'text' => "Light air",
				'style' => [
					'color' => "#606060",
				],
			],
		];
		$this->yAxis->plotBands[1]->from = 1.5;
		$this->yAxis->plotBands[1]->to = 3.3;
		$this->yAxis->plotBands[1]->color = "rgba(0, 0, 0, 0)";
		$this->yAxis->plotBands[1]->label->text = "Light breeze";
		$this->yAxis->plotBands[1]->label->style->color = "#606060";
		$this->yAxis->plotBands[]->from = 3.3;
		$this->yAxis->plotBands[2]->to = 5.5;
		$this->yAxis->plotBands[2]->color = "rgba(68, 170, 213, 0.1)";
		$this->yAxis->plotBands[2]->label->text = "Gentle breeze";
		$this->yAxis->plotBands[2]->label->style->color = "#606060";
		$this->yAxis->plotBands[] = [
			'from' => 5.5,
			'to' => 8,
			'color' => "rgba(0, 0, 0, 0)",
			'label' => [
				'text' => "Moderate breeze",
				'style' => [
					'color' => "#606060",
				],
			],
		];
		$this->yAxis->plotBands[] = [
			'from' => 8,
			'to' => 11,
			'color' => "rgba(68, 170, 213, 0.1)",
			'label' => [
				'text' => "Fresh breeze",
				'style' => [
					'color' => "#606060",
				],
			],
		];
		$this->yAxis->plotBands[] = [
			'from' => 11,
			'to' => 14,
			'color' => "rgba(0, 0, 0, 0)",
			'label' => [
				'text' => "Strong breeze",
				'style' => [
					'color' => "#606060",
				],
			],
		];
		$this->yAxis->plotBands[] = [
			'from' => 14,
			'to' => 15,
			'color' => "rgba(68, 170, 213, 0.1)",
			'label' => [
				'text' => "High wind",
				'style' => [
					'color' => "#606060",
				],
			],
		];
		$this->tooltip->formatter = new HighchartJsExpr("function() {
		                                return ''+
		                                Highcharts.dateFormat('%e. %b %Y, %H:00', this.x) +': '+ this.y +' m/s';}");
		$this->plotOptions->spline->lineWidth = 4;
		$this->plotOptions->spline->states->hover->lineWidth = 5;
		$this->plotOptions->spline->marker->enabled = false;
		$this->plotOptions->spline->marker->states->hover->enabled = 1;
		$this->plotOptions->spline->marker->states->hover->symbol = "circle";
		$this->plotOptions->spline->marker->states->hover->radius = 5;
		$this->plotOptions->spline->marker->states->hover->lineWidth = 1;
		$this->plotOptions->spline->pointInterval = 3600000;
		$this->plotOptions->spline->pointStart = new HighchartJsExpr("Date.UTC(2009, 9, 6, 0, 0, 0)");
		$this->series[] = [
			'name' => "Hestavollane",
			'data' => [
				4.3,
				5.1,
				4.3,
				5.2,
				5.4,
				4.7,
				3.5,
				4.1,
				5.6,
				7.4,
				6.9,
				7.1,
				7.9,
				7.9,
				7.5,
				6.7,
				7.7,
				7.7,
				7.4,
				7.0,
				7.1,
				5.8,
				5.9,
				7.4,
				8.2,
				8.5,
				9.4,
				8.1,
				10.9,
				10.4,
				10.9,
				12.4,
				12.1,
				9.5,
				7.5,
				7.1,
				7.5,
				8.1,
				6.8,
				3.4,
				2.1,
				1.9,
				2.8,
				2.9,
				1.3,
				4.4,
				4.2,
				3.0,
				3.0,
			],
		];
		$this->series[] = [
			'name' => "Voll",
			'data' => [
				0.0,
				0.0,
				0.0,
				0.0,
				0.0,
				0.0,
				0.0,
				0.0,
				0.1,
				0.0,
				0.3,
				0.0,
				0.0,
				0.4,
				0.0,
				0.1,
				0.0,
				0.0,
				0.0,
				0.0,
				0.0,
				0.0,
				0.0,
				0.0,
				0.0,
				0.6,
				1.2,
				1.7,
				0.7,
				2.9,
				4.1,
				2.6,
				3.7,
				3.9,
				1.7,
				2.3,
				3.0,
				3.3,
				4.8,
				5.0,
				4.8,
				5.0,
				3.2,
				2.0,
				0.9,
				0.4,
				0.3,
				0.5,
				0.4,
			],
		];
		$this->navigation->menuItemStyle->fontSize = "10px";
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/line/spline_with_plot_bands.php');
	}
}
