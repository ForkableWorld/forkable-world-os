<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseRadialGradient extends HighchartOption {
	/**
	 * @var float
	 * @link https://api.highcharts.com/highcharts/baseRadialGradient.cx
	 */
	public $cx;
	/**
	 * @var float
	 * @link https://api.highcharts.com/highcharts/baseRadialGradient.cy
	 */
	public $cy;
	/**
	 * @var float
	 * @link https://api.highcharts.com/highcharts/baseRadialGradient.r
	 */
	public $r;
	public function __construct(){
		parent::__construct();
	}
}
