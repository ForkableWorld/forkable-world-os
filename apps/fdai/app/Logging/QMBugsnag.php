<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Logging;
use App\Files\FileHelper;
use App\Http\Parameters\DebugRequestParam;
use App\Utils\Env;
use App\Utils\ReleaseStage;
use Bugsnag\Breadcrumbs\Breadcrumb;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Bugsnag\Client;
use Bugsnag\Configuration;
use Bugsnag\Report;
use Psr\Log\LogLevel;
class QMBugsnag {
	public static \Bugsnag\Configuration $bugsnagConfig;
	/** @var Report $report */
	public static function preSendReportConfigCallback(Report $report): void{
		$last = QMLog::getLastQMLog();
		if($last && $last->getName() === $report->getName()){
			$me = $last;
			$me->updateBugsnagReport($report);
		} else{
			try {
				$report->addMetaData(GlobalLogMeta::get());
			} catch (\Throwable $e){
			    error_log("Could not add global log meta to bugsnag report because:\n" . $e->getMessage());
			}
		}
		self::removeLoggyFrames($report->getStacktrace());
	}
	/**
	 * @param string $apiKey
	 * @return Configuration
	 */
	public static function config(string $apiKey): Configuration{
		if($c = self::$bugsnagConfig){
			return $c;
		}
		$c = Client::make($apiKey); // Don't use Laravel facade, it doesn't work before bootstrap
		$c->setReleaseStage(ReleaseStage::getReleaseStage());
		$c->setAppVersion(Env::getFormatted('API_LAST_MODIFIED') ?? Env::getFormatted('GIT_COMMIT'));
		$c->setProjectRoot(FileHelper::projectRoot());
		if(Env::getFormatted('NODE_NAME')){
			$c->setHostname(Env::getFormatted('NODE_NAME'));
		}
		if(DebugRequestParam::isDebug()){
			$c->setErrorReportingLevel(LogLevel::DEBUG);
		}
		return self::$bugsnagConfig = $c->getConfig();
	}
	public static function resetLoggersForTesting(): void{
		QMLogger::resetLoggers();
	}
	/**
	 * @param string $name
	 * @param string $type
	 * @param array|null $metaData
	 */
	public static function leaveBreadCrumb(string $name, string $type = Breadcrumb::LOG_TYPE, array $metaData = null){
		if(in_array($type, [
			QMLogLevel::ERROR,
			QMLogLevel::CRITICAL,
			QMLogLevel::WARNING,
		])){
			$type = Breadcrumb::ERROR_TYPE;
		}
		try {
			\Bugsnag\BugsnagLaravel\Facades\Bugsnag::leaveBreadcrumb($name, $type, $metaData ?? []);
		} catch (\Throwable $e) {
		    error_log("Bugsnag::leaveBreadcrumb() failed to leave this breadcrumb: 
	$name
	because of this error:
" . $e->getMessage());
		}
	}
	/**
	 * Notify Bugsnag of a non-fatal/handled throwable.
	 * @param \Throwable|\Error $throwable the throwable to notify Bugsnag about
	 * @param callable|null $callback  the customization callback
	 * @return void
	 */
	public static function notifyException(\Throwable|\Error $throwable, callable $callback = null): void{
		try {
			\Bugsnag::notifyException($throwable, $callback);
		} catch (\Throwable $e){
		    ConsoleLog::info("Could not Bugsnag::notifyException because ". $e->getMessage());
		}
	}
	/**
	 * Notify Bugsnag of the given error report.
	 * This may simply involve queuing it for later if we're batching.
	 * @param \Bugsnag\Report $report the error report to send
	 * @param callable|null $callback the customization callback
	 * @return void
	 * @static
	 */
	public static function notify(Report $report, callable $callback = null): void{
		try {
			/** @var Client $instance */
			\Bugsnag::notify($report, $callback);
		} catch (\Throwable $e){
			ConsoleLog::info("Could not Bugsnag::notifyException because ". $e->getMessage());
		}
	}
	public static function notifyError($name, $message, $callback = null): void{
		try {
			\Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError($name, $message, $callback);
		} catch (\Throwable $e) {
		    error_log("Bugsnag::notifyError() failed to notify this error: 
	$name
	because of this error:
" . $e->getMessage());
		}
	}
	/**
	 * @param \Bugsnag\Stacktrace $stacktrace
	 * @return array[]
	 */
	public static function removeLoggyFrames(\Bugsnag\Stacktrace $stacktrace): array{
		$frames = &$stacktrace->getFrames();
		$excludeFilesLike = [
			"Log",
			"Exception",
			"Facade",
			"Monolog",
			"LoggerTrait",
		];
		foreach($frames as $i => $frame){
			$file = $frame['file'];
			foreach($excludeFilesLike as $excludeFileLike){
				if(str_contains($file, $excludeFileLike)){
					$stacktrace->removeFrame(0);
					continue 2;
				}
			}
			break;
		}
		return $frames;
	}
}
