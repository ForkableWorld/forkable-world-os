<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Exceptions\InvalidAttributeException;
use App\Properties\BaseProperty;
use App\Types\TimeHelper;
trait IsTemporalTwin {
	use IsTemporal;
	abstract public function getTwinTimeAttribute(): string;
	/**
	 * @throws InvalidAttributeException
	 */
	public function validateTwinTime(): void{
		if($twin = $this->getTwinTimeAttribute()){
			$fromTwin = $this->getDateTimeFromTwin();
			$me = $this->getDateTime();
			if($me !== $fromTwin){
				$this->throwException("\n\t\t$me does not equal value from $twin:\n\t\t$fromTwin");
			}
		}
	}
	/**
	 * @throws InvalidAttributeException
	 */
	public function validateTime(){
		if(!$this->shouldValidate()){
			return;
		}
		$value = $this->getDBValue();
		$required = $this->cannotBeChangedToNull();
		if(!$required && $value === null){
			return;
		}
		$this->assertNotTooLate();
		$this->assertNotTooEarly();
		$this->validateTwinTime();
	}
	public function getUnixTimeFromTwin(): ?int{
		$twin = $this->getTwinTimeAttribute();
		if(!$twin){
			return null;
		}
		$value = $this->getParentAttribute($twin);
		return TimeHelper::universalConversionToUnixTimestamp($value);
	}
	public function getDateTimeFromTwin(): ?string{
		$twin = $this->getTwinTimeAttribute();
		if(!$twin){
			return null;
		}
		$value = $this->getParentAttribute($twin);
		return db_date($value);
	}
	/**
	 * @param $processed
	 * @return mixed
	 * @noinspection PhpUnused
	 */
	public function setRawAttribute($processed): void{
		$parent = $this->getParentModel();
		$twin = $this->getTwinTimeAttribute();
		$at = ($processed) ? db_date($processed) : null;
		$time = ($processed) ? TimeHelper::universalConversionToUnixTimestamp($processed) : null;
		if($this->isUnixtime()){
			$parent->setRawAttribute($twin, $at);
			$parent->setRawAttribute($this->name, $time);
		} else{
			$parent->setRawAttribute($twin, $time);
			$parent->setRawAttribute($this->name, $at);
		}
	}
	/**
	 * @return int
	 */
	public function getExample(){
		if($this->isUnixtime()){
			return $this->getExampleUnixTime();
		}
		return $this->getTwinProperty()->getExampleUnixTime();
	}
	/**
	 * @return IsTemporalTwin
	 * @noinspection PhpDocSignatureInspection
	 */
	public function getTwinProperty(): BaseProperty{
		$p = $this->getParentModel();
		return $p->getPropertyModel($this->getTwinTimeAttribute());
	}
}
