<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseLegend extends HighchartOption {
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseLegend.enabled
	 */
	public $enabled;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseLegend.layout
	 */
	public $layout;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseLegend.align
	 */
	public $align;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseLegend.verticalAlign
	 */
	public $verticalAlign;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseLegend.x
	 */
	public $x;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseLegend.y
	 */
	public $y;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseLegend.floating
	 */
	public $floating;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseLegend.borderWidth
	 */
	public $borderWidth;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseLegend.backgroundColor
	 */
	public $backgroundColor;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseLegend.shadow
	 */
	public $shadow;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseLegend.reversed
	 */
	public $reversed;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseLegend.borderColor
	 */
	public $borderColor;
	public function __construct(){
		parent::__construct();
	}
}
