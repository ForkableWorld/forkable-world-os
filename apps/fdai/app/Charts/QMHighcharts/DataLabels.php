<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseBackgroundColor;
use App\Charts\QMHighcharts\Options\BaseDataLabels;
class DataLabels extends BaseDataLabels {
	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct(){
		//$this->style = new BaseStyle(); Don't set this or it json encodes to empty array and breaks charts
		$this->backgroundColor = new BaseBackgroundColor();
		$this->enabled = true;
	}
}
