<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\OAuth2;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\UnauthorizedException;
use App\Models\Application;
use App\Models\User;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Auth\OAuth2Server;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\Model\QMResponseBody;
use App\Slim\View\Request\QMRequest;
use App\Types\QMStr;
use App\Utils\IonicHelper;
use Illuminate\Http\RedirectResponse;
use OAuth2 as OAuth;
use OAuth2\Response;
use Throwable;
/** OAuth2
 * @package App\Slim\Controller * @SWG\Resource(
 *     apiVersion="1.0",
 *     swaggerVersion="1.2",
 *     resourcePath="/oauth2",
 *     description="OAuth2 authorization token that can be used to create an access token.",
 *     produces="['application/json']"
 * )
 */
class CreateAuthorizationTokenController extends PostController {
	/**
	 * POST /oauth2/authorize
	 * @return RedirectResponse
	 * @throws UnauthorizedException
	 */
	public function post(){
		$isAuthorized = QMRequest::getBool('authorized');
		$request = OAuth\Request::createFromGlobals();
		$response = new OAuth\Response();
		QMAccessToken::checkSessionToken(QMRequest::getBodyParam('token'));
		$server = OAuth2Server::get();
		if(!$server->validateAuthorizeRequest($request, $response)){
			$response->send();
			$this->getApp()->stop();
		}
		$id = QMAuth::id();
		if(!$id){
			throw new UnauthorizedException('User not logged in');
		}
		try {
			$server->handleAuthorizeRequest($request, $response, $isAuthorized, $id);
		} catch (\Throwable $e) {
			ExceptionHandler::dumpOrNotify($e);
			$user = User::findInMemoryOrDB($id);
			if(!$user){
				le("Could not find user with id $id");
			}
			le($e);
		}
		try {
			Application::createDefaultReminders();
		} catch (Throwable $e) {
			ExceptionHandler::throwIfNotProductionApiRequest($e);
		}
		return $this->redirect($response->getHttpHeader('Location'));
	}
	/**
	 * @param Response $response
	 * @return RedirectResponse
	 * @throws ClientNotFoundException
	 * @throws UnauthorizedException
	 */
	public function redirectToIntroPage(OAuth\Response $response): RedirectResponse {
		$u = QMAuth::getQMUserIfSet();
		$app = $this->getApp();
		$url = $response->getHttpHeader('Location');
		if(stripos($url, 'account/applications#') !== false){  // Physician
			$query = QMStr::after('account/applications#', $url);
			//$url = "https://web.quantimo.do/#/app/data-sharing?".$query;
			$params = [];
			$params[BaseAccessTokenProperty::URL_PARAM_NAME] = $u->getOrSetAccessTokenString();
			$params['clientId'] = BaseClientIdProperty::fromRequest(true);
			$url = IonicHelper::getIntroUrl($params);
		}
		$app->response->setStatus(QMResponseBody::CODE_TEMPORARY_REDIRECT);
		$app->response->headers->set('Location', $url);
		return redirect($url, QMResponseBody::CODE_TEMPORARY_REDIRECT);
	}
}
