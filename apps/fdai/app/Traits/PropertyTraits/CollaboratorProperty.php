<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\Collaborator;
use App\Traits\HasModel\HasCollaborator;
trait CollaboratorProperty {
	use HasCollaborator;
	public function getCollaboratorId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getCollaborator(): Collaborator{
		return $this->getParentModel();
	}
}
