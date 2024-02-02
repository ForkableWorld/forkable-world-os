<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\CtCorrelation;
use App\Traits\HasModel\HasCtCorrelation;
trait CtCorrelationProperty {
	use HasCtCorrelation;
	public function getCtCorrelationId(): int{
		return $this->getParentModel()->getId();
	}
	public function getCtCorrelation(): CtCorrelation{
		return $this->getParentModel();
	}
}
