<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace App\PhpUnitJobs\Debug;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Variables\QMUserVariable;
use App\PhpUnitJobs\JobTestCase;
use App\Slim\Model\User\QMUser;
/** @package Tests\Api\Production
 */
class CorrelationDebugTest extends JobTestCase {
    /**
     * @group Production
     */
    public function testCorrelationPush(): void{
        $correlations = QMUserVariableRelationship::getOrCreateUserOrGlobalVariableRelationships(['userId' => 230]);
        $c = $correlations[0];
        $c->sendPushNotification();
    }
    /**
     * @group Production
     */
    public function testCorrelateAllStaleForUser(): void{
        $userId = 230;
        $user = QMUser::find(230);
        $user->correlateAllStale();
	}
    /**
     * @group Production
     */
    public function testCorrelateCalculation(): void{
        $userId = 230;
        $cause = QMUserVariable::getByNameOrId($userId, "Sleep Quality");
        $effect = QMUserVariable::getByNameOrId($userId, "Headache Severity");
        $c = new QMUserVariableRelationship(null, $cause, $effect);
        $c->analyzeFully(__FUNCTION__);
    }
    /**
     * @group Production
     */
    public function testCorrelateForVariable(): void{
        $v = QMUserVariable::getByNameOrId(230, "Interest");
        $v->calculateCorrelationsIfNecessary();
    }
}
