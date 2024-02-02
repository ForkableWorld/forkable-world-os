<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Actions\Actionable;
use App\Http\Requests\DeleteLensResourceRequest;
use App\Astral;

class LensResourceDestroyController extends Controller
{
    use DeletesFields;

    /**
     * Destroy the given resource(s).
     *
     * @param  \App\Http\Requests\DeleteLensResourceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function handle(DeleteLensResourceRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $request->chunks(150, function ($models) use ($request) {
            $models->each(function ($model) use ($request) {
                $this->deleteFields($request, $model);

                if (in_array(Actionable::class, class_uses_recursive($model))) {
                    $model->actions()->delete();
                }

                $model->delete();

                DB::table('action_events')->insert(
                    Astral::actionEvent()->forResourceDelete($request->user(), collect([$model]))
                                ->map->getAttributes()->all()
                );
            });
        });
    }
}
