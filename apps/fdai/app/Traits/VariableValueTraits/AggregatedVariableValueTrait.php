<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\VariableValueTraits;
use App\Exceptions\InvalidVariableValueException;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Variables\QMVariable;
trait AggregatedVariableValueTrait {
	use VariableValueTrait;
	/**
	 * @throws InvalidVariableValueException
	 */
	public function validateForCommonVariableAndUnit(){
		$val = $this->getDBValue();
		if($val === null){
			return;
		}
		/** @var QMVariable $v */
		$v = $this->getVariable()->getDBModel();
		$v->validateValueForCommonVariableAndUnit($val, $this->name, $this->getDuration(), $v);
	}
	/**
	 * @return int
	 */
	abstract public function getDuration(): int;
	/** @noinspection PhpUnused */
	public function getMaximum(): ?float{
		$v = $this->getVariable();
		$max = $v->getMaximumAllowedValueAttribute();
		if(!$max){
			return null;
		}
		$days = $this->getDuration() / 86400;
		if($days < 1){
			return $max;
		}
		if($v->getCombinationOperation() === BaseCombinationOperationProperty::COMBINATION_MEAN){
			return $max;
		}
		return $max * $this->getDuration() / 86400;
	}
}
