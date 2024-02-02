<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpDeprecationInspection */
/** @noinspection OnlyWritesOnParameterInspection */
namespace Tests\SlimTests\Variables;
use App\Models\Variable;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\Writable;
class VariablesSearchTest extends \Tests\SlimTests\SlimTestCase {
    /**
     * List of fixture files
     * @var string[]
     */
    protected $fixtureFiles = [
        'user_variables' => 'common/user_variables_search.xml',
    ];
    /**
     * @param $apiUrl
     * @param $parameters
     */
    private function getAndCheckAdderall(string $apiUrl, array $parameters){
        $variables = $this->getAndDecodeBody($apiUrl, $parameters);
        $this->assertGreaterThan(0, count($variables));
        $variableNames = [];
        foreach($variables as $variable){
            $this->checkCommonVariable($variable);
            $variableNames[] = $variable->name;
        }
        $this->assertContains('Adderall Xr', $variableNames);
    }
    public function testSearchUserVariablesIncludePublic(){
        $this->setAuthenticatedUser(1);
        $searchTerm = 'Add';
        $this->getAndCheckAdderall('/api/v1/variables/search/'.$searchTerm, ['includePublic' => 'true']);
    }
    public function testSearchVariablesWithMixedUpWordOrder(){
        $this->setAuthenticatedUser(1);
        $searchTerm = 'Mood Overall';
        $variables = $this->searchVariables($searchTerm, ['includePublic' => true]);
        $this->assertCount(1, $variables);
        foreach($variables as $variable){
            $this->assertInstanceOf('stdClass', $variable);
            $this->checkSharedQmVariableObjectStructureV3($variable);
            $this->assertNotEquals('positive', $variable->description);
	        $this->assertEquals('positive', $variable->valence);
            $this->assertEquals('Overall Mood', $variable->name);
        }
    }
    public function testGetVariablesByVariableName(){
        $this->setAuthenticatedUser(1);
        // test category param
        $variables = $this->getVariablesV3(['category' => 'Treatments']);
        foreach($variables as $variable){
            $this->assertEquals('Treatments', $variable->variableCategoryName);
        }
        // test name param
        $variables = $this->getVariablesV3(['name' => 'Overall Mood']);
        foreach($variables as $variable){
            $this->assertEquals('Overall Mood', $variable->name);
        }
        // test source param
        $variables = $this->getVariablesV3(['sourceName' => 'Med Helper']);
        foreach($variables as $variable){
            $this->checkSharedQmVariableObjectStructureV3($variable, $searchTerm = null, 1);
            //$this->assertEquals('Med Helper', $variable->sources);
        }
        $this->assertQueryCountLessThan(10);
    }
    public function testSearchOutcomeVariablesWithNumberOfUserVariableRelationshipsFilter(){
        Writable::db()
            ->table('user_variables')
            ->where('variable_id', 1398)
            ->update(['number_of_user_variable_relationships_as_effect' => 2]);
        $this->setAuthenticatedUser(1);
        $searchTerm = 'Mood Overall';
        $variables = $this->searchVariables($searchTerm,
            [
                'numberOfUserVariableRelationshipsAsEffect' => '(gt)1',
                Variable::FIELD_OUTCOME      => "true"
            ]);
        $this->assertCount(1, $variables);
        foreach($variables as $variable){
            $this->assertInstanceOf('stdClass', $variable);
            $this->checkSharedQmVariableObjectStructureV3($variable);
            $this->assertEquals('positive', $variable->valence);
            $this->assertNotEquals('positive', $variable->description);
            $this->assertEquals('Overall Mood', $variable->name);
        }
    }
    public function testSearchPrivateOutcomeVariablesWithAggregatedCorrelationsFilter(){
        Writable::db()
            ->table('user_variables')
            ->where('variable_id', 1398)
            ->update(['number_of_user_variable_relationships_as_effect' => 2]);
        Writable::db()->table('variables')->where('id', 1398)->update([
            'number_of_global_variable_relationships_as_effect' => 2,
            Variable::FIELD_IS_PUBLIC                                     => 0
        ]);
        Writable::db()->table('variable_categories')->where('name', 'Emotions')->update([
            Variable::FIELD_IS_PUBLIC => 0
        ]);
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
            $this->assertInstanceOf('stdClass', $variable);
            $this->checkSharedQmVariableObjectStructureV3($variable);
            $this->assertNotEquals('positive', $variable->description);
            $this->assertEquals('positive', $variable->valence);
            $this->assertEquals('Overall Mood', $variable->name);
        }
        Writable::db()->table('variable_categories')->where('name', 'Emotions')->update([
            Variable::FIELD_IS_PUBLIC => 1
        ]);
        Writable::db()->table('variables')->where('id', 1398)->update([
            Variable::FIELD_IS_PUBLIC => 1
        ]);
    }
    public function testSearchUserVariablesWithCategory(){
        $this->setAuthenticatedUser(1);
        $searchTerm = 'Body Mass Index Or BMI';
        $variables = $this->searchVariables($searchTerm, ['category' => 'Physique']);
        $this->assertCount(1, $variables);
        $variableNames = [];
        foreach($variables as $variable){
            $variableNames[] = $variable->name;
            $this->checkSharedQmVariableObjectStructureV3($variable, $searchTerm);
        }
        $this->assertArrayEquals(['Body Mass Index Or BMI'], $variableNames);
    }
    public function testSearchVariables(){
        $this->setAuthenticatedUser(1);
        $searchTerm = 'Body Mass Index Or BMI';
		$row = Variable::whereName($searchTerm)->first();
		$this->assertNotNull($row);
		$this->assertTrue((bool)$row->is_public);
        $variables = $this->searchVariables($searchTerm, [QMRequest::PARAM_INCLUDE_PUBLIC => true]);
        $this->assertGreaterThan(0, count($variables));
        $variableNames = [];
        foreach($variables as $variable){
            $this->checkSharedQmVariableObjectStructureV3($variable);
            $variableNames[] = $variable->name;
        }
        $this->assertArrayEquals(['Body Mass Index Or BMI'], $variableNames);
    }
}
