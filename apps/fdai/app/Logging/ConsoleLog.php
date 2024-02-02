<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Logging;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Utils\Env;
use Closure;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
class ConsoleLog {
	public static $logger;
    private static $lastMessage;
	private static array $previousMessages = [];
	private static ?string $lastDebugMessage = null;
	/**
	 * @return Logger
	 * Use this to avoid infinite loops by avoiding complex loggers before load is complete
	 */
	public static function logger(): Logger{
		if(self::$logger){
			return self::$logger;
		} // Return a cached logger if one exists
		return self::$logger = new Logger('Console', [self::newHandler()]);
	}
	/**
	 * Adds a log record at the DEBUG level.
	 * This method allows for compatibility with common interfaces.
	 * @param string $message The log message
	 * @param array|object|null $context The log context
	 * @return bool   Whether the record has been processed
	 */
	public static function debug(string $message, $context = [], bool $truncate = true): bool{
        if($message === static::$lastDebugMessage){return false;}
		self::$lastDebugMessage = $message;
		if(!QMLogLevel::shouldLog(QMLogLevel::DEBUG)){return false;}
		return static::addRecord(Logger::DEBUG, $message, $context, $truncate);
	}
	/**
	 * Adds a log record.
	 * @param int $level The logging level
	 * @param string $message The log message
	 * @param array|object|null $context The log context
	 * @return bool Whether the record has been processed
	 */
	public static function addRecord(int $level, string $message, $context = [], bool $truncate = true): bool{
        static::$lastMessage = $message;
		if(!$context){$context = [];}
		if(is_object($context)){
			try {
				$context = QMArr::toArray($context);
			} catch (\Throwable $e) {
				$context = [];
				$context['could_not_convert_message_context to array because'] = $e->getMessage();
			}
		}
		if($truncate){
			$message = QMStr::truncate($message, 1000);
		}
		if(!is_array($context)){
			$context = ['context' => $context];
		}
		return self::logger()->addRecord($level, $message, $context ?? []);
	}
	/**
	 * @return StreamHandler
	 */
	public static function newHandler(): StreamHandler{
		try {
			$handler = new StreamHandler((Env::getFormatted('HEROKU')) ? 'php://stderr' : 'php://stdout',
			                             QMLogLevel::getMonoLogLevelInt());
		} catch (Exception $e) {
			le($e);
		}
		$formatter = QMLogger::getLineFormatter();
		$formatter->includeStacktraces(false);
		$handler->setFormatter($formatter);
		return $handler;
	}
	/**
	 * Adds a log record at the INFO level.
	 * This method allows for compatibility with common interfaces.
	 * @param string $message The log message
	 * @param array|object|null $context The log context
	 * @return bool   Whether the record has been processed
	 */
	public static function info(string $message, $context = [], bool $truncate = true): bool{
        if($message === static::$lastMessage){return false;}
		return static::addRecord(Logger::INFO, $message, $context, $truncate);
	}
	/**
	 * Adds a log record at the NOTICE level.
	 * This method allows for compatibility with common interfaces.
	 * @param string $message The log message
	 * @param array|object|null $context The log context
	 * @return bool   Whether the record has been processed
	 */
	public static function notice(string $message, $context = [], bool $truncate = true): bool{
		return static::addRecord(Logger::NOTICE, $message, $context, $truncate);
	}
	/**
	 * Adds a log record at the WARNING level.
	 * This method allows for compatibility with common interfaces.
	 * @param string $message The log message
	 * @param array|object|null $context The log context
	 * @return bool   Whether the record has been processed
	 */
	public static function warning(string $message, $context = [], bool $truncate = true): bool{
		return static::addRecord(Logger::WARNING, $message, $context, $truncate);
	}
	/**
	 * Adds a log record at the ERROR level.
	 * This method allows for compatibility with common interfaces.
	 * @param string $message The log message
	 * @param array|object|null $context The log context
	 * @return bool   Whether the record has been processed
	 */
	public static function error(string $message, $context = [], bool $truncate = true): bool{
		return static::addRecord(Logger::ERROR, "ERROR: ".$message, $context, $truncate);
	}
	public static function logTerminalOutput(): Closure {
		return function($type, $buffer){
			ConsoleLog::infoWithoutContext($buffer);
//			if($type === Process::ERR){
//				ConsoleLog::error($buffer);
//			} else{
//				ConsoleLog::info($buffer);
//			}
		};
	}
	public static function infoWithoutContext(string $message): void{
		$file = fopen("php://stderr", "w");
		fwrite($file, $message . PHP_EOL);
		fclose($file);
	}
	/**
	 * Adds a log record at the CRITICAL level.
	 * This method allows for compatibility with common interfaces.
	 * @param string $message The log message
	 * @param array|object|null $context The log context
	 * @return bool   Whether the record has been processed
	 */
	public static function critical(string $message, $context = [], bool $truncate = true): bool{
		return static::addRecord(Logger::CRITICAL, $message, $context, $truncate);
	}
	/**
	 * Adds a log record at the ALERT level.
	 * This method allows for compatibility with common interfaces.
	 * @param string $message The log message
	 * @param array|object|null $context The log context
	 * @return bool   Whether the record has been processed
	 */
	public static function alert(string $message, $context = [], bool $truncate = true): bool{
		return static::addRecord(Logger::ALERT, $message, $context, $truncate);
	}
	/**
	 * Adds a log record at the EMERGENCY level.
	 * This method allows for compatibility with common interfaces.
	 * @param string $message The log message
	 * @param array|object|null $context The log context
	 * @return bool   Whether the record has been processed
	 */
	public static function emergency(string $message, $context = [], bool $truncate = true): bool{
		return static::addRecord(Logger::EMERGENCY, $message, $context, $truncate);
	}
	public static function exception(\Throwable $e, $meta = null){
		static::error($e->getMessage()."\n".$e->getTraceAsString(), $meta);
	}
	/**
	 * Sets minimum logging level at which this handler will be triggered.
	 * @param int|string $level Level or level name
	 * @return void
	 */
	public static function setLogLevel($level): void {
		$handlers = ConsoleLog::getHandlers();
		foreach($handlers as $handler){
			$handler->setLevel($level);
		}
	}
	private static function getHandlers(): array{
		return self::logger()->getHandlers();
	}
	/**
	 * @param string $message
	 */
	public static function once(string $message){
		if(in_array($message, static::$previousMessages)){
			return;
		}
		static::$previousMessages[] = $message;
		static::info($message);
	}
}
