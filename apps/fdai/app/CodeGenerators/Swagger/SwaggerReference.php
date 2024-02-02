<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\CodeGenerators\Swagger;
use LogicException;
use App\Logging\QMLog;
class SwaggerReference {
    /**
     * SwaggerReference constructor.
     * @param $definitionName
     */
    public function __construct($definitionName){
        $this->{'$ref'} = self::getDefinitionReference($definitionName);
    }
    /**
     * @param $definitionName
     * @return string
     */
    public static function getDefinitionReference($definitionName){
        $definitionName = SwaggerDefinition::formatDefinitionName($definitionName);
        if(!isset(SwaggerJson::getStdClassDefinitions()->$definitionName)){
            QMLog::error("Reference to non-existent definition $definitionName");
        }
        if($definitionName === "Item"){
            le("definitionName should not be Item!");
        }
        return "#/definitions/$definitionName";
    }
}
