<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Metrics;
use App\Traits\QMMetric;
use App\Metrics\Trend;
class QMTrend extends Trend {
	use QMMetric;
	public function __construct(string $class, $component = null){
		$this->modelClass = $class;
		parent::__construct($component);
	}
	protected function getMetricType(): string{
		return "Trend";
	}
	/**
	 * Determine for how many minutes the metric should be cached.
	 * @return  \DateTimeInterface|\DateInterval|float|int
	 */
	public function cacheFor(){
		return now()->addMinutes(5);
	}
}
