<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\Application;
use App\Traits\HasModel\HasApplication;
trait ApplicationProperty {
	use HasApplication;
	public function getApplicationId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getApplication(): Application{
		return $this->getParentModel();
	}
}
