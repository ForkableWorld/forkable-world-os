<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\AstralRequest;

class FilterController extends Controller
{
    /**
     * List the filters for the given resource.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(AstralRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        return response()->json($request->newResource()->availableFilters($request));
    }
}
