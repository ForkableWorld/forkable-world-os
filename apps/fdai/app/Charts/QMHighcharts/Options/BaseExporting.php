<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseExporting extends HighchartOption {
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseExporting.enabled
	 */
	public $enabled;
	public function __construct(){
		parent::__construct();
	}
}
