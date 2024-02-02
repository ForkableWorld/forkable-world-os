<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\View\Request;
use App\Computers\ThisComputer;
use App\Exceptions\UnauthorizedException;
use App\Files\FileHelper;
use App\Http\Urls\IntendedUrl;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Models\User;
use App\Models\UserVariable;
use App\Parameters\StateParameter;
use App\Properties\Base\BaseEffectVariableIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Slim\Model\User\QMUser;
use App\Slim\QMSlim;
use App\Storage\DB\QMDB;
use App\Storage\DB\QMQB;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Storage\QueryBuilderHelper;
use App\Types\BoolHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\InternalImageUrls;
use App\Utils\APIHelper;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\IPHelper;
use App\Utils\Subdomain;
use App\Utils\UrlHelper;
use App\Variables\QMUserVariable;
use App\Variables\QMVariableCategory;
use Dialogflow\WebhookClient;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
class QMRequest extends \Illuminate\Http\Request {
	public const METHOD_DELETE = 'DELETE';
	public const METHOD_GET = 'GET';
	public const METHOD_POST = 'POST';
	public const METHOD_PUT = 'PUT';
	public const GENERATE_TEST = 'GENERATE_TEST';
	public const PARAM_ACCESS_TOKEN = 'access_token';
	public const PARAM_ANALYSIS = 'analysis';
	public const PARAM_ANALYZABLE = 'analyzable';
	public const PARAM_ANALYZE = 'analyze';
	public const PARAM_AUTOCOMPLETE = 'autocomplete';
	public const PARAM_CREATE_TEST = 'createTest';
	public const PARAM_DEBUG = 'debug';
	public const PARAM_DELETED = 'deleted';
	public const PARAM_DESCRIPTION = 'description';
	public const PARAM_ERRORED = 'errored';
	public const PARAM_GENERATE_PHPUNIT = 'generate_phpunit';
	public const PARAM_IMAGE = 'image';
	public const PARAM_INCLUDE_CHARTS = 'includeCharts';
	public const PARAM_INCLUDE_PUBLIC = 'includePublic';
	public const PARAM_INCLUDE_TAGS = 'includeTags';
	public const PARAM_LIMIT = 'limit';
	public const PARAM_OFFSET = 'offset';
	public const PARAM_PROFILE = 'profile';
	public const PARAM_REFRESH = 'refresh';
	public const PARAM_SORT = 'sort';
	public const PARAM_TABLE = 'table';
	public const PARAM_TIME_LIMIT = 'time_limit';
	public const PARAM_TITLE = 'title';
	public const PARAM_TRASH = 'trash';
	public const PARAM_TRASHED = 'trashed';
	public const PARAM_UPDATE = 'update';
	public const PARAM_VIEW = 'view';
	const PARAM_LOGOUT = 'logout';
	private static $webHookClient;
	public $limit;
	public $offset;
	protected $models;
	protected $qb;
	protected $user;
	/**
	 * @param array $query
	 * @param array $request
	 * @param array $attributes
	 * @param array $cookies
	 * @param array $files
	 * @param array $server
	 * @param null $content
	 */
	public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [],
	                            array $files = [], array $server = [], $content = null){
		parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
		Memory::set(Memory::QM_REQUEST, $this);
	}
	/**
	 * @param string $url
	 * @param array $additionalParams
	 * @return string
	 */
	public static function addProvidedAndRequestQueryParamsToUrl(string $url, array $additionalParams = []): string{
		foreach($additionalParams as $key => $value){
			if(is_array($value) || is_object($value)){
				continue;
			}
			if(is_string($value) && strlen($value) > 2000){
				continue;
			}
			if($value === null){
				continue;
			}
			if(QMStr::isNullString($value)){
				continue;
			}
			if($value === "undefined"){
				continue;
			}
			$url = QMRequest::addQueryParamAndRequestParamsToUrl($url, $key, $value);
		}
		return $url;
	}
	/**
	 * @param string $url
	 * @param string $parameterName
	 * @param string|int $parameterValue
	 * @return string
	 */
	public static function addQueryParamAndRequestParamsToUrl(string $url, string $parameterName,
	                                                                 $parameterValue): string{
		if($parameterValue === null){ // Sometimes offset is 0
			QMLog::error("Url parameter value $parameterName is null!");
		}
		$base = QMStr::before("?", $url, $url);
		$queryArray = self::getQueryParamsFromStringAndRequest($url);
		$queryArray[$parameterName] = $parameterValue;
		$base .= "?".http_build_query($queryArray);
		return $base;
	}
	/**
	 * @param string|null $url
	 * @return array
	 */
	public static function getQueryParamsFromStringAndRequest(string $url): array{
		$queryArray = UrlHelper::getParams($url);
		if(isset($_GET)){
			$queryArray = array_merge($queryArray, $_GET);
		}
		return $queryArray;
	}
	public static function endsWith(string $needle): bool{
		return QMStr::endsWith($needle, QMRequest::withoutQuery());
	}
	public static function withoutQuery(): string{
		return QMStr::before("?", self::current(), self::current());
	}
	/**
	 * @param array|null $params
	 * @return string
	 */
	public static function current(array $params = null): string{
		if(isset($_SERVER['REQUEST_URI'])){
			$url = Env::getAppUrl() .$_SERVER["REQUEST_URI"];
		} else {
			$url = url();
			$url = $url->full();
		}
		if($params){
			$url = UrlHelper::addParams($url, $params);
		}
		return $url;
	}
	public static function getAppHostUrl(string $path, array $params): string{
		return qm_url($path, $params);
	}
	/**
	 * @return string
	 */
	public static function urlWithoutProtocol(): ?string{
		if(!QMRequest::host()){
			return null;
		}
		return QMRequest::host().qm_request()->getPathInfo();
	}
	/**
	 * @return mixed
	 */
	public static function host(){
		if(isset($_SERVER['HTTP_HOST'])){
			return $_SERVER['HTTP_HOST'];
		}
		// return str_replace("https://", "", UrlHelper::origin());
		$h = Request::getHost();
		if($h){
			return $h;
		}
		return str_replace("https://", "", Env::getAppUrl());
	}
	/**
	 * @return object
	 */
	public static function bodyAsObj(): ?object{
		$body = self::body();
		if(!$body){
			return null;
		}
		return json_decode(json_encode($body));
	}
	/**
	 * @return bool|resource|array
	 */
	public static function body(): ?array{
        if(!empty($_POST)){
	        $keys = array_keys($_POST);
			if(count($keys) === 1){
				$firstKey = $keys[0];
				if(QMStr::isJson($firstKey)){
					return $_POST =  json_decode($firstKey, true);
				}
	        }
			return $_POST;
		}
		$body = Memory::get(__FUNCTION__);
		if($body !== null){
			return $body;
		}
		if(!AppMode::isApiRequest()){
			return null;
		}
		if(self::isGetRequest()){
			return null;
		}
		if($i = QMSlim::getInstance()){
			if($body = $i->request()->getBody()){
				if($body === "null"){
					return null;
				}
				$body = QMStr::jsonDecodeIfNecessary($body, true);
				$body = QMArr::toArray($body);
				Memory::set(__FUNCTION__, $body);
				return $body;
			}
		}
		$body =
			\Illuminate\Support\Facades\Request::all(); // Sometimes this is called from index.php before Laravel bootstrapped
		return Memory::set(__FUNCTION__, $body);
	}
	private static function isGetRequest(): bool{
		$type = $_SERVER['REQUEST_METHOD'] ?? null;
		return $type === static::METHOD_GET;
	}
	/**
	 * @return string
	 */
	public static function getReferrerHost(): ?string{
		$referrer = self::getReferrer();
		if(!$referrer){
			return null;
		}
		$parsed = parse_url($referrer);
		return $parsed['host'];
	}
	/**
	 * @return string
	 */
	public static function getReferrer(): ?string{
		if(isset($_SERVER['HTTP_REFERER'])){
			return $_SERVER['HTTP_REFERER'];
		}
		return null;
	}
	/**
	 * @return string
	 */
	public static function getRequestPathWithoutQuery(): string{
		return request()->getPathInfo();
	}
	public static function getTestVersionOfCurrentUrl(): string{
		$current = self::current();
		return Subdomain::replaceSubdomain(Env::ENV_TESTING, $current);
	}
	/**
	 * @param string $name
	 * @return string
	 */
	public static function getParamFromCurrentOrIntendedUrl(string $name): ?string{
		if($value = QMRequest::getQueryParam($name)){
			return $value;
		}
		return IntendedUrl::getParam($name);
	}
	/**
	 * @param string|array $names
	 * @return string
	 */
	public static function getParamFromReferrer($names): ?string{
		$query = self::getReferrerParams();
		if(!$query){
			return null;
		}
		if(!is_array($names)){
			$names = [$names];
		}
		foreach($names as $name){
			$result = QMArr::getValueForSnakeOrCamelCaseKey($query, $name);
			if($result !== null){
				return $result;
			}
		}
		return null;
	}
	/**
	 * @return array
	 */
	public static function getReferrerParams(): array{
		if(isset($_SERVER['HTTP_REFERER'])){
			$referrer = $_SERVER['HTTP_REFERER'];
			$parts = parse_url($referrer);
			if(isset($parts['query'])){
				parse_str($parts['query'], $query);
				return $query;
			}
		}
		return [];
	}
	public static function hasReferrerRelationIdFilter(): bool{
		$params = QMRequest::getReferrerParams();
		foreach($params as $name => $value){
			if(QMStr::endsWith("_id", $name) && $value){
				return true;
			}
		}
		return false;
	}
	public static function isTesting(): bool{
		return Subdomain::is('testing');
	}
	/**
	 * @return bool
	 */
	public static function onLaravelAPIPath(): bool{
		$mem = Memory::get(__FUNCTION__);
		if($mem !== null){
			return $mem;
		}
		foreach(UrlHelper::LARAVEL_API_PATHS as $path){
			if(QMRequest::urlContains($path)){
				return Memory::set(__FUNCTION__, true);
			}
		}
		return Memory::set(__FUNCTION__, false);
	}
	/**
	 * @param string $string
	 * @param bool $caseInsensitive
	 * @return bool
	 */
	public static function urlContains(string $string, bool $caseInsensitive = false): bool{
		if(!AppMode::isApiRequest()){
			return false;
		}
		$result = Memory::get($string, __FUNCTION__);
		if($result !== null){
			return $result;
		}
		$url = self::current();
		if($caseInsensitive){
			$result = stripos($url, $string) !== false;
		} else{
			$result = str_contains($url, $string);
		}
		return Memory::set($string, $result, __FUNCTION__);
	}
	/**
	 * @param $needle
	 * @return bool
	 */
	public static function pathContains($needle): bool{
		return strpos(QMRequest::requestUri(), $needle) !== false;
	}
	/**
	 * @return string
	 */
	public static function requestUri(): ?string{
		if(isset($_SERVER['REQUEST_URI'])){
			return $_SERVER['REQUEST_URI'];
		}
		return null;
	}
	/**
	 * @param string|array $nameOrArray
	 * @return string
	 */
	public static function getHeader($nameOrArray): ?string{
		if(!is_array($nameOrArray)){
			$nameOrArray = [$nameOrArray];
		}
		foreach($nameOrArray as $name){
			$snake = QMStr::snakize($name);
			$key = "HTTP_".strtoupper($snake);
			if(isset($_SERVER[$key])){
				return $_SERVER[$key];
			}
			$key = "HTTP_X_".strtoupper($snake);
			if(isset($_SERVER[$key])){
				return $_SERVER[$key];
			}
			$camel = QMStr::camelize($name);
			$key = "HTTP_".strtoupper($camel);
			if(isset($_SERVER[$key])){
				return $_SERVER[$key];
			}
			$key = "HTTP_X_".strtoupper($camel);
			if(isset($_SERVER[$key])){
				return $_SERVER[$key];
			}
		}
		return null;
	}
	/**
	 * @return WebhookClient
	 */
	public static function getWebHookClient(): WebhookClient{
		return self::$webHookClient ?: self::setWebHookClient();
	}
	/**
	 * @return WebhookClient
	 */
	public static function setWebHookClient(): WebhookClient{
		return self::$webHookClient = WebhookClient::fromData(self::body());
	}
	public static function unsetWebHookClient(){
		self::$webHookClient = null;
	}
	/**
	 * @return string
	 */
	public static function getRequestPathAsFileName(): string{
		return qm_request()->getMethod().str_replace('/', '_', qm_request()->getRequestUri());
	}
	/**
	 * @return string
	 */
	public function getMethod(): string{
		if(isset($_SERVER['REQUEST_METHOD'])){
			return $_SERVER['REQUEST_METHOD'];
		}
		return parent::getMethod();
	}
	public function query($key = null, $default = null){
		$q = parent::query($key, $default);
		if($q !== null && $q !== []){
			return $q;
		}
		if(!empty($_GET)){
			$this->query = new ParameterBag($_GET);
		}
		return parent::query($key, $default);
	}
	/**
	 * @return string
	 */
	public function getRequestUri(): ?string{
		return $_SERVER['REQUEST_URI'] ?? parent::getRequestUri();
	}
	/**
	 * @return int
	 */
	public static function getDuration(): int{
		return APIHelper::getRequestDurationInSeconds();
	}
	/**
	 * @param $nameOrArray
	 * @param bool $throwException
	 * @return int
	 */
	public static function getParamInt($nameOrArray, bool $throwException = false){
		$value = self::getParam($nameOrArray, null, $throwException);
		if($value){
			return (int)$value;
		}
		return $value;
	}
	/**
	 * @param string|array $nameOrArray
	 * @param null $default
	 * @param bool $throwException
	 * @return mixed|null|string
	 */
	public static function getParam($nameOrArray, $default = null, bool $throwException = false){
		if(is_array($nameOrArray)){
			$value = self::getParamByNames($nameOrArray, $throwException);
			if($value === null){
				return $default;
			}
			return $value;
		}
		$mem = Memory::getRequestParam($nameOrArray);
		if($mem !== Memory::UNSET){
			return $mem;
		} // Use Globals so they're easily reset in tests
		if(!AppMode::isApiRequest()){
			return $default;
		}
		$input = self::getInput();
		$value = null;
		if($input){
			$value = QMArr::pluckValue($input, $nameOrArray, null);
		}
		if($value === null){
			$value = StateParameter::getValueFromStateParam($nameOrArray);
		}
		if($value === null){
			$value = self::fromInput($nameOrArray);
		}
		if($value === null){
			if($intended = IntendedUrl::fromQuery()){
				$value = UrlHelper::getParam($nameOrArray, $intended);
			}
		}
		// Don't always check referrer because sometimes it sends the wrong referrer header.  Do it specifically where needed
		//if($value === null){$value = self::getUrlParameterFromReferrer($nameOrArray);}
		if($value === null){
			$value = self::getHeader($nameOrArray);
		}
		if($value === null && $throwException){
			throw new BadRequestHttpException("Please provide $nameOrArray with request");
		}
		if($default !== null && $value === null){
			return $default;
		}
		if($value === "false"){
			$value = false;
		}
		if($value === "true"){
			$value = true;
		}
		return Memory::setRequestParam($nameOrArray, $value);
	}
	/**
	 * @param array $possibleNames
	 * @param bool $throwException
	 * @return array|mixed|null|string
	 */
	private static function getParamByNames(array $possibleNames, bool $throwException = false){
		foreach($possibleNames as $name){
			$value = self::getParam($name, null, false);
			if($value !== null){
				return $value;
			}
		}
		if($throwException){
			throw new BadRequestHttpException("Please provide one of the following parameters: ".
			                                  implode(" ", $possibleNames));
		}
		return null;
	}
	/**
	 * @param string $name
	 * @return array|mixed|null|string
	 */
	public static function fromInput(string $name){
		if(Memory::getRequestParam($name) !== Memory::UNSET){
			return Memory::getRequestParam($name);
		}
		$value = self::getBodyParam($name);
		if($value === null){
			$value = self::getQueryParam($name);
		}
		Memory::setRequestParam($name, $value);
		return $value;
	}
	/**
	 * @param string $name
	 * @return null|string
	 */
	public static function getBodyParam(string $name): ?string{
		if(self::isGetRequest()){
			return null;
		}
		$body = self::body();
		if(isset($body[$name])){
			return $body[$name];
		}
		$dashes = QMStr::convertStringFromCamelCaseToDashes($name);
		if(isset($body[$dashes])){
			return $body[$dashes];
		}
		$camel = QMStr::toCamelCase($name);
		if(isset($body[$camel])){
			return $body[$camel];
		}
		return QMArr::getValue($body, $name);
	}
	/**
	 * @param string $name
	 * @param null $default
	 * @return mixed
	 */
	public static function getQueryParam(string $name, $default = null){
		if(Memory::getUrlParam($name) !== Memory::UNSET){
			return Memory::getUrlParam($name);
		}
		if(!AppMode::isApiRequest()){
			Memory::setUrlParam($name, $default);
			return $default;
		}
		$value = $default;
        if(empty($_GET)){
            $r = \request();
            $input = $r->input();
            $query = $r->query();
            $all = $r->query->all();
            $_GET = \request()->query->all();
        }
		if(isset($_GET[$name])){
			$value = $_GET[$name];
		} elseif(isset($_GET[$camel = QMStr::toCamelCase($name)])){
			$value = $_GET[$camel];
		} elseif(isset($_GET[$dash = QMStr::convertStringFromCamelCaseToDashes($name)])){
			$value = $_GET[$dash];
		}
		if(isset($value) && !str_starts_with($value, "http")){
			$value = urldecode($value);
		}
		Memory::setUrlParam($name, $value);
		return $value;
	}
	/**
	 * @param string|array $parameterNameOrArray
	 * @param array $possibleValues
	 * @return bool
	 */
	public static function parameterValueInArray($parameterNameOrArray, array $possibleValues): bool{
		$value = self::getParam($parameterNameOrArray);
		if(!$value){
			return false;
		}
		return QMArr::inArrayCaseInsensitive($value, $possibleValues);
	}
	/**
	 * @return string
	 */
	public static function getPlatform(): ?string{
		$p = self::getParam('platform');
		if($p){
			$p = strtolower($p);
		}
		return $p;
	}
	/**
	 * @return string
	 */
	public static function getFramework(){
		return self::getParam('framework');
	}
	/**
	 * @return bool
	 */
	public static function isPost(): bool{
		return qm_request()->getMethod() === self::METHOD_POST;
	}
	/**
	 * @return QMVariableCategory
	 */
	public static function getQMVariableCategory(): ?QMVariableCategory{
		$nameOrId = self::getParam([
			                           'variableCategoryName',
			                           'variableCategoryId',
			                           'category',
			                           'categoryName',
		                           ]);
		if($nameOrId){
			if(strtolower($nameOrId) === "anything"){
				return null;
			}
			return QMVariableCategory::find($nameOrId);
		}
		return null;
	}
	/**
	 * @return string
	 */
	public static function getSearchPhrase(): ?string{
		$search = self::getParam([
			                         'searchPhrase',
			                         'search',
			                         'q',
			                         'query',
		                         ]);
		return $search;
	}
	/**
	 * Get Headers
	 * This method returns a key-value array of headers sent in the HTTP request, or
	 * the value of a hash key if requested; if the array key does not exist, NULL is returned.
	 * @param string|null $key
	 * @param mixed $default The default value returned if the requested header is not available
	 * @return mixed
	 */
	public static function headers(string $key = null, $default = null){
		if(!AppMode::isApiRequest()){
			return $default;
		}
		return Request::header($key, $default);
	}
	/**
	 * @param int $seconds
	 */
	public static function setMaximumApiRequestTimeLimit(int $seconds){
		QMLog::info("Setting maximum API request time limit to $seconds seconds");
		Memory::set(Memory::API_REQUEST_TIME_LIMIT, $seconds);
		ThisComputer::setMaximumPhpExecutionTimeLimit($seconds);
	}
	/**
	 * @return QMUserVariable|null
	 * @throws UnauthorizedException
	 */
	public static function getEffectUserVariable(): ?QMUserVariable{
		$nameOrId = BaseEffectVariableIdProperty::nameOrIdFromRequest();
		if(!$nameOrId){
			return null;
		}
		$uv = UserVariable::findByVariableIdOrName($nameOrId, QMAuth::id());
		if(!$uv){
			return null;
		}
		return $uv->getDBModel();
	}
	/**
	 * @return bool
	 */
	public static function recalculateRefreshOrAnalyze(): bool{
		$value = self::getParam([
			                        'recalculate',
			                        'refresh',
			                        '_',
			                        'analyze',
		                        ]);
		return self::convertToBool($value);
	}
	/**
	 * @param $param
	 * @return bool
	 */
	private static function convertToBool($param): bool{
		if($param === false){
			return false;
		}
		if($param === "0"){
			return false;
		}
		if($param === "false"){
			return false;
		}
		return !empty($param);
	}
	/**
	 * @return array
	 */
	public static function getQuery(): array{
		return qm_request()->query();
	}
	/**
	 * @return int
	 */
	public static function getApiRequestTimeLimit(): int{
		$seconds = self::getParam(self::PARAM_TIME_LIMIT);
		if(!$seconds){
			$seconds = Memory::get(Memory::API_REQUEST_TIME_LIMIT, Memory::MISCELLANEOUS);
		}
		if(!$seconds){
			$seconds = 30;
		}
		return $seconds;
	}
	public static function getRequestTime(): int{
		return APIHelper::getRequestTime();
	}
	public static function redirect(string $url){
		if(headers_sent()){
			die("Redirect failed. Please click on this link: <a href=\"$url\">$url</a>");
		} else{
			return UrlHelper::redirect($url);
		}
	}
	public static function getPluralTitle(): ?string{
		$plural = self::getPluralClassName();
		if(!$plural){
			return null;
		}
		return QMStr::classToTitle($plural);
	}
	public static function getPluralClassName(): ?string{
		$name = self::getShortClassFromRoute();
		if(!$name){
			return null;
		}
		return ucfirst(QMStr::pluralize($name));
	}
	public static function getShortClassFromRoute(): ?string{
		$fullClassName = self::getFullClassFromRoute();
		if(!$fullClassName){
			return null;
		}
		return QMStr::toShortClassName($fullClassName);
	}
	public static function getFullClassFromRoute(string $url = null): ?string{
		$table = self::getTable($url);
		if(!$table){
			return null;
		}
		return QMStr::tableToFullClassName($table);
	}
	/** @noinspection PhpUnused */
	public static function getTable(string $url = null): ?string{
		$table = QMRequest::getParam(self::PARAM_TABLE); // Testing
		if(!empty($table) && !$url){
			return $table;
		}
		if(!$url){
			$url = $_SERVER["REQUEST_URI"] ?? null;
            if(empty($url)){
                $url = request()->url();
            }
			if(empty($url)){
				return null;
			}
			$url = QMStr::before("?", $url, $url);
		}
		$route = QMStr::after('admin/', $url);
		if(!$route){
			$route = QMStr::after('datalab/', $url);
		}
		if(!$route){
			$route = QMStr::after('api/v6/', $url);
		}
		if(!$route){
			$route = QMStr::after('api/v2/', $url);
		}
		if(!$route){
			$exploded = explode("/", $url);
			$end = end($exploded);
			$end = QMStr::snakize($end);
			if(BaseModel::tableExists($end)){
				return $end;
			}
		}
		if(!$route){
			return null;
		}
		$route = QMStr::addDBPrefixes($route);
		return QMDB::routeToTable($route);
	}
	/** @noinspection PhpUnused */
	public static function isIndexView(): bool{
		$url = url()->current();
		if(strpos($url, 'api/v6') !== false){
			return false;
		}
		$last = explode('/', $url);
		$end = end($last);
		$table = self::getTable();
		return $end === $table;
	}
	/**
	 * @param $value
	 * @return bool
	 */
	public static function isFalsy($value): bool{
		return BoolHelper::isFalsey($value);
	}
	/** @noinspection PhpUnused */
	/**
	 * @param array|null $params
	 * @return string
	 */
	public static function getHumanizedWhereClause(array $params = null): string{
		$params = $params ?? $_GET;
		$fields = QMRequest::getFilterableFields();
		$arr = [];
		foreach($params as $key => $value){
			if(in_array($key, $fields)){
				$arr[$key] = $value;
			}
		}
		$res = QueryBuilderHelper::getHumanizedWhereClause($arr, QMRequest::getTable());
		return $res;
	}
	public static function getFilterableFields(): array{
		$m = static::getModelInstance();
		return $m->getAllowedFilterFields();
	}
	/**
	 * @return BaseModel
	 */
	public static function getModelInstance(): BaseModel{
		$class = QMRequest::getFullClass();
		if(!class_exists($class)){
			$class = QMRequest::getFullClass();
			$table = static::getTableName();
			throw new \LogicException("$class not found!
                You might need to add alias for $table
                at \App\Storage\DB\QMDB::routeToTable");
		}
		if($u = QMAuth::getQMUser()){
			if($id = QMRequest::getId()){
				$model = $class::findInMemoryOrDB($id);
				if($model && $model->canReadMe($u->l())){
					return $model;
				}
			}
		}
		return new $class;
	}
	/**
	 * @return BaseModel
	 */
	public static function getFullClass(): ?string{
		$table = QMRequest::getTable();
		if(!$table){
			return null;
		}
		return BaseModel::getClassByTable($table);
	}
	public static function getTableName(): string{
		$class = QMRequest::getFullClass();
		return $class::TABLE;
	}
	/**
	 * @return array|mixed|string|null
	 */
	public static function getId(){
		return self::getParam('id');
	}
	public static function notNull(string $name): bool{
		$val = self::getParam($name);
		if(!$val){
			return false;
		}
		$val = strtolower($val);
		$val = str_replace("+", " ", $val);
		return $val === "not null";
	}
	public static function getSubtitleAttribute(): string{
		$param = self::getParam(self::PARAM_DESCRIPTION);
		if($param){
			return $param;
		}
		$description = "Better living through data";
		if(QMRequest::isModelRequest()){
			/** @var BaseModel $class */
			$m = self::getModelInstance();
			return $m->getSubtitleAttribute();
		}
		return $description;
	}
	public static function isModelRequest(): bool{
		$class = QMRequest::getFullClass();
		return class_exists($class);
	}
	public static function getKeywordString(): string{
		if(QMRequest::isModelRequest()){
			$class = static::getLaravelClass();
			return implode(", ", $class::getIndexKeywords());
		}
		return static::getTitleAttribute();
	}
	/**
	 * @return BaseModel
	 */
	public static function getLaravelClass(): string{
		$table = static::getTable();
		return BaseModel::getClassByTable($table);
	}
	public static function getTitleAttribute(): string{
		$param = self::getParam(self::PARAM_TITLE);
		if($param){
			return $param;
		}
		if(QMRequest::isModelRequest()){
			$title = QMRequest::getPluralTitleWithHumanizedQuery();
		} else{
			$title = qm_request()->getRequestUri();
			if(empty($title)){
				return config('app.name');
			}
			for($i = 1; $i < 10; $i++){
				$title = QMStr::after("/v$i/", $title, $title);
			}
			$title = QMStr::before('?', $title, $title);
			$title = QMStr::before('/api/', $title, $title);
			$title = str_replace('/', ' ', $title);
			$title = QMStr::titleCaseSlow($title);
		}
		return $title;
	}
	public static function getPluralTitleWithHumanizedQuery(): ?string{
		$qb = self::getQueryBuilder();
		if(!$qb){
			return null;
		}
		return $qb->getPluralTitleWithHumanizedQuery();
	}
	public static function getQueryBuilder(): QMQB{
		$qb = Writable::getBuilderByTable(self::getTableName());
		QueryBuilderHelper::addParams($qb, $_GET);
		return $qb;
	}
	public static function getImage(): string{
		$param = self::getParam(self::PARAM_IMAGE);
		if($param){
			return $param;
		}
		if(QMRequest::isModelRequest()){
			/** @var BaseModel $class */
			$m = self::getModelInstance();
			return $m->getImage();
		} else{
			$img = InternalImageUrls::BETTER_WORLD_THROUGH_DATA_PEOPLE_4096_2304;
		}
		return $img;
	}
	public static function setTable(string $table){
		self::setParam(self::PARAM_TABLE, $table);
	}
	/**
	 * @param $nameOrArray
	 * @param $value
	 * @return mixed
	 */
	public static function setParam($nameOrArray, $value){
		return Memory::setRequestParam($nameOrArray, $value);
	}
	public static function qb(): QMQB{
		return QMRequest::getQueryBuilder();
	}
	/**
	 * @param string|null $rawSort
	 * @return string
	 */
	public static function formatSort(?string $rawSort): string{
		$sort = str_replace('-', '', $rawSort);
		return QMStr::snakize($sort);
	}
	/**
	 * @return bool
	 */
	public static function autocomplete(): bool{
		return self::getBool(QMRequest::PARAM_AUTOCOMPLETE);
	}
	public static function getBool(string $name): bool{
		$val = QMRequest::getParam($name);
		return self::convertToBool($val);
	}
	public static function isLoggingOut(){
		if(UrlHelper::urlContains("logout=0")){
			return false;
		}
		return self::getBool(QMRequest::PARAM_LOGOUT);
	}
	/**
	 * @return bool
	 */
	public static function refresh(): bool{
		return self::getBool(QMRequest::PARAM_REFRESH);
	}
	public static function addClientWarning(string $message){
		QMLog::error($message);
	}
	/**
	 * @param BaseModel|DBModel|array|Collection $unsorted
	 * @param User|QMUser|int $reader
	 */
	public static function validateCanRead($unsorted, $reader = null){
		if(!$reader && !AppMode::isApiRequest()){
			return;
		}
		if($unsorted instanceof BaseModel || $unsorted instanceof DBModel){
			$unsorted = [$unsorted];
		}
		/** @var BaseModel[]|Collection $unsorted */
		foreach($unsorted as $one){
			$one->validateCanRead($reader);
		}
	}
	public static function hostIs(string $string): bool{
		return self::host() === $string;
	}
	public static function origin(): string{
		if(isset($_SERVER["HTTP_HOST"])){
			return self::protocol().$_SERVER["HTTP_HOST"];
		}
		return Env::getAppUrl();
	}
	/**
	 * @return string
	 */
	public static function protocol(): string{
		if(!isset($_SERVER['SERVER_PORT'])){
			$url = Env::getAppUrl();
			$p = QMStr::before("://", $url)."://";
			return $p;
		}
		return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ||
		        $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	}
	/**
	 * @return string|null
	 */
	public static function getSortColumnName(): ?string{
		$sort = self::getSort();
		if($sort){
			$sort = str_replace('-', '', $sort);
		}
		return $sort;
	}
	public static function getSort(): ?string{
		return QMRequest::getParam('sort');
	}
	public static function getSingularCamelClassTitle(string $class): string{
		$singular = QMRequest::getSingularClassTitle($class);
		return QMStr::camelize($singular);
	}
	public static function getSingularClassTitle(string $class = null): string{
		try {
			$class = (new \ReflectionClass($class ?? static::class))->getShortName();
		} catch (\ReflectionException $e) {
			le($e);
		}
		$title = QMStr::classToTitle($class);
		return QMStr::singularize($title);
	}
	public static function getViewPathByType(string $type, string $table = null): string{
		if($param = QMRequest::getParam(QMRequest::PARAM_VIEW)){
			return $param;
		}
		$table = $table ?? static::getSnakeClassPlural();
		$singular = QMStr::singularize($table);
		$singularClassWithDashes = strtolower(str_replace(" ", "-", $singular));
		if($dots = static::bladeExists("datalab.$table.$type-".$singularClassWithDashes)){
			return $dots;
		}
		if($dots = static::bladeExists("datalab.$table.$type")){
			return $dots;
		}
		if($dots = static::bladeExists("model-$type")){
			return $dots;
		}
		if($dots = static::bladeExists($type)){
			return $dots;
		}
		throw new \LogicException("View not found for $dots");
	}
	protected static function getSnakeClassPlural(): string{
		return QMStr::pluralize(static::getSnakeClass());
	}
	protected static function getSnakeClass(): string{
		$singular = static::getShortClass();
		return QMStr::snakize($singular);
	}
	protected static function getShortClass(): string{
		return QMStr::toShortClassName(QMRequest::getFullClass());
	}
	private static function bladeExists(string $dots): ?string{
		$prefix = 'resources/views/';
		$path = $prefix.str_replace('.', '/', $dots).'.blade.php';
		if(FileHelper::fileExists($path)){
			return $dots;
		}
		return null;
	}
	public static function showBackToAllButton(): bool{
		return !static::isFiltering();
	}
	public static function isFiltering(): bool{
		$filters = static::getFilters();
		return !empty($filters);
	}
	public static function getFilters(): array{
		$params = request()->input();
		$filters = [];
		$m = self::getModelInstance();
		foreach($params as $key => $value){
			$valid = $m->validFilterField($key);
			if($valid){
				$filters[$valid] = $value;
			}
		}
		return $filters;
	}
	/**
	 * @return string[]
	 */
	public static function getGetColumnsFromRequest(): array{
		$params = request()->input() ?? $_GET ?? [];
		$m = self::getModelInstance();
		$requested = [];
		foreach($params as $key => $val){
			$valid = $m->validFilterField($key);
			if($valid){
				$requested[] = $valid;
			}
		}
		return $requested;
	}
	public static function errorFilter(): bool{
		$internal = QMRequest::getParam(UserVariableRelationship::FIELD_INTERNAL_ERROR_MESSAGE);
		if($internal && stripos($internal, "not") === 0){
			return true;
		}
		return (bool)QMRequest::getParam([QMRequest::PARAM_ERRORED]);
	}
	/**
	 * @return bool
	 */
	public static function deletedFilter(): ?bool{
		return QMRequest::getParam([QMRequest::PARAM_DELETED]);
	}
	public static function userFilter(): bool{
		$id = $_GET['user_id'] ?? $_GET['userId'] ?? null;
		return !empty($id);
	}
	public static function hasAttribute(string $name): bool{
		return static::getModelInstance()->hasColumn($name);
	}
	/** @noinspection PhpUnused */
	public static function getQueryParams(): array{
		$params = self::getFilters();
		if($sort = self::getSort()){
			$params['sort'] = $sort;
		}
		return $params;
	}
	/**
	 * Bootstrap the given application.
	 * @param QMSlim $app
	 * @return void
	 */
	public static function bootstrapForSlim(QMSlim $app){
		$r = $app->request();
        $url = $r->getUrl();
        $path = $r->getPath();
        $getPathInfo = $r->getPathInfo();
        $params =  $r->params();
		app()->instance(Memory::QM_REQUEST,
			QMRequest::create($url.$path, $r->getMethod(), $params, 
			                  $r->cookies()->all(), [], $r->headers()->all()));
	}
	/**
	 * Bootstrap the given application.
	 * @param Application $app
	 * @return void
	 */
	public static function bootstrapForConsole(Application $app){
		$uri = config('app.url', 'http://localhost');
		$components = parse_url($uri);
		$server = $_SERVER;
		if(isset($components['path'])){
			$server = array_merge($server, [
				'SCRIPT_FILENAME' => $components['path'],
				'SCRIPT_NAME' => $components['path'],
			]);
		}
		$app->instance(Memory::QM_REQUEST, $r = QMRequest::create($uri, 'GET', [], [], [], $server));
		Memory::set(Memory::QM_REQUEST, $r);
	}
	/**
	 * @param string|null $param
	 * @return array|bool|mixed|resource|string|null
	 */
	public static function getInput(string $paramName = null) {
        $r = qm_request();
        $input = $r->input();
        if(!$input){
            $body = QMRequest::body();
            $query = QMRequest::getQuery();
            $input = array_merge($body ?? [], $query ?? []);
        }
		if($paramName){
			return QMArr::pluckValue($input, $paramName);
		}
        return $input;
    }

    public static function analyze()
    {
		$input = self::getInput('analyze');
		return $input;
    }

    public static function isGet()
    {
		return qm_request()->isMethod('GET');
    }

    /**
	 * @return string
	 */
	protected static function getShortClassName(): string{
		if(!AppMode::isApiRequest()){
			return QMStr::toShortClassName(static::class);
		}
		$class = QMRequest::getShortClassFromRoute();
		return $class;
	}
	private static function port(){

	}
	public function getDataLabRouteName(): ?string{
		return QMStr::between(url()->current(), 'datalab/', '/');
	}
	public function getPluralClassTitle(): string{
		return QMStr::pluralize(QMRequest::getSingularClassTitle());
	}
	public function getViewPath(): ?string{
		$viewPath = $table = qm_request()->getTable();
		if($table === User::TABLE){
			$viewPath = "users";
		}
		return $viewPath;
	}
	/**
	 * @param $id
	 * @param string $viewOrModify
	 * @return string
	 */
	public function getNotAuthorizedMessage($id, string $viewOrModify): string{
		return "You are not authorized to $viewOrModify ".QMRequest::getFullClass()." $id";
	}
	/**
	 * Create a new Illuminate HTTP request from server variables.
	 *
	 * @return static
	 */
	public static function capture(): QMRequest{
		static::enableHttpMethodParameterOverride();
		return static::createFromBase(request());
	}
	/**
	 * @param null $key
	 * @param null $default
	 * @return array|bool|null|resource|string
	 */
	public function input($key = null, $default = null){
		$body = self::body();
		if($body){
			return data_get(
				$body, $key, $default
			);
		}
		return data_get(
			$this->getInputSource()->all() + $this->query(), $key, $default
		);
	}
}
