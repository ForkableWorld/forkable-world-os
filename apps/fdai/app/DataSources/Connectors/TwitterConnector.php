<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpMultipleClassDeclarationsInspection */
namespace App\DataSources\Connectors;
use App\DataSources\HasUserProfilePage;
use App\DataSources\OAuth1Connector;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\TooSlowException;
use App\Slim\Controller\Connector\ConnectorConnectedResponse;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Controller\Connector\ConnectorRedirectResponse;
use App\Slim\Model\Measurement\AdditionalMetaData;
use App\UI\FontAwesome;
use App\Units\EventUnit;
use App\VariableCategories\SocialInteractionsVariableCategory;
use App\Variables\CommonVariables\SocialInteractionsCommonVariables\TwitterStatusUpdateCommonVariable;
use OAuth\Common\Exception\Exception;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\OAuth1\Token\StdOAuth1Token;
use OAuth\OAuth1\Token\TokenInterface;
/** Class TwitterConnector
 * @package App\DataSources\Connectors
 */
class TwitterConnector extends OAuth1Connector {
	use HasUserProfilePage;
	public static $BASE_API_URL = "https://api.twitter.com";
	const ENDPOINT_AUTHENTICATE = "https://api.twitter.com/oauth/authenticate";
	const ENDPOINT_AUTHORIZE    = "https://api.twitter.com/oauth/authorize";
	protected $authorizationEndpoint   = self::ENDPOINT_AUTHENTICATE;
    protected const AFFILIATE = false;
    protected const BACKGROUND_COLOR = '#e4405f';
    protected const CLIENT_REQUIRES_SECRET = true;
    protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Social Interactions';
    protected const DEVELOPER_CONSOLE = 'https://twitter.com/settings/applications/new';
	
	
	public const DISPLAY_NAME = 'Twitter';
	protected const ENABLED = 1;
	protected const GET_IT_URL = 'https://twitter.com/';
	public const IMAGE = 'https://help.twitter.com/content/dam/help-twitter/brand/logo.png';
	protected const LOGO_COLOR = '#1da1f2';
	protected const LONG_DESCRIPTION = 'From breaking news and entertainment to sports and politics, get the full story with all the live commentary.';
	protected const SHORT_DESCRIPTION = 'Tracks social interaction.';
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
    public $fontAwesome = FontAwesome::TWITTER;
	public $getItUrl = self::GET_IT_URL;
	public $id = self::ID;
	public $image = self::IMAGE;
	public $logoColor = self::LOGO_COLOR;
	public $longDescription = self::LONG_DESCRIPTION;
	public $name = self::NAME;
	public $providesUserProfileForLogin = true;
    public $shortDescription = self::SHORT_DESCRIPTION;
    public const ID = 81;
    public const NAME = 'twitter';
    public static $OAUTH_SERVICE_NAME = 'Twitter';
    public static array $SCOPES = [];
    public $variableNames = [TwitterStatusUpdateCommonVariable::NAME];
	/**
	 * @param array $parameters
	 * @return ConnectorRedirectResponse|ConnectorConnectedResponse
	 * @throws \App\Slim\Controller\Connector\ConnectException
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \OAuth\Common\Http\Exception\TokenResponseException
	 * @throws \OAuth\Common\Storage\Exception\TokenNotFoundException
	 */
    public function connect($parameters){
        return $this->connectOrRedirect($parameters);
    }
	/**
	 * @param array $additionalParameters
	 * @return UriInterface
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 * @throws \OAuth\Common\Http\Exception\TokenResponseException
	 */
    public function getAuthorizationUri(array $additionalParameters = []): UriInterface{
        $token = $this->requestRequestToken();
        $additionalParameters['oauth_token'] = $token->getRequestToken();
        $additionalParameters['callback_url '] = static::getConnectUrlWithoutParams();
        return parent::getAuthorizationUri($additionalParameters);
    }
	/**
	 * @return void
	 * @throws InvalidAttributeException
	 * @throws ModelValidationException
	 * @throws TooSlowException
	 * @throws ConnectorException
	 */
    public function importData(): void {
        $statuses = $this->getRequest("https://api.twitter.com/1.1/statuses/user_timeline.json");
        $v = $this->getQMUserVariable(TwitterStatusUpdateCommonVariable::NAME, EventUnit::NAME);
        foreach($statuses as $status){
            $note = new AdditionalMetaData($status, $status->text);
            $this->addMeasurement($v->name,
                $status->created_at,
                1,
                EventUnit::NAME,
                SocialInteractionsVariableCategory::NAME,
                [],
                null,
                $note);
        }
        $this->saveMeasurements();
    }
    /**
     * @return string
     */
    public function urlUserDetails(): string{
        return 'https://api.twitter.com/1.1/account/verify_credentials.json?include_email=true';
    }
    public function getUserProfilePageUrl():string{
        return "https://twitter.com/".$this->getConnectorUserName();
    }
	/**
	 * @param bool $throwException
	 * @return string
	 */
	public function getConnectorUserName(bool $throwException = false): string{
        $str = parent::getConnectorUserName($throwException);
        if(!$str){
            /** @var StdOAuth1Token $token */
            $token = $this->getCredentialsArray();
            if(is_string($token["token"])){
                $token = unserialize($token["token"]);
            }
            return $token->getExtraParams()["screen_name"];
        }
        return $str;
    }
	/**
	 * {@inheritdoc}
	 */
	public function getRequestTokenEndpoint()
	{
		return new Uri('https://api.twitter.com/oauth/request_token');
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationEndpoint()
	{
		if ($this->authorizationEndpoint != self::ENDPOINT_AUTHENTICATE
		    && $this->authorizationEndpoint != self::ENDPOINT_AUTHORIZE) {
			$this->authorizationEndpoint = self::ENDPOINT_AUTHENTICATE;
		}
		return new Uri($this->authorizationEndpoint);
	}
	/**
	 * @param $endpoint
	 * @throws \OAuth\Common\Exception\Exception
	 */
	public function setAuthorizationEndpoint($endpoint)
	{
		if ($endpoint != self::ENDPOINT_AUTHENTICATE && $endpoint != self::ENDPOINT_AUTHORIZE) {
			throw new Exception(
				sprintf("'%s' is not a correct Twitter authorization endpoint.", $endpoint)
			);
		}
		$this->authorizationEndpoint = $endpoint;
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAccessTokenEndpoint()
	{
		return new Uri('https://api.twitter.com/oauth/access_token');
	}
	/**
	 * {@inheritdoc}
	 */
	protected function parseRequestTokenResponse(string $responseBody): TokenInterface
	{
		parse_str($responseBody, $data);
		if (!is_array($data)) {
			throw new TokenResponseException('Unable to parse response.');
		} elseif (!isset($data['oauth_callback_confirmed']) || $data['oauth_callback_confirmed'] !== 'true') {
			throw new TokenResponseException('Error in retrieving token.');
		}
		return $this->parseAccessTokenResponse($responseBody);
	}
	/**
	 * {@inheritdoc}
	 */
	protected function parseAccessTokenResponse(string $responseBody): TokenInterface
	{
		parse_str($responseBody, $data);
		if (!is_array($data)) {
			throw new TokenResponseException('Unable to parse response: ' . $responseBody);
		} elseif (isset($data['error'])) {
			throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
		} elseif (!isset($data["oauth_token"]) || !isset($data["oauth_token_secret"])) {
			throw new TokenResponseException('Invalid response. OAuth Token data not set: ' . $responseBody);
		}
		$token = new StdOAuth1Token();
		$token->setRequestToken($data['oauth_token']);
		$token->setRequestTokenSecret($data['oauth_token_secret']);
		$token->setAccessToken($data['oauth_token']);
		$token->setAccessTokenSecret($data['oauth_token_secret']);
		$token->setEndOfLife(StdOAuth1Token::EOL_NEVER_EXPIRES);
		unset($data['oauth_token'], $data['oauth_token_secret']);
		$token->setExtraParams($data);
		return $token;
	}
}
