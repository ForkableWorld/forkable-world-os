<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\Auth;
use App\Buttons\Auth\LoginButton;
use App\Buttons\QMButton;
use App\DataSources\Connectors\GoogleLoginConnector;
use App\DataSources\OAuthConnector;
use App\DataSources\QMConnector;
use App\Exceptions\BadRequestException;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Urls\FinalCallbackUrl;
use App\Http\Urls\IntendedUrl;
use App\Logging\QMLog;
use App\Models\OAClient;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Controller\Connector\ConnectException;
use App\Slim\Controller\Connector\ConnectorRedirectResponse;
use App\Slim\Controller\Connector\ConnectorResponse;
use App\Slim\Controller\Connector\GetConnectorsController;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Types\QMStr;
use App\UI\HtmlHelper;
use App\Utils\IonicHelper;
use App\Utils\UrlHelper;
use Config;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Log;
use Request;
use Session;
use Socialite;
class SocialAuthController extends Controller {
	public static $availableProviders = ['facebook', 'google'];
	//twitter is disabled because they don't provide email
	/**
	 * @return mixed
	 */
	public function login(){
		$provider = Request::input('provider');
		return $this->returnSocialLogInUrl($provider);
	}
	/**
	 * @param string $provider
	 * @return mixed
	 */
	private function returnSocialLogInUrl(string $provider){
		$connector = OAuthConnector::findByNameIdOrSynonym($provider);
		if($connector){
			$driver = $connector->getSocialiteDriver();
		}
		if(!in_array($provider, self::$availableProviders)){
			throw new BadRequestException("Invalid Social Provider Name: " . $provider);
		}
		$this->setSocialAuthCallbackRedirect($provider);
		$driver = Socialite::driver($provider);
		return $driver->redirect();
	}
	/**
	 * @return mixed
	 */
	public function webLogin(){
		$provider = Request::input('provider');
		Log::debug("webLogin: Setting redirect to " . $this->getSocialAuthCallbackUrl($provider));
		return $this->returnSocialLogInUrl($provider);
	}
	/**
	 * @param $provider
	 * @return string
	 */
	public function getSocialAuthCallbackUrl($provider): string{
		return route('auth.social.callback', ['provider' => $provider]);
	}
	/**
	 * @param $provider
	 */
	public function setSocialAuthCallbackRedirect($provider){
		$value = $this->getSocialAuthCallbackUrl($provider);
		Config::set('services.'.$provider.'.redirect', $value);
		Session::set('social.auth.redirectUrl', $value);
	}
	/**
	 * @param $arr
	 * @return JsonResponse
	 */
	public function getAuthorizationResponse($arr): JsonResponse{
		return new JsonResponse([
				'success' => true,
				'data' => [
					'accessToken' => $arr['accessAndRefreshToken']['accessToken'],
					'refreshToken' => $arr['accessAndRefreshToken']['refreshToken'],
					'expiresIn' => $arr['accessAndRefreshToken']['expiresIn'],
					'expiresAt' => $arr['accessAndRefreshToken']['expiresAt'],
					'access_token' => $arr['accessAndRefreshToken']['accessToken'],
					'refresh_token' => $arr['accessAndRefreshToken']['refreshToken'],
					'expires_in' => $arr['accessAndRefreshToken']['expiresIn'],
					'expires_at' => $arr['accessAndRefreshToken']['expiresAt'],
					'user' => $arr,
				],
			]);
	}
	/**
	 * @return JsonResponse
	 * @throws UnauthorizedException
	 */
	public function authorizeCode(): JsonResponse{
		$user = GoogleLoginConnector::loginByRequest();
		return $this->getAuthorizationResponse($user);
	}
	/**
	 * @return JsonResponse
	 * @throws UnauthorizedException
	 */
	public function authorizeToken(): JsonResponse{
		if(!Request::input('accessToken')){
			throw new BadRequestException("Token Required");
		}
		$user = GoogleLoginConnector::loginByRequest();
		return $this->getAuthorizationResponse($user);
	}
	/**
	 * @param string $connectorName
	 * @return RedirectResponse
	 * @throws ConnectException
	 */
	public function webAuthCallback(string $connectorName): RedirectResponse{
		$c = QMConnector::getConnectorByName($connectorName);
		$parameters = request()->all();
		$r = $c->connect($parameters);
		if($r instanceof ConnectorRedirectResponse){
			return UrlHelper::redirect($r->location);
		}
		return IntendedUrl::getRedirectResponse();
	}
	/**
	 * @param string $connectorName
	 * @return JsonResponse|RedirectResponse
	 */
	public function disconnect(string $connectorName){
		//return UrlHelper::redirect(QMRequest::getReferrer());
		$c = QMConnector::find($connectorName);
		$user = QMAuth::getUser();
		$c = $user->connections()->where('connector_id', $c->id)->first();
		if($c){
			$c->disconnect(QMConnector::USER_DISCONNECT_REQUEST);
		}
		if($this->request->expectsJson()){
			return new JsonResponse(['success' => true, 'message' => 'Disconnected '.$connectorName,
				                        'connectors' => $user->getQMConnectors()
			                        ], 201);
		}
		return UrlHelper::redirect(QMRequest::getReferrer());
	}
	/**
	 * @param string $connectorName
	 * @return RedirectResponse|Response
	 * @throws ConnectException
	 */
	public function connect(string $connectorName){
		$finalCallback = FinalCallbackUrl::getIfSet();
		$session = session();
		$intended = IntendedUrl::get();
		$user = QMAuth::getUser();
		$c = QMConnector::find($connectorName);
		if(!$user && !$c->providesUserProfileForLogin){
			IntendedUrl::setToCurrent();
			return LoginButton::redirectToLoginOrRegister();
		}
		$input = QMRequest::getInput();
		$r = $c->connect($input);
		if($r instanceof ConnectorRedirectResponse){
			return UrlHelper::redirect($r->location);
		}
		if($this->request->expectsJson()){
			if($r instanceof ConnectorResponse){
				return new Response(json_encode($r), $r->code, []);
			}
		}
		$isConnected = $c->isConnected();
		if($this->isGetRequest() &&
		   !$this->expectsJson() &&
		   $c->hasInputFields() &&
		   !$isConnected){
			return $c->getEditHTML();
		}
		return $this->redirect($connectorName);
	}
	/**
	 * @param string $connectorName
	 * @return RedirectResponse
	 */
	private function redirect(string $connectorName): RedirectResponse{
		$url = $this->getRedirectUrl($connectorName);
		try {
			return QMConnector::addParamsToUrlAndRedirect($url, BaseAccessTokenProperty::URL_PARAM_NAME);
		} catch (UnauthorizedException|ClientNotFoundException $e) {
			QMLog::error("Could not add access token to url: ".$url." because of ".$e->getMessage());
			return UrlHelper::redirect($url);
		}
	}
	/**
	 * @param string $connectorName
	 * @return string
	 */
	private function getRedirectUrl(string $connectorName): string{
		$url = $finalCallback = FinalCallbackUrl::getIfSet();
		if(!$url){
			$url = IntendedUrl::get();
			$currentWithoutQuery = QMStr::before('?', UrlHelper::current());
			if(str_starts_with($url, $currentWithoutQuery)){
				$url = null;
			}
		}
		if(!$url){
			$client = OAClient::fromRequest();
			if($client){$url = $client->getRedirectUri();}
		}
		if(!$url){
			$url = IonicHelper::ionicOrigin(BaseClientIdProperty::fromRequest(false));
			$user = QMAuth::getUser();
			QMLog::error("No final callback url set for connector: ".$connectorName.
			             " and no intended url set so redirecting to $url");
		}
		return $url;
	}
	public function list(){
		if($this->expectsJson()){
			return new JsonResponse(GetConnectorsController::getDataSources());
		}
		return SocialAuthController::getConnectorsListHtml();
	}
	/**
	 * @param string $connectorName
	 * @return JsonResponse
	 * @throws UnauthorizedException
	 */
	public function import(string $connectorName){
		$user = QMAuth::getUser();
		$c = QMConnector::find($connectorName);
		if(!$user){
			throw new UnauthorizedException("Please log in to import");
		}
		$newMeasurements = $c->importData();
		$connection = $c->getConnection();
		$measurements = $c->getNewMeasurements();
		if($this->expectsJson()){
			return new JsonResponse([
                'success' => true,
                'data' => [
                    'userVariables' => $c->getQMUserVariables(),
                    'connector_import' => $c->getConnectorImport(),
                    'connection' => $c->getConnection(),
                ],
            ], 201);
		}
		return $connection->getHtmlPage(true);
	}
	/**
	 * @return string
	 */
	public static function getConnectorsListHtml(): string{
		$buttons = QMButton::toButtons(GetConnectorsController::getDataSources());
		$view = view('ifttt.list', ['buttons' => $buttons]);
		return HtmlHelper::renderView($view);
	}
	protected function isGetRequest(){
		return $this->request->getMethod() === 'GET';
	}
}
