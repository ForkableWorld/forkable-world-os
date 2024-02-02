<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use DateTime;
use App\Http\Requests\AstralRequest;

class MorphedResourceAttachController extends ResourceAttachController
{
    /**
     * Initialize a fresh pivot model for the relationship.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  \Illuminate\Database\Eloquent\Relations\MorphToMany  $relationship
     * @return \Illuminate\Database\Eloquent\Pivot
     */
    protected function initializePivot(AstralRequest $request, $relationship)
    {
        ($pivot = $relationship->newPivot())->forceFill([
            $relationship->getForeignPivotKeyName() => $request->resourceId,
            $relationship->getRelatedPivotKeyName() => $request->input($request->relatedResource),
            $relationship->getMorphType() => $request->findModelOrFail()->{$request->viaRelationship}()->getMorphClass(),
        ]);

        if ($relationship->withTimestamps) {
            $pivot->forceFill([
                $relationship->createdAt() => new DateTime,
                $relationship->updatedAt() => new DateTime,
            ]);
        }

        return $pivot;
    }
}
