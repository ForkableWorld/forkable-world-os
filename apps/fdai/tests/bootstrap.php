<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpMissingDocCommentInspection */
/** @noinspection SpellCheckingInspection */
//putenv("APP_ENV=testing");
if(stripos(json_encode($GLOBALS["argv"]), 'killphp') !== false){shell_exec('pkill -f phpunit');} // Faster than loading \App\PhpUnitJobs\Code\CMD::testKillPHPUnit
if(!defined('PROJECT_ROOT')){define('PROJECT_ROOT', dirname(__DIR__));}
// This is a script to set up the environment for PHPUnit tests
use App\Computers\ThisComputer;
use App\DevOps\GithubHelper;
use App\Exceptions\ExceptionHandler;
use App\Logging\QMLog;
use App\Logging\QMLogLevel;
use App\Repos\QMAPIRepo;
use App\Storage\DB\Writable;
use App\Utils\Env;
use App\Utils\EnvOverride;
use App\Utils\QMProfile;
use Tests\QMBaseTestCase;
copy(PROJECT_ROOT.'/tests/fixtures/qm_test.sqlite', PROJECT_ROOT.'/storage/qm_test.sqlite');
require_once PROJECT_ROOT.'/scripts/php/bootstrap_script.php';
if(function_exists('xdebug_info') && QMLogLevel::isDebug()){xdebug_info();}
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set("date.timezone", "UTC");
function exception_handler($exception){
	try {
		ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($exception);
	} catch (\Throwable $loggerException){
	    error_log("Could not log exception because: ".$loggerException->__toString());
	}
	try {
		QMAPIRepo::createFailedStatus($exception, QMBaseTestCase::getSuiteName());
	} catch (\Throwable $loggerException) {
		error_log("Could not createFailedStatus because: ".$loggerException->__toString());
	}
}
set_exception_handler('exception_handler'); // Needed because Windows doesn't output exceptions otherwise
QMBaseTestCase::setSuiteStartTime(microtime(true));
$suiteName = QMBaseTestCase::getSuiteName();
if($suiteName !== 'bootstrap.php' && !EnvOverride::isLocal()){
	QMAPIRepo::logErrorIfAlreadyTesting();
	if(QMAPIRepo::suiteStatusIsSuccess()){
		QMLog::notice("Suite $suiteName is already successful. Skipping tests.");
		exit(0);
	}
	if(QMAPIRepo::suiteStatusIsPending()){
		QMLog::notice("Suite $suiteName is already running. Skipping tests...");
		exit(0);
	}
	if(QMAPIRepo::suiteStatusIsFailure()){
		QMLog::notice("Suite $suiteName already failed so retrying here...");
	}
	if(QMAPIRepo::suiteStatusIsError()){
		QMLog::notice("Suite $suiteName errored so retrying here...");
	}
	QMAPIRepo::setSuitePending();
	function phpunit_shutdown(){ 
		try {
			QMAPIRepo::setFinalStatus();
		} catch (\Throwable $loggerException) {
			error_log("Could not setFinalStatus because: ".$loggerException->__toString());
		}
	}
	register_shutdown_function('phpunit_shutdown'); // Needed because Windows doesn't output exceptions otherwise
}
QMProfile::deleteProfiles();
$env = Env::get('APP_ENV');
QMLog::logStartOfProcess(__FILE__);
Writable::assertDBTimeMatchesPHPTime();
//QMSlim::bootstrapApp();
if(!EnvOverride::isLocal()){
	Env::outputEnv();
	ThisComputer::logDebugUrlsForCurrentComputer();
}
// URGENT: Uncomment this and only test required services QMServices::assertHealthy();
// This is slow and can be done in composer post-install GitRepo::updateImportantSubmodules();
if(!EnvOverride::isLocal() && Env::get('ABORT_IF_OTHER_TESTS_FAILED')){QMAPIRepo::exceptionIfHasFailedStatus();}
// Don't do this!!! TestDB::importAndMigrateTestDB();
if(!EnvOverride::isLocal() && GithubHelper::enabled()){QMAPIRepo::sleepIfNecessary();}
QMLog::logEndOfProcess(__FILE__);
//QMRedis::validate();
