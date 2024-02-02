<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AppDesign;
use App\AppSettings\AppDesign;
use App\AppSettings\AppSettings;
class OnboardingSettings extends AppDesignSettings {
    /**
     * Onboarding constructor.
     * @param AppSettings $appSettings
     */
    public function __construct($appSettings = null){
        if(!$appSettings){
            return;
        }
        if(!isset($appSettings->appDesign->onboarding)){
            $appSettings->appDesign->onboarding = $this;
        }
        $this->appSettings = $appSettings;
        $this->active = $appSettings->appDesign->onboarding->active ?? null;
        $this->custom = $appSettings->appDesign->onboarding->custom ?? null;
        $this->type = $appSettings->appDesign->onboarding->type ?? 'general';
        $this->removeDeprecatedOnboardingPageProperties();
        $this->active = AppDesign::removeNullItemsFromArray($this->active);
        $this->custom = AppDesign::removeNullItemsFromArray($this->custom);
    }
    /**
     * @internal param AppDesign $appDesign
     */
    private function removeDeprecatedOnboardingPageProperties(){
        $deprecatedProperties = [
            'ionIcon',
            'iconClass'
        ];
        if(isset($this->custom)){
            AppDesign::removeDeprecatedPropertiesFromObjectsInArray($this->custom, $deprecatedProperties);
        }
        if(isset($this->active)){
            AppDesign::removeDeprecatedPropertiesFromObjectsInArray($this->active, $deprecatedProperties);
        }
    }
}
