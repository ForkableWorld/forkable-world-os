<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use App\Contracts\Deletable;
use App\DeleteField;
use App\Http\Requests\AstralRequest;

trait DeletesFields
{
    /**
     * Delete the deletable fields on the given model / resource.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    protected function forceDeleteFields(AstralRequest $request, $model)
    {
        return $this->deleteFields($request, $model, false);
    }

    /**
     * Delete the deletable fields on the given model / resource.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  bool  $skipSoftDeletes
     * @return void
     */
    protected function deleteFields(AstralRequest $request, $model, $skipSoftDeletes = true)
    {
        if ($skipSoftDeletes && $request->newResourceWith($model)->softDeletes()) {
            return;
        }

        $request->newResourceWith($model)
                    ->detailFields($request)
                    ->whereInstanceOf(Deletable::class)
                    ->filter->isPrunable()
                    ->each(function ($field) use ($request, $model) {
                        DeleteField::forRequest($request, $field, $model);
                    });
    }
}
