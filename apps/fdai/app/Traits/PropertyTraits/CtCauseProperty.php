<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\CtCause;
use App\Traits\HasModel\HasCtCause;
trait CtCauseProperty {
	use HasCtCause;
	public function getCtCauseId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getCtCause(): CtCause{
		return $this->getParentModel();
	}
}
