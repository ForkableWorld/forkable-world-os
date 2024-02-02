<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\BaseModel;
use App\Fields\Field;
use App\Fields\KeyValue;
trait IsJsonEncoded {
	use IsString;
	abstract public function getParentModel(): BaseModel;
	public function getDBValue(): ?string{
		$p = $this->getParentModel();
		$val = $p->getRawAttribute($this->name); // bypassAccessor bypasses mutation
		if($val && !is_string($val)){
			return json_encode($val);
		}
		return $val;
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getUpdateField($resolveCallback = null, string $name = null): Field{
		return $this->getKeyValueField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
		return $this->getKeyValueField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
		return $this->getKeyValueField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
		return $this->getKeyValueField($name, $resolveCallback);
	}
	/**
	 * @param string|null $name
	 * @param $resolveCallback
	 * @return KeyValue
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected function getKeyValueField(?string $name, $resolveCallback): KeyValue{
		$f = (new KeyValue($name ?? $this->getTitleAttribute(), $this->name,
			$resolveCallback ?? function($value, $resource, $attribute){
				if(is_array($value) && isset($value[0])){
					$value[count($value)] = $value[0];
					unset($value[0]); // For some reason Astral will never return the element with index 0
				}
				return $value;
			}))->rules('json');
		return $f;
	}
}
