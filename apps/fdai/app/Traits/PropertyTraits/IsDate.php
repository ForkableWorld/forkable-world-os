<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Types\TimeHelper;
use App\Fields\Date;
use App\Fields\Field;
trait IsDate {
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
		return $this->getDateField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
		return $this->getDateField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getUpdateField($resolveCallback = null, string $name = null): Field{
		return $this->getDateField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
		return $this->getDateField($name, $resolveCallback);
	}
	public function getExample(): string{ return "2020-12-31"; }
	public function getDBValue(): string{
		$p = $this->getParentModel();
		$val = $p->getRawAttribute($this->name); // bypassAccessor bypasses mutation
		return TimeHelper::YYYYmmddd($val);
	}
	/**
	 * @param string|null $name
	 * @param $resolveCallback
	 * @return Date
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected function getDateField(?string $name, $resolveCallback): Date{
		return (new Date($name ?? $this->getTitleAttribute(), $this->name,
			$resolveCallback ?? function($date, $resource, $attribute){
				return TimeHelper::humanDate(strtotime($date));
			}))->sortable(true);
	}
}
