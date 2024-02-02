<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controller;
use App\Contracts\RelatableField;
use App\Http\Requests\AstralRequest;
use App\AstralResource;

class AssociatableController extends Controller
{
    /**
     * List the available related resources for a given resource.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return array|\Illuminate\Http\Response
     */
    public function index(AstralRequest $request)
    {
        AstralRequest::setInMemory($request);
        $res = $request->newResource();
        $available = $res->availableFields($request);
        $relatable = $available->whereInstanceOf(RelatableField::class);
        $rField = $request->field;
        $field = $relatable->findFieldByAttribute($rField);
        if(!is_object($field)){le("Field named $rField not found. Available relatable fields: ", $relatable);}
        /** @var AstralResource $associatedResource */
        $withTrashed = $this->shouldIncludeTrashed(
            $request, $associatedResource = $field->resourceClass
        );
        $query = $field->buildAssociatableQuery($request, $withTrashed);
        $models = $query->get();
        $mapped =  $models->mapInto($field->resourceClass);
        $authorized = $mapped->filter->authorizedToAdd($request, $request->model());
        $formatted = $authorized->map(function ($resource) use ($request, $field) {
            return $field->formatAssociatableResource($request, $resource);
        });
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        if(empty($query->getQuery()->orders[0])){
            $formatted = $formatted->sortBy('display');
        }
        return [
            'resources' => $formatted->values(),
            'softDeletes' => $associatedResource::softDeletes(),
            'withTrashed' => $withTrashed,
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

        /** @var AstralResource $associatedResource */
        $associatedModel = $associatedResource::newModel();

        if ($request->current && empty($request->search) && $associatedResource::softDeletes()) {
            /** @var Model $associatedModel */
            $associatedModel = $associatedModel->newQueryWithoutScopes()->find($request->current);

            return $associatedModel ? $associatedModel->trashed() : false;
        }

        return false;
    }
}
