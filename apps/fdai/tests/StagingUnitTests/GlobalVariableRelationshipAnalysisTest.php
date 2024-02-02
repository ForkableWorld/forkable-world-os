<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests;
use Tests\SlimStagingTestCase;
use App\VariableRelationships\QMGlobalVariableRelationship;
class GlobalVariableRelationshipAnalysisTest extends SlimStagingTestCase
{
    public function testGlobalVariableRelationshipAnalysis(): void{
		$c = QMGlobalVariableRelationship::getOrCreateByIds(5954773 ,102685);
		$c->analyzeFully('we are testing');
		$this->checkTestDuration(10);
		$this->checkQueryCount(34);
	}
}
