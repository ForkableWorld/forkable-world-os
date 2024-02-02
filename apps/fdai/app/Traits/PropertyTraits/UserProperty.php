<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\User;
use App\Traits\HasModel\HasUser;
trait UserProperty {
	use HasUser;
	public function getUserId(): ?int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getUser(): User{
		return $this->getParentModel();
	}
}
