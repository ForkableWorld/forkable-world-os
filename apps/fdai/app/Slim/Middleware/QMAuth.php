<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Middleware;
use App\Buttons\Auth\AuthButton;
use App\Buttons\Auth\LoginButton;
use App\DataSources\Connectors\GoogleLoginConnector;
use App\Exceptions\AccessTokenExpiredException;
use App\Exceptions\BadRequestException;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\InvalidClientException;
use App\Exceptions\QMException;
use App\Exceptions\UnauthorizedException;
use App\Http\Urls\IntendedUrl;
use App\Logging\QMLog;
use App\Models\OAAccessToken;
use App\Models\OAClient;
use App\Models\User;
use App\Parameters\StateParameter;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseClientSecretProperty;
use App\Properties\OAClient\OAClientClientSecretProperty;
use App\Properties\User\UserPasswordProperty;
use App\Properties\User\UserProviderIdProperty;
use App\Properties\User\UserUserEmailProperty;
use App\Properties\User\UserUserLoginProperty;
use App\Slim\Configuration\RouteConfiguration;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\Model\Auth\QMRefreshToken;
use App\Slim\Model\GoogleAnalyticsEvent;
use App\Slim\Model\User\QMUser;
use App\Slim\QMSlim;
use App\Slim\View\Request\QMRequest;
use App\Utils\AppMode;
use App\Utils\QMCookie;
use App\Utils\RequestRateLimiter;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;
class QMAuth {
	/**
	 * Error constants.
	 */
	public const ERROR_NOT_AUTHENTICATED = 'Not authenticated';
	public const ERROR_NOT_ADMIN = 'Not an admin';
	public const LOGIN_FAILURE_MESSAGE = "Hmm.  I can't find a user with those credentials. Please try again.  If you don't have an account yet, click the sign up link above.  If you forgot your password, click the link below.";
	public const LOGIN_REQUEST_PARAMS = "LOGIN_REQUEST_PARAMS";
	/**
	 * @var QMUser|null|false
	 */
	private static $user = null;
	/**
	 * @param string $name
	 * @return mixed|null
	 */
	public static function getLoginRequestParam(string $name){
		$params = self::getLoginRequestParams();
		return $params[$name] ?? null;
	}
	public static function getLoginRequestParams(): array{
		return session(self::LOGIN_REQUEST_PARAMS, []);
	}
	/**
	 * @return string
	 */
	private static function getRecallerCookieName(): string{
		return \Auth::getRecallerName();
	}
	private static function recallerCookieValue(): ?string{
		return QMCookie::getCookieValue(self::getRecallerCookieName());
	}
	/**
	 * @return string
	 */
	private static function getLoginCookieName(): string{
		$guard = self::getGuard();
		$name = $guard->getName();
		return $name;
	}
	public static function loginCookieValue(): ?string{
		return QMCookie::getCookieValue(self::getLoginCookieName());
	}
	/**
	 * @return User
	 */
	public static function getUserByUserNameAndPassword(): ?User{
		$r = qm_request();
		$input = $r->input();
		if(isset($input['user']) && is_array($input['user'])){$input = $input['user'];}
		$referrerParams = QMRequest::getReferrerParams();
		$input = array_merge($referrerParams, $input);
		if(!$input){return null;}
		$user = null;
        $name = UserUserLoginProperty::pluck($input);
		$plainText = UserPasswordProperty::pluck($input);
		if(!$plainText){return null;}
        if($name && str_contains($name, "@")) {
            $email = $name;
            $name = null;
        } else {
            $email = UserUserEmailProperty::pluck($input);
        }
		if($name){
			if($name === "demo"){
				return User::demo();
			}
			$user = User::whereUserLogin($name)->first();
		} elseif($email){
			$IHUserQB = User::whereUserEmail($email);
			$user = $IHUserQB->first();
		}
		if($user){
			if(UserPasswordProperty::pluckAndCheck($input, $user)){
				return $user;
			}
		}
		return null;
	}
	/**
	 * @param string|null $scopes
	 * @param bool $throwException
	 * @return QMUser|null
	 * @throws AccessTokenExpiredException
	 * @throws AuthorizationException
	 * @throws UnauthorizedException
	 */
	public static function getQMUser(string $scopes = null, bool $throwException = false): ?QMUser{
		if($u = self::getQMUserIfSet()){
			return $u;
		}
		if(self::$user === false){
			return null;
		}
		if(!AppMode::isApiRequest()){
			return null;
		}
		if(QMRequest::getBool('logout')){
			return null;
		}
		$u = self::byDemoTokenOrClient();
		if(!$u){
			//$u = self::byStateParam(); // What is this for?
		} // Need to do this before cookie check
		if(!$u){
			$u = self::byAccessToken($scopes);
		}
		if(!$u){
			$u = self::getUserByUserNameAndPassword();
		}
		if(!$u){
			$u = self::byAppSecret();
		}
		if(!$u){
			$u = self::byPublicToken();
		}
		if(!$u){
			$u = Auth::user();
		}
		if(!$u){
			$loginCookie = self::loginCookieValue();
			$recaller = self::recallerCookieValue();
			if($loginCookie){
				$explode = explode("|", $loginCookie);
				$userId = $explode[1] ?? $explode[0] ?? null;
				if($userId && is_numeric($userId)){
					$u = User::findInMemoryOrDB($userId);
				}
			}
		}
		if($u){
			if($scopes){
				self::validateAdminScope($scopes, $u);
			}
			if(!$u->client_id){
                $u->client_id = BaseClientIdProperty::fromRequest(false);
            }
		}
		if($throwException && !$u){
			self::throwUnauthorizedException();
		}
		if($u){
			self::setUser($u->getQMUser());
			// Don't setUserLoggedIn when we're just using an access token.
			// Do it specifically in the controllers where we need it.
			// self::setUserLoggedIn($u, true);
		} else{
			self::setUser(false);
		}
		return static::getQMUserIfSet();
	}
	/**
	 * @return User|null
	 * @throws UnauthorizedException
	 */
	private static function byAppSecret(): ?User{
        $clientId = BaseClientIdProperty::fromRequest(false);
        $clientSecret = OAClientClientSecretProperty::fromRequest(false);
        $input = qm_request()->input();
        if($clientId){$input[BaseClientIdProperty::NAME] = $clientId;}
        if($clientSecret){$input[BaseClientSecretProperty::NAME] = $clientSecret;}
        if(!$input){return null;}
        try {
            $client = OAClient::authorizeBySecret($input);
        } catch (InvalidClientException $e) {
            return null;
        }
        $clientUserId = UserProviderIdProperty::fromRequest(false);
		if(!$clientUserId){
            return $client->getUser();
			//return null;
		}
		return User::findByClientUserId($clientUserId, $client->getId());
	}
	/**
	 * @param string|null $scopes
	 * @return QMUser|null
	 * @throws AccessTokenExpiredException
	 * @throws AuthorizationException
	 */
	private static function byAccessToken(string $scopes = null): ?User{
		if($at = QMAccessToken::fromRequest($scopes)){
			return $at->getUser();
		}
		return null;
	}
	private static function byDemoTokenOrClient(): ?User{
		$isDemo = BaseClientIdProperty::isDemo();
		if($isDemo || QMAccessToken::isDemo()){
			$clientId = BaseClientIdProperty::fromRequest(false);
			if(!$clientId){
				BaseClientIdProperty::setInMemory('demo');
			}
			return User::demo();
		}
		return null;
	}
	/**
	 * @return bool
	 * @throws AccessTokenExpiredException
	 */
	public static function isLoggedInAdmin(): bool{
		$hasUser = Auth::hasUser();
		if(!$hasUser){
			return false;
		}
		$user = Auth::user();
		return $user->isAdmin();
	}
	/**
	 * @return bool
	 * @throws AccessTokenExpiredException
	 * @throws AuthorizationException
	 * @throws UnauthorizedException
	 */
	public static function isAdmin(): bool{
        try {
            $u = self::getQMUser();
        } catch (BadRequestException $e) {
            return false;
        }
		if(!$u){
			return false;
		}
		return $u->isAdmin();
	}
	/**
	 * @throws UnauthorizedException
	 */
	public static function isAdminOrException(){
		if(!QMAuth::isAdmin()){
			throw new UnauthorizedException("Must be an admin to do this");
		}
	}
	/**
	 * Get authenticated user. Throws exception if user is not authenticated.
	 * @return QMUser
	 * @throws UnauthorizedException
	 */
	public static function getAuthenticatedUserOrThrowException(): QMUser{
		if(!Auth::user()){
            $user = self::$user;
            if($user){
                Auth::login($user->getUser());
            } else {
                throw new UnauthorizedException(self::ERROR_NOT_AUTHENTICATED);
            }
		}
		return self::getQMUserIfSet();
	}
	/**
	 * @param string $scopes
	 * @param bool $haltIfNotAuthenticated
	 * @return Closure
	 */
	public static function authenticate(string $scopes = '', bool $haltIfNotAuthenticated = true): Closure{
		return function() use ($scopes, $haltIfNotAuthenticated){
			RequestRateLimiter::rateLimit();
			self::getQMUser($scopes);
            $user = self::getQMUserIfSet();
            if(!$user){
                $user = Auth::user();
            }
			if(!$user){
				self::handleUnauthorizedRequest($haltIfNotAuthenticated);
			}
		};
	}
	/**
	 * @param bool $haltIfNotAuthenticated
	 */
	private static function handleUnauthorizedRequest(bool $haltIfNotAuthenticated = true){
		$app = QMSlim::getInstance();
		$route = $app->router()->getCurrentRoute();
		$routeName = $route->getPattern();
		$eventCategory = 'Unauthorized Request';
		$eventAction = QMRequest::requestUri();
		$eventValue = 1;
		$userId = $app->request->getIp();
		try {
			GoogleAnalyticsEvent::logEventToGoogleAnalytics($eventCategory, $eventAction, $eventValue, $userId,
			                                                BaseClientIdProperty::fromRequest(false));
		} catch (UnauthorizedException $e) {
			//le($e);
		}
		QMLog::warning('Could not authenticate user requesting route: ' . $routeName, [
			'request' => $app->request,
			'route' => $app->router()->getCurrentRoute(),
		]);
		if($haltIfNotAuthenticated){
			$app->haltJson(QMException::CODE_UNAUTHORIZED, ['message' => self::ERROR_NOT_AUTHENTICATED]);
		}
	}
    public static function login(User $user){
		self::setUserLoggedIn($user, true);
    }
	/**
	 * @return void
	 */
	public static function logoutIfParamSet(): void{
		if(QMRequest::isLoggingOut()){
			$u = Auth::user();
			QMAuth::logout('logout param set');
			$user = QMAuth::getUser();
			if($user){
				$user = QMAuth::getUser();
				le('User still logged in after logout');
			}
		}
	}
	private static function setUserClientId(QMUser $u){
		if(empty($u->clientId)){
			$u->clientId = BaseClientIdProperty::fromRequest(false);
		}
	}
	/**
	 * @return QMUser|null
	 * @throws UnauthorizedException
	 */
	private static function byPublicToken(): ?User{
		$publicToken = QMRequest::getQueryParam('publicToken');
		$clientId = BaseClientIdProperty::fromRequest(false);
		if($publicToken && $clientId){
            try {
                if ($u = QMUser::findByTokenString($publicToken)) {
                    return $u;
                }
            } catch (UnauthorizedException $e) {
                QMLog::error(__METHOD__.": ".$e->getMessage());
            }
        }
		$clientUserId = UserProviderIdProperty::fromRequest(false);
		if($clientId && $clientUserId){
			if(User::findByClientUserId($clientUserId, $clientId)){
				$clientUserId = UserProviderIdProperty::fromRequest(false);
				throw new BadRequestException("You already created this user with id $clientUserId. " .
					"Please add the user's publicToken to window.QuantiModoIntegration.options.publicToken. " .
					"See https://app.quantimo.do/account/apps for more information.");
			}
            $r = qm_request();
            $input = $r->input();
            if(!$input){
                $input = QMRequest::body();
            }
            if(!$input){
                $input = QMRequest::getQuery();
            }
            $input = QMRequest::getInput();
            try {
                $secret = OAClientClientSecretProperty::fromRequest(false);
                if($secret){
                    $client = OAClient::fromRequest();
                    if($client && $client->getClientSecret() === $secret){
                        $u = User::whereClientId($clientId)
                            ->where(User::FIELD_PROVIDER_ID, $clientUserId)
                            ->first();
                    }
                    if($u){
                        return $u;
                    }
                }
                $u = User::createUserFromClient($input);
            } catch (\Illuminate\Database\QueryException $e) {
                $u = User::whereProviderId($input);
                le($e);
            }
		}
		return $u ?? null;
	}

    /**
     * @param QMUser|null|false $user
     */
    public static function setUser($user): void
    {
        if(self::$user && !$user){
            Auth::logout();
        }
		if($user && $user->isSystem()){
			le("Why are we logging in as system user?");
		}
        self::$user = $user;
    }

    private static function byStateParam(): ?QMUser{
		$userId = StateParameter::getUserIdFromStateParam();
		if($userId && QMRequest::urlContains('/connect?')){
			//$user = QMUser::getByIdIncludingMemcached($userId, $getAccessToken, $tokenArray);
			$u = QMUser::findWithToken($userId);
		}
		return $u ?? null;
	}
	/**
	 * @param string $message
	 * @throws UnauthorizedException
	 */
	public static function throwUnauthorizedException(string $message = self::ERROR_NOT_AUTHENTICATED){
		$a = QMSlim::getInstance();
		if(!$a){
			throw new UnauthorizedException($message);
		}
		$a->haltJson(QMException::CODE_UNAUTHORIZED, [
			'message' => $message,
			'status' => "ERROR",
			'success' => false,
		]);
	}
	public static function canSeeOtherUsers(): bool{
		$u = self::getUser();
		if(!$u){return false;}
		return $u->canSeeOtherUsers();
	}
	/**
	 * @return null|QMUser
	 * @throws AccessTokenExpiredException
	 * @throws AuthorizationException
	 * @throws UnauthorizedException
	 */
	public static function getUserOrSendToLogin(): ?QMUser{
		$user = self::getQMUser();
		if(!$user){
			LoginButton::redirect();
		}
		return $user;
	}
	public static function isAdminOrSendToLogin(): bool{
		if(!self::isAdmin()){
			self::logoutAndRedirectToLogin(__METHOD__ . ': !self::isAdmin()');
			return false;
		}
		return true;
	}
	/**
	 * @return Closure
	 */
	public static function authenticateAdmin(): Closure{
		return static function(){
			QMAuth::setUserLoggedIn(self::getQMUser(), true);
			if(!QMAuth::getQMUserIfSet()){
				throw new UnauthorizedException(self::ERROR_NOT_AUTHENTICATED);
			}
			if(!QMAuth::getQMUserIfSet()->administrator){
				throw new UnauthorizedException(self::ERROR_NOT_ADMIN);
			}
		};
	}

