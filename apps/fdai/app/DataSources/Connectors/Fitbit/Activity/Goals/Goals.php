<?php

declare(strict_types=1);

namespace App\DataSources\Connectors\Fitbit\Activity\Goals;

use App\DataSources\Connectors\Fitbit\Api\Fitbit;

class Goals
{
    private $fitbit;

    public function __construct(Fitbit $fitbit)
    {
        $this->fitbit = $fitbit;
    }

    /**
     * Retrieves a user's current daily or weekly activity goals using
     * measurement units as defined in the unit system, which corresponds to the Accept-Language header provided.
     *
     * @param Period $period
     */
    public function get(Period $period)
    {
        return $this->fitbit->get(implode('/', ['activities', 'goals', $period]) . '.json');
    }

    /**
     * The Update Activity Goals endpoint creates or updates a user's daily activity goals and returns a
     * response using units in the unit system which corresponds to the Accept-Language header provided.
     *
     * @param Period $period
     * @param Goal $goal
     */
    public function update(
        Period $period,
        Goal $goal
    ) {
        return $this->fitbit->post(implode('/', ['activities', 'goals', $period]) . '.json?' . $goal->asUrlParam());
    }
}
