<?php

declare(strict_types=1);

namespace App\DataSources\Connectors\Fitbit\Api;

use App\DataSources\Connectors\Fitbit\Body\Fat\Fat as FatOperations;
use App\DataSources\Connectors\Fitbit\Body\Goals\Goals as GoalsOperations;
use App\DataSources\Connectors\Fitbit\Body\TimeSeries as TimeSeriesOperations;
use App\DataSources\Connectors\Fitbit\Body\Weight\Weight as WeightOperations;

class Body
{
    private $fat;
    private $goals;
    private $weight;
    private $timeSeries;

    public function __construct(Fitbit $fitbit)
    {
        $this->fat = new FatOperations($fitbit);
        $this->goals = new GoalsOperations($fitbit);
        $this->weight = new WeightOperations($fitbit);
        $this->timeSeries = new TimeSeriesOperations($fitbit);
    }

    public function fat()
    {
        return $this->fat;
    }

    public function goals()
    {
        return $this->goals;
    }

    public function weight()
    {
        return $this->weight;
    }

    public function timeSeries()
    {
        return $this->timeSeries;
    }
}
