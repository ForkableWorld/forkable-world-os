<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\API;
use App\Exceptions\InvalidClientException;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\BaseAPIController;
use App\Http\Resources\MeasurementResource;
use App\Http\Resources\UserVariableResource;
use App\Models\UserVariable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
/** Class MeasurementController
 * @package App\Http\Controllers\API
 */
class MeasurementAPIController extends BaseAPIController {
	/**
	 * @throws InvalidClientException
	 * @throws UnauthorizedException
	 */
	public function store(Request $request){
		$models = $this->saveModels($request);
		$measurementCollection = MeasurementResource::collection($models);
		$userVariables = UserVariable::getAllFromMemoryIndexedById();
		$userVariableCollection = UserVariableResource::collection($userVariables);
		return new JsonResponse([
			'measurements' => $measurementCollection,
			'user_variables' => $userVariableCollection,
		], 201);
	}
}
