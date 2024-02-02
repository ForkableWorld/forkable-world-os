<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\User;
use App\DataSources\Connectors\GoogleLoginConnector;
use App\Exceptions\InvalidUsernameException;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\QMSlim;
use App\Slim\View\Request\QMRequest;
use App\Types\QMArr;

/** Class PostUserController
 * @package App\Slim\Controller\User
 */
class PostUserController extends PostController {
	/**
	 * @throws InvalidUsernameException
	 */
	public function post(){
		if($app = QMSlim::getInstance()) {
            $clientUserData = $app->getRequestJsonBodyAsArray(true);
        } else {
            $clientUserData = request()->input();
        }
		if(!$clientUserData && isset($_REQUEST["clientUser"])){
			$clientUserData = $_REQUEST["clientUser"];
		}
		$u = QMAuth::getQMUser();
		if(!$u && GoogleLoginConnector::getIdTokenFromRequest()){
			$u = QMAuth::authenticateByGoogle();
		}
		if(!$u){
			$u = User::createNewUserAndLogin($clientUserData);
		} else {
            $snake = QMArr::snakize($clientUserData);
            $u->fill($snake);
            $u->save();
			$u->login();
        }
		$u = $u->getQMUser();
		$u->getOrSetAccessTokenString(BaseClientIdProperty::fromRequest(false) ?? 
		                              BaseClientIdProperty::CLIENT_ID_QUANTIMODO);
        $arr = [
            'status' => 201,
            'success' => true,
            'user' => $u,
            'data' => ['user' => $u],
        ];
        if(!$app){
            return response()->json($arr, 201);
        }
		return $this->writeJsonWithGlobalFields(201, $arr);
	}
}
