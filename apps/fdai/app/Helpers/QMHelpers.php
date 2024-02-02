<?php /** @noinspection PhpUnused */ /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ // Can't detect usages in blades for some reason
use App\AppSettings\AppDesign\AliasSettings;
use App\AppSettings\AppSettings;
use App\AppSettings\HostAppSettings;
use App\Buttons\Auth\LoginButton;
use App\Buttons\Auth\RegistrationButton;
use App\Buttons\IonicButton;
use App\Buttons\States\StudiesStateButton;
use App\DataSources\Connectors\FacebookConnector;
use App\DataSources\Connectors\GoogleLoginConnector;
use App\DevOps\XDebug;
use App\Exceptions\ExceptionHandler;
use App\Files\FileHelper;
use App\Http\Urls\FinalCallbackUrl;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Mail\PhysicianInvitationEmail;
use App\Models\Application;
use App\Properties\Base\BaseAppDescriptionProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseUserEmailProperty;
use App\Reports\AnalyticalReport;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Types\BoolHelper;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\InternalImageUrls;
use App\UI\MetaHtml;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\EnvOverride;
use App\Utils\IonicHelper;
use App\Utils\QMRoute;
use App\Utils\UrlHelper;
use Facade\Ignition\ErrorPage\ErrorPageHandler;
use Illuminate\Support\HtmlString;
/**
 * @return string
 */
function physicianAlias(): string{
	return AliasSettings::getPhysicianAlias();
}
/**
 * @return string
 */
function patientAlias(): string{
    return AliasSettings::getPatientAlias();
}
/**
 * @return string
 */
function hostClientId(): string{
    return BaseClientIdProperty::getHostClientId();
}
/**
 * @return string
 */
function hostOriginWithProtocol(): string{
    return QMRequest::origin();
}
/**
 * @return string
 */
function hostOriginWithoutProtocol(): string{
    return hostClientId().".quantimo.do";
}
/**
 * @param $string
 * @return string
 */
function camelCaseToTitleCase($string): string{
    return QMStr::camelToTitle($string);
}
/**
 * @param int $maxAge
 * @return AppSettings
 */
function getHostAppSettings(int $maxAge = 86400): AppSettings{
    return HostAppSettings::instance($maxAge);
}
/**
 * @return Application
 */
function host_application(): Application {
	return HostAppSettings::application();
}
/**
 * @return string
 */
function text_logo(): string {
    return HostAppSettings::application()->text_logo;
}
/**
 * @param string $path
 * @param array $params
 * @return string
 */
function qm_url(string $path, array $params = []): string {
    $url = QMRequest::origin().'/'.trim($path, '/');
    $url = UrlHelper::addParams($url, $params);
    return $url;
}
/**
 * @return string
 */
function getRegisterUrl(): string {
    return RegistrationButton::url($_GET);
}
/**
 * @return string
 */
function getLoginPageUrl(): string {
    return LoginButton::url($_GET);
}
/**
 * @return bool
 */
function showLogo(): bool{
    $val = QMRequest::getParamFromCurrentOrIntendedUrl('showLogo');
    return BoolHelper::toBool($val);
}
/**
 * @return bool
 */
function isMobile(): bool{
    return Application::setIsMobile();
}
/**
 * @return string
 */
function getClientIdFromRequestOrQuantiModoAsFallback(): string{
    if ($clientId = BaseClientIdProperty::fromMemory()) {return $clientId;}
    $clientId = BaseClientIdProperty::fromRequest(false);
    if(!$clientId){return BaseClientIdProperty::CLIENT_ID_QUANTIMODO;}
    Session::put('CLIENT_ID', $clientId);
    return $clientId;
}
/**
 * @param $name
 * @param string $message
 * @param array|object $meta
 */
function logError($name, string $message = '', $meta = []): void{
    Log::error($message, $meta);
    QMLog::error($name, $meta, $message);
}
/**
 * @return string
 */
function getPhysicianFeatureBulletsHtml(): string{
    return PhysicianInvitationEmail::PHYSICIAN_FEATURES_BULLET_HTML;
}
/**
 * @return string|null
 */
function getUserEmailFromUrlOrRedirectParameter(): ?string{
    return BaseUserEmailProperty::getUserEmailFromUrlOrRedirectParameter();
}
/**
 * @param string $name
 * @param null $default
 * @return string|null
 */
function getUrlParam(string $name, $default = null): ?string{
    return QMRequest::getQueryParam($name, $default);
}
/**
 * @return string
 */
