<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\AppSettings\AdditionalSettings\DownloadLinks;
use App\AppSettings\HostAppSettings;
use App\Buttons\QMButton;
use App\Buttons\States\SettingsStateButton;
use App\DataSources\ConnectInstructions;
use App\DataSources\ConnectParameter;
use App\DataSources\OAuth2Connector;
use App\DataSources\QMClient;
use App\DataSources\QMDataSource;
use App\Models\Connection;
use App\Properties\Base\BaseClientIdProperty;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Token\TokenInterface;
/** Class amazonConnector
 * @package App\DataSources\Connectors
 */
class AmazonConnector extends OAuth2Connector {
	/**
	 * Defined scopes
	 * @link https://images-na.ssl-images-amazon.com/images/G/01/lwa/dev/docs/website-developer-guide._TTH_.pdf
	 */
	const           SCOPE_PROFILE                  = 'profile';
	const           SCOPE_POSTAL_CODE              = 'postal_code';
	protected const AFFILIATE                      = true;
	protected const BACKGROUND_COLOR               = '#fa4876';
	protected const CLIENT_REQUIRES_SECRET         = false;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Treatments';
	public const    DISPLAY_NAME                   = 'Amazon';
	protected const ENABLED                        = 0;
	public const    ID                             = 79;
	public const    IMAGE                          = 'https://lofrev.net/wp-content/photos/2016/06/amazon-logo-1.png';
	protected const LOGO_COLOR                     = '#ff9900';
	public const    NAME                           = 'amazon';
	protected const SHORT_DESCRIPTION              = 'Imports foods and nutritional supplements via Chrome extension';
	protected const LONG_DESCRIPTION               = 'Imports foods and nutritional supplements via Chrome extension';
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
	public $id = self::ID;
	public $image = self::IMAGE;
	public $importViaApi = false;
	public $logoColor = self::LOGO_COLOR;
	public $name = self::NAME;
	public $shortDescription = self::SHORT_DESCRIPTION;
	public $longDescription = self::LONG_DESCRIPTION;
	
	
	
	public static array $SCOPES = ['profile'];
	/**
	 * @param $userId
	 */
	public function __construct($userId){
		parent::__construct($userId);
		if(self::ENABLED){
			$s = HostAppSettings::instance();
			$this->getItUrl = $s->getAdditionalSettings()->getDownloadLinks()->chromeExtension;
			$this->longDescription =
				'Automatically import your foods and nutritional supplements. You can also enjoy ' .
				$s->appDisplayName .
				' Plus and support us for free by allowing our Chrome extension to automatically add our affiliate code at checkout.';
		}
	}
	/**
	 * @return QMButton[]
	 */
	public function setDefaultButtons(Connection $connection = null): array{
		$this->buttons = [];
		if(!QMClient::isChromeExtension()){
			$getItHere = QMDataSource::getItHereButton(DownloadLinks::DEFAULT_CHROME_EXTENSION_DOWNLOAD_LINK,
				BaseClientIdProperty::CLIENT_ID_QUANTIMODO);
			//$s = AppSettings::getGlobalHostAppSettings(); // This is too slow and connector isn't even enabled
			//$getItHere = Button::getItHereButton($s->getAdditionalSettings()->getDownloadLinks()->chromeExtension, $s->clientId);
			$this->buttons[] = $getItHere;
		} else{
			$this->buttons[] = new SettingsStateButton();
		}
		return $this->buttons;
	}
	/**
	 * @return ConnectInstructions
	 * @throws \App\Slim\Controller\Connector\ConnectException
	 * @throws \OAuth\Common\Storage\Exception\TokenNotFoundException
	 */
	public function getConnectInstructions(): ?ConnectInstructions{
		$appSettings = HostAppSettings::instance();
		$url = $appSettings->getAdditionalSettings()->getDownloadLinks()->chromeExtension;
		if(!QMClient::isChromeExtension()){
			return $this->connectInstructions = new ConnectInstructions($url, [], false);
		}
		$credentials = $this->getCredentialsArray();
		if(empty($credentials) || !isset($credentials['importPurchases'])){
			$importPurchases = $parameters['importPurchases'] = true;
			$addAffiliateTag = $parameters['addAffiliateTag'] = true;
			$this->connect($parameters);
		} else{
			$importPurchases = $credentials['importPurchases'];
			$addAffiliateTag = $credentials['addAffiliateTag'];
		}
		$param =
			new ConnectParameter('Support ' . $appSettings->appDisplayName, 'addAffiliateTag', 'bool', $addAffiliateTag,
				$addAffiliateTag);
		$param->setHelpText('Support ' . $appSettings->appDisplayName . ' when shopping at Amazon');
		$parameters[] = $param;
		$param =
			new ConnectParameter('Import Purchases', 'importPurchases', 'bool', $importPurchases, $importPurchases);
		$param->setHelpText('Automatically import you food and nutritional supplement purchases');
		$parameters[] = $param;
		return $this->connectInstructions = new ConnectInstructions($url, $parameters, false);
	}
	public function importData(): void{
		$loginName = $this->getConnectorUserEmail();
		$this->logDebug("Login name: $loginName");
	}
	/**
	 * @return string|bool
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
	public function getConnectorUserEmail(): ?string{
		$profile = $this->getConnectorUserProfile();
		if(!$profile){
			return null;
		}
		return $this->connectorUserEmail = $profile['PrimaryEmail'];
	}
	/**
	 * @return string
	 */
	public function urlUserDetails(): string{
		return 'https://api.amazon.com/user/profile';
	}
	/**
	 * @return string
	 */
	public function getConnectorClientId(): ?string{
		return $this->setConnectorClientId(\App\Utils\Env::get('AWS_ACCESS_KEY_ID'));
	}
	/**
	 * @return string
	 */
	public function getOrSetConnectorClientSecret(): ?string{
		return static::getClientSecret();
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationEndpoint(){
		return new Uri('https://www.amazon.com/ap/oa');
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAccessTokenEndpoint(): Uri{
		return new Uri('https://www.amazon.com/ap/oatoken');
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
	protected function parseAccessTokenResponse($responseBody): TokenInterface{
		$data = $this->jsonDecodeAccessTokenResponse($responseBody);
		return $this->newStdOAuth2Token($data);
	}
}
