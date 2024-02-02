<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpDocMissingThrowsInspection */
namespace App\Properties;
use App\CodeGenerators\TVarDumper;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Str;
use App\Fields\Field;
class BasePropertyGenerator extends BaseProperty {
	public static function generatePropertyTraits(){
		try {
			$paths = FileFinder::listFolders('app/Properties');
		} catch (QMFileNotFoundException $e) {le($e);}
		foreach($paths as $modelShortClass){
			if($modelShortClass === "Base"){
				continue;
			}
			/** @var BaseModel $modelClass */
			$modelClass = "App\\Models\\" . $modelShortClass;
			$shortTrait = $modelShortClass . "Property";
			$traitClass = "App\\Traits\\PropertyTraits\\" . $shortTrait;
			BasePropertyGenerator::generateTraitStatic($traitClass, $modelClass);
			$path = 'app/Properties/' . $modelShortClass;
			$oldShort = "Has$modelShortClass";
			FileHelper::replaceStringInAllFilesInFolder($path, "App\\Traits\\HasModel\\$oldShort", $traitClass);
			FileHelper::replaceStringInAllFilesInFolder($path, $oldShort, $shortTrait);
		}
	}
	/**
	 * @param bool $overwrite
	 * @return void
	 */
	public function generateBaseProperty(bool $overwrite = false): string{
		$baseShortClass = $this->getBaseShortClassName();
		$path = 'app/Properties/Base/' . $baseShortClass . '.php';
		if(!$overwrite && FileHelper::fileExists($path)){
			QMLog::info("Skipping $path because it already exists");
			return $path;
		}
		try {
			$this->populate_from_model_schema();
		} catch (\Throwable $e) {
			$this->populate_from_model_schema();
		}
		$parent = $this->getParentModelOrFirstExample();
		$this->parentClass = $parent->getFullClassName();
		$parent = $this->getParentModelOrFirstExample();
		//$baseClass = $this->getBaseClass();
		//if(class_exists($baseClass)){return $baseClass;}
		/** @noinspection PhpUnhandledExceptionInspection */
		$template = FileHelper::getContents($this->getPropertiesPath() . '/base-property.stub');
		$template = str_replace('{{short_class}}', $this->getBaseShortClassName(), $template);
		$template = str_replace('{{type_trait}}', $this->getTypeTrait(), $template);
		$properties = '';
		$vars = get_object_vars($this);
		ksort($vars); // Alphabetize
		foreach($vars as $key => $value){
			if(stripos($key, '_') === 0){
				continue;
			}
			if(in_array($key, [
				'table',
				'x',
				'property',
				'parentClass',
				'tableName',
				'parentModel',
				'migrationText',
			])){
				continue;
			}
			$properties = $this->dump($key, $value, $properties, $parent, false);
		}
		$template = str_replace('{{properties}}', $properties, $template);
		FileHelper::writeByFilePath($path, $template);
		return $path;
	}
	/**
	 * @param bool $overwrite
	 * @return mixed
	 */
	public function generateModelProperty(bool $overwrite = false): ?string{
		if(!$overwrite){
			$path = $this->getOutputPath();
			if(FileHelper::fileExists($path)){
				QMLog::info("Skipping $path because it already exists");
				return null;
			}
		}
		$this->populate_from_model_schema();
		$parent = $this->getParentModelOrFirstExample();
		$this->parentClass = $parent->getFullClassName();
		$template = $this->getTemplate();
		$hasTrait = $this->generateHasTrait($overwrite);
		$fullTrait = $this->generatePropertyTrait($overwrite);
		$baseShortClass = $this->getBaseShortClassName();
		$shortTrait = QMStr::toShortClassName($fullTrait);
		$template = str_replace('{{full_trait}}', $fullTrait, $template);
		$template = str_replace('{{short_trait}}', $shortTrait, $template);
		$template = str_replace('{{namespace}}', $this->generateNamespace(), $template);
		$template = str_replace('{{short_base_class}}', $baseShortClass, $template);
		$template = str_replace('{{short_class}}', $this->generateShortClassName(), $template);
		$template = str_replace('{{short_parent_class}}', $parent->getShortClassName(), $template);
		$template = str_replace('use {{parent}}', "use " . $parent->getFullClassName(), $template);
		$properties = '';
		$vars = get_object_vars($this);
		ksort($vars); // Alphabetize
		foreach($vars as $key => $value){
			if(stripos($key, '_') === 0){
				continue;
			}
			if(in_array($key, ['x', 'property', 'parentModel', 'migrationText'])){
				continue;
			}
			if(empty($value)){
				continue;
			}
			$properties = $this->dump($key, $value, $properties, $parent, true);
		}
		$template = str_replace('{{properties}}', $properties, $template);
		FileHelper::writeByFilePath($this->getOutputPath(), $template);
		return $this->getOutputPath();
	}
	public function getOutputPath(): string{
		$parent = QMStr::toShortClassName($this->parentClass);
		return $this->getPropertiesPath() . DIRECTORY_SEPARATOR . $parent . DIRECTORY_SEPARATOR . $this->generateShortClassName() . '.php';
	}
	public function getPropertiesPath(): string{
		return 'app/Properties';
	}
	public function generateShortClassName(): string{
		return QMStr::toShortClassName($this->generateFullClassName());
	}
	public function generateFullClassName(): string{
		$parentClass = QMStr::toShortClassName($this->parentClass);
		return $this->generateNamespace() . '\\' . $parentClass . QMStr::snakeToClassName($this->name) . "Property";
	}
	public function generateNamespace(): string{
		return 'App\\Properties\\' . QMStr::toShortClassName($this->parentClass);
	}
	protected function populate_from_model_schema(){
		$fieldInput = (array)$this->get_model_schema_field();
		if($fieldInput){
			$this->name = $fieldInput['name'];
			$this->parseDBType($fieldInput['dbType']);
			$this->parseHtmlInput($fieldInput['htmlType'] ?? '');
			$this->validations = $fieldInput['validations'] ?? '';
			$this->isSearchable = $fieldInput['searchable'] ?? false;
			$this->isFillable = $fieldInput['fillable'] ?? true;
			$this->isPrimary = $fieldInput['primary'] ?? false;
			$this->inForm = $fieldInput['inForm'] ?? true;
			$this->inIndex = $fieldInput['inIndex'] ?? true;
			$this->inView = $fieldInput['inView'] ?? true;
		}
		$class = $this->getBaseClass();
		if(class_exists($class)){
			try {
				$existing = new $class();
			} catch (\Throwable $e) {
				QMLog::info(__METHOD__.": ".$e->getMessage());
				return;
			}
			foreach($existing as $key => $value){
				if($value !== null){
					$this->$key = $value;
				}
			}
		}
	}
	/**
	 * @return mixed|null
	 */
	public function get_model_schema_field(){
		$model_schema = $this->getParentModelSchema();
		if(!$model_schema){
			return null;
		}
		return collect($model_schema)->where('name', $this->name)->first();
	}
	/**
	 * @param Column|null $column
	 * @param $dbInput
	 */
	public function parseDBType($dbInput, Column $column = null){
		$this->dbInput = $dbInput;
		if(!is_null($column)){
			$this->dbInput = ($column->getLength() > 0) ? $this->dbInput . ',' . $column->getLength() : $this->dbInput;
			$this->dbInput = (!$column->getNotnull()) ? $this->dbInput . ':nullable' : $this->dbInput;
		}
		$this->prepareMigrationText();
	}
	private function prepareMigrationText(){
		$inputsArr = explode(':', $this->dbInput);
		$this->migrationText = '$table->';
		$fieldTypeParams = explode(',', array_shift($inputsArr));
		$this->fieldType = array_shift($fieldTypeParams);
		$this->migrationText .= $this->fieldType . "('" . $this->name . "'";
		if($this->fieldType == 'enum'){
			$this->migrationText .= ', [';
			foreach($fieldTypeParams as $param){
				$this->migrationText .= "'" . $param . "',";
			}
			$this->migrationText = substr($this->migrationText, 0, strlen($this->migrationText) - 1);
			$this->migrationText .= ']';
		} else{
			foreach($fieldTypeParams as $param){
				$this->migrationText .= ', ' . $param;
			}
		}
		$this->migrationText .= ')';
		foreach($inputsArr as $input){
			$inputParams = explode(',', $input);
			$functionName = array_shift($inputParams);
			if($functionName == 'foreign'){
				$foreignTable = array_shift($inputParams);
				$foreignField = array_shift($inputParams);
				$this->foreignKeyText .= "\$table->foreign('" . $this->name . "')->references('" . $foreignField .
					"')->on('" . $foreignTable . "');";
			} else{
				$this->migrationText .= '->' . $functionName;
				$this->migrationText .= '(';
				$this->migrationText .= implode(', ', $inputParams);
				$this->migrationText .= ')';
			}
		}
		$this->migrationText .= ';';
	}
	/**
	 * @param $htmlInput
	 */
	public function parseHtmlInput($htmlInput){
		$this->htmlInput = $htmlInput;
		$this->htmlValues = [];
		if(empty($htmlInput)){
			$this->htmlType = 'text';
			return;
		}
		if(Str::contains($htmlInput, 'selectTable')){
			$inputsArr = explode(':', $htmlInput);
			$this->htmlType = array_shift($inputsArr);
			$this->htmlValues = $inputsArr;
			return;
		}
		$inputsArr = explode(',', $htmlInput);
		$this->htmlType = array_shift($inputsArr);
		if(count($inputsArr) > 0){
			$this->htmlValues = $inputsArr;
		}
	}
	/**
	 * @return string
	 */
	public function getBaseClass(): string{
		$baseShortClass = $this->getBaseShortClassName();
		$baseClass = "App\Properties\Base\\$baseShortClass";
		return $baseClass;
	}
	/**
	 * @return string
	 */
	protected function getBaseShortClassName(): string{
		return "Base" . QMStr::toClassName($this->name) . "Property";
	}
	public function getTemplate(): string{
		/** @noinspection PhpUnhandledExceptionInspection */
		return FileHelper::getContents($this->getPropertiesPath() . '/property.stub');
	}
	/**
	 * @param bool $overwrite
	 * @return mixed
	 */
	public function generateHasTrait(bool $overwrite = false): string{
		$parent = $this->getParentModelOrFirstExample();
		$shortParentClass = $parent->getShortClassName();
		$traitClass = "App\\Traits\\HasModel\\Has$shortParentClass";
		return $this->generateTrait($traitClass, $overwrite);
	}
	/**
	 * @param string $traitClass
	 * @param bool $overwrite
	 * @return string
	 */
	private function generateTrait(string $traitClass, bool $overwrite = false): string{
		$parent = $this->getParentModelOrFirstExample();
		return BasePropertyGenerator::generateTraitStatic($traitClass, $parent->getFullClassName(), $overwrite);
	}
	/**
	 * @param string $traitClass
	 * @param string $parentClass
	 * @param bool $overwrite
	 * @return string
	 */
	public static function generateTraitStatic(string $traitClass, string $parentClass, bool $overwrite = true): string{
		$folderPath = FileHelper::classToFolderPath($traitClass);
		$shortTraitClass = QMStr::toShortClassName($traitClass);
		$folderName = FileHelper::classToFolderName($traitClass);
		$stubPath = $folderPath . "/" . $folderName . ".stub";
		$path = $folderPath . "/" . $shortTraitClass . ".php";
		if(!$overwrite && FileHelper::fileExists($path)){
			return $traitClass;
		}
		if(class_exists($traitClass)){
			return $traitClass;
		}
		try {
			$template = FileHelper::getContents($stubPath);
		} catch (QMFileNotFoundException $e) {
			le($e);
			le($e);
		}
		$shortParentClass = QMStr::toShortClassName($parentClass);
		$template = str_replace('{{short_trait_class}}', $shortTraitClass, $template);
		$template = str_replace('{{short_parent_class}}', $shortParentClass, $template);
		$template = str_replace('{{camel_class}}', QMStr::camelize($shortParentClass), $template);
		$template = str_replace('{{snake_class}}', QMStr::snakize($shortParentClass), $template);
		FileHelper::writeByFilePath($path, $template);
		return $traitClass;
	}
	/**
	 * @param bool $overwrite
	 * @return mixed
	 */
	public function generatePropertyTrait(bool $overwrite = false): string{
		$parent = $this->getParentModelOrFirstExample();
		$shortParentClass = $parent->getShortClassName();
		$shortTraitClass = $shortParentClass . "Property";
		$traitClass = "\App\\Traits\\PropertyTraits\\" . $shortTraitClass;
		return $this->generateTrait($traitClass, $overwrite);
	}
	/**
	 * @param $propertyModelKey
	 * @param $value
	 * @param string $properties
	 * @param BaseModel $parent
	 * @param bool $useConstants
	 * @return string
	 */
	protected function dump($propertyModelKey, $value, string $properties, BaseModel $parent,
		bool $useConstants): string{
		if(is_array($value)){
			return $properties;
		}
		$basePropertyName = $this->name;
		if(is_string($value)){
			$upper = strtoupper($value);
			$constName = 'TYPE_' . $upper;
			if(defined(BaseProperty::class . '::' . $constName)){
				$properties .= "\tpublic \$$propertyModelKey = self::$constName;\n";
				return $properties;
			}
		}
		if($propertyModelKey === 'name'){
			$properties .= "\tpublic \$name = self::NAME;
	public const NAME = '$value';\n";
			return $properties;
		}
		if($propertyModelKey === 'image'){
			$properties .= $this->image($value, $basePropertyName);
			return $properties;
		}
		if($propertyModelKey === 'fontAwesome'){
			$properties .= $this->fontAwesome($value, $basePropertyName);
			return $properties;
		}
		$value = $this->getValueFromBaseModel($propertyModelKey, $value, $parent);
		if($value === null || $value === ''){
			return $properties;
		}
		//break_if($propertyModelKey === "minimum" && $value, "value is $value");
		$upperKey = strtoupper($propertyModelKey);
		$shortParent = QMStr::toShortClassName(get_class($parent));
		$nameSuggestsNumeric = $this->propertyModelKeyIsNumeric($propertyModelKey);
		//break_if($propertyModelKey === 'description' && stripos($value, "'") !== false);
		if($propertyModelKey === 'default' && is_string($value) && stripos($value, "undefined")){
			$value = "undefined";
		}
		if(!$nameSuggestsNumeric && ($value === true || $value === "1")){
			$properties .= "\tpublic \$$propertyModelKey = true;\n";
		} elseif(!$nameSuggestsNumeric && $value === false || $value === "0"){
			$properties .= "\tpublic \$$propertyModelKey = false;\n";
		} elseif($value === get_class($parent)){
			$properties .= "\tpublic \$$propertyModelKey = $shortParent::class;\n";
		} elseif($useConstants && empty($value) && $value = $parent->getConstantValue($upperKey)){
			$properties .= "\tpublic \$$propertyModelKey = $shortParent::$upperKey;\n";
		} elseif($useConstants && is_string($value) && $constName = $parent->getConstantNameForValue($value)){
			$properties .= "\tpublic \$$propertyModelKey = $shortParent::$constName;\n";
		} else{
			if($nameSuggestsNumeric){
				$properties .= "\tpublic \$$propertyModelKey = $value;\n";
			} else{
				$properties .= "\tpublic \$$propertyModelKey = " . TVarDumper::dump($value) . ";\n";
			}
		}
		return $properties;
	}
	/**
	 * @param $value
	 * @param string $basePropertyName
	 * @return string
	 */
	protected function image($value, string $basePropertyName): string{
		if($value === ImageUrls::QUESTION_MARK){
			$value = null;
		}
		if(!$value){
			$value = ImageUrls::findConstantNameLike($basePropertyName, "QUESTION_MARK");
			return "\tpublic \$image = ImageUrls::$value;\n";
		} else{
			$value = ImageUrls::findConstantNameWithValue($value);
			if($value){
				return "\tpublic \$image = ImageUrls::$value;\n";
			} else{
				return "\tpublic \$image = '$value';\n";
			}
		}
	}
	/**
	 * @param $value
	 * @param string $basePropertyName
	 * @return string
	 */
	protected function fontAwesome($value, string $basePropertyName): string{
		if($value === FontAwesome::QUESTION_CIRCLE){
			$value = null;
		}
		if(!$value){
			$value = FontAwesome::findConstantNameLike($basePropertyName, "QUESTION_CIRCLE");
			if(empty($value)){
				le('empty($value)');
			}
			return "\tpublic \$fontAwesome = FontAwesome::$value;\n";
		} else{
			$value = FontAwesome::findConstantNameWithValue($value);
			if($value){
				return "\tpublic \$fontAwesome = FontAwesome::$value;\n";
			} else{
				if(empty($value)){
					le('empty($value)');
				}
				return "\tpublic \$fontAwesome = '$value';\n";
			}
		}
	}
	/**
	 * @param $key
	 * @param string|null $value
	 * @param BaseModel $parent
	 * @return float|string|null
	 */
	protected function getValueFromBaseModel(string $key, ?string $value, BaseModel $parent){
		if($key === 'default' && is_string($value) && stripos($value, "undefined")){
			$value = "\OpenApi\Generator::UNDEFINED";
		}
		if($key === 'description'){
			$value = $parent->getAttributeDescription($this->name);
		}
		if($key === 'maximum' && empty($value)){
			$value = $parent->getAttributeMaximum($this->name);
		}
		if($key === 'maxLength' && empty($value)){
			$maxLength = $parent->getAttributeMaxLength($this->name);
			if($maxLength){
				$value = (int)$maxLength;
			}
			if($value === 0){
				le('$value === 0');
			}
		}
		if($key === 'minimum' && empty($value)){
			$value = $parent->getAttributeMinimum($this->name);
		}
		if($key === 'minLength' && empty($value)){
			$minLength = $parent->getAttributeMinLength($this->name);
			if($minLength){
				$value = (int)$minLength;
			}
		}
		if($key === 'title' && empty($value)){
			$value = $parent->getAttributeTitle($this->name);
		}
		if($key === 'type' && empty($value)){
			$value = $parent->getAttributeType($this->name);
		}
		if($key === 'format' && empty($value)){
			$value = $parent->getAttributeFormat($this->name);
		}
		if($key === 'isOrderable'){
			$value = $parent->isOrderable($this->name);
		}
		if($key === 'isSearchable'){
			$value = $parent->isSearchable($this->name);
		}
		if($key === 'example' && empty($value)){
			$value = $parent->getAttribute($this->name);
			if(TimeHelper::isCarbon($value)){
				$value = $value->toDateTimeString();
			}
		}
		if($key === 'example' && !empty($value)){
			QMLog::debug("ex " . \App\Logging\QMLog::print_r($value, true));
		}
		if($key === 'validations' && empty($value)){
			$rules = $parent->getRules();
			$value = $rules[$this->name] ?? null;
		}
		return $value;
	}
	/**
	 * @param $propertyModelKey
	 * @return bool
	 */
	protected function propertyModelKeyIsNumeric(string $propertyModelKey): bool{
		if($propertyModelKey === "order"){return true;}
		if($propertyModelKey === "example" && $this->isNumeric()){
			return true;
		}
		if(stripos($propertyModelKey, "min") === 0){
			return true;
		}
		if(stripos($propertyModelKey, "max") === 0){
			return true;
		}
		return false;
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
		// TODO: Implement getCreateField() method.
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
		// TODO: Implement getDetailsField() method.
	}
	/**
	 * @return mixed|void
	 */
	public function getExample(){
		// TODO: Implement getExample() method.
	}
	/**
	 * @return BaseProperty
	 */
	public function getExisting(): ?BaseProperty{
		$baseShortClass = $this->getBaseClass();
		if(!class_exists($baseShortClass)){
			return null;
		}
		return new $baseShortClass();
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
		// TODO: Implement getIndexField() method.
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getUpdateField($resolveCallback = null, string $name = null): Field{
		// TODO: Implement getUpdateField() method.
	}
	/**
	 * @param $options
	 */
	public function parseOptions($options){
		$options = strtolower($options);
		$optionsArr = explode(',', $options);
		if(in_array('s', $optionsArr)){
			$this->isSearchable = false;
		}
		if(in_array('p', $optionsArr)){
			// if field is primary key, then its not searchable, fillable, not in index & form
			$this->isPrimary = true;
			$this->isSearchable = false;
			$this->isFillable = false;
			$this->inForm = false;
			$this->inIndex = false;
			$this->inView = false;
		}
		if(in_array('f', $optionsArr)){
			$this->isFillable = false;
		}
		if(in_array('if', $optionsArr)){
			$this->inForm = false;
		}
		if(in_array('ii', $optionsArr)){
			$this->inIndex = false;
		}
		if(in_array('iv', $optionsArr)){
			$this->inView = false;
		}
	}
	/**
	 * @return string
	 */
	protected function getTypeTrait(): string{
		return "\App\Traits\PropertyTraits\Is" . QMStr::toClassName($this->getPHPType());
	}
}
