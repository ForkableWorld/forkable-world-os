<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AppStatus;
use App\AppSettings\AppSettings;
use App\Types\ObjectHelper;
class BuildEnabled {
    public $androidArmv7Debug;
    public $androidArmv7Release;
    public $androidRelease;
    public $androidDebug;
    public $androidX86Debug;
    public $androidX86Release;
    public $chromeExtension;
    public $ios;
    /**
     * AppIds constructor.
     * @param AppSettings $appSettings
     */
    public function __construct($appSettings = null){
        if(isset($appSettings->appStatus->buildEnabled) && is_array($appSettings->appStatus->buildEnabled)){
            $appSettings->appStatus->buildEnabled = ObjectHelper::convertToObject($appSettings->appStatus->buildEnabled);
        }
        $classToPropertyName = ObjectHelper::classToPropertyName(get_class($this));
        $allPropertiesOfClass = ObjectHelper::getAllPropertiesOfClassAsKeyArray($this);
        foreach($allPropertiesOfClass as $propertyOfClass){
            if(isset($appSettings->appStatus->$classToPropertyName) && isset($appSettings->appStatus->$classToPropertyName->$propertyOfClass)){
                $this->$propertyOfClass = (bool)$appSettings->appStatus->$classToPropertyName->$propertyOfClass;
            }else{
                $this->$propertyOfClass = false;
            }
        }
    }
    /**
     * @return bool
     */
    public function atLeastOneBuildTypeEnabled(){
        return $this->ios || $this->androidRelease || $this->chromeExtension;
    }
}
