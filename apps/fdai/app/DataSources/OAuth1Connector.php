<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources;
use App\Slim\Controller\Connector\ConnectException;
use App\Slim\Controller\Connector\ConnectorConnectedResponse;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Controller\Connector\ConnectorRedirectResponse;
use App\Slim\Controller\Connector\ConnectorResponse;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\OAuth1\Service\ServiceInterface;
use OAuth\OAuth1\Signature\Signature;
use OAuth\OAuth1\Signature\SignatureInterface;
use OAuth\OAuth1\Token\StdOAuth1Token;
use OAuth\OAuth1\Token\TokenInterface;
abstract class OAuth1Connector extends OAuthConnector implements ServiceInterface
{
	/** @const OAUTH_VERSION */
	const OAUTH_VERSION = 1;
	/** @var SignatureInterface */
	protected $signature;
	/** @var UriInterface|null */
	protected $baseApiUri;
	public function __construct(int $userId = null){
		$this->signature = new Signature($this->getCredentialsInterface());
		$this->signature->setHashingAlgorithm('HMAC-SHA1');
		parent::__construct($userId);
	}
	/**
	 * @param array $parameters
	 * @return ConnectorException|ConnectorRedirectResponse|ConnectorResponse
	 * @throws TokenNotFoundException
	 * @throws TokenResponseException
	 */
	public function connect($parameters){
		$this->authCheck();
		$connectResponse = false;
		if(isset($parameters['connectorCredentials'])){ // Token or parameters provided by the client-side authentication process
			$connectResponse = $this->storeOAuthToken($parameters);
		}else if(!empty($parameters['oauth_token']) && !empty($parameters['oauth_verifier'])){
			try {
				$connectResponse = $this->requestAccessToken($parameters['oauth_token'], $parameters['oauth_verifier']);
			} catch (ConnectorException | TokenNotFoundException | TokenResponseException $e) {
				$this->logError(__METHOD__.": ".$e->getMessage());
				return $this->getAuthorizationPageRedirectResponse();
			}
		}
		if($connectResponse && $this->getToken() && $this->providesUserProfileForLogin){
			if($this->tryToCreateUser()){
				return $connectResponse;
			}
		}
		return $this->getAuthorizationPageRedirectResponse();
	}
	/**
	 * @return \OAuth\Common\Token\TokenInterface|TokenInterface
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 * @throws \OAuth\Common\Http\Exception\TokenResponseException
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
	public function requestRequestToken()
	{
		$authorizationHeader = ['Authorization' => $this->buildAuthorizationHeaderForTokenRequest()];
		$headers = array_merge($authorizationHeader, $this->getExtraOAuthHeaders());

		$responseBody = $this->getHttpClient()->retrieveResponse($this->getRequestTokenEndpoint(), [], $headers);

		$token = $this->parseRequestTokenResponse($responseBody);
		$this->getTokenStorage()->storeAccessToken($this->service(), $token);

		return $token;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationUri(array $additionalParameters = []): UriInterface
	{
		// Build the url
		$url = clone $this->getAuthorizationEndpoint();
		foreach ($additionalParameters as $key => $val) {
			$url->addToQuery($key, $val);
		}

		return $url;
	}
	/**
	 * {@inheritDoc}
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 * @throws \OAuth\Common\Storage\Exception\TokenNotFoundException
	 * @throws \OAuth\Common\Storage\Exception\TokenNotFoundException
	 */
	public function requestAccessToken($token, $verifier, $tokenSecret = null)
	{
		if (is_null($tokenSecret)) {
			$storedRequestToken = $this->getTokenStorage()->retrieveAccessToken($this->service());
			$tokenSecret = $storedRequestToken->getRequestTokenSecret();
		}
		$this->signature->setTokenSecret($tokenSecret);

		$bodyParams = [
			'oauth_verifier' => $verifier,
		];

		$authorizationHeader = [
			'Authorization' => $this->buildAuthorizationHeaderForAPIRequest(
				'POST',
				$this->getAccessTokenEndpoint(),
				$this->getTokenStorage()->retrieveAccessToken($this->service()),
				$bodyParams
			)
		];

		$headers = array_merge($authorizationHeader, $this->getExtraOAuthHeaders());

		$responseBody = $this->getHttpClient()->retrieveResponse($this->getAccessTokenEndpoint(), $bodyParams, $headers);

		$token = $this->parseAccessTokenResponse($responseBody);
		$this->getTokenStorage()->storeAccessToken($this->service(), $token);

		return $token;
	}

