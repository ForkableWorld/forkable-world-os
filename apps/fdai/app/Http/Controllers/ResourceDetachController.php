<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Contracts\Deletable;
use App\DeleteField;
use App\Http\Requests\DetachResourceRequest;
use App\Astral;

class ResourceDetachController extends Controller
{
    /**
     * Detach the given resource(s).
     *
     * @param  \App\Http\Requests\DetachResourceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function handle(DetachResourceRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $request->chunks(150, function ($models) use ($request) {
            $parent = $request->findParentModelOrFail();

            foreach ($models as $model) {
                DB::transaction(function () use ($request, $model, $parent) {
                    $this->deletePivotFields(
                        $request, $resource = $request->newResourceWith($model),
                        $pivot = $model->{$parent->{$request->viaRelationship}()->getPivotAccessor()}
                    );

                    $pivot->delete();

                    DB::table('action_events')->insert(
                        Astral::actionEvent()->forResourceDetach(
                            $request->user(), $parent, collect([$model]), $pivot->getMorphClass()
                        )->map->getAttributes()->all()
                    );
                });
            }
        });
    }

    /**
     * Delete the pivot fields on the given pivot model.
     *
     * @param  \App\Http\Requests\DetachResourceRequest  $request
     * @param  \App\AstralResource  $resource
     * @param  \Illuminate\Database\Eloquent\Model
     * @return void
     */
    protected function deletePivotFields(DetachResourceRequest $request, $resource, $pivot)
    {
        $resource->resolvePivotFields($request, $request->viaResource)
            ->whereInstanceOf(Deletable::class)
            ->filter->isPrunable()
            ->each(function ($field) use ($request, $pivot) {
                DeleteField::forRequest($request, $field, $pivot)->save();
            });
    }
}
