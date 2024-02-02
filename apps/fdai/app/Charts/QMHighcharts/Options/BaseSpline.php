<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseSpline extends HighchartOption {
	/**
	 * @var BaseMarker
	 * @link https://api.highcharts.com/highcharts/marker
	 */
	public $marker;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseSpline.lineWidth
	 */
	public $lineWidth;
	/**
	 * @var BaseStates
	 * @link https://api.highcharts.com/highcharts/states
	 */
	public $states;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseSpline.pointInterval
	 */
	public $pointInterval;
	/**
	 * @var object
	 * @link https://api.highcharts.com/highcharts/baseSpline.pointStart
	 */
	public $pointStart;
	public function __construct(){
		parent::__construct();
		$this->marker = new BaseMarker();
		$this->states = new BaseStates();
	}
}
