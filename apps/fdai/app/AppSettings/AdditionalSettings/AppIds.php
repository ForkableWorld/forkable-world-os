<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AdditionalSettings;
use App\AppSettings\AppSettings;
use App\AppSettings\HostAppSettings;
use App\Types\ObjectHelper;
use App\Types\QMStr;
class AppIds {
    public const DEFAULT_FACEBOOK_APP_ID = 225078261031461;
    public $googleReversedClientId;
    public $appleId;
    public $appIdentifier;
    public $facebookAppId;
    public $ionicAppId;
    /**
     * AppIds constructor.
     * @param AppSettings $appSettings
     */
    public function __construct($appSettings = null){
        if(!$appSettings){
            $appSettings = HostAppSettings::instance();
        }
        $classToPropertyName = ObjectHelper::classToPropertyName(get_class($this));
        foreach(self::getDefaults($appSettings) as $key => $defaultValue){
            if(!isset($appSettings->additionalSettings->$classToPropertyName->$key)){
                $this->$key = $defaultValue;
            }else{
                $this->$key = $appSettings->additionalSettings->$classToPropertyName->$key;
            }
        }
    }
    /**
     * @param AppSettings $appSettings
     * @return array
     */
    public static function getDefaults($appSettings){
        return [
            'googleReversedClientId' => 'com.googleusercontent.apps.1052648855194-9cv6lr7d617fu6b95gutkc7gvdubb8gl',
            'appleId'                => '',
            'appIdentifier'          => 'com.quantimodo.'.$appSettings->clientId,
            'facebookAppId'          => 225078261031461,
            'ionicAppId'             => '',
        ];
    }
    /**
     * @param AppSettings $s
     */
    public static function processAppIds($s){
        if(!property_exists($s->additionalSettings, 'appIds')){
            $s->additionalSettings->appIds = new AppIds($s);
        }
        if(property_exists($s->additionalSettings, 'appIdentifier')){
            $s->additionalSettings->appIds->appIdentifier = $s->additionalSettings->appIdentifier;
            unset($s->additionalSettings->appIdentifier);
        }
        if(property_exists($s->additionalSettings, 'appleId')){
            $s->additionalSettings->appIds->appleId = $s->additionalSettings->appleId;
            unset($s->additionalSettings->appleId);
        }
        if(property_exists($s->additionalSettings, 'facebookAppId')){
            $s->additionalSettings->appIds->facebookAppId = $s->additionalSettings->facebookAppId;
            unset($s->additionalSettings->facebookAppId);
        }
        if(property_exists($s->additionalSettings, 'ionicAppId')){
            $s->additionalSettings->appIds->ionicAppId = $s->additionalSettings->ionicAppId;
            unset($s->additionalSettings->ionicAppId);
        }
        if(property_exists($s->additionalSettings, 'googleReversedClientId')){
            $s->additionalSettings->appIds->googleReversedClientId = $s->additionalSettings->googleReversedClientId;
            unset($s->additionalSettings->googleReversedClientId);
        }
        $qmPrefix = 'com.quantimodo.';
        if(strpos($s->additionalSettings->appIds->appIdentifier, $qmPrefix) !== false || str_replace($qmPrefix, '', $s->additionalSettings->appIds->appIdentifier) !== $s->clientId){
	        //QMLog::error("App id does not match client id! Fixing it", ['appIdentifier' => $appSettings->additionalSettings->appIds->appIdentifier]);
            $s->additionalSettings->appIds->appIdentifier = $qmPrefix.$s->clientId;
        }
    }
    /**
     * @param AppSettings $appSettings
     */
    public static function setDefaults($appSettings){
        $className = QMStr::toCamelCase(__CLASS__);
        ObjectHelper::setDefaultProperties($appSettings->additionalSettings->$className, self::getDefaults($appSettings));
    }
    /**
     * @return int
     */
    public function getFacebookAppId(): int{
        if(!$this->facebookAppId){
            $this->facebookAppId = self::DEFAULT_FACEBOOK_APP_ID;
        }
        return $this->facebookAppId;
    }
}
