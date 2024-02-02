<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseMapNavigation extends HighchartOption {
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseMapNavigation.enabled
	 */
	public $enabled;
	/**
	 * @var BaseButtonOptions
	 * @link https://api.highcharts.com/highcharts/buttonOptions
	 */
	public $buttonOptions;
	public function __construct(){
		parent::__construct();
		$this->buttonOptions = new BaseButtonOptions();
	}
}
