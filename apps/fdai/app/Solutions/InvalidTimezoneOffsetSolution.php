<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Models\User;
use App\Utils\QMTimeZone;
class InvalidTimezoneOffsetSolution extends BaseRunnableSolution {
	public function getSolutionTitle(): string{
		return "Fix Invalid Timezone Offsets";
	}
	public function getSolutionDescription(): string{
		return "Fix Invalid Timezone Offsets";
	}
	public function run(array $parameters = []){
		/** @var User[] $users */
		$users = User::getInvalidRecordForAttribute(User::FIELD_TIME_ZONE_OFFSET);
		foreach($users as $u){
			$abbrev = $u->timezone;
			$offset = $u->time_zone_offset;
			if($offset > 1440 || $offset < -1440){
				if($abbrev && $abbrev !== "UTC"){
					$u->time_zone_offset = QMTimeZone::timeZoneAbbreviationToOffsetInMinutes($abbrev);
				} else{
					$u->time_zone_offset = $offset / 60; // Probably in seconds
				}
				$u->logInfo("Changed time_zone_offset from $offset to $u->time_zone_offset");
			}
			if(!$abbrev){
				$u->timezone = QMTimeZone::convertTimeZoneOffsetToStringAbbreviation($u->time_zone_offset);
				$u->logInfo("Set timezone to $u->timezone");
			}
			$u->save();
		}
	}
}
