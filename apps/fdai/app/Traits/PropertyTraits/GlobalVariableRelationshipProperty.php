<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\GlobalVariableRelationship;
use App\Traits\HasModel\HasGlobalVariableRelationship;
trait GlobalVariableRelationshipProperty {
	use HasGlobalVariableRelationship;
	public function getGlobalVariableRelationshipId(): int{
		return $this->getParentModel()->getId();
	}
	public function getGlobalVariableRelationship(): GlobalVariableRelationship{
		return $this->getParentModel();
	}
}
