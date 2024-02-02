<?php

declare(strict_types=1);

namespace Tests\Fitbit\Food;

use Carbon\Carbon;
use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;
use App\DataSources\Connectors\Fitbit\Food\Water\Log;
use App\DataSources\Connectors\Fitbit\Food\Water\Logs;
use App\DataSources\Connectors\Fitbit\Food\Water\Unit;
use App\DataSources\Connectors\Fitbit\Food\Water\UpdatedLog;

class WaterLogsTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $water;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->logs = new Logs($this->fitbit);
    }

    public function testGettingLogs()
    {
        $this->fitbit->shouldReceive('get')
            ->once()
            ->with('foods/log/water/date/2019-03-21.json')
            ->andReturn('waterLogs');
        $this->assertEquals(
            'waterLogs',
            $this->logs->get(
                Carbon::today()
            )
        );
    }

    public function testAddingALogEntry()
    {
        $this->fitbit->shouldReceive('post')
            ->once()
            ->with('foods/log/water.json?date=2019-03-21&unit=ml&amount=1.2')
            ->andReturn('addedLog');
        $this->assertEquals(
            'addedLog',
                        $this->logs->add(
                            new Log(Carbon::now(), 12, new Unit(Unit::MILIMETER))
                        )
        );
    }

    public function testUpdatingALogEntry()
    {
        $this->fitbit->shouldReceive('post')
            ->once()
            ->with('foods/log/water/logId.json?unit=ml&amount=1.2')
            ->andReturn('updatedLog');
        $this->assertEquals(
            'updatedLog',
                        $this->logs->update(
                            'logId',
                            new UpdatedLog(12, new Unit(Unit::MILIMETER))
                        )
        );
    }

    public function testDeletingALogEntry()
    {
        $this->fitbit->shouldReceive('delete')
            ->once()
            ->with('foods/log/water/logId.json')
            ->andReturn('');
        $this->assertEquals(
            '',
            $this->logs->remove('logId')
        );
    }
}
