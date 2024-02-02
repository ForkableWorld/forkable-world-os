<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Metrics;
use Illuminate\Http\Request;
class TotalUsersSharingDataValue extends QMValue {
	/**
	 * Calculate the value of the metric.
	 * @param Request $request
	 * @return mixed
	 */
	public function calculate(Request $request){
		$res = $this->qb()->count();
		return $this->result($res)->allowZeroResult();
	}
	/**
	 * Determine for how many minutes the metric should be cached.
	 * @return  \DateTimeInterface|\DateInterval|float|int
	 */
	public function cacheFor(){
		return now()->addMinutes(5);
	}
}
