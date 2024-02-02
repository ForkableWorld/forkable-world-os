<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseTitle;
class Title extends BaseTitle {
	public function __construct(){
		parent::__construct();
		$this->enabled = true;
	}
}
