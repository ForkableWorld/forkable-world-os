<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Fields\File;
use App\Http\Requests\AstralRequest;

class FieldDownloadController extends Controller
{
    /**
     * Download the given field's contents.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function show(AstralRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $resource = $request->findResourceOrFail();

        $resource->authorizeToView($request);

        return $resource->detailFields($request)
                    ->whereInstanceOf(File::class)
                    ->findFieldByAttribute($request->field, function () {
                        abort(404);
                    })->toDownloadResponse($request, $resource);
    }
}
