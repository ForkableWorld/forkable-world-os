<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
trait HasOptions {
	public static function getOptionsByName(): array{
		$opts = [];
		foreach(static::all() as $item){
			$opts[$item->getDisplayNameAttribute()] = $item->getId();
		}
		ksort($opts);
		return $opts;
	}
	public static function getOptionsById(): array{
		$opts = [];
		foreach(static::all() as $item){
			$opts[$item->getId()] = $item->getDisplayNameAttribute();
		}
		ksort($opts);
		return $opts;
	}
}
