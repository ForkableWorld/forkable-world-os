<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\CtConditionSymptom;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasCtConditionSymptom {
	public function getCtConditionSymptomId(): int{
		$nameOrId = $this->getAttribute('ct_condition_symptom_id');
		return $nameOrId;
	}
	public function getCtConditionSymptomButton(): QMButton{
		$ctConditionSymptom = $this->getCtConditionSymptom();
		if($ctConditionSymptom){
			return $ctConditionSymptom->getButton();
		}
		return CtConditionSymptom::generateDataLabShowButton($this->getCtConditionSymptomId());
	}
	/**
	 * @return CtConditionSymptom
	 */
	public function getCtConditionSymptom(): CtConditionSymptom{
		if($this instanceof BaseProperty && $this->parentModel instanceof CtConditionSymptom){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('ct_condition_symptom')){
			return $l;
		}
		$id = $this->getCtConditionSymptomId();
		$ctConditionSymptom = CtConditionSymptom::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['ct_condition_symptom'] = $ctConditionSymptom;
		}
		if(property_exists($this, 'ctConditionSymptom')){
			$this->ctConditionSymptom = $ctConditionSymptom;
		}
		return $ctConditionSymptom;
	}
	public function getCtConditionSymptomNameLink(): string{
		return $this->getCtConditionSymptom()->getDataLabDisplayNameLink();
	}
	public function getCtConditionSymptomImageNameLink(): string{
		return $this->getCtConditionSymptom()->getDataLabImageNameLink();
	}
}
