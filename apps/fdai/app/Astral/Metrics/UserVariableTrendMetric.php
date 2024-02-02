<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Metrics;
use App\Models\BaseModel;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Http\Requests\AstralRequest;
use App\Metrics\Trend;
class UserVariableTrendMetric extends Trend {
	public $userVariableId;
	/**
	 * Calculate the value of the metric.
	 * @param AstralRequest $request
	 * @return mixed
	 */
	public function calculate(AstralRequest $request){
		$userVariable = $this->getUserVariable();
		$qb = Measurement::whereUserVariableId($userVariable->getId());
		return $this->averageByDays($request, $qb, Measurement::FIELD_VALUE, Measurement::FIELD_START_AT);
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
	 * Determine for how many minutes the metric should be cached.
	 * @return  \DateTimeInterface|\DateInterval|float|int
	 */
	public function cacheFor(){
		return now()->addMinutes(1440);
	}
	/**
	 * Get the URI key for the metric.
	 * @return string
	 */
	public function uriKey(): string {
		return 'user-variable-trend-metric';
	}
	/**
	 * @param int $userVariableId
	 */
	public function setUserVariableId(int $userVariableId): void{
		$this->userVariableId = $userVariableId;
	}
	/**
	 * @return mixed
	 */
	public function getUserVariableId(): int{
		return $this->userVariableId;
	}
	/**
	 * @return BaseModel|UserVariable|null
	 */
	protected function getUserVariable(){
		$userVariable = UserVariable::findInMemoryOrDB($this->getUserVariableId());
		return $userVariable;
	}
}
