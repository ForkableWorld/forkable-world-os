<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\D\Variables\CommonVariables;
use Tests\SlimStagingTestCase;
class CommonVariableAnalysisWalkOrRunDistanceTest extends SlimStagingTestCase {
    public function testCommonVariableAnalysisWalkOrRunDistance(): void{
        if($disabled = true){
            $this->skipTest("Too slow and we don't have enough memory");
            return;
        }
        $this->analyzeAndCheckCommonVariable(1304);
        $this->checkTestDuration(90);
        $this->checkQueryCount(2);
    }
}
