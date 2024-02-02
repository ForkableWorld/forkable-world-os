<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Metrics;
use App\Models\User;
use App\UI\QMColor;
use App\Http\Requests\AstralRequest;
class AnalysisProgressPartition extends QMPartition {
	/**
	 * Calculate the value of the metric.
	 * @param AstralRequest $request
	 * @return mixed
	 */
	public function calculate(AstralRequest $request){
		$failed = $this->qb()->whereNull(User::FIELD_ANALYSIS_ENDED_AT)->whereNotNull(User::FIELD_ANALYSIS_STARTED_AT)
				->count() +
			User::whereRaw(User::FIELD_ANALYSIS_ENDED_AT . " < " . User::FIELD_ANALYSIS_STARTED_AT)->count();
		$Successful =
			$this->qb()->whereNotNull(User::FIELD_ANALYSIS_ENDED_AT)->whereNotNull(User::FIELD_ANALYSIS_STARTED_AT)
				->whereRaw(User::FIELD_ANALYSIS_ENDED_AT . " >= " . User::FIELD_ANALYSIS_STARTED_AT)->count();
		return $this->result([
			'Successful' => $Successful,
			'Failed' => $failed,
			'Never Started' => $this->qb()->whereNull(User::FIELD_ANALYSIS_STARTED_AT)->count(),
		])->colors([
			'Successful' => QMColor::HEX_GOOGLE_GREEN,
			'Failed' => QMColor::HEX_GOOGLE_RED,
			'Never Started' => QMColor::HEX_ORANGE,
		]);
	}
	/**
	 * Determine for how many minutes the metric should be cached.
	 * @return  \DateTimeInterface|\DateInterval|float|int
	 */
	public function cacheFor(){
		return now()->addMinutes(5);
	}
}
