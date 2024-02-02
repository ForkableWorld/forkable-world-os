<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
use App\Charts\QMHighcharts\Options\BaseStops;
class BaseFillColor extends HighchartOption {
	/**
	 * @var BaseLinearGradient[]
	 * @link https://api.highcharts.com/highcharts/linearGradient
	 */
	public $linearGradient;
	/**
	 * @var BaseStops[]
	 * @link https://api.highcharts.com/highcharts/stops
	 */
	public $stops;
	/**
	 * @var BaseRadialGradient
	 * @link https://api.highcharts.com/highcharts/radialGradient
	 */
	public $radialGradient;
	public function __construct(){
		parent::__construct();
		$this->linearGradient = [];
		$this->stops = [];
		$this->radialGradient = new BaseRadialGradient();
	}
}
