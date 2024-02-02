<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings;
use App\AppSettings\AdditionalSettings\AppIds;
use App\AppSettings\AdditionalSettings\AppImages;
use App\AppSettings\AdditionalSettings\BuildSettings;
use App\AppSettings\AdditionalSettings\DownloadLinks;
use App\AppSettings\AdditionalSettings\GoogleAnalyticsTrackingIds;
use App\AppSettings\AdditionalSettings\MonetizationSettings;
use App\AppSettings\AdditionalSettings\SocialLinks;
class AdditionalSettings {
    protected $appSettings;
    public $appIds;
    public $appImages;
    public $buildSettings;
    public $companyEmail;
    public $downloadLinks;
    public $googleAnalyticsTrackingIds;
    public $socialLinks;
    public $upgradeDisabled;
    public $monetizationSettings;
    /**
     * AdditionalSettings constructor.
     * @param AppSettings|BaseApplication $appSettings
     */
    public function __construct($appSettings = null){
        if(!$appSettings){return;}
        $this->appSettings = $appSettings;
        if(isset($appSettings->additionalSettings)){
            if(is_string($appSettings->additionalSettings)){
                $appSettings->additionalSettings = json_decode($appSettings->additionalSettings);
            }
        }else{
            $appSettings->additionalSettings = $this;
        }
        $this->appIds = new AppIds($appSettings);
        $this->monetizationSettings = new MonetizationSettings($appSettings);
        $this->appImages = new AppImages($appSettings);
        $this->buildSettings = new BuildSettings($appSettings);
        $this->companyEmail = $appSettings->additionalSettings->companyEmail ?? self::getDefaults($appSettings)['companyEmail'];
        $this->downloadLinks = new DownloadLinks($appSettings);
        $this->googleAnalyticsTrackingIds = new GoogleAnalyticsTrackingIds($appSettings);
        $this->socialLinks = new SocialLinks($appSettings);
        $this->upgradeDisabled = $appSettings->additionalSettings->upgradeDisabled ?? false;
    }
    /**
     * @param AppSettings|object $appSettings
     * @return array
     */
    public static function getDefaults(object $appSettings): array
    {
        return [
            'companyEmail'               => 'info@quantimo.do',
            'appIds'                     => new AppIds($appSettings),
            'socialLinks'                => new SocialLinks($appSettings),
            'downloadLinks'              => new DownloadLinks($appSettings),
            'googleAnalyticsTrackingIds' => new GoogleAnalyticsTrackingIds($appSettings),
            'appImages'                  => new AppImages($appSettings),
            'buildSettings'              => new BuildSettings($appSettings),
            'upgradeDisabled'            => false
        ];
    }
    /**
     * @return AppIds
     */
    public function getAppIds(){
        return $this->appIds;
    }
    /**
     * @return AppImages
     */
    public function getAppImages(): AppImages {
        if($this->appImages instanceof AppImages){
            return $this->appImages;
        }
        return $this->appImages = new AppImages($this->appSettings);
    }

    /**
     * @return DownloadLinks
     */
    public function getDownloadLinks(){
        return $this->downloadLinks;
    }

    /**
     * @param mixed $buildSettings
     */
    public function setBuildSettings($buildSettings){
        $this->buildSettings = $buildSettings;
    }
    /**
     * @return MonetizationSettings
     */
    public function getMonetizationSettings(): MonetizationSettings{
        return $this->monetizationSettings;
    }
}
