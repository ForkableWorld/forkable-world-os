<?php

declare(strict_types=1);

namespace Tests\Fitbit\Api;

use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;
use App\DataSources\Connectors\Fitbit\Api\HeartRate;

class ApiHeartRateTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $heartRate;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->heartRate = new HeartRate($this->fitbit);
    }

    public function testGettingAHeartRateInstance()
    {
        $this->assertTrue($this->heartRate->heartRate() instanceof \App\DataSources\Connectors\Fitbit\HeartRate\HeartRate);
    }
}
