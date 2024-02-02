<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\WpLink;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasWpLink {
	public function getWpLinkId(): int{
		$nameOrId = $this->getAttribute('wp_link_id');
		return $nameOrId;
	}
	public function getWpLinkButton(): QMButton{
		$wpLink = $this->getWpLink();
		if($wpLink){
			return $wpLink->getButton();
		}
		return WpLink::generateShowButton($this->getWpLinkId());
	}
	/**
	 * @return WpLink
	 */
	public function getWpLink(): WpLink{
		if($this instanceof BaseProperty && $this->parentModel instanceof WpLink){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('wp_link')){
			return $l;
		}
		$id = $this->getWpLinkId();
		$wpLink = WpLink::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['wp_link'] = $wpLink;
		}
		if(property_exists($this, 'wpLink')){
			$this->wpLink = $wpLink;
		}
		return $wpLink;
	}
}
