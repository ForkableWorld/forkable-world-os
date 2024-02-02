<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Types;
class BoolHelper {
	const TRUE_STRING = "true";
	const FALSE_STRING = "false";
	const ALL_STRING = "all";
	const NULL_STRING = "null";
	/**
	 * @param $val
	 * @return bool|mixed
	 */
	public static function convertFromStringIfNecessary($val){
		if($val === "0" || $val === "false" || $val === "FALSE"){
			return false;
		}
		if($val === "1" || $val === "true" || $val === "TRUE"){
			return true;
		}
		return $val;
	}
	/**
	 * @param $value
	 * @return bool
	 */
	public static function isFalseyAndNotNull($value): bool{
		return $value === 0 || $value === false || $value === "false" || $value === '"false"' ||
			(is_string($value) && strlen($value) < 20 &&
				strpos($value, 'false') !== false); // handles screwed up json encodes "\"\\\"false\\\"\""
	}
	/**
	 * @param $value
	 * @return bool
	 */
	public static function isFalsey($value): bool{
		return !$value || $value === "false" || $value === '"false"' || $value === 'FALSE' || $value === 'False';
	}
	/**
	 * @param $expected
	 * @param $actual
	 * @return bool
	 */
	public static function isEqual($expected, $actual): bool{
		if(self::isFalseyAndNotNull($expected) && self::isFalseyAndNotNull($actual)){
			return true;
		}
		if(self::isTrue($expected) && self::isTrue($actual)){
			return true;
		}
		return false;
	}
	/**
	 * @param $a
	 * @param $b
	 * @return bool
	 */
	public static function equalAccordingToMySQL($a, $b): bool{
		if($a === null){
			return false;
		}
		if($b === null){
			return false;
		}
		if($a === 1 && $b === true){
			return true;
		}
		if($b === 1 && $a === true){
			return true;
		}
		if($a === 0 && $b === false){
			return true;
		}
		if($b === 0 && $a === false){
			return true;
		}
		if(is_object($a) && is_object($b)){
			return json_encode($a) === json_encode($b);
		}
		return false;
	}
	/**
	 * @param $value
	 * @return bool
	 */
	public static function isTrue($value): bool{
		return $value && !self::isFalseyAndNotNull($value);
	}
	/**
	 * @param $val
	 * @param string|null $name
	 */
	public static function assertFalsey($val, string $name = null): void{
		if(!$val){
			return;
		}
		if(self::isFalseyAndNotNull($val)){
			return;
		}
		le("$name should be falsey but is " . \App\Logging\QMLog::print_r($val, true));
	}
	/**
	 * @param $val
	 * @param string|null $name
	 */
	public static function assertTruthy($val, string $name = null): void{
		if(!$val || self::isFalseyAndNotNull($val)){
			le("$name should be truthy but is " . \App\Logging\QMLog::print_r($val, true));
		}
	}
	/**
	 * @param $paging
	 * @return string
	 */
	public static function toString($paging): string{
		if($paging){
			return "true";
		}
		return "false";
	}
	/**
	 * @param $val
	 * @return bool
	 */
	public static function toBool($val): bool{
		if(self::isFalsey($val)){
			return false;
		}
		return true;
	}
	/**
	 * @param $value
	 * @return bool
	 */
	public static function isTruthy($value): bool{
		// NOTE: != will return false if $value === true
		return !self::isFalsey($value);
	}
	/**
	 * @param $value
	 * @return int|null
	 */
	public static function toDBBool($value): ?int{
		if($value === "false" || $value === false || $value === 0 || $value === "0"){
			return 0;
		}
		if($value === "true" || $value === true || $value === 1 || $value === "1"){
			return 1;
		}
		return null;
	}
}
