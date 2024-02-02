<?php

namespace Tests\UnitTests\Slim\Model\Measurement;

use App\Slim\Model\Measurement\AnonymousMeasurement;
use Tests\UnitTestCase;

/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Slim\Model\Measurement\AnonymousMeasurement
 */
class AnonymousMeasurementTest extends UnitTestCase
{

    public function testGetByVariableId()
    {
        $measurements = AnonymousMeasurement::getByVariableId(
            1398, null, "%Y-%m-%d", "AVG");
        $this->assertTrue(true);
    }
}
