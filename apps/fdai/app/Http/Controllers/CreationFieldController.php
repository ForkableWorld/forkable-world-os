<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\AstralRequest;

class CreationFieldController extends Controller
{
    /**
     * List the creation fields for the given resource.
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(AstralRequest $request)
    {
        AstralRequest::setInMemory($request);
        $resourceClass = $request->resource();

        $resourceClass::authorizeToCreate($request);

		$new =  $request->newResource();
		$fields = $new->creationFieldsWithinPanels($request);
	    $panels = $new->availablePanelsForCreate($request);
        return response()->json([
            'fields' => $fields,
            'panels' => $panels,
        ]);
    }
}
