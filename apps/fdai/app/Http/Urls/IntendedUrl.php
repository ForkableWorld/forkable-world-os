<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Urls;
use App\Buttons\Auth\AuthButton;
use App\Buttons\States\OnboardingStateButton;
use App\DataSources\QMClient;
use App\DataSources\QMConnector;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\NoEmailAddressException;
use App\Exceptions\UnauthorizedException;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\OAClient;
use App\Parameters\StateParameter;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseClientSecretProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Storage\Memory;
use App\Types\QMStr;
use App\Utils\Env;
use App\Utils\QMCookie;
use App\Utils\Subdomain;
use App\Utils\UrlHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Session\Store;
use Redirect;
class IntendedUrl {
	public const INTENDED_URL = 'intended_url';
	/**
	 * @return string
	 */
	public static function fallback(): string{
		$params = qm_request()->query();
		if($clientId = BaseClientIdProperty::fromRequest(false)){
			if($clientId !== BaseClientIdProperty::CLIENT_ID_QUANTIMODO){
				$params[BaseClientIdProperty::NAME] = $clientId;
			}
		}
		$params['loggingIn'] = true;
		return OnboardingStateButton::url($params);
	}
	/**
	 * @param $name
	 * @return null|string
	 */
	public static function getQueryParam($name): ?string{
		$url = self::get();
		if(!$url){
			return null;
		}
		return UrlHelper::getQueryParam($name, $url);
	}
	/**
	 * @param string $name
	 * @return string
	 */
	public static function getParam(string $name): ?string{
		$redirectUrl = IntendedUrl::fromQuery();
		if(!$redirectUrl){
			return null;
		}
		return UrlHelper::getParam($name, $redirectUrl);
	}
	/**
	 * @return mixed|null|string
	 */
	public static function getFromCookie(): mixed{
		$url = $_COOKIE["intended_url"] ?? null;
		if($url && !str_starts_with($url, "http")){
			$encrypted = $url;
			$decrypted = QMCookie::decrypt('intended_url', $url);
			$exploded = explode('|', $decrypted);
			foreach($exploded as $part){
				if(str_starts_with($part, 'http')){
					$url = $part;
					break;
				}
			}
		}
		return $url;
	}
	public static function setToCurrent(): void{
		if(QMRequest::isPost()){
			$url = QMRequest::getReferrer();
		} else{
			$url = QMRequest::current();
		}
		self::validateAndSet($url);
	}
	/**
	 * @param string|null $url
	 * @return void
	 */
	public static function validateAndSet(string $url): void{
		self::validate($url);
		//self::$url = $url;
		self::set($url);
	}
	/**
	 * @param string $url
	 * @return string
	 * @throws UnauthorizedException
	 * @throws ClientNotFoundException
	 */
	public static function addUserInfoIfNecessary(string $url): string{
		if(!str_starts_with($url, QMRequest::origin())){
			$clientId = BaseClientIdProperty::fromRequestDirectly(false);
			if(!$clientId){
				$sub = Subdomain::getSubDomainIfDomainIsQuantiModo($url);
				if($sub){$clientId = BaseClientIdProperty::CLIENT_ID_QUANTIMODO;}
			}
			if($clientId){
				if($user = QMAuth::getQMUser()){
					if(!QMStr::contains($url, BaseAccessTokenProperty::URL_PARAM_NAME)){
						$url = UrlHelper::addParams($url,
							[BaseAccessTokenProperty::URL_PARAM_NAME => $user->getOrSetAccessTokenString($clientId)]);
					}
					$url = UrlHelper::addParams($url, [
						// Why do we need this? 'quantimodoUserId' => $user->getId()
					]);
				}
			}
		}
		return $url;
	}
	/**
	 * @return string|null
	 */
	public static function fromCookie(): ?string{
		$synonyms = self::getSynonyms();
		foreach($synonyms as $synonym){
			$url = QMCookie::getCookieValue($synonym);
			if($url){
                QMLog::info(" Got ".$url ." QMCookie::getCookieValue($synonym)");
				return $url;
			}
		}
		return null;
	}
	/**
	 * @param string $url
	 * @return string
	 */
	public static function addConnectedMessageIfNecessary(string $url): string{
		if($c = QMConnector::getCurrentlyImportingConnector()){
			$url = UrlHelper::addParams($url, ['message' => "Connected " . $c->displayName . "!"]);
		}
		return $url;
	}
	/**
	 * @param string $url
	 * @return string
	 */
	public static function addClientSecretIfNecessary(string $url): string{
		if($s = BaseClientSecretProperty::fromRequest()){
			$url = UrlHelper::addParam($url, QMClient::FIELD_CLIENT_SECRET, $s);
		}
		return $url;
	}
	public static function get(): ?string{
		// This doesn't get removed in tests if(self::$url){return self::$url;} // Avoid recursion in addParams
		$url = self::validGet(self::fromQuery());
		if(!$url){
			$url = StateParameter::getValueFromStateParam(self::INTENDED_URL);
		}
		if(!$url){
			$currentUrlGenerator = url();
			$previous = $currentUrlGenerator->previous();
			if($previous){
				$intended = UrlHelper::getParam('intended_url', $previous);
				if($intended){
					$url = $intended;
				}
			}
		}
		if(!$url){
			/** @var Store $session */
			$session = session();
			$intended = self::fromSession();
			if($intended !== Env::getAppUrl()){
				$url = $intended;
			}
		}
		if(!$url){
			/** @var Store $session */
			$session = session();
			$url = $session->get(self::INTENDED_URL);
		}
		if(!$url){
			$url = self::getFromCookie();
		}
		if(!$url){
			$url = self::validGet(self::fromReferrer());
		}
		if(!$url){
			$url = self::validGet(self::fromCookie());
		}

		if(QMRequest::getParam(['popup', 'close'])){
			$url = QMConnector::getWindowCloseUrl();
		}
		if($url && !str_starts_with($url, 'http')){
			$explode = explode('|', $url);
			if(isset($explode[1])){
				if(!str_starts_with($explode[1], 'http')){
					QMLog::error("Invalid IntendedUrl: $url");
				} else {
					$url = $explode[1];
				}
			} else {
				ConsoleLog::error("Invalid IntendedUrl: $url"); // Causes infinite loop if we call bugsnag
				$url = null;
			}
		}
		return $url;
	}
	/**
	 * @return string
	 */
	public static function fromQuery(): ?string{
		foreach(self::getSynonyms() as $synonym){
			$url = QMRequest::getQueryParam($synonym);
			if($url && self::validSet($url)){
				return $url;
			}
		}
		return null;
	}
	public static function getSynonyms(): array{
		return [
			self::INTENDED_URL,
			'intendedUrl',
			'redirect_uri',
			'redirectToUrl',
			'redirectUrl',
			'redirectTo',
			'redirectToUrl',
			'afterLoginGoTo',
			//'state'  // TODO: why are we getting this from state?
		];
	}
	public static function validate(string $url = null){
		if(!self::validSet($url)){
			le("Not a valid redirect: $url");
		}
	}
	/**
	 * @param string|null $url
	 * @return null
	 */
	public static function validSet(string $url = null): ?string{
		if(!$url){
			return null;
		}
		$uri = $_SERVER['REQUEST_URI'] ?? false;
		if($url === Env::getAppUrl() && $uri && $uri = '/'){
			return null;
		} // Default
		if(AuthButton::isAuthUrl($url) && stripos($url, UrlHelper::generateApiUrl()) !== false){
			QMLog::error("Not using redirect ($url) because it's an auth page containing the APP_URL: " .
				UrlHelper::generateApiUrl());
			return null;
		}
		$url = str_replace(" ", "+", $url); // Needed if they put a space in the state param
		if(!UrlHelper::isUrl($url)){
			QMLog::error("Not using redirect ($url) because it's not a valid url!");
			return null;
		}
		if(stripos($url, 'http') !== 0){
			QMLog::error("Invalid redirect url $url from session!");
			return null;
		}
		if(stripos($url, 'broadcasting/auth') !== false){
			QMLog::error("Invalid redirect url $url!");
			return null;
		}
		// Why do we require a query?
//		$noQ = QMStr::before("?", $url, $url);
//		if($noQ === \App\Utils\Env::getAppUrl()){
//			QMLog::error("Invalid redirect url $url!");
//			return null;
//		}
		return $url;
	}
	/**
	 * @param string|null $url
	 * @return string|null
	 */
	private static function validGet(string $url = null): ?string{
		$url = static::validSet($url);
		if(!$url){
			return null;
		}
		$current = QMRequest::withoutQuery();
		if(stripos($url, $current) === 0){
			return null;
		}
		if(stripos($current, $url) === 0){
			return null;
		}
		return $url;
	}
	public static function forget(){
		self::set(null);
	}
	/**
	 * @param string|null $url
	 * @return void
	 */
	private static function set(?string $url): void{
		QMLog::info("Redirect::setIntendedUrl($url);
		Memory::set(static::class, $url);
		QMCookie::setCookie(static::INTENDED_URL, $url);
		QMCookie::setCookie('final_callback_url', $url);");
		Redirect::setIntendedUrl($url);
		$session = \session();
		$intended = self::fromSession();
		Memory::set(static::class, $url);
		QMCookie::setCookie(static::INTENDED_URL, $url);
		QMCookie::setCookie('final_callback_url', $url);
	}
	/**
	 * @param Store $session
	 * @return string
	 */
	public static function fromSession(): ?string {
		/** @var Store $session */
		$session = session();
		$intended = $session->get('url.intended');
		return $intended; // don't pull or it deletes it
	}
	/**
	 * @param string $url
	 * @return string
	 */
	private static function addPhysicianClientParamsIfNecessary(string $url): string{
		$params = QMAuth::getLoginRequestParams();
		if(isset($params[OAClient::FIELD_REDIRECT_URI])){
			try {
				$url = UrlHelper::addParams($url, QMAuth::getQMUser()->getPhysicianClientParams());
			} catch (InvalidEmailException|NoEmailAddressException|UnauthorizedException $e) {
				QMLog::error("Could not add physician client params to redirect url: " . $e->getMessage());
			}
		}
		return $url;
	}
	/**
	 * @return string|null
	 */
	private static function fromReferrer(): ?string{
		$syn = self::getSynonyms();
		return QMRequest::getParamFromReferrer($syn);
	}
	/**
	 * @return RedirectResponse
	 */
	public static function getRedirectResponse(): RedirectResponse{
		$url = self::getRedirectUrl();
		return redirect()->away($url);
	}
	/**
	 * @param array $params
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public static function redirect(array $params = []){
		return UrlHelper::redirect(self::getRedirectUrl($params));
	}
	/**
	 * @param string $url
	 * @return string
	 */
	private static function addParams(string $url): string{
		$url = self::addClientSecretIfNecessary($url);
		$url = BaseClientIdProperty::addToUrlIfNecessary($url);
		$url = self::addConnectedMessageIfNecessary($url);
		try {
			$url = self::addUserInfoIfNecessary($url);
		} catch (UnauthorizedException $e) {
			QMLog::error("Could not add user info to redirect url: " . $e->getMessage());
		}
		//TODO: What's this for? $url = self::addPhysicianClientParamsIfNecessary($url);
		return $url;
	}
	/**
	 * @param array $params
	 * @return string
	 */
	private static function getRedirectUrl(array $params = []): string{
		$url = self::get();
		if(!$url){
			$url = self::fallback();
		}
		$url = self::addParams($url);
		$url = UrlHelper::addParams($url, $params);
		if(!str_starts_with($url, 'http')){
			$fallback = self::fallback();
			QMLog::error("Invalid redirect url $url! using fallback $fallback");
		}
		return $url;
	}
}
