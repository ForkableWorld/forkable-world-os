<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
use App\Charts\QMHighcharts\Options\BaseMargin;
class BaseChart extends HighchartOption {
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseChart.type
	 */
	public $type;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseChart.zoomType
	 */
	public $zoomType;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseChart.renderTo
	 */
	public $renderTo;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseChart.spacingBottom
	 */
	public $spacingBottom;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseChart.inverted
	 */
	public $inverted;
	/**
	 * @var BaseMargin[]
	 * @link https://api.highcharts.com/highcharts/margin
	 */
	public $margin;
	/**
	 * @var BaseEvents
	 * @link https://api.highcharts.com/highcharts/events
	 */
	public $events;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseChart.marginRight
	 */
	public $marginRight;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseChart.marginBottom
	 */
	public $marginBottom;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseChart.width
	 */
	public $width;
	/**
	 * @var BaseStyle
	 * @link https://api.highcharts.com/highcharts/style
	 */
	public $style;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseChart.spacingRight
	 */
	public $spacingRight;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseChart.plotBackgroundColor
	 */
	public $plotBackgroundColor;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseChart.plotBackgroundImage
	 */
	public $plotBackgroundImage;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseChart.plotBorderWidth
	 */
	public $plotBorderWidth;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseChart.plotShadow
	 */
	public $plotShadow;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseChart.height
	 */
	public $height;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseChart.alignTicks
	 */
	public $alignTicks;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseChart.polar
	 */
	public $polar;
	/**
	 * @var BaseMapNavigation
	 * @link https://api.highcharts.com/highcharts/mapNavigation
	 */
	public $mapNavigation;
}
