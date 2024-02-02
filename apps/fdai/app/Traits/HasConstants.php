<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use ReflectionClass;
use ReflectionException;
trait HasConstants {
	/**
	 * @param null $class
	 * @return array
	 */
	public static function getConstants($class = null): array{
		if(!$class){
			$class = static::class;
		}
		try {
			$reflection = new ReflectionClass($class);
		} catch (ReflectionException $e) {
			le($e);
		}
		$constants = $reflection->getConstants();
		return $constants;
	}
	/**
	 * @param $needle
	 * @return string|null
	 */
	protected static function getConstantEqualTo($needle): ?string{
		$constants = static::getConstants();
		foreach($constants as $name => $haystack){
			if($needle === $haystack){
				return $name;
			}
		}
		return null;
	}
	/**
	 * @param string $needle
	 * @param string|null $negativeNeedle
	 * @return array
	 */
	public static function getConstantValuesWithNameLike(string $needle, string $negativeNeedle = null): array{
		$constants = static::getConstants();
		$matches = [];
		foreach($constants as $key => $value){
			if(stripos($key, $needle) !== false){
				$matches[$key] = $value;
			}
		}
		if($negativeNeedle){
			foreach($matches as $key => $value){
				if(stripos($key, $negativeNeedle) !== false){
					unset($matches[$key]);
				}
			}
		}
		return $matches;
	}
}
