<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Http\Controllers;
use App\Models\Variable;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use Tests\UnitTestCase;
/**
 * @coversDefaultClass \App\Http\Controllers\VariablesController
 */
class VariablesControllerTest extends UnitTestCase
{
	/**
	 * @covers \App\Http\Controllers\VariablesController
	 */
	public function testGetVariableByName(){
        $this->actingAsUserOne();
        //$this->testPostVariable();
        $getApiUrl = '/api/v2/variables?name=' . OverallMoodCommonVariable::NAME;
        $this->get($getApiUrl);
        $getContent = $this->getTestResponse()->getContent();
        $getResponse = json_decode($getContent, true);
        $this->assertEquals( OverallMoodCommonVariable::NAME, $getResponse['data'][0]['name'], "getResponse()->getContent(: ".
            \App\Logging\QMLog::print_r($getResponse, true));
    }
    public function testGetVariableLikeName(){
        $this->actingAsUserOne();
        $searchTerm = '%mood';
        $builder = Variable::whereNameLike($searchTerm);
        $vars = $builder->get();
        $this->assertCount(2, $vars);
        //$names = Variable::names();
        //$this->assertArrayEquals([], $names->toArray());
        $getLikeApiUrl = '/api/v2/variables?name=' . $searchTerm;
        $this->get($getLikeApiUrl);
        $likeContent = $this->getTestResponse()->getContent();
        $likeResponse = json_decode($likeContent, true);
        $this->assertEquals(OverallMoodCommonVariable::NAME, $likeResponse['data'][0]['name']);
    }
    public function testGetVariableDoubleLikeName(){
        $this->actingAsUserOne();
        $search = 'moo';
        $v = Variable::search('mood')->first();
        $this->assertNotNull($v);
        $doubleLikeApiUrl = '/api/v2/variables?name=%' . $search . '%';
        $this->get($doubleLikeApiUrl);
        $doubleLikeContent = $this->getTestResponse()->getContent();
        $doubleLikeResponse = json_decode($doubleLikeContent, true);
        $this->assertEquals(OverallMoodCommonVariable::NAME, $doubleLikeResponse['data'][0]['name']);
    }
}
