<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Contracts\RelatableField;
use App\Http\Requests\AstralRequest;
use App\Astral;

class MorphableController extends Controller
{
    /**
     * List the available morphable resources for a given resource.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(AstralRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $relatedResource = Astral::resourceForKey($request->type);

        $field = $request->newResource()
                        ->availableFields($request)
                        ->whereInstanceOf(RelatableField::class)
                        ->findFieldByAttribute($request->field);

        $withTrashed = $this->shouldIncludeTrashed(
            $request, $relatedResource
        );

        return [
            'resources' => $field->buildMorphableQuery($request, $relatedResource, $withTrashed)->get()
                                ->mapInto($relatedResource)
                                ->filter->authorizedToAdd($request, $request->model())
                                ->map(function ($resource) use ($request, $field, $relatedResource) {
                                    return $field->formatMorphableResource($request, $resource, $relatedResource);
                                })->sortBy('display')->values(),
            'withTrashed' => $withTrashed,
            'softDeletes' => $relatedResource::softDeletes(),
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

        if ($request->current && empty($request->search) && $associatedResource::softDeletes()) {
            $associatedModel = $associatedModel->newQueryWithoutScopes()->find($request->current);

            return $associatedModel ? $associatedModel->trashed() : false;
        }

        return false;
    }
}
