<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\CtCause;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasCtCause {
	public function getCtCauseId(): int{
		$nameOrId = $this->getAttribute('ct_cause_id');
		return $nameOrId;
	}
	public function getCtCauseButton(): QMButton{
		$ctCause = $this->getCtCause();
		if($ctCause){
			return $ctCause->getButton();
		}
		return CtCause::generateDataLabShowButton($this->getCtCauseId());
	}
	/**
	 * @return CtCause
	 */
	public function getCtCause(): CtCause{
		if($this instanceof BaseProperty && $this->parentModel instanceof CtCause){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('ct_cause')){
			return $l;
		}
		$id = $this->getCtCauseId();
		$ctCause = CtCause::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['ct_cause'] = $ctCause;
		}
		if(property_exists($this, 'ctCause')){
			$this->ctCause = $ctCause;
		}
		return $ctCause;
	}
	public function getCtCauseNameLink(): string{
		return $this->getCtCause()->getDataLabDisplayNameLink();
	}
	public function getCtCauseImageNameLink(): string{
		return $this->getCtCause()->getDataLabImageNameLink();
	}
}
