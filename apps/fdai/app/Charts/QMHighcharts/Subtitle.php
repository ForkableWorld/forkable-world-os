<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseStyle;
use App\Charts\QMHighcharts\Options\BaseSubtitle;
class Subtitle extends BaseSubtitle {
	public function setColor(string $color){
		le("Text color should be set in theme!");
		$this->getStyle()->color = $color;
	}
	/**
	 * @return BaseStyle
	 */
	public function getStyle(): BaseStyle{
		le("Text color should be set in theme!");
		if(!$this->style instanceof BaseStyle){
			$this->style = BaseStyle::instantiateIfNecessary($this->style);
		}
		return $this->style;
	}
}
