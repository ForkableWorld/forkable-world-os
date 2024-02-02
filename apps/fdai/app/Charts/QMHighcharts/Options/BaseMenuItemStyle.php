<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseMenuItemStyle extends HighchartOption {
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseMenuItemStyle.fontSize
	 */
	public $fontSize;
	public function __construct(){
		parent::__construct();
	}
}
