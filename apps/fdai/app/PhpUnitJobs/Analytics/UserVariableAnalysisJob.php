<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Analytics;
use App\Models\Measurement;
use LogicException;
use App\Storage\DB\Writable;
use App\Logging\QMLog;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use App\PhpUnitJobs\JobTestCase;
class UserVariableAnalysisJob extends JobTestCase {
    //protected const SLACK_CHANNEL = 'user-variable-analysis';
    protected const SLACK_CHANNEL = '#emergency';
    public function testUserVariableAnalysisJob(): void{
		if(mt_rand(0, 100) > 95){
			UserVariableAnalysisJob::makeSureThereAreNoAnalysisSettingsDuplicatedInUserVariablesTable();
		}
        QMUserVariable::analyzeWaitingStaleStuck();
        $this->checkNumberAnalyzedInLast24Hours();
        $this->assertTrue(true);
    }
    protected function checkNumberAnalyzedInLast24Hours(): void{
        $count = QMUserVariable::getNumberAnalyzedInLastDay();
        $this->assertGreaterThan(10, $count);
        $v = QMUserVariable::getMostRecentlyAnalyzed();
        $lastMeasurementAt = Measurement::max(Measurement::FIELD_START_AT);
        $analysisEndedAt = $v->getAnalysisEndedAt();
        if(time_or_exception($lastMeasurementAt) > strtotime($lastMeasurementAt)){
            le("Last measurement at $lastMeasurementAt but last analysisEndedAt $analysisEndedAt ");
        }
    }
    public static function makeSureThereAreNoAnalysisSettingsDuplicatedInUserVariablesTable(): void{
        $fields = QMVariable::getAnalysisSettingsFields();
        foreach($fields as $field){
            $skip = [
//                UserVariable::FIELD_CAUSE_ONLY,
//                UserVariable::FIELD_COMBINATION_OPERATION,
//                UserVariable::FIELD_DURATION_OF_ACTION,
            ];
            if(in_array($field, $skip)){continue;}
            $result = Writable::selectStatic("select count(*) as total
                from user_variables uv
                    join variables v on v.id = uv.variable_id
                where uv.$field is not null and uv.$field = v.$field
            ");
            $total = $result[0]->total;
            if($total > 0){
                $m = "$field has $total duplicated variable settings!";
                QMLog::error($m);
                //le("$field has $total duplicated variable settings!");
            }
        }
    }
}
