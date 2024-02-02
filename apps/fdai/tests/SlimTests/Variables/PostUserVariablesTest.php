<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Variables;
use App\Exceptions\BadRequestException;
use App\Exceptions\QMException;
use App\Exceptions\VariableCategoryNotFoundException;
use App\Files\TestArtifacts\TestQueryLogFile;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseVariableIdProperty;
use App\Properties\UserVariable\UserVariableVariableIdProperty;
use App\Slim\Controller\Variable\PostVariableController;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Units\MinutesUnit;
use App\Units\OneToFiveRatingUnit;
use App\Units\SecondsUnit;
use App\Variables\CommonVariables\SoftwareCommonVariables\AppUsageCommonVariable;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariableCategory;
use PDO;
use Tests\DBUnitTestCase;
use Tests\QMBaseTestCase;
class PostUserVariablesTest extends \Tests\SlimTests\SlimTestCase {
    protected function setUp(): void{
        parent::setUp();
        $this->fixInvalidUserVariables();
		TestQueryLogFile::flushTestQueryLog();
    }
    private function fixInvalidUserVariables(){
        QMUserVariable::writable()
            ->whereNull(UserVariable::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN)
            ->update([UserVariable::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT => null]);
    }
    public function testDeleteAllMeasurementsForAVariable(){
        $variable = $this->createTestSymptomRatingMeasurement();
        $dbh = Writable::pdo();
        $sqlCheckMeasurements = 'SELECT user_id, source_name, start_time as startTime, value, unit_id, latitude, longitude,
        location FROM measurements WHERE variable_id = '.$variable['id'].' AND deleted_at IS NULL';
        $measurements = $dbh->query($sqlCheckMeasurements)->fetchAll(PDO::FETCH_ASSOC);
        $this->assertGreaterThan(0, count($measurements));
        $this->slimPost('api/v3/userVariables/delete', ['variableId' => $variable['id'],]);
        $sqlCheckMeasurements = 'SELECT user_id, source_name, start_time as startTime, value, unit_id, latitude, longitude,
        location FROM measurements WHERE variable_id = '.$variable['id'].' AND deleted_at IS NULL';
        $measurements = $dbh->query($sqlCheckMeasurements)->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(0, $measurements);
        $this->assertQueryCountLessThan(35);
    }
    public function testResetUserVariable(){
        $newlyCreatedAdderallUserVariable = QMUserVariable::getOrCreateById(1, 1256);
        $this->assertEquals(1, $newlyCreatedAdderallUserVariable->userId);
        $this->assertEquals(1256, $newlyCreatedAdderallUserVariable->variableId);
        $adderallUserVariable = QMUserVariable::getByNameOrId(1, 1256);
        $this->assertEquals(1, $adderallUserVariable->userId);
        $this->assertEquals(1256, $adderallUserVariable->variableId);
        $rowBeforeUpdate = UserVariable::whereVariableId(1256)->first();
        $this->assertEquals(1, $rowBeforeUpdate->user_id);
        $variableName = 'Adderall Xr';
        $maxInUserUnitGrams = 12;
        $userUnitName = 'g';
        $this->postVariableSettings([[
            'variable'            => $variableName,
            'minimumAllowedValue' => '-Infinity',
            //'minimumAllowedValue' => 10,
            //'maximumAllowedValue' => 'Infinity',
            'maximumAllowedValue' => $maxInUserUnitGrams,
            'unit'                => $userUnitName,
            'fillingValue'        => 11
        ]]);
        /** @var UserVariable $uv */
        $uv = UserVariable::whereVariableId(1256)->first();
        $val = $uv->filling_value;
        $this->assertNotNull($val, "Filling value did not get saved!");
        $this->assertEquals(0.0, $uv->minimum_allowed_value);
        $this->assertEquals($maxInUserUnitGrams * 1000,
            $uv->maximum_allowed_value,
            "$maxInUserUnitGrams g should be stored in common unit mg");
        Memory::resetClearOrDeleteAll();
        $updatedVariable = QMUserVariable::getByNameOrId(1, 1256);
        $this->assertEquals(11, $updatedVariable->getFillingValueInUserUnit());
        $variables = $this->getUserVariablesRequest(['name' => $variableName]);
        $this->assertCount(1, $variables);
        $variable = $variables[0];
        $this->assertEquals(11, $variable->fillingValueInUserUnit);
        $response = $this->postAndGetDecodedBody('/api/v1/userVariables/reset', ['variableId' => $variable->variableId]);
        $this->assertEquals($adderallUserVariable->variableId, $response->data->userVariable->id,
            "Need to return variable id instead of user variable id for backward compatibility");
        // Hack to deal with User 1 being an admin
        QMCommonVariable::writable()->update([Variable::FIELD_MINIMUM_ALLOWED_VALUE => null]);
        QMCommonVariable::writable()->update([Variable::FIELD_FILLING_VALUE => null]);
        $variables = $this->getUserVariablesRequest(['name' => $variableName]);
        $variable = $variables[0];
        $this->assertEquals(null, $variable->minimumAllowedValue);
        $this->assertEquals(0, $variable->fillingValue);
        $this->assertEquals($variable->id, $variable->variableId,
            "Need to return variable id instead of user variable id for backward compatibility");
    }
    public function testPostUserVariables(){
        $this->setAuthenticatedUser(1);
        $minInUserUnit = 10; // Minutes
        $duration = 864000;
        $variableSettings = [
            [
                'variable'            => 'App Usage',
                'minimumAllowedValue' => $minInUserUnit,
                'maximumAllowedValue' => 'Infinity',
                'unit'                => 'min',
                'duration_of_action'                => $duration,
            ]
        ];
        $variableId = UserVariableVariableIdProperty::pluckOrDefault($variableSettings[0]);
        if(!$variableId){le('!$variableId');}
        $v = Variable::findByData($variableSettings[0]);
        if(!$v){le('!$v');}
        $uv = $this->postVariableSettingsAndCheckRows($variableSettings);
        $commonUnit = $v->getCommonUnit();
        $this->assertEquals(SecondsUnit::NAME, $commonUnit->name);
        $userUnit = $uv->getUserUnit();
        $this->assertEquals(MinutesUnit::NAME, $userUnit->name);
        $inCommonUnit = $uv->toCommonUnit($minInUserUnit);
        $this->assertEquals($inCommonUnit, $uv->minimum_allowed_value);
        $this->assertEquals($minInUserUnit, $uv->getAttributeInUserUnit(UserVariable::FIELD_MINIMUM_ALLOWED_VALUE));
        $this->assertQueryCountLessThan(8);
        $this->assertEquals($duration, $uv->duration_of_action);
    }
    /**
     * @param array $variableSettings
     * @return UserVariable
     */
    protected function postVariableSettingsAndCheckRows(array $variableSettings): UserVariable {
        $response = $this->slimPost('api/v3/userVariables', $variableSettings);
        $this->assertEquals(201, $response->getStatus(), DBUnitTestCase::getErrorMessageFromResponse($response));
        $uv = UserVariable::whereUserId(1)
            ->where('variable_id', AppUsageCommonVariable::ID)
            ->first();
        if(!$uv){
            $uv = UserVariable::whereUserId(1)
                ->where('variable_id', AppUsageCommonVariable::ID)
                ->first();
        }
        $this->assertNull($uv->getRawAttribute(UserVariable::FIELD_ONSET_DELAY));
        $this->assertNull($uv->getRawAttribute(UserVariable::FIELD_MAXIMUM_ALLOWED_VALUE));
        $this->assertContains(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, $uv->client_id);
        return $uv;
    }
    public function testPostUserVariablesWithNegativeInfinity(){
        $this->setAuthenticatedUser(1);
        $uv = $this->postVariableSettingsAndCheckRows([
            [
                'variable'            => 'App Usage',
                'minimumAllowedValue' => '-Infinity',
                'maximumAllowedValue' => 'Infinity',
                'unit'                => 'min',
                'alias'               => 'testAlias'
            ]
        ]);
        $this->assertNull($uv->getRawAttribute(UserVariable::FIELD_MINIMUM_ALLOWED_VALUE));
        $this->assertEquals('testAlias', $uv->alias);
    }
    public function testPostExperimentStartEndTimes(){
        $this->setAuthenticatedUser(1);
        $start = '2005-08-15 15:52:01';
        $end = '2006-08-15 15:52:01';
        $variableSettings = [[
            'variable'            => 'App Usage',
            'experimentStartTime' => $start,
            'experimentEndTime'   => $end,
        ]];
        $variableName = 'App Usage';
        $variables = $this->postAndGetUserVariables($variableSettings, $variableName);
        $this->assertEquals($start, $variables[0]->experimentStartTime);
        $this->assertEquals($end, $variables[0]->experimentEndTime);
    }
    /**
     * @param array|string $postData
     * @param string $variableName
     * @return mixed
     */
    private function postAndGetUserVariables($postData, string $variableName = null){
        if(!is_string($postData)){
			if($variableName){
				$postData[0]['variableName'] = $variableName;
			}
            $postData = json_encode($postData);
        }
        $response = $this->slimPost('api/v3/userVariables', $postData, false, 201);
	    $userVariables = $this->getUserVariablesRequest(['name' => $variableName]);
        $this->assertGreaterThan(0, count($userVariables));
        return $userVariables;
    }
    public function testPostNullExperimentStartEndTimes(){
        $this->setAuthenticatedUser(1);
        $variableName = 'App Usage';
        $expectedExperimentStartTime = null;
        $expectedExperimentEndTime = null;
        $postData =
            '{"variableName":"'.
            $variableName.
            '","durationOfAction":18000,"fillingValue":0,"maximumAllowedValue":100,'.
            '"onsetDelay":7200,"combinationOperation":"SUM","shareUserMeasurements":false,'.
            '"experimentStartTimeString":null}';
        $variables = $this->postAndGetUserVariables($postData, $variableName);
        $this->assertNull($variables[0]->experimentStartTime);
        $this->assertEquals($variables[0]->experimentEndTime, $expectedExperimentEndTime);
        $this->assertEquals(2, $variables[0]->onsetDelayInHours);
        $this->assertEquals(5, $variables[0]->durationOfActionInHours);
    }
    public function testPostUserVariablesVariableId(){
        $this->setAuthenticatedUser(1);
        $variableSettings =
            [['variableId' => AppUsageCommonVariable::ID, 'minimumAllowedValue' => 11, 'maximumAllowedValue' => 'Infinity'],];
        $postData = json_encode($variableSettings);
        $this->postAndGetUserVariables($postData);
        $uv = UserVariable::whereUserId(1)
            ->where('variable_id', AppUsageCommonVariable::ID)
            ->first();
        if(!$uv){
            $uv = UserVariable::whereUserId(1)
                ->where('variable_id', AppUsageCommonVariable::ID)
                ->first();
        }
        $this->assertNull($uv->getRawAttribute(UserVariable::FIELD_DURATION_OF_ACTION));
        $this->assertNull($uv->getRawAttribute(UserVariable::FIELD_ONSET_DELAY));
        $this->assertNull($uv->getRawAttribute(UserVariable::FIELD_MAXIMUM_ALLOWED_VALUE));
        $this->assertContains(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, $uv->client_id);
        $this->assertEquals(11, $uv->minimum_allowed_value);
    }
    public function testPostVariableUserSettings(){
        $this->setAuthenticatedUser(1);
        $variableSettings = [['variable' => 'App Usage', 'minimumValue' => 0, 'unit' => 'min',],];
        $response = $this->slimPost('api/v3/variableUserSettings', $variableSettings);
        $this->assertEquals(201, $response->getStatus(), DBUnitTestCase::getErrorMessageFromResponse($response));
        $this->assertQueryCountLessThan(5);
    }
    public function testUpdateVariableUserSettings(){
	    $userId = 1;
	    $this->setAuthenticatedUser($userId);
        $expectedAlias = 'App Usage Alias';
        $response = $this->postAndGetDecodedBody('/api/variableUserSettings',
            [
                [
                    'durationOfAction'    => 86400,
                    'fillingValue'        => '1',
                    'maximumAllowedValue' => 'Infinity',
                    'minimumAllowedValue' => '-Infinity',
                    'name'                => 'App Usage',
                    'onsetDelay'          => 0,
                    'unit'                => 'h',
                    //'userId'              => 19061,
                    'variable'            => 'App Usage',
                    'alias'            => $expectedAlias
                ]
            ]);
        /** @var QMUserVariable $dbm */
        $dbm = $response->userVariables[0];
	    /** @var Variable $dbVar */
	    $dbVar = Variable::whereName('App Usage')->first();
		$l = $dbVar->findUserVariable($userId);
	    $this->assertEquals($expectedAlias, $l->alias);
	    $this->assertEquals($expectedAlias, $dbm->alias);
	    //$this->assertEquals($expectedAlias, $dbVar->common_alias);
        $this->assertEquals($expectedAlias, $dbm->alias);
        $this->assertEquals($expectedAlias, $dbm->displayName);
        $this->assertEquals(0, $dbm->onsetDelayInHours);
        $this->assertEquals(24, $dbm->durationOfActionInHours);
        $expectedAlias = 'App Usage UserVariableAlias';
        $response = $this->postAndGetDecodedBody('/api/v1/variableUserSettings',
            [
                'durationOfAction'    => 86400,
                'fillingValue'        => '1',
                'maximumAllowedValue' => 'Infinity',
                'minimumAllowedValue' => '-Infinity',
                'name'                => 'App Usage',
                'onsetDelay'          => 0,
                'unit'                => 'min',
                //'userId'              => 19061,
                'variable'            => 'App Usage',
                'alias'   => $expectedAlias
            ]);
        /** @var QMUserVariable $v */
        $v = $response->userVariables[0];
        $this->assertEquals($expectedAlias, $v->alias);
        $this->assertEquals($expectedAlias, $v->displayName);
        $this->assertEquals(0, $v->onsetDelayInHours);
        $this->assertEquals(24, $v->durationOfActionInHours);
    }
    public function testPostVariableWithUnknownCombinationOperator(){
        $this->setAuthenticatedUser(1);
        $combinationOperation = 'Unknown operator';
        $postData =
            json_encode([
                [
                    'parent'               => '',
                    'name'                 => 'PHPUnitTestVariable',
                    'category'             => 'Cognitive Performance',
                    'unit'                 => 'min',
                    'combinationOperation' => $combinationOperation,
                ]
            ]);
        QMBaseTestCase::setExpectedRequestException(BadRequestException::class);
        $response = $this->slimPost('api/v3/variables', $postData, false, 400);
        $expectedErrorMessage =
            sprintf(PostVariableController::ERROR_INVALID_COMBINATION_OPERATION, $combinationOperation);
        $this->assertResponseIsError(QMException::CODE_BAD_REQUEST, $expectedErrorMessage, $response);
        $this->assertQueryCountLessThan(5);
    }
    public function testPostVariableWithUnknownCategory(){
        $this->setAuthenticatedUser(1);
        $variableCategoryName = 'Unknown Category Name';
        QMBaseTestCase::setExpectedRequestException(VariableCategoryNotFoundException::class);
        $response = $this->slimPost('api/v3/variables', [[
            'parent'               => '',
            'name'                 => 'PHPUnitTestVariable',
            'category'             => $variableCategoryName,
            'unit'                 => 'min',
            'combinationOperation' => 'SUM',
        ]], false, QMException::CODE_NOT_FOUND);
        $variableCategoryNames = QMVariableCategory::getVariableCategoryNames();
        $err = "Variable category \"$variableCategoryName\" doesn't exist. Current variable categories are: ".
            implode(', ', $variableCategoryNames).'. Please send an email to '.
            'help@curedao.org if you want to add a new variable category.';
        $this->assertResponseIsError(QMException::CODE_NOT_FOUND, $err, $response);
    }
    public function testPostVariableWithUnknownUnit(){
        $this->setAuthenticatedUser(1);
        $unitName = 'unknown unit';
        QMBaseTestCase::setExpectedRequestException(BadRequestException::class);
        $response = $this->slimPost('api/v3/variables',
            [
                'parent'               => '',
                'name'                 => 'PHPUnitTestVariable',
                'category'             => 'Cognitive Performance',
                'unit'                 => $unitName,
                'combinationOperation' => 'SUM'
            ],
            false,
            400);
        $this->assertResponseIsError(400, "Could not find unit named unknown unit.  Available units are % Recommended Daily Allowance
	-4 to 4 Rating
	0 to 1 Rating
	0 to 5 Rating
	1 to 10 Rating
	1 to 3 Rating
	1 to 5 Rating
	Applications
	Beats per Minute
	Calories
	Capsules
	Centimeters
	Count
	Decibels
	Degrees Celsius
	Degrees East
	Degrees Fahrenheit
	Degrees North
	Dollars
	Doses
	Drops
	Event
	Feet
	Gigabecquerel
	Grams
	Hectopascal
	Hours
	Inches
	Index
	International Units
	Kilocalories
	Kilograms
	Kilometers
	Liters
	Meters
	Meters per Second
	Micrograms
	Micrograms per decilitre
	Miles
	Miles per Hour
	Millibar
	Milligrams
	Milliliters
	Millimeters
	Millimeters Merc
	Milliseconds
	Minutes
	Ounces
	Parts per Million
	Pascal
	Percent
	Pieces
	Pills
	Pounds
	Puffs
	Quarts
	Seconds
	Serving
	Sprays
	Tablets
	Torr
	Units
	Yes/No
	per Minute", $response);
    }
    public function testPostVariableWithMalformedBody(){
		$this->skipTest("This test is not working");
        $this->setAuthenticatedUser(1);
        $postData = 'this-is+not%a=valid*json';
        QMBaseTestCase::setExpectedRequestException(BadRequestException::class);
        $response = $this->slimPost('api/v3/variables', $postData, 400);
        $this->assertResponseIsError(400, 'This is not valid json: this-is+not%a=valid*json', $response);
    }
    public function testPostVariableWithEmptyBody(){
        $this->setAuthenticatedUser(1);
        $postData = '';
        $this->expectQMException();
        $response = $this->slimPost('api/v3/variables', $postData, false, 400);
        $this->assertResponseIsError(400, 'Expected at least one variable', $response);
    }
    public function testPostVariable(){
	    
        $this->setAuthenticatedUser(1);
        $variables = [
            [
                'parent'               => '',
                'name'                 => 'PHPUnitTestVariable 01a',
                'category'             => 'Emotions',
                'unit'                 => OneToFiveRatingUnit::NAME,
                'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN
            ],
            [
                'parent'               => '',
                'name'                 => 'PHPUnitTestVariable 02b',
                'category'             => 'Emotions',
                'unit'                 => OneToFiveRatingUnit::NAME,
                'combinationOperation' => BaseCombinationOperationProperty::COMBINATION_MEAN
            ],
        ];
        $this->slimPost('api/v3/variables', $variables);
        foreach($variables as $variableData){
            $variableId = BaseVariableIdProperty::fromName($variableData['name']);
            $this->assertNotNull($variableId);
        }
        $variableFromDB = Variable::whereName('PHPUnitTestVariable 01a')->first();
        $this->assertEquals(0, $variableFromDB->is_public, "Why should this be public?");
    }

}
