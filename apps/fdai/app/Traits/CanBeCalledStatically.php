<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
trait CanBeCalledStatically {
	/**
	 * Handle dynamic static method calls into the method.
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 */
	public static function __callStatic(string $method, array $parameters){
		return (new static)->$method(...$parameters);
	}
}
