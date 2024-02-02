<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\OAuth2;
use App\Buttons\Auth\AuthButton;
use App\DataSources\QMClient;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Http\Urls\IntendedUrl;
use App\Logging\QMLog;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseScopeProperty;
use App\Slim\Controller\GetController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Auth\OAuth2Server;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\Model\QMResponseBody;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Types\QMStr;
use App\Utils\APIHelper;
use App\Utils\UrlHelper;
use Illuminate\Http\JsonResponse;
use OAuth2 as OAuth;
class GetAuthorizationPageController extends GetController {
	private $oauth2Request;
	private $oauth2Response;
	/**
	 * @param string $clientId
	 * @return bool
	 */
	private static function isTrusted(string $clientId): bool{
		$allowedClientIds = [
			//'EnergyModo',  // Breaks Android login
			'IXCq4KOabRaIQw9x',
			//MediModo
			'Mind First',
			'bOGtinzJyQrbXEfv',
			// MoodiModo
			'Vv7HymHq6PkTrZfM',
			//QuantiModo
			BaseClientIdProperty::CLIENT_ID_QUANTIMODO,
			'medimodo',
			//'moodimodo'  // We can't allow moodimodo to skip because it breaks android auth
		];
		return in_array($clientId, $allowedClientIds, true);
	}
	/**
	 * @throws ClientNotFoundException
	 * @throws \App\Exceptions\UnauthorizedException
	 */
	public function get(){
		$user = QMAuth::getQMUser();
		if(!$user){
			return AuthButton::redirectToLoginOrRegister();
		}
		if($token = BaseAccessTokenProperty::fromRequest()){  // They already have an access token!
			BaseAccessTokenProperty::fromRequest();
			$intended = IntendedUrl::get();
			if(!str_contains($intended, 'oauth2/authorize')){
				return IntendedUrl::redirect([BaseAccessTokenProperty::NAME => $token]);
			}
		}
		// This should already be set in getUser $user->setLoggedInCookie();
		$this->validateOAuthRequest();
		if($this->skipAuthorizationDialogPageIfClientIsTrusted()){
			$oauth2Response = $this->getOauth2Response();
			if(APIHelper::apiVersionIsAbove(3)){
				return $this->writeJsonWithGlobalFields(QMResponseBody::CODE_TEMPORARY_REDIRECT,
					['oauth2ResponseParameters' => $oauth2Response->getParameters()]);
			} else{
				return $this->writeJsonWithoutGlobalFields(QMResponseBody::CODE_TEMPORARY_REDIRECT,
					$oauth2Response->getParameters());
			}
		}
		return $this->renderAuthorizationDialogPage($user);
	}
	/**
	 * @return \Illuminate\Http\Response
	 * @throws \App\Exceptions\UnauthorizedException
	 * @throws ClientNotFoundException
	 */
	private function skipAuthorizationDialogPageIfClientIsTrusted(): bool {
		if(self::isTrusted(BaseClientIdProperty::fromRequestDirectly(true))){
			$oauth2Response = $this->getOauth2Response();
			$oauth2Request = $this->getOauth2Request();
			$user = $this->getUser();
			OAuth2Server::get()->handleAuthorizeRequest($oauth2Request, $oauth2Response, 
			                                            true, $user->id);
			$this->getApp()->response->setStatus(QMResponseBody::CODE_TEMPORARY_REDIRECT);
            $url = $oauth2Response->getHttpHeader('Location');
            $url = IntendedUrl::addUserInfoIfNecessary($url);
			$this->getApp()->response->headers->set('Location', $url);
			return true;
		}
		QMLog::debug("Client is not trusted: " . BaseClientIdProperty::fromMemory());
		return false;
	}
	/**
	 * @return OAuth\Request
	 */
	public function getOauth2Request(): OAuth\Request{
		if($this->oauth2Request){
			return $this->oauth2Request;
		}
		return $this->oauth2Request = OAuth\Request::createFromGlobals();
	}
	/**
	 * @return OAuth\Response
	 */
	public function getOauth2Response(): OAuth\Response{
		if($this->oauth2Response){
			return $this->oauth2Response;
		}
		return $this->oauth2Response = new OAuth\Response();
	}
	/**
	 * @param QMUser $QMUser
	 * @return string
	 * @throws ClientNotFoundException
	 */
	private function renderAuthorizationDialogPage(QMUser $QMUser): string {
		$scopes = $this->getScopes();
		$app = $this->getApp();
		$app->response->headers->set('Content-Type',
			'text/html'); // We'll be responding with a form, so set the content type to text/html
		$view = $app->view();
		$view->appendData([
			'csrf_key' => 'token',
			'csrf_token' => QMAccessToken::createOrGetCsrfSessionToken(),
		]);  // Assign CSRF token key and value to view.
		$client = QMClient::fromRequest();
		$s = $client->getAppSettings();
		$image = $s->getImage();
		$data = [
			'scopes' => $scopes,
			'appDisplayName' => $s->getTitleAttribute(),
			'loginName' => $QMUser->loginName,
			'requestPath' => QMRequest::current(),
			'scopeDescriptions' => BaseScopeProperty::scopeDescriptionsArray($scopes),
			'logoUrl' => $image,
		];
		$app->render('OAuth2Authorize.php', $data);
		return $view->fetch('OAuth2Authorize.php', $data);
	}
	private function validateOAuthRequest(): void{
		if(!isset($_GET['client_id'])){
			$_GET['client_id'] = BaseClientIdProperty::fromRequest(true);
		}
		$oauth2Response = $this->getOauth2Response();
		$oauth2Request = $this->getOauth2Request();
		$server = OAuth2Server::get();
		$validationResponse = $server->validateAuthorizeRequest($oauth2Request, $oauth2Response);
		if(!$validationResponse){
			$errors = $oauth2Response->getParameters();
			QMLog::error($errors["error"] . " " . $errors["error_description"], $oauth2Request->headers);
			$oauth2Response->send();
			new JsonResponse($errors, $oauth2Response->getStatusCode());
			//$this->getApp()->stop();
		}
	}
	/**
	 * @return array
	 */
	private function getScopes(): array{
		$param = QMRequest::getParam('scope');
		return explode(' ', $param);
	}
	public static function generateAuthorizeUrl(string $clientId, ?string $redirect): string{
		if($redirect){
			$redirect = QMStr::before($redirect, " ", $redirect);
		} else{
			$redirect = 'please_provide_redirect_at_' . UrlHelper::getBuilderUrl($clientId);
		}
		return UrlHelper::getUrl('/oauth/authorize', [
			'state' => 'abc',
			'client_id' => urlencode($clientId),
			'redirect_uri' => urlencode($redirect),
			'response_type' => 'code',
			'scope' => 'readmeasurements+writemeasurements',
		]);
	}
	/**
	 * @return \App\Slim\Model\User\QMUser
	 * @throws \App\Exceptions\UnauthorizedException
	 */
	public function getUser(): \App\Slim\Model\User\QMUser{
		$user = QMAuth::getQMUser();
		if(!$user){
			QMLog::error("Could not getUserForCookie! Figure out why this happens! ");
			throw new UnauthorizedException();
		}
		return $user;
	}
}
