<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highcharts\Line\AjaxLoadedDataClickablePoints;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseLegend;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseSubtitle;
use App\Charts\QMHighcharts\Options\BaseTitle;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\Options\BaseXAxis;
use App\Charts\QMHighcharts\Options\BaseYAxis;
use App\Charts\QMHighcharts\Options\PlotOptions;
use Ghunti\HighchartsPHP\HighchartJsExpr;
class BaseAjaxLoadedDataClickablePoints extends HighchartConfig {
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
	 * @var BaseYAxis[]
	 * @link https://api.highcharts.com/highcharts/yAxis
	 */
	public $yAxis;
	/**
	 * @var BaseLegend
	 * @link https://api.highcharts.com/highcharts/legend
	 */
	public $legend;
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
		$this->subtitle = new BaseSubtitle();
		$this->xAxis = new BaseXAxis();
		$this->yAxis = [];
		$this->legend = new BaseLegend();
		$this->tooltip = new BaseTooltip();
		$this->plotOptions = new PlotOptions();
		$this->series = [];
		$this->chart->renderTo = 'container';
		$this->title->text = 'Daily visits at www.highcharts.com';
		$this->subtitle->text = 'Source: Google Analytics';
		$this->xAxis->type = 'datetime';
		$this->xAxis->tickInterval = 7 * 24 * 3600 * 1000;
		$this->xAxis->tickWidth = 0;
		$this->xAxis->gridLineWidth = 1;
		$this->xAxis->labels->align = 'left';
		$this->xAxis->labels->x = 3;
		$this->xAxis->labels->y = -3;
		$leftYaxis = new BaseYAxis();
		$leftYaxis->title->text = null;
		$leftYaxis->labels->align = 'left';
		$leftYaxis->labels->x = 3;
		$leftYaxis->labels->y = 16;
		$leftYaxis->labels->formatter =
			new HighchartJsExpr("function() { return Highcharts.numberFormat(this.value, 0);}");
		$leftYaxis->showFirstLabel = false;
		$this->yAxis[] = $leftYaxis;
		$rightYaxis = new BaseYAxis();
		$rightYaxis->linkedTo = 0;
		$rightYaxis->gridLineWidth = 0;
		$rightYaxis->opposite = true;
		$rightYaxis->title->text = null;
		$rightYaxis->labels->align = 'right';
		$rightYaxis->labels->x = -3;
		$rightYaxis->labels->y = 16;
		$rightYaxis->labels->formatter =
			new HighchartJsExpr("function() { return Highcharts.numberFormat(this.value, 0);}");
		$rightYaxis->showFirstLabel = false;
		$this->yAxis[] = $rightYaxis;
		// The yAxis can also be an array of non-associative arrays
		/*
		 * $this->yAxis = array(array('title' => array('text' => null), 'labels' =>
		 * array('align' => 'left', 'x' => 3, 'y' => 16, 'formatter' => new
		 * HighchartJsExpr("function() { return Highcharts.numberFormat(this.value,
		 * 0);}")), 'showFirstLabel' => false), array('linkedTo' => 0, 'gridLineWidth'
		 * => 0, 'opposite' => true, 'title' => array('text' => null), 'labels' =>
		 * array('align' => 'right', 'x' => -3, 'y' => 16, 'formatter' => new
		 * HighchartJsExpr("function() { return Highcharts.numberFormat(this.value,
		 * 0);}")) ));
		 */
		$this->legend = [
			'align' => 'left',
			'verticalAlign' => 'top',
			'y' => 20,
			'floating' => true,
			'borderWidth' => 0,
		];
		$this->tooltip = [
			'shared' => true,
			'crosshairs' => true,
		];
		$clickFunction = new HighchartJsExpr("function() {
		        hs.htmlExpand(null, {
		            pageOrigin: {
		                x: this.pageX,
		                y: this.pageY
		            },
		            headingText: this.series.name,
		            maincontentText: Highcharts.dateFormat('%A, %b %e, %Y', this.x) +':<br/> '+
		                this.y +' visits',
		            width: 200
		        });
		    }");
		$this->plotOptions->series->cursor = 'pointer';
		$this->plotOptions->series->point->events->click = $clickFunction;
		$this->plotOptions->series->marker->lineWidth = 1;
		$this->series[] = [
			'name' => 'All visits',
			'lineWidth' => 4,
			'marker' => [
				'radius' => 4,
			],
		];
		$this->series[]->name = 'New visitors';
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highcharts/line/ajax_loaded_data_clickable_points.php');
	}
}
