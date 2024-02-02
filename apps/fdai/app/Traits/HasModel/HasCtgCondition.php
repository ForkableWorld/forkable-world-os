<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\CtgCondition;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasCtgCondition {
	public function getCtgConditionId(): int{
		$nameOrId = $this->getAttribute('ctgov_condition_id');
		return $nameOrId;
	}
	public function getCtgConditionButton(): QMButton{
		$ctgovCondition = $this->getCtgCondition();
		if($ctgovCondition){
			return $ctgovCondition->getButton();
		}
		return CtgCondition::generateDataLabShowButton($this->getCtgConditionId());
	}
	/**
	 * @return CtgCondition
	 */
	public function getCtgCondition(): CtgCondition{
		if($this instanceof BaseProperty && $this->parentModel instanceof CtgCondition){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('ctgov_condition')){
			return $l;
		}
		$id = $this->getCtgConditionId();
		$ctgovCondition = CtgCondition::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['ctgov_condition'] = $ctgovCondition;
		}
		if(property_exists($this, 'ctgovCondition')){
			$this->ctgovCondition = $ctgovCondition;
		}
		return $ctgovCondition;
	}
	public function getCtgConditionNameLink(): string{
		return $this->getCtgCondition()->getDataLabDisplayNameLink();
	}
	public function getCtgConditionImageNameLink(): string{
		return $this->getCtgCondition()->getDataLabImageNameLink();
	}
}
