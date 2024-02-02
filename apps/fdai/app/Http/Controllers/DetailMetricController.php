<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\MetricRequest;

class DetailMetricController extends Controller
{
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
