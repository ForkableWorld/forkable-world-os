<?php

declare(strict_types=1);

namespace Tests\Fitbit\Devices;

use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;
use App\DataSources\Connectors\Fitbit\Devices\Devices;

class DevicesTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $devices;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->devices = new Devices($this->fitbit);
    }

    public function testGettingTheDevicesList()
    {
        $this->fitbit->shouldReceive('get')
            ->once()
            ->with('devices.json')
            ->andReturn('devicesList');
        $this->assertEquals(
            'devicesList',
            $this->devices->get()
        );
    }
}