    /**
     * @param string|null $scopes
     * @param User|QMUser $user
     * @throws UnauthorizedException
     */
	protected static function validateAdminScope(string $scopes, $user): void{
		if(str_contains($scopes, RouteConfiguration::SCOPE_SUPER_ADMIN) && !$user->isAdmin()){
			throw new UnauthorizedException("Not an admin");
		}
	}
	public static function loginMike(){
		self::setUserLoggedIn(User::mike(), true);
	}
	/**
	 * @param null|QMUser|bool|User $user
	 * @return null|QMUser
	 */
	public static function setUserLoggedIn($user, bool $remember){
		if($user instanceof User){
			$user = $user->getQMUser();
		}
		self::setUser($user);
		if(!$user){
			le("no user!");
		}
		$user->addToMemory();
		$recallerName = self::getRecallerCookieName();
		if(Auth::hasUser() && Auth::user()->getId() === $user->getId()){
			Auth::user()->logInfo(__METHOD__ . ": already set in Auth::user()");
		} else{
			$l = $user->l();
			if(self::recallerCookieValue()){ // Don't keep recycling the remember token
				Auth::setUser($l);
			} else{
				Auth::login($l, $remember);
				$recaller = self::recallerCookieValue();
				$auth = auth();
				$remembered = $auth->viaRemember();
				if($remember && !$recaller){
					if(!AppMode::isSlimHttpRequest()){ 
						// Doesn't work in slim.  Just use tokens for API requests and
						// got through laravel for anything that needs persistent cookie sessions
						QMLog::errorOrInfoIfTesting("No recaller cookie after login");
					}
				}
			}
			try {
				$user->updateLastLoginAtIfNecessary();
			} catch (Throwable $exception) {
				ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($exception);
			}
		}
        self::setUser($user);
		return $user;
	}
	/**
	 * @param int $dataOwnerUserId
	 * @return bool
	 * @throws AccessTokenExpiredException
	 * @throws AuthorizationException
	 * @throws UnauthorizedException
	 */
	public static function loggedInUserIsAuthorizedToAccessAllDataForUserId(int $dataOwnerUserId): bool{
		if(self::isAdmin()){
			return true;
		}
		$dataOwner = User::findInMemoryOrDB($dataOwnerUserId);
		if($dataOwner->getShareAllData()){
			return true;
		}
		$loggedInUser = self::getUser();
		if($loggedInUser && $dataOwnerUserId === $loggedInUser->getId()){
            return true;
        }
        if($clientId = BaseClientIdProperty::fromRequest(false)){
            try {
                OAClient::authorizeBySecret(qm_request()->input());
                if($dataOwner->client_id === $clientId){return true;}
            } catch (InvalidClientException $e) {
            }
            $t = $dataOwner->oa_access_tokens->where('client_id', $clientId)
                ->sortByDesc(OAAccessToken::FIELD_EXPIRES)->first();
            if($t && $t->isValid()){
                return true;
            }
        }
        return false;
	}
	/**
	 * @param bool $throwException
	 * @return int
	 * @throws UnauthorizedException
	 */
	public static function id(bool $throwException = false): ?int{
		if($u = QMAuth::getQMUser()){
			return $u->getId();
		}
		if($throwException){
			throw new UnauthorizedException("Please log in");
		}
		return null;
	}
	/**
	 * @param string|null $scopes
	 * @return User|null
	 */
	public static function getUser(string $scopes = null): ?User{
		if(Auth::hasUser()){
			return Auth::user();
		}
		$u = self::getQMUser($scopes);
		if($u){
			$l = $u->l();
			return $l;
		}
		return null;
	}
	/**
	 * @return QMUser|null
	 */
	public static function getAdminOrLogout(): ?QMUser{
		$u = self::getQMUser();
		if(!$u){
			return null;
		}
		if(!$u->isAdmin()){
			self::logout(__FUNCTION__ . ": Logging out because not admin!");
			return null;
		}
		return $u;
	}
	public static function logout(string $reason){
		$isLoggedIn = self::$user !== null || 
		              Auth::hasUser() || 
		              Auth::check(); // Avoid redundant logging
		if(!$isLoggedIn){
			$isLoggedIn = Auth::user();
		}
		foreach($_COOKIE as $k => $v){
			QMCookie::deleteCookie($k);
		}
		if(!$isLoggedIn){
			return;
		}
		QMLog::info("Logging out because: $reason");
		self::setUser(null);
		// DON"T DO THIS IntendedUrl::unset();
		try {
			Auth::logout();
			$guard = self::getGuard();
			$guard->logout();
		} catch (Throwable $e) { // Sometimes Auth isn't defined
			QMLog::debug(__METHOD__.": ".$e->getMessage());
		}
	}
	/**
	 * Handle the creation of access token, also issue refresh token if supported / desirable.
	 * @param string $clientId client identifier related to the access token.
	 * @param int $userId user ID associated with the access token
	 * @param string $scopes
	 * @param int|null $expiresInSeconds
	 * @param bool $includeRefreshToken if true, a new refresh_token will be added to the response
	 * @return array
	 * @internal param string $scope OPTIONAL scopes to be stored in space-separated string.
	 * @see http://tools.ietf.org/html/rfc6749#section-5
	 * @ingroup oauth2_section_5
	 */
	public static function getOrCreateAccessAndRefreshTokenArrays(string $clientId, int $userId,
		string $scopes = 'readmeasurements writemeasurements', int $expiresInSeconds = null,
		bool $includeRefreshToken = true): array{
		$clientId = BaseClientIdProperty::replaceWithQuantiModoIfAlias($clientId);
		$t = QMAccessToken::getOrCreateToken($clientId, $userId, $scopes, null);
		$arr['expires_in'] = $arr['expiresIn'] = $t->getExpiresInSeconds();
		$arr['expiresAt'] = $t->getExpiresAt();
		$arr['accessToken'] = $t->getAccessTokenString();
		if($includeRefreshToken){
			$r = QMRefreshToken::getOrCreateRefreshToken($clientId, $userId, $scopes, $expiresInSeconds);
			$arr['refreshToken'] = $r->getRefreshToken();
		}
		return $arr;
	}
	/**
	 * @param int $userId
	 * @return User
	 */
	public static function loginUsingId(int $userId): User{
		/** @var User $user */
		$user = Auth::loginUsingId($userId);
		QMAuth::setUserLoggedIn($user->getQMUser(), true);
		return $user;
	}
	/**
	 * @param string $reason
	 * @return RedirectResponse
	 */
	public static function logoutAndRedirectToLogin(string $reason): RedirectResponse{
		self::logout($reason);
		return AuthButton::getRedirect();
	}
	/**
	 * @param int $ID
	 * @return false
	 */
	public static function currentUserIs(int $ID): bool{
		$u = self::getQMUser();
		if(!$u){
			return false;
		}
		return $ID === $u->getId();
	}
	/**
	 * @param int $ID
	 * @throws AccessTokenExpiredException
	 * @throws AuthorizationException
	 * @throws UnauthorizedException
	 */
	public static function exceptionUnlessCurrentUserMatchesIdOrIsAdmin(int $ID){
		if(self::isAdmin()){
			return;
		}
		if(self::currentUserIs($ID)){
			return;
		}
		throw new UnauthorizedException();
	}
	public static function getQMUserIfSet(): ?QMUser{
		if(Auth::hasUser()){
			$u = Auth::user();
			$u->addToMemory(); // Sometimes this gets removed from memory during tests but it's still on QMAuth::$user
			return $u->getQMUser();
		}
        $QMUser = self::$user;
        if($QMUser === false){return null;}
		if($QMUser){$QMUser->addToMemory();}
        return $QMUser;
	}
	public static function authenticateByGoogle(): ?QMUser{
		if($u = Auth::user()){
			return $u->getQMUser();
		}
		try {
			if($u = GoogleLoginConnector::loginByRequest()){
				self::setUserLoggedIn($u, true);
			}
		} catch (UnauthorizedException | ClientNotFoundException $e) {
			QMLog::debug(__METHOD__.": ".$e->getMessage());
		}
		return $u;
	}
	public static function saveLoginRequestParams(): void{
		session([self::LOGIN_REQUEST_PARAMS => $_GET]);
		if($url = IntendedUrl::fromQuery()){
			if(!IntendedUrl::validSet($url)){
				QMLog::error("Invalid intended url: $url 
				from QMRequest::getParam(IntendedUrl::INTENDED_URL)");
				return;
			}
            try {
                IntendedUrl::validateAndSet($url);
            } catch (\Exception $e) {
                ExceptionHandler::dumpOrNotify($e);
            }
		}
	}
	/**
	 * @throws UnauthorizedException
	 */
	public static function getUserId(): ?int{
		$u = self::getQMUser();
		if(!$u){
			return null;
		}
		//if(!AppMode::isApiRequest()){return null;}
		return $u->getId();
	}
	/**
	 * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
	 */
	private static function getGuard(): \Illuminate\Contracts\Auth\StatefulGuard|\Illuminate\Contracts\Auth\Guard{
		return Auth::guard('web');
	}
}
