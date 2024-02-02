<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseLinearGradient extends HighchartOption {
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseLinearGradient.x1
	 */
	public $x1;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseLinearGradient.y1
	 */
	public $y1;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseLinearGradient.x2
	 */
	public $x2;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseLinearGradient.y2
	 */
	public $y2;
	public function __construct(){
		parent::__construct();
	}
}
