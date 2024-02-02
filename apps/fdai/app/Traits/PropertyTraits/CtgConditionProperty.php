<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\CtgCondition;
use App\Traits\HasModel\HasCtgCondition;
trait CtgConditionProperty {
	use HasCtgCondition;
	public function getCtgConditionId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getCtgCondition(): CtgCondition{
		return $this->getParentModel();
	}
}
