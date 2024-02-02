<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use Carbon\Carbon;
trait HasLocalDates {
	/**
	 * Localize a date to users timezone
	 * @param null $dateField
	 * @return Carbon
	 */
	public function localize($dateField = null){
		$dateValue = $this->{$dateField} ?? Carbon::now();
		return $this->inUsersTimezone($dateValue);
	}
	/**
	 * Change timezone of a carbon date
	 * @param $dateValue
	 * @return Carbon
	 */
	private function inUsersTimezone($dateValue): Carbon{
		$timezone = optional(auth()->user())->timezone ?? config('app.timezone');
		return $this->asDateTime($dateValue)->timezone($timezone);
	}
}
