<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
trait CompressibleTrait {
	public function unsetNullFields(){
		foreach($this as $key => $value){
			if($value === null){
				unset($this->$key);
			}
		}
	}
	/**
	 * @return $this
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function unsetNullAndEmptyStringFields() {
		foreach($this as $key => $value){
			if($key === 'variable'){
				unset($this->$key);
				continue;
			}
			if(is_array($value)){
				foreach($value as $item){
					if(is_object($item) && method_exists($item, 'unsetNullAndEmptyStringFields')){
						$item->unsetNullAndEmptyStringFields();
					}
				}
			}
			if(is_object($value) && method_exists($value, 'unsetNullAndEmptyStringFields')){
				$value->unsetNullAndEmptyStringFields();
			}
			if($value === null || $value === ""){
				unset($this->$key);
			}
		}
		return $this;
	}
	public function unsetFieldsWithHtmlInName(){
		foreach($this as $key => $value){
			if(is_array($value)){
				foreach($value as $item){
					if(is_object($item) && method_exists($item, 'unsetFieldsWithHtmlInName')){
						$item->unsetFieldsWithHtmlInName();
					}
				}
			}
			if(is_object($value) && method_exists($value, 'unsetFieldsWithHtmlInName')){
				$value->unsetFieldsWithHtmlInName();
			}
			if(stripos($key, 'html') !== false){
				unset($this->$key);
			}
		}
	}
}
