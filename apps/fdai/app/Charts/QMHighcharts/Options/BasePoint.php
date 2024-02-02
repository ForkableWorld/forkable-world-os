<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BasePoint extends HighchartOption {
	/**
	 * @var BaseEvents
	 * @link https://api.highcharts.com/highcharts/events
	 */
	public $events;
	public function __construct(){
		parent::__construct();
		$this->events = new BaseEvents();
	}
}
