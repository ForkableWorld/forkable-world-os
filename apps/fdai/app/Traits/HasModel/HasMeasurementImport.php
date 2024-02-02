<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\MeasurementImport;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasMeasurementImport {
	public function getMeasurementImportId(): int{
		$nameOrId = $this->getAttribute('measurement_import_id');
		return $nameOrId;
	}
	public function getMeasurementImportButton(): QMButton{
		$measurementImport = $this->getMeasurementImport();
		if($measurementImport){
			return $measurementImport->getButton();
		}
		return MeasurementImport::generateShowButton($this->getMeasurementImportId());
	}
	/**
	 * @return MeasurementImport
	 */
	public function getMeasurementImport(): MeasurementImport{
		if($this instanceof BaseProperty && $this->parentModel instanceof MeasurementImport){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('measurement_import')){
			return $l;
		}
		$id = $this->getMeasurementImportId();
		$measurementImport = MeasurementImport::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['measurement_import'] = $measurementImport;
		}
		if(property_exists($this, 'measurementImport')){
			$this->measurementImport = $measurementImport;
		}
		return $measurementImport;
	}
}
