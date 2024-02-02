<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests;
use App;
use App\Buttons\Admin\IgnitionButton;
use App\Buttons\Admin\PHPUnitButton;
use App\Computers\ThisComputer;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\DevOps\Jenkins\Build;
use App\DevOps\XDebug;
use App\Exceptions\DiffException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidStringException;
use App\Exceptions\InvalidUrlException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\QMFileNotFoundException;
use App\Exceptions\SlowTestException;
use App\Exceptions\TooManyQueriesException;
use App\Exceptions\UnauthorizedException;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Files\HtmlFile;
use App\Files\Json\JsonTestArtifactFile;
use App\Files\PHP\PhpClassFile;
use App\Files\TestArtifacts\IsTestArtifactFile;
use App\Files\TestArtifacts\TestQueryLogFile;
use App\Jobs\PHPUnitTestJob;
use App\Logging\ConsoleLog;
use App\Logging\GlobalLogMeta;
use App\Logging\QMClockwork;
use App\Logging\QMIgnition;
use App\Logging\QMLog;
use App\Logging\SolutionButton;
use App\Menus\Admin\DebugMenu;
use App\Models\GlobalVariableRelationship;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\SentEmail;
use App\Models\TrackingReminder;
use App\Models\User;
use App\Models\UserVariable;
use App\Models\UserVariableClient;
use App\Models\Variable;
use App\PhpUnitJobs\DevOps\TestJob;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseClientSecretProperty;
use App\Properties\Base\BaseNameProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\User\UserNumberOfPatientsProperty;
use App\Repos\QMAPIRepo;
use App\Repos\TestResultsRepo;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\User\QMUser;
use App\Storage\CacheManager;
use App\Storage\DB\QMDB;
use App\Storage\DB\TestDB;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Traits\HasFunctionLinks;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\HtmlHelper;
use App\Utils\APIHelper;
use App\Utils\AppMode;
use App\Utils\DiffFile;
use App\Utils\Env;
use App\Utils\EnvOverride;
use App\Utils\QMProfile;
use App\Utils\SecretHelper;
use App\Utils\UrlHelper;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\SymptomsCommonVariables\BackPainCommonVariable;
use App\Variables\CommonVariables\TreatmentsCommonVariables\BupropionSrCommonVariable;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use ArrayAccess;
use Auth;
use Clockwork\Support\Laravel\Tests\UsesClockwork;
use Countable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use LogicException;
use Mockery;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Constraint\ObjectHasAttribute;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\TestResult;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\TextUI\Command;
use PHPUnit\TextUI\TestRunner;
use ReflectionObject;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symplify\SmartFileSystem\SmartFileInfo;
use Tests\Traits\CreatesApplication;
use Tests\Traits\LogsTests;
use Tests\Traits\QMVariableTestTrait;
use Throwable;
use TitasGailius\Terminal\Terminal;
use Traversable;
abstract class QMBaseTestCase extends TestCase {
	use ApiTestTrait;
	use QMVariableTestTrait, LogsTests, HasFunctionLinks, CreatesApplication;
	use UsesClockwork;
	const STATUS_ERROR = 'ERROR';
	const STATUS_FAILURE = 'FAILURE';
	const PATH_JUNIT = App\Folders\AbstractFolder::PATH_BUILD_LOGFILES ."/junit.xml";
	protected const REASON_FOR_SKIPPING = null;
	protected const DISABLED_UNTIL = null;
	public const COLLECTOR_DATA_FIXTURES_FOLDER = QMBaseTestCase::FIXTURES_FOLDER.'/collector-data';
	public const FIXTURES_FOLDER = QMBaseTestCase::TEST_FOLDER.'/fixtures';
	public const STATUS_MAP = [
		-1 => 'UNKNOWN',
		0 => 'PASSED',
		1 => 'SKIPPED',
		2 => 'INCOMPLETE',
		3 => self::STATUS_FAILURE,
		4 => self::STATUS_ERROR,
		5 => 'RISKY',
		6 => 'WARNING',
	];
	public const TEST_FOLDER = 'tests';
	public static array $memoryForAll;
	private static float $suiteStartTime;
	private static array $failedTests = [];
	public $alreadySeeded = [];
	public static string $testStartTime;
	private static array $skippedTests = [];
	private static array $initialServer;
	public bool $isRetry = false;// Don't type this because it breaks freeUpMemoryAfterTest
    protected ?int $memoryUsageAtStartOfTestInMB = null; // Not sure what this was for?
	private int $setupStartTime;
	private int $teardownStartTime;
	private bool $alreadyHandledOnNotSuccessfulTest = false;
	private Throwable $exception;
	private ?string $profileUrl = null;
	/**
	 * @param string|\PHPUnit\Framework\TestCase $testOrClass
	 * @param string|null $testName
	 * @return bool False on failure
	 */
	public static function runTestOrClass($testOrClass, string $testName = null): bool{
		if(FileHelper::isFilePath($testOrClass)){
			$testOrClass = FileHelper::pathToClass($testOrClass);
		}
		if(!$testName){
			/** @var \PHPUnit\Framework\TestCase $testOrClass */
			$test = $testOrClass;
			$class = get_class($testOrClass);
			$testName = $test->getName();
		} else{
			$class = $testOrClass;
		}
		Env::setTesting();
		$shortClass = QMStr::toShortClassName($class);
		try {
			$suiteClassFile = FileHelper::getFilePathToClass($class);
			$command = new Command();
			$args = self::getPHPUnitRunArguments();
			$args[] = '--filter';
			$args[] = '/(::'.$testName.')( .*)?$/';
			$args[] = $testOrClass;
			$args[] = $suiteClassFile;
			$args[] = '--log-junit';
			$args[] = app_path(App\Folders\AbstractFolder::PATH_BUILD_LOGFILES ."/$shortClass-junit.xml");
			$GLOBALS["argv"] = $_SERVER['argv'] = $args;
			/** @noinspection PhpUnusedLocalVariableInspection */
			$code = $command->run($args, false);
			$e = QMBaseTestCase::getTestException();
			if($e){
				/** @var LogicException $e */
				throw $e;
			}
			$test = AppMode::getCurrentTest();
			$testName = $test->getName();
			$status = QMBaseTestCase::STATUS_MAP[$test->getStatus()];
			$m = "$class::$testName result $status ".$test->getStatusMessage();
			if($test->getStatus() !== BaseTestRunner::STATUS_PASSED){
				QMLog::error($m);
				return false;
			}
			return true;
		} catch (Exception $e) {
			print $e->getMessage()."\n";
			die ("$class::$testName failed because: ".$e->getMessage());
		}
	}
	public static function getPHPUnitRunArguments(): array{
		$arr = [
			0 => self::getPHPUnitExecutablePath(),
			1 => '--configuration',
			2 => self::getPHPUnitConfigPath(),
			3 => '--cache-result-file='.app_path('tests/.phpunit.result.cache'),
			4 => '--stop-on-failure',
		];
		$arr[] = '--stop-on-error';  // I think this breaks expected exception tests
		return $arr;
	}
	/**
	 * @return string
	 */
	public static function getPHPUnitExecutablePath(): string{
		return FileHelper::absPath('vendor/phpunit/phpunit/phpunit');
	}
	/**
	 * @return string
	 */
	public static function getPHPUnitConfigPath(): string{
		return FileHelper::absPath('phpunit.xml');
	}
	/**
	 * @return Throwable|null
	 */
	public static function getTestException(): ?Throwable{
		return Memory::get(Memory::TEST_EXCEPTION);
	}
	/**
	 * @param QMBaseTestCase $test
	 */
	public static function setCurrentTest(QMBaseTestCase $test): void{
		AppMode::$currentTest = $test;
	}
	public static function currentTestClassIs(string $class): bool{
		return AppMode::getCurrentTestClass() === $class;
	}
	public static function deleteUserVariablesMeasurementsRemindersAndCorrelations(){
		UserVariableRelationship::deleteAll();
		Measurement::deleteAll();
		static::assertEquals(0, Measurement::count());
		TrackingReminder::deleteAll();
		UserVariableClient::deleteAll();
		UserVariable::deleteAll();
		Memory::flush();
	}
	/**
	 * @param BaseModel|string $class
	 * @return BaseModel
	 */
	public static function firstOrFakeNew(string $class): BaseModel{
		if(!class_exists($class)){
			le("class $class not found");
		}
		if(str_contains($class, "Bshaffer")){
			le("class $class not found");
		}
		/** @var BaseModel $m */
		$m = new $class;
		$qb = $class::query();
		if($m->hasUserIdAttribute()){
			/** @var UserVariable $class */
			$qb->where($class::FIELD_USER_ID, UserIdProperty::USER_ID_DEMO);
		}
		$m = $qb->first();
		if($m){
			return $m;
		}
		/** @var BaseModel $factory */
		$factory = $class::fakeFromPropertyModels();
		return $factory;
	}
	/**
	 * @param BaseModel|string $class
	 * @return BaseModel
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function firstOrFakeSave(string $class): BaseModel{
		$m = $class::first();
		if($m){
			return $m;
		}
		$m = $class::fakeFromPropertyModels();
		$m->save();
		return $m;
	}
	public static function getBrowserTestUrl(): string{
		$name = AppMode::getCurrentTestName();
		$url = UrlHelper::getTestUrl("/admin/phpunit", [
			'test' => $name,
			'class' => self::getCurrentTestClassName(),
		]);
		return $url;
	}
	public static function getCurrentTestClassName(): string{
		return get_class(AppMode::getCurrentTest());
	}
	public static function getPhysicianUser(): QMUser{
		return QMUser::find(UserIdProperty::USER_ID_PHYSICIAN);
	}
	/**
	 * @param string|null $testPath
	 */
	public static function runTestFile(string $folderOrFile = null): void{
		if(!defined('PROJECT_ROOT')){
			define('PROJECT_ROOT', dirname(__DIR__).'');
		}
		Env::setTesting();
		if(!$folderOrFile){
			$folderOrFile = Env::get('TEST_PATH');
		}
		if(!$folderOrFile){
			QMLog::error("No TEST_PATH env");
			die("No TEST_PATH env");
		}
		QMLog::info(__METHOD__." $folderOrFile");
		$phpunit = new TestRunner;
		try {
			$folderOrFile = abs_path($folderOrFile);
			$generatedSuite = $phpunit->getTest($folderOrFile, 'Test.php');
			$generatedSuite->setRunTestInSeparateProcess(true);
		} catch (Exception $e) {
			print $e->getMessage()."\n";
			die ("Unit tests failed.");
		}
	}
	public static function runTestsInFolder(string $folder): string{
		if(stripos($folder, "Staging") !== false){
			Env::setStagingRemote();
		} else{
			Env::setTesting();
		}
		$command = new Command();
		$args = self::getPHPUnitRunArguments();
		$args[] = FileHelper::absPath($folder);
		$args[] = '--log-junit';
		$args[] = app_path("phpunit/$folder.xml");
		$GLOBALS["argv"] = $_SERVER['argv'] = $args;
		$code = $command->run($args, false);
		$status = QMBaseTestCase::STATUS_MAP[$code];
		return "$folder result $status";
	}
	public static function getSuiteName(): string {
		$args = ThisComputer::getCommandLineArguments();
		foreach($args as $arg){
			if(str_starts_with($arg, abs_path("tests"))){
				$suite = str_replace(abs_path("tests"), '', $arg);
				$suite = str_replace('\\', '', $suite);
				return $suite;
			}
			$arg = str_replace('\\', '/', $arg);
			if(str_starts_with($arg, "tests/")){
				return str_replace("tests/", '', $arg);
			}
		}
		foreach($args as $arg){if(str_contains($arg, "Tests\\")){return $arg;}}
		return "Unknown Test Suite";
	}
	public static function setSuiteStartTime(float $microtime){
		static::$suiteStartTime = $microtime;
	}
	public static function getSuiteStartTime(): float{
		return static::$suiteStartTime;
	}
	public static function getSuiteDuration(): string {
		$seconds = microtime(true) - static::$suiteStartTime;
		return TimeHelper::convertSecondsToHumanString($seconds);
	}
	public static function getFailedTests(){
		return static::$failedTests;
	}
	public function addFailedTest(){
		return static::$failedTests[$this->getName()] = $this;
	}
	public static function queueTestsInFolder(string $folder, 
	                                          string $url = "https://feature.quantimo.do/admin/phpunit", 
	                                          string $sha = null){
		$res = APIHelper::makePostRequest($url, [
			'folder' => $folder,
			'sha' => $sha ?? QMAPIRepo::getLongCommitShaHash(),
			'immediate' => true,
		], User::mike()->getOrCreateAccessTokenString(BaseClientIdProperty::CLIENT_ID_SYSTEM));
		QMLog::info("Queued $folder tests. Response: ".QMLog::print_r($res));
		return $res;
	}
	/**
	 * @param \TitasGailius\Terminal\Fakes\BuilderFake|\TitasGailius\Terminal\Builder $builder
	 * @param string $folderToTest
	 * @return \TitasGailius\Terminal\Response
	 */
	public static function runTestsInFolderByCommandLine(string $folderToTest, string $params = ""): 
	\TitasGailius\Terminal\Response{
		QMLog::importantInfo(__FUNCTION__." $folderToTest");
		$builder = Terminal::builder();
		$builder->in(abs_path());
		$phpunit = abs_path('vendor/bin/phpunit');
		$command = "php $phpunit --stop-on-failure --printer mheap\\\\GithubActionsReporter\\\\Printer ".
		           abs_path($folderToTest);
		$builder->withEnvironmentVariables($_ENV);
		ConsoleLog::info($command);
		$response = $builder->run($command, ConsoleLog::logTerminalOutput());
		$output = $response->output();
		if(!$response->successful() || str_contains($output, "FAILURES!") || str_contains($output, "Failed tests")){
			le("Failed tests in $folderToTest: ".$output);
		}
		return $response;
	}
	/**
	 * @param mixed $testName
	 * @return void
	 */
	private static function runTestByName(string $testName): void{
		$file = FileFinder::findFirstContaining('tests', "function ".$testName, true, 'Test.php');
		if(!$file){
			le("Could not find file for test: ".$testName);
		}
		$filepath = $file->getRealPath();
		$filename = $file->getFilename();
		$folder = FileHelper::getDirectoryFromFilePath($filepath);
		$class = FileHelper::pathToClass($filepath);
		$escapedClass = str_replace('\\', '\\\\', $class);
		$builder = Terminal::builder();
		$builder->in(abs_path());
		$builder->timeout(0);
		if(str_contains($class, 'Staging')){
			$env = Env::readEnvFile('.env.staging-remote');
		} else{
			$env = Env::readEnvFile('.env.testing');
		}
		$builder->withEnvironmentVariables($env);
		$debug = "";
		if(XDebug::active()){
			$debug = "-dxdebug.mode=debug -dxdebug.client_port=9000 -dxdebug.client_host=127.0.0.1";
		}
		$str =
			"php $debug vendor/phpunit/phpunit/phpunit --log-junit build/junit.xml --filter \"/($escapedClass::$testName)( .*)?$/\" --test-suffix $filename $folder";
		//$str .= "--teamcity";
		$response = $builder->run($str, ConsoleLog::logTerminalOutput());
		ConsoleLog::infoWithoutContext($response->output());
	}
	/**
	 * @return array
	 */
	private static function getFailedTestNames(): array{
		$statuses = QMAPIRepo::getFailedStatuses();
		$failedTests = [];
		foreach($statuses as $status){
			if(str_contains($status["context"], 'test')){
				$failedTests[] = $status["context"];
				continue;
			}
			if(str_contains($status["description"], 'test')){
				$name = QMStr::afterAndIncluding($status["description"], 'test');
				if(str_contains($name, '...')){
					le("Found ellipsis in test name: ".$name);
				}
				$exploded = explode(", ", $name);
				$failedTests = array_merge($failedTests, $exploded);
			}
		}
		$failedTests = array_unique($failedTests);
		QMLog::list($failedTests, 'Failed Tests');
		return $failedTests;
	}
	/**
	 * @param string|null $expectedRequestException
	 */
	public static function setExpectedRequestException(?string $expectedRequestException): void{
		if($expectedRequestException && !class_exists($expectedRequestException)){
			throw new LogicException("Exception $expectedRequestException does not exist!");
		}
		ExceptionHandler::$expectedRequestException = $expectedRequestException;
		if($expectedRequestException){
			$t = AppMode::getCurrentTest();
			//if($t){$t->expectException($expectedRequestException);}
		}
	}
	/**
	 * @param Throwable $e
	 */
	public static function setTestException(Throwable $e): void{
		Memory::set(Memory::TEST_EXCEPTION, $e);
	}
	/**
	 * @param string $uri
	 * @param array|false[] $options
	 * @return TestResponse
	 * @throws GuzzleException
	 * @noinspection PhpMultipleClassDeclarationsInspection
	 */
	public static function getTestResponseFrommExternalUrl(string $uri, array $options = [
		'allow_redirects' => true,
		'timeout' => 15,
		'verify' => true,
	]): TestResponse{
		/** @noinspection HttpUrlsUsage */
		if(str_starts_with($uri, "http://")){
			$options['verify'] = false;
		}
		ConsoleLog::info(__FUNCTION__.": Getting $uri...");
		$client = new Client();
		$response = $client->request('GET', $uri, $options);
		$content = $response->getBody()->getContents();
		$l = new Response($content, $response->getStatusCode(), $response->getHeaders());
		return TestResponse::fromBaseResponse($l);
	}
	/**
	 * @param $object
	 * @param string $propertyName
	 */
	public static function assertPropertyExistsAndOutputIfFalse($object, string $propertyName){
		$result = property_exists($object, $propertyName);
		if(!$result){
			SlimStagingTestCase::obfuscateAndPrintR($object);
			le("$propertyName does not exist!");
		}
	}
	/**
	 * Asserts the number of elements of an array, Countable or Traversable.
	 * @param int $expectedCount
	 * @param iterable|Collection|array $haystack
	 * @param string $message
	 */
	public static function assertCountGreaterThan(int $expectedCount, $haystack, string $message = ''): void{
		QMAssert::assertCountGreaterThan($expectedCount, $haystack, $message);
		self::assertTrue(true);
	}
	/**
	 * Asserts that two variables are equal.
	 * @param $expected
	 * @param $actual
	 * @param string|null $expectedName
	 * @param string|null $actualName
	 * @param string $message
	 */
	public static function assertDateEquals($expected, $actual, string $expectedName = null, string $actualName = null,
	                                        string $message = ''): void{
		if(!$actual){
			le("No actual $actualName date provided to ".__FUNCTION__);
		}
		if(!$expected){
			le("No expected $expectedName date provided to ".__FUNCTION__);
		}
		$expectedDate = db_date($expected);
		$actualDate = db_date($actual);
		if($expectedDate === $actualDate){
			return;
		}
		if(!$actualName){
			$params = self::getArgumentNamesFromFunctionCall();
			$expectedName = $params[0];
			$actualName = $params[1];
		}
		$message .= "\n$actualName\n\t$actualDate should equal $expectedName\n\t$expectedDate";
		static::assertEquals($expectedDate, $actualDate, $message);
	}
	/**
	 * @return string[]
	 */
	public static function getArgumentNamesFromFunctionCall(): array{
		$back = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);
		$frame = $back[1];
		$code = FileHelper::getLineOfCode($frame['file'], $frame['line']);
		$code = QMStr::between($code, "(", ");");
		$params = explode(',', $code);
		return $params;
	}
	/**
	 * @param $actual
	 * @param string|null $expectedName
	 * @param string|null $actualName
	 * @param string $message
	 */
	public static function assertPast($actual, string $expectedName = null, string $actualName = null,
	                                  string $message = ''){
		self::assertDateLessThan(time(), $actual, $expectedName, $actualName, $message);
	}
	/**
	 * @param $expected
	 * @param $actual
	 * @param string|null $expectedName
	 * @param string|null $actualName
	 * @param string $message
	 */
	public static function assertDateLessThan($expected, $actual, string $expectedName = null,
	                                          string $actualName = null, string $message = ''): void{
		if(!$actual){
			le("$expectedName\n$actualName\n$message\n"."No actual $actualName date provided to ".__FUNCTION__);
		}
		[
			$expectedDate,
			$actualDate,
			$expectedName,
			$actualName,
		] = self::getDateAssertionParams($actual, $actualName, $expected, $expectedName);
		$message .= "\n$actualName $actualDate should be less than $expectedName $expectedDate";
		Assert::assertLessThan($expectedDate, $actualDate, $message);
	}
	/**
	 * @param $actual
	 * @param string|null $actualName
	 * @param $expected
	 * @param string|null $expectedName
	 * @return array
	 */
	private static function getDateAssertionParams($actual, ?string $actualName, $expected,
	                                               ?string $expectedName): array{
		if(!$actual){
			le("No actual $actualName date provided to ".__FUNCTION__);
		}
		if(!$expected){
			le("No expected $expectedName date provided to ".__FUNCTION__);
		}
		$expectedDate = db_date($expected);
		$actualDate = db_date($actual);
		if(!$actualName){
			$params = self::getArgumentNamesFromFunctionCall();
			$expectedName = $params[0];
			$actualName = $params[1];
		}
		return [
			$expectedDate,
			$actualDate,
			$expectedName,
			$actualName,
		];
	}
	/**
	 * Asserts that two variables are equal.
	 * @param int $seconds
	 * @param $expected
	 * @param $actual
	 * @param string|null $expectedName
	 * @param string|null $actualName
	 * @param string $message
	 */
	public static function assertDateWithinXSecondsOf(int    $seconds, $expected, $actual, string $expectedName = null,
	                                                  string $actualName = null, string $message = ''): void{
		if(!$actual){
			le("No actual $actualName date provided to ".__FUNCTION__);
		}
		if(!$expected){
			le("No expected $expectedName date provided to ".__FUNCTION__);
		}
		$expectedTime = TimeHelper::universalConversionToUnixTimestamp($expected);
		$max = $expectedTime + 30;
		$min = $expectedTime - 30;
		$expectedDate = db_date($expected);
		$actualDate = db_date($actual);
		$actualTime = TimeHelper::universalConversionToUnixTimestamp($expected);
		if($actualTime > $max || $actualTime < $min){
			if(!$actualName){
				$params = self::getArgumentNamesFromFunctionCall();
				$expectedName = $params[0];
				$actualName = $params[1];
			}
			le("\n$message.\n$actualName $actualDate should be within $seconds seconds of $expectedName $expectedDate. ");
		}
	}
	/**
	 * Asserts that two variables are equal.
	 * @param $expected
	 * @param $actual
	 * @param string|null $expectedName
	 * @param string|null $actualName
	 * @param string $message
	 */
	public static function assertDateNotEquals($expected, $actual, string $expectedName = null,
	                                           string $actualName = null, string $message = ''): void{
		[
			$expectedDate,
			$actualDate,
			$expectedName,
			$actualName,
		] = self::getDateAssertionParams($actual, $actualName, $expected, $expectedName);
		$message .= "\n$actualName $actualDate should equal $expectedName $expectedDate";
		static::assertNotEquals($expectedDate, $actualDate, $message);
	}
	private static function isValidObjectAttributeName(string $attributeName): bool
	{
		return (bool) preg_match('/[^\x00-\x1f\x7f-\x9f]+/', $attributeName);
	}
	/**
	 * Asserts that an object has a specified attribute.
	 *
	 * @param object $object
	 *
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws Exception
	 * @throws ExpectationFailedException
	 *
	 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/4601
	 */
	public static function assertObjectHasAttribute(string $attributeName, $object, string $message = ''): void
	{
		if (!self::isValidObjectAttributeName($attributeName)) {
			throw \PHPUnit\Framework\InvalidArgumentException::create(1, 'valid attribute name');
		}

		if (!is_object($object)) {
			throw new InvalidArgumentException("not an object");
		}

		static::assertThat(
			$object,
			new ObjectHasAttribute($attributeName),
			$message
		);
	}
	/**
	 * @param $expected
	 * @param $actual
	 * @param string|null $expectedName
	 * @param string|null $actualName
	 * @param string $message
	 */
	public static function assertDateLessThanOrEqual($expected, $actual, string $expectedName = null,
	                                                 string $actualName = null, string $message = ''): void{
		[
			$expectedDate,
			$actualDate,
			$expectedName,
			$actualName,
		] = self::getDateAssertionParams($actual, $actualName, $expected, $expectedName);
		$message .= "\n$actualName $actualDate should be less than or equal to $expectedName $expectedDate";
		Assert::assertLessThanOrEqual($expectedDate, $actualDate, $message);
	}
	/**
	 * Asserts that two variables are equal.
	 * @param $earlier
	 * @param $later
	 * @param string|null $earlierName
	 * @param string|null $laterName
	 * @param string $message
	 */
	public static function assertDateGreaterThanOrEqual($earlier, $later, string $earlierName = null,
	                                                    string $laterName = null, string $message = ''): void{
		[
			$earlierDate,
			$laterDate,
			$earlierName,
			$laterName,
		] = self::getDateAssertionParams($later, $laterName, $earlier, $earlierName);
		$message .= "\n$laterName $laterDate should be greater than or equal to $earlierName $earlierDate";
		Assert::assertGreaterThanOrEqual($earlierDate, $laterDate, $message);
	}
	/**
	 * Asserts that two variables are equal.
	 * @param $expected
	 * @param $actual
	 * @param string|null $expectedName
	 * @param string|null $actualName
	 * @param string $message
	 */
	public static function assertDateGreaterThan($expected, $actual, string $expectedName = null,
	                                             string $actualName = null, string $message = ''): void{
		[
			$expectedDate,
			$actualDate,
			$expectedName,
			$actualName,
		] = self::getDateAssertionParams($actual, $actualName, $expected, $expectedName);
		$message .= "\n$actualName $actualDate should be greater than $expectedName $expectedDate";
		Assert::assertGreaterThan($expectedDate, $actualDate, $message);
	}
	/**
	 * @param BaseModel|string $class
	 * @param $id
	 * @param string $needle
	 */
	public static function assertFieldsLikeNotNull(string $class, $id, string $needle){
		$row = $class::find($id);
		foreach($class::getColumnsLike($needle) as $field){
			self::assertNotNull($row->getAttribute($field), "$class $field should not be null");
		}
	}
	public static function assertFilesExist(array $paths){
		foreach($paths as $path){
			static::assertFileExists($path);
		}
	}
	public static function validatePaths(array $paths){
		foreach($paths as $path){
			static::assertFileExists($path);
		}
	}
	/**
	 * @param string $path
	 * @param string $html
	 * @param bool $ignoreNumbers
	 * @param string $message
	 * @noinspection PhpUnused
	 */
	public static function comparePublicHtml(string $path, string $html, bool $ignoreNumbers, string $message = ''){
		$html = HtmlHelper::relativizePaths($html);
		$path = FileHelper::addPublicToPathIfNecessary($path);
		self::compareAndValidateHtml($path, $html, $ignoreNumbers, $message);
	}
	/**
	 * @param string $pathToFixture
	 * @param string $html
	 * @param bool $ignoreNumbers
	 * @param string $message
	 * @throws DiffException
	 * @throws QMFileNotFoundException
	 */
	protected static function compareAndValidateHtml(string $pathToFixture, string $html, bool $ignoreNumbers,
	                                                 string $message = ''): void{
		$d = new DiffFile($html, $pathToFixture, $ignoreNumbers);
		$d->assertSame($message);
		try {
			HtmlHelper::validateHtmlPage($html, $pathToFixture);
		} catch (InvalidStringException $e) {
			le($e);
		}
		self::assertTrue(true);
	}
	/**
	 * @param string $path
	 * @param string $html
	 * @param bool $ignoreNumbers
	 * @param string $message
	 * @throws InvalidStringException
	 */
	public static function compareStaticHtml(string $path, string $html, bool $ignoreNumbers, string $message = ''){
		$path = FileHelper::addStaticToPathIfNecessary($path);
		self::compareAndValidateHtml($path, $html, $ignoreNumbers, $message);
		HtmlHelper::validateStaticHtml($html, $path);
	}
	public static function compareStringFixture(string $filename, string $str){
		$html = HtmlHelper::stripDebugInfo($str);
		$html = QMStr::replace_between_and_including($html, 'DB_URL=', '"', '"');
		IsTestArtifactFile::assertSameStringInFile($filename, $html);
		self::assertTrue(true);
	}
	/**
	 * @param string $key
	 * @param $obj
	 * @param string|null $message
	 */
	protected static function compareObjectFixture(string $key, $obj, string $message = null): void{
		JsonTestArtifactFile::assertSameObject($key, $obj, $message);
		self::assertTrue(true);
	}
	public static function assertButtonTitles(array $expectedTitles, array $actualButtons){
		$actualTitles = QMArr::pluckColumn($actualButtons, 'title');
		static::assertArrayEquals($expectedTitles, $actualTitles, "Actual Buttons: ".\App\Logging\QMLog::print_r($actualButtons, true));
	}
    /**
     * @param array $expected
     * @param array $actual
     * @param string|null $message
     */
    public static function assertArrayContains(array $expected, array $actual, string $message = null): void{
        foreach ($expected as $key => $value) {
            static::assertArrayHasKey($key, $actual, "Array should have key $key but is ".
                \App\Logging\QMLog::print_r($actual, true));
            static::assertEquals($value, $actual[$key], "Key $key should be ".\App\Logging\QMLog::print_r($value, true).
                " but is ".\App\Logging\QMLog::print_r($actual[$key], true)."\n$message");
        }
    }
	/**
	 * @param array $expected
	 * @param array $actual
	 * @param string|null $message
	 */
	public static function assertArrayEquals(array $expected, array $actual, string $message = null, bool 
	$ignoreDates = false): void{
		foreach($actual as $m){
			unset($m->updatedAt);
			unset($m->createdAt);
			unset($m->deletedAt);
		}
		if($ignoreDates){
			$expected = QMArr::removeDates($expected);
			$actual = QMArr::removeDates($actual);
		}
		ksort($expected);
		ksort($actual);
		$actual = json_decode(json_encode($actual), true);
		if($expected !== $actual){
			$message .= "\nExpected:\n".QMLog::var_export($expected, true);
			$message .= "\nGot:\n".QMLog::var_export($actual, true);
			ConsoleLog::error($message, [], false);
			static::fail("Arrays are not equal. See above for details.");
		}
		parent::assertEquals($expected, $actual, $message ?? '');
	}
	/**
	 * @param array $expected
	 * @param object[]|array[]|Collection $objects
	 * @param string|null $message
	 */
	public static function assertNames(array $expected, $objects, string $message = null): void{
		$names = BaseNameProperty::pluckArray($objects);
		sort($expected);
		sort($names);
		//try {
		static::assertArrayEquals($expected, $names, $message);
		//        } catch (\Throwable $e){
		//            // TODO: Figure out how to make this work PHPUnitFile::replaceArray($expected, $names);
		//            $this->assertArrayEquals($expected, $names, $message);
		//        }
	}
	public static function assertDurationLessThan(float $max){
		$duration = LogsTests::getDuration();
		static::assertLessThan($max, $duration);
	}
	public static function assertFileContains(string $path, string $needle){
		try {
			$num = FileHelper::getContents($path);
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
		static::assertStringContainsString($needle, $num);
	}
	public static function generateAll(){
		$classes = PhpClassFile::get();
		foreach($classes as $class){
			$class->generateUnitTest();
		}
	}
	/**
	 * checks if the object has this class as one of its parents or implements it
	 * @link https://php.net/manual/en/function.is-subclass-of.php
	 * @param mixed $object_or_class <p>
	 * A class name or an object instance
	 * @param string $class <p>
	 * The class name
	 * @param string|null $message
	 * @param bool $allow_string [optional] <p>
	 * If this parameter set to false, string class name as object is not allowed.
	 * This also prevents from calling autoloader if the class doesn't exist.
	 */
	public static function assertExtends($object_or_class, string $class, string $message = null,
	                                     bool $allow_string = true){
		static::assertIsSubClassOf($object_or_class, $class, $message, $allow_string);
	}
	/**
	 * checks if the object has this class as one of its parents or implements it
	 * @link https://php.net/manual/en/function.is-subclass-of.php
	 * @param mixed $object_or_class <p>
	 * A class name or an object instance
	 * @param string $class <p>
	 * The class name
	 * @param string|null $message
	 * @param bool $allow_string [optional] <p>
	 * If this parameter set to false, string class name as object is not allowed.
	 * This also prevents from calling autoloader if the class doesn't exist.
	 */
	public static function assertIsSubClassOf($object_or_class, string $class, string $message = null,
	                                          bool $allow_string = true){
		if(!is_subclass_of($object_or_class, $class, $allow_string)){
			static::assertTrue(true, get_class($object_or_class)." should be a subclass of $class\n$message");
		}
	}
	public static function assertSameRelativePath(string $actual, string $expected){
		$actual = FileHelper::toRelativePath($actual);
		$expected = FileHelper::toRelativePath($expected);
		if($actual !== $expected){
			le("Actual relative path does not match expected relative path!
    ACTUAL: $actual
    EXPECTED: $expected
    ");
		}
	}
	/**
	 * @param array $urls
	 * @param string $message
	 */
	public static function assertValidUrls(array $urls, string $message = ''){
		foreach($urls as $url){
			self::assertValidUrl($url, $message);
		}
	}
	/**
	 * @param $url
	 * @param string $message
	 */
	private static function assertValidUrl($url, string $message): void{
		try {
			QMAssert::assertValidUrl($url, $message);
		} catch (InvalidUrlException $e) {
			le($e);
		}
	}
	/**
	 * Asserts that an array has a specified key.
	 * @param int|string $key
	 * @param array|ArrayAccess $array
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 * @throws Exception
	 */
	public static function assertArrayHasKey($key, $array, string $message = ''): void{
		try {
			parent::assertArrayHasKey($key, $array, $message);
		} catch (\Throwable $e) {
			parent::assertArrayHasKey($key, $array, $message."\n".QMArr::listKeys($array, "Array keys are"));
		}
	}
	public static function assertFileContentsEqual(string $expected, string $path){
		$contents = FileHelper::getContents($path);
		static::assertEquals($expected, $contents);
	}
	public static function assertTableExists(string $table){
		if(!Writable::tableExists($table)){
			le("$table does not exist!");
		}
	}
	/**
	 * @param $value
	 * @param string $type
	 * @throws InvalidUrlException
	 */
	protected static function assertIsUrl($value, string $type){
		UrlHelper::assertIsUrl($value, $type);
	}
	/**
	 * @param string $path
	 * @param string $contents
	 * @param bool $ignoreNumbers
	 * @param string $message
	 * @throws DiffException
	 * @throws QMFileNotFoundException
	 */
	protected static function compareFile(string $path, string $contents, bool $ignoreNumbers,
	                                      string $message = ''): void{
		$d = new DiffFile($contents, $path, $ignoreNumbers);
		$d->assertSame($message);
		self::assertTrue(true);
	}
	/**
	 * @param array $actualArr
	 * @param array $expectedKeys
	 */
	protected static function assertArrayKeysContain(array $actualArr, array $expectedKeys): void{
		$keys = array_keys($actualArr);
		foreach($expectedKeys as $one){
			static::assertContains($one, $keys, "Keys are: ".var_export($keys, true));
		}
	}
	/**
	 * @return null|string
	 */
	public function __toString(){
		return $this->getName();
	}
	/**
	 * @param string $method
	 * @param string $uri
	 * @param array $parameters
	 * @param array $cookies
	 * @param array $files
	 * @param array $server
	 * @param null $content
	 * @return TestResponse
	 */
	public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [],
	                     $content = null): TestResponse{
		$msg = "$method $uri...";
		$start = microtime(true);
		$logParams = QMArr::removeEmpty([
			                                  'parameters' => $parameters,
			                                  'cookies' => $cookies,
			                                  'files' => $files,
			                                  'server' => $server,
			                                  'content' => $content,
		                                  ]);
		QMLog::info("$method $uri...".QMLog::print_r($logParams));
        AppMode::setIsApiRequest(true);
		$_SERVER['REQUEST_METHOD'] = $method;
        $uri = UrlHelper::stripDomainIfNecessary($uri);
		$uri = QMStr::addPrefixIfNecessary('/', $uri);
		$_SERVER['REQUEST_URI'] = $uri;
        try {
            $res = parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
        } catch (NotFoundHttpException $e) {
            $lower = strtolower($method);
            $path = QMStr::before("?", $uri, $uri);
            $path = QMStr::after('v3/', $path, $path);
            $class = ucfirst($lower).QMStr::toShortClassName($path)."Controller";
            le($e->getMessage()."\n
            
Route::$lower('$path', $class::class . '@$lower');    
        
            ");
        }
        AppMode::setIsApiRequest(false);
		QMClockwork::logDuration($msg, $start, microtime(true));
		Memory::flush();
		return $res;
	}
	/**
	 * @param string $needle
	 * @param string $haystack
	 */
	public function assertHtmlHeadContains(string $needle, string $haystack){
		$head = QMStr::between($haystack, "<head", "</head>");
		$this->assertContains($needle, $head);
	}

    /**
	 * Asserts that a haystack contains a needle.
	 * @param mixed $needle
	 * @param mixed $haystack
	 * @param string $message
	 * @param bool $ignoreCase
	 * @param bool $checkForObjectIdentity
	 * @param bool $checkForNonObjectIdentity
	 */
	public static function assertContains($needle, $haystack, string $message = '', bool $ignoreCase = false,
	                                      bool $checkForObjectIdentity = true,
	                                      bool $checkForNonObjectIdentity = false): void{
		if(is_array($haystack) || ($haystack instanceof Traversable)){
			if(!in_array($needle, $haystack, $ignoreCase)){
                if(!empty($message)){
                    static::fail($message);
                }
				if(!is_array($needle)){
					static::fail("Failed asserting that '$needle'. is in array: ".var_export($haystack, true));
				}
                $diff = QMArr::array_diff_recursive($needle, $haystack);
                if(!$diff){
                    return;
                }
                $needle = var_export($needle, true);
                static::fail("$needle 
not found in array: 
    ".var_export($haystack, true)."
DIFF: 
    ".var_export($diff, true));
            }
		} elseif(is_string($haystack)){
			static::assertStringContains($haystack, $needle, AppMode::getCurrentTestName() ."-".$message, $ignoreCase);
		} else{
			throw new \InvalidArgumentException('array, traversable or string', 2);
		}
	}
	/**
	 * @param string $haystack
	 * @param array|string $requiredStrings
	 * @param string $type
	 * @param bool $ignoreCase
	 * @param string|null $message
	 */
	public static function assertStringContains(string $haystack, $requiredStrings, string $type,
	                                            bool   $ignoreCase = false, string $message = null){
		if(!is_array($requiredStrings)){
			$requiredStrings = [$requiredStrings];
		}
		foreach($requiredStrings as $needle){
			if($ignoreCase){
				static::assertStringContainsStringIgnoringCase($needle, $haystack, "$type-$message");
			} else{
				static::assertStringContainsString($needle, $haystack, "$type-$message");
			}
		}
	}
	/**
	 * @param int $expectedCount
	 * @param mixed $haystack
	 * @param string $message
	 */
	public function assertCountAndLog(int $expectedCount, $haystack, string $message = ''){
		if(count($haystack) !== $expectedCount){
			foreach($haystack as $item){
				QMLog::infoWithoutContext($item);
			}
		}
		Assert::assertCount($expectedCount, $haystack, $message);
	}
	/**
	 * @param [] $booleanAttributes
	 * @param $object
	 */
	public function checkBooleanAttributes($booleanAttributes, $object){
		foreach($booleanAttributes as $attribute){
            if($attribute === 'is_public'){
                //debugger("");
                continue;
            }
			if($object->$attribute !== null){
				$this->assertIsBool($object->$attribute,
				                    "$attribute should be a boolean but ".$object->$attribute.' is '.
				                    gettype($object->$attribute));
			}
		}
	}
	/**
	 * @param $notNullAttributes
	 * @param $object
	 */
	public function checkNotNullAttributes($notNullAttributes, $object){
		foreach($notNullAttributes as $attribute){
			$this->assertNotNull($object->$attribute, "$attribute should not be null");
		}
	}
	/**
	 * @param $floatAttributes
	 * @param $object
	 */
	public function checkFloatAttributes($floatAttributes, $object){
		foreach($floatAttributes as $attribute){
			if(!property_exists($object, $attribute)){
				$arr = QMArr::toArray($object);
				ksort($arr);
				$this->fail("$attribute property does not exist in ".QMLog::print_r($arr, true));
			}
			$actualValue = $object->$attribute;
			$this->assertIsNotString($actualValue,
			                         "$attribute should be a float but ".$actualValue.' is a string');
			if($actualValue !== null){
				$this->assertTrue(is_float($actualValue) || is_int($actualValue),
				                  "$attribute should be a float but ".$actualValue.' is '.
				                  gettype($actualValue));
			}
		}
	}
	/**
	 * @param string $message
	 * @param array|object $metaData
	 */
	public function logError(string $name, $meta = [], bool $obfuscate = true, string $message = null){
		if(is_object($meta)){
			$meta = json_decode(json_encode($meta), true);
		}
		$meta['failedTest'] = $this->getName();
		QMLog::error($this->getLogMetaDataString().$name, $meta, $obfuscate, $message);
	}
	/**
	 * @return string
	 */
	public function getLogMetaDataString(): string{
		return $this->getName().": ";
	}
	/**
	 * @return mixed
	 */
	public function saveClockwork(){
		ConsoleLog::info("Saving clockwork test data: ".QMClockwork::getAppUrl());
		$make = $this->app->make('clockwork');
		$asserts = $this->resolveClockworkAsserts();
		$status = $this->resolveClockworkStatus();
		$statusMessage = $this->getStatusMessage();
		$name = $this->toString();
		$resolveAsTest = $make->resolveAsTest($name, $status, $statusMessage, $asserts);
		return $resolveAsTest->storeRequest();
	}
	/**
	 * @param int|null $maximumSeconds
	 */
	public function checkTestDuration(int $maximumSeconds, string $message = null){
		$duration = self::getTestDuration();
		ConsoleLog::info("TEST DURATION: $duration seconds");
		$name = $this->getName();
		if($maximumSeconds){
			$cutoff = 0.5 * $maximumSeconds;
			if($duration < $cutoff && $maximumSeconds > 20){
				$new = floor($duration * 1.5);
				$msg = "Duration was $duration but maximum is set to $maximumSeconds.  Please decrease max to $new in ".
				       $this->getName()."\n$message";
				if(EnvOverride::isLocal()){
					le($msg);
				} else{
					QMLog::error($msg);
				}
			}
			$m = "$name DURATION: $duration seconds.  Maximum: $maximumSeconds";
			if($duration > $maximumSeconds){
				$this->logError("$m\n$message");
				//$this->addWarning("$m\n$message");
				// TODO: re-enable when we have consistent slave performance
				// $this->retryOrFailSlowTest($duration, $maximumSeconds);
			} else {
				$this->logInfo($m);
			}
		}
	}
	/**
	 * @return int
	 */
	public static function getTestDuration(): int{
		if(!isset(self::$testStartTime)){return 0;}
		return round(time() - self::$testStartTime);
	}
	/**
	 * @param int $duration
	 * @param int $maximumSeconds
	 * @throws SlowTestException
	 */
	protected function retryOrFailSlowTest(int $duration, int $maximumSeconds): void{
		$name = $this->getName();
		QMProfile::endProfile();
		$message = "$duration s exceeds maximum $maximumSeconds s! ";
		try {
			$this->fail($message.TestQueryLogFile::getQueryLogCliTable());
		} catch (\Throwable $failException) {
		    QMLog::error(__METHOD__.": Could not this->fail because: $failException");
			throw $failException;
		}
//		ConsoleLog::info($message);
//		if(!$this->isRetry){
//			$retryDuration = $this->retryWithProfile();
//			if($retryDuration < $maximumSeconds){
//				QMProfile::endProfile();
//				ConsoleLog::info("$name retry passed!");
//				return;
//			}
//			$message = "Duration on retry was $retryDuration s";
//		}
//		QMProfile::endProfile();
//		$this->fail("$name retry failed! ".$message."\nProfile: ".QMProfile::getLastProfileUrl()."\n".
//		                            TestQueryLogFile::getQueryLogCliTable());
	}
//	/**
//	 * @return int
//	 */
//	private function retryWithProfile(): int{
//		$clockworkSupport = $this->getClockworkSupport();
//		$config = $this->app->make('config');
//		$config->set("clockwork.enable", true);
//		$config->set("clockwork.collect", true);
//		$this->saveClockwork();
//		ConsoleLog::info("Retrying test with profile");
//		try {
//			$this->tearDown();
//			$this->createApplication();
//		} catch (\Throwable $e) {
//			QMLog::info($e->getMessage());
//		}
//		try {
//			QMProfile::startXhguiProfile();
//		} catch (Throwable $e) {
//			QMLog::info("Set development env PROFILE=1 and run locally or export PROFILE=1 at ".Build::getConsoleUrl().
//			            " to enable profiling.\n");
//		}
//		$start = microtime(true);
//		$this->isRetry = true;
//		$this->setMemoryUsageAtStartOfTestInMB();
//		$this->run();
//		$retryDuration = microtime(true) - $start;
//		ConsoleLog::info("Duration on retry was $retryDuration s");
//		return $retryDuration;
//	}
	/**
	 * @throws Throwable
	 */
	protected function tearDown(): void{
		if(in_array($this->getName(), self::$skippedTests)){
			$this->skipTeardown();
			return;
		}
		if($this->hasFailed()){
            $this->callBeforeApplicationDestroyedCallbacks();
			$this->skipTeardown(); // We need to app to be accessible for the saving test results to DB
			return;
			//$this->failurePreTeardown();
		} else{ // We need app instance to report failures using Ignition later in onNotSuccessfulTest. Otherwise destroy to reclaim memory.
			$this->successPreTeardown();
		}
        if (QMProfile::available()) {
            QMProfile::endProfile(); // Do this last because it's slow
        }
		if ($this->getStatus() == 0) {
			parent::tearDown(); // don't teardown app if test failed
		}
	}
	/**
	 * @throws \Throwable
	 * DO NOT REMOVE!
	 * This is the only place we can save the results on exception before the app is destroyed
	 */
	public function runBare(): void{
		try {
			parent::runBare();
		} catch (\Throwable $e) {
			$this->onNotSuccessfulTest($e);
		    le($e);
		}
	}
	private function skipTeardown(): void{
		$this->teardownStartTime = time();
		return;
		parent::tearDown(); // Avoid slow teardown
	}
	/**
	 * @return string
	 */
	private function failurePreTeardown(): string{
		$this->globalPreTeardown();
		$status = $this->getTestStatusString();
		$context = $this->getName()." $status ";
		if($branch = QMAPIRepo::getBranchFromMemoryOrGit()){
			$context .= " on branch $branch";
		}
		$context .= " because ".$this->getStatusMessage();
		SolutionButton::add("Open ".$this->getName()." in PHPStorm", self::getPHPStormUrl());
		ThisComputer::logDebugUrlsForCurrentComputer();
		ConsoleLog::error($context);
		return GlobalLogMeta::setGlobalContext($context);
	}
	private function globalPreTeardown(): void{
		$this->teardownStartTime = time();
		ConsoleLog::info("Tearing down ".$this->getName()."...");
		AppMode::setIsApiRequest(null);
		// This is slow and causes problems IsTestArtifactFile::saveAllIfLocal();
		$this->postTestMemoryAnalysis();
		$this->beforeApplicationDestroyed(function(){
			ConsoleLog::debug("DB::disconnect in beforeApplicationDestroyed");
			DB::disconnect();
			ConsoleLog::debug("logEndOfTest in beforeApplicationDestroyed");
			$this->logEndOfTest();
			ConsoleLog::debug("QMClockwork::logDuration in beforeApplicationDestroyed");
			QMClockwork::logDuration("$this Teardown", $this->teardownStartTime, time());
			ConsoleLog::debug("hasFailed in beforeApplicationDestroyed");
			$hasFailed = $this->hasFailed();
			ConsoleLog::debug("EnvOverride::isLocal in beforeApplicationDestroyed");
			$isLocal = EnvOverride::isLocal();
			ConsoleLog::debug("QMClockwork::collectTests in beforeApplicationDestroyed");
			$collectTests = QMClockwork::collectTests();
			if($hasFailed && $isLocal && $collectTests){
				ConsoleLog::debug("QMClockwork::open in beforeApplicationDestroyed");
				QMClockwork::open();
			}
		});
		$this->warnIfCollectingTestData();
		$_SERVER = self::$initialServer;
	}

	private function postTestMemoryAnalysis(): void{
		ConsoleLog::debug(__FUNCTION__);
		$mb = $this->getMemoryUsageMB();
		self::$memoryForAll[$this->getName()] = round($mb);
		arsort(self::$memoryForAll);
		if(Env::get('OUTPUT_MEMORY_USAGE')){
			QMLog::print(self::$memoryForAll, "Memory Usage (MB)");
			ThisComputer::logMemoryUsage();
		}
	}
	public function getMemoryUsageAtStartOfTestInMB(): int{
		return $this->memoryUsageAtStartOfTestInMB;
	}
	public function setMemoryUsageAtStartOfTestInMB(): void{
		$this->memoryUsageAtStartOfTestInMB = ThisComputer::getMemoryUsageInMB();
	}
	protected function getStartedAt(): string{
		if(!self::$testStartTime){
			le("no test testStartTime");
		}
		return db_date(self::$testStartTime);
	}
	/**
	 * @return int
	 */
	private function getQueryCount(): int{
		$queries = TestQueryLogFile::getQueriesByTest();
		$totalQueryCount = count($queries);
		return $totalQueryCount;
	}
	public function warnIfCollectingTestData(){
		ConsoleLog::debug("QMClockwork::collectTests in warnIfCollectingTestData");
		if(QMClockwork::collectTests()){
			ConsoleLog::info("Collecting clockwork test data...");
		}
	}
	/**
	 * @return string
	 */
	protected function getTestStatusString(): string{
		//QMLog::info(__METHOD__);
		$statusCode = $this->getStatus();
		$statusName = self::STATUS_MAP[$statusCode];
		return $statusName;
	}
	public function getPHPStormUrl(): string{
		return $this->getPhpStormButton()->getUrl();
	}
	public function getPhpStormButton(): PHPUnitButton{
		return PHPUnitButton::getForCurrentTest();
	}
	private function successPreTeardown(): void{
		ConsoleLog::debug(__METHOD__);
		$this->globalPreTeardown();
		if(Env::get(Env::UPDATE_COLLECTOR_DATA)){
			QMDebugBar::saveCollectorData();
		}
	}
	/**
	 * @param TestResult|null $result
	 * @return TestResult
	 */
	public function run(TestResult $result = null): TestResult{
		try {
			$this->setupStartTime = time();
			return parent::run($result);
		} catch (Throwable $exception) {
			$this->failurePreTeardown();
			$this->onNotSuccessfulTest($exception);
			throw $exception;
			//exit(1); // Need exit 1 here or exits with 0 on Travis and we don't know it failed
		}
	}
	/**
	 * This method is called when a test method did not execute successfully.
	 * @param Throwable $t
	 * @throws Throwable
	 * @since Method available since Release 3.4.0
	 */
	protected function onNotSuccessfulTest(Throwable $t): void{
		if(!$t instanceof \PHPUnit\Framework\SkippedTestError){ConsoleLog::once(__METHOD__.": $t");}
		$this->exception = $t;
		if($this->alreadyHandledOnNotSuccessfulTest){
			ConsoleLog::debug(__METHOD__.": not saving because alreadyHandledOnNotSuccessfulTest");
			return;
		}
		$this->alreadyHandledOnNotSuccessfulTest = true;
		self::setCurrentTest($this);
		QMBaseTestCase::setTestException($t);
		if($t instanceof SkippedTestError){
			ConsoleLog::debug(__METHOD__.": Not saving because exception is SkippedTestError");
			$this->logEndOfTest();
			return;
		}
		$this->addFailedTest();
		ExceptionHandler::dumpExceptionToConsole($t);
		try {
			QMIgnition::getUrlOrGenerateAndOpen($t);
			QMAPIRepo::githubComment($t);
			QMAPIRepo::createFailedStatus($t);
		} catch (\Throwable $t) {
			ConsoleLog::debug("Couldn't report test failure because ".$t->getMessage());
		}
		$this->logEndOfTest();
		parent::onNotSuccessfulTest($t);
	}
	/**
	 * @param int $maximumQueries
	 * @throws DiffException
	 * @throws QMFileNotFoundException
	 * @throws TooManyQueriesException
	 */
	public function checkQueryCount(int $maximumQueries){
		$result = $this->assertQueryCountLessThan($maximumQueries);
		$this->assertTrue($result === true, $result);
	}
	/**
	 * @param int|null $max
	 * @return bool
	 * @throws DiffException
	 * @throws QMFileNotFoundException
	 * @throws TooManyQueriesException
	 */
	protected function assertQueryCountLessThan(int $max, bool $checkMin = true): bool{
		$totalQueryCount = $this->getQueryCount();
		ConsoleLog::info("=== $totalQueryCount QUERIES ===");
		if(Env::get(Env::DEBUGBAR_WHILE_TESTING)){
			QMDebugBar::saveCollectorData();
			return true;
		}
		if($max && $totalQueryCount && $totalQueryCount > 1.1 * $max){
			TestQueryLogFile::outputQueryLogs();
			TestQueryLogFile::compareQueryLog();
			throw new TooManyQueriesException("New query count for ".$this->getName().
			                                  " is $totalQueryCount and previous one was $max");
		}
		if($checkMin && $max && $max > 1.5 * $totalQueryCount && EnvOverride::isLocal()){
			$new = $totalQueryCount + 2;
			TestQueryLogFile::outputQueryLogs();
			$msg = "Max queries is $max but total queries are $totalQueryCount.\n\t"."Reduce this number to $new in ".
			       $this->getName();
			if(EnvOverride::isLocal() && $max > 5){
				throw new AssertionFailedError($msg);
			}
			ConsoleLog::info($msg);
		}
		TestQueryLogFile::saveIfLocal();
		return true;
	}

	public function getFileInfo(): SmartFileInfo{
		return new SmartFileInfo(App\Files\FileFinder::getAbsPathToCurrentTest());
	}
	/**
	 * @param string $message
	 * @param array|object $metaData
	 */
	public function logDebug(string $message, $metaData = []){
		if(!Env::APP_DEBUG()){
			return;
		}
		QMLog::debug($this->getLogMetaDataString().$message, $metaData);
	}
	public function queueTestLocally(): PendingDispatch{
		$j = new PHPUnitTestJob(get_class($this), $this->getName(), QMAPIRepo::getCommitShaHash());
		return $j->dispatch()->onQueue('local');
	}
	/**
	 * @param string $key
	 * @param string $html
	 * @param bool $ignoreNumbers
	 * @param object|null $obj
	 */
	public function compareHtmlFragment(string $key, string $html, bool $ignoreNumbers = false, object $obj = null){
		$html = HtmlHelper::renderReportWithTailwind($html, $obj);
		$this->compareHtmlPage($key, $html, $ignoreNumbers);
	}
	/**
	 * @param string $key
	 * @param string $html
	 * @param bool $ignoreNumbersAndTimes
	 */
	public function compareHtmlPage(string $key, string $html, bool $ignoreNumbersAndTimes = false){
		$html = HtmlHelper::stripDebugInfo($html);
		$html = QMStr::replace_between_and_including($html, 'DB_URL=', '"', '"');
		$html = QMStr::replace_between($html, '"timezone":', ',"trackLocation":true',
		                               '"Etc\/UTC","timeZoneOffset":300,"token":"testuser|1606764882|86db9a3d39d98100ae332be88d45d355|quantimodo"');
		try {
			HtmlFile::assertSameHtml($key, $html, '', $ignoreNumbersAndTimes);
		} catch (Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			if(Env::get(Env::UPDATE_HTML_FIXTURES)){
				TestJob::testPHPUnitFunction(AppMode::getCurrentTestClass(), $this->getName());
			}
			throw $e;
		}
		try {
			HtmlHelper::validateHtmlPage($html, $key);
		} catch (InvalidStringException $e) {
			le($e);
		}
		self::assertTrue(true);
	}
	public function compareLastEmail(){
		$sentMail = SentEmail::query()->orderBy(SentEmail::CREATED_AT, 'desc')->first();
		if(!$sentMail){
			le("no sent email!");
		}
		$this->compareEmailHtml($sentMail->slug ?? $sentMail->type, $sentMail->content);
	}
	/**
	 * @param string $key
	 * @param string $html
	 * @param bool $ignoreNumbersAndTimes
	 */
	public static function compareEmailHtml(string $key, string $html, bool $ignoreNumbersAndTimes = false){
		if(stripos($html, "<html") === false){
			$html = view('email.email-layout', ['content' => $html]);
		}
		$html = HtmlHelper::stripDebugInfo($html);
		$html = QMStr::replace_between_and_including($html, 'DB_URL=', '"', '"');
        HtmlFile::assertSameHtml($key, $html, '', $ignoreNumbersAndTimes);
		HtmlHelper::validateHtml($html, $key);
		self::assertTrue(true);
	}
	public function assertTrackingReminderNames(array $expected, int $userId = 1){
		$reminders = TrackingReminder::whereUserId($userId)->get();
		$actual = [];
		foreach($reminders as $reminder){
			$actual[] = $reminder->getVariableName();
		}
		$this->assertArrayEquals($expected, $actual, TrackingReminder::generateDataLabIndexUrl());
	}
	public function getIgnitionButton(Throwable $e): IgnitionButton{
		return new IgnitionButton($e);
	}
	public function getTooltip(): string{
		return $this->getName()." on branch ".QMAPIRepo::getBranchFromMemoryOrGit();
	}
	/**
	 * @return int
	 * Set memory at start as the baseline because it accumulates while running a suite
	 */
	public function getApiMemoryLimit(): int{
		return $this->getMemoryUsageAtStartOfTestInMB() + ThisComputer::API_MEMORY_LIMIT_MB;
	}
	/**
	 * Visit the given URI with a POST request.
	 * @param string $uri
	 * @param array $data
	 * @param array $headers
	 * @return array
	 */
	public function postWithClientId(string $uri, array $data = [], array $headers = []): array{
		$data['clientId'] = App\Properties\Base\BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;
		$r = $this->post($uri, $data, $headers);
		$this->assertNoException($r);
		$body = $r->json();
		$this->assertTrue($body['success'], "Success is not true in this response: ".QMLog::print_r($body, true));
		return $body;
	}
	/**
	 * @param string $uri
	 * @param array $data
	 * @param array $headers
	 * @return TestResponse
	 */
	public function post($uri, array $data = [], array $headers = []): TestResponse{
		$this->logInfo("POST $uri", $data);
        //$this->postToProd($uri, $data, $headers);
		return $r = $this->testResponse = parent::post($uri, $data, $headers);
	}
    /**
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return array
     */
    protected function postToProd(string $uri, array $data = [], array $headers = []): array {
        $this->logInfo("POST $uri", $data);
        $client = new Client();
        $data['client_id'] = Env::get('TEST_CLIENT_ID');
        $data['client_secret'] = Env::get('TEST_CLIENT_SECRET');
        $response = $client->post('https://v7.curedao.org/'.$uri, [
            'headers' => $headers,
            'json' => $data,
        ]);
        $body = json_decode($response->getBody()->getContents(), true);
        return $body;
    }
	/**
	 * @param string $message
	 * @param array|object $metaData
	 */
	public function logInfo(string $message, $metaData = []){
		QMLog::info($this->getLogMetaDataString().$message, $metaData);
	}
	private function assertNoException(TestResponse $r){
		if(isset($r->exception)){
			$this->assertNull($r->exception, "Exception from API response is: ".$r->exception->getMessage());
		}
	}
	/**
	 * @return User
	 */
	public function actingAsUserOne(): User{
		return $this->actingAsUserId(1);
	}
	/**
	 * @param int|null $userId
	 * @return User
	 */
	protected function actingAsUserId(?int $userId = 1): ?User{
        if($userId === null){
            $guard = Mockery::mock(Guard::class);
            $guard->expects('check')
                ->andReturns(false);
            Auth::shouldReceive('guard')
                ->andReturns($guard);
            return null;
        }
        $u = User::findInMemoryOrDB($userId);
        if(!$u){
            le("User $userId not found");
        }
		$this->actingAs($u);
		return $u;
	}
	/**
	 * @param $path
	 * @param $expectedText
	 * @return TestResponse
	 */
	public function getResponseContains($path, $expectedText): TestResponse{
		$content = $this->getRequest($path);
		$this->assertContains($expectedText, $content);
		return $this->testResponse;
	}
	/**
	 * @param $path
	 * @param int $expectedCode
	 * @return false|string
	 */
	private function getRequest($path, int $expectedCode = 200){
		$r = $this->get($path);
		$this->assertNoException($r);
		$this->assertEquals($expectedCode, $r->getStatusCode(), $r->getContent());
		$content = $r->getContent();
		return $content;
	}
	/**
	 * @param string $uri
	 * @param array $headers
	 * @return TestResponse
	 */
	public function get($uri, array $headers = []): TestResponse{
		$this->logInfo("GET ".qm_url($uri));
		$r = $this->testResponse = parent::get($uri, $headers);
		AppMode::setIsApiRequest(false);
		return $this->testResponse;
	}
	/**
	 * @param $path
	 * @param $expectedText
	 * @param int $expectedCode
	 * @return TestResponse
	 */
	public function getResponseDoesNotContain($path, $expectedText, int $expectedCode = 200): TestResponse{
		$content = $this->getRequest($path, $expectedCode);
		if($content){
			$this->assertNotContains($expectedText, $content);
		}
		return $this->testResponse;
	}
	/**
	 * @param $needle
	 * @param $haystack
	 * @param string $message
	 * @param bool $ignoreCase
	 * @param bool $checkForObjectIdentity
	 * @param bool $checkForNonObjectIdentity
	 * @throws InvalidStringException
	 */
	public static function assertNotContains($needle, $haystack, string $message = '', bool $ignoreCase = false,
	                                         bool $checkForObjectIdentity = true,
	                                         bool $checkForNonObjectIdentity = false): void{
		if(is_array($haystack) || ($haystack instanceof Traversable)){
			Assert::assertNotContains($needle, $haystack, $message);
		} elseif(is_string($haystack)){
			QMStr::assertStringDoesNotContain($haystack, $needle, $message, $ignoreCase);
		} else{
			throw new \InvalidArgumentException('array, traversable or string');
		}
		self::assertTrue(true);
	}
	/**
	 * @param string $path
	 * @param string $expectedRedirectPath
	 * @return TestResponse
	 */
	public function assertGetRedirect(string $path,
	                                  string $expectedRedirectPath = 'register',
	                                  string $message = null): TestResponse{
		if(!ExceptionHandler::$expectedRequestException){
			self::setExpectedRequestException(UnauthorizedException::class);
			self::setExpectedRequestException(\Illuminate\Auth\AuthenticationException::class);
		}
		$r = $this->get($path);
		if(!$message){
			$message = '';
		}
		$content = $r->getContent();
		$message .= "\nRESPONSE CONTENT: ".QMStr::truncate($content, 240);
		$this->assertStatusCodeEquals(302, $r, $path, 'GET', $message);
		$actualURL = $r->headers->get('Location');
		$this->assertContains($expectedRedirectPath, $actualURL);
		return $r;
	}
	/**
	 * @param string $getPath
	 * @return TestResponse
	 */
	public function assertGet200(string $getPath): TestResponse{
		$response = $this->get($getPath);
		$this->assertStatusCodeEquals(200, $response, $getPath, 'GET', $response->getContent());
		return $this->testResponse;
	}
	/**
	 * @param string $postPath
	 * @param $postData
	 * @param string $expectedError
	 * @return TestResponse
	 */
	public function assertErrorMessageContains(string $postPath, $postData, string $expectedError): TestResponse{
		$r = $this->decodeAndPost($postPath, $postData);
		$code = $r->getStatusCode();
		$content = $r->getContent();
		$this->assertEquals(400, $code, $content);
		$this->assertContains($expectedError, $content);
		return $this->testResponse;
	}
	/**
	 * Visit the given URI with a POST request.
	 * @param string $uri
	 * @param array|string $data
	 * @param array $headers
	 * @return TestResponse
	 */
	public function decodeAndPost(string $uri, $data = [], array $headers = []): TestResponse{
		if(is_string($data)){
			$data = json_decode($data, true);
		}
		return $this->post($uri, $data, $headers);
	}
	/**
	 * @param string $postPath
	 * @param $postData
	 * @param string $expectedRedirectPath
	 * @return TestResponse
	 */
	public function assertPostRedirect(string $postPath, $postData, string $expectedRedirectPath): TestResponse{
		$r = $this->decodeAndPost($postPath, $postData);
		$code = $r->getStatusCode();
		if($code !== 302){
			$r = $this->decodeAndPost($postPath, $postData);
		}
		$code = $r->getStatusCode();
		$content = $r->getContent();
		$this->assertEquals(302, $code, $content);
		$this->assertContains("Redirecting to ".$expectedRedirectPath, $content);
		return $this->testResponse;
	}
	/**
	 * @return Throwable
	 */
	public function getException(): ?Throwable{
		return $this->exception;
	}
	/**
	 * @param Throwable $exception
	 */
	public function setException(Throwable $exception): void{
		$this->exception = $exception;
	}
	public function getProfileUrl(): ?string {
		return $this->profileUrl;
	}
	/**
	 * @param string $profileUrl
	 */
	public function setProfileUrl(string $profileUrl): void{
		$this->profileUrl = $profileUrl;
	}
	protected function setUp(): void{
		self::setCurrentTest($this);
		if($this->weShouldSkip()){
			return;
		}
		//$this->disconnectDB();
		parent::setUp();         // CreatesApplication is called in parent
		$this->logStartOfTest(); // Must come after CreatesApplication
		//QMLogLevel::setFromDotEnv();
		ConsoleLog::debug(__METHOD__);
		// Need to set app.debug false so it's consistent with production and livewire scripts are minified
		config(['app.debug' => false]);
		ThisComputer::validatePHPVersion();
		ThisComputer::setPrecision();
		CacheManager::flushTestCache();
		if(!AppMode::isStagingUnitTesting()){TestDB::resetTestDB();}
		self::setupProfiling();
		$this->setupMemory();
		ThisComputer::setMaximumPhpExecutionTimeLimit(0, false);  // Prevents test timeout
		self::setupDBLogging();
		self::setupErrorReporting();
		self::setExpectedRequestException(null);
		//if(method_exists($this, 'setUpClockwork')){$this->setUpClockwork();}
		self::$initialServer = $_SERVER;
		$this->resetTestStartTime();
		$this->setUpClockwork();
	}
	/**
	 * @return bool
	 */
	protected function weShouldSkip(): bool{
		$this->setupMemory(); // Avoid TypeError : Return value of Tests\QMBaseTestCase::getMemoryUsageAtStartOfTestInMB() must be of the type int, null returned
		$date = static::DISABLED_UNTIL;
		if($date && time() < strtotime($date)){
			$str = 'Disabled until '.$date;
			if(static::REASON_FOR_SKIPPING){
				$str .= " because ".static::REASON_FOR_SKIPPING;
			}
			$this->skipTest($str);
		}
		return false;
	}
	private function setupMemory(): void{
		$this->setMemoryUsageAtStartOfTestInMB();
		ThisComputer::outputMemoryUsageIfEnabledOrDebug();
		ThisComputer::setWorkerMemoryLimit();              // Use 1G generally in workers and set to 256M when making API requests to simulate reality
	}
	/**
	 * @param string $message
	 * USE THIS INSTEAD OF markTestSkipped to avoid slow teardown
	 */
	protected function skipTest(string $message = ''): void{
		$this->setCurrentTest($this);
		$this->getTestResultObject()->beStrictAboutTestsThatDoNotTestAnything(false);
		self::$skippedTests[] = $this->getName();
		//$this->logEndOfTest();
		parent::markTestSkipped($message);
	}
	protected function logUrlToTest(): void{
		ConsoleLog::info($this->getLinkToTest());
	}
	/**
	 * @return string
	 */
	public function getLinkToTest(): string{
		return self::getUrlToFunction($this->getName());
	}
	private function setupProfiling(): void{
		if(!AppMode::isTravisOrHeroku()){
			QMProfile::profileIfEnvSet(false);
		}
		if(EnvOverride::getFormatted(Env::DEBUGBAR_WHILE_TESTING)){ // Quite slow for testing Laravel API requests
			QMDebugBar::enable();
		}
		$this->disableXDebugIfNecessary();
	}
	public function disableXDebugIfNecessary(): void{
		if(AppMode::isJenkins()){
			XDebug::disable();
		}
	}
	private static function setupDBLogging(): void{
		TestDB::setBlackListedTables([]);
		Writable::logDbNameAndHost(true);
		DB::enableQueryLog();
		QMDB::assertLogging();
		QMDB::flushQueryLogs(__METHOD__);
	}
	private static function setupErrorReporting(): void{
		ini_set('display_errors', 1);
		error_reporting(E_ALL ^ E_NOTICE);
	}
	protected function resetTestStartTime(): void{
		self::$testStartTime = time();
	}
	/**
	 * @throws \Throwable
	 */
	protected function runTest(){
		QMClockwork::logDuration("$this Setup", $this->setupStartTime, time());
		QMIgnition::queryRecorder()->reset();
		return parent::runTest();
	}
	protected function setAdminUser(){
		$this->setAuthenticatedUser(UserIdProperty::USER_ID_MIKE);
	}
	/**
	 * Set user authenticated in the application.
	 * @param int|null $userId The QuantiModo unique ID of the user to authenticate.
	 * @return QMUser
	 */
	protected function setAuthenticatedUser(?int $userId = 1): ?QMUser{
		if($userId){
			$user = QMUser::findInDB($userId);
			QMAuth::setUserLoggedIn($user, true);
			QMLog::debug("Switched to user $userId");
            $this->actingAs($user->getUser());
			$this->assertUserIsLoggedIn($userId);
		} else{
			if(QMAuth::getQMUserIfSet()){
				QMAuth::logout(__METHOD__);
			}
			$user = null;
		}
		$this->seeIfUserIsLoggedIn();
		return $user;
	}
	/**
	 * @param string|null $key
	 * @return array|object
	 */
	protected function lastResponseData(string $key = null){
		$data = json_decode($this->testResponse->getContent());
		if($key){
			return $data->$key;
		}
		return $data;
	}
	/**
	 * @deprecated Doesn't seem to work and crashes when setting typed properties to null
	 */
	protected function freeUpMemoryAfterTest(): void{
		QMLog::info(__METHOD__);
		$reflectionObject = new ReflectionObject($this);
		foreach($reflectionObject->getProperties() as $prop){
			if(!$prop->isStatic() && 0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit_')){
				$prop->setAccessible(true);
				$prop->setValue($this, null);
			}
		}
		$this->setMemoryUsageAtStartOfTestInMB();
	}
	/**
	 * @param int $expected
	 * @param array $array
	 */
	protected function assertCountAndPrintIfFalse(int $expected, array $array){
		$count = count($array);
		if($count !== $expected){
			SlimStagingTestCase::obfuscateAndPrintR($array);
		}
		$this->assertCount($expected, $array);
	}
	/**
	 * @param $object
	 */
	protected static function obfuscateAndPrintR($object){
		$object = SecretHelper::obfuscateObject($object);
		\App\Logging\QMLog::print_r($object);
	}
	/**
	 * Asserts the number of elements of an array, Countable or Traversable.
	 * @param int $expectedCount
	 * @param iterable|Collection|array $haystack
	 * @param string $message
	 */
	public static function assertCount(int $expectedCount, $haystack, string $message = ''): void{
		if(!$haystack instanceof Countable && !is_iterable($haystack)){
			throw new \InvalidArgumentException('Argument 2 passed to ' . __METHOD__ .
                ' must be an instance of Countable or Traversable, array given');
		}
		$actual = count($haystack);
		if($actual !== $expectedCount){
			$names = [];
			foreach($haystack as $key => $value){
				if(is_object($value) || is_array($value)){
					$name = BaseNameProperty::pluck($value);
					if($name){
						$names[] = $name;
					}
				}
			}
			if($names){
				$message .= "\n\tGot: \n\t-" . implode("\n\t-", $names);
			}
			throw new AssertionFailedError("
            Expected $expectedCount but got $actual
            $message");
		}
		parent::assertCount($expectedCount, $haystack, $message);
	}
	protected function deleteFile(string $path){
		FileHelper::deleteFile($path, $this->getName());
		static::assertFileDoesNotExist($path);
	}
	/**
	 * @param string $path
	 * @return string|null
	 * @throws QMFileNotFoundException
	 */
	protected function getFileContents(string $path): ?string{
		return FileHelper::getContents($path);
	}
	/**
	 * @param \Throwable $e
	 * @param string $needle
	 * @throws \Throwable
	 */
	protected function skipIfExceptionContains(Throwable $e, string $needle): void{
		if(stripos($e->getMessage(), $needle) !== false){
			$this->skipAndMarkRisky($e);
		} else{
			throw $e;
		}
	}
	/**
	 * @param string|\Throwable $reason
	 * @param array $meta
	 */
	protected function skipAndMarkRisky($reason, array $meta = []): void{
		if($reason instanceof Throwable){
			$meta['exception'] = $reason;
			$reason = $reason->getMessage();
		}
		$name = $this->getName();
		QMLog::error("Skipping $name because $reason", $meta, false);
		$this->assertTrue(true);
		$this->markAsRisky();
		$this->skipTest($reason);
	}
	/**
	 * @param object $obj
	 * @param string $propertyName
	 */
	protected function assertPropertyGreaterThanAnHourAgo(object $obj, string $propertyName): void{
		$hourAgoAt = db_date(time() - 60);
		$at = $obj->$propertyName;
		if(!$at){
			throw new LogicException("$propertyName not found on ".\App\Logging\QMLog::print_r($obj, true));
		}
		if($hourAgoAt > $at){
			$this->assertGreaterThan($hourAgoAt, $at, "Created at is $at on ".\App\Logging\QMLog::print_r($obj, true));
		}
	}
    /**
     * @return void
     */
    protected function createGlobalVariableRelationships(): void
    {
        $v = Variable::first();
        $this->assertIsInt($v->id);
        $this->createTreatmentOutcomeMeasurementsFor2Users();
        $this->checkUserVariables();
        $this->analyzeUsersAndCheckCorrelations();
        //$this->checkCommonVariables();
        $this->checkGlobalVariableRelationships();
    }
    /**
     * @param int $userId
     * @return void
     */
    protected function createTreatmentOutcomeMeasurements(int $userId = 1){
        try {
            $this->createMoodMeasurements($userId);
            $this->createTreatmentMeasurements($userId);
        } catch (InvalidVariableValueException|IncompatibleUnitException $e) {
            le($e);
        }
    }
    /**
     * @return QMUser[]
     */
    protected function analyzeUsers(): array {
        $analyzed = QMUser::analyzeWaitingStaleStuck();
        foreach($analyzed as $user){
            $calculated = UserNumberOfPatientsProperty::calculate($user);
            $attr = $user->l()->number_of_patients;
            $this->assertEquals($calculated, $attr);
        }
        return $analyzed;
    }
    public function checkUserVariables(){
        $stale = QMUserVariable::whereWaiting()->getLaravelModels();
        $names = $stale->pluck('name');
        $this->assertCount(4, $stale, 
                           "Should get 4 user variables but got ".\App\Logging\QMLog::print_r($names, true));
        $analyzed = QMUserVariable::analyzeWaitingStaleStuck();
        $this->assertCount(4, $analyzed, "Should have analyzed 4");
        $userVariables = UserVariable::all();
        $this->assertCount(4, $userVariables);
        foreach($userVariables as $uv){
            $charts = $uv->getChartGroup();
            $charts->getOrSetHighchartConfigs();
            //$this->assertNotNull($uv->charts, "No charts on $uv");
            $this->assertTrue($charts->highchartsPopulated(), "No highchartsPopulated on $uv");
            $this->assertNotNull($uv->analysis_ended_at);
        }
        $this->assertEquals(0, UserVariable::whereNotNull(UserVariable::FIELD_WP_POST_ID)->count());
    }
    public function checkCommonVariables(){
        //$alreadyAnalyzed = Variable::query()->whereNotNull(Variable::FIELD_ANALYSIS_ENDED_AT)->count();
        //$this->assertEquals(0, $alreadyAnalyzed);
        $qb = QMCommonVariable::whereAnalysisStale();
        $qb->countAndLog();
        $stale = $qb->getLaravelModels();
        $staleVariable = $stale->first();
        if(!$staleVariable){
            $qb = QMCommonVariable::whereAnalysisStale();
            $qb->countAndLog();
            $stale->first();
            $variables = Variable::whereIn('id',
                [BupropionSrCommonVariable::ID, OverallMoodCommonVariable::ID])->get();
            le(\App\Logging\QMLog::print_r($variables->toArray(), true));
        }
        $this->assertNotNull($staleVariable, "No stale variables");
        $unit = $staleVariable->getCommonUnit();
        $this->assertNotNull($unit);
        $names = $stale->pluck('name');
        $this->assertCount(2, $stale, "Only 2 variables should be stale, but got ".\App\Logging\QMLog::print_r($names, true));
        $this->assertEquals(2, QMCommonVariable::whereAnalysisStale()->count());
        $this->assertEquals(0, QMCommonVariable::whereWaiting()->count());
        $this->assertEquals(0, QMCommonVariable::whereNeverStartedAnalyzing()->count());
        $this->assertEquals(0, QMCommonVariable::whereStuck()->count());
        $analyzed = QMCommonVariable::analysisJobsTest(30);
        $this->assertEquals(0, Variable::whereNotNull(Variable::FIELD_WP_POST_ID)->count());
        $this->assertCount(2, $analyzed);
        $outcome = $this->getMoodQMUserVariable(1)->getCommonVariable();
        $treatment = $this->getTreatmentUserVariable(1)->getCommonVariable();
        $v = Variable::find($outcome->id);
        $this->assertNotNull($v->charts);
        $this->assertTrue($v->getChartGroup()->highchartsPopulated(), "No highchartsPopulated on $v");
        $this->assertNotNull(Variable::find($treatment->id)->charts);
    }
    public function analyzeUsersAndCheckCorrelations(){
        /** @var int[] $correlatable */
        $correlatable = UserVariable::correlatableUserVariableIds()->pluck("id");
        $this->assertCount(4, $correlatable);
        $uv = UserVariable::findInMemoryOrDB($correlatable[0]);
        $ids = $uv->getUserVariableIdsToCorrelateWith();
        $this->assertCount(1, $ids);
        $toCorrelateWith = QMUserVariable::find($ids[0]);
        $this->assertNotEquals($toCorrelateWith->name, $uv->name);
        $correlatable = UserVariable::correlatableUserVariableIds()
            ->whereNull(UserVariable::FIELD_LAST_CORRELATED_AT)
            ->get();
        $this->assertCount(4, $correlatable);
        $qb = QMUser::excludeUnAnalyzableUsers(QMUser::whereNeverStartedAnalyzing());
        $users = $qb->getBaseModels();
        $neverAnalyzed = $users->pluck('id');
        $this->assertContains(1, $neverAnalyzed->toArray());
        JobTestCase::setMaximumJobDuration(0);
        $usersAnalyzed = $this->analyzeUsers();
        $this->assertEquals(0, UserVariableRelationship::whereNotNull(UserVariableRelationship::FIELD_WP_POST_ID)->count(),
            "We should only post if lots of users or someone up-voted");
        $analyzed = collect($usersAnalyzed)->pluck('id')->all();
        $this->assertContains(1, $analyzed);
        //$this->assertCount(2, $usersAnalyzed);
        $correlations = UserVariableRelationship::all();
        $this->assertCount(2, $correlations, UserVariableRelationship::getDataLabIndexUrl());
        foreach($correlations as $c){
            $this->assertNotNull($c->analysis_ended_at, "$c analysis never ended? Attributes:". $c->toCLITable());
            $post = $c->findInMemoryOrNewQMStudy()->getWpPostIfExists();
            $this->assertNull($post, "We should only post if lots of users or someone up-voted");
            $charts = $c->getChartGroup();
            if(!$charts->highchartsPopulated()){
                $charts = $c->getChartGroup();
                $charts->highchartsPopulated();
            }
            $this->assertTrue($charts->highchartsPopulated(), "No highchartsPopulated on $c");
        }
        $this->assertCorrelationAnalysisCompleted();
    }
    /**
     * @param UserVariableRelationship|GlobalVariableRelationship|Variable|UserVariable|BaseModel $m
     */
    public function assertAnalysisCompleted(BaseModel $m){
        $this->assertNull($m->deleted_at, "$m is deleted");
        $this->assertNotNull($m->analysis_started_at, "$m analysis never started");
        $this->assertNotNull($m->analysis_ended_at, "$m analysis never ended");
    }
    /**
     * @return void
     */
    private function assertCorrelationAnalysisCompleted(): void{
        $correlations = UserVariableRelationship::all();
        foreach($correlations as $correlation){
            $this->assertAnalysisCompleted($correlation);
        }
    }
    public function checkGlobalVariableRelationships(): void{
        sleep(1); // Make sure `global_variable_relationships`.`analysis_started_at` < ?
        $this->assertCorrelationAnalysisCompleted();
        $analyzed = QMGlobalVariableRelationship::analyzeWaitingStaleStuck();
        $this->assertCount(1, $analyzed,
            "We should have 1 stale GLOBAL Variable Relationship because we should have updated newest_data_at on ".
            "GLOBAL variable relationship after analyzing 2nd USER correlation");
        $correlations = GlobalVariableRelationship::all();
        $this->assertCount(1, $correlations);
        /** @var GlobalVariableRelationship $ac */
        $ac = $correlations->first();
        $this->assertNotNull($ac->charts);
        $post = $ac->getWpPostIfExists();
        $this->assertNull($post, "We should only publish up-voted studies");
        $charts = $ac->getChartGroup();
        $this->assertTrue($charts->highchartsPopulated(), "No highchartsPopulated on $ac");
    }
	/**
	 * @param int $userId
	 * @return QMMeasurement[]
	 * @throws InvalidVariableValueException
	 */
	protected function createMoodMeasurements(int $userId = 1): array{
		BaseClientIdProperty::setInMemory(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
		$mood = $this->getMoodQMUserVariable($userId);
		$uv = $mood->l();
		$uncombined = $this->generateHighLowMeasurementsForLast120Days();
		$effectMeasurementItems = [];
		foreach($uncombined as $m){
			$effectMeasurementItems[] = $uv->newMeasurementData([
                Measurement::FIELD_START_TIME => $m->startTime,
                Measurement::FIELD_VALUE => $m->originalValue,
                Measurement::FIELD_ORIGINAL_VALUE => $m->originalValue,
                Measurement::FIELD_CLIENT_ID => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
                Measurement::FIELD_SOURCE_NAME => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
            ]);
		}
		$measurements = $uv->bulkMeasurementInsert($effectMeasurementItems);
		$str = $uv->getDBModel()->getCorrelationDataRequirementAndCurrentDataQuantityString();
		$this->assertNotNull($str);
		return $measurements;
	}
	/**
	 * @param int $userId
	 * @return QMUserVariable
	 */
	protected function getMoodQMUserVariable(int $userId = 1): QMUserVariable{
		$mood = $this->getMoodUserVariable($userId)->getQMUserVariable();
		$cv = $mood->getCommonVariable();
		$cv->l()->assertHasStatusAttribute();
		return $mood;
	}
	/**
	 * @param int $userId
	 * @return UserVariable
	 */
	protected function getMoodUserVariable(int $userId = 1): UserVariable{
		return OverallMoodCommonVariable::instance()->getUserVariable($userId);
	}
	/**
	 * @return QMMeasurement[]
	 */
	protected function generateHighLowMeasurementsForLast120Days(): array{
		$measurements = [];
		for($i = -120; $i < -90; $i++){
			$measurements[] = new QMMeasurement($this->getTimeMinusXDays(-1 * $i), 1);
		}
		for($i = -90; $i < -60; $i++){
			$measurements[] = new QMMeasurement($this->getTimeMinusXDays(-1 * $i), 5);
		}
		for($i = -60; $i < -30; $i++){
			$measurements[] = new QMMeasurement($this->getTimeMinusXDays(-1 * $i), 1);
		}
		for($i = -30; $i < 0; $i++){
			$measurements[] = new QMMeasurement($this->getTimeMinusXDays(-1 * $i), 5);
		}
		return $measurements;
	}
	/**
	 * @param int $days
	 * @return float|int
	 */
	protected function getTimeMinusXDays(int $days){
		$baseTime = strtotime("2020-01-01");
		return $baseTime - $days * 86400;
	}
	/**
	 * @param int $userId
	 * @return QMUser
	 */
	protected function getOrSetAuthenticatedUser(int $userId): QMUser{
		return $this->setAuthenticatedUser($userId);
	}
	protected function skipIfNotLocal(string $message = ''){
		if(!EnvOverride::isLocal()){
			$this->skipTest("Skipping because not local. $message");
		}
	}
	/**
	 * @param bool $asArray
	 * @return array|object
	 */
	protected function getJsonDecodedContent(bool $asArray = true){
		$r = $this->getTestResponse();
		$contentType = $r->headers->get('Content-Type');
		$this->assertEquals('application/json', $contentType);
		$content = $r->getContent();
		return json_decode($content, $asArray);
	}
	/**
	 * @return TestResponse
	 */
	protected function getTestResponse(): TestResponse{
		return $this->testResponse;
	}
	/**
	 * @param int $userId
	 * @return QMUser
	 */
	protected function getQMUser(int $userId = 1): QMUser{
		$u = QMUser::find($userId);
		QMAuth::setUserLoggedIn($u, true);
		return $u;
	}
	/**
	 * @param string $method
	 * @param string $uri
	 * @param array $data
	 * @param array $headers
	 * @return TestResponse
	 */
	protected function jsonAsUser18535(string $method, string $uri, array $data = [],
	                                   array  $headers = []): TestResponse{
		$this->actingAsUserId(18535);
		return $this->testResponse = $this->json($method, $uri, $data, $headers);
	}
    /**
     * Asserts that two variables are equal.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertEquals($expected, $actual, string $message = ''): void
    {
        if(!is_array($expected) && !is_object($expected)){
			if(is_string($expected) && is_string($actual)){
				if($expected !== $actual){
					self::fail("Actual string: $actual
	 does not equal 
	 expected string: $expected. 
	 Message: $message");
				}
			}
            parent::assertEquals($expected, $actual, $message);
            return;
        }
        $constraint = new IsEqual($expected);
        try {
            static::assertThat($actual, $constraint, $message);
        } catch (\Throwable $e) {
            $message .= "\n\nACTUAL:\n" . var_export($actual, true);
            static::assertThat($actual, $constraint, $message);
        }
    }
    /**
     * Call the given URI with a JSON request.
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Testing\TestResponse
     */
    public function json($method, $uri, array $data = [], array $headers = []): TestResponse
    {
		$origin = Env::getAppUrl();
		$url = $this->prepareUrlForRequest($uri);
		if($method === 'GET'){$url .= '?' . http_build_query($data);}
		QMLog::info("$method $url");
        return $this->testResponse = parent::json($method, $uri, $data, $headers);
    }
    /**
     * @param string $method
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return TestResponse
     */
    protected function jsonAsUser1(string $method, string $uri, array $data = [],
                                       array  $headers = []): TestResponse {
        $this->actingAsUserId(1);
        return $this->testResponse = $this->json($method, $uri, $data, $headers);
    }
    /**
     * @param string $method
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return TestResponse
     */
    protected function jsonAsApp(string $method, string $uri, array $data = [],
                                       array  $headers = []): TestResponse{
        //$this->actingAsUserId(18535);
        $headers['X-Client-ID'] = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;
        $headers['X-Client-Secret'] = BaseClientSecretProperty::TEST_CLIENT_SECRET;
        return $this->json($method, $uri, $data, $headers);
    }
	/**
	 * @param string $uri
	 * @param array $data
	 * @param array $headers
	 * @return TestResponse
	 */
	protected function getAsApp(string $uri, array $data = [], array  $headers = []): TestResponse{
		return $this->jsonAsApp("GET", $uri, $data, $headers);
	}
	/**
	 * @param string $uri
	 * @param array $data
	 * @param array $headers
	 * @return TestResponse
	 */
	protected function postAsApp(string $uri, array $data = [], array  $headers = []): TestResponse{
		return $this->jsonAsApp("POST", $uri, $data, $headers);
	}
	/**
	 * @param int $userId
	 * @return UserVariable
	 */
	protected function getOverallMoodUserVariable(int $userId = 1): UserVariable{
		$uv = UserVariable::findOrCreateByNameOrVariableId($userId, OverallMoodCommonVariable::ID);
		return $uv;
	}
	/**
	 * @return float|int
	 */
	private function getMemoryUsageMB(): float {
		$mb = ThisComputer::getMemoryUsageInMB() - $this->getMemoryUsageAtStartOfTestInMB();
		return $mb;
	}
	/**
	 * @return void
	 */
	private function logEndOfTest(): void {
		QMLog::logEndOfProcessIfStarted($this->getName()); // Must come before application destroyed
	}
	private function logStartOfTest(){
		QMLog::logStartOfProcess($this->getName());
		if(!EnvOverride::isLocal()){
			$this->logUrlToTest();
		}
	}
	/**
	 * @param bool $stripExt
	 * @return string
	 */
	public function getFilename(bool $stripExt = false): string {
		return FileHelper::pathToName($this->getPathToTest(), $stripExt);
	}
    public static function markTestSkipped(string $message = ''): void
    {
        self::$skippedTests[] = AppMode::getCurrentTestName();
        parent::markTestSkipped($message);
    }
    protected function assertPathContains(string $needle, string $haystack): void {
        $this->assertContains(str_replace("/", DIRECTORY_SEPARATOR, $needle),
            str_replace("/", DIRECTORY_SEPARATOR, $haystack));
    }
    protected function logout(): void {
	    $this->app['auth']->guard('web')->logout(); // or omit `->guard('api')` part
        QMAuth::logout(__FUNCTION__);
        $this->assertGuest();
    }
    protected static function assertFileNotContains(string $path, string $needle): void {
        $content = FileHelper::getContents($path);
        static::assertStringNotContainsString($needle, $content);
    }
    protected function dumpSeeds(): void {
        TestDB::generateSeeds($this->getName());
    }
	/**
	 * @return void
     * @deprecated User TestDB::copyFixtureToStorage();
	 */
	protected function createTables(): void{
		$this->artisan('migrate', [
			'--path' => 'database/migrations/tables',
		]);
	}
	/**
	 * @return void
     * @deprecated User TestDB::copyFixtureToStorage();
	 */
	protected function createForeignKeys(): void{
		$this->artisan('migrate', [
			'--path' => 'database/migrations/fk',
		]);
	}
	/**
	 * @return void
	 */
	protected function seeIfUserIsLoggedIn(): bool {
		$user = QMAuth::getUser();
		if($user){
			QMLog::info("User is logged in as " . $user->getUsername());
			return true;
		} else{
			QMLog::info("User is not logged in");
			return false;
		}
	}
	protected function assertUserIsLoggedIn(?int $userId = null): void {
		$this->assertAuthenticated();
		$u = Auth::user();
		if($userId){
			$this->assertEquals($userId, $u->id);
		}
		$this->assertNotNull($u);
		$this->assertTrue($this->seeIfUserIsLoggedIn());
		if($userId){
			$u = QMAuth::getUser();
			$this->assertEquals($userId, $u->id);
		}
	}
	/**
	 * @param int $userId
	 * @return UserVariable[]|Collection
	 */
	protected function getUserVariables(int $userId = 1): Collection {
		return UserVariable::whereUserId($userId)->get();
	}
	/**
	 * @param int $userId
	 * @param array $expectedUserVariables
	 * @return void
	 */
	protected function assertUserVariables(int $userId, array $expectedUserVariables): void{
		$userVariables = $this->getUserVariables($userId);
		$this->assertNames($expectedUserVariables, $userVariables);
	}
	protected function createMeasurement(string $variableName, float $value, $timeAt = null, int $userId = 1){
		$uv = UserVariable::findOrCreateByNameOrId($userId, $variableName);
		$uv->saveMeasurement($value, $timeAt);
	}
	protected function getBackPainUserVariable(int $userId = 1){
		return UserVariable::findByNameOrId($userId, BackPainCommonVariable::NAME);
	}
	public function getExpectedException(): ?string{
		return ExceptionHandler::$expectedRequestException;
	}
	public static function getCurrentTestName(): string {
		return AppMode::getCurrentTestName();
	}
	/**
	 * @return \Clockwork\Support\Laravel\ClockworkSupport
	 */
	protected function getClockworkSupport(): \Clockwork\Support\Laravel\ClockworkSupport {
		$application = $this->app;
		$make = $application->make('clockwork.support');
		return $make;
	}
	public static function checkoutAndTestFolder(string $folderToTest, string $branch): 
	\TitasGailius\Terminal\Response {
		$relative = 'tmp/'.$folderToTest;
		$absolute = abs_path($relative);
		try {
			QMAPIRepo::cloneRepo($absolute, $branch);
		} catch (\Throwable $e) {
			le($e);
		}
		$builder = Terminal::builder();
		$builder->in($absolute);
		$response = $builder->run('composer install', ConsoleLog::logTerminalOutput());
		$response = $builder->run('php vendor/bin/phpunit '.$folderToTest, ConsoleLog::logTerminalOutput());
		return $response;
	}
	protected function signIn($user = null){
		$user = $user ?: User::testUser();
		$this->actingAs($user);
		return $this;
	}
	public static function runFailedTests(): void{
		$failedTests = self::getFailedTestNames();
		foreach($failedTests as $testName){
			self::runTestByName($testName);
		}
	}
	/**
	 * @param TestResponse $response
	 * @param string $uri
	 * @return \Illuminate\Testing\TestResponse
	 * Wrapper for assertRedirect() to make it more readable on failures
	 */
	protected function assertRedirect(\Illuminate\Testing\TestResponse $response, string $uri){
		$actual = $response->headers->get('Location');
		$this->assertStringContains($actual, $uri, 'redirect');
		return $response;
	}
	/**
	 * Asserts that an object does not have a specified attribute.
	 *
	 * @param object $object
	 *
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws Exception
	 * @throws ExpectationFailedException
	 *
	 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/4601
	 */
	public static function assertObjectNotHasAttribute(string $attributeName, $object, string $message = ''): void
	{
		//self::createWarning('assertObjectNotHasAttribute() is deprecated and will be removed in PHPUnit 10.');

		if (!self::isValidObjectAttributeName($attributeName)) {
			throw \PHPUnit\Framework\InvalidArgumentException::create(1, 'valid attribute name');
		}

		if (!is_object($object)) {
			//throw InvalidArgumentException::create(2, 'object');
			le("assertObjectNotHasAttribute() expects parameter 2 to be object, " . gettype($object) . " given");
		}

		static::assertThat(
			$object,
			new LogicalNot(
				new ObjectHasAttribute($attributeName)
			),
			$message
		);
	}
}
