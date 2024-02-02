<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\CtTreatmentSideEffect;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasCtTreatmentSideEffect {
	public function getCtTreatmentSideEffectId(): int{
		$nameOrId = $this->getAttribute('ct_treatment_side_effect_id');
		return $nameOrId;
	}
	public function getCtTreatmentSideEffectButton(): QMButton{
		$ctTreatmentSideEffect = $this->getCtTreatmentSideEffect();
		if($ctTreatmentSideEffect){
			return $ctTreatmentSideEffect->getButton();
		}
		return CtTreatmentSideEffect::generateDataLabShowButton($this->getCtTreatmentSideEffectId());
	}
	/**
	 * @return CtTreatmentSideEffect
	 */
	public function getCtTreatmentSideEffect(): CtTreatmentSideEffect{
		if($this instanceof BaseProperty && $this->parentModel instanceof CtTreatmentSideEffect){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('ct_treatment_side_effect')){
			return $l;
		}
		$id = $this->getCtTreatmentSideEffectId();
		$ctTreatmentSideEffect = CtTreatmentSideEffect::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['ct_treatment_side_effect'] = $ctTreatmentSideEffect;
		}
		if(property_exists($this, 'ctTreatmentSideEffect')){
			$this->ctTreatmentSideEffect = $ctTreatmentSideEffect;
		}
		return $ctTreatmentSideEffect;
	}
	public function getCtTreatmentSideEffectNameLink(): string{
		return $this->getCtTreatmentSideEffect()->getDataLabDisplayNameLink();
	}
	public function getCtTreatmentSideEffectImageNameLink(): string{
		return $this->getCtTreatmentSideEffect()->getDataLabImageNameLink();
	}
}
