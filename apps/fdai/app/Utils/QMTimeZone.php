<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
use App\Exceptions\BadRequestException;
use App\Http\Controllers\DataLab\UserController;
use App\Logging\QMLog;
use App\UI\Alerter;
use App\UI\FontAwesome;
use Carbon\Carbon;
use Illuminate\Support\Arr;
/** DOCS => https://github.com/jamesmills/laravel-timezone
 */
class QMTimeZone {
	const EUROPE_HELSINKI = "Europe/Helsinki";
	const AFRICA_ABIDJAN = "Africa/Abidjan";
	/**
	 * @var
	 */
	public static $timezone_abbreviations_list;
	/**
	 * @var
	 */
	public $currentTime;
	/**
	 * @var
	 */
	public $dstSavings;
	/**
	 * @var
	 */
	public $isDst;
	/**
	 * @var
	 */
	public $name;
	/**
	 * @var
	 */
	public $offset;
	/**
	 * @param int $timeZoneOffsetInMinutes
	 * @return string|null
	 */
	public static function convertTimeZoneOffsetToStringAbbreviation(int $timeZoneOffsetInMinutes): ?string{
		$hoursAfterUTC = (int)($timeZoneOffsetInMinutes / 60) * -1;
		$secondsAfterUTC = $hoursAfterUTC * 3600; // adjust UTC offset from hours to seconds
		if($timezone = timezone_name_from_abbr('', $secondsAfterUTC)){
			return $timezone; // attempt to guess the timezone string from the UTC offset
		}
		$matches = self::getMatchingTimeZonesByContinent($secondsAfterUTC);
		$best = self::getMostPopulatedMatch($matches);
		if(!$best){
			QMLog::error("Could not determine time zone for $secondsAfterUTC offset! Using UTC...");
		}
		return $best;
	}
	/**
	 * @param array $matches
	 * @return string|null
	 */
	private static function getMostPopulatedMatch(array $matches): ?string{
		if(!$matches){
			le("No matches!");
		}
		if(isset($matches["Europe"])){
			if(in_array(self::EUROPE_HELSINKI, $matches["Europe"])){
				return self::EUROPE_HELSINKI;
			}
			if(in_array("Europe/Amsterdam", $matches["Europe"])){
				return "Europe/Amsterdam";
			}
			if(in_array("Europe/London", $matches["Europe"])){
				return "Europe/London";
			}
			return $matches["Europe"][0];
		}
		if(isset($matches["America"])){
			if(in_array("America/Chicago", $matches["America"])){
				return "America/Chicago";
			}
			if(in_array("America/Halifax", $matches["America"])){
				return "America/Halifax";
			}
			if(in_array("America/Denver", $matches["America"])){
				return "America/Denver";
			}
			return $matches["America"][0];
		}
		if(isset($matches["Australia"])){
			if(in_array("Australia/Melbourne", $matches["Australia"])){
				return "Australia/Melbourne";
			}
			if(in_array("Australia/Perth", $matches["Australia"])){
				return "Australia/Perth";
			}
			return $matches["Australia"][0];
		}
		if(isset($matches["Asia"])){
			if(in_array("Asia/Karachi", $matches["Asia"])){
				return "Asia/Karachi";
			}
			return $matches["Asia"][0];
		}
		if(isset($matches["Africa"])){
			return $matches["Africa"][0];
		}
		return Arr::first(Arr::flatten($matches, 1));
	}
	/**
	 * @return array
	 */
	public static function timezone_abbreviations_list(): array{
		if(self::$timezone_abbreviations_list){
			return self::$timezone_abbreviations_list;
		}
		return self::$timezone_abbreviations_list =
			timezone_abbreviations_list(); // Uses lots of memory to keep calling it
	}
	/**
	 * @param string $timezone
	 */
	public static function notify(string $timezone){
		if(config('timezone.flash') == 'off'){
			return;
		}
		$message = 'We have set your timezone to ' . $timezone;
		Alerter::toastWithButton($message, UserController::getEditProfileUrl(), "Settings", FontAwesome::SETTINGS);
		if(config('timezone.flash') == 'laravel'){
			request()->session()->flash('success', $message);
			return;
		}
		if(config('timezone.flash') == 'laracasts'){
			flash()->success($message);
			return;
		}
		if(config('timezone.flash') == 'mercuryseries'){
			flashy()->success($message);
			return;
		}
		if(config('timezone.flash') == 'spatie'){
			flash()->success($message);
			return;
		}
		if(config('timezone.flash') == 'mckenziearts'){
			notify()->success($message);
			return;
		}
	}
	/**
	 * The time-zone offset is the difference, in minutes, between UTC and local time. Note that this means that
	 * the offset is positive if the local timezone is behind UTC (i.e. UTC−06:00 Central) and negative if it is ahead.
	 * @param int $minutesOrSecondsBeforeUTC
	 * @param string $timezone
	 */
	public static function validateTimeZoneOffsetToAbbreviation(int $minutesOrSecondsBeforeUTC, string $timezone){
		if($timezone && $minutesOrSecondsBeforeUTC < 0 && strpos($timezone, 'America') !== false){
			le("$minutesOrSecondsBeforeUTC not valid for timezone $timezone");
		}
	}
	/**
	 * @param string $timezoneAbbreviation
	 * @return int
	 */
	public static function timeZoneAbbreviationToOffsetInMinutes(string $timezoneAbbreviation): int{
		$carbon = Carbon::createFromTimestamp(time(), $timezoneAbbreviation);
		//Returns the timezone offset in seconds from UTC on success or FALSE on failure.
		$secondsBeforeUTC = -1 * $carbon->getOffset();
		$minutesBeforeUTC = $secondsBeforeUTC / 60;
		QMTimeZone::validateTimeZoneOffsetToAbbreviation($minutesBeforeUTC, $timezoneAbbreviation);
		return $minutesBeforeUTC;
	}
	/**
	 * @param int $offsetInMinutes
	 */
	public static function validateOffset(int $offsetInMinutes){
		if($offsetInMinutes < -1440){
			throw new BadRequestException('timeZoneOffset should be provided in minutes and cannot be less than -1440');
		}
		if($offsetInMinutes > 1440){
			throw new BadRequestException('timeZoneOffset should be provided in minutes and cannot be greater than 1440');
		}
	}
	/**
	 * @param int $secondsAfterUTC
	 * @return array
	 */
	public static function getMatchingTimeZonesByContinent(int $secondsAfterUTC): array{
		$is_daylight_savings_time = (bool)date('I'); // last try, guess timezone string manually
		$list = self::timezone_abbreviations_list();
		$matchesWithDST = $matchesNoDST = $roundedToHour = [];
		$hours = $secondsAfterUTC / 3600;
		foreach($list as $abbr){
			foreach($abbr as $city){
				$abbreviation = $city['timezone_id'];
				if(!$abbreviation){
					continue;
				}
				$cityHours = (int)$city['offset'] / 3600;
				$cityRounded = (int)round($cityHours);
				if($cityRounded == (int)$hours){
					$continent = explode('/', $abbreviation)[0];
					$roundedToHour[$continent][] = $abbreviation;
					$offsetMatches = (int)$cityHours == (int)$hours;
					if($offsetMatches){
						$matchesNoDST[$continent][] = $abbreviation;
						$dstMatches = (bool)$city['dst'] === $is_daylight_savings_time;
						if($dstMatches){
							$matchesWithDST[$continent][] = $abbreviation;
						}
					}
				}
			}
		}
		if($matchesWithDST){
			return $matchesWithDST;
		} elseif($matchesNoDST){
			return $matchesNoDST;
		} else{
			return $roundedToHour;
		}
	}
}
