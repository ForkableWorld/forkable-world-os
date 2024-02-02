<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Requests;

use App\Astral;

class DashboardCardRequest extends AstralRequest
{
    /**
     * Get all of the possible cards for the request.
     *
     * @param  string  $dashboard
     *
     * @return \Illuminate\Support\Collection
     */
    public function availableCards($dashboard)
    {
        if ($dashboard === 'main') {
            return collect(Astral::$defaultDashboardCards)
                ->unique()
                ->filter
                ->authorize($this)
                ->values();
        }

        return Astral::availableDashboardCardsForDashboard($dashboard, $this);
    }
}
