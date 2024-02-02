<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\CodeGenerators\Swagger;
use stdClass;
class SwaggerSchema {
    public $type;
    public $items;
    /**
     * SwaggerSchema constructor.
     * @param $definitionName
     * @param $exampleModel
     */
    public function __construct($definitionName, $exampleModel){
        $this->setType($exampleModel);
        $this->setItems($definitionName, $exampleModel);
    }
    /**
     * @return string
     */
    public function getType(){
        return $this->type;
    }
    /**
     * @param $exampleModel
     */
    public function setType($exampleModel){
        $this->type = "object";
        if(is_array($exampleModel)){
            foreach($exampleModel as $key => $value){
                if(is_numeric($key)){
                    $this->type = "array";
                }
                return;
            }
        }
    }
    /**
     * @return stdClass
     */
    public function getItems(){
        return $this->items;
    }
    /**
     * @param $definitionName
     * @param null $exampleModel
     * @internal param stdClass $items
     */
    public function setItems($definitionName, $exampleModel){
        if(!is_array($exampleModel) && !is_object($exampleModel)){
            $this->items = new SwaggerDefinitionProperty($definitionName, $exampleModel);
            return;
        }
        if(!isset(SwaggerJson::getStdClassDefinitions()->$definitionName)){
            SwaggerDefinition::addOrUpdateSwaggerDefinition($exampleModel, $definitionName);
        }
        $definitionReference = SwaggerReference::getDefinitionReference($definitionName);
        if($this->type == "array"){
            $this->items = new SwaggerReference($definitionName);
        }else{
            unset($this->type);
            unset($this->items);
            $this->{'$ref'} = $definitionReference;
        }
    }
}
