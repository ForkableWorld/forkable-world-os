<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Logging;
use App\Utils\AppMode;
use App\Utils\Env;
use Exception;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
class QMLogger extends Logger {
	public const allowInlineLineBreaks = true;
	/**
	 * @var self
	 */
	private static $monolog;
	public function __construct(){
		// https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md
		parent::__construct("qm-monolog", [ConsoleLog::newHandler()]);
		if(\App\Utils\Env::get('LOG_MEMORY_USAGE')){
			$this->logMemoryUsage();
		}
		if(\App\Utils\Env::get('PAPERTRAIL_PORT')){
			$this->setPaperTrailHandler();
		}
	}
	/**
	 * Create a new QMLogger.
	 * @return static The created instance.
	 * This is run from the command line so Supervisor or Docker receives the output.
	 */
	public static function get(): self{
		if(self::$monolog){
			return self::$monolog;
		} // Return a cached logger if one exists
		return self::$monolog = new static();
	}
	public static function resetLoggers(){ self::$monolog = null; }
	/**
	 * @return LineFormatter
	 */
	public static function getLineFormatter(): LineFormatter{
		//$f = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
		$f = "%level_name%: %message%\n";
		//		if(AppMode::getJobTaskOrTestName() || AppMode::isWorker()){$f = "%level_name%: %message%\n";}
		//		if(AppMode::isStagingUnitTesting()){$f = "%level_name%: %message%\n";}
		if(AppMode::isGithubRunner()){
			// https://docs.github.com/en/actions/using-workflows/workflow-commands-for-github-actions#setting-an-error-message
			$f = "%message%\n";
		}
		$formatter = new LineFormatter($f, "", self::allowInlineLineBreaks, true);
		return $formatter;
	}
	private function setFileHandler(): void{
		try {
			$h = new StreamHandler(QMLog::getLogPath(), QMLogLevel::getSlimInt());
		} catch (Exception $e) {
			le($e);
		}
		$h->setFormatter(self::getLineFormatter());
		$this->pushHandler($h);
	}
	/**
	 * @return void
	 */
	private function setPaperTrailHandler(): void{
		$handler = new SyslogUdpHandler('logs2.papertrailapp.com', (int)\App\Utils\Env::get('PAPERTRAIL_PORT'));
		$handler->setLevel(QMLogLevel::getSlimInt());
		$formatter = self::getLineFormatter();
		$formatter->includeStacktraces(true);
		$handler->setFormatter($formatter);
		$this->pushHandler($handler);
	}
	private function logMemoryUsage(): void{
		$this->pushProcessor(new MemoryUsageProcessor);
		$this->pushProcessor(new MemoryPeakUsageProcessor);
	}
	/**
	 * Sets minimum logging level at which this handler will be triggered.
	 *
	 * @param  int|string $level Level or level name
	 * @return void
	 */
	public static function setLogLevel($level){
		$handlers = static::get()->getHandlers();
		foreach($handlers as $handler){
			$handler->setLevel($level);
		}
	}
}
