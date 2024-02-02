<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseMarker extends HighchartOption {
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseMarker.fillColor
	 */
	public $fillColor;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseMarker.lineWidth
	 */
	public $lineWidth;
	/**
	 * @var object
	 * @link https://api.highcharts.com/highcharts/baseMarker.lineColor
	 */
	public $lineColor;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseMarker.enabled
	 */
	public $enabled = true;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseMarker.symbol
	 */
	public $symbol;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseMarker.radius
	 */
	public $radius = 5;
	/**
	 * @var BaseStates
	 * @link https://api.highcharts.com/highcharts/states
	 */
	public $states;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseMarker.enable
	 */
	public $enable;
	public function __construct(){
		parent::__construct();
		//$this->states = new BaseStates();
	}
}
