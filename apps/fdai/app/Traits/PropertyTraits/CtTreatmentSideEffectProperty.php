<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\CtTreatmentSideEffect;
use App\Traits\HasModel\HasCtTreatmentSideEffect;
trait CtTreatmentSideEffectProperty {
	use HasCtTreatmentSideEffect;
	public function getCtTreatmentSideEffectId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getCtTreatmentSideEffect(): CtTreatmentSideEffect{
		return $this->getParentModel();
	}
}
