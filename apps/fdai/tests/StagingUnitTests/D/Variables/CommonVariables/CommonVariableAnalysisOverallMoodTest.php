<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\D\Variables\CommonVariables;
use App\Variables\QMCommonVariable;
use Tests\SlimStagingTestCase;
class CommonVariableAnalysisOverallMoodTest extends SlimStagingTestCase {
    public function testCommonVariableAnalysisOverallMood(): void{
        if(true){
            $this->skipTest("Too slow");
            return;
        }
		$v = QMCommonVariable::findByNameOrId(1398);
		$tags = $v->getCommonTaggedVariables();
        $this->assertCount(0, $tags);
		$this->analyzeAndCheckCommonVariable(1398);
		$this->checkTestDuration(70);
		$this->checkQueryCount(2);
	}
}
