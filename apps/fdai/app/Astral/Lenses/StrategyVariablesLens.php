<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Lenses;
use App\Properties\Variable\VariableEarliestNonTaggedMeasurementStartAtProperty;
use App\Properties\Variable\VariableImageUrlProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Properties\Variable\VariableNumberOfMeasurementsProperty;
use Illuminate\Http\Request;
use App\Http\Requests\AstralRequest;
class StrategyVariablesLens extends StrategyUserVariablesLens {
	/**
	 * Get the fields displayed by the resource.
	 * @param Request|AstralRequest $request
	 * @return array
	 */
	public function fields(Request $request): array{
		return [
			VariableImageUrlProperty::field(null, null),
			VariableNameProperty::field(null, null),
			VariableNumberOfMeasurementsProperty::field(null, null),
			VariableEarliestNonTaggedMeasurementStartAtProperty::field(null, null),
		];
	}
}
