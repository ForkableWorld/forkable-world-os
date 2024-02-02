<?php

declare(strict_types=1);

namespace Tests\Fitbit\Api;

use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Activities;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;

class ActivitiesTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $activities;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->activities = new Activities($this->fitbit);
    }

    public function testGettingAnActivityInstance()
    {
        $this->assertTrue($this->activities->activity() instanceof \App\DataSources\Connectors\Fitbit\Activity\Activity);
    }

    public function testGettingATimeSeriesInstance()
    {
        $this->assertTrue($this->activities->timeSeries() instanceof \App\DataSources\Connectors\Fitbit\Activity\TimeSeries);
    }

    public function testGettingAnIntradayInstance()
    {
        $this->assertTrue($this->activities->intraday() instanceof \App\DataSources\Connectors\Fitbit\Activity\Intraday);
    }

    public function testGettingATypesInstance()
    {
        $this->assertTrue($this->activities->activityTypes() instanceof \App\DataSources\Connectors\Fitbit\Activity\Types);
    }

    public function testGettingALogsInstance()
    {
        $this->assertTrue($this->activities->activityLogs() instanceof \App\DataSources\Connectors\Fitbit\Activity\Logs\Logs);
    }

    public function testGettingAFavoritesInstance()
    {
        $this->assertTrue($this->activities->favorites() instanceof \App\DataSources\Connectors\Fitbit\Activity\Favorites);
    }

    public function testGettingAGoalsInstance()
    {
        $this->assertTrue($this->activities->goals() instanceof \App\DataSources\Connectors\Fitbit\Activity\Goals\Goals);
    }
}
