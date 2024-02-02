<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseNavigator extends HighchartOption {
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseNavigator.adaptToUpdatedData
	 */
	public $adaptToUpdatedData;
	/**
	 * @var BaseSeries
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseNavigator.enabled
	 */
	public $enabled = true;
	public function __construct(){
		parent::__construct();
		//$this->series = new BaseSeries();
	}
}
