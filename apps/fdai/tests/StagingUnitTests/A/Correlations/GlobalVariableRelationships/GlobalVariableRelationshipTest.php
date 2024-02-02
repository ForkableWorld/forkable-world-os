<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A\Correlations\GlobalVariableRelationships;
use Tests\SlimStagingTestCase;
use App\VariableRelationships\QMGlobalVariableRelationship;

class GlobalVariableRelationshipTest extends SlimStagingTestCase
{
    public function testGlobalVariableRelationship(): void{
		QMGlobalVariableRelationship::getOrCreateByIds(1248 ,1398);
		$this->checkTestDuration(18);
		$this->checkQueryCount(5);
	}
}
