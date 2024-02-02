<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseGauge extends HighchartOption {
	/**
	 * @var BaseDataLabels
	 * @link https://api.highcharts.com/highcharts/dataLabels
	 */
	public $dataLabels;
	/**
	 * @var BaseDial
	 * @link https://api.highcharts.com/highcharts/dial
	 */
	public $dial;
	public function __construct(){
		parent::__construct();
		$this->dataLabels = new BaseDataLabels();
		$this->dial = new BaseDial();
	}
}
