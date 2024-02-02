<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

use App\Computers\ThisComputer;
use App\Logging\QMLog;
use App\Storage\CacheManager;
use App\Slim\QMSlim;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\SecretHelper;
if(!defined('PROJECT_ROOT')){define('PROJECT_ROOT', dirname(__DIR__, 2));}
require_once __DIR__.'/../../vendor/autoload.php';
if(ThisComputer::user() === "root" && !AppMode::isDocker() && !AppMode::isGithubRunner()){
	$env = Env::printObfuscated();
	$srv = SecretHelper::obfuscateArray($_SERVER);
	$srv = QMLog::print($srv, "Obfuscated \$_SERVER", true);
    throw new \LogicException("User should not be root!
    $srv
    ENV: $env");
}
Env::setAppEnvIfEmpty();
QMLog::logStartOfProcess(basename($_SERVER["PHP_SELF"]));
CacheManager::clearConfigIfCached();
QMSlim::bootstrapLaravelConsoleApp();
ThisComputer::setWorkerMemoryLimit();
function shutdown(){
	try {
		QMSlim::bootstrapLaravelConsoleApp();
		// This is our shutdown function, in  here we can do any last operations before the script is complete.
		QMLog::logEndOfProcess(basename($_SERVER["PHP_SELF"]));
	} catch (\Throwable $e) {
	   error_log("Error in shutdown function: ".$e);
	}
}
register_shutdown_function('shutdown');
