<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasMeasurement {
	public function getMeasurementId(): int{
		$nameOrId = $this->getAttribute('measurement_id');
		return $nameOrId;
	}
	public function getMeasurementButton(): QMButton{
		$measurement = $this->getMeasurement();
		if($measurement){
			return $measurement->getButton();
		}
		return Measurement::generateDataLabShowButton($this->getMeasurementId());
	}
	/**
	 * @return Measurement
	 */
	public function getMeasurement(): Measurement{
		if($this instanceof BaseProperty && $this->parentModel instanceof Measurement){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('measurement')){
			return $l;
		}
		$id = $this->getMeasurementId();
		$measurement = Measurement::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['measurement'] = $measurement;
		}
		if(property_exists($this, 'measurement')){
			$this->measurement = $measurement;
		}
		return $measurement;
	}
	public function getMeasurementNameLink(): string{
		return $this->getMeasurement()->getDataLabDisplayNameLink();
	}
	public function getMeasurementImageNameLink(): string{
		return $this->getMeasurement()->getDataLabImageNameLink();
	}
	public function getVariable(): Variable{
		return $this->getMeasurement()->getVariable();
	}
	public function getUserVariable(): UserVariable{
		return $this->getMeasurement()->getUserVariable();
	}
}
