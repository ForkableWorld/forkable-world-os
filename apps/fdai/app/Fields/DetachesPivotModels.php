<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Fields;

use App\Contracts\Deletable;
use App\DeleteField;
use App\Astral;

trait DetachesPivotModels
{
    /**
     * Get the pivot record detachment callback for the field.
     *
     * @return \Closure
     */
    protected function detachmentCallback()
    {
        return function ($request, $model) {
            foreach ($model->{$this->attribute}()->withoutGlobalScopes()->get() as $related) {
                $resource = Astral::resourceForModel($related);

                $resource = new $resource($related);

                $pivot = $related->{$model->{$this->attribute}()->getPivotAccessor()};

                $pivotFields = $resource->resolvePivotFields($request, $request->resource);

                $pivotFields->whereInstanceOf(Deletable::class)
                        ->filter->isPrunable()
                        ->each(function ($field) use ($request, $pivot) {
                            DeleteField::forRequest($request, $field, $pivot)->save();
                        });

                $pivot->delete();
            }

            return true;
        };
    }
}
