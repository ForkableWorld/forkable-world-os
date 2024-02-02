<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Analytics;
use App\Computers\ThisComputer;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Models\User;
use App\Models\UserVariable;
use App\PhpUnitJobs\JobTestCase;
use App\Slim\Model\User\QMUser;
/** Class UserVariableRelationshipsJobTest
 * @package App\PhpUnitJobs
 */
class UserVariableRelationshipsJob extends JobTestCase {
    //public const SLACK_CHANNEL = "user-variable-relationships";
    protected const SLACK_CHANNEL = '#emergency';
    public function testUserVariableRelationshipAnalysisJob(){
        $this->assertGreaterThanOrEqual(1500, ThisComputer::getMemoryLimitWithBufferInMB());
        //CorrelationInternalErrorMessageProperty::fixInvalidRecords();
        UserVariable::correlateNeverCorrelated();
        QMUserVariableRelationship::analyzeNeverAnalyzedUntilComplete();
        $correlations = QMUserVariableRelationship::analyzeWaitingStaleStuck();
        if(!$correlations){$correlations = QMUserVariableRelationship::analyzeStuck();}
        if(!$correlations){$correlations = QMUserVariableRelationship::analyzeNeverFinished();}
        $this->assertGreaterThan(0, count($correlations), "We didn't find any user_variable_relationships that needed analysis!");
    }
    private static function correlateByUser(): void{
        /** @var QMUser[] $rows */
        $rows = QMUser::readonly()
            ->orderBy(User::FIELD_LAST_CORRELATION_AT, 'asc')
            ->limit(100)
            ->getDBModels();
        foreach($rows as $row){
            $row->updateUserStatisticsAndCorrelate();
        }
    }
}
