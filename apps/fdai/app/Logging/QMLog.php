<?php /** @noinspection PhpMissingParamTypeInspection *//*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Logging;
use App\Buttons\Admin\PHPStormButton;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\NotEnoughMeasurementsForCorrelationException;
use App\Exceptions\QMException;
use App\Files\FileHelper;
use App\Models\BaseModel;
use App\Models\User;
use App\Notifications\LinkNotification;
use App\Repos\QMAPIRepo;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Storage\LocalFileCache;
use App\Storage\Memory;
use App\Storage\TestMemory;
use App\Tables\QMTable;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\Alerter;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\SecretHelper;
use App\Utils\UrlHelper;
use Bugsnag\Configuration;
use Bugsnag\Report;
use Countable;
use Illuminate\Support\Collection;
use Log;
use PHPUnit\Framework\RiskyTestError;
use PHPUnit\Framework\SkippedTestError;
use Tests\QMDebugBar;
use Throwable;
/**
 * Class QMLog
 */
class QMLog extends Report {
	const ERROR_SPECIFIC_META = 'error_specific_meta';
	const CONSOLE_WIDTH = 80;
	public const DO_NOT_REPORT = [
		NotEnoughMeasurementsForCorrelationException::class,
		SkippedTestError::class,
		RiskyTestError::class,
	];
	public const MAXIMUM_MESSAGE_LENGTH = 20000;
	const MAX_NAME_LENGTH = 100;
	/**
	 * @var array
	 */
	public static array $alreadyReported = [];
	protected static $lastDebugMessage;
	protected static $lastMessage;
	/**
	 * @var array
	 */
	protected static array $previousMessages = [];
	protected static array $queue = [];
	protected static $processStart = [];
	public $url;
	protected Report $bugsnagReport;
	protected bool $obfuscate = true;
	protected $originalMessage;
	protected $originalName;
	protected $truncatedOriginalNameAndMessage;
	private $originalMetaData;
	/**
	 * @param null $name
	 * @param string|null $severity
	 * @param null $meta
	 * @param string|null $message
	 * @param bool $obfuscate
	 */
	public function __construct($name = null, string $severity = null, $meta = null, string $message = null,
	                            bool $obfuscate = true){
		if($name instanceof Configuration){
			parent::__construct($name);
			return;
		}
        if(!$name){
            return;
        }
		$this->addToMemory();
		if(!$message && $meta && is_string($meta)){
			$message = $meta;
			$meta = null;
		}
		$this->obfuscate = $obfuscate;
		$this->setOriginalMessage($message);
		$this->setOriginalName($name);
        if(!is_string($name)){
            return; // instance of bugsnag config
        }
		$this->setName($name);
		if($severity){$this->setSeverity(strtolower($severity));}
		if($meta){
			$this->addMetaTab(self::ERROR_SPECIFIC_META, $meta);
		}
		if($message = $message ?? $meta['message'] ?? null){
			$this->setMessage($message);
		}
	}
	/**
	 * @param string|null $originalMessage
	 */
	public function setOriginalMessage(?string $originalMessage): void{
		if(!$this->originalMessage){
			$this->originalMessage = $originalMessage;
		}
	}
	/**
	 * @param string|null $originalName
	 */
	public function setOriginalName(?string $originalName): void{
		if(!$this->originalName){
			$this->originalName = $originalName;
		}
	}
	/**
	 * @param string $name
	 * @return QMLog
	 */
	public function setName($name): QMLog{
		$this->setOriginalName($name);
		if(strlen($name) > self::MAX_NAME_LENGTH && !$this->message){
			$this->setOriginalMessage($name);
			$name = str_replace("=", "", $name);
			$name = QMStr::removeEmptyLines($name);
			[
				$name,
				$message,
			] = $this->truncateName($name);
			$this->setMessage($this->originalName);
		}
		return parent::setName($name);
	}
	/**
	 * @param string $name
	 * @return array
	 */
	protected function truncateName(string $name): array{
		[
			$start,
			$end,
		] = QMStr::splitAt($name, self::MAX_NAME_LENGTH);
		return [
			$start."...",
			"...".$end,
		];
	}
	/**
	 * @param string|null $message
	 * @return QMLog
	 */
	public function setMessage($message): QMLog{
		$this->setOriginalMessage($message);
		if(!$message){
			$message = $this->getMessage();
		}
		$message = self::truncate($message, self::MAXIMUM_MESSAGE_LENGTH);
		$message = QMStr::removeBlankLines($message);
		if($this->obfuscate){
			$message = SecretHelper::obfuscateString($message);
		}
		return parent::setMessage($message);
	}
	/**
	 * @return string|null
	 */
	public function getMessage(): ?string{
		if($m = $this->message){return $m;}
		$this->populateFailedTestMessage();
		$this->populateMessageFromMeta();
		$this->addFailedAssertionToMessage();
		$this->addBranchNameToFailedTestMessage();
		if($this->message === $this->name){
			if(strlen($this->name) > 100){
				$this->populateMessageFromTruncatedName();
			} else{
				$this->populateMessageFromStackTracePhpStormUrl();
			}
		}
		if(!$this->message){
			ConsoleLog::debug("Could not populate message in ".__METHOD__."for name ".$this->name);
			//$this->message = "";
		}
		return $this->message;
	}
	/**
	 * @return void
	 */
	protected function populateFailedTestMessage(): void{
		if(empty($this->message) && stripos($this->name, "Failed assert") !== false){
			$n = $this->getName();
			$this->setMessage("$n failed!");
			$name = \App\Utils\AppMode::getCurrentTestName();
			if(!empty($name)){
				$this->setName($name);
			}
			if(QMAPIRepo::getBranchFromMemory()){
				$this->setContext("Branch ".QMAPIRepo::getBranchFromMemory());
			}
		}
	}
	protected function populateMessageFromMeta(): void{
		if($message = $this->metaData['message'] ?? null){
			$message = QMStr::toString($message);
			$this->setMessage($message);
		}
	}
	protected function addFailedAssertionToMessage(): void{
		if($this->message && str_contains($this->message, 'Failed asserting')){
			$this->setMessage(str_replace('. ', ' ', $this->message)." in " . \App\Utils\AppMode::getCurrentTestName());
		}
	}
	protected function addBranchNameToFailedTestMessage(): void{
		$t = \App\Utils\AppMode::getCurrentTest();
		if($t && empty($this->message) && $t->hasFailed()){
			$branch = QMAPIRepo::getBranchFromMemoryOrGit();
			$this->setMessage($t->getName()." FAILED on branch $branch");
		}
	}
	protected function populateMessageFromTruncatedName(): void{
		[
			$start,
			$end,
		] = $this->truncateName($this->name);
		$this->setName($start);
		$this->setMessage($end);
	}
	protected function populateMessageFromStackTracePhpStormUrl(): void{
		$stack = $this->getStacktrace();
		if($frame = $stack->getFrames()[0] ?? null){
			$url = PHPStormButton::redirectUrl($frame['file'], $frame['line'] ?? $frame["lineNumber"]);
			$this->setMessage($url);
			$this->addMetaData(["OPEN_IN_PHPSTORM" => $url]);
		}
	}
	/**
	 * @param string $name
	 * @param $meta
	 */
	public function addMetaTab(string $name, $meta){
		$this->originalMetaData = $meta;
		$meta = (array) $meta;
		if($this->obfuscate && $meta){
			$meta = SecretHelper::obfuscateArray($meta);
		}
		$meta = QMArr::toArray($meta);
		$meta = QMArr::removeEmpty($meta);
		$this->addMetaData([$name => $meta]);
	}
	/**
	 * @param string $message
	 * @param int $length
	 * @return string
	 */
	public static function truncate(string $message, int $length = self::MAXIMUM_MESSAGE_LENGTH): string{
		return QMStr::truncate($message, $length, "[MAXIMUM_MESSAGE_LENGTH EXCEEDED]");
	}
	protected function addToMemory(): void{
		TestMemory::add(TestMemory::LOGS, $this);
	}
	public static function getBacktrace(){
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$backtrace = array_slice($backtrace, 2);
		return $backtrace;
	}
	public static function getBacktraceString():string{
		return self::print_r(self::getBacktrace());
	}
	/**
	 * @return void
	 */
	public static function logSession(): void{
		$REQUEST_URI = $_SERVER['REQUEST_URI'] ?? "\$_SERVER['REQUEST_URI'] NOT SET";
		QMLog::print(session()->all(), "session for ".$REQUEST_URI);
		QMLog::print($_COOKIE, "cookies for ".$REQUEST_URI);
	}
	public static function var_export(mixed $n, bool $return = true, bool $log = false): string{
		$str = var_export($n, $return);
		if($log){
			Log::info($str); // Use regular log to avoid truncation
		}
		return $str;
	}
	public function logToHandlers(): void{
		//XDebug::break();
		$name = $this->getName();
		try {
			$meta = $this->getErrorSpecificMeta();
		} catch (\Throwable $e){
			$meta = [];
			error_log("Could not get error specific meta in 
	".__METHOD__." 
	for ".$name." 
	because ".$e->getMessage());
		}
		$metaStr = '';
		if($meta){
			$meta = json_decode(json_encode($meta), true); // Prevents memory error
			$metaStr = self::print_r($meta, true);
		}
		$nameMessage = $this->getTruncatedOriginalNameAndMessage();
		$severityString = $this->getSeverity();
		//ChromePhp::logIfLocalApiRequest($nameMessage, $s, $meta);
		//QMClockwork::log($this);
		QMFlare::addGlow($nameMessage, $severityString, $meta ?? []);
		//$this->logToMonolog();
		//error_log("$s: $nameMessage");
		$nameMessageMeta = $nameMessage.$metaStr;
		try {
			if(AppMode::isGithubRunner()){
				$ghSeverityString = $severityString;
				if($ghSeverityString === 'info'){$ghSeverityString = 'notice';}
				// https://docs.github.com/en/actions/using-workflows/workflow-commands-for-github-actions#setting-an-error-message
				//$msg = "::error file={name},line={line},endLine={endLine},title={title}::{message}";
				$message = str_replace($name, $nameMessageMeta, $nameMessage);
				$test = AppMode::getJobTaskOrTestName();
				$nameMessageMeta = '::'.$ghSeverityString.' title='.$name.'::'.$test.': '.$message;
				$severityInt = QMLogLevel::STRING_TO_INT[$severityString];
				ConsoleLog::logger()->addRecord($severityInt, $nameMessageMeta, []);
			} else {
				Log::write($severityString, $nameMessageMeta, []);
			}
		} catch (\Throwable $e) {
			error_log( "Could not Log::write in " . __METHOD__ . " for this message 
	'".$name."'
	 because: 
    '" . $e->getMessage() . "'");
		}
		$this->notifyBugsnagIfNecessary();
		try {
			QMBugsnag::leaveBreadcrumb($nameMessage, $severityString, $meta);
			QMDebugBar::addMessage($nameMessage, $severityString);
		} catch (\Throwable $e) {
			error_log( "Could not log to handlers in " . __METHOD__ . " for this message 
	'".$name."'
	 because: 
    '" . $e->getMessage() . "'");
		}
	}
	/**
	 * @param int $length
	 * @return string
	 */
	public function getTruncatedOriginalNameAndMessage(int $length = self::MAXIMUM_MESSAGE_LENGTH): string{
		if($m = $this->truncatedOriginalNameAndMessage){
			return $m;
		}
		return $this->truncatedOriginalNameAndMessage =
			QMStr::removeBlankLines(QMLog::truncate($this->getOriginalNameAndMessage(), $length));
	}
	/**
	 * @return string
	 */
	protected function getOriginalNameAndMessage(): string{
		if($this->originalMessage === $this->originalName){
			return $this->originalMessage;
		}
		$str = $this->originalName;
		if($this->originalMessage){$str .= "\n\tMessage: ".$this->originalMessage;}
		return $str;
	}
	/**
	 * @return array|null
	 */
	public function getErrorSpecificMeta():?array{
		$m = $this->metaData;
		if(isset($m[self::ERROR_SPECIFIC_META])){$m = $m[self::ERROR_SPECIFIC_META];}
		if($this->originalError){$m['exception'] = $this->originalError;}
		if($this->message && $this->message !== $this->name){$m['message'] = $this->message;}
		if(!$m){return null;}
		return $m;
	}
	public static function getTraceAsString(int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0){
		$trace = debug_backtrace($options, $limit);
		$trace = array_slice($trace, 2);
		return self::print_r($trace, true);
	}
    public static function log(string $severityType, string $msg)
    {
		static::save($msg, $severityType);
    }
	public static function infoWithoutTruncation(string $string){
		ConsoleLog::info($string, null, false);
	}
	/**
	 * @return Throwable|null
	 */
	public function getException(): ?\Throwable{
		$orig = $this->getOriginalError();
		return ($orig instanceof \Throwable) ? $orig : null;
	}
	/**
	 * @return bool
	 */
	public function isError(): bool{ return $this->severity === QMLogLevel::ERROR; }
	/**
	 * @param float $length
	 * @return string
	 */
	protected static function divider(float $length = self::CONSOLE_WIDTH):string{
		return str_repeat("═", (int)$length);
	}
	/**
	 * @param string $title
	 * @param string $message
	 * @return string
	 */
	public static function box(string $title, string $message): string{
		$topDivider = self::titledDivider($title);
		$bottomDivider = self::divider();
		return "
$topDivider
$message
$bottomDivider
";
	}
	/**
	 * @return bool
	 */
	public function isWarning(): bool{ return $this->severity === QMLogLevel::WARNING; }
	/**
	 * @return bool
	 */
	public function isWarningOrError(): bool{
		return $this->isWarning() || $this->isError();
	}
	protected function notifyBugsnagIfNecessary(){
		$warnOrErr = $this->isWarningOrError();
		if(!$warnOrErr){return;}
		QMBugsnag::notifyError($this->getName(), $this->getMessage(), function($report){
			$this->updateBugsnagReport($report);
		});
	}
	/**
	 * @return int
	 */
	public static function getLogCount(): int{
		return count(TestMemory::getLogs());
	}
	/**
	 * @param Report $report
	 */
	public function updateBugsnagReport(Report $report){
		$this->setBugsnagReport($report);
		if($u = QMAuth::getQMUserIfSet()){
            $report->setUser(['id' => $u->getId(),'name' => $u->getDisplayNameAttribute(), 'email' => $u->email]);
        }
		$this->populateNameAndMessageFromMessageIfNameContainsWordException();
		if($c = $this->getContext()){
			$c = str_replace(abs_path(), "", $c);
			$report->setContext($c);       // Top line at bugsnag.com
		}
		if($m = $this->getMessage()){
			$report->setMessage($m); // Second line at bugsnag.com
		}
	}
	/**
	 * @param string $str
	 * @return bool
	 */
	public static function containsLogFilename(string $str): bool{
		if(str_contains($str, 'Framework/TestCase.php')){
			return true;
		}
		if(str_contains($str, 'vendor')){
			return true;
		}
		$loggyFileNames = [
			'Collection.php',
			'BugsnagHandler.php',
			'AbstractProcessingHandler.php',
			'Logger.php',
			'QMLog.php',
			'QMProfile.php',
			'Log.php',
			'QMLogger.php',
			'Exception.php',
			'Builder.php',
			'vendor',
		];
		foreach($loggyFileNames as $loggyFileName){
			if(str_contains($str, $loggyFileName)){
				return true;
			}
		}
		return false;
	}
	/**
	 * @param string $str
	 * @return bool
	 */
	public static function isLoggyFunction(string $str): bool{
		$loggyWords = [
			'__call',
			'__callStatic',
			'__construct',
			'__destruct',
			'__get',
			'aggregate',
			'affectingStatement',
			'App\Providers\{closure}',
			'create',
			'debug',
			'delete ',
			'dispatch',
			'error',
			'event',
			'exception',
			'find',
			'findInDB',
			'findInMemoryOrDB',
			'findOneInMemoryOrDB',
			'first',
			'forwardCallTo',
			'get',
			'getArray',
			'getModels',
			'getUserIndexedByKeyMeta',
			'getUserMetaByKey',
			'Illuminate\Database\Query\{closure}',
			'Illuminate\Events\{closure}',
			'Illuminate\Pipeline\{closure}',
			'info',
			'insert',
			'performUpdate',
			'performInsert',
			'loadMissing',
			'log',
			'logQuery',
			'onceWithColumns',
			'pluck',
			'read',
			'readFromHandler',
			'run',
			'runBare',
			'runSelect',
			'runTest',
			'save',
			'saveQueryExecuted',
			'select',
			'setUserMetaValue',
			'slim',
			'startProfile',
			'statement',
			'update',
			'write',
		];
		return in_array($str, $loggyWords);
	}
	/**
	 * @param string $name
	 * @param array|object $context
	 * @param bool         $obfuscate
	 */
	public static function errorIfNotTesting(string $name, $context = [], bool $obfuscate = true){
		if(!AppMode::isTestingOrStaging()){
			self::error($name, $context, $obfuscate);
		} else{
			self::debug($name, $context, $obfuscate);
		}
	}
	/**
	 * @param string      $name
	 * @param null        $meta
	 * @param bool        $obfuscate
	 * @param string|null $message
	 * @return void
	 */
	public static function error(string $name, $meta = null, bool $obfuscate = true, string $message = null): void{
		self::save($name, QMLogLevel::ERROR, $meta, $obfuscate, $message);
	}
	/**
	 * @param string $name
	 * @param string $messageLevel
	 * @param null $meta
	 * @param bool $obfuscate
	 * @param string|null $message
	 * @return QMLog|null
	 */
	public static function save(string $name, string $messageLevel = QMLogLevel::INFO, $meta = null,
	                            bool $obfuscate = true, string $message = null): ?self {
		if($name === static::$lastMessage){return null;}  // Too many duplicate logs can crash API requests or cause infinite loops
		if(empty($name)){$name ="Log name is empty!";}
		self::$lastMessage = $name;
		if(!$message && $meta && isset($meta['message'])){$message = $meta['message'];}
		if(QMLogLevel::shouldLog($messageLevel)){
			try {
				$l = new QMLog($name, $messageLevel, $meta, $message, $obfuscate);
				$l->logToHandlers();
			} catch (\RuntimeException $e){
				if(str_contains($e->getMessage(), "A facade root has not been set")){
					$consoleMessage = $name;
					if($message){$consoleMessage .= "\n\tMESSAGE: ".$message;}
					ConsoleLog::logger()->log($messageLevel, $consoleMessage, (array)$meta ?? []);
				} else {
					throw $e;
				}
			}
		}
		return $l ?? null;
	}
	/**
	 * @param string $name
	 * @param array|object|mixed $context
	 * @param bool         $obfuscate
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public static function debug(string $name, $context = [], bool $obfuscate = true){
		if($name === self::$lastDebugMessage){return;}
		self::$lastDebugMessage = $name;
		if(!QMLogLevel::shouldLog(QMLogLevel::DEBUG)){return;}
		if($obfuscate){$name = SecretHelper::obfuscateString($name);}
		ConsoleLog::debug($name, $context);
	}
	/**
	 * @param string $name
	 * @param array|object $context
	 * @param bool         $obfuscate
	 */
	public static function errorIfProduction(string $name, $context = [], bool $obfuscate = true){
		if(AppMode::isProduction()){
			self::error($name, $context, $obfuscate);
		} else{
			self::info($name, $context, $obfuscate);
		}
	}
	/**
	 * @param string $name
	 * @param array $context
	 * @param bool $obfuscate
	 * @return QMLog|null
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public static function info(string $name, $context = [], bool $obfuscate = true, string $message = null): ?QMLog{
		if(static::$queue){
			$name .= implode("\n", static::$queue);
		}
		return self::save($name, QMLogLevel::INFO, $context, $obfuscate, $message);
	}
	/**
	 * @param string $message
	 * @param array $context
	 * @param bool $truncate
	 * @return bool
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public static function notice(string $message, $context = [], bool $truncate = true): bool {
		return ConsoleLog::notice($message, $context, $truncate);
	}
	/**
	 * @param string $name
	 * @param array $context
	 * @param bool $obfuscate
	 * @param string|null $message
	 */
	public static function errorOrInfoIfTesting(string $name, $context = [], bool $obfuscate = true, string $message = null){
		if(!AppMode::isTestingOrStaging()){
			self::error($name, $context, $obfuscate, $message);
		} else{
			self::info($name, $context, $obfuscate, $message);
		}
	}
	/**
	 * @param string $name
	 * @param array|object $context
	 * @param bool         $obfuscate
	 */
	public static function errorOrDebugIfTesting(string $name, $context = [], bool $obfuscate = true){
		if(!AppMode::isTestingOrStaging()){
			self::error($name, $context, $obfuscate);
		} else{
			self::debug($name, $context, $obfuscate);
		}
	}
	/**
	 * @param string $name
	 * @param array|object $context
	 * @param bool         $obfuscate
	 */
	public static function infoOrDebugIfTesting(string $name, $context = [], bool $obfuscate = true){
		if(!AppMode::isTestingOrStaging()){
			self::info($name, $context, $obfuscate);
		} else{
			self::debug($name, $context, $obfuscate);
		}
	}
	/**
	 * @param string $name
	 * @param array|object $context
	 * @param string|null $message
	 * @param int|null $userId
	 */
	public static function errorOrDebugIfTestingOrTestUser(string $name, $context, string $message = null, int
	$userId = null){
		if(self::isTestModeOrTestUser($userId)){
			self::debug($name, $context, true);
		} else{
			self::error($name, $context, true, $message);
		}
	}
	/**
	 * @param int|null $userId
	 * @return bool
	 */
	protected static function isTestModeOrTestUser(int $userId = null): bool{
		$testing = AppMode::isTestingOrStaging();
		if($testing){
			return true;
		}
		if($userId){
			$user = QMUser::find($userId);
		} else{
			$user = QMAuth::getQMUserIfSet();
		}
		if(!$user){
			return false;
		}
		$testing = $user->isTestUser();
		return $testing;
	}
	/**
	 * @param string $message
	 * @param int $every
	 */
	public static function infoFast(string $message, int $every = 50){
		static::$queue[] = $message;
		if(count(static::$queue) > $every){
			$message = implode("\n", static::$queue);
			ConsoleLog::info($message);
			static::$queue = [];
		}
	}
	/**
	 * @param int $seconds
	 * @param string $why
	 */
	public static function sleep(int $seconds, string $why){
		self::info("Sleeping $seconds seconds because $why...");
		sleep($seconds);
	}
	/**
	 * @param string $name
	 * @param array|object $context
	 * @param bool         $obfuscate
	 */
	public static function warning(string $name, $context = [], bool $obfuscate = true){
		self::save($name, QMLogLevel::WARNING, $context, $obfuscate);
	}
	/**
	 * @param string $message
	 * @param array $meta
	 * @param bool   $obfuscate
	 */
	public static function exceptionIfNotProduction(string $message, array $meta = [], bool $obfuscate = true){
		self::error($message, $meta, $obfuscate);
		if(!AppMode::isProduction()){
			le($message, $meta);
		}
	}
	/**
	 * @param string $name
	 * @param array $meta
	 * @param bool $obfuscate
	 * @param string|null $message
	 */
	public static function logicExceptionIfNotProductionApiRequest(string $name, array $meta = [], bool $obfuscate = 
	true, string $message = null){
		if(!AppMode::isProduction() || !AppMode::isApiRequest()){
			le($name." ".$message, $meta);
		}
		self::error($name, $meta, $obfuscate, $message);
	}
	/**
	 * @param string     $message
	 * @param array|null $array $array
	 */
	public static function exceptionIfTesting(string $name, $meta = [], bool $obfuscate = true, string $message = null){
		if(!AppMode::isTestingOrStaging()){
			self::error($name, $meta, $obfuscate, $message);
		} else{
			le($name." ".$message, $meta);
		}
	}
	/**
	 * @param string     $message
	 * @param array|null $array $array
	 */
	public static function exceptionIfUnitTest(string $name, $meta = [], bool $obfuscate = true, string $message = null){
		if(AppMode::isNonStagingUnitTest()){
			le($name." ".$message, $meta);
		}
		self::error($name, $meta, $obfuscate, $message);
	}
	/**
	 * @param array       $arr
	 * @param string|null $name
	 * @param bool        $obfuscate
	 */
	public static function logKeyValueArray(array $arr, string $name = null, bool $obfuscate = true){
		$str = "";
		if($name){
			$str .= "\n=== $name ===\n";
		}
		foreach($arr as $key => $value){
			if($obfuscate){
				$value = SecretHelper::obfuscateString($value, $key);
			}
			$str .= "$key: $value\n";
		}
		ConsoleLog::info($str);
	}
	/**
	 * @param string $string
	 * @return void
	 */
	public static function infoWithoutObfuscationOrContext(string $string): void{
		self::infoWithoutContext($string, false);
	}
	/**
	 * @param string $message
	 * @param bool   $obfuscate
	 * @return void
	 */
	public static function infoWithoutContext(string $message, bool $obfuscate = true): void{
		if($obfuscate){
			//Env::validateEnv();
			$message = SecretHelper::obfuscateString($message);
		}
		ConsoleLog::info($message);
	}
	/**
	 * @param string $string
	 */
	public static function infoWithoutObfuscation(string $string){
		self::info($string, [], false);
	}
	/**
	 * @param mixed $mixed
	 * @return string
	 */
	public static function export($mixed): string{
		$str = var_export($mixed, true);
		self::infoWithoutObfuscationOrContext($str);
		return $str;
	}
	/**
	 * @param string      $name
	 * @param array|null $context
	 * @param string|null $message
	 */
	public static function errorWithoutObfuscation(string $name, array $context = null, string $message = null){
		self::error($name, $context, false, $message);
	}
	/**
	 * @param array|Collection $arr
	 * @param string $title
	 * @return string
	 */
	public static function table($arr, string $title): string{
		if(!$arr instanceof Collection){
			$arr = collect($arr);
		}
		return QMTable::collectionToConsoleTable($arr, $title);
	}
	/**
	 * @param array $attributes Properties you want in the table
	 * @param array|Collection $arr Models
	 * @param string|null $title
	 * @param int $maxLength
	 * @return string
	 */
	public static function attributesTable(array $attributes, $arr, string $title = null, int $maxLength = 0): string{
		if(!$arr instanceof Collection){$arr = collect($arr);}
		$data = $arr->map(function(BaseModel $t) use ($maxLength, $attributes){
			$arr = [];
			foreach($attributes as $attribute){
				$val = $t->getAttribute($attribute);
				if($maxLength){$val = QMStr::truncate($val, $maxLength);}
				$arr[$attribute] = $val;
			}
			return $arr;
		});
		return self::table($data, $title);
	}
	/**
	 * @param string $url
	 * @param string|null $title
	 */
	public static function logLink(string $url, string $title = null){
		//$url = str_replace("staging.quantimo.do", "local.quantimo.do", $url);
		self::logBox($title, $url);
	}
	/**
	 * @param string $title
	 * @param string $message
	 */
	public static function logBox(string $title, string $message){
		self::infoWithoutContext(self::box($title, $message));
	}
	/**
	 * @param string $url
	 * @param string|null $title
	 */
	public static function logLocalLinkButton(string $url, string $title = null){
		$url = UrlHelper::toLocalUrl($url);
		self::infoWithoutObfuscationOrContext(self::getLinkButton($url, $title));
	}
	/**
	 * @param string $url
	 * @param string|null $title
	 * @return string
	 */
	public static function getLinkButton(string $url, string $title = null): string{
		return self::box($title, $url);
	}
	/**
	 * @param \Throwable $e
	 */
	public static function throwIfNotProductionAPIRequest(Throwable $e){
		ExceptionHandler::throwIfNotProductionApiRequest($e);
	}
	/**
	 * @param Countable $arr
	 * @param string    $description
	 */
	public static function count(Countable $arr, string $description){
		self::infoWithoutObfuscationOrContext(count($arr)." $description");
	}
	/**
	 * @param string $title
	 * @param string $url
	 * @return string
	 */
	public static function linkButton(string $title, string $url): string{
		$str = self::getLinkButton($url, $title);
		self::infoWithoutObfuscationOrContext($str);
		return $str;
	}
	public static function phpErrorSettings(){
		error_reporting(E_ALL | E_STRICT);
		if(Env::get('APP_DEBUG')){
			ini_set('display_errors', true);
		} else{
			ini_set('display_errors', false);
		}
		ini_set('log_errors', true);
		ini_set('error_log', self::getLogPath());
	}
	/**
	 * @return string
	 */
	public static function getLogPath(): string{
		return FileHelper::absPath('storage/logs/laravel.log');
	}
	/**
	 * @param array $items
	 * @param string|null $title
	 */
	public static function list(array $items, string $title = null){
		$str = "";
		if($title){
			$str .= "==== $title ====\n";
		}
		$i = 1;
		foreach($items as $key => $url){
			if(is_string($key)){
				$str .= "\t$i. $key => $url\n";
			} else{
				$str .= "\t$i. $url\n";
			}
			$i++;
		}
		\App\Logging\ConsoleLog::info($str);
	}
	/**
	 * @param string $string
	 */
	public static function immediately(string $string){
		self::infoWithoutObfuscationOrContext($string);
		self::infoWithoutObfuscationOrContext("");
	}
	/**
	 * @param string $message
	 */
	public static function once(string $message){
		if(in_array($message, static::$previousMessages)){
			return;
		}
		static::$previousMessages[] = $message;
		static::infoWithoutObfuscationOrContext($message);
	}
	/**
	 * @param array $arr
	 * @param string $title
	 */
	public static function printNonNullNumbersAndStrings(array $arr, string $title){
		foreach($arr as $key => $value){
			if($value === null){
				unset($arr[$key]);
			}
			if(is_object($value)){
				unset($arr[$key]);
			}
			if(is_array($value)){
				unset($arr[$key]);
			}
		}
		self::print($arr, $title);
	}
	/**
	 * @param mixed $mixed
	 * @param string $title
	 * @param bool $return
	 * @return string
	 */
	public static function print($mixed, string $title = "", bool $return = true): string{
		$str = QMLog::print_r($mixed, $return);
		$str = self::truncate($str, self::MAXIMUM_MESSAGE_LENGTH);
		$str = "=== $title ===\n$str";
		if(!$return){QMLog::info($str);}
		return $str;
	}
	/**
	 * @param string $title
	 * @param string $url
	 * @param string|null $body
	 * @param string|null $icon
	 */
	public static function notifyLink(string $title, string $url, string $body = null, string $icon = null){
		self::importantInfo($title."\n$url\n$body");
		User::mike()->notify(new LinkNotification($title, $url, $body, $icon));
	}
    public static function print_r($mixed, bool $return = true): bool|string{
        return print_r($mixed, $return);
    }
	/**
	 * @param string $string
	 */
	public static function importantInfo(string $string){
		self::infoWithoutContext("
======================================
$string
======================================
");
	}
	public static function logEndOfProcessIfStarted(string $name){
		if(!isset(self::$processStart[$name])){
			return;
		}
		self::logEndOfProcess($name);
	}
	/**
	 * @param string $name
	 */
	public static function logEndOfProcess(string $name){
		QMClockwork::logEnd($name);
        try {
            LocalFileCache::set($name, self::$processStart);
        } catch (\Throwable $e) {
            ConsoleLog::exception($e);
        }
		if(!isset(self::$processStart[$name])){
			le("No start time for process $name");
		}
		$startTime = self::$processStart[$name];
		unset(self::$processStart[$name]);
		$duration = round(microtime(true) - $startTime);
		$msg = $name;
		$msg .=" (took $duration s)";
		ConsoleLog::notice("====== DONE WITH $msg ======");
	}
	/**
	 * @param string $name
	 */
	public static function logStartOfProcess(string $name){
		QMClockwork::logStart($name);
		self::$processStart[$name] = microtime(true);
		$time = TimeHelper::humanTime(time());
		ConsoleLog::notice("====== STARTING ".$name." at $time ======");
	}
	/**
	 * @param string $name
	 * @return false|mixed
	 */
	public static function getLastProcessStartTime(string $name): mixed{
		return LocalFileCache::get($name);
	}
	/**
	 * @return Collection
	 */
	public static function all(): Collection{
		$arr = TestMemory::get(TestMemory::LOGS);
		return collect($arr);
	}
	/**
	 * @return QMLog|null
	 */
	public static function getLastQMLog(): ?self{
		$all = static::all();
		return $all->last();
	}
	private function populateNameAndMessageFromMessageIfNameContainsWordException(): void{
		if(str_contains($this->name, 'Exception')){
			$arr = explode("\n", $this->message);
			if(!isset($arr[1])){
				$arr = explode(", called in", $this->message);
			}
			if(!empty($arr[0])){
				$this->setName($arr[0]);
			}
			if(isset($arr[1])){
				$this->setMessage($arr[1]);
			}
		}
	}
	/**
	 * @return string|null
	 */
	public function getContext(): ?string{
		$c = $originalContext = $this->context;
		if($c && str_contains($c, 'vendor/phpunit')){
			$c = "";
		}
		if($manuallySetContext = GlobalLogMeta::getGlobalContext()){
			$c = $manuallySetContext;
		}
		if($testName = \App\Utils\AppMode::getCurrentTestName()){
			$c .= " in $testName";
		}
		if($jobName = AppMode::getJobName()){
			$c .= " in $jobName";
		}
		if($branchName = QMAPIRepo::getBranchFromMemory()){
			$c .= " on branch $branchName";
		}
		$c = trim($c);
		if(empty($c)){
			$c = $originalContext;
		}
		$this->setContext($c);
		return $c;
	}
	/**
	 * @param string $title
	 * @return string
	 */
	public static function titledDivider(string $title): string{
		$topDividerWidth = (self::CONSOLE_WIDTH - strlen($title)) / 2;
		if($topDividerWidth < 3){
			$topDividerWidth = 3;
		}
		$topDivider = self::divider($topDividerWidth);
		$topDivider = "$topDivider $title $topDivider";
		return $topDivider;
	}
	/**
	 * @param string $message
	 * @param string|null $url
	 */
	protected static function alertToast(string $message, string $url = null): void {
		Alerter::errorToast($message, $url, 5);
	}
	/**
	 * @param Report $report
	 */
	protected function setBugsnagReport(Report $report){
		foreach($report as $key => $value){
			if(empty($this->$key)){
				$this->$key = $value;
			}
		}
		$this->bugsnagReport = $report;
	}
	/**
	 * @param string $url
	 * @return static
	 */
	public function setUrl(string $url): self{
		$this->url = $url;
		return $this;
	}
	/**
	 * @return string
	 */
	public function __toString(){
		return $this->message."\n META: ".\App\Logging\QMLog::print_r($this->metaData, true);
	}
	public function break(){
		debugger($this);
	}
}
