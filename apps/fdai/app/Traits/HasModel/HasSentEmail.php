<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\SentEmail;
use App\Properties\BaseProperty;
trait HasSentEmail {
	public function getSentEmailId(): int{
		$nameOrId = $this->getAttribute('sent_email_id');
		return $nameOrId;
	}
	public function getSentEmailButton(): QMButton{
		$sentEmail = $this->getSentEmail();
		if($sentEmail){
			return $sentEmail->getButton();
		}
		return SentEmail::generateDataLabShowButton($this->getSentEmailId());
	}
	/**
	 * @return SentEmail
	 */
	public function getSentEmail(): SentEmail{
		if($this instanceof BaseProperty && $this->parentModel instanceof SentEmail){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('sent_email')){
			return $l;
		}
		$id = $this->getSentEmailId();
		$sentEmail = SentEmail::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['sent_email'] = $sentEmail;
		}
		if(property_exists($this, 'sentEmail')){
			/** @noinspection PhpUndefinedFieldInspection */
			$this->sentEmail = $sentEmail;
		}
		return $sentEmail;
	}
	public function getSentEmailNameLink(): string{
		return $this->getSentEmail()->getDataLabDisplayNameLink();
	}
	public function getSentEmailImageNameLink(): string{
		return $this->getSentEmail()->getDataLabImageNameLink();
	}
}
