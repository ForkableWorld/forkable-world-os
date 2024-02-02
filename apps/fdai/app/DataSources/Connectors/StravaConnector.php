<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\OAuth2Connector;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Controller\Connector\ConnectorRedirectResponse;
use App\Slim\Controller\Connector\ConnectorResponse;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Units\MetersPerSecondUnit;
use App\Units\MetersUnit;
use App\VariableCategories\PhysicalActivityVariableCategory;
use App\Variables\QMUserVariable;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\OAuth2\Service\Exception\InvalidAccessTypeException;
use OAuth\OAuth2\Service\Strava;
use OAuth\OAuth2\Token\StdOAuth2Token;
use Pest;
use Strava\API\Client;
use Strava\API\Exception;
use Strava\API\Service\REST;
/** Class StravaConnector
 * @package App\DataSources\Connectors
 */
class StravaConnector extends OAuth2Connector {
	/**
	 * Scopes
	 */
	// default
	const SCOPE_READ = 'read';
	// Modify activities, upload on the user’s behalf
	const SCOPE_WRITE = 'write';
	// View private activities and data within privacy zones
	const SCOPE_VIEW_PRIVATE = 'view_private';
	protected $approvalPrompt = 'auto';
	protected $providesEmail = false;
	protected const AFFILIATE                      = false;
	protected const BACKGROUND_COLOR               = '#fc4c02';
	protected const CLIENT_REQUIRES_SECRET         = true;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Physical Activity';
	public const    DISPLAY_NAME                   = 'Strava';
	public const    DISABLED_UNTIL = "2023-04-01";
	protected const ENABLED                        = 0; // TODO: Update authentication
	protected const GET_IT_URL                     = null;
	public const    IMAGE                          = 'https://assets.ifttt.com/images/channels/1055884022/icons/large.png';
	protected const LOGO_COLOR                     = '#2d2d2d';
	protected const LONG_DESCRIPTION               = 'If you like to run, ride or just adventure outside, you\'ll love Strava. Give it a try, it\'s free! Millions of runners, cyclists and active people use Strava to record their activities, compare performance over time, connect with their community, and share the photos, stories and highlights of their adventures with friends.';
	protected const PREMIUM                        = true;
	protected const SHORT_DESCRIPTION              = 'Runners and cyclists use Strava to record their activities and compare performance over time';
	protected const DEVELOPER_CONSOLE              = 'https://www.strava.com/settings/api';
	protected const API_DOCS                       = 'http://developers.strava.com/docs/reference';
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
	public $premium = self::PREMIUM;
	public $providesUserProfileForLogin = true;
	public $shortDescription = self::SHORT_DESCRIPTION;
	public const ID   = 76;
	public const NAME = 'strava';
	public static array$SCOPES = [Strava::SCOPE_READ];
	public static $OAUTH_SERVICE_NAME = 'Strava';
	// Dev Console: https://Strava.com/settings/applications/new
	/**
	 * @param $parameters
	 * @return ConnectorException|ConnectorResponse|ConnectorRedirectResponse
	 * @throws TokenResponseException
	 */
	public function connect($parameters){
		return parent::connect($parameters);
	}
	/**
	 * @return void
	 * @throws InvalidVariableValueAttributeException
	 * @throws TokenNotFoundException
	 * @throws TokenResponseException
	 */
	public function importData(): void{
		$loginName = $this->getConnectorUserName();
		$this->logDebug("Strava Connector: Login name: $loginName");
		$this->getActivities();
	}
	/**
	 * @return Client
	 * @throws TokenResponseException
	 * @throws TokenNotFoundException
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	private function getStravaClient(){
		$adapter = new \GuzzleHttp\Client(['base_uri' => 'https://www.strava.com/api/v3/']);
		$service = new REST($this->getAccessTokenString(), $adapter);  // Define your user token here.
		$client = new Client($service);
		return $client;
	}
	/**
	 * @return int
	 * @throws InvalidVariableValueAttributeException
	 * @throws TokenResponseException
	 * @throws TokenNotFoundException
	 */
	private function getActivities(): int{
		try {
			$activities = $this->getStravaClient()->getAthleteActivities();
		} catch (Exception $e) {
			le($e);
		}
		foreach($activities as $activity){
			$this->addAverageSpeedMeasurement($activity);
			$this->addDistanceMeasurement($activity);
		}
		$this->saveMeasurements();
	}
	/**
	 * @param float $value
	 * @param array $activity
	 * @param QMUserVariable $v
	 * @param string $unitName
	 * @return QMMeasurement
	 */
	private function getMeasurementItem(float $value, array $activity, QMUserVariable $v,
		string $unitName): QMMeasurement{
		$m = $this->generateMeasurement($v, $activity['start_date'], $value, $unitName, $activity['moving_time'],
			$activity['name']);
		$m->getAdditionalMetaData()->addMetaData('start_latlng', $activity['start_latlng']);
		$m->getAdditionalMetaData()->addMetaData('end_latlng', $activity['end_latlng']);
		$m->getAdditionalMetaData()->addMetaData('map', $activity['map']);
		$m->setLatitude($activity['start_latitude']);
		$m->setLongitude($activity['start_longitude']);
		return $m;
	}
	/**
	 * @param array $activity
	 * @throws InvalidVariableValueAttributeException
	 */
	private function addDistanceMeasurement(array $activity){
		$variableName = $activity['type'] . " Distance";
		$v = $this->getQMUserVariable($variableName, MetersUnit::NAME, PhysicalActivityVariableCategory::NAME);
		$measurementItem = $this->getMeasurementItem($activity['distance'], $activity, $v, MetersUnit::NAME);
		$measurementItem->setOriginalUnitByNameOrId(MetersUnit::NAME);
		$v->addToMeasurementQueueIfNoneExist($measurementItem);
	}
	/**
	 * @param array $activity
	 * @throws InvalidVariableValueAttributeException
	 */
	private function addAverageSpeedMeasurement(array $activity){
		$variableName = "Average " . $activity['type'] . " Speed";
		$v = $this->getQMUserVariable($variableName, MetersPerSecondUnit::NAME, PhysicalActivityVariableCategory::NAME);
		$m = $this->getMeasurementItem($activity['average_speed'], $activity, $v, MetersPerSecondUnit::NAME);
		$v->addToMeasurementQueueIfNoneExist($m);
	}
	/**
	 * @throws Exception
	 * @throws TokenResponseException
	 * @throws TokenNotFoundException
	 */
	public function getConnectorUserProfile(): ?array{
		if($this->connectorUserProfile){
			return $this->connectorUserProfile;
		}
		$profile = $this->getStravaClient()->getAthlete();
		$this->updateUserMeta($profile);
		return $this->connectorUserProfile = $profile;
	}
	protected function updateUserMeta(array $profile){
		$this->updateUserAvatarIfNecessary($profile['profile']);
		parent::updateUserMeta($profile);
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationEndpoint(){
		return new Uri('https://www.strava.com/oauth/authorize?approval_prompt=' . $this->approvalPrompt);
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAccessTokenEndpoint(){
		return new Uri('https://www.strava.com/oauth/token');
	}
	/**
	 * {@inheritdoc}
	 */
	protected function getAuthorizationMethod(): int{
		return static::AUTHORIZATION_METHOD_HEADER_BEARER;
	}
	/**
	 * {@inheritdoc}
	 */
	protected function parseAccessTokenResponse($responseBody): StdOAuth2Token{
		$data = $this->jsonDecodeAccessTokenResponse($responseBody);
		return $this->newStdOAuth2Token($data);
	}
	/**
	 * @param $prompt
	 * @throws InvalidAccessTypeException
	 */
	public function setApprovalPrompt($prompt){
		if(!in_array($prompt, ['auto', 'force'], true)){
			// @todo Maybe could we rename this exception
			throw new InvalidAccessTypeException('Invalid approvalPrompt, expected either auto or force.');
		}
		$this->approvalPrompt = $prompt;
	}
	/**
	 * {@inheritdoc}
	 */
	protected function getScopesDelimiter(): string{
		return ',';
	}
}
