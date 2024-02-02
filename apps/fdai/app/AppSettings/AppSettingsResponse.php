<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings;
use App\Slim\Model\QMResponseBody;
class AppSettingsResponse extends QMResponseBody {
    public $appSettings;
    public $privateConfig;
    public $allAppSettings;
    public $allBuildableAppSettings;
    public $staticData;
    /**
     * AppSettingsResponse constructor.
     * @param null $responseArray
     */
    public function __construct($responseArray = null){
        parent::__construct($responseArray);
    }
    /**
     * @return AppSettings
     */
    public function getAppSettings(): AppSettings{
        return $this->appSettings;
    }
    /**
     * @param AppSettings $appSettings
     */
    public function setAppSettings($appSettings){
        $this->appSettings = $appSettings;
    }
    /**
     * @return AppSettings[]
     */
    public function getAllAppSettings(): array{
        return $this->allAppSettings;
    }
    /**
     * @param AppSettings[] $allAppSettings
     */
    public function setAllAppSettings($allAppSettings){
        $this->allAppSettings = $allAppSettings;
    }
    /**
     * @return AppSettings[]
     */
    public function getAllBuildableAppSettings(): array{
        return $this->allBuildableAppSettings;
    }
    /**
     * @param AppSettings[] $allBuildableAppSettings
     */
    public function setAllBuildableAppSettings($allBuildableAppSettings){
        $allBuildableAppSettings = array_values($allBuildableAppSettings); // I index by display name internally for debugging but it's not compatible with clients
        $this->allBuildableAppSettings = $allBuildableAppSettings;
    }
    /**
     * @return StaticAppData
     */
    public function getStaticData(): StaticAppData{
        return $this->staticData;
    }
    /**
     * @param StaticAppData $staticData
     */
    public function setStaticData($staticData){
        $this->staticData = $staticData;
    }
}
