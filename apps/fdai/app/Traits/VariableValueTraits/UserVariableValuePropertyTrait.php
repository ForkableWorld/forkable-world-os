<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\VariableValueTraits;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueException;
use App\Models\UserVariable;
trait UserVariableValuePropertyTrait {
	public $inUserUnit;
	/**
	 * @return float|null
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function inUserUnit(): ?float{
		$inCommonUnit = $this->inCommonUnit();
		if($inCommonUnit === null){
			return $inCommonUnit;
		}
		if(is_string($inCommonUnit) && stripos($inCommonUnit, 'infinity') !== false){
			return null;
		}
		return $this->inUserUnit = $this->toUserUnit($inCommonUnit);
	}
	public function inCommonUnit(): ?float{
		$inCommonUnit = $this->getDBValue();
		if(is_string($inCommonUnit) && stripos($inCommonUnit, 'infinity') !== false){
			return null;
		}
		return $inCommonUnit;
	}
	/**
	 * @param float $inCommonUnit
	 * @return float
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function toUserUnit($inCommonUnit): ?float{
		if($inCommonUnit === null){
			return null;
		}
		/** @var UserVariable $uv */
		$uv = $this->getUserVariable();
		return $uv->toUserUnit($inCommonUnit);
	}
	/**
	 * @param float $inUserUnit
	 * @return float
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function toCommonUnit($inUserUnit): ?float{
		if($inUserUnit === null){
			return null;
		}
		/** @var UserVariable $uv */
		$uv = $this->getUserVariable();
		return $uv->toCommonUnit($inUserUnit);
	}
	/**
	 * @param $inUserUnit
	 * @return float
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function setOriginalValueAndConvertToDBValue($inUserUnit): ?float{
		return $this->toCommonUnit($inUserUnit);
	}
	/**
	 * @param $data
	 * @return float|null
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function pluckInCommonUnit($data): ?float{
		$inUserUnit = $this->pluckOrDefault($data);
		return $this->toCommonUnit($inUserUnit);
	}
}
