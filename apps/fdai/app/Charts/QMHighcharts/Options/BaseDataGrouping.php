<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
use App\Charts\QMHighcharts\Options\BaseUnits;
class BaseDataGrouping extends HighchartOption {
	/**
	 * @var BaseUnits[]
	 * @link https://api.highcharts.com/highcharts/units
	 */
	public $units;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseDataGrouping.enabled
	 */
	public $enabled;
	public function __construct(){
		parent::__construct();
		$this->units = [];
	}
}
