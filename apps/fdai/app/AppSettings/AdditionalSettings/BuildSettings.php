<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AdditionalSettings;
use App\AppSettings\AppSettings;
use App\Types\ObjectHelper;
/** Class BuildSettings
 * @package App\AppSettings\AdditionalSettings
 */
class BuildSettings {
    public $androidReleaseKeystoreFile;
    public $androidReleaseKeystorePassword;
    public $androidReleaseKeyAlias;
    public $androidReleaseKeyPassword;
    //public $xwalkMultipleApk;
    /**
     * BuildSettings constructor.
     * @param AppSettings $appSettings
     */
    public function __construct($appSettings = null){
        $allProperties = ObjectHelper::getAllPropertiesOfClassAsKeyArray($this);
        foreach($allProperties as $property){
            $this->$property = isset($appSettings->additionalSettings->buildSettings->$property) ? $appSettings->additionalSettings->buildSettings->$property : null;
        }
        //$this->setXwalkMultipleApk($this->xwalkMultipleApk);
    }
    /**
     * @return array
     */
    public static function getAllProperties(){
        return ObjectHelper::getAllPropertiesOfClassAsKeyArray(new self());
    }
    /**
     * @return mixed
     */
    public function getXwalkMultipleApk(){
        return $this->xwalkMultipleApk;
    }
    /**
     * @param mixed $xwalkMultipleApk
     */
    public function setXwalkMultipleApk($xwalkMultipleApk = false){
        if(!$xwalkMultipleApk){
            $this->xwalkMultipleApk = false;
        }else{
            $this->xwalkMultipleApk = true;
        }
    }
    /**
     * @return array
     */
    public static function getBooleanPropertyNames(){
        return [//'xwalkMultipleApk'
        ];
    }
}
