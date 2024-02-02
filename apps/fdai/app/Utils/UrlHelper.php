<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
use App\AppSettings\AppSettings;
use App\AppSettings\HostAppSettings;
use App\Charts\HighchartExport;
use App\Computers\ThisComputer;
use App\Exceptions\InvalidStringException;
use App\Exceptions\InvalidUrlException;
use App\Logging\QMLog;
use App\Models\OAAccessToken;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Repos\CCStudiesRepo;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\QMResponseBody;
use App\Slim\Model\User\QMUser;
use App\Slim\Model\WordPress\QMWordPressApi;
use App\Slim\QMSlim;
use App\Slim\View\Request\QMRequest;
use App\Types\BoolHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use GuzzleHttp\Psr7\Query;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Spatie\Url\Url;

/** Class UrlHelper
 * @package App\Slim\Model
 */
class UrlHelper extends Url {
	public const LARAVEL_API_PATHS = [
		'/api/v2',
		'/api/v6',
		'/api/v7',
	];
	public const CHROME_WEB_STORE_DEVELOPER_DASHBOARD = "https://chrome.google.com/webstore/developer/dashboard";
	const LOCAL_ORIGIN = ThisComputer::LOCAL_ORIGIN;
	const PATH_CLEANUP_SELECT = 'cleanup-select';
	const PATH_CLEANUP_UPDATE = 'cleanup-update';
	const DOCS_URL = "https://docs.quantimo.do";
	const STATIC_URL = "https://static.quantimo.do";
	const STUDIES_CROWDSOURCING_CURES = "https://" . self::STUDIES_CROWDSOURCING_CURES_HOST;
	const STUDIES_CROWDSOURCING_CURES_HOST = "studies." . self::CROWDSOURCING_CURES_HOSTNAME;
	const CROWDSOURCING_CURES_HOSTNAME = "crowdsourcingcures.org";
	const CROWDSOURCING_CURES_HOMEPAGE = "https://" . self::CROWDSOURCING_CURES_HOSTNAME;
	public const API_APEX_DOMAIN = '.' . self::QM_APEX_HOST;
	const IONIC_ORIGIN = IonicHelper::WEB_QUANTIMO_DO;
	const DEV_IONIC_ORIGIN = IonicHelper::IONIC_DEV_ORIGIN;
	const QM_APEX_HOST = "quantimo.do";
	const LOCAL_QM_HOST = Env::ENV_LOCAL . "." . self::QM_APEX_HOST;
	const STAGING_QM_HOST = Env::ENV_STAGING . "." . self::QM_APEX_HOST;
	const STAGING_ORIGIN = "https://" . self::STAGING_QM_HOST;
	const TESTING_QM_HOST = Env::ENV_TESTING . "." . self::QM_APEX_HOST;
	const APP_QM_HOST = "app." . self::QM_APEX_HOST;
	const API_DOCS_URL = 'https://curedao.org/api-docs';

