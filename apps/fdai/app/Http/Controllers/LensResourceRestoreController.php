<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RestoreLensResourceRequest;
use App\Astral;

class LensResourceRestoreController extends Controller
{
    /**
     * Force delete the given resource(s).
     *
     * @param  \App\Http\Requests\RestoreLensResourceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function handle(RestoreLensResourceRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $request->chunks(150, function ($models) use ($request) {
            $models->each(function ($model) use ($request) {
                $model->restore();

                DB::table('action_events')->insert(
                    Astral::actionEvent()->forResourceRestore($request->user(), collect([$model]))
                                ->map->getAttributes()->all()
                );
            });
        });
    }
}
