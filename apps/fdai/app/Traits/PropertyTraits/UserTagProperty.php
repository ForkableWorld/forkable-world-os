<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\UserTag;
use App\Traits\HasModel\HasUserTag;
trait UserTagProperty {
	use HasUserTag;
	public function getUserTagId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getUserTag(): UserTag{
		return $this->getParentModel();
	}
}
