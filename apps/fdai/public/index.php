<?php //phpinfo();
//xdebug_info();
if(!defined('PROJECT_ROOT')){
	$str = dirname(__DIR__, 1);
	define('PROJECT_ROOT', $str);
}
if(!defined('IONIC_PUBLIC_ROOT_PATH')){
	define('IONIC_PUBLIC_FOLDER_PATH', __DIR__.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public');
}
if(!defined('IONIC_PUBLIC_WEB_PATH')){
	define('IONIC_PUBLIC_WEB_PATH', '/app/public');
}
define('SUBDOMAIN', explode('.', $_SERVER['HTTP_HOST'] ?? "")[0] ?? "");
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'){$_SERVER['HTTPS'] = 'on';}  // Pass https through load balancer
if($_SERVER["SERVER_NAME"] === 'testing.quantimo.do'){$_ENV['APP_ENV'] = 'testing';}
define('REQUEST_URI',$_SERVER["REQUEST_URI"]);
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // return only the headers and not the content
    // only allow CORS if we're doing a GET - i.e. no saving for now.
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        if($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'GET' || $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'POST') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: *');
        }
    }
    exit;
}

function preserve_query(): string{return (empty($_SERVER['QUERY_STRING']) &&
	!str_contains($_SERVER['REQUEST_URI'], "?")) ? "" : "?" .$_SERVER['QUERY_STRING'];}
function redirect_with_query(string $to, bool $permanent): void{
	if(!str_starts_with($to, 'http') && $_SERVER['HTTP_HOST'] !== 'localhost'){
		$to = "https://".$_SERVER['HTTP_HOST'].$to;
	}
	error_log("redirect_with_query: $to".print_r(debug_backtrace(0, 5), true));
    header( "Location: ".$to.preserve_query(), true, $permanent ? 301 : 302 );
}
if(SUBDOMAIN === "studies" && REQUEST_URI === "/"){
	redirect_with_query('/variables', false);
	return;
}
function serve_public_html(): bool{
	$uriToFilePath = REQUEST_URI;
	if(str_starts_with($uriToFilePath, '/static')){
		return false;
	}
	$hasFileExtension = false;
	if(str_contains($uriToFilePath, '/api/v')){return false;} // For https://local.quantimo.do/api/v1/connect.js
	if(DIRECTORY_SEPARATOR == '\\'){$uriToFilePath = str_replace('/', DIRECTORY_SEPARATOR, $uriToFilePath);}
	$publicPath = __DIR__. $uriToFilePath;
	$staticFiles = [
		'.css',
		'.js',
		'.png',
		'.jpg',
		'.jpeg',
		'.gif',
		'.svg',
		'.ico',
		'.woff',
		'.woff2',
		'.ttf',
		'.map',
		'.html',
		'.json',
	];
	// Extract the path from the URL, excluding the query string
	$urlComponents = parse_url($publicPath);
	$path = $urlComponents['path'] ?? '';
	foreach($staticFiles as $ext) {
		if(str_ends_with($path, $ext)){
			$hasFileExtension = true;
			if(file_exists($publicPath)){
				serve_static_file($publicPath);
				return true;
			}
			$ionicPath = IONIC_PUBLIC_FOLDER_PATH.$uriToFilePath;
			if(file_exists($ionicPath)){
				serve_static_file($ionicPath);
				return true;
			}
			$assetPath = str_replace(IONIC_PUBLIC_WEB_PATH.'/', '/', REQUEST_URI);
			redirect_with_query("https://static.quantimo.do".$assetPath, false);
			return true;
		}
	}
	/** @noinspection PhpConditionAlreadyCheckedInspection */
	if(!$hasFileExtension){
		$uriToFilePath.=".html";
		if(file_exists($uriToFilePath)){
			serve_static_file($uriToFilePath);
			return true;
		}
	}
	if(str_starts_with(REQUEST_URI, '/app/') || REQUEST_URI === '/app'){
		serve_static_file(IONIC_PUBLIC_FOLDER_PATH.DIRECTORY_SEPARATOR.'index.html');
		return true;
	}
    return false;
}
/**
 * @param string $path
 * @return string|null
 */
