<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AppDesign;
class Aliases {
    public $physicianAlias;
    public $patientAlias;
    /**
     * Aliases constructor.
     * @param null $aliases
     */
    public function __construct($aliases = null){
        $this->physicianAlias = 'physician';
        $this->patientAlias = 'patient';
        if(isset($aliases)){
            if(!empty($aliases->physicianAlias)){
                $this->physicianAlias = $aliases->physicianAlias;
            }
            if(!empty($aliases->patientAlias)){
                $this->patientAlias = $aliases->patientAlias;
            }
        }
    }
}
