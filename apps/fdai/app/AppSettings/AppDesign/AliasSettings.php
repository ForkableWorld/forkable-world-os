<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AppDesign;
use App\AppSettings\AppSettings;
use App\AppSettings\HostAppSettings;

class AliasSettings extends AppDesignSettings {
    /**
     * Aliases constructor.
     * @param AppSettings $appSettings
     */
    public function __construct($appSettings){
        $this->active = new Aliases();
        $this->custom = new Aliases();
        if(isset($appSettings->appDesign->aliases)){
            if(isset($appSettings->appDesign->aliases->active)){
                $this->active = new Aliases($appSettings->appDesign->aliases->active);
            }
            if(isset($appSettings->appDesign->aliases->custom)){
                $this->custom = new Aliases($appSettings->appDesign->aliases->custom);
            }
        }
        $this->type = isset($appSettings->appDesign->aliases->type) ? $appSettings->appDesign->aliases->type : 'general';
    }
    /**
     * @param $aliasName
     * @return mixed
     */
    public static function getAlias(string $aliasName) {
        $s = HostAppSettings::instance();
        $d = $s->getAppDesign();
        $aliases = $d->getAliases();
        if (isset($aliases->$aliasName)) {
            return $aliases->$aliasName;
        }
        $propertyName = $aliasName.'Alias';
        if (isset($d->$propertyName)) {
            return $d->$propertyName;
        }
        return $aliasName;
    }
    /**
     * @return mixed
     */
    public static function getPhysicianAlias(){
        return self::getAlias('physician');
    }
    /**
     * @return mixed
     */
    public static function getPatientAlias(){
        return self::getAlias('patient');
    }
    /**
     * @return Aliases
     */
    public function getActive(): Aliases{
        return parent::getActive();
    }
}
