<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\CtCondition;
use App\Traits\HasModel\HasCtCondition;
trait CtConditionProperty {
	use HasCtCondition;
	public function getCtConditionId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getCtCondition(): CtCondition{
		return $this->getParentModel();
	}
}
