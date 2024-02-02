<?php /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Variables;
use App\Slim\Model\QMUnit;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use Tests\UnitTestCase;
class UnitConversionTest extends UnitTestCase {
    public function testNoConversionToEmptyUnits(){
        $this->checkConversion(60, 60, 's', null);
    }
    public function testNoConversionToSameUnits(){
        $this->checkConversion(60, 60, 's', 's');
    }
    public function testConversionFromSecondsToMinutes(){
        $this->checkConversion(1, 60, 's', 'min');
    }
    public function testConversionFromHoursToMinutes(){
        $this->checkConversion(120, 2, 'h', 'min');
    }
    public function testConversionRatingUnitsToPercents(){
        $this->checkConversion(25, 2, '/5', '%');
    }
    public function testConversionRatingUnitsFromPercents(){
        $this->checkConversion(5, 100, '%', '/5');
    }
    /**
     * @param int $expectedToValue
     * @param int $fromValue
     * @param string $fromUnit
     * @param string $toUnit
     */
    private function checkConversion(int $expectedToValue, int $fromValue, string $fromUnit, ?string $toUnit): void{
        $this->assertEquals($expectedToValue, QMUnit::convertValue($fromValue,
            $fromUnit, $toUnit, OverallMoodCommonVariable::instance()));
    }
}
