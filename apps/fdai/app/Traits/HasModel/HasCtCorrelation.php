<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\CtCorrelation;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasCtCorrelation {
	public function getCtCorrelationId(): int{
		$nameOrId = $this->getAttribute('ct_correlation_id');
		return $nameOrId;
	}
	public function getCtCorrelationButton(): QMButton{
		$ctCorrelation = $this->getCtCorrelation();
		if($ctCorrelation){
			return $ctCorrelation->getButton();
		}
		return CtCorrelation::generateDataLabShowButton($this->getCtCorrelationId());
	}
	/**
	 * @return CtCorrelation
	 */
	public function getCtCorrelation(): CtCorrelation{
		if($this instanceof BaseProperty && $this->parentModel instanceof CtCorrelation){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('ct_correlation')){
			return $l;
		}
		$id = $this->getCtCorrelationId();
		$ctCorrelation = CtCorrelation::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['ct_correlation'] = $ctCorrelation;
		}
		if(property_exists($this, 'ctCorrelation')){
			$this->ctCorrelation = $ctCorrelation;
		}
		return $ctCorrelation;
	}
	public function getCtCorrelationNameLink(): string{
		return $this->getCtCorrelation()->getDataLabDisplayNameLink();
	}
	public function getCtCorrelationImageNameLink(): string{
		return $this->getCtCorrelation()->getDataLabImageNameLink();
	}
}
