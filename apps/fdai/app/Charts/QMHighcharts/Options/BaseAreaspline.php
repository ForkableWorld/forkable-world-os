<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseAreaspline extends HighchartOption {
	/**
	 * @var float
	 * @link https://api.highcharts.com/highcharts/baseAreaspline.fillOpacity
	 */
	public $fillOpacity;
	public function __construct(){
		parent::__construct();
	}
}
