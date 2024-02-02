<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Metrics;
use App\Http\Requests\AstralRequest;
class NewUsersValue extends QMValue {
	/**
	 * Calculate the value of the metric.
	 * @param AstralRequest $request
	 * @return mixed
	 */
	public function calculate(AstralRequest $request){
		$qb = $this->qb();
		return $this->count($request, $qb);
	}
	/**
	 * Get the ranges available for the metric.
	 * @return array
	 */
	public function ranges(){
		return [
			30 => '30 Days',
			60 => '60 Days',
			365 => '365 Days',
			'TODAY' => 'Today',
			'MTD' => 'Month To Date',
			'QTD' => 'Quarter To Date',
			'YTD' => 'Year To Date',
		];
	}
	/**
	 * Determine for how many minutes the metric should be cached.
	 * @return  \DateTimeInterface|\DateInterval|float|int
	 */
	public function cacheFor(){
		return now()->addMinutes(5);
	}
}
