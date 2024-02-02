<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Metrics;
use App\Properties\BaseProperty;
use App\UI\QMColor;
use App\Http\Requests\AstralRequest;
class InvalidAnalyzablesPartition extends QMPartition {
	/**
	 * @var BaseProperty
	 */
	protected $propertyModel;
	public function __construct(string $propertyClass = null, $component = null){
		/** @var BaseProperty $p */
		$p = new $propertyClass();
		$this->propertyModel = $p;
		parent::__construct($p->parentClass, $component);
	}
	/**
	 * Calculate the value of the metric.
	 * @param AstralRequest $request
	 * @return mixed
	 */
	public function calculate(AstralRequest $request){
		$qb = $this->getPropertyModel()->whereInvalid();
		$total = $this->qb()->count();
		$failed = $qb->count();
		$successful = $total - $failed;
		return $this->result([
			'Valid' => $successful,
			'Invalid' => $failed,
		])->colors([
			'Valid' => QMColor::HEX_GOOGLE_GREEN,
			'Invalid' => QMColor::HEX_GOOGLE_RED,
		]);
	}
	/**
	 * Determine for how many minutes the metric should be cached.
	 * @return  \DateTimeInterface|\DateInterval|float|int
	 */
	public function cacheFor(){
		return now()->addMinutes(0);
	}
	/**
	 * @return BaseProperty
	 */
	public function getPropertyModel(): BaseProperty{
		return $this->propertyModel;
	}
}
