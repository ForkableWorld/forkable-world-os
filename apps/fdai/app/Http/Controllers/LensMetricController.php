<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\LensMetricRequest;

class LensMetricController extends Controller
{
    /**
     * List the metrics for the given resource.
     *
     * @param  \App\Http\Requests\LensMetricRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(LensMetricRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        return response()->json(
            $request->availableMetrics()
        );
    }

    /**
     * Get the specified metric's value.
     *
     * @param  \App\Http\Requests\LensMetricRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function show(LensMetricRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        return response()->json([
            'value' => $request->metric()->resolve($request),
        ]);
    }
}
