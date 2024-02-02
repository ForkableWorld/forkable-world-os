<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseNavigation extends HighchartOption {
	/**
	 * @var BaseMenuItemStyle
	 * @link https://api.highcharts.com/highcharts/menuItemStyle
	 */
	public $menuItemStyle;
	public function __construct(){
		parent::__construct();
		$this->menuItemStyle = new BaseMenuItemStyle();
	}
}
