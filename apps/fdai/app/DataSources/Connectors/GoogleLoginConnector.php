<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\DataSources\Connectors;
use App\DataSources\GoogleBaseConnector;
use App\Exceptions\BadRequestException;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Logging\QMLog;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserUserEmailProperty;
use App\Slim\Controller\Connector\ConnectException;
use App\Slim\Controller\Connector\ConnectorConnectedResponse;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Controller\Connector\ConnectorRedirectResponse;
use App\Slim\Controller\Connector\ConnectorResponse;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Types\QMArr;
use App\Utils\AppMode;
use App\Utils\Env;
use Google_AccessToken_Verify;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\OAuth2\Token\StdOAuth2Token;
/** @package App\DataSources\Connectors
 */
class GoogleLoginConnector extends GoogleBaseConnector {
	// Test PW: Iamapassword1!
	// Test User: quantimodo.test.user@gmail.com
	protected const AFFILIATE                      = false;
	protected const BACKGROUND_COLOR               = '#23448b';
	protected const CONNECTOR_ID                   = 77;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Social Interactions';
	public const    DISPLAY_NAME                   = 'Google';
	protected const ENABLED                        = 1;
	protected const GET_IT_URL                     = null;
	protected const LOGO_COLOR                     = '#d34836';
	protected const LONG_DESCRIPTION               = 'Imports your profile information from Google';
	protected const SHORT_DESCRIPTION              = 'Imports profile information';
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $crappy = true;
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
	public $providesUserProfileForLogin = true;
	public $shortDescription = self::SHORT_DESCRIPTION;
	public const ID    = 84;
	public const IMAGE = 'https://static.quantimo.do/img/connectors/google-logo-icon-PNG-Transparent-Background.png';
	public const NAME  = 'googleplus';
	public static array $SCOPES = [
		//Google_Service_Oauth2::USERINFO_EMAIL,
		//Google_Service_Oauth2::USERINFO_PROFILE,
		//Google_Service_Oauth2::PLUS_LOGIN
		'openid', // Copied from vendor/laravel/socialite/src/Two/GoogleProvider.php
		'profile', // Copied from vendor/laravel/socialite/src/Two/GoogleProvider.php
		'email', // Copied from vendor/laravel/socialite/src/Two/GoogleProvider.php
	];
	/**
	 * @return string
	 */
	public static function getIdTokenFromRequest(): ?string{
		$idToken = QMRequest::getParam([
			'idToken',
			//'accessToken'
		]);
		if($idToken){
			return $idToken;
		}
		$idToken = QMArr::recursiveFind(qm_request()->input() + qm_request()->query(), 'idToken');
		return $idToken;
	}

    /**
     * @param array $parameters
     * @return ConnectorRedirectResponse|ConnectorResponse|ConnectorConnectedResponse
     * @throws ConnectException
     * @throws UnauthorizedException
     */
	public function connect($parameters){
		if(QMRequest::getParam('idToken')){
			$this->loginViaIdToken();
			return new ConnectorConnectedResponse($this);
		}
		return parent::connect($parameters);
	}
	/**
	 * @return void
	 * @throws ConnectorException
	 */
	public function importData(): void{
		$this->loginOrCreateUserAndUpdateMeta();
	}
	/**
	 * @return QMUser
	 * @throws UnauthorizedException
	 */
	public static function loginByRequest(): QMUser{
		$googlePlus = new GoogleLoginConnector();
		$user = $googlePlus->loginViaIdToken();
		try {
			$user->getOrCreateToken();
		} catch (ClientNotFoundException $e) {
			$user->logError(__METHOD__.": ".$e->getMessage());
		}
		return $user;
	}
	/**
	 * @return QMUser|null
	 */
	public function loginViaIdToken(): ?QMUser{
		$input = qm_request()->input() + qm_request()->query();
		$profile = QMRequest::getParam('connectorCredentials', $input, false);
		try {
			$verify = new Google_AccessToken_Verify();
			$idToken = $this->getIdToken();
			if($verificationResponse = $verify->verifyIdToken($idToken)){
				$profile = $verificationResponse;
			}
		} catch (\Throwable $e) {
			if(AppMode::isProduction()){
				QMLog::error('Could not verifyIdToken from Google login user because ' . $e->getMessage(),
					['body' => qm_request()->input()]);
			}
		}
		$email = UserUserEmailProperty::pluck($profile);
		if(empty($email)){
			throw new UnauthorizedException("No email in googleUserData: " . \App\Logging\QMLog::print_r($profile, true));
		}
		$user = QMUser::getOrCreateByEmail($email, 'google', $profile);
		$user->login();
		self::makeSureWeAreNotUsingGoogleAccessToken($user);
		$this->setUserId($user->id);
		return $user->getQMUser();
	}
	/**
	 * @param QMUser $user
	 */
	private static function makeSureWeAreNotUsingGoogleAccessToken(QMUser $user){
		$user = $user->getQMUser();
		$requestBody = qm_request()->input() + qm_request()->query();
		/** @noinspection TypeUnsafeComparisonInspection */
		if(isset($requestBody['accessToken']) && $requestBody['accessToken'] == $user->getOrSetAccessTokenString()){
			QMLog::error("Returning Google access token instead of QM access token! :( ", ['user' => $user]);
			$user->setAccessTokenStringByClientId(BaseClientIdProperty::fromRequest());
		}
	}
	public function getBaseApiUrl(): string{
		return "https://accounts.google.com";
	}
	/**
	 * {@inheritdoc}
	 */
	protected function parseAccessTokenResponse($responseBody): StdOAuth2Token{
		if(is_string($responseBody)){
			$responseBody = json_decode($responseBody, true);
		}
		if(is_object($responseBody) && stripos(get_class($responseBody), 'EntityBody') !== false){
			$responseBody = json_decode($responseBody, true);
		}
		if(!is_array($responseBody)){
			$responseBody = json_decode(json_encode($responseBody), true);
		}
		if(!$responseBody){
			throw new TokenResponseException('Unable to parse response.');
		} elseif(isset($responseBody['error'])){
			throw new TokenResponseException('Error in retrieving token: "' . $responseBody['error'] . '"');
		}
		$responseBody = QMArr::convertKeysFromCamelToSnakeCase($responseBody);
		$token = $this->newStdOAuth2Token($responseBody);
		if(!$token->getRefreshToken()){
			$m = 'No Google Login refresh token even though we have an access token!';
            QMLog::errorWithoutObfuscation($m, $responseBody);
			if(AppMode::isTestingOrStaging()){
				le($m, $responseBody);
			}
		}
		return $token;
	}
	/**
	 * @return string
	 */
	private function getIdToken(): string{
		$idToken = self::getIdTokenFromRequest();
		if(!$idToken){
			try {
				$idToken = $this->getIdTokenFromStdOauthToken();
			} catch (TokenNotFoundException $e) {
				$this->logError(__METHOD__.": ".$e->getMessage());
			}
		}
		if(!$idToken){
			throw new BadRequestException("Please provide Google idToken");
		}
		return $idToken;
	}
	public static function clientID(): ?string{
		$val = Env::get('CONNECTOR_GOOGLE_CLIENT_ID');
		if(empty($val)){return parent::clientID();}
		return $val;
	}
}
