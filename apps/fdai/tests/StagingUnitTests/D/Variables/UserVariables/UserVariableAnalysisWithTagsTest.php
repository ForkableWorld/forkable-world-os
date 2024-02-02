<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\Variables\UserVariables;
use App\Models\UserVariableClient;
use Tests\SlimStagingTestCase;
use App\Variables\QMUserVariable;

class UserVariableAnalysisWithTagsTest extends SlimStagingTestCase {
    public function testUserVariableAnalysisWithTagsTest(){
        UserVariableClient::whereId(0)->forceDelete();
		$v = QMUserVariable::getByNameOrId(230, 86672);
		$v->analyzeFully(__FUNCTION__);
		$this->checkTestDuration(23);
		$this->checkQueryCount(108);
	}
}
