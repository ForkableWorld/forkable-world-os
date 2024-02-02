<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use App\Models\Variable;
use App\Services\VariableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class PublicVariableController extends Controller {
	public function index(Request $request, VariableService $variableService){
		$publicVariables = $variableService->searchOrGetAllPublicVariables($this->getRequest()->all());
		// We have to map combinationOperation
		$commonVariable = new Variable();
		$combinationOperations = $commonVariable->combinationOperations;
		foreach($publicVariables as $key => $variable){
			/** @var \stdClass $variable */
			$publicVariables[$key]->combinationOperation = $combinationOperations[$variable->combinationOperation];
		}
		return new JsonResponse($this->hydrateResponse($publicVariables));
	}
}
