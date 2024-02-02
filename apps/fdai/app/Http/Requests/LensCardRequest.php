<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Requests;

class LensCardRequest extends CardRequest
{
    use InteractsWithLenses;

    /**
     * Get all of the possible metrics for the request.
     *
     * @return \Illuminate\Support\Collection
     */
    public function availableCards()
    {
        return $this->lens()->availableCards($this);
    }
}
