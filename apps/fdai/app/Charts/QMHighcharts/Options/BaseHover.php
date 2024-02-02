<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseHover extends HighchartOption {
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseHover.enabled
	 */
	public $enabled;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseHover.lineWidth
	 */
	public $lineWidth;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseHover.symbol
	 */
	public $symbol;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseHover.radius
	 */
	public $radius;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseHover.lineColor
	 */
	public $lineColor;
	/**
	 * @var BaseMarker
	 * @link https://api.highcharts.com/highcharts/marker
	 */
	public $marker;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseHover.color
	 */
	public $color;
	public function __construct(){
		parent::__construct();
		//$this->marker = new BaseMarker();
	}
}
