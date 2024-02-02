<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Properties\BaseProperty;
use App\Properties\PropertiesGenerator;
use App\Traits\PropertyTraits\IsDateTime;
use App\Traits\PropertyTraits\IsFloat;
use App\Types\QMStr;
trait HasPropertyModels {
	protected $propertyModels = [];
	/**
	 * @return bool
	 */
	public function hasPropertyModels(): bool{
		$shortClass = $this->getShortClassName();
		$absPath = FileHelper::folder_exist("app/Properties/$shortClass");
		return (bool)$absPath;
	}
	/**
	 * @param string $attribute
	 * @return BaseProperty
	 */
	public function getPropertyModel(string $attribute): ?BaseProperty{
		if($fromMem = $this->propertyModels[$attribute] ?? null){
			return $fromMem;
		}
		if(!$this->hasColumn($attribute)){
			return null; // Probably a relationship
		}
		$shortClass = $this->getShortClassName();
		/** @var BaseProperty $class */
		$attrClass = QMStr::toShortClassName($attribute);
		if($attrClass === "ID"){
			$attrClass = "Id";
		}
		/** @var BaseProperty $class */
		$class = "App\Properties\\" . $shortClass . "\\" . $shortClass . $attrClass . "Property";
		if(class_exists($class)){
			try {
				return $this->propertyModels[$attribute] = new $class($this);
			} catch (\Throwable $e) {
				le("Could not instantiate $class in getPropertyModel because " . $e->getMessage());
			}
		} else{
			QMLog::once("getPropertyModel: $class not found");
			//$this->generatePropertyModel($attribute);
		}
		return null;
	}
	/**
	 * @return BaseProperty[]|HasFilter[]
	 */
	public function getPropertyModels(): array{
		if(!$this->hasPropertyModels()){
			return [];
		}
		$fields = $this->getColumns();
		$properties = [];
		foreach($fields as $attribute){
			if($p = $this->getPropertyModel($attribute)){
				$properties[$p->name] = $p;
			} else{
                if($attribute === "time_zone"){
                    le("time_zone is called timezone");
                }
				QMLog::once("No property for $attribute");
			}
		}
		return $properties;
	}
	/**
	 * @return BaseProperty[]|IsFloat[]
	 */
	public function getFloatPropertyModels(): array{
		$properties = $this->getPropertyModels();
		$floats = [];
		foreach($properties as $property){
			if($property->isFloat()){
				$floats[] = $property;
			}
		}
		return $floats;
	}
	/**
	 * @return BaseProperty[]|IsDateTime[]
	 */
	public function getDateTimePropertyModels(): array{
		$properties = $this->getPropertyModels();
		$floats = [];
		foreach($properties as $property){
			if($property->isDateTime()){
				$floats[] = $property;
			}
		}
		return $floats;
	}
	/**
	 * @return BaseProperty[]|HasFilter[]
	 */
	public function getNonIdNumericPropertyModels(): array{
		$properties = $this->getPropertyModels();
		$arr = [];
		foreach($properties as $property){
			if($property->isNonIdNumeric()){
				$arr[$property->name] = $property;
			}
		}
		return $arr;
	}
	/**
	 * @return BaseProperty[]|HasFilter[]
	 */
	public function getIdPropertyModels(): array{
		$properties = $this->getPropertyModels();
		$arr = [];
		foreach($properties as $property){
			if($property->isId()){
				$arr[$property->name] = $property;
			}
		}
		return $arr;
	}
	/**
	 * @return BaseProperty[]|HasFilter[]
	 */
	public function getNumericPropertyModels(): array{
		$properties = $this->getPropertyModels();
		$arr = [];
		foreach($properties as $property){
			if($property->isNumeric()){
				$arr[$property->name] = $property;
			}
		}
		$props = collect($arr)->sort(function($prop){
			/** @var BaseProperty $prop */
			return $prop->getTitleAttribute();
		});
		return $props->all();
	}
	/**
	 * @return BaseProperty[]|HasFilter[]
	 */
	public function getStringPropertyModels(): array{
		$properties = $this->getPropertyModels();
		$arr = [];
		foreach($properties as $property){
			if($property->isString()){
				$arr[$property->name] = $property;
			}
		}
		$props = collect($arr)->sort(function($prop){
			/** @var BaseProperty $prop */
			return $prop->getTitleAttribute();
		});
		return $props->all();
	}
	/**
	 * @return BaseProperty[]
	 */
	public function getForeignKeyPropertyModels(): array{
		$properties = $this->getPropertyModels();
		$foreign = [];
		foreach($properties as $p){
			if(method_exists($p, 'getForeignClass')){
				$foreign[] = $p;
			}
		}
		return $foreign;
	}
	/**
	 * @return BaseProperty[]
	 */
	public function getNonForeignKeyPropertyModels(): array{
		$properties = $this->getPropertyModels();
		$foreign = [];
		foreach($properties as $p){
			if(!method_exists($p, 'getForeignClass')){
				$foreign[] = $p;
			}
		}
		return $foreign;
	}
	/**
	 * @return BaseProperty[]
	 */
	public function getModifiedPropertyModels(): array{
		if(!$this->hasPropertyModels()){
			return [];
		}
		if($this->exists){
			$dirty = $this->getDirty();
		} else {
			$dirty = $this->attributes;
		}
		$modified = [];
		foreach($dirty as $attribute => $value){
			if($p = $this->getPropertyModel($attribute)){
				$modified[] = $p;
			} else{
                if($attribute === "time_zone"){
                    le("time_zone is called timezone");
                }
				QMLog::once("No property for $attribute");
			}
		}
		return $modified;
	}
	/**
	 * @return BaseProperty
	 */
	public function getIdPropertyModel(): BaseProperty{
		return $this->getPropertyModel($this->getPrimaryKey());
	}
	public static function generateProperties(string $connectionName = null, string $column = null,
		bool $overwrite = false): PropertiesGenerator{
		if(!$connectionName){
            $me = new static;
			$connectionName = $me->getConnectionName() ?? config('database.default');
		}
		$generator = new PropertiesGenerator(static::TABLE, [], $connectionName);
		$generator->generatePropertyModelCodeFiles($column, $overwrite);
		return $generator;
	}
}
