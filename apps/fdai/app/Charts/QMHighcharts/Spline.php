<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseMarker;
class Spline extends HighchartOption {
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
	 * @var States
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
		//$this->states = new States();
	}
}
