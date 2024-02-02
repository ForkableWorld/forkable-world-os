<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseDial extends HighchartOption {
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseDial.radius
	 */
	public $radius;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseDial.baseWidth
	 */
	public $baseWidth;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseDial.baseLength
	 */
	public $baseLength;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseDial.rearLength
	 */
	public $rearLength;
	public function __construct(){
		parent::__construct();
	}
}
