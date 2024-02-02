<?php
namespace App\Traits\HasModel;
use App\Models\BaseModel;
use App\Slim\Model\DBModel;
use App\Models\OARefreshToken;
use App\Buttons\QMButton;
use App\Properties\BaseProperty;
trait HasOARefreshToken
{
    public function getOARefreshTokenId(): int {
        $nameOrId = $this->getAttribute('oa_refresh_token_id');
        return $nameOrId;
    }
    public function getOARefreshTokenButton(): QMButton {
        $oARefreshToken = $this->getOARefreshToken();
        if($oARefreshToken){
            return $oARefreshToken->getButton();
        }
        return OARefreshToken::generateShowButton($this->getOARefreshTokenId());
    }
    /**
     * @return OARefreshToken
     */
    public function getOARefreshToken(): OARefreshToken {
        if($this instanceof BaseProperty && $this->parentModel instanceof OARefreshToken){return $this->parentModel;}
        /** @var BaseModel|DBModel $this */
        if($l = $this->getRelationIfLoaded('oa_refresh_token')){return $l;}
        $id = $this->getOARefreshTokenId();
        $oARefreshToken = OARefreshToken::findInMemoryOrDB($id);
        if(property_exists($this, 'relations')){ $this->relations['oa_refresh_token'] = $oARefreshToken; }
        if(property_exists($this, 'oARefreshToken')){
            $this->oARefreshToken = $oARefreshToken;
        }
        return $oARefreshToken;
    }
}