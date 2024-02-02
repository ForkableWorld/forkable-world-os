<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Lenses;
use App\Models\GlobalVariableRelationship;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\LensRequest;
class GlobalVariableRelationshipsWithNoUserVariableRelationshipsLens extends GlobalVariableRelationshipInvalidLens {
	public function name(): string{
		return "No User Variable Relationships";
	}
	/**
	 * Get the query builder / paginator for the lens.
	 * @param LensRequest $request
	 * @param Builder $query
	 * @return mixed
	 */
	public static function query(LensRequest $request, $query){
		return $request->withOrdering($request->withFilters($query->where(GlobalVariableRelationship::FIELD_NUMBER_OF_CORRELATIONS,
				0)->orderBy(GlobalVariableRelationship::FIELD_ANALYSIS_STARTED_AT, BaseModel::ORDER_DIRECTION_DESC)));
	}
}
