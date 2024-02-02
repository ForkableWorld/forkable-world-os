<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Sorts;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Sorts\Sort;
class StringLengthSort implements Sort {
	public function __invoke(Builder $query, bool $descending, string $property){
		$direction = $descending ? 'DESC' : 'ASC';
		$query->orderByRaw("LENGTH(`{$property}`) {$direction}");
	}
}