	/**
	 * @param $value
	 * @param $type
	 * @throws InvalidUrlException
	 */
	public static function assertIsUrl($value, $type){
		self::validateUrl($value, $type);
	}
	/**
	 * Builds Url from route path
	 * @param string $baseUrl
	 * @param array $params
	 * @return string
	 */
	public static function buildApiUrl(string $baseUrl, array $params = []): string{
		if(!empty($params)){
			$baseUrl = HostAppSettings::instance()->additionalSettings->downloadLinks->webApp . $baseUrl . '?' .
			           http_build_query($params);
		}
		return $baseUrl;
	}
	/**
	 * @param string $name
	 * @param string $url
	 * @return string
	 */
	public static function getParam(string $name, string $url): ?string{
		$url = str_replace("#", "", $url);
		$parts = parse_url($url);
		if(isset($parts['query'])){
			parse_str($parts['query'], $query);
			if(isset($query[$name])){
				return $query[$name];
			}
			if(isset($query[QMStr::toCamelCase($name)])){
				return $query[QMStr::toCamelCase($name)];
			}
			if(isset($query[QMStr::convertStringFromCamelCaseToDashes($name)])){
				return $query[QMStr::convertStringFromCamelCaseToDashes($name)];
			}
		}
		return null;
	}
	/**
	 * @param string $url
	 * @return bool
	 */
	public static function isQM(string $url): bool{
		return str_contains($url, self::API_APEX_DOMAIN);
	}
	public static function isUrl(string $url): bool{
		return !self::urlInvalid($url);
	}
	/**
	 * @param string $url
	 * @param string $parameterName
	 * @param string $parameterValue
	 * @return string
	 */
	public static function addParam(string $url, string $parameterName, string $parameterValue): string{
		$base = QMStr::before("?", $url, $url);
		$queryArray = self::getParams($url);
		if($parameterValue === ""){
			le("No parameter value provided with url $url!");
		}
		$queryArray[$parameterName] = $parameterValue;
		$base .= "?" . http_build_query($queryArray);
		return $base;
	}
	/**
	 * @param string $url
	 * @return array
	 */
	public static function getParams(string $url): array{
		$urlWithoutHash = Str::replaceFirst('#', '', $url);
		$queryString = parse_url($urlWithoutHash, PHP_URL_QUERY);
		if(!$queryString){
			return [];
		}
		$queryString = str_replace("?", "", $queryString);
		parse_str($queryString, $queryArray);
		return $queryArray;
	}
	/**
	 * @param string $path
	 * @param array $params
	 * @return string
	 */
	public static function addPathParams(string $path, array $params): string{
		foreach($params as $placeholder => $value){
			$path = str_replace($placeholder, $value, $path);
		}
		return $path;
	}
	/**
	 * @param string $url
	 * @param array $params
	 * @return string
	 */
	public static function addParams(string $url, array $params = []): string{
		foreach($params as $key => $value){
			if(is_array($value) || is_object($value)){
				continue;
			}
			if(is_string($value) && strlen($value) > 2000){
				continue;
			}
			if($value === null){
				continue;
			}
			if($value === "null"){
				continue;
			}
			if($value === "undefined"){
				continue;
			}
			$url = self::addParam($url, $key, $value);
		}
		if(strlen($url) > 2083){
			QMLog::error("Too long for url: $url");
		}
		return $url;
	}
	public static function parseQuery(string $pathWithQuery): array{
		return Query::parse(QMStr::after("?", $pathWithQuery, $pathWithQuery));
	}
	/**
	 * @param string $url
	 * @param $names
	 * @return string
	 */
	public static function removeParams(string $url, $names = null): string{
		if(!$names){
			return QMStr::before("?", $url, $url);
		}
		if(!is_array($names)){
			$names = [$names];
		}
		$newUrl = $url;
		foreach($names as $name){
			$array = self::getParams($url);
			if(empty($array)){
				return $url;
			}
			$array = QMArr::unsetCamelOrSnakeCaseKey($array, $name);
			$urlWithoutQuery = QMStr::before("?", $newUrl);
			$newUrl = self::addParams($urlWithoutQuery, $array);
		}
		return $newUrl;
	}
	/**
	 * @param $subDomain
	 * @return bool
	 */
	public static function isQMAliasSubDomain($subDomain): bool{
		return in_array($subDomain, BaseClientIdProperty::QUANTIMODO_ALIAS_CLIENT_IDS, true);
	}
	/**
	 * @param string|null $clientId
	 * @return string
	 */
	public static function getBuilderUrl(string $clientId = null): string{
		$url = AppSettings::APP_BUILDER_URL;
		if($clientId){
			$url .= "/#/app/configuration/$clientId?clientId=$clientId";
		}
		return $url;
	}
	/**
	 * @param string $path
	 * @return string
	 */
	public static function getApiV3UrlForPath(string $path): string{
		return QMRequest::origin() . "/api/v3/" . $path;
	}
	/**
	 * @param string $path
	 * @param array $parameters
	 * @param string|null $host
	 * @return string
	 */
	public static function getApiUrlForPath(string $path, array $parameters = [], string $host = null): string{
		if(!$host){
			/** @noinspection CallableParameterUseCaseInTypeContextInspection */
			$host = QMRequest::origin();
		} else{
			$host = "https://" . $host;
		}
		$url = $host . "/api/";
		if(stripos($path, 'v1/') !== 0 && stripos($path, 'v2/') !== 0 && stripos($path, 'v4/') !== 0 &&
		   stripos($path, 'v5/') !== 0){
			$url .= 'v6/';
		}
		$url .= $path;
		if($parameters){
			$url = QMRequest::addProvidedAndRequestQueryParamsToUrl($url, $parameters);
		}
		try {
			QMStr::assertIsUrl($url, "some api endpoint url");
		} catch (InvalidStringException $e) {
			le($e);
		}
		return $url;
	}
	public static function origin(string $url = null): string{
		if(!$url){
			return Env::getAppUrl() ?? "https://app.quantimo.do"; // env isn't loaded yet
		}
		$result = parse_url($url);
		return $result['scheme'] . "://" . $result['host'];
	}
	/**
	 * @param string $url
	 * @param string|null $accessToken
	 * @return string
	 */
	public static function convertProductionToDevelopmentUrl(string $url, string $accessToken = null): string{
		$url = str_replace('app.', 'local.', $url);
		$url = str_replace('/www/', '/src/', $url);
		if($accessToken){
			$url .= "&access_token=$accessToken";
		}
		$url = self::toLocalUrl($url);
		return $url;
	}
	/**
	 * @param string $url
	 * @return string|null
	 */
	public static function getImageTypeFromUrl(string $url): ?string{
		if(stripos($url, ".png") !== false){
			return HighchartExport::PNG;
		}
		if(stripos($url, ".svg") !== false){
			return HighchartExport::SVG;
		}
		return null;
	}
	/**
	 * @param string $url
	 * @param int $userId
	 * @param string $clientId
	 * @return string
	 */
	public static function addAccessToken(string $url, int $userId, string $clientId): string{
		$u = QMUser::find($userId);
		$token = $u->getOrSetAccessTokenString($clientId);
		if(empty($token)){
			le("No token from $u");
		}
		return self::addParams($url, [BaseAccessTokenProperty::URL_PARAM_NAME => $token]);
	}
	public static function generateApiUrl(string $path = null, array $params = null): string{
		$url = QMRequest::origin();
		if($path){
			if(!str_starts_with($path, '/')){
				$path = '/' . $path;
			}
			$url = $url . $path;
		}
		if($params){
			$url = self::addParams($url, $params);
		}
		return $url;
	}
	public static function getDataLabUrl(string $path, array $params = []): string{
		// This is too much trouble to deal with HTML comparisons
		//        $url = Env::getEnv(Env::DB_URL);
		//        if(AppMode::isPHPUnitTest() && stripos($url, 'test') !== false){
		//            // This allows us to view datalab for slave servers
		//            $params[Env::DB_URL] = TestDB::getExternalDbUrl();
		//        }
		return self::addParams(QMRequest::origin() . '/datalab/' . $path, $params);
	}
	/**
	 * @param string $url
	 * @return string
	 * DB_URL is added by test but shouldn't be included in HTML
	 */
	public static function removeDBParamFromUrl(string $url): string{
		$url = UrlHelper::removeParams($url, Env::DB_PASSWORD);
		return $url;
	}
	public static function getCleanupSelectUrl(string $selectQuery, string $updateQuery, string $where,
	                                           string $message): string{
		$selectQuery = QMStr::trimWhitespaceAndLineBreaks($selectQuery . " " . $where);
		$updateQuery = QMStr::trimWhitespaceAndLineBreaks($updateQuery . " " . $where);
		$url = self::getDataLabUrl(self::PATH_CLEANUP_SELECT,
		                           ['select' => $selectQuery, 'update' => $updateQuery, 'message' => $message]);
		return $url;
	}
	public static function getCleanupUpdateUrl(string $selectQuery, string $updateQuery, string $message): string{
		$selectQuery = QMStr::trimWhitespaceAndLineBreaks($selectQuery);
		$updateQuery = QMStr::trimWhitespaceAndLineBreaks($updateQuery);
		return self::getDataLabUrl(self::PATH_CLEANUP_UPDATE,
		                           ['select' => $selectQuery, 'update' => $updateQuery, 'message' => $message]);
	}
	public static function getLocalUrl(string $path = null, array $params = []): string{
		if($path && str_starts_with($path, "http")){
			$path = self::getPathWithQuery($path);
		}
		$url =  self::LOCAL_ORIGIN;
		if(self::appUrlIsLocal()){
			$url = Env::getAppUrl();
		}
		if($path){
			$url .= "/$path";
			$url = str_replace(".do//", ".do/", $url);
		}
		return self::addParams($url, $params);
	}
	public static function toLocalUrl(string $url): string{
		$origin = self::getLocalUrl();
		return $origin . "/" . self::getPathWithQuery($url);
	}
	public static function getTestUrl(string $path = null, array $params = []): string{
		$url = "http://localhost$path";
		return self::addParams($url, $params);
	}
	public static function getWordpressPostUrl(string $slug): string{
		return QMWordPressApi::getSiteUrl() . '/' . $slug;
	}
	public static function redirect(string $url, int $status = QMResponseBody::CODE_TEMPORARY_REDIRECT): RedirectResponse {
		$url = self::addAccessTokenIfNecessary($url);
		if($app = QMSlim::getInstance()){
			$app->redirect($url, $status);
		}
		$session = session();
		QMLog::info("Redirecting to $url\n".QMLog::getBacktraceString());
		return redirect($url, $status);
	}
	public static function getCreateClassUrl(string $type, array $params = []): string{
		return self::getLocalUrl('admin/create/' . $type, $params);
	}
	public static function getCreateMigrationUrl(string $name = null): string{
		return self::getCreateClassUrl('migration', ['name' => $name]);
	}
	public static function getCreateExceptionUrl(string $name = null): string{
		return self::getCreateClassUrl('exception', ['name' => $name]);
	}
	public static function getCreateSolutionUrl(string $name = null): string{
		return self::getCreateClassUrl('solution', ['name' => $name]);
	}
	public static function getUrl(string $path, array $params = []): string{
		$path = str_replace('//', '/', '/' . $path);
		$url = QMRequest::origin() . $path;
		$url = self::addParams($url, $params);
		return $url;
	}
	/**
	 * @param string $url
	 * @param string $type
	 * @throws InvalidUrlException
	 */
	public static function validateUrl(string $url, string $type){
		QMStr::assertIsUrl($url, $type);
	}
	public static function stripProtocol(string $url): string{
		return QMStr::after("://", $url, $url);
	}
	public static function stripAPIKeysFromURL(string $url): string{
		$url = UrlHelper::removeParams($url, 'API_KEY');
		$url = UrlHelper::removeParams($url, 'token');
		$url = UrlHelper::removeParams($url, 'access_token');
		return $url;
	}
	public static function getHostName(string $url = null): string{
		if(!$url){
			$url = \App\Utils\Env::getAppUrl();
		}
		$host = QMStr::between($url, '://', '/');
		if(!$host){
			$host = QMStr::after('://', $url);
		}
		return $host;
	}
	public static function toPath(string $url): string{
		$path = QMStr::after("//", $url);
		$path = QMStr::after("/", $path);
		return $path;
	}
	public static function toQueryString(array $data): string{
		return http_build_query($data);
	}
	public static function stripQuery(string $url): string{
		return QMStr::before("?", $url, $url);
	}
	public static function getBetween(string $start, string $end): ?string{
		return QMStr::between(QMRequest::current(), $start, $end, null);
	}
	public static function canonicalizeUrls(string $str): string{
		return CCStudiesRepo::replaceUrls($str);
	}
	public static function getPathWithQuery(string $url): string{
		return self::toPath($url);
	}
	/**
	 * @param string $name
	 * @param string|null $url
	 * @return string
	 */
	public static function getQueryParam(string $name, string $url): ?string{
		$queryArray = self::getParams($url);
		return QMArr::getValueForSnakeOrCamelCaseKey($queryArray, $name);
	}

