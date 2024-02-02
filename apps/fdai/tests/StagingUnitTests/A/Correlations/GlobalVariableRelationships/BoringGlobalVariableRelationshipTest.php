<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A\Correlations\GlobalVariableRelationships;
use App\Logging\QMLog;
use App\Properties\Variable\VariableNameProperty;
use App\PhpUnitJobs\Cleanup\CorrelationsCleanUpJob;
use Tests\SlimStagingTestCase;
use App\VariableRelationships\QMGlobalVariableRelationship;

class BoringGlobalVariableRelationshipTest extends SlimStagingTestCase
{
    public function testBoringGlobalVariableRelationship(): void{
        //CorrelationsCleanUpJobTest::fix3rdPartyQmScore();
        $fix = false;
        if($fix){
            VariableNameProperty::deleteStupidBoringVariables();
            CorrelationsCleanUpJob::testDeleteStupidCorrelations();
        }
		$correlations = QMGlobalVariableRelationship::getOrCreateGlobalVariableRelationships();
		$this->assertCount(10, $correlations);
		foreach($correlations as $correlation){
		    QMLog::infoWithoutContext($correlation->generateStudyTitle());
		    if($correlation->isBoring()){
		        $correlation->logInfo("QM SCORE: ".$correlation->getAggregateQMScore());
                $this->assertFalse($correlation->isBoring(), $correlation->generateStudyTitle());
            }
        }
		$this->checkTestDuration(16);
		$this->checkQueryCount(5);
	}
}
