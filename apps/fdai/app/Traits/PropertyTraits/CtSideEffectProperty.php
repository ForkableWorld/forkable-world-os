<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\CtSideEffect;
use App\Traits\HasModel\HasCtSideEffect;
trait CtSideEffectProperty {
	use HasCtSideEffect;
	public function getCtSideEffectId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getCtSideEffect(): CtSideEffect{
		return $this->getParentModel();
	}
}
