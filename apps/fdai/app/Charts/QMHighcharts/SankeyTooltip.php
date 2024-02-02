<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
class SankeyTooltip extends NodeTooltip {
	public function __construct(){
		parent::__construct();
		// headerFormat won't show point.name for some reason so just use html in pointFormat
		// https://api.highcharts.com/highcharts/series.sankey.tooltip.headerFormat
		$this->headerFormat = null;
		$this->pointFormat = null;
		$this->formatter = new HighchartJsExpr("function() {
            //debugger;
            var data = this.point.series.data;
            var i = this.point.index;
            var point = data[i];
            if(!point){
                return this.point.name;
            }
            return point.tooltip;
        }");
	}
}
