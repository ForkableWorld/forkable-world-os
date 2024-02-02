<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpMissingReturnTypeInspection */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Actions\Actionable;
use App\Http\Requests\ForceDeleteResourceRequest;
use App\Astral;

class ResourceForceDeleteController extends Controller
{
    use DeletesFields;

    /**
     * Force delete the given resource(s).
     *
     * @param  \App\Http\Requests\ForceDeleteResourceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function handle(ForceDeleteResourceRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        $request->chunks(150, function ($models) use ($request) {
            $models->each(function ($model) use ($request) {
                DB::transaction(function () use ($request, $model) {
                    $this->forceDeleteFields($request, $model);

                    if (in_array(Actionable::class, class_uses_recursive($model))) {
                        $model->actions()->delete();
                    }

                    $model->forceDelete();

                    DB::table('action_events')->insert(
                        Astral::actionEvent()->forResourceDelete($request->user(), collect([$model]))
                                    ->map->getAttributes()->all()
                    );
                });
            });
        });
    }
}
