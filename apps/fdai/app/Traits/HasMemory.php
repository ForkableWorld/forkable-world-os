<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Models\BaseModel;
use App\Slim\Model\DBModel;
use App\Storage\Memory;
use App\Types\QMArr;
use Cache;
use DateInterval;
use Illuminate\Support\Collection;
use Psr\SimpleCache\InvalidArgumentException;

trait HasMemory {
	abstract public function getId();
	/**
	 * @param string $key
	 * @param bool|mixed|null|object|array|string $value
	 * @return array|mixed|object
	 */
	public static function setInClassMemory(string $key, $value){
		Memory::set($key, $value, static::getMemoryPrimaryKey());
		return $value;
	}
	/**
	 * @param string $key
	 * @return bool|mixed|null|object|array|string
	 */
	public static function getFromClassMemory(string $key){
		return Memory::get($key, static::getMemoryPrimaryKey());
	}
	/**
	 * @param string $key
	 * @param bool|mixed|null|object|array|string $value
	 * @return array|mixed|object
	 */
	public function setInModelMemory(string $key, $value){
		Memory::set($this->memoryKey($key), $value, static::memoryStuffKey());
		return $value;
	}
	/**
	 * @param string $key
	 * @return bool|mixed|null|object|array|string
	 */
	public function getFromModelMemory(string $key){
		return Memory::get($this->memoryKey($key), static::memoryStuffKey());
	}
	private static function memoryStuffKey(): string{
		return static::getMemoryPrimaryKey() . "-stuff";
	}
	/**
	 * @param string $key
	 * @return string
	 */
	private function memoryKey(string $key): string{
		return $this->getId() . "-" . $key;
	}
	/**
	 * @return DBModel|false|null We set false when there's no global variable relationships in the database
	 */
	public function getMeFromMemory(){
		// We need to use getUUID instead of getId so we can set things false that don't exist in DB to avoid redundant DB requests
		return static::getFromClassMemory($this->getUUID());
	}
	/**
	 * @return void
	 */
	public function addToMemory(): void{
		// We need to use getUUID instead of getId so we can set things
		// false that don't exist in DB to avoid redundant DB requests
//		if($id = (string)$this->getId()){
		// What's the point of these duplicates?
//			static::setInClassMemory($id, $this);
//		}
		$uuid = $this->getUUID();
		static::setInClassMemory($uuid, $this);
	}
	public function deleteFromMemory(): void{
		// We need to use getUUID instead of getId so we can set things false that don't exist
		// in DB to avoid redundant DB requests
		static::setInClassMemory($this->getUUID(), null);
		$uuid = $this->getUUID();
	}
	public function setFalseInMemory(): void{
		// We need to use getUUID instead of getId so we can set things false
		// that don't exist in DB to avoid redundant DB requests
		static::setInClassMemory($this->getUUID(), false);
	}
	/**
	 * @param int|string $idOrUniqueIndex
	 * @return static|null|BaseModel|DBModel
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function findInMemory($idOrUniqueIndex){
		$all = static::getAllFromMemoryIndexedByUuidAndId();
		if(is_string($idOrUniqueIndex) && isset($all[$idOrUniqueIndex])){
			return $all[$idOrUniqueIndex];
		}
		foreach($all as $one){
			/** @var static $one */
			if($one && $one->getId() === $idOrUniqueIndex){
				return $one;
			}
		}
		return null;
	}
    /**
     * @param array $ids
     * @return array
     * @noinspection PhpMissingReturnTypeInspection
     */
    public static function findArrayInMemory(array $ids){
        $all = static::getAllFromMemoryIndexedByUuidAndId();
        $found = [];
        foreach($all as $one){
            /** @var static $one */
            if($one && in_array($one->getId(), $ids)){
                $found[$one->getId()] = $one;
            }
        }
        return $found;
    }
	/**
	 * @param $id
	 * @return void
	 */
	public static function flushFromMemory($id): void{
		$all = static::getAllFromMemoryIndexedByUuidAndId();
		$keep = [];
		foreach($all as $one){
			/** @var static $one */
			if($one && $one->getId() === $id){
				continue;
			}
			$keep[] = $one;
		}
		Memory::setByPrimaryKey(static::getMemoryPrimaryKey(), $keep);
	}
	/**
	 * @param array $data
	 * @return void
	 */
	public static function setFalseInMemoryByUniqueIndex(array $data): void{
		$uuid = static::generateUUID($data);
		static::setInClassMemory($uuid, false);
	}
	/**
	 * @return static[]
	 */
	public static function getAllFromMemoryIndexedByUuidAndId(): array{
		return Memory::getByPrimaryKey(static::getMemoryPrimaryKey());
	}
	protected static function hasNonIdUniqueIndex(): bool{
		$unique = static::getUniqueIndexColumns(); // Don't use self::
		if(count($unique) === 1 && ($unique[0] === static::FIELD_ID)){
			return false;
		}
		return true;
	}
	/**
	 * @return static[]
	 */
	public static function getAllFromMemoryIndexedById(): array{
		$all = static::getAllFromMemoryIndexedByUuidAndId();
		if(!static::hasNonIdUniqueIndex()){
			return $all;
		}
		$byId = [];
		foreach($all as $one){
			$byId[$one->getId()] = $one;
		}
		return $byId;
	}
	protected static function getMemoryPrimaryKey(): string{
		return (new \ReflectionClass(static::class))->getShortName();
	}
	/**
	 * @return static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function getLastFromMemory(){
		return Memory::getLast(static::getMemoryPrimaryKey());
	}
	public static function flushAllFromMemory(){
		Memory::purgePrimaryKey(static::getMemoryPrimaryKey());
	}
	/**
	 * @param array $wheres
	 * @return array
	 */
	public static function getFromMemoryWhere(array $wheres): array{
		$cachedModels = static::getAllFromMemoryIndexedByUuidAndId();
		if(!$cachedModels){
			return [];
		}
		return QMArr::filter($cachedModels, $wheres);
	}
	public static function generateUUID(array $data): string{
		$columns = static::getUniqueIndexColumns(); // Don't use self::
		$values = [];
		foreach($columns as $column){
			$values[] = $data[$column];
		}
		if(count($values) === 1){
			return $values[0];
		}
		return implode("-", $values);
	}
	public function getUniqueIndexArray(): array {
		$columns = static::getUniqueIndexColumns(); // Have to use static not self:: here
		$values = [];
		foreach($columns as $column){
			$values[$column] = $this->getAttribute($column);
		}
		return $values;
	}
	public function getUUID(): ?string{
		$columns = static::getUniqueIndexColumns(); // Have to use static not self:: here
		if(!static::hasNonIdUniqueIndex()){
			$id = $this->getId();
			if($id === null || $id === ""){
				debugger("no id");
				$this->getId();
			}
		}
		$values = [];
		foreach($columns as $column){
			$values[$column] = $this->getAttribute($column);
		}
		return implode("-", $values);
	}
	/**
	 * @param string|array $params
	 * @param $value
	 * @return static|null
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function findInMemoryWhere($params, $value = null){
		if(is_string($params)){
			$params = [$params => $value];
		}
		return QMArr::firstMatch($params, static::getAllFromMemoryIndexedByUuidAndId());
	}
	/**
	 * @param string|array $params
	 * @param $value
	 * @return static[]
	 */
	public static function fromMemoryWhere($params, $value = null): array{
		if(is_string($params)){
			$params = [$params => $value];
		}
		return QMArr::whereByParams($params, static::getAllFromMemoryIndexedByUuidAndId());
	}
	/**
	 * @param $data
	 * @return static|null
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function findInMemoryByUUID($data){
		$id = static::generateUUID($data);
		return static::getFromClassMemory($id);
	}

    /**
     * @param $ids
     * @return static|null
     */
    public static function findInMemoryOrDB($ids) {
        if($ids instanceof Collection || is_array($ids)){
            return static::findArrayInMemoryOrDB($ids);
        }
        return static::findOneInMemoryOrDB($ids);
    }
    /**
     * @param int|string $id
     * @return static|null|BaseModel|DBModel
     */
    protected static function findOneInMemoryOrDB($id){
        if($m = static::findInMemory($id)){
            return $m;
        }
        /** @var static $m */
        if ($m = static::find($id)) {
            $m->addToMemory();
        }
        return $m;
    }
    /**
     * @param array|Collection $ids
     * @return null|static[]
     */
    protected static function findArrayInMemoryOrDB($ids): ?array
    {
        $indexedIds = [];
        foreach ($ids as $id) {
            $indexedIds[$id] = $id;
        }
        // ConsoleLog::info(static::class." ".__FUNCTION__." ".$id); // Uncomment for segfault debugging
        if($arr = static::findArrayInMemory($indexedIds)){
            foreach ($arr as $one) {
                /** @var HasMemory $one */
                $id = $one->getId();
                $arr[$id] = $one;
                unset($indexedIds[$id]);
            }
        }
        $db = static::find($indexedIds);
        foreach ($db as $one) {
            $one->addToMemory();
            $arr[$one->getId()] = $one;
        }
        return $arr;
    }
    /**
     * @param null|int|DateInterval $ttl Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     * @return Collection|BaseModel
     *          * @throws InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public static function allFromCache($ttl = null): Collection{
        $key = static::getCacheKey(__FUNCTION__);
        $all = Cache::get($key);
        if(!$all) {
            $all = static::all();
            Cache::set($key, $all, $ttl);
        }
        return $all;
    }
    private static function getCacheKey(string $method): string{
        return static::class."::".$method;
    }
}
