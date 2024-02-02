<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Metrics;
use App\Traits\QMMetric;
use App\Metrics\Partition;
class QMPartition extends Partition {
	use QMMetric;
	/**
	 * The width of the card (1/3, 1/2, or full).
	 * @var string
	 */
	public $width = '1/2';
	public function __construct(string $modelClass = null, $component = null){
		$this->modelClass = $modelClass;
		parent::__construct($component);
	}
	protected function getMetricType(): string{
		return "Partition";
	}
	/**
	 * Determine for how many minutes the metric should be cached.
	 * @return  \DateTimeInterface|\DateInterval|float|int
	 */
	public function cacheFor(){
		return now()->addMinutes(5);
	}
}
