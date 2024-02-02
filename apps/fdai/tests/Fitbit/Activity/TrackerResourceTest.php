<?php

declare(strict_types=1);

namespace Tests\Fitbit\Activity;

use App\DataSources\Connectors\Fitbit\Activity\Resource\TrackerResource;

class TrackerResourceTest extends \Tests\Fitbit\FitbitTestCase
{
    public function testGettingAPeriodAsString()
    {
        $this->assertEquals(
            'activities/tracker/calories',
            (string) (new TrackerResource(TrackerResource::CALORIES))
        );
    }

    public function testWhenAnInvalidPeriodIsPassedAnExceptionWillBeThrown()
    {
        $this->expectException(\App\DataSources\Connectors\Fitbit\Exceptions\InvalidConstantValueException::class);
        new TrackerResource('invalidString');
    }
}
