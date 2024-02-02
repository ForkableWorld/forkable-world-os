<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Properties\BaseProperty;
use App\Traits\HasFilter;
use App\Types\BoolHelper;
use Illuminate\Database\Eloquent\Builder;
use App\Fields\Boolean;
use App\Fields\Field;
use App\Fields\Select;
trait IsBoolean {
	use HasFilter;
	/**
	 * Apply the filter to the given query.
	 * @param Builder $query
	 * @param bool $type
	 * @return Builder
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function applyFilter($query, $type){
		/** @var BaseProperty $this */
		if($type === BoolHelper::ALL_STRING){
			return $query;
		}
		if($type === BoolHelper::NULL_STRING){
			$this->applyWhereNull($query);
		}
		if($type === BoolHelper::FALSE_STRING){
			$this->applyWhere($query, "=", 0);
		}
		if($type === BoolHelper::TRUE_STRING){
			$this->applyWhere($query, "=", 1);
		}
		return $query;
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
		return $this->getBooleanField($name, $resolveCallback);
	}
	public function getDBValue(): ?int{
		$p = $this->getParentModel();
		$value = $p->getRawAttribute($this->name); // bypassAccessor bypasses mutation
		return BoolHelper::toDBBool($value);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
		return $this->getBooleanField($name, $resolveCallback);
	}
	public function getExample(): bool{ return true; }
	public function getFilterOptions(): array{
		return [
			"True" => BoolHelper::TRUE_STRING,
			"False" => BoolHelper::FALSE_STRING,
			"All" => BoolHelper::ALL_STRING,
			"Null" => BoolHelper::NULL_STRING,
		];
	}
	/**
	 * @return string
	 */
	public function getHardCodedValue(): string{
		$value = $this->getAccessorValue();
		if($value === false || $value === 0 || $value === "0"){
			return "false";
		}
		if($value === true || $value === 1 || $value === "1"){
			return "true";
		}
		return "null";
	}
	public function getAccessorValue(): ?bool{
		$p = $this->getParentModel();
		$value = $p->getRawAttribute($this->name);
		if($value === false || $value === 0 || $value === "0"){
			return false;
		}
		if($value === true || $value === 1 || $value === "1"){
			return true;
		}
		return null;
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
		return $this->getBooleanField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getUpdateField($resolveCallback = null, string $name = null): Field{
		return $this->getBooleanField($name, $resolveCallback);
	}
	/**
	 * @param string|null $name
	 * @param $resolveCallback
	 * @return Boolean
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected function getBooleanField(?string $name, $resolveCallback): Field{
		if($this->canBeChangedToNull()){
			return $this->getBoolSelectorWithNull($name, $resolveCallback);
		}
		return new Boolean($name ?? $this->getTitleAttribute(), $this->name,
			$resolveCallback ?? function($value, $resource, $attribute){
				return $value;
			});
	}
	/**
	 * @param string|null $name
	 * @param $resolveCallback
	 * @return Select
	 */
	protected function getBoolSelectorWithNull(string $name = null, $resolveCallback = null): Select{
		return Select::make($name ?? $this->getTitleAttribute(), $this->name, $resolveCallback ?? function($value){
				if($value === null){
					return "null";
				}
				if($value){
					return "true";
				}
				return "false";
			})->displayUsing(function($value){
			if($value === null){
				return "Null";
			}
			if($value){
				return "True";
			}
			return "False";
		})->options(function(){
			return [
				"true" => "True",
				"false" => "False",
				"null" => "Null",
			];
		});
	}
	/**
	 * @param $value
	 * @return int|null
	 */
	public function toDBValue($value): ?int{
		return BoolHelper::toDBBool($value);
	}
}
