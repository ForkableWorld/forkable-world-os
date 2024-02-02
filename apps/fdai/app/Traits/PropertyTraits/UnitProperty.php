<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\Unit;
use App\Traits\HardCodableProperty;
use App\Traits\HasModel\HasUnit;
trait UnitProperty {
	use HasUnit, HardCodableProperty;
	public function getUnitIdAttribute(): ?int{
		return $this->getParentModel()->getId();
	}
	public function getUnit(): Unit{
		return $this->getParentModel();
	}
}
