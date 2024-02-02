<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpMissingFieldTypeInspection */

namespace App\Slim;
use App\CodeGenerators\Swagger\SwaggerParameter;
use App\CodeGenerators\Swagger\SwaggerResponse;
use App\Console\Kernel;
use App\Exceptions\BadRequestException;
use App\Exceptions\ExceptionHandler;
use App\Files\FileHelper;
use App\Http\Parameters\LimitParam;
use App\Logging\ConsoleLog;
use App\Logging\QMClockwork;
use App\Logging\QMLog;
use App\Logging\QMLogger;
use App\Logging\QMLogLevel;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Configuration\RouteConfiguration;
use App\Slim\Controller\Controller;
use App\Slim\Controller\OAuth2\CreateAccessTokenController;
use App\Slim\Controller\OAuth2\GetAuthorizationPageController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\GoogleAnalyticsEvent;
use App\Slim\Model\QMResponseBody;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Types\ObjectHelper;
use App\Types\QMStr;
use App\Utils\APIHelper;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\EnvOverride;
use Clockwork\Support\Slim\Old\ClockworkMiddleware;
use Exception;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use LogicException;
use OAuth2\Storage\Pdo as OAuth2Pdo;
use Slim\Http\Request as HttpRequest;
use Slim\Slim;
/** Class Application
 * @package App\Slim
 */
class QMSlim extends Slim {
	/**
	 * Error constants.
	 */
	public const ERROR_EMPTY_REQUEST = 'No data was sent with the request';
	/**
	 * The Methods namespace
	 */
	public const NAMESPACE_METHODS = 'App\\Slim\\Controller';
	/**
	 * @var OAuth2Pdo
	 */
	public static $oauth;
	private static $encoded;
	private static $laravel;
	private $maxAgeInSeconds;
    private Request $laravelRequest;
    private \App\Http\Kernel $kernel;

    /**
	 * Constructor
	 */
	public function __construct(bool $console) {
		self::$apps = [];  // Important: Reset static variable for testing
		Memory::set(Memory::SLIM, $this);
        if($console){
            QMSlim::bootstrapLaravelConsoleAppIfNecessary();
        } else {
			if(!isset($_SERVER['HTTP_COOKIE'])){
				$_SERVER['HTTP_COOKIE'] = ''; // Prevents PHP warning
			}
            AppMode::setIsApiRequest(true);  // Not sure why this doesn't work sometimes?
            QMSlim::bootstrapLaravelHttpApp();
        }
		parent::__construct($this->getSlimSettings());
		$exceptionHandler = new SlimExceptionHandler();
		$exceptionHandler->register($this); // Setup error handling.
		APIHelper::setRequestStartMicroTime();
		$this->registerAllRouteTypes();
		$this->setHeaders();
		QMRequest::bootstrapForSlim($this);
		if(QMClockwork::enabled()){$this->add(new ClockworkMiddleware(config('clockwork.storage_files_path')));}
	}
	private function setHeaders(){
		$this->response->headers->set('Content-Type', 'application/json');
		$this->response->headers->set('Access-Control-Allow-Origin',
			'*');  // This appears to be necessary in both nginx config and here
		// This is already done in configs/etc/nginx/quantimodo.locations.nginx.conf
		//$this->response->headers->set('Access-Control-Allow-Headers', 'Authorization, content-type, X-Mashape-Key, X-App-Name, X-Platform, X-App-Version, X-Client-Id', 'X-Framework', 'X-Timezone');
		//$this->response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
		//$this->response->headers->set('Cache-Control', "no-cache, must-revalidate, max-age=0");
	}
	/**
	 * URGENT Replace this with QMSlim::bootstrapApp to avoid disaster since bootstrapApp copies env
	 */
	public static function bootstrapLaravelConsoleAppIfNecessary(){
		if(!defined('PROJECT_ROOT')){
			define('PROJECT_ROOT', dirname(__DIR__));
		}
		//ConsoleLog::debug("PROJECT_ROOT is " . PROJECT_ROOT);
		if(!isset($_ENV['DB_HOST'])){ // Avoid re-loading if already loaded
			QMSlim::bootstrapLaravelConsoleApp();
		} else{
			ConsoleLog::debug(__FUNCTION__ . ": not bootstrapping because already bootstrapped.");
		}
		//Env::validateEnv();
	}
	/**
	 * @return OAuth2Pdo
	 */
	public static function getOAuthConnection(): OAuth2Pdo{
		// OAuth2 storage (clients, tokens, etc)
		if(!self::$oauth){
			self::$oauth = new OAuth2Pdo(DB::connection(Writable::CONNECTION_NAME)->getPdo(), [
				'client_table' => 'oa_clients',
				'access_token_table' => 'oa_access_tokens',
				'refresh_token_table' => 'oa_refresh_tokens',
				'code_table' => 'oa_authorization_codes',
				'user_table' => 'oa_users',
				'jwt_table' => 'oa_jwt',
				'scope_table' => 'oa_scopes',
				'public_key_table' => 'oa_public_keys',
			]);
		}
		return self::$oauth;
	}

