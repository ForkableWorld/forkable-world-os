<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AppDesign;
use App\AppSettings\AppSettings;
use App\Cards\QMCard;
use App\Slim\Model\StaticModel;
class AppDesignSettings extends StaticModel{
    public $type;
    public $active;
    public $custom;
    protected $appSettings;
    /**
     * @return string
     */
    public function getType(){
        return $this->type;
    }
    /**
     * @param string $type
     */
    public function setType(string $type){
        $this->type = $type;
    }
    /**
     * @return QMCard[]
     */
    public function getActiveAsCards(): array{
        $raw = $this->active;
        $cards = [];
        if(!$raw[0] instanceof QMCard){
            foreach($raw as $stdClass){
                $cardType = $this->getShortClassName();
                $cardType = str_replace("Settings", "", $cardType);
                $card = QMCard::instantiateCard($stdClass, $cardType);
                if(isset($stdClass->image->url)){$card->setImage($stdClass->image->url);}
                $cards[] = $card;
            }
        }else{
            $cards = $raw;
        }
        return $this->active = $cards;
    }
    /**
     * @param QMCard[] $active
     */
    public function setActive($active){
        $this->active = $active;
    }
    /**
     * @return QMCard[]
     */
    public function getCustom(){
        return $this->custom;
    }
    /**
     * @param QMCard[] $custom
     */
    public function setCustom($custom){
        $this->custom = $custom;
    }
    /**
     * @return QMCard[]
     */
    public function getCards(): array{
        $active = $this->getActiveAsCards();
        $cards = [];
        foreach($active as $item){
            $card = new QMCard();
            $card->populateFieldsByArrayOrObject($item);
            $cards[] = $card;
        }
        return $cards;
    }
    /**
     * @return AppSettings
     */
    public function getAppSettings(): AppSettings{
        return $this->appSettings;
    }
    /**
     * @return mixed
     */
    public function getActive(){
        return $this->active;
    }
}
