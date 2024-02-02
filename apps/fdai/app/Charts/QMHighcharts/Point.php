<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
class Point {
	public $x; // Always visible. Relative to visible chart canvas regardless of zoom
	public $y;  // Always visible. Relative to visible chart canvas regardless of zoom
	public $xAxis;
	public $yAxis;
}