function serve_static_file(string $path): ?string {
	$html = file_get_contents($path);
	if(!$html){return null;}
	//$html = relativize_paths($html);
	$mimeContentType = mime_content_type($path);
	header('Content-Type: '.$mimeContentType);
	echo $html;
	return $html;
}
if(serve_public_html()){return;}
function redirect_to_file(): bool{
    $uri = REQUEST_URI;
    if(empty($_GET)) {
        $parsed = parse_url($uri);
        parse_str($parsed["query"] ?? "", $_GET);
    }
    $file = $_GET['file'] ?? $_GET['path'] ?? null;

    if($file && stripos($uri, "dev/phpstorm") !== false){
        //$file = '\\\\wsl$\Ubuntu-22.04\www\wwwroot\qm-api\\'.str_replace('/', '\\', $file);
        //$url = "phpstorm://open?file=".$file."&line=".$_GET['line']??"0";
	    $url = "jetbrains://php-storm/navigate/reference?project=cd-api&path=".$file."&line=".$_GET['line']??"0";
        header( "Location: $url" );
        return true;
    }
    return false;
}
function redirect_host_to_path(): bool{
    $hostPath = [
        'datalab.quantimo.do' =>  "https:://app.quantimo.do/datalab",
	    'n8n.quantimo.do' =>  "https:://n8n.quantimo.do:5680",
	    'jenkins.quantimo.do' =>  "https:://jenkins.quantimo.do:8082",
    ];
    $host = $_SERVER["HTTP_HOST"] ?? "";
    foreach($hostPath as $fromHost => $toUrl){
        if($host === $fromHost){
            //redirect_with_query($toUrl, true);
	        redirect_with_query($toUrl, false);
            return true;
        }
    }
    return false;
}

function serve_static(): bool{
    $path = __DIR__."/../static".REQUEST_URI."/index.html";
    if(file_exists($path)){
        $html = file_get_contents($path);
        $html = relativize_paths($html);
        echo $html;
        return true;
    }
    return false;
}
/**
 * @param string $html
 * @return string
 */
function relativize_paths(string $html): string {
    if(str_contains($_SERVER["HTTP_HOST"], "crowdsourcingcures.org")){
        foreach([
            "web.quantimo.do" => "app.crowdsourcingcures.org",
            "quantimo.do" => "crowdsourcingcures.org",
            "QuantiModo" => "Crowdsourcing Cures",
            "https://static.quantimo.do/img/icons/quantimodo/icon_512.png" =>
                "https://static.quantimo.do/app_uploads/crowdsourcing-cures/app_images_appIcon.png"
        ] as $old => $new){
            $html = str_replace($old, $new, $html);
        }
    }
    foreach(['app', 'staging', 'local'] as $sub){
        $html = str_replace("https://$sub.quantimo.do/", "/", $html);
    }
    return $html;
}
function is_api_request(): bool{
    return str_starts_with(REQUEST_URI, '/api/');
}
function is_laravel_api_request(): bool{
    $laravelApiPrefixes = [
		'/api/v2/', 
	    '/api/v7/', 
	    '/api/v6/',
	    '/api/v1/connectors',
	    '/api/oauth2/',
	    '/oauth/'
    ];
    foreach($laravelApiPrefixes as $prefix){
        if(stripos(REQUEST_URI, $prefix) === 0){
            return true;
        }
    }
    return false;
}
# Logic Below
if(redirect_to_file()){return;}
// REDIRECT EXACT MATCH
function redirect_exact_match(){
	foreach([
		        //"fromPath" => "toPath
		        "/apps" => IONIC_PUBLIC_WEB_PATH."/index.html#/app/configuration",
		        "/import" => IONIC_PUBLIC_WEB_PATH."/index.html#/app/import",
		        "/builder" => IONIC_PUBLIC_WEB_PATH."/index.html#/app/configuration",
		        '/dashboard' =>  "/datalab",
		        '/login' =>  "/auth/login",
		        '/logout' =>  "/auth/logout",
		        '/clockwork' =>  "/__clockwork/app#",
		        '/register' =>  "/auth/register",
		        '/app' =>  IONIC_PUBLIC_WEB_PATH,
		        '/' =>  IONIC_PUBLIC_WEB_PATH,
	        ] as $from => $to){
		if($from === REQUEST_URI){
			redirect_with_query($to, false);
			return true;
		}
	}
	return false;
}
if(redirect_exact_match()){return;}
function redirect_path(){
	foreach([
		        '/api/v2/auth' =>  "/auth",
		        '/api/v2/account' =>  "/account",
		        '/api/v2/password' =>  "/password",
		        '/api/oauth2/authorize' =>  "/oauth/authorize",
	        ] as $from => $to){
		if(str_starts_with(REQUEST_URI, $from)){
			$to = str_replace($from, $to, REQUEST_URI);
			redirect_with_query($to, false);
			return true;
		}
	}
	return false;
}
if(redirect_path()){return;}
// Redirect requests to specific subdomains to a different url

$subToUrl = [
    "developer" => "https://builder.quantimo.do",
    //"testing" => "https://local.quantimo.do" // This didn't solve session problems
    "tools" => "https://studies.crowdsourcingcures.org",
];


