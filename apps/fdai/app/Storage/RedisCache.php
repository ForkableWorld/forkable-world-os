<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage;
use App\Computers\ThisComputer;
use App\DevOps\QMServices;
use App\Exceptions\TooBigForCacheException;
use App\Logging\ConsoleLog;
use App\Storage\DB\Writable;
use App\Types\ObjectHelper;
use App\Utils\AppMode;
use App\Utils\Env;
use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
class RedisCache {
    public static $cache;
    public const MAXIMUM_KB = 1000;
    public const MAX_KEY_LENGTH = 249;
    public const USE_PREFIX = false;
	/**
	 * @return string
	 */
	public static function getPrefix(): string{
		$prefix = Writable::getDbName() . "_cache:";
		return $prefix;
	}
	/**
     * @param string $functionName
     * @param array $requestParams
     * @param string|int $userId
     * @param bool $force
     * @return mixed
     */
    public static function getByRequestParams(string $functionName, array $requestParams, $userId = '', bool $force =
    false){
        $memcacheKey = CacheManager::convertParamsToKey($functionName, $requestParams) .$userId;
        if($force || !AppMode::appModeIs(Env::ENV_TESTING)){
            $response = self::get($memcacheKey);
            if($response){
                return $response;
            }
        }
        return null;
    }
	/**
	 * @param $data
	 * @param string $function
	 * @param array $requestParams
	 * @param int $ttl
	 * @param string|int $userId
	 * @return void
	 */
    public static function setByRequestParams($data, string $function, array $requestParams, int $ttl = 3600, $userId = ''): void {
        $memcacheKey = CacheManager::convertParamsToKey($function, $requestParams) .$userId;
	    self::set($memcacheKey, $data, $ttl);
    }
    /**
     * @param string $key
     * @param $value
     * @param int|null $modifiedMaximumKb
     * @return array|object|null
     * @throws TooBigForCacheException
     */
    public static function shrinkAndValidateSize(string $key, $value, int $modifiedMaximumKb = null){
        $maximumKb = $modifiedMaximumKb ?: self::MAXIMUM_KB;
        $value = ObjectHelper::shrinkObjectIfTooBigForMemcached($value, $modifiedMaximumKb);
        $sizeInKb = ObjectHelper::getSizeInKiloBytes($value);
        if($sizeInKb > $maximumKb){
            /** @noinspection PhpUnusedLocalVariableInspection */
            $propertySizes = ObjectHelper::getSubPropertySizesInKb($value);
            $value = ObjectHelper::shrinkObjectIfTooBigForMemcached($value, $modifiedMaximumKb);
            $sizeInKb = ObjectHelper::getSizeInKiloBytes($value);
            if($sizeInKb > $maximumKb){
                throw new TooBigForCacheException($key, $value, $sizeInKb, $maximumKb);
            }
        }
        return $value;
    }
    /**
     * @param string $key
     * @param mixed $value
     * @param int|null $secondsToLive
     * @return void
     */
    public static function set(string $key, $value, int $secondsToLive = null): void {
        $oneMonthSeconds = 60 * 60 * 24 * 30;
        $seconds = null;
        if(!$secondsToLive){
            $minutes = $seconds = null;
        }else if($secondsToLive < $oneMonthSeconds){
            $seconds = $secondsToLive;
            $minutes = $seconds/60;
        } else if ($secondsToLive > time()){
            $seconds = $secondsToLive - time();
            $minutes = $seconds/60;
        } else {
            le("$secondsToLive is not a valid expirationTimeEpochOrSecondsFromNow");
        }
        $key = CacheManager::formatKey($key);
        Cache::set($key, $value, $seconds);
        return;
        $serialized = self::serialize($value);
	    // Use this because laravel cache doesn't have all redis
	    // functions like ->keys and we want to maintain consistency
	    $r = self::redis();
		if($seconds){
			$r->setex($key, $seconds, $serialized);
		} else {
			$r->set($key, $serialized);
		}
    }
	/**
	 * @param string $key
	 * @return mixed
	 */
    public static function get(string $key){
	    // Use this because laravel cache doesn't have all redis
	    // functions like ->keys and we want to maintain consistency
        return Cache::get(CacheManager::formatKey($key));
	    $str = self::redis()->get(CacheManager::formatKey($key));
		return self::unserialize($str);
    }
	/**
	 * Unserialize the value.
	 *
	 * @param  mixed  $value
	 * @return mixed
	 */
	private static function unserialize($value){
		if($value === null){return null;}
		return is_numeric($value) ? $value : unserialize($value);
	}
	/**
	 * Delete an item from the cache by its unique key.
	 * @param string $key The unique cache key of the item to delete.
	 * @return int True if the item was successfully removed. False if there was an error.
	 */
    public static function delete(string $key): int{
        return Cache::delete(CacheManager::formatKey($key)) ;
		 // Use this because laravel cache doesn't have all redis
	    // functions like ->keys and we want to maintain consistency
	    return self::redis()->del(CacheManager::formatKey($key));
    }
	public static function flush(): void {
        Cache::flush();
        return;
        $host = self::getRedisHost();
        if(stripos($host, 'localhost') === false &&
            stripos($host, ThisComputer::getHostAddress()) === false){
            le("Why are we flushing non-local redis?");
        }
        ConsoleLog::info('Flushing cache!');
		// Use this because laravel cache doesn't have all redis
		// functions like ->keys and we want to maintain consistency
		$r = self::redis();
		//$prefix = $r->getOptions()->prefix;
		$pattern = static::getPrefix()."*";
		$keys = $r->keys($pattern);
		if ($keys) $r->del($keys);
    }
    /**
     * @param string $pattern
     * @return array
     */
    public static function keys(string $pattern = "*"):array {
        //return Cache::keys(static::getPrefix().$pattern);
        return self::redis()->keys(static::getPrefix().$pattern);
    }
	/**
	 * @param string $key
	 * @param int $value
	 * @param int|null $ttlSeconds
	 * @return int
	 */
	public static function increment(string $key, int $value = 1, int $ttlSeconds = null): int{
		$existing = static::get($key) ?? 0;
		$new = $existing + $value;
		static::set(CacheManager::formatKey($key), $new, $ttlSeconds);
		return $new;
	}
	/**
     * @return Connection
     */
    private static function redis(): Connection {
        // Needed for flushing with prefix but doesn't provide expiration and other Laravel Cache niceties
        $c = Redis::connection('cache');
        return $c;
    }
    /**
     * @return Repository
     */
    public static function redisCacheRepository(): Repository {
        // Cache::driver('redis') is best because it provides Laravel niceties like expiration and is a drop in
        // replacement for Memcache, but doesn't allow getting keys and proper flushing with prefix
        return Cache::driver('redis');
    }
    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     * @param string $key
     * @param \DateTimeInterface|\DateInterval|int|null $ttl
     * @param Closure $callback
     * @return mixed
     */
    public static function remember(string $key, $ttl, Closure $callback){
	    // Use this because laravel cache doesn't have all redis
	    // functions like ->keys and we want to maintain consistency
	    $value = self::redis()->get(CacheManager::formatKey($key));
	    // If the item exists in the cache we will just return this immediately and if
        // not we will execute the given Closure and cache the result of that for a
        // given number of seconds so it's available for all subsequent requests.
        if (! is_null($value)) {return $value;}
	    self::redis()->set(CacheManager::formatKey($key), $value = $callback(), $ttl);
        return $value;
    }
    /**
     * Delete an item from the cache by its unique key.
     * @param string $key The unique cache key of the item to delete.
     * @return int True if the item was successfully removed. False if there was an error.
     */
    public static function forget(string $key): int{
        return static::delete($key);
    }
    /**
     * Serialize the value.
     * @param  mixed  $value
     * @return int|string
     */
    protected static function serialize($value){
        return is_numeric($value) ? $value : serialize($value);
    }
    /**
     * @return string
     */
    public static function getRedisHost(): string{
        $host = config('database.redis.cache.host');
        return $host;
    }
	/**
	 * @throws \Exception
	 */
	public static function validate(): void {
    	QMServices::checkRedis();
	}
    public static function enabled(): bool{
        return config('cache.default') === 'redis';
    }
}
