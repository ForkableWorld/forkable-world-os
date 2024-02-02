<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\LensCardRequest;

class LensCardController extends Controller
{
    /**
     * List the cards for the given lens.
     *
     * @param  \App\Http\Requests\LensCardRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(LensCardRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        return response()->json(
            $request->availableCards()
        );
    }
}
