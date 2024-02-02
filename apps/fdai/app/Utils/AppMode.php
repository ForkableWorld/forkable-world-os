<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
use App;
use App\Computers\ThisComputer;
use App\DevOps\Jenkins\Jenkins;
use App\DevOps\Jenkins\JenkinsJob;
use App\Files\FileHelper;
use App\Files\PHP\BasePhpUnitTestFile;
use App\Folders\DynamicFolder;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Model\User\QMUser;
use App\Slim\QMSlim;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Types\QMStr;
use Tests\QMBaseTestCase;
class AppMode {
	const IS_WORKER = 'IS_WORKER';
	public static $currentTest;
	private static $appModeIsProduction;
	private static $appMode;
	/**
	 * @var false
	 */
	private static bool $isDocker;
	/**
	 * @return string
	 */
	public static function getCurrentTestShortClass(): string{
		return QMStr::toShortClassName(AppMode::getCurrentTestClass());
	}
	public static function getPHPStormUrlStatic(): string{
		$test = AppMode::getCurrentTest();
		return App\Buttons\Admin\PHPStormButton::urlToFunction(get_class($test), $test->getName());
	}
	/**
	 * @return string|null
	 */
	public static function getCurrentTestName(): ?string{
		if(!AppMode::getCurrentTest()){
			return null;
		}
		return AppMode::getCurrentTest()->getName();
	}
	/**
	 * @return QMBaseTestCase|null
	 */
	public static function getCurrentTest(): ?\PHPUnit\Framework\TestCase{
		return AppMode::$currentTest;
	}
	/**
	 * @return string
	 */
	public static function getCurrentTestClass(): ?string{
		$t = \App\Utils\AppMode::getCurrentTest();
		if(!$t){
			return null;
		}
		return get_class($t);
	}
	/**
	 * @param string $mode
	 * @return bool
	 */
	public static function appModeIs(string $mode): bool{
		return $mode === self::getAppMode();
	}
	/**
	 * @return bool
	 */
	public static function isProduction(): bool{
		if(self::$appModeIsProduction !== null){
			return self::$appModeIsProduction;
		}
		return self::$appModeIsProduction = self::appModeIs(Env::ENV_PRODUCTION);
	}
	/**
	 * @return bool
	 */
	public static function isProductionApiRequest(): bool{
		return self::appModeIs(Env::ENV_PRODUCTION) && self::isApiRequest();
	}
	/**
	 * @return bool
	 */
	public static function isAnyKindOfUnitTest(): bool{
		if(!ThisComputer::PWD()){
			return false;
		}
		if(self::isLaravelTest()){
			return true;
		}
		if(self::isStagingUnitTesting()){
			return true;
		}
		if(self::isSlimUnitTest()){
			return true;
		}
		if(Env::isTesting()){
			return true;
		}
		if(self::unitTestPathOrArgs()){
			return true;
		}
		if(self::isProduction()){
			return false;
		}
		if(Env::isStaging()){
			return false;
		}
		if(self::isStagingOrProductionApiRequest()){
			return false;
		}
		return \App\Utils\AppMode::getCurrentTest() !== null;
	}
	/**
	 * @return bool
	 */
	public static function isTestingOrStaging(): bool{
		if(self::isStagingUnitTesting()){
			return true;
		}
		//if(\App\Utils\Env::getAppUrl() === "https://utopia.quantimo.do"){return true;}
		if(\App\Utils\Env::getAppUrl() === "https://staging.quantimo.do"){
			return true;
		}
		return self::appModeIs(Env::ENV_TESTING);
	}
	/**
	 * @return bool
	 */
	public static function isStagingUnitTesting(): bool{
		if($class = AppMode::getCurrentTestClass()){
			return str_contains($class, "Tests\\StagingUnitTests\\");
		}
		$stagingUnit = self::workingDirIsStagingUnit();
		if(!$stagingUnit){
			$stagingUnit = Env::get('APP_ENV') == Env::ENV_STAGING_REMOTE;
		}
		return $stagingUnit;
	}
	/**
	 * @return bool
	 */
	public static function unitTestPathOrArgs(): bool{
		if($class = \App\Utils\AppMode::getCurrentTestClass()){
			return strpos($class, "Tests\\UnitTests\\") !== false;
		}
		$res = false;
		if(self::workingDirectoryOrArgumentStartsWith('tests/UnitTests')){ // Don't use slash
			$res = true;
		}
		if(self::workingDirectoryOrArgumentStartsWith('tests/LaravelTests/Unit')){ // Don't use slash
			$res = true;
		}
		return $res;
	}
	/**
	 * @return bool
	 */
	public static function isSlimUnitTest(): bool{
		if($class = \App\Utils\AppMode::getCurrentTestClass()){
			return str_contains($class, "Tests\\SlimTests\\");
		}
		return self::workingDirectoryOrArgumentStartsWith('tests/SlimTests');
	}
	/**
	 * @return bool
	 */
	public static function isLaravelTest(): bool{
		if($class = \App\Utils\AppMode::getCurrentTestClass()){
			return str_contains($class, "Tests\\LaravelTests\\");
		}
		return self::workingDirectoryOrArgumentStartsWith('tests/LaravelTests');
	}
	/**
	 * @param string $relativePath
	 * @return bool
	 */
	public static function workingDirectoryOrArgumentStartsWith(string $relativePath): bool{
		$absolute = FileHelper::absPath($relativePath);
		$args = ThisComputer::getCommandLineArguments();
		foreach($args as $a){
			if(str_starts_with($a, $relativePath)){
				return $a;
			}
			if(str_starts_with($a, $absolute)){
				return $a;
			}
		}
		$pwd = ThisComputer::PWD();
		if($pwd && str_starts_with($pwd, $relativePath)){
			return $pwd;
		}
		if($pwd && str_starts_with($pwd, $absolute)){
			return $pwd;
		}
		return false;
	}
	/**
	 * @return bool
	 */
	public static function workingDirectoryOrArgumentStartsWithJobsOrTasksFolder(): bool{
		$production = false;
		if(self::workingDirectoryOrArgumentStartsWith('Tasks/phpunit')){
			$production = true;
		}
		if(self::workingDirectoryOrArgumentStartsWith(DynamicFolder::FOLDER_APP_PHPUNIT_JOBS)){
			$production = true;
		}
		return $production;
	}
	/**
	 * @return bool
	 */
	public static function isJenkins(): bool{
		if(\App\Utils\Env::get(Env::SIMULATE_JENKINS)){
			return true;
		}
		$url = ThisComputer::getBuildConsoleUrl();
		if($url && stripos($url, Jenkins::HOST) !== false){
			return true;
		}
		return !empty(\App\Utils\Env::get('JENKINS_URL'));
	}
	/**
	 * @return bool
	 */
	public static function isTestingStagingOrDevelopmentOrConsoleTask(): bool{
		return self::isTestingOrStaging() || EnvOverride::isLocal() || Env::isStaging() ||
			self::getJobTaskOrTestName();
	}
	/**
	 * @return string
	 */
	public static function getAppMode(): ?string{
		if(self::$appMode !== null){
			return self::$appMode;
		}
		if($_SERVER['PHP_SELF'] === "artisan"){
			return self::$appMode = "artisan";
		}
		$env = \App\Utils\Env::get('APP_ENV');
		if($env){
			$env = strtolower($env); // If you're getting the wrong value here, make sure you're using the right phpunit.xml file
			//if(empty($env)){throw new \LogicException("Please set APP_ENV");}  // Infinite loop
			if(str_contains($env, Env::ENV_STAGING_REMOTE)){
				return self::$appMode = Env::ENV_STAGING_REMOTE;
			}
			if(str_contains($env, 'test')){
				return self::$appMode = Env::ENV_TESTING;
			}
			if(str_contains($env, 'production')){
				return self::$appMode = Env::ENV_PRODUCTION;
			}
			if(str_contains($env, Env::ENV_LOCAL)){
				return self::$appMode = Env::ENV_LOCAL;
			}
			if(str_contains($env, 'staging')){
				return self::$appMode = Env::ENV_STAGING;
			}
		}

		if(isset($_SERVER["HTTP_HOST"]) && str_contains($_SERVER["HTTP_HOST"], "staging")){
			return self::$appMode = Env::ENV_STAGING;
		}
		if(isset($_SERVER["HTTP_HOST"]) && str_contains($_SERVER["HTTP_HOST"], "local")){
			return self::$appMode = Env::ENV_LOCAL;
		}
		return null;
	}
	/**
	 * @param bool $value
	 */
	public static function setIsApiRequest(?bool $value): void{
		if(!defined('LARAVEL_START') && AppMode::isUnitOrStagingUnitTest()){
			define('LARAVEL_START',
				microtime(true)); // Fixes LARAVEL_START undefined exception issue in vendor/moesif/moesif-laravel/src/Moesif/Middleware/MoesifLaravel.php:168 during testing
		}
		Memory::set(Memory::API_REQUEST, $value);
	}
	/**
	 * @return string|null
	 */
	public static function getJobOrTaskNameIfNotTesting(): ?string{
		if(AppMode::isAnyKindOfUnitTest()){
			return null;
		}
		return Memory::get(Memory::CONSOLE_TASK_OR_JOB, Memory::MISCELLANEOUS) ?? JenkinsJob::getJobNameEnv();
	}
	/**
	 * @return string|null
	 */
	public static function getJobTaskOrTestName(): ?string{
		$n = self::getJobOrTaskNameIfNotTesting();
		if($n){
			return $n;
		}
		if($n = AppMode::getCurrentTestName()){
			return $n;
		}
		if(ThisComputer::PWD()){
			$pwd = ThisComputer::PWD();
			if(stripos($pwd, '/Jobs/') !== false){
				$n = QMStr::afterLast($pwd, '/Jobs/');
			}
			if(stripos($pwd, '/tests/') !== false){
				$n = QMStr::afterLast($pwd, '/tests/');
			}
			if($n){
				return $n;
			}
		}
		if(AppMode::isApiRequest()){
			return null;
		}
		//QMLog::outputEnvironmentalVariables(true);
		return JenkinsJob::getJobNameEnv();
	}
	/**
	 * @return bool
	 */
	public static function isSlimHttpRequest(): bool{
		if(!self::isApiRequest()){
			return false;
		}
		if(QMRequest::onLaravelAPIPath()){
			return false;
		}
		//if(!QMGlobals::getGlobal(QMGlobals::SLIM)){return false;}
		if(!QMSlim::getInstance()){
			return false;
		}
		return true;
	}
	/**
	 * @param int|string $userIdOrEmail
	 * @return bool
	 */
	public static function isTestingOrIsTestUser($userIdOrEmail): bool{
		return self::isTestingOrStaging() || QMUser::isTestUserByIdOrEmail($userIdOrEmail);
	}
	public static function isLocalAPIRequest(): bool{
		$local = EnvOverride::isLocal();
		$api = AppMode::isApiRequest();
		return $api && $local;
	}
	/**
	 * @return bool
	 */
	public static function isApiRequest(): bool{
		return (bool)Memory::get(Memory::API_REQUEST, Memory::MISCELLANEOUS);
		//        if(isset($_SERVER['REQUEST_METHOD'])){
		//            return true;
		//        }
		//        if(AppMode::isWorker()){
		//            return false;
		//        }
		//        if(AppMode::isConsoleTask()){
		//            return false;
		//        }
		//        return true;
	}
	/**
	 * @param string|null $name
	 */
	public static function setJobOrTaskName(?string $name){
		Memory::set(Memory::CONSOLE_TASK_OR_JOB, $name);
	}
	/**
	 * @return bool
	 */
	public static function isTravisOrHeroku(): bool{
		return (\App\Utils\Env::get('TRAVIS') || \App\Utils\Env::get('HEROKU'));
	}
	/**
	 * @return bool
	 */
	public static function isCI(): bool{
		return self::isJenkins() || self::isTravisOrHeroku();
	}
	/**
	 * @return bool
	 */
	public static function isTravis(): bool{
		return (bool)\App\Utils\Env::get('TRAVIS');
	}
	/**
	 * @return bool
	 */
	public static function consoleAvailable(): bool{
		if(isset($_ENV['APP_RUNNING_IN_CONSOLE'])){
			return $_ENV['APP_RUNNING_IN_CONSOLE'] === 'true';
		}
		return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
	}
	/**
	 * @return bool
	 */
	public static function isDialogFlowRequest(): bool{
		return QMRequest::urlContains('/dialogflow');
	}
	/**
	 * @return bool
	 */
	public static function isStagingOrProductionApiRequest(): bool{
		if(!self::isApiRequest()){
			return false;
		}
		return Env::isStaging() || self::isProduction();
	}
	/**
	 * @return bool
	 */
	public static function isFirewalledFromProduction(): bool{
		return AppMode::isTravisOrHeroku();
	}
	/**
	 * @return bool
	 */
	public static function workingDirIsStagingUnit(): bool{
		$stagingUnit = false;
		if(self::workingDirectoryOrArgumentStartsWith(App\Folders\AbstractFolder::STAGING_UNIT_TEST_PATH)){ // Don't use slash at end
			$stagingUnit = true;
		}
		if(self::workingDirectoryOrArgumentStartsWith(BasePhpUnitTestFile::GENERATED_TESTS_PATH)){
			$stagingUnit = true;
		}
		return $stagingUnit;
	}
	/**
	 * @return bool
	 */
	public static function workingDirIsUnit(): bool{
		$startsWithTests = self::workingDirectoryOrArgumentStartsWith('tests/');
		$isStagingUnit = self::isStagingUnitTesting();
		return $startsWithTests && !$isStagingUnit;
	}
	/**
	 * @param bool $isWorker
	 */
	public static function setIsWorker(bool $isWorker = true){
		Memory::set(self::IS_WORKER, $isWorker);
	}
	/**
	 * @return bool
	 */
	public static function isWorker(): ?bool{
		return Memory::get(self::IS_WORKER, Memory::MISCELLANEOUS);
	}
	public static function isWSL(): ?bool{
		$path = FileHelper::absPath();
		return strpos($path, '/mnt/') === 0;
	}
	/**
	 * @return bool
	 */
	public static function isNonSlimUnitTest(): bool{
		if($class = \App\Utils\AppMode::getCurrentTestClass()){
            if(str_starts_with($class, "Tests\\APIs\\") ||
               str_starts_with($class, "Tests\\ConnectorTests\\") ||
                str_starts_with($class, "Tests\\UnitTests\\")){
                return true;
            }
		}
        if(self::workingDirectoryOrArgumentStartsWith('tests/APIs')){
            return true;
        }
		if(self::workingDirectoryOrArgumentStartsWith('tests/ConnectorTests')){
			return true;
		}
		return self::workingDirectoryOrArgumentStartsWith('tests/UnitTests');
	}
	public static function isUnitTest(): bool{
		if(self::isSlimUnitTest()){
			return true;
		}
		if(self::isStagingUnitTesting()){
			return false;
		}
		return self::isAnyKindOfUnitTest();
	}
	public static function isNonStagingUnitTest(): bool{
		return self::isUnitTest();
	}
	public static function isArtisan(): bool{
		return isset($_SERVER["PHP_SELF"]) && $_SERVER["PHP_SELF"] === "artisan";
	}
	/**
	 * @return bool
	 */
	public static function isUnitOrStagingUnitTest(): bool{
        if(Writable::isSQLite()){
            return true;
        }
		return self::isNonSlimUnitTest() || self::isSlimUnitTest() || self::isStagingUnitTesting();
	}
	public static function isStaging(): bool{
		return self::isStagingUnitTesting() || Env::isStaging();
	}
	public static function isLaravelAPIRequest(): bool{
		return QMRequest::onLaravelAPIPath();
	}
	public static function isJob(): bool{
		return JobTestCase::getCurrentJob() !== null;
	}
	public static function getJobName(): ?string{
		if(!self::isJob()){
			return null;
		}
		return JobTestCase::getJobName();
	}
	public static function setAppDebug(bool $val){
		Env::set(Env::APP_DEBUG, $val);
		config('app.debug', $val);
	}
	public static function isAstral(): bool{
		return false;
		return AppMode::isApiRequest() && QMRequest::urlContains('/astral');
	}
	public static function isCrowdsourcingCures(): bool{
		$as = Memory::getHostAppSettings();
		if($as && $as->getClientId() === BaseClientIdProperty::CLIENT_ID_CROWDSOURCING_CURES){
			return true;
		}
		if(!AppMode::isApiRequest()){
			return false;
		}
		return QMRequest::urlContains(UrlHelper::CROWDSOURCING_CURES_HOSTNAME);
	}
	public static function setCrowdsourcingCures(): void{
		BaseClientIdProperty::setHostClientId(BaseClientIdProperty::CLIENT_ID_CROWDSOURCING_CURES);
	}
	public static function isWindows(): bool{
		return DIRECTORY_SEPARATOR === '\\';
	}
	public static function isCli(): bool{
		return php_sapi_name() === 'cli';
	}
    public static function isFailedTestRunner(): bool{
		$argv = $_SERVER['argv'] ?? [];
		if(!$argv){return false;}
        return in_array("FailedTestsTest.php", $argv);
    }
	public static function isPHPStorm(): bool{
		if(AppMode::isJenkins()){
			return false;
		}
		if(isset($_SERVER["JETBRAINS_REMOTE_RUN"])){
			return true;
		} // Doesn't seem to have this anymore
		$args = $_SERVER["argv"] ?? [];
		foreach($args as $arg){
			if($arg === "--teamcity"){
				return true;
			}
		}
		return false;
	}
	public static function isRemote(): bool{
		return self::isPHPStorm();
	}
	public static function isDocker(): bool{
		if(isset(self::$isDocker)){
			return self::$isDocker;
		}
        if(self::isWindows()){
            return self::$isDocker = false;
        }
        try {
            $processStack = explode(PHP_EOL, shell_exec('cat /proc/self/cgroup'));
        } catch (\Throwable $e) {
            return false;
        }
		$processStack = array_filter($processStack); // remove empty item made by EOL

		foreach ($processStack as $process) {
			if (strpos($process, 'docker') !== false) {
				return self::$isDocker = true;
			}
		}
		return self::$isDocker = false;
	}
    public static function isDebug(): bool{
		if(EnvOverride::getFormatted('APP_DEBUG')){
			return true;
		}
	    if(Env::getFormatted('APP_DEBUG')){
		    return true;
	    }
	    return false;
    }
    public static function isConsole(){
		return app()->runningInConsole();
    }
    public static function isGithubRunner(): bool{
		return getenv('GITHUB_RUN_ID');
    }
}
