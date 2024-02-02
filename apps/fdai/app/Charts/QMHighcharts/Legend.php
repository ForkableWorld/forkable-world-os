<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseLegend;
class Legend extends BaseLegend {
	/**
	 * Legend constructor.
	 */
	public function __construct(){
		parent::__construct();
		$this->enabled = true;
	}
}
