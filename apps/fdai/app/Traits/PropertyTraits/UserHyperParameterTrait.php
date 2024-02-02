<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Exceptions\InvalidAttributeException;
trait UserHyperParameterTrait {
	use IsHyperParameter, OverridesVariable;
	/**
	 * @param string $attribute
	 * @return mixed
	 */
	public function getHyperParameter(string $attribute){
		return $this->getAttributeOrFallBackToVariable($attribute);
	}
	public function validateHyperParameter(){
		$value = $this->getDBValue();
		if($value === null){
			return;
		}
		$this->assertAPIRequest();
		$this->assertParameterNotRedundant();
	}
	/** @noinspection PhpUnusedLocalVariableInspection */
	/**
	 * @throws InvalidAttributeException
	 */
	protected function assertParameterNotRedundant(): void{
		$value = $this->getDBValue();
		$parent = $this->getParentModel();
		$variable = $parent->getVariable();
		$variableValue = $variable->getAttribute($this->name);
		if($variableValue === $value){
			$message = "TODO: should not be the same as common variable value: $variableValue";
			if($throw = false){
				$this->throwException($message);
			} else{
				$this->logWarning($message);
				$this->setRawAttribute(null);
			}
		}
	}
}
