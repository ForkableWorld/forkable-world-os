<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Logging;
use App\Buttons\Admin\ClockworkButton;
use App\Files\FileHelper;
use App\Types\QMArr;
use App\Utils\Env;
use App\Utils\EnvOverride;
use App\Utils\N8N;
use App\Utils\UrlHelper;
use Clockwork\Request\Request;
class QMClockwork
{
	const PATH = "__clockwork/app";
	public static $alreadySaved;
	/**
	 * @param string $tableName
	 * @param array $table
	 */
	public static function addUserTable(string $tableName, array $table){
		$tab = self::getUserDetailsTab();
		$tab->table($tableName, $table);
	}
	public static function getLink(): array{
		return ['Clockwork' => self::getAppUrl()];
	}
	/**
	 * @param QMLog $log
	 */
	public static function log(QMLog $log){
		if(!self::available()){return;}
		try {
			$messageLevel = $log->getSeverity();
			$name = $log->getTruncatedOriginalNameAndMessage();
			$meta = $log->getErrorSpecificMeta();
			$c = self::clock();
            if($meta){
                $c->$messageLevel($name, $meta);
            } else {
                $c->$messageLevel($name);
            }
		} catch (\Throwable $e) { // Not available before application is created
			ConsoleLog::error("Could not log to clockwork because: ".$e->getMessage()."\n\tTried to log: {$log->getName()}");
		}
    }
	/**
	 * @param string $message
	 * @param string|int $start
	 * @param string|int|null $end
	 */
	public static function logDuration(string $message, $start, $end = null){
		if(!$end){$end = microtime(true);}
		$end = time_or_exception($end);
		$start = time_or_exception($start);
		$duration = round($end - $start);
		$color = 'green';
		if($duration > 5){$color = 'orange';}
		if($duration > 10){$color = 'red';}
		if($duration){ConsoleLog::info($message." took ".round($duration)."s");}
		if(!self::available()){return;}
		// manually adding event with start and end time
		self::clock()->event($message)
			->start($start)
			->end($end)
			->color($color);
	}
	/**
	 * @param $message
	 */
	public static function logSlowOperation($message){
		if(!self::available()){return;}
		/** @noinspection PhpUndefinedMethodInspection */
		self::clock()->info($message, [ 'trace' => true, 'performance' => true]);
	}
	public static function logStart(string $name, array $data = []){
		if(!self::available()){return;}
		self::clock()->event($name, $data)->begin();
	}
	public static function logEnd(string $name, array $data = []){
		if(!self::available()){return;}
		self::clock()->event($name, $data)->end();
	}
	public static function meta(): array{
		$r = self::clock()->request();
		$arr = $r->toArray();
		unset($arr['logs']);
		return QMArr::notEmptyValues($arr);
	}
	private static function available():bool{
		if(!function_exists('clock')){
			return false;
		}
		try {
			clock();
			return true;
		} catch (\Throwable $e){
		    QMLog::error("clock function exists but we got this when we tried to call it: ".$e->getMessage());
		    return false;
		}
	}
	public static function open(){
		ConsoleLog::debug(__METHOD__);
		N8N::openUrl(self::getAppUrl());
	}
	/**
	 * Clockwork is enabled by default only when your application is in debug mode. Here you can explicitly enable or
	| disable Clockwork. When disabled, no data is collected and the api and web ui are inactive.
	 * Request|\Clockwork\Clockwork
	 */
	public static function enabled(){
		if(!function_exists('clock')){return false;}
		if(function_exists('config')){
			try {
				return config('clockwork.enable');
			} catch (\Throwable $e){
			    ConsoleLog::exception($e);
				return false;
			}
		}else {
			$enabled = EnvOverride::getFormatted(Env::CLOCKWORK_ENABLE);
			if($enabled === null){
				$enabled = Env::getFormatted(Env::CLOCKWORK_ENABLE);
			}
		}
		if($enabled && !self::available()){
			ConsoleLog::error("Clockwork enabled but clock function doesn't exist!");
			return false;
		}
        return self::clock();
    }
	/**
	 * @deprecated
	 * We store in MySQL now and it cleans old entries itself
	 */
	public static function clean(){
        QMLog::info(__METHOD__.": this seems to run out of memory sometimes");
        FileHelper::deleteFilesLike('storage/clockwork', true, '.json');
    }
    public static function saveResults(){
        if(QMClockwork::$alreadySaved){
            return;
        }
        self::$alreadySaved = true;
        $test = \App\Utils\AppMode::getCurrentTest();
        if(method_exists($test, 'saveClockwork')){
            if(!self::enabled()){
                QMLog::info("Not saving clockwork test profile because: ");
                EnvOverride::logValue(Env::CLOCKWORK_ENABLE);
                EnvOverride::logValue(Env::CLOCKWORK_TESTS_COLLECT);
                return;
            }
            $test->saveClockwork();
            self::logAppUrl();
        }
    }
    public static function collectTests(): bool{
		ConsoleLog::debug(__METHOD__.": Calling Env::get(Env::CLOCKWORK_TESTS_COLLECT)");
	    $var = Env::get(Env::CLOCKWORK_TESTS_COLLECT);
	    return $var ?? false;
    }
	public static function getButton(): ClockworkButton{
        return new ClockworkButton();
    }
    public static function getStoragePath(): string {
        return config('clockwork.storage_files_path');
    }
    /**
     * @return string
     */
    public static function getAppUrl(): string{
        return UrlHelper::getLocalUrl('__clockwork/app#');
    }
    public static function logAppUrl(): void{
        QMLog::info(self::getAppUrl());
    }
	/**
	 * @return mixed
	 */
	private static function getUserDetailsTab(){
		$tab = self::clock()->userData('user_details')->title('User Details');
		return $tab;
	}
	/**
	 * @return Request|\Clockwork\Clockwork
	 */
	public static function clock() {
		return clock();
	}
	/**
	 * @param array $meta
	 * @return array|mixed
	 */
	protected static function formatMeta(array $meta){
		if(array_key_exists(QMLog::ERROR_SPECIFIC_META, $meta)){
			$meta = $meta[QMLog::ERROR_SPECIFIC_META];
		}
		unset($meta['trace']);
		return $meta;
	}
}
