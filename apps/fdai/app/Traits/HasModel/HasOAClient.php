<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\OAClient;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasOAClient {
	public function getOAClientId(): int{
		$nameOrId = $this->getAttribute('oa_client_id');
		return $nameOrId;
	}
	public function getOAClientButton(): QMButton{
		$bshafferOauthClient = $this->getOAClient();
		if($bshafferOauthClient){
			return $bshafferOauthClient->getButton();
		}
		return OAClient::generateDataLabShowButton($this->getOAClientId());
	}
	/**
	 * @return OAClient
	 */
	public function getOAClient(): OAClient{
		if($this->parentModel instanceof OAClient){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('oa_client')){
			return $l;
		}
		$id = $this->getOAClientId();
		$bshafferOauthClient = OAClient::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['oa_client'] = $bshafferOauthClient;
		}
		if(property_exists($this, 'bshafferOauthClient')){
			$this->bshafferOauthClient = $bshafferOauthClient;
		}
		return $bshafferOauthClient;
	}
	public function getOAClientNameLink(): string{
		return $this->getOAClient()->getDataLabDisplayNameLink();
	}
	public function getOAClientImageNameLink(): string{
		return $this->getOAClient()->getDataLabImageNameLink();
	}
}
