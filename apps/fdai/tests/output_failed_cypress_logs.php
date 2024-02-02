<?php
use App\Slim\QMSlim;
if(!defined('PROJECT_ROOT')){
    define('PROJECT_ROOT', dirname(__DIR__, 1));
}
require_once PROJECT_ROOT.'/vendor/autoload.php';
QMSlim::bootstrapLaravelConsoleAppIfNecessary();
\App\DevOps\Jenkins\Jenkins::outputFailedCypressLogs();
