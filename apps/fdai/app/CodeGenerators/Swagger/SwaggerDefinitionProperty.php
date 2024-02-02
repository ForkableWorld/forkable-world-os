<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\CodeGenerators\Swagger;
use LogicException;
use App\Utils\SecretHelper;
use App\InputFields\CheckBoxInputField;
use App\InputFields\InputField;
use App\InputFields\IntegerInputField;
use App\InputFields\NumberInputField;
use App\InputFields\SelectOptionInputField;
use App\InputFields\StringInputField;
use App\Logging\QMLog;
use App\Slim\Model\StaticModel;
use App\Types\QMStr;
use App\Slim\Model\QMUnit;
use stdClass;

class SwaggerDefinitionProperty extends StaticModel{
    public const FORMAT_double = 'double';
    public const TYPE_array = 'array';
    public const TYPE_boolean = 'boolean';
    public const TYPE_integer = 'integer';
    public const TYPE_number = 'number';
    public const TYPE_string = 'string';
    public const TYPE_object = 'object';
    public $description;
    public $format;
    public $items;
    public $name;
    public $type;
    public $enum;
    /**
     * @var array
     */
    public static $mappings = [
        self::TYPE_string => ['varchar', 'text', 'string', 'char', 'enum', 'tinytext', 'mediumtext', 'longtext', 'datetime', 'year', 'date', 'time', 'timestamp'],
        self::TYPE_integer => ['bigint', 'int', 'integer', 'tinyint', 'smallint', 'mediumint'],
        self::TYPE_number => ['float', 'decimal', 'numeric', 'dec', 'fixed', 'double', 'real', 'double precision'],
        self::TYPE_boolean => ['longblob', 'blob', 'bit', 'bool', 'boolean'],
    ];
    /**
     * SwaggerDefinitionProperty constructor.
     * @param null $name
     * @param null $exampleValue
     */
    public function __construct($name = null, $exampleValue = null){
        if(!$name && !$exampleValue){
            return;
        }
        if(!$exampleValue){
            return;
        }
        if(!\App\Utils\Env::get('CREATE_SWAGGER_PROPERTIES_FOR_EMPTY_VALUES') && empty($exampleValue) && $exampleValue !== 0 && $exampleValue !== false){
            le("We cannot create property for empty non-zero and non-false values");
        }
        $this->type = SwaggerJson::getAllowedSwaggerType($exampleValue);
        if(is_float($exampleValue)){
            $this->type = "number";
            $this->format = "double";
        }
        if($this->type === "string" && (strpos($name, "At") !== false || strpos($name, "Time") !== false)){
            $this->format = "date-time";
        }
        if(is_array($exampleValue) || is_object($exampleValue)){
            $this->setReference($name, $exampleValue);
        }else{
            if(is_bool($exampleValue)){
                $exampleValue = $exampleValue ? 'true' : 'false';
            }
            if(empty($exampleValue) && $exampleValue !== 0){
                QMLog::error("example value is empty");
            }
            $this->description = "Example: ". SecretHelper::obfuscateString($exampleValue, $name);
        }
        if(!$this->format){
            unset($this->format);
        }
    }
    /**
     * @param $name
     * @param null $exampleValue
     */
    public function setReference(string $name, $exampleValue = null){
        $this->{'$ref'} = SwaggerReference::getDefinitionReference($name);
        if(!isset(SwaggerJson::getStdClassDefinitions()->$name)){
            SwaggerDefinition::addOrUpdateSwaggerDefinition($exampleValue, $name);
            QMLog::error("No definition reference!");
        }
        unset($this->type);
        unset($this->description);
    }
    /**
     * @return SwaggerDefinition
     */
    public function getItemDefinition(){
        $ref = $this->items->{'$ref'};
        $definition = SwaggerDefinition::getByName($ref);
        return $definition;
    }
    /**
     * @return CheckBoxInputField|InputField|IntegerInputField|NumberInputField|SelectOptionInputField|StringInputField
     */
    public function getInputField(){
        $type = $this->getType();
        if($this->getEnum()){
            $field = new SelectOptionInputField();
            $field->setOptions($this->getEnum());
        }else if($type === self::TYPE_number){
            $field = new NumberInputField();
        }else if($type === self::TYPE_integer){
            $field = new IntegerInputField();
        }else if($type === self::TYPE_boolean){
            $field = new CheckBoxInputField();
        }else if($type === self::TYPE_string){
            $field = new StringInputField();
        }else{
            $field = new InputField();
        }
        $field->setDisplayName($this->getTitleAttribute());
        $field->setKey($this->getNameAttribute());
        $description = $this->getDescription();
        $cleanDescription = trim(QMStr::after(": ", $description, $description));
        $unitName = QMStr::after("Unit: ", $description);
        if($unitName){
            $unit = QMUnit::getUnitByFullName($unitName);
            if($unit){
                $field->setUnit($unit);
            }
        }
        $field->setHelpText($cleanDescription);
        $field->setHint($cleanDescription);
        return $field;
    }
    /**
     * @return string
     */
    public function getType(): string{
        return $this->type;
    }
    /**
     * @return string
     */
    public function getFormat(): string{
        return $this->format;
    }
    /**
     * @param mixed $name
     */
    public function setName(string $name){
        $this->name = $name;
    }
    /**
     * @return string
     */
    public function getTitleAttribute(): string{
        $name = QMStr::titleCaseSlow($this->name);
        return $name;
    }
    /**
     * @return string
     */
    public function getNameAttribute(): string {
        return $this->name;
    }
    /**
     * @return string
     */
    public function getDescription(): string{
        return $this->description;
    }
    /**
     * @param string $description
     */
    public function setDescription(string $description){
        $this->description = $description;
    }
    /**
     * @return string[]
     */
    public function getEnum(){
        return $this->enum;
    }
    /**
     * @param string $type
     */
    public function setType(string $type){
        if($type === "int"){
            $type = "integer";
        }
        $this->type = $type;
    }
    /**
     * @param string $itemsDefinitionName
     */
    public function setArrayItemsReference(string $itemsDefinitionName){
        $this->type = "array";
        $this->items = new stdClass();
        $this->items->{'$ref'} = '#/definitions/'.$itemsDefinitionName;
        unset($this->name);
        unset($this->format);
        unset($this->enum);
    }
    /**
     * @param string $itemsType
     */
    public function setArrayItemsType(string $itemsType){
        $this->type = "array";
        $this->items = new stdClass();
        $this->items->type = $itemsType;
        unset($this->name);
        unset($this->format);
        unset($this->enum);
    }
    public static function dbTypeToSwaggerType(string $dbType): string {
        foreach (self::$mappings as $swaggerType => $database) {
            if (in_array($dbType, $database)) {
                return $swaggerType;
            }
        }
        le("Please define type for $dbType");
    }
}
