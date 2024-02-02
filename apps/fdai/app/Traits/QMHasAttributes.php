<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
trait QMHasAttributes {
	use HasAttributes, HasRelationships;
	public function attributeExists(string $key): bool{
		if(method_exists($this, "hasColumn")){
			return static::hasColumn($key);
		}
		if(isset($this->attributes) && isset($this->attributes[$key])){
			return true;
		}
		if(property_exists($this, $key)){
			return true;
		}
		return false;
	}
	abstract public function __toString();
}
