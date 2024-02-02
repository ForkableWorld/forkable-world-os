<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use App\Logging\QMLog;
use Illuminate\Routing\Controller;
use App\Http\Requests\AstralRequest;
use App\Http\Requests\ResourceIndexRequest;
use App\AstralResource;
class ResourceIndexController extends Controller
{
    /**
     * List the resources for administration.
     *
     * @param  \App\Http\Requests\ResourceIndexRequest  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function handle(ResourceIndexRequest $request){
        AstralRequest::setInMemory($request);
        $resource = $request->resource();
        try {
            $paginator = $this->paginator($request, $resource);
        } catch (\Throwable $e){
            QMLog::error(__METHOD__.": ".$e->getMessage());
            $paginator = $this->paginator($request, $resource);
        }
        AstralRequest::setInMemory($request); // Not sure how to get access to ResourceIndexRequest otherwise
        $deletes = $resource::softDeletes();
        $collection = $paginator->getCollection();
        $mapInto = $collection->mapInto($resource);
        /** @var AstralResource $map */
        $map = $mapInto->map;
        $resources = $map->serializeForIndex($request);
        return response()->json([
            'label' => $resource::label(),
            'resources' => $resources,
            'prev_page_url' => $paginator->previousPageUrl(),
            'next_page_url' => $paginator->nextPageUrl(),
            'per_page' => $paginator->perPage(),
            'per_page_options' => $resource::perPageOptions(),
            'softDeletes' => $deletes,
        ]);
    }

    /**
     * Get the paginator instance for the index request.
     *
     * @param  \App\Http\Requests\ResourceIndexRequest  $request
     * @param  string  $resource
     * @return \Illuminate\Contracts\Pagination\Paginator|\Illuminate\Pagination\Paginator
     */
    protected function paginator(ResourceIndexRequest $request, $resource)
    {
        $q = $request->toQuery();
        /** @var AstralResource $resource */
        return $q->simplePaginate(
            $request->viaRelationship()
                        ? $resource::$perPageViaRelationship
                        : ($request->perPage ?? $resource::perPageOptions()[0])
        );
    }
}
