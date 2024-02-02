<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\CtgIntervention;
use App\Traits\HasModel\HasCtgIntervention;
trait CtgInterventionProperty {
	use HasCtgIntervention;
	public function getCtgInterventionId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getCtgIntervention(): CtgIntervention{
		return $this->getParentModel();
	}
}
