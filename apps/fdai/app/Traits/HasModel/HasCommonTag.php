<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\CommonTag;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasCommonTag {
	public function getCommonTagId(): int{
		$nameOrId = $this->getAttribute('common_tag_id');
		return $nameOrId;
	}
	public function getCommonTagButton(): QMButton{
		$commonTag = $this->getCommonTag();
		if($commonTag){
			return $commonTag->getButton();
		}
		return CommonTag::generateDataLabShowButton($this->getCommonTagId());
	}
	/**
	 * @return CommonTag
	 */
	public function getCommonTag(): CommonTag{
		if($this instanceof BaseProperty && $this->parentModel instanceof CommonTag){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('global_variable_relationship')){
			return $l;
		}
		$id = $this->getCommonTagId();
		$commonTag = CommonTag::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['common_tag'] = $commonTag;
		}
		if(property_exists($this, 'commonTag')){
			$this->commonTag = $commonTag;
		}
		return $commonTag;
	}
	public function getCommonTagNameLink(): string{
		return $this->getCommonTag()->getDataLabDisplayNameLink();
	}
	public function getCommonTagImageNameLink(): string{
		return $this->getCommonTag()->getDataLabImageNameLink();
	}
}
