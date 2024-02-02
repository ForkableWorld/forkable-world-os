<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Tables\BaseTable;
trait HasTable {
	abstract public function getBaseTable(): BaseTable;
}
