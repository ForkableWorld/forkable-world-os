<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Lenses;
use App\Models\BaseModel;
use App\Models\Variable;
use App\Astral\Metrics\AnalysisProgressPartition;
use App\Properties\Variable\VariableDefaultUnitIdProperty;
use App\Properties\Variable\VariableImageUrlProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Variables\QMVariableCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Requests\LensRequest;
use App\Http\Requests\AstralRequest;
abstract class VariableCategoryLens extends QMLens {
	/**
	 * Get the query builder / paginator for the lens.
	 * @param LensRequest $request
	 * @param Builder $query
	 * @return mixed
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public static function query(LensRequest $request, $query){
		return $request->withOrdering($request->withFilters($query->where(Variable::FIELD_VARIABLE_CATEGORY_ID,
				static::getQMVariableCategory()->id)
			->orderBy(Variable::FIELD_NUMBER_OF_USER_VARIABLES, BaseModel::ORDER_DIRECTION_DESC)));
	}
	/**
	 * Get the cards available on the lens.
	 * @param Request $request
	 * @return array
	 */
	public function cards(Request $request){
		return [
			new AnalysisProgressPartition($this->getResource()->getModelClass()),
		];
	}
	/**
	 * Get the filters available for the lens.
	 * @param Request $request
	 * @return array
	 */
	public function filters(Request $request){
		return parent::filters($request);
	}
	/**
	 * Get the actions available on the lens.
	 * @param Request $request
	 * @return array
	 */
	public function actions(Request $request){
		return parent::actions($request);
	}
	/**
	 * Get the fields displayed by the resource.
	 * @param Request|AstralRequest $request
	 * @return array
	 */
	public function fields(Request $request): array{
		return [
			VariableImageUrlProperty::field(null, null),
			VariableNameProperty::field(null, null),
			VariableDefaultUnitIdProperty::field(null, null),
		];
	}
	abstract public static function getQMVariableCategory(): QMVariableCategory;
}
