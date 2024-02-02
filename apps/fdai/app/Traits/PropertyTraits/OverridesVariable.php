<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
trait OverridesVariable {
	/**
	 * @param string $attribute
	 * @return false|float|int|mixed|string
	 */
	public function getAttributeOrFallBackToVariable(string $attribute){
		$variableId = $this->getVariableIdAttribute();
		if(!$variableId){
			return null;
		}
		$val = $this->getDBValue();
		if($val !== null){
			return $val;
		}
		$v = $this->getVariable();
		return $v->getAttribute($attribute);
	}
}