	/**
	 * Refreshes an OAuth1 access token
	 * @param  TokenInterface $token
	 * @return void $token
	 */
	public function refreshAccessToken(\OAuth\Common\Token\TokenInterface $token): \OAuth\Common\Token\TokenInterface
	{
	}
	/**
	 * Sends an authenticated API request to the path provided.
	 * If the path provided is not an absolute URI, the base API Uri (must be passed into constructor) will be used.
	 * @param string|UriInterface $path
	 * @param string $method HTTP method
	 * @param null $body Request body if applicable (key/value pairs)
	 * @param array $extraHeaders Extra headers if applicable.
	 *                                          These will override service-specific any defaults.
	 * @return string
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 * @throws \OAuth\Common\Storage\Exception\TokenNotFoundException
	 */
	public function request($path, $method = 'GET', $body = null, array $extraHeaders = []): string{
		$uri = $this->determineRequestUriFromPath($path, $this->baseApiUri);

		/** @var StdOAuth1Token $token */
		$token = $this->getTokenStorage()->retrieveAccessToken($this->service());
		$extraHeaders = array_merge($this->getExtraApiHeaders(), $extraHeaders);
		$authorizationHeader = [
			'Authorization' => $this->buildAuthorizationHeaderForAPIRequest($method, $uri, $token, $body)
		];
		$headers = array_merge($authorizationHeader, $extraHeaders);

		return $this->getHttpClient()->retrieveResponse($uri, $body, $headers, $method);
	}

	/**
	 * Return any additional headers always needed for this service implementation's OAuth calls.
	 *
	 * @return array
	 */
	protected function getExtraOAuthHeaders(): array{
		return [];
	}

	/**
	 * Return any additional headers always needed for this service implementation's API calls.
	 *
	 * @return array
	 */
	protected function getExtraApiHeaders(): array{
		return [];
	}

	/**
	 * Builds the authorization header for getting an access or request token.
	 *
	 * @param array $extraParameters
	 *
	 * @return string
	 */
	protected function buildAuthorizationHeaderForTokenRequest(array $extraParameters = []): string{
		$parameters = $this->getBasicAuthorizationHeaderInfo();
		$parameters = array_merge($parameters, $extraParameters);
		$parameters['oauth_signature'] = $this->signature->getSignature(
			$this->getRequestTokenEndpoint(),
			$parameters,
			'POST'
		);

		$authorizationHeader = 'OAuth ';
		$delimiter = '';
		foreach ($parameters as $key => $value) {
			$authorizationHeader .= $delimiter . rawurlencode($key) . '="' . rawurlencode($value) . '"';

			$delimiter = ', ';
		}

		return $authorizationHeader;
	}

	/**
	 * Builds the authorization header for an authenticated API request
	 * @param string $method
	 * @param UriInterface   $uri        The uri the request is headed
	 * @param TokenInterface $token
	 * @param array|null $bodyParams Request body if applicable (key/value pairs)
	 * @return string
	 */
	protected function buildAuthorizationHeaderForAPIRequest(
		string         $method,
		UriInterface   $uri,
		TokenInterface $token,
		array          $bodyParams = null
	): string{
		$this->signature->setTokenSecret($token->getAccessTokenSecret());
		$authParameters = $this->getBasicAuthorizationHeaderInfo();
		if (isset($authParameters['oauth_callback'])) {
			unset($authParameters['oauth_callback']);
		}

		$authParameters = array_merge($authParameters, ['oauth_token' => $token->getAccessToken()]);

		$signatureParams = (is_array($bodyParams)) ? array_merge($authParameters, $bodyParams) : $authParameters;
		$authParameters['oauth_signature'] = $this->signature->getSignature($uri, $signatureParams, $method);

		if (is_array($bodyParams) && isset($bodyParams['oauth_session_handle'])) {
			$authParameters['oauth_session_handle'] = $bodyParams['oauth_session_handle'];
			unset($bodyParams['oauth_session_handle']);
		}

		$authorizationHeader = 'OAuth ';
		$delimiter = '';

		foreach ($authParameters as $key => $value) {
			$authorizationHeader .= $delimiter . rawurlencode($key) . '="' . rawurlencode($value) . '"';
			$delimiter = ', ';
		}

		return $authorizationHeader;
	}

