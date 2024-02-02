<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\UserTag;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasUserTag {
	public function getUserTagId(): int{
		$nameOrId = $this->getAttribute('user_tag_id');
		return $nameOrId;
	}
	public function getUserTagButton(): QMButton{
		$userTag = $this->getUserTag();
		if($userTag){
			return $userTag->getButton();
		}
		return UserTag::generateDataLabShowButton($this->getUserTagId());
	}
	/**
	 * @return UserTag
	 */
	public function getUserTag(): UserTag{
		if($this instanceof BaseProperty && $this->parentModel instanceof UserTag){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('user_tag')){
			return $l;
		}
		$id = $this->getUserTagId();
		$userTag = UserTag::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['user_tag'] = $userTag;
		}
		if(property_exists($this, 'userTag')){
			$this->userTag = $userTag;
		}
		return $userTag;
	}
	public function getUserTagNameLink(): string{
		return $this->getUserTag()->getDataLabDisplayNameLink();
	}
	public function getUserTagImageNameLink(): string{
		return $this->getUserTag()->getDataLabImageNameLink();
	}
}
