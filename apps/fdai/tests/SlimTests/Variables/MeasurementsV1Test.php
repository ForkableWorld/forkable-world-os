<?php /** @noinspection PhpDeprecationInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Variables;
use App\Exceptions\NoChangesException;
use App\Exceptions\QMException;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Slim\Model\Measurement\MeasurementSet;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\QMMeasurementExtended;
use App\Slim\Model\QMUnit;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\TestDB;
use App\Storage\DB\Writable;
use App\Units\OneToFiveRatingUnit;
use App\Units\OneToTenRatingUnit;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use Carbon\Carbon;
use PDO;
use Tests\DBUnitTestCase;
use Tests\QMBaseTestCase;
use Tests\SlimTests\SlimTestCase;
/**
 * Class MeasurementsV1Test
 * @package Tests\Api\Measurements
 */
class MeasurementsV1Test extends \Tests\SlimTests\SlimTestCase {
	public function setUp(): void{
		parent::setUp();
		Measurement::deleteAll();
		GlobalVariableRelationship::deleteAll();
		UserVariableRelationship::deleteAll();
		UserVariable::deleteAll();		
	}
	/**
     * Test Inconsistent conversion
     *
     * @group api
     */
    public function testPostCountForPrivateVariableWithTwoUsers()
    {
        $this->setAuthenticatedUser(1);
        $postData = [
            [
                'measurements' => [ [ 'startTime' => 1338120000, 'value' => 1, 'note' => 'Note 1' ] ],
                'name' => 'County Test Variable',
                'sourceName' => 'Accupedo,Fitbit',
                'category' => 'Physique',
                'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_SUM,
                'unit' => 'count'
            ]
        ];
        $response = $this->slimPost('api/v3/measurements', $postData);
        $this->checkPostMeasurementsResponse($response);
        $this->setAuthenticatedUser($userId = 2);
        $response = $this->slimPost('api/v3/measurements', $postData);
        $this->checkPostMeasurementsResponse($response);
        $variables = QMCommonVariable::readonly()->whereLike('name', '%County%')
            ->getArray();
        $this->assertCount(1, $variables);
    }
    /**
     * Test /api/v1/measurements method for adding measurements
     * @group api
     */
    public function testPostMeasurementsAndUpdateWithId(){
	    Measurement::truncate();
	    UserVariable::truncate();
        $this->setAuthenticatedUser(1);
        $variable = $this->postMeasurementAndCheckVariable('[{"measurements":[
            {"startTime":1406419860,"value":"1"},
            {"startTime":1406519965,"value":"3"}
        ],
        "name":"Back Pain","source":"test source name","category":"Symptoms","combinationOperation":"MEAN","unit":"/5",
        "latitude":26.56,"longitude":56.53,"location":"Test Location"}]');
        $this->checkBackPainCommonVariableRow($variable);
        $measurements = $measurements = Measurement::whereVariableId($variable['id'])->get();
        $this->assertCount(2, $measurements);
        $response = $this->slimPost('api/v3/measurements', '[{"measurements":[
            {"startTime":1406419760,"value":"2", "id":1},
            {"startTime":1406519865,"value":"4", "id":2}
        ],
        "name":"Back Pain","source":"test source name","category":"Symptoms","combinationOperation":"MEAN","unit":"/5",
        "latitude":26.56,"longitude":56.53,"location":"Test Location"}]');
        $this->checkPostMeasurementsResponse($response);
        $measurements = Measurement::whereVariableId($variable['id'])->get();
        $this->assertCount(2, $measurements,
            "We should have updated the existing measurements by ID instead of creating new ones");
        $this->assertQueryCountLessThan(41);
    }
    public function testGetNoteAverages(){
        $this->postOneEmotionTestMeasurement();
        $response = $this->slimGet('/api/v1/notes', ['variableName' => "Nervousness"]);
        $responseObject = json_decode($response->getBody(), true);
        $this->assertCount(6, $responseObject['data']['wordsByAverageValueAscending']);
        $this->assertCount(6, $responseObject['data']['wordsByAverageValueDescending']);
        $this->assertCount(6, $responseObject['data']['words']);
        $this->assertCount(1, $responseObject['data']['phrasesByAverageValueAscending']);
        $this->assertCount(1, $responseObject['data']['phrasesByAverageValueDescending']);
        $this->assertCount(1, $responseObject['data']['phrases']);
    }
    /**
     * @param int $expectedCode
     * @return void
     */
    public function postOneEmotionTestMeasurement(int $expectedCode = 201){
        $this->setAuthenticatedUser(1);
        $response = $this->slimPost('api/v3/measurements', [
            0 =>
                [
                    'category' => 'Emotions',
                    'combinationOperation' => 'MEAN',
                    'measurements' =>
                        [
                            0 =>
                                [
                                    'duration' => 0,
                                    'note' => 'I\'m nervous about what to do about my ex boyfriend Eric.',
                                    'timestamp' => 1465428659,
                                    'value' => 5,
                                ],
                        ],
                    'name' => 'Nervousness',
                    'source' => 'test source name',
                    'unit' => '/5',
                ],
        ], false,$expectedCode);
        if($expectedCode === 201){
            $this->checkPostMeasurementsResponse($response);
        }
    }
    public function testPostBasicMeasurementsArrayInsteadOfSet(){
        Measurement::deleteAll();
	    $builder = Variable::whereName("overall Mood");
	    $v = $builder->first();
		$this->assertNotNull($v);
        $this->setAuthenticatedUser(1);
        $v = OverallMoodCommonVariable::getUserVariableByUserId(1);
        $first = 1406419860;
        $firstRounded = $v->roundStartTime($first);
        $second = 1406519965;
        $secondRounded = $v->roundStartTime($second);
        $testMeasurements = [
            [
                'variable_name'       => "overall Mood",
                'source_name'         => 'test source name',
                'startTime'           => $first,
                'value'               => '10',
                'note'                => 'note 1',
                'unitAbbreviatedName' => '/10',
                'latitude'            => 26.56,
                'longitude'           => 56.53,
                'location'            => 'Test Location'
            ],
            [
                'variable_name'       => "Overall Mood",
                'source_name'         => 'test source name',
                'startTime'           => $second,
                'value'               => '1',
                'note'                => 'note 2',
                'unitAbbreviatedName' => '/10',
                'latitude'            => 26.56,
                'longitude'           => 56.53,
                'location'            => 'Test Location'
            ],
        ];
        $response = $this->slimPost('api/v3/measurements', $testMeasurements);
        $this->checkPostMeasurementsResponse($response);
        $measurements = $this->getMeasurements(['sort' => '-startTime'], 2);
        $m1 = $measurements[0];
        $m2 = $measurements[1];
        $this->assertEquals(OverallMoodCommonVariable::NAME, $m1->variableName);
        $this->assertEquals('test source name', $m1->sourceName);
        $this->assertEquals($secondRounded, $m1->startTimeEpoch);
        $this->assertNotNull($m1->startTimeString);
        $this->assertEquals(1, $m1->value);
        $this->assertEquals(OneToFiveRatingUnit::ABBREVIATED_NAME, $m1->unitAbbreviatedName);
        $this->assertEquals('note 2', $m1->note);
        $this->assertEquals(OverallMoodCommonVariable::NAME, $m2->variableName);
        $this->assertEquals($firstRounded, $m2->startTimeEpoch);
        $this->assertNotNull($m2->startTimeString);
        $this->assertEquals(5, $m2->value);
        $this->assertEquals(OneToFiveRatingUnit::ABBREVIATED_NAME, $m2->unitAbbreviatedName);
        $this->assertEquals('note 1', $m2->note);
    }
    public function testPostBasicMeasurementsArrayInsteadOfSetForNewVariable(){
        QMMeasurement::writable()->delete();
        $this->setAuthenticatedUser(1);
        $testMeasurements = '
            {
              "variableName": "Unique Test Variable",
              "value": 3,
              "note": "note",
              "startTimeEpoch": 1490482006,
              "unitAbbreviatedName": "/5",
              "variableCategoryName": "Emotions",
              "combinationOperation": "MEAN",
              "sourceName": "MoodiModo for linux"
            }';
        $response = $this->slimPost('api/v3/measurements', $testMeasurements);
        $this->checkPostMeasurementsResponse($response);
        $response = $this->slimGet('/api/v1/measurements', ['sort' => '-startTime']);
        $responseObject = json_decode($response->getBody(), true);
        $this->assertCount(1, $responseObject);
        $this->assertEquals('Unique Test Variable', $responseObject[0]['variableName']);
        $v = QMUserVariable::getByNameOrId(1, 'Unique Test Variable');
        $rounded = $v->roundStartTime(1490482006);
        $this->assertEquals($rounded, $responseObject[0]['startTimeEpoch']);
        $this->assertNotNull($responseObject[0]['startTimeString']);
        $this->assertEquals(3, $responseObject[0]['value']);
        $this->assertEquals('/5', $responseObject[0]['unitAbbreviatedName']);
        $this->assertEquals('note', $responseObject[0]['note']);
    }
    /**
     * Test /api/v1/measurements method for adding measurements
     * @group api
     */
    public function testPostSameMeasurementTwice(){
        $this->postOneEmotionTestMeasurement();
        $this->checkNervousnessAndMeasurements();
        QMBaseTestCase::setExpectedRequestException(NoChangesException::class);
        $this->postOneEmotionTestMeasurement(400);
        $this->checkNervousnessAndMeasurements();
    }
    public function testShareUserMeasurements(){
        $this->createTestSymptomRatingMeasurement();
        $variableName = 'Back Pain';
        $variableSettings = [['variable' => $variableName, 'shareUserMeasurements' => 1]];
        $this->postVariableSettings($variableSettings);
        $uv = UserVariable::findByName($variableName, 1);
        $this->assertEquals(1, $uv->is_public);
        $this->setAuthenticatedUser(4);
        $response = $this->slimGet('/api/v1/measurements', ['variableName' => $variableName, 'userId' => 1]);
        $responseObject = json_decode($response->getBody(), true);
        $this->assertCount(1, $responseObject);
        $variableSettings = [['variable' => $variableName, 'shareUserMeasurements' => 0]];
        $this->postVariableSettings($variableSettings);
        $this->setAuthenticatedUser(4);
        $this->slimGet('/api/v1/measurements', ['variableName' => $variableName, 'userId' => 1]);
    }
    public function testDeleteMeasurementsWithVariableId(){
        $variable = $this->createTestSymptomRatingMeasurement();
        $dbh = Writable::pdo();
        $this->deleteMeasurementAndCheckResponse([
            'startTime'  => '1406519965',
            'variableId' => $variable['id'],
        ], $variable, $dbh);
    }
    public function testDeleteMeasurementsWithVariableName(){
        $variable = $this->createTestSymptomRatingMeasurement();
        $dbh = Writable::pdo();
        $this->deleteMeasurementAndCheckResponse([
            'startTime'    => 1406519965,
            'variableName' => $variable['name'],
        ], $variable, $dbh);
    }
    /**
     * Test /api/v1/measurements method for adding measurements
     * @group api
     */
    public function testPostMeasurementsWithCombinationOperationCaseInsensitive(){
        $this->setAuthenticatedUser(1);
        $postData = '[{"measurements":[{"startTime":1406419860,"value":"1"},{"startTime":1406519965,"value":"3"}],
        "name":"Back Pain","source":"test source name","category":"Symptoms","combinationOperation":"mean","unit":"/5",
        "latitude":26.56,"longitude":56.53,"location":"Test Location"}]';
        $response = $this->slimPost('api/v3/measurements', $postData);
        $this->checkPostMeasurementsResponse($response);
        $dbh = Writable::pdo();
        DBUnitTestCase::setUserVariablesWithZeroStatusToWaiting();
        while ($v = QMUserVariable::getWaitingUserVariableToUpdate()) {
            $v->analyzeFully(__FUNCTION__);
        }
        $sqlCheckVariables = "SELECT * FROM variables WHERE name = 'Back Pain'";
        $variables = $dbh->query($sqlCheckVariables)->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(1, $variables);
        $variable = $variables[0];
        $this->checkBackPainCommonVariableRow($variable);
        $sqlCheckMeasurements = 'SELECT user_id, source_name, start_time as startTime, value, unit_id, latitude, longitude,
        location FROM measurements WHERE variable_id = ' . $variable['id'];
        $measurements = $dbh->query($sqlCheckMeasurements)->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(2, $measurements);
    }
    public function testGetZeroMeasurements(){
        $this->setAuthenticatedUser(1);
        $variable = $this->createTestSymptomRatingMeasurement();
        $postData =
            [
                'startTime'  => '1406519965',
                'variableId' => $variable['id'],
            ];
        $this->postApiV3('measurements/delete', json_encode($postData), 204);
        $parameters = ['userId' => 1, 'variableId'  => $variable['id']];
        $responseObject = $this->getMeasurements($parameters, 0);
        $this->assertEquals([], $responseObject);
    }
    public function testGetMeasurementsByVariableName(){
        $this->setAuthenticatedUser(1);
        $postData = '[{"measurements":[{"startTime":1406419860,"value":"1"},{"startTime":1406519965,"value":"3"}],
            "name":"Back Pain","source":"test source name","category":"Symptoms","combinationOperation":"MEAN","unit":"/5"}]';
        $this->slimPost('api/v3/measurements', $postData);
        // Assert the measurements were inserted
        $parameters = [
            'userId'       => 1,
            'variableName' => 'Back Pain',
            'startTime'    => 1406419859,
            'endTime'      => 1406519966
        ];
        $responseObject = $this->getMeasurements($parameters, 2);
        $limit = 1;
        //limit offset test
        $parameters = [
            'userId'       => 1,
            'variableName' => 'Back Pain',
            'startTime'    => 1406419859,
            'endTime'      => 1406519966,
            'offset'       => 1,
            'limit'        => $limit
        ];
        $measurements = $this->getMeasurements($parameters, $limit);
        $this->getAndCheckUserVariables(['name' => "Back Pain", QMRequest::PARAM_INCLUDE_CHARTS => true]);
    }
    public function testGetMeasurementsV3InReverseChronologicalOrder(){
        $this->savePositivelyCorrelatedCauseAndEffectMeasurements();
        $this->setAuthenticatedUser(1);
        $this->getMeasurements([
            'sort'         => '-startTimeEpoch',
            'variableName' => 'CauseVariableName',
            'doNotProcess' => true, 'offset' => 0, 'limit' => 50]);
    }
    public function testGetMeasurementsWithDiffUnitV1(){
        $this->setAuthenticatedUser(1);
        $postData = '[{"measurements":[{"startTime":1406419860,"value":"60"},{"startTime":1406519965,"value":"180"}],
            "name":"Test Diff Unit Variable","source":"test source name","category":"Symptoms","combinationOperation":"SUM",
            "unit":"min"}]';
        $this->slimPost('api/v3/measurements', $postData);
        // Assert the measurements were inserted
        $parameters = [
            'userId'       => 1,
            'variableName' => 'Test Diff Unit Variable',
            'startTime'    => 1406419859,
            'endTime'      => 1406519966,
            'unit'         => 'h'
        ];
        $response = $this->slimGet('/api/v1/measurements', $parameters);
        $responseObject = json_decode($response->getBody(), true);
        $this->assertCount(2, $responseObject);
        $this->assertEquals($responseObject[0]['value'], '1', 'Not returning correct unit');
        foreach ($responseObject as $measurement) {
            $this->assertEquals($measurement['originalValue']/60, $measurement['value']);
            $this->assertEquals($measurement['unitAbbreviatedName'], 'h', 'Not returning correct unit');
            $this->assertEquals('h', $measurement['unitAbbreviatedName'], 'Not returning correct unitAbbreviatedName');
            $this->assertEquals('Hours', $measurement['unitName'], 'Not returning correct unitName');
            $this->checkMeasurementPropertyTypes((object)$measurement);
        }
    }
    public function testPostMeasurementsWithDifferentUnitsV1(){
        $this->setAuthenticatedUser(1);
        // add a measurement
        $response = $this->slimPost('api/v3/measurements', [[
            'measurements'         => [['startTime' => 1406419860, 'value' => '1', 'note' => 'Apples 1']],
            'name'                 => 'Apples',
            'sourceName'               => 'Different units test source',
            'category'             => 'Miscellaneous',
            'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN,
            'unit'                 => 'kg',
        ]]);
        $this->checkPostMeasurementsResponse($response);
        // add measurement with same unit category
        $response = $this->slimPost('api/v3/measurements', [[
            'measurements'         => [['startTime' => 1406419860, 'value' => '300', 'note' => 'Apples 1']],
            'name'                 => 'Apples',
            'sourceName'               => 'Different units test source',
            'category'             => 'Miscellaneous',
            'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN,
            'unit'                 => 'g',
        ]]);
        $this->checkPostMeasurementsResponse($response);
        // add measurement with different unit category
        $this->slimPost('api/v3/measurements', [[
            'measurements'         => [['startTime' => 1406419860, 'value' => '1', 'note' => 'Apples 1']],
            'name'                 => 'Apples',
            'sourceName'               => 'Different units test source',
            'category'             => 'Miscellaneous',
            'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN,
            'unit'                 => 'pieces',
        ]]);
    }
    public function testPostMeasurementsWithDifferentUnitsButSameUnitCategoryAndCorrectUserDefaultUnitReturned(){
        $this->setAuthenticatedUser(1);
        $variableName = 'Water Temperature';
        $set = [
            'measurements'         => [['startTime' => 1406419860, 'value' => 0, 'note' => 'DifferentUnitVariable 1']],
            'name'                 => $variableName,
            'sourceName'           => 'Different units test source',
            'category'             => 'Miscellaneous',
            'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN,
            'unitAbbreviatedName'  => 'C',
        ];
        $response = $this->postAndGetDecodedBody('/api/v1/measurements', $set);
        $userVariableFromApi = $response->data->userVariables[0];
        $this->assertEquals($set['unitAbbreviatedName'], $userVariableFromApi->unitAbbreviatedName);
        $this->assertEquals(0, $userVariableFromApi->lastValue);
        $this->assertEquals(null, $userVariableFromApi->secondToLastValue);
        $this->assertEquals(null, $userVariableFromApi->thirdToLastValue);
        $set['unitAbbreviatedName'] = 'F';
        $set['measurements'] =
            [
                [
                    'startTime' => 1406419861,
                    'value'     => 212,
                    'note'      => 'DifferentUnitVariable 1'
                ]
            ];
        [
            $userVariableFromApi,
            $userVariableObject
        ] = $this->postMeasurementAndCheckLastValue($set, $variableName);
        $this->assertEquals(212, $userVariableFromApi->lastValue);
        $this->assertEquals(0, $userVariableObject->getSecondToLastValueInCommonUnit());
        $this->assertEquals(32, $userVariableFromApi->secondToLastValue);
        $this->assertEquals(null, $userVariableObject->getThirdToLastValueInCommonUnit());
        $this->assertEquals(null, $userVariableFromApi->thirdToLastValue);
        $set['unitAbbreviatedName'] = 'C';
        $set['measurements'] =
            [
                [
                    'startTime' => 1406419862,
                    'value'     => 100,
                    'note'      => 'DifferentUnitVariable 1'
                ]
            ];
        [$userVariableFromApi, $userVariableObject] = $this->postMeasurementAndCheckLastValue($set, $variableName);
        $this->assertEquals(100, $userVariableFromApi->lastValue);
        $this->assertEquals(0, $userVariableObject->getSecondToLastValueInCommonUnit());
        $this->assertEquals(0, $userVariableFromApi->secondToLastValue);
        $this->assertEquals(null, $userVariableObject->getThirdToLastValueInCommonUnit());
        $this->assertEquals(null, $userVariableFromApi->thirdToLastValue);
        $set['unitAbbreviatedName'] = 'mg';
        $set['category'] = 'Treatments';
        $set['measurements'] =
            [
                [
                    'startTime' => 1406419862,
                    'value'     => 0.5,
                    'note'      => 'DifferentUnitVariable 1'
                ]
            ];
        $response = $this->slimPost('api/v3/measurements', json_encode($set));
        $response = json_decode($response->getBody(), false);
        $userVariableFromApi = $response->data->userVariables[0];
        $this->assertEquals($set['unitAbbreviatedName'], $userVariableFromApi->unitAbbreviatedName);
        $userVariableObject = QMUserVariable::getByNameOrId(1, $userVariableFromApi->id);
        $this->assertEquals($set['category'], $userVariableObject->getVariableCategoryName());
        $this->assertEquals(0.5, $userVariableObject->getLastValueInCommonUnit());
        $this->assertEquals(0.5, $userVariableFromApi->lastValue);
        $this->assertEquals(null, $userVariableObject->getSecondToLastValueInCommonUnit());
        $this->assertEquals(null, $userVariableObject->secondToLastValue);
        $this->assertEquals(null, $userVariableObject->getThirdToLastValueInCommonUnit());
        $this->assertEquals(null, $userVariableFromApi->thirdToLastValue);
    }
    public function testPostMeasurementsWithDifferentUnitsButSameUnitCategory(){
        $this->setAuthenticatedUser(1);
        $commonUnit = OneToFiveRatingUnit::instance();
        $postData = [
            [
                'measurements'         => [
                    [
                        'startTime' => 1406419860,
                        'value'     => '5',
                        'note'      => 'DifferentUnitVariable 1'
                    ]
                ],
                'name'                 => 'DifferentUnitVariable',
                'sourceName'           => 'Different units test source',
                'category'             => 'Miscellaneous',
                'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN,
                'unit'                 => $commonUnit->abbreviatedName,
            ]
        ];
        $response = $this->slimPost('api/v3/measurements', $postData);
        $this->checkPostMeasurementsResponse($response);
        $gottenMeasurements = $this->getMeasurements(['variableName' => 'DifferentUnitVariable'], 1);
        /** @var QMMeasurementExtended $m */
        foreach ($gottenMeasurements as $m) {
            $this->assertEquals($commonUnit->name, $m->unitName);
            $this->assertEquals($commonUnit->abbreviatedName, $m->unitAbbreviatedName);
            $this->assertEquals(5, round($m->value));
        }
        $userUnit = OneToTenRatingUnit::instance();
        // add measurement with same unit category
        $postData = [
            'measurements'         => [
                [
                    'startTime' => 1406419861,
                    'value'     => '10',
                    'note'      => 'DifferentUnitVariable 1'
                ]
            ],
            'name'                 => 'DifferentUnitVariable',
            'sourceName'           => 'Different units test source',
            'category'             => 'Miscellaneous',
            'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN,
            'unit'                 => $userUnit->abbreviatedName,
        ];
        $response = $this->slimPost('api/v3/measurements', $postData);
        $this->checkPostMeasurementsResponse($response);
        $gottenMeasurements = $this->getMeasurements(['variableName' => 'DifferentUnitVariable'], 2);
        foreach ($gottenMeasurements as $m) {
            $this->assertEquals($userUnit->name, $m->unitName);
            $this->assertEquals($userUnit->abbreviatedName, $m->unitAbbreviatedName);
            $this->assertEquals(10, $m->value);
        }
        $this->assertEquals(5, $gottenMeasurements[0]->originalValue);
        $this->assertEquals(10, $gottenMeasurements[1]->originalValue);
        $gottenMeasurements = $this->getMeasurements([], 2);
        foreach ($gottenMeasurements as $m) {
            $this->assertEquals($commonUnit->name, $m->unitName);
            $this->assertEquals($commonUnit->abbreviatedName, $m->unitAbbreviatedName);
            $this->assertEquals(5, round($m->value));
        }
        $this->assertEquals(5, $gottenMeasurements[0]->originalValue);
        $this->assertEquals(10, $gottenMeasurements[1]->originalValue);
    }
    // Need to be rounded for MoodiModo
    public function testMakeSureFiveStarRatingsAreRounded(){
        $this->setAuthenticatedUser(1);
        $postData = json_encode([[
                                     'measurements'         => [['startTime' => 1406419860, 'value' => '2.6', 'note' => 'DifferentUnitVariable 1']],
                                     'name'                 => 'DifferentUnitVariable',
                                     'sourceName'               => 'Different units test source',
                                     'category'             => 'Miscellaneous',
                                     'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN,
                                     'unit'                 => '/5',
        ]]);
        $response = $this->slimPost('api/v3/measurements', $postData);
        $this->checkPostMeasurementsResponse($response);
        $response = $this->slimGet('/api/v1/measurements', ['variableName' => 'DifferentUnitVariable']);
        /** @var QMMeasurement[] $gottenMeasurements */
        $gottenMeasurements = json_decode($response->getBody(), true);
        $this->assertCount(1, $gottenMeasurements);
        $gottenMeasurements[0] = (object) $gottenMeasurements[0];
        foreach ($gottenMeasurements as $gottenMeasurement){
            $this->assertEquals('1 to 5 Rating', $gottenMeasurement->unitName);
            $this->assertEquals('/5', $gottenMeasurement->unitAbbreviatedName);
            $this->assertEquals('/5', $gottenMeasurement->unitAbbreviatedName);
            $this->assertEquals(3, round($gottenMeasurement->value));
        }
    }
    /**
     * Test Inconsistent conversion
     *
     * @group api
     */
    public function testPostIncompatibleMeasurementsV1(){
        $this->setAuthenticatedUser(1);
        $response = $this->slimPost('api/v3/measurements', [
            [
                'measurements' => [ [ 'startTime' => 1338120000, 'value' => 1, 'note' => 'Note 1' ] ],
                'name' => 'IncompatibleMeasurements Test Variable',
                'sourceName' => 'Accupedo,Fitbit',
                'category' => 'Physique',
                'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN,
                'unit' => 'lb'
            ]
        ]);
        $this->checkPostMeasurementsResponse($response);
        $response = $this->slimPost('api/v3/measurements', [
            [
                'measurements' => [ [ 'startTime' => 1338120003, 'value' => 4, 'note' => 'Note 4' ] ],
                'name' => 'IncompatibleMeasurements Test Variable',
                'sourceName' => 'Accupedo,Fitbit',
                'category' => 'Physique',
                'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN,
                'unit' => 'h'
            ]
        ]);
        $this->checkPostMeasurementsResponse($response);
        $response = $this->slimGet('/api/v1/measurements', [
	        'userId' => 1,
	        'variableName' => 'IncompatibleMeasurements Test Variable (Duration)',
	        'startTime' => 1263690300,
	        'endTime' => 1431176222,
	        'groupingWidth' => 86400
        ]);
        $response = json_decode($response->getBody(), true);
        $this->assertEquals('IncompatibleMeasurements Test Variable (Duration)', $response[0]['variableName']);
        $this->assertEquals(null, $response[0]['originalValue']);
        $this->assertEquals('h', $response[0]['unitAbbreviatedName']);
        $this->assertEquals('Hours', $response[0]['unitName']);
        $this->assertEquals(0, $response[0]['value']);
        $response = $this->slimPost('api/v3/measurements', [
            [
                'measurements' => [ [ 'startTime' => 1338120003, 'value' => 4, 'note' => 'Note 4' ] ],
                'name' => 'IncompatibleMeasurements Test Variable (Duration)',
                'sourceName' => 'Accupedo,Fitbit',
                'category' => 'Physique',
                'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN,
                'unit' => 'm'
            ]
        ]);
        $this->checkPostMeasurementsResponse($response);
        $parameters = [
            'userId' => 1,
            'variableName' => 'IncompatibleMeasurements Test Variable (Distance)',
            'startTime' => 1263690300,
            'endTime' => 1431176222,
            'groupingWidth' => 86400
        ];
        $response = $this->getMeasurements($parameters);
        $this->assertEquals('IncompatibleMeasurements Test Variable (Distance)', $response[0]->variableName);
        $this->assertEquals('4', $response[0]->originalValue);
        $this->assertEquals('m', $response[0]->unitAbbreviatedName);
        $this->assertEquals('4', $response[0]->value);
    }
    /**
     * @param int $limit
     * @param bool $filterByVariable
     *
     */
    public function getBackPainMeasurements($limit = 60, $filterByVariable = false){
        $this->setAuthenticatedUser(1);
        $variableName = "Back Pain";
        $postData = '[{"measurements":[{"startTime":1406419860,"value":"1"},{"startTime":1406519965,"value":"3"}],
            "name":"' . $variableName . '","source":"test source name","category":"Symptoms","combinationOperation":"MEAN","unit":"/5"}]';
        $this->slimPost('api/v3/measurements', $postData);
        $parameters = ['offset' => 0, 'limit' => $limit, 'sort' => '-startTime'];
        if($filterByVariable){$parameters['variableName'] = $variableName;}
        $measurements = $this->getMeasurements($parameters);
        $totalMeasurements = 2;
        if($limit > $totalMeasurements){$this->assertCount($totalMeasurements, $measurements);}
    }
    public function testGetAllMeasurementsWithLowLimit(){$this->getBackPainMeasurements(1);}
    public function testGetAllMeasurementsWithHighLimit(){$this->getBackPainMeasurements(3);}
    public function testGetVariableMeasurementsWithLowLimit(){$this->getBackPainMeasurements(1, true);}
    public function testGetVariableMeasurementsWithHighLimit(){$this->getBackPainMeasurements(3, true);}
    public function testGetAllMeasurementsWithNullCategoryParameter(){
        $this->setAuthenticatedUser(1);
        $postData = '[{"measurements":[{"startTime":1406419860,"value":"1"},{"startTime":1406519965,"value":"3"}],
            "name":"Back Pain","source":"test source name","category":"Symptoms","combinationOperation":"MEAN","unit":"/5"}]';
        $this->slimPost('api/v3/measurements', $postData);
        // Assert the measurements were inserted
        $parameters = ['offset' => 0, 'limit' => 60, 'sort' => '-startTime', 'variableCategoryName' => null];
        $measurements = $this->getMeasurements($parameters, 2);
    }
    public function testGetAllMeasurementsWithinCategory(){
        $this->setAuthenticatedUser(1);
        $postData = '[{"measurements":[{"startTime":1406419860,"value":"1"},{"startTime":1406519965,"value":"3"}],
            "name":"Back Pain","source":"test source name","category":"Symptoms","combinationOperation":"MEAN","unit":"/5"}]';
        $this->slimPost('api/v3/measurements', $postData);
        // Assert the measurements were inserted
        $parameters = ['offset' => 0, 'limit' => 60, 'sort' => '-startTimeEpoch', 'variableCategoryName' => "Symptoms"];
        $measurements = $this->getMeasurements($parameters, 2);
    }
    public function testGetAllMeasurementsSortedByStartTimeEpoch(){
        $this->setAuthenticatedUser(1);
        $postData = '[{"measurements":[{"startTime":1406419860,"value":"1"},{"startTime":1406519965,"value":"3"}],
            "name":"Back Pain","source":"test source name","category":"Symptoms","combinationOperation":"MEAN","unit":"/5"}]';
        $this->slimPost('api/v3/measurements', $postData);
        // Assert the measurements were inserted
        $parameters = ['offset' => 0, 'limit' => 60, 'sort' => '-startTimeEpoch'];
        $measurements = $this->getMeasurements($parameters, 2);
    }
    public function testDeleteMeasurement(){
        $this->setAuthenticatedUser(1);
        // Insert measurements
        $postData = '[{"measurements":[{"startTime":1406419860,"value":"1"},{"startTime":1406519965,"value":"3"}],
            "name":"Overall Mood","source":"test source name","category":"Symptoms","combinationOperation":"MEAN","unit":"/5"}]';
        $this->slimPost('api/v3/measurements', $postData);
        // Assert the measurements were inserted
        $body = ['startTime' => 1406419860, 'variableId' => 1398];
        $this->deleteAndVerify($body);
    }
    public function testDeleteMeasurementUsingStartTimeEpoch(){
        $this->setAuthenticatedUser(1);
        $postData = '[{"measurements":[{"startTime":1406419860,"value":"1"},{"startTime":1406519965,"value":"3"}],
            "name":"Overall Mood","source":"test source name","category":"Symptoms","combinationOperation":"MEAN","unit":"/5"}]';
        $this->slimPost('api/v3/measurements', $postData);
        // Assert the measurements were inserted
        $body = ['startTimeEpoch' => 1406419860, 'variableId' => 1398];
        $this->deleteAndVerify($body);
    }
    /**
     * Test /api/v1/measurements method for adding measurements
     *
     * @group api
     */
    public function testPostMeasurementsAndUpdateStartTimeWithId(){
		Measurement::truncate();
		UserVariable::truncate();
        $this->setAuthenticatedUser(1);
        $postData = '[{"measurements":[{"startTime":1406419861,"value":"1"}],
        "name":"Back Pain","source":"test source name","category":"Symptoms","combinationOperation":"MEAN","unit":"/5",
        "latitude":26.56,"longitude":56.53,"location":"Test Location"}]';
        $variable = $this->postMeasurementAndCheckVariable($postData);
        $dbh = Writable::pdo();
        $postData = '[{"measurements":[{"startTime":1406519966,"value":"1", "id":1}],
        "name":"Back Pain","source":"test source name","category":"Symptoms","combinationOperation":"MEAN","unit":"/5",
        "latitude":26.56,"longitude":56.53,"location":"Test Location"}]';
        $response = $this->slimPost('api/v3/measurements', $postData);
        $this->checkPostMeasurementsResponse($response);
        $sqlCheckMeasurements = 'SELECT user_id, source_name, start_time as startTime, value, unit_id, latitude, longitude,
        location FROM measurements WHERE variable_id = ' . $variable['id'] . ' AND deleted_at IS NULL';
        $measurements = $dbh->query($sqlCheckMeasurements)->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(1, $measurements);
    }
    public function testPostMeasurementWithStartTimeEpoch(){
        $this->setAuthenticatedUser(1);
        $this->slimPost('api/v3/measurements',
            '[{"variableName":"Aaa Test Treatment","sourceName":"QuantiModo for linux","variableCategoryName":"Treatments",
            "unitAbbreviatedName":"mg","measurements":[{"startTimeEpoch":1499784420,"value":100,"note":null}]}]');
    }
    /**
     * Test /api/v1/measurements method for adding measurements
     *
     * @group api
     */
    public function testPostMeasurementsAndUpdateValueWithId(){
        Measurement::deleteAll();
        $this->setAuthenticatedUser(1);
        $variable = $this->postMeasurementAndCheckVariable( [
            0 =>
                 [
                    'measurements' =>
                         [
                            0 =>
                                 [
                                    'startTime' => 1406419861,
                                    'value' => '1',
                                ],
                        ],
                    'name' => 'Back Pain',
                    'source' => 'test source name',
                    'category' => 'Symptoms',
                    'combinationOperation' => 'MEAN',
                    'unit' => '/5',
                    'latitude' => 26.56,
                    'longitude' => 56.53,
                    'location' => 'Test Location',
                ],
        ]);
        $id = Measurement::query()->first()->value('id');
        $lastValue = 2;
        $postData = '[{"measurements":[{"startTime":1406519966,"value":"'.$lastValue.'", "id":'.$id.'}],
            "name":"Back Pain","source":"test source name","category":"Symptoms","combinationOperation":"MEAN","unit":"/5",
            "latitude":26.56,"longitude":56.53,"location":"Test Location"}]';
        $response = $this->slimPost('api/v3/measurements', $postData);
        $this->checkPostMeasurementsResponse($response);
        $measurements = Measurement::whereVariableId($variable['id'])->get();
        $this->assertCount(1, $measurements);
        $parameters = ['offset' => 0, 'limit' => 60, 'sort' => '-startTime'];
        $measurements = $this->getMeasurements($parameters, 1);
        $this->assertEquals($lastValue, $measurements[0]->value);
    }
    /**
     * Test /api/v1/measurements method for adding measurements
     * @group api
     */
    public function testPostMeasurementsAndUpdateValueUpdateEndpoint(){
        $this->setAuthenticatedUser(1);
        QMMeasurement::writable()->delete();
        $postData = '[{"measurements":[{"startTime":1406419861,"value":"1"}],
        "name":"Back Pain","source":"test source name","category":"Symptoms","combinationOperation":"MEAN","unit":"/5",
        "latitude":26.56,"longitude":56.53,"location":"Test Location"}]';
        $response = $this->slimPost('api/v3/measurements', $postData);
        $this->checkPostMeasurementsResponse($response);
        $dbh = Writable::pdo();
        $sql = "SELECT * FROM measurements";
        $measurements = $dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(1, $measurements);
        $measurement = $measurements[0];
        $updatedMeasurement = [ 'id' => $measurement['id'], 'value' => 2 ];
        $this->postApiV3('measurements/update', $updatedMeasurement);
        $measurements = $this->getMeasurements([
            'limit' => 200,
            'appName' => 'test source name',
            'appVersion' => '2.1.1.0',
            'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT
        ], 1);
        $this->assertEquals(2, $measurements[0]->value);
    }
    /**
     * @param $postData
     * @param $variable
     * @param PDO $dbh
     */
    private function deleteMeasurementAndCheckResponse($postData, $variable, PDO $dbh){
        $this->deleteViaPostMethod('/api/v1/measurements/delete', $postData,
            'Measurement deleted successfully');
        $sqlCheckMeasurements = 'SELECT user_id, source_name, start_time as startTime, value, unit_id, latitude, longitude,
        location FROM measurements WHERE variable_id = ' . $variable['id'] . ' AND deleted_at IS NULL';
        $measurements = $dbh->query($sqlCheckMeasurements)->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(0, $measurements);
    }
    public function testPostYesNoTwice(){
        $this->setAuthenticatedUser(1);
        Measurement::deleteAll();
        $this->slimPost('api/v3/measurements', '[{"measurements":[{"startTime":1406419861,"value":"1"}],
            "name":"YesNoVariable","source":"test source name","category":"Symptoms","unit":"yes/no",
            "latitude":26.56,"longitude":56.53,"location":"Test Location"}]');
        //QMBaseTestCase::setExpectedRequestException(NoChangesException::class);
        $this->slimPost('api/v3/measurements', '[{"measurements":[{"startTime":1406419862,"value":"1"}],
            "name":"YesNoVariable","source":"test source name","category":"Symptoms","unit":"yes/no",
            "latitude":26.56,"longitude":56.53,"location":"Test Location"}]', false, 201);
        $measurements = Writable::pdo()->query("SELECT * FROM measurements")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($measurements as $measurement){
            $this->assertEquals(19, $measurement['unit_id']);
        }
    }
    public function testGetMeasurementsInReverseChronologicalOrder(){
        $this->postOneEmotionTestMeasurement();
        $this->getMeasurements([
            'sort'         => '-startTimeEpoch',
            'variableName' => 'Nervousness',
            'doNotProcess' => true,
            'offset' => 0,
            'limit' => 50
        ]);
    }
    public function testGetMeasurementsByDescendingStartAt(){
        $this->postOneEmotionTestMeasurement();
        $this->getMeasurements([
            'sort'         => '-startAt',
            'variableName' => 'Nervousness',
            'doNotProcess' => true,
            'offset' => 0,
            'limit' => 50
        ]);
    }
    public function testGetDailyMeasurements(){
	    Measurement::truncate();
	    UserVariable::truncate();
        $userId = 1;
        $this->setAuthenticatedUser($userId);
        $time = 1348072640;
        $day = 86400;
        $measurementATime = $time;
        $measurementAHumanTime = date('Y-m-d H:i:s', $measurementATime);
        $measurementBTime = $time + 35 * $day;
        $measurementBHumanTime = date('Y-m-d H:i:s', $measurementBTime);
        $measurementCTime = $time + $day;
        $measurementCHumanTime = date('Y-m-d H:i:s', $measurementCTime);
        $measurementItems[] = new QMMeasurement($measurementATime, 1);
        $measurementItems[] = new QMMeasurement($measurementBTime, 1);
        $measurementItems[] = new QMMeasurement($measurementCTime, 1);
        $sets[] = new MeasurementSet('variableA', $measurementItems, 'serving',
            'Foods', BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
            'SUM', []);
        self::saveMeasurementSets($userId, $sets);
        $variableA = QMUserVariable::getByNameOrId($userId, 'variableA');
        $this->assertEquals(0, $variableA->fillingValue);
        DBUnitTestCase::setUserVariablesWithZeroStatusToWaiting();
        while ($v = QMUserVariable::getWaitingUserVariableToUpdate()) {
            $v->analyzeFully(__FUNCTION__);
        }
        $parameters = [
            'userId' => $userId,
            'variableName' => 'variableA',
            'startTime' => $measurementATime - 30 * $day,
            'endTime' => $measurementATime + 60 * $day,
            'unitAbbreviatedName' => 'serving'
        ];
        /** @var QMMeasurement[] $measurements */
        $measurements = $this->getApiV3('/measurements/daily', $parameters);
        $this->assertCount(91, $measurements);
        $filledMeasurements = $unfilled = [];
        foreach ($measurements as $m) {
            $this->assertGreaterThan(0, $m->startTimeEpoch);
            if ($m->value == 0) {
                $filledMeasurements[] = $m;
            } else {
                $unfilled[] = $m;
            }
        }
        $this->assertCount(88, $filledMeasurements);
        $this->assertCount(3, $unfilled);
        $this->assertQueryCountLessThan(84);
    }
    /**
     * Validates if the date is in the given format
     * Taken from comments of: http://php.net/manual/en/function.checkdate.php
     *
     * @param $date
     * @param string $format
     * @return bool
     */
    public function validateDate($date, $format = 'Y-m-d H:i:s'){
        $d = Carbon::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
    /**
     * @param array $body
     */
    private function deleteAndVerify(array $body): void{
        $this->postApiV3('measurements/delete', $body, 204);
        QMBaseTestCase::setExpectedRequestException(QMException::class);
        $this->postApiV3('measurements/delete', $body, 400);
        $parameters = ['offset' => 0, 'limit' => 60, 'sort' => '-startTime'];
        $responseObject = $this->getMeasurements($parameters, 1);
    }
    /**
     * @param string|array $postData
     * @return array
     */
    private function postMeasurementAndCheckVariable($postData): array{
        $response = $this->slimPost('api/v3/measurements', $postData);
        $this->checkPostMeasurementsResponse($response);
        $dbh = Writable::pdo();
        //UserVariable::updateOrCreateAllUserVariables();
        $sqlCheckVariables = "SELECT * FROM variables WHERE name = 'Back Pain'";
        $variables = $dbh->query($sqlCheckVariables)->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(1, $variables);
        $variable = $variables[0];
        return $variable;
    }
    /**
     * @param array $row
     */
    private function checkBackPainCommonVariableRow(array $row): void {
        $this->assertEquals('Back Pain', $row['name']);
        $this->assertEquals('10', $row['variable_category_id']);
        $this->assertEquals('10', $row['default_unit_id']);
        //$this->assertEquals('MEAN', $row['combination_operation']);
        //$this->assertEquals(null , $row['duration_of_action']);
        $this->assertEquals(1, $row['is_public']);
    }
    /**
     * @param array $measurementSet
     * @param string $variableName
     * @return array
     */
    private function postMeasurementAndCheckLastValue(array $measurementSet, string $variableName): array{
        $submittedUserUnit = QMUnit::getByNameOrId($measurementSet["unitAbbreviatedName"]);
        $body = $this->postAndGetDecodedBody('/api/v1/measurements', $measurementSet);
        $userVariableFromApi = $body->data->userVariables[0];
        $row = UserVariable::find($userVariableFromApi->userVariableId);
        $userVariableObject = QMUserVariable::getByNameOrId(1, $variableName);
        $commonUnit = $userVariableObject->getCommonUnit();
        if($measurementSet['unitAbbreviatedName'] !== $userVariableFromApi->unitAbbreviatedName){
            throw new \LogicException("userVariableFromApi->unitAbbreviatedName should be $submittedUserUnit ".
                "but is $userVariableFromApi->unitAbbreviatedName");
        }
        $this->assertEquals(100, $userVariableObject->getLastValueInCommonUnit());
        return [$userVariableFromApi, $userVariableObject];
    }
    private function checkNervousnessAndMeasurements() {
        $variable = Variable::findByName('Nervousness');
        $this->assertEquals('Nervousness', $variable->name);
        $this->assertEquals(1, $variable->variable_category_id);
        $this->assertEquals(10, $variable->default_unit_id);
        $this->assertEquals(1, $variable->is_public);
        $measurements = Measurement::whereVariableId($variable->id)->get();
        $this->assertCount(1, $measurements);
    }
}
