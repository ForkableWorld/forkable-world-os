<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\DeviceToken;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasDeviceToken {
	public function getDeviceTokenId(): int{
		$nameOrId = $this->getAttribute('device_token_id');
		return $nameOrId;
	}
	public function getDeviceTokenButton(): QMButton{
		$deviceToken = $this->getDeviceToken();
		if($deviceToken){
			return $deviceToken->getButton();
		}
		return DeviceToken::generateDataLabShowButton($this->getDeviceTokenId());
	}
	/**
	 * @return DeviceToken
	 */
	public function getDeviceToken(): DeviceToken{
		if($this instanceof BaseProperty && $this->parentModel instanceof DeviceToken){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('device_token')){
			return $l;
		}
		$id = $this->getDeviceTokenId();
		$deviceToken = DeviceToken::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['device_token'] = $deviceToken;
		}
		if(property_exists($this, 'deviceToken')){
			$this->deviceToken = $deviceToken;
		}
		return $deviceToken;
	}
	public function getDeviceTokenNameLink(): string{
		return $this->getDeviceToken()->getDataLabDisplayNameLink();
	}
	public function getDeviceTokenImageNameLink(): string{
		return $this->getDeviceToken()->getDataLabImageNameLink();
	}
}
