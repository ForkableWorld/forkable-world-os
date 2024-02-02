<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\AstralRequest;

class FieldController extends Controller
{
    /**
     * Retrieve the given field for the given resource.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function show(AstralRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        return response()->json(
            $request->newResource()
                    ->availableFields($request)
                    ->findFieldByAttribute($request->field, function () {
                        abort(404);
                    })
        );
    }
}
