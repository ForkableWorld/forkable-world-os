<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Logging;
use App\Files\Env\EnvFile;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Utils\Env;
use Monolog\Logger;
use Psr\Log\LogLevel;
use Slim\Log;
class QMLogLevel extends LogLevel {
	public const DEFAULT = self::INFO;
	public const INFO_UPPER = 'INFO';
	public const STRING_ALERT = 'ALERT';
	public const STRING_CRITICAL = 'CRITICAL';
	public const STRING_DEBUG = 'DEBUG';
	public const STRING_EMERGENCY = 'EMERGENCY';
	public const STRING_ERROR = 'ERROR';
	public const STRING_NOTICE = 'NOTICE';
	public const STRING_WARNING = 'WARNING';
	public const STRING_TO_INT = [
		self::STRING_EMERGENCY => Log::EMERGENCY,
		self::STRING_ALERT => Log::ALERT,
		self::STRING_CRITICAL => Log::CRITICAL,
		self::STRING_ERROR => Log::ERROR,
		self::STRING_WARNING => Log::WARN,
		self::STRING_NOTICE => Log::NOTICE,
		self::INFO_UPPER => Log::INFO,
		self::STRING_DEBUG => Log::DEBUG,
		LogLevel::EMERGENCY => Log::EMERGENCY,
		LogLevel::ALERT => Log::ALERT,
		LogLevel::CRITICAL => Log::CRITICAL,
		LogLevel::ERROR => Log::ERROR,
		LogLevel::WARNING => Log::WARN,
		LogLevel::NOTICE => Log::NOTICE,
		LogLevel::INFO => Log::INFO,
		LogLevel::DEBUG => Log::DEBUG,
	];
	/**
	 * Logging level
	 * @var string
	 */
	public static string $logLevelUpper;
	public static $previousLogLevel;
	/**
	 * Allowed log level strings
	 * @var array
	 */
	private static $toSlimInt = self::STRING_TO_INT;
	public static function revertToPreviousLogLevel(){
		if(self::$previousLogLevel){
			self::set(self::$previousLogLevel);
		}
	}
	/**
	 * @param string $level
	 */
	public static function set(string $level){
		QMLogger::setLogLevel($level);
		ConsoleLog::setLogLevel($level);
		self::$previousLogLevel = self::upper();
		self::setLogLevelUpper(QMStr::upper($level));
		if(self::$previousLogLevel !== self::$logLevelUpper){
			ConsoleLog::info("Changed log level from ".self::$previousLogLevel." to: " . self::$logLevelUpper);
		}
	}
	/**
	 * Get log level
	 * @return string
	 */
	public static function upper(): string{
		if(!isset(self::$logLevelUpper)){  // getenv is slower so it's best to store it here
            $level = Env::getFormatted('LOG_LEVEL');
            if ($level) {
                self::setLogLevelUpper(strtoupper($level));
            }
		}
		if(!isset(self::$logLevelUpper)){
			return self::INFO_UPPER;
		}
		return self::$logLevelUpper;
	}
	public static function setDebug(){
		QMLog::info("Setting log level to debug");
		self::set(self::STRING_DEBUG);
		Env::set(Env::LOG_LEVEL, self::STRING_DEBUG);
		QMLog::debug("Set log level to debug");
	}
	public static function setInfo(){
		self::set(self::INFO_UPPER);
		QMLog::debug("Set log level to info");
	}
	public static function setFromDotEnv(){
		$level = EnvFile::getValueFromRootEnv('LOG_LEVEL') ?: self::DEFAULT;
		self::set($level);
		QMLog::debug("Set log level to $level");
	}
	/**
	 * @param string $messageLevel
	 * @return bool
	 */
	public static function shouldLog(string $messageLevel): bool{
		try {
			$toSlimInt = self::STRING_TO_INT;
			$int = $toSlimInt[$messageLevel];
			if($messageLevel === "info"){
				$int = Log::INFO;
			}
			$currentSettingInt = self::getSlimInt();
			return $int <= $currentSettingInt;
		} catch (\Throwable $e) {
		    le($e);
		}// The lower the number the more important
	}
	/**
	 * @return int
	 */
	public static function getSlimInt(): int{
		$toSlimInt = self::STRING_TO_INT;
		$upper = self::upper();
		try {
			return $toSlimInt[$upper];
		} catch (\Throwable $e) {
		    le($e);
		}
	}
	/**
	 * Get log level
	 * @return string
	 */
	public static function lowerCase(): string{
		$upper = self::upper();
		return QMStr::toLower($upper);
	}
	/**
	 * @return bool
	 */
	public static function isDebug(): bool{
		return self::upper() === self::STRING_DEBUG;
	}
	/**
	 * @return int
	 */
	public static function getMonoLogLevelInt(): int{
		$upper = self::upper();
		return constant(Logger::class.'::'.$upper);
	}
	/**
	 * @param string $logLevelUpper
	 */
	public static function setLogLevelUpper(string $logLevelUpper): void{
		if(is_numeric($logLevelUpper)){
			le("Log level is numeric: $logLevelUpper");
		}
		self::$logLevelUpper = $logLevelUpper;
	}
}
