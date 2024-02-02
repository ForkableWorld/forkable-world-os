<?php

declare(strict_types=1);

namespace App\DataSources\Connectors\Fitbit\Activity;

use Carbon\Carbon;
use App\DataSources\Connectors\Fitbit\Activity\Resource\AbstractResource;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;

class TimeSeries
{
    private $client;

    public function __construct(Fitbit $fitbit)
    {
        $this->fitbit = $fitbit;
    }

    /**
     * Returns time series data in the specified period from the specified date
     * for a given resource in the format requested using units in the unit system that corresponds
     * to the Accept-Language header provided.
     *
     * @param AbstractResource $resource
     * @param Carbon $date
     * @param Period $period
     */
    public function getByPeriod(AbstractResource $resource, Carbon $date, Period $period)
    {
        return $this->fitbit->get(implode('/', [
            $resource,
            'date',
            $date->format('Y-m-d'),
            $period,
          ]) . '.json');
    }

    /**
     * Returns time series data in the specified range
     * for a given resource in the format requested using units in the unit system that corresponds
     * to the Accept-Language header provided.
     *
     * @param AbstractResource $resource
     * @param Carbon $baseDate
     * @param Carbon $endDate
     */
    public function getByDateRange(
        AbstractResource $resource,
        Carbon $baseDate,
        Carbon $endDate
    ) {
        return $this->fitbit->get(implode('/', [
            $resource,
            'date',
            $baseDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
          ]) . '.json');
    }
}
