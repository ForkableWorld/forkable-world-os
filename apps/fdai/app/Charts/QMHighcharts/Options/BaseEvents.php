<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseEvents extends HighchartOption {
	/**
	 * @var object
	 * @link https://api.highcharts.com/highcharts/baseEvents.click
	 */
	public $click;
	/**
	 * @var object
	 * @link https://api.highcharts.com/highcharts/baseEvents.load
	 */
	public $load;
	/**
	 * @var object
	 * @link https://api.highcharts.com/highcharts/baseEvents.afterSetExtremes
	 */
	public $afterSetExtremes;
	public function __construct(){
		parent::__construct();
	}
}
