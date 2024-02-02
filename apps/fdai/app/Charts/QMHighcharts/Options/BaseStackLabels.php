<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseStackLabels extends HighchartOption {
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseStackLabels.enabled
	 */
	public $enabled;
	/**
	 * @var BaseStyle
	 * @link https://api.highcharts.com/highcharts/style
	 */
	public $style;
	public function __construct(){
		parent::__construct();
		//$this->style = new BaseStyle(); Don't set this or it json encodes to empty array and breaks charts
	}
}
