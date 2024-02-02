<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use Illuminate\Support\Collection;
trait IsAnalyzableProperty {
	public static function analyzeWhereNull(): Collection{
		$qb = static::whereNull();
		$rows = $qb->get();
		foreach($rows as $row){
			$row->analyze();
		}
		return $rows;
	}
}
