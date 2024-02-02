<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseArea extends HighchartOption {
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
	 * @var BaseMarker
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
	 * @var BaseStates
	 * @link https://api.highcharts.com/highcharts/states
	 */
	public $states;
	public function __construct(){
		parent::__construct();
		$this->marker = new BaseMarker();
		$this->fillColor = new BaseFillColor();
		$this->states = new BaseStates();
	}
}
