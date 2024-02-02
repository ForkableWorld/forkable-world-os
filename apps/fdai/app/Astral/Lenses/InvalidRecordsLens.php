<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Lenses;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Properties\BaseProperty;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Fields\ID;
use App\Http\Requests\LensRequest;
class InvalidRecordsLens extends QMLens {
	public $filters;
	protected $property;
	public function __construct(BaseProperty $property){
		$this->property = $property;
		parent::__construct($property->getAstralResource());
	}
	/**
	 * Get the query builder / paginator for the lens.
	 * @param LensRequest $request
	 * @param Builder $query
	 * @return mixed
	 */
	public static function query(LensRequest $request, $query){
		return $request->withOrdering($request->withFilters($query
			//                ->select( [
			//                    UserVariableRelationship::FIELD_ID,
			//                    UserVariableRelationship::FIELD_ANALYSIS_STARTED_AT,
			//                    UserVariableRelationship::FIELD_INTERNAL_ERROR_MESSAGE,
			//                ])
			->whereNotNull(UserVariableRelationship::FIELD_INTERNAL_ERROR_MESSAGE)
			->orderBy(UserVariableRelationship::FIELD_ANALYSIS_STARTED_AT, BaseModel::ORDER_DIRECTION_DESC)));
	}
	/**
	 * Get the fields available to the lens.
	 * @param Request $request
	 * @return array
	 */
	public function fields(Request $request){
		return [
			ID::make('ID', 'id')->sortable(),
		];
	}
	/**
	 * Get the cards available on the lens.
	 * @param Request $request
	 * @return array
	 */
	public function cards(Request $request): array{
		return parent::cards($request);
	}
	/**
	 * Get the filters available for the lens.
	 * @param Request $request
	 * @return array
	 */
	public function filters(Request $request){
		return [];
	}
	/**
	 * Get the actions available on the lens.
	 * @param Request $request
	 * @return array
	 */
	public function actions(Request $request){
		return parent::actions($request);
	}
}
