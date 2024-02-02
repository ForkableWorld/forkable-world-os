<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Metrics;
use App\Http\Requests\AstralRequest;
class NewUsersPerDayTrend extends QMTrend {
	/**
	 * Calculate the value of the metric.
	 * @param AstralRequest $request
	 * @return mixed
	 */
	public function calculate(AstralRequest $request){
		$qb = $this->qb();
		return $this->countByDays($request, $qb);
	}
	/**
	 * Get the ranges available for the metric.
	 * @return array
	 */
	public function ranges(){
		return [
			30 => '30 Days',
			60 => '60 Days',
			90 => '90 Days',
		];
	}
	/**
	 * Get the URI key for the metric.
	 * @return string
	 */
	public function uriKey(): string {
		return 'users-per-day';
	}
	protected function getMetricType(): string{
		return "Trend";
	}
}
