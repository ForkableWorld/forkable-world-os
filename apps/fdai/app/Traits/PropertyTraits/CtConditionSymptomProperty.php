<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\CtConditionSymptom;
use App\Traits\HasModel\HasCtConditionSymptom;
trait CtConditionSymptomProperty {
	use HasCtConditionSymptom;
	public function getCtConditionSymptomId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getCtConditionSymptom(): CtConditionSymptom{
		return $this->getParentModel();
	}
}