function facebookLoginUrl(): string{
    return FacebookConnector::getLoginUrl();
}
/**
 * @return string
 */
function googleLoginUrl(): string{
    return GoogleLoginConnector::getLoginUrl();
}
/**
 * @return string
 */
function getAppHostNameWithoutProtocol(): string{
    return str_replace('https://', '', \App\Utils\Env::getAppUrl());
}
/**
 * @param $timeStringOrUnixEpochSeconds
 * @return string
 */
function db_date($timeStringOrUnixEpochSeconds): string {
    return TimeHelper::YmdHis($timeStringOrUnixEpochSeconds);
}
/**
 * @return string
 */
function now_at(): string {
	return date('Y-m-d H:i:s', time());
}
/**
 * @param $timeAt
 * @return string
 */
function date_or_null($timeAt = null): ?string {
    if(!$timeAt){return null;}
    if(TimeHelper::isZeroTime($timeAt)){return null;}
    return db_date($timeAt);
}
/**
 * @param $timeAt
 * @return int|null
 */
function time_or_null($timeAt): ?int {
    return TimeHelper::timeOrNull($timeAt);
}
/**
 * @param $timeStringOrUnixEpochSeconds
 * @return int
 */
function time_or_exception($timeStringOrUnixEpochSeconds): int {
    if(!$timeStringOrUnixEpochSeconds){ // This is faster than calling lei a million times
        le("No time provided");
    }
    return time_or_null($timeStringOrUnixEpochSeconds);
}
/**
 * @param $before
 * @param $after
 * @return bool
 */
function is_before($before, $after): bool {
    $beforeTime = time_or_exception($before);
    $afterTime = time_or_exception($after);
    return $beforeTime < $afterTime;
}
/**
 * @param string $string
 * @param array $allowExceptionsLike
 * @return array
 */
function db_statement(string $string, array $allowExceptionsLike = []): array  {
    return Writable::statementStatic($string, $allowExceptionsLike);
}
/**
 * @param $timeAt
 * @return string
 */
function datetime_or_null($timeAt = null): ?string {
    if(!$timeAt){return null;}
    return TimeHelper::YmdHis($timeAt);
}
/** @noinspection JSUnresolvedVariable
 * @noinspection CommaExpressionJS
 */
