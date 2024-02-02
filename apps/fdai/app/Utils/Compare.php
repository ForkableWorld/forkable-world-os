<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
use App\Logging\QMLog;
use App\Types\BoolHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use Carbon\CarbonInterface;
/**
 * @package App\Utils
 */
class Compare {
	/**
	 * @param $old
	 * @param $new
	 * @return bool
	 */
	public static function isSimilar($old, $new): bool{
		if($old === $new){
			return true;
		}
		if(BoolHelper::equalAccordingToMySQL($old, $new)){
			return true;
		}
		$oldArr = is_array($old);
		$newArr = is_array($new);
		if($oldArr && $newArr){
			return QMArr::arraysAreEqual($old, $new);
		}
		if(!self::containsArrayOrObject([$old, $new])){
			if(is_numeric($old) && is_numeric($new)){
				if(Stats::equal($old, $new)){
					return true;
				}
			}
		}
		if(!$oldArr && !$newArr){
			if(is_object($new) && !$new instanceof CarbonInterface){
				return false;
			}
			if(is_object($old) && !$old instanceof CarbonInterface){
				return false;
			}
			if(TimeHelper::isSame($old, $new)){
				return true;
			}
		}
		return false;
	}
	/**
	 * @param array $arr
	 * @return bool
	 */
	public static function containsArrayOrObject(array $arr): bool{
		foreach($arr as $item){
			if(is_array($item)){
				return true;
			}
			if(is_object($item)){
				return true;
			}
		}
		return false;
	}
	/**
	 * @param $old
	 * @param $new
	 * @param string $what
	 * @param int $maxLength
	 * @return string
	 */
	public static function toString($old, $new, string $what, int $maxLength = 280): string{
		return "$what
    OLD VALUE => " . QMStr::efficientPrint($old, $maxLength/2) . "
    NEW VALUE => " . QMStr::efficientPrint($new, $maxLength/2);
	}
	/**
	 * @param $old
	 * @param $new
	 * @param string $message
	 */
	public static function validateSame($old, $new, string $message){
		if(!self::isSimilar($old, $new)){
			le("Not the same! $message" . self::toString($old, $new, $message));
		}
	}
	/**
	 * @param $old
	 * @param $new
	 * @param string $message
	 */
	public static function assertDifferent($old, $new, string $message){
		if($old === $new){
			le("These values should be different! 
            " . Compare::toString($old, $new, $message));
		}
	}
	/**
	 * @param $old
	 * @param $new
	 * @param string $what
	 */
	public static function logChange($old, $new, string $what){
		if($old !== $new){
			QMLog::info(Compare::toString($old, $new, $what));
		}
	}
}
