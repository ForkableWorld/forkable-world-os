<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Query;

use App\TrashedStatus;

class ApplySoftDeleteConstraint
{
    /**
     * Apply the trashed state constraint to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $withTrashed
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function __invoke($query, $withTrashed)
    {
        if ($withTrashed == TrashedStatus::WITH) {
            $query = $query->withTrashed();
        } elseif ($withTrashed == TrashedStatus::ONLY) {
            $query = $query->onlyTrashed();
        }

        return $query;
    }
}
