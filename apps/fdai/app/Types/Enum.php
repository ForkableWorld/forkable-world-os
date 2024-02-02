<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Types;
class Enum {
	public const NULL = "NULL";
	public static function format(string $str): string{
		return strtoupper($str);
	}
}
