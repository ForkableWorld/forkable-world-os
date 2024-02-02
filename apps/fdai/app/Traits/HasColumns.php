<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
trait HasColumns {
	public static function hasColumn(string $field): bool{
		return in_array($field, static::getColumns());
	}
	abstract public static function getColumns(): array;
}
