<?php
use App\Logging\QMLog;
use App\Slim\QMSlim;
use App\Utils\Env;
if (!defined('PROJECT_ROOT')) {define('PROJECT_ROOT', dirname(__DIR__, 1));}
require_once PROJECT_ROOT.'/vendor/autoload.php';
Env::setTesting();
QMLog::logStartOfProcess(basename(__FILE__));
QMSlim::bootstrapLaravelConsoleAppIfNecessary();
\Tests\UpdateHtmlTestFixturesTest::updateHtmlFixturesContaining(null);
QMLog::logEndOfProcess(basename(__FILE__));

