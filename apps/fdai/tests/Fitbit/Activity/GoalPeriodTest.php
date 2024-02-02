<?php

declare(strict_types=1);

namespace Tests\Fitbit\Activity;

use App\DataSources\Connectors\Fitbit\Activity\Goals\Period;

class GoalPeriodTest extends \Tests\Fitbit\FitbitTestCase
{
    public function testGettingAPeriodAsString()
    {
        $this->assertEquals(
            'daily',
            (string) (new Period(Period::DAILY))
        );
    }

    public function testWhenAnInvalidPeriodIsPassedAnExceptionWillBeThrown()
    {
        $this->expectException(\App\DataSources\Connectors\Fitbit\Exceptions\InvalidConstantValueException::class);
        new Period('invalidString');
    }
}
