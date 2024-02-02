<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseButtons extends HighchartOption {
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseButtons.type
	 */
	public $type;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseButtons.count
	 */
	public $count;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseButtons.text
	 */
	public $text;
	public function __construct(){
		parent::__construct();
	}
}
