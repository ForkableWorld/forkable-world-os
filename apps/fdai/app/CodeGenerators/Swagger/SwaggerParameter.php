<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\CodeGenerators\Swagger;
use App\Slim\QMSlim;
use App\Utils\SecretHelper;
use App\Logging\QMLog;
use App\Types\QMStr;
use stdClass;
class SwaggerParameter {
    public $name;
    public $required;
    public $description;
    public $in;
    public $type;
    /**
     * SwaggerParameter constructor.
     * @param $name
     * @param $exampleValue
     */
    public function __construct($name, $exampleValue){
        $this->name = $name;
        $this->in = "query";
        $this->setType($exampleValue);
        $this->required = false;
        $this->setDescription($name, $exampleValue);
    }
    /**
     * @param $params
     */
    public static function addNewSwaggerParameters($params){
        $params = QMStr::properlyFormatRequestParams($params);
        $path = SwaggerJson::getFormattedPath();
        $currentRequestMethod = strtolower(QMSlim::getInstance()->request()->getMethod());
        if($currentRequestMethod === "post"){
            return;
        }
        if(strpos($path, "trackingReminderNotifications/track") !== false){
            return;
        }
        if(!isset(SwaggerJson::paths()->$path)){
            SwaggerJson::paths()->$path = new StdClass();
        }
        if(!isset(SwaggerJson::paths()->$path->$currentRequestMethod)){
            SwaggerJson::paths()->$path->$currentRequestMethod = new SwaggerPathMethod($currentRequestMethod, $path);
        }
        self::convertPathParametersToGlobalParams();
        self::createGlobalParametersFromRequestParams($params);
        self::addParamReferencesToCurrentPathMethod($params, $currentRequestMethod, $path);
        SwaggerJson::updateSwaggerJsonFile();
    }
    /**
     * @param $paramName
     * @return array|string
     */
    public static function convertRequestParamToSwaggerParamName($paramName){
        return QMStr::toCamelCase($paramName.'Param');
    }
    /**
     * @param $swaggerParamName
     * @return string
     */
    public static function convertSwaggerParamNameToRef($swaggerParamName){
        return "#/parameters/".$swaggerParamName;
    }
    /**
     * @param $paramName
     * @return bool
     */
    private static function inExcludedParameters($paramName){
        $excluded = [
            'body',
            'id',
            'name',
            'token'
        ];
        return in_array($paramName, $excluded, true);
    }
    /**
     * @return void
     */
    private static function convertPathParametersToGlobalParams(){
        foreach(SwaggerJson::paths() as $pathName => $pathValue){
            foreach($pathValue as $methodName => $methodValue){
                if(!isset($methodValue->parameters)){
                    continue;
                }
                foreach($methodValue->parameters as $pathParameterIndex => $pathParameterValue){
                    if(!isset($pathParameterValue->name) || self::inExcludedParameters($pathParameterValue->name)){
                        continue;
                    }
                    $swaggerParamName = self::convertRequestParamToSwaggerParamName($pathParameterValue->name);
                    if(!isset(SwaggerJson::parameters()->$swaggerParamName)){
                        SwaggerJson::parameters()->$swaggerParamName = $pathParameterValue;
                        SwaggerJson::paths()->$pathName->$methodName->parameters[$pathParameterIndex] = new SwaggerReference($swaggerParamName);
                    }
                }
            }
        }
    }
    /**
     * @param array $params
     */
    private static function createGlobalParametersFromRequestParams($params){
        if(!is_array($params)){
            return;
        }
        foreach($params as $key => $value){
            if(QMStr::isJson($key) || is_array($value) || self::inExcludedParameters($key)){
                self::addNewSwaggerParameters(json_decode($key));
                continue;
            }
            $swaggerParamName = self::convertRequestParamToSwaggerParamName($key);
            if(!isset(SwaggerJson::parameters()->$swaggerParamName)){
                SwaggerJson::parameters()->$swaggerParamName = new SwaggerParameter($key, $value);
            }
        }
    }
    /**
     * @param array $params
     * @param $currentRequestMethod
     * @param $path
     */
    private static function addParamReferencesToCurrentPathMethod($params, $currentRequestMethod, $path){
        if(!is_array($params)){
            return;
        }
        foreach($params as $paramName => $paramValue){
            $parameterAlreadyPresent = false;
            $paramRef = self::convertSwaggerParamNameToRef(self::convertRequestParamToSwaggerParamName($paramName));
            if(!isset(SwaggerJson::paths()->$path->$currentRequestMethod->parameters)){
                SwaggerJson::paths()->$path->$currentRequestMethod->parameters = [];
            }
            foreach(SwaggerJson::paths()->$path->$currentRequestMethod->parameters as $existingPathParameter){
                if(isset($existingPathParameter->{'$ref'}) && $existingPathParameter->{'$ref'} === $paramRef){
                    $parameterAlreadyPresent = true;
                }
            }
            if(!$parameterAlreadyPresent && !self::inExcludedParameters($paramName)){
                SwaggerJson::paths()->$path->$currentRequestMethod->parameters[] = new SwaggerReference($paramName);
            }
        }
    }
    /**
     * @return string
     */
    public function getType(){
        return $this->type;
    }
    /**
     * @param $exampleValue
     */
    public function setType($exampleValue){
        $this->type = SwaggerJson::getAllowedSwaggerType($exampleValue);
        if($exampleValue === true || $exampleValue === "false" || $exampleValue === false || $exampleValue === "true"){
            $this->type = "boolean";
        }
    }
    /**
     * @return string
     */
    public function getDescription(){
        return $this->description;
    }
    /**
     * @param $name
     * @param $exampleValue
     */
    public function setDescription($name, $exampleValue){
        if(!is_array($exampleValue)){
            if(empty($exampleValue)){
                QMLog::debug("example value is empty");
            }
            $this->description = "Example: ". SecretHelper::obfuscateString($exampleValue, $name);
        }
    }
}
