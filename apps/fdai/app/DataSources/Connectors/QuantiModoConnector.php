<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\AppSettings\AppSettings;
use App\Buttons\Links\AboutUsButton;
use App\Buttons\QMButton;
use App\Computers\ThisComputer;
use App\DataSources\OAuth2Connector;
use App\DataSources\QMClient;
use App\Logging\ConsoleLog;
use App\Models\Application;
use App\Models\Connection;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\InvalidClientIdException;
use App\Http\Urls\IntendedUrl;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseClientSecretProperty;
use App\Slim\Configuration\RouteConfiguration;
use App\Slim\View\Request\QMRequest;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Utils\UrlHelper;
use App\Variables\QMVariable;
use OAuth\Common\Http\Uri\Uri;
use OAuth\OAuth2\Token\StdOAuth2Token;
/** Class QuantiModoConnector
 * @package App\DataSources\Connectors
 */
class QuantiModoConnector extends OAuth2Connector {
	/**
	 * Scopes
	 * @var string
	 */
	public const SCOPE_readmeasurements = 'readmeasurements', SCOPE_writemeasurements        = 'writemeasurements';
	protected const                                           DEVELOPER_CONSOLE              = null;
	protected const                                           DEVELOPER_PASSWORD             = null;
	protected const                                           DEVELOPER_USERNAME             = null;
	protected const                                           TEST_PASSWORD                  = null;
	protected const                                           TEST_USERNAME                  = null;
	protected const                                           AFFILIATE                      = false;
	protected const                                           BACKGROUND_COLOR               = '#e4405f';
	protected const                                           CLIENT_REQUIRES_SECRET         = true;
	protected const                                           DEFAULT_VARIABLE_CATEGORY_NAME = 'Symptoms';
	public const                                              DISPLAY_NAME                   = 'QuantiModo';
	protected const                                           ENABLED                        = 1;
	protected const                                           GET_IT_URL                     = AboutUsButton::QM_INFO_URL;
	public const                                              ID                             = 72;
	public const                                              IMAGE                          = 'https://static.quantimo.do/img/logos/quantimodo-logo-qm-rainbow-200-200.png';
	protected const                                           LONG_DESCRIPTION               = 'QuantiModo allows you to easily track mood, symptoms, or any outcome you want to optimize in a fraction of a second.  You can also import your data from over 30 other apps and devices.  QuantiModo then analyzes your data to identify which hidden factors are most likely to be influencing your mood or symptoms.';
	public const                                              NAME                           = 'quantimodo';
	protected const                                           SHORT_DESCRIPTION              = 'Tracks anything';
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
	public $getItUrl = self::GET_IT_URL;
	public $id = self::ID;
	public $image = self::IMAGE;
	public $longDescription = self::LONG_DESCRIPTION;
	public $name = self::NAME;
	public $shortDescription = self::SHORT_DESCRIPTION;
	// Keys for callback https://local.quantimo.do/api/connectors/quantimodo/connect
	// Dev Console: https://quantimodo.com/settings/applications/new
	// Contact m@thinkbynumbers.org to add you to the app admins
	public static $OAUTH_SERVICE_NAME = 'QuantiModo';
	public static array $SCOPES = [
		RouteConfiguration::SCOPE_READ_MEASUREMENTS,
        RouteConfiguration::SCOPE_WRITE_MEASUREMENTS,
	];
	public $providesUserProfileForLogin = true;
	private ?AppSettings $quantiModoClientAppSettings = null;
	public $importViaApi = false;
	/**
	 * @param string $url
	 * @return string
	 */
	public static function convertToTestUrl(string $url): string{
		$endpointToUse = static::getBaseHostname();
		if(AppMode::isUnitOrStagingUnitTest()){
			$url = str_replace("staging.quantimo.do", $endpointToUse, $url);
			$url = str_replace(ThisComputer::LOCAL_HOST_NAME, $endpointToUse, $url);
			$url = str_replace("testing.quantimo.do", $endpointToUse, $url);
		}
		return $url;
	}
	/**
	 * @return void
	 */
	public function importData(): void{
		ConsoleLog::info("Importing data from QuantiModo not implemented yet");
	}
	/**
	 * @return string
	 */
	public function urlUserDetails(): string{
		return $this->getBaseApiUrl()."/api/user";
	}
	/**
	 * @return string
	 */
	public function getOrSetConnectorClientSecret(): ?string{
		if($this->connectorClientSecret !== null){
			return $this->connectorClientSecret;
		}
		return $this->setConnectorClientSecret();
	}
	/**
	 * @return string
	 */
	public function setConnectorClientSecret(){
		$clientSecret = BaseClientSecretProperty::fromRequest();
		if(!$clientSecret){
			$clientSecret = IntendedUrl::getQueryParam(QMClient::FIELD_CLIENT_SECRET);
		}
		$clientIdFromReferrerSubDomain = BaseClientIdProperty::fromReferrerSubDomain();
		if($clientIdFromReferrerSubDomain && !$clientSecret && AppMode::isApiRequest()){
			$appSettings = $this->getQuantiModoClientAppSettings();
			if(!$appSettings){
				$this->logError("Could not get QuantiModoClientAppSettings with clientIdFromReferrerSubDomain: " .
					$clientIdFromReferrerSubDomain);
			} else{
				$clientSecret = $appSettings->clientSecret;
			}
		}
		if(QMStr::isNullString($clientSecret)){
			le("QuantiModo client secret should not be a null string! Referrer is " . QMRequest::getReferrer());
		}
		if(!$clientSecret){
			return $this->connectorClientSecret = false;
		}
		return $this->connectorClientSecret = $clientSecret;
	}
	protected function getBaseApiUrl(): string{
		$hostname = $this->getBaseHostname();
		return "https://".$hostname;
	}
	/**
	 * @return string
	 */
	public function getConnectorClientId(): ?string{
		if($this->connectorClientId !== null){
			return $this->connectorClientId;
		}
		if(!AppMode::isApiRequest()){
			return $this->setConnectorClientId(BaseClientIdProperty::CLIENT_ID_QUANTIMODO);
		}
		$clientId = BaseClientIdProperty::fromRequest(false);
		if(!$clientId){
			$clientId = IntendedUrl::getQueryParam(QMClient::FIELD_CLIENT_ID);
		}
		if(!$clientId && $this->getQuantiModoClientAppSettings()){
			$clientId = $this->getQuantiModoClientAppSettings()->clientId;
		}
		if(!$clientId){
			return $this->connectorClientId = false;
		}
		return $this->setConnectorClientId($clientId);
	}
	/**
	 * @return AppSettings
	 */
	private function getQuantiModoClientAppSettings(){
		if($this->quantiModoClientAppSettings !== null){
			return $this->quantiModoClientAppSettings;
		}
		try {
			$clientId = BaseClientIdProperty::fromRequest(false);
			if(!$clientId){
				return null;
			}
			$this->quantiModoClientAppSettings = Application::getClientAppSettings();
		} catch (InvalidClientIdException | ClientNotFoundException $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());
		}
		if(!$this->quantiModoClientAppSettings){
			$this->quantiModoClientAppSettings = false;
		}
		return $this->quantiModoClientAppSettings;
	}
	/**
	 * @return QMButton[]
	 */
	public function setDefaultButtons(Connection $connection = null): array{
		return $this->setReminderButtons();
	}
	/**
	 * @param QMVariable|\App\Models\Variable $variable
	 * @param array $urlParams
	 * @return mixed
	 */
	public function setInstructionsHtml($variable, array $urlParams = []): string{
		return $this->setInboxInstructionsHtml($variable, $urlParams);
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationEndpoint(){
		$register = QMRequest::getParam('register');
		if($register === null){
			$register = 'true';
		}
		$url = UrlHelper::getApiV3UrlForPath('oauth2/authorize?register=' . $register);
		$url = QuantiModoConnector::convertToTestUrl($url);
		return new Uri($url);
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAccessTokenEndpoint(){
		$url = UrlHelper::getApiV3UrlForPath('oauth2/token');
		$url = QuantiModoConnector::convertToTestUrl($url);
		return new Uri($url);
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
		return $this->parseStandardAccessTokenResponse($responseBody);
	}
	/**
	 * {@inheritdoc}
	 */
	protected function getExtraOAuthHeaders(): array{
		return [
			'Authorization' => 'Basic ' .
				base64_encode($this->getConnectorClientId() . ':' . $this->getOrSetConnectorClientSecret()),
		];
	}
	/**
	 * @return string
	 */
	private static function getBaseHostname(): string{
		return UrlHelper::getHostName();
		$hostname = "app.quantimo.do";
		if(AppMode::isUnitOrStagingUnitTest()){
			$hostname = "staging.quantimo.do";
		}
		if($h = \App\Utils\Env::get('QM_CONNECTOR_HOST')){
			$hostname = $h;
		}
		return $hostname;
	}
}
