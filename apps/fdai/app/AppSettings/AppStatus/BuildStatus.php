<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AppStatus;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\QMException;
use App\Models\Application;
use App\Storage\QMFileCache;
use App\Utils\AppMode;
use App\AppSettings\AppSettings;
use App\DevOps\GithubHelper;
use App\Types\ObjectHelper;
use App\Logging\QMLog;
use App\Utils\EnvOverride;
class BuildStatus {
    public $androidArmv7Debug;
    public $androidArmv7Release;
    public $androidRelease;
    public $androidDebug;
    public $androidX86Debug;
    public $androidX86Release;
    public $chromeExtension;
    public $ios;
    public const LAST_ALL_APPS_BUILD_TIMESTAMP = "LAST_ALL_APPS_BUILD_TIMESTAMP";
    public const STATUS_BUILDING = 'BUILDING';
    public const STATUS_READY = 'READY';
    /**
     * AppIds constructor.
     * @param AppSettings $appSettings
     */
    public function __construct($appSettings = null){
        if(isset($appSettings->appStatus->buildStatus) && is_array($appSettings->appStatus->buildStatus)){
            $appSettings->appStatus->buildStatus = ObjectHelper::convertToObject($appSettings->appStatus->buildStatus);
        }
        $classToPropertyName = ObjectHelper::classToPropertyName(get_class($this));
        $allPropertiesOfClass = ObjectHelper::getAllPropertiesOfClassAsKeyArray($this);
        foreach($allPropertiesOfClass as $propertyOfClass){
            if(isset($appSettings->appStatus->$classToPropertyName) && isset($appSettings->appStatus->$classToPropertyName->$propertyOfClass)){
                $this->$propertyOfClass = $appSettings->appStatus->$classToPropertyName->$propertyOfClass;
            }else{
                $this->$propertyOfClass = self::STATUS_BUILDING;
            }
        }
    }
    /**
     * @param bool $force
     * @return array|bool
     */
    public static function triggerBuildsForAllApps($force = false){
        if(!$force && self::alreadyBuiltAllAppsInLast24()){
            QMLog::info("Not building all apps because we already did in last 24 hours");
            return false;
        }
        $allBuildAbleApps = Application::getAllBuildableAppSettings();
        $responses = [];
        foreach($allBuildAbleApps as $app){
            if(!$app->isTestApp()){
                $responses[$app->clientId] = self::triggerBuilds($app->clientId, $force);
            }
        }
        QMFileCache::set(self::LAST_ALL_APPS_BUILD_TIMESTAMP, time(), time() + 86400);
        return $responses;
    }
    /**
     * @return bool
     */
    private static function alreadyBuiltAllAppsInLast24(){
        $lastBuiltTimestamp = QMFileCache::get(self::LAST_ALL_APPS_BUILD_TIMESTAMP);
        if(!$lastBuiltTimestamp){
            return false;
        }
        if($lastBuiltTimestamp > time() - 86400){
            return true;
        }
        return false;
    }
    /**
     * @param BuildStatus $buildStatus
     */
    public static function validateProperties($buildStatus){
        $allowedProperties = ObjectHelper::getAllPropertiesOfClassAsKeyArray(new self());
        foreach($buildStatus as $key => $value){
            if(!in_array($key, $allowedProperties, true)){
                throw new QMException(400, "$key is not a property of buildStatus. Available properties: ".implode(" ", $allowedProperties));
            }
        }
    }
    /**
     * @param BuildStatus $buildStatus
     * @internal param AppStatus $appSettings
     */
    public static function addMissingProperties($buildStatus){
        $requiredProperties = ObjectHelper::getAllPropertiesOfClassAsKeyArray(new self());
        foreach($requiredProperties as $requiredProperty){
            if(!isset($buildStatus->$requiredProperty)){
                $buildStatus->$requiredProperty = self::STATUS_BUILDING;
            }
        }
    }
	/**
	 * @param string $clientId
	 * @param bool $force
	 * @throws ClientNotFoundException
	 */
    public static function triggerBuilds($clientId, $force = false){
        $response = [];
        $appSettings = Application::getClientAppSettings($clientId);
        if(!$appSettings->appStatus->buildEnabled->atLeastOneBuildTypeEnabled()){
            return;
        }
        if($force || AppMode::isProduction() || EnvOverride::isLocal()){
            $response['merge'] = GithubHelper::mergeAppBuilderMasterIntoAppGitBranch($clientId);
            if($appSettings->appStatus->buildEnabled->chromeExtension || $appSettings->appStatus->buildEnabled->androidRelease){
                $response['circleCi'] = CircleCIHelper::triggerBuildOnCircleCI($clientId);
            }
            // BuddyBuild doesn't work anymore
            //            if($appSettings->appStatus->buildEnabled->ios){
            //                try {
            //                    $response['buddyBuild'] = BuddyBuildHelper::triggerBuildOnBuddyBuild($clientId);
            //                } catch (\Exception $e){
            //                    QMLog::exception($e);
            //                }
            //            }
        }else{
            $response = "Not building because app mode is neither production or development";
        }
        return $response;
    }
}
