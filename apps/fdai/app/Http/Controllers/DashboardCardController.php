<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\DashboardCardRequest;

class DashboardCardController extends Controller
{
    /**
     * List the cards for the dashboard.
     *
     * @param  \App\Http\Requests\DashboardCardRequest  $request
     * @param  string  $dashboard
     * @return \Illuminate\Http\Response
     */
    public function index(DashboardCardRequest $request, $dashboard = 'main')
    {
        return $request->availableCards($dashboard);
    }
}
