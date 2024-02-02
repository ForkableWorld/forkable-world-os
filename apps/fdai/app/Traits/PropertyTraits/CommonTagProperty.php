<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\CommonTag;
use App\Traits\HasModel\HasCommonTag;
trait CommonTagProperty {
	use HasCommonTag;
	public function getCommonTagId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getCommonTag(): CommonTag{
		return $this->getParentModel();
	}
}
