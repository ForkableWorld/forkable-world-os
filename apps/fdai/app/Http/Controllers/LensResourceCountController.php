<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\LensCountRequest;

class LensResourceCountController extends Controller
{
    /**
     * Get the resource count for a given query.
     *
     * @param  \App\Http\Requests\LensCountRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function show(LensCountRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        return response()->json(['count' => $request->toCount()]);
    }
}
