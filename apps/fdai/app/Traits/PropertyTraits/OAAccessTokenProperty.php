<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\OAAccessToken;
use App\Traits\HasModel\HasOAAccessToken;
trait OAAccessTokenProperty {
	use HasOAAccessToken;
	public function getOAAccessTokenId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getOAAccessToken(): OAAccessToken{
		return $this->getParentModel();
	}
}
