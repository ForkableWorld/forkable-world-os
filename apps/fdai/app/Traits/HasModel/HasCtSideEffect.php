<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\CtSideEffect;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasCtSideEffect {
	public function getCtSideEffectId(): int{
		$nameOrId = $this->getAttribute('ct_side_effect_id');
		return $nameOrId;
	}
	public function getCtSideEffectButton(): QMButton{
		$ctSideEffect = $this->getCtSideEffect();
		if($ctSideEffect){
			return $ctSideEffect->getButton();
		}
		return CtSideEffect::generateDataLabShowButton($this->getCtSideEffectId());
	}
	/**
	 * @return CtSideEffect
	 */
	public function getCtSideEffect(): CtSideEffect{
		if($this instanceof BaseProperty && $this->parentModel instanceof CtSideEffect){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('ct_side_effect')){
			return $l;
		}
		$id = $this->getCtSideEffectId();
		$ctSideEffect = CtSideEffect::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['ct_side_effect'] = $ctSideEffect;
		}
		if(property_exists($this, 'ctSideEffect')){
			$this->ctSideEffect = $ctSideEffect;
		}
		return $ctSideEffect;
	}
	public function getCtSideEffectNameLink(): string{
		return $this->getCtSideEffect()->getDataLabDisplayNameLink();
	}
	public function getCtSideEffectImageNameLink(): string{
		return $this->getCtSideEffect()->getDataLabImageNameLink();
	}
}
