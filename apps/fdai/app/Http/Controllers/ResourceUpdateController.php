<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\AstralRequest;
use App\Http\Requests\UpdateResourceRequest;
use App\Astral;

class ResourceUpdateController extends Controller
{
    /**
     * Create a new resource.
     *
     * @param  \App\Http\Requests\UpdateResourceRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(UpdateResourceRequest $request)
    {
        AstralRequest::setInMemory($request);
        [$model, $resource] = DB::transaction(function () use ($request) {
            $model = $request->findModelQuery()
                // TODO: Not sure why lockForUpdate() keeps causing deadlock errors
                // ->lockForUpdate()
                ->firstOrFail();

            $resource = $request->newResourceWith($model);
            $resource->authorizeToUpdate($request);
            $resource::validateForUpdate($request, $resource);

            if ($this->modelHasBeenUpdatedSinceRetrieval($request, $model)) {
                return response('', 409)->throwResponse();
            }

            [$model, $callbacks] = $resource::fillForUpdate($request, $model);

            Astral::actionEvent()->forResourceUpdate($request->user(), $model)->save();

            $model->save();

            collect($callbacks)->each->__invoke();

            return [$model, $resource];
        });

        return response()->json([
            'id' => $model->getKey(),
            'resource' => $model->attributesToArray(),
            'redirect' => $resource::redirectAfterUpdate($request, $resource),
        ]);
    }

    /**
     * Determine if the model has been updated since it was retrieved.
     *
     * @param  \App\Http\Requests\UpdateResourceRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    protected function modelHasBeenUpdatedSinceRetrieval(UpdateResourceRequest $request, $model)
    {
        $resource = $request->newResource();

        // Check to see whether Traffic Cop is enabled for this resource...
        if ($resource::trafficCop($request) === false) {
            return false;
        }

        $column = $model->getUpdatedAtColumn();

        if (! $model->{$column}) {
            return false;
        }

        return $request->input('_retrieved_at') && $model->usesTimestamps() && $model->{$column}->gt(
            Carbon::createFromTimestamp($request->input('_retrieved_at'))
        );
    }
}
