<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\UserClient;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasUserClient {
	public function getUserClientId(): int{
		$nameOrId = $this->getAttribute('user_client_id');
		return $nameOrId;
	}
	public function getUserClientButton(): QMButton{
		$userClient = $this->getUserClient();
		if($userClient){
			return $userClient->getButton();
		}
		return UserClient::generateDataLabShowButton($this->getUserClientId());
	}
	/**
	 * @return UserClient
	 */
	public function getUserClient(): UserClient{
		if($this instanceof BaseProperty && $this->parentModel instanceof UserClient){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('user_client')){
			return $l;
		}
		$id = $this->getUserClientId();
		$userClient = UserClient::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['user_client'] = $userClient;
		}
		if(property_exists($this, 'userClient')){
			$this->userClient = $userClient;
		}
		return $userClient;
	}
	public function getUserClientNameLink(): string{
		return $this->getUserClient()->getDataLabDisplayNameLink();
	}
	public function getUserClientImageNameLink(): string{
		return $this->getUserClient()->getDataLabImageNameLink();
	}
}
