<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\OAClient;
use App\Traits\HasModel\HasOAClient;
trait OAClientProperty {
	use HasOAClient;
	public function getOAClientId(): int{
		return $this->getParentModel()->getId();
	}
	public function getOAClient(): OAClient{
		return $this->getParentModel();
	}
}
