<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
class Label {
	//  Series must be like:  data: [{ y: 29.9, id: 'min' }, 71.5]
	public $align = 'center';
	public $verticalAlign = 'top';
	public $point; //  The id of the point i.e. '2';
	public $distance; // i.e. 20;
	public $text; // i.e. "Max"
	public $backgroundColor = 'white';
	/**
	 * @var HighchartJsExpr
	 */
	public $x;
}
