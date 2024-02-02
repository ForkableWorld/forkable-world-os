<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Debug;
use App\Slim\Model\User\QMUser;
use App\PhpUnitJobs\JobTestCase;
class UserStatsDebugTest extends JobTestCase {
    public function testUserDebugUrl(){
        $u = QMUser::getByEmailOrId(82869);
        $u->outputDebugStatsAndUrlWithToken();
    }
}
