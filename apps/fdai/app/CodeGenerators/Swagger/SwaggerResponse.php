<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\CodeGenerators\Swagger;
use App\Properties\Base\BaseClientIdProperty;
use LogicException;
use App\Slim\QMSlim;
use App\Exceptions\QMException;
use App\Utils\APIHelper;
use App\DataSources\QMClient;
use App\Types\QMArr;
use App\Files\FileHelper;
use stdClass;
class SwaggerResponse {
    public $description;
    public $schema;
    /**
     * SwaggerResponse constructor.
     * @param null $schemaDefinitionName
     * @param null $responseDataArray
     */
    public function __construct($schemaDefinitionName = null, $responseDataArray = null){
        $this->description = "Successful operation";
        SwaggerDefinition::addOrUpdateSwaggerDefinition($responseDataArray, $schemaDefinitionName);
        $this->schema = new SwaggerReference($schemaDefinitionName);
    }
    /**
     * @param $responseCode
     * @param $responseArray
     */
    public static function addNewSwaggerGetResponses($responseCode, $responseArray){
        if($responseCode == QMException::CODE_UNAUTHORIZED){
            return;
        }
        if(!\App\Utils\Env::get('UPDATE_SWAGGER_JSON')){
            return;
        }
        $currentPath = SwaggerJson::getFormattedPath();
        if(strpos($currentPath, "trackingReminderNotifications/track") !== false){
            return;
        }
        $currentRequestMethod = strtolower(QMSlim::getInstance()->request()->getMethod());
        if(!isset(SwaggerJson::paths()->$currentPath)){
            SwaggerJson::paths()->$currentPath = new StdClass();
        }
        if(!isset(SwaggerJson::paths()->$currentPath->$currentRequestMethod)){
            SwaggerJson::paths()->$currentPath->$currentRequestMethod = new SwaggerPathMethod($currentRequestMethod, $currentPath);
        }
        if(!\App\Utils\Env::get('UPDATE_SWAGGER_RESPONSES') && isset(SwaggerJson::paths()->$currentPath->$currentRequestMethod->responses->$responseCode)){
            return;
        }
        $operationId = SwaggerJson::paths()->$currentPath->$currentRequestMethod->operationId;
        if(QMArr::isNonAssociativeArray($responseArray)){
            $responseDefinitionName = str_replace("get", "", $operationId);
            $responseDefinitionName = str_replace("post", "", $responseDefinitionName);
            $responseDefinitionName = SwaggerDefinition::formatDefinitionName($responseDefinitionName)."Array";
        }else{
            $responseDefinitionName = SwaggerDefinition::formatDefinitionName($operationId."Response");
        }
        SwaggerJson::paths()->$currentPath->$currentRequestMethod->responses->$responseCode = new SwaggerResponse($responseDefinitionName, $responseArray);
        $currentResponse = SwaggerJson::paths()->$currentPath->$currentRequestMethod->responses->$responseCode;  // Easier debugging
        $responseDefinition = SwaggerJson::getStdClassDefinitions()->$responseDefinitionName;
        if(!isset(SwaggerJson::getStdClassDefinitions()->$responseDefinitionName)){
            le("Response definition not created!");
        }
        if($responseCode === 200 && !isset($currentResponse->schema)){
            le("Response schema not defined!");
        }
        SwaggerJson::setUpdated();
        SwaggerJson::updateSwaggerJsonFile();
    }
    /**
     * @param $filename
     * @param $data
     */
    public static function saveToResponseBodyExamplesFolder($filename, $data){
        if(!\App\Utils\Env::get('UPDATE_SWAGGER_JSON')){
            return;
        }
        FileHelper::writeJsonFile(SwaggerJson::SWAGGER_PATH . '/example-response-bodies/' .
            BaseClientIdProperty::fromRequest(false) . '/v' . APIHelper::getApiVersion(), $data, $filename);
    }
}
