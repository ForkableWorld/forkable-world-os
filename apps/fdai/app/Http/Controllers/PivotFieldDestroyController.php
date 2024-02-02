<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\DeleteField;
use App\Http\Requests\PivotFieldDestroyRequest;
use App\Astral;

class PivotFieldDestroyController extends Controller
{
    /**
     * Delete the file at the given field.
     *
     * @param  \App\Http\Requests\PivotFieldDestroyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function handle(PivotFieldDestroyRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $request->authorizeForAttachment();

        DeleteField::forRequest(
            $request, $request->findFieldOrFail(),
            $pivot = $request->findPivotModel()
        )->save();

        Astral::actionEvent()->forAttachedResourceUpdate(
            $request, $request->findModelOrFail(), $pivot
        )->save();
    }
}
