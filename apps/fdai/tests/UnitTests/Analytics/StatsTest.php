<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Analytics;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Properties\BaseProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\UserVariable\UserVariableDurationOfActionProperty;
use App\Properties\UserVariable\UserVariableLatestFillingTimeProperty;
use App\Slim\Model\Measurement\QMMeasurementV1;
use App\Slim\View\Request\Pair\GetPairRequest;
use App\Units\OneToFiveRatingUnit;
use App\Utils\Stats;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\TreatmentsCommonVariables\BupropionSrCommonVariable;
use App\Variables\QMUserVariable;
use Tests\UnitTestCase;
class StatsTest extends UnitTestCase {
    protected function setUp(): void{
        parent::setUp();
        BaseProperty::setValidationDisabledFor([
            UserVariableDurationOfActionProperty::class,
            UserVariableLatestFillingTimeProperty::class,
        ]);
    }
    protected function tearDown(): void{
        BaseProperty::setValidationDisabledFor([]);
        parent::tearDown();
    }
    public function testDivision(){
        $sum = 2000;
        $count = 716;
        $average = $sum / $count;
        $this->assertEquals(2.793296089385475, $average);
    }
	/**
	 * @covers \App\Slim\View\Request\Pair\GetPairRequest::createAbsolutePairs
	 */
	public function testCountChanges() {
        $day = 86400;
        $day1 = $day;
        $day2 = 2 * $day;
        $day3 = 3 * $day;
        $day4 = 4 * $day;
        $day5 = 5 * $day;
        $cause = BupropionSrCommonVariable::getUserVariableByUserId(UserIdProperty::USER_ID_DEMO);
        $cause->setFillingTypeAttribute(BaseFillingTypeProperty::FILLING_TYPE_NONE);
        $cause->combinationOperation = BaseCombinationOperationProperty::COMBINATION_MEAN;
        $cause->setOnsetDelay(0.5 * $day);
        $cause->setDurationOfAction($day);
        $effect = $this->getEffectUserVariable();
        $rawCauseMeasurements = $this->getOneToFiveMgMeasurements($cause, $day1, $day2, $day3, $day4, $day5);
        $rawEffectMeasurements = $this->getOneToFiveRatingMeasurements($effect, $day1, $day2, $day3, $day4, $day5);
	    $req = new GetPairRequest($cause, $effect);
	    $req->setCauseDailyMeasurements($rawCauseMeasurements);
	    $req->setEffectDailyMeasurements($rawEffectMeasurements);
		$this->assertFalse($cause->hasFillingValue());
	    $actualPairs = $req->createAbsolutePairs();
        $causeArray = [];
        $effectArray = [];
        foreach ($actualPairs as $pair) {
            $causeArray[] = $pair->causeMeasurementValue;
            $effectArray[] = $pair->effectMeasurementValue;
        }
        $this->assertEquals(3, Stats::countChanges($causeArray));
        $this->assertEquals(3, Stats::countChanges($effectArray));
    }
    public function testRounding(){
        $pi = 3.1415926535898;
        $expectedRounded = 3.1416;
        //$n = Number::n($pi);
        $sigFigs = 5;
        //$rounded = $n->round($sigFigs)->getArray();
        //$this->assertEquals($expectedRounded, $rounded);
        $rounded = Stats::roundByNumberOfSignificantDigits($pi, $sigFigs);
        $this->assertEquals($expectedRounded, $rounded);
        $rounded = Stats::roundToSignificantFiguresIfGreater($pi, $sigFigs);
        $this->assertEquals($expectedRounded, $rounded);
        $jsonEncoded = json_encode(3.1415926535898);
        $this->assertEquals("3.1415926535898", $jsonEncoded);
        $jsonEncoded = json_encode(5);
        $this->assertEquals("5", $jsonEncoded);
        $jsonEncoded = json_encode("9.2");
        $this->assertEquals('"9.2"', $jsonEncoded);
        $float = floatval("9.2");
        $rounded = round($float);
        $this->assertEquals(9, $rounded);
    }
    /**
     * @return QMUserVariable
     */
    public function getEffectUserVariable(): QMUserVariable {
        $effect = OverallMoodCommonVariable::getUserVariableByUserId(UserIdProperty::USER_ID_DEMO);
        return $effect;
    }
	/**
	 * @param QMUserVariable $variable
	 * @param int $day1
	 * @param int $day2
	 * @param int $day3
	 * @param int $day4
	 * @param int $day5
	 * @return array
	 */
	public function getOneToFiveMgMeasurements(QMUserVariable $variable, int $day1, int $day2, int $day3, int $day4, int $day5): array{
		$rawCauseMeasurements = [];
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day1, 1, 'mg');
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day2, 2, 'mg');
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day3, 3, 'mg');
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day4, 4, 'mg');
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day5, 5, 'mg');
		return $rawCauseMeasurements;
	}
	/**
	 * @param QMUserVariable $variable
	 * @param int $day1
	 * @param int $day2
	 * @param int $day3
	 * @param int $day4
	 * @param int $day5
	 * @return array
	 */
	public function getOneToFiveRatingMeasurements(QMUserVariable $variable, int $day1, int $day2, int $day3, int $day4, int $day5): array{
		$rawCauseMeasurements = [];
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day1, 1, OneToFiveRatingUnit::ABBREVIATED_NAME);
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day2, 2, OneToFiveRatingUnit::ABBREVIATED_NAME);
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day3, 3, OneToFiveRatingUnit::ABBREVIATED_NAME);
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day4, 4, OneToFiveRatingUnit::ABBREVIATED_NAME);
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day5, 5, OneToFiveRatingUnit::ABBREVIATED_NAME);
		return $rawCauseMeasurements;
	}
}
