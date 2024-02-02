<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Metrics;

abstract class RangedMetric extends Metric
{
    /**
     * The ranges available for the metric.
     *
     * @var array
     */
    public $ranges = [];

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return $this->ranges;
    }

    /**
     * Prepare the metric for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        return array_merge(parent::jsonSerialize(), [
            'ranges' => collect($this->ranges() ?? [])->map(function ($range, $key) {
                return ['label' => $range, 'value' => $key];
            })->values()->all(),
        ]);
    }
}
