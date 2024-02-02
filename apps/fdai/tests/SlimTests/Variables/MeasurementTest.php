<?php /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Variables;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\Variable;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\BadRequestException;
use App\Exceptions\UserVariableNotFoundException;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Properties\User\UserIdProperty;
use App\Storage\DB\TestDB;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\QMMeasurementV1;
use App\Slim\Model\Measurement\MeasurementSet;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\WalkOrRunDistanceCommonVariable;
use App\Slim\Model\QMUnit;
use App\Variables\QMUserVariable;
use App\Slim\View\Request\Measurement\GetMeasurementRequest;
class MeasurementTest extends \Tests\SlimTests\SlimTestCase {
    protected function setUp(): void{
        parent::setUp();
	    TestDB::resetTestDB();
		UserVariableRelationship::deleteAll();
    }
    /**
     * Test code to get list of row measurements with grouping by day
     * @group Model
     * @group Measurement
     */
    public function testProcessMeasurements(){
        $baseline = time() - 365 * 86400;
        $day = 86400;
        $day1 = $day + $baseline;
        $day2 = 2*$day + $baseline;
        $day3 = 3*$day + $baseline;
        $day4 = 4*$day + $baseline;
        $day5 = 5*$day + $baseline;
        $userId = 1;
        $this->setAuthenticatedUser($userId);
        $startTime = $day1;
        $endTime = $day5;  //5 days later
        $displayUnitAbbreviatedName = 'm';
        $variableName = WalkOrRunDistanceCommonVariable::NAME;
        $v = QMUserVariable::findOrCreateByNameOrId($userId, $variableName);
        $v->setFillingTypeAttribute(BaseFillingTypeProperty::FILLING_TYPE_NONE);
        $v->combinationOperation = BaseCombinationOperationProperty::COMBINATION_MEAN;
        $v->setOnsetDelay(0.5 * $day);
        $v->setDurationOfAction($day);
        $v->minimumAllowedValue = 2;
        $v->maximumAllowedValue = 4;
        $v->setCommonUnitId(3);
        $rawMeasurements = $this->getOneThroughFiveRawMeasurementsV1($v,
            $displayUnitAbbreviatedName, $day1, $day2, $day3, $day4, $day5);
        $requestParams['groupingWidth'] = 2*$day;
        $v->setMeasurements($rawMeasurements);
        $processedMeasurements = $v->getProcessedMeasurements();
        foreach ($processedMeasurements as $processedMeasurement) {
            //$this->assertInstanceOf('App\Slim\Model\Measurement\Measurement\ProcessedMeasurement', $processedMeasurement);
            $this->assertTrue($startTime <= $processedMeasurement->startTimeEpoch + $requestParams['groupingWidth']/2);
            $this->assertTrue($endTime + $requestParams['groupingWidth'] / 2 >= $processedMeasurement->startTimeEpoch);
            $this->assertEquals($displayUnitAbbreviatedName, $processedMeasurement->unitAbbreviatedName);
            $this->assertEquals($variableName, $processedMeasurement->variableName);
        }
    }
    /**
     * Test code to get list of row measurements with specified min and max time and display unit
     * @group Model
     * @group Measurement
     * @throws BadRequestException
     */
    public function testGetRawMeasurements(){
        Measurement::deleteAll();
        $userId = 4;
        $sourceName = 'Slice';
        $variableName = 'App Usage';
        $unitAbbreviatedName = 's';
        $this->setAuthenticatedUser($userId);
        $startTime = time();
        $rawMeasurements = $this->generateRawMeasurements($startTime);
        $measurementSet[] = new MeasurementSet($variableName, $rawMeasurements,
            $unitAbbreviatedName, 'Miscellaneous',
            $sourceName, 'sum', []);
        $this->saveMeasurementSets($userId, $measurementSet);
        QMUserVariable::analyzeByNameOrId($userId, $variableName);
        $uv = QMUserVariable::getByNameOrId($userId, $variableName);
        $req = new GetMeasurementRequest(['limit' => 0]);
        $req->setUserId($userId);
        $id = $uv->getVariableIdAttribute();
        $req->setVariableId($id);
        $measurements = $req->getMeasurementsInRequestedUnit();
        /*
         * This magic number is based on number of measurements we just created.
         */
        $this->assertCount(5, $measurements);
        foreach ($measurements as $m) {
            $commonVariable = $m->getCommonVariable();
            $this->assertEquals($uv->name, $commonVariable->name);
            $this->assertEquals($uv->getVariableIdAttribute(), $commonVariable->id);
            $this->assertEquals($sourceName, $m->sourceName);
            $this->assertTrue($uv->earliestTaggedMeasurementTime <= $m->startTimeEpoch);
            $this->assertDateLessThanOrEqual($uv->getLatestTaggedMeasurementAt(),
                $m->getStartAt(), "latestTaggedMeasurementTime", "startTimeEpoch");
            $this->assertTrue(0 <= $m->value);
            $this->assertEquals($commonVariable->unitAbbreviatedName, $m->unitAbbreviatedName);
            $this->assertEquals($variableName, $m->variableName);
        }
    }
    /**
     * Test code to get list of row measurements without specified min and max time and display unit
     *
     * @group Model
     * @group Measurement
     * @throws BadRequestException
     * @throws AlreadyAnalyzingException
     */
    public function testGetRawMeasurementsWithDefaultUnit(){
        Measurement::deleteAll();
        $userId = 4;
        $sourceName = 'Slice';
        $variableName = 'App Usage';
        $unit = null;
        $this->setAuthenticatedUser($userId);
        $startTime = time();
        $rawMeasurements = $this->generateRawMeasurements($startTime);
        $measurementSet[] = new MeasurementSet($variableName, $rawMeasurements,
            's', 'Miscellaneous', $sourceName,
            'sum', []);
        $this->saveMeasurementSets($userId, $measurementSet);
        $uv = QMUserVariable::getByNameOrId($userId, $variableName);
        $uv->analyzeFully(__FUNCTION__);
        $uv = QMUserVariable::getByNameOrId($userId, $variableName);
        $req = new GetMeasurementRequest(['limit' => 0]);
        $req->setUserId($userId);
        $req->setVariableId($uv->getVariableIdAttribute());
        $measurements = $req->getMeasurementsInCommonUnit();
        /*
         * This magic number is based on number of measurements we just created.
         */
        $this->assertCount(5, $measurements);
        foreach ($measurements as $m) {
            //$this->assertInstanceOf('App\Slim\Model\Measurement\Measurement', $measurement);
            $this->assertEquals($sourceName, $m->sourceName);
            $this->assertTrue($uv->earliestTaggedMeasurementTime <= $m->startTimeEpoch);
            $this->assertDateLessThanOrEqual($uv->getLatestTaggedMeasurementAt(),
                $m->getStartAt(), "latestTaggedMeasurementTime", "startTimeEpoch");
            $this->assertTrue(0 <= $m->value);
            $this->assertEquals($uv->getCommonUnit()->abbreviatedName, $m->unitAbbreviatedName);
            $this->assertEquals($variableName, $m->variableName);
            //$this->assertInstanceOf('DateTime', $measurement->humanTime);
        }
    }
    /**
     * Test code to get list of row measurements with grouping by day
     * @group Model
     * @group Measurement
     * @throws BadRequestException
     * @throws UserVariableNotFoundException
     */
    public function testGetMeasurementsWithDayGrouping(){
        $userId = 1;
        $this->setAuthenticatedUser($userId);
        $sourceName = 'sourceForFillingTest';
        $variableName = 'VariableA';
        $latestFillingTime = 1348072640 + 86400 * 5;  //5 days later
        $latestFillingTimeString = date('Y-m-d H:i:s', $latestFillingTime);
        $unitAbbreviatedName = 'serving';
        $groupingWidth = $day = 86400;
        $measurementATime = $baselineTime = 1348072640;
        $measurementBTime = $baselineTime + 35*$day;
        $measurementCTime = $baselineTime + 5*$day;
        $items[] = new QMMeasurement($measurementATime, 1);
        $items[] = new QMMeasurement($measurementBTime, 1);
        $items[] = new QMMeasurement($measurementCTime, 1);
        $measurementSetA[] = new MeasurementSet($variableName, $items, $unitAbbreviatedName,
            'Foods', $sourceName, 'sum', []);
        $this->saveMeasurementSets($userId, $measurementSetA);
        QMUserVariable::analyzeByNameOrId($userId, $variableName);
        $v = QMUserVariable::getByNameOrId($userId, $variableName);
        $this->assertZeroFillingValue($v);
        $actionArray = $v->getNotificationActionButtons();
        $this->assertEquals("Yes", $actionArray[0]->shortTitle);
        $this->assertEquals("No", $actionArray[1]->shortTitle);
        $req = new GetMeasurementRequest([
            'limit' => 0,
            'groupingWidth' => $groupingWidth,
            'earliestMeasurementTime' => $baselineTime,
            'latestMeasurementTime' => $latestFillingTime
        ]);
        $this->assertEquals($baselineTime, $req->getEarliestFillingTime());
        $this->assertEquals($latestFillingTime, $req->getLatestFillingTime());
        $req->setQmUserVariable($v);
        $this->assertEquals($baselineTime, $req->getEarliestFillingTime());
        $this->assertEquals($latestFillingTime, $req->getLatestFillingTime());
        $processed = $req->getProcessedMeasurements();
        $this->assertCount(6, $processed);
        $rawMeasurements = $req->getMeasurementsInCommonUnit();
        $this->assertCount(2, $rawMeasurements);
        $hoursWithMeasurement = [];
        $fillerMeasurements = [];
        $actualMeasurements = [];
        $fillingValue = $v->fillingValue;
        foreach ($processed as $m) {
            $hour = $m->startTimeEpoch;
            $value = $m->value;
            $this->assertNotContains($hour, $hoursWithMeasurement, 'We should not have more than one measurement for single startTime');
            $hoursWithMeasurement[] = $hour;
            if ($value === $fillingValue) {
                $fillerMeasurements[] = $m;
            } else {
                $actualMeasurements[] = $m;
            }
            $start = $m->startTimeEpoch;
            $this->assertTrue($baselineTime <= $start + $groupingWidth);
            if(!$v->latestFillingTime){le('!$v->latestFillingTime');}
            $this->assertTrue($v->latestFillingTime + $groupingWidth / 2 >= $start,
                "Measurement at $start is after latest filling time: $v->latestFillingTime");
            $this->assertTrue(0 <= $value);
            $this->assertEquals(QMUnit::getByNameOrId($unitAbbreviatedName)->id, $m->unitId);
            $this->assertEquals($variableName, $m->variableName);
        }
        $this->assertCount(4, $fillerMeasurements);
        $this->assertCount(2, $actualMeasurements);
        // Assert that all groups containing an actual measurement have a source set.
        foreach ($actualMeasurements as $m) {
            $this->assertEquals($sourceName, $m->sourceName);
        }
    }
    /**
     * @param int $startTime
     * @return array
     */
    private function generateRawMeasurements(int $startTime): array{
        $rawMeasurements[] = new QMMeasurement($startTime, 600);
        $rawMeasurements[] = new QMMeasurement($startTime - 1, 457);
        $rawMeasurements[] = new QMMeasurement($startTime - 2, 372);
        $rawMeasurements[] = new QMMeasurement($startTime - 3, 400);
        $rawMeasurements[] = new QMMeasurement($startTime - 4, 124);
        foreach($rawMeasurements as $m){$m->userId = UserIdProperty::USER_ID_DEMO;}
        return $rawMeasurements;
    }
    /**
     * @param QMUserVariable $uv
     * @param string $displayUnitAbbreviatedName
     * @param $day1
     * @param $day2
     * @param $day3
     * @param $day4
     * @param $day5
     * @return array
     */
    private function getOneThroughFiveRawMeasurementsV1(QMUserVariable $uv, string $displayUnitAbbreviatedName, $day1, $day2, $day3, $day4, $day5): array{
        $rawMeasurements[] = new QMMeasurementV1(null, $uv, $day1, 1, $displayUnitAbbreviatedName, 'I am a note.');
        $rawMeasurements[] = new QMMeasurementV1(null, $uv, $day2, 2, $displayUnitAbbreviatedName, 'I am a note.');
        $rawMeasurements[] = new QMMeasurementV1(null, $uv, $day3, 3, $displayUnitAbbreviatedName, 'I am a note.');
        $rawMeasurements[] = new QMMeasurementV1(null, $uv, $day4, 4, $displayUnitAbbreviatedName, 'I am a note.');
        $rawMeasurements[] = new QMMeasurementV1(null, $uv, $day5, 5, $displayUnitAbbreviatedName, 'I am a note.');
        foreach($rawMeasurements as $m){$m->userId = UserIdProperty::USER_ID_DEMO;}
        return $rawMeasurements;
    }
    public function testPostYesNoMeasurementWithWrongCombOp(){
        $this->setAuthenticatedUser(1);
        $name = 'Pfizer-BioNTech COVID-19 Vaccine';
        $res = $this->slimPost('api/v3/measurements', [
            0 => [
                'combinationOperation' => 'MEAN',
                'icon' => 'ion-ios-medkit-outline',
                'inputType' => 'yesOrNo',
                'maximumAllowedValue' => NULL,
                'minimumAllowedValue' => NULL,
                'pngPath' => 'img/variable_categories/treatments.png',
                'startAt' => '2021-05-28 20:34:00',
                'unitAbbreviatedName' => 'yes/no',
                'unitId' => 19,
                'unitName' => 'Yes/No',
                'value' => 'Yes',
                'variableCategoryId' => NULL,
                'variableCategoryName' => 'Treatments',
                'variableName' => $name,
                'note' => '',
                'displayValueAndUnitString' => 'Yes yes/no',
                'valueUnitVariableName' => 'Yes yes/no Pfizer-BioNTech COVID-19 Vaccine',
                'originalStartAt' => '2021-05-28 20:34:00',
            ],
        ]);
        $body = json_decode($res->getBody());
        $this->assertEquals(1, $body->data->measurements->{"Pfizer-BioNTech COVID-19 Vaccine"}[0]->value);
        $v = QMUserVariable::findByName(UserIdProperty::USER_ID_DEMO, $name);
        $this->assertEquals(1, $v->numberOfMeasurements);
        $this->assertEquals(1, $v->numberOfRawMeasurementsWithTagsJoinsChildren);
    }
}
