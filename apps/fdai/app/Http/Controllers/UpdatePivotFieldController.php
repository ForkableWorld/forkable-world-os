<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Requests\AstralRequest;

class UpdatePivotFieldController extends Controller
{
    /**
     * List the pivot fields for the given resource and relation.
     * @param  AstralRequest $request
     * @return JsonResponse
     */
    public function index(AstralRequest $request): JsonResponse{
        AstralRequest::setInMemory($request);
        $model = $request->findModelOrFail();

        $model->setRelationAndAddToMemory(
            $model->{$request->viaRelationship}()->getPivotAccessor(),
            $model->{$request->viaRelationship}()->withoutGlobalScopes()->findOrFail($request->relatedResourceId)->pivot
        );

        return response()->json(
            $request->newResourceWith($model)->updatePivotFields(
                $request,
                $request->relatedResource
            )->all()
        );
    }
}
