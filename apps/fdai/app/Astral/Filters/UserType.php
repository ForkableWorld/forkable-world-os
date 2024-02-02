<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Filters;
use App\Models\User;
use App\Properties\User\UserIdProperty;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Filters\BooleanFilter;
class UserType extends BooleanFilter {
	/**
	 * Apply the filter to the given query.
	 * @param Request $request
	 * @param Builder $query
	 * @param mixed $value
	 * @return Builder
	 */
	public function apply(Request $request, $query, $value){
		if($value['test_user']){
			$query->whereIn(User::FIELD_ID, UserIdProperty::getTestUserIds());
		}
		if($value['admin']){
			$query->where(User::FIELD_ROLES, \App\Storage\DB\ReadonlyDB::like(), "%admin%");
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
			'Administrator' => 'admin',
			'Test User' => 'test_user',
		];
	}
}
