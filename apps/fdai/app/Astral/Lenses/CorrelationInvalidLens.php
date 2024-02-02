<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Lenses;
use App\Models\BaseModel;
use App\Astral\Metrics\InvalidAnalyzablesPartition;
use App\Astral\BaseAstralAstralResource;
use App\Properties\UserVariableRelationship\CorrelationCauseNumberOfProcessedDailyMeasurementsProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseNumberOfRawMeasurementsProperty;
use App\Properties\UserVariableRelationship\CorrelationIdProperty;
use App\Traits\AnalyzableTrait;
use Illuminate\Http\Request;
use App\Http\Requests\AstralRequest;
class CorrelationInvalidLens extends FailedAnalysesLens {
	/**
	 * Get the fields displayed by the resource.
	 * @param Request|AstralRequest $request
	 * @return array
	 */
	public function fields(Request $request): array{
		/** @var BaseAstralAstralResource $res */
		$res = $request->newResourceWith($this->resource);
		/** @var BaseModel|AnalyzableTrait $model */
		$model = $res->getModel();
		$fields[] = $res->getNameField();
		$fields[] = CorrelationCauseNumberOfProcessedDailyMeasurementsProperty::field(null, null);
		$fields[] = CorrelationCauseNumberOfRawMeasurementsProperty::field(null, null);
		$fields[] = $model->analysisStarted($request);
		$fields[] = $model->internalError($request);
		$fields[] = $model->userError($request);
		// Need ID for deletion https://astral.laravel.com/docs/3.0/lenses/defining-lenses.html
		$fields[] = CorrelationIdProperty::field(null, null);
		$fields = $model->addUserFieldIfNecessary($fields);
		return $fields;
	}
	public function cards(Request $request): array{
		return [
			new InvalidAnalyzablesPartition(CorrelationCauseNumberOfProcessedDailyMeasurementsProperty::class),
		];
	}
}
