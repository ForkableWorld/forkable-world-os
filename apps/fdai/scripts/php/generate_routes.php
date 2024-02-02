<?php
use App\Slim\Configuration\RouteConfiguration;

require_once __DIR__ . '/bootstrap_script.php';
$routes = \App\Slim\Configuration\RouteConfiguration::getRoutes();
foreach($routes as $route){
	$lower = $route[RouteConfiguration::FIELD_METHOD];
	$lower = strtolower($lower);
	$path = $route[RouteConfiguration::FIELD_PATH];
	if(str_starts_with($path, '/v4')){
		//continue;
	}
	$path = str_replace('/v4/', '', $path);
	$class = $route[RouteConfiguration::FIELD_CONTROLLER];
	$class = '\App\Slim\Controller\\'.$class;
	echo "Route::$lower('$path', $class::class . '@$lower');    
";
}
