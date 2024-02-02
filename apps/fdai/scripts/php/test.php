<?php
if(!defined('PROJECT_ROOT')){define('PROJECT_ROOT', dirname(__DIR__, 2));}
use App\Repos\QMAPIRepo;
use App\Utils\Env;
use Tests\QMBaseTestCase;
require_once PROJECT_ROOT.'/scripts/php/bootstrap_script.php';
putenv("TEST_FOLDER=tests/UnitTests");
putenv("BRANCH=".QMAPIRepo::getBranchFromMemoryOrGit());
$folderToTest = Env::get(Env::TEST_FOLDER) ?? $_SERVER['argv'][1];
$branch = QMAPIRepo::getBranchFromEnv() ?? $_SERVER['argv'][2];
QMBaseTestCase::checkoutAndTestFolder($folderToTest, $branch);
