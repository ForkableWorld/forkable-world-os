<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\ColumnAndBar\ColumnWithNegativeValues;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseCredits;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseColumnWithNegativeValues extends HighchartConfig {
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
	 * @var BaseTooltip
	 * @link https://api.highcharts.com/highcharts/tooltip
	 */
	public $tooltip;
	/**
	 * @var BaseCredits
	 * @link https://api.highcharts.com/highcharts/credits
	 */
	public $credits;
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
		$this->tooltip = new BaseTooltip();
		$this->credits = new BaseCredits();
		$this->series = [];
		$this->chart->renderTo = "container";
		$this->chart->type = "column";
		$this->title->text = "Column chart with negative values";
		$this->xAxis->categories = [
			'Apples',
			'Oranges',
			'Pears',
			'Grapes',
			'Bananas',
		];
		$this->tooltip->formatter = new HighchartJsExpr("function() {
		    return '' + this.series.name +': '+ this.y +'';}");
		$this->credits->enabled = false;
		$this->series[] = [
			'name' => "John",
			'data' => [
				5,
				3,
				4,
				7,
				2,
			],
		];
		$this->series[] = [
			'name' => "Jane",
			'data' => [
				2,
				-2,
				-3,
				2,
				1,
			],
		];
		$this->series[] = [
			'name' => "Joe",
			'data' => [
				3,
				4,
				4,
				-2,
				5,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/column_and_bar/column_with_negative_values.php');
	}
}
