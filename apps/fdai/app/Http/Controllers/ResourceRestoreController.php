<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RestoreResourceRequest;
use App\Astral;

class ResourceRestoreController extends Controller
{
    /**
     * Restore the given resource(s).
     *
     * @param  \App\Http\Requests\RestoreResourceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function handle(RestoreResourceRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $request->chunks(150, function ($models) use ($request) {
            $models->each(function ($model) use ($request) {
                DB::transaction(function () use ($request, $model) {
                    $model->restore();

                    DB::table('action_events')->insert(
                        Astral::actionEvent()->forResourceRestore($request->user(), collect([$model]))
                                    ->map->getAttributes()->all()
                    );
                });
            });
        });
    }
}