foreach($subToUrl as $subdomain => $subdomainRedirectUrl){
    if(SUBDOMAIN === $subdomain){redirect_with_query($subdomainRedirectUrl, true);return true;}
}
// Redirect deprecated paths to the new ones
$redirectLike = [
    //"fromPath" => "toPath
    '/developer-registration/' =>  "/account/apps/create",
	'/clockwork/app' =>  "/__clockwork/",
];
if(str_contains(REQUEST_URI, '/clockwork/app')){
	header( "Location: ".str_replace("/clockwork/", '/__clockwork/', REQUEST_URI));return;
}

foreach($redirectLike as $from => $to){
    if(stripos(REQUEST_URI, $from) !== false){redirect_with_query($to, false);return;}
}
if(redirect_host_to_path()){return;}
//if(serve_static()){return;}
$GLOBALS['MIKE_IP']="23.84.182.244";
function remote_address_is_mike(): bool{
	return $_SERVER["REMOTE_ADDR"] === $GLOBALS['MIKE_IP'];
}
function is_mike(): bool{
	if(remote_address_is_mike()){return true;}
	if(SUBDOMAIN === "local"){return true;}
	$addr = $_SERVER["SERVER_ADDR"] ?? null;
	if($addr === "127.0.0.1" && $_SERVER["REMOTE_ADDR"] === "127.0.0.1"){return true;}
	return false;
}
if(is_mike()){
	if(str_contains(REQUEST_URI, '/records')){
		require_once PROJECT_ROOT."/public/api.php";return; // TODO: add authentication to api.php
	}
	$repos = PROJECT_ROOT.'/repos';
	putenv('XHGUI_PATH_PREFIX=xhgui');
	$fileToPath = [
		'pimp/index.php' => '/pimp',
		$repos.'/mikepsinn/xhgui/webroot/index.php' => '/xhgui',
		$repos.'/preinheimer/xhprof/xhprof_html/index.php' => '/xhprof',
		$repos.'/mikepsinn/liveprof-ui/src/www/index.php' => '/profiler',
		//$repos.'/mikepsinn/tddd-starter/public/index.php' => '/tddd',
		$repos.'/powershell-php-wrapper/index.php' => '/powershell',
	];
	foreach($fileToPath as $file => $path){
		if(str_starts_with(REQUEST_URI, $path)){
			require_once $file;
			return;
		}
	}
}
function serve_json($type){
	header('Content-Type: application/json');
	echo file_get_contents(PROJECT_ROOT."/data/$type.json");
	return true;
}
//if(str_ends_with(REQUEST_URI, '/units')){serve_json('units');return;}
//if(str_ends_with(REQUEST_URI, '/variableCategories')){serve_json('variable_categories');return;}
function setCacheControl($seconds_to_cache){
    $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
    header("Expires: $ts");
    header("Pragma: cache");
    header("Cache-Control: max-age=$seconds_to_cache");
}
//if(REQUEST_URI === "/api/v6/units"){
//    setCacheControl(3600);
//    echo file_get_contents(PROJECT_ROOT."/data/units.json");
//    return;
//}
//if(REQUEST_URI === "/api/v6/variableCategories"){
//    setCacheControl(3600);
//    echo file_get_contents(PROJECT_ROOT."/data/variableCategories.json");
//    return;
//}
if($_GET["debug"] ?? false){
	if($_GET["debug"] === "rIOvYwWFctZK2UieU7Vb3sdolNy6xmO48rb4yabZmHlG0PqlF5q"){
		setAppDebug();
	}
}
ini_set('memory_limit', '512M');
$gotten = ini_get('memory_limit');
if($gotten !== '512M'){
	$errMsg = "ini_set('memory_limit', '512M') not working. ini_get('memory_limit') is $gotten";
	error_log($errMsg);
	echo $errMsg;
	exit;
}
$isApiRequest = is_api_request();
$isLaravelApiRequest = is_laravel_api_request();
if($isApiRequest && !$isLaravelApiRequest){
	require_once PROJECT_ROOT.'/public/index-slim.php';
    return;
}
// Causes infinite loop on Cloud Run
//if($_SERVER["SERVER_PORT"] === "80" && str_contains($_SERVER["HTTP_HOST"], ".quantimo.do")){
//	redirect_with_query("https://".$_SERVER["HTTP_HOST"].REQUEST_URI, false);return;
//}
/**
 * @return void
 */
function setAppDebug(): void{
	putenv("APP_DEBUG=true");  // Show ignition page when going to test URLS in the browser
	$_ENV["APP_DEBUG"] = true; // We don't want to use APP_DEBUG in regular tests though, so we simulate production usage
}
if($_SERVER["HTTP_HOST"] === "testing.quantimo.do"){
	set_time_limit($s = 10*60);  // This doesn't seem to work, so you have to set it in
	ini_set('max_execution_time', $s); // http://127.0.0.1:7777/ https://prnt.sc/1xfceue
	setAppDebug();
}
require_once 'index-laravel.php';
