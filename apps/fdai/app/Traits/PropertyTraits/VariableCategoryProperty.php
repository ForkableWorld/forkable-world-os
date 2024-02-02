<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\VariableCategory;
use App\Traits\HardCodableProperty;
use App\Traits\HasModel\HasVariableCategory;
trait VariableCategoryProperty {
	use HasVariableCategory;
	use HardCodableProperty;
	public function getVariableCategoryId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getVariableCategory(): VariableCategory{
		return $this->getParentModel();
	}
}
