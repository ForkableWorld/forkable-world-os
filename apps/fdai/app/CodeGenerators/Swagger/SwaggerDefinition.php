<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\CodeGenerators\Swagger;
use App\Exceptions\ExceptionHandler;
use App\Logging\QMLog;
use App\Slim\Model\StaticModel;
use App\Types\ObjectHelper;
use App\Types\QMStr;
use App\Utils\Env;
use Exception;
use stdClass;
class SwaggerDefinition extends StaticModel {
    public $required;
    public $properties;
    public $description;
    public $type;
    public $items;
    /**
     * SwaggerDefinition constructor.
     * @param null $definitionName
     * @param null $exampleModel
     * @param array $legacyProperties
     */
    public function __construct($definitionName = null, $exampleModel = null, $legacyProperties = []){
        $this->properties = new stdClass();
        if(!$definitionName && !$exampleModel){
            return;
        }
        if(!$exampleModel){
            return;
        }
        try {
            $exampleModel = ObjectHelper::replaceLegacyPropertiesInObject($exampleModel, $legacyProperties);
        } catch (Exception $e) {
            ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
        }
        //$exampleModel = self::getObjectFromExampleArray($exampleModel);
        $definitionName = self::formatDefinitionName($definitionName, null, $exampleModel);
        $this->setRequired($definitionName);
        $this->setProperties($definitionName, $exampleModel);
        $this->setType($exampleModel);
        $this->setItems($definitionName, $exampleModel);
        SwaggerJson::getSwaggerJson()->definitions->$definitionName = $this;
        SwaggerJson::setUpdated();
    }
    /**
     * @param $possibleArray
     * @return mixed
     */
    public static function getObjectFromExampleArray($possibleArray): array{
        if(is_array($possibleArray)){
            foreach($possibleArray as $key => $value){
                if(is_numeric($key)){
                    return $value;
                }
            }
        }
        return $possibleArray;
    }
    /**
     * @param $key
     * @param $responseName
     * @return mixed
     */
    public static function convertDataKeyToModelNameIfNecessary($key, $responseName){
        if($key === "data"){
            $key = self::convertResponseNameToModelName($responseName);
        }
        return $key;
    }
    /**
     * @param $exampleModel
     * @param $definitionName
     * @param array $legacyProperties
     */
    public static function addOrUpdateSwaggerDefinition($exampleModel, $definitionName, $legacyProperties = []){
        if(!Env::get('UPDATE_SWAGGER_JSON')){
            return;
        }
        if(empty($exampleModel)){
            return;
        }
        $definitionName = self::formatDefinitionName(str_replace("Array", "", $definitionName));
        foreach($exampleModel as $key => $value){
            if(is_numeric($key)){
                $definitionNameWithArray = $definitionName."Array";
                SwaggerJson::getStdClassDefinitions()->$definitionName = new SwaggerDefinition($definitionName, $value, $legacyProperties);
                SwaggerJson::getStdClassDefinitions()->$definitionNameWithArray = new SwaggerSchema($definitionName, $exampleModel);
                return;
            }
        }
        SwaggerJson::getStdClassDefinitions()->$definitionName = new SwaggerDefinition($definitionName, $exampleModel, $legacyProperties);
    }
    /**
     * @param $dataResponse
     * @param $newDataResponseDefinitionName
     * @param $responseDefinitionName
     */
    public static function addNewDataResponseDefinition($dataResponse, $newDataResponseDefinitionName, $responseDefinitionName){
	    $definitions = SwaggerJson::getStdClassDefinitions();
	    $definitions->$responseDefinitionName = self::deepClone($definitions->CommonResponse);
        if(is_array($dataResponse) && isset($dataResponse[0])){
            $newDataResponseDefinitionName = self::convertResponseNameToModelName($newDataResponseDefinitionName)."Array";
        }
        self::addOrUpdateSwaggerDefinition($dataResponse, $newDataResponseDefinitionName);
        $definitions->$responseDefinitionName->properties->data->{'$ref'} = SwaggerReference::getDefinitionReference($newDataResponseDefinitionName);
    }
    /**
     * @param $originalVariable
     * @return mixed
     */
    private static function deepClone($originalVariable){
        $newVariable = clone $originalVariable;
        foreach($newVariable as $key => $val){
            if(is_object($val) || is_array($val)){
                /** @noinspection UnserializeExploitsInspection */
                $newVariable->{$key} = unserialize(serialize($val));
            }
        }
        return $newVariable;
    }
    /**
     * @param $responseName
     * @return string
     */
    public static function convertResponseNameToModelName($responseName){
        $newDataResponseDefinitionName = str_replace("Get", "", $responseName);
        return str_replace("DataResponse", "", $newDataResponseDefinitionName);
    }
    /**
     * @param $className
     * @return string
     */
    private static function getDefinitionNameFromClass($className){
        $definitionName = explode('\\', $className);
        return array_pop($definitionName);
    }
    /**
     * @param $rawName
     * @param string $prefix
     * @param null $exampleModel
     * @return string|string[]
     */
    public static function formatDefinitionName($rawName, $prefix = '', $exampleModel = null){
        if($exampleModel && is_array($exampleModel)){
            foreach($exampleModel as $key => $value){
                if(is_numeric($key)){
                    $rawName .= "Array";
                }
                break;
            }
        }
        if(is_numeric($rawName) && str_contains($prefix, "Array")){
            $rawName = str_replace("Array", "", $prefix);
            $prefix = '';
        }
        $definitionName = self::getDefinitionNameFromClass($rawName);
        $definitionName = ucfirst($definitionName);
        $definitionName = QMStr::singularize($definitionName);
        $definitionName = str_replace("CauseVariable", "UserVariable", $definitionName);
        $definitionName = str_replace("EffectVariable", "UserVariable", $definitionName);
        $definitionName = str_replace("CauseVariable", "", $definitionName);
        $definitionName = str_replace("Effect", "", $definitionName);
        $definitionName = str_replace("Available", "", $definitionName);
        $definitionName = str_replace("Default", "", $definitionName);
        $definitionName = str_replace("UserVariableRelationship", "UserVariableRelationship", $definitionName);
        $definitionName = str_replace("CorrelationDataSource", "DataSource", $definitionName);
        $definitionName = str_replace("StudyPair", "Pair", $definitionName);
        $definitionName = str_replace("StudyChart", "Chart", $definitionName);
        $definitionName = str_replace("ChartChart", "Chart", $definitionName);
        $definitionName = str_replace("ArrayArray", "Array", $definitionName);
        if(str_contains($definitionName, "Image")){
            return "Image";
        }
        if(str_contains($definitionName, "Button")){
            return "Button";
        }
        if(str_contains($definitionName, "ProcessedDailyMeasurement")){
            return "ProcessedDailyMeasurement";
        }
        if(str_contains($definitionName, "DataSource")){
            return "DataSource";
        }
        if(str_contains($definitionName, "UserTaggedVariable")){
            return "UserVariable";
        }
        if(str_contains($definitionName, "UserTagVariable")){
            return "UserVariable";
        }
        if(str_contains($definitionName, "TagVariable")){
            return "Variable";
        }
        if(str_contains($definitionName, "TaggedVariable")){
            return "Variable";
        }
        if(!\App\Utils\Env::get('USE_SWAGGER_DEFINITION_PREFIXES')){
            return $definitionName;
        }
        if(isset(SwaggerJson::getStdClassDefinitions()->$definitionName)){
            return $definitionName;
        }
        return $prefix.$definitionName;
    }
    /**
     * @param $definitionName
     */
    private static function removeMissingRequiredProperties($definitionName){
        $newRequiredArray = [];
        if(!isset(SwaggerJson::getStdClassDefinitions()->$definitionName->required)){
            return;
        }
        foreach(SwaggerJson::getStdClassDefinitions()->$definitionName->required as $requiredPropertyName){
            if(isset(SwaggerJson::getStdClassDefinitions()->$definitionName->properties->$requiredPropertyName)){
                $newRequiredArray[] = $requiredPropertyName;
            }
        }
        SwaggerJson::getStdClassDefinitions()->$definitionName->required = $newRequiredArray;
        if(!count($newRequiredArray)){
            unset(SwaggerJson::getStdClassDefinitions()->$definitionName->required);
        }
    }
    /**
     * @param $definitionName
     * @return array
     */
    public function getRequired($definitionName): array{
        if(isset(SwaggerJson::getStdClassDefinitions()->$definitionName) && 
           isset(SwaggerJson::getStdClassDefinitions()->$definitionName->required)){
            return SwaggerJson::getStdClassDefinitions()->$definitionName->required;
        }
		return [];
    }
    /**
     * @param $definitionName
     */
    public function setRequired($definitionName){
        $this->required = $this->getRequired($definitionName);
        if(!count($this->required)){
            unset($this->required);
        }
    }
    /**
     * @param $definitionName
     * @return stdClass
     */
    public function getPropertiesFromSwaggerJson($definitionName): stdClass{
        if(isset(SwaggerJson::getStdClassDefinitions()->$definitionName) && isset(SwaggerJson::getStdClassDefinitions()->$definitionName->properties)){
            return SwaggerJson::getStdClassDefinitions()->$definitionName->properties;
        }
        return new stdClass();
    }
    /**
     * @param $definitionName
     * @param $exampleModel
     * @internal param stdClass $properties
     */
    public function setProperties($definitionName, $exampleModel){
        if(is_numeric($definitionName)){
            le("Definition name cannot be numeric!");
        }
        if(isset($exampleModel->items) && isset($exampleModel->type)){
            if(isset($exampleModel->items->{'$ref'}) && SwaggerReference::getDefinitionReference($definitionName) === $exampleModel->items->{'$ref'}){
                le("Definition should not reference itself!");
            }
            $this->type = $exampleModel->type;
            $this->items = $exampleModel->items;
            unset($this->properties);
            return; // No properties for references!
        }
        $this->properties = $this->getPropertiesFromSwaggerJson($definitionName);
        if(!is_array($exampleModel) && !is_object($exampleModel)){
            $this->properties->$definitionName = new SwaggerDefinitionProperty($definitionName, $exampleModel);
            return;
            //throw new \LogicException("Example model must be an object or array!");
        }
        foreach($exampleModel as $key => $value){
            if($key === "token"){
                continue;
            }
            $key = QMStr::toCamelCase($key);
            if(!isset(SwaggerJson::getStdClassDefinitions()->$definitionName->properties->$key)){
                if(Env::get('CREATE_SWAGGER_PROPERTIES_FOR_EMPTY_VALUES') || !empty($value) || $value === 0 || $value === false){
                    if($definitionName === "Datum"){
                        le("definitionName should not be Datum");
                    }
                    if(is_numeric($key)){
                        QMLog::debug("Key should not be numeric!");
                        $this->type = "array";
                        $itemName = str_replace("Array", "", $definitionName);
                        $this->items = new SwaggerSchema(self::formatDefinitionName($itemName), $value);
                        break;
                    }
                    SwaggerJson::setUpdated();
                    if(is_array($value) && !isset($value[0])){
                        $value = ObjectHelper::convertToObject($value);
                    }
                    if(is_object($value) || is_array($value)){
                        $this->properties->$key = new SwaggerSchema(self::formatDefinitionName($key, $definitionName), $value);
                    }else{
                        $this->properties->$key = new SwaggerDefinitionProperty($key, $value);
                    }
                }
            }else if(isset(SwaggerJson::getStdClassDefinitions()->$definitionName->properties->$key->description) && (SwaggerJson::getStdClassDefinitions()->$definitionName->properties->$key->description == "Example: ") && !is_object($value) && !is_array($value) && $value){
                $this->properties->$key = new SwaggerDefinitionProperty($key, $value);
            }
        }
        if(!count(get_object_vars($this->properties))){
            unset($this->properties);
        }
        self::removeMissingRequiredProperties($definitionName);
        if(!isset($this->properties)){
            unset($this->properties);
        }
    }
    /**
     * @param $definitionName
     * @return mixed
     */
    public function getType(string $definitionName){
	    $stdClassDefinitions = SwaggerJson::getStdClassDefinitions();
	    return $stdClassDefinitions->$definitionName->type ?? null;
    }
    /**
     * @param $exampleModel
     */
    public function setType($exampleModel){
        if(is_array($exampleModel) && isset($exampleModel[0])){
            $this->type = "array";
            $this->items = new StdClass();
        }
        if(!$this->type){
            unset($this->type);
        }
    }
    /**
     * @param $definitionName
     * @return mixed
     */
    public function getItems($definitionName){
	    $defs = SwaggerJson::getStdClassDefinitions();
	    return $defs->$definitionName->items ?? null;
    }
    /**
     * @param $definitionName
     * @param $exampleModel
     */
    public function setItems($definitionName, $exampleModel){
        if(is_array($exampleModel) && isset($exampleModel[0])){
            $this->items = new StdClass();
            $this->items->type = SwaggerJson::getAllowedSwaggerType($exampleModel[0]);
            if(str_contains($definitionName, "Array")){
                $itemDefinitionName = str_replace("Array", "", $definitionName);
                $itemDefinitionName = self::formatDefinitionName($itemDefinitionName);
                $this->items = new SwaggerReference($itemDefinitionName);
                if($itemDefinitionName === $definitionName){
                    le("Item name should not be the same as definition name!");
                }
            }
        }
        if(!isset($this->items)){
            unset($this->items);
        }
    }
    /**
     * @return SwaggerDefinitionProperty[]
     */
    public function getProperties(){
        $properties = $this->properties;
        $models = [];
        foreach($properties as $propertyName => $property){
            if($property instanceof SwaggerDefinitionProperty){
                $models = $properties;
                break;
            }
            $model = new SwaggerDefinitionProperty();
            $model->populateFieldsByArrayOrObject($property);
            $model->setName($propertyName);
            $models[] = $model;
        }
        return $this->properties = $models;
    }
    /**
     * @param string $definitionName
     * @return SwaggerDefinition
     */
    public static function getByName(string $definitionName): SwaggerDefinition{
        return SwaggerJson::getDefinition($definitionName);
    }
    /**
     * @return string
     */
    public function getDescription(): string {
        if(!$this->description){
	        $type = $this->type;
        QMLog::error("Please set description for $type swagger definition for this:".QMLog::print_r($this, true));
            $propertyNames = [];
            foreach($this->properties as $propertyName => $obj){$propertyNames[] = $propertyName;}
            $def = "Object containing ".implode(', ', $propertyNames);
            return $def;
        }
        return $this->description;
    }
	public function getSubtitleAttribute(): string{
		return $this->getDescription();
	}
}
