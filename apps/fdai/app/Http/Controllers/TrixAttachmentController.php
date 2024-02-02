<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\AstralRequest;

class TrixAttachmentController extends Controller
{
    /**
     * Store an attachment for a Trix field.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AstralRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $field = $request->newResource()
                        ->availableFields($request)
                        ->findFieldByAttribute($request->field, function () {
                            abort(404);
                        });

        return response()->json(['url' => call_user_func(
            $field->attachCallback, $request
        )]);
    }

    /**
     * Delete a single, persisted attachment for a Trix field by URL.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function destroyAttachment(AstralRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $field = $request->newResource()
                        ->availableFields($request)
                        ->findFieldByAttribute($request->field, function () {
                            abort(404);
                        });

        call_user_func(
            $field->detachCallback, $request
        );
    }

    /**
     * Purge all pending attachments for a Trix field.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function destroyPending(AstralRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $field = $request->newResource()
                        ->availableFields($request)
                        ->findFieldByAttribute($request->field, function () {
                            abort(404);
                        });

        call_user_func(
            $field->discardCallback, $request
        );
    }
}
