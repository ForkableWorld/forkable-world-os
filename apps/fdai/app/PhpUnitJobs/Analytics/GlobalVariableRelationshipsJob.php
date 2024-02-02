<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Analytics;
use App\Exceptions\InsufficientMemoryException;
use App\Logging\QMLog;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipAggregateQmScoreProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipIsPublicProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipNumberOfCorrelationsProperty;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\PhpUnitJobs\JobTestCase;
class GlobalVariableRelationshipsJob extends JobTestCase {
    public function testGlobalVariableRelationshipsJob(): void{
        GlobalVariableRelationshipNumberOfCorrelationsProperty::fixInvalidRecords();
        GlobalVariableRelationshipAggregateQmScoreProperty::updateAll();
        GlobalVariableRelationshipIsPublicProperty::updateAll();
        QMGlobalVariableRelationship::analyzeNeverAnalyzedUntilComplete();
        try {
            QMGlobalVariableRelationship::analyzeWaitingStaleStuck();
        } catch (InsufficientMemoryException $e){ // We don't want to fail the job just because we ran out of memory
            QMLog::info(__METHOD__.": ".$e->getMessage());
        }
        $this->checkAggregatedCorrelationStats();
    }
}
