<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use App\Http\Requests\AstralRequest;
use App\Astral;

class ScriptController extends Controller
{
    /**
     * Serve the requested script.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function show(AstralRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $path = Arr::get(Astral::allScripts(), $request->script);

        abort_if(is_null($path), 404);

        return response(
            file_get_contents($path),
            200,
            [
                'Content-Type' => 'application/javascript',
            ]
        )->setLastModified(DateTime::createFromFormat('U', filemtime($path)));
    }
}
