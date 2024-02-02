<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
class HighchartOption implements \JsonSerializable {
	/**
	 * @param null $obj
	 */
	public function __construct($obj = null){
		if($obj){
			foreach($obj as $key => $value){
				$this->$key = $value;
			}
		}
	}
	public function jsonSerialize(): \stdClass{
		$stdClass = new \stdClass();
		foreach($this as $key => $value){
			if($value === null){
				continue;
			}
			if(in_array($key, ["rawData"])){
				continue;
			}
			if(strpos($key, '_')){
				continue;
			}
			if(is_object($value) && !(array)$value){
				continue;
			}
			$stdClass->$key = $value;
		}
		return $stdClass;
	}
	/**
	 * @param array|object $arrayOrObject
	 * @return static
	 */
	public static function instantiateIfNecessary(array|object|string $arrayOrObject){
		if($arrayOrObject instanceof static){
			return $arrayOrObject;
		}
		$model = new static();
		foreach($arrayOrObject as $key => $value){
			$model->$key = $value;
		}
		return $model;
	}
}
