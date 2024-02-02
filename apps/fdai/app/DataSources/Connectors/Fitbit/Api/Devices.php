<?php

declare(strict_types=1);

namespace App\DataSources\Connectors\Fitbit\Api;

use App\DataSources\Connectors\Fitbit\Devices\Alarms as AlarmsOperations;
use App\DataSources\Connectors\Fitbit\Devices\Devices as DevicesOperations;

class Devices
{
    private $devices;
    private $alarms;

    public function __construct(Fitbit $fitbit)
    {
        $this->devices = new DevicesOperations($fitbit);
        $this->alarms = new AlarmsOperations($fitbit);
    }

    public function devices()
    {
        return $this->devices;
    }

    public function alarms()
    {
        return $this->alarms;
    }
}
