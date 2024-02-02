<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AdditionalSettings;
use App\AppSettings\AppDesign;
use App\AppSettings\AppSettings;
class DownloadLinkDescriptions {
    public $androidApp;
    public $iosApp;
    public $chromeExtension;
    public $webApp;
    public $physicianDashboard;
    public $integrationGuide;
    public $appDesigner;
    /**
     * DownloadLinks constructor.
     * @param AppSettings $appSettings
     */
    public function __construct($appSettings){
        if(!$appSettings->appDesign instanceof AppDesign){
            $appSettings->appDesign = new AppDesign($appSettings);
        }
        $physicianAlias = $appSettings->appDesign->aliases->active->physicianAlias;
        $patientAlias = $appSettings->appDesign->aliases->active->physicianAlias;
        $this->webApp = "Here $patientAlias"."s can import data from other apps and devices, manually record diet, ".
            "symptoms, treatments and anything else.  Then they can explore their data to find new ways to improve their lives.";
        $this->physicianDashboard = "Here $physicianAlias"."s can get an authorization url to share with their $patientAlias".
            "s, allowing them to review and analyze their data.";
        $this->appDesigner = "Easily design $appSettings->appDisplayName with our drag and drop app designer!  ".
            "Then we can build it for the web, Android, Chrome, and iOS. No coding required!";
        $this->integrationGuide = "Integrate our SDK and automatically import your users data from dozens of apps and ".
            "devices.  Then we can analyze it and help you find actionable insights for your users!";
    }
}
