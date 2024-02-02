<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\DashboardMetricRequest;

class DashboardMetricController extends Controller
{
    /**
     * List the metrics for the dashboard.
     *
     * @param  \App\Http\Requests\DashboardMetricRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(DashboardMetricRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        return $request->availableMetrics();
    }

    /**
     * Get the specified metric's value.
     *
     * @param  \App\Http\Requests\DashboardMetricRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function show(DashboardMetricRequest $request)
    {
        \App\Http\Requests\AstralRequest::setInMemory($request);
        return response()->json([
            'value' => $request->metric()->resolve($request),
        ]);
    }
}
