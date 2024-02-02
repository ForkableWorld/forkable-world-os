<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\DataSources\Connectors;
use App\DataSources\HasUserProfilePage;
use App\DataSources\OAuth2Connector;
use App\Exceptions\CredentialsNotFoundException;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Model\Measurement\AdditionalMetaData;
use App\Slim\Model\Measurement\MeasurementSet;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\Units\EventUnit;
use App\Utils\AppMode;
use App\Variables\QMUserVariable;
use LogicException;
use OAuth\Common\Exception\Exception;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Token\Exception\ExpiredTokenException;
use OAuth\OAuth2\Token\StdOAuth2Token;
/** Class FacebookConnector
 * @package App\DataSources\Connectors
 */
class FacebookConnector extends OAuth2Connector {
	/**
	 * Facebook www url - used to build dialog urls
	 */
	const WWW_URL = 'https://www.facebook.com/';
	/**
	 * Defined scopes
	 * If you don't think this is scary you should not be allowed on the web at all
	 * @link https://developers.facebook.com/docs/reference/login/
	 * @link https://developers.facebook.com/tools/explorer For a list of permissions use 'Get Access Token'
	 */
	// Default scope
	const SCOPE_PUBLIC_PROFILE = 'public_profile';
	// Email scopes
	const SCOPE_EMAIL = 'email';
	// Extended permissions
	const SCOPE_READ_FRIENDLIST         = 'read_friendlists';
	const SCOPE_READ_INSIGHTS           = 'read_insights';
	const SCOPE_READ_MAILBOX            = 'read_mailbox';
	const SCOPE_READ_PAGE_MAILBOXES     = 'read_page_mailboxes';
	const SCOPE_READ_REQUESTS           = 'read_requests';
	const SCOPE_READ_STREAM             = 'read_stream';
	const SCOPE_VIDEO_UPLOAD            = 'video_upload';
	const SCOPE_XMPP_LOGIN              = 'xmpp_login';
	const SCOPE_USER_ONLINE_PRESENCE    = 'user_online_presence';
	const SCOPE_FRIENDS_ONLINE_PRESENCE = 'friends_online_presence';
	const SCOPE_ADS_MANAGEMENT          = 'ads_management';
	const SCOPE_ADS_READ                = 'ads_read';
	const SCOPE_CREATE_EVENT            = 'create_event';
	const SCOPE_CREATE_NOTE             = 'create_note';
	const SCOPE_EXPORT_STREAM           = 'export_stream';
	const SCOPE_MANAGE_FRIENDLIST       = 'manage_friendlists';
	const SCOPE_MANAGE_NOTIFICATIONS    = 'manage_notifications';
	const SCOPE_PHOTO_UPLOAD            = 'photo_upload';
	const SCOPE_PUBLISH_ACTIONS         = 'publish_actions';
	const SCOPE_PUBLISH_CHECKINS        = 'publish_checkins';
	const SCOPE_PUBLISH_STREAM          = 'publish_stream';
	const SCOPE_RSVP_EVENT              = 'rsvp_event';
	const SCOPE_SHARE_ITEM              = 'share_item';
	const SCOPE_SMS                     = 'sms';
	const SCOPE_STATUS_UPDATE           = 'status_update';
	// Extended Profile Properties
	const SCOPE_USER_POSTS                    = 'user_posts';
	const SCOPE_USER_FRIENDS                  = 'user_friends';
	const SCOPE_USER_ABOUT                    = 'user_about_me';
	const SCOPE_USER_TAGGED_PLACES            = 'user_tagged_places';
	const SCOPE_FRIENDS_ABOUT                 = 'friends_about_me';
	const SCOPE_USER_ACTIVITIES               = 'user_activities';
	const SCOPE_FRIENDS_ACTIVITIES            = 'friends_activities';
	const SCOPE_USER_BIRTHDAY                 = 'user_birthday';
	const SCOPE_FRIENDS_BIRTHDAY              = 'friends_birthday';
	const SCOPE_USER_CHECKINS                 = 'user_checkins';
	const SCOPE_FRIENDS_CHECKINS              = 'friends_checkins';
	const SCOPE_USER_EDUCATION                = 'user_education_history';
	const SCOPE_FRIENDS_EDUCATION             = 'friends_education_history';
	const SCOPE_USER_EVENTS                   = 'user_events';
	const SCOPE_FRIENDS_EVENTS                = 'friends_events';
	const SCOPE_USER_GROUPS                   = 'user_groups';
	const SCOPE_USER_MANAGED_GROUPS           = 'user_managed_groups';
	const SCOPE_FRIENDS_GROUPS                = 'friends_groups';
	const SCOPE_USER_HOMETOWN                 = 'user_hometown';
	const SCOPE_FRIENDS_HOMETOWN              = 'friends_hometown';
	const SCOPE_USER_INTERESTS                = 'user_interests';
	const SCOPE_FRIEND_INTERESTS              = 'friends_interests';
	const SCOPE_USER_LIKES                    = 'user_likes';
	const SCOPE_FRIENDS_LIKES                 = 'friends_likes';
	const SCOPE_USER_LOCATION                 = 'user_location';
	const SCOPE_FRIENDS_LOCATION              = 'friends_location';
	const SCOPE_USER_NOTES                    = 'user_notes';
	const SCOPE_FRIENDS_NOTES                 = 'friends_notes';
	const SCOPE_USER_PHOTOS                   = 'user_photos';
	const SCOPE_USER_PHOTO_VIDEO_TAGS         = 'user_photo_video_tags';
	const SCOPE_FRIENDS_PHOTOS                = 'friends_photos';
	const SCOPE_FRIENDS_PHOTO_VIDEO_TAGS      = 'friends_photo_video_tags';
	const SCOPE_USER_QUESTIONS                = 'user_questions';
	const SCOPE_FRIENDS_QUESTIONS             = 'friends_questions';
	const SCOPE_USER_RELATIONSHIPS            = 'user_relationships';
	const SCOPE_FRIENDS_RELATIONSHIPS         = 'friends_relationships';
	const SCOPE_USER_RELATIONSHIPS_DETAILS    = 'user_relationship_details';
	const SCOPE_FRIENDS_RELATIONSHIPS_DETAILS = 'friends_relationship_details';
	const SCOPE_USER_RELIGION                 = 'user_religion_politics';
	const SCOPE_FRIENDS_RELIGION              = 'friends_religion_politics';
	const SCOPE_USER_STATUS                   = 'user_status';
	const SCOPE_FRIENDS_STATUS                = 'friends_status';
	const SCOPE_USER_SUBSCRIPTIONS            = 'user_subscriptions';
	const SCOPE_FRIENDS_SUBSCRIPTIONS         = 'friends_subscriptions';
	const SCOPE_USER_VIDEOS                   = 'user_videos';
	const SCOPE_FRIENDS_VIDEOS                = 'friends_videos';
	const SCOPE_USER_WEBSITE                  = 'user_website';
	const SCOPE_FRIENDS_WEBSITE               = 'friends_website';
	const SCOPE_USER_WORK                     = 'user_work_history';
	const SCOPE_FRIENDS_WORK                  = 'friends_work_history';
	// Open Graph Permissions
	const SCOPE_USER_MUSIC    = 'user_actions.music';
	const SCOPE_FRIENDS_MUSIC = 'friends_actions.music';
	const SCOPE_USER_NEWS     = 'user_actions.news';
	const SCOPE_FRIENDS_NEWS  = 'friends_actions.news';
	const SCOPE_USER_VIDEO    = 'user_actions.video';
	const SCOPE_FRIENDS_VIDEO = 'friends_actions.video';
	const SCOPE_USER_APP      = 'user_actions:APP_NAMESPACE';
	const SCOPE_FRIENDS_APP   = 'friends_actions:APP_NAMESPACE';
	const SCOPE_USER_GAMES    = 'user_games_activity';
	const SCOPE_FRIENDS_GAMES = 'friends_games_activity';
	//Page Permissions
	const SCOPE_PAGES           = 'manage_pages';
	const SCOPE_PAGES_MESSAGING = 'pages_messaging';
	const SCOPE_PUBLISH_PAGES   = 'publish_pages';
	use HasUserProfilePage;
	// Might need to click CONTINUE and go through dumbass app review again
	// Test User: https://developers.facebook.com/apps/225078261031461/roles/test-users/
	//private static $URL_PAGELIKES = 'https://graph.facebook.com/me/likes?fields=created_time&limit=_LIMIT_';
	private $profileImages;
	private static $URL_PAGE_LIKES = 'https://graph.facebook.com/me/likes?limit=_LIMIT_';
	protected const AFFILIATE              = false;
	protected const BACKGROUND_COLOR       = '#3b579d';
	protected const CLIENT_REQUIRES_SECRET = false;
	protected const COMBINATION_OPERATION  = 'SUM';
	protected const DEVELOPER_CONSOLE      = 'https://developers.facebook.com/apps/593060094090917/settings/basic/';
	protected const DEVELOPER_PASSWORD     = null;
	protected const DEVELOPER_USERNAME     = null;
	public const    DISPLAY_NAME           = 'Facebook';
	protected const ENABLED                = 1;
	protected const FB_BASE_API_URL        = "https://graph.facebook.com/v2.9";
	protected const GET_IT_URL             = 'https://www.facebook.com';
	protected const LOGO_COLOR             = '#3b5998';
	protected const LONG_DESCRIPTION       = 'Facebook is a social networking website where users may create a personal profile, add other users as friends, and exchange messages.';
	protected const SCOPES                 = ['user_likes', 'user_posts'];
	protected const SHORT_DESCRIPTION      = 'Tracks social interaction. QuantiModo requires permission to access your Facebook "user likes" and "user posts".';
	protected const VARIABLE_CATEGORY_NAME = 'Social Interactions';
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $defaultVariableCategoryName = self::VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
	public $fontAwesome = FontAwesome::FACEBOOK;
	public $getItUrl = self::GET_IT_URL;
	public $id = self::ID;
	public $image = self::IMAGE;
	public $logoColor = self::LOGO_COLOR;
	public $longDescription = self::LONG_DESCRIPTION;
	public $mobileConnectMethod = 'facebook';
	public $name = self::NAME;
	public $providesUserProfileForLogin = true;
	public $scopes = self::SCOPES;
	public $shortDescription = self::SHORT_DESCRIPTION;
	public $stdOAuthToken;
	public const APP_PERMISSIONS_PAGE                 = 'https://developers.facebook.com/apps';
	public const ID                                   = 8;
	public const IMAGE                                = 'https://applets.imgix.net/https%3A%2F%2Fassets.ifttt.com%2Fimages%2Fchannels%2F29%2Ficons%2Fon_color_large.png%3Fversion%3D0?ixlib=rails-2.1.3&w=240&h=240&auto=compress&s=216b3768523a87b6eadc8f3ee46dcef7';
	public const NAME                                 = 'facebook';
	public const VARIABLE_NAME_COMMENTS_ON_YOUR_POSTS = "Comments on Your Facebook Posts";
	public const VARIABLE_NAME_LIKES_OF_YOUR_POSTS    = "Likes on Your Facebook Posts";
	public const VARIABLE_NAME_YOUR_PAGE_LIKES        = 'Facebook Pages Liked';
	public const VARIABLE_NAME_YOUR_POSTS             = 'Facebook Posts Made';
	public static $OAUTH_SERVICE_NAME = 'Facebook';
	public static $BASE_API_URL = self::FB_BASE_API_URL;
	public static array $SCOPES = [
		//'user_likes',
		//'user_posts' // TODO: Apply for access
	];
	/**
	 * @return int
	 * @throws InvalidVariableValueAttributeException
	 * @throws Exception
	 * @throws ExpiredTokenException
	 */
	public function importData(): void{
		$fromTime = $this->getFromTime();
		$this->getPageLikes($fromTime);
		$this->getPosts($fromTime);
		//$this->getPostLikes($fromTime);
	}
	/**
	 * @return array
	 * @throws Exception
	 * @throws ExpiredTokenException
	 */
	public function setConnectorUserData(): array{
		$response =
			$this->getDecodedFacebookResponse('/me?locale=en_US&fields=name,email,birthday,cover,currency,devices,first_name,gender,hometown,last_name,locale,quotes');
		$response->imageUrl = $this->getDecodedFacebookResponse("/me/picture?redirect=false");
		return $this->connectorUserProfile = $response;
	}
	/**
	 * @param $responseObject
	 * @return null
	 */
	private function getNextUrl($responseObject){
		if(property_exists($responseObject, 'paging') && property_exists($responseObject->paging, 'next')){
			$url = $responseObject->paging->next;
			$this->logDebug("Facebook Connector: Next page: $url");
		} else{
			$url = null;
			$this->logDebug('Facebook Connector: No more pages available');
		}
		return $url;
	}
	/**
	 * Gets Facebook likes
	 * @param $fromTime
	 * @return void
	 * @throws Exception
	 * @throws ExpiredTokenException
	 */
	private function getPageLikes($fromTime): void{
		$timeDiffSeconds = time() - $fromTime;
		$timeDiffDays = round($timeDiffSeconds / (60 * 60 * 24));
		$likesPerRequest = max(10, min(1000, $timeDiffDays * 20)); // Request a reasonable number of likes per day
		$url = str_replace('_LIMIT_', $likesPerRequest, self::$URL_PAGE_LIKES);
		$likesMeasurements = [];
		// Loop through the feed as long as the activities are newer
		// than our last update and we have a nextPage
		while($url){
			if($this->haveEnoughTestMeasurements([self::VARIABLE_NAME_YOUR_PAGE_LIKES])){
				break;
			}
			$responseObject = $this->getDecodedFacebookResponse($url);
			$i = $this->getLastStatusCode();
			if($i === 200){
				$url = $this->getNextUrl($responseObject);
				foreach($responseObject->data as $currentPageLike){
					if(property_exists($currentPageLike, 'created_time')){
						$timestamp = strtotime($currentPageLike->created_time);
					} else{
						$timestamp = $fromTime;
					}
					if($timestamp < $fromTime){
						$url = null;
						break;
					}
					$likesMeasurements[] = new QMMeasurement($timestamp, 1, [
						'message' => $currentPageLike->name,
						'url' => "https://facebook.com/pages/-/$currentPageLike->id",
					]);
				}
			} else{
				$this->handleUnsuccessfulResponses($responseObject);
			}
		}
		$measurementSet = new MeasurementSet(self::VARIABLE_NAME_YOUR_PAGE_LIKES, $likesMeasurements, 'event',
			self::VARIABLE_CATEGORY_NAME, $this->displayName, self::COMBINATION_OPERATION);
		$this->saveMeasurementSets([$measurementSet]);
	}
	/**
	 * @param $postId
	 * @return mixed
	 * @throws Exception
	 * @throws ExpiredTokenException
	 * @noinspection PhpUnusedPrivateMethodInspection
	 */
	private function getPostComments($postId){
		$response = $this->getDecodedFacebookResponse(self::FB_BASE_API_URL . "/$postId/comments");
		return $response->data;
	}

