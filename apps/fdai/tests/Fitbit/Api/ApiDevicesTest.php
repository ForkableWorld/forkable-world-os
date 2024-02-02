<?php

declare(strict_types=1);

namespace Tests\Fitbit\Api;

use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Devices;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;

class ApiDevicesTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $devices;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->devices = new Devices($this->fitbit);
    }

    public function testGettingADevicesInstance()
    {
        $this->assertTrue($this->devices->devices() instanceof \App\DataSources\Connectors\Fitbit\Devices\Devices);
    }

    public function testGettingAnAlarmsInstance()
    {
        $this->assertTrue($this->devices->alarms() instanceof \App\DataSources\Connectors\Fitbit\Devices\Alarms);
    }
}
