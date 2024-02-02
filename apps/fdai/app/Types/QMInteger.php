<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Types;
use App\Exceptions\InvalidNumberException;
class QMInteger {
	/**
	 * @param $val
	 * @param int $min
	 * @param string $type
	 * @throws InvalidNumberException
	 */
	public static function validateMin($val, int $min, string $type){
		if($val < $min){
			throw new InvalidNumberException("$type cannot be less than $min but is: " . \App\Logging\QMLog::print_r($val, true));
		}
	}
}
