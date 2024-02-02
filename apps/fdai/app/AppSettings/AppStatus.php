<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\AppSettings;
use App\AppSettings\AppStatus\BetaDownloadLinks;
use App\AppSettings\AppStatus\BuildEnabled;
use App\AppSettings\AppStatus\BuildStatus;
use App\Types\ObjectHelper;
class AppStatus {
    public $buildStatus;
    public $betaDownloadLinks;
    public $buildEnabled;
    /**
     * AppStatus constructor.
     * @param AppSettings|object $a
     */
    public function __construct($a = null){
        if(isset($a->appStatus) && is_string($a->appStatus)){
            $a->appStatus = json_decode($a->appStatus);
        }
        if(isset($a->appStatus) && is_array($a->appStatus)){
            $a->appStatus = ObjectHelper::convertToObject($a->appStatus);
        }
        if(!$a->appStatus){
            $a->appStatus = $this;
        }
        $this->buildStatus = new BuildStatus($a);
        $this->betaDownloadLinks = new BetaDownloadLinks($a);
        $this->buildEnabled = new BuildEnabled($a);
    }
    /**
     * @return BuildStatus
     */
    public function getBuildStatus(): BuildStatus{
        return $this->buildStatus;
    }
    /**
     * @return BetaDownloadLinks
     */
    public function getBetaDownloadLinks(): BetaDownloadLinks{
        return $this->betaDownloadLinks;
    }
}
