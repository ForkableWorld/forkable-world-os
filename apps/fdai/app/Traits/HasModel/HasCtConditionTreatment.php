<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\CtConditionTreatment;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasCtConditionTreatment {
	public function getCtConditionTreatmentId(): int{
		$nameOrId = $this->getAttribute('ct_condition_treatment_id');
		return $nameOrId;
	}
	public function getCtConditionTreatmentButton(): QMButton{
		$ctConditionTreatment = $this->getCtConditionTreatment();
		if($ctConditionTreatment){
			return $ctConditionTreatment->getButton();
		}
		return CtConditionTreatment::generateDataLabShowButton($this->getCtConditionTreatmentId());
	}
	/**
	 * @return CtConditionTreatment
	 */
	public function getCtConditionTreatment(): CtConditionTreatment{
		if($this instanceof BaseProperty && $this->parentModel instanceof CtConditionTreatment){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('ct_condition_treatment')){
			return $l;
		}
		$id = $this->getCtConditionTreatmentId();
		$ctConditionTreatment = CtConditionTreatment::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['ct_condition_treatment'] = $ctConditionTreatment;
		}
		if(property_exists($this, 'ctConditionTreatment')){
			$this->ctConditionTreatment = $ctConditionTreatment;
		}
		return $ctConditionTreatment;
	}
	public function getCtConditionTreatmentNameLink(): string{
		return $this->getCtConditionTreatment()->getDataLabDisplayNameLink();
	}
	public function getCtConditionTreatmentImageNameLink(): string{
		return $this->getCtConditionTreatment()->getDataLabImageNameLink();
	}
}
