<?php
namespace App\Traits\HasModel;
use App\Models\BaseModel;
use App\Slim\Model\DBModel;
use App\Models\IpDatum;
use App\Buttons\QMButton;
use App\Properties\BaseProperty;
trait HasIpDatum
{
    public function getIpDatumId(): int {
        $nameOrId = $this->getAttribute('ip_datum_id');
        return $nameOrId;
    }
    public function getIpDatumButton(): QMButton {
        $ipDatum = $this->getIpDatum();
        if($ipDatum){
            return $ipDatum->getButton();
        }
        return IpDatum::generateShowButton($this->getIpDatumId());
    }
    /**
     * @return IpDatum
     */
    public function getIpDatum(): IpDatum {
        if($this instanceof BaseProperty && $this->parentModel instanceof IpDatum){return $this->parentModel;}
        /** @var BaseModel|DBModel $this */
        if($l = $this->getRelationIfLoaded('ip_datum')){return $l;}
        $id = $this->getIpDatumId();
        $ipDatum = IpDatum::findInMemoryOrDB($id);
        if(property_exists($this, 'relations')){ $this->relations['ip_datum'] = $ipDatum; }
        if(property_exists($this, 'ipDatum')){
            $this->ipDatum = $ipDatum;
        }
        return $ipDatum;
    }
}