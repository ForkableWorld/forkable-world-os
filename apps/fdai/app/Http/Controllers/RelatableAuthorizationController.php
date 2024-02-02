<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\AstralRequest;

class RelatableAuthorizationController extends Controller
{
    /**
     * Get the relatable authorization status for the resource.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function show(AstralRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $model = $request->findParentModelOrFail();

        $resource = $request->viaResource();

        if (in_array($request->relationshipType, ['belongsToMany', 'morphToMany'])) {
            return ['authorized' => (new $resource($model))->authorizedToAttachAny(
                $request, $request->model()
            )];
        }

        return ['authorized' => (new $resource($model))->authorizedToAdd(
            $request, $request->model()
        )];
    }
}
