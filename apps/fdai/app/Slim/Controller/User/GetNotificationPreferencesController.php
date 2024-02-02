<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\User;
use App\Slim\Controller\GetController;
use App\Slim\Model\User\QMUser;
use App\Utils\APIHelper;
/** Class GetNotificationPreferencesController
 * @package App\Slim\Controller\User
 */
class GetNotificationPreferencesController extends GetController {
	public function get(){
		$user = QMUser::getUserNotificationPreferences($this->getApp()->params());
		if(APIHelper::apiVersionIsAbove(3)){
			return $this->writeJsonWithGlobalFields(200, ['user' => $user], JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		} else{
			$this->writeJsonWithoutGlobalFields(200, $user, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		}
	}
}
