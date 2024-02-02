<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Models\BaseModel;
use App\Slim\Model\DBModel;
use App\Slim\Model\StaticModel;
use App\Storage\Memory;
use App\Types\QMArr;
use Illuminate\Support\Collection;
trait HasDBModel {
	/**
	 * @return DBModel
	 */
	abstract public static function getSlimClass(): string;
	/**
	 * @param int|string $id
	 * @return DBModel|null
	 */
	public static function findDBModelInMemory($id): ?StaticModel{
		/** @var DBModel $DBModelClass */
		$DBModelClass = self::getSlimClass();
		return $DBModelClass::findInMemory($id);
	}
	/**
	 * @return DBModel
	 */
	public function getDBModel(): DBModel{
		if($dbm = $this->getDBModelFromMemory()){
			//if($dirty = $this->getDirty()){$this->logDebug("Should we be populating changes here?");}
			return $dbm;
		}
		/** @var DBModel $class */
		$class = self::getSlimClass();
		if(!$class){
			le("Please set SLIM_CLASS for " . $this->getShortClassName());
		}
		$arr = $this->toNonNullArrayWithRelations();
        $dbm = new $class;
		/** @var BaseModel $this */
		$dbm->setLaravelModel($this);
		$dbm->populateFromSnakeCaseArray($arr);
		$this->populateDBModelFromRelations($dbm);
		$dbm->populateByDbFieldNames($arr, false);
		$dbm->populateDefaultFields();
		if($this->hasId()){
			$dbm->addToMemory();
		}
		return $dbm;
	}
	/**
	 * @return array
	 */
	private function toNonNullArrayWithRelations(): array{
		$arr = QMArr::removeNulls($this->toArray());
		/** @var BaseModel $relation */
		foreach($this->relations as $relationName => $relation){ // Give preference to User data
			if(stripos($relationName, "user") !== false){
				$relationArr = $relation->toArray();
				foreach($relationArr as $key => $value){
					$arr[$relationName . "_" . $key] = $value;
				}
				$arr = array_merge(QMArr::removeNulls($relation->toArray()), $arr);
				unset($arr[$relationName]);
			}
		}
		return $arr;
	}
	/**
	 * @param DBModel $DBModel
	 * @param string|null $prefix
	 */
	public function populateDBModelFromRelations(DBModel $DBModel, string $prefix = null){
		/** @var BaseModel $relation */
		foreach($this->relations as $relationName => $relation){
			if(!$prefix){
				$prefix = $relationName;
			}
			$relationArr = $relation->toArray();
			foreach($relationArr as $key => $value){
				$DBModel->setAttributeIfNotSet($prefix . "_" . $key, $value);
			}
		}
	}
	/**
	 * @return DBModel|null
	 */
	public function getDBModelFromMemory(): ?DBModel{
		if($this->hasId()){
			$DBModel = static::findDBModelInMemory($this->getId());
			if($DBModel){
				return $DBModel;
			}
		}
		return null;
	}
	/**
	 * @param DBModel[] $dbms
	 * @return static[]
	 */
	public static function fromDBModels(array $dbms): array{
		$lArr = [];
		foreach($dbms as $dbm){
			$lArr[] = $dbm->l();
		}
		return $lArr;
	}
	/**
	 * @param int|string|array $id
	 * @return static|null
	 */
	public static function findInMemory($id): ?BaseModel{
		if(!$id){le("No id provided to " . __METHOD__);}
		$class = (new \ReflectionClass(static::class))->getShortName();
		if(is_array($id)){
			$params = $id;
			$m = Memory::firstMatch($params, $class);
		} else{
			$all = Memory::getAll($class) ?? [];
			foreach($all as $m){
				if($m->getId() == $id){
					return $m;
				}
			}
		}
		return null;
	}
	protected function updateDBModel(): void{
		if($mem = $this->getDBModelFromMemory()){
			$mem->populateByLaravelModel($this);
		} else{
			if(!$this->wasRecentlyCreated){ // Breaks when new
				$this->getDBModel(); // Make sure it's put in memory
			}
		}
	}

	/**
	 * @param DBModel[] $dbms
	 * @return Collection|\Tightenco\Collect\Support\Collection
	 */
	public static function toBaseModels(array $dbms){
		$arr = [];
		foreach($dbms as $dbm){
			$arr[] = $dbm->l();
		}
		return collect($arr);
	}
}
