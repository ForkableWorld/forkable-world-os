<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Debug;
use App\Properties\UserVariable\UserVariableStatusProperty;
use App\Utils\QMProfile;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\PhpUnitJobs\JobTestCase;
class CommonVariableDebugTest extends JobTestCase{
    public function testCommonVariableAnalysisDebug(){
        $v = QMCommonVariable::findByNameOrId(88271);
        $row = $v->getDbRow();
        $correlation = $v->getBestGlobalVariableRelationship();
        $v->status = UserVariableStatusProperty::STATUS_UPDATED;
        $v->analyzeFully(__FUNCTION__);
    }
}
