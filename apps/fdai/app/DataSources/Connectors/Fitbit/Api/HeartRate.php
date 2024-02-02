<?php

declare(strict_types=1);

namespace App\DataSources\Connectors\Fitbit\Api;

use App\DataSources\Connectors\Fitbit\HeartRate\HeartRate as HeartRateOperations;

class HeartRate
{
    private $heartRate;

    public function __construct(Fitbit $fitbit)
    {
        $this->heartRate = new HeartRateOperations($fitbit);
    }

    public function heartRate()
    {
        return $this->heartRate;
    }
}
