<?php
/** @noinspection PhpUnhandledExceptionInspection */
if(!defined('PROJECT_ROOT')){define('PROJECT_ROOT', dirname(__DIR__, 2));}
use App\Files\FileFinder;
use App\Repos\QMAPIRepo;
use App\Types\QMStr;
use App\Utils\Env;
use Tests\QMBaseTestCase;
require_once PROJECT_ROOT.'/scripts/php/bootstrap_script.php';
Env::set(Env::APP_ENV, 'testing');
$branch = QMAPIRepo::getBranchFromMemoryOrGit();
$baseFolder = 'tests/UnitTests';
$folders = FileFinder::listFolders($baseFolder);
foreach($folders as $subFolder){
	$title = QMStr::after($baseFolder, $subFolder);
	error_log("::group::$title");
	$response = QMBaseTestCase::runTestsInFolderByCommandLine($subFolder, "--debug --stop-on-failure");
	if(!$response->successful()){
		le("$title failed: ".$response->output());
	}
	error_log("::endgroup::");
}
