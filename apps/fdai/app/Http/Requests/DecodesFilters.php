<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Requests;

use App\FilterDecoder;

trait DecodesFilters
{
    /**
     * Get the filters for the request.
     *
     * @return array
     */
    public function filters()
    {
        $available = $this->availableFilters();
        $decoder = new FilterDecoder($this->filters, $available);
        return $decoder->filters();
    }

    /**
     * Get all of the possibly available filters for the request.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function availableFilters()
    {
        $r = $this->newResource();
        return $r->availableFilters($this);
    }
}
