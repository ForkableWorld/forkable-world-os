<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\DeleteField;
use App\Fields\File;
use App\Http\Requests\AstralRequest;
use App\Astral;

class FieldDestroyController extends Controller
{
    /**
     * Delete the file at the given field.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function handle(AstralRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $resource = $request->findResourceOrFail();

        $resource->authorizeToUpdate($request);

        $field = $resource->updateFields($request)
                    ->whereInstanceOf(File::class)
                    ->findFieldByAttribute($request->field, function () {
                        abort(404);
                    });

        DeleteField::forRequest(
            $request, $field, $resource->resource
        )->save();

        Astral::actionEvent()->forResourceUpdate(
            $request->user(), $resource->resource
        )->save();
    }
}