	/**
	 * Builds the authorization header array.
	 *
	 * @return array
	 */
	protected function getBasicAuthorizationHeaderInfo(): array{
		$dateTime = new \DateTime();
		$headerParameters = [
			'oauth_callback'         => $this->getCredentialsInterface()->getCallbackUrl(),
			'oauth_consumer_key'     => $this->getCredentialsInterface()->getConsumerId(),
			'oauth_nonce'            => $this->generateNonce(),
			'oauth_signature_method' => $this->getSignatureMethod(),
			'oauth_timestamp'        => $dateTime->format('U'),
			'oauth_version'          => $this->getVersion(),
		];

		return $headerParameters;
	}

	/**
	 * Pseudo random string generator used to build a unique string to sign each request
	 * @param int $length
	 * @return string
	 */
	protected function generateNonce(int $length = 32): string{
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

		$nonce = '';
		$maxRand = strlen($characters)-1;
		for ($i = 0; $i < $length; $i++) {
			$nonce.= $characters[rand(0, $maxRand)];
		}

		return $nonce;
	}

	/**
	 * @return string
	 */
	protected function getSignatureMethod(): string{
		return 'HMAC-SHA1';
	}

	/**
	 * This returns the version used in the authorization header of the requests
	 *
	 * @return string
	 */
	protected function getVersion(): string{
		return '1.0';
	}

	/**
	 * Parses the request token response and returns a TokenInterface.
	 * This is only needed to verify the `oauth_callback_confirmed` parameter. The actual
	 * parsing logic is contained in the access token parser.
	 * @abstract
	 * @param string $responseBody
	 * @return TokenInterface
	 * @throws TokenResponseException
	 */
	abstract protected function parseRequestTokenResponse(string $responseBody): TokenInterface;
	/**
	 * Parses the access token response and returns a TokenInterface.
	 * @abstract
	 * @param string $responseBody
	 * @return TokenInterface
	 * @throws TokenResponseException
	 */
	abstract protected function parseAccessTokenResponse(string $responseBody): TokenInterface;
	/**
	 * @return ConnectorRedirectResponse
	 * @throws GuzzleException
	 * @throws TokenResponseException
	 */
	protected function getRedirectResponse(): ConnectorRedirectResponse{
		$token = $this->requestRequestToken();
		$url =
			$this
			     ->getAuthorizationUri(['oauth_token' => $token->getRequestToken()])
			     ->getAbsoluteUri();
		$this->storeAuthorizationState();
		return new ConnectorRedirectResponse($this, 'connect', $url);
	}
	/**
	 * @param $parameters
	 * @return ConnectorRedirectResponse|ConnectorResponse
	 * @throws \App\Slim\Controller\Connector\ConnectException
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \OAuth\Common\Http\Exception\TokenResponseException
	 * @throws \OAuth\Common\Storage\Exception\TokenNotFoundException
	 */
	protected function connectOrRedirect($parameters){
		if(isset($parameters['connectorCredentials'])){
			return $this->storeOAuthToken($parameters);
		}
		//$this->initService();
		if(empty($parameters['oauth_token']) || empty($parameters['oauth_verifier'])){
			return $this->getRedirectResponse();
		}
		try {
			$accessToken =
				$this->requestAccessToken($parameters['oauth_token'], $parameters['oauth_verifier']);
			if(!$accessToken){
				$this->logDebug('Received access token');
				return new ConnectorConnectedResponse($this);
			}
			$this->logError("Couldn't get access token");
			throw new ConnectException($this,"$this->displayName failed to return an access token");
		} catch (BadResponseException $e) {
			throw new ConnectException($this, $e->getMessage());
		}
	}
	/**
	 * @return ConnectInstructions
	 */
	public function getConnectInstructions(): ConnectInstructions{
		// Point to /connect endpoint so that we don't have to make an expensive call to Withings just to get the URL
		return $this->connectInstructions = new ConnectInstructions(static::getCallbackRedirectUrl(), 
		                                                            [], true);
	}


}
