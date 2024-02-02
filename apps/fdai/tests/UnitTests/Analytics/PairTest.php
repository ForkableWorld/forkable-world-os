<?php /** @noinspection PhpUnhandledExceptionInspection */ /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Analytics;
use App\Models\Measurement;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Properties\BaseProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\UserVariable\UserVariableDurationOfActionProperty;
use App\Slim\Model\Measurement\Pair;
use App\Slim\Model\Measurement\QMMeasurementV1;
use App\Slim\View\Request\Pair\GetPairRequest;
use App\Units\OneToFiveRatingUnit;
use App\Utils\Stats;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\TreatmentsCommonVariables\BupropionSrCommonVariable;
use App\Variables\QMUserVariable;
use Tests\UnitTestCase;
class PairTest extends UnitTestCase {
	/**
	 * @param array $expectedPairs
	 * @param array $actualPairs
	 */
	public function assertPairsEqual(array $expectedPairs, array $actualPairs): void{
		$actual = [];
		foreach($actualPairs as $pair){
			$actual[$pair->startAt] = ['cause' => $pair->causeMeasurementValue,
				'effect'=>$pair->effectMeasurementValue];
		}
		$this->assertArrayEquals($expectedPairs, $actual);
	}
	protected function setUp(): void{
        parent::setUp();
        BaseProperty::setValidationDisabledFor([UserVariableDurationOfActionProperty::class]);
		Measurement::deleteAll();
    }
    protected function tearDown(): void{
        BaseProperty::setValidationDisabledFor([]);
        parent::tearDown();
    }
    public function testCreateAbsolutePairsWithZeroFilling(){
        $day = 86400;
        $day1 = $day;
        $day2 = 2 * $day;
        $day3 = 3 * $day;
        $day4 = 4 * $day;
        $day5 = 5 * $day;
        $cause = $this->getBupropion(BaseFillingTypeProperty::FILLING_TYPE_ZERO, 0.5 * $day, $day);
        $effect = $this->getMood();
        (new QMMeasurementV1(null, $cause, $day2, 2, 'mg'))->save();
        (new QMMeasurementV1(null, $cause, $day4, 4, 'mg'))->save();
        $effectMeasurements = $this->getFiveRatingMeasurements($effect, $day1, $day2, $day3, $day4, $day5);
	    $effect->saveMultipleMeasurements($effectMeasurements);
		$req = new GetPairRequest($cause, $effect);
	    $cause->validateTimes();
        $actualPairs = $req->createAbsolutePairs();
	    $this->assertPairsEqual(array (
		    '1970-01-04 00:00:00' =>
			    array (
				    'cause' => 2,
				    'effect' => 3,
			    ),
		    '1970-01-05 00:00:00' =>
			    array (
				    'cause' => 0,
				    'effect' => 4,
			    ),
		    '1970-01-06 00:00:00' =>
			    array (
				    'cause' => 4,
				    'effect' => 5,
			    ),
	    ), $actualPairs);
    }
    public function testCreateAbsolutePairs() {
        $day = 86400;
        $day1 = $day;
        $day2 = 2 * $day;
        $day3 = 3 * $day;
        $day4 = 4 * $day;
        $day5 = 5 * $day;
        $cause = $this->getBupropion(BaseFillingTypeProperty::FILLING_TYPE_NONE, 0.5 * $day, $day);
        $effect = $this->getMood();
        $rawCauseMeasurements = $this->getFiveMgMeasurements($cause, $day1, $day2, $day3, $day4, $day5);
        $rawEffectMeasurements = $this->getFiveRatingMeasurements($effect, $day1, $day2, $day3, $day4, $day5);
	    $req = new GetPairRequest($cause, $effect);
	    $req->setCauseDailyMeasurements($rawCauseMeasurements);
	    $req->setEffectDailyMeasurements($rawEffectMeasurements);
        $expectedPairs[] = new Pair($day1, 1, 2, $req);
        $expectedPairs[] = new Pair($day2, 2, 3, $req);
        $expectedPairs[] = new Pair($day3, 3, 4, $req);
        $expectedPairs[] = new Pair($day4, 4, 5, $req);
        foreach($expectedPairs as $p){
            $p->setEffectUserVariable($effect);
            $p->setCauseUserVariable($cause);
        }
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
    public function testAggregateMeasurementsNoFillingAndAverage(){
        $variable = $this->getMood();
        $this->aggregateAndCheck($variable);
    }
    public function testAggregateCausesWithNoFilling(){
        $effect = $this->getMood();
        $cause = $this->getBupropion(BaseFillingTypeProperty::FILLING_TYPE_NONE, 15, 20);
        $causeMeasurements = $this->getFourMgMeasurements($cause);
        $effectMeasurements = $this->getFourRatingMeasurements($effect);
	    $req = new GetPairRequest( $cause, $effect);
	    $req->setCauseDailyMeasurements($causeMeasurements);
	    $req->setEffectDailyMeasurements($effectMeasurements);
        $actualPairs = $req->createPairForEachEffectMeasurement();
	    $this->assertPairsEqual([
		    '1970-01-01 00:00:40' =>
			    [
				    'cause' => 1.5,
				    'effect' => 4,
			    ],
		    '1970-01-01 00:00:50' =>
			    [
				    'cause' => 2,
				    'effect' => 5,
			    ],], $actualPairs);
    }
    public function testAggregateCausesWithMeanAndNoFillingNoDelay(){
        $effect = $this->getMood();
        $cause = $this->getBupropion(BaseFillingTypeProperty::FILLING_TYPE_NONE, 0, 10);
        $causeMeasurements = $this->getFourMgMeasurements($cause);
        $effectMeasurements = $this->getFourMgMeasurements($effect);
	    $req = new GetPairRequest($cause, $effect);
	    $req->setCauseDailyMeasurements($causeMeasurements);
	    $req->setEffectDailyMeasurements($effectMeasurements);
        $actualPairs = $req->createPairForEachEffectMeasurement();
        foreach ($actualPairs as $actualPair) {
            $this->assertEquals($actualPair->effectMeasurementValue, $actualPair->causeMeasurementValue);
        }
    }
    public function testAggregateEffectsWithMeanAndNoFillingNoDelay(){
        $effect = $this->getMood();
        $cause = $this->getBupropion(BaseFillingTypeProperty::FILLING_TYPE_NONE, 0, 10);
        $causeMeasurements = $this->getFourMgMeasurements($cause);
        $effectMeasurements = $this->getFourMgMeasurements($effect);
		$r = new GetPairRequest($cause, $effect);
		$r->setCauseDailyMeasurements($causeMeasurements);
		$r->setEffectDailyMeasurements($effectMeasurements);
        $actualPairs = $r->createPairForEachCauseMeasurement();
        foreach ($actualPairs as $actualPair) {
            $this->assertEquals($actualPair->effectMeasurementValue, $actualPair->causeMeasurementValue);
        }
    }
    public function testAggregateEffectsWithMeanAndNoFilling(){
        $effect = $this->getMood();
        $cause = $this->getBupropion(BaseFillingTypeProperty::FILLING_TYPE_NONE, 15, 20);
        $causeMeasurements = $this->getFourMgMeasurements($cause);
        $effectMeasurements = $this->getFourRatingMeasurements($effect);
	    $req = new GetPairRequest($cause, $effect);
	    $req->setCauseDailyMeasurements($causeMeasurements);
	    $req->setEffectDailyMeasurements($effectMeasurements);
        $expectedPairs[] = new Pair(10, 1, 4,$req);
        $expectedPairs[] = new Pair(20, 2, 4.5,$req);
        foreach($expectedPairs as $p){
            $p->setEffectUserVariable($effect);
            $p->setCauseUserVariable($cause);
        }
        $actualPairs = $req->createPairForEachCauseMeasurement();
        $this->assertPairsEqual([
	        '1970-01-01 00:00:10' =>
		        [
			        'cause' => 1,
			        'effect' => 4,
		        ],
	        '1970-01-01 00:00:20' =>
		        [
			        'cause' => 2,
			        'effect' => 4.5,
		        ],], $actualPairs);
    }
    public function testAggregateMeasurementsFillingAndMean(){
        $variable = BupropionSrCommonVariable::getUserVariableByUserId(UserIdProperty::USER_ID_DEMO);
        $variable->setFillingValue(0);
        $variable->combinationOperation = BaseCombinationOperationProperty::COMBINATION_MEAN;
        $this->aggregateAndCheck($variable);
    }
    public function testAggregateEffectsWithFillingAndMean(){
        $cause = $this->getBupropion(BaseFillingTypeProperty::FILLING_TYPE_ZERO, 15, 20);
        $effect = $this->getMood();
        $causeMeasurements = $this->getFourMgMeasurements($cause);
        $effectMeasurements = $this->getFourRatingMeasurements($effect);
	    $req = new GetPairRequest($cause, $effect);
	    $req->setCauseDailyMeasurements($causeMeasurements);
	    $req->setEffectDailyMeasurements($effectMeasurements);
        $actualPairs = $req->createPairForEachCauseMeasurement();
        $this->assertPairsEqual([
	        '1970-01-01 00:00:10' =>
		        [
			        'cause' => 1,
			        'effect' => 4,
		        ],
	        '1970-01-01 00:00:20' =>
		        [
			        'cause' => 2,
			        'effect' => 4.5,
		        ],], $actualPairs);
    }
    /**
     * @param $variable
     * @covers GetPairRequest::averageValues()
     */
    private function aggregateAndCheck($variable){
        $measurements[] = new QMMeasurementV1(null, $variable, 1, 1, 'mg');
        $measurements[] = new QMMeasurementV1(null, $variable, 2, 2, 'mg');
        //$measurements[] = new MeasurementV1(null, $variable, 3, null, null, null, 'mg');
        $measurements[] = new QMMeasurementV1(null, $variable, 4, 4, 'mg');
        $measurements[] = new QMMeasurementV1(null, $variable, 5, 5, 'mg');
        $startTime = 1;
        $endTime = 3;
        $actual = GetPairRequest::averageValues($variable, $measurements, $startTime, $endTime);
        $expected = 1.5;
        $this->assertEquals($expected, $actual);
    }
    /**
     * @param QMUserVariable $variable
     * @param int $day1
     * @param $day2
     * @param $day3
     * @param $day4
     * @param $day5
     * @return array
     */
    public function getFiveMgMeasurements(QMUserVariable $variable, int $day1, $day2, $day3, $day4, $day5): array{
        $measurements = [];
        $measurements[] = new QMMeasurementV1(null, $variable, $day1, 1, 'mg');
        $measurements[] = new QMMeasurementV1(null, $variable, $day2, 2, 'mg');
        $measurements[] = new QMMeasurementV1(null, $variable, $day3, 3, 'mg');
        $measurements[] = new QMMeasurementV1(null, $variable, $day4, 4, 'mg');
        $measurements[] = new QMMeasurementV1(null, $variable, $day5, 5, 'mg');
        return $measurements;
    }
    /**
     * @param QMUserVariable $variable
     * @param int $day1
     * @param $day2
     * @param $day3
     * @param $day4
     * @param $day5
     * @return array
     */
    public function getFiveRatingMeasurements(QMUserVariable $variable, int $day1, $day2, $day3, $day4, $day5): array{
        $measurements = [];
        $measurements[] = new QMMeasurementV1(null, $variable, $day1, 1, OneToFiveRatingUnit::ABBREVIATED_NAME);
        $measurements[] = new QMMeasurementV1(null, $variable, $day2, 2, OneToFiveRatingUnit::ABBREVIATED_NAME);
        $measurements[] = new QMMeasurementV1(null, $variable, $day3, 3, OneToFiveRatingUnit::ABBREVIATED_NAME);
        $measurements[] = new QMMeasurementV1(null, $variable, $day4, 4, OneToFiveRatingUnit::ABBREVIATED_NAME);
        $measurements[] = new QMMeasurementV1(null, $variable, $day5, 5, OneToFiveRatingUnit::ABBREVIATED_NAME);
        return $measurements;
    }
    /**
     * @param string $fillingType
     * @return QMUserVariable
     */
    public function getMood(): QMUserVariable{
        $effect = OverallMoodCommonVariable::getUserVariableByUserId(UserIdProperty::USER_ID_DEMO);
        return $effect;
    }
    /**
     * @param QMUserVariable $variable
     * @return QMMeasurementV1[]
     */
    public function getFourMgMeasurements(QMUserVariable $variable): array{
        $measurements = [];
        $measurements[] = new QMMeasurementV1(null, $variable, 10, 1, 'mg');
        $measurements[] = new QMMeasurementV1(null, $variable, 20, 2, 'mg');
        //$effectMeasurements[] = new MeasurementV1(null, $effect, 30, null, null, null, 'mg');
        $measurements[] = new QMMeasurementV1(null, $variable, 40, 4, 'mg');
        $measurements[] = new QMMeasurementV1(null, $variable, 50, 5, 'mg');
        return $measurements;
    }
    /**
     * @param QMUserVariable $variable
     * @return QMMeasurementV1[]
     */
    public function getFourRatingMeasurements(QMUserVariable $variable): array{
        $measurements = [];
        $measurements[] = new QMMeasurementV1(null, $variable, 10, 1, OneToFiveRatingUnit::ABBREVIATED_NAME);
        $measurements[] = new QMMeasurementV1(null, $variable, 20, 2, OneToFiveRatingUnit::ABBREVIATED_NAME);
        //$effectMeasurements[] = new MeasurementV1(null, $effect, 30, null, null, null, 'mg');
        $measurements[] = new QMMeasurementV1(null, $variable, 40, 4, OneToFiveRatingUnit::ABBREVIATED_NAME);
        $measurements[] = new QMMeasurementV1(null, $variable, 50, 5, OneToFiveRatingUnit::ABBREVIATED_NAME);
        return $measurements;
    }
    /**
     * @param string $fillingType
     * @param int $onsetDelay
     * @param int $durationOfAction
     * @return QMUserVariable
     * @throws \App\Exceptions\InvalidAttributeException
     * @throws \App\Exceptions\ModelValidationException
     */
    public function getBupropion(string $fillingType, int $onsetDelay, int $durationOfAction): QMUserVariable{
        $cause = BupropionSrCommonVariable::getUserVariableByUserId(UserIdProperty::USER_ID_DEMO);
        $cause->setDurationOfAction($durationOfAction);
        $cause->setFillingTypeAttribute($fillingType);
        $cause->setOnsetDelay($onsetDelay);
        $cause->save();
        $cause->validateTimes();
        return $cause;
    }
}
