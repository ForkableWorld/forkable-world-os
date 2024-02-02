<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\CtConditionCause;
use App\Traits\HasModel\HasCtConditionCause;
trait CtConditionCauseProperty {
	use HasCtConditionCause;
	public function getCtConditionCauseId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getCtConditionCause(): CtConditionCause{
		return $this->getParentModel();
	}
}
