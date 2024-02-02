<?php
if(!defined('PROJECT_ROOT')){define('PROJECT_ROOT', dirname(__DIR__, 2));}
require __DIR__.'/../bootstrap/autoload.php';

use App\Storage\Memory;
use App\Logging\QMLog;
use App\Slim\QMSlim;
use App\Utils\Env;
use Illuminate\Http\Response;

if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'){
	$_SERVER['HTTPS'] = 'on';
}  // Pass https through load balancer
//require_once '../vendor/autoload.php';
//session_write_close();           // Close session WordPress opened
Env::loadEnvIfNoAppUrl();
Env::getAppUrl();
$slim = new QMSlim(false);
Memory::set(Memory::QM_REQUEST, request());
QMLog::phpErrorSettings();                  // Don't start before new App\Slim\Application()
register_shutdown_function(function() use ($slim){  // Wait until after profiling
    [$status, $headers, $body] = $slim->response->finalize();
	$response = new Response($body, $status, $headers->all());
	$slim->getKernel()->terminate($slim->getLaravelRequest(), $response);
});
$slim->run();
