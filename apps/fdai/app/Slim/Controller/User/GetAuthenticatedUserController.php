<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\User;
use App\Exceptions\ClientNotFoundException;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserIdProperty;
use App\Slim\Controller\GetController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model;
use App\Slim\Model\User\QMUser;
use App\Utils\APIHelper;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
class GetAuthenticatedUserController extends GetController {
	/**
	 * @return JsonResponse|Response|null
	 * @throws ClientNotFoundException
	 */
	public function get(){
		$user = $this->getUser();
		$this->addAdditionalProperties($user);
		return $this->returnUser($user);
	}
	/**
	 * @return QMUser|null
	 * @throws ClientNotFoundException
	 */
	protected function getUser(): ?QMUser{
		$params = request()->all();
		$userId = UserIdProperty::fromRequest();
		if($userId && QMAuth::isAdmin()){
			$user = QMUser::findGetTokenAndUnsetPassword($userId);
		} else{
			User::setUserPlatform($params);
			$user = QMUser::getAuthenticatedUserWithAccessTokenAndWithoutPassword();
		}
		if($user && !Auth::user()){QMAuth::login($user->getUser());}
		return $user;
	}
	/**
	 * @param QMUser $user
     * Response
	 */
	protected function returnUser(QMUser $user): JsonResponse{
		if(APIHelper::apiVersionIsAbove(3)){
			return $this->writeJsonWithGlobalFields(200, ['user' => $user]);
		} else{
			$obj = json_decode(json_encode($user));
			if(BaseClientIdProperty::fromRequest(false)){
				$obj->sessionTokenObject = Model\QMResponseBody::generateSessionTokenObject();
			}
			return $this->writeJsonWithoutGlobalFields(200, $user);
		}
	}
	/**
	 * @param QMUser $user
	 */
	protected function addAdditionalProperties(QMUser $user): void{
		$user->getShareAllData();
		$user->getOrSetPrimaryOutcomeVariableName();
		if(qm_request('includeAuthorizedClients')){
			$user->getAuthorizedClients();
		}
	}
}
