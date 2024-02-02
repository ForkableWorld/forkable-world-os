<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\ColumnAndBar\StackedAndGroupedColumn;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use App\Charts\QMHighcharts\Options\PlotOptions;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseStackedAndGroupedColumn extends HighchartConfig {
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
	 * @var PlotOptions
	 * @link https://api.highcharts.com/highcharts/plotOptions
	 */
	public $plotOptions;
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
		$this->plotOptions = new PlotOptions();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->type = "column";
		$this->title->text = "Total fruit consumtion, grouped by gender";
		$this->xAxis->categories = [
			'Apples',
			'Oranges',
			'Pears',
			'Grapes',
			'Bananas',
		];
		$this->yAxis->allowDecimals = false;
		$this->yAxis->min = 0;
		$this->yAxis->title->text = "Number of fruits";
		$this->tooltip->formatter = new HighchartJsExpr("function() {
		    return '<b>'+ this.x +'</b><br/>'+
		    this.series.name +': '+ this.y +'<br/>'+
		    'Total: '+ this.point.stackTotal;}");
		$this->plotOptions->column->stacking = "normal";
		$this->series[] = [
			'name' => "John",
			'data' => [
				5,
				3,
				4,
				7,
				2,
			],
			'stack' => 'male',
		];
		$this->series[] = [
			'name' => "Joe",
			'data' => [
				3,
				4,
				4,
				2,
				5,
			],
			'stack' => 'male',
		];
		$this->series[] = [
			'name' => "Jane",
			'data' => [
				2,
				5,
				6,
				2,
				1,
			],
			'stack' => 'female',
		];
		$this->series[] = [
			'name' => "Janet",
			'data' => [
				3,
				0,
				4,
				4,
				3,
			],
			'stack' => 'female',
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/column_and_bar/stacked_and_grouped_column.php');
	}
}
