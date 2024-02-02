<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\AstralRequest;

class SoftDeleteStatusController extends Controller
{
    /**
     * Determine if the resource is soft deleting.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function show(AstralRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $resource = $request->resource();

        return response()->json(['softDeletes' => $resource::softDeletes()]);
    }
}
