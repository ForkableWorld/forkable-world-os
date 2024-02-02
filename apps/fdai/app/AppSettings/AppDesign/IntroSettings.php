<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AppDesign;
use App\AppSettings\AppDesign;
use App\AppSettings\AppSettings;
class IntroSettings extends AppDesignSettings {
    public $futuristicBackground;
    /**
     * intro constructor.
     * @param AppSettings $appSettings
     */
    public function __construct($appSettings = null){
        if(!$appSettings){
            return;
        }
        $this->appSettings = $appSettings;
        if(!isset($appSettings->appDesign->intro)){
            $appSettings->appDesign->intro = $this;
        }
        $this->populateFieldsByArrayOrObject($appSettings->appDesign->intro);
        $this->type = $appSettings->appDesign->intro->type ?? 'general';
        $this->active = AppDesign::removeNullItemsFromArray($this->active);
        $this->custom = AppDesign::removeNullItemsFromArray($this->custom);
        if($this->futuristicBackground === null){
            $this->setFuturisticBackground(true);
        }
    }
    /**
     * @return bool
     */
    public function getFuturisticBackground(){
        return $this->futuristicBackground;
    }
    /**
     * @param bool $futuristicBackground
     */
    public function setFuturisticBackground(bool $futuristicBackground){
        $this->futuristicBackground = $futuristicBackground;
    }
}
