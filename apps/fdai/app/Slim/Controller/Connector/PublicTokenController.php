<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Connector;
use App\DataSources\QMClient;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\Model\User\QMUser;
use App\Slim\QMSlim;
class PublicTokenController extends PostController {
	public function post(){
		$app = QMSlim::getInstance();
		$body = $app->getRequestJsonBodyAsArray(true);
		QMClient::authenticateClient($body['clientId'], $body['clientSecret']);
		QMAccessToken::checkSessionToken($body['sessionToken']);
		$user = QMUser::findWithToken($body['quantimodoUserId']);
		$public = QMAccessToken::getOrCreateToken($body['clientId'], QMAuth::id(), 'importdata');
		return $this->writeJsonWithGlobalFields(201, [
			'status' => 'OK',
			'accessToken' => $user->getOrSetAccessTokenString(),
			'publicToken' => $public->getAccessTokenString(),
			//'quantimodoUserId' => $body['quantimodoUserId'],
			'clientUserId' => $body['clientUserId'],
		]);
	}
}
