<?php

declare(strict_types=1);

namespace Tests\Fitbit\Body;

use Carbon\Carbon;
use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;
use App\DataSources\Connectors\Fitbit\Body\Period;
use App\DataSources\Connectors\Fitbit\Body\Resource;
use App\DataSources\Connectors\Fitbit\Body\TimeSeries;

class BodyTimeSeriesTest extends \Tests\Fitbit\FitbitTestCase
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
            ->with('body/bmi/date/2019-03-21/max.json')
            ->andReturn('periodTimeSeries');
        $this->assertEquals(
            'periodTimeSeries',
            $this->timeSeries->getByPeriod(
                new Resource(Resource::BMI),
                Carbon::today(),
                new Period(Period::MAX_PERIOD)
            )
        );
    }

    public function testGettingByDateRange()
    {
        $this->fitbit->shouldReceive('get')
            ->once()
            ->with('body/weight/date/2019-03-21/2019-03-22.json')
            ->andReturn('DateRangeTimeSeries');
        $this->assertEquals(
            'DateRangeTimeSeries',
            $this->timeSeries->getByDateRange(
                new Resource(Resource::WEIGHT),
                Carbon::today(),
                Carbon::tomorrow()
            )
        );
    }
}
