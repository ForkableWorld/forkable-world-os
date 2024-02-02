<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;
use App\Fields\ID;
use App\Http\Requests\LensRequest;

class LensController extends Controller
{
    /**
     * List the lenses for the given resource.
     *
     * @param  \App\Http\Requests\LensRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(LensRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        return $request->availableLenses();
    }

    /**
     * Get the specified lens and its resources.
     *
     * @param  \App\Http\Requests\LensRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function show(LensRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $lens = $request->lens();

        $paginator = $lens->query($request, $request->newQuery());

        if ($paginator instanceof Builder) {
            $paginator = $paginator->simplePaginate($request->perPage ?? $request->resource()::perPageOptions()[0]);
        }

        return response()->json([
            'name' => $request->lens()->name(),
            'resources' => $request->toResources($paginator->getCollection()),
            'prev_page_url' => $paginator->previousPageUrl(),
            'next_page_url' => $paginator->nextPageUrl(),
            'per_page' => $paginator->perPage(),
            'per_page_options' => $request->resource()::perPageOptions(),
            'softDeletes' => $request->resourceSoftDeletes(),
            'hasId' => $lens->availableFields($request)->whereInstanceOf(ID::class)->isNotEmpty(),
        ]);
    }
}
