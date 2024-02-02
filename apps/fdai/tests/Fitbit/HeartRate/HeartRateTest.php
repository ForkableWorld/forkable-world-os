<?php

declare(strict_types=1);

namespace Tests\Fitbit\HeartRate;

use Carbon\Carbon;
use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;
use App\DataSources\Connectors\Fitbit\HeartRAte\HeartRate;
use App\DataSources\Connectors\Fitbit\HeartRate\Period;

class HeartRateTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $heartRate;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->heartRate = new HeartRate($this->fitbit);
    }

    public function testGettingByPeriod()
    {
        $this->fitbit->shouldReceive('get')
            ->once()
            ->with('activities/heart/date/2019-03-21/1m.json')
            ->andReturn('periodHeartRate');
        $this->assertEquals(
            'periodHeartRate',
            $this->heartRate->getByPeriod(
                Carbon::today(),
                new Period(Period::ONE_MONTH)
            )
        );
    }

    public function testGettingByDateRange()
    {
        $this->fitbit->shouldReceive('get')
            ->once()
            ->with('activities/heart/date/2019-03-21/2019-03-22.json')
            ->andReturn('DateRangeHeartRate');
        $this->assertEquals(
            'DateRangeHeartRate',
            $this->heartRate->getByDateRange(
                Carbon::today(),
                Carbon::tomorrow()
            )
        );
    }
}
