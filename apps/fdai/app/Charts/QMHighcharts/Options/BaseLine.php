<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseLine extends HighchartOption {
	/**
	 * @var BaseDataLabels
	 * @link https://api.highcharts.com/highcharts/dataLabels
	 */
	public $dataLabels;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseLine.enableMouseTracking
	 */
	public $enableMouseTracking;
	public function __construct(){
		parent::__construct();
		$this->dataLabels = new BaseDataLabels();
	}
}
