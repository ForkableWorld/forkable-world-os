<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AdditionalSettings;
use App\Models\Application;
use App\Properties\Base\BaseClientIdProperty;
use App\AppSettings\AppSettings;
use App\Properties\Base\BaseUrlProperty;
use App\Utils\IonicHelper;
use App\Types\ObjectHelper;
use App\Types\QMStr;
use App\AppSettings\HostAppSettings;
use stdClass;
class DownloadLinks {
    public const DEFAULT_CHROME_EXTENSION_DOWNLOAD_LINK = "https://chrome.google.com/webstore/detail/quantimodo-life-tracking/jioloifallegdkgjklafkkbniianjbgi";
    public const DEFAULT_IOS_DOWNLOAD_LINK = 'https://itunes.apple.com/us/app/quantimodo-life-tracker/id1115037060?mt=8';
    public const DEFAULT_ANDROID_DOWNLOAD_LINK = "https://play.google.com/store/apps/details?id=com.quantimodo.".
    BaseClientIdProperty::CLIENT_ID_QUANTIMODO;
    public $androidApp;
    public $iosApp;
    public $chromeExtension;
    public $webApp;
    public $physicianDashboard;
    public $integrationGuide;
    public $appDesigner;
    public $descriptions;
    public $icons;
    public $images;
    public $inboxUrl;
    public $settingsUrl;
    /**
     * @var AppSettings|null
     */
    protected $appSettings;
    /**
     * DownloadLinks constructor.
     * @param AppSettings $appSettings
     */
    public function __construct($appSettings = null){
        if(!$appSettings){
            $appSettings = HostAppSettings::instance();
        }
        $this->appSettings = $appSettings;
        $clientId = $appSettings->clientId;
        $this->populateFromAppSettingsObjectOrDefaults($appSettings);
        $this->webApp = self::getWebAppUrlForClientId($clientId);
        $this->physicianDashboard = "https://physician.quantimo.do?clientId=$clientId";
        $this->integrationGuide = AppSettings::getIntegrationGuideLink($clientId);
        $this->appDesigner = AppSettings::getAppDesignerLink($clientId);
        $this->descriptions = new DownloadLinkDescriptions($appSettings);
        $this->icons = new DownloadLinkIcons($appSettings);
        $this->images = new DownloadLinkImages($appSettings);
        $this->androidApp = self::DEFAULT_ANDROID_DOWNLOAD_LINK;
        $this->inboxUrl = IonicHelper::getInboxUrl([], $clientId);
	    $this->settingsUrl = IonicHelper::getSettingsUrl([], $clientId);
    }
    /**
     * @param AppSettings $appSettings
     * @return array
     */
    public static function getDefaults($appSettings): array{
        return [
            "chromeExtension" => self::DEFAULT_CHROME_EXTENSION_DOWNLOAD_LINK,
            "iosApp"          => self::DEFAULT_IOS_DOWNLOAD_LINK,
            "androidApp"      => self::DEFAULT_ANDROID_DOWNLOAD_LINK,
            "webApp"          => self::getWebAppUrlForClientId($appSettings->clientId)
        ];
    }
    /**
     * @param AppSettings $appSettings
     */
    public static function setDefaults($appSettings){
        $className = QMStr::toCamelCase(__CLASS__);
        ObjectHelper::setDefaultProperties($appSettings->additionalSettings->$className, self::getDefaults($appSettings));
    }
    /**
     * @return string
     */
    public function getInboxUrl(): string {
        return $this->inboxUrl = IonicHelper::getInboxUrl([], $this->getClientId());
    }
    /**
     * @return string
     */
    public function getSettingsUrl(): string {
        return $this->settingsUrl = IonicHelper::getSettingsUrl([], $this->getClientId());
    }
    /**
     * @return stdClass|AppSettings
     */
    public function getAppSettings(){
        return $this->appSettings;
    }
    /**
     * @return string
     */
    private function getClientId(): string {
        return $this->getAppSettings()->clientId;
    }
    /**
     * @param $appSettings
     */
    private function populateFromAppSettingsObjectOrDefaults($appSettings): void{
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
     * @param string $clientId
     * @return string
     */
    private static function getWebAppUrlForClientId(string $clientId): string{
        return "https://".$clientId. BaseUrlProperty::WILDCARD_APEX_DOMAIN;
    }
}
