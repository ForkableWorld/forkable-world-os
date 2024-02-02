<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Lenses;
use App\VariableCategories\EconomicIndicatorsVariableCategory;
use App\Variables\QMVariableCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Requests\LensRequest;
use App\Http\Requests\AstralRequest;
class EconomicIndicatorsLens extends VariableCategoryLens {
	/**
	 * Get the query builder / paginator for the lens.
	 * @param LensRequest $request
	 * @param Builder $query
	 * @return mixed
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public static function query(LensRequest $request, $query){
		return parent::query($request, $query);
	}
	/**
	 * Get the cards available on the lens.
	 * @param Request $request
	 * @return array
	 */
	public function cards(Request $request): array{
		$r = $this->getResource();
		return [//new AnalysisProgressPartition($r->getModelClass())
		];
	}
	/**
	 * Get the filters available for the lens.
	 * @param Request $request
	 * @return array
	 */
	public function filters(Request $request): array{
		return parent::filters($request);
	}
	/**
	 * Get the actions available on the lens.
	 * @param Request $request
	 * @return array
	 */
	public function actions(Request $request): array{
		return parent::actions($request);
	}
	/**
	 * Get the fields displayed by the resource.
	 * @param Request|AstralRequest $request
	 * @return array
	 */
	public static function getQMVariableCategory(): QMVariableCategory{
		return EconomicIndicatorsVariableCategory::instance();
	}
}
