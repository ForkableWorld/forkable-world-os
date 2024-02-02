<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\MoreChartTypes\Clock;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseCredits;
use App\Charts\QMHighcharts\Options\BasePane;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use Ghunti\HighchartsPHP\HighchartJsExpr;
use Ghunti\HighchartsPHP\HighchartOption;
class BaseClock extends HighchartConfig {
	/**
	 * @var BaseChart
	 * @link https://api.highcharts.com/highcharts/chart
	 */
	public $chart;
	/**
	 * @var BaseCredits
	 * @link https://api.highcharts.com/highcharts/credits
	 */
	public $credits;
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
	 * @var BaseSeries[]
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->credits = new BaseCredits();
		$this->title = new BaseTitle();
		$this->pane = new BasePane();
		$this->yAxis = new BaseYAxis();
		$this->tooltip = new BaseTooltip();
		$this->series = [];
		$this->includeExtraScripts();
		$backgroundOptions = new HighchartOption();
		$backgroundOptions->radialGradient = [
			'cx' => 0.5,
			'cy' => -0.4,
			'r' => 1.9,
		];
		$backgroundOptions->stops = [
			[0.5, 'rgba(255, 255, 255, 0.2)'],
			[0.5, 'rgba(200, 200, 200, 0.2)'],
		];
		$this->chart = [
			'type' => 'gauge',
			'plotBackgroundColor' => null,
			'plotBackgroundImage' => null,
			'plotBorderWidth' => 0,
			'plotShadow' => false,
			'height' => 200,
		];
		$this->credits->enabled = false;
		$this->title->text = 'The Highcharts clock';
		$this->pane->background = [
			new stdClass(),
			[
				'backgroundColor' => new HighchartJsExpr('Highcharts.svg ? ' .
					HighchartOptionRenderer::render($backgroundOptions) . ' : null'),
			],
		];
		$this->yAxis = [
			'labels' => [
				'distance' => -20,
			],
			'min' => 0,
			'max' => 12,
			'lineWidth' => 0,
			'showFirstLabel' => false,
			'minorTickInterval' => 'auto',
			'minorTickWidth' => 1,
			'minorTickLength' => 5,
			'minorTickPosition' => 'inside',
			'minorGridLineWidth' => 0,
			'minorTickColor' => '#666',
			'tickInterval' => 1,
			'tickWidth' => 2,
			'tickPosition' => 'inside',
			'tickLength' => 10,
			'tickColor' => '#666',
			'title' => [
				'text' => 'Powered by<br/>Highcharts',
				'style' => [
					'color' => '#BBB',
					'fontWeight' => 'normal',
					'fontSize' => '8px',
					'lineHeight' => '10px',
				],
				'y' => 10,
			],
		];
		$this->tooltip->formatter = new HighchartJsExpr('function () { return this.series.chart.tooltipText; }');
		$this->series[] = [
			'data' => [
				[
					'id' => 'hour',
					'y' => new HighchartJsExpr('now.hours'),
					'dial' => [
						'radius' => '60%',
						'baseWidth' => 4,
						'baseLength' => '95%',
						'rearLength' => 0,
					],
				],
				[
					'id' => 'minute',
					'y' => new HighchartJsExpr('now.minutes'),
					'dial' => [
						'baseLength' => '95%',
						'rearLength' => 0,
					],
				],
				[
					'id' => 'second',
					'y' => new HighchartJsExpr('now.seconds'),
					'dial' => [
						'radius' => '100%',
						'baseWidth' => 1,
						'rearLength' => '20%',
					],
				],
			],
			'animation' => false,
			'dataLabels' => [
				'enabled' => false,
			],
		];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/more_chart_types/clock.php');
	}
}
