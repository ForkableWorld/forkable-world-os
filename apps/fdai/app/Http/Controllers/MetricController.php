<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\MetricRequest;

class MetricController extends Controller
{
    /**
     * List the metrics for the given resource.
     *
     * @param  \App\Http\Requests\MetricRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(MetricRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        return $request->availableMetrics();
    }

    /**
     * Get the specified metric's value.
     *
     * @param  \App\Http\Requests\MetricRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function show(MetricRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        return response()->json([
            'value' => $request->metric()->resolve($request),
        ]);
    }
}
