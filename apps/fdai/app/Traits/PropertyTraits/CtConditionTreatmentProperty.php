<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\CtConditionTreatment;
use App\Traits\HasModel\HasCtConditionTreatment;
trait CtConditionTreatmentProperty {
	use HasCtConditionTreatment;
	public function getCtConditionTreatmentId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getCtConditionTreatment(): CtConditionTreatment{
		return $this->getParentModel();
	}
}
