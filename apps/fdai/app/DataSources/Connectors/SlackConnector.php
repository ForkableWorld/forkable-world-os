<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\OAuth2Connector;
use App\DataSources\QMConnectorResponse;
use App\Slim\Controller\Connector\ConnectorConnectedResponse;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Controller\Connector\ConnectorRedirectResponse;
use App\Slim\Model\Notifications\SlackNotification;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\OAuth2\Token\StdOAuth2Token;
class SlackConnector extends OAuth2Connector {
	/**
	 * Defined scopes
	 * @link https://api.slack.com/docs/oauth-scopes
	 */
	const           SCOPE_INCOMING_WEBHOOK         = 'incoming-webhook';
	const           SCOPE_COMMANDS                 = 'commands';
	const           SCOPE_BOT                      = 'bot';
	const           SCOPE_CHAT_WRITE_BOT           = 'chat:write:bot';
	const           SCOPE_LINKS_WRITE              = 'links:write';
	protected const AFFILIATE                      = false;
	protected const BACKGROUND_COLOR               = '#0f7965';
	protected const CLIENT_REQUIRES_SECRET         = true;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Social Interactions';
	protected const DEVELOPER_CONSOLE              = 'https://api.slack.com/apps/A67AGGRG9/general';
	protected const DEVELOPER_PASSWORD             = null;
	protected const DEVELOPER_USERNAME             = null;
	public const    DISPLAY_NAME                   = 'Slack';
	protected const ENABLED                        = 1;
	protected const GET_IT_URL                     = null;
	public const    IMAGE                          = 'https://upload.wikimedia.org/wikipedia/commons/7/76/Slack_Icon.png';
	protected const LOGO_COLOR                     = '#2d2d2d';
	protected const LONG_DESCRIPTION               = 'Slack brings all your communication together in one place. It\'s real-time messaging, archiving and search for modern teams.';
	protected const PREMIUM                        = false;
	protected const SHORT_DESCRIPTION              = 'Tracks social interaction';
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
	public $getItUrl = self::GET_IT_URL;
	public $id = self::ID;
	public $image = self::IMAGE;
	public $importViaApi = false;
	public $logoColor = self::LOGO_COLOR;
	public $longDescription = self::LONG_DESCRIPTION;
	public $name = self::NAME;
	public $premium = self::PREMIUM;
	public $shortDescription = self::SHORT_DESCRIPTION;
	public const ID   = 87;
	public const NAME = 'slack';
	public static array $SCOPES = [
		'incoming-webhook',
		'chat:write:bot',
		'links:write',
	];
	protected const CONNECT_URL = 'https://slack.com/oauth/authorize?&client_id=212721326246.211356569553&scope=incoming-webhook,chat:write:bot,links:write';
	/**
	 * @param array $parameters
	 * @return QMConnectorResponse|ConnectorConnectedResponse|ConnectorRedirectResponse
	 * @throws ConnectorException
	 * @throws TokenResponseException
	 */
	public function connect($parameters){
		if(isset($parameters['access_token'])){
			$credentials = ['token' => $parameters];
			$this->storeCredentials($credentials);
			return new ConnectorConnectedResponse($this);
		}
		if(isset($parameters['connectorCredentials'])){
			return $this->storeOAuthToken($parameters);
		}
		if(empty($parameters['code'])){
			return $this->getAuthorizationPageRedirectResponse();
		}
		$accessTokenResponse = $this->requestOAuth2AccessToken($parameters);
		if(!($accessTokenResponse instanceof ConnectorException)){
			$this->getQmUser()->setUserMetaValue(SlackNotification::SLACK_TOKEN_META_KEY, $this->stdOAuthToken);
		}
		return $accessTokenResponse;
	}
	/**
	 * @return ConnectorException|int|void
	 */
	public function importData(): void{
	}
	/**
	 * @return string
	 */
	public function urlUserDetails(): string{
		return 'https://slack.com/api/users.profile.get';
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationEndpoint(){
		return new Uri('https://slack.com/oauth/authorize');
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAccessTokenEndpoint(){
		return new Uri('https://slack.com/api/oauth.access');
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
}
