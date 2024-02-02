<?php /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Variables;
use App\Models\UserVariableRelationship;
use App\Models\UserVariable;
use App\Types\TimeHelper;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\MeasurementSet;
use App\Storage\Memory;
use App\Variables\QMUserVariable;
use Tests\DBUnitTestCase;
use RuntimeException;
/**
 * Class MeasurementProcessingTest
 * @package Tests\Api\Measurements
 */
class MeasurementProcessingTest extends \Tests\SlimTests\SlimTestCase{
    private $userId = 1;
    public const BASELINE_TIMESTAMP = 1348072640;
	protected function setUp(): void{
		parent::setUp();
		UserVariableRelationship::deleteAll();
	}
	/**
     * @param $timestamp
     * @return float|int
     */
    private function getDaysRelativeToBaseline($timestamp){
        $timestamp = TimeHelper::universalConversionToUnixTimestamp($timestamp);
        return ($timestamp - self::BASELINE_TIMESTAMP)/86400;
    }
    /**
     * Test code to get list of row measurements with grouping by day
     * @group Model
     * @group Measurement
     * @throws RuntimeException
     */
    public function testProcessFilledMeasurementsEarlySourceTime(): void{
        $this->deleteMeasurementsAndReminders();
        $this->setAuthenticatedUser($this->userId);
        $this->create_variable_a_food_measurements_at_0_days();
        $this->check_earliest_latest_times_for_variable_a();
        $this->create_same_source_var_b_meas_at_35_days();
        $this->check_earliest_5_and_latest_35_for_var_b();
        $this->create_var_c_meas_at_1_day();
        $this->check_earliest_and_latest_var_c_at_1_day();
    }
    private function create_variable_a_food_measurements_at_0_days(): void{
        $MeasurementItemAtBaseline[] = new QMMeasurement(self::BASELINE_TIMESTAMP, 1);
        // Create a measurement for variableA in "Foods" category with sourceA at $measurementATime
        // Run user variables updater task
        $measurementSetAAtBaseline[] = new MeasurementSet('variableA', $MeasurementItemAtBaseline,
            'serving', 'Foods', 'sourceForFillingTest',
            'sum', []);
        $this->saveMeasurementSets($this->userId, $measurementSetAAtBaseline);
        $variableA = QMUserVariable::getByNameOrId($this->userId, 'variableA');
        $this->assertZeroFillingValue($variableA);
        $processedMeasurementsA = $variableA->getValidDailyMeasurementsWithTagsAndFilling();
        $this->assertCount(1, $processedMeasurementsA);
        $measurements = $variableA->generateValidDailyMeasurementsWithTags();
        $this->assertCount(1, $measurements);
    }
    /**
     * @return void
     */
    private function check_earliest_latest_times_for_variable_a(): void{
        DBUnitTestCase::updateOrCreateAllUserVariablesAndSetMeasurementSourceNamesToTestClientId();
        $uv = QMUserVariable::getByNameOrId($this->userId, 'variableA');
        if($uv->variableId === 1){le('$uv->variableId === 1');}
        $this->assertEquals(0, $uv->fillingValue, $uv->getUrl());
        $this->assertNotNull($uv->dataSourcesCount, $uv->getUrl());
        if(!$uv->earliestFillingTime){le('!$uv->earliestFillingTime');}
        $this->assertNotNull($uv->earliestFillingTime, $uv->getUrl());
        $this->assertEquals(0, $this->getDaysRelativeToBaseline($uv->earliestFillingTime), $uv->getUrl());
        $this->assertEquals(0, $this->getDaysRelativeToBaseline($uv->latestFillingTime), $uv->getUrl());
        $this->assertEquals(0, $this->getDaysRelativeToBaseline($uv->earliestTaggedMeasurementTime), $uv->getUrl());
        $this->assertEquals(0, $this->getDaysRelativeToBaseline($uv->getLatestTaggedMeasurementAt()), $uv->getUrl());
        $this->assertDateEquals(self::BASELINE_TIMESTAMP, $uv->earliestSourceTime);
        $latestSourceTime = $uv->latestSourceTime;
        if(!TimeHelper::dateEquals(self::BASELINE_TIMESTAMP, $latestSourceTime)){
            $fromL = $uv->l()->latest_source_measurement_start_at;
            lei(!TimeHelper::dateEquals($fromL, $latestSourceTime),
                "latestSourceTime does not match one on laravel model");
            $calculated = $uv->calculateAttribute(UserVariable::FIELD_LATEST_SOURCE_MEASUREMENT_START_AT);
            lei(!TimeHelper::dateEquals($calculated, $latestSourceTime),
                "latestSourceTime was not calculated");
        }
        $this->assertDateEquals(self::BASELINE_TIMESTAMP, $uv->latestSourceTime);
        $processedMeasurementsA = $uv->getProcessedMeasurements();
        $this->assertCount(1, $processedMeasurementsA);
    }
    private function create_same_source_var_b_meas_at_35_days(): void{
        $day = 86400;
        $baseLinePlus35Days = self::BASELINE_TIMESTAMP + 35*$day;
        $MeasurementItemAtPlus35Days[] = new QMMeasurement($baseLinePlus35Days, 1);
        $measurementSetBAtPlus35Days[] = new MeasurementSet('variableB', $MeasurementItemAtPlus35Days,
            'serving', 'Foods', 'sourceForFillingTest', 'sum', []);
        $this->saveMeasurementSets($this->userId, $measurementSetBAtPlus35Days);
    }
    /**
     * @return QMUserVariable
     */
    private function check_earliest_5_and_latest_35_for_var_b(): QMUserVariable{
        sleep(1);
        $uv = QMUserVariable::getByNameOrId($this->userId, 'variableB');
        $uv->analyzeFully(__FUNCTION__);
        $this->assertZeroFillingValue($uv);
        $this->assertNotEmpty($uv->getOrCalculateDataSourceNames());
        $this->assertNotEmpty($uv->calculateDataSourcesCount());
        $this->assertEmpty($uv->getDataSources(), "We shouldn't have any sources because oauth_test_client doesn't have a QMDataSource");
        $l = $uv->l();
        $earliest_source_measurement_start_at = $l->earliest_source_measurement_start_at;
        $baselineAt = db_date(self::BASELINE_TIMESTAMP);
        if(!TimeHelper::dateEquals($baselineAt, $earliest_source_measurement_start_at)){
            $calculated = $uv->calculateAttribute(UserVariable::FIELD_EARLIEST_SOURCE_MEASUREMENT_START_AT);
            lei(!TimeHelper::dateEquals($earliest_source_measurement_start_at, $calculated),
                "earliest_source_measurement_start_at was not calculated before I guess");
        }
        $this->assertDateEquals($baselineAt, $earliest_source_measurement_start_at, 'BASELINE_TIMESTAMP', '$l->earliest_source_measurement_start_at');
        if(QMUserVariable::USE_SOURCE_TIMES_FOR_FILLING_TIMES){
            $this->assertDateEquals($uv->earliestSourceMeasurementStartAt, $uv->getEarliestFillingAt(),
                'earliestSourceAt', 'getEarliestFillingTime');
        } else {
            $this->assertEquals(5, $this->getDaysRelativeToBaseline($uv->getEarliestFillingAt()),
                "Should be 30 days before the B measurement because we do not go by source times anymore");
        }
        $uv->forceAnalyze(__FUNCTION__);
        $this->assertZeroFillingValue($uv);
        Memory::resetClearOrDeleteAll();
        $uv = QMUserVariable::getByNameOrId($this->userId, 'variableB');
        $this->assertZeroFillingValue($uv);
        $this->assertTrue(strtotime($uv->analysisEndedAt) > time() - 60);
        $this->assertEquals(0, $uv->fillingValue);
        $this->assertNotNull($uv->dataSourcesCount);
        $this->assertEquals(35, $this->getDaysRelativeToBaseline($uv->earliestTaggedMeasurementTime));
        $this->assertEquals(35, $this->getDaysRelativeToBaseline($uv->getLatestTaggedMeasurementAt()));
        $this->assertEquals(0, $this->getDaysRelativeToBaseline($uv->earliestSourceTime));
        $this->assertEquals(35, $this->getDaysRelativeToBaseline($uv->latestSourceTime));
        if(QMUserVariable::USE_SOURCE_TIMES_FOR_FILLING_TIMES){
            $this->assertDateEquals($uv->earliestSourceMeasurementStartAt, $uv->getEarliestFillingAt(),
                'calculateEarliestSourceTime', 'getEarliestFillingTime');
        } else {
            $existing = $uv->earliestFillingTime;
            $days = $this->getDaysRelativeToBaseline($existing);
            if($days !== 5){
                $calculated = $uv->getEarliestFillingAt();
                $calculatedDays = $this->getDaysRelativeToBaseline($calculated);
            }
            $this->assertEquals(5, $days,
                "Should be 30 days before the B measurement because we don not go by source times anymore");
        }
        $this->assertEquals(35, $this->getDaysRelativeToBaseline($uv->latestFillingTime));
        $this->assertZeroFillingValue($uv);
        $processed = $uv->getProcessedMeasurements();
        $this->assertCount(1, $processed);
        $daily = $uv->getValidDailyMeasurementsWithTagsAndFilling();
        if(QMUserVariable::USE_SOURCE_TIMES_FOR_FILLING_TIMES){
            $this->assertCount(36, $daily);
        } else {
            $this->assertCount(31, $daily);
        }
        return $uv;
    }
    private function create_var_c_meas_at_1_day(): void{
        $day = 86400;
        $baselinePlus1Day = self::BASELINE_TIMESTAMP + $day;
        $MeasurementItemAtPlus1Day[] = new QMMeasurement($baselinePlus1Day, 1, null, null, 'serving');
        $measurementSetC[] = new MeasurementSet('variableC', $MeasurementItemAtPlus1Day,
            'serving', 'Foods', 'sourceForFillingTest', 'sum', []);
        $this->saveMeasurementSets($this->userId, $measurementSetC);
    }
    private function check_earliest_and_latest_var_c_at_1_day(): void{
        sleep(1);
        $variableC = QMUserVariable::getByNameOrId($this->userId, 'variableC');
        $variableC->analyzeFully(__FUNCTION__);
        $this->assertDateEquals(self::BASELINE_TIMESTAMP, $variableC->earliestSourceMeasurementStartAt,
            'BASELINE_TIMESTAMP', 'calculateEarliestSourceTime');
        $this->assertDateEquals(self::BASELINE_TIMESTAMP + 35 * 86400,
            $variableC->latestSourceMeasurementStartAt, "35 days past baseline", 'variableC->latestSourceAt');
        if(QMUserVariable::USE_SOURCE_TIMES_FOR_FILLING_TIMES){
            $this->assertDateEquals($variableC->latestSourceMeasurementStartAt, $variableC->getLatestFillingAt(),
                'variableC->latestSourceAt', 'variableC->getLatestFillingTime');
        } else {
            $this->assertEquals(31, $this->getDaysRelativeToBaseline($variableC->getLatestFillingAt()),
                "Should be 30 days before the B measurement because we do not go by source times anymore");
        }
        $variableC->forceAnalyze(__FUNCTION__);
        $this->assertEquals(0, $variableC->fillingValue);
        $this->assertNotNull($variableC->dataSourcesCount);
        $this->assertEquals(1, $this->getDaysRelativeToBaseline($variableC->earliestTaggedMeasurementTime));
        $this->assertEquals(1, $this->getDaysRelativeToBaseline($variableC->getLatestTaggedMeasurementAt()));
        $this->assertEquals(0, $this->getDaysRelativeToBaseline($variableC->earliestSourceTime));
        $this->assertEquals(35, $this->getDaysRelativeToBaseline($variableC->latestSourceTime));
        $this->assertEquals(0, $this->getDaysRelativeToBaseline($variableC->earliestFillingTime));
        if(QMUserVariable::USE_SOURCE_TIMES_FOR_FILLING_TIMES){
            $this->assertDateEquals($variableC->latestSourceMeasurementStartAt, $variableC->getLatestFillingAt(),
                'variableC->latestSourceAt', 'variableC->getLatestFillingTime');
        } else {
            $existing = $variableC->latestFillingTime;
            $days = $this->getDaysRelativeToBaseline($existing);
            if($days !== 31){
                $calculated = $variableC->getLatestFillingAt();
                $calculatedDays = $this->getDaysRelativeToBaseline($calculated);
                $this->assertEquals(31, $calculatedDays, "Should be 30 days after last measurement");
            }
            $this->assertEquals(31, $days, "Should be 30 days after last measurement");
        }
        $this->assertZeroFillingValue($variableC);
        $processedMeasurementsC = $variableC->getValidDailyMeasurementsWithTagsAndFilling();
        if(QMUserVariable::USE_SOURCE_TIMES_FOR_FILLING_TIMES){
            $this->assertCount(36, $processedMeasurementsC);
        } else {
            $this->assertCount(31, $processedMeasurementsC, $variableC->getTestChartsUrl());
        }
    }
}
