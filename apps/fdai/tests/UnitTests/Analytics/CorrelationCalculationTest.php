<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Analytics;
use Exception;
use App\Utils\Stats;
use Tests\UnitTestCase;
class CorrelationCalculationTest extends UnitTestCase {
    public function testCalculateCorrelationWithNullResult(){
        $result = Stats::calculatePearsonCorrelationCoefficient(
            [10, 20, 30, 25, 20, 15, 25, 15, 20, 10],
            [10, 10, 10, 10, 10, 10, 10, 10, 10, 10]);
        $this->assertEquals(null, $result);
    }
    public function testCalculateCorrelationWithOneResult(){
        $result = Stats::calculatePearsonCorrelationCoefficient(
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $this->assertEquals(1, $result);
    }
    public function testCalculateCorrelationWithNegativeOneResult(){
        $result = Stats::calculatePearsonCorrelationCoefficient(
            [-1, -2, -3, -4, -5, -6, -7, -8, -9, -10],
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $this->assertEquals(-1, $result);
    }
    public function testCalculateCorrelationWithEmptyArray(){
        try {
            $result = Stats::calculatePearsonCorrelationCoefficient([-1, -2, -3, -4, -5, -6, -7, -8, -9, -10], []);
        } catch (Exception $exception){
            $this->assertEquals("Division by zero", $exception->getMessage());
        }
        $this->assertNotTrue(isset($result));
    }
}
