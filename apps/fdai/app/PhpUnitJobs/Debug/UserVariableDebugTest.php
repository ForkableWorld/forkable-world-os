<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Debug;
use App\Variables\QMUserVariable;
use App\PhpUnitJobs\JobTestCase;
class UserVariableDebugTest extends JobTestCase {
    public function testUserVariableAnalysisDebug(){
        $v = QMUserVariable::getByNameOrId(230, "Purchases Of BulkSupplements Pure Potassium Citrate Powder");
        $v->getLastValuesInUserUnit();
        $v->forceAnalyze(__FUNCTION__);
    }
}
