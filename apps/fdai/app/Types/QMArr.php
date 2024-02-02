<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Types;
use App\Exceptions\BadRequestException;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Utils\APIHelper;
use App\Utils\Stats;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonException;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/** Class ArrayHelper
 * @package App\Utils
 */
class QMArr {
	public const WHERE_MAX = 'max';
	public const OPERATOR_GT = '(gt)';
	public const OPERATOR_LT = '(lt)';
	public const OPERATOR_GTE = '(gte)';
	public const OPERATOR_LTE = '(lte)';
	public const OPERATOR_NE = '(ne)';
	/**
	 * @param array $arr
	 * @param bool $unset
	 * @return array
	 * @noinspection PhpUnused
	 */
	public static function getAndRemoveFirstElementOfAssociativeArray(array &$arr, bool $unset = true): array{
		$val = reset($arr);
		$key = key($arr);
		//$ret = array( $key => $val );
		if($unset){
			unset($arr[$key]);
		}
		return $val;
	}
	/**
	 * @param $element
	 * @return array
	 */
	public static function toArray($element): array{
		$original = $element;
		if(!$element){
			return [];
		}
		if($element === "[]"){
			return [];
		}
		if(is_array($element)){
			return $element;
		}
		if(is_object($element) && method_exists($element, 'toArray')){
			return $element->toArray();
		}
		if(is_string($element)){
			$decoded = json_decode($element, true);
			if($decoded === null){
				throw new BadRequestException("This is not valid json: $original");
			}
			if(is_string($decoded)){ // double encoded?
				$decoded = json_decode($decoded, true);
				if(!$decoded || is_string($decoded)){
					le("Could not decode $original");
				}
			}
			return $decoded;
		}
		$decoded = json_decode(json_encode($element), true);
		if(!$decoded){
			try {
				throw new JsonException("Got empty array after trying to convert this " . get_class($element) .
					" to array");
			} catch (\Throwable $e) {
				le($e);
				throw new \LogicException();
			}
		}
		return $decoded;
	}
	/**
	 * @param $array
	 * @return mixed
	 */
	public static function convertKeysFromCamelToSnakeCase($array): array{
		$newArray = [];
		foreach($array as $key => $value){
			$snake = QMStr::snakize($key);
			$newArray[$snake] = $value;
		}
		return $newArray;
	}
	/**
	 * @param string $propertyName
	 * @param $value
	 * @param $array
	 * @return array
	 */
	public static function getNotMatching(string $propertyName, $value, $array): array{
		return self::getElementsWithPropertyMatching($propertyName . '(ne)', $value, $array);
	}
	/**
	 * @param string $propertyName
	 * @param $whereValue
	 * @param array $array
	 * @return array
	 */
	public static function getElementsWithPropertyMatching(string $propertyName, $whereValue, array $array): array{
		$matches = [];
		if(stripos($whereValue, self::OPERATOR_GT) === 0){
			$operator = self::OPERATOR_GT;
			$whereValue = str_replace(self::OPERATOR_GT, '', $whereValue);
		} elseif(stripos($whereValue, self::OPERATOR_LT) === 0){
			$operator = self::OPERATOR_LT;
			$whereValue = str_replace(self::OPERATOR_LT, '', $whereValue);
		} elseif(stripos($whereValue, self::OPERATOR_NE) === 0){
			$operator = self::OPERATOR_NE;
			$whereValue = str_replace(self::OPERATOR_NE, '', $whereValue);
		} else{
			$operator = '==';
		}
		$numeric = false;
		if(is_numeric($whereValue)){
			$numeric = true;
			$whereValue = (float)$whereValue; // Why?  It prevents finding int's?
		}
		foreach($array as $element){
			if(!$element){
				continue;
			} elseif(method_exists($element, 'getAttribute')){
				$currentValue = $element->getAttribute($propertyName);
			} elseif(is_array($element)){
				$currentValue = $element[$propertyName];
			} elseif(is_object($element)){
				if(property_exists($element, $propertyName)){
					$currentValue = $element->$propertyName;
				} else{
					$camel = QMStr::camelize($propertyName);
					$currentValue = $element->$camel;
				}
			} else{
				le("Not an object or array: " . \App\Logging\QMLog::print_r($element, true));
			}
			if($operator === self::OPERATOR_GT){
				if($currentValue > $whereValue){
					$matches[] = $element;
				}
			} elseif($operator === self::OPERATOR_LT){
				if($currentValue < $whereValue){
					$matches[] = $element;
				}
			} elseif($operator === self::OPERATOR_NE){
				if($currentValue !== $whereValue){
					$matches[] = $element;
				}
			} elseif($currentValue === null){
				if($currentValue === $whereValue){
					$matches[] = $element;
				}
			} elseif($currentValue == $whereValue){
				$matches[] = $element;
			} elseif($numeric && Stats::floatsEqual($currentValue, $whereValue)){
				$matches[] = $element;
			}
		}
		return $matches;
	}
	/**
	 * @param array $data
	 * @param array $wheres
	 * @return array
	 */
	public static function getMatchingQbWhereClauses(array $data, array $wheres): array{
		$matches = [];
		foreach($data as $datum){
			$match = $datum;
			foreach($wheres as $where){
				$column = $where['column'];
				$operator = $where['operator'];
				$whereValue = $where['value'];
				$currentValue = $datum->$column;
				if($operator === "="){
					if($whereValue !== $currentValue){
						$match = false;
						break;
					}
				} elseif($operator === ">"){
					if($currentValue <= $whereValue){
						$match = false;
						break;
					}
				} elseif($operator === "<"){
					if($currentValue >= $whereValue){
						$match = false;
						break;
					}
				} elseif($operator === "<="){
					if($currentValue > $whereValue){
						$match = false;
						break;
					}
				} elseif($operator === ">="){
					if($currentValue < $whereValue){
						$match = false;
						break;
					}
				}
			}
			if($match){
				$matches[] = $match;
			}
		}
		return $matches;
	}
	/**
	 * @param array $arr
	 * @return mixed
	 */
	public static function firstValue(array &$arr){
		if(!$arr){
			throw new BadRequestHttpException("Please provide array to getFirstValueOfAssociativeArray!");
		}
		$val = reset($arr);
		return $val;
	}
	/**
	 * @param array $arr
	 * @param bool $unset
	 * @return mixed
	 */
	public static function getAndRemoveLastElement(array &$arr, bool $unset = true){
		$value = end($arr);
		$key = key($arr);
		if($unset){
			unset($arr[$key]);
		}
		return $value;
	}
	/**
	 * @param array $arr
	 * @return mixed
	 */
	public static function getLastElement(array &$arr): array{
		return self::getAndRemoveLastElement($arr, false);
	}
	/**
	 * @param array $array
	 * @return int|null|string
	 */
	public static function getKeyOfFirstElement(array $array){
		reset($array);
		return key($array);
	}
	/**
	 * @param array $array
	 * @return int|null|string
	 */
	public static function getValueOfFirstElement(array $array){
		$value = $array[self::getKeyOfFirstElement($array)];
		return $value;
	}
	/**
	 * @param \ArrayAccess|array $array
	 * @param string $message
	 * @return string
	 */
	public static function listKeys($array, string $message = "Array Keys Are: "): string{
		$keys = array_keys((array)$array);
		sort($keys);
		$message .= "\n" . implode("\n\t- ", $keys);
		return $message;
	}
	/**
	 * @param array $array
	 * @return int|null|string
	 */
	public static function getKeyOfLastElement(array $array){
		end($array);
		return key($array);
	}
	/**
	 * @param array $array
	 * @param string $name
	 */
	public static function verifyKeysInAscendingOrder(array $array, string $name = 'Array'){
		if(self::getKeyOfFirstElement($array) > self::getKeyOfLastElement($array)){
			le("$name keys should be ascending!");
		}
	}
	/**
	 * @param array $array
	 * @return array
	 */
	public static function sortByKeysAscending(array &$array): array{
		ksort($array);
		return $array;
	}
	/**
	 * @param array $array
	 * @return array
	 */
	public static function sortByKeysDescending(array &$array): array{
		if(!$array){
			le("No array given!");
		}
		krsort($array);
		return $array;
	}
	/**
	 * @param array $array
	 * @return int
	 */
	public static function getSmallestKey(array $array){
		$smallest = self::getKeyOfFirstElement($array);
		foreach($array as $key => $value){
			if($key < $smallest){
				$smallest = $key;
			}
		}
		return $smallest;
	}
	/**
	 * @param array $array
	 * @return int
	 */
	public static function getLargestKey(array $array){
		$largest = self::getKeyOfFirstElement($array);
		foreach($array as $key => $value){
			if($key > $largest){
				$largest = $key;
			}
		}
		return $largest;
	}
	/**
	 * @param array $array
	 * @param bool $recursive
	 * @return array
	 */
	public static function flattenArray(array $array, bool $recursive): array{
		$flat = [];
		foreach($array as $key => $value){
			if(is_array($value)){
				$flat = array_merge($flat, $value);
			} else{
				$flat[$key] = $value;
			}
		}
		if($recursive && is_array(self::firstValue($flat))){
			$flat = self::flattenArray($flat, $recursive);
		}
		return $flat;
	}
	/**
	 * @param object[] $array
	 * @param array $except
	 * @return object[]
	 */
	public static function unsetNullPropertiesOfObjectsInArray(array $array, array $except = []): array{
		foreach($array as $key => $value){
			if($except && in_array($key, $except)){
				continue;
			}
			$array[$key] = ObjectHelper::unsetNullProperties($value);
		}
		return $array;
	}
	/**
	 * @param object[] $array
	 * @param array $except
	 * @return object[]
	 */
	public static function unsetNullAndEmptyArrayOrStringProperties(array|object $array, array $except = []): array{
		foreach($array as $key => $value){
			if($except && in_array($key, $except)){
				continue;
			}
			$array[$key] = ObjectHelper::unsetNullAndEmptyArrayOrStringProperties($value);
		}
		return $array;
	}
	/**
	 * @param [] $array
	 * @param string $property
	 * @return array
	 */
	public static function getAllValuesForKeyOrProperty($array, string $property): array{
		$values = [];
		foreach($array as $item){
			if(!is_array($item)){
				$item = json_decode(json_encode($item), true);
			}
			$values[] = $item[$property];
		}
		return $values;
	}
	/**
	 * @param array $array
	 * @param string $property
	 * @return array
	 */
	public static function sortAssociativeArrayByFieldDescending(array $array, string $property): array{
		$profit = [];
		foreach($array as $key => $row){
			if(!isset($row[$property])){
				le("$property not set!", $row);
			}
			$profit[$key] = $row[$property];
		}
		array_multisort($profit, SORT_DESC, $array);
		return $array;
	}
	/**
	 * @param array $array
	 * @param string $propertyName
	 * @param null $fallbackPropertyName
	 * @return array
	 */
	public static function sortByProperty(array $array, string $propertyName, $fallbackPropertyName = null): array{
		if(empty($array)){
			return $array;
		}
		$reverse = strpos($propertyName, '-') === 0;
		if($reverse){
			$propertyName = Str::replaceFirst('-', '', $propertyName);
			if($fallbackPropertyName){
				$fallbackPropertyName = Str::replaceFirst('-', '', $fallbackPropertyName);
			}
		}
		$propertyNameOrFallback = self::validateSortField($array, $propertyName, $fallbackPropertyName);
		if(!$propertyNameOrFallback){
			QMLog::error("$propertyName not found!");
			return $array;
		}
		if($reverse){
			self::sortDescending($array, $propertyNameOrFallback);
			if(count($array) > 1){
				$first = $array[0]->$propertyNameOrFallback;
				$second = $array[1]->$propertyNameOrFallback;
				if($first < $second){
					le("Sorting not working!");
				}
			}
		} else{
			self::sortAscending($array, $propertyNameOrFallback);
		}
		return $array;
	}
	/**
	 * @param array $array
	 * @param string $property
	 */
	public static function sortDescending(array &$array, string $property): void{
		usort($array, function($a, $b) use ($property){
			$bValue = $b->$property;
			$aValue = $a->$property;
			if(is_int($bValue) && is_int($aValue)){
				$result = $aValue < $bValue;
			} else{
				if(!$bValue){
					QMLog::error("No value for $property to sort by!", ['b' => $b,]);
					$bValue = "";
				}
				if(!$aValue){
					QMLog::error("No value for $property to sort by!", ['a' => $a,]);
					$aValue = "";
				}
				$result = strcmp($bValue, $aValue);
			}
			return (int)$result;
		});
	}
	/**
	 * @param array $arr
	 */
	public static function sortAssociativeArray(array &$arr): void{ asort($arr); }
	/**
	 * @param array $array
	 * @param string $property
	 * @param null $fallback
	 */
	public static function sortAscending(array &$array, string $property, $fallback = null){
		usort($array, function($a, $b) use ($property, $fallback){
			if(!property_exists($a, $property) && $fallback !== null){
				$aValue = $fallback;
				//throw new \LogicException("No $property property on ".json_encode($a));
			} else{
				if(!isset($a->$property)){
					$a->$property = $fallback;
				} // Property existed but was probably deleted during compression
				$aValue = $a->$property;
			}
			if(!property_exists($b, $property) && $fallback !== null){
				$bValue = $fallback;
				//throw new \LogicException("No $property property on ".json_encode($b));
			} else{
				if(!isset($b->$property)){
					$b->$property = $fallback;
				} // Property existed but was probably deleted during compression
				$bValue = $b->$property;
			}
			if(is_int($bValue) && is_int($aValue)){
				$result = $aValue > $bValue;
			} else{
				$result = strcmp($aValue, $bValue);
			}
			return$result ? 1 : -1;
		});
	}
	/**
	 * @param $propertyName
	 * @param $array
	 * @param $lessThan
	 * @return mixed
	 */
	public static function getElementWithHighestPropertyBeneath($propertyName, $array, $lessThan): ?array{
		$sorted = self::sortAssociativeArrayByFieldDescending($array, $propertyName);
		foreach($sorted as $item){
			if(is_array($item)){
				if($lessThan === null || $item[$propertyName] < $lessThan){
					return $item;
				}
			} elseif($lessThan === null || $item->$propertyName < $lessThan){
				return $item;
			}
		}
		return null;
	}
	/**
	 * @param [] $array
	 * @param [] $legacyKeyMap
	 * @return mixed
	 */
	public static function replaceLegacyKeys($array, $legacyKeyMap = null){
		if(!$legacyKeyMap){
			$legacyKeyMap = APIHelper::getGlobalLegacyRequestParams();
		}
		foreach($legacyKeyMap as $legacyKey => $currentKey){
			if(isset($array[$legacyKey]) && !isset($array[$currentKey])){
				$array[$currentKey] = $array[$legacyKey];
			}
			if(isset($array['sort'])){
				$sortFiledWithoutSign = str_replace("-", "", $array['sort']);
				if($sortFiledWithoutSign == $legacyKey){
					$array['sort'] = str_replace($legacyKey, $currentKey, $array['sort']);
				}
			}
		}
		foreach($legacyKeyMap as $legacyKey => $currentKey){
			unset($array[$legacyKey]);
		}
		return $array;
	}
	/**
	 * @param object|array $haystack
	 * @param string $key
	 * @return mixed
	 */
	public static function getValueForSnakeOrCamelCaseKey($haystack, string $key){
		$variations = QMStr::snakeCamelLowercaseVariations($key);
		$isObj = is_object($haystack);
		$val = null;
		foreach($variations as $variation){
			if($isObj){
				if(property_exists($haystack, $variation)){
					$val = $haystack->$variation;
				}
			} else{
				if(isset($haystack[$variation])){
					$val = $haystack[$variation];
				}
			}
			if($val === ""){
				$val = null;
			}
		}
		return $val;
	}
	/**
	 * @param object|array $haystack
	 * @param string $key
	 * @return bool
	 */
	public static function keyExistsForSnakeOrCamelCaseKey($haystack, string $key): bool{
		$variations = QMStr::snakeCamelLowercaseVariations($key);
		$isObj = is_object($haystack);
		foreach($variations as $variation){
			$exists = $isObj ? property_exists($haystack, $variation) : array_key_exists($variation, $haystack);
			if($exists){
				return true;
			}
		}
		return false;
	}
	/**
	 * @param array|object $haystack
	 * @param array|string $names
	 * @param null $default
	 * @return mixed
	 */
	public static function getValue($haystack, $names, $default = null){
		if(is_string($names)){
			$names = [$names];
		}
		foreach($names as $key){
			if($haystack instanceof BaseModel){
				$snake = QMStr::snakize($key);
				$value = $haystack->getRawAttribute($snake);
			} else{
				$value = self::getValueForSnakeOrCamelCaseKey($haystack, $key);
			}
			if($value !== null){
				return $value;
			}
		}
		return $default;
	}
	/**
	 * @param array|object $haystack
	 * @param array|string $names
	 * @return mixed
	 */
	public static function keyExists($haystack, $names): bool{
		if(is_string($names)){
			$names = [$names];
		}
		foreach($names as $key){
			if($haystack instanceof BaseModel){
				$snake = QMStr::snakize($key);
				$exists = $haystack->attributePresent($snake);
			} else{
				$exists = self::keyExistsForSnakeOrCamelCaseKey($haystack, $key);
			}
			if($exists){
				return true;
			}
		}
		return false;
	}
	/**
	 * @param array|object $haystack
	 * @param string $needle
	 * @return mixed|null
	 */
	public static function recursiveFind(array|object $haystack, string $needle){
		if(!is_array($haystack)){
			$haystack = json_decode(json_encode($haystack), true);
		}
		$iterator = new RecursiveArrayIterator($haystack);
		$recursive = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
		foreach($recursive as $key => $value){
			if($key === $needle){
				return $value;
			}
		}
		return null;
	}
	/**
	 * @param array $haystack
	 * @param array|string $snakeOrCamelCaseKeys
	 * @param null $default
	 * @return mixed
	 */
	public static function getValueRecursive(array $haystack, $snakeOrCamelCaseKeys, $default = null): ?string{
		if(is_string($snakeOrCamelCaseKeys)){
			$snakeOrCamelCaseKeys = [$snakeOrCamelCaseKeys];
		}
		$value = self::getValue($haystack, $snakeOrCamelCaseKeys);
		if($value){
			return $value;
		}
		foreach($haystack as $value){
			if(is_array($value) || is_object($value)){
				$value = self::getValue($value, $snakeOrCamelCaseKeys);
				if($value){
					return $value;
				}
			}
		}
		return $default;
	}
	/**
	 * @param array $array
	 * @param array $properties
	 */
	public static function sortByTwoProperties(array &$array, array $properties){
		usort($array, function($a, $b) use ($properties){
			$property1 = $properties[0];
			$property2 = $properties[1];
			if($a->$property1 == $b->$property1){
				return $a->$property2 < $b->$property2 ? 1 : -1;
			}
			return $a->$property1 < $b->$property1 ? 1 : -1;
		});
	}
	/**
	 * @param array $params
	 * @param array $array
	 * @return array
	 */
	public static function getElementsMatchingRequestParams(array $params, array $array): array{
		$matches = $array;
		foreach($params as $key => $value){
			if($key === 'limit'){
				continue;
			}
			$matches = self::getElementsWithPropertyMatching($key, $value, $matches);
			if(!$matches){
				QMLog::debug("$key $value filtered out all matches");
			}
		}
		return $matches;
	}
	/**
	 * @param string $propertyName
	 * @param $value
	 * @param array|Collection $haystack
	 * @return mixed|null
	 */
	public static function firstWhere(string $propertyName, $value, $haystack){
		if($haystack instanceof Collection){$haystack = $haystack->all();}
		$matches = self::getElementsWithPropertyMatching($propertyName, $value, $haystack);
		if(!empty($matches)){
			return $matches[0];
		}
		return null;
	}
	/**
	 * @param array|Collection $arr
	 * @return mixed
	 */
	public static function first($arr){
		if(!$arr){
			return null;
		}
		if(is_array($arr)){
			foreach($arr as $item){
				return $item;
			}
		}
		return $arr->first();
	}
	/**
	 * @param array $array
	 * @param string $key
	 * @return mixed
	 */
	public static function unsetCamelOrSnakeCaseKey(array $array, string $key): array{
		unset($array[$key]);
		$camel = QMStr::camelize($key);
		$snake = QMStr::snakize($key);
		unset($array[$camel]);
		unset($array[$snake]);
		return $array;
	}
	/**
	 * @param string $needle
	 * @param array $haystack
	 * @return bool
	 */
	public static function inArrayCaseInsensitive(string $needle, array $haystack): bool{
		$needle = strtolower($needle);
		foreach($haystack as $one){
			if($needle === strtolower($one)){
				return true;
			}
		}
		return false;
	}
	/**
	 * @param array $array
	 * @return array
	 */
	public static function arrayUniqueCaseInsensitive(array $array): array{
		$unique = [];
		foreach($array as $item){
			if(!self::inArrayCaseInsensitive($item, $unique)){
				$unique[] = $item;
			}
		}
		return $unique;
	}
	/**
	 * @param array $array
	 * @return array
	 */
	public static function jsonEncodeObjectsAndArrays(array $array): array{
		foreach($array as $key => $value){
			if(is_array($value) || is_object($value)){
				$array[$key] = json_encode($value);
			}
		}
		return $array;
	}
	/**
	 * @param array $array
	 * @param string $propertyName
	 * @param array $valuesToAvoid
	 * @return array
	 */
	public static function getWithPropertyNotInArray(array $array, string $propertyName, array $valuesToAvoid): array{
		$nonMatches = [];
		foreach($array as $item){
			if(!in_array($item->$propertyName, $valuesToAvoid, true)){
				$nonMatches[] = $item;
			}
		}
		return $nonMatches;
	}
	/**
	 * @param array $array
	 * @param string $propertyName
	 * @param $fallbackPropertyName
	 * @return string
	 */
	private static function validateSortField(array $array, string $propertyName, $fallbackPropertyName = null){
		if(!property_exists($array[0], $propertyName)){
			if($fallbackPropertyName && isset($array[0]->$fallbackPropertyName)){
				QMLog::error("Cannot sort by $propertyName, sorting by $fallbackPropertyName instead");
				$propertyName =
					(strpos($propertyName, '-') !== false) ? '-' . $fallbackPropertyName : $fallbackPropertyName;
			} else{
				$message = "Cannot sort by $propertyName!  Available sort fields are: ";
				foreach($array[0] as $key => $value){
					$message .= $key . " ";
				}
				//throw new \LogicException($message);
                QMLog::error("Cannot sort by $propertyName, sorting by $fallbackPropertyName instead");
				return false;
			}
		}
		return $propertyName;
	}
	/**
	 * @param array $array
	 * @return array
	 */
	public static function unsetKeysWithDots(array $array): array{
		foreach($array as $key => $value){
			if(stripos($key, '.') !== false){
				unset($array[$key]);
			}
		}
		return $array;
	}
	/**
	 * @param array|Collection $array
	 * @param string $propertyName
	 * @return array
	 */
	public static function getUniqueByProperty($array, string $propertyName = 'id'): array{
		$unique = [];
		foreach($array as $item){
            try {
                $unique[$item->$propertyName] = $item;
            } catch (\Throwable $e) {
                le($e);
            }
		}
		return array_values($unique);
	}
	/**
	 * @param string $uniqueKey
	 * @param array $preferredArray
	 * @param array $secondaryArray
	 * @return array
	 */
	public static function arrayMergeUnique(string $uniqueKey, array $preferredArray, array $secondaryArray): array{
		$existing = [];
		foreach($preferredArray as $item){
			$existing[] = $item->$uniqueKey;
		}
		foreach($secondaryArray as $item){
			if(!in_array($item->$uniqueKey, $existing, true)){
				$preferredArray[] = $item;
			}
		}
		return $preferredArray;
	}
	/**
	 * @param array $array
	 * @param array $params
	 * @return array
	 */
	public static function filter(array $array, array $params): array{
		return self::getElementsMatchingRequestParams($params, $array);
	}
	/**
	 * @param $variable
	 * @return bool
	 */
	public static function isNonAssociativeArray($variable): bool{
		if(is_array($variable)){
			foreach($variable as $key => $value){
				if(is_int($key)){
					return true;
				}
			}
		}
		return false;
	}
	/**
	 * @param $associativeArray
	 * @return array
	 */
	public static function getValuesAsArray($associativeArray): array{
		$values = [];
		foreach($associativeArray as $value){
			$values[] = $value;
		}
		return $values;
	}
	/**
	 * @param $data
	 * @return array
	 */
	public static function alphabetizeObjectToArray($data): array{
		foreach($data as $key => $value){
			$array[$key] = $value;
		}
		ksort($array);
		return $array;
	}
	/**
	 * @param $a
	 * @return bool
	 */
	public static function alphabetizeKeysRecursive(&$a): bool{
		if(!is_array($a)){
			return false;
		}
		ksort($a);
		foreach($a as $k => $v){
			self::alphabetizeKeysRecursive($a[$k]);
		}
		return true;
	}
	/**
	 * Pluck an array of values from an array. (Only for PHP 5.3+)
	 * @param  $array - data
	 * @param  $key - value you want to pluck from array
	 * @return array
	 */
	public static function pluckColumn(array $array, string $key): array{
		return array_map(function($v) use ($key){
			return is_object($v) ? $v->$key : $v[$key];
		}, $array);
	}
	/**
	 * @param array $arr
	 * @param int|null $depth
	 * @return array
	 */
	public static function flatten(array $arr, int $depth = null): array{
		if($depth){
			return Arr::flatten($arr, $depth);
		}
		$it = new RecursiveIteratorIterator(new RecursiveArrayIterator($arr));
		return iterator_to_array($it);
	}
	/**
	 * @param array|Collection $arr
	 * @param string $propertyName
	 * @return mixed
	 */
	public static function max($arr, string $propertyName){
		if(is_array($arr)){
			$values = self::pluckColumn($arr, $propertyName);
			return max($values);
		}
		return $arr->max($propertyName);
	}
	/**
	 * @param array|Collection $arr
	 * @param string $propertyName
	 * @return mixed
	 */
	public static function min($arr, string $propertyName){
		if(is_array($arr)){
			$values = self::pluckColumn($arr, $propertyName);
			return min($values);
		}
		return $arr->min($propertyName);
	}
	public static function removeNullsObjectsAndArrays(array $first): array{
		$keep = [];
		foreach($first as $key => $value){
			if(is_null($value) || is_object($value) || is_array($value)){
				continue;
			}
			$keep[$key] = $value;
		}
		return $keep;
	}
	public static function removeNulls(array $arr): array{
		$keep = [];
		foreach($arr as $key => $value){
			if(is_null($value)){
				continue;
			}
			$keep[$key] = $value;
		}
		return $keep;
	}
	public static function unsetNullKeys(array $arr): array{
		return self::removeNulls($arr);
	}
	public static function removeNullsAndEmptyStrings(array $arr): array{
		$keep = [];
		foreach($arr as $key => $value){
			if(is_null($value) || $value === ""){
				continue;
			}
			$keep[$key] = $value;
		}
		return $keep;
	}
	public static function minVal(array $arr): ?float{
		$values = self::removeNulls($arr);
		if(!$values){
			return null;
		}
		$min = min($values);
		return $min;
	}
	public static function maxVal(array $arr): ?float{
		$values = self::removeNulls($arr);
		if(!$values){
			return null;
		}
		$min = min($values);
		return $min;
	}
	public static function snakize(array $data): array{
		return self::convertKeysFromCamelToSnakeCase($data);
	}
	public static function mergeSnakizedNotNull(array $one, array $two): array{
		$one = self::removeNulls($one);
		$two = self::removeNulls($two);
		$one = self::snakize($one);
		$two = self::snakize($two);
		return array_merge($one, $two);
	}
	public static function whereByParams(array $params, array $all): array{
		$matches = $all;
		foreach($params as $key => $value){
			$matches = QMArr::where($key, $value, $matches);
		}
		return $matches;
	}
	/**
	 * @param array $params
	 * @param array $all
	 * @return mixed|null
	 */
	public static function firstMatch(array $params, array $all){
		foreach($all as $key => $one){
            if(!$one){
                QMLog::error("Key $key is null");
                continue;
            }
			if($matches = self::whereByParams($params, [$one])){
				return $matches[0];
			}
		}
		return null;
	}
	/**
	 * @param string $property
	 * @param $val
	 * @param array $arr
	 * @return array
	 */
	public static function where(string $property, $val, array $arr): array{
		$matches = [];
		foreach($arr as $v){
			if(is_object($v)){
				if($v->$property === $val){
					$matches[] = $v;
				}
			} elseif(is_array($v)){
				if($v[$property] === $val){
					$matches[] = $v;
				}
			} else{
				QMLog::info("need array or object but got " . gettype($v));
			}
		}
		return $matches;
	}
	public static function whereUserId(int $userId, array $arr): array{
		return self::where('userId', $userId, $arr);
	}
	public static function indexBy(array $unIndexed, string $key): array{
		$indexed = [];
		foreach($unIndexed as $item){
			$newKey = self::pluckValue($item, $key);
			if(TimeHelper::isCarbon($newKey)){
				/** @var CarbonInterface $newKey */
				$newKey = $newKey->toDateTimeString();
			}
			$indexed[$newKey] = $item;
		}
		return $indexed;
	}
	public static function indexAscending(array $unIndexed, string $key): array{
		$indexed = self::indexBy($unIndexed, $key);
		ksort($indexed);
		return $indexed;
	}
	public static function indexDescending(array $unIndexed, string $key): array{
		$indexed = self::indexBy($unIndexed, $key);
		krsort($indexed);
		return $indexed;
	}
	/**
	 * @param $haystack
	 * @param $names
	 * @param null $default
	 * @return mixed|null
	 */
	public static function pluckValue($haystack, $names, $default = null){
		return self::getValue($haystack, $names, $default);
	}
	public static function removeEmpty(array $arr): array{
		return array_filter($arr);
	}
	/**
	 * @param array $arr
	 * @return array
	 */
	public static function removeEmptyStrings(array $arr): array{
		return collect($arr)->filter(function($one){
			return $one !== "";
		})->all();
	}
	public static function mergeRemoveEmptyAndDuplicates(array $arr, array $arr2): array{
		$merged = array_merge($arr, $arr2);
		$notEmpty = QMArr::removeEmpty($merged);
		return array_values(array_unique($notEmpty));
	}
	/**
	 * @param array|Collection $arr
	 * @return mixed
	 */
	public static function last($arr){
		if(!$arr){
			return null;
		}
		if($arr instanceof Collection){
			return $arr->last();
		}
		return end($arr);
	}
	/**
	 * @param array $inUserUnit
	 * @return float[]
	 */
	public static function uniqueFloats(array $inUserUnit): array{
		$floats = [];
		$inUserUnit = self::removeNulls($inUserUnit);
		foreach($inUserUnit as $value){
			$float = (float)$value;
			if(!in_array($float, $floats)){
				$floats[] = $float;
			}
		}
		return $floats;
	}
	public static function arraysAreEqual(array $a, array $b): bool{
		if(count($a) !== count($b)){
			return false;
		} // check size of both arrays
		foreach($b as $bValue){
			if(!in_array($bValue, $a, true)){
				return false; // check that expected value exists in the array
			}
			if(count(array_keys($a, $bValue, true)) !== count(array_keys($b, $bValue, true))){
				return false; // check that expected value occurs the same amount of times in both arrays
			}
		}
		return true;
	}
	public static function scalarOnly(array $meta): array{
		return array_filter($meta, 'is_scalar');
	}
	public static function inArraySnakeCamelInsensitive(string $needle, array $arr): bool{
		return QMStr::inArraySnakeCamelInsensitive($needle, $arr);
	}
	/**
	 * @param array|Collection $arr
	 * @return bool
	 */
	public static function empty($arr): bool{
		if($arr instanceof Collection){
			return $arr->isEmpty();
		} else{
			return empty($arr);
		}
	}
	/**
	 * @param $items
	 * @param string $key
	 * @return mixed|null
	 */
	public static function getElementWithHighest($items, string $key){
		self::sortDescending($items, $key);
		return self::first($items);
	}
	/**
	 * @param Collection $models
	 * @return array
	 */
	public static function toArrays($models): array{
		$arrays = [];
		foreach($models as $model){
			$arrays[$model->getNameAttribute()] = $model->toNonNullArrayFast();
		}
		return $arrays;
	}
	/**
	 * @param $models
	 * @return string
	 */
	public static function toJsonEncodedArray($models): string{
		$arrays = static::toArrays($models);
		return QMStr::prettyJsonEncode($arrays);
	}
	/**
	 * @param string $variableName
	 * @param $models
	 * @return string
	 */
	public static function toJavascript(string $variableName, $models): string{
		$json = static::toJsonEncodedArray($models);
		$js = "var $variableName = " . $json;
		return $js;
	}
	public static function assertNotNull(array $items, string $propertyName){
		foreach($items as $item){
			if(!isset($item->$propertyName)){
				le("$propertyName should not be null", $item);
			}
		}
	}
	/**
	 * @param Collection|array $arr
	 * @return Collection
	 */
	public static function collect($arr): Collection{
		if(is_array($arr)){
			$arr = collect($arr);
		} elseif(!$arr instanceof Collection){
			le("Please provide collection or array to " . __METHOD__);
			throw new \LogicException();
		}
		return $arr;
	}
	/**
	 * @param array $associative
	 * @return float|int
	 */
	public static function keyOfBiggestValue(array $associative): string{
		if(!$associative){
			le("nothing provided to " . __FUNCTION__);
		}
		arsort($associative);
		return self::firstKey($associative);
	}
	public static function firstKey(array $associative): string{
		$keys = array_keys($associative);
		//QMStr::assertIsString($keys[0]);
		return $keys[0];
	}
	/**
	 * @param string $property
	 * @param string $needle
	 * @param $arr
	 * @return Collection
	 */
	public static function filterWhereLike(string $property, string $needle, $arr): Collection{
		$arr = self::collect($arr);
		$filtered = $arr->filter(fn($item) => strpos($item->$property, $needle) !== false);
		return $filtered;
	}
	/**
	 * @param string $property
	 * @param string $needle
	 * @param $arr
	 * @return Collection
	 */
	public static function filterWhereStartsWith(string $property, string $needle, $arr): Collection{
		$arr = self::collect($arr);
		$filtered = $arr->filter(fn($item) => strpos($item->$property, $needle) === 0);
		return $filtered;
	}
	/**
	 * @param array $links
	 * @return array
	 */
	public static function sortByLengthOfKeysDescending(array $links): array{
		uksort($links, function($a, $b){
			if(strlen($a) == strlen($b)) return 0;
			if(strlen($a) < strlen($b)) return 1;
			return -1;
		});
		return $links;
	}
	/**
	 * @param $arrOrObj
	 * @return array
	 */
	public static function notNullOrEmptyStringValues($arrOrObj): array{
		$notNull = [];
		foreach($arrOrObj as $key => $value){
			if($value !== null && $value !== ""){
				$notNull[$key] = $value;
			}
		}
		return $notNull;
	}
	/**
	 * @param $arrOrObj
	 * @return array
	 */
	public static function notEmptyValues($arrOrObj): array{
		$notNull = [];
		foreach($arrOrObj as $key => $value){
			if($value !== null && $value !== "" && $value !== []){
				$notNull[$key] = $value;
			}
		}
		return $notNull;
	}
	/**
	 * @param $arrOrObj
	 * @return array
	 */
	public static function getStringsAndNumbers($arrOrObj): array{
		/** @var BaseModel $this */
		if($arrOrObj instanceof BaseModel){
			$arrOrObj = $arrOrObj->attributesToArray();
		}
		$stringsAndNumbers = [];
		foreach($arrOrObj as $key => $value){
			if($value == null && $value != ""){
				if(!is_array($value) && !is_object($value)){
					$stringsAndNumbers[$key] = $value;
				}
			}
		}
		return $stringsAndNumbers;
	}
	public static function implodeArrayKeys(array $arr): string{
		$keys = array_keys($arr);
		return implode('\n\t', $keys);
	}
	/**
	 * @param $arr
	 * @return array
	 */
	public static function toStrings($arr): array{
		$strings = [];
		foreach($arr as $item){
			$strings[] = (string)$item;
		}
		return $strings;
	}
	/**
	 * @param array $rows
	 * @return array
	 */
	public static function headerToAssociativeArray(array $rows): array{
	    /* Map Rows and Loop Through Them */
	    $header = array_shift($rows);
	    $csv    = [];
	    foreach($rows as $row) {
		    $csv[] = array_combine($header, $row);
	    }
		return $csv;
    }

    public static function array_diff_recursive($aArray1, $aArray2): array
    {
        $aReturn = [];

        foreach ($aArray1 as $mKey => $mValue) {
            if (array_key_exists($mKey, $aArray2)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = QMArr::array_diff_recursive($mValue, $aArray2[$mKey]);
                    if (count($aRecursiveDiff)) {
                        $aReturn[$mKey] = $aRecursiveDiff;
                    }
                } else {
                    if ($mValue != $aArray2[$mKey]) {
                        $aReturn[$mKey] = $mValue;
                    }
                }
            } else {
                $aReturn[$mKey] = $mValue;
            }
        }
        return $aReturn;
    }
	public static function removeDates(array $expected): array {
		return TimeHelper::stripDatesAndTimes($expected, true);
	}
}
