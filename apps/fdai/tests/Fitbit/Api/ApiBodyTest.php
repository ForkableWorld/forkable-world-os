<?php

declare(strict_types=1);

namespace Tests\Fitbit\Api;

use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Body;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;

class ApiBodyTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $body;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->body = new Body($this->fitbit);
    }

    public function testGettingAFatInstance()
    {
        $this->assertTrue($this->body->fat() instanceof \App\DataSources\Connectors\Fitbit\Body\Fat\Fat);
    }

    public function testGettingAWeightInstance()
    {
        $this->assertTrue($this->body->weight() instanceof \App\DataSources\Connectors\Fitbit\Body\Weight\Weight);
    }

    public function testGettingAGoalsInstance()
    {
        $this->assertTrue($this->body->goals() instanceof \App\DataSources\Connectors\Fitbit\Body\Goals\Goals);
    }

    public function testGettingATimeSeriesInstance()
    {
        $this->assertTrue($this->body->timeSeries() instanceof \App\DataSources\Connectors\Fitbit\Body\TimeSeries);
    }
}
