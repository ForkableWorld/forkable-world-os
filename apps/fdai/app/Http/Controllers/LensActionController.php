<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\LensActionRequest;
use App\Http\Requests\LensRequest;

class LensActionController extends Controller
{
    /**
     * List the actions for the given resource.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(LensRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        return response()->json([
            'actions' => $request->lens()->availableActions($request),
            'pivotActions' => [
                'name' => $request->pivotName(),
                'actions' => $request->lens()->availablePivotActions($request),
            ],
        ]);
    }

    /**
     * Perform an action on the specified resources.
     *
     * @param  \App\Http\Requests\LensActionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(LensActionRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $request->validateFields();

        return $request->action()->handleRequest($request);
    }
}
