<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection SpellCheckingInspection */
/** @noinspection PhpUnused */
namespace App\DataSources;
use App\Slim\Controller\Connector\ConnectException;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Controller\Connector\ConnectorResponse;
use App\Slim\Middleware\QMAuth;
use App\Types\QMArr;
use App\UI\FontAwesome;
use Google_Client;
use Google_Service_Oauth2;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use App\Exceptions\CredentialsNotFoundException;
use App\Slim\Controller\Connector\ConnectorRedirectResponse;
use App\Utils\AppMode;
use OAuth\OAuth2\Token\StdOAuth2Token;
abstract class GoogleBaseConnector extends OAuth2Connector
{
	/**
	 * Defined scopes - More scopes are listed here:
	 * https://developers.google.com/oauthplayground/
	 *
	 * Make a pull request if you need more scopes.
	 */
	// Basic
	const SCOPE_EMAIL                       = 'email';
	const SCOPE_PROFILE                     = 'profile';
	const SCOPE_USERINFO_EMAIL              = 'https://www.googleapis.com/auth/userinfo.email';
	const SCOPE_USERINFO_PROFILE            = 'https://www.googleapis.com/auth/userinfo.profile';
	// Google+
	const SCOPE_GPLUS_ME                    = 'https://www.googleapis.com/auth/plus.me';
	const SCOPE_GPLUS_LOGIN                 = 'https://www.googleapis.com/auth/plus.login';
	const SCOPE_GPLUS_CIRCLES_READ          = 'https://www.googleapis.com/auth/plus.circles.read';
	const SCOPE_GPLUS_CIRCLES_WRITE         = 'https://www.googleapis.com/auth/plus.circles.write';
	const SCOPE_GPLUS_STREAM_READ           = 'https://www.googleapis.com/auth/plus.stream.read';
	const SCOPE_GPLUS_STREAM_WRITE          = 'https://www.googleapis.com/auth/plus.stream.write';
	const SCOPE_GPLUS_MEDIA                 = 'https://www.googleapis.com/auth/plus.media.upload';
	const SCOPE_EMAIL_PLUS                  = 'https://www.googleapis.com/auth/plus.profile.emails.read';
	// Google Drive
	const SCOPE_DOCUMENTSLIST               = 'https://docs.google.com/feeds/';
	const SCOPE_SPREADSHEETS                = 'https://spreadsheets.google.com/feeds/';
	const SCOPE_GOOGLEDRIVE                 = 'https://www.googleapis.com/auth/drive';
	const SCOPE_DRIVE_APPS                  = 'https://www.googleapis.com/auth/drive.appdata';
	const SCOPE_DRIVE_APPS_READ_ONLY        = 'https://www.googleapis.com/auth/drive.apps.readonly';
	const SCOPE_GOOGLEDRIVE_FILES           = 'https://www.googleapis.com/auth/drive.file';
	const SCOPE_DRIVE_METADATA_READ_ONLY    = 'https://www.googleapis.com/auth/drive.metadata.readonly';
	const SCOPE_DRIVE_READ_ONLY             = 'https://www.googleapis.com/auth/drive.readonly';
	const SCOPE_DRIVE_SCRIPTS               = 'https://www.googleapis.com/auth/drive.scripts';
	// Adwords
	const SCOPE_ADSENSE                     = 'https://www.googleapis.com/auth/adsense';
	const SCOPE_ADWORDS                     = 'https://www.googleapis.com/auth/adwords';
	const SCOPE_ADWORDS_DEPRECATED          = 'https://www.googleapis.com/auth/adwords/'; //deprecated in v201406 API version
	const SCOPE_GAN                         = 'https://www.googleapis.com/auth/gan'; // google affiliate network...?
	//Doubleclick for Publishers
	const SCOPE_DFP                         = 'https://www.googleapis.com/auth/dfp';
	const SCOPE_DFP_TRAFFICKING             = 'https://www.googleapis.com/auth/dfatrafficking';
	const SCOPE_DFP_REPORTING               = 'https://www.googleapis.com/auth/dfareporting';
	// Google Analytics
	const SCOPE_ANALYTICS                   = 'https://www.googleapis.com/auth/analytics';
	const SCOPE_ANALYTICS_EDIT              = 'https://www.googleapis.com/auth/analytics.edit';
	const SCOPE_ANALYTICS_MANAGE_USERS      = 'https://www.googleapis.com/auth/analytics.manage.users';
	const SCOPE_ANALYTICS_READ_ONLY         = 'https://www.googleapis.com/auth/analytics.readonly';
	//Gmail
	const SCOPE_GMAIL_MODIFY                = 'https://www.googleapis.com/auth/gmail.modify';
	const SCOPE_GMAIL_READONLY              = 'https://www.googleapis.com/auth/gmail.readonly';
	const SCOPE_GMAIL_COMPOSE               = 'https://www.googleapis.com/auth/gmail.compose';
	const SCOPE_GMAIL_SEND                  = 'https://www.googleapis.com/auth/gmail.send';
	const SCOPE_GMAIL_INSERT                = 'https://www.googleapis.com/auth/gmail.insert';
	const SCOPE_GMAIL_LABELS                = 'https://www.googleapis.com/auth/gmail.labels';
	const SCOPE_GMAIL_FULL                  = 'https://mail.google.com/';
	// Other services
	const SCOPE_BOOKS                       = 'https://www.googleapis.com/auth/books';
	const SCOPE_BLOGGER                     = 'https://www.googleapis.com/auth/blogger';
	const SCOPE_CALENDAR                    = 'https://www.googleapis.com/auth/calendar';
	const SCOPE_CALENDAR_READ_ONLY          = 'https://www.googleapis.com/auth/calendar.readonly';
	const SCOPE_CONTACT                     = 'https://www.google.com/m8/feeds/';
	const SCOPE_CONTACTS_RO                 = 'https://www.googleapis.com/auth/contacts.readonly';
	const SCOPE_CHROMEWEBSTORE              = 'https://www.googleapis.com/auth/chromewebstore.readonly';
	const SCOPE_GMAIL                       = 'https://mail.google.com/mail/feed/atom';
	const SCOPE_GMAIL_IMAP_SMTP             = 'https://mail.google.com';
	const SCOPE_PICASAWEB                   = 'https://picasaweb.google.com/data/';
	const SCOPE_SITES                       = 'https://sites.google.com/feeds/';
	const SCOPE_URLSHORTENER                = 'https://www.googleapis.com/auth/urlshortener';
	const SCOPE_WEBMASTERTOOLS              = 'https://www.google.com/webmasters/tools/feeds/';
	const SCOPE_TASKS                       = 'https://www.googleapis.com/auth/tasks';
	// Cloud services
	const SCOPE_CLOUDSTORAGE                = 'https://www.googleapis.com/auth/devstorage.read_write';
	const SCOPE_CONTENTFORSHOPPING          = 'https://www.googleapis.com/auth/structuredcontent'; // what even is this
	const SCOPE_USER_PROVISIONING           = 'https://apps-apis.google.com/a/feeds/user/';
	const SCOPE_GROUPS_PROVISIONING         = 'https://apps-apis.google.com/a/feeds/groups/';
	const SCOPE_NICKNAME_PROVISIONING       = 'https://apps-apis.google.com/a/feeds/alias/';
	// Old
	const SCOPE_ORKUT                       = 'https://www.googleapis.com/auth/orkut';
	const SCOPE_GOOGLELATITUDE =
		'https://www.googleapis.com/auth/latitude.all.best https://www.googleapis.com/auth/latitude.all.city';
	const SCOPE_OPENID                      = 'openid';
	// YouTube
	const SCOPE_YOUTUBE_GDATA               = 'https://gdata.youtube.com';
	const SCOPE_YOUTUBE_ANALYTICS_MONETARY  = 'https://www.googleapis.com/auth/yt-analytics-monetary.readonly';
	const SCOPE_YOUTUBE_ANALYTICS           = 'https://www.googleapis.com/auth/yt-analytics.readonly';
	const SCOPE_YOUTUBE                     = 'https://www.googleapis.com/auth/youtube';
	const SCOPE_YOUTUBE_READ_ONLY           = 'https://www.googleapis.com/auth/youtube.readonly';
	const SCOPE_YOUTUBE_UPLOAD              = 'https://www.googleapis.com/auth/youtube.upload';
	const SCOPE_YOUTUBE_PARTNER             = 'https://www.googleapis.com/auth/youtubepartner';
	const SCOPE_YOUTUBE_PARTNER_AUDIT       = 'https://www.googleapis.com/auth/youtubepartner-channel-audit';
	// Google Glass
	const SCOPE_GLASS_TIMELINE              = 'https://www.googleapis.com/auth/glass.timeline';
	const SCOPE_GLASS_LOCATION              = 'https://www.googleapis.com/auth/glass.location';
	// Android Publisher
	const SCOPE_ANDROID_PUBLISHER           = 'https://www.googleapis.com/auth/androidpublisher';
	// Google Classroom
	const SCOPE_CLASSROOM_COURSES           = 'https://www.googleapis.com/auth/classroom.courses';
	const SCOPE_CLASSROOM_COURSES_READONLY  = 'https://www.googleapis.com/auth/classroom.courses.readonly';
	const SCOPE_CLASSROOM_PROFILE_EMAILS    = 'https://www.googleapis.com/auth/classroom.profile.emails';
	const SCOPE_CLASSROOM_PROFILE_PHOTOS    = 'https://www.googleapis.com/auth/classroom.profile.photos';
	const SCOPE_CLASSROOM_ROSTERS           = 'https://www.googleapis.com/auth/classroom.rosters';
	const SCOPE_CLASSROOM_ROSTERS_READONLY  = 'https://www.googleapis.com/auth/classroom.rosters.readonly';
	/** View your activity information in Google Fit. */
	const FITNESS_ACTIVITY_READ =
		"https://www.googleapis.com/auth/fitness.activity.read";
	/** View and store your activity information in Google Fit. */
	const FITNESS_ACTIVITY_WRITE =
		"https://www.googleapis.com/auth/fitness.activity.write";
	/** View body sensor information in Google Fit. */
	const FITNESS_BODY_READ =
		"https://www.googleapis.com/auth/fitness.body.read";
	/** View and store body sensor data in Google Fit. */
	const FITNESS_BODY_WRITE =
		"https://www.googleapis.com/auth/fitness.body.write";
	/** View your stored location data in Google Fit. */
	const FITNESS_LOCATION_READ =
		"https://www.googleapis.com/auth/fitness.location.read";
	/** View and store your location data in Google Fit. */
	const FITNESS_LOCATION_WRITE =
		"https://www.googleapis.com/auth/fitness.location.write";
	/** Create, edit, organize, and delete all your tasks. */
	const TASKS =
		"https://www.googleapis.com/auth/tasks";
	/** View your tasks. */
	const TASKS_READONLY =
		"https://www.googleapis.com/auth/tasks.readonly";
	/** See, edit, create, and delete all of your Google Drive files. */
	const DRIVE =
		"https://www.googleapis.com/auth/drive";
	/** View and manage Google Drive files and folders that you have opened or created with this app. */
	const DRIVE_FILE =
		"https://www.googleapis.com/auth/drive.file";
	/** See and download all your Google Drive files. */
	const DRIVE_READONLY =
		"https://www.googleapis.com/auth/drive.readonly";
	/** See, edit, create, and delete your spreadsheets in Google Drive. */
	const SPREADSHEETS =
		"https://www.googleapis.com/auth/spreadsheets";
    protected const AFFILIATE = false;
    protected const CLIENT_REQUIRES_SECRET = false;
    protected const DEVELOPER_CONSOLE = null;
    
    
    protected const OAUTH_SERVICE_NAME = 'Google';
    protected const PREMIUM = false;
    public $affiliate = self::AFFILIATE;
    public $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
    public $fontAwesome = FontAwesome::GOOGLE;
    public $mobileConnectMethod = 'google';
    public $oauthServiceName = self::OAUTH_SERVICE_NAME;
    public $premium = self::PREMIUM;
    public static $OAUTH_SERVICE_NAME = 'Google';
    /**
     * @return Google_Client
     * @throws CredentialsNotFoundException|TokenNotFoundException
     */
    protected function getGoogleClient(): Google_Client{
	    /************************************************
	     * Make an API request on behalf of a user. In
	     * this case we need to have a valid OAuth 2.0
	     * token for the user, so we need to send them
	     * through a login flow. To do this we need some
	     * information from our API console project.
	     ************************************************/
	    $client = new Google_Client();
	    $client->setClientId($this->getConnectorClientId());
	    $client->setClientSecret($this->getOrSetConnectorClientSecret());
	    $client->setAccessType('offline');
	    $client->setScopes(self::$SCOPES);
        $token = $this->getOrRefreshToken();
	    $tokenArr = $token->getExtraParams();
	    $tokenArr['access_token'] = $token->getAccessToken();
	    $tokenArr['expires_in'] = $token->getEndOfLife() - time();
		if($refeshToken = $token->getRefreshToken()){$tokenArr['refresh_token'] = $refeshToken;}
        $client->setAccessToken(json_encode($tokenArr));
        return $client;
    }
    /**
     * @return ConnectInstructions
     */
    public function getConnectInstructions(): ?ConnectInstructions{
	    if($this->connectInstructions){return $this->connectInstructions;}
	    if(!AppMode::isApiRequest()){return null;}
        $parameters = []; //Google doesn't return a refresh token unless we send this access_type : offline
        $additionalParameters = [
            'approval_prompt' => 'force',
            'prompt'          => 'consent',
        ];
        $url = $this->getAuthorizationUri($additionalParameters);
        $usePopup = true;
        return $this->connectInstructions = new ConnectInstructions($url, $parameters, $usePopup);
    }
	/**
	 * @return string
	 * @throws TokenNotFoundException
	 */
    protected function getIdTokenFromStdOauthToken(): string{
        $std = $this->getOrRefreshToken();
        return $std->getExtraParams()['id_token'];
    }
    /**
     * @return string
     */
    public function urlUserDetails(): string{
        //return 'https://www.googleapis.com/plus/v1/people/me?';
        return 'https://www.googleapis.com/userinfo/v2/me?';
    }
    public static function getClientSecret(): ?string{
        return \App\Utils\Env::get('CONNECTOR_GOOGLE_CLIENT_SECRET');
    }
    public function getConnectorClientId(): ?string{
        if(empty($this->connectorClientId)){
            $this->setConnectorClientId(\App\Utils\Env::get('CONNECTOR_GOOGLE_CLIENT_ID'));
        }
        return $this->connectorClientId;
    }
	/**
	 * @param array $additionalParameters
	 * @return ConnectorRedirectResponse
	 */
	public function getAuthorizationPageRedirectResponse(array $additionalParameters = []): ConnectorRedirectResponse{
		//Google doesn't return a refresh token unless we send this access_type : offline
		$additionalParameters['approval_prompt'] = 'force';
		$uri = $this->getAuthorizationUri($additionalParameters);
		$url = $uri->getAbsoluteUri();
		return new ConnectorRedirectResponse($this, $this->name.'/connect', $url);
	}
	/**
	 * @param array $parameters
	 * @return ConnectorRedirectResponse|ConnectorResponse
	 * @throws TokenResponseException
	 * @throws ConnectorException
	 */
	public function connect($parameters){
		$response = false;
		$authCode = QMArr::getValue($parameters, ['code', 'serverAuthCode']);
		if(isset($parameters['connectorCredentials'])){ // Token or parameters provided by the client-side authentication process
			$response = $this->storeOAuthToken($parameters);
		}else if($authCode){
			$response = $this->requestOAuth2AccessToken($parameters);
		}
		if($response){
			if(!$this->userId){
				$user = $this->tryToCreateUser();
			} else {
				$user = $this->getUser();
			}
			if(!empty($user) || !$this->providesUserProfileForLogin){
				QMAuth::login($user);
				return $response;
			}
		}
		return $this->getAuthorizationPageRedirectResponse();
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationEndpoint()
	{
		return new Uri('https://accounts.google.com/o/oauth2/auth?access_type=' . $this->accessType);
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAccessTokenEndpoint()
	{
		return new Uri('https://accounts.google.com/o/oauth2/token');
	}
	/**
	 * {@inheritdoc}
	 */
	protected function parseAccessTokenResponse(string $responseBody): StdOAuth2Token{
		$data = json_decode($responseBody, true);
		if (!is_array($data)) {
			throw new TokenResponseException('Unable to parse response.');
		} elseif (isset($data['error'])) {
			throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
		}
		return $this->newStdOAuth2Token($data);
	}
	/**
	 * Returns a class constant from ServiceInterface defining the authorization method used for the API
	 * Header is the sane default.
	 *
	 * @return int
	 */
	protected function getAuthorizationMethod(): int{
		return static::AUTHORIZATION_METHOD_HEADER_BEARER;
	}
	/**
	 * @return array
	 * @throws \OAuth\Common\Storage\Exception\TokenNotFoundException
	 */
	public function getConnectorUserProfile(): ?array {
		if($this->connectorUserProfile){return $this->connectorUserProfile;}
		$googleClient = $this->getGoogleClient();
		$objOAuthService = new Google_Service_Oauth2($googleClient);
		$userinfo = $objOAuthService->userinfo;
		$user = $userinfo->get();
		return $this->connectorUserProfile = (array)$user;
	}
}
