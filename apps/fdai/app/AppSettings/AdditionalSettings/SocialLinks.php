<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AdditionalSettings;
use App\AppSettings\AppSettings;
use App\Types\ObjectHelper;
use App\Types\QMStr;
class SocialLinks {
    public $facebook;
    public $twitter;
    public $google;
    public $linkedIn;
    /**
     * SocialLinks constructor.
     * @param AppSettings $appSettings
     */
    public function __construct($appSettings){
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
            "facebook" => "https://www.facebook.com/Quantimodology",
            "twitter"  => "https://twitter.com/quantimodo",
            "google"   => "https://plus.google.com/communities/100581500031158281444",
            "linkedIn" => "https://www.linkedin.com/company/quantimodo"
        ];
    }
    /**
     * @param AppSettings $appSettings
     */
    public static function setDefaults($appSettings){
        $className = QMStr::toCamelCase(__CLASS__);
        ObjectHelper::setDefaultProperties($appSettings->additionalSettings->$className, self::getDefaults($appSettings));
    }
}
