<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\AstralRequest;

class CreationPivotFieldController extends Controller
{
    /**
     * List the pivot fields for the given resource and relation.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(AstralRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        return response()->json(
            $request->newResource()->creationPivotFields(
                $request,
                $request->relatedResource
            )->all()
        );
    }
}
