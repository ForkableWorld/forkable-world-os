<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Models;
use App\DataSources\Connectors\DaylightConnector;
use App\Models\Connector;
use App\Models\User;
use App\Models\Variable;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\GoalsCommonVariables\CodeCommitsCommonVariable;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Models\BaseModel;
 */
class BaseModelTest extends UnitTestCase {
    public function testCreateUnitTest(){
        $this->skipTest("TODO: fix this test");
        $v = Variable::find(OverallMoodCommonVariable::ID);
        $testFile = $v->getUnitTestFile();
        $expectedPath = 'tests/UnitTests/Generated/Models/VariableTest.php';
        $this->assertSameRelativePath($testFile->getPath(), $expectedPath);
        $this->assertSameRelativePath($testFile->getRelativeFilePath(), $expectedPath);
        $this->assertSameRelativePath($testFile->getRealPath(), $expectedPath);
        $this->assertSameRelativePath($testFile->getRelativeFilePath(), $expectedPath);
        $this->assertEquals('Tests\UnitTests\Generated\Models\VariableTest', $testFile->getClassName());
        $testBody = "some code";
        $testFile->addMethod(__FUNCTION__)->setBody($testBody);
        $methods = $testFile->getMethods();
        $this->assertCount(1, $methods);
        $testFile->save();
        $this->assertFileExists($testFile->getPath());
        $this->assertFileContains($expectedPath, $testBody);
        $testFile->deleteMethod(__FUNCTION__);
        $testFile->save();
        $this->assertFileNotContains($expectedPath, $testBody);
        $testFile->delete(__FUNCTION__);
        $this->assertFileDoesNotExist($testFile->getPath());

    }
	/**
	 * @covers BaseModel::clone
	 * @noinspection PhpUnitMissingTargetForTestInspection (from a trait)
	 */
	public function testCloneBaseModel(){
		QMAuth::setUserLoggedIn(User::testUser(), false);
		BaseClientIdProperty::setInMemory(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
		$commits = CodeCommitsCommonVariable::instance()->l();
		$name = "Test Variable Clone";
		Variable::whereName($name)->forceDelete(__FUNCTION__);
		$clone = $commits->clone([Variable::FIELD_NAME => $name]);
		$clone->name = $name;
		$clone->save();
		$this->assertEquals($name, $clone->name);
		$this->assertNotEquals($commits->id, $clone->id);
		$this->assertIsInt($clone->id);
		$this->assertArrayEquals([$name], $clone->synonyms);
		$this->assertEquals($name, $clone->common_alias);
		$this->assertEquals(User::testUser()->getId(), $clone->creator_user_id);
		$this->assertEquals(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, $clone->client_id);
	}
    public function testWhereLike(){
        $vars = Variable::whereLike(Variable::FIELD_NAME, "a")->get();
        $this->assertCount(0, $vars);
        $vars = Variable::whereLike(Variable::FIELD_NAME, OverallMoodCommonVariable::NAME)->get();
        $this->assertCount(1, $vars);
        $vars = Variable::whereLike(Variable::FIELD_NAME, "%a%")->get();
        $this->assertGreaterThan(1, $vars->count());
    }
	public function testFindByStringId(){
		$c = Connector::findByNameLikeOrId(DaylightConnector::ID);
		$this->assertEquals(DaylightConnector::DISPLAY_NAME, $c->getTitleAttribute());
	}

}
