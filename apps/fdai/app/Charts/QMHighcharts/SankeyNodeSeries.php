<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
class SankeyNodeSeries extends NodeSeries {
	/**
	 * @param $name
	 */
	public function __construct($name){
		parent::__construct($name);
		$this->type = SankeyHighchart::TYPE;
	}
}
