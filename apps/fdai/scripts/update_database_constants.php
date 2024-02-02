<?php
use App\Slim\QMSlim;
use App\Storage\DB\Writable;
use App\Files\FileHelper;
use App\Logging\QMLog;
if (!defined('PROJECT_ROOT')) {define('PROJECT_ROOT', dirname(__DIR__, 1));}
require_once PROJECT_ROOT.'/vendor/autoload.php';
QMLog::logStartOfProcess(basename(__FILE__));
QMSlim::bootstrapLaravelConsoleAppIfNecessary();
Writable::updateDBConstants();
//QMDB::enableUnusedIndexLogging(); // Need to be root to do this
//if(Env::isStaging()){StagingMaster::updateCallbackUrls();}
//QMCommonVariable::renameVariables();
\App\Logging\ConsoleLog::info("update_database_constants succeeded so deleting update_database_constants_success");
FileHelper::deleteFile(FileHelper::absPath("update_database_constants_success"), __METHOD__);
QMLog::logEndOfProcess(basename(__FILE__));
