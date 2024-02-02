<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpDeprecationInspection */
/** @noinspection OnlyWritesOnParameterInspection */
namespace Tests\UnitTests\Variables;
use App\Exceptions\BadRequestException;
use App\Models\Variable;
use App\Properties\Variable\VariableNameProperty;
use App\Storage\DB\Writable;

class VariablesSearchTest extends \Tests\SlimTests\SlimTestCase {
    public function testV1SearchVariablesWithLimit(){
        $this->setAuthenticatedUser(1);
        $searchTerm = 'Fat';
        $variables =
            $this->searchVariables($searchTerm, ['limit' => 1, 'includePublic' => true]);
        $this->assertCount(1, $variables);
        $this->assertContains($searchTerm, $variables[0]->name);
        foreach($variables as $variable){

            $this->checkSharedQmVariableObjectStructureV3($variable, $searchTerm);
        }
    }
    public function testV1SearchVariablesWithApostrophe(){
        $this->setAuthenticatedUser(1);
        $searchTerm = 'McDonald%27s%20hotcake';
        $variables = $this->searchVariables($searchTerm,
            ['manualTracking' => true, 'includePublic' => true, 'variableCategoryName' => 'Foods']);
        foreach($variables as $variable){
            $this->checkSharedQmVariableObjectStructureV3($variable, $searchTerm);
        }
    }
    public function testMakeSureV1SearchVariablesIncludePublicReturnsPrivateExactMatch(){
        $this->setAuthenticatedUser(1);
        $searchTerm = 'Caloric Intake';
        $variables = $this->searchVariables($searchTerm, ['includePublic' => true]);
        $names = array_column($variables, 'name');
        $this->assertArrayEquals(array (
            0 => 'Caloric Intake',
            1 => 'Net Caloric Intake',
        ), $names, "Variables like $searchTerm");
        $this->assertName($searchTerm, $variables[0]);
        foreach($variables as $variable){
            $this->checkSharedQmVariableObjectStructureV3($variable, $searchTerm);
        }
    }
    public function testMakeSureV1SearchVariablesWithPercentSignsIncludePublicReturnsPrivateExactMatch(){
        $this->setAuthenticatedUser(1);
        $searchTerm = '%Caloric Intake%';
        $response =
            $this->getApiV3('variables/search/'.urlencode($searchTerm), ['includePublic' => true], false);
        return $response;
    }
    public function testMakeSureV1SearchVariablesWithAstrixSignsIncludePublicReturnsPrivateExactMatch(){
        $this->setAuthenticatedUser(1);
        $searchTerm = '**Caloric Intake**';
        $variables = $this->searchVariables($searchTerm, ['includePublic' => true]);
        $this->assertCount(2, $variables);
        $this->assertName('Caloric Intake', $variables[0]);
        foreach($variables as $variable){
            $this->checkSharedQmVariableObjectStructureV3($variable, $searchTerm);
        }
    }
    public function testMakeSureV1SearchVariablesIncludePublicReturnsPrivateExactMatchAndAnythingCategory(){
        $this->setAuthenticatedUser(1);
        $searchTerm = 'Caloric Intake';
        $variables = $this->searchVariables($searchTerm,
            ['includePublic' => true, 'variableCategoryName' => 'Anything']);
        $this->assertCount(2, $variables);
        $this->assertName($searchTerm, $variables[0]);
        foreach($variables as $variable){
            $this->checkSharedQmVariableObjectStructureV3($variable, $searchTerm);
        }
    }
    public function testV0SearchVariablesWithLimit(){
        $this->setAuthenticatedUser(1);
        $searchTerm = 'Fat';
        $variables =
            $this->getAndDecodeBody('/api/variables/search/'.$searchTerm, ['limit' => 1, 'includePublic' => true]);
        $this->assertCount(1, $variables);
        $this->assertContains($searchTerm, $variables[0]->name);
        foreach($variables as $variable){
            $this->checkSharedQmVariableObjectStructureV3($variable, $searchTerm);
        }
    }
    public function testExactMatchFirstInV0SearchVariablesIncludePublic(){
        $this->setAuthenticatedUser(1);
        $searchTerm = 'Fat';
        $variables = $this->getAndDecodeBody('/api/variables/search/'.$searchTerm, ['includePublic' => true]);
        $this->assertGreaterThan(1, count($variables));
        $this->assertContains($searchTerm, $variables[0]->name);
        foreach($variables as $variable){
            $this->checkSharedQmVariableObjectStructureV3($variable, $searchTerm);
        }
    }
    public function testPublicSearchVariablesWithCategory(){
        $this->setAuthenticatedUser(1);
        $searchTerm = 'Add';
        $cat = 'Treatments';
        $vars = Variable::search($searchTerm);
        $names = Variable::search($searchTerm)->pluck('name')->all();
	    $expected = [
		    0 => 'Vitamin B-12, Added',
		    1 => 'Vitamin E, Added',
		    2 => 'Adderall Xr',
	    ];
		sort($expected);
		sort($names);
	    $this->assertArrayEquals($expected, $names);
        $numOfUsers = $vars->pluck(Variable::FIELD_NUMBER_OF_USER_VARIABLES);
        $this->getAndCheckAdderall('/api/v1/public/variables/search/'.$searchTerm, ['category' => $cat]);
    }
    /**
     * @param string $apiUrl
     * @param array $parameters
     */
    private function getAndCheckAdderall(string $apiUrl, array $parameters) {
        $query = 'Adderall Xr';
        $names = Variable::search($query)->pluck('name')->all();
        $public = Variable::search($query)->pluck(Variable::FIELD_IS_PUBLIC)->all();
        $this->assertArrayEquals(array (
            0 => $query,
        ), $names);
        $variables = $this->getAndDecodeBody($apiUrl, $parameters);
        $this->assertGreaterThan(0, count($variables));
        $variableNames = [];
        foreach($variables as $variable){
            $this->checkCommonVariable($variable);
            $variableNames[] = $variable->name;
        }
        $this->assertContains($query, $variableNames);
    }
    public function testVariablesSearchWithNullCategory(){
        $this->setAuthenticatedUser(1);
        $searchTerm = 'Add';
        $variables = Variable::search($searchTerm);
        $this->assertCount(3, $variables);
        $this->getAndCheckAdderall('/api/v1/public/variables/search/'.$searchTerm, ['category' => 'Null']);
    }
    public function testSearchUserVariablesWithWrongCategory(){
        $searchTerm = 'Body Mass Index Or BMI';
        $variables = $this->searchVariables($searchTerm, ['category' => 'Emotions']);
        $this->assertCount(0, $variables);
    }
    public function testSearchUserVariablesIncludePublicWrongCategory(){
        $this->skipTest("deprecated");
        $this->setAuthenticatedUser(1);
        $searchTerm = 'Add';
        $this->getAndCheckAdderall('/api/v1/variables/search/'.$searchTerm,
            ['includePublic' => 'true', 'categoryName' => 'Emotions']);
    }
    public function testPublicSearchVariablesWithWrongCategory(){
        $this->setAuthenticatedUser(1);
        $searchTerm = 'Add';
        $response = $this->slimGet($apiUrl = '/api/v1/public/variables/search/'.$searchTerm, ['category' => 'Activity']);
        $variables = json_decode($response->getBody(), false);
        $this->assertIsArray( $variables);
        $this->assertCount(0, $variables);
        $variableNames = [];
        foreach($variables as $variable){
            $this->checkCommonVariable($variable);
            $variableNames[] = $variable->name;
        }
        $this->assertNotContains('Adderall Xr', $variableNames);
    }
    public function testSearchOutcomeVariablesWithFallbackToAggregatedCorrelations(){
        Writable::db()
            ->table('user_variables')
            ->where('variable_id', 1398)
            ->update(['number_of_user_variable_relationships_as_effect' => 0]);
        Writable::db()
            ->table('variables')
            ->where('id', 1398)
            ->update(['number_of_global_variable_relationships_as_effect' => 2]);
        $this->setAuthenticatedUser(1);
        $searchTerm = 'Mood Overall';
        $variables = $this->searchVariables($searchTerm,
            [
                'includePublic'                    => true,
                'fallbackToAggregatedCorrelations' => true,
                'numberOfUserVariableRelationshipsAsEffect' => '(gt)1',
                Variable::FIELD_OUTCOME      => "true"
            ]);
        $this->assertCount(1, $variables);
        foreach($variables as $variable){
            $variable = json_decode(json_encode($variable));
            $this->assertInstanceOf('stdClass', $variable);
            $this->checkSharedQmVariableObjectStructureV3($variable);
            $this->assertNotEquals('positive', $variable->description);
            $this->assertEquals('positive', $variable->valence);
            $this->assertEquals('Overall Mood', $variable->name);
        }
    }
    public function testSearchVariablesWithBadLimitParameter(){
        $this->setAuthenticatedUser(1);
        $searchTerm = 'App Usage';
		self::setExpectedRequestException(BadRequestException::class);
        $this->slimGet('/api/v1/variables/search/'.$searchTerm,
            ['limit' => 'broken limit'],
            400,
            'Limit must be numeric');
    }
    public function testSearchVariablesWithBadOffsetParameter(){
        $this->setAuthenticatedUser(1);
        $searchTerm = 'App Usage';
        $this->slimGet('/api/v1/variables/search/'.$searchTerm,
            ['limit' => 5, 'offset' => 'broken offset'],
            400,
            '"error":"Offset must be numeric"');
    }
    public function testGetVariablesWithSourceParam(){
        $this->skipTest("deprecated");
        $this->setAuthenticatedUser(1);
        $variables = $this->getVariablesV3(['source' => 'Med Helper']);
        foreach($variables as $v){
            $this->checkSharedQmVariableObjectStructureV3($v, null, 1);
            //$this->assertEquals('Med Helper', $v->sources);
        }
        $this->assertQueryCountLessThan(6);
    }

    /**
     * @param string $searchTerm
     * @param $variable
     * @return void
     */
    private function assertName(string $searchTerm, $variable): void
    {
        $name = VariableNameProperty::pluck($variable);
        $this->assertEquals($searchTerm, $name);
    }
}