    /**
     * @param string $url
     * @return array|object
     * @throws ConnectorException
     * @throws TokenNotFoundException
     */
	private function getDecodedFacebookResponse(string $url){
		$accessTokenString = $this->getAccessTokenString();
		if(!$accessTokenString){
			throw new CredentialsNotFoundException($this);
		}
		$app_secret_proof = hash_hmac('sha256', $accessTokenString, static::getClientSecret());
		$extraHeaders = ["appsecret_proof" => $app_secret_proof];
		return $this->getRequest($url, [], $extraHeaders);
	}
	/**
	 * @param $currentPost
	 * @param $fromTime
	 * @return bool|int
	 */
	private function getTimeStampFromPost($currentPost, $fromTime){
		if(isset($currentPost->updated_time)){
			$timestamp = strtotime($currentPost->updated_time);
		} elseif(isset($currentPost->created_time)){
			$timestamp = strtotime($currentPost->created_time);
		} else{
			$this->logError("No time provided with post", [(array)$currentPost]);
			return false;
		}
		if($timestamp < $fromTime || $timestamp < 948915529){
			$this->logError('Facebook Connector: Timestamp is less than fromTime or year 2000. Continuing to next post.',
				[
					'currentPost' => $currentPost,
					'currentUrl' => $this->currentUrl,
				]);
			return false;
		}
		return $timestamp;
	}
	/**
	 * Gets Facebook posts
	 * Thanks to Graph API limitations we can only get the most recent 100 posts
	 * @param $fromTime
	 * @return void
	 * @throws InvalidVariableValueAttributeException
	 * @throws Exception
	 * @throws ExpiredTokenException
	 */
	private function getPosts($fromTime): void{
		// Ctrl and click to update at https://developers.facebook.com/tools/explorer/?method=GET&path=me%2Fposts%3Ffields%3Dcreated_time%2Cicon%2Cmessage%2Cdescription%2Clink%2Cfull_picture%2Cfrom%2Cname%2Cstory%2Cupdated_time%2Clikes%7Bpic%2Cid%2Clink%2Cname%2Cpic_square%2Cusername%7D%2Ccomments%7Bcreated_time%2Cfrom%2Cmessage%2Cobject%2Cpermalink_url%2Cuser_likes%2Cid%2Cparent%2Ccomments%2Clikes%7D%2Cpicture%2Cobject_id%2Cpermalink_url%2Creactions&version=v2.9
		$postsUrl = self::FB_BASE_API_URL .
			"/me/posts?fields=created_time%2Cicon%2Cmessage%2Cdescription%2Clink%2Cfull_picture%2Cfrom%2Cname%2Cstory%2Cupdated_time%2Clikes%7Bpic%2Cid%2Clink%2Cname%2Cpic_square%2Cusername%7D%2Ccomments%7Bcreated_time%2Cfrom%2Cmessage%2Cobject%2Cpermalink_url%2Cuser_likes%2Cid%2Cparent%2Ccomments%2Clikes%7D%2Cpicture%2Cobject_id%2Cpermalink_url%2Creactions";
		$url = $postsUrl . '&since=' . $fromTime . "&limit=100"; // Time-based pagination
		while($url){  // Loop through the feed as long as the activities are newer than our last update and we have a nextPage
			if($this->haveEnoughTestMeasurements([
				self::VARIABLE_NAME_COMMENTS_ON_YOUR_POSTS,
				self::VARIABLE_NAME_YOUR_POSTS,
				self::VARIABLE_NAME_LIKES_OF_YOUR_POSTS,
			])){
				break;
			}
			$postListResponse = $this->getDecodedFacebookResponse($url);
			$i = $this->getLastStatusCode();
			if($i === 200){
				$url = $this->getNextUrl($postListResponse);
				foreach($postListResponse->data as $currentPost){
					$timestamp = $this->getTimeStampFromPost($currentPost, $fromTime);
					if(!$timestamp){
						continue;
					}
					$this->addPostMeasurementItem($currentPost, $timestamp);
					$this->addPostLikesMeasurementItems($currentPost, $timestamp);
					$this->addPostCommentMeasurementItems($currentPost);
				}
			} else{
				$this->handleUnsuccessfulResponses($postListResponse);
			}
		}
		$this->saveMeasurements();
	}
	/**
	 * @return QMUserVariable
	 */
	private function getPostLikesVariable(): QMUserVariable{
		return $this->getQMUserVariable(self::VARIABLE_NAME_LIKES_OF_YOUR_POSTS, EventUnit::NAME);
	}
	/**
	 * @return QMUserVariable
	 */
	private function getPostsVariable(): QMUserVariable{
		return $this->getQMUserVariable(self::VARIABLE_NAME_YOUR_POSTS, EventUnit::NAME);
	}
	/**
	 * @return QMUserVariable
	 */
	private function getPostCommentsVariable(): QMUserVariable{
		return $this->getQMUserVariable(self::VARIABLE_NAME_COMMENTS_ON_YOUR_POSTS, EventUnit::NAME);
	}
	/**
	 * @param $currentPost
	 * @param $timestamp
	 * @throws InvalidVariableValueAttributeException
	 */
	private function addPostLikesMeasurementItems($currentPost, $timestamp){
		if(isset($currentPost->likes)){
			foreach($currentPost->likes->data as $like){
				$this->profileImages[$like->name] = $like->pic;
				$likeNote = new AdditionalMetaData($currentPost);
				$likeNote->populateFieldsByArrayOrObject($like);
				if(isset($currentPost->name)){
					$likeNote->message = $currentPost->name;
				} elseif(isset($currentPost->message)){
					$likeNote->message = $currentPost->message;
				}
				$likeNote->message = $like->name . " likes your post " . $likeNote->message;
				$postLikeMeasurementItem = new QMMeasurement($timestamp, 1, $likeNote);
				$this->getPostLikesVariable()->addToMeasurementQueue($postLikeMeasurementItem);
			}
		}
	}
	/**
	 * @param $currentPost
	 * @throws InvalidVariableValueAttributeException
	 */
	private function addPostCommentMeasurementItems($currentPost): void{
		if(isset($currentPost->comments)){
			foreach($currentPost->comments->data as $comment){
				$commentNote = new AdditionalMetaData($currentPost);
				$commentNote->populateFieldsByArrayOrObject($comment);
				if(isset($this->profileImages[$comment->from->name])){
					$commentNote->image = $this->profileImages[$comment->from->name];
				}
				$postCommentMeasurementItem = new QMMeasurement(strtotime($comment->created_time), 1, $commentNote);
				$this->getPostCommentsVariable()->addToMeasurementQueue($postCommentMeasurementItem);
			}
		}
	}
	/**
	 * @param $currentPost
	 * @param $timestamp
	 * @throws InvalidVariableValueAttributeException
	 */
	private function addPostMeasurementItem($currentPost, $timestamp): void{
		$postNote = new AdditionalMetaData($currentPost);
		if(isset($currentPost->permalink_url)){
			$postNote->url = $currentPost->permalink_url;
		} else{
			$this->logError("No permalink_url in " . json_encode($currentPost), $currentPost);
		}
		$postMeasurement = new QMMeasurement($timestamp, 1, $postNote);
		$this->getPostsVariable()->addToMeasurementQueue($postMeasurement);
	}
	/**
	 * @param array $variables
	 * @return bool
	 */
	public function haveEnoughTestMeasurements(array $variables): bool{
		if(!AppMode::isTestingOrStaging()){
			return false;
		}
		foreach($variables as $variable){
			if(!isset($this->qmUserVariables[$variable])){
				return false;
			}
		}
		return true;
	}
	public static function getFacebookUserIdFromUrl(string $url): ?string{
		if(strpos($url, 'app_scoped_user_id') !== false){
			return QMStr::between($url, 'app_scoped_user_id/', '/');
		}
		return null;
	}
	public static function getAvatarFromFacebookUrl(string $url): ?string{
		$facebookId = self::getFacebookUserIdFromUrl($url);
		if(!$facebookId){
			return null;
		}
		return 'https://graph.facebook.com/' . $facebookId . '/picture?type=square';
	}
	public function getUserProfilePageUrl(): ?string{
		return "https://www.facebook.com/app_scoped_user_id/" . $this->getConnectorUserId();
	}
	/**
	 * @return int|string
	 */
	public function getConnectorUserId(){
		$id = parent::getConnectorUserId();
		if(!$id){
			$this->getUserMetaValue('id');
		}
		return $id;
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationEndpoint(){
		return new Uri('https://www.facebook.com' . $this->getApiVersionString() . '/dialog/oauth');
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAccessTokenEndpoint(){
		return new Uri('https://graph.facebook.com' . $this->getApiVersionString() . '/oauth/access_token');
	}
	/**
	 * {@inheritdoc}
	 */
	protected function parseAccessTokenResponse($responseBody): StdOAuth2Token{
		$data = @json_decode($responseBody, true);
		// Facebook gives us a query string on old api (v2.0)
		if(!$data){
			parse_str($responseBody, $data);
		}
		if(!is_array($data)){
			throw new TokenResponseException('Unable to parse response.');
		} elseif(isset($data['error'])){
			throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
		}
		return $this->newStdOAuth2Token($data);
	}
	public function getDialogUri($dialogPath, array $parameters){
		if(!isset($parameters['redirect_uri'])){
			throw new LogicException("Redirect uri is mandatory for this request");
		}
		$parameters['app_id'] = $this->getCredentialsInterface()->getConsumerId();
		$baseUrl = self::WWW_URL . $this->getApiVersionString() . '/dialog/' . $dialogPath;
		$query = http_build_query($parameters);
		return new Uri($baseUrl . '?' . $query);
	}
	/**
	 * {@inheritdoc}
	 */
	protected function getApiVersionString(): string{
		return empty($this->apiVersion) ? '' : '/v' . $this->apiVersion;
	}
	/**
	 * {@inheritdoc}
	 */
	protected function getScopesDelimiter(): string{
		return ',';
	}
	/**
	 * Returns a class constant from ServiceInterface defining the authorization method used for the API
	 * Header is the sane default.
	 * @return int
	 */
	protected function getAuthorizationMethod(): int{
		return static::AUTHORIZATION_METHOD_HEADER_OAUTH;
	}
	public function urlUserDetails(): ?string{
		return 'https://graph.facebook.com/me?fields=id,name,email';
	}
}
