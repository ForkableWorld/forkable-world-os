<?php
namespace Computers;
use App\Computers\ThisComputer;
use Tests\UnitTestCase;

class ServerTest extends UnitTestCase
{
    public function testPrecision(){
        ThisComputer::validatePrecision();
        $this->assertEquals(ThisComputer::SERIALIZE_PRECISION, ini_get('serialize_precision'));
        $this->assertEquals(ThisComputer::PRECISION, ini_get('precision'));
    }
}
