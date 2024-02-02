<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use Illuminate\Routing\Controller;
use App\GlobalSearch;
use App\Http\Requests\AstralRequest;
use App\Astral;
class SearchController extends Controller
{
    /**
     * Get the global search results for the given query.
     *
     * @param  \App\Http\Requests\AstralRequest $request
     * @return array|\Illuminate\Http\Response
     */
    public function index(AstralRequest $request){
        $resources = Astral::globallySearchableResources($request);
        $search = new GlobalSearch($request, $resources);
        $results = $search->get();
        return $results;
    }
}
