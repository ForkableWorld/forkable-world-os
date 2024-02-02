<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\CodeGenerators\Swagger;
use App\DataSources\QMConnector;
use App\Exceptions\ExceptionHandler;
use App\Files\FileHelper;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Model\QMUnit;
use App\Slim\QMSlim;
use App\Storage\DB\Migrations;
use App\Storage\Memory;
use App\Types\QMStr;
use App\Utils\APIHelper;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Variables\QMVariableCategory;
use Exception;
class SwaggerJson {
    public const SWAGGER_PATH = 'vendor/quantimodo/docs/swagger';
    private static $instantiatedDefinitions;
    private static $cached;
    public $parameters;
    public $definitions;
    public $paths;
    public $tags;
    public $schemes;
    public $info;
    public function __construct(){
    }
    /**
     * @param null $swagger
     */
    public static function updateSwaggerJsonFile($swagger = null){
        if($swagger || (Env::get('UPDATE_SWAGGER_JSON') && Memory::get('SWAGGER_UPDATED'))){
            if(!$swagger){
                $swagger = self::getSwaggerJson();
            }
            try {
                FileHelper::writeJsonFile(self::SWAGGER_PATH, $swagger, 'swagger');
            } catch (Exception $e) {
                ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
            }
        }
    }
    /**
     * @return string
     */
    public static function getFormattedPath(): string{
        $currentPath = QMSlim::getInstance()->request()->getPath();
        $currentPath = str_replace("/api", "", $currentPath);
        $currentPath = str_replace("v1", "v3", $currentPath);
        $parts = explode("/search/", $currentPath);
        $currentPath = $parts[0];
        $parts = explode("/past", $currentPath);
        $currentPath = $parts[0];
        $parts = explode("/future", $currentPath);
        $currentPath = $parts[0];
        $parts = explode("/daily", $currentPath);
        $currentPath = $parts[0];
        //$parts = explode("/track", $currentPath);
        //$currentPath = $parts[0];
        $parts = explode("/snooze", $currentPath);
        $currentPath = $parts[0];
        $parts = explode("/skip", $currentPath);
        $currentPath = $parts[0];
        $parts = explode("/v2", $currentPath);
        $currentPath = $parts[0];
        if(strpos($currentPath, "/v3") === false && strpos($currentPath, "/v4") === false){
            $currentPath = "/v3".$currentPath;
        }
        return $currentPath;
    }
    public static function setUpdated(){
        Memory::set('SWAGGER_UPDATED', true);
    }
    /**
     * @return SwaggerJson|object
     */
    private static function getSwaggerJsonGlobal(){
        if(isset(self::$cached)){
            return self::$cached;
        }
        return null;
    }
    /**
     * @return SwaggerJson|object
     */
    public static function getSwaggerJson(){
        if(self::getSwaggerJsonGlobal()){
            return self::getSwaggerJsonGlobal();
        }
        /** @var SwaggerJson $swaggerJson */
        // GET requests are too slow to do all the time so just use local version
        //self::$cached = APIHelper::getRequest("https://raw.githubusercontent.com/QuantiModo/docs/master/swagger/swagger.json");
        // Make sure we get up to date version
        self::$cached = FileHelper::getDecodedJsonFile(FileHelper::absPath('public/docs/swagger.json'));
        if(!Env::get('UPDATE_SWAGGER_JSON')){
            return self::getSwaggerJsonGlobal();
        }
        if(!isset(self::getSwaggerJsonGlobal()->parameters->variableCategoryNameParam->enum)){
            foreach(QMVariableCategory::getVariableCategoryNames() as $variableCategoryName){
                self::getSwaggerJsonGlobal()->parameters->variableCategoryNameParam->enum[] = $variableCategoryName;
            }
            self::updateSwaggerJsonFile();
        }
        if(!isset(self::getSwaggerJsonGlobal()->parameters->connectorNamePathParam->enum)){
            foreach(QMConnector::getConnectors() as $connector){
                self::getSwaggerJsonGlobal()->parameters->connectorNamePathParam->enum[] = $connector->name;
            }
            self::updateSwaggerJsonFile();
        }
        if(!isset(self::getSwaggerJsonGlobal()->parameters->unitNameParam->enum)){
            foreach(QMUnit::getUnits() as $unit){
                self::getSwaggerJsonGlobal()->parameters->unitNameParam->enum[] = $unit->name;
            }
            self::updateSwaggerJsonFile();
        }
        return self::getSwaggerJsonGlobal();
    }
    /**
     * @param $filename
     * @param $data
     */
    public static function saveToPostBodyExamplesFolder($filename, $data){
        if(!AppMode::isTestingOrStaging()){
            FileHelper::writeJsonFile(self::SWAGGER_PATH.
                '/example-post-bodies/'.
                BaseClientIdProperty::fromRequest(false) .
                '/v'.
                APIHelper::getApiVersion(),
                $data,
                $filename);
        }
    }
    /**
     * @return mixed
     */
    public static function getProjectVersionNumberFromSwagger(): string{
        return "2017-10-01-13";
        //        try {
        //            return SwaggerJson::getSwaggerJson()->info->version;
        //        } catch (Exception $e){
        //            QMLog::debug($e->getMessage(), ['exception' => $e]);
        //            return null;
        //        }
    }
    /**
     * @param $value
     * @return string
     */
    public static function getAllowedSwaggerType($value): string{
        if(is_float($value) || gettype($value) === "double"){
            return "number";
        }
        if(!gettype($value) || is_null($value)){
            return "string";
        }
        return gettype($value);
    }
    /**
     * @return object
     */
    public static function getStdClassDefinitions(): object {
        $definitions = self::getSwaggerJson()->definitions;
        return $definitions;
    }
    /**
     * @param $modelName
     * @return SwaggerDefinition
     */
    public static function getDefinition(string $modelName): SwaggerDefinition{
        $modelName = str_replace("#/definitions/", "", $modelName);
        $definitions = self::getDefinitions();
        foreach($definitions as $definitionName => $definition){
            if(strtolower($definitionName) === strtolower($modelName)){
                return $definition;
            }
        }
        foreach($definitions as $definitionName => $definition){
            if(stripos($modelName, $definitionName) !== false){
                return $definition;
            }
        }
        le("Could not find swagger definition for $modelName");
    }
    /**
     * @return object
     */
    public static function paths(): object {
        return self::getSwaggerJson()->paths;
    }
    /**
     * @return SwaggerParameter[]
     */
    public static function parameters(): array{
        return self::getSwaggerJson()->parameters;
    }
    /**
     * @param $pathToGet
     * @param string $method
     * @return string
     */
    public static function getPathMethodDescription(string $pathToGet = null, $method = null): ?string{
        $data = self::getPathMethodData($pathToGet, $method);
        if($data){
            return $data->description;
        }
        return null;
    }
    public static function getDescription(string $definitionName): ?string{
        $str = self::getModelDescription($definitionName);
        if(!$str){
            $str = self::getPathMethodSummary($definitionName);
        }
        if(!$str){
            $str = self::getPathMethodDescription($definitionName);
        }
        if(!$str){
            $parameters = self::get()->parameters;
            foreach($parameters as $parameter){
                if($parameter->name === $definitionName){
                    if(!isset($parameter->description) || !isset($parameter->type)){continue;}
                    $thisDescription = $parameter->description." (type: ".$parameter->type.")";
                    if(!$str || strlen($thisDescription) > strlen($str)){
                        if(stripos($thisDescription, 'What do you expect') === false){
                            $str = $thisDescription;
                        }
                    }
                }
            }
        }
        if(!$str){
            $definitions = self::getDefinitions();
            foreach($definitions as $currentDefinitionName =>  $definition){
                foreach($definition->properties as $name => $property){
                    if($name === $definitionName){
                        if(!isset($property->description)){continue;}
                        $thisDescription = $property->description." (type: ".$property->type.")";
                        if(!$str || strlen($thisDescription) > strlen($str)){
                            if(stripos($thisDescription, 'What do you expect') === false){
                                $str = $thisDescription;
                            }
                        }
                    }
                }
            }
        }
        return $str;
    }
    /**
     * @param string $definitionName
     * @return string
     */
    public static function getModelDescription(string $definitionName): ?string {
        $definitions = self::getStdClassDefinitions();
        $definition = $definitions->$definitionName ?? null;
        if(!$definition){return null;}
        return $definition->description ?? null;
    }
    /**
     * @param $pathToGet
     * @param string $method
     * @return string
     */
    public static function getPathMethodSummary(string $pathToGet = null, string $method = null): ?string {
        if(!$method){
            $method = qm_request()->getMethod();
        }
        $data = self::getPathMethodData($pathToGet, $method);
        if($data){
            return $data->summary;
        }
        //throw new \LogicException("Could not find $method $pathToGet");
        return null;
    }
    /**
     * @param string|null $pathToGet
     * @param string|null $method
     * @return SwaggerPathMethod
     */
    public static function getPathMethodData(string $pathToGet = null, string $method = null): ?object{
        if(!$method){$method = qm_request()->getMethod();}
        if(!$method){$method = 'get';}
        if(!$pathToGet){$pathToGet = url()->current();}
        $pathToGet = QMStr::removeApiVersionFromPath($pathToGet);
        //$pathToGet = StringHelper::camelize($pathToGet);
        //$pathToGet = StringHelper::pluralize($pathToGet);
        $paths = self::paths();
        $method = strtolower($method);
        foreach($paths as $currentPath => $data){
            $currentPath = QMStr::removeApiVersionFromPath($currentPath);
            if($currentPath === $pathToGet && isset($data->$method)){
                return $data->$method;
            }
        }
        foreach($paths as $currentPath => $data){
            $currentPath = QMStr::removeApiVersionFromPath($currentPath);
            if(stripos($currentPath, $pathToGet) && isset($data->$method)){
                return $data->$method;
            }
        }
        //throw new \LogicException("Could not find $method $pathToGet");
        return null;
    }
    /**
     * @return SwaggerJson
     */
    public static function get(): ?object{
        return self::getSwaggerJson();
    }
    public static function addCommentsToDB(){
        $definitions = self::getStdClassDefinitions();
        foreach($definitions as $definitionName => $stdClass){
            $definition  = self::getDefinition($definitionName);
            $tableName = QMStr::snakize("$definitionName");
            $modelName = ucfirst($definitionName);
            $class = null;
            if(class_exists("\App\Models\\".$modelName)){$class = "\App\Models\\".$modelName;}
            if(class_exists("\App\Slim\Model\\$modelName\\".$modelName)){$class = "\App\Slim\Model\\$modelName\\".$modelName;}
            if(class_exists("\App\Slim\Model\\".$modelName)){$class = "\App\Slim\Model\\".$modelName;}
            if(!$class){
                \App\Logging\ConsoleLog::info("No class for $definitionName");
                continue;
            }
            $tableName = $class::TABLE;
            if(isset($definition->description)){
                $description = $definition->description;
            } else {
                $description = self::getPathMethodDescription("/api/v3/".lcfirst($modelName));
            }
            Migrations::makeMigration("alter table $tableName
                comment '$description';", "alter table $tableName
                comment '$description';");
            foreach($definition->getProperties() as $property){
                $column = QMStr::snakize($property->name);
                if($property->description === 'What do you expect?'){continue;}
                if(empty($property->description)){continue;}
                Migrations::commentMigration($tableName, $column, $property->description);
            }
        }
    }
    /**
     * @return SwaggerDefinition[]
     */
    public static function getDefinitions(): array {
        if(isset(self::$instantiatedDefinitions)){return self::$instantiatedDefinitions;}
        $definitions = self::getStdClassDefinitions();
        foreach($definitions as $definitionName => $definition){
            $model = new SwaggerDefinition();
            $model->populateFieldsByArrayOrObject($definition);
            self::$instantiatedDefinitions[$definitionName] = $model;
        }
        return self::$instantiatedDefinitions;
    }
}
