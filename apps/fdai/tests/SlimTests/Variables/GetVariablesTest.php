<?php /** @noinspection PhpMissingReturnTypeInspection */
/** @noinspection SuspiciousBinaryOperationInspection */
/** @noinspection PhpDeprecationInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
namespace Tests\SlimTests\Variables;
use App\Exceptions\BadRequestException;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Slim\Middleware\QMAuth;
use App\Variables\CommonVariables\EnvironmentCommonVariables\AverageDailyOutdoorTemperatureCommonVariable;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use Tests\UnitTests\Products\AmazonTest;
use Tests\QMBaseTestCase;

class GetVariablesTest extends \Tests\SlimTests\SlimTestCase {
    public function testGetPublicVariablesWhenUserIsNotAuthenticated(){
        $publicVariables = $this->getAPIV1PublicVariables();
        $this->assertCount(100, $publicVariables);
    }
    public function testGetPublicVariablesByVariableName(){
        $this->getPublicVariableByName("Overall Mood");
    }
    /**
     * @param $name
     * @return mixed
     */
    private function getPublicVariableByName(string $name){
        $publicVariables = $this->getAPIV1PublicVariables(['variableName' => $name]);
        return $publicVariables[0];
    }
    public function testTitleCase(){
        $title = "Overall Mood";
        $lower = "overall mood";
        $qb = QMCommonVariable::writable()->where(Variable::FIELD_ID, '=', 1398);
        $qb->update([Variable::FIELD_NAME => $lower]);
        $publicVariable = $this->getPublicVariableByName($lower);
        $this->assertEquals($lower, $publicVariable->name);
        $this->assertEquals($title, $publicVariable->displayName);
        $userVariable = $this->getUserVariableByName($lower);
        $this->assertEquals($lower, $userVariable->name);
        $this->assertEquals($title, $userVariable->displayName);
        $qb->update([Variable::FIELD_NAME => $title]);
    }
	/**
	 * @param string $name
	 * @return QMUserVariable
	 */
    private function getUserVariableByName(string $name){
		if(!QMAuth::getQMUserIfSet()){
			$this->setAuthenticatedUser(1);
		}
        $userVariables = $this->getApiV3('userVariables', ['variableName' => $name]);
        $this->assertCount(1, $userVariables);
        foreach($userVariables as $variable){
            $this->assertInstanceOf('stdClass', $variable);
            $this->checkUserVariableObjectStructureV3($variable);
        }
        return $userVariables[0];
    }
    public function testFallbackToPublicVariablesWhenUserIsNotAuthenticated(){
        $publicVariables = $this->getVariablesV3(['sort' => '-latestTaggedMeasurementTime']);
        $this->assertCount(100, $publicVariables);
        foreach($publicVariables as $variable){
            $this->assertInstanceOf('stdClass', $variable);
            $this->checkCommonVariable($variable);
            $this->assertNotTrue(isset($variable->userId));
        }
	    $this->assertQueryCountLessThan(4);
    }
    public function testGetPublicVariablesWhenUserIsAuthenticated(){
        $this->setAuthenticatedUser(1);
        $variables = $this->getAPIV1PublicVariables([]);
        $this->assertGreaterThan(0, count($variables));
	    $this->assertQueryCountLessThan(6);
    }
	/**
	 * @param array $params
	 * @return QMCommonVariable[]
	 * @covers GetCommonVariableController
	 */
	protected function getAPIV1PublicVariables(array $params = []): array {
		$publicVariables = $this->getAndDecodeBody('api/v1/public/variables', $params);
		$this->assertIsArray( $publicVariables);
		foreach($publicVariables as $variable){
			$this->assertInstanceOf('stdClass', $variable);
			$this->checkCommonVariable($variable);
		}
		return $publicVariables;
	}
    public function testGetVariableInSearchFormat(){
        $this->setAuthenticatedUser(1);
        $variables = $this->getVariablesV3(['concise' => true]);
        $this->assertGreaterThan(0, count($variables));
        foreach($variables as $variable){
            $this->assertNotTrue(isset($variable->unit));
            $this->assertNotTrue(isset($variable->variableCategory));
        }
	    $this->assertQueryCountLessThan(7);
    }
    public function testGetVariables(){
        $this->setAuthenticatedUser(1);
        $variables = $this->getVariablesV3([]);
        $this->assertGreaterThan(0, count($variables));
        foreach($variables as $variable){
            $this->checkSharedQmVariableObjectStructureV3($variable);
        }
        $limit = 2;
        //limit offset test
        $variables = $this->getVariablesV3(['limit' => $limit, 'offset' => 1]);
        $this->assertGreaterThan(0, count($variables));
        // This doesn't work randomly $this->assertCount($limit, $variables);
        foreach($variables as $variable){
            $this->checkSharedQmVariableObjectStructureV3($variable);
        }
        //all records test
        $variables = $this->getVariablesV3(['limit' => 200]);
        $this->assertGreaterThan(101, count($variables));
        foreach($variables as $variable){
            $this->checkSharedQmVariableObjectStructureV3($variable);
        }
	    $this->assertQueryCountLessThan(17);
    }
    public function testGetVariablesByVariableName(){
        $this->setAuthenticatedUser(1);
        $variableName = 'Overall Mood';
        $variables = $this->getVariablesV3(['name' => $variableName]);
        $this->assertInstanceOf('stdClass', $variables[0]);
        $this->checkSharedQmVariableObjectStructureV3($variables[0]);
	    $this->assertQueryCountLessThan(5);
    }
    public function testGetVariableById(){
        $this->setAuthenticatedUser(1);
        $expectedVariableName = 'Overall Mood';
        $variables = $this->getVariablesV3(['id' => 1398]);
        $this->assertCount(1, $variables);
        $this->assertInstanceOf('stdClass', $variables[0]);
        $this->assertEquals($expectedVariableName, $variables[0]->name);
        $this->checkSharedQmVariableObjectStructureV3($variables[0]);
	    $this->assertQueryCountLessThan(8);
    }
    public function testGetVariablesByComplicatedNameParameter(){
        $this->setAuthenticatedUser(1);
        $name = AverageDailyOutdoorTemperatureCommonVariable::NAME;
        $this->postApiV3('userVariables', [[
            'variable'            => $name,
            'minimumAllowedValue' => -50,
            'maximumAllowedValue' => 'Infinity',
            'unit'                => 'F'
        ]]);
        $variables = $this->getVariablesV3(['name' => $name]);
        $this->assertCount(1, $variables);
        $this->checkSharedQmVariableObjectStructureV3($variables[0]);
	    $this->assertQueryCountLessThan(8);
    }
    public function testGetUserVariablesByVariableName(){
        $this->setAuthenticatedUser(1);
        $variableName = 'Overall Mood';
        /** @var QMUserVariable $userVariables */
        $userVariables = $this->getAndCheckUserVariables(['name' => $variableName]);
        $this->assertEquals($variableName, $userVariables[0]->name);
	    $this->assertQueryCountLessThan(6);
    }
    public function testGetUserVariables(){
        $this->setAuthenticatedUser(1);
        $userVariables = $this->getAndCheckUserVariables();
        $this->assertCount(2, $userVariables);
        //limit offset test
        $userVariables = $this->getAndCheckUserVariables(['limit' => 2, 'offset' => 1]);
        $this->assertCount(1, $userVariables);
        //all records test
        QMBaseTestCase::setExpectedRequestException(BadRequestException::class);
        $this->getAndDecodeBody('/api/v4/variables', ['limit' => 0], 400);
		$this->assertQueryCountLessThan(10);
    }
    public function testUpcCheck(){
        if(time() < strtotime(AmazonTest::DISABLED_UNTIL)){
            $this->skipTest('Waiting for Amazon to approve use of US product API at https://affiliate-program.amazon.com/assoc_credentials/home');
            return;
        }
        $this->setAuthenticatedUser(1);
        $upc = "037000947714";
        $variables = $this->getAndDecodeBody('/api/v3/variables', ['upc' => $upc, 'includePublic' => true]);
        $this->assertCount(2, $variables);
        $badUpc = 'VW9C086D.835852901';
        $this->expectQMException();
        $variables = $this->getAndDecodeBody('/api/v3/variables', ['upc' => $badUpc, 'includePublic' => true]);
        $this->assertCount(0, $variables);
        $this->expectQMException();
        QMUserVariable::writable()
            ->where(UserVariable::FIELD_VARIABLE_ID, 1398)
            ->update([UserVariable::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT => null]);
        $this->postApiV3('v3/userVariables', ['variableId' => 1398, 'upc' => $badUpc]);
        $variables = $this->getAndDecodeBody('/api/v3/variables', ['upc' => $badUpc, 'includePublic' => true]);
        $this->assertCount(1, $variables);
	    $this->assertQueryCountLessThan(10);
    }
}