function qm_integration_loader_and_options(): string{
    $clientId = BaseClientIdProperty::fromRequest(false) ?? 'YOUR_CLIENT_ID';
    if(isset($_SERVER["REQUEST_URI"])){$clientId = QMStr::between($_SERVER["REQUEST_URI"], "apps/", '/integration');}
    if(!$clientId){$clientId = BaseClientIdProperty::fromRequest(false)
        ?? 'YOUR_CLIENT_ID';}
    $options = quantimodo_integration_options();
    return "
    <script defer>
    var Loader=function(){};Loader.prototype={require:function(t,e){this.loadCount=0,this.totalRequired=t.length,this.callback=e;for(var a=0;a<t.length;a++)this.writeScript(t[a])},loaded:function(t){this.loadCount++,this.loadCount==this.totalRequired&&\"function\"==typeof this.callback&&this.callback.call()},writeScript:function(t){var e=this,a=document.createElement(\"script\");a.type=\"text/javascript\",a.async=!0,a.src=t,a.addEventListener(\"load\",function(t){e.loaded(t)},!1),document.getElementsByTagName(\"head\")[0].appendChild(a)}};
    var l = new Loader(); l.require([\"/api/v1/integration.js?clientId=$clientId\"], function() { $options });
    </script>";
}
function quantimodo_integration_options(): string{
    $clientId = BaseClientIdProperty::fromRequest(false) ?? 'YOUR_CLIENT_ID';
    if(isset($_SERVER["REQUEST_URI"])){
        $clientId = QMStr::between($_SERVER["REQUEST_URI"], "apps/", '/integration');
    }
    $loggedInUser = Auth::user();
    $publicToken = $loggedInUser->quantimodoPublicToken ?? '';
    $clientUserId = ($loggedInUser) ? $loggedInUser->ID : null;
    $str = "<script>
            window.QuantiModoIntegration.options = {
                // Replace the clientUserId with javascript code to get a unique ID for your user
                clientUserId: encodeURIComponent('$clientUserId'),
                clientId: '$clientId',
                publicToken: '$publicToken',
                fullscreen: true,
                showButton: true,
                // defaultState is the default page that opens.
                // As you navigate around the app at https://web.quantimo.do/, check the URL.
                // The string after https://web.quantimo.do/#/app/  is the name of the state for a given page
                //defaultState: 'import',
                defaultState: 'reminders-inbox',
                floatingActionButtonRightOffset: '15px', // Distance from right of screen is adjustable in case you have another button like Drift
                floatingActionButtonBottomOffset: '100px',  // Distance from bottom of screen is adjustable in case you have another button like Drift
                hideMenu: false, // If you only want to display a single page, you might want to hide the navigation menu
                finish: function(sessionTokenObject) {
                    console.warn(\"window.QuantiModoIntegration.options.finish is called after user finishes connecting their health data.\");
                    console.warn(\"You should set this to POST sessionTokenObject as-is to your server for step 2\");
                    var xmlhttp = new XMLHttpRequest();   // new HttpRequest instance
                    xmlhttp.open(\"POST\", \"https://app.quantimo.do/api/v1/quantimodo/connect/finish\");
                    xmlhttp.setRequestHeader(\"Content-Type\", \"application/json\");
                    xmlhttp.send(sessionTokenObject);
                },
                close: function() {
                    /* (optional) Called when a user closes the popup without connecting any data sources */
                },
                error: function(err) {
                    console.error(err);
                    /* (optional) Called if an error occurs when loading the popup. */
                }
            };
            window.QuantiModoIntegration.createSingleFloatingActionButton();
        </script>";
    $str = str_replace("<script>", "", $str);
    $str = str_replace("</script>", "", $str);
    return $str;
}
function get_page_title(string $title = null):string{
    if(!$title){
        $title = QMRequest::getPluralTitle();
    }
    if(!$title){
        $title = config('app.name');
    }
    return $title;
}
function env_is_testing(): bool{
    return Env::isTesting();
}
if (! function_exists('ionic_url')) {
    /**
     * Generate an asset path for the application.
     * @param  string  $path
     * @return string
     */
    function ionic_url(string $path = "/"): string{
		if($path === "studies"){
			return StudiesStateButton::url();
		}
        $path = trim($path, '/');
	    //return '/'.IonicHelper::RELATIVE_URL_PATH."/$path";
        return IonicHelper::ionicOrigin()."/$path";
    }
}
if (! function_exists('public_app_public_url')) {
	/**
	 * Generate an asset path for the application.
	 * @param  string  $path
	 * @return string
	 */
	function public_app_public_url(string $path = "/"): string{
		if($path === "studies"){
			return StudiesStateButton::url();
		}
		$path = trim($path, '/');
		//return '/'.IonicHelper::RELATIVE_URL_PATH."/$path";
		return Env::getAppUrl()."/app/public/$path";
	}
}
function qm_csrf_token(): ?string {
    if(AppMode::isUnitOrStagingUnitTest()){
        return "test_csrf_token";
    }
    $token = csrf_token();
    if(!$token){
        if(!QMRequest::urlContains("/api/")){
            QMLog::error("No csrf_token!");
        }
        return "could_not_generate_csrf";
    }
    return $token;
}
if (! function_exists('qm_csrf_field')) {
    /**
     * Generate a CSRF token form field.
     *
     * @return HtmlString
     */
    function qm_csrf_field(): HtmlString{
        return new HtmlString('<input type="hidden" name="_token" value="'.qm_csrf_token().'">');
    }
}
/**
 * Used to suppress PHPStorm exception PHPDoc recommendations
 * @param Throwable|string $e
 * @noinspection PhpUnhandledExceptionInspection
 */
function le($e, $meta = null){
    if (function_exists('xdebug_break')) {xdebug_break();}
	//ConsoleLog::exception($e, $meta);
	if($e instanceof \Throwable){
		/** @var \LogicException $e */
		throw $e;
	}
	if($meta){$e .= QMLog::print_r($meta, true);}
	throw new LogicException($e);
}
/**
 * Throw the given exception if the given condition is true.
 * @param mixed $condition
 * @param callable|string $message
 * @param null $meta
 * @deprecated Too slow, use le instead
 */
function lei(mixed $condition, callable|string $message = '', $meta = null): void{
    if ($condition) {
        if(is_callable($message)){
            $message();
        }
        if($condition instanceof Throwable){
            /** @var LogicException $condition */
            throw $condition;
        }
        if(!is_string($message)){
            $meta = $message;
            $message = '';
        }
        if(is_string($condition)){$message .= "\n$condition";}
        if(empty($message)){
            $back = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            if($back[0]["function"] === "lei"){
                $file = $back[1]['file'];
                $line = $back[1]['line'];
            } else {
                $file = $back[0]['file'];
                $line = $back[0]['line'];
            }
            $code = FileHelper::getLineOfCode($file, $line);
            if($back[0]["function"] === "lei"){
                $code = QMStr::between($code, "lei(", ");");
            } else {
                $code = QMStr::between($code, "lei(", ");");
            }
            $message = $code."\n $file:$line";
        }
	    le($message, $meta);
    }
}
/**
 * Add a break point here and watch it go!
 * @param string|object|null $message
 * @param null $meta
 * @return void
 */
function debugger($message, $meta = null): void {
	$local = EnvOverride::isLocal();
	if($local && XDebug::active()){
		QMRequest::setMaximumApiRequestTimeLimit(600);
	}
	if (function_exists('xdebug_break')) {
		xdebug_break();
	}else {
		ConsoleLog::debug("xdebug_break function does not exist!");
	}
	if(is_string($message)){
		ConsoleLog::info("debugger called because:".$message, $meta);
	} elseif($message instanceof \Throwable) {
		ConsoleLog::exception($message);
	} else {
		ConsoleLog::info("debugger called because:", $meta);
	}
}
/**
 * @param $condition
 * @param $data
 */
function ddd_if($condition, $data): void{
	if($condition){
		qddd($data);
	}
}
function qddd(): void{
    $args = func_get_args();
    if (count($args) === 0) {throw new LogicException('You should pass at least 1 argument to `ddd`');}
    call_user_func_array('dump', $args);
    if(app()->runningInConsole()){
		ConsoleLog::error("qddd print_r:");
        QMLog::print_r($args);
        die();
    }
    $handler = app(ErrorPageHandler::class);
    /** @noinspection PhpUnhandledExceptionInspection */
    $client = app()->make('flare.client');
    $report = $client->createReportFromMessage('Dump, Die, Debug', 'info');
    $handler->handleReport($report, 'DebugTab', [
        'dump' => true,
        'glow' => false,
        'log' => false,
        'query' => false,
    ]);
    die();
}
function app_display_name():string{
	if(getenv('APP_NAME')){
		return getenv('APP_NAME');
	}
    $name = QMRoute::getAppDisplayName();
    if(empty($name)){le("no getAppDisplayName");}
    return $name;
}
/**
 * @param null $obj
 * @return string
 */
function html_social_meta($obj = null):string{
    /** @var AnalyticalReport $obj */
    if($obj && method_exists($obj, 'getSocialMetaHtml')){
        return $obj->getSocialMetaHtml();
    }
    return MetaHtml::generateSocialMetaHtml($obj);
}
/**
 * @param null $obj
 * @return string
 */
function html_meta_author($obj = null):string{
    /** @var AnalyticalReport $obj */
    if($obj && method_exists($obj, 'getAuthor')){
        return $obj->getAuthor();
    }
    return app_display_name();
}
/**
 * @param null $obj
 * @return string
 */
function html_meta_description($obj = null):string{
    /** @var AnalyticalReport $obj */
    if($obj && method_exists($obj, 'getSubtitleAttribute')){return $obj->getSubtitleAttribute();}
    if($meta = MetaTag::get('description')){return $meta;}
    if($mem = HostAppSettings::fromMemory()){return $mem->appDescription;}
    return BaseAppDescriptionProperty::DEFAULT_DESCRIPTION;
}
/**
 * @param null $obj
 * @return string
 */
function html_meta_image($obj = null):string{
    /** @var AnalyticalReport $obj */
    if($obj && method_exists($obj, 'getImage')){
        return $obj->getImage();
    }
    if($meta = MetaTag::get('image')){
        return $meta;
    }
    if($mem = HostAppSettings::fromMemory()){return $mem->getImage();}
    return InternalImageUrls::DEFAULT_ICON;
}
/**
 * @param null $obj
 * @return string
 */
function html_meta_keywords($obj = null):string{
    /** @var AnalyticalReport $obj */
    if($obj && method_exists($obj, 'getKeyWordString')){
        return $obj->getKeyWordString();
    }
	if($obj && method_exists($obj, 'getKeyWords')){
		return QMStr::generateKeyWordString($obj->getKeyWords());
	}
    if($meta = MetaTag::get('keywords')){
        return $meta;
    }
    if(!$obj){
        QMLog::debug("Please provide object with getKeyWordString HasView trait");
    } else {
	    QMLog::error("Please implement getKeyWordString for ".get_class($obj));
    }
    return app_display_name();
}
/**
 * @param null $obj
 * @return string
 */
function html_title($obj = null):string{
    if(is_array($obj) && isset($obj['title'])){
        return $obj['title'];
    }
    /** @var AnalyticalReport $obj */
    if($obj && method_exists($obj, 'getTitleAttribute')){
        return $obj->getTitleAttribute();
    }
    if($meta = MetaTag::get('title')){
        return $meta;
    }
    if($r = QMRoute::getCurrent()){
        if($t = $r->getTitleAttribute()){
            return $t;
        }
    }
    return $meta;
}
function home_page():string{
	return Env::getAppUrl();
//    if(AppMode::isCrowdsourcingCures()){
//        return UrlHelper::CROWDSOURCING_CURES_HOMEPAGE;
//    }
//    return HostAppSettings::instance()->getHomepageUrl();
}
function app_wide_logo():string{
    return InternalImageUrls::LOGOS_QUANTIMODO_LOGOS_RAINBOW_1200_630;
}
function app_icon():string{
    return HostAppSettings::iconUrl();
}
function default_sharing_image():string{
	return InternalImageUrls::BETTER_WORLD_THROUGH_DATA_PEOPLE_4096_2304;
}
function route_name():?string{
    $route = Request::route();
    if(!$route){return null;}
    return $route->getName();
}
function route_title():?string{
    $route = Request::route();
    if(!$route){return null;}
    return QMRoute::getCurrent()->getTitleAttribute();
}
/**
 * Generate an asset path for the application.
 * @param  string  $path
 * @param  bool|null  $secure
 * @return string
 */
function qm_asset(string $path, bool $secure = null): string {
	if(str_starts_with('vendors', $path)){
		return 'https://static.quantimo.do/'.$path;
	}
	//return asset($path, $secure);
	if(Env::isLocal() && !AppMode::isUnitOrStagingUnitTest()){
		return qm_local_asset($path);
	}
    //if(\App\Utils\EnvOverride::isLocal()){return qm_local_asset($path);}
	return qm_static_asset($path);
}
/**
 * Generate an asset path for the application.
 * @param string $path
 * @return string
 */
function qm_static_asset(string $path): string {
    return UrlHelper::STATIC_URL.'/'.trim($path, '/');
}
/**
 * Generate an asset path for the application.
 * @param  string  $path
 * @return string
 */
function qm_api_asset(string $path): string {
	$path = trim($path, '/');
    return QMRequest::origin().'/'.$path;
}
function qm_local_asset(string $path): string {
	return QMRequest::origin().'/'.trim($path, '/');
    //return \App\Utils\Env::getAppUrl().'/'.trim($path, '/');
}
function relative_path(string $abs): string{
	return FileHelper::getRelativePath($abs);
}
function abs_path(string $path = null): string{
	return FileHelper::absPath($path);
}
function tests_path(string $path = null): string{
	return abs_path("tests".($path ? DIRECTORY_SEPARATOR.$path : $path));
}
function configs_path(string $path = null): string{
	return abs_path("configs".($path ? DIRECTORY_SEPARATOR.$path : $path));
}
function repos_path(string $path = null): string{
	return abs_path("repos".($path ? DIRECTORY_SEPARATOR.$path : $path));
}
function scripts_path(string $path = null): string{
	return abs_path("scripts".($path ? DIRECTORY_SEPARATOR.$path : $path));
}
/**
 * @param $command
 * @param array $params
 */
function artisan($command, array $params): void{
	try {
		Artisan::call($command, $params);
	} catch (Exception $e) {
		ExceptionHandler::dumpOrNotify($e);
	}
	QMLog::info(Artisan::output());
}
//require abs_path("tests/Tddd/Support/helpers.php");
/**
 * Get an instance of the current request or an input item from the request.
 * @param  array|string|null  $key
 * @param mixed|null $default
 * @return QMRequest|string|array
 */
function qm_request(array|string $key = null, mixed $default = null): mixed {
	$r = Memory::get(Memory::QM_REQUEST);
	if(!$r){
		$r = QMRequest::capture();
	}
	if (is_null($key)) {
		return $r;
	}

	if (is_array($key)) {
		return $r->only($key);
	}

	$value = $r->__get($key);

	return is_null($value) ? value($default) : $value;
}

function current_url() {
	return UrlHelper::current();
}

function login_url(string $finalCallback = null): string{
	$url = LoginButton::url([FinalCallbackUrl::FINAL_CALLBACK_URL => $finalCallback ?? current_url(),]);
	return $url;
}
function register_url(string $finalCallback = null): string{
	$url = RegistrationButton::url([FinalCallbackUrl::FINAL_CALLBACK_URL => $finalCallback ??  current_url(),]);
	return $url;
}
