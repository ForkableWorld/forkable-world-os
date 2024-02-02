<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\UserVariableClient;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasUserVariableClient {
	public function getUserVariableClientId(): int{
		$nameOrId = $this->getAttribute('user_variable_client_id');
		return $nameOrId;
	}
	public function getUserVariableClientButton(): QMButton{
		$userVariableClient = $this->getUserVariableClient();
		if($userVariableClient){
			return $userVariableClient->getButton();
		}
		return UserVariableClient::generateDataLabShowButton($this->getUserVariableClientId());
	}
	/**
	 * @return UserVariableClient
	 */
	public function getUserVariableClient(): UserVariableClient{
		if($this instanceof BaseProperty && $this->parentModel instanceof UserVariableClient){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('user_variable_client')){
			return $l;
		}
		$id = $this->getUserVariableClientId();
		$userVariableClient = UserVariableClient::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['user_variable_client'] = $userVariableClient;
		}
		if(property_exists($this, 'userVariableClient')){
			$this->userVariableClient = $userVariableClient;
		}
		return $userVariableClient;
	}
	public function getUserVariableClientNameLink(): string{
		return $this->getUserVariableClient()->getDataLabDisplayNameLink();
	}
	public function getUserVariableClientImageNameLink(): string{
		return $this->getUserVariableClient()->getDataLabImageNameLink();
	}
}
