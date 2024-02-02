<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\DeviceToken;
use App\Traits\HasModel\HasDeviceToken;
trait DeviceTokenProperty {
	use HasDeviceToken;
	public function getDeviceTokenId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getDeviceToken(): DeviceToken{
		return $this->getParentModel();
	}
}
