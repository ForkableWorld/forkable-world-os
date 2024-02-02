<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
trait HardCodedProperty {
	/**
	 * @param $hardCodedValue
	 */
	public function updateFromHardCoded($hardCodedValue){
		if($hardCodedValue !== null){
			$l = $this->getParentModel();
			$l->setAttributeIfDifferentFromAccessor($this->name, $hardCodedValue);
		}
	}
}
