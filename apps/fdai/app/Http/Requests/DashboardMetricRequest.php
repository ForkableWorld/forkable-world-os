<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Requests;

use App\Metrics\Metric;
use App\Astral;

class DashboardMetricRequest extends AstralRequest
{
    /**
     * Get the metric instance for the given request.
     *
     * @return \App\Metrics\Metric
     */
    public function metric()
    {
        return $this->availableMetrics()->first(function ($metric) {
            return $this->metric === $metric->uriKey();
        }) ?: abort(404);
    }

    /**
     * Get all of the possible metrics for the request.
     *
     * @return \Illuminate\Support\Collection
     */
    public function availableMetrics()
    {
        return Astral::allAvailableDashboardCards($this)->whereInstanceOf(Metric::class);
    }
}
