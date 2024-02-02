<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Fields\Field;
use App\Fields\Number;
use App\Utils\Stats;
trait IsFloat {
	use IsNumeric;
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getUpdateField($resolveCallback = null, string $name = null): Field{
		return $this->getFloatField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
		return $this->getFloatField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
		return $this->getFloatField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
		return $this->getFloatField($name, $resolveCallback);
	}
	public function getExample(): ?float{
		if($this->example !== null){
			return $this->example;
		}
		$val = 140;
		$max = $this->getMaximum();
		$min = $this->getMinimum();
		if($max !== null){
			$val = $max - 1;
		}
		if($min !== null){
			$val = $min + 1;
		}
		if($min !== null && $max !== null){
			$val = ($min + $max) / 2;
		}
		return $this->example = (float)$val;
	}
	/**
	 * @param string|null $name
	 * @param $resolveCallback
	 * @return Number
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected function getFloatField(?string $name, $resolveCallback): Number{
		$f = (new Number($name ?? $this->getTitleAttribute(), $this->name,
			$resolveCallback ?? function($value, $resource, $attribute){
				$formatted = $this->formatFloat($value);
				return $formatted;
			}))->sortable(true);
		$this->addMinMaxStepToField($f);
		return $f;
	}
	/**
	 * @param $val
	 * @return false|null|string
	 */
	protected function formatFloat($val){
		if($val === null){
			return null;
		}
		$sigFigs = $this->getSigFigs();
		$rounded = Stats::roundByNumberOfSignificantDigits($val, $sigFigs);
		// What was this for?  It rounds to int? $rounded = number_format($rounded);
		// TODO: Fix rounding. Tried number_format to prevent -0.08000000000000002 but it rounds
		$str = (string)$rounded;
		if(strlen($str) > 2 * $sigFigs){
			$str = substr($str, 0, 2 * $sigFigs);
		}
		return $str;
	}
	public function getDBValue(): ?float{
		$val = parent::getDBValue();
		if(is_string($val)){
			$val = (float)str_replace(",", "", $val);
		}
		return $val;
	}
}
