<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\AstralRequest;
use App\Astral;

class AttachedResourceUpdateController extends Controller
{
    /**
     * Update an attached resource pivot record.
     * @param  AstralRequest $request
     * @return Response
     */
    public function handle(AstralRequest $request): Response{
        AstralRequest::setInMemory($request);
        $this->validate(
            $request, $model = $request->findModelOrFail(),
            $resource = $request->resource()
        );

        return DB::transaction(function () use ($request, $resource, $model) {
            $model->setRelationAndAddToMemory(
                $model->{$request->viaRelationship}()->getPivotAccessor(),
                $pivot = $this->findPivot($request, $model)
            );

            if ($this->modelHasBeenUpdatedSinceRetrieval($request, $pivot)) {
                return response('', 409);
            }

            [$pivot, $callbacks] = $resource::fillPivot($request, $model, $pivot);

            Astral::actionEvent()->forAttachedResourceUpdate($request, $model, $pivot)->save();

            $pivot->save();

            collect($callbacks)->each->__invoke();
        });
    }

    /**
     * Validate the attachment request.
     * @param  AstralRequest $request
     * @param Model $model
     * @param string $resource
     * @return void
     */
    protected function validate(AstralRequest $request, Model $model, string $resource)
    {
        $attribute = $resource::validationAttributeFor(
            $request, $request->relatedResource
        );

        Validator::make($request->all(), $resource::updateRulesFor(
            $request,
            $request->relatedResource
        ), [], [$request->relatedResource => $attribute])->validate();

        $resource::validateForAttachmentUpdate($request);
    }

    /**
     * Find the pivot model for the operation.
     * @param  AstralRequest  $request
     * @param Model $model
     * @return Model
     */
    protected function findPivot(AstralRequest $request, Model $model): Model{
        $pivot = $model->{$request->viaRelationship}()->getPivotAccessor();

        return $model->{$request->viaRelationship}()
                    ->withoutGlobalScopes()
                    ->lockForUpdate()
                    ->findOrFail($request->relatedResourceId)->{$pivot};
    }

    /**
     * Determine if the model has been updated since it was retrieved.
     * @param  AstralRequest  $request
     * @param Model $model
     * @return false|void
     */
    protected function modelHasBeenUpdatedSinceRetrieval(AstralRequest $request, Model $model)
    {
        $column = $model->getUpdatedAtColumn();

        if (! $model->{$column}) {
            return false;
        }

        return $request->input('_retrieved_at') && $model->usesTimestamps() && $model->{$column}->gt(
            Carbon::createFromTimestamp($request->input('_retrieved_at'))
        );
    }
}
