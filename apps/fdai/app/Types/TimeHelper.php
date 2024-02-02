<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Types;
use App\Exceptions\InvalidTimestampException;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\User;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\ReadonlyDB;
use App\Storage\DB\Writable;
use App\Utils\AppMode;
use App\Utils\Stats;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTime;
use Exception;
use Tests\QMAssert;
/** Class TimeHelper
 * @package App\Utils
 */
class TimeHelper {
	public const MOMENT_DATE                  = 'MMM D, YYYY';
	public const MOMENT_SIMPLE_DATE_TIME      = self::MOMENT_DATE . " hh:mm A";
	public const MOMENT_DAY_OF_WEEK_TIME      = "ddd, MMM D @ hA";
	public const RFC3339_ENABLED              = false; // Can't do this because MySQL says Invalid datetime format on inserts/updates
	public const YEAR_2000_UNIXTIME           = 946684800;
	public const YEAR_2030_UNIXTIME           = 1893521493;
	public const MONTH_DAY_HOUR_MINUTE        = 'M-d H:i';
	public const FORMAT_HOURS_MINUTES_SECONDS = 'H:i:s';
	public const DAY                          = 86400;
	const        AT_FORMAT                    = 'Y-m-d H:i:s';
	/**
	 * @var float|string
	 */
	private static $start;
	private static $carbons = [];
	private static $cache;
	/**
	 * @param int|null $epochUnixSeconds
	 * @return string
	 */
	public static function getIso8601UtcDateTimeString(int $epochUnixSeconds = null): string{
		if(!$epochUnixSeconds){
			$epochUnixSeconds = time();
		}
		return date(self::AT_FORMAT, $epochUnixSeconds);
	}
	/**
	 * @param int|string|null $timeAt
	 * @param bool $validate
	 * @return string
	 */
	public static function YYYYmmddd($timeAt = null, bool $validate = false): string{
		if($timeAt instanceof CarbonInterface){
			return $timeAt->toDateString();
		}
		if(isset(static::$cache[__FUNCTION__][$timeAt])){
			return static::$cache[__FUNCTION__][$timeAt];
		}
		if(!$timeAt){
			$timeAt = $epochUnixSeconds = time();
		} else{
			$epochUnixSeconds = self::universalConversionToUnixTimestamp($timeAt, $validate);
		}
		return static::$cache[__FUNCTION__][$timeAt] = date('Y-m-d', $epochUnixSeconds);
	}
	/**
	 * @param int|string|null $timeAt
	 * @param bool $validate
	 * @return string
	 */
	public static function getYmdHourMinuteString($timeAt = null, bool $validate = false): string{
		if(!$timeAt){
			$epochUnixSeconds = time();
		} else{
			$epochUnixSeconds = self::universalConversionToUnixTimestamp($timeAt, $validate);
		}
		return date('Y-m-d-H:i', $epochUnixSeconds);
	}
	/**
	 * @param int|string|null $timeAt
	 * @param bool $validate
	 * @return string
	 */
	public static function getDayOfWeekString($timeAt = null, bool $validate = false): string{
		if(!$timeAt){
			$epochUnixSeconds = time();
		} else{
			$epochUnixSeconds = self::universalConversionToUnixTimestamp($timeAt, $validate);
		}
		return date('D', $epochUnixSeconds);
	}
	/**
	 * @param int|string|null $timeAt
	 * @param bool $validate
	 * @return string
	 */
	public static function getMonthString($timeAt = null, bool $validate = false): string{
		if(!$timeAt){
			$epochUnixSeconds = time();
		} else{
			$epochUnixSeconds = self::universalConversionToUnixTimestamp($timeAt, $validate);
		}
		return date('M', $epochUnixSeconds);
	}
	/**
	 * @param int|string|null $timeAt
	 * @param bool $validate
	 * @return string
	 */
	public static function getDayOfMonth($timeAt = null, bool $validate = false): string{
		if(!$timeAt){
			$epochUnixSeconds = time();
		} else{
			$epochUnixSeconds = self::universalConversionToUnixTimestamp($timeAt, $validate);
		}
		return date('j', $epochUnixSeconds);
	}
	/**
	 * @param int|string|null $timeAt
	 * @return string
	 */
	public static function getWeekdayAndDateString($timeAt = null): string{
		return self::getDayOfWeekString($timeAt) . ' ' . self::getMonthDayString($timeAt);
	}
	/**
	 * @param int|string|null $timeAt
	 * @return string
	 */
	public static function getMonthDayString($timeAt = null): string{
		return self::getMonthString($timeAt) . ' ' . self::getDayOfMonth($timeAt);
	}
	/**
	 * @param int|string|null $timeAt
	 * @return string
	 */
	public static function getHourWeekdayAndDateString($timeAt = null): string{
		return self::getHourAmPm($timeAt) . ' ' . self::getWeekdayAndDateString($timeAt);
	}
	/**
	 * @param int|string|null $timeAt
	 * @return string
	 */
	public static function getHourAndDateString($timeAt = null): string{
		if(!$timeAt){
			$timeAt = time();
		}
		return self::getHourAmPm($timeAt) . ' ' . self::getMonthDayString($timeAt);
	}
	/**
	 * @param $timestamp
	 * @param bool $validate
	 * @return bool
	 */
	public static function isUnixTimestamp($timestamp, bool $validate = true): bool{
		if($timestamp instanceof DateTime){
			return false;
		}
		if(is_string($timestamp)){
			if(strpos($timestamp, "-") !== false){
				return false;
			}
			if(strpos($timestamp, ":") !== false){
				return false;
			}
			if(strpos($timestamp, "/") !== false){
				return false;
			}
		}
		$int = (int)$timestamp;
		if($validate){
			return self::timestampIsReasonable($int);
		} else{
			return $int > 2030 && $int < self::YEAR_2030_UNIXTIME;
		}
	}
	/**
	 * @param int $timestamp
	 * @return bool
	 */
	public static function timestampIsReasonable(int $timestamp): bool{
		if($timestamp > self::YEAR_2030_UNIXTIME){
			return false;
		}
		if($timestamp < 1){
			return false;
		}
		return true;
	}
	/**
	 * @param $timestamp
	 * @return bool
	 */
	public static function isUnixMilliseconds($timestamp): bool{
		if(!is_numeric($timestamp)){
			return false;
		}
		if(is_string($timestamp) && strpos($timestamp, '-') !== false){
			return false;
		}
		if(is_string($timestamp) && strpos($timestamp, '+') !== false){
			return false;
		}
		if(is_string($timestamp) && strpos($timestamp, ' ') !== false){
			return false;
		}
		try {
			$timestamp /= 1000;
		} catch (Exception $e) {
			QMLog::error("Could not divide $timestamp : " . $e->getMessage());
			return false;
		}
		return self::isUnixTimestamp($timestamp);
	}
	/**
	 * @param string|int|CarbonInterface $originalProvidedTimestamp
	 * @param bool $validate
	 * @return int
	 * @throws InvalidTimestampException
	 */
	public static function universalConversionToUnixTimestamp($originalProvidedTimestamp, bool $validate = true){
		if($originalProvidedTimestamp === "0000-00-00 00:00:00"){
			$originalProvidedTimestamp = 0;
		}
		if($originalProvidedTimestamp === null){
			$errorMessage = "originalProvidedTimestamp should not be null!";
			if(!$validate){
				QMLog::error($errorMessage);
				return null;
			}
			throw new InvalidTimestampException($errorMessage);
		}
		if($originalProvidedTimestamp instanceof DateTime){
			return $originalProvidedTimestamp->getTimestamp();
		}
		if(self::isUnixTimestamp($originalProvidedTimestamp, $validate)){
			return (int)$originalProvidedTimestamp;
		}
		if(self::isUnixMilliseconds($originalProvidedTimestamp)){
			return $originalProvidedTimestamp / 1000;
		}
		$str = (string)$originalProvidedTimestamp;
		if(strlen($originalProvidedTimestamp) === 4 && strpos($str, "20") === 0){
			$originalProvidedTimestamp = $originalProvidedTimestamp . "-01-01";
		}
		$convertedTimestamp = strtotime($originalProvidedTimestamp);
		if(self::isUnixTimestamp($convertedTimestamp, $validate)){
			return $convertedTimestamp;
		}
		$providedTimestampWithoutSlashes = str_replace('/', '-', $originalProvidedTimestamp);
		$unixTimestampWithoutSlashes = strtotime($providedTimestampWithoutSlashes);
		if(self::isUnixTimestamp($unixTimestampWithoutSlashes, $validate)){
			return $unixTimestampWithoutSlashes;
		}
		$providedTimestampWithSlashes = str_replace('-', '/', $originalProvidedTimestamp);
		$unixTimestampWithSlashes = strtotime($providedTimestampWithSlashes);
		if(self::isUnixTimestamp($unixTimestampWithSlashes, $validate)){
			return $unixTimestampWithSlashes;
		}
		if(is_int($originalProvidedTimestamp) && $originalProvidedTimestamp < time() && AppMode::isTestingOrStaging()){
			return $originalProvidedTimestamp;
		}
		if(strpos($originalProvidedTimestamp, '(') !== false){
			// For Fri, 2 Nov 2018 15:53:29 -0700 (GMT-07:00) from emails
			$abbreviatedTimeString = QMStr::before('(', $originalProvidedTimestamp, $originalProvidedTimestamp);
			$abbreviatedTimeString = QMStr::after(',', $abbreviatedTimeString, $abbreviatedTimeString);
			$abbreviatedUnixtime = strtotime($abbreviatedTimeString);
			if(self::isUnixTimestamp($abbreviatedUnixtime, $validate)){
				return $abbreviatedUnixtime;
			}
		}
		$errorMessage =
			"Could not identify time format for: $originalProvidedTimestamp. Please use Unix timestamps or ISO8601 format";
		if(!$validate){
			QMLog::error($errorMessage);
			return null;
		}
		throw new InvalidTimestampException($errorMessage);
	}
	/**
	 * @param $epochUnixTimeSeconds
	 * @return string
	 */
	public static function convertEpochUnixTimeSecondsToTimeHHMMSS($epochUnixTimeSeconds): string{
		return date('H:i:s', $epochUnixTimeSeconds);
	}
	/**
	 * @return string
	 */
	public static function getCurrentUtcTimeStringHHMMSS(): string{
		return date('H:i:s', time());
	}
	/**
	 * @param string|int $timeStringOrUnixEpochSeconds
	 * @return float|int
	 */
	public static function hoursAgo($timeStringOrUnixEpochSeconds): int{
		return round((time() - self::universalConversionToUnixTimestamp($timeStringOrUnixEpochSeconds)) / 3600);
	}
	/**
	 * @param string|int $timeStringOrUnixEpochSeconds
	 * @return float|string
	 */
	public static function daysAgo($timeStringOrUnixEpochSeconds){
		if(!$timeStringOrUnixEpochSeconds){
			return "Infinite";
		}
		return round((time() - self::universalConversionToUnixTimestamp($timeStringOrUnixEpochSeconds)) / 86400);
	}
	/**
	 * @param string|int $timeStringOrUnixEpochSeconds
	 * @return float|null
	 */
	public static function minutesAgo($timeStringOrUnixEpochSeconds): ?float{
		if(self::isZeroTime($timeStringOrUnixEpochSeconds)){
			return null;
		}
		return round((time() - self::universalConversionToUnixTimestamp($timeStringOrUnixEpochSeconds)) / 60);
	}
	/**
	 * @param string|int $timeStringOrUnixEpochSeconds
	 * @param int|null $baseTime
	 * @return float
	 * @throws InvalidTimestampException
	 */
	public static function secondsAgo($timeStringOrUnixEpochSeconds, int $baseTime = null): float{
		if(!$baseTime){
			$baseTime = time();
		}
		return round($baseTime - self::universalConversionToUnixTimestamp($timeStringOrUnixEpochSeconds));
	}
	/**
	 * @param string|int $timeStringOrUnixEpochSeconds
	 * @param int|null $baseTime
	 * @return string
	 */
	public static function timeSinceHumanStringHtml($timeStringOrUnixEpochSeconds, int $baseTime = null): string{
		$since = self::timeSinceHumanString($timeStringOrUnixEpochSeconds, $baseTime);
		if($timeStringOrUnixEpochSeconds){
			$date = db_date($timeStringOrUnixEpochSeconds) . " UTC";
		} else{
			$date = $since;
		}
		return "<span title=\"$date\">$since</span>";
	}
	/**
	 * @param string|int $timeStringOrUnixEpochSeconds
	 * @param int|null $baseTime
	 * @return string
	 */
	public static function timeSinceHumanString($timeStringOrUnixEpochSeconds, int $baseTime = null): string{
		if(self::isZeroTime($timeStringOrUnixEpochSeconds)){
			return "never";
		}
		$secondsAgo = self::secondsAgo($timeStringOrUnixEpochSeconds, $baseTime);
		if($baseTime){
			if($secondsAgo < 0){
				return self::convertSecondsToHumanString(-1 * $secondsAgo) . " after";
			}
			return self::convertSecondsToHumanString($secondsAgo) . " before";
		}
		if($secondsAgo < 0){
			return self::convertSecondsToHumanString(-1 * $secondsAgo) . " from now";
		}
		if($secondsAgo > 86400 && $secondsAgo < 2 * 86400){
			return "yesterday";
		}
		return self::convertSecondsToHumanString($secondsAgo) . " ago";
	}
	/**
	 * @param int $seconds
	 * @return string
	 */
	public static function convertSecondsToHumanString(float $seconds): ?string{
		if($seconds == 0){
			return "0 seconds";
		}
		$negative = $seconds < 0;
		if($negative){
			$seconds = -1 * $seconds;
		}
		if($seconds > 2 * 24 * 60 * 60 * 365){
			$str = round($seconds / (24 * 60 * 60 * 365)) . " years";
		} elseif($seconds > 2 * 24 * 60 * 60 * 30){
			$str = round($seconds / (24 * 60 * 60 * 30)) . " months";
		} elseif($seconds > 2 * 24 * 60 * 60){
			$str = round($seconds / (24 * 60 * 60)) . " days";
		} elseif($seconds > 2 * 60 * 60){
			$str = round($seconds / (60 * 60)) . " hours";
		} elseif($seconds > 2 * 60){
			$str = round($seconds / 60) . " minutes";
		} elseif($seconds >= 1){
			$str = round($seconds) . " seconds";
		} elseif($seconds >= 0.001){
			$str = round($seconds * 1000) . " milliseconds";
		} else{
			$str = round($seconds * 1000000) . " microseconds";
		}
		if($negative){
			return "negative " . $str;
		}
		return $str;
	}
	public static function checkForDbServerTimeDiscrepancy(){
		$rdsNow = ReadonlyDB::db()->select('SELECT NOW() as now');
		$dbTime = $rdsNow[0]->now;
		$phpTime = date(self::AT_FORMAT);
		QMLog::info("DB time is " . $dbTime);
		QMLog::info("PHP time is " . $phpTime);
		if($dbTime !== $phpTime){
			QMLog::error('DB TIME <> ' . gethostname() . ' TIME ', [
				'DB' => $dbTime,
				gethostname() => $phpTime,
			]);
		}
	}
	/**
	 * @param int|string $timeAt
	 * @return bool
	 */
	public static function isZeroTime($timeAt): bool{
		if(!$timeAt){
			return true;
		}
		if("0000-00-00 00:00:00" === $timeAt){
			return true;
		}
		if("1970-01-01 00:00:00" === $timeAt){
			return true;
		}
		//if("1970-01-01 00:00:01" === $timeAt){return true;}
		//if(is_string($timeAt) && strpos($timeAt, "1970-") === 0){return true;}
		return false;
	}
	/**
	 * @param int|string $timeAt
	 * @param QMUser|int|null $user
	 * @return string
	 */
	public static function getHourAmPm($timeAt, $user = null): string{
		if(!$timeAt){
			le("Please provide timeStringOrUnixEpochSeconds");
		}
		if(is_int($user)){
			$user = QMUser::find($user);
		}
		if($user){
			$utcTimestamp = self::universalConversionToUnixTimestamp($timeAt);
			$carbon = $user->convertToLocalTimezone($utcTimestamp);
			$amPm = $carbon->format('g:iA');
		} else{
			$time = self::universalConversionToUnixTimestamp($timeAt);
			$amPm = date('g:iA', $time);
		}
		return str_replace(':00', '', $amPm);
	}
	/**
	 * @param int|string $timeStringOrUnixEpochSeconds
	 * @param QMUser|User $user
	 * @return string
	 */
	public static function getMilitaryHourMinute($timeStringOrUnixEpochSeconds = null, $user = null): string{
		if(!$timeStringOrUnixEpochSeconds){
			$timeStringOrUnixEpochSeconds = time();
		}
		if($user){
			$time = self::universalConversionToUnixTimestamp($timeStringOrUnixEpochSeconds) -
				$user->getTimeZoneOffsetInSeconds();
		} else{
			$time = self::universalConversionToUnixTimestamp($timeStringOrUnixEpochSeconds);
		}
		$time = date('H:i', $time);
		return $time;
	}
	/**
	 * @param $value
	 * @return bool
	 */
	public static function isDate($value): bool{
		$timestamp = strtotime($value);
		if($timestamp > time() - 86400 * 365 * 10 && $timestamp < time()){
			return true;
		}
		if(self::validateSingleDateFormat($value, 'Y-m-d')){
			return true;
		}
		if(self::validateSingleDateFormat($value, 'd/m/Y')){
			return true;
		}
		if(self::validateSingleDateFormat($value, 'Y-m-d\TH:i:sP')){
			return true;
		}
		if(self::validateSingleDateFormat($value, DateTime::ATOM)){
			return true;
		}
		if(self::validateSingleDateFormat($value, 'D, d M Y H:i:s O')){
			return true;
		}
		if(self::validateSingleDateFormat($value, DateTime::RSS)){
			return true;
		}
		return false;
	}
	/**
	 * @param $date
	 * @param string $format
	 * @return bool
	 */
	private static function validateSingleDateFormat($date, string $format = 'Y-m-d H:i:s'): bool{
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}
	/**
	 * @param object $object
	 * @return object
	 */
	public static function convertAllDateTimeValuesToRFC3339(object $object): object{
		if(self::RFC3339_ENABLED){ // Can't do this because MySQL says Invalid datetime format on inserts/updates
			foreach($object as $key => $value){
				if(strpos($key, 'Time') === false && strpos($key, 'At') === false){
					continue;
				}
				if(strpos($value, ':') === false){
					continue;
				}
				if(strpos($value, '-') === false){
					continue;
				}
				if(self::isZeroTime($value)){
					continue;
				}
				$unixTime = self::universalConversionToUnixTimestamp($value);
				$datetime = DateTime::createFromFormat('U', $unixTime);
				$rfc = $datetime->format(DateTime::RFC3339);
				$object->$key = $rfc;
			}
		}
		return $object;
	}
	/**
	 * @param float $hoursSinceMidnight
	 * @return string
	 */
	public static function morningNoonOrNight(float $hoursSinceMidnight): string{
		while($hoursSinceMidnight < 0){
			$hoursSinceMidnight = $hoursSinceMidnight + 24;
		}
		if($hoursSinceMidnight < 12){
			return 'morning';
		}
		if($hoursSinceMidnight < 17){
			return 'afternoon';
		}
		return 'evening';
	}
	/**
	 * @param int|null $unixtime
	 * @param string $type
	 * @return bool
	 */
	public static function earlierThan2000(?int $unixtime, string $type): bool{
		if($unixtime === null){
			return false;
		}
		if($unixtime < self::YEAR_2000_UNIXTIME){
			QMLog::errorOrInfoIfTesting("$type is " . date(self::AT_FORMAT, $unixtime));
			return true;
		}
		return false;
	}
	/**
	 * @param int|null $unixtime
	 * @param string $type
	 */
	public static function exceptionIfEarlierThan2000(?int $unixtime, string $type){
		if(self::earlierThan2000($unixtime, $type)){
			throw new InvalidTimestampException("$type should not be earlier than the year 2000: $unixtime");
		}
	}
	/**
	 * @param $value
	 * @return bool
	 */
	public static function isCarbon($value): bool{
		if($value instanceof Carbon){
			return true;
		}
		if($value instanceof CarbonInterface){
			return true;
		}
		return false;
	}
	/**
	 * @param int|string|null $timeAt
	 * @return string
	 * @throws InvalidTimestampException
	 */
	public static function YmdHis($timeAt): string{
		if($timeAt === null){
			le("please provide time or use now_at() for current time");
		}
		if(is_string($timeAt) && strpos($timeAt, "-") === 4){
			if(strlen($timeAt) === 10){
				return $timeAt . " 00:00:00";
			}
			if(strlen($timeAt) === 19){
				return $timeAt;
			}
		}
		$unixtime = self::universalConversionToUnixTimestamp($timeAt);
		return date(self::AT_FORMAT, $unixtime);
	}
	/**
	 * @return int
	 */
	public static function getCurrentYear(): int{
		return date('Y');
	}
	/**
	 * @param $atOrTime
	 * @return int
	 */
	public static function roundToLastMidnightTimestamp($atOrTime): int{
		$int = self::universalConversionToUnixTimestamp($atOrTime);
		return floor($int / 86400) * 86400;
	}
	public static function getYesterdayMidnightTimestamp(): int{
		return self::roundToLastMidnightTimestamp(time() - 86400);
	}
	public static function getYesterdayMidnightAt(): string{
		return db_date(self::getYesterdayMidnightTimestamp());
	}
	public static function getLastMidnightAt(): string{
		return db_date(self::roundToLastMidnightTimestamp(time()));
	}
	/**
	 * Create a Carbon instance from a timestamp.
	 * @param $datetime
	 * @param null $tz
	 * @return CarbonInterface|Carbon
	 */
	public static function toCarbon($datetime, $tz = null): CarbonInterface{
		if($datetime instanceof CarbonInterface){
			return $datetime;
		}
		if(isset(self::$carbons[$datetime])){
			return clone self::$carbons[$datetime];
		}
		$t = self::universalConversionToUnixTimestamp($datetime);
		return self::$carbons[$datetime] = Carbon::createFromTimestamp($t, $tz);
	}
	/**
	 * @param $expected
	 * @param $actual
	 * @param string|null $expectedName
	 * @param string|null $actualName
	 * @return bool
	 */
	public static function dateEquals($expected, $actual, string $expectedName = null, string $actualName = null): bool{
		if(!$actual){
			le("No actual $actualName date provided to " . __FUNCTION__);
		}
		if(!$expected){
			le("No expected $expectedName date provided to " . __FUNCTION__);
		}
		$expectedDate = db_date($expected);
		$actualDate = db_date($actual);
		return $expectedDate === $actualDate;
	}
	/**
	 * @param $expected
	 * @param $actual
	 * @return bool
	 */
	public static function dateLessThanOrEqual($expected, $actual): bool{
		$expectedDate = db_date($expected);
		$actualDate = db_date($actual);
		return $expectedDate <= $actualDate;
	}
	/**
	 * @param $expected
	 * @param $actual
	 * @return bool
	 */
	public static function dateLessThan($expected, $actual): bool{
		$expectedDate = db_date($expected);
		$actualDate = db_date($actual);
		return $expectedDate < $actualDate;
	}
	/**
	 * @param $expected
	 * @param $actual
	 * @return bool
	 */
	public static function dateGreaterThanOrEqual($expected, $actual): bool{
		$expectedDate = db_date($expected);
		$actualDate = db_date($actual);
		return $expectedDate <= $actualDate;
	}
	/**
	 * @param $expected
	 * @param $actual
	 * @return bool
	 */
	public static function dateGreaterThan($expected, $actual): bool{
		$expectedDate = db_date($expected);
		$actualDate = db_date($actual);
		return $expectedDate < $actualDate;
	}
	/**
	 * @return float Seconds since this function was first called
	 */
	public static function getDuration(): float{
		if(!static::$start){
			static::$start = microtime(true);
		}
		$sec = microtime(true) - static::$start;
		return round(1000 * $sec) / 1000;
	}
	public static function validateDBTimeZone(){
		$time = time();
		$dbDateTime = Writable::now();
		$tz = Writable::selectStatic("SELECT @@global.time_zone as value;");
		ConsoleLog::info("MySQL time is " . $dbDateTime . " and TZ is " . $tz[0]->value);
		ConsoleLog::info("PHP time is " . TimeHelper::getMilitaryHourMinute(time()));
		$dbTime = strtotime($dbDateTime);
		$diff = $dbTime - $time;
		$mess = "DB time is $diff seconds different from time()!\n\t" . "Try:\n\tUpdating Windows time" .
			"Try:\n\tsudo ntpdate time.windows.com" . "Try:\n\tsudo service mysql restart";
		if($diff > 5){
			le($mess);
		}
		if($diff < -5){
			le($mess);
		}
	}
	public static function getTomorrowUnixTime(): int{
		return time() + 86400;
	}
	/**
	 * @param int|string $timeAt
	 * @param int $seconds
	 * @return int
	 */
	public static function roundToNearestXSeconds($timeAt, int $seconds): int{
		$unixtime = self::universalConversionToUnixTimestamp($timeAt);
		$rounded = Stats::roundToNearestMultipleOf($unixtime, $seconds);
		return $rounded;
	}
	public static function isAtAttribute(string $name): bool{
		return in_array($name, [
			BaseModel::FIELD_DELETED_AT,
			BaseModel::UPDATED_AT,
			BaseModel::CREATED_AT,
		]);
	}
	/**
	 * @param $expectedTimeAt
	 * @param $actualTimeAt
	 * @param string|null $expectedName
	 * @param string|null $actualName
	 * @param string $message
	 */
	public static function assertDateEquals($expectedTimeAt, $actualTimeAt, string $expectedName = null,
		string $actualName = null, string $message = ''){
		QMAssert::assertDateEquals($expectedTimeAt, $actualTimeAt, $expectedName, $actualName, $message);
	}
	/**
	 * @param string|int|null $timeAt
	 * @return string
	 */
	public static function humanTime($timeAt = null, string $timezone = null): string{
		if(!$timeAt){
			$timeAt = time();
		}
		$unixtime = self::universalConversionToUnixTimestamp($timeAt);
		if($timezone){
			$c = self::convertToTimezone($unixtime, $timezone);
			$unixtime = $c->getTimestamp();
		}
		$str = self::getHourAndDateString($timeAt);
		if($unixtime < time() - 86400 * 365){
			$str .= ", " . self::year($unixtime);
		}
		return $str;
	}
	private static function year(int $unixtime): int{
		return date("Y", $unixtime);
	}
	/**
	 * @param $timeAt
	 * @return int|null
	 */
	public static function timeOrNull($timeAt): ?int{
		if(!$timeAt){
			return null;
		}
		if(is_float($timeAt)){
			return (int)$timeAt;
		} // Handles 1606901541.677 or strtotime returns 0
		if(is_int($timeAt)){
			return $timeAt;
		}
		if(is_string($timeAt) && is_numeric($timeAt) && self::timestampIsReasonable($timeAt)){
			return (int)$timeAt;
		}
		if(TimeHelper::isCarbon($timeAt)){
			/** @var CarbonInterface $timeAt */
			return $timeAt->timestamp;
		}
		if(is_object($timeAt)){
			le("should not be an object", $timeAt);
		}
		return strtotime($timeAt);
	}
	/**
	 * @param $from
	 * @param $to
	 * @return float
	 */
	public static function yearsBetween($from, $to): float{
		$seconds = time_or_exception($to) - time_or_exception($from);
		return $seconds / (365 * 86400);
	}
	/**
	 * @param $from
	 * @param $to
	 * @return float
	 */
	public static function daysBetween($from, $to): float{
		$seconds = time_or_exception($to) - time_or_exception($from);
		return $seconds / (86400);
	}
	/**
	 * @param $to
	 * @return bool
	 */
	public static function withinLast24Hours($to): bool{
		$time = self::universalConversionToUnixTimestamp($to);
		return $time > (time() - 86400);
	}
	/**
	 * @param float $minHours
	 * @param $timeAt
	 * @return bool
	 */
	public static function inLastXHours(float $minHours, $timeAt): bool{
		$time = time_or_exception($timeAt);
		$diff = time() - $time;
		$diffHours = $diff / 3600;
		return $diffHours < $minHours;
	}
	/**
	 * @param $timeAt
	 * @return bool
	 */
	public static function wasToday($timeAt): bool{
		$time = time_or_exception($timeAt);
		$yesterday = time() - 86400;
		$tomorrow = time() + 86400;
		return $time < $tomorrow && $time > $yesterday;
	}
	/**
	 * @param $from
	 * @param $to
	 * @return int
	 */
	public static function diffInSeconds($from, $to): int{
		return time_or_exception($to) - time_or_exception($from);
	}
	/**
	 * @param int|string $timeAt
	 * @param QMUser|int|null $user
	 * @return string
	 */
	public static function humanTimeOfDay($timeAt = null, $user = null): string{
		$timeAt = $timeAt ?? time();
		return self::getHourAmPm($timeAt, $user);
	}
	public static function humanDate(int $unixtime): string{
		if(self::isThisYear($unixtime)){
			return date("F j", $unixtime);
		}
		return date("F j, Y", $unixtime);
	}
	private static function isThisYear(int $unixtime): bool{
		return self::year($unixtime) === self::year(time());
	}
	public static function monthNumberToName(int $monthNumber): string{
		if(isset(static::$cache[__FUNCTION__][$monthNumber])){
			return static::$cache[__FUNCTION__][$monthNumber];
		}
		return static::$cache[__FUNCTION__][$monthNumber] = date("F", mktime(0, 0, 0, $monthNumber, 1, 2011));
	}
	public static function yesterday(): string{ return db_date(time() - 86400); }
	/**
	 * @param $actual
	 * @param $earliest
	 */
	public static function assertLaterThan($actual, $earliest){
		if(time_or_exception($actual) < time_or_exception($earliest)){
			le("$actual should be later than $earliest");
		}
	}
	/**
	 * @param $actual
	 * @param $latest
	 */
	public static function assertEarlierThan($actual, $latest){
		if(time_or_exception($actual) > time_or_exception($latest)){
			le("$actual should be earlier than $latest");
		}
	}
	/**
	 * @param $actual
	 */
	public static function assertPast($actual){
		self::assertEarlierThan($actual, now_at());
	}
	public static function assertPastOrNow($actual){
		if(time_or_exception($actual) === time()){
			return;
		}
		self::assertEarlierThan($actual, now_at());
	}
	public static function getRequestTime(): int{
		return QMRequest::getRequestTime();
	}
	/**
	 * @param $old
	 * @param $new
	 * @return bool
	 */
	public static function isSame($old, $new): bool{
		return self::timeOrNull($old) === self::timeOrNull($new);
	}
	/**
	 * @param $input
	 * @param bool $associative
	 * @return string|array|object
	 */
	public static function stripDatesAndTimes($input, bool $associative){
		if(is_string($input)){
			return  QMStr::removeDatesAndTimes($input);
		}   
		$json = json_encode($input);
		$json = QMStr::removeDatesAndTimes($json);
		return json_decode($json, $associative);
	}
	public static function isPast($timeAt): bool{
		return time_or_exception($timeAt) < time();
	}
	public static function convertToTimezone(float|int|string $timeAt, string $timezone): CarbonInterface {
		$unixtime = time_or_exception($timeAt);
		$carbon = Carbon::createFromTimestamp($unixtime, $timezone);
		return $carbon;
	}
	public static function hourMinute(int|string|float $timeAt, string $timezone = null): string{
		$carbon = static::toCarbon($timeAt, $timezone);
		return $carbon->format("g:i a");
	}
    public static function inLastXMinutes($timeAt, int $mins): bool{
		$time = time_or_exception($timeAt);
		$diff = time() - $time;
		$diffMinutes = $diff / 60;
		return $diffMinutes < $mins;
    }
}
