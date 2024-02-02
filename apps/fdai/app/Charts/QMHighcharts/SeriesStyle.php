<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseStyle;
class SeriesStyle extends BaseStyle {
	public $color;
	public $fontSize;
	/**
	 * SeriesStyle constructor.
	 * @param string $textColor
	 */
	public function __construct(string $textColor){
		$this->color = $textColor;
		$this->fontSize = '16px';
	}
}
