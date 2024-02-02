<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\ModelTraits;
use App\Buttons\QMButton;
use App\Buttons\RelationshipButtons\Measurement\MeasurementConnectorButton;
use App\Buttons\RelationshipButtons\Measurement\MeasurementUnitButton;
use App\Buttons\RelationshipButtons\Measurement\MeasurementUserVariableButton;
use App\Buttons\RelationshipButtons\Measurement\MeasurementVariableCategoryButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Buttons\States\MeasurementAddStateButton;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\InvalidVariableValueException;
use App\Models\Measurement;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\QMUnit;
use App\Traits\HasModel\HasUserVariable;
use App\Traits\HasModel\HasVariableCategory;
use App\Traits\HasTime;
use App\Traits\IsEditable;
use App\Types\TimeHelper;
use App\Utils\Stats;
use App\Utils\UrlHelper;
use App\Variables\QMVariable;
trait MeasurementTrait {
	use IsEditable, HasTime, HasVariableCategory, HasUserVariable;
	public $valueInUserUnit;
	public $valueInCommonUnit;
	public function getStartTime(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Measurement::FIELD_START_TIME] ?? null;
		} else{
			/** @var QMMeasurement $this */
			return $this->startTime;
		}
	}
	public function getIonicEditButton(): QMButton{
		$b = new MeasurementAddStateButton($this);
		$b->setTextAndTitle($this->getValueUnitTime());
		$b->setTooltip("Edit or Delete This Measurement");
		return $b;
	}
	public function getEditButton(): QMButton{
		return $this->getIonicEditButton();
	}
	public function getUrlParams(): array{
		$arr = [
			Measurement::FIELD_UNIT_ID => $this->getUnitIdAttribute(),
			Measurement::FIELD_VARIABLE_ID => $this->getUnitIdAttribute(),
			Measurement::FIELD_VALUE => $this->getUnitIdAttribute(),
			Measurement::FIELD_START_AT => $this->getStartAtAttribute(),
			Measurement::FIELD_VARIABLE_CATEGORY_ID => $this->getUnitIdAttribute(),
		];
		try {
			$arr[Measurement::FIELD_ID] = $this->getId();
		} catch (\Exception $e) {
			//
		}
		return $arr;
	}
	/**
	 * @param array $params
	 * @return MeasurementAddStateButton
	 */
	public function getButton(array $params = []): QMButton{
		$b = new MeasurementAddStateButton($this);
		if($params){
			$b->parameters = $params;
		}
		return $b;
	}
	/**
	 * @param bool $useAbbreviatedName
	 * @param int $sigFigs
	 * @return string
	 */
	public function getValueUnitTime(bool $useAbbreviatedName = false, int $sigFigs = 3): string{
		$valueUnit = $this->getValueUnitString($useAbbreviatedName, $sigFigs) . " at " . $this->getHumanAt();
		return $valueUnit;
	}
	public function getAt(): string{
		return $this->getStartAtAttribute();
	}
	public function getFontAwesome(): string{
		return $this->getVariable()->getFontAwesome();
	}
	public function getImage(): string{
		return $this->getVariable()->getImage();
	}
	public function getUrl(array $params = []): string{
		return $this->getButton()->getUrl($params);
	}
	public function getStartSince(): string{
		return TimeHelper::timeSinceHumanString($this->getStartTime());
	}
	/**
	 * @param bool $useAbbreviatedName
	 * @param int $sigFigs
	 * @return string
	 */
	public function getValueUnitString(bool $useAbbreviatedName = false, int $sigFigs = 3): string{
		$v = Stats::roundByNumberOfSignificantDigits($this->value, $sigFigs);
		$u = $this->getQMUnit();
		return $u->getValueAndUnitString($v, $useAbbreviatedName);
	}
	public function getMillis(): int{
		return 1000 * $this->getStartTime();
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		$arr = [];
		$arr[] = new MeasurementVariableCategoryButton($this);
		$arr[] = new MeasurementUnitButton($this);
		$arr[] = new MeasurementUserVariableButton($this);
		if($this->connector_id){
			$arr[] = new MeasurementConnectorButton($this);
		}
		return $arr;
	}
	public function getSortingScore(): float{
		return $this->getStartTime();
	}
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
		$v = $this->getVariable();
		$v->validateValueForCommonVariableAndUnit($val, static::class, $this->duration, $v);
	}
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
				"value must be at most a maximum of $max $commonUnit->abbreviatedName but is $val $commonUnit->abbreviatedName";
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
		throw new InvalidVariableValueAttributeException($this->l(), Measurement::FIELD_VALUE, $this->getDBValue(),
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
