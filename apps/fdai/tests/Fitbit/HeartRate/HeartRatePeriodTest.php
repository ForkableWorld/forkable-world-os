<?php

declare(strict_types=1);

namespace Tests\Fitbit\HeartRate;

use App\DataSources\Connectors\Fitbit\HeartRate\Period;

class HeartRatePeriodTest extends \Tests\Fitbit\FitbitTestCase
{
    public function testGettingAPeriodAsString()
    {
        $this->assertEquals(
            '1d',
            (string) (new Period(Period::ONE_DAY))
        );
    }

    public function testWhenAnInvalidPeriodIsPassedAnExceptionWillBeThrown()
    {
        $this->expectException(\App\DataSources\Connectors\Fitbit\Exceptions\InvalidConstantValueException::class);
        new Period('invalidString');
    }
}
