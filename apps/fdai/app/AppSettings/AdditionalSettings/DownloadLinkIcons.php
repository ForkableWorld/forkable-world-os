<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AdditionalSettings;
use App\AppSettings\AppSettings;
class DownloadLinkIcons {
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
        $this->physicianDashboard = "ion-android-share-alt";
        $this->appDesigner = "ion-wand";
        $this->integrationGuide = "ion-code";
    }
}
