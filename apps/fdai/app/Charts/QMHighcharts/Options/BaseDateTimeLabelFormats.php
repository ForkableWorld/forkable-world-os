<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseDateTimeLabelFormats extends HighchartOption {
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseDateTimeLabelFormats.month
	 */
	public $month;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseDateTimeLabelFormats.year
	 */
	public $year;
	public function __construct(){
		parent::__construct();
	}
}
