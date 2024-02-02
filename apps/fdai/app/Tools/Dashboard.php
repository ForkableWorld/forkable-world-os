<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Tools;

use App\Tool;

class Dashboard extends Tool
{
    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return \Illuminate\View\View
     */
    public function renderNavigation(): \Illuminate\View\View
    {
        return view('astral::dashboard.navigation');
    }
}
