<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\CtTreatment;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasCtTreatment {
	public function getCtTreatmentId(): int{
		$nameOrId = $this->getAttribute('ct_treatment_id');
		return $nameOrId;
	}
	public function getCtTreatmentButton(): QMButton{
		$ctTreatment = $this->getCtTreatment();
		if($ctTreatment){
			return $ctTreatment->getButton();
		}
		return CtTreatment::generateDataLabShowButton($this->getCtTreatmentId());
	}
	/**
	 * @return CtTreatment
	 */
	public function getCtTreatment(): CtTreatment{
		if($this instanceof BaseProperty && $this->parentModel instanceof CtTreatment){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('ct_treatment')){
			return $l;
		}
		$id = $this->getCtTreatmentId();
		$ctTreatment = CtTreatment::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['ct_treatment'] = $ctTreatment;
		}
		if(property_exists($this, 'ctTreatment')){
			$this->ctTreatment = $ctTreatment;
		}
		return $ctTreatment;
	}
	public function getCtTreatmentNameLink(): string{
		return $this->getCtTreatment()->getDataLabDisplayNameLink();
	}
	public function getCtTreatmentImageNameLink(): string{
		return $this->getCtTreatment()->getDataLabImageNameLink();
	}
}
