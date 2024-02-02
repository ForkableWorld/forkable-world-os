<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Connector;
use App\Exceptions\QMException;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\UserMeta;
use App\Slim\QMSlim;
use App\Slim\View\Request\QMRequest;
use App\Utils\APIHelper;
class QuantiModoConnectFinishController extends PostController {
	public function post(){
		$app = QMSlim::getInstance();
		$user = QMAuth::getQMUserIfSet();
		$requestBody = QMRequest::body();
		$response =
			APIHelper::makePostRequest(QMRequest::origin() . '/api/v1/connect/tokens', $requestBody);
		if(isset($response->error)){
			throw new QMException(400, $response['error']);
		}
		$db = UserMeta::writable();
		$user->publicToken = $response->publicToken;
		//$user->quantimodoUserId = $response->quantimodoUserId;
		$db->insert([
			'user_id' => $user->id,
			'meta_key' => 'quantimodo_api_access_token',
			'meta_value' => $response->accessToken,
		]);
		$db->insert([
			'user_id' => $user->id,
			'meta_key' => 'quantimodo_api_public_token',
			'meta_value' => $response->publicToken,
		]);
//		$db->insert([
//			'user_id' => $user->id,
//			'meta_key' => 'quantimodo_api_quantimodo_id',
//			'meta_value' => $response->quantimodoUserId,
//		]);
		$measurements = APIHelper::callAPI('GET',
			'https://' . $request->getHost() . '/api/v1/measurements?access_token=' . $response->accessToken);
		return $this->writeJsonWithGlobalFields(201, [
			'status' => 'OK',
			'user' => $user,
			'measurements' => $measurements,
		]);
	}
}
