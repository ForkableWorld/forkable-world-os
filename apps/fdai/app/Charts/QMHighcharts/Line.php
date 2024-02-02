<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseLine;
class Line extends BaseLine {
	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct(){
		$this->dataLabels = new DataLabels();
		$this->enableMouseTracking = true;
	}
}
