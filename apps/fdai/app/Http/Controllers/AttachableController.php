<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Fields\BelongsToMany;
use App\Fields\MorphToMany;
use App\Http\Requests\AstralRequest;

class AttachableController extends Controller
{
    /**
     * List the available related resources for a given resource.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(AstralRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $field = $request->newResource()
                    ->availableFields($request)
                    ->filter(function ($field) {
                        return $field instanceof BelongsToMany || $field instanceof MorphToMany;
                    })
                    ->firstWhere('resourceName', $request->field);

        $withTrashed = $this->shouldIncludeTrashed(
            $request, $associatedResource = $field->resourceClass
        );

        $parentResource = $request->findResourceOrFail();

        return [
            'resources' => $field->buildAttachableQuery($request, $withTrashed)->get()
                        ->mapInto($field->resourceClass)
                        ->filter(function ($resource) use ($request, $parentResource) {
                            return $parentResource->authorizedToAttach($request, $resource->resource);
                        })
                        ->map(function ($resource) use ($request, $field) {
                            return $field->formatAttachableResource($request, $resource);
                        })->sortBy('display', SORT_NATURAL | SORT_FLAG_CASE)->values(),
            'withTrashed' => $withTrashed,
            'softDeletes' => $associatedResource::softDeletes(),
        ];
    }

    /**
     * Determine if the query should include trashed models.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  string  $associatedResource
     * @return bool
     */
    protected function shouldIncludeTrashed(AstralRequest $request, $associatedResource)
    {
        if ($request->withTrashed === 'true') {
            return true;
        }

        $associatedModel = $associatedResource::newModel();

        if ($request->current && $associatedResource::softDeletes()) {
            $associatedModel = $associatedModel->newQueryWithoutScopes()->find($request->current);

            return $associatedModel ? $associatedModel->trashed() : false;
        }

        return false;
    }
}
