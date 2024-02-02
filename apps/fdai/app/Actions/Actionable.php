<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Actions;

use App\Astral;

trait Actionable
{


    /**
     * Get all of the action events for the user.
     */
    public function actions()
    {
        return $this->morphMany(Astral::actionEvent(), 'actionable');
    }
}
