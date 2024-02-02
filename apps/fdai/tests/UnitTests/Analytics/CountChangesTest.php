<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Analytics;
use App\Utils\Stats;
use Tests\UnitTestCase;
class CountChangesTest extends UnitTestCase{
    public function testZeroChanges(){
        $arr = [0,0,0,0,0,0,0];
	    $result = Stats::countChanges($arr);
	    $expected = 0;
        $this->assertEquals($expected, $result);
    }
    public function testFourChanges(){
	    $arr = [0,1,0,1,1,1,0];
	    $result = Stats::countChanges($arr);
	    $expected = 4;
	    $this->assertEquals($expected, $result);
    }
}
