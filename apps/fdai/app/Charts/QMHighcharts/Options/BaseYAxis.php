<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseYAxis extends HighchartOption {
	/**
	 * @var BaseTitle
	 * @link https://api.highcharts.com/highcharts/title
	 */
	public $title;
	/**
	 * @var BaseLabels
	 * @link https://api.highcharts.com/highcharts/labels
	 */
	public $labels;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseYAxis.min
	 */
	public $min;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseYAxis.max
	 */
	public $max;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseYAxis.allowDecimals
	 */
	public $allowDecimals;
	/**
	 * @var BaseStackLabels
	 * @link https://api.highcharts.com/highcharts/stackLabels
	 */
	public $stackLabels;
	/**
	 * @var float
	 * @link https://api.highcharts.com/highcharts/baseYAxis.minPadding
	 */
	public $minPadding;
	/**
	 * @var float
	 * @link https://api.highcharts.com/highcharts/baseYAxis.maxPadding
	 */
	public $maxPadding;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseYAxis.maxZoom
	 */
	public $maxZoom;
	/**
	 * @var BasePlotLines[]
	 * @link https://api.highcharts.com/highcharts/plotLines
	 */
	public $plotLines;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseYAxis.type
	 */
	public $type;
	/**
	 * @var float
	 * @link https://api.highcharts.com/highcharts/baseYAxis.minorTickInterval
	 */
	public $minorTickInterval;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseYAxis.lineWidth
	 */
	public $lineWidth;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseYAxis.showFirstLabel
	 */
	public $showFirstLabel;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseYAxis.minorGridLineWidth
	 */
	public $minorGridLineWidth;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseYAxis.gridLineWidth
	 */
	public $gridLineWidth;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseYAxis.alternateGridColor
	 */
	public $alternateGridColor;
	/**
	 * @var BasePlotBands[]
	 * @link https://api.highcharts.com/highcharts/plotBands
	 */
	public $plotBands;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseYAxis.startOnTick
	 */
	public $startOnTick;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseYAxis.minorTickWidth
	 */
	public $minorTickWidth;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseYAxis.minorTickLength
	 */
	public $minorTickLength;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseYAxis.minorTickPosition
	 */
	public $minorTickPosition;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseYAxis.minorTickColor
	 */
	public $minorTickColor;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseYAxis.tickPixelInterval
	 */
	public $tickPixelInterval;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseYAxis.tickWidth
	 */
	public $tickWidth;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseYAxis.tickPosition
	 */
	public $tickPosition;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseYAxis.tickLength
	 */
	public $tickLength;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseYAxis.tickColor
	 */
	public $tickColor;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseYAxis.tickInterval
	 */
	public $tickInterval;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseYAxis.lineColor
	 */
	public $lineColor;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseYAxis.offset
	 */
	public $offset;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseYAxis.endOnTick
	 */
	public $endOnTick;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseYAxis.gridLineInterpolation
	 */
	public $gridLineInterpolation;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseYAxis.pane
	 */
	public $pane;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseYAxis.showLastLabel
	 */
	public $showLastLabel;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseYAxis.reversed
	 */
	public $reversed;
	public function __construct(){
		parent::__construct();
		$this->title = new BaseTitle();
		//$this->labels = new BaseLabels();
		//$this->stackLabels = new BaseStackLabels();
		//$this->plotLines = [];
		//$this->plotBands = [];
	}
	/**
	 * @return BaseTitle
	 */
	public function getBaseTitle(): BaseTitle{
		$this->title = BaseTitle::instantiateIfNecessary($this->title);
		return $this->title;
	}
}
