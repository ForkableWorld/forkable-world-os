<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model;
use App\Cards\QMCard;
use App\CodeGenerators\Swagger\SwaggerDefinition;
use App\CodeGenerators\Swagger\SwaggerJson;
use App\Exceptions\QMException;
use App\Files\FileHelper;
use App\Http\Parameters\SortParam;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\PhpUnitJobs\Cleanup\ModelGeneratorJob;
use App\Slim\View\Request\QMRequest;
use App\Storage\Memory;
use App\Storage\S3\S3Private;
use App\Traits\CompressibleTrait;
use App\Traits\HasClassName;
use App\Traits\LoggerTrait;
use App\Types\ObjectHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\IonIcon;
use App\UI\QMColor;
use App\Utils\AppMode;
use App\Utils\UrlHelper;
use App\Variables\QMCommonVariable;
use App\Variables\QMVariableCategory;
use Illuminate\Support\Collection;
use LogicException;
use ReflectionObject;
use ReflectionProperty;
use stdClass;
use Throwable;
/** @package App\Slim\Model
 */
abstract class StaticModel {
	use LoggerTrait, CompressibleTrait, HasClassName;
	// Don't use private properties because it has unintended consequences
	protected $card;
	protected $sortingScore;
	protected const BACKGROUND_COLOR = QMColor::HEX_DARK_GRAY;
	protected static $booleanAttributes = [];
	protected static $json;
	protected static $pluralizedClassName;
	public $id;
	public const COLOR = BaseModel::COLOR;
	public const FIELD_ID = 'id';
	public const JSON_MAP = [];
	public const PATH_TO_JSON = 'tmp/qm-static-data';
	public const RECURSION_REPLACEMENT_STRING = "***RECURSION***";
	public const UNIQUE_INDEX_COLUMNS = [self::FIELD_ID];
	protected $calledFunctions = [];
	public static function getS3Bucket(): string{ return S3Private::getBucketName(); }
	/**
	 * @return array
	 */
	public static function getBooleanAttributes(): array{
		return static::$booleanAttributes;
	}
	/**
	 * @param $arrayOrObject
	 * @return void
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function populateFieldsByArrayOrObject(array|object $arrayOrObject): void {
		if(!$arrayOrObject){
			return;
		}
		$obj = ObjectHelper::convertToObject($arrayOrObject);
		foreach($this as $propertyName => $currentValue){
			if($propertyName === "json_encoded"){
				continue;
			} // This needs to be done later
			if($obj === null){
				continue;
			}
			$providedValue = ObjectHelper::getPropertyValueSnakeInsensitive($obj, $propertyName);
			if($providedValue === null){
				continue;
			}
			$providedValue = static::jsonDecodeAndCastToIntIfNecessary($providedValue, $propertyName);
			//$this->logValueAndProperty($providedValue, $propertyName);
			if($providedValue === null){
				continue;
			}
			$this->$propertyName = $providedValue;
		}
		try {
			$this->addJsonEncodedPropertiesIfNecessary($obj);
		} catch (\Throwable $e){
		    le($e);
		}
		if(isset($this->dbRow)){
			$this->dbRow = null;
		} // this causes problems when we populate study from a correlation
	}
	/**
	 * @return array
	 */
	protected function getUniqueIndex(): array{
		$uniqueFields = static::getUniqueIndexColumns();  // Don't use self::
		$index = [];
		foreach($uniqueFields as $uniqueField){
			if(!$uniqueField){
				le("Empty unique field!");
			}
			$property = static::getPropertyNameForDbField($uniqueField);
			if(empty($property)){
				le("No property for DB field $uniqueField");
			}
			if(!isset($this->$property)){
				return [];
			}
			$index[$uniqueField] = $this->$property;
		}
		return $index;
	}
	/**
	 * @param string $dbField
	 * @return string
	 */
	private static function getPropertyNameForDbField(string $dbField): string{
		return $dbField;
	} // Overridden in child class DBModel
	/**
	 * @param array $params
	 * @return static[]
	 */
	public static function get(array $params = []): array{
		return [];
	}
	/**
	 * @param $message
	 * @param array|object $metaData
	 */
	public function exceptionIfNotProduction($message, $metaData = []){
		QMLog::exceptionIfNotProduction($this->__toString() . " $message", $this->getLogMetaData($metaData), null);
	}
	/**
	 * @return int|string
	 */
	public function getId(){
		return $this->id;
	}
	/**
	 * @return bool
	 */
	public function hasId(): bool{
		$id = $this->id ?? null;
		if($id === null && static::FIELD_ID !== "id"){
			$idField = QMStr::camelize(static::FIELD_ID);
			$id = $this->$idField;
		}
		return $id !== null;
	}
	/**
	 * @return array
	 */
	public function toArray(): array{
		return json_decode(json_encode($this), true);
	}
	/**
	 * @param object|array $object
	 * @param array|object $excluded
	 */
	public function setFallbackProperties($object, $excluded = []){
		$object = json_decode(json_encode($object), true);
		foreach($this as $key => $value){
			if(in_array($key, $excluded, true)){
				continue;
			}
			if(!isset($this->$key) && isset($object[$key])){
				$this->$key = $object[$key];
			}
		}
	}
	public function populateDefaultFields(){
	}
	/**
	 * @param string $namespace
	 * @param string $className
	 * @param $data
	 * @return static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function instantiate(string $namespace, string $className, $data = null){
		$className = '\App\Slim\Model\\' . $namespace . '\\' . str_replace(' ', '', $className);
		/** @var StaticModel $instance */
		$instance = new $className;
		if($data){
			$instance->populateFieldsByArrayOrObject($data);
		}
		return $instance;
	}
	/**
	 * @return object
	 */
	public function getWithoutArrayOrObjectProperties(){
		$object = new stdClass();
		foreach($this as $key => $value){
			if(!is_array($value) && !is_object($value) && $value !== null){
				$object->$key = $value;
			}
		}
		return $object;
	}
	/**
	 * @return SwaggerDefinition
	 */
	public function getSwaggerDefinition(): SwaggerDefinition{
		$name = $this->getShortClassName();
		if(str_contains($name, "UnitCategory")){$name = "UnitCategory";}
		if(str_contains($name, "VariableCategory")){$name = "VariableCategory";}
		try {
			return SwaggerJson::getDefinition($name);
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
		}
		$name = str_replace("QM", "", $name);
		return SwaggerJson::getDefinition($name);
	}
	/**
	 * @param array|object $arrayOrObject
	 * @return static|null|bool
	 */
	public static function instantiateIfNecessary(array|object|string $arrayOrObject){
		if($arrayOrObject instanceof static){
			return $arrayOrObject;
		}
		if(is_string($arrayOrObject)){$arrayOrObject = json_decode($arrayOrObject, true);}
		$model = new static();
		$model->populateFieldsByArrayOrObject($arrayOrObject);
		return $model;
	}
	/**
	 * @param Collection|array $array
	 * @return static[]
	 * Much slower than instantiateDBRows but more versatile
	 * @deprecated Replace with $qb->getDBModels();
	 */
	public static function instantiateNonDBRows($array): array{
		QMLog::debug(__METHOD__);
		$models = [];
		foreach($array as $item){
			$models[] = static::instantiateIfNecessary($item);
		}
		return $models;
	}
	/**
	 * @return float
	 */
	public function getSortingScore(): float{
		if($this->sortingScore === null){
			le("Sorting score not set on $this");
		}
		return $this->sortingScore;
	}
	/**
	 * @return int
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	protected function setSortingScore(){
		$sort = SortParam::getSort();
		if($sort && property_exists($this, $sort)){
			$this->sortingScore = $this->$sort;
		}
		return $this->sortingScore;
	}
	/**
	 * @return string
	 */
	public function __toString(){
		return $this->getTitleAttribute();
	}
	/**
	 * @param $arrayOrObject
	 */
	public function addAndPopulateExtraFieldsByArrayOrObject($arrayOrObject){
		foreach($arrayOrObject as $propertyName => $value){
			if(!isset($this->$propertyName)){
				$this->$propertyName = $value;
			}
		}
	}
	/**
	 * @param string $message
	 * @param int $code
	 */
	public function invalid(string $message, int $code = QMException::CODE_BAD_REQUEST){
		if(QMRequest::isPost() && Memory::get(Memory::THROW_EXCEPTION_IF_INVALID, Memory::MISCELLANEOUS)){
			$this->throwQMException($message, $code);
		} else{
			$this->logError($message);
		}
	}
	/**
	 * @param static[] $array
	 * @param bool $unsetNulls
	 * @return QMCard[]
	 */
	public static function toCards(array $array, bool $unsetNulls): array{
		$cards = [];
		foreach($array as $item){
			$cards[] = $item->getCard();
		}
		if($unsetNulls){
			$cards = static::removeNullPropertiesFromArray($cards);
		}
		return $cards;
	}
	/**
	 * @return QMCard
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getCard(){
		return $this->card ?: $this->setCard();
	}
	/**
	 * @return QMCard
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function setCard(){
		return $this->card = new QMCard();
	}
	/**
	 * @return stdClass
	 */
	public function getWithoutNullArrayObjectProperties(): stdClass{
		$clone = new stdClass();
		foreach($this as $key => $value){
			if($value === null){
				continue;
			}
			if(is_object($value)){
				continue;
			}
			if(is_array($value)){
				continue;
			}
			$clone->$key = $value;
		}
		return $clone;
	}
	/**
	 * @return stdClass
	 */
	public function getWithoutNullProperties(): stdClass{
		$clone = new stdClass();
		foreach($this as $key => $value){
			if($value === null){
				continue;
			}
			$clone->$key = $value;
		}
		return $clone;
	}
	/**
	 * @param StaticModel[] $array
	 * @return array
	 */
	public static function removeNullPropertiesFromArray(array $array): array{
		$clones = [];
		foreach($array as $item){
			$clones[] = $item->getWithoutNullProperties();
		}
		return $clones;
	}
	/**
	 * @return stdClass
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	protected function getSearchItem(){
		$this->getSortingScore();
		$item = $this->getWithoutNullArrayObjectProperties();
		unset($item->cachedAt);
		return $item;
	}
	/**
	 * @return array
	 */
	public static function getJsonArray(): array{
		if(static::$json !== null){
			return static::$json;
		}
		try {
			$path = static::getPathToJson() . '/' . static::getPluralizedClassName() . '.json';
			$arrayOrObject = FileHelper::readJsonFile($path);
			if(is_array($arrayOrObject)){
				return static::$json = $arrayOrObject;
			}
			$array = [];
			foreach($arrayOrObject as $key => $value){
				$array[$value->id] = $value;
			}
			return static::$json = $array;
		} catch (Throwable $e) {
			return static::$json = [];
		}
	}
	/**
	 * @return string
	 */
	protected static function getPathToJson(): string{
		return static::PATH_TO_JSON;
	}
	/**
	 * @return array
	 */
	public function addToJson(): array{
		$array = static::getJsonArray();
		$array[$this->getId()] = $this->getSearchItem();
		return $this->writeJson($array);
	}
	/**
	 * @return array
	 */
	public function deleteFromJson(): array{
		$this->logInfo(__FUNCTION__);
		$array = static::getJsonArray();
		if(isset($array[0])){
			$array = QMArr::filter($array, ['id' => '(ne)' . $this->getId()]);
		} else{
			unset($array[$this->getId()]);
		}
		return $this->writeJson($array);
	}
	/**
	 * @return string
	 */
	protected static function getPluralizedClassName(): string{
		if(static::$pluralizedClassName){
			return static::$pluralizedClassName;
		}
		return static::$pluralizedClassName = QMStr::pluralize((new \ReflectionClass(static::class))->getShortName());
	}
	/**
	 * @param array $array
	 * @return array
	 */
	private function writeJson(array $array): array{
		$this->logInfo(__FUNCTION__);
		$indexedById = [];
		foreach($array as $item){
			$indexedById[$item->id] = $item;
		}
		$json = QMStr::prettyJsonEncode($indexedById);
		FileHelper::writeByDirectoryAndFilename(static::getPathToJson(), static::getPluralizedClassName() . '.json',
			$json);
		$js = 'var ' . static::getPluralizedClassName() . ' = ' . $json . ';';
		FileHelper::writeByDirectoryAndFilename(static::getPathToJson(), static::getPluralizedClassName() . '.js', $js);
		return static::$json = $array;
	}
	/**
	 * @return string
	 */
	public function getLogMetaDataSlug(): string{
		return QMStr::slugify($this->__toString(), false, 0);
	}
	/**
	 * @param object|array $arrayOrObject
	 */
	private function addJsonEncodedPropertiesIfNecessary(object|array $arrayOrObject): void{
		if(isset($arrayOrObject->json_encoded)){
			$json = json_decode($arrayOrObject->json_encoded, false);
			if(!is_object($json)){
				$this->logError("Could not decode json_encoded: $arrayOrObject->json_encoded");
			} else{
				foreach($json as $propertyName => $currentValue){
					if(isset(static::JSON_MAP[$propertyName])){
						$propertyName = static::JSON_MAP[$propertyName];
					}
					$this->$propertyName = $currentValue;
				}
			}
		}
	}
	/**
	 * @param string $message
	 */
	public function infoOrLogicExceptionIfTesting(string $message){
		if(AppMode::isTestingOrStaging()){
			le($message);
		}
		$this->logInfo($message);
	}
	/**
	 * @param $providedValue
	 * @param string $propertyName
	 * @return int|mixed
	 */
	public static function jsonDecodeAndCastToIntIfNecessary($providedValue, string $propertyName){
		$providedValue = QMStr::jsonDecodeIfNecessary($providedValue);
		if(is_string($providedValue) && stripos($propertyName, 'UnitId') !== false){
			$providedValue = (int)$providedValue;
		}
		return $providedValue;
	}
	/**
	 * @return array
	 */
	public function getHardCodedParametersArray(): array{
		le("Please define getHardCodedParametersArray as done in \App\Variables\CommonVariable::getHardCodedParametersArray");
		throw new \LogicException();
	}
	/**
	 * @return string
	 */
	public function getTitleAttribute(): string{
		if(method_exists($this, 'getDisplayNameAttribute')){
			return $this->getDisplayNameAttribute();
		}
		if(method_exists($this, 'getName')){
			return $this->getName();
		}
		if(method_exists($this, 'getNameAttribute')){
			return $this->getNameAttribute();
		}
		if(method_exists($this, 'getSourceObject')){
			$s = $this->getSourceObject();
			if(method_exists($s, 'getTitleAttribute')){
				return $s->getTitleAttribute();
			}
		}
		le("Please implement getTitleAttribute on ".get_class($this));
	}
	/**
	 * @return string
	 */
	public function getSubtitleAttribute(): string{
		if(method_exists($this, 'getSourceObject')){
			$s = $this->getSourceObject();
			if(method_exists($s, 'getDescription')){
				return $s->getSubtitleAttribute();
			}
		}
		le("Please implement getSubtitleAttribute for " . $this->getShortClassName());
	}
	/**
	 * @return string
	 */
	public function getImage(): string{
		$img = ObjectHelper::get($this, ['image', 'imageUrl', 'avatar']);
		if(!empty($img)){
			return $img;
		}
		if(method_exists($this, 'getSourceObject')){
			$s = $this->getSourceObject();
			if(method_exists($s, 'getImage')){
				return $s->getImage();
			}
		}
		le("Please implement getImage on " . get_class($this));
		throw new \LogicException();
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getUrl(array $params = []): string{
		if(method_exists($this, 'getSourceObject')){
			$s = $this->getSourceObject();
			if(method_exists($s, 'getUrl')){
				return $s->getUrl();
			}
		}
		le("Please implement getUrl for " . static::class);
		throw new \LogicException();
	}
	/**
	 * @return string
	 */
	protected function getHardCodedShortClassName(): string{
		$parentClassName = $this->getShortClassName();
		$parentWithoutQM = str_replace('QM', '', $parentClassName);
		$name = $this->displayName ?? $this->name ?? $this->title ?? null;
		if(stripos($parentClassName, $name) === 0){
			return $parentClassName;
		}
		if(!$name){
			le("No name to generate static model!");
		}
		return ModelGeneratorJob::getChildClassName($parentWithoutQM, $name);
	}
	/**
	 * @param string $propertyName
	 * @return string
	 */
	public function getConstantReferenceToPropertyOfChildClass(string $propertyName): string{
		return $this->getHardCodedShortClassName() . "::" . QMStr::toScreamingSnakeCase($propertyName);
	}
	/**
	 * @param array $array
	 * @return static
	 */
	public static function __set_state(array $array): self{
		$object = new static;
		foreach($array as $key => $value){
			$object->{$key} = $value;
		}
		return $object;
	}
	/**
	 * @return string|BaseModel
	 */
	public static function getLaravelClassName(): string{
		try {
			if(static::LARAVEL_CLASS){
				return static::LARAVEL_CLASS;
			}
		} catch (\Throwable $e) {
			QMLog::info(static::class . ": " . $e->getMessage());
			/** @var LogicException $e */
			throw $e;
		}
		if(str_contains(static::class, "\QM")){
			$laravelClass = QMStr::after("\QM", static::class);
			return '\App\Models\\' . $laravelClass;
		}
		//$laravelClass = str_replace('App\Slim\Model', '\App\Models', static::class);
		$short = (new \ReflectionClass(static::class))->getShortName();
		$short = str_replace("QM", "", $short);
		$laravelClass = '\App\Models\\' . $short;
		if(!class_exists($laravelClass)){
			le("$laravelClass does not exist.  Please define laravel class for " . static::class);
		}
		return $laravelClass;
	}
	/**
	 * @return string
	 */
	public function getCategoryDescription(): string{
		$swaggerDefinition = $this->getSwaggerDefinition();
		return $swaggerDefinition->getSubtitleAttribute();
		//return "Examination of the effects of a predictor variable on an outcome of interest. ";
	}
	/**
	 * @param array $stack
	 * @return static|string
	 */
	public function removeRecursiveCircularReferences(array $stack = []){
		if(!in_array($this, $stack, true)){
			$stack[] = $this;
			foreach($this as $subKey => &$subObject){
				try {
					if(method_exists($subObject, 'removeRecursiveCircularReferences')){
						$this->$subKey = $subObject->removeRecursiveCircularReferences($stack);
					} elseif(is_array($subObject)){
						foreach($subObject as $subSubKey => $subSubValue){
							if(method_exists($subSubValue, 'removeRecursiveCircularReferences')){
								$subSubValue->removeRecursiveCircularReferences($stack);
							}
						}
					}
				} catch (Throwable $e) {
					QMLog::info("$subKey: " . $e->getMessage());
					$this->$subKey = null;
				}
			}
		} else{
			return self::RECURSION_REPLACEMENT_STRING;
		}
		return $this;
	}
	/**
	 * @return void
	 */
	public function outputPropertySizes(){
		$table = [];
		foreach($this as $propertyName => $value){
			$bytes = ObjectHelper::getSizeInBytes($value);
			$table[$bytes]['property'] = $propertyName;
			$table[$bytes]['size'] = QMStr::human_filesize($bytes);
			$table[$bytes]['type'] = gettype($value);
			if(is_array($value) && isset($value[0])){
				$array = $value;
				$first = $array[0];
				$type = gettype($first);
				$count = count($array);
				$table[$bytes]['type'] = "Array of $count $type's";
			}
		}
		krsort($table);
		QMLog::table($table, "Total Size of " . $this->getShortClassName() . ": " .
			ObjectHelper::getSizeHumanized($this));
	}
	/**
	 * @return static $this
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function cloneAndShrink(){
		$clone = clone $this;
		$clone->shrink();
		return $clone;
	}
	public function shrink(){
		foreach($this as $key => $value){
			if(is_array($value) && count($value) > 1000){
				$this->$key = null;
			}
		}
	}
	/**
	 * Get an attribute from the model.
	 * @param string|string[] $key
	 * @return mixed
	 */
	public function getAttribute($key){
		if(is_array($key)){
			foreach($key as $one){
				$result = $this->getAttribute($one);
				if(isset($result)){
					return $result;
				}
			}
			return null;
		}
		$camel = QMStr::camelize($key);
		if(!isset($this->$camel)){
			return null;
		}
		return $this->$camel;
	}
	/**
	 * @param $key
	 * @return mixed|null
	 */
	public function getRawAttribute($key){
		return $this->getAttribute($key);
	}
	/**
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	public function setAttribute($key, $value){
		$camel = QMStr::camelize($key);
		return $this->$camel = $value;
	}
	/**
	 * @return string
	 */
	public static function getTableName(): string{
		return static::getPluralizedSlugifiedClassName();
	}
	public function verifyJsonEncodableAndNonRecursive(){
		$enabled = false;
		if(!$enabled){
			return;
		}
		if(AppMode::isTestingOrStaging()){
			if(isset($this->commonTagVariables[0]->commonTaggedVariables[0])){
				le("this->commonTagVariables[0]->commonTaggedVariables[0] should not be set!");
			}
			$encoded = json_encode($this);
			if(!$encoded){
				$err = json_last_error_msg();
				if(stripos($err, "recursion") !== false){
					$this->removeRecursiveCircularReferences();
				}
				throw new LogicException($err);
			}
			if(stripos($encoded, self::RECURSION_REPLACEMENT_STRING) !== false){
				le("$this contains " . self::RECURSION_REPLACEMENT_STRING);
			}
		}
	}
	/**
	 * @return static|object
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function cloneAndRemoveRecursion(){
		$clone = clone $this;
		return ObjectHelper::remove_recursive_circular_references($clone);
	}
	/**
	 * @return string
	 */
	public function getBackgroundColor(): string{
		return static::BACKGROUND_COLOR;
	}
	public function getPublicPropertyNames(): array{
		$names = [];
		$reflect = new ReflectionObject($this);
		foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC /* + ReflectionProperty::IS_PROTECTED*/) as $prop) {
			$names[]= $prop->getName();
		}
		return $names;
	}
	public function removeNonPublicProperties(): void {
		$publicProps  = $this->getPublicPropertyNames();
		foreach($this as $key => $value){
			if(!$value){continue;}
			if(!in_array($key, $publicProps)){
				$this->$key = null;
			}
		}
	}
	public function removeRecursion(){
		$this->removeNonPublicProperties();
		foreach($this as $key => $value){
			if(!$value){continue;}
			if(is_array($value) && isset($value[0])){
				$value = $value[0];
			}
			if(!is_object($value)){
				continue;
			}
			$class = static::class;
			$valueClass = get_class($value);
			if($valueClass === $class){
				$this->$key = null;
			}
		}
	}
	public function getUniqueParams(): array{
		return $this->getUniqueIndex();
	}
	public function getAvatar(): string{
		return IonIcon::getIonIconPngUrl($this->getIonIcon());
	}
	public function getIonIcon(): string{
		return IonIcon::help;
	}
	public static function generateDataLabUrl(string $path = null, array $params = []): string{
		$camel = QMStr::camelize(static::TABLE);
		if($path){
			$path = $camel . "/$path";
		} else{
			$path = $camel;
		}
		$path = str_replace($camel . "//", $camel . "/", $path);
		return UrlHelper::getDataLabUrl($path, $params);
	}
	/**
	 * @param $id
	 * @param array $params
	 * @return string
	 */
	public static function generateDataLabShowUrlById($id, array $params = []): string{
		return static::generateDataLabUrl($id, $params);
	}
	public function getDataLabEditUrl(array $params = []): string{
		if(!isset($this->id)){
			return "No id to generate edit url";
		}
		return static::generateDataLabUrl($this->getId() . "/edit", $params);
	}
	public function getDataLabShowUrl(array $params = []): string{
		if(!isset($this->id)){
			return "No id to generate show url";
		}
		return static::generateDataLabShowUrlById($this->getId(), $params);
	}
	public function getDataLabDisplayNameLink(array $params = [], int $maxLength = 50): string{
		$url = $this->getDataLabShowUrl($params);
		$name = $this->getTitleAttribute();
		$name = QMStr::truncate($name, $maxLength);
		return "<a href=\"$url\" target='_blank' title=\"See $name Details\">$name</a>";
	}
	public function getDataLabUrls(): array{
		$table = static::TABLE;
		$meta = $this->__toString();
		$arr = [
			"view $table" => static::generateDataLabUrl(),
			"edit $meta" => $this->getDataLabEditUrl(),
			"show $meta" => $this->getUrl(),
		];
		return $arr;
	}
	public function getUrlsString(): string{
		$urls = $this->getDataLabUrls();
		$str = '';
		foreach($urls as $key => $value){
			$key = QMStr::titleCaseSlow($key);
			$str .= "\n$key => $value\n";
		}
		return $str;
	}
	/**
	 * @param int|string $id
	 * @return static|null
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function find($id){
		$memory = static::findInMemory($id);
		if($memory !== null){
			return $memory;
		}
		$arr = static::get([static::FIELD_ID => $id]);
		return $arr[0] ?? null;
	}
	/**
	 * @return string
	 */
	protected static function getPluralParentClassName(): string{
		$parentClassName = (new \ReflectionClass(static::class))->getShortName();
		$parentWithoutQM = str_replace('QM', '', $parentClassName);
		$pluralParentClass = QMStr::pluralize($parentWithoutQM);
		return $pluralParentClass;
	}
	/**
	 * @return string
	 */
	protected function getClassCategoryName(): string{
		$categoryName = $unit = $unitClass = null;
		if(method_exists($this, 'getVariableCategoryName')){
			$categoryName = $this->getVariableCategoryName();
			$categoryName = QMStr::toClassName($categoryName);
		}
		return $categoryName;
	}
	/**
	 * @param string $function
	 * @return mixed|null
	 */
	protected function getPreviousFunctionOutput(string $function){
		return $this->calledFunctions[$function] ?? null;
	}
	/**
	 * @param null $apiVersionNumber
	 */
	public function addLegacyProperties($apiVersionNumber = null){
		$legacyProperties = static::getLegacyPropertiesToAdd($apiVersionNumber);
		foreach($legacyProperties as $legacyKey => $currentKey){
			if(isset($this->$currentKey)){
				$this->$legacyKey = $this->$currentKey;
			} elseif(!property_exists($this, $legacyKey)){
				$this->$legacyKey = null;
			}
		}
	}
	/**
	 * @param $apiVersionNumber
	 * @return array
	 */
	public static function getLegacyPropertiesToAdd($apiVersionNumber = null): array{
		// Legacy => Current
		return [];
	}
	/**
	 * @return Collection|static[]
	 */
	public static function all(): Collection{
		return collect(static::get());
	}
	/**
	 * @param $id
	 * @return mixed
	 */
	public function setId($id){
		return $this->id = $id;
	}
	/**
	 * @param array|Collection $arrOrCollection
	 * @return static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function getFirst($arrOrCollection){
		return QMArr::first($arrOrCollection);
	}
	/**
	 * @param bool $snakize
	 * @return array
	 */
	public function toNonNullArray(bool $snakize = true): array{
		$arr = $this->toArray();
		$nonNull = [];
		foreach($arr as $key => $value){
			if($value !== null){
				if($snakize){
					$nonNull[QMStr::snakize($key)] = $value;
				} else{
					$nonNull[$key] = $value;
				}
			}
		}
		return $nonNull;
	}
}
