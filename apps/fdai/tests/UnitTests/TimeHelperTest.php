<?php /** @noinspection PhpDeprecationInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests;
use App\Exceptions\InvalidTimestampException;
use App\Types\TimeHelper;
use Tests\UnitTestCase;
class TimeHelperTest extends UnitTestCase {
	/**
	 * @covers \App\Types\TimeHelper::YmdHis
	 */
	public function testYmdHis(){
		$date = TimeHelper::YmdHis(1789841399);
		$this->assertEquals("2026-09-19 18:09:59", $date);
	}
    public function testDBDate(){
        $this->assertEquals("1975-01-01 00:00:00", db_date("1975-01-01"));
        $this->assertEquals("2116-04-19 00:00:00", db_date("2116-04-19"));
    }
    public function testConvertFloat(){
        $startTime = time_or_exception(1606901541.677);
        $this->assertEquals(1606901541, $startTime);
    }
    public function testHumanizeTime(){
        $human = TimeHelper::timeSinceHumanString(1336267020);
        $this->assertEquals("11 years ago", $human);
    }
    public function testUniversalConversionToUnixTimestamp(){
        $str = "2000-01-01";
        $time = TimeHelper::universalConversionToUnixTimestamp($str);
        $this->assertEquals(strtotime($str), $time);
    }
    /**
     * @param $providedString
     * @param $expectedTimestamp
     * @throws InvalidTimestampException
     */
    private function checkConversion($providedString, $expectedTimestamp){
        $timestamp = TimeHelper::universalConversionToUnixTimestamp($providedString);
        $this->assertGreaterThan($expectedTimestamp - 86400, $timestamp);
        $this->assertLessThan($expectedTimestamp + 86400, $timestamp);
    }
    /**
     * @throws InvalidTimestampException
     */
    public function testDateConversion(){
        $this->checkConversion("10/15/2013", 1381813200);
        $this->checkConversion("1/3/2013", 1357192800);
        $this->checkConversion("1-3-2013", 1362117600);
        $this->checkConversion("1357192800000", 1357192800);
        $this->checkConversion(1357192800000, 1357192800);
        $this->checkConversion(1357192800, 1357192800);
        $this->checkConversion("1357192800", 1357192800);
    }
}
