<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseLabels;
class HighchartLabels extends BaseLabels {
	/**
	 * HighchartLabels constructor.
	 * @param $unitName
	 * @param $series
	 */
	public function __construct(string $unitName, HighstockSeries $series){
		parent::__construct();
		$this->style->color = $series->getColor();
		$this->formatter = new HighchartJsExpr("function() {
    return this.value +' $unitName'; }");
	}
}
