<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage;
use App\Logging\QMLog;
use App\Utils\EnvOverride;
use Illuminate\Support\Facades\Cache;
class LocalFileCache {
	const LOCAL_FILE_CACHE = 'local-file-cache';
	const PATH             = 'storage/local-file-cache';

	private static \Illuminate\Contracts\Cache\Repository $cache;
	private static bool $disabled = false;
	public static function cache(): \Illuminate\Contracts\Cache\Repository{
		if(!isset(self::$cache)){
            static::$cache = Cache::store(self::LOCAL_FILE_CACHE);
		}
		return self::$cache;
	}
	/**
     * @param string $key
     * @param mixed $val
     */
    public static function set(string $key, mixed $val): void{
		if(!self::enabled()){return;}
        //$val = var_export($val, true);
	    try {
		    file_put_contents(self::getPath($key), serialize($val));
	    } catch (\Throwable $e) {
			static::$disabled = true;
	        QMLog::warning("Unable to write to local file cache because: " . $e->getMessage());
	    }
		//static::cache()->set($key, $val);
    }
    /**
     * @param string $key
     * @return mixed
     */
    public static function get(string $key): mixed{
	    if(!self::enabled()){
		    return null;
	    }
        if(!EnvOverride::isLocal()){return false;}
        //$path = self::getPath($key, $folder);
        //return self::cache()->get($key);
	    $p = static::getPath($key);
		if(!file_exists($p)){return null;}
	    $c = file_get_contents(static::getPath($key));
	    return unserialize($c);
    }
	private static function getPath(string $key): string{
		$key = str_replace("/", '-', $key);
		$key = str_replace("\\", '-', $key); // windows
		$key = str_replace(":", '-', $key); // windows
        $key = str_replace('"', '-', $key); // windows
        $key = str_replace(' ', '-', $key); // windows
		return abs_path('storage/'.self::LOCAL_FILE_CACHE."/$key");
	}
	public static function enabled(): bool {
		if(self::$disabled){return false;}
		return EnvOverride::isLocal();
	}
}
