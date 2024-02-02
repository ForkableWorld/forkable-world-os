<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\Study;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasStudy {
	public function getStudyId(): int{
		$nameOrId = $this->getAttribute('study_id');
		return $nameOrId;
	}
	public function getStudyButton(): QMButton{
		$study = $this->getStudy();
		if($study){
			return $study->getButton();
		}
		return Study::generateDataLabShowButton($this->getStudyId());
	}
	/**
	 * @return Study
	 */
	public function getStudy(): Study{
		if($this instanceof BaseProperty && $this->parentModel instanceof Study){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('study')){
			return $l;
		}
		$id = $this->getStudyId();
		$study = Study::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['study'] = $study;
		}
		if(property_exists($this, 'study')){
			$this->study = $study;
		}
		return $study;
	}
	public function getStudyNameLink(): string{
		return $this->getStudy()->getDataLabDisplayNameLink();
	}
	public function getStudyImageNameLink(): string{
		return $this->getStudy()->getDataLabImageNameLink();
	}
}
