<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseColorAxis extends HighchartOption {
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseColorAxis.min
	 */
	public $min;
	public function __construct(){
		parent::__construct();
	}
}