    private function bootstrapLaravelHttpApp(){
        /** @var Application $app */
        $app = require __DIR__ . '/../../bootstrap/app.php';
        /** @var \App\Http\Kernel $kernel */
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        $this->laravelRequest = Request::capture();
        $kernel->getApplication()->instance('request', $this->laravelRequest);
        $kernel->bootstrap();
        $this->kernel = $kernel;
    }

    /**
	 * @param $body
	 * @param int $size
	 * @param $obj
	 */
	private static function analyzeResponsePropertySizes($body, int $size, $obj): void{
		$message = "Response size is $size KB. ";
		if(is_object($obj)){
			$obj = $obj->data ?? $obj->study ?? $obj->variable ?? $obj;
		}
		if(is_array($obj) && isset($obj[0])){
			$obj = $obj[0];
			$message .= "First element of array ";
		}
		if(!isset($obj)){
			$obj = $body;
		}
		if(is_object($obj)){
			$sizes = ObjectHelper::getPropertySizesInKb($obj, true, true);
			QMLog::warning("$message property sizes: " . \App\Logging\QMLog::print_r($sizes, true), $sizes);
		}
	}
	/**
	 * @return string
	 */
	protected function getETag(): string{
		// TODO: Alphabetize query params to avoid duplicate content with different eTags
		$url = QMRequest::current();
		$eTag = QMStr::after('api/', $url);
		$user = QMAuth::getQMUser();
		if($user){
			$eTag .= '-' . $user->id;
		}
		$eTag = QMStr::slugify($eTag, false, 0);
		return $eTag;
	}
	/**
	 * @param int $maxAgeSeconds
	 */
	public function setCacheControlHeader(int $maxAgeSeconds = 5){
		$eTag = $this->getETag();
		$this->etag($eTag);
		//$this->lastModified($eTag);  // TODO: Add last modified field to memcached that is updated by variable, reminder, and correlation tasks
		//if(QMAccessToken::isDemo()){$maxAgeSeconds *= 100;}
		$this->expires(time() + $maxAgeSeconds);
		$this->maxAgeInSeconds = $maxAgeSeconds;
		$value = "max-age=$maxAgeSeconds";
		$this->response->headers->set('Cache-Control', $value);
	}
	/**
	 * Stop
	 * The thrown exception will be caught in application's `call()` method
	 * and the response will be sent as is to the HTTP client.
	 */
	public function stop(){
		/** @noinspection PhpUnhandledExceptionInspection */
		parent::stop();
	}
    private const APP_NAME = 'default';
	/**
	 * Get application instance by name. I did this wrapper method for IDE type hinting
	 * @param string $name The name of the Slim application
	 * @return QMSlim|null
     */
	public static function getInstance($name = self::APP_NAME): ?QMSlim
    {
        return static::$apps[$name] ?? null;
	}
	/**
	 * @return bool|HttpRequest
	 */
	public static function getRequest(){
		$i = self::getInstance();
		if(!$i){
			return false;
		}
		return $i->request();
	}
	/**
	 * @return string
	 */
	public static function getCurrentUrl(): ?string{
		$r = self::getRequest();
		if($r){ // We're using Slim instead of Laravel
			return $r->getScheme() . '://' . $r->getHostWithPort() . $r->getPath();
		}
		if(!isset($_SERVER["REQUEST_URI"])){
			return null;
		}
		$origin = \App\Utils\Env::getAppUrl();
		if(isset($_SERVER['HTTP_HOST'])){
			if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
				$protocol = "https";
			} else{
				$protocol = "http";
			}
			$origin = "$protocol://" . $_SERVER['HTTP_HOST'];  // Append the host(domain name, ip) to the URL.
		}
		$url = $origin . $_SERVER['REQUEST_URI']; // Append the requested resource location to the URL
		return $url;
	}
	/**
	 * Parse and valid request body as JSON string, return parsed object.
	 * @param bool $includeClientId
	 * @param bool $throw
	 * @return array The request decoded to an associative array.
	 */
	public function getRequestJsonBodyAsArray(bool $includeClientId, bool $throw = false): ?array{
		$decoded = QMRequest::body();
		if(!$decoded){
			if($throw){
				throw new BadRequestException(self::ERROR_EMPTY_REQUEST);
			}
			return null;
		}
		BaseClientIdProperty::setInMemory($decoded);
		if(!$includeClientId){
			unset($decoded['clientId'], $decoded['client_id']);
		}
		return $decoded;
	}
	/**
	 * Write the response with a specific status, content type and response.
	 * @param int $status The HTTP response status
	 * @param string $contentType The content type of the response.
	 * @param mixed $response The response to send to the client.
	 */
	public function write(int $status, string $contentType, $response): Response
    {
		$this->cleanBuffer();
		$this->response->status($status);
		$this->response->headers->set('Content-Type', $contentType);
		$this->response->body($response);
		Memory::resetClearOrDeleteAll();
        return new Response($response, $status, [
            'Content-Type' => $contentType,
        ]);
	}
	/**
	 * Send the response with a specific status, JSON content type and JSON message to the HTTP client.
	 * @param int $status The HTTP response status
	 * @param mixed $htmlString The HTML string to output.
	 * @return Response
     */
	public function writeHtml(int $status, $htmlString): Response
    {
		return $this->write($status, 'text/html', $htmlString);
	}
	/**
	 * Send the response with a specific status, JSON content type and JSON message to the HTTP client.
	 * @param int $code The HTTP response status
	 * @param mixed $body The object which should be encoded in JSON and sent in HTTP body
	 * @param int|null $jsonOpt
	 * @return void|Response
     */
	public function writeJsonWithGlobalFields(int $code, $body, int $jsonOpt = null){
		Controller::setUserVariableIdToVariableIdIfNecessary($body);
		if(is_array($body)){
			$body = new QMResponseBody($body, $code);
		}
		if(is_string($body)){
			return $this->writeHtml($code, $body);
		} else{
			$body->addErrorsDescriptionAndTokens();
			return $this->writeJsonWithoutGlobalFields($code, $body, $jsonOpt);
		}
	}
	/**
	 * Send the response with a specific status, JSON content type and JSON message to the HTTP client.
	 * @param int $code The HTTP response status
	 * @param mixed $body The object which should be encoded in JSON and sent in HTTP body
	 * @param int|null $jsonOpt
	 */
	public function writeJsonWithoutGlobalFields(int $code, $body, int $jsonOpt = null){
		Controller::setUserVariableIdToVariableIdIfNecessary($body);
		// TODO: Update php-ga-measurement-protocol lib to log API Request to Google Analytic
		// $this->logToGoogleAnalytics();
		if($this->request()->get('format') === 'csv'){
			$this->downloadCSV($body);
			return \response()->download($body, 'file.csv', ['Content-Type' => 'text/csv']);
		}
		$json = $this->jsonEncodeAndCheckForErrors($body, $jsonOpt);
		$this->checkForSuccessField($json);
		return $this->writeJson($code, $json, $body);
	}

    /**
     * @param int $code
     * @param $data
     * @param $json
     */
	public function registerDevelopmentShutdownTasks(int $code, $data, $json){
		self::$encoded = $json;
		$dev = EnvOverride::isLocal();
		//$testing = AppMode::isTestingOrStaging() && !AppMode::isProductionTesting();
		$testing = AppMode::isTestingOrStaging();  // No need for this in production testing
		if($dev && !$testing){
			register_shutdown_function(function() use ($code, $data, $json){  // Wait until after profiling
				$this->updateSwagger($code, $data);
				$this->checkForTooManyZeros($json);
				self::checkResponseSizeIfGreaterThan2MB($data, $json);
                $app = app();
                $app->terminate($data, $code);
				//$this->checkForEmptyParenthesis(Application::$responseData, Application::$encoded);
			});
		}
	}
	/**
	 * Encode array to utf8 recursively
	 * @param $dat
	 * @return array|string
	 */
	public static function array_utf8_encode($dat){
		if(is_string($dat)){
			return utf8_encode($dat);
		}
		if(!is_array($dat)){
			return $dat;
		}
		$ret = [];
		foreach($dat as $i => $d){
			$ret[$i] = self::array_utf8_encode($d);
		}
		return $ret;
	}
	/**
	 * @param $data
	 * @param null $jsonOpt
	 * @return string
	 */
	public function jsonEncodeAndCheckForErrors($data, $jsonOpt = null): string{
		$encoded = self::$encoded = $jsonOpt ? json_encode($data, $jsonOpt) : json_encode($data);
		[$data, $encoded] = $this->retryJsonEncodeIfError($data, $jsonOpt, $encoded);
		//SecretHelper::exceptionIfContainsSecretValue($encoded);
		return $encoded;
	}
	/**
	 * @param string $encoded
	 */
	private function checkForSuccessField(string $encoded){
		if(APIHelper::apiVersionIsAbove(3) && strpos($encoded, '"success":') === false){
			QMLog::error("Responses from API above v3 should have success field!");
		}
	}
	/**
	 * Stop the application and immediately send the response with a specific status, JSON content type
	 * and JSON message to the HTTP client.
	 * @param int $code The HTTP response status
	 * @param mixed $body The object which should be encoded in JSON and sent in HTTP body
	 * @return void
	 */
	public function haltJson(int $code, $body){
		$this->writeJsonWithGlobalFields($code, $body);
		$this->stop();
	}
	/**
	 * Stop the application and immediately send the response with a specific status, JSON content type
	 * and JSON message to the HTTP client.
	 * @param int $status The HTTP response status.
	 * @param mixed $htmlString The HTML string to return to the client.
	 * @return void
	 */
	public function haltHtml(int $status, $htmlString){
		$this->writeHtml($status, $htmlString);
		$this->stop();
	}
	/**
	 * Get Slim application settings.
	 * Note: debug mode should be set in order to pass the exception to HandleErrors middleware.
	 * Else it is handled internally.
	 * @return array
	 */
	private function getSlimSettings(): array{
		return [
			'mode' => AppMode::getAppMode(),
			'debug' => false,
			// Always use false, or it interferes with response making it different that production response.  Get error info from Bugsnag or Tracy
			'templates.path' => realpath(__DIR__ . '/Templates'),
			'log.enable' => !empty(Env::get('LOG_LEVEL')),
			'log.level' => QMLogLevel::getSlimInt(),
			'log.writer' => QMLogger::get(),
		];
	}
	/**
	 * Register routing for application users
	 *  When a method type is unsupported.
	 */
	private function registerUserRoutes(){
		$routeConfig = new RouteConfiguration();
		$routes = RouteConfiguration::getRoutes();
		foreach($routes as $def){
			$methodName = $this->getMethodName($def); // Figure out the method to call in the controller
			$callable = self::NAMESPACE_METHODS . '\\' . $def[RouteConfiguration::FIELD_CONTROLLER] . ':' . $methodName;
			$slimRoute = $this->map(RouteConfiguration::BASE_PATH . $def[RouteConfiguration::FIELD_PATH], $callable);
			//$slimRoute->setMiddleware(LimitParam::validateLimit());
			if($def[RouteConfiguration::FIELD_AUTH]){
				$slimRoute->setMiddleware(QMAuth::authenticate($def[RouteConfiguration::FIELD_AUTH_SCOPE]));
			}
			$slimRoute->via($def[RouteConfiguration::FIELD_METHOD]);
		}
	}
	/**
	 * Register routing for admin requests
	 * @return void
	 */
	private function registerAdminRoutes(){
		$ctrl = 'App\Slim\Controller\Administration:';
		$this->get('/api/ionic/master/merge', Middleware\QMAuth::authenticateAdmin(), $ctrl . 'ionicMasterMerge');
		$this->get('/api/administration/cache/clear', Middleware\QMAuth::authenticateAdmin(), $ctrl . 'getCacheClear');
		$this->get('/api/user_variable_relationships/update', Middleware\QMAuth::authenticateAdmin(), $ctrl . 'getCorrelationsUpdate');
		$this->get('/api/user_variable_relationships/updateAllVariables', Middleware\QMAuth::authenticateAdmin(),
			$ctrl . 'getCorrelationsUpdateAllVariables');
		$this->get('/api/user_variable_relationships/updateAllUsers', Middleware\QMAuth::authenticateAdmin(),
			$ctrl . 'getCorrelationsUpdateAllUsers');
		$this->get('/api/user_variable_relationships/updateAVariableForAllUsers', Middleware\QMAuth::authenticateAdmin(),
			$ctrl . 'getCorrelationsUpdateAVariableForAllUsers');
		$this->get('/api/user_variable_relationships/updateOutcomesForAllUsers', Middleware\QMAuth::authenticateAdmin(),
			$ctrl . 'getCorrelationsUpdateOutcomesForAllUsers');
	}
	/**
	 * Register routing for admin requests
	 * @return void
	 */
	private function registerOauthRoutes(){
		$this->get('/oauth/authorize', GetAuthorizationPageController::class . ':get');
		$this->post('/oauth/token',  CreateAccessTokenController::class . ':post');
	}
	/**
	 * @return void
	 */
	private function registerDocumentationRoute(){
		$app = $this;
		$this->get('/api/', function() use ($app){
			$app->response->headers->set('Content-Type', 'text/html');
			$app->redirect('/api/docs/');
		});
	}
	/**
	 * Return 200 when we get an OPTIONS request
	 * @return void
	 */
	private function registerOptionsRoute(){
		$app = $this;
		$this->options('/:x+', function() use ($app){
			$app->halt(200, '');
		});
	}
	/**
	 * @param $status
	 * @param $arr
	 */
	protected function updateSwagger($status, $arr){
		if(\App\Utils\Env::get('GENERATE_EXAMPLES')){
			SwaggerResponse::saveToResponseBodyExamplesFolder(QMRequest::getRequestPathAsFileName(), $arr);
		}
		if(\App\Utils\Env::get('UPDATE_SWAGGER_JSON')){
			SwaggerParameter::addNewSwaggerParameters($_GET);
			SwaggerResponse::addNewSwaggerGetResponses($status, $arr);
		}
	}
	/**
	 * @param $arr
	 */
	protected function downloadCSV($arr){
		$longestArrayCount = count($arr);
		$longestArray = $arr;
		$path = $this->request()->getPath();
		$parts = explode('/', $path);
		$objectType = $parts[count($parts) - 1];
		foreach($arr as $key => $value){
			if(is_array($value) && count($value) > $longestArrayCount){
				$longestArray = $value;
				$objectType = $key;
			}
		}
		FileHelper::downloadCsv($longestArray, $objectType);
	}
	protected function logToGoogleAnalytics(){
		le("TODO: Update php-ga-measurement-protocol lib to log API Request to Google Analytic");
		try {
			GoogleAnalyticsEvent::logApiRequestToGoogleAnalytics();
		} catch (Exception $e) {
            if(!AppMode::isWindows()){
                ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
            }
		}
	}
	/**
	 * @param object|array $body
	 * @param string $encoded
	 */
	private static function checkResponseSizeIfGreaterThan2MB($body, string $encoded){
		$key = QMRequest::requestUri() . ":" . now_at();
		$size = ObjectHelper::getSizeOfStringInKiloBytes($encoded);
		Memory::set($key, ['size' => $size, 'data' => $body], Memory::RESPONSES);
		$obj = $body;
		if($size > 2000 && is_object($body)){ // Can't handle associative arrays
			self::analyzeResponsePropertySizes($body, $size, $obj);
		}
	}

    /**
     * @param $encoded
     */
	private function checkForTooManyZeros($encoded){
		if(EnvOverride::isLocal() && strpos($encoded, $zeros = "0000000") !== false){
			$precision = ini_get('precision');
			$serialize_precision = ini_get('serialize_precision');
			$pos = strpos($encoded, $zeros);
			$segment = substr($encoded, $pos - 20, 60);
			QMLog::warning("Response contains $zeros.  
precision: $precision; 
serialize_precision: $serialize_precision
segment: $segment");
		}
	}
	/**
	 * @return int
	 */
	public function getMaxAgeInSeconds(): ?int{
		return $this->maxAgeInSeconds;
	}
	/**
	 * @param $data
	 * @param $jsonOpt
	 * @param $lastError
	 * @return array
	 */
	protected function utf8JsonEncode($data, $jsonOpt, $lastError): array{
		QMLog::error("json_last_error_msg: " . $lastError, $data);
		$data = self::array_utf8_encode($data);
		$encoded = $jsonOpt ? json_encode($data, $jsonOpt) : json_encode($data);
		return [$data, $encoded];
	}
	/**
	 * @param $data
	 * @param $jsonOpt
	 * @param $encoded
	 * @return array
	 */
	protected function retryJsonEncodeIfError($data, $jsonOpt, $encoded): array{
		if(!$encoded){
			$lastError = json_last_error_msg();
			if($lastError === 'JSON_ERROR_UTF8'){
				[$data, $encoded] = $this->utf8JsonEncode($data, $jsonOpt, $lastError);
			}
			if(stripos($lastError, "Recursion") !== false){
				$data = ObjectHelper::remove_recursive_circular_references($data);
				$encoded = $jsonOpt ? json_encode($data, $jsonOpt) : json_encode($data);
			}
			if(!$encoded){
				QMLog::error("json_last_error_msg: " . $lastError);
				le("Could not json encode response: " . $lastError);
			}
		}
		return [
			$data,
			$encoded,
		];
	}
	private function registerAllRouteTypes(){
		$this->config('routes.case_sensitive', false);
		$this->registerUserRoutes();
		$this->registerOauthRoutes();
		$this->registerAdminRoutes();
		$this->registerDocumentationRoute();
		$this->registerOptionsRoute();
	}
	/**
	 * Default Not Found handler
	 * @param null $callable
	 */
	public function notFound($callable = null){
		// Not sure why ob_start is here but it breaks tests when throwing exceptions
		//ob_start();
		$message = '
            <h1>' . QMRequest::current() . ' could not be found.
                </h1>
            ' . "
            <h2>
                Please create a ticket at
                <a href=\"https://help.quantimo.do\">
                    https://help.quantimo.do
                </a>
            </h2>
            <h2>
                or check out the documentation at
                <a href=\"https://docs.quantimo.do\">
                    https://docs.quantimo.do
                </a>
            </h2>
        ";
		echo static::generateTemplateMarkup('404 Page Not Found', $message);
		$this->haltHtml(404, $message);
	}
	/**
	 * @param string $name
	 * @param $value
	 */
	public static function setEnvironmentalVariable(string $name, $value){
		putenv("$name=$value");
		$_ENV[$name] = $value;
		$_SERVER[$name] = $value;
		//app('config')->set([$name => $value]);
	}
	public static function destroyInstances(){
		self::$apps = [];
	}
	/**
	 * @param string|null $key
	 * @param mixed $default
	 * @return string|array
	 */
	public function params(string $key = null, $default = null){
		$params = $this->request->params($key, $default);
		if($params){
			$params = QMStr::urlDecodeIfNecessary($params);
		}
		return $params;
	}

    /**
     * @return Application
     */
	public static function bootstrapLaravelConsoleApp(): Application{
        /** @var Application $app */
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $k = $app->make(\App\Console\Kernel::class);
        $k->bootstrap();
        QMRequest::bootstrapForConsole($app);
		try {
			Writable::logDbNameAndHost(true);
		} catch (\Throwable $e){
		    ConsoleLog::error("Could not logDbNameAndHost because ".$e->getMessage());
		}
		return self::$laravel = $app;
	}
	/**
	 * @param array $def
	 * @return string
	 */
	private function getMethodName(array $def): string{
		if(isset($def[RouteConfiguration::FIELD_ACTION])){
			return $def[RouteConfiguration::FIELD_ACTION];
		}
		switch($def[RouteConfiguration::FIELD_METHOD]) {
			case HttpRequest::METHOD_GET:
				$methodName = 'initGet';
				break;
			case HttpRequest::METHOD_POST:
				$methodName = 'initPost';
				break;
			case HttpRequest::METHOD_DELETE:
				$methodName = 'initDelete';
				break;
			case HttpRequest::METHOD_OPTIONS:
				$methodName = 'initOptions';
				break;
			default:
				throw new LogicException(sprintf('Invalid HttpRequest method in route %s',
					$def->$def[RouteConfiguration::FIELD_PATH]));
		}
		return $methodName;
	}

    /**
     * @param int $code
     * @param string $json
     * @param $body
     * @return Response
     */
	public function writeJson(int $code, string $json, $body): JsonResponse{
		try {
			$this->registerDevelopmentShutdownTasks($code, $body, $json);
		} catch (Exception $e) {
			ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
		}
        $this->write($code, 'application/json', $json);
		return new JsonResponse($body, $code);
	}
    /**
     * Call
     *
     * This method finds and iterates all route objects that match the current request URI.
     */
    public function call()
    {
		Env::loadEnvIfNoAppUrl();  // Not sure why this needs to be done here as well as index-slim.php
		Env::getAppUrl();
        try {
            if (isset($this->environment['slim.flash'])) {
                $this->view()->setData('flash', $this->environment['slim.flash']);
            }
            $this->applyHook('slim.before');
            ob_start();
            $this->applyHook('slim.before.router');
            $dispatched = false;
	        $request = $this->request;
	        $uri = $request->getResourceUri();
            if($uri === "/"){
                $uri = $_SERVER["SCRIPT_NAME"];
            }
	        $method = $request->getMethod();
	        $router = $this->router;
	        $matchedRoutes = $router->getMatchedRoutes($method, $uri);
            foreach ($matchedRoutes as $route) {
                try {
                    $this->applyHook('slim.before.dispatch');
                    $dispatched = $route->dispatch();
                    $this->applyHook('slim.after.dispatch');
                    if ($dispatched) {
                        break;
                    }
                } catch (\Slim\Exception\Pass $e) {
                    continue;
                }
            }
            if (!$dispatched) {
                $this->notFound();
            }
            $this->applyHook('slim.after.router');
            $this->stop();
        } catch (\Slim\Exception\Stop $e) {
            $this->response()->write(ob_get_clean());
        } catch (\Exception $e) {
            if ($this->config('debug')) {
                ob_end_clean();
                throw $e;
            } else {
                try {
                    $this->response()->write(ob_get_clean());
                    $this->error($e);
                } catch (\Slim\Exception\Stop $e) {
                    // Do nothing
                }
            }
        }
    }

    /**
     * @return \App\Http\Kernel
     */
    public function getKernel(): \App\Http\Kernel
    {
        return $this->kernel;
    }

    /**
     * @return Request
     */
    public function getLaravelRequest(): Request
    {
        return $this->laravelRequest;
    }
}
