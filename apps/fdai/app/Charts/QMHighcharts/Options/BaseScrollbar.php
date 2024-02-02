<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseScrollbar extends HighchartOption {
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseScrollbar.liveRedraw
	 */
	public $liveRedraw;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseScrollbar.enabled
	 */
	public $enabled;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseScrollbar.barBackgroundColor
	 */
	public $barBackgroundColor;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseScrollbar.barBorderRadius
	 */
	public $barBorderRadius;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseScrollbar.barBorderWidth
	 */
	public $barBorderWidth;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseScrollbar.buttonBackgroundColor
	 */
	public $buttonBackgroundColor;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseScrollbar.buttonBorderWidth
	 */
	public $buttonBorderWidth;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseScrollbar.buttonBorderRadius
	 */
	public $buttonBorderRadius;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseScrollbar.trackBackgroundColor
	 */
	public $trackBackgroundColor;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseScrollbar.trackBorderWidth
	 */
	public $trackBorderWidth;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseScrollbar.trackBorderRadius
	 */
	public $trackBorderRadius;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseScrollbar.trackBorderColor
	 */
	public $trackBorderColor;
	public function __construct(){
		parent::__construct();
	}
}
