<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\CtgIntervention;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasCtgIntervention {
	public function getCtgInterventionId(): int{
		$nameOrId = $this->getAttribute('ctgov_intervention_id');
		return $nameOrId;
	}
	public function getCtgInterventionButton(): QMButton{
		$ctgovIntervention = $this->getCtgIntervention();
		if($ctgovIntervention){
			return $ctgovIntervention->getButton();
		}
		return CtgIntervention::generateDataLabShowButton($this->getCtgInterventionId());
	}
	/**
	 * @return CtgIntervention
	 */
	public function getCtgIntervention(): CtgIntervention{
		if($this instanceof BaseProperty && $this->parentModel instanceof CtgIntervention){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('ctgov_intervention')){
			return $l;
		}
		$id = $this->getCtgInterventionId();
		$ctgovIntervention = CtgIntervention::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['ctgov_intervention'] = $ctgovIntervention;
		}
		if(property_exists($this, 'ctgovIntervention')){
			$this->ctgovIntervention = $ctgovIntervention;
		}
		return $ctgovIntervention;
	}
	public function getCtgInterventionNameLink(): string{
		return $this->getCtgIntervention()->getDataLabDisplayNameLink();
	}
	public function getCtgInterventionImageNameLink(): string{
		return $this->getCtgIntervention()->getDataLabImageNameLink();
	}
}
