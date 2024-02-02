<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Fields\Field;
use App\Fields\Number;
use App\Logging\QMLog;
trait IsInt {
	use IsNumeric;
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getUpdateField($resolveCallback = null, string $name = null): Field{
		return $this->getIntField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
		return $this->getIntField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
		return $this->getIntField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
		return $this->getIntField($name, $resolveCallback);
	}
	/**
	 * @return mixed
	 * @noinspection PhpReturnDocTypeMismatchInspection
	 * Don't change the return type or PHPStorm inspection it will require that all examples be int
	 */
	public function getExample(){
		if(is_string($this->example)){
			return $this->example;
		}
		if($this->example !== null){
			return $this->example;
		}
		if($this->isPrimary){
			return null;
		} // The database creates this
		$val = 140;
		if($max = $this->getMaximum()){
			$val = $max - 1;
		}
		if($min = $this->getMinimum()){
			$val = $min + 1;
		}
		return $val; // Don't set example or PHPStorm inspection it will require that all examples be int
	}
	/**
	 * @param string|null $name
	 * @param $resolveCallback
	 * @return Number
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected function getIntField(?string $name, $resolveCallback): Number{
		$f = (new Number($name ?? $this->getTitleAttribute(), $this->name,
			$resolveCallback ?? function($value, $resource, $attribute){
				return $this->formatInt($value);
			}))->sortable(true);
		$this->addMinMaxStepToField($f);
		return $f;
	}
	/**
	 * @param $val
	 * @return null|string
	 */
	protected function formatInt($val){
		if($val === null){
			return null;
		}
		return number_format($val);
	}
	public function getDBValue(): ?int{
		$val = parent::getDBValue();
		if(is_string($val)){
			$val = (int)str_replace(",", "", $val);
		}
		if(is_float($val)){
			$val = (int)$val;
		}
		if($val && !is_int($val)){
			le("$this->name should be an int but is: ".QMLog::print_r($val));
		}
		return $val;
	}
}
