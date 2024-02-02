<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\UserClient;
use App\Traits\HasModel\HasUserClient;
trait UserClientProperty {
	use HasUserClient;
	public function getUserClientId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getUserClient(): UserClient{
		return $this->getParentModel();
	}
}
