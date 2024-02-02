<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\CtCondition;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasCtCondition {
	public function getCtConditionId(): int{
		$nameOrId = $this->getAttribute('ct_condition_id');
		return $nameOrId;
	}
	public function getCtConditionButton(): QMButton{
		$ctCondition = $this->getCtCondition();
		if($ctCondition){
			return $ctCondition->getButton();
		}
		return CtCondition::generateDataLabShowButton($this->getCtConditionId());
	}
	/**
	 * @return CtCondition
	 */
	public function getCtCondition(): CtCondition{
		if($this instanceof BaseProperty && $this->parentModel instanceof CtCondition){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('ct_condition')){
			return $l;
		}
		$id = $this->getCtConditionId();
		$ctCondition = CtCondition::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['ct_condition'] = $ctCondition;
		}
		if(property_exists($this, 'ctCondition')){
			$this->ctCondition = $ctCondition;
		}
		return $ctCondition;
	}
	public function getCtConditionNameLink(): string{
		return $this->getCtCondition()->getDataLabDisplayNameLink();
	}
	public function getCtConditionImageNameLink(): string{
		return $this->getCtCondition()->getDataLabImageNameLink();
	}
}
