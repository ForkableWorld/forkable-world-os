<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage;
use App\Logging\QMLog;
use Tests\QMBaseTestCase;
/**
 * @package App\Storage
 */
class TestMemory
{
    public const LOGS = 'LOGS';
    private static $testCache = [];
    /**
     * @return array
     */
    public static function all(): array{
        return self::$testCache[\App\Utils\AppMode::getCurrentTestClass()][\App\Utils\AppMode::getCurrentTestName()];
    }
    /**
     * @return void
     */
    public static function flush(): void {
    	self::$testCache = [];
    }
	/**
	 * @param array $cache
	 */
	public static function setTestCache(array $cache): void {
        if($t = \App\Utils\AppMode::getCurrentTestName()){
            if(self::$testCache && !isset(self::$testCache[$t])){
                self::flush();  // It must be a new test so reduce memory usage by flushing
            }
            self::$testCache[$t] = $cache;
        }
    }
	/**
	 * @param string $key
	 * @param $value
	 */
	public static function add(string $key, $value){
        if($t = \App\Utils\AppMode::getCurrentTestName()){
            TestMemory::$testCache[$t][$key][] = $value;
        } else {
            TestMemory::$testCache[$key][] = $value;
        }
    }
	/**
	 * @param string $key
	 * @return mixed
	 */
	public static function get(string $key){
        if($t = \App\Utils\AppMode::getCurrentTestName()){
            return TestMemory::$testCache[$t][$key] ?? null;
        } else {
            return TestMemory::$testCache[$key] ?? null;
        }
    }
	/**
	 * @param string $key
	 * @param $val
	 * @return void
	 */
	public static function set(string $key, $val){
		if($t = \App\Utils\AppMode::getCurrentTestName()){
			TestMemory::$testCache[$t][$key] = $val;
		} else {
			TestMemory::$testCache[$key] = $val;
		}
	}
    /**
     * @return QMLog[]
     */
    public static function getLogs(): array{
        $logs = self::get(self::LOGS);
        if(!$logs){return [];}
        return $logs;
    }
	/**
	 * @param string $name
	 * @param string $messageLevel
	 * @param $meta
	 */
	public static function addLog(string $name, string $messageLevel, $meta){
		$l = new QMLog($name,$messageLevel, $meta);
		$l->logToHandlers();
        self::add(self::LOGS, $l);
    }
}
