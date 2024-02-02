<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Debug;
use App\DataSources\Connectors\TigerViewConnector;
use App\Utils\QMProfile;
use App\Reports\GradeReport;
use App\Slim\Model\User\QMUser;
use App\PhpUnitJobs\JobTestCase;
class RootCauseReportDebugTest extends JobTestCase {
    public function testRootCauseAnalysis(){
        //$c = TigerViewConnector::getByUserId(1);
        //$c->importData(0);
        $r = new GradeReport(1);
        $r->email();
        $u = QMUser::find(230);
        $v = $u->findOrCreateQMUserVariable("Energy");
        $a = $v->getRootCauseAnalysis();
        $a->email();
        //$a->email();
        //$a->saveHtmlFileLocallyForDebugging();
    }
}
