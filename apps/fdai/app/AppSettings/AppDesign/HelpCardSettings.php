<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AppDesign;
use App\AppSettings\AppSettings;
class HelpCardSettings extends AppDesignSettings {
    /**
     * @param AppSettings $appSettings
     */
    public function __construct($appSettings){
        $this->appSettings = $appSettings;
    }
}
