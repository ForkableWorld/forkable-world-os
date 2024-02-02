<?php

declare(strict_types=1);

namespace Tests\Fitbit\Api;

use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;
use App\DataSources\Connectors\Fitbit\Api\SleepLogs;

class ApiSleepLogsTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $sleepLogs;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->sleepLogs = new SleepLogs($this->fitbit);
    }

    public function testGettingASleepLogsInstance()
    {
        $this->assertTrue($this->sleepLogs->sleepLogs() instanceof \App\DataSources\Connectors\Fitbit\SleepLogs\SleepLogs);
    }

    public function testGettingAGoalsInstance()
    {
        $this->assertTrue($this->sleepLogs->goals() instanceof \App\DataSources\Connectors\Fitbit\SleepLogs\Goals);
    }
}
