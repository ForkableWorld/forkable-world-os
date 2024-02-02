<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Variables;
use App\Properties\UserVariableRelationship\CorrelationOptimalPearsonProductProperty;
use App\VariableRelationships\QMUserVariableRelationshipV1;
use App\Utils\Stats;
use Tests\UnitTestCase;
class VariableCalculationHelperTest extends UnitTestCase
{
    public function testStandardDeviation(){
        $arr = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $standardDeviation = Stats::standardDeviation($arr);
        $this->assertEquals(3.02765, round($standardDeviation, 5));
    }
    public function testOptimalPearsonProduct() {
        $causeArr = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $correlationObject = new QMUserVariableRelationshipV1();
        $correlationObject->avgDailyValuePredictingHighOutcome = 7;
        $correlationObject->avgDailyValuePredictingLowOutcome = 2;
        $correlationObject->correlationCoefficient = 0.5;
        $correlationObject->reversePearsonCorrelationCoefficient = 0;
        $optimalPearsonProduct = CorrelationOptimalPearsonProductProperty::calculateOptimalPearsonProduct($causeArr, $correlationObject);
        $this->assertEquals(round(0.825725, 4), round($optimalPearsonProduct, 4));
    }
    public function testVariance()
    {
        $arr = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $variance = Stats::variance($arr);
        $this->assertEquals(9.16667, round($variance, 5));
    }
    public function testVarianceForLessThanTwoElements()
    {
        $arr = [1];
        $variance = Stats::variance($arr);
        $this->assertEquals(0.0, $variance);
    }
    public function testSkewnessAndKurtosisForLessThanTwoElements(){
        $arr = [11];
        $skew = null;
        $kurt = null;
        Stats::skewnessAndKurtosis($arr, $skew, $kurt);
        $this->assertEquals(null, $skew);
        $this->assertEquals(null, $kurt);
    }
    public function testSkewnessAndKurtosis(){
        $arr = [11, 21, 15, 13, 17, 28, 19, 16, 12, 20];
        $skew = null;
        $kurt = null;
        Stats::skewnessAndKurtosis($arr, $skew, $kurt);
        $this->assertEquals(0.7464, round($skew, 4));
        $this->assertEquals(2.7569, round($kurt, 4));
    }
    public function testAverage()
    {
        $arr = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $average = Stats::average($arr);
        $this->assertEquals(5.5, $average);
    }
    public function testRange()
    {
        $arr = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $range = Stats::range($arr);
        $this->assertEquals(9, $range);
    }
    public function testMedian()
    {
        $arr = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $median = Stats::median($arr);
        $this->assertEquals(5.5, $median);
    }
    public function testMode()
    {
        $arr = [1, 1, 3, 4, 3, 6, 5, 8, 3];
        $mode = Stats::mode($arr);
        $this->assertEquals(3, $mode);
    }
}
