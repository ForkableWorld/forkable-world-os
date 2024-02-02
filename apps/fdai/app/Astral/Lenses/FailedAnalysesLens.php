<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Lenses;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Astral\Metrics\AnalysisProgressPartition;
use App\Astral\BaseAstralAstralResource;
use App\Traits\AnalyzableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Requests\LensRequest;
use App\Http\Requests\AstralRequest;
class FailedAnalysesLens extends QMLens {
	/**
	 * Get the query builder / paginator for the lens.
	 * @param LensRequest $request
	 * @param Builder $query
	 * @return Builder
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function query(LensRequest $request, $query){
		return $request->withOrdering($request->withFilters($query->whereNotNull(UserVariableRelationship::FIELD_INTERNAL_ERROR_MESSAGE)
			->orderBy(UserVariableRelationship::FIELD_ANALYSIS_STARTED_AT, BaseModel::ORDER_DIRECTION_DESC)));
	}
	/**
	 * Get the cards available on the lens.
	 * @param Request $request
	 * @return array
	 */
	public function cards(Request $request): array{
		return [
			new AnalysisProgressPartition($this->getResource()->getModelClass()),
		];
	}
	/**
	 * Get the fields displayed by the resource.
	 * @param Request|AstralRequest $request
	 * @return array
	 */
	public function fields(Request $request): array{
		/** @var BaseAstralAstralResource $res */
		$res = $request->newResourceWith($this->resource);
		/** @var AnalyzableTrait|BaseModel $model */
		$model = $res->getModel();
		$fields[] = $res->getNameField();
		$fields[] = $model->analysisStarted($request);
		$fields[] = $model->internalError($request);
		$fields[] = $model->userError($request);
		$fields = $model->addUserFieldIfNecessary($fields);
		return $fields;
	}
}
