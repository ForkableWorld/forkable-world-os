<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage;
use App\Files\FileHelper;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Logging\SolutionButton;
use App\Slim\Middleware\QMAuth;
use App\Storage\DB\QMDB;
use App\UI\QMColor;
use Spatie\ResponseCache\Facades\ResponseCache;
use Tests\DBUnitTestCase;
class CacheManager {
	/**
	 * Prevents env from loading
	 */
	public static function clearConfigIfCached(): void {
		if(self::configurationIsCached()){
			$_ENV['APP_CONFIG_CACHE'] = null;
			FileHelper::delete("bootstrap/cache/config.php");
		}
    }
	/**
	 * @param string $function
	 * @param [] $requestParams
	 * @return string
	 */
	public static function convertParamsToKey(string $function, $requestParams): string{
		if(isset($requestParams[0]) && is_string($requestParams[0])){
			sort($requestParams);  // serialization leads to lots of duplication and unacceptably long keys
			$string = $function . implode('', $requestParams);
		} else{
			unset($requestParams['clientId'], $requestParams['appName'], $requestParams['appVersion']);
			$string = $function . serialize($requestParams);
		}
		return self::removeSpecialCharactersAndTruncateForMemcacheKey($string);
	}
    public static function flushTestCache(){
        TestMemory::flush();
	    QMFileCache::flush();
	    MemoryOrRedisCache::flush();
	    Memory::resetClearOrDeleteAll();
	    QMAuth::logout(__METHOD__);
	    QMColor::resetRandomColors();
	    ConsoleLog::debug("flushQueryLogs");
	    QMDB::flushQueryLogs(__METHOD__);
	    DBUnitTestCase::resetHttpGlobals();
	    SolutionButton::reset();
        ResponseCache::clear();
    }
	/**
	 * Determine if the application configuration is cached.
	 *
	 * @return bool
	 */
	public static function configurationIsCached(): bool{
		return file_exists(self::getCachedConfigPath());
	}
	/**
	 * Get the path to the configuration cache file.
	 *
	 * @return string
	 */
	public static function getCachedConfigPath(): string{
		return $_ENV['APP_CONFIG_CACHE'] ??  abs_path("bootstrap/cache/config.php");
	}
	/**
	 * @param string $key
	 * @return string
	 */
	public static function formatKey(string $key): string{
		$prefix = MemoryOrRedisCache::getPrefix();
		if(str_starts_with($key, $prefix)){
			return $key;
		}
		return $prefix . $key;
	}
	/**
     * @param string $string
     * @return string
     */
	public static function removeSpecialCharactersAndTruncateForMemcacheKey(string $string): string{
		$string = str_replace([
			'";s:',
			'"',
			':',
			';',
			'/',
			' ',
			'{',
			'}',
			'@',
			'.'
		], '', $string);
		$string = stripslashes($string);
		if(strlen($string) > MemoryOrRedisCache::MAX_KEY_LENGTH){
			QMLog::warning('Memcache key too long!  Truncating...', ['key' => $string]);
			$string = substr($string, 0, 249);
		}
		return $string;
	}
	/**
     * @param string $key
     * @return bool
     */
	public static function keyTooLong(string $key): bool{
		return strlen($key) > MemoryOrRedisCache::MAX_KEY_LENGTH;
	}

    public static function clear(): void{
		self::clearConfigIfCached();
		self::flushTestCache();
    }
}
