<?php
namespace Tests\UnitTests\Types;
use App\Types\TimeHelper;
use Tests\UnitTestCase;

class CarbonTest extends UnitTestCase
{
    public function testToCarbon(){
        $c = TimeHelper::toCarbon("2020-01-01 06:00:00");
        $this->assertEquals(2020, $c->year);
    }
}
