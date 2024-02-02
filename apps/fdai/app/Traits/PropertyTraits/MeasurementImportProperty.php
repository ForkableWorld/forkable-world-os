<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\MeasurementImport;
use App\Traits\HasModel\HasMeasurementImport;
trait MeasurementImportProperty {
	use HasMeasurementImport;
	public function getMeasurementImportId(): int{
		return $this->getParentModel()->getId();
	}
	public function getMeasurementImport(): MeasurementImport{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->getParentModel();
	}
}