	public static function urlContains(string $needle): bool
	{
		$url = static::fullUrl();
		return strpos($url, $needle) !== false;

	}

	public static function fullUrl(): string
	{
		return request()->fullUrl();
	}

	/**
	 * @param string $url
	 * @return bool
	 */
	public static function urlInvalid(string $url): bool
	{
		$b = filter_var($url, FILTER_VALIDATE_URL) === false;
		return $b;
	}
	/**
	 * @return bool
	 */
	private static function appUrlIsLocal(): bool{
		$APP_URL = \App\Utils\Env::getAppUrl();
		$isLocal = str_contains($APP_URL, "local") || str_contains($APP_URL, ".test:");
		return $isLocal;
	}
	public static function getOrigin(): string{
		return QMRequest::origin();
	}
	public static function current(){
		return self::fullUrl();
	}

	public static function getAppUrl(){
		return Env::getAppUrl();
	}
	public static function stripDomainIfNecessary(string $uri){
		if(!str_contains($uri, "://")){
			return $uri;
		}
		$domain = Env::getAppUrl();
		$uri = str_replace($domain, "", $uri);
		return $uri;
	}
	public static function getBuildUrl(): ?string {
		return ThisComputer::getBuildConsoleUrl();
	}
	private static function hasAccessToken(string $url){
		$synonyms = BaseAccessTokenProperty::getSynonyms();
		foreach($synonyms as $synonym){
			if(str_contains($url, $synonym)){
				return true;
			}
		}
		return false;
	}
	/**
	 * @param string $url
	 * @return string
	 */
	private static function addAccessTokenIfNecessary(string $url): string{
		QMLog::info(__METHOD__);
		if(!UrlHelper::hasAccessToken($url)){
			$appUrl = Env::getAppUrl();
			if(!str_starts_with($url, $appUrl)){
				if(!QMRequest::isLoggingOut()){
					if($user = QMAuth::getUser()){
						$clientId = BaseClientIdProperty::fromRequestOrDefault();
						$url = self::addAccessToken($url, $user->getUserId(), $clientId);
					} else {
						QMLog::info(__METHOD__.": No user!");

					}
				} else {
					QMLog::info(__METHOD__.": !BoolHelper::isTruthy($logout)");
				}
			} else {
				QMLog::info(__METHOD__.": !str_starts_with($url, $appUrl)");
			}
		} else {
			QMLog::info(__METHOD__.": already have access token");
		}
		return $url;
	}
}
