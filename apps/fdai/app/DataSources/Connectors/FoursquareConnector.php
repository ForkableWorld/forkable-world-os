<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\OAuth2Connector;
use App\DataSources\QMConnectorResponse;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Models\Variable;
use App\Slim\Model\Measurement\AdditionalMetaData;
use App\Types\QMArr;
use App\Units\CountUnit;
use App\Utils\APIHelper;
use App\Utils\UrlHelper;
use App\VariableCategories\LocationsVariableCategory;
use App\Variables\QMUserVariable;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\OAuth2\Token\StdOAuth2Token;
use stdClass;
/** Class FoursquareConnector
 * @package App\DataSources\Connectors
 */
class FoursquareConnector extends OAuth2Connector {
	private $apiVersionDate = '20130829';
	protected const AFFILIATE                      = false;
	protected const BACKGROUND_COLOR               = '#fa4876';
	protected const CLIENT_REQUIRES_SECRET         = false;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Locations';
	protected const DEVELOPER_CONSOLE              = 'https://foursquare.com/settings/applications/new';
	protected const DEVELOPER_PASSWORD             = null;
	protected const DEVELOPER_USERNAME             = null;
	public const    DISPLAY_NAME                   = 'Foursquare';
	protected const GET_IT_URL                     = 'https://foursquare.com/';
	public const    IMAGE                          = 'https://cdn2.iconfinder.com/data/icons/social-icons-33/128/Foursquare-512.png';
	protected const LOGO_COLOR                     = '#f94877';
	protected const LONG_DESCRIPTION               = 'Foursquare helps you find the perfect places to go with friends. Discover the best food, nightlife, and entertainment in your area.';
	protected const SHORT_DESCRIPTION              = 'Tracks locations.';
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
	public $getItUrl = self::GET_IT_URL;
	public $id = self::ID;
	public $image = self::IMAGE;
	public $logoColor = self::LOGO_COLOR;
	public $longDescription = self::LONG_DESCRIPTION;
	public $name = self::NAME;
	public $providesUserProfileForLogin = true;
	public $shortDescription = self::SHORT_DESCRIPTION;
	public const ENABLED = false;
	public const ID      = 78;
	public const NAME    = 'foursquare';
	public static array $SCOPES = [];
	/**
	 * @return array|int
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 * @throws \OAuth\Common\Storage\Exception\TokenNotFoundException
	 */
	public function importData(): void{
		$fromTime = $this->getFromTime();
		$path = '/users/self/checkins?afterTimestamp=' . $fromTime;
		// TODO: Not sure if these should always be added?
		// https://foursquare.com/developers/explore#req=users%2Fself%2Fcheckins%3FafterTimestamp%3D1%26v%3D20130829
		$checkIns = $this->getRequest($path);
		foreach($checkIns->response->checkins->items as $checkIn){
			$this->addCheckInMeasurementItem($checkIn, $checkIn->venue->name);
			$this->addCheckInMeasurementItem($checkIn, $checkIn->venue->categories[0]->pluralName);
		}
		//$venues  = $this->getFoursquareService()->request('/users/self/venuehistory?afterTimestamp='.$fromTime);
		$this->saveMeasurements();
	}
	/**
	 * @param $checkIn
	 * @param string $variableName
	 * @return void
	 * @throws InvalidVariableValueAttributeException
	 */
	private function addCheckInMeasurementItem($checkIn, string $variableName){
		$variableName = "Visits to " . $variableName;
		foreach($checkIn->venue as $key => $value){
			$checkIn->$key = $value;
		}
		$image = $checkIn->venue->categories[0]->icon->prefix . '88' . $checkIn->venue->categories[0]->icon->suffix;
		$newVariableParams = [
			'variableCategoryName' => LocationsVariableCategory::NAME,
			'unitName' => CountUnit::NAME,
			Variable::FIELD_IMAGE_URL => $image,
			Variable::FIELD_MOST_COMMON_CONNECTOR_ID => $this->id,
			Variable::FIELD_IS_PUBLIC => 0,
		];
		if(isset($checkIn->venue->url)){
			$newVariableParams[Variable::FIELD_INFORMATIONAL_URL] = $checkIn->venue->url;
		}
		$uv = QMUserVariable::findOrCreateByNameOrId($this->userId, $variableName, [], $newVariableParams);
		$note = new AdditionalMetaData($checkIn);
		$m = $this->generateMeasurement($uv, $checkIn->createdAt, 1, CountUnit::NAME, null, $note);
		$m->setLatitude($checkIn->venue->location->lat);
		$m->setLongitude($checkIn->venue->location->lng);
		$m->setImageUrl($image);
		if(isset($checkIn->venue->url)){
			$m->setUrl($checkIn->venue->url);
		}
		$uv->addToMeasurementQueue($m);
		$this->setUserVariableByName($uv);
	}
	/**
	 * @return bool|string
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
	public function getConnectorUserEmail(): ?string{
		$profile = $this->getConnectorUserProfile();
		return $this->connectorUserEmail = $profile['contact']['email'] ?? null;
	}
	/**
	 * @return string
	 * @throws TokenNotFoundException
	 */
	public function urlUserDetails(): string{
		return 'https://api.foursquare.com/v2/users/self?oauth_token=' . $this->getAccessTokenString();
	}
	/**
	 * @param array $additionalParameters
	 * @return UriInterface
	 */
	public function getAuthorizationUri(array $additionalParameters = []): UriInterface{
		$additionalParameters['v'] = '20181026';
		$additionalParameters['client_id'] = $this->getConnectorClientId();
		$additionalParameters['client_secret'] = static::getClientSecret();
		return parent::getAuthorizationUri($additionalParameters);
	}
	/**
	 * @param array $parameters
	 * @return mixed|QMConnectorResponse|stdClass
	 * @throws \App\Exceptions\RateLimitConnectorException
	 */
	public function requestOAuth2AccessToken(array $parameters){
		$parameters['client_id'] = $this->getConnectorClientId();
		$parameters['client_secret'] = static::getClientSecret();
		$parameters['grant_type'] = 'authorization_code';
		$parameters['redirect_uri'] = $this->getCallbackRedirectUrl();
		$parameters['code'] = QMArr::getValue($parameters, [
			'code',
			'serverAuthCode',
		]);
		$url = 'https://foursquare.com/oauth2/access_token';
		$url = UrlHelper::addParams($url, $parameters);
		$response = APIHelper::getRequest($url);
		$token = new StdOAuth2Token();
		$token->setAccessToken($response->access_token);
		$this->setToken($token);
		return $response;
	}
	/**
	 * @param string $path
	 * @param array $params
	 * @param array $extraHeaders
	 * @param array $options
	 * @return array|object
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 * @throws \OAuth\Common\Storage\Exception\TokenNotFoundException
	 */
	public function getRequest(string $path, array $params = [], array $extraHeaders = [], array $options = []){
		$params['oauth_token'] = $this->getAccessTokenString();
		$params['v'] = '20181026';
		return parent::getRequest($path, $params, $extraHeaders, $options);
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationEndpoint(){
		return new Uri('https://foursquare.com/oauth2/authenticate');
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAccessTokenEndpoint(){
		return new Uri('https://foursquare.com/oauth2/access_token');
	}
	/**
	 * {@inheritdoc}
	 */
	protected function parseAccessTokenResponse($responseBody): StdOAuth2Token{
		return parent::parseNeverExpiresAccessTokenResponse($responseBody);
	}
	/**
	 * {@inheritdoc}
	 */
	public function request($path, $method = 'GET', $body = null, array $extraHeaders = []){
		$uri = $this->determineRequestUriFromPath($path, new Uri($this->getBaseApiUrl()));
		$uri->addToQuery('v', $this->apiVersionDate);
		return parent::request($uri, $method, $body, $extraHeaders);
	}
	/**
	 * Returns a class constant from ServiceInterface defining the authorization method used for the API
	 * Header is the sane default.
	 * @return int
	 */
	protected function getAuthorizationMethod(): int{
		return static::AUTHORIZATION_METHOD_HEADER_OAUTH;
	}
}
