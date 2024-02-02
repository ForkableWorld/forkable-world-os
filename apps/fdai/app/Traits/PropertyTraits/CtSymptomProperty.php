<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\CtSymptom;
use App\Traits\HasModel\HasCtSymptom;
trait CtSymptomProperty {
	use HasCtSymptom;
	public function getCtSymptomId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getCtSymptom(): CtSymptom{
		return $this->getParentModel();
	}
}
