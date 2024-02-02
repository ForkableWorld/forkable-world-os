<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\VariableCategoryCharts;
use App\Charts\QMHighcharts\SankeyHighchart;
class VariableCategoriesSankeyHighchart extends SankeyHighchart {
	public function __construct(){
		parent::__construct();
	}
	public function renderOptions(): string{
		return parent::renderOptions();
	}
	protected function generateSeries(){
		$this->addSeriesAndSetColor(new CorrelationNodeSeries());
	}
}
