<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources;
use App\DataSources\Connectors\GoogleLoginConnector;
use App\DataSources\Connectors\QuantiModoConnector;
use App\Exceptions\BadRequestException;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\InvalidUsernameException;
use App\Exceptions\UnauthorizedException;
use App\Models\Connection;
use App\Models\User;
use App\Parameters\StateParameter;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\User\UserUserEmailProperty;
use App\Properties\User\UserUserLoginProperty;
use App\Slim\Controller\Connector\ConnectorConnectedResponse;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Controller\Connector\ConnectorRedirectResponse;
use App\Slim\Controller\Connector\ConnectorResponse;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\UrlHelper;
use Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Service\ServiceInterface;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Token\AbstractToken;
use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth1\Token\StdOAuth1Token;
use OAuth\OAuth2\Token\StdOAuth2Token;
abstract class OAuthConnector extends QMConnector implements ServiceInterface{
    protected $stdOAuthToken;
    public $connectorClientId;
    protected $connectorClientSecret;
    public $mobileConnectMethod = 'oauth';
    public $oauth = true;
    public $scopes;
    public static $OAUTH_SERVICE_NAME;
    public static array $SCOPES = [];
	/**
     * @param string $connectorClientId
     * @return string
     */
    public function setConnectorClientId(string $connectorClientId): string{
        return $this->connectorClientId = $connectorClientId;
    }
    /**
     * @return string
     */
    public static function getClientSecret(): ?string{
        $uppercase = QMStr::toScreamingSnakeCase(static::NAME);
        $key = 'CONNECTOR_'.$uppercase.'_CLIENT_SECRET';
        $val = Env::get($key);
        if(empty($val)){le("No $key!");}
        return $val;
    }
	public static function clientID(): ?string{
		$uppercase = QMStr::toScreamingSnakeCase(static::NAME);
		$key = 'CONNECTOR_'.$uppercase.'_CLIENT_ID';
		$val = Env::get($key);
		if(empty($val)){le("No $key!");}
		return $val;
	}
    /**
     * @return string
     */
    public function getConnectorClientId(): ?string{
        if(empty($this->connectorClientId)){
	        $connectorName = $this->name;
			$uppercase = QMStr::toScreamingSnakeCase($connectorName);
	        $connectorClientId = Env::getClientIdByConnectorName($connectorName);
	        if(empty($connectorClientId)){
				le("No CONNECTOR_{$uppercase}_CLIENT_ID!");
			}
	        $this->setConnectorClientId($connectorClientId);
        }
        return $this->connectorClientId;
    }
    /**
     * @return string
     */
    public function getOrSetConnectorClientSecret(): ?string {
        if($secret = $this->connectorClientSecret){
            return $secret;
        }
        $secret = static::getClientSecret();
        return $secret;
    }
    /**
     * @param StdOAuth2Token|TokenInterface|bool|string $stdOAuthToken
     * @return StdOAuth2Token
     */
    public function setToken($stdOAuthToken){
        $maybeSerialized = $stdOAuthToken;
        if(is_string($stdOAuthToken) && stripos($stdOAuthToken, 'oauth') !== false){
            /** @noinspection UnserializeExploitsInspection */
            $stdOAuthToken = unserialize($stdOAuthToken);
        }
        if($stdOAuthToken){
            $this->stdOAuthToken = $stdOAuthToken;
            $this->credentialsArray = ['token' => $stdOAuthToken];
        }else{
            $this->logError("No StdOAuthToken to set!", ['provided_token' => $maybeSerialized]);
        }
        return $stdOAuthToken;
    }
	/** Tries to refresh token if it's expired or about to expire*
	 * @param TokenInterface $token
	 * @return AbstractToken|null
	 * @throws TokenNotFoundException
	 */
	abstract public function refreshAccessToken(TokenInterface $token): TokenInterface;
	/**
	 * @return bool|StdOAuth2Token|StdOAuth1Token
	 * @throws TokenNotFoundException
	 */
    protected function getOrRefreshToken(): AbstractToken {
        $token = $this->getToken();
		$expired = $token->isExpired();
		//$expired = true;
        if($expired){
	        return $this->refreshAccessToken($token);
        }
        return $token;
    }
	/**
	 * @return AbstractToken
	 * @throws TokenNotFoundException
	 */
    public function getToken(): AbstractToken {
		if($this->stdOAuthToken){
			return $this->stdOAuthToken;
		}
        return $this->getTokenStorage()->retrieveAccessToken();
    }

