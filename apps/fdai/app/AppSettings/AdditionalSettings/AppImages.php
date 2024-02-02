<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AdditionalSettings;
use App\Logging\QMLog;
use App\Properties\Base\BaseClientIdProperty;
use App\UI\ImageHelper;
use App\UI\ImageUrls;
use App\AppSettings\AppSettings;
use App\Types\ObjectHelper;
use App\Storage\S3\S3PublicApps;
use stdClass;
class AppImages {
    protected $appSettings;
    public $appIcon;
    public $notificationIcon;
    public $splashScreen;
    public $textLogo;
    public $favicon;
    public $instructions;
    /**
     * SocialLinks constructor.
     * @param AppSettings $appSettings
     */
    public function __construct($appSettings = null){
        if(!$appSettings){return;}
        $this->appSettings = $appSettings;
        $classToPropertyName = ObjectHelper::classToPropertyName(get_class($this));
        if(!isset($appSettings->additionalSettings->$classToPropertyName)){
            $appSettings->additionalSettings->$classToPropertyName = new stdClass();
        }
        if(isset($appSettings->appIcon) && !isset($appSettings->additionalSettings->appImages->appIcon)){
            $appSettings->additionalSettings->appImages->appIcon = $appSettings->appIcon;
        }
        if(isset($appSettings->splashScreen) && !isset($appSettings->additionalSettings->appImages->splashScreen)){
            $appSettings->additionalSettings->appImages->splashScreen = $appSettings->splashScreen;
        }
        if(isset($appSettings->textLogo) && !isset($appSettings->additionalSettings->appImages->textLogo)){
            $appSettings->additionalSettings->appImages->textLogo = $appSettings->textLogo;
        }
        foreach(self::getDefaults() as $key => $defaultValue){
            if(!isset($appSettings->additionalSettings->$classToPropertyName->$key)){
                $this->$key = $defaultValue;
            }else{
                $this->$key = $appSettings->additionalSettings->$classToPropertyName->$key;
            }
        }
        $this->favicon = S3PublicApps::getAppResourceUrl($appSettings->clientId, 'icon_16.png');
        $this->instructions = new AppImagesInstructions();
        if(empty($appSettings->additionalSettings->appImages->appIcon)){
            $url = S3PublicApps::getAppResourceUrl(BaseClientIdProperty::CLIENT_ID_QUANTIMODO, 'app_images_appIcon.png');
            $appSettings->additionalSettings->appImages->appIcon = $url;
        }
    }
    /**
     * @return array
     */
    public static function getDefaults(): array{
        return [
            "appIcon"          => null,
            "textLogo"         => null,
            "splashScreen"     => "https://static.quantimo.do/img/splash-screens/mindfulness-splash.png",
            "notificationIcon" => "ion-android-happy"
        ];
    }
    /**
     * @param AppImages $appImages
     */
    public static function setDefaults($appImages){
        ObjectHelper::setDefaultProperties($appImages, self::getDefaults());
    }
    /**
     * @param AppSettings $appSettings
     */
    public static function processAppImages($appSettings){
        if(!property_exists($appSettings->additionalSettings, 'appImages')){
            $appSettings->additionalSettings->appImages = new appImages($appSettings);
        }
        self::setDefaults($appSettings->additionalSettings->appImages);
    }
    /**
     * @return string
     */
    public function getTextLogo(): string {
        return $this->textLogo;
    }
    /**
     * @return string
     */
    public function getAppIcon(): string {
        if(!$this->appIcon){
            QMLog::error("No app icon for ".
                $this->getAppSettings()->getTitleAttribute()." so using brain. \n\tPlease set one at ".
                $this->getAppSettings()->getEditUrl());
            return ImageUrls::CROWD_SOURCING_UTOPIA_BRAIN_ICON;
        }
        return $this->appIcon;
    }
    /**
     * @return AppSettings
     */
    public function getAppSettings(): AppSettings{
        return $this->appSettings;
    }
}
