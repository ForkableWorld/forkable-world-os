<?php
namespace App\Traits\HasModel;
use App\Models\BaseModel;
use App\Slim\Model\DBModel;
use App\Models\Credential;
use App\Buttons\QMButton;
use App\Properties\BaseProperty;
trait HasCredential
{
    public function getCredentialId(): int {
        $nameOrId = $this->getAttribute('credential_id');
        return $nameOrId;
    }
    public function getCredentialButton(): QMButton {
        $credential = $this->getCredential();
        if($credential){
            return $credential->getButton();
        }
        return Credential::generateShowButton($this->getCredentialId());
    }
    /**
     * @return Credential
     */
    public function getCredential(): Credential {
        if($this instanceof BaseProperty && $this->parentModel instanceof Credential){return $this->parentModel;}
        /** @var BaseModel|DBModel $this */
        if($l = $this->getRelationIfLoaded('credential')){return $l;}
        $id = $this->getCredentialId();
        $credential = Credential::findInMemoryOrDB($id);
        if(property_exists($this, 'relations')){ $this->relations['credential'] = $credential; }
        if(property_exists($this, 'credential')){
            $this->credential = $credential;
        }
        return $credential;
    }
}