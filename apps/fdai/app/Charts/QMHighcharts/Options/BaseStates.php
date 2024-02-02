<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseStates extends HighchartOption {
	/**
	 * @var BaseHover
	 * @link https://api.highcharts.com/highcharts/hover
	 */
	public $hover;
	public function __construct(){
		parent::__construct();
		//$this->hover = new BaseHover();
	}
}
