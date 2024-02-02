<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\UserVariableClient;
use App\Traits\HasModel\HasUserVariableClient;
trait UserVariableClientProperty {
	use HasUserVariableClient;
	public function getUserVariableClientId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getUserVariableClient(): UserVariableClient{
		return $this->getParentModel();
	}
}
