<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use Illuminate\Routing\Controller;
use App\Http\Requests\AstralRequest;
class LensFilterController extends Controller{
    /**
     * List the lenses for the given resource.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(AstralRequest $request){
        AstralRequest::setInMemory($request);
        $res = $request->newResource();
        $lenses = $res->availableLenses($request);
        $lens = $lenses->first(function ($lens) use ($request) {
            $lensKey = $lens->uriKey();
            $reqLens = $request->lens;
            return $lensKey === $reqLens;
        });
        if(!$lens){le("Could not find lens matching $request->lens");}
        $filters = $lens->availableFilters($request);
        return response()->json($filters);
    }
}
