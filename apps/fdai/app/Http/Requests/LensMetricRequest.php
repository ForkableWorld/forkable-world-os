<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Requests;

use App\Metrics\Metric;

class LensMetricRequest extends MetricRequest
{
    use InteractsWithLenses;

    /**
     * Get all of the possible metrics for the request.
     *
     * @return \Illuminate\Support\Collection
     */
    public function availableMetrics()
    {
        return $this->lens()->availableCards($this)
                ->whereInstanceOf(Metric::class);
    }
}
