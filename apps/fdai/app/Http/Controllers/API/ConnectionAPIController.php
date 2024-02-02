<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\API;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Class ConnectionController
 * @package App\Http\Controllers\API
 */
class ConnectionAPIController extends BaseAPIController {
    public function __construct() {
        parent::__construct();
        $this->with = ['user'];
    }

    /**
     * @param Request $request
     * @return JsonResponse|object
     * @throws UnauthorizedException
     */
    public function store(Request $request){
        $this->authorize('create');
        $class = $this->getModelClass();
        $existing = $class::findByData($request->all());
        if ($existing) {
            $existing->authorize('update');
            $existing->update($request->all());
            return $this->respondWithJsonResource($existing, 201);
        }
        return parent::store($request);
    }

}
