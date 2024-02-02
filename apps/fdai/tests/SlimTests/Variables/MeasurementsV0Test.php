<?php /** @noinspection PhpDeprecationInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Variables;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoChangesException;
use App\Models\Measurement;
use App\Models\OAClient;
use App\Models\Variable;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\QMMeasurementExtended;
use App\Slim\Model\QMUnit;
use App\Storage\DB\TestDB;
use App\Storage\DB\Writable;
use App\Units\OneToFiveRatingUnit;
use App\VariableCategories\MiscellaneousVariableCategory;
use App\Variables\QMCommonVariable;
use PDO;
use Tests\DBUnitTestCase;
use Tests\QMBaseTestCase;
/**
 * Class MeasurementsV0Test
 * @package Tests\Api\Measurements
 */
class MeasurementsV0Test extends \Tests\SlimTests\SlimTestCase {
	protected function setUp(): void{
		parent::setUp();
		TestDB::resetTestDB();
	}
	/**
     * Test /api/measurements/v2 method for adding measurements
     * @group api
     */
    public function testPostMeasurementsV0() {
        $this->setAuthenticatedUser(1);
        $postData = '[{"measurements":[{"timestamp":1406419860,"value":"1"},{"timestamp":1406519965,"value":"3"}],
        "name":"testPostMeasurementsVariable","source":"testPostMeasurementsSource","category":"Symptoms","combinationOperation":"MEAN","unit":"/5",
        "latitude":26.56,"longitude":56.53,"location":"Test Location"}]';
        $this->postAndCheckMeasurementsResponse($postData, '/5');
        $variable = Variable::findByName('testPostMeasurementsVariable');
        $this->assertEquals('10', $variable->variable_category_id);
        $this->assertEquals('10', $variable->default_unit_id);
        $this->assertEquals(BaseCombinationOperationProperty::COMBINATION_MEAN, $variable->combination_operation);
        $this->assertEquals(null, $variable->getRawAttribute(Variable::FIELD_DURATION_OF_ACTION));
        $this->assertEquals(null, $variable->getRawAttribute(Variable::FIELD_ONSET_DELAY));
        $this->assertEquals(0, $variable->is_public, "New user-created variables should not be automatically public in case it contains sensitive info like a calendar event or something. ");
        $measurements = $variable->measurements()->get();
        $this->assertCount(2, $measurements);
        $this->assertQueryCountLessThan(25);
    }
    public function testPostMeasurementsYesNoForFiveRating() {
        $this->setAuthenticatedUser(1);
        $postData = '[{"measurements":[{"timestamp":1406419860,"value":"1"}],
        "name":"Overall Mood","source":"testPostMeasurementsSource","category":"Symptoms","combinationOperation":"MEAN","unit":"yes/no",
        "latitude":26.56,"longitude":56.53,"location":"Test Location"}]';
        $this->postAndCheckMeasurementsResponse($postData, 'yes/no');
        /** @var Measurement $measurement */
        $measurement = Measurement::orderBy(Measurement::CREATED_AT, 'desc')->first();
        $this->assertEquals(5, $measurement->value);
        $this->assertEquals(1, $measurement->original_value);
    }
    /**
     * Test /api/measurements/v2 method for adding measurements with diff unit for same variable
     * Check if new variable is created with unit name like "VariableName (UnitName)"
     * @group api
     */
    public function testPostMeasurementsWithSameVariableDiffUnits() {
        $this->setAuthenticatedUser(1);
        $this->postAndGetDecodedBody('api/v3/measurements',
            '[{"measurements":[{"timestamp":1406419860,"value":"1"},{"timestamp":1406519965,"value":"3"}],
            "name":"Test Variable","source":"test source name","category":"Miscellaneous","combinationOperation":"SUM",
            "unit":"m"}]');
        $this->postAndCheckMeasurementsResponse('[{"measurements":[{"timestamp":1406419861,"value":"1"},{"timestamp":1406519966,"value":"3"}],
        "name":"Test Variable","source":"test source name","category":"Miscellaneous","combinationOperation":"MEAN",
        "unit":"mg"}]', 'mg');
        $variable = $this->getVariableRowArrayByName('Test Variable');
        $this->assertEquals(MiscellaneousVariableCategory::ID, $variable['variable_category_id']);
        $this->assertEquals('3', $variable['default_unit_id']);
        $variable = $this->getVariableRowArrayByName('Test Variable (Weight)');
        $this->assertEquals(MiscellaneousVariableCategory::ID,
            $variable['variable_category_id']);
        $this->assertEquals('7', $variable['default_unit_id']);
    }
    /**
     * @return void
     */
    private function postTestMeasurements(): void{
        $this->setAuthenticatedUser(1);
        // Insert measurements
	    $this->postAndGetDecodedBody('api/v3/measurements',
            '[{"measurements":[{"timestamp":1406419860,"value":"1"},{"timestamp":1406519965,"value":"3"}],
            "name":"Back Pain","source":"test source name","category":"Symptoms","combinationOperation":"MEAN","unit":"/5"}]');
        $response = $this->getAndDecodeBody('/api/measurements', [
	        'userId' => 1,
	        'variableName' => 'Back Pain',
	        'startTime' => "1406419859",
	        'endTime' => "1406519966"
        ]);
        $this->assertCount(2, $response);
        $this->getAndCheckMeasurementsLimit();
    }
    /**
     * @param array $parameters
     * @return mixed
     */
    private function getAndCheckMeasurements(array $parameters = []) {
        $measurements = $this->getAndDecodeBody('/api/measurements', $parameters);
        foreach ($measurements as $measurement) {
            $this->checkProcessedMeasurementObject($measurement);
        }
        return $measurements;
    }
    private function getAndCheckMeasurementsLimit() {
        $limit = 1;  //limit offset test
        $responseObject = $this->getAndCheckMeasurements([
            'userId'       => 1,
            'variableName' => 'Back Pain',
            'startTime'    => "1406419859",
            'endTime'      => "1406519966",
            'offset'       => 1,
            'limit'        => $limit
        ]);
        $this->assertCount($limit, $responseObject);
    }
    public function testGetMeasurementsInReverseChronologicalOrderV0() {
	    TestDB::resetTestDB();
        $this->postTestMeasurements();
        $this->getAndCheckMeasurements([
            'sort'         => '-startTimeEpoch',
            'variableName' => 'Back Pain',
            'doNotProcess' => true,
            'offset'       => 0,
            'limit'        => 50
        ]);
    }
    /**
     * @param QMMeasurementExtended|QMMeasurement $measurement
     */
    public function checkProcessedMeasurementObject($measurement) {
        foreach (QMMeasurement::getLegacyPropertiesToAdd() as $legacyProperty) {
            $this->assertNotNull($measurement->$legacyProperty, "V0 needs legacy properties for MoodiModo Android");
        }
        $this->assertNotNull($measurement->value);
        $this->assertNotNull($measurement->startTime);
        if ($measurement->unitAbbreviatedName === OneToFiveRatingUnit::ABBREVIATED_NAME) {
            $this->assertNotFalse(stripos($measurement->displayValueAndUnitString,
                QMUnit::getOneToFiveRating()->abbreviatedName),
                "displayValueAndUnitString " . $measurement->displayValueAndUnitString . " does not contain /5");
        }
        $this->checkMeasurementPropertyTypes($measurement);
    }
    public function testGetMeasurementsWithDiffUnitV0() {
        $this->setAuthenticatedUser(1);
        // Insert measurements
        $response = $this->postAndGetDecodedBody('api/v3/measurements', '[{"measurements":[{"timestamp":1406419860,"value":"60"},{"timestamp":1406519965,"value":"180"}],
            "name":"Test Diff Unit Variable","source":"test source name","category":"Symptoms","combinationOperation":"SUM",
            "unit":"min"}]');
        // Assert the measurements were inserted
        $measurements = $this->getMeasurementsV0([
            'userId'       => 1,
            'variableName' => 'Test Diff Unit Variable',
            'startTime'    => 1406419859,
            'endTime'      => 1406519966,
            'unit'         => 'h'
        ]);
        $this->assertCount(2, $measurements);
        $this->assertEquals(1, $measurements[0]->value, 'Not returning correct value');
        foreach ($measurements as $measurement) {
            $this->assertEquals($measurement->originalValue / 60, $measurement->value);
            $this->assertEquals('h', $measurement->unitAbbreviatedName, 'Not returning correct unit');
            $this->assertEquals('h', $measurement->unitAbbreviatedName, 'Not returning correct unitAbbreviatedName');
            $this->checkProcessedMeasurementObject($measurement);
        }
    }
    /**
     * @param $parameters
     * @return mixed
     * @noinspection PhpSameParameterValueInspection
     */
    private function getMeasurementsV0($parameters) {
        $response = $this->slimGet('/api/measurements', $parameters);
        $measurements = json_decode($response->getBody(), false);
        return $measurements;
    }
    public function testGetMeasurementsWithOutOfFiveUnit() {
        $this->setAuthenticatedUser(1);
	    $this->postAndGetDecodedBody('api/v3/measurements', '[{"measurements":[{"timestamp":1406419860,"value":"100"},{"timestamp":1406519965,"value":"50"}],
            "name":"Test Diff Unit Variable","source":"test source name","category":"Emotions","combinationOperation":"MEAN",
            "unit":"%"}]');
        // Assert the measurements were inserted
        $parameters = [
            'userId'       => 1,
            'variableName' => 'Test Diff Unit Variable',
            'startTime'    => 1406419859,
            'endTime'      => 1406519966,
            'unit'         => '/5'
        ];
        $responseObject = $this->getMeasurements($parameters);
        $this->assertCount(2, $responseObject);
        $this->assertEquals('5', $responseObject[0]->value, 'Not returning correctly converted value');
        foreach ($responseObject as $measurement) {
            $this->assertEquals($measurement->originalValue / 25 + 1, $measurement->value);
            $this->assertEquals('/5', $measurement->unitAbbreviatedName, 'Not returning correct unit');
            $this->assertEquals('/5', $measurement->unitAbbreviatedName, 'Not returning correct unitAbbreviatedName');
            $this->checkProcessedMeasurementObject($measurement);
        }
    }
    /**
     * Issue #151
     * @group api
     */
    public function testPostMeasurementsWithDifferentUnitsV0() {
        $this->setAuthenticatedUser(1);
        $postData = json_encode([
            [
                'measurements'         => [
                    [
                        'timestamp' => 1406419860,
                        'value'     => '1',
                        'note'      => 'Apples 1'
                    ]
                ],
                'name'                 => 'Apples',
                'sourceName'           => 'Different units test source',
                'category'             => 'Miscellaneous',
                'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN,
                'unit'                 => 'kg',
            ]
        ]);
        $this->postAndCheckMeasurementsResponse($postData, 'kg');
        // add measurement with same unit category
        $postData = json_encode([
            [
                'measurements'         => [
                    [
                        'timestamp' => 1406419860,
                        'value'     => '300',
                        'note'      => 'Apples 1'
                    ]
                ],
                'name'                 => 'Apples',
                'sourceName'           => 'Different units test source',
                'category'             => 'Miscellaneous',
                'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN,
                'unit'                 => 'g',
            ]
        ]);
        $this->postAndCheckMeasurementsResponse($postData, 'g');
        // add measurement with different unit category
        sleep(1); // To verify we're using new created_at even when they happen too fast
        $this->postAndCheckMeasurementsResponse([
            [
                'measurements'         => [
                    [
                        'timestamp' => 1406419860,
                        'value'     => '1',
                        'note'      => 'Apples 1'
                    ]
                ],
                'name'                 => 'Apples',
                'sourceName'           => 'Different units test source',
                'category'             => 'Miscellaneous',
                'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN,
                'unit'                 => 'pieces',
            ]
        ], 'pieces');
        $firstVariable = Variable::where('name', 'Apples')->first();
        $secondVariable = Variable::where('name', 'Apples (Count)')->first();
        $this->assertEquals(0, $secondVariable->is_public);
        $this->assertDateGreaterThan($firstVariable->created_at, $secondVariable->created_at);
    }
    /**
     * Test Inconsistent conversion
     * @group api
     * @covers \App\Slim\Controller\Measurement\PostMeasurementController::saveNormalMeasurements
     */
    public function testInconsistentConversionMeasurements() {
        $this->setAuthenticatedUser(1);
        $this->postAndCheckMeasurementsResponse([[
            'measurements' => [
                ['timestamp' => 1338100000, 'value' => 1, 'note' => 'Note 1'],
                ['timestamp' => 1338200000, 'value' => 2, 'note' => 'Note 2' ],
                ['timestamp' => 1338300000, 'value' => 3, 'note' => 'Note 3' ]
            ],
            'name'                 => 'Weight Test Variable',
            'sourceName'           => 'Accupedo,Fitbit',
            'category'             => 'Physique',
            'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN,
            'unit'                 => 'lb'
        ]], 'lb');
        $this->postAndCheckMeasurementsResponse([[
            'measurements'         => [['timestamp' => 1338400000, 'value' => 4,  'note' => 'Note 4']],
            'name'                 => 'Weight Test Variable',
            'sourceName'           => 'Accupedo,Fitbit',
            'category'             => 'Physique',
            'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN,
            'unit'                 => 'kg'
        ]], 'kg');
        $measurements = $this->getAndDecodeBody('/api/measurements', [
	        'userId' => 1,
	        'variableName' => 'Weight Test Variable',
	        'startTime' => 1263690300,
	        'endTime' => 1431176222,
	        'groupingWidth' => 86400
        ]);
        $this->compareObjectFixture('measurements', $measurements);
        $this->assertEquals('Note 1', $measurements[0]->note);
        $this->assertEquals(1, $measurements[0]->originalValue);
        $this->assertEquals(0.454, $measurements[0]->value);
        $this->assertEquals(4, $measurements[3]->value);
    }
    /**
     * Test Inconsistent conversion
     * @group api
     */
    public function testPostIncompatibleMeasurementsV0() {
        $this->setAuthenticatedUser(1);
        $response = $this->postAndGetDecodedBody('api/v3/measurements',  [
            [
                'measurements'         => [
                    [
                        'timestamp' => 1338120000,
                        'value'     => 1,
                        'note'      => 'Note 1'
                    ]
                ],
                'name'                 => 'IncompatibleMeasurements Test Variable',
                'sourceName'           => 'Accupedo,Fitbit',
                'category'             => 'Physique',
                'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN,
                'unit'                 => 'lb'
            ]
        ], false,201);
        $measurementTime = 1338120003;
        $response = $this->postAndGetDecodedBody('api/v3/measurements', [ [
                'measurements'         => [ [
                        'timestamp' => $measurementTime,
                        'value'     => 4,
                        'note'      => 'Note 4'
                    ]
                ],
                'name'                 => 'IncompatibleMeasurements Test Variable',
                'sourceName'           => 'Accupedo,Fitbit',
                'category'             => 'Physique',
                'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN,
                'unit'                 => 'h'
            ]
        ], false,201);
        $variableResponse = $response->data->userVariables[0];
        $this->assertEquals("IncompatibleMeasurements Test Variable (Duration)", $variableResponse->name);
        if($variableResponse->id !== $variableResponse->variableId){le("Needed for backward compatibility");}
        $measurement = Measurement::whereUserVariableId($variableResponse->userVariableId)->first();
        if(!$measurement){le("No measurement for new variable. ".Measurement::getDataLabIndexUrl());}
        $this->assertEquals(4, $measurement->value);
        $newVariable = $measurement->getUserVariable();
        $this->assertZeroFillingValue($newVariable, "We should have zero default for hours");
        $start = $measurementTime - 86400;
        $end = $measurementTime + 86400;
        $response = $this->getAndDecodeBody('/api/measurements', [
	        'userId' => 1,
	        'variableName' => 'IncompatibleMeasurements Test Variable (Duration)',
	        'startTime' => $start,
	        'endTime' => $end,
	        'groupingWidth' => 86400
        ]);
        $this->compareObjectFixture('measurements', $response);
        $this->assertEquals('IncompatibleMeasurements Test Variable (Duration)', $response[0]->variableName);
        $this->assertEquals(null, $response[0]->originalValue, "Should be a zero filling value for hours");
        $this->assertEquals('h', $response[0]->unitAbbreviatedName);
        $this->assertEquals(0, $response[0]->value);
    }
    /**
     * Test /api/measurements/v2 method for adding measurements
     * @group api
     */
    public function testPostMeasurementsThatCannotBeSummed() {
        $this->setAuthenticatedUser(1);
        $postData = '[{"measurements":[{"timestamp":1406419860,"value":"1"},{"timestamp":1406519965,"value":"3"}],
        "name":"testCannotBeSum","source":"testCannotBeSum","category":"Symptoms","combinationOperation":"SUM","unit":"/5",
        "latitude":26.56,"longitude":56.53,"location":"Test Location"}]';
        QMBaseTestCase::setExpectedRequestException(ModelValidationException::class);
        $this->postAndCheckMeasurementsResponse($postData, '/5', 400);
        $dbh = Writable::pdo();
        $sqlCheckVariables = "SELECT * FROM variables WHERE name = 'testCannotBeSum'";
        $variables = $dbh->query($sqlCheckVariables)->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(0, $variables);
    }
    /**
     * Test /api/measurements/v2 method for adding measurements
     * @group api
     */
    public function testPostMeasurementWithNewVariableTwice() {
        $this->setAuthenticatedUser(1);
        $postData = '
            [
              {
                "variableName": "Unique Test Variable Mon Sep 19 2016 19:07:55 GMT+0000 (UTC)",
                "source": "test source name",
                "variableCategoryName": "Emotions",
                "unitAbbreviatedName": "/5",
                "combinationOperation": "MEAN",
                "measurements": [
                  {
                    "startTimeEpoch": 1474312083,
                    "value": 3,
                    "note": "",
                    "latitude": null,
                    "longitude": null
                  }
                ]
              }
            ]
        ';
        $this->postAndCheckMeasurementsResponse($postData, '/5');
        $this->getVariableRowFromDbAndCheckNameAndCombinationOperation('Unique Test Variable Mon Sep',
            BaseCombinationOperationProperty::COMBINATION_MEAN);
        QMBaseTestCase::setExpectedRequestException(NoChangesException::class);
	    $this->postAndGetDecodedBody('api/v3/measurements', $postData, false, 400);
        $this->getVariableRowFromDbAndCheckNameAndCombinationOperation('Unique Test Variable Mon Sep',
            BaseCombinationOperationProperty::COMBINATION_MEAN);
    }
    /**
     * @return PDO
     */
    private function getDb(): PDO{
        return Writable::pdo();
    }
    /**
     * @param $name
     * @return array
     */
    private function getVariableRowArrayByName($name): array{
        $sqlCheckVariables = 'SELECT * FROM variables WHERE name = "' . $name . '"';
        $variables = $this->getDb()->query($sqlCheckVariables)->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(1, $variables);
        $variable = $variables[0];
        $this->assertEquals($name, $variable['name']);
        return $variable;
    }

    /**
     * @param string $name
     * @param string $combinationOperation
     */
    private function getVariableRowFromDbAndCheckNameAndCombinationOperation(string $name,
                                                                             string $combinationOperation) {
        $variables = Variable::query()
            ->where('name', Writable::like(), '%'.$name.'%')
            ->get();
        $this->assertCount(1, $variables);
        $this->assertContains($name, $variables[0]->name);
        $this->assertEquals($combinationOperation, $variables[0]->combination_operation);
    }
}
