<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\UnitCategory;
use App\Traits\HardCodableProperty;
use App\Traits\HasModel\HasUnitCategory;
trait UnitCategoryProperty {
	use HasUnitCategory, HardCodableProperty;
	public function getUnitCategoryId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getUnitCategory(): UnitCategory{
		return $this->getParentModel();
	}
}
