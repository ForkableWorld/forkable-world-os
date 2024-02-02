<?php

declare(strict_types=1);

namespace Tests\Fitbit\Activity;

use App\DataSources\Connectors\Fitbit\Activity\Period;

class PeriodTest extends \Tests\Fitbit\FitbitTestCase
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
