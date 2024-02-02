<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Filters;
use App\Models\UserVariableRelationship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Filters\BooleanFilter;
class AnalyzableFilter extends BooleanFilter {
	/**
	 * Apply the filter to the given query.
	 * @param Request $request
	 * @param Builder $query
	 * @param mixed $value
	 * @return Builder
	 */
	public function apply(Request $request, $query, $value){
		if($value['failed']){
			$query->whereNotNull(UserVariableRelationship::FIELD_INTERNAL_ERROR_MESSAGE);
		}
		return $query;
	}
	/**
	 * Get the filter's available options.
	 * @param Request $request
	 * @return array
	 */
	public function options(Request $request){
		return [
			'Failed' => 'failed',
		];
	}
}
