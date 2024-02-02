<?php

declare(strict_types=1);

namespace Tests\Fitbit\Devices;

use App\DataSources\Connectors\Fitbit\Devices\Weekdays;

class WeekdaysTest extends \Tests\Fitbit\FitbitTestCase
{
    public function testPrintingAWeekdayList()
    {
        $this->assertEquals(
        'MONDAY,TUESDAY',
        (string) new Weekdays([Weekdays::MONDAY, Weekdays::TUESDAY])
      );
    }

    public function testValidatingAllWeekdayList()
    {
        $this->expectException(\App\DataSources\Connectors\Fitbit\Exceptions\InvalidConstantValueException::class);
        new Weekdays([Weekdays::MONDAY, 'INVALID_STRING']);
    }
}
