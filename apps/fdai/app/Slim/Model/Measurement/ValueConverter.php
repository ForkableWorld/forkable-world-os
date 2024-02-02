<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Measurement;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueException;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Slim\Model\QMUnit;
use App\Traits\LoggerTrait;
use App\Types\ObjectHelper;
use App\Units\YesNoUnit;
use App\Utils\AppMode;
use App\Variables\QMVariable;
/** Holds variable measurement value and allows to convert value to different units
 * @package App\Slim\Model
 */
class ValueConverter {
	use LoggerTrait;
	public $fromValue;
	public $toValue;
	public $fromUnit;
	public $toUnit;
	private $variable;
	/**
	 * @param string|int|QMUnit $unitNameOrId
	 * @return QMUnit
	 */
	public function setToUnit($unitNameOrId): QMUnit{
		if($unitNameOrId instanceof QMUnit){
			return $this->toUnit = $unitNameOrId;
		}
		$unit = QMUnit::getByNameOrId($unitNameOrId);
		return $this->toUnit = $unit;
	}
	/**
	 * @return QMUnit
	 */
	public function getFromUnit(): QMUnit{
		return $this->fromUnit;
	}
	/**
	 * @param QMUnit|string|int $unitNameOrId
	 * @return QMUnit
	 */
	public function setFromUnit($unitNameOrId): QMUnit{
		if($unitNameOrId instanceof QMUnit){
			return $this->fromUnit = $unitNameOrId;
		}
		$unit = QMUnit::getByNameOrId($unitNameOrId);
		return $this->fromUnit = $unit;
	}
	/**
	 * @return float
	 */
	private function convertYesNoToRating(): float{
		return $this->toValue = YesNoUnit::toRating($this->fromValue, $this->getToUnit());
	}
	/**
	 * @return float
	 */
	private function convertRatingToYesNo(): float{
		return $this->toValue = QMUnit::convertToYesNoFromRating($this->fromValue, $this->getFromUnit());
	}
	/**
	 * @param QMVariable|BaseModel $variable
	 * @param int|null $durationInSeconds
	 * @return float
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function convert($variable, int $durationInSeconds = null, bool $validate = true): float{
		$this->variable = $variable;
		$fromUnit = $this->getFromUnit();
		$toUnit = $this->getToUnit();
		if($this->sameUnit()){
			return $this->fromValue;
		}
		if($fromUnit->isYesNo() && $toUnit->isRating()){
			return $this->convertYesNoToRating();
		}
		if($toUnit->isYesNo() && $fromUnit->isRating()){
			return $this->convertRatingToYesNo();
		}
		if($fromUnit->isYesNo() && $toUnit->isCountCategory()){
			return $this->toValue = YesNoUnit::toNumber($this->fromValue);
		}
		if($toUnit->isYesNo() && $fromUnit->isCountCategory()){
			return $this->toValue = QMUnit::convertToYesNoFromCountCategory($this->fromValue);
		}
		if($fromUnit->categoryName !== $toUnit->categoryName){
			$e = new IncompatibleUnitException($fromUnit, $toUnit, $variable);
			if(!AppMode::isProductionApiRequest()){
				throw $e;
			} else{
				$this->logError($e->getProblemAndSolutionString());
			}
			return $this->toValue = $this->fromValue;
		}
		$inStdUnit = self::applyConversionSteps($fromUnit->getConversionSteps(), 'TO_UNIT_CATEGORY_STANDARD_UNIT',
			$this->fromValue);
		$this->toValue =
			self::applyConversionSteps($toUnit->getConversionSteps(), 'FROM_UNIT_CATEGORY_STANDARD_UNIT', $inStdUnit);
		if($validate){
			$this->postConversionValidation($durationInSeconds);
		}
		return $this->toValue;
	}
	/**
	 * @param int|null $durationInSeconds
	 * @throws InvalidVariableValueException
	 */
	private function validateToUnitMaximum(int $durationInSeconds = null){
		$toUnit = $this->getToUnit();
		$fromUnit = $this->getFromUnit();
		$max = $toUnit->maximumValue;
		if($max !== null && (float)$this->toValue > (float)$max){
			$this->toValue = round($this->toValue);
			if((float)$this->toValue == (float)$max){
				return;
			}
			$message =
				"After converting $this->fromValue $fromUnit to $this->toValue $toUnit, it's too big for $toUnit maximum of $max.";
			if(AppMode::isProductionApiRequest()){
				$this->logError($message);
			} else{
				throw new InvalidVariableValueException($message, $this->variable, $durationInSeconds);
			}
		}
		$maxDaily = $toUnit->maximumDailyValue ?? $max;
		if($durationInSeconds && $maxDaily !== null){
			$days = $durationInSeconds / 86400;
			$perDay = $this->toValue / $days;
			if($perDay > $maxDaily){
				$message =
					"After converting $this->fromValue $fromUnit to $this->toValue $toUnit, daily value $perDay too big for $toUnit maximum of $maxDaily.";
				if(AppMode::isProductionApiRequest()){
					$this->logError($message);
				} else{
					throw new InvalidVariableValueException($message, $this->variable, $durationInSeconds);
				}
			}
		}
	}
	/**
	 * @param int|null $durationInSeconds
	 * @throws InvalidVariableValueException
	 */
	private function validateToUnitMinimum(int $durationInSeconds = null){
		$toUnit = $this->getToUnit();
		$min = $toUnit->minimumValue;
		$v = $this->toValue;
		if($min !== null && $v < $min){
			$message = "$v IS too small for UNIT $toUnit->name. ";
			if(AppMode::isProductionApiRequest()){
				$this->logError($message);
			} else{
				throw new InvalidVariableValueException($message, $this->variable, $durationInSeconds);
			}
		}
	}
	/**
	 * @return bool
	 */
	private function sameUnit(): bool{
		if($this->getFromUnit()->id == $this->getToUnit()->id){
			QMLog::debug('Should not convert this value because the source unit is the same as destination unit', [
				'Source Unit' => $this->getFromUnit(),
				'originalValue' => $this->fromValue,
				'Destination Unit' => $this->getToUnit(),
			]);
			return true;
		}
		return false;
	}
	/**
	 * @return QMUnit
	 */
	public function getToUnit(): QMUnit{
		return $this->toUnit;
	}
	/**
	 * Apply conversion steps to convert current value ($this->value) to base value in category or from base value.
	 * @param array $steps
	 * @param string $direction Allowed values are 'TO_UNIT_CATEGORY_STANDARD_UNIT' and
	 *     'FROM_UNIT_CATEGORY_STANDARD_UNIT'
	 * @param float $fromValue
	 * @return float
	 */
	private static function applyConversionSteps(array $steps, string $direction, float $fromValue): float{
		if($direction == 'FROM_UNIT_CATEGORY_STANDARD_UNIT'){
			$steps = array_reverse($steps);
		}
		$toValue = $fromValue;
		foreach($steps as $step){
			$step = ObjectHelper::convertToObject($step);
			$operation = $direction . '_' . $step->operation;
			switch($operation) {
				case 'TO_UNIT_CATEGORY_STANDARD_UNIT_MULTIPLY':
					$toValue *= $step->value;
					break;
				case 'TO_UNIT_CATEGORY_STANDARD_UNIT_ADD':
					$toValue += $step->value;
					break;
				case 'FROM_UNIT_CATEGORY_STANDARD_UNIT_MULTIPLY':
					$toValue /= $step->value;
					break;
				case 'FROM_UNIT_CATEGORY_STANDARD_UNIT_ADD':
					$toValue -= $step->value;
					break;
			}
		}
		return $toValue;
	}
	/**
	 * @param int|null $durationInSeconds
	 * @throws InvalidVariableValueException
	 */
	private function postConversionValidation(int $durationInSeconds = null): void{
		$this->validateToUnitMaximum($durationInSeconds);
		$this->validateToUnitMinimum();
	}
	/**
	 * @param float $fromValue
	 */
	public function setFromValue(float $fromValue): void{
		$this->fromValue = $fromValue;
	}
	public function __toString(){
		return "Converting $this->fromValue from $this->fromUnit to $this->toUnit";
	}
}
