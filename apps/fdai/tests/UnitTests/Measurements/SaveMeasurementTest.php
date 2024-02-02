<?php /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Measurements;
use App\Models\Measurement;
use InvalidArgumentException;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\MeasurementSet;
use App\Variables\QMUserVariable;
use App\Slim\View\Request\Measurement\GetMeasurementRequest;

use Tests\UnitTestCase;
class SaveMeasurementTest extends UnitTestCase {
	
    protected function setUp(): void{
        parent::setUp();
        Measurement::deleteAll();
    }
    /**
     * Test code to get list of row measurements with specified min and max time and display unit
     * @throws InvalidArgumentException
     */
    public function testSaveMeasurementsAndGetConverted(){
        $userId = 4;
        $variableName = 'App Usage';
        $sourceName = 'test source name';
        $timestamp = 1431461486;
        $originalValue = 60;
        $note = 'I am a note.';
        $submittedMeasurement = new QMMeasurement($timestamp, $originalValue, $note);
        $submittedMeasurementSet =
            new MeasurementSet($variableName, [$submittedMeasurement], 'min', 'Activity', $sourceName, 'sum');
        $this->setAuthenticatedUser($userId);
        $this->saveMeasurementSets($userId, [$submittedMeasurementSet]);
        //UserVariable::updateOrCreateAllUserVariables();
        $userVariable = QMUserVariable::getByNameOrId($userId, $variableName);
        $startTime = $timestamp - 1;
        $endTime = $timestamp + 1;
        $displayUnitAbbreviatedName = 'h';
        $displayValue = 1;
        $req = new GetMeasurementRequest(['limit' => 0, 'unitAbbreviatedName' => $displayUnitAbbreviatedName]);
        $req->setUserId($userId);
        $req->setVariableId($userVariable->getVariableIdAttribute());
        $req->setEarliestFillingTime($startTime);
        $req->setLatestFillingTime($endTime);
        $obtainedMeasurements = $req->handleGetRequest();
        $this->assertCount(1, $obtainedMeasurements);
        $this->checkMeasurementsFromApi($obtainedMeasurements,
            $sourceName,
            $userVariable,
            $displayValue,
            $displayUnitAbbreviatedName,
            $originalValue,
            $variableName,
            $note);
    }
    /**
     * @group Model
     * @group Measurement
     * @throws InvalidArgumentException
     */
    public function testSaveMeasurementsWithUnitAbbreviatedName(){
        $userId = 4;
        $variableName = 'App Usage';
        $sourceName = 'test source name';
        $timestamp = 1431461486;
        $originalValue = 60;
        $note = 'I am a note.';
        $submittedMeasurement = new QMMeasurement($timestamp, $originalValue, $note);
        $submittedMeasurementSet =
            new MeasurementSet($variableName, [$submittedMeasurement], 'minutes',
                'Activity', $sourceName, 'sum');
        $this->setAuthenticatedUser($userId);
        $this->saveMeasurementSets($userId, [$submittedMeasurementSet]);
        $userVariable = QMUserVariable::getByNameOrId($userId, $variableName);
        $startTime = $timestamp - 1;
        $endTime = $timestamp + 1;
        $displayUnitAbbreviatedName = 'h';
        $displayValue = 1;
        $req = new GetMeasurementRequest(['limit' => 0, 'unitAbbreviatedName' => 'h']);
        $req->setUserId($userId);
        $req->setVariableId($userVariable->getVariableIdAttribute());
        $req->setEarliestFillingTime($startTime);
        $req->setLatestFillingTime($endTime);
        $req->setUnitAbbreviatedName($displayUnitAbbreviatedName);
        $obtainedMeasurements = $req->getMeasurementsInRequestedUnit();
        $this->assertCount(1, $obtainedMeasurements);
        $this->checkMeasurementsFromApi($obtainedMeasurements,
            $sourceName,
            $userVariable,
            $displayValue,
            $displayUnitAbbreviatedName,
            $originalValue,
            $variableName,
            $note);
    }
    /**
     * Test code to get list of row measurements with grouping by day
     *
     * @group Model
     * @group Measurement
     * @throws InvalidArgumentException
     */
    public function testGetMeasurementsWithNoUnitSpecified(){
        $userId = 4;
        $variableName = 'App Usage';
        $sourceName = 'test source name';
        $timestamp = 1431461486;
        $submittedValue = 10;
        $submittedUnit = 'min';
        $category = 'Activity';
        $combinationOperation = 'sum';
        $note = 'I am a note.';
        $submittedMeasurement = new QMMeasurement($timestamp, $submittedValue, $note);
        $submittedMeasurementSet = new MeasurementSet($variableName, [$submittedMeasurement], $submittedUnit, $category, $sourceName, $combinationOperation);
        $this->setAuthenticatedUser($userId);
        $this->saveMeasurementSets($userId, [$submittedMeasurementSet]);
        //UserVariable::updateOrCreateAllUserVariables();
        $uv = QMUserVariable::getByNameOrId($userId, $variableName);
        $this->assertEquals($submittedUnit, $uv->getUnitAbbreviatedName());
        $startTime = $timestamp - 1;
        $endTime = $timestamp + 1;
        $req = new GetMeasurementRequest(['limit' => 0]);
        $req->setUserId($userId);
        $req->setVariableId($uv->getVariableIdAttribute());
        $req->setEarliestFillingTime($startTime);
        $req->setLatestFillingTime($endTime);
        $req->setExcludeExtendedProperties(false);
        $obtainedMeasurements = $req->handleGetRequest();
        $this->assertCount(1, $obtainedMeasurements);
        $this->assertEquals($submittedUnit, $uv->getUnitAbbreviatedName());
        foreach ($obtainedMeasurements as $m) {
            $this->assertEquals($sourceName, $m->sourceName);
            $this->assertTrue($uv->earliestTaggedMeasurementTime <= $m->startTimeEpoch);
            $this->assertEquals($submittedUnit, $m->unitAbbreviatedName,
                "Return in the user's last submitted unit if none specified");
            $this->assertEquals(10, $m->value);
            $this->assertTrue($submittedValue <= $m->originalValue);
            $this->assertEquals($variableName, $m->variableName);
            $this->assertEquals($variableName, $m->variableName);
            $this->assertTrue($note <= $m->note);
        }
    }
    /**
     * @param $obtainedMeasurements
     * @param string $sourceName
     * @param QMUserVariable $userVariable
     * @param int $displayValue
     * @param string $displayUnitAbbreviatedName
     * @param int $originalValue
     * @param string $variableName
     * @param string $note
     */
    protected function checkMeasurementsFromApi($obtainedMeasurements,
                                                string $sourceName,
                                                QMUserVariable $userVariable,
                                                int $displayValue,
                                                string $displayUnitAbbreviatedName,
                                                int $originalValue,
                                                string $variableName,
                                                string $note): void{
        foreach($obtainedMeasurements as $m){
            $this->assertEquals($sourceName, $m->sourceName);
            $this->assertGreaterThanOrEqual($userVariable->earliestTaggedMeasurementTime, $m->startTimeEpoch);
            $this->assertGreaterThanOrEqual($displayValue, $m->value);
            $this->assertEquals($displayUnitAbbreviatedName, $m->unitAbbreviatedName);
            $this->assertGreaterThanOrEqual($originalValue, $m->originalValue);
            $this->assertEquals($variableName, $m->variableName);
            $this->assertTrue($note <= $m->note);
        }
    }
}
