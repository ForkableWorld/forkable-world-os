<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Types;
use App\Exceptions\ExceptionHandler;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Slim\Model\StaticModel;
use App\Storage\MemoryOrRedisCache;
use Exception;
use MongoDB\Model\BSONDocument;
use ReflectionClass;
use ReflectionProperty;
use stdClass;
/** Class ObjectHelper
 * @package App\Utils
 */
class ObjectHelper {
	private static $instantiated;
	public static function empty(object $obj): bool{
		$arr = (array)$obj;
		return !$arr;
	}
	/**
	 * @param object|array $object
	 * @param bool $publicOnly
	 * @return float
	 */
	public static function getSizeInKiloBytes($object, bool $publicOnly = true): float {
		if($publicOnly){
			$object = json_decode(json_encode($object));
		} // Avoids PDO error
		$serialized = serialize($object);
		return self::getSizeOfStringInKiloBytes($serialized);
	}
	/**
	 * @param object|array $object
	 * @return int
	 */
	public static function getSizeInBytes($object): int{
		$serialized = serialize($object);
		return strlen($serialized);
	}
	/**
	 * @param string $string
	 * @param bool $round
	 * @return float
	 */
	public static function getSizeOfStringInKiloBytes(string $string, bool $round = false): float {
		$size = strlen($string) / 1000;
		if($round){
			return round($size);
		}
		return $size;
	}
	/**
	 * @param mixed $mixed
	 * @return string
	 */
	public static function getSizeHumanized($mixed): string{
		return QMStr::getSizeOfStringHumanized(serialize($mixed));
	}
	/**
	 * @param object $obj
	 * @param bool $publicOnly
	 * @param bool $round
	 * @return array
	 */
	public static function getPropertySizesInKb(object $obj, bool $publicOnly = true, bool $round = false): array{
		if($publicOnly){
			$obj = json_decode(json_encode($obj));
		} // Avoids PDO error
		$sizes = [];
		foreach($obj as $key => $value){
			$sizes[$key] = self::getSizeOfStringInKiloBytes(json_encode($value), $round);
		}
		arsort($sizes);
		return $sizes;
	}
	/**
	 * @param object $object
	 * @param bool $publicOnly // json_encodes to avoid PDO error
	 * @return array|int
	 */
	public static function getSubPropertySizesInKb(object $object, bool $publicOnly = true){
		if(!is_object($object)){
			return self::getSizeInKiloBytes($object);
		}
		$propertySizes = [];
		foreach($object as $key => $subValue){
			if(self::getSubPropertySizesInKb($subValue)){
				$propertySizes[$key] = self::getSubPropertySizesInKb($subValue, $publicOnly);
			}
		}
		return $propertySizes;
	}
	/**
	 * @param int $maximumKb
	 * @param object $object
	 * @param bool $unsetPrivate
	 * @param bool $unsetObjects
	 * @return object
	 */
	private static function unsetPropertiesWithSizeGreaterThanForObject(int $maximumKb, object $object,
		bool $unsetPrivate = true, bool $unsetObjects = false): ?object{
		if(!is_object($object)){
			return null;
		}
		if($unsetPrivate){
			$object = json_decode(json_encode($object));
		}
		foreach($object as $key => $value){
			if(!$unsetObjects && is_object($value) && $key !== "highchartConfig"){
				$object->$key =
					self::unsetPropertiesWithSizeGreaterThanForObject($maximumKb, $value, $unsetPrivate, $unsetObjects);
			} elseif($size = self::getSizeInKiloBytes($value) > $maximumKb){
				QMLog::error("$key size $size KB too big for memcached (>$maximumKb KB).  Unsetting...");
				unset($object->$key);
			}
		}
		return $object;
	}
	/**
	 * @param int $maximumKb
	 * @param array $array
	 * @param bool $unsetPrivate
	 * @param bool $unsetObjects
	 * @return array
	 */
	private static function unsetPropertiesWithSizeGreaterThanForArray(int $maximumKb, array $array,
		bool $unsetPrivate = true, bool $unsetObjects = false): array{
		$smaller = [];
		foreach($array as $object){
			$smaller[] =
				self::unsetPropertiesWithSizeGreaterThanForObject($maximumKb, $object, $unsetPrivate, $unsetObjects);
		}
		return $smaller;
	}
	/**
	 * @param int $maximumKb
	 * @param array|object $arrayOrObject
	 * @param bool $unsetPrivate
	 * @param bool $unsetObjects
	 * @return null
	 */
	public static function unsetPropertiesWithSizeGreaterThan(int $maximumKb, $arrayOrObject, bool $unsetPrivate = true,
		bool $unsetObjects = false){
		if(is_array($arrayOrObject)){
			return self::unsetPropertiesWithSizeGreaterThanForArray($maximumKb, $arrayOrObject, $unsetPrivate,
				$unsetObjects);
		}
		return self::unsetPropertiesWithSizeGreaterThanForObject($maximumKb, $arrayOrObject, $unsetPrivate,
			$unsetObjects);
	}
	/**
	 * @param $value
	 * @param int|null $modifiedMaximumKb
	 * @return null
	 */
	public static function shrinkObjectIfTooBigForMemcached($value, int $modifiedMaximumKb = null){
		$maximumKb = $modifiedMaximumKb ?: MemoryOrRedisCache::MAXIMUM_KB;
		if(!is_object($value)){
			return $value;
		}
		$value = clone $value;
		$sizeInKbBefore = self::getSizeInKiloBytes($value);
		if($sizeInKbBefore > $maximumKb){
			$value = self::unsetPropertiesWithSizeGreaterThan($maximumKb, $value);
		}
		return $value;
	}
	/**
	 * @param string $name
	 * @param $object
	 * @param bool $recursive
	 * @return array|null
	 */
	public static function logPropertySizes(string $name, $object, bool $recursive = true): ?array{
		if(!is_array($object) && !is_object($object)){
			return [];
		}
		QMLog::info("=== " . QMStr::camelToTitle($name) . " property sizes ====");
		$sizes = [];
		foreach($object as $key => $value){
			$size = self::getSizeInKiloBytes($value);
			$sizes[(int)$size] = $key;
		}
		if(!$sizes){
			QMLog::error("No sizes in logPropertySizes!");
			return $sizes;
		}
		QMArr::sortByKeysDescending($sizes);
		foreach($sizes as $size => $key){
			if(is_int($key)){
				$message = " Array Item $key: $size kB";
			} else{
				$message = QMStr::camelToTitle($key) . ": $size kB";
			}
			if($recursive){
				$message = QMStr::camelToTitle($name) . " " . $message;
			}
			ConsoleLog::info($message);
			if($size > 100 && $recursive){
				if(is_object($object)){
					self::logPropertySizes($key, $object->$key);
				} elseif(is_array($object)){
					self::logPropertySizes($key, $object[$key]);
				}
			}
		}
		return $sizes;
	}
	/**
	 * @param object $obj
	 * @return object
	 */
	public static function unsetNullProperties(object $obj): object{
		foreach($obj as $key => $value){
			if($value === null){
				unset($obj->$key);
			}
		}
		return $obj;
	}
	/**
	 * @param object $obj
	 * @return object
	 */
	public static function unsetNullAndEmptyArrayOrStringProperties(object|array $obj): object|array{
		foreach($obj as $key => $value){
			if(is_array($value) && $value){
				$value = self::unsetNullAndEmptyArrayOrStringProperties($value);
				if(is_object($obj)){
					$obj->$key = $value;
				} elseif(is_array($obj)){
					$obj[$key] = $value;
				}
				continue;
			}
			if($value === null || $value === [] || $value === ""){
				if(is_object($obj)){
					unset($obj->$key);
				} elseif(is_array($obj)){
					unset($obj[$key]);
				}
			}
		}
		return $obj;
	}
	/**
	 * @param array|object $objectOrArray
	 * @return array|object
	 */
	public static function unsetNullAndEmptyStringFields($objectOrArray){
		if(!is_array($objectOrArray) && !is_object($objectOrArray)){
			return $objectOrArray;
		}
		$objectOrArray = json_decode(json_encode($objectOrArray));
		foreach($objectOrArray as $key => $value){
			$value = self::unsetNullAndEmptyStringFields($value);
			if(self::valueIsNullEmptyStringOrEmptyStdClass($value)){
				if(is_object($objectOrArray)){
					unset($objectOrArray->$key);
				} else{
					unset($objectOrArray[$key]);
				}
			} else{
				if(is_object($objectOrArray)){
					$objectOrArray->$key = $value;
				} else{
					$objectOrArray[$key] = $value;
				}
			}
		}
		return $objectOrArray;
	}
	/**
	 * @param $value
	 * @return bool
	 */
	public static function valueIsNullEmptyStringOrEmptyStdClass($value): bool{
		if($value === ""){
			return true;
		}
		if($value === null){
			return true;
		}
		if($value instanceof stdClass && empty((array)$value)){
			return true;
		}
		return false;
	}
	/**
	 * @param $idealObject
	 * @return array
	 */
	public static function getAllPropertiesOfClass($idealObject): array{
		return get_object_vars($idealObject);
	}
	/**
	 * @param $idealObject
	 * @return array
	 */
	public static function getAllPropertiesOfClassAsKeyArray($idealObject): array{
		$properties = self::getAllPropertiesOfClass($idealObject);
		$keys = [];
		foreach($properties as $key => $value){
			$keys[] = $key;
		}
		return $keys;
	}
	/**
	 * @param StaticModel[] $array
	 */
	public static function addLegacyPropertiesToObjectsInArray(array $array){
		foreach($array as $object){
			$object->addLegacyProperties();
		}
	}
	/**
	 * @param string $filePath
	 * @return array
	 */
	public static function getJsonFileAsArrayOfObjects(string $filePath): array{
		$string = file_get_contents($filePath);
		$array = json_decode($string);
		return $array;
		//$array = json_decode($string, true);
		//return ObjectHelper::convertArrayOfArraysToArrayOfObjects($array);
	}
	/**
	 * @param string $classNameWithNamespace
	 * @return array|string
	 */
	public static function classToPropertyName(string $classNameWithNamespace){
		$shortName = substr($classNameWithNamespace, strrpos($classNameWithNamespace, '\\') + 1);
		return QMStr::toCamelCase($shortName);
	}
	/**
	 * @param $sourceObject
	 * @param $destinationObject
	 * @return object
	 */
	public static function copyPublicPropertiesFromOneObjectToAnother($sourceObject, $destinationObject): object{
		if(!$sourceObject){
			le("No sourceObject");
		}
		if(!$destinationObject){
			le("No destinationObject");
		}
		$publicProperties = self::getPublicPropertiesOfObject($sourceObject);
		foreach($publicProperties as $publicProperty){
			if(isset($sourceObject->$publicProperty)){
				$destinationObject->$publicProperty = $sourceObject->$publicProperty;
			}
		}
		return $destinationObject;
	}
	/**
	 * @param $sourceArray
	 * @param $targetObject
	 * @param bool $overwrite
	 * @return mixed
	 */
	public static function populate($sourceArray, $targetObject, bool $overwrite = true){
		if(!$sourceArray){
			return $targetObject;
		}
		$publicPropertiesOfTarget = self::getPublicPropertiesOfObject($targetObject);
		foreach($sourceArray as $key => $value){
			$camel = QMStr::toCamelCase($key);
			if(!in_array($camel, $publicPropertiesOfTarget, true)){
				continue;
			}
			if(empty($targetObject->$camel) || $overwrite){
				$targetObject->$camel = $value;
			}
		}
		return $targetObject;
	}
	/**
	 * @param object $object
	 * @return ReflectionProperty[]
	 */
	public static function getPublicPropertiesOfObject(object $object): array{
		$names = [];
		if($object instanceof stdClass){
			foreach(get_object_vars($object) as $key => $value){
				$names[] = $key;
			}
		} else{
			$reflect = new ReflectionClass($object);
			$publicProperties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
			foreach($publicProperties as $publicProperty){
				$names[] = $publicProperty->getName();
			}
		}
		return $names;
	}
	/**
	 * @param array|object $array
	 * @return object
	 */
	public static function convertToObject($array): ?object{
		if($array == "null" || $array === null || $array === ""){
			return null;
		}
		$original = $array;
		if(is_object($array)){
			return $array;
		}
		if(QMStr::isJson($array)){
			$array = json_decode($array);
		}
		$result = json_decode(json_encode($array));
		if(is_string($result)){
			QMLog::error("After json_decode json_encode array became this string: $result
            Original Object was: " . \App\Logging\QMLog::print_r($original, true));
		}
		return $result;
	}
	/**
	 * @param $object
	 * @param array $legacyPropertyMap
	 * @param bool $convertToCamelCaseFirst
	 * @return mixed
	 * @internal param $ [] $array
	 * @internal param $ [] $legacyKeyMap
	 */
	public static function replaceLegacyPropertiesInObject($object, array $legacyPropertyMap = [],
		bool $convertToCamelCaseFirst = true){
		try {
			foreach($object as $key => $value){
				if(is_numeric($key)){
					return $object;
				}
			}
		} catch (Exception $e) {
			ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
		}
		$object = self::convertToObject($object);
		$legacyPropertyMap = array_merge($legacyPropertyMap, self::getGlobalLegacyProperties());
		if($convertToCamelCaseFirst){
			$object = QMStr::convertPropertiesToCamelCase($object);
		}
		foreach($legacyPropertyMap as $legacyProperty => $currentProperty){
			if(isset($object->$legacyProperty) && !isset($object->$currentProperty)){
				$object->$currentProperty = $object->$legacyProperty;
			}
		}
		foreach($legacyPropertyMap as $legacyProperty => $currentProperty){
			unset($object->$legacyProperty);
		}
		return $object;
	}
	/**
	 * @param string|null $legacyProperty
	 * @return array|string
	 */
	public static function getGlobalLegacyProperties(string $legacyProperty = null){
		$map = [  // legacy => current
			'abbreviatedUnitName' => 'unitAbbreviatedName',
		];
		if($legacyProperty){
			if(isset($map[$legacyProperty])){
				return $map[$legacyProperty];
			}
			return $legacyProperty;
		}
		return $map;
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function convertXmlStringToJsonWithoutDashesInProperties(string $string): string{
		$xml = simplexml_load_string($string);
		$json = json_encode($xml);
		$json = str_replace('-', '_', $json);
		//$object = json_decode($json);
		return $json;
	}
	/**
	 * @param array|object $object
	 * @return array
	 */
	public static function getNonNullValuesWithCamelKeys($object): array{
		if(!$object){
			return [];
		}
		$array = [];
		foreach($object as $key => $value){
			if($value !== null && is_string($key)){
				$camel = QMStr::camelize($key);
				$array[$camel] = QMStr::decodeIfJson($value);
			}
		}
		return $array;
	}
	/**
	 * @param $object
	 * @return bool
	 */
	public static function isMongoOrStdClass($object): bool{
		return $object instanceof BSONDocument || $object instanceof stdClass;
	}
	/**
	 * @param object $haystackObject
	 * @param array|string $snakeOrCamelCaseKeysArray
	 * @param null $default
	 * @return mixed
	 */
	public static function getValueOfFirstMatchingProperty(object $haystackObject, $snakeOrCamelCaseKeysArray,
		$default = null){
		return QMArr::getValue($haystackObject, $snakeOrCamelCaseKeysArray, $default);
	}
	/**
	 * @param $obj
	 * @param array|string $properties
	 * @param null $default
	 * @return mixed
	 */
	public static function get($obj, $properties, $default = null){
		if(is_string($properties)){
			$properties = [$properties];
		}
		foreach($properties as $p){
			if(property_exists($obj, $p) && $obj->$p !== null){
				return $obj->$p;
			}
		}
		return $default;
	}
	/**
	 * @param $object
	 * @return mixed|stdClass|void
	 */
	public static function toStdClassIfNecessary($object): stdClass{
		if($object && is_string($object)){
			$object = json_decode($object, false);
		}
		if(!isset($object)){
			$object = new stdClass();
		}
		if($object instanceof BSONDocument){
			$object = json_decode(json_encode($object), false);
		}
		if(is_array($object)){
			$object = json_decode(json_encode($object));
		}
		return $object;
	}
	/**
	 * @param $object
	 * @param $defaults
	 * @return mixed
	 */
	public static function setDefaultProperties($object, $defaults){
		foreach($defaults as $key => $defaultValue){
			if(!isset($object->$key)){
				$object->$key = $defaultValue;
			}
		}
		return $object;
	}
	/**
	 * @param object $object
	 * @param string $propertyName
	 * @return mixed
	 */
	public static function getPropertyValueSnakeInsensitive(object $object, string $propertyName){
		$val = $object->$propertyName ?? null;
		if($val === null){
			$snake = QMStr::snakize($propertyName);
			if(isset($object->$snake)){
				$val = $object->$snake;
			}
		}
		return $val;
	}
	/**
	 * @param string $type
	 * @param string $absPath
	 * @return array
	 */
	public static function instantiateAllModelsInFolder(string $type, string $absPath): array{
		if(isset(self::$instantiated[$absPath])){
			return self::$instantiated[$absPath];
		}
		$instances = $files = [];
		$fileInfoArray = FileFinder::listFilesRecursively($absPath);
		foreach($fileInfoArray as $fileInfo){
			$fileName = $fileInfo->getFileName();
			if(strpos($fileName, '.bak') !== false){
				continue;
			}
			$filePath = $fileInfo->getRealPath();
			$class = FileHelper::get_class_from_file($filePath);
			$instance = new $class;
			$nameOrId = $instance->name ?? $instance->id;  // When we create a new file we don't know the id yet
			if(empty($nameOrId)){
				QMLog::info("No nameOrId in $instance " . $class . " file");
			}
			if(isset($instances[$nameOrId])){
				le("Duplicate $type nameOrId $nameOrId in $fileName and " . $files[$nameOrId]);
			}
			//$instance->filePath = $filePath; // What is that for?
			$files[$nameOrId] = $filePath;
			$instances[$nameOrId] = $instance;
		}
		return self::$instantiated[$absPath] =
			array_values($instances);  // json_encodes array to object if we have string keys
	}
	/**
	 * @param $object
	 * @param array $stack
	 * @return object
	 */
	public static function remove_recursive_circular_references(&$object, array &$stack = []){
		if((is_object($object) || is_array($object)) && $object){
			if ((is_object($object) || is_array($object)) && $object) {
				if (!in_array($object, $stack, true)) {
					$stack[] = $object;
					foreach ($object as &$subobject) {
						self::remove_recursive_circular_references($subobject, $stack);
					}
				} else {
					$object = "***RECURSION***";
				}
			}
			return $object;
		}
		return $object;
	}
	/**
	 * @param string $key
	 * @param $object
	 */
	public static function list_unserializable_properties_recursive(string $key, &$object){
		if($object && (is_object($object) || is_array($object))){
			foreach($object as $subKey => &$subObject){
				QMLog::info("Checking $key -> $subKey");
				try {
					$serial = serialize($subObject);
				} catch (\Throwable $e) {
					QMLog::info("$key -> $subKey no serializable ");
				}
				self::list_unserializable_properties_recursive($subKey, $subObject);
			}
		}
	}
	/**
	 * @param string $type
	 * @param string|null $parentFolder
	 * @return string
	 */
	public static function getPathToHardCodedConstantFiles(string $type, string $parentFolder = null): string{
		$pluralType = str_replace("QM", "", $type);
		$pluralType = QMStr::pluralize($pluralType);
		$absPath = FileHelper::absPath("app/");
		if($parentFolder){
			$absPath .= "$parentFolder/";
		}
		$absPath .= $pluralType;
		return $absPath;
	}
}
