<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\AstralRequest;

class UpdateFieldController extends Controller
{
    /**
     * List the update fields for the given resource.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(AstralRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $resource = $request->newResourceWith($request->findModelOrFail());

        $resource->authorizeToUpdate($request);

        return response()->json([
            'fields' => $resource->updateFieldsWithinPanels($request),
            'panels' => $resource->availablePanelsForUpdate($request),
        ]);
    }
}