	/**
	 * @param null $token
	 * @return ConnectorResponse
	 */
    public function storeOAuthToken($token = null){
        if(is_string($token) && stripos($token, 'token')){
            $token = json_decode($token, true); // Google native login response
        }
	    if(is_array($token) && isset($token['connectorCredentials'])){
		    $token = $token['connectorCredentials'];
	    }
        if(is_array($token) && isset($token['token'])){
            $token = $token['token'];
        }
        if(is_array($token) && isset($token['authResponse'])){
            $token = $token['authResponse'];
        }
        if(is_array($token)){
            $token = QMArr::convertKeysFromCamelToSnakeCase($token);
            $token = $this->parseAccessTokenResponseArray($token);
        }
        if(!$token){
            $token = $this->stdOAuthToken;
        }
        $this->setToken($token); // Need to do this first so we can create user if necessary for storage
        $this->storeCredentials(['token' => $token]);  // Let's use this method instead of library because it stores test tokens
        return new ConnectorConnectedResponse($this);
    }
	/**
	 * Parses the access token response and returns a TokenInterface.
	 * @abstract
	 * @param string|array $responseBody
	 * @return TokenInterface
	 */
	private function parseAccessTokenResponseArray($responseBody): TokenInterface{
		$responseBody = json_encode($responseBody);
		return $this->parseAccessTokenResponse($responseBody);
	}
	/**
	 * @return string
	 * @throws \OAuth\Common\Storage\Exception\TokenNotFoundException
	 */
    protected function getAccessTokenString(){
        if(static::$testAccessToken){
            return static::$testAccessToken;
        }
        $stdOAuthToken = $this->getOrRefreshToken();
        if(is_string($stdOAuthToken)){
            return $stdOAuthToken;
        }
        if($stdOAuthToken){
            return $stdOAuthToken->getAccessToken();
        }
        return false;
    }
	/**
	 * @return array
	 * @throws ConnectorException
	 */
    public function getConnectorUserProfile(): ?array {
        if($this->connectorUserProfile){return $this->connectorUserProfile;}
	    $url = $this->urlUserDetails();
	    if(!$url){return null;}
	    /** @noinspection PhpUnhandledExceptionInspection */
	    $response = $this->getRequest($url);
	    $arr = json_decode(json_encode($response), true);
	    $user = $this->parseUserResponse($arr);
		if($this->userId){
			$this->updateUserMeta((array)$response);
		}
	    return $this->connectorUserProfile = $user;
    }
	/**
	 * @param bool $throwException
	 * @return string|null
	 */
	protected function getConnectorUserName(bool $throwException = false): ?string{
		if($r = $this->connectorUserProfile){
			$name = UserUserLoginProperty::pluck($r);
			if($name){
				return $name;
			}
		}
		$meta = $this->getUserMeta();
		foreach(UserUserLoginProperty::SYNONYMS as $SYNONYM){
			if(isset($meta[$this->name."_$SYNONYM"])){return $meta[$this->name."_$SYNONYM"];}
		}
		return parent::getConnectorUserName($throwException);
	}
	/**
	 * @param array|object $response
	 * @return array
	 */
	protected function parseUserResponse(array $response): array {
        return $response;
    }
    /**
     * @return string
     */
    public function urlUserDetails(): ?string {return null;}
    /**
     * @param int|string $nameOrId
     * @param int|null $userId
     * @return OAuthConnector
     */
    public static function getConnectorByNameOrId($nameOrId, int $userId = null): QMConnector{
        return parent::getConnectorByNameOrId($nameOrId, $userId);
    }
	/**
	 * @return int|null
	 */
    public function getUserId(): ?int{
        $this->userId = parent::getUserId();
        if(!$this->userId && $this->stdOAuthToken){
	        try {
		        $this->loginOrCreateUserAndUpdateMeta();
	        } catch (ConnectorException $e) {
				le($e);
	        }
        }
        return $this->userId;
    }
    /**
     * @return string
     */
    public function getOAuthServiceName(): string{
        if(static::$OAUTH_SERVICE_NAME){
            return static::$OAUTH_SERVICE_NAME;
        }
        return ucfirst($this->name);
    }
    /**
     * @return array
     */
    public function getScopes(): array {
        if(QMRequest::urlContains($this->name . '/connect')){
            $scopes = StateParameter::getValueFromStateParam('scope', $this->name);
            if($scopes){
                if(is_string($scopes)){
                    $scopes = (str_contains($scopes, ',')) ? explode(',', $scopes) : explode(' ', $scopes);
                }
                return $this->scopes = $scopes;
            }
        }
        if($this->getOAuthServiceName() === "Google"){
            $scopes = array_merge(static::$SCOPES, GoogleLoginConnector::$SCOPES);
            return $this->scopes = array_values(array_unique($scopes));
        }
        $this->scopes = static::$SCOPES;
        return $this->scopes;
    }
    /**
     * @return ConnectInstructions
     */
    public function getConnectInstructions(): ?ConnectInstructions{
        if($this->connectInstructions){return $this->connectInstructions;}
        if(!AppMode::isApiRequest()){return null;}
        $parameters = [];
        $url = $this->getAuthorizationUri();
        return $this->connectInstructions = new ConnectInstructions($url, $parameters, true);
    }
    /**
     * @return Credentials
     */
    protected function getCredentialsInterface(): Credentials{
	    $callbackUrl = static::getCallbackRedirectUrl();
		$callbackUrl = UrlHelper::removeParams($callbackUrl);
	    return new Credentials($this->getConnectorClientId(),
	                           $this->getOrSetConnectorClientSecret(), $callbackUrl);
    }
    /**
     * @param array $additionalParameters
     * @return ConnectorRedirectResponse
     */
    public function getAuthorizationPageRedirectResponse(array $additionalParameters = []): ConnectorRedirectResponse{
        $url = $this->getAuthorizationUri($additionalParameters);
        return new ConnectorRedirectResponse($this, 'connect', $url);
    }
	/**
	 * @param $requestBody
	 * @return QMUser
	 */
    public function createUser($requestBody): QMUser{
        if(!$this->providesEmail){
            throw new BadRequestException("Cannot create a new user with ".
                $this->getTitleAttribute().
                " because it does not provide email address");
        }
        try {
            $userData = json_decode(json_encode($requestBody), true);
            $userData[User::FIELD_CLIENT_ID] = BaseClientIdProperty::fromRequest(false) ?: BaseClientIdProperty::fromMemory();
            unset($userData["primaryOutcomeVariableId"]); // These are different on staging sometimes
            $user = User::createNewUser($userData, $this->name);
        } catch (InvalidUsernameException $e) {
            le($e);
        }
        $this->setUserId($user->id);
        $this->storeOAuthToken();
        return $user->getQMUser();
    }
	/**
	 * @return null|QMUser
	 * @throws ConnectorException
	 */
    public function loginOrCreateUserAndUpdateMeta(): ?QMUser{
        $connectorUserProfile = $this->getConnectorUserProfile();
        if(!$connectorUserProfile){return null;}
	    $user = null;
		if($id = $connectorUserProfile["id"] ?? null){
			if($connection = Connection::firstWhere(Connection::FIELD_CONNECTOR_USER_ID, $id, $this->name)){
				$user = $connection->getUser();
				$user->login();
				$user = $user->getQMUser();
			}
		}
	    try {$loggedInUser = QMAuth::getQMUser();} catch (UnauthorizedException $e) {$loggedInUser = null;}
		if(!$user){
			if($email = $this->getConnectorUserEmail()){
				$user = QMUser::findByEmail($email);
				if($user){
					QMAuth::loginUsingId($user->getId());
				}
			}
		}
		if(!$user && $loggedInUser){
			$loggedInUser = $user;
		}
        if(!$user){
	        if($id = $this->getConnectorUserId()){
		        $user = QMUser::findByConnectorUserId($id, $this->name);
	        }
        }
        if(!$user){
            $user = $this->createUser($connectorUserProfile);
        }else{
            $this->setUserId($user->id);
            if($this->name !== QuantiModoConnector::NAME){  // Redundant
	            $this->updateUserMeta($connectorUserProfile);
	            //$this->instantiateResponseObject(QMStr::toClassName($this->displayName)."User",
	            // $connectorUserProfile);
            }
        }
        try {
            $user->getOrCreateToken();
        } catch (BadRequestException $e) {
            $this->logError(__METHOD__.": ".$e->getMessage());
        } catch (ClientNotFoundException $e) {
            le($e);
        }  // Access token only added on API requests
	    //QMAuth::setUser($user, true);
        if(AppMode::isApiRequest()){
            Auth::login($user->l(), true);
        }
        return $user;
    }
	/**
	 * @return QMUser
	 * @throws ConnectorException
	 */
	public function getQmUser(): QMUser{
		if($user = parent::getQmUser()){
			return $user;
		}
		return $this->loginOrCreateUserAndUpdateMeta();
	}
	/**
	 * @return string|bool
	 * @throws ConnectorException
	 */
    public function getConnectorUserEmail(): ?string {
        $profile = $this->getConnectorUserProfile();
        if(!$profile){
            return null;
        }
        return $this->connectorUserEmail = UserUserEmailProperty::pluck($profile);
    }
	/**
	 * @return string|int
	 * @throws ConnectorException
	 */
    public function getConnectorUserId(){
        if($this->connectorUserId){
            return $this->connectorUserId;
        }
		if($id = $this->getUserMetaValue('id')){
			return $this->connectorUserId = $id;
		}
        $profile = $this->getConnectorUserProfile();
        if(!$profile){return null;}
        return $this->connectorUserId = UserIdProperty::pluck($profile);
    }
    public function addExtendPropertiesForRequest(): void{
        $this->getConnectorClientId();
        $this->getScopes();
        parent::addExtendPropertiesForRequest();
    }
	/**
	 * @return QMUser|null
	 */
	protected function tryToCreateUser(): ?QMUser{
		try {
			return $this->loginOrCreateUserAndUpdateMeta();
		} catch (\Throwable $e) {
			$url = $this->urlUserDetails();
			if(!AppMode::isProductionApiRequest()){
				le($e);
			}
			ExceptionHandler::dumpOrNotify($e);
			$this->logError("Failed to create user with url $url because " . $e->getMessage(), ['e' => $e]);
			return null;
		}
	}
	protected function authCheck(): void{
		if(!$this->providesUserProfileForLogin && !$this->userId && !QMAuth::getQMUser()){
			throw new UnauthorizedException("Please log in first!");
		}
	}
	/**
	 * @return string
	 */
	public function service(): string{
		// get class name without backslashes
		$classname = get_class($this);
		return preg_replace('/^.*\\\\/', '', $classname);
	}
	public static function getSocialiteDriver(): AbstractProvider {
		$with = Socialite::with(static::NAME);
		$scopes = $with->scopes(static::$SCOPES);
		return $with;
	}
	public static function getServiceConfig(): array{
		return [
			'client_id' => static::clientID(),
			'client_secret' => static::getClientSecret(),
			'redirect' => static::getConnectUrlWithoutParams(),
			'scopes' => static::$SCOPES,
		];
	}
}
