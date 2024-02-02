<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\CtTreatment;
use App\Traits\HasModel\HasCtTreatment;
trait CtTreatmentProperty {
	use HasCtTreatment;
	public function getCtTreatmentId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getCtTreatment(): CtTreatment{
		return $this->getParentModel();
	}
}
