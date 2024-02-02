<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\WpPost;
use App\Properties\BaseProperty;
trait HasWpPost {
	public function getWpPost(): ?WpPost{
		if($this instanceof BaseProperty && $this->parentModel instanceof WpPost){
			return $this->parentModel;
		}
		$id = $this->getPostId();
		if(!$id){
			return null;
		}
		return WpPost::findInMemoryOrDB($id);
	}
	public function getWpPostButton(): QMButton{
		return $this->getWpPost()->getButton();
	}
	public function getWpPostLink(): string{
		$id = $this->getPostId();
		if(!$id){
			return "N/A";
		}
		return $this->getWpPost()->getDataLabDisplayNameLink();
	}
	public function getPostId(): ?int{
		return $this->getAttribute('wp_post_id');
	}
}
