<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Lenses;
use App\Astral\Metrics\InvalidAnalyzablesPartition;
use App\Astral\BaseAstralAstralResource;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipAnalysisStartedAtProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipCauseVariableCategoryIdProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipEffectFollowUpPercentChangeFromBaselineProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipEffectVariableCategoryIdProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipIdProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipInternalErrorMessageProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipNumberOfCorrelationsProperty;
use Illuminate\Http\Request;
use App\Http\Requests\AstralRequest;
abstract class GlobalVariableRelationshipInvalidLens extends FailedAnalysesLens {
	/**
	 * Get the fields displayed by the resource.
	 * @param Request|AstralRequest $request
	 * @return array
	 */
	public function fields(Request $request): array{
		/** @var BaseAstralAstralResource $res */
		$res = $request->newResourceWith($this->resource);
		$fields[] = $res->getNameField();
		$fields[] = GlobalVariableRelationshipEffectFollowUpPercentChangeFromBaselineProperty::field(null, null);
		$fields[] = GlobalVariableRelationshipNumberOfCorrelationsProperty::field(null, null);
		$fields[] = GlobalVariableRelationshipCauseVariableCategoryIdProperty::field(null, null);
		$fields[] = GlobalVariableRelationshipEffectVariableCategoryIdProperty::field(null, null);
		$fields[] = GlobalVariableRelationshipAnalysisStartedAtProperty::field(null, null);
		$fields[] = GlobalVariableRelationshipInternalErrorMessageProperty::field(null, null);
		// Need ID for deletion https://astral.laravel.com/docs/3.0/lenses/defining-lenses.html
		$fields[] = GlobalVariableRelationshipIdProperty::field(null, null);
		return $fields;
	}
	public function cards(Request $request): array{
		return [
			new InvalidAnalyzablesPartition(GlobalVariableRelationshipEffectFollowUpPercentChangeFromBaselineProperty::class),
		];
	}
}
