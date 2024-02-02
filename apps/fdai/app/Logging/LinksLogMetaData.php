<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Logging;
use App\Menus\Admin\DebugMenu;
use App\Types\QMArr;
class LinksLogMetaData {
	const IGNITION = 'IGNITION';
	const PHPSTORM_LOG_LOCATION = 'Log Location';
	const OPEN_TEST = 'Go to Test';
	const PROFILE = 'PROFILE';
	private static array $links = [];
	public static function add(string $name, string $url){
		self::$links[$name] = $url;
	}
	public static function addAndLog(string $name, string $url){
		QMLog::logLink($url, $name);
		self::add($name, $url);
	}
	public static function find(string $name): ?string {
		return self::$links[$name] ?? null;
	}
	public static function get(QMLog $log = null): array{
        try {
            $arr = array_merge(self::$links, (new DebugMenu)->getLinks());
        } catch (\Throwable $e) {
            error_log("WARNING: Could not get LinksLogMetaData because: ".$e->__toString());
        }
		$arr = array_merge(SolutionButton::addUrlNameArrays(), $arr ?? []);
		return QMArr::notEmptyValues($arr);
	}
}
