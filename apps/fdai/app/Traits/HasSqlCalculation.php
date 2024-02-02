<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
/** Use this trait on property models that should be calculated by
 */
trait HasSqlCalculation {
	abstract public static function calculateInSQL();
}
