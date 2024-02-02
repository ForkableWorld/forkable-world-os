<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AdditionalSettings;
use App\AppSettings\AppSettings;
class DownloadLinkImages {
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
        $this->webApp = "https://static.quantimo.do/img/monitor-1.png?raw=true";
        $this->physicianDashboard = "https://static.quantimo.do/img/graph-8.png?raw=true";
        $this->appDesigner = "https://static.quantimo.do/img/magician.png?raw=true";
        $this->integrationGuide = "https://static.quantimo.do/img/050-laptop.png?raw=true";
    }
}
