<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\VariableValueTraits;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\InvalidVariableValueException;
use App\Models\Variable;
use App\Slim\Model\QMUnit;
use App\Traits\PropertyTraits\IsFloat;
use App\Variables\QMVariable;
trait VariableValueTrait {
	use IsFloat;
	public $valueInUserUnit;
	public $valueInCommonUnit;
	/**
	 * @return float
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	abstract function getDBValue();
	/**
	 * @throws InvalidVariableValueException
	 */
	public function validateForCommonVariableAndUnit(){
		$val = $this->getValueInCommonUnit();
		if($val === null){
			return;
		}
		/** @var QMVariable $v */
		$v = $this->getVariable()->getDBModel();
		$v->validateValueForCommonVariableAndUnit($val, $this->name, null, $v);
	}
	abstract public function getVariable(): Variable;
	//    public function getExample(){
	//        $min = $this->getMinimum();
	//        $max = $this->getMaximum();
	//        if($min !== null & $max !== null){
	//            return ($min + $max)/2;
	//        }
	//        if($min !== null){
	//            return $min;
	//        }
	//        if($max !== null){
	//            return $max;
	//        }
	//        return null;
	//    }
	/** @noinspection PhpUnused */
	public function getMaximum(): ?float{
		$v = $this->getVariable();
		$max = $v->getMaximumAllowedValueAttribute();
		return $max;
	}
	/** @noinspection PhpUnused */
	public function getMinimum(): ?float{
		$v = $this->getVariable();
		$min = $v->getMinimumAllowedValueAttribute();
		return $min;
	}
	public function getValueInCommonUnit(): float{
		return $this->valueInCommonUnit;
	}
	public function getValueInUserUnit(): float{
		if($this->valueInUserUnit){
			return $this->valueInUserUnit;
		}
		return $this->valueInCommonUnit;
	}
	/**
	 * @throws InvalidVariableValueAttributeException
	 * @noinspection PhpUnused
	 */
	protected function validateMin(): void{
		$valueInCommonUnit = $this->getDBValue();
		$min = $this->getMinimum();
		if($min === null){
			return;
		}
		if($valueInCommonUnit === null){
			return;
		}
		if($valueInCommonUnit < $min){
			$commonUnit = $this->getCommonQMUnit();
			$v = $this->getVariable();
			$variableName = $v->name;
			$message =
				"$variableName must be at least a minimum of $min $commonUnit->abbreviatedName but is $valueInCommonUnit $commonUnit->abbreviatedName";
			//$this->throwException($message);
			$this->throwInvalidVariableValueException($message);
		}
	}
	/**
	 * @return void
	 * @throws InvalidVariableValueAttributeException
	 * @noinspection PhpUnused
	 */
	protected function validateMax(): void{
		$max = $this->getMaximum();
		if($max === null){
			return;
		}
		$val = $this->getDBValue();
		if($val === null){
			return;
		}
		if($val > $max){
			$commonUnit = $this->getCommonQMUnit();
			$message =
				"$this->name must be at most a maximum of $max $commonUnit->abbreviatedName but is $val $commonUnit->abbreviatedName";
			$this->throwInvalidVariableValueException($message);
		}
	}
	public function getCommonQMUnit(): QMUnit{
		return $this->getVariable()->getCommonUnit();
	}
	/**
	 * @param string $message
	 * @throws InvalidVariableValueAttributeException
	 */
	protected function throwInvalidVariableValueException(string $message){
		throw new InvalidVariableValueAttributeException($this->getParentModel(), $this->name, $this->getDBValue(),
			$message);
	}
	/**
	 * @throws InvalidVariableValueAttributeException
	 * @throws InvalidAttributeException
	 */
	public function validateValue(){
		$this->validateMax();
		$this->validateMin();
	}
}
