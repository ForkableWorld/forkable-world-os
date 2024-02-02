<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AdditionalSettings;
use App\AppSettings\AppSettings;
use App\Types\ObjectHelper;
use App\Types\QMStr;
class GoogleAnalyticsTrackingIds {
    public $adminPanel;
    public $endUserApps;
    public $backEndAPI;
    public $informationalHomePage;
    /**
     * GoogleAnalyticsTrackingIds constructor.
     * @param $s
     */
    public function __construct($s){
        $p = ObjectHelper::classToPropertyName(get_class($this));
        if(isset($s->additionalSettings) && isset($s->additionalSettings->$p)){
            $s->additionalSettings->$p = self::replaceDeprecatedIds($s->additionalSettings->$p);
            if(is_string($s->additionalSettings->$p)){
                $s->additionalSettings->$p = json_decode($s->additionalSettings->$p);
            }
            $s->additionalSettings->$p = ObjectHelper::replaceLegacyPropertiesInObject($s->additionalSettings->$p, self::getLegacyProperties(), false);
        }
        foreach(self::getDefaults() as $key => $defaultValue){
            if(!isset($s->additionalSettings->$p->$key)){
                $this->$key = $defaultValue;
            }else{
                $this->$key = $s->additionalSettings->$p->$key;
            }
        }
    }
    /**
     * @return array
     */
    public static function getLegacyProperties(): array{
        // Legacy => Current
        return [
            'ionic'   => 'endUserApps',
            'laravel' => 'adminPanel',
            'api'     => 'backEndAPI',
            'wp'      => 'informationalHomePage'
        ];
    }
    /**
     * @return array
     */
    public static function getDefaults(){
        return [
            "adminPanel"            => "UA-39222734-29",
            "endUserApps"           => "UA-39222734-25",
            "backEndAPI"            => "UA-39222734-24",
            "informationalHomePage" => "UA-39222734-2"
        ];
    }
    /**
     * @param AppSettings $appSettings
     */
    public static function setDefaults($appSettings){
        $className = QMStr::toCamelCase(__CLASS__);
        ObjectHelper::setDefaultProperties($appSettings->additionalSettings->$className, self::getDefaults());
    }
    /**
     * @param GoogleAnalyticsTrackingIds $googleAnalyticsIds
     * @return GoogleAnalyticsTrackingIds
     */
    private static function replaceDeprecatedIds($googleAnalyticsIds){
        // Old => New
        $deprecatedIds = [
            "UA-39222734-26" => "UA-39222734-24"
        ];
        $string = json_encode($googleAnalyticsIds);
        foreach($deprecatedIds as $old => $new){
            $string = str_replace($old, $new, $string);
        }
        return json_decode($string);
    }
}
