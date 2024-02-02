<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
class NetworkGraphTooltip extends SankeyTooltip {
	public function __construct(){
		parent::__construct();
		$this->formatter = new HighchartJsExpr("function() {
            //debugger;
            var data = this.point.series.data;
            var i = this.point.index;
            var point = data[i - 1];
            if(!point){
                return this.point.name;
            }
            return point.tooltip;
        }");
	}
}
