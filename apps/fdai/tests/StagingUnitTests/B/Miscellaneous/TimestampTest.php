<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\B\Miscellaneous;
use App\Types\TimeHelper;
use Tests\SlimStagingTestCase;

class TimestampTest extends SlimStagingTestCase {
    public function testTimestamp(){
        $time = TimeHelper::universalConversionToUnixTimestamp("Fri, 2 Nov 2018 15:53:29 -0700 (GMT-07:00)");
        $this->assertIsInt($time);
    }
}
