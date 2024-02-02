<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Requests;

use App\Fields\File;
use App\Astral;

class PivotFieldDestroyRequest extends AstralRequest
{
    /**
     * Authorize that the user may attach resources of the given type.
     *
     * @return void
     */
    public function authorizeForAttachment()
    {
        if (! $this->newResourceWith($this->findModelOrFail())->authorizedToAttach(
            $this, $this->findRelatedModel()
        )) {
            abort(403);
        }
    }

    /**
     * Get the pivot model for the relationship.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findPivotModel()
    {
        return once(function () {
            $model = $this->findModelOrFail();

            return $this->findRelatedModel()->{
                $model->{$this->viaRelationship}()->getPivotAccessor()
            };
        });
    }

    /**
     * Find the related resource for the operation.
     *
     * @return \App\AstralResource
     */
    public function findRelatedResource()
    {
        $related = $this->findRelatedModel();

        $resource = Astral::resourceForModel($related);

        return new $resource($related);
    }

    /**
     * Find the related model for the operation.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findRelatedModel()
    {
        return once(function () {
            return $this->findModelOrFail()->{$this->viaRelationship}()
                        ->withoutGlobalScopes()
                        ->lockForUpdate()
                        ->findOrFail($this->relatedResourceId);
        });
    }

    /**
     * Find the field being deleted or fail if it is not found.
     *
     * @return \App\Fields\Field
     */
    public function findFieldOrFail()
    {
        return $this->findRelatedResource()->resolvePivotFields($this, $this->resource)
            ->whereInstanceOf(File::class)
            ->findFieldByAttribute($this->field, function () {
                abort(404);
            });
    }
}
