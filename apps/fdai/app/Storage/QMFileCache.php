<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage;
use Illuminate\Cache\Repository;
class QMFileCache
{
	/**
	 * @return \Illuminate\Cache\Repository
	 */
    protected static function driver(): Repository {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return \Cache::driver('file');
    }
	/**
	 * @param $key
	 * @return mixed|null
	 */
	public static function get($key): mixed{
        return self::driver()->get($key);
    }
	/**
	 * @param $key
	 * @param $value
	 * @param null $ttl
	 * @return bool
	 */
	public static function set($key, $value, $ttl = null): bool{
        return self::put($key, $value, $ttl);
    }
	/**
	 * @param $key
	 * @return bool
	 */
	public static function delete($key): bool{
        return self::forget($key);
    }
	/**
	 * @param $key
	 * @param $value
	 * @param null $ttl
	 * @return bool
	 */
	public static function put($key, $value, $ttl = null): bool{
        return self::driver()->put($key, $value, $ttl);
    }
	/**
	 * @param $key
	 * @param int $value
	 * @return int
	 */
	public static function increment($key, int $value = 1): int{
        return self::driver()->increment($key, $value);
    }
	/**
	 * @param $key
	 * @param int $value
	 * @return int
	 */
	public static function decrement($key, int $value = 1): int{
        return self::driver()->decrement($key, $value);
    }
	/**
	 * @param $key
	 * @param $value
	 * @return bool
	 */
	public static function forever($key, $value): bool{
        return self::driver()->forever($key, $value);
    }
	/**
	 * @param $key
	 * @return bool
	 */
	public static function forget($key): bool{
        return self::driver()->forget($key);
    }
	/**
	 * @return bool
	 */
	public static function flush(): bool{
        return self::driver()->flush();
    }
	/**
	 * @return bool
	 */
	public static function clear(): bool{
        return self::flush();
    }
}
