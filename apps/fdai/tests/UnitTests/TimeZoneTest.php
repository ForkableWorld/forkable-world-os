<?php
namespace Tests\UnitTests;
use App\Utils\QMTimeZone;
use Tests\UnitTestCase;
class TimeZoneTest extends UnitTestCase
{
    public function testConvertOffsetToTimeZoneAbbreviation(){
        $abbrev = QMTimeZone::convertTimeZoneOffsetToStringAbbreviation(300);
        $this->assertEquals("America/Chicago", $abbrev);
    }
}