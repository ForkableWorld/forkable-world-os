<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
trait HardCodableProperty {
	public function setFromHardCodedValue($hardCoded){
		if($hardCoded !== null){
			$this->setAttributeIfDifferentFromAccessor($hardCoded);
		}
	}
}
