<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\OAAccessToken;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasOAAccessToken {
	public function getOAAccessTokenId(): int{
		$nameOrId = $this->getAttribute('oa_access_token_id');
		return $nameOrId;
	}
	public function getOAAccessTokenButton(): QMButton{
		$bshafferOauthAccessToken = $this->getOAAccessToken();
		if($bshafferOauthAccessToken){
			return $bshafferOauthAccessToken->getButton();
		}
		return OAAccessToken::generateDataLabShowButton($this->getOAAccessTokenId());
	}
	/**
	 * @return OAAccessToken
	 */
	public function getOAAccessToken(): OAAccessToken{
		if($this instanceof BaseProperty && $this->parentModel instanceof OAAccessToken){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('oa_access_token')){
			return $l;
		}
		$id = $this->getOAAccessTokenId();
		$bshafferOauthAccessToken = OAAccessToken::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['oa_access_token'] = $bshafferOauthAccessToken;
		}
		if(property_exists($this, 'bshafferOauthAccessToken')){
			$this->bshafferOauthAccessToken = $bshafferOauthAccessToken;
		}
		return $bshafferOauthAccessToken;
	}
	public function getOAAccessTokenNameLink(): string{
		return $this->getOAAccessToken()->getDataLabDisplayNameLink();
	}
	public function getOAAccessTokenImageNameLink(): string{
		return $this->getOAAccessToken()->getDataLabImageNameLink();
	}
}
