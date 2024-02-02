<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Logging;
use App\Types\QMArr;
use App\Utils\AppMode;
class GlobalLogMeta {
	const BUILD = 'BUILD';
	const CLOCKWORK = 'CLOCKWORK';
	const CONFIG = 'CONFIG';
	const CUSTOM_GLOBAL_META = 'CUSTOM_GLOBAL_META';
	const GIT = 'GIT';
	const GLOBAL_CONTEXT = 'GLOBAL_CONTEXT';
	const LINKS = 'LINKS';
	public const MAX_BUGSNAG_PAYLOAD_KB = 900;
	const PHPSTORM_STACK = 'PHPSTORM_STACK';
	const REQUEST_META = 'REQUEST_META';
	const SOLUTIONS = 'SOLUTIONS';
	public static $CUSTOM_GLOBAL_META_DATA;
	public static $GLOBAL_CONTEXT;
	public static function get(QMLog $log = null): array{
		try {
			$arr1 = LinksLogMetaData::get($log);
		} catch (\Throwable $e){
		    error_log("Could not get LinksLogMetaData because: " . $e->getMessage());
			$arr1 = [];
		}
		$arr = [
			self::BUILD => BuildLogMeta::get(),
			self::GIT => GitLogMeta::get(),
			self::GLOBAL_CONTEXT => self::$GLOBAL_CONTEXT,
			self::CUSTOM_GLOBAL_META => self::$CUSTOM_GLOBAL_META_DATA,
			self::LINKS => $arr1,
		];
		if(AppMode::isApiRequest()){$arr[self::REQUEST_META] = RequestLogMeta::get();}
		if(QMClockwork::enabled()){$arr[self::CLOCKWORK] = QMClockwork::meta();}
		if(function_exists('config')){
			// Too big and risky.  if you decide you need specific ones you should add them here.  $arr[self::CONFIG] =
			// config()->all();
		}
		return QMArr::notEmptyValues($arr);
	}
	/**
	 * @param string $key
	 * @param        $value
	 */
	public static function addCustomGlobalMetaData(string $key, $value){
		if(!empty($value)){
			self::$CUSTOM_GLOBAL_META_DATA[$key] = $value;
		}
	}
	/**
	 * @return string
	 */
	public static function getGlobalContext(): ?string{
		return GlobalLogMeta::$GLOBAL_CONTEXT;
	}
	/**
	 * @param string $context
	 * @return string
	 */
	public static function setGlobalContext(string $context): string{
		return GlobalLogMeta::$GLOBAL_CONTEXT = $context;
	}
}
