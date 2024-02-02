<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Connector;
use App\DataSources\QMClient;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\Model\User\QMUser;
use App\Slim\QMSlim;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
class PostConnectTokensController extends PostController {
	/**
	 * @return JsonResponse|Response
	 * @throws ClientNotFoundException
	 * @throws UnauthorizedException
	 */
	public function post(){
		$app = QMSlim::getInstance();
		$body = $app->getRequestJsonBodyAsArray(true);
		QMClient::authenticateClient($body['clientId'], $body['clientSecret']);
		QMAccessToken::checkSessionToken($body['sessionToken']);
		QMAuth::setUserLoggedIn(QMUser::findWithToken($body['quantimodoUserId']), true);
		$t = QMAccessToken::getOrCreateToken($body['clientId'], QMAuth::id(), 'importdata');
		return $this->writeJsonWithGlobalFields(201, [
			'status' => 'OK',
			'accessToken' => QMAuth::getQMUserIfSet()->getOrSetAccessTokenString(),
			'publicToken' => $t->getAccessTokenString(),
			'quantimodoUserId' => $body['quantimodoUserId'],
			'clientUserId' => $body['clientUserId'],
		]);
	}
}
