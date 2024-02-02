<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
use App\Charts\QMHighcharts\Options\BaseStops;
class BasePlotBackgroundColor extends HighchartOption {
	/**
	 * @var BaseLinearGradient
	 * @link https://api.highcharts.com/highcharts/linearGradient
	 */
	public $linearGradient;
	/**
	 * @var BaseStops[]
	 * @link https://api.highcharts.com/highcharts/stops
	 */
	public $stops;
	public function __construct(){
		parent::__construct();
		$this->linearGradient = new BaseLinearGradient();
		$this->stops = [];
	}
}
