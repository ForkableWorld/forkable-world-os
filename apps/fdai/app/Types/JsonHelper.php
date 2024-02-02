<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Types;
class JsonHelper {
	/**
	 * @param string $jsonString
	 * @return string
	 */
	public static function alphabetizeJson($jsonString): string{
		$array = json_decode($jsonString, true);
		$array = self::ksortRecursive($array);
		return json_encode($array);
	}
	/**
	 * @param array $array
	 * @param int $sort_flags
	 * @return array|bool
	 */
	public static function ksortRecursive(&$array, $sort_flags = SORT_REGULAR){
		if(!is_array($array)){
			le("Must be array");
		}
		ksort($array, $sort_flags);
		foreach($array as &$arr){
			self::ksortRecursive($arr, $sort_flags);
		}
		return $array;
	}
	public static function prettyJsonEncode($obj, int $maxChars = null): string{
		return QMStr::prettyJsonEncode($obj, $maxChars);
	}
	/**
	 * @param string $path
	 * @return string
	 */
	public static function appendJsonExtensionIfNecessary(string $path): string{
		if(strpos($path, ".") === false){
			$path = $path . ".json";
		}
		return $path;
	}
}
