<?php
namespace App\Traits\HasModel;
use App\Models\BaseModel;
use App\Slim\Model\DBModel;
use App\Models\WpUsermetum;
use App\Buttons\QMButton;
use App\Properties\BaseProperty;
trait HasWpUsermetum
{
    public function getWpUsermetumId(): int {
        $nameOrId = $this->getAttribute('wp_usermetum_id');
        return $nameOrId;
    }
    public function getWpUsermetumButton(): QMButton {
        $wpUsermetum = $this->getWpUsermetum();
        if($wpUsermetum){
            return $wpUsermetum->getButton();
        }
        return WpUsermetum::generateShowButton($this->getWpUsermetumId());
    }
    /**
     * @return WpUsermetum
     */
    public function getWpUsermetum(): WpUsermetum {
        if($this instanceof BaseProperty && $this->parentModel instanceof WpUsermetum){return $this->parentModel;}
        /** @var BaseModel|DBModel $this */
        if($l = $this->getRelationIfLoaded('wp_usermetum')){return $l;}
        $id = $this->getWpUsermetumId();
        $wpUsermetum = WpUsermetum::findInMemoryOrDB($id);
        if(property_exists($this, 'relations')){ $this->relations['wp_usermetum'] = $wpUsermetum; }
        if(property_exists($this, 'wpUsermetum')){
            $this->wpUsermetum = $wpUsermetum;
        }
        return $wpUsermetum;
    }
}