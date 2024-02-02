<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AppStatus;
use App\AppSettings\AppSettings;
use App\Types\ObjectHelper;
class BetaDownloadLinks {
    public $androidArmv7Debug;
    public $androidArmv7Release;
    public $androidRelease;
    public $androidDebug;
    public $androidX86Debug;
    public $androidX86Release;
    public $chromeExtension;
    public $ios;
    /**
     * BetaDownloadLinks constructor.
     * @param AppSettings|object $a
     */
    public function __construct($a){
        if(isset($a->appStatus->betaDownloadLinks) && is_array($a->appStatus->betaDownloadLinks)){
            $a->appStatus->betaDownloadLinks = ObjectHelper::convertToObject($a->appStatus->betaDownloadLinks);
        }
        if(!isset($a->appStatus->betaDownloadLinks)){
            $a->appStatus->betaDownloadLinks = new \stdClass();
        }
		$links = $a->appStatus->betaDownloadLinks;
        $this->androidArmv7Debug = $links->androidArmv7Debug ?? null;
        $this->androidArmv7Release = $links->androidArmv7Release ?? null;
        $this->androidRelease = $links->androidRelease ?? null;
        $this->androidDebug = $links->androidDebug ?? null;
        $this->androidX86Debug = $links->androidX86Debug ?? null;
        $this->androidX86Release = $links->androidX86Release ?? null;
        $this->chromeExtension = $links->chromeExtension ?? null;
        $this->ios = $links->ios ?? null;
    }
}
