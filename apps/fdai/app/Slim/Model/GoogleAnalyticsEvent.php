<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model;
use App\AppSettings\HostAppSettings;
use App\Exceptions\ExceptionHandler;
use App\Logging\QMLog;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Types\QMInteger;
use App\Types\QMStr;
use App\Utils\APIHelper;
use App\Utils\AppMode;
use App\Utils\EnvOverride;
use App\Utils\IPHelper;
use App\Utils\UserAgent;
use Exception;
use KeenIO\Client\KeenIOClient;
use TheIconic\Tracking\GoogleAnalytics\Analytics;
/** Google Analytics Event Tracking
 * This library provides a method to track a server side event to
 * Google Analytics
 * @author Craig Davis <craig@there4development.com>
 * @created 07/15/2010
 */
class GoogleAnalyticsEvent {
	public const KEEN_ENABLED = false;
	/**
	 * @var string Google Analytics code
	 */
	private $_code;
	/**
	 * @var string Domain name we are requesting from
	 */
	private $_domain;
	/**
	 * @var string User Agent string for this request from CURL
	 */
	private $_useragent = 'PHPAnalyticsAgent/0.1 (https://there4development.com/)';
	/**
	 * @var string cookie name
	 */
	private $_cookie = "phpanalytics";
	/**
	 * @var bool verbose output
	 */
	private $_verbose = false;
	/**
	 * Setup Analytics
	 * @param string $code Google Analytics key (default: const GOOG_UA)
	 * @param string $domain HTTP_HOST (default: $_SERVER['HTTP_HOST'])
	 */
	public function __construct($code = '', $domain = ''){
		$this->_code = !empty($code) ? $code : \App\Utils\Env::get('ANALYTICS_TRACKING_ID');
		$this->_domain = \App\Utils\Env::getAppUrl();
		if(!empty($domain)){
			$this->_domain = $domain;
		} elseif(array_key_exists('HTTP_HOST', $_SERVER)){
			$this->_domain = $_SERVER['HTTP_HOST'];
		}
	}
	/**
	 * @param string $eventName
	 * @param [] $event
	 * @internal param $keenMessage
	 */
	public static function sendKeenEvent($eventName, $event){
		if(!self::KEEN_ENABLED){
			QMLog::info("Keen not installed!");
			return;
		}
		if(!\App\Utils\Env::get('KEEN_PROJECT_ID')){
			return;
		}
		$projectId = \App\Utils\Env::get('KEEN_PROJECT_ID') ?: '58950ef88db53dfda8a8664d';
		$writeKey = \App\Utils\Env::get('KEEN_WRITE_KEY') ?: 'CD899F5BE67C2F3238C5579F9B1353BD2EF37260EBA91469E870773B8566528E';
		$readKey = \App\Utils\Env::get('KEEN_READ_KEY') ?: 'CD899F5BE67C2F3238C5579F9B1353BD2EF37260EBA91469E870773B8566528E';
		$keenIOClient = KeenIOClient::factory([
			'projectId' => $projectId,
			'writeKey' => $writeKey,
			'readKey' => $readKey,
		]);
		try {
			$keenIOClient->addEvent($eventName, $event);
		} catch (Exception $e) {
			QMLog::error("Keen Error: " . $e->getMessage());
		}
	}
	/**
	 * @param null $customMetric
	 * @throws Exception
	 */
	public static function logApiRequestToGoogleAnalytics($customMetric = null){
		QMLog::debug("Need to update php-ga-measurement-protocol lib to log API Request to Google Analytics ");
		return;
		$trackAsPageView = false;
		if(AppMode::isTestingOrStaging() || EnvOverride::isLocal()){
			return;
		}  // Analytics seems to break PHPUnit - We should use analytics on staging to make sure it doesn't break anything on production
		$queryString = ($_SERVER['QUERY_STRING'] ?? 'UNKNOWN_QUERY_STRING');
		$dataSource = ($_REQUEST['appName'] ?? BaseClientIdProperty::fromRequest(false));
		$appVersion = ($_REQUEST['appVersion'] ?? 'UNKNOWN_APP_VERSION');
		// Instantiate the Analytics object
		// optionally pass TRUE in the constructor if you want to connect using HTTPS
		$analytics = new Analytics(true);
		$eventCategory = 'API Request';
		$eventAction =
			QMRequest::getRequestPathWithoutQuery() ? QMRequest::getRequestPathWithoutQuery() : "UnknownAction";
		if(APIHelper::getRequestMethod()){
			$eventAction = APIHelper::getRequestMethod() . ' ' . QMRequest::getRequestPathWithoutQuery();
		}
		if(QMRequest::getQueryParam('variable')){
			$queryString = QMRequest::getQueryParam('variable');
		}
		if(QMRequest::getQueryParam('variableName')){
			$queryString = QMRequest::getQueryParam('variableName');
		}
		$eventLabel =
			"User: " . QMUser::getLoggedInUserId() . ", Client ID: " . BaseClientIdProperty::fromRequest(false) .
			", Query: $queryString";
		$eventValue = (int)(APIHelper::getRequestDurationInSeconds() * 1000);
		// Build the GA hit using the Analytics class methods they should autocomplete if you use a PHP IDE
		$analytics->setProtocolVersion('1')
			->setTrackingId(HostAppSettings::instance()->additionalSettings->googleAnalyticsTrackingIds->backEndAPI)
			->setClientId(QMUser::getGoogleAnalyticsClientId(QMUser::getLoggedInUserId()))
            ->setHitType('event')->setEventCategory($eventCategory)
			->setEventAction($eventAction)->setEventLabel($eventLabel)->setEventValue($eventValue)
			->setCustomDimension(QMUser::getPaid(), 1)->setCustomDimension(QMUser::getLoggedIn(), 2);
        if($ip = IPHelper::getClientIp()){
            $analytics->setIpOverride($ip);
        }
		if(QMRequest::getReferrer()){
			$analytics->setDocumentReferrer(QMRequest::getReferrer());
		}
		if(UserAgent::getUserAgent()){
			$analytics->setUserAgentOverride(UserAgent::getUserAgent());
		}
		$analytics->setDocumentHostName(QMRequest::host());
		if($trackAsPageView){
			$analytics->setDocumentTitle(APIHelper::getRequestMethod() . ' ' . QMRequest::requestUri());
			$analytics->setDocumentLocationUrl(QMRequest::current());
			if($path = QMRequest::requestUri()){
				$analytics->setDocumentPath($path);
			}
		}
		if($customMetric){
			$analytics->setCustomMetric($customMetric['value'], $customMetric['index']);
		}
		if(isset($dataSource)){
			$analytics->setDataSource($dataSource);
			$analytics->setApplicationName($dataSource);
		}
		if($appVersion){
			$analytics->setApplicationVersion($appVersion);
		}
		if(BaseClientIdProperty::fromRequest(false)){
			$analytics->setApplicationId(BaseClientIdProperty::fromRequest(false));
		}
		if(QMUser::getLoggedInUserId()){
			$analytics->setUserId(QMUser::getLoggedInUserId());
		}
		$debugMode = false;
		if($debugMode){
			// Make sure AsyncRequest is set to false (it defaults to false)
			$eventLogger = new GoogleAnalyticsEvent();
			$eventLogger->trackEvent($eventCategory, $eventAction, $eventLabel, $eventValue,
				QMUser::getLoggedInUserId());
			$response = $analytics->sendEvent();
		} else{
			// When you finish building the payload send a hit (such as an page view or event)
			try {
				$analytics->setAsyncRequest(true)->sendEvent();
			} catch (Exception $e) {
				QMLog::error('Could not log to Google Analytics', ['exception' => $e]);
			}
		}
	}
	/**
	 * Track an event in Google Analytics
	 *  http://code.google.com/apis/analytics/docs/tracking/eventTrackerGuide.html
	 * @param string $eventCategory the name you supply for the group of objects you want to track. Typically the
	 *     object that was interacted with (e.g. 'Video')
	 * @param string $eventAction A string that is uniquely paired with each category, and commonly used to define the
	 *     type of user interaction for the web object. The type of interaction (e.g. 'play')
	 * @param string $eventLabel An optional string to provide additional dimensions to the event data.  Useful for
	 *                      categorizing events (e.g. 'Fall Campaign' or user id)
	 * @param int|null $eventValue An integer that you can use to provide numerical data about the user event.
	 * @param int|string|null $userId
	 * @param string|null $clientId
	 * @return bool success
	 * @throws Exception
	 */
	public function trackEvent(string $eventCategory, string $eventAction, string $eventLabel, int $eventValue,
		 $userId, string $clientId = null): bool{
		QMStr::validateMaxLength($eventCategory, 149, 'Event category');
		QMStr::validateMaxLength($eventAction, 500, 'Event action');
		QMStr::validateMaxLength($eventLabel, 500, 'Event label');
		QMInteger::validateMin($eventValue, 0, 'Event value');
		$ANALYTICS_TRACKING_ID = \App\Utils\Env::get('ANALYTICS_TRACKING_ID');
		if(!$ANALYTICS_TRACKING_ID){
			QMLog::info("No \App\Utils\Env::get('ANALYTICS_TRACKING_ID') for " . __METHOD__);
			return false;
		}
		$url =
			"https://www.google-analytics.com/collect?v=1" . "&t=event" . "&tid=" . $ANALYTICS_TRACKING_ID . "&uid=" .
			$userId . "&ev=" . $eventValue .
			//"&aid=" . rawurlencode($clientId  . '.quantimo.do') . // Don't add this (or any unnecessary parameters) because it prevents event registration!
			"&el=" . rawurlencode($eventLabel . " Client ID:" . $clientId) . "&ec=" . rawurlencode($eventCategory) .
			"&ea=" . rawurlencode($eventAction);
		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => $this->_useragent,
			CURLOPT_VERBOSE => $this->_verbose,
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_COOKIEFILE => $this->_cookie,
		]);
		$output = curl_exec($ch);
		curl_close($ch);
		$is_gif = ('GIF89a' == substr($output, 0, 6));
		QMLog::debug("$url RESULT => $output");
		return $is_gif;
	}
	/**
	 * Track an event in Google Analytics
	 *  http://code.google.com/apis/analytics/docs/tracking/eventTrackerGuide.html
	 * @param string $eventCategory the name you supply for the group of objects you want to track. Typically the
	 *     object that was interacted with (e.g. 'Video')
	 * @param string $eventAction A string that is uniquely paired with each category, and commonly used to define the
	 *     type of user interaction for the web object. The type of interaction (e.g. 'play')
	 * @param int $eventValue An integer that you can use to provide numerical data about the user event.
	 * @param null $userIdOrIP
	 * @param string|null $clientId
	 * @param string|null $eventLabel An optional string to provide additional dimensions to the event data.  Useful for
	 *                      categorizing events (e.g. 'Fall Campaign' or user id)
	 * @return bool success
	 */
	public static function logEventToGoogleAnalytics(string $eventCategory, string $eventAction, int $eventValue = 1,
		$userIdOrIP = null, string $clientId = null, string $eventLabel = null): bool{
		if(!$clientId){
			$clientId = gethostname();
		}
		if(!$eventLabel){
			$eventLabel = $clientId;
		}
		if($userIdOrIP){
			$eventLabel = "userIdOrIP: " . $userIdOrIP . " " . $eventLabel;
		}
		if(AppMode::isTestingOrStaging()){
			QMLog::debug('Could not log to Google Analytics because tracking id is not set!');
			return false;
		}
		if(AppMode::isApiRequest()){  // Wait until after API response
			register_shutdown_function(function() use (
				$eventCategory, $eventAction, $eventLabel, $eventValue, $userIdOrIP, $clientId
			){
				GoogleAnalyticsEvent::tryToTrack($eventCategory, $eventAction, $eventLabel, $eventValue, $userIdOrIP,
					$clientId);
			});
		} else{
			self::tryToTrack($eventCategory, $eventAction, $eventLabel, $eventValue, $userIdOrIP, $clientId);
		}
		return true;
	}
	/**
	 * @param $eventCategory
	 * @param $eventAction
	 * @param $eventLabel
	 * @param $eventValue
	 * @param $userId
	 * @param $clientId
	 */
	private static function tryToTrack($eventCategory, $eventAction, $eventLabel, $eventValue, $userId, $clientId){
		try {
			$eventLogger = new GoogleAnalyticsEvent();
			$eventLogger->trackEvent($eventCategory, $eventAction, $eventLabel, $eventValue, $userId, $clientId);
		} catch (Exception $e) {
			ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
		}
	}
	/**
	 * Get the current Url
	 * @return string current url
	 */
	public function getCurrentUrl(): string{
		$url = isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http';
		$url .= '://' . $_SERVER['SERVER_NAME'];
		$url .= in_array($_SERVER['SERVER_PORT'], [
			'80',
			'443',
		], true) ? '' : ':' . $_SERVER['SERVER_PORT'];
		$url .= $_SERVER['REQUEST_URI'];
		return $url;
	}
}
/* End of file AnalyticsEvent.php */
