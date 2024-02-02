<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseFillColor;
class Area extends HighchartOption {
	/**
	 * @var float
	 * @link https://api.highcharts.com/highcharts/baseArea.fillOpacity
	 */
	public $fillOpacity;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseArea.pointStart
	 */
	public $pointStart;
	/**
	 * @var Marker
	 * @link https://api.highcharts.com/highcharts/marker
	 */
	public $marker;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseArea.stacking
	 */
	public $stacking;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseArea.lineColor
	 */
	public $lineColor;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseArea.lineWidth
	 */
	public $lineWidth;
	/**
	 * @var BaseFillColor
	 * @link https://api.highcharts.com/highcharts/fillColor
	 */
	public $fillColor;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseArea.shadow
	 */
	public $shadow;
	/**
	 * @var States
	 * @link https://api.highcharts.com/highcharts/states
	 */
	public $states;
	public function __construct(){
		parent::__construct();
		//$this->marker = new Marker();
		//$this->fillColor = new BaseFillColor();
		//$this->states = new States();
	}
}
