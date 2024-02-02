<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage;
use Closure;
class MemoryOrRedisCache extends RedisCache {
	/**
	 * @param string $key
	 * @param mixed $value
	 * @param int|null $secondsToLive
	 * @return void
	 */
    public static function set(string $key, $value, int $secondsToLive = null): void {
		if(!$secondsToLive){ // TODO: Implement memory expiration
			Memory::set($key, $value, Memory::REDIS);
		}
        parent::set($key, $value, $secondsToLive);
    }
    /**
     * @param string $key
     * @return mixed
     */
    public static function get(string $key){
        $val = Memory::get($key,Memory::REDIS);
        if($val !== null){return $val;}
        return parent::get($key);
    }
	/**
	 * Delete an item from the cache by its unique key.
	 * @param string $key The unique cache key of the item to delete.
	 * @return int True if the item was successfully removed. False if there was an error.
	 */
    public static function delete(string $key): int{
        Memory::set($key, null, Memory::REDIS);
        return parent::delete($key);
    }
    public static function flush(): void {
        Memory::flush();
        //if(!RedisCache::enabled()){return;}
        parent::flush();
    }
    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     * @param string $key
     * @param \DateTimeInterface|\DateInterval|int|null $ttl
     * @param Closure $callback
     * @return mixed
     */
    public static function remember(string $key, $ttl, Closure $callback){
        $value = Memory::get($key, Memory::REDIS);
        if($value !== null){return $value;}
        return parent::remember($key, $ttl, $callback);
    }
    /**
     * Delete an item from the cache by its unique key.
     * @param string $key The unique cache key of the item to delete.
     * @return int True if the item was successfully removed. False if there was an error.
     */
    public static function forget(string $key): int{
		Memory::delete($key, Memory::REDIS);
        return static::delete($key);
    }
}
