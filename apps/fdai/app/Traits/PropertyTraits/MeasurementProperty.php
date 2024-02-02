<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\Measurement;
use App\Traits\HasModel\HasMeasurement;
trait MeasurementProperty {
	use HasMeasurement;
	public function getMeasurementId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getMeasurement(): Measurement{
		return $this->getParentModel();
	}
}
