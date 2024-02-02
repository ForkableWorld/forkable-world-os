<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\CtSymptom;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasCtSymptom {
	public function getCtSymptomId(): int{
		$nameOrId = $this->getAttribute('ct_symptom_id');
		return $nameOrId;
	}
	public function getCtSymptomButton(): QMButton{
		$ctSymptom = $this->getCtSymptom();
		if($ctSymptom){
			return $ctSymptom->getButton();
		}
		return CtSymptom::generateDataLabShowButton($this->getCtSymptomId());
	}
	/**
	 * @return CtSymptom
	 */
	public function getCtSymptom(): CtSymptom{
		if($this instanceof BaseProperty && $this->parentModel instanceof CtSymptom){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('ct_symptom')){
			return $l;
		}
		$id = $this->getCtSymptomId();
		$ctSymptom = CtSymptom::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['ct_symptom'] = $ctSymptom;
		}
		if(property_exists($this, 'ctSymptom')){
			$this->ctSymptom = $ctSymptom;
		}
		return $ctSymptom;
	}
	public function getCtSymptomNameLink(): string{
		return $this->getCtSymptom()->getDataLabDisplayNameLink();
	}
	public function getCtSymptomImageNameLink(): string{
		return $this->getCtSymptom()->getDataLabImageNameLink();
	}
}
