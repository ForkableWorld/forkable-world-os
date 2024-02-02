<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage;
use App\AppSettings\AppSettings;
use App\VariableRelationships\QMUserVariableRelationship;
use App\DataSources\QMConnector;
use App\DevOps\XDebug;
use App\Logging\ConsoleLog;
use App\Logging\QMClockwork;
use App\Logging\QMLog;
use App\Models\Measurement;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\Model\Auth\QMRefreshToken;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\QMMeasurementExtended;
use App\Slim\Model\User\QMUser;
use App\Slim\QMSlim;
use App\Slim\View\Request\QMRequest;
use App\Studies\PairOfAverages;
use App\Types\QMArr;
use Cache;
/** Class Memory
 * @package App\Slim\Model
 */
class Memory {
	public const QM_REQUEST = 'qm_request';
    private const LAST = 'LAST';
    public const API_REQUEST = "API_REQUEST";
    public const API_REQUEST_TIME_LIMIT = "TIME_LIMIT";
    public const BSHAFFER_OAUTH_CLIENT_ROW = "BSHAFFER_OAUTH_CLIENT_ROW";
    public const CLIENT_APP_SETTINGS = "CLIENT_APP_SETTINGS";
    public const CLIENT_ID = "CLIENT_ID";
    public const CLIENT_WARNINGS = "CLIENT_WARNINGS";
    public const COLLECTOR_DATA = 'COLLECTORS';
    public const CONNECTOR_REQUESTS = 'CONNECTOR_REQUESTS';
    public const CONSOLE_TASK_OR_JOB = "CONSOLE_TASK";
    public const CURRENT_TASK = 'CURRENT_TASK';
    public const CURRENTLY_IMPORTING_CONNECTOR = "CURRENTLY_IMPORTING_CONNECTOR";
    public const EXCEPTIONS = 'EXCEPTIONS';
    public const FAILED_REQUEST_ERROR_MESSAGE = "FAILED_REQUEST_ERROR_MESSAGE";
    public const HOST_APP_SETTINGS = "HOST_APP_SETTINGS";
    public const INTERNAL_ERRORS = "INTERNAL_ERRORS";
    public const IS_MOBILE = 'IS_MOBILE';
    public const LARAVEL = "LARAVEL";
    public const MEMORY_CACHE_IN_GLOBALS = 'MEMORY';
    public const MISCELLANEOUS = "MISCELLANEOUS";
	public const REDIS = "REDIS";
    public const MONGO_MEMORY_CACHE = "MONGO_MEMORY_CACHE";
    public const NEW_MEASUREMENTS = 'NEW_MEASUREMENTS';
    public const NEWLY_CALCULATED_USER_VARIABLE_RELATIONSHIPS = 'NEWLY_CALCULATED_USER_VARIABLE_RELATIONSHIPS';
    public const PAIRS_OF_AVERAGES = "PAIRS_OF_AVERAGES";
    public const QM_ACCESS_TOKEN = "QM_ACCESS_TOKEN";
    public const QM_ACCESS_TOKEN_STRING = "QM_ACCESS_TOKEN_STRING";
    public const QM_CLIENTS = "QM_CLIENTS";
    public const QUERIES = "QUERIES";
    public const QUERIES_EXECUTED = "QUERIES_EXECUTED";
    public const QUERIES_EXECUTED_BY_TABLE = 'QUERIES_EXECUTED_BY_TABLE';
    public const QUERIES_BY_TABLE = 'QUERIES_BY_TABLE';
    public const IGNITION_QUERY_RECORDER = 'IGNITION_QUERY_RECORDER';
    public const QUERY_BUILDERS = 'QUERY_BUILDERS';
    public const READ_ONLY_QUERIES = 'READ_ONLY_QUERIES';
    public const RECORDS_INSERTED_OR_UPDATED = "RECORD_INSERTED_OR_UPDATED";
    public const REFRESH_TOKEN = "REFRESH_TOKEN_ROW";
    public const REQUEST_PARAMS = "REQUEST_PARAMS";
    public const REQUEST_START_MICRO_TIME = 'REQUEST_START_MICRO_TIME';
    public const REQUESTS = 'REQUESTS';
    public const RESPONSES = 'RESPONSES';
    public const ROUTES = "ROUTES";
    public const SELECT_QUERY_RESULTS = "SELECT_QUERY_RESULTS";
    public const SELF_TRACKING_DEVICES = "SELF_TRACKING_DEVICES";
    public const SENT_PUSH_NOTIFICATIONS = "SENT_PUSH_NOTIFICATIONS";
    public const SLIM = "SLIM";
    public const START_TIME = "START_TIME";
    public const TASK_COMMON_VARIABLE_ANALYSIS = 'CommonVariableUpdate';
    public const TEST_EXCEPTION = "TEST_EXCEPTION";
    public const THROW_EXCEPTION_IF_INVALID = "THROW_EXCEPTION_IF_INVALID";
    public const UNSET = 'UNSET';
    public const URL_QUERY_PARAMS = 'URL_QUERY_PARAMS';
    public const QUERY_CACHE = 'QUERY_CACHE';
    public const ALL_VOTES = 'ALL_VOTES';
    const PROPERTIES = 'PROPERTIES';
    public const DATATABLE_RESPONSE = 'DATATABLE_RESPONSE';
    public const DB_CONNECTIONS = 'db-connections';
    public const NOVA_REQUEST = "NOVA_REQUEST";
    public const REPROCESSING = 'REPROCESSING';
    private static array $cache = [];
    public static function resetClearOrDeleteAll(){
	    ConsoleLog::debug(__METHOD__);
        self::flush();
        QMRequest::unsetWebHookClient();
        QMConnector::setCurrentlyImportingConnector(null);
        QMSlim::destroyInstances();
        Cache::store('cache-builder')->flush();  // Query builder cache from BaseModel
    }
	/**
     * @return string[]
     */
    public static function getClientWarnings(): array {
        return self::getByPrimaryKey(self::CLIENT_WARNINGS);
    }
	/**
     * @param QMAccessToken|string $providedToken
     * @return bool
     */
    public static function setAccessTokenIfNotSet($providedToken): bool{
        if(!$providedToken){
            QMLog::error("No accessTokenRow provided to setAccessTokenRow!");
            return false;
        }
        if(is_string($providedToken)){
            self::setQmAccessTokenString($providedToken);
            return true;
        }
        if(is_array($providedToken) || isset($providedToken->access_token)){
            $providedToken = new QMAccessToken($providedToken);
        }
        if($existing = self::getQmAccessTokenObject()){
            if($existing->userId !== $providedToken->userId){
                QMLog::debug("Not overwriting global access token for user ".
                    "$existing->userId with token for user $providedToken->userId because it might be a patient sharing ".
                    "with a physician.");
                return false;
            }
        }
        self::set(self::QM_ACCESS_TOKEN, $providedToken);
        return true;
    }
    /**
     * @param string $accessTokenString
     */
    public static function setQmAccessTokenString(string $accessTokenString){
        self::set(self::QM_ACCESS_TOKEN_STRING, $accessTokenString);
    }
    /**
     * @return string
     */
    public static function getQmAccessTokenString(){
        return self::get(self::QM_ACCESS_TOKEN_STRING,self::MISCELLANEOUS);
    }
    /**
     * @param string|null $clientId
     * @return QMAccessToken
     */
    public static function getQmAccessTokenObject(string $clientId = null): ?QMAccessToken {
        /** @var QMAccessToken $token */
        $token = self::get(self::QM_ACCESS_TOKEN,self::MISCELLANEOUS);
        if(!$token){return null;}
        if(!$clientId){return $token;}
        if($clientId === $token->clientId){return $token;}
        return null;
    }
    /**
     * @param QMRefreshToken|null $refreshTokenRow
     */
    public static function setRefreshToken(?QMRefreshToken $refreshTokenRow) {
        self::set(self::REFRESH_TOKEN, $refreshTokenRow);
    }
    /**
     * @param string|null $clientId
     * @return mixed
     */
    public static function getRefreshToken(string $clientId = null): ?QMRefreshToken {
        /** @var QMRefreshToken $t */
        $token = self::get(self::REFRESH_TOKEN,self::MISCELLANEOUS);
        if(!$token){return null;}
        if(!$clientId){return $token;}
        if($clientId === $token->clientId){return $token;}
        return null;
    }
    public static function flush(){
        self::$cache = [];
        self::setCacheInGlobalsForXDebugViewing();
    }
    /**
     * @param string|array $secondaryKey
     * @param string|null $primaryKey
     * @param null $default
     * @return mixed
     */
    public static function get($secondaryKey, string $primaryKey = null, $default = null){
        if(!$primaryKey){$primaryKey = self::MISCELLANEOUS;}
        if($secondaryKey instanceof \ArrayAccess){
            $arr = [];
            foreach($secondaryKey as $id){
                $one = static::get($id, $primaryKey);
                if($one){
                    $arr[$id] = $one;
                }
            }
            if(!$arr){return $default;}
        }
        $val = self::$cache[$primaryKey][$secondaryKey] ?? null;
        if($val && $val !== ""){  // Don't use !empty because it will filter 0's and empty arrays
            return $val;
        }
        if($val === false){
            return false;
        }
        return $default;
    }
    /**
     * @param string $secondaryKey
     * @param mixed $value
     * @param string|null $primaryKey
     * @return mixed
     */
    public static function set(string $secondaryKey, $value, string $primaryKey = null){
        if(!$primaryKey){$primaryKey = self::MISCELLANEOUS;}
        self::$cache[$primaryKey][$secondaryKey] = $value;
        self::setLast($value, $primaryKey);
        self::setCacheInGlobalsForXDebugViewing();
        return $value;
    }
    public static function flushSecondaryKey(string $primaryKey, string $secondaryKey){
        self::set($secondaryKey, null, $primaryKey);
    }
    /**
     * @param string $secondaryKey
     * @param string|null $primaryKey
     * @return mixed
     */
    public static function delete(string $secondaryKey, string $primaryKey): void {
        unset(self::$cache[$primaryKey][$secondaryKey]);
        self::setCacheInGlobalsForXDebugViewing();
    }
    /**
     * @param string $primaryKey
     * @param array|object $objects
     * @return mixed
     */
    public static function setByPrimaryKey(string $primaryKey, $objects): void {
        self::$cache[$primaryKey] = $objects;
        self::setCacheInGlobalsForXDebugViewing();
    }
    public static function setStartTime(): void {
        self::set(self::START_TIME, time());
    }
    public static function setStartTimeIfNotSet(): void{
        if(self::getStartTime() === null){
            self::setStartTime();
        }
    }
    /**
     * @return int
     */
    public static function getStartTime(): ?int {
        $time = self::get(self::START_TIME,self::MISCELLANEOUS);
        if(!$time){
            QMLog::debug("Start time not set!");
            return null;
        }
        return $time;
    }
    /**
     * @return int
     */
    public static function getDurationInSeconds(string $logMessageIfComplete = null): int {
		$start = self::getStartTime();
		$end = time();
	    $duration = $end - $start;
		if($logMessageIfComplete){
			QMClockwork::logDuration($logMessageIfComplete, $start, $end);
		}
        return $duration;
    }
    /**
     * @return string
     */
    public static function getDurationInSecondsString(string $logMessageIfComplete = null): string {
        return " (took ".self::getDurationInSeconds($logMessageIfComplete)." seconds)";
    }
    /**
     * @param QMUserVariableRelationship $c
     */
    public static function addNewlyCalculatedUserVariableRelationship(QMUserVariableRelationship $c){
        self::set($c->getUniqueIndexIdsSlug(), $c, self::NEWLY_CALCULATED_USER_VARIABLE_RELATIONSHIPS);
    }
    /**
     * @param int $userId
     * @param int $causeId
     * @param int $effectId
     * @return QMUserVariableRelationship
     */
    public static function getNewlyCalculatedUserVariableRelationship(int $userId, int $causeId, int $effectId): ?QMUserVariableRelationship {
        $all = self::getByPrimaryKey(self::NEWLY_CALCULATED_USER_VARIABLE_RELATIONSHIPS);
        if(!$all){return null;}
        return collect($all)->filter(function($one) use($userId, $causeId, $effectId){
            /** @var QMUserVariableRelationship $one */
            return $one->userId === $userId &&
                $one->causeVariableId === $causeId &&
                $one->effectVariableId === $effectId;
        })->first();
    }
    /**
     * @param string $currentTask
     */
    public static function setCurrentTask(string $currentTask){
        self::set(self::CURRENT_TASK, $currentTask);
    }
    /**
     * @return string
     */
    public static function getCurrentTask(): ?string {
        return self::get(self::CURRENT_TASK,self::MISCELLANEOUS);
    }
    /**
     * @return bool
     */
    public static function currentTaskIsCommonVariableAnalysis(): bool {
        return self::getCurrentTask() === self::TASK_COMMON_VARIABLE_ANALYSIS;
    }
    /**
     * @param int $causeVariableId
     * @param int $effectVariableId
     * @param PairOfAverages[] $pairs
     */
    public static function setPairsOfAverages(int $causeVariableId, int $effectVariableId, array $pairs){
        self::set("$causeVariableId-$effectVariableId", $pairs, self::PAIRS_OF_AVERAGES);
    }
    /**
     * @param int $causeVariableId
     * @param int $effectVariableId
     * @return PairOfAverages[]
     */
    public static function getPairsOfAverages(int $causeVariableId, int $effectVariableId): ?array {
        return self::get("$causeVariableId-$effectVariableId", self::PAIRS_OF_AVERAGES);
    }
    /**
     * @param string $name
     * @param $value
     * @return mixed
     */
    public static function setRequestParam(string $name, $value){
        return self::set($name, $value, self::REQUEST_PARAMS);
    }
    /**
     * @param array|object $arrayOrObject
     */
    public static function setRequestParams($arrayOrObject){
        foreach($arrayOrObject as $secondaryKey => $value){
            self::setRequestParam($secondaryKey, $value);
        }
    }
    /**
     * @return array
     */
    public static function getRequestParams(): array{
        return self::getByPrimaryKey(self::REQUEST_PARAMS);
    }
    /**
     * @param string $name
     * @return mixed
     */
    public static function getRequestParam(string $name){
        return self::get($name, self::REQUEST_PARAMS, self::UNSET);
    }
    /**
     * @param string $name
     * @param $value
     * @return mixed
     */
    public static function setUrlParam(string $name, $value){
        return self::set($name, $value, self::URL_QUERY_PARAMS);
    }
    /**
     * @param string $name
     * @return mixed
     */
    public static function getUrlParam(string $name){
        return self::get($name, self::URL_QUERY_PARAMS, self::UNSET);
    }
    /**
     * @param string $clientId
     * @param AppSettings|bool $appSettings
     */
    public static function setClientAppSettings(string $clientId, $appSettings){
        self::set($clientId, $appSettings, self::CLIENT_APP_SETTINGS);
    }
    /**
     * @param string|null $clientId
     * @return AppSettings
     */
    public static function getClientAppSettings(string $clientId = null){
        if(!$clientId){$clientId = BaseClientIdProperty::fromMemory();}
        return self::get($clientId,self::CLIENT_APP_SETTINGS);
    }
	/**
     * @param string $secondaryKey
     * @param $item
     * @param string|null $primaryKey
     */
    public static function add(string $secondaryKey, $item, string $primaryKey = null){
        if(!$primaryKey){$primaryKey = self::MISCELLANEOUS;}
        if(!isset(self::$cache[$primaryKey][$secondaryKey])){
            self::$cache[$primaryKey][$secondaryKey] = [];
        }
        self::$cache[$primaryKey][$secondaryKey][] = $item;
        self::setLast($item, $primaryKey);
        self::setCacheInGlobalsForXDebugViewing();
    }
    /**
     * @param string $secondaryKey
     * @param string|null $primaryKey
     */
    public static function flushKey(string $secondaryKey, string $primaryKey){
        self::$cache[$primaryKey][$secondaryKey] = [];
        self::setCacheInGlobalsForXDebugViewing();
    }
    /**
     * @param string|null $primaryKey
     * @param $item
     */
    public static function addByPrimaryKey(string $primaryKey, $item){
        if(!isset(self::$cache[$primaryKey])){self::$cache[$primaryKey] = [];}
        self::$cache[$primaryKey][] = $item;
        self::setLast($item, $primaryKey);
        self::setCacheInGlobalsForXDebugViewing();
    }
    private static function setCacheInGlobalsForXDebugViewing(){
        if(XDebug::active()){ // Not sure why this is so slow?
            $GLOBALS[self::MEMORY_CACHE_IN_GLOBALS] = self::$cache;
        }
        if(self::$cache){
            //TestMemory::setTestCache(self::$cache);
        }
        // THIS IS SLOW FOR SOME REASON
//        if(self::$cache){
//            if(!isset($GLOBALS[self::TEST_CACHE])){$GLOBALS[self::TEST_CACHE] = [];}
//            foreach(self::$cache as $key => $value){
//                $GLOBALS[self::TEST_CACHE][$key] = $value;
//            }
//        }
    }
    /**
     * @param QMMeasurement[]|Measurement[] $combined
     */
    public static function addNewMeasurements(array $combined){
        foreach($combined as $m){
            self::$cache[self::NEW_MEASUREMENTS][$m->getUserId()][$m->getVariableName()][$m->getStartAtAttribute()] = $m;
        }
        self::setCacheInGlobalsForXDebugViewing();
    }
    /**
     * @param int $userId
     * @return array
     */
    public static function getNewMeasurementsForUserByVariable(int $userId): array {
        $newByUser = self::getByPrimaryKey(self::NEW_MEASUREMENTS);
        $byVariableName = $newByUser[$userId] ?? [];
        $dbms = [];
        foreach($byVariableName as $variableName => $measurements){
            $dbms[$variableName] = QMMeasurementExtended::toDBModels($measurements);
        }
        return $dbms;
    }
    /**
     * @return array
     */
    public static function getNewMeasurements(): array {
        $newByUser = self::getByPrimaryKey(self::NEW_MEASUREMENTS);
        $all = [];
        foreach($newByUser as $user => $byVariable){
            foreach($byVariable as $var => $measurements){
                $all = array_merge($all, $measurements);
            }
        }
        return $all;
    }
    /**
     * @return AppSettings|null
     */
    public static function getHostAppSettings(): ?AppSettings{
        return self::get(self::HOST_APP_SETTINGS,self::MISCELLANEOUS);
    }
    /**
     * @param AppSettings $appSettings
     * @return AppSettings|null
     */
    public static function setHostAppSettings(AppSettings $appSettings): ?AppSettings{
        return self::set(self::HOST_APP_SETTINGS, $appSettings);
    }
    /**
     * @param string $primaryKey
     * @return array|object
     */
    public static function getByPrimaryKey(string $primaryKey) {
        $cache = self::$cache;
        $forPrimaryKey = $cache[$primaryKey] ?? [];
        return $forPrimaryKey;
    }
    /**
     * @param string $primaryKey
     */
    public static function purgePrimaryKey(string $primaryKey){
        self::setByPrimaryKey($primaryKey, []);
    }
    public static function outputMemoryContents(){
        QMLog::print(self::$cache, "Memory Contents");
    }
    public static function getUsers(): array {
        return QMUser::getAllFromMemoryIndexedById() ?? [];
    }
	/**
	 * @param string $primaryKey
	 * @return mixed|null
	 */
	public static function getLast(string $primaryKey) {
        return self::$cache[self::LAST][$primaryKey] ?? null;
    }
    /**
     * @param $item
     * @param string $primaryKey
     */
    public static function setLast($item, string $primaryKey): void{
        self::$cache[self::LAST][$primaryKey] = $item;
    }
    public static function where(array $params, string $primaryKey): array{
        $all = static::getByPrimaryKey($primaryKey);
        $matches = QMArr::whereByParams($params, $all);
        return $matches;
    }
	/**
	 * @param array $params
	 * @param string $primaryKey
	 * @return mixed|null
	 */
	public static function firstMatch(array $params, string $primaryKey){
        $all = static::where($params, $primaryKey);
        return QMArr::first($all);
    }
	/**
     * @return array
     */
    public static function all(): array{
        return self::$cache;
    }
	/**
	 * @param string $primaryKey
	 * @param string|null $secondaryKey
	 * @return array
	 */
	public static function getAll(string $primaryKey = self::MISCELLANEOUS, string $secondaryKey = null): ?array {
		if($secondaryKey){
			return self::$cache[$primaryKey][$secondaryKey];
		}
		return self::$cache[$primaryKey] ?? null;
	}
}
