<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources;
use App\Exceptions\UnauthorizedException;
use App\Http\Urls\IntendedUrl;
use App\Logging\QMLog;
use App\Parameters\StateParameter;
use App\Slim\Controller\Connector\ConnectException;
use App\Slim\Controller\Connector\ConnectorConnectedResponse;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Controller\Connector\ConnectorRedirectResponse;
use App\Slim\Controller\Connector\ConnectorResponse;
use App\Types\QMArr;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth2\Service\Exception\InvalidAuthorizationStateException;
use OAuth\OAuth2\Service\Exception\MissingRefreshTokenException;
use OAuth\OAuth2\Service\ServiceInterface;
use OAuth\OAuth2\Token\StdOAuth2Token;
abstract class OAuth2Connector extends OAuthConnector implements ServiceInterface {
	public const OAUTH_VERSION = 2;
	/** @var bool */
	protected $stateParameterInAuthUrl;
	/** @var string */
	protected $apiVersion;
	protected $accessType = 'offline'; // Use offline so we get a refresh token

    /**
     * @param array $parameters
     * @return ConnectorException|ConnectorRedirectResponse|ConnectorResponse
     * @throws TokenNotFoundException
     * @throws UnauthorizedException
     */
	public function connect($parameters){
		$this->authCheck();
		$connectResponse = false;
		$authCode = QMArr::getValue($parameters, ['code', 'serverAuthCode']);
		if(isset($parameters['connectorCredentials'])){ // Token or parameters provided by the client-side authentication process
			$connectResponse = $this->storeOAuthToken($parameters);
		} elseif($authCode){
			try {
				$connectResponse = $this->requestOAuth2AccessToken($parameters);
			} catch (ConnectException|ConnectorException|TokenResponseException $e) {
				$this->logError(__METHOD__.": ".$e->getMessage());
				return $this->getAuthorizationPageRedirectResponse();
			}
		}
		if($connectResponse){return $connectResponse;}
		return $this->getAuthorizationPageRedirectResponse();
	}
	/**
	 * @param $data
	 * @return StdOAuth2Token
	 */
	protected function newStdOAuth2Token($data): StdOAuth2Token{
		$token = new StdOAuth2Token();
		$token->setAccessToken($data['access_token']);
		unset($data['access_token']);
		if(isset($data['refresh_token'])){
			$token->setRefreshToken($data['refresh_token']);
			unset($data['refresh_token']);
		}
		if(isset($data['expires_in'])){
			$token->setLifeTime($data['expires_in']);
			unset($data['expires_in']);
		}
		$token->setExtraParams($data);
		return $token;
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationUri(array $additionalParameters = []): UriInterface{
		$additionalParameters['state'] = StateParameter::getEncodedStateParam();
		$credentials = $this->getCredentialsInterface();
		$redirect = $credentials->getCallbackUrl();
        if(!str_contains($redirect, ".quantimo.do")){
            // Why are we doing this here?  IntendedUrl::set($finalCallback = $redirect);
            //$redirect = str_replace(\App\Utils\Env::getAppUrl(), "https://app.quantimo.do", $redirect);
            //$redirect = UrlHelper::addParams($redirect, ['final_callback_url='=>$finalCallback]);
        }
		$parameters = array_merge($additionalParameters, [
			'type' => 'web_server',
			'client_id' => $credentials->getConsumerId(),
			'redirect_uri' => $redirect,
			'response_type' => 'code',
		]);
		if($s = $this->getScopes()){
			$parameters['scope'] = implode($this->getScopesDelimiter(), $s);
		}
		if($this->needsStateParameterInAuthUrl()){
			if(!isset($parameters['state'])){
				$parameters['state'] = $this->generateAuthorizationState();
			}
			$this->storeAuthorizationState($parameters['state']);
		}
		$url = $this->getAuthorizationEndpoint();
		if(!is_object($url)){
			$url = $this->getAuthorizationEndpoint();
			le("getAuthorizationEndpoint should return an object");
		}
		$url = clone $url;
		foreach($parameters as $key => $val){
			$url->addToQuery($key, $val);
		}
		return $url;
	}
	/**
	 * @param array $parameters
	 * @return ConnectorResponse
	 * @throws ConnectorException
	 * @throws TokenResponseException
	 */
	public function requestOAuth2AccessToken(array $parameters){
		$authCode = QMArr::getValue($parameters, ['code', 'serverAuthCode']);
		$state = $parameters['state'] ?? null;
		$token = $this->requestAccessToken($authCode, $state);
		$this->setToken($token);
		if($this->isImportViaApi()){$this->requestImport();}
		$this->logDebug('Received access token from $this->displayName');
		return new ConnectorConnectedResponse($this);
	}
	/**
	 * @param string $code
	 * @param null $state
	 * @return TokenInterface
	 * @throws ConnectorException
	 * @throws TokenResponseException
	 */
	public function requestAccessToken($code, $state = null): TokenInterface{
        $bodyParams = $this->getAccessTokenRequestParams($code);
        $uri = $this->getAccessTokenEndpoint();
		$lusitanianGuzzleClient = $this->getHttpClient();
		$responseBody = $lusitanianGuzzleClient->retrieveResponse($uri, $bodyParams, $this->getExtraOAuthHeaders());
		$token = $this->parseAccessTokenResponse($responseBody);
        if(!$this->userId && $this->providesUserProfileForLogin){
            $this->setToken($token);
            $this->tryToCreateUser();
        }
		$storage = $this->getTokenStorage();
		$storage->storeAccessToken($this->service(), $token);
		return $token;
	}
	/**
	 * Sends an authenticated API request to the path provided.
	 * If the path provided is not an absolute URI, the base API Uri (must be passed into constructor) will be used.
	 * @param string|UriInterface $path
	 * @param string $method HTTP method
	 * @param null $body Request body if applicable.
	 * @param array $extraHeaders Extra headers if applicable. These will override service-specific
	 *                                          any defaults.
	 * @return mixed
	 * @throws ConnectorException
	 * @throws TokenNotFoundException
	 */
	public function request($path, $method = 'GET', $body = null, array $extraHeaders = []){
        $path = str_replace($this->getBaseApiUrl(), "", $path);
		$uri = $this->determineRequestUriFromPath($path, new Uri($this->getBaseApiUrl()));
		$token = $this->getOrRefreshToken();
		// add the token where it may be needed
		if(static::AUTHORIZATION_METHOD_HEADER_OAUTH === $this->getAuthorizationMethod()){
			$extraHeaders = array_merge(['Authorization' => 'OAuth ' . $token->getAccessToken()], $extraHeaders);
		} elseif(static::AUTHORIZATION_METHOD_QUERY_STRING === $this->getAuthorizationMethod()){
			$uri->addToQuery('access_token', $token->getAccessToken());
		} elseif(static::AUTHORIZATION_METHOD_QUERY_STRING_V2 === $this->getAuthorizationMethod()){
			$uri->addToQuery('oauth2_access_token', $token->getAccessToken());
		} elseif(static::AUTHORIZATION_METHOD_QUERY_STRING_V3 === $this->getAuthorizationMethod()){
			$uri->addToQuery('apikey', $token->getAccessToken());
		} elseif(static::AUTHORIZATION_METHOD_QUERY_STRING_V4 === $this->getAuthorizationMethod()){
			$uri->addToQuery('auth', $token->getAccessToken());
		} elseif(static::AUTHORIZATION_METHOD_HEADER_BEARER === $this->getAuthorizationMethod()){
			$extraHeaders = array_merge(['Authorization' => 'Bearer ' . $token->getAccessToken()], $extraHeaders);
		}
		$extraHeaders = array_merge($this->getExtraApiHeaders(), $extraHeaders);
		$c = $this->getHttpClient();
		$response = $c->retrieveResponse($uri, $body, $extraHeaders, $method);
		return $response;
	}
	/**
	 * Refreshes an OAuth2 access token.
	 * @param TokenInterface $token
	 * @return TokenInterface $token
	 * @throws ConnectException
	 * @throws MissingRefreshTokenException
	 * @throws TokenResponseException
	 */
	public function refreshAccessToken(TokenInterface $token): TokenInterface{
		QMLog::info("Refreshing access token", [], false);

        $parameters = $this->getRefreshTokenParams($token);
        QMLog::debug("Refreshing token with parameters: ", $parameters);
		try {
			$responseBody = $this->getHttpClient()
				->retrieveResponse($this->getAccessTokenEndpoint(), $parameters, 
				                   $this->getExtraOAuthHeaders());
		} catch (ConnectorException $e) {
			throw new ConnectException($this, $e->getMessage());
		}
		if(str_contains($responseBody, 'invalid')){
			throw new ConnectException($this, $responseBody);
		}
		$token = $this->parseAccessTokenResponse($responseBody);
		// an ugly hack for Google because they give us a refresh token only once
		if($this->service() === 'Google'){
			$token->setRefreshToken($token['refresh_token']);
		}
		$service = $this->service();
		$this->getTokenStorage()->storeAccessToken($service, $token);
		$this->setToken($token);
		return $token;
	}
	/**
	 * Return whether or not the passed scope value is valid.
	 * @param string $scope
	 * @return bool
	 */
	public function isValidScope(string $scope): bool{
		$reflectionClass = new \ReflectionClass(get_class($this));
		$constants = $reflectionClass->getConstants();
		$valid = in_array($scope, $constants, true);
		if(!$valid){
			QMLog::error("$scope not valid.  You might need to copy scope constants from " .
				"vendor/google/apiclient-services/src/Google/Service to vendor-overrides/VendorPatch/Google.php");
		}
		return $valid;
	}
	/**
	 * Check if the given service need to generate a unique state token to build the authorization url
	 * @return bool
	 */
	public function needsStateParameterInAuthUrl(): bool{
		return $this->stateParameterInAuthUrl ?? true;
	}
	/**
	 * Validates the authorization state against a given one
	 * @param string $state
	 * @throws InvalidAuthorizationStateException
	 */
	protected function validateAuthorizationState(string $state){
		if($this->retrieveAuthorizationState() !== $state){
			throw new InvalidAuthorizationStateException($this->getAuthorizationUri());
		}
	}
	/**
	 * Generates a random string to be used as state
	 * @return string
	 */
	protected function generateAuthorizationState(): string{
		return md5(mt_rand());
	}
	/**
	 * Retrieves the authorization state for the current service
	 * @return string
	 */
	protected function retrieveAuthorizationState(): string{
		return $this->getTokenStorage()->retrieveAuthorizationState($this->service());
	}
	/**
	 * Stores a given authorization state into the storage
	 * @param string $state
	 */
	protected function storeAuthorizationState(string $state){
		$this->getTokenStorage()->storeAuthorizationState($this->service(), $state);
	}
	/**
	 * Return any additional headers always needed for this service implementation's OAuth calls.
	 * @return array
	 */
	protected function getExtraOAuthHeaders(): array{
		return [];
	}
	/**
	 * Return any additional headers always needed for this service implementation's API calls.
	 * @return array
	 */
	protected function getExtraApiHeaders(): array{
		return [];
	}
	/**
	 * Parses the access token response and returns a TokenInterface.
	 * @abstract
	 * @param string $responseBody
	 * @return TokenInterface
	 * @throws TokenResponseException
	 */
	abstract protected function parseAccessTokenResponse(string $responseBody): TokenInterface;
	/**
	 * Returns a class constant from ServiceInterface defining the authorization method used for the API
	 * Header is the sane default.
	 * @return int
	 */
	abstract protected function getAuthorizationMethod(): int;
	/**
	 * Returns api version string if is set else return empty string
	 * @return string
	 */
	protected function getApiVersionString(): string{
		return !empty($this->apiVersion) ? "/" . $this->apiVersion : "";
	}
	/**
	 * Returns delimiter to scopes in getAuthorizationUri
	 * For services that do not fully respect the Oauth RFC,
	 * and use scopes with commas as delimiter
	 * @return string
	 */
	protected function getScopesDelimiter(): string{
		return ' ';
	}
	/**
	 * @param $responseBody
	 * @return StdOAuth2Token
	 * @throws TokenResponseException
	 */
	protected function parseStandardAccessTokenResponse($responseBody): StdOAuth2Token{
		$data = json_decode($responseBody, true);
		if(isset($data['body'])){
			$data = $data['body'];
		}
		if(!is_array($data)){
			throw new TokenResponseException('Unable to parse response.');
		} elseif(isset($data['error_description']) || isset($data['error'])){
			throw new TokenResponseException(sprintf('Error in retrieving token: "%s"',
				$data['error_description'] ?? $data['error']));
		}
		return $this->newStdOAuth2Token($data);
	}
	/**
	 * @throws TokenResponseException
	 */
	protected function parseNeverExpiresAccessTokenResponse($responseBody): StdOAuth2Token{
		$data = json_decode($responseBody, true);
		if(!is_array($data)){
			throw new TokenResponseException('Unable to parse response.');
		} elseif(isset($data['error'])){
			throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
		}
		$token = new StdOAuth2Token();
		$token->setAccessToken($data['access_token']);
		// Github tokens evidently never expire...
		$token->setEndOfLife(StdOAuth2Token::EOL_NEVER_EXPIRES);
		unset($data['access_token']);
		$token->setExtraParams($data);
		return $token;
	}
	/**
	 * @param string $responseBody
	 * @return mixed
	 * @throws TokenResponseException
	 */
	protected function jsonDecodeAccessTokenResponse(string $responseBody){
		$data = json_decode($responseBody, true);
		if(!is_array($data)){
			throw new TokenResponseException('Unable to parse response.');
		} elseif(isset($data['error_description'])){
			throw new TokenResponseException('Error in retrieving token: "' . $data['error_description'] . '"');
		} elseif(isset($data['error'])){
			throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
		}
		return $data;
	}

    /**
     * @param string $code
     * @return array
     */
    protected function getAccessTokenRequestParams(string $code): array
    {
        $cred = $this->getCredentialsInterface();
        $bodyParams = [
            'code' => $code,
            'client_id' => $cred->getConsumerId(),
            'client_secret' => $cred->getConsumerSecret(),
            'redirect_uri' => $cred->getCallbackUrl(),
            'grant_type' => 'authorization_code',
        ];
        return $bodyParams;
    }

    /**
     * @param TokenInterface $token
     * @return array
     * @throws MissingRefreshTokenException
     */
    protected function getRefreshTokenParams(TokenInterface $token): array
    {
        $refreshToken = $token->getRefreshToken();
        if(empty($refreshToken)){
            QMLog::error("No refresh token!", [], false);
            throw new MissingRefreshTokenException("No refresh token for $this");
        }
        $parameters = [
            'grant_type' => 'refresh_token',
            'type' => 'web_server',
            'client_id' => $this->getCredentialsInterface()->getConsumerId(),
            'client_secret' => $this->getCredentialsInterface()->getConsumerSecret(),
            'refresh_token' => $refreshToken,
        ];
        return $parameters;
    }
}
