<?php

declare(strict_types=1);

namespace Tests\Fitbit\Activity;

use Carbon\Carbon;
use Mockery;
use App\DataSources\Connectors\Fitbit\Activity\Period;
use App\DataSources\Connectors\Fitbit\Activity\Resource\Resource;
use App\DataSources\Connectors\Fitbit\Activity\TimeSeries;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;

class TimeSeriesTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $timeSeries;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->timeSeries = new TimeSeries($this->fitbit);
    }

    public function testGettingByPeriod()
    {
        $this->fitbit->shouldReceive('get')
            ->once()
            ->with('activities/calories/date/2019-03-21/1w.json')
            ->andReturn('periodTimeSeries');
        $this->assertEquals(
            'periodTimeSeries',
            $this->timeSeries->getByPeriod(
                new Resource(Resource::CALORIES),
                Carbon::today(),
                new Period(Period::ONE_WEEK)
            )
        );
    }

    public function testGettingByDateRange()
    {
        $this->fitbit->shouldReceive('get')
            ->once()
            ->with('activities/elevation/date/2019-03-21/2019-03-22.json')
            ->andReturn('DateRangeTimeSeries');
        $this->assertEquals(
            'DateRangeTimeSeries',
            $this->timeSeries->getByDateRange(
                new Resource(Resource::ELEVATION),
                Carbon::today(),
                Carbon::tomorrow()
            )
        );
    }
}
