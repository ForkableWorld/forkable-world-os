<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection ALL */
namespace App\Types;
use App\Computers\ThisComputer;
use App\Exceptions\BlackListedStringException;
use App\Exceptions\InvalidFilePathException;
use App\Exceptions\InvalidStringException;
use App\Exceptions\InvalidUrlException;
use App\Files\FileHelper;
use App\Files\PHP\ConstantGenerator;
use App\Files\PHP\PhpClassFile;
use App\Logging\QMLog;
use App\Models\GlobalVariableRelationship;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Models\User;
use App\Properties\WpPost\WpPostPostNameProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\Model\QMUnit;
use App\Slim\Model\User\QMUser;
use App\UI\HtmlHelper;
use App\Utils\APIHelper;
use App\Utils\QMAPIValidator;
use App\Utils\UrlHelper;
use App\Variables\QMVariableCategory;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use LogicException;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use SplStack;
use stdClass;
use Throwable;

/** Class StringHelper
 * @package App\Slim\Model
 */
class QMStr {
	public const CONTACT_MIKE_FOR_HELP_STRING = "Please contact help@curedao.org if you need any help. ";
	const PREFIXES_TO_REPLACE = [
		"BshafferOAuth" => "OAuth",
		"Bshaffer Oauth" => "OAuth",
		"Oauth" => "OAuth",
		"Wp " => "",
		"Bp " => "",
		"wp_" => "",
		"bshaffer_" => "",
	];
	/**
	 * @var
	 */
	private static $toCamelCache;
	/**
	 * @var
	 */
	private static $toSnakeCache;
	/**
	 * @var array
	 */
	private static $cache = [];
	/**
	 * @var
	 */
	private static $sizes;
	/**
	 * @var
	 */
	private static $classesToTitles;
	/**
	 * @var
	 */
	private static $snakeCamelLowerCaseVariations;
	/**
	 * @var
	 */
	private static $contains;
	/**
	 * @var
	 */
	private static $lower;
	/**
	 * @var
	 */
	private static $upper;
	/**
	 * Convert output ansi chars to html.
	 * @param string $log
	 * @return string
	 */
	public static function ansi2Html(string $log): string{
		$string = html_entity_decode((new AnsiToHtmlConverter())->convert($log));
		$string = str_replace("\r\n", '<br>', $string);
		$string = str_replace("\n", '<br>', $string);
		return $string;
	}
	public static function generateKeyWordString(array $keywords): string{
		$str = implode(", ", $keywords);
		if(strpos($str, ',,') !== false){
			le($str);
		}
		return $str;
	}
	/**
	 * @param string $input
	 * @return float
	 */
	public static function getNumberFromStringWithLeadingSpaceOrAtBeginning(string $input): ?float{
		preg_match_all('!\d+!', $input, $matches);
		if(isset($matches[0])){
			if(is_array($matches[0]) && isset($matches[0][0])){
				$matches[0] = $matches[0][0];
			}
			if(empty($matches[0])){
				return null;
			}
			$matchString = $matches[0];
			if(strpos($input, " " . $matchString) !==
				false){ // Avoid returning 16 from D'Addario EJ16 Phosphor Bronze Light Acoustic Guitar Strings Single-Pack
				return (float)$matchString;
			}
			if(strpos($input, $matchString) === 0){
				return (float)$matchString;
			}
		}
		return null;
	}
	/**
	 * @param string $str
	 * @param int $int
	 * @return string
	 */
	public static function getFirstXChars(string $str, int $int): string{
		return substr($str, 0, $int);
	}
	/**
	 * @param string $haystack
	 * @param string $needle
	 * @return bool
	 */
	public static function isCaseInsensitiveMatch(string $haystack, string $needle): bool{
		return strtolower($haystack) === strtolower($needle);
	}
	/**
	 * @param string $haystack
	 * @param array $needleArray
	 * @return bool
	 */
	public static function isCaseInsensitiveMatchInArray(string $haystack, array $needleArray): bool{
		foreach($needleArray as $needle){
			if(self::isCaseInsensitiveMatch($haystack, $needle)){
				return true;
			}
		}
		return false;
	}
	/**
	 * @param array $array
	 * @return string
	 */
	public static function list(array $array):string {
		$str = "";
		foreach($array as $key => $value){
			$str .= "\n\t$key - $value";
		}
		return $str;
	}
	/**
	 * @param string $str
	 * @return string
	 */
	public static function removeBackSlashes(string $str): string{
		return str_replace('\\', '', $str);
	}
	public static function removeLinesContaining(string $needle, string $haystack): string{
		$lines = preg_split('/\r\n|\r|\n/', $haystack);
		$nonEmpty = [];
		foreach($lines as $line){
			if(strpos($line, $needle) === false){
				$nonEmpty[] = $line;
			}
		}
		return implode("\n", $nonEmpty);
	}
	/**
	 * @param $constName
	 * @return array|string|string[]
	 */
	public static function replaceDisallowedVariableCharactersWithUnderscore($constName){
		$constName = str_replace('+', '_', $constName);
		$constName = str_replace('/', '_', $constName);
		$constName = str_replace('-', '_', $constName);
		$constName = str_replace(' ', '_', $constName);
		$constName = str_replace('.', '_', $constName);
		$constName = str_replace('__', '_', $constName);
		$constName = str_replace('@', '_', $constName);
		$constName = str_replace('~', '_', $constName);
		$constName = str_replace('(', '_', $constName);
		$constName = str_replace(')', '_', $constName);
		return $constName;
	}
	/**
	 * @param string $name
	 * @return array|string|string[]
	 */
	public static function replaceDoubleParenthesis(string $name){
		$name = str_replace([
			'((',
			'))',
		], [
			'(',
			')',
		], $name);
		return $name;
	}
	/**
	 * @param string $name
	 * @return mixed
	 */
	public static function removeDiamondWithQuestionMark(string $name): string{
		return preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $name);
	}
	/**
	 * @param string $subString
	 * @param string $haystack
	 * @param string|null $default
	 * @param bool $caseInsensitive
	 * @return string
	 */
	public static function after(string $subString, string $haystack, string $default = null,
		bool $caseInsensitive = false): ?string{
		if(!$haystack){
			le("No fullString provided to getStringAfterSubString for subString $subString!");
		}
		if($caseInsensitive){
			$pos = stripos($haystack, $subString);
		} else{
			$pos = strpos($haystack, $subString);
		}
		if($pos === false){
			return $default;
		}
		// TODO:  What is important for?
		$important = substr($haystack, $pos + strlen($subString), strlen($haystack) - 1);
		if(!empty($important) || $important === "0"){
			return $important;
		}
		return $default;
	}
	public static function stringsToKeywords(array $strings): array{
		$keywords = [];
		foreach($strings as $string){
			$keywords = array_merge($keywords, explode(" ", $string));
			$keywords[] = $string;
		}
		$keywords = array_unique($keywords);
		foreach($keywords as $i => $keyword){
			if(strlen($keyword) < 4){
				unset($keywords[$i]);
			} else{
				$keywords[$i] = str_replace(",", "", $keyword);
			}
		}
		return $keywords;
	}
	/**
	 * @param string $subString
	 * @param string $fullString
	 * @param string|null $default
	 * @return string|null
	 */
	public static function beforeLast(string $subString, string $fullString, string $default = null): ?string{
        $pos =  strrpos($fullString, $subString);
		$result = substr($fullString, 0, $pos);
		if(empty($result)){
			return $default;
		}
		return $result;
	}
	/**
	 * @param string $subString
	 * @param string $fullString
	 * @param string|null $default
	 * @param bool $caseInsensitive
	 * @return string
	 */
	public static function before(string $subString, string $fullString, string $default = null,
		bool $caseInsensitive = false): ?string{
		if(empty($subString)){
			QMLog::error("Delimiter empty for $fullString");
			return $default;
		}
		if($subString === '\n'){
			$subString = "\n";
		}
		if($caseInsensitive){
			$position = stripos($fullString, $subString);
			if($position !== false){
				return substr($fullString, 0, $position);
			}
		}
		$arr = explode($subString, $fullString);
		if(count($arr) === 1){
			return $default;
		}
		return $arr[0] ?? $default;
	}
	/**
	 * @param object|array $object
	 * @return array|null|object|stdClass
	 */
	public static function convertPropertiesToCamelCase($object){
		if(!$object){
			return $object;
		}
		if(!is_object($object)){
			$object = ObjectHelper::convertToObject($object);
		}
		$newObject = new stdClass();
		foreach($object as $key => $value){
			$camelCaseName = self::toCamelCase($key);
			$newObject->$camelCaseName = $value;
		}
		return $newObject;
	}
	/**
	 * Convert under_score type array's keys to camelCase type array's keys
	 * @param array $array array to convert
	 * @param array $arrayHolder parent array holder for recursive array
	 * @return  array   camelCase array
	 */
	public static function convertKeysToCamelCase(array $array, array $arrayHolder = []): array{
		if(!$array || !is_array($array)){
			return $array;
		}
		if(isset($array[0]) && is_array($array[0])){
			$camelCaseItems = [];
			foreach($array as $item){
				$camelCaseItems[] = self::convertKeysToCamelCase($item);
			}
			return $camelCaseItems;
		}
		$camelCaseArray = !empty($arrayHolder) ? $arrayHolder : [];
		foreach($array as $key => $val){
			if(is_numeric($key)){
				$newKey = $key;
			} else{
				$newKey = self::toCamelCase($key);
			}
			$camelCaseArray[$newKey] = $val;
		}
		return $camelCaseArray;
	}
	/**
	 * @param string $originalString
	 * @return array|string
	 */
	public static function toCamelCase(string $originalString){
		if(!$originalString){
			le("No string provided!");
		}
		if(isset(self::$toCamelCache[$originalString])){
			return self::$toCamelCache[$originalString];
		}
		$spaces = str_replace(['-', '_'], ' ', $originalString);
		$camelCaseString = lcfirst(str_replace(' ', '', ucwords($spaces)));
		return self::$toCamelCache[$originalString] = $camelCaseString;
	}
	/**
	 * @param string|object $stringOrTargetObject
	 * @param object|null $snakeSourceObject
	 * @return array|object|string
	 */
	public static function camelize($stringOrTargetObject, object $snakeSourceObject = null){
		if($stringOrTargetObject === 'id'){
			return 'id';
		}
		if($stringOrTargetObject === 'ID'){
			return 'id';
		}
		if($snakeSourceObject){
			foreach($snakeSourceObject as $key => $value){
				$camel = self::toCamelCase($key);
				$stringOrTargetObject->$camel = $value;
			}
			return $stringOrTargetObject;
		}
		return self::toCamelCase($stringOrTargetObject);
	}
	/**
	 * @param string $originalString
	 * @return array|string
	 */
	public static function toCamelCaseIfSnakeCaseOrSpaces(string $originalString){
		if(strpos($originalString, '_') === false && strpos($originalString, ' ') === false){
			return $originalString;
		}
		return self::toCamelCase($originalString);
	}
	/**
	 * @param string $camelStr
	 * @return string
	 */
	public static function camelToTitle(string $camelStr): string{
		$intermediate = preg_replace('/(?!^)([[:upper:]][[:lower:]]+)/', ' $0', $camelStr);
		$titleStr = preg_replace('/(?!^)([[:lower:]])([[:upper:]])/', '$1 $2', $intermediate);
		$titleStr = str_replace("  ", " ", $titleStr);
		return ucfirst($titleStr);
	}
	/**
	 * @param string $camelStr
	 * @return string
	 */
	public static function routeToTitle(string $camelStr): string{
		$camelStr = self::stripPrefixes($camelStr);
		$camelStr = self::camelToTitle($camelStr);
		$camelStr = str_replace("Bshaffer Oauth", "OAuth", $camelStr);
		return $camelStr;
	}
	/**
	 * Convert camelCase type array's keys to under_score+lowercase type array's keys
	 * @param array $array array to convert
	 * @param array $arrayHolder parent array holder for recursive array
	 * @param bool $convertSubArrays
	 * @return array under_score array
	 */
	public static function convertKeysToUnderscore(array $array, array $arrayHolder = [],
		bool $convertSubArrays = true): array{
		$underscoreArray = !empty($arrayHolder) ? $arrayHolder : [];
		foreach($array as $key => $val){
			$newKey = preg_replace('/[A-Z]/', '_$0', $key);
			$newKey = strtolower($newKey);
			$newKey = ltrim($newKey, '_');
			if(!is_array($val) || !count($val)){
				$underscoreArray[$newKey] = $val;
			} elseif($convertSubArrays && isset($underscoreArray[$newKey])){
				$underscoreArray[$newKey] = self::convertKeysToUnderscore($val, $underscoreArray[$newKey]);
			}
		}
		return $underscoreArray;
	}
	/**
	 * @param $string
	 * @return string
	 */
	public static function convertStringFromCamelCaseToDashes(string $string): string{
		$convertedString = preg_replace('/[A-Z]/', '_$0', $string);
		$convertedString = strtolower($convertedString);
		$convertedString = ltrim($convertedString, '-');
		return $convertedString;
	}
	/**
	 * @param string $haystack
	 * @param string $start
	 * @param string $end
	 * @param string|null $default
	 * @param bool $caseInsensitive
	 * @return string
	 */
	public static function between(string $haystack, string $start, string $end, string $default = null,
		bool $caseInsensitive = false): ?string{
		if(!$haystack){
			return $haystack;
		}
		$afterStart = self::after($start, $haystack, null, $caseInsensitive);
		if(!$afterStart){
			return $default;
		}
		$between = self::before($end, $afterStart, null, $caseInsensitive);
		if(!$between){
			return $default;
		}
		return $between;
	}
	/**
	 * @param string $string
	 * @param string $start
	 * @param string $end
	 * @param string|null $default
	 * @return string
	 */
	public static function betweenAndIncluding(string $string, string $start, string $end,
		string $default = null): ?string{
		$between = self::between($string, $start, $end, $default);
		if(!$between){
			return $default;
		}
		return $start . $between . $end;
	}
	/**
	 * Convert under_score type array's keys to camelCase type array's keys
	 * @param array|object $array array to convert
	 * @param array $legacyRequestParameterMap
	 * @param array $arrayHolder parent array holder for recursive array
	 * @return array camelCase array
	 */
	public static function properlyFormatRequestParams(array $array, array $legacyRequestParameterMap = [],
		array $arrayHolder = []): array{
		if(!$array){
			return [];
		}
		$legacyRequestParameterMap = array_merge(APIHelper::getLegacyParametersForPath(), $legacyRequestParameterMap);
		$legacyRequestParameterMap = array_merge(APIHelper::getGlobalLegacyRequestParams(), $legacyRequestParameterMap);
		$array = (array)$array;
		$array = QMArr::replaceLegacyKeys($array);
		$array = QMAPIValidator::convertToBooleanIfNecessary($array);
		unset($array["_"]); // Not sure what this parameter is
		$array = self::convertKeysToCamelCase($array, $arrayHolder);
		//$array = StringHelper::removeApostrophesFromNames($array);  // TODO: Why do we remove apostrophes?
		$array = QMAPIValidator::convertToIntegerIfNecessary($array);
		$array = self::rawUrlDecodeNamesInArray($array);
		User::setUserPlatform($array);
		if($legacyRequestParameterMap){
			$array = QMArr::replaceLegacyKeys($array, $legacyRequestParameterMap);
		}
		$array = QMVariableCategory::replaceVariableCategoryNameWithIdInArray($array, false);
		return $array;
	}
	/**
	 * @param string $from
	 * @param string $to
	 * @param string $subject
	 * @return string
	 */
	public static function str_replace_first(string $from, string $to, string $subject): string{
		$from = '/' . preg_quote($from, '/') . '/';
		return preg_replace($from, $to, $subject, 1);
	}
	/**
	 * Decodes a JSON string
	 * @link https://php.net/manual/en/function.json-decode.php
	 * @param string $json <p>
	 * The <i>json</i> string being decoded.
	 * </p>
	 * <p>
	 * This function only works with UTF-8 encoded strings.
	 * </p>
	 * <p>PHP implements a superset of
	 * JSON - it will also encode and decode scalar types and <b>NULL</b>. The JSON standard
	 * only supports these values when they are nested inside an array or an object.
	 * </p>
	 * @param bool|null $associative [optional] <p>
	 * When <b>TRUE</b>, returned objects will be converted into
	 * associative arrays.
	 * </p>
	 * @param int $depth [optional] <p>
	 * User specified recursion depth.
	 * </p>
	 * @param int $flags [optional] <p>
	 * Bitmask of JSON decode options:<br/>
	 * {@see JSON_BIGINT_AS_STRING} decodes large integers as their original string value.<br/>
	 * {@see JSON_INVALID_UTF8_IGNORE} ignores invalid UTF-8 characters,<br/>
	 * {@see JSON_INVALID_UTF8_SUBSTITUTE} converts invalid UTF-8 characters to \0xfffd,<br/>
	 * {@see JSON_OBJECT_AS_ARRAY} decodes JSON objects as PHP array, since 7.2.0 used by default if $assoc parameter
	 *     is null,<br/>
	 * {@see JSON_THROW_ON_ERROR} when passed this flag, the error behaviour of these functions is changed. The global
	 *     error state is left untouched, and if an error occurs that would otherwise set it, these functions instead
	 *     throw a JsonException<br/>
	 * </p>
	 * @return mixed the value encoded in <i>json</i> in appropriate
	 * PHP type. Values true, false and
	 * null (case-insensitive) are returned as <b>TRUE</b>, <b>FALSE</b>
	 * and <b>NULL</b> respectively. <b>NULL</b> is returned if the
	 * <i>json</i> cannot be decoded or if the encoded
	 * data is deeper than the recursion limit.
	 */
	public static function decodeIfJson($json, ?bool $associative = false, int $depth = 512, int $flags = 0){
		if(empty($json)){
			return $json;
		}
		if(!is_string($json)){
			return $json;
		}
		if(strpos($json, '{') === false){
			return $json;
		}
		$decoded = json_decode($json, $associative, $depth, $flags);
		$error = json_last_error();
		if($error === JSON_ERROR_NONE){
			return $decoded;
		}
		return $json;
	}
	/**
	 * @param string $json
	 * @return mixed|string|void
	 */
	public static function json_decode_or_exception(string $json){
		if($json === '"false"'){
			le("double quoted false is not valid json!:
            $json");
		}
		$decoded = json_decode($json);
		if($decoded === '"false"'){
			le("Got double quoted false after decoding this:
            $json");
		}
		if($decoded === "false"){
			le("Got false after decoding this:
            $json");
		}
		if(is_string($decoded)){
			le("Got this string from json_decode!
            Provided:
            $json
            Output:
            $decoded
            ");
		}
		$error = json_last_error();
		if($error === JSON_ERROR_NONE){
			return $decoded;
		}
		$msg = json_last_error_msg();
		le("Could not decode because $msg.
         Provided string was:
          $json");
	}
	/**
	 * @param mixed $string
	 * @return array|object
	 */
	public static function isJson($string, bool $assoc = false){
		if(empty($string)){
			return null;
		}
		if(!is_string($string)){
			return null;
		}
		if(strpos($string, '{') === false){
			return null;
		}
		$decoded = json_decode($string, $assoc);
		if(json_last_error() === JSON_ERROR_NONE){
			return $decoded;
		}
		return null;
	}
	/**
	 * @param mixed $string
	 * @return mixed
	 */
	public static function unserializeIfNecessary($string){
		if(!self::is_serialized($string)){
			return $string;
		}
		/** @noinspection UnserializeExploitsInspection */
		return @unserialize($string);
	}
	/**
	 * @param $data
	 * @return bool
	 */
	public static function is_serialized($data): bool{
		// if it isn't a string, it isn't serialized
		if(!is_string($data)){
			return false;
		}
		$data = trim($data);
		if('N;' == $data){
			return true;
		}
		if(!preg_match('/^([adObis]):/', $data, $badions)){
			return false;
		}
		switch($badions[1]) {
			case 'a' :
			case 'O' :
			case 's' :
				if(preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data)){
					return true;
				}
				break;
			case 'b' :
			case 'i' :
			case 'd' :
				if(preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data)){
					return true;
				}
				break;
		}
		return false;
	}
	/**
	 * @param string $clientId
	 * @return string
	 */
	public static function getAppEditInstructionsHtml(string $clientId): string{
		return "
<p>If you need to make any changes, you can do so in the</p>
<p>" . HtmlHelper::generateLink("APP BUILDER", UrlHelper::getBuilderUrl($clientId), false) . "</p>
<p>" . QMStr::CONTACT_MIKE_FOR_HELP_STRING . "</p>
";
	}
	/**
	 * @return string
	 */
	public static function getChromeExtensionInstructions(): string{
		return "Download, unzip and load the folder as an unpacked extension at chrome://extensions.  If everything looks good, you can upload and release at " .
			UrlHelper::CHROME_WEB_STORE_DEVELOPER_DASHBOARD . ".  ";
	}
	/**
	 * @param string $downloadLink
	 * @return string
	 */
	public static function getChromeExtensionInstructionsForSlack(string $downloadLink): string{
		return "<$downloadLink|Download>, unzip and load the folder as an unpacked extension at chrome://extensions.  If everything looks good, you can <" .
			UrlHelper::CHROME_WEB_STORE_DEVELOPER_DASHBOARD . "|upload and release>.  ";
	}
	/**
	 * @param $value
	 * @return string
	 */
	public static function convertToStringIfNecessary($value): string{
		if(is_array($value)){
			return json_encode($value);
		}
		if(is_object($value)){
			return json_encode($value);
		}
		return $value;
	}
	/**
	 * Singularize a string.
	 * Converts a word to english singular form.
	 * Usage example:
	 * {singularize "people"} # person
	 * @param $params
	 * @return bool|null|string|string[]
	 */
	public static function singularize($params){
		if(is_string($params)){
			$word = $params;
		} elseif(!$word = $params['word']){
			return false;
		}
		$singular = [
			'/(quiz)zes$/i' => '\\1',
			'/(matr)ices$/i' => '\\1ix',
			'/(vert|ind)ices$/i' => '\\1ex',
			'/^(ox)en/i' => '\\1',
			'/(alias|status)es$/i' => '\\1',
			'/([octop|vir])i$/i' => '\\1us',
			'/(cris|ax|test)es$/i' => '\\1is',
			'/(shoe)s$/i' => '\\1',
			'/(o)es$/i' => '\\1',
			'/(bus)es$/i' => '\\1',
			'/([m|l])ice$/i' => '\\1ouse',
			'/(x|ch|ss|sh)es$/i' => '\\1',
			'/(m)ovies$/i' => '\\1ovie',
			'/(s)eries$/i' => '\\1eries',
			'/([^aeiouy]|qu)ies$/i' => '\\1y',
			'/([lr])ves$/i' => '\\1f',
			'/(tive)s$/i' => '\\1',
			'/(hive)s$/i' => '\\1',
			'/([^f])ves$/i' => '\\1fe',
			'/(^analy)ses$/i' => '\\1sis',
			'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\\1\\2sis',
			'/([ti])a$/i' => '\\1um',
			'/(n)ews$/i' => '\\1ews',
			'/s$/i' => '',
		];
		$irregular = [
			'person' => 'people',
			'man' => 'men',
			'child' => 'children',
			'sex' => 'sexes',
			'move' => 'moves',
		];
		$ignore = [
			'equipment',
			'information',
			'rice',
			'money',
			'species',
			'series',
			'fish',
			'sheep',
			'press',
			'sms',
		];
		$lower_word = strtolower($word);
		foreach($ignore as $ignore_word){
			if(substr($lower_word, -1 * strlen($ignore_word)) == $ignore_word){
				return $word;
			}
		}
		foreach($irregular as $singular_word => $plural_word){
			if(preg_match('/(' . $plural_word . ')$/i', $word, $arr)){
				return preg_replace('/(' . $plural_word . ')$/i', substr($arr[0], 0, 1) . substr($singular_word, 1),
					$word);
			}
		}
		foreach($singular as $rule => $replacement){
			if(preg_match($rule, $word)){
				return preg_replace($rule, $replacement, $word);
			}
		}
		return $word;
	}
	/**
	 * @param $value
	 * @param bool $assoc
	 * @return mixed
	 */
	public static function jsonDecodeIfNecessary($value, bool $assoc = false){
		if(is_string($value)){
			// Don't do this because it messes up valid json!
			//			$value = QMStr::removeIfFirstCharacter('"', $value);
			//			$value = QMStr::removeIfLastCharacter('"', $value);
			//			$value = str_replace('/"', '"', $value);
		}
		if($value === "[]"){
			return [];
		}
		if($decoded = self::isJson($value, $assoc)){
			return $decoded;
		}
		return $value;
	}
	/**
	 * @param [] $array
	 * @return array
	 */
	public static function rawUrlDecodeNamesInArray($array): array{
		$urlDecodedArray = [];
		foreach($array as $key => $value){
			if(is_string($value) && self::isCaseInsensitiveMatch($key, "name")){
				$urlDecodedArray[$key] = self::rawUrlDecodeStringIfNecessary($value);
			} else{
				$urlDecodedArray[$key] = $value;
			}
		}
		return $urlDecodedArray;
	}
	/**
	 * @param array|string $array
	 * @return array|string
	 */
	public static function urlDecodeIfNecessary($array){
		if(!$array){
			return $array;
		}
		if(is_array($array)){
			foreach($array as $key => $value){
				$array[$key] = self::urlDecodeStringIfNecessary($value);
			}
			return $array;
		}
		if(is_string($array)){
			return self::urlDecodeStringIfNecessary($array);
		}
		return $array;
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function urlDecodeStringIfNecessary(string $string): string{
		if(strpos($string, '+') !== false && strpos($string, ' ') === false){
			$string = urldecode($string);
		}
		return $string;
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function rawUrlDecodeStringIfNecessary(string $string): string{
		$decoded = rawurldecode($string);
		if(rawurlencode($decoded) === $string){
			return $decoded; // 'string urlencoded';
		}
		return $string; // 'string is NOT urlencoded';
	}
	/**
	 * @param string $str
	 * @param string $needle_start
	 * @param string $needle_end
	 * @param string $replacement
	 * @return string
	 */
	public static function replace_between(string $str, string $needle_start, string $needle_end,
		string $replacement): string{
		$pos = strpos($str, $needle_start);
		if($pos === false){
			return $str;
		}
		$start = $pos + strlen($needle_start);
		$end = strpos($str, $needle_end, $start);
		if($end === false){
			return $str;
		}
		return substr_replace($str, $replacement, $start, $end - $start);
	}
	/**
	 * @param string $haystack
	 * @param string $needle_start
	 * @param string $needle_end
	 * @param string $replacement
	 * @return string
	 */
	public static function replace_between_and_including(string $haystack, string $needle_start, string $needle_end,
		string $replacement): string{
		$before = strlen($haystack);
		$after = null;
		while($before !== $after){
			$before = strlen($haystack);
			$pos = strpos($haystack, $needle_start);
			if($pos === false){
				return $haystack;
			}
			$start = $pos;
			$end = strpos($haystack, $needle_end, $start);
			if($end === false){
				return $haystack;
			}
			$haystack = substr_replace($haystack, $replacement, $start, +strlen($needle_end) + $end - $start);
			$after = strlen($haystack);
		}
		return $haystack;
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function removeDoubleSpacesFromString(string $string): string{
		$string = str_replace("  ", " ", $string);
		return $string;
	}
	/**
	 * @param string $string
	 * @param string $replacement
	 * @return string
	 */
	public static function removeNonAlphaNumericCharactersFromString(string $string, string $replacement = ""): string{
		$string = preg_replace("/[^A-Za-z0-9 ]/", $replacement, $string);
		$string = self::removeDoubleSpacesFromString($string);
		return $string;
	}
	/**
	 * @param string $string
	 * @return array
	 */
	public static function extractNumericValuesFromString(string $string): ?array{
		preg_match_all('!\d+!', $string, $matches);
		return $matches[0] ?? null;
	}
	/**
	 * @param string $url
	 * @param string $type
	 * @throws InvalidUrlException
	 */
	public static function validateUrl(string $url, string $type){
		self::assertIsUrl($url, $type);
	}
	/**
	 * @param string $string
	 * @param bool $throwException
	 * @return string
	 * @throws InvalidUrlException
	 */
	public static function validateUrlAndAddHttpsIfNecessary(string $string, bool $throwException = false): string{
		try {
			self::validateUrl($string, __FUNCTION__);
		} catch (InvalidUrlException $e) {
			if($throwException){
				throw $e;
			}
		}
		if(strpos($string, ".") === false){
			$message = "$string is not a valid image url!";
			if($throwException){
				throw new InvalidUrlException($message, $string, __FUNCTION__);
			}
			QMLog::error("$string is not a valid image url!");
			return false;
		}
		$missingHttp = strpos($string, 'http') === false;
		if($missingHttp){ // Let's just add http if missing.
			$string = "https://" . $string;
		}
		return $string;
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function getFirstWordOfString(string $string): string{
		$arr = explode(' ', trim($string));
		return $arr[0];
	}
	/**
	 * @param string $string
	 * @return int
	 */
	public static function getWordCount(string $string): int{
		return str_word_count($string);
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function removeLastWord(string $string): string{
		$string = preg_replace('/\W\w+\s*(\W*)$/', '$1', $string);
		return $string;
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function removeFirstWord(string $string): string{
		$string = substr(strstr($string, " "), 1);
		return $string;
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function getLastWord(string $string): string{
		$pieces = explode(' ', $string);
		$last_word = array_pop($pieces);
		if(empty($last_word)){
			$last_word = array_pop($pieces);
		}
		return $last_word;
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function getLastCharacter(string $string): string{
		return substr($string, -1); // returns "s"
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function getFirstCharacter(string $string): string{
		return substr($string, 0, 1); // returns "s"
	}
	/**
	 * @param string $needle
	 * @param string $haystack
	 * @return string
	 */
	public static function removeIfLastCharacter(string $needle, string $haystack): string{
		$last = self::getLastCharacter($haystack);
		if($last === $needle){
			$haystack = self::removeLastCharacter($haystack);
		}
		return $haystack;
	}
	/**
	 * @param string $needle
	 * @param string $haystack
	 * @return string
	 */
	public static function removeIfFirstCharacter(string $needle, string $haystack): string{
		$last = self::getFirstCharacter($haystack);
		if($last === $needle){
			$haystack = self::removeFirstCharacter($haystack);
		}
		return $haystack;
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function removeLastCharacter(string $string): string{
		return substr($string, 0, -1);
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function removeFirstCharacter(string $string): string{
		return substr($string, 1);
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function removeYYYMMDDFromString(string $string): string{
		$string = preg_replace('/(\d{4}[\.\/\-][01]\d[\.\/\-][0-3]\d)/', '', $string);
		return $string;
	}
	/**
	 * @param string $haystack
	 * @param string $substring
	 * @return float
	 */
	public static function getNumberBeforeSubString(string $haystack, string $substring): ?float{
		// "Glycerin Vegetable Kosher USP-Highest Quality Available-1 Quart"
		// Regular Can Coke 355ml (12 Oz)
		$haystack = str_replace([
			"-",
			"(",
			")",
		], [
			" ",
			"",
			"",
		], $haystack); // Regular Can Coke 355ml (12 Oz)
		$lowerCaseHayStack = strtolower($haystack);
		$lowerCaseSubstring = strtolower($substring);
		if(strpos($lowerCaseHayStack, $lowerCaseSubstring) !== false){
			$stringBeforeSubstring = self::before($lowerCaseSubstring, $lowerCaseHayStack);
			$lastWord = self::getLastWord($stringBeforeSubstring);
			if(is_numeric($lastWord)){
				//$numberFormat = number_format($lastWord);
				// TODO: Why does this convert "8.5" to 8?
				return (float)$lastWord;
			}
		}
		return null;
	}
	/**
	 * @param $string
	 * @param null $unit
	 * @param array $additionalFeatureStringsToCheck
	 * @return float|null
	 */
	public static function getDefaultValueFromString($string, $unit = null,
		array $additionalFeatureStringsToCheck = []): ?float{
		if(!$unit){
			$unit = QMUnit::getUnitFromString($string);
			if(!$unit){
				return null;
			}
		}
		$lowerCaseTitle = strtolower($string);
		$defaultValue = $unit->getNumberBeforeUnitNameOrAbbreviatedName($lowerCaseTitle);
		if($defaultValue && strpos($lowerCaseTitle, 'serving')){
			return $defaultValue;
		}
		foreach($additionalFeatureStringsToCheck as $feature){
			if(stripos($feature, 'serving size') !== false){
				$defaultValueNextToUnitName =
					$unit->getNumberBeforeUnitNameOrAbbreviatedName($feature);  // Necessary for Serving Size: 1 5g Scoop (Included)
				if($defaultValueNextToUnitName){
					return $defaultValueNextToUnitName;
				}
				$defaultValue =
					self::getNumberFromStringWithLeadingSpaceOrAtBeginning($feature);  // Keep going in cause another feature has value next to unit name
			}
		}
		return $defaultValue;
	}
	/**
	 * @param string $string
	 * @param int $length
	 * @param string $warning
	 * @return string
	 */
	public static function truncate(?string $string, int|float $length = 15, string $warning = "..."): ?string{
        if($string === null){return null;}
		$length = (int)$length; 
		if(strlen($string) < $length){
			return $string;
		}
		$lengthOfWarning = strlen($warning);
		$lengthMinusTruncation = $length - $lengthOfWarning - 1;
		if($lengthMinusTruncation < $lengthOfWarning){
			return substr($string, 0, $length);
		}
		return substr($string, 0, $lengthMinusTruncation) . $warning;
	}
	/**
	 * @param string $str
	 * @param int $position
	 * @return array
	 */
	public static function splitAt(string $str, int $position): array{
		$start = QMStr::truncate($str, $position, "");
		$end = QMStr::after($start, $str);
		return [$start, $end];
	}
	/**
	 * @param string $needlesString
	 * @param string $haystack
	 * @return bool
	 */
	public static function stringContainsAllWordsInAnotherString(string $needlesString, string $haystack): bool{
		$containsAll = true;
		$needlesArray = explode(" ", $needlesString);
		$haystack = strtolower($haystack);
		foreach($needlesArray as $needle){
			$needle = trim($needle);
			if(empty($needle)){
				continue;
			}
			$needle = strtolower($needle);
			if(strpos($haystack, $needle) === false){
				$containsAll = false;
			}
		}
		return $containsAll;
	}
	/**
	 * @param string $input
	 * @return string
	 */
	public static function snakize(string $input): string{
		if(isset(self::$toSnakeCache[$input])){
			return self::$toSnakeCache[$input];
		}
		//$output = $input;
		$output = ucfirst($input);
		if(strpos($output, " ") !== false){
			$output = strtolower($output);
			$output = str_replace("-", "_", $output);
			$output = str_replace(" ", "_", $output);
			return self::$toSnakeCache[$input] = $output;
		}
		preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $output, $matches);
		$ret = $matches[0];
		foreach($ret as &$match){
			$match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
		}
		$output = implode('_', $ret);
		if(strlen($output) < strlen($input)){
			$output = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
		}
		$output = str_replace("-", "_", $output);
		return self::$toSnakeCache[$input] = $output;
	}
	/**
	 * @param $string
	 * @return bool
	 */
	public static function containsUnterminatedParenthesis($string): bool{
		return strpos($string, "(") !== false && strpos($string, ")") === false;
	}
	/**
	 * @param string $string
	 * @return bool
	 */
	public static function isPlural(string $string): bool{
		$lastLetter = strtolower(self::getLastCharacter(trim($string)));
		return $lastLetter === 's';
	}
	/**
	 * Generate Slugs from Title or any given string
	 * @param string $str
	 * @param bool $allowUnderscore
	 * @param int $maxCharacters
	 * @return string
	 */
	public static function slugify(string $str, bool $allowUnderscore = false,
		int $maxCharacters = WpPostPostNameProperty::MAX_LENGTH): string{
		if(self::containsUpperCase($str)){
			$str = self::snakize($str);
		}
		$str = self::removeUrlUnsafeCharacters($str, $allowUnderscore);
		$str = strtolower($str);
		if($maxCharacters && strlen($str) > $maxCharacters){
			if(stripos($str, 'purchases') === false &&
				stripos($str, 'how-to-track') === false){ // Amazon titles are long
				QMLog::error("Trimming $str to $maxCharacters characters for slug...");
			}
			$str = substr($str, 0, $maxCharacters);
		}
		return $str;
	}
	/**
	 * @param mixed $json
	 * @return mixed
	 */
	public static function replaceLocalUrlsWithProduction($json){
		$isString = is_string($json);
		if(!$isString){
			$json = json_encode($json);
		}
		$json = str_replace([
			UrlHelper::getLocalUrl(),
			ThisComputer::LOCAL_HOST_NAME,
			'utopia.quantimo.do',
		], 'app.quantimo.do', $json);
		if(!$isString){
			$json = json_decode($json);
		}
		return $json;
	}
	/**
	 * @param string $key
	 * @return string
	 */
	public static function toScreamingSnakeCase(string $key): string{
		$str = strtoupper(self::snakize($key));
		$str = str_replace(".", "_", $str);
		return $str;
	}
	/**
	 * Convert words to PHP Class name
	 * @param $input
	 * @return string
	 */
	public static function toClassName(string $input): string{
		$output = preg_replace(['#(?<=[^A-Z\s])([A-Z\s])#i'], ' $0', $input);
		$output = explode(' ', $output);
		$output = array_map(function($item){
			$item = preg_replace('#[^a-z0-9]#i', '', $item);
			//if(strlen($item) > 2){$item = ucfirst($item);} // Why was this necessary?  it breaks "Spending on My Italian Secret"
			$item = ucfirst($item);
			return $item;
		}, $output);
		$output = array_filter($output);
		$output = implode('', $output);
		$output = self::replaceNumbersWithWords($output);
		return $output;
	}
	/**
	 * First char to upper, other to lower
	 * @param $input
	 * @return string
	 */
	public static function toLowerAndUpperCaseFirst(string $input): string{
		$string = strtolower($input);
		$string = ucfirst($string);
		return $string;
	}
	/**
	 * @return string
	 */
	public static function tab(): string{
		return chr(9);
	}
	/**
	 * @param string $string
	 */
	public static function validateJson(string $string){
		$decoded = json_decode($string, false);
		if(!$decoded){
			le("Empty after decode!");
		}
		$error = json_last_error();
		if($error !== JSON_ERROR_NONE){
			throw new LogicException($error);
		}
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function removeLineBreaks(string $string): string{
		return preg_replace("/\r|\n/", "", $string);
	}
	/**
	 * @param string $message
	 * @return string
	 */
	public static function trimWhitespaceAndLineBreaks(string $message): string{
		$noLineBreaks = self::removeLineBreaks($message);
		$result = self::trimExcessWhiteSpace($noLineBreaks);
		return $result;
	}
	/**
	 * @param string $str
	 * @return string
	 */
	public static function trimExcessWhiteSpace(string $str): string{
		$foo = preg_replace('/\s+/', ' ', $str);
		$foo = trim($foo);
		return $foo;
	}
	/**
	 * @param string $str
	 * @return string
	 */
	public static function trimTabsAndWhiteSpace(string $str): string{
		return trim($str);
	}
	/**
	 * @param $cell
	 * @return array[]|false|string[]
	 */
	public static function getNotEmptyLinesAsArray(string $cell): array{
		$lines = preg_split('/\r\n|\r|\n/', $cell);
		$nonEmpty = [];
		foreach($lines as $line){
			$line = trim($line);
			if($line !== ""){
				$nonEmpty[] = $line;
			}
		}
		return $nonEmpty;
	}
	/**
	 * @param string $gradeString
	 * @return float|null
	 */
	public static function fractionStringToPercent(string $gradeString, string $context): ?float{
		if(strtoupper(trim($gradeString)) === "Z"){
			return 0;
		}
		if(strpos($gradeString, '/') === false){
			return null;
		}
		$numbers = QMStr::getNumbersFromString($gradeString);
		if(!$numbers){
			return null;
		}
		$numerator = $numbers[0] ?? null;
		$denominator = $numbers[1] ?? null;
		if($denominator !== null && $numerator !== null){
			$denominator = (int) $denominator;
			$numerator = (float) $numerator;
			if($denominator === 0 && $numerator){
				return 100; // Extra credit
			}
			return $numerator/$denominator * 100;
		}
		try {
			$percent = eval('return ' . $gradeString . ';') * 100;
			return $percent;
		} catch (Throwable $e) {
			le("Could not evaluate $gradeString because " . $e->getMessage()."\n\t Context: $message");
			return null;
		}
	}
	/**
	 * @param string $needle
	 * @param string $haystack
	 * @return bool
	 */
	public static function endsWith(string $needle, string $haystack): bool{
		$length = strlen($needle);
		if($length == 0){
			return true;
		}
		return (substr($haystack, -$length) === $needle);
	}
	/**
	 * @param $unitName
	 * @return string
	 */
	public static function removeParenthesis(string $unitName){
		$unitName = str_replace([
			'(',
			')',
		], '', $unitName);
		return $unitName;
	}
	/**
	 * @param $string
	 * @return bool
	 */
	public static function isAddress($string): bool{
		if(!self::getNumbersFromString($string)){
			return false;
		}
		//$string = self::removeStringFollowingTerminatingString($string);
		$data = ['street' => $string];
		$unit = QMUnit::getUnitFromString($string);
		if($unit){
			return false;
		}
		preg_match_all('!\d+!', $string, $matches);
		if(empty($matches[0]) || count($matches[0]) < 2){
			return false;
		}
		if(strpos($string, ', ') === false){
			return false;
		}
		$words = explode(' ', $string);
		$addressTerms = [
			'usa',
			'ave',
			'st',
			'avenue',
			'street',
			'highway',
		];
		foreach($words as $word){
			$word = strtolower($word);
			$word = str_replace([
				'.',
				',',
			], '', $word);
			if(in_array($word, $addressTerms, true)){
				return true;
			}
		}
		return false;
	}
	/**
	 * @param string $someString
	 * @return string
	 */
	public static function removeNumbersFromEndOfString(string $someString): string{
		return trim(preg_replace("/\d+$/", "", $someString));
	}
	/**
	 * @param string $string
	 * @return mixed
	 */
	public static function getNumbersFromString(string $string){
		preg_match_all('!\d+!', $string, $matches);
		if(empty($matches[0])){
			return null;
		}
		return $matches[0];
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function addSpaceBetweenNumbersAndLetters(string $string): string{
		$result = preg_replace('/(\d+)/', '${1} ', $string);
		$result = self::removeDoubleSpacesFromString($result);
		return $result;
	}
	/**
	 * @param $fullString
	 * @param $unitName
	 * @param bool $truncate
	 * @return mixed|string
	 */
	public static function removeSubstringAndPrecedingNumber(string $fullString, string $unitName,
		bool $truncate = true): ?string{
		$number = self::getNumberBeforeSubString($fullString, $unitName);
		if($number){
			$stringsToReplace = [ // Most unique first
				$number . ' ' . $unitName . '.',
				$number . $unitName . '.',
				$number . ' ' . $unitName,
				$number . $unitName,
				$number . '-' . $unitName,
			];
			foreach($stringsToReplace as $stringToReplace){
				if($truncate){
					$fullString = self::before($stringToReplace, $fullString, $fullString);
				} else{
					$fullString = str_ireplace($stringToReplace, '', $fullString);
				}
			}
		}
		return $fullString;
	}
	/**
	 * @param string $title
	 * @return string
	 */
	public static function titleCaseFast(string $title): string{
		$original = $title;
		if($cached = static::$cache[__FUNCTION__][$original] ?? null){
			return $cached;
		}
		$title = str_replace("_", " ", $title);
		$title = ucwords($title);
		$title = self::titleCaseArticles($title);
		return static::$cache[__FUNCTION__][$original] = $title;
	}
	/**
	 * @param string $title
	 * @return string
	 */
	public static function titleCaseSlow(string $title): string{
		$original = $title;
		if($cached = static::$cache[__FUNCTION__][$original] ?? null){
			return $cached;
		}
		$title = str_replace("_", " ", $title);
		//remove HTML, storing it for later
		//       HTML elements to ignore    | tags  | entities
		//$regx = '/<(code|var)[^>]*>.*?<\/\1>|<[^>]+>|&\S+;/';
		//preg_match_all ($regx, $title, $html, PREG_OFFSET_CAPTURE);
		//$title = preg_replace ($regx, '', $title);
		//find each word (including punctuation attached)
		preg_match_all('/[\w\p{L}&`\'‘’"“\.@:\/\{\(\[<>_]+-? */u', $title, $m1, PREG_OFFSET_CAPTURE);
		foreach($m1[0] as &$m2){
			//shorthand these- "match" and "index"
			[$m, $i] = $m2;
			//correct offsets for multi-byte characters (`PREG_OFFSET_CAPTURE` returns *byte*-offset)
			//we fix this by recounting the text before the offset using multi-byte aware `strlen`
			$i = mb_strlen(substr($title, 0, $i), 'UTF-8');
			//find words that should always be lowercase…
			//(never on the first word, and never if preceded by a colon)
			/** @noinspection NestedTernaryOperatorInspection */
			$m = $i > 0 && mb_substr($title, max(0, $i - 2), 1, 'UTF-8') !== ':' &&
			!preg_match('/[\x{2014}\x{2013}] ?/u', mb_substr($title, max(0, $i - 2), 2, 'UTF-8')) &&
			preg_match('/^(a(nd?|s|t)?|b(ut|y)|en|for|i[fn]|o[fnr]|t(he|o)|vs?\.?|via)[ \-]/i',
				$m) ?    //…and convert them to lowercase
				mb_strtolower($m,
					'UTF-8')                                                                                                                                                                                                                                //else:	brackets and other wrappers
				: (preg_match('/[\'"_{(\[‘“]/u', mb_substr($title, max(0, $i - 1), 3,
					'UTF-8')) ?                                                               //convert first letter within wrapper to uppercase
					mb_substr($m, 0, 1, 'UTF-8') . mb_strtoupper(mb_substr($m, 1, 1, 'UTF-8'), 'UTF-8') .
					mb_substr($m, 2, mb_strlen($m, 'UTF-8') - 2, 'UTF-8') //else:	do not uppercase these cases
					: (preg_match('/[\])}]/', mb_substr($title, max(0, $i - 1), 3, 'UTF-8')) ||
					preg_match('/[A-Z]+|&|\w+[._]\w+/u', mb_substr($m, 1, mb_strlen($m, 'UTF-8') - 1,
						'UTF-8')) ? $m //if all else fails, then no more fringe-cases; uppercase the word
						: mb_strtoupper(mb_substr($m, 0, 1, 'UTF-8'), 'UTF-8') .
						mb_substr($m, 1, mb_strlen($m, 'UTF-8'), 'UTF-8')));
			//resplice the title with the change (`substr_replace` is not multi-byte aware)
			$title = mb_substr($title, 0, $i, 'UTF-8') . $m .
				mb_substr($title, $i + mb_strlen($m, 'UTF-8'), mb_strlen($title, 'UTF-8'), 'UTF-8');
		}
		//restore the HTML
		//foreach ($html[0] as &$tag) $title = substr_replace ($title, $tag[0], $tag[1], 0);
		$title = self::titleCaseAcronyms($title);
		$title = self::titleCaseArticles($title);
		return static::$cache[__FUNCTION__][$original] = trim($title);
	}
	/**
	 * @param string $string
	 * @param string $replacement
	 * @return null|string|string[]
	 */
	public static function replaceNewLineBreaks(string $string, string $replacement = " "){
		return preg_replace("/[\n\r]/", $replacement, $string);
	}
	/**
	 * @param array $input
	 * @return string
	 */
	public static function base64_url_encode_array(array $input): string{
		return strtr(base64_encode(json_encode($input)), '+/=', '._-');
	}
	/**
	 * @param $input
	 * @return bool|string|array
	 */
	public static function base64_url_decode_array(string $input){
		return json_decode(base64_decode(strtr($input, '._-', '+/=')), true);
	}
	/**
	 * @param string $original
	 * @return string
	 */
	public static function removeApiVersionFromPath(string $original): string{
		$pathToGet = Str::replaceFirst('/api', '', $original);
		$pathToGet = Str::replaceFirst('/v', '', $pathToGet);
		$pathToGet = self::after('/', $pathToGet, $pathToGet);
		if(!$pathToGet){
			le("No path after removeApiVersionFromPath on $original");
		}
		return $pathToGet;
	}
	/**
	 * @param $raw
	 * @param bool $fast
	 * @return string
	 */
	public static function displayName($raw, bool $fast = true): string{
		if($fast){
			$displayName = self::titleCaseFast($raw);
		} else{
			$displayName = self::titleCaseSlow($raw);
		}
		// Why?  $displayName = self::before(' - ', $displayName, $displayName);
		// It turns "Plus - Almond Walnut Macadamia + Protein, With Peanuts" into "Plus"
		$displayName = self::before('(', $displayName, $displayName);
		$displayName = trim($displayName);
		return $displayName;
	}
	/**
	 * @param $obj
	 * @param int|null $maxChars
	 * @param bool $escapeSlashes
	 * @return string
	 */
	public static function prettyJsonEncode($obj, int $maxChars = null, bool $escapeSlashes = true): string{
		if($escapeSlashes){
			$encoded = json_encode($obj, JSON_PRETTY_PRINT);
		} else{
			$encoded = json_encode($obj, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
		}
		if($maxChars){
			return self::truncate($encoded, $maxChars);
		}
		return $encoded;
	}
	/**
	 * @param $json
	 * @param int|null $maxChars
	 * @return string
	 */
	public static function prettyPrintJson($json, int $maxChars = null): string{
		$decoded = json_decode($json);
		return self::prettyJsonEncode($decoded, $maxChars);
	}
	/**
	 * @param $obj
	 * @return string
	 */
	public static function prettyJsonEncodeUnescapedSlashes($obj): string{
		$json = json_encode($obj, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		//$json = preg_replace('/^(  +?)\\1(?=[^ ])/m', '$1', $json);  // I think just reduces indentation
		return $json;
	}
	/**
	 * @param string $variableName
	 * @param $obj
	 * @return string
	 */
	public static function prettyJsonInJavascript(string $variableName, $obj): string{
		return "var $variableName = " . self::prettyJsonEncode($obj) . ";";
	}
	/**
	 * @param array $synonyms
	 * @return array
	 */
	public static function addSingularVersions(array $synonyms): array{
		$synonyms = array_filter($synonyms); // Remove empty values
		foreach($synonyms as $synonym){
			if(strlen($synonym) < 4){
				continue;
			}
			$synonyms[] = self::singularize($synonym);
		}
		$synonyms = array_values(array_unique($synonyms));
		return $synonyms;
	}
	/**
	 * @param string $string
	 * @return bool
	 */
	public static function containsUpperCase(string $string): bool{
		for($i = 0, $iMax = strlen($string); $i < $iMax; $i++){
			$character = $string[$i];
			if(ctype_upper($character)){
				return true;
			}
		}
		return false;
	}
	/**
	 * @param array $synonyms
	 * @return array
	 */
	public static function removeLowerCaseDuplicates(array $synonyms): array{
		$upper = [];
		$synonyms = array_filter($synonyms); // Remove empty values
		foreach($synonyms as $synonym){
			if(!isset($upper[strtolower($synonym)])){
				$upper[strtolower($synonym)] = $synonym;
			}
			if(self::containsUpperCase($synonym)){
				$upper[strtolower($synonym)] = $synonym;
			}
		}
		$upper = array_values(array_unique($upper));
		return $upper;
	}
	/**
	 * @param $arrayOrObject
	 * @return string
	 */
	public static function convertArrayOrObjectToFileName($arrayOrObject): string{
		$file = json_encode($arrayOrObject);
		if(strlen($file) > 200){
			$file = substr($file, 0, 200);
		}
		$file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $file);
		// Remove any runs of periods (thanks falstro!)
		$file = mb_ereg_replace("([\.]{2,})", '', $file);
		return $file;
	}
	/**
	 * @param string $input
	 * @return string
	 */
	public static function sanitizePHPVariableName(string $input): string{
		$out = self::replaceDisallowedVariableCharactersWithUnderscore($input);
		$out = self::replaceNumbersWithWords($out);
		$out = QMStr::toClassName($out);
		$pattern = '/[^a-zA-Z0-9]/';
		$out = preg_replace($pattern, '', $out);
		$out = lcfirst($out);
		return $out;
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function replaceNumbersWithWords(string $string): string{
		$search = [
			0,
			1,
			2,
			3,
			4,
			5,
			6,
			7,
			8,
			9,
		];
		$replace = [
			'Zero',
			'One',
			'Two',
			'Three',
			'Four',
			'Five',
			'Six',
			'Seven',
			'Eight',
			'Nine',
		];
		return str_ireplace($search, $replace, $string);
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function pluralize(string $string): string{
		return Pluralizer::plural($string);
	}
	/**
	 * @param string $haystack
	 * @param string $needle
	 * @param string|null $default
	 * @return string
	 */
	public static function afterLast(string $haystack, string $needle, string $default = null): ?string{
		if(isset(self::$cache[__FUNCTION__][$haystack][$needle])){
			return self::$cache[__FUNCTION__][$haystack][$needle];
		}
		$arr = explode($needle, $haystack);
		$result = end($arr);
		if(empty($result)){
			$result = $default;
		}
		return self::$cache[__FUNCTION__][$haystack][$needle] = $result;
	}
	/**
	 * @param string $url
	 * @return string
	 */
	public static function urlToTitle(string $url): string{
		$title = self::afterLast($url, '/');
		$title = str_replace("-", " ", $title);
		$title = str_replace(".", " ", $title);
		$title = str_replace("_", " ", $title);
		$title = self::titleCaseSlow($title);
		return $title;
	}
	/**
	 * Convert a string such as "one hundred thousand" to 100000.00.
	 * @param string $string The numeric string.
	 * @return float or false on error
	 */
	public static function wordsToNumber(string $string){
		// Replace all number words with an equivalent numeric value
		$string = strtr($string, [
			'zero' => '0',
			'a' => '1',
			'one' => '1',
			'two' => '2',
			'three' => '3',
			'four' => '4',
			'five' => '5',
			'six' => '6',
			'seven' => '7',
			'eight' => '8',
			'nine' => '9',
			'ten' => '10',
			'eleven' => '11',
			'twelve' => '12',
			'thirteen' => '13',
			'fourteen' => '14',
			'fifteen' => '15',
			'sixteen' => '16',
			'seventeen' => '17',
			'eighteen' => '18',
			'nineteen' => '19',
			'twenty' => '20',
			'thirty' => '30',
			'forty' => '40',
			'fourty' => '40', // common misspelling
			'fifty' => '50',
			'sixty' => '60',
			'seventy' => '70',
			'eighty' => '80',
			'ninety' => '90',
			'hundred' => '100',
			'thousand' => '1000',
			'million' => '1000000',
			'billion' => '1000000000',
			'and' => '',
		]);
		// Coerce all tokens to numbers
		$parts = array_map('floatval', preg_split('/[\s-]+/', $string));
		$stack = new SplStack; // Current work stack
		$sum = 0;            // Running total
		$last = null;
		foreach($parts as $part){
			if(!$stack->isEmpty()){
				// We're part way through a phrase
				if($stack->top() > $part){
					// Decreasing step, e.g. from hundreds to ones
					if($last >= 1000){
						// If we drop from more than 1000 then we've finished the phrase
						$sum += $stack->pop();
						// This is the first element of a new phrase
						$stack->push($part);
					} else{
						// Drop down from less than 1000, just addition
						// e.g. "seventy one" -> "70 1" -> "70 + 1"
						$stack->push($stack->pop() + $part);
					}
				} else{
					// Increasing step, e.g ones to hundreds
					$stack->push($stack->pop() * $part);
				}
			} else{
				// This is the first element of a new phrase
				$stack->push($part);
			}
			// Store the last processed part
			$last = $part;
		}
		return $sum + $stack->pop();
	}
	/**
	 * @param $object
	 * @return string
	 */
	public static function jsonEncodeForTextField($object): string{
		$encoded = json_encode($object);
		$kb = strlen($encoded) / 1000;
		if($kb > 60){
			le("Too big to json encode! $kb kb but max for text field is 65kb!");
		}
		return $encoded;
	}
	/**
	 * @param $object
	 * @return string
	 */
	public static function serializeForTextField($object): string{
		$encoded = serialize($object);
		$kb = strlen($encoded) / 1000;
		if($kb > 60){
			le("Too big to serialize! $kb kb but max for text field is 65kb!");
		}
		return $encoded;
	}
	/**
	 * @param Validator $validator
	 * @return string
	 */
	public static function validatorToString(Validator $validator): string{
		$errorString = implode(",", $validator->messages()->all());
		$errorString = str_replace('.,', ".\n", $errorString);
		return $errorString;
	}
	/**
	 * @param string $str
	 * @param bool $allowUnderscore
	 * @return string
	 */
	public static function removeUrlUnsafeCharacters(string $str, bool $allowUnderscore): string{
		if($str !== mb_convert_encoding(mb_convert_encoding($str, 'UTF-32', 'UTF-8'), 'UTF-8', 'UTF-32')){
			$str = mb_convert_encoding($str, 'UTF-8', mb_detect_encoding($str));
		}
		$str = htmlentities($str, ENT_NOQUOTES, 'UTF-8');
		$str = preg_replace('`&([a-z]{1,2})(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig);`i', '\\1', $str);
		$str = html_entity_decode($str, ENT_NOQUOTES, 'UTF-8');
		if($allowUnderscore){
			$str = preg_replace([
				'`[^a-z0-9_]`i',
				'`[-]+`',
			], '-', $str);
		} else{
			$str = preg_replace([
				'`[^a-z0-9]`i',
				'`[-]+`',
			], '-', $str);
		}
		$str = trim($str, '-');
		return $str;
	}
	/**
	 * @param string $str
	 * @param QMUser $originalUser
	 * @return string
	 */
	public static function convertTextToThirdParty(string $str, QMUser $originalUser): string{
		$name = QMStr::titleCaseSlow($originalUser->getDisplayNameAttribute());
		$str = str_replace("Your", "$name's", $str);
		$str = str_replace("your", "$name's", $str);
		$str = str_replace("you", $name, $str);
		$str = str_replace("Your", $name, $str);
		return $str;
	}
	/**
	 * @param string $class
	 * @return string
	 */
	public static function getShortPluralizedClassName(string $class): string{
		$class = self::afterLast($class, '\\');
		$name = str_replace('\\', '', Str::snake(Str::plural($class)));
		$slug = str_replace('_', '-', $name);
		$slug = str_replace('q-m-', '', $slug);
		return $slug;
	}
	/**
	 * @param string $class
	 * @return string
	 */
	public static function classToTableName(string $class): string{
		$str = self::getShortPluralizedClassName($class);
		return str_replace('-', '_', $str);
	}
	/**
	 * @param string $url
	 * @param bool $removeExtension
	 * @return string
	 */
	public static function getFileNameFromUrl(string $url, bool $removeExtension): string{
		$filename = self::afterLast($url, '/');
		if($removeExtension){
			$filename = self::before('.', $filename);
		}
		return $filename;
	}
	/**
	 * @param string $class
	 * @return string
	 */
	public static function fullClassToTitle(string $class): string{
		$arr = explode("\\", $class);
		foreach($arr as $i => $item){
			$arr[$i] = self::classToTitle($item);
		}
		return implode(" ", $arr);
	}
	/**
	 * @param string $class
	 * @return string
	 */
	public static function classToTitle(string $class): string{
		if(isset(self::$classesToTitles[$class])){
			return self::$classesToTitles[$class];
		}
		$title = str_replace("QM", "", $class);
		$title = str_replace("OA", "OAuth", $title);
		$title = self::toShortClassName($title);
		$title = self::snakize($title);
		$title = self::titleCaseSlow($title);
		$title = str_replace("O Auth", "oAuth", $title);
		return self::$classesToTitles[$class] = $title;
	}
	/**
	 * @param string $class
	 * @return string
	 */
	public static function classToPluralTitle(string $class): string{
		$class = self::classToTitle($class);
		$parts = explode(" ", $class);
		if(count($parts) === 1){
			return Str::plural($parts[0]);
		}
		$lastWord = array_pop($parts);
		return implode(' ', $parts) . " " . Str::plural($lastWord);
	}
	/**
	 * @param float $bytes
	 * @param string $unit
	 * @param int $decimals
	 * @return string
	 */
	public static function human_filesize(float $bytes, string $unit = "", int $decimals = 0): string{
		if((!$unit && $bytes >= 1 << 30) || $unit == "GB") return number_format($bytes / (1 << 30), $decimals) . " GB";
		if((!$unit && $bytes >= 1 << 20) || $unit == "MB") return number_format($bytes / (1 << 20), $decimals) . " MB";
		if((!$unit && $bytes >= 1 << 10) || $unit == "KB") return number_format($bytes / (1 << 10), $decimals) . " KB";
		return number_format($bytes) . " bytes";
	}
	/**
	 * @param $obj
	 */
	public static function outputSnakeCamelCaseMapsForObject($obj): void{
		foreach($obj as $camel => $value){
			$snake = QMStr::snakize($camel);
			\App\Logging\ConsoleLog::info("'$snake' => '$camel',");
		}
		foreach($obj as $camel => $value){
			$snake = QMStr::snakize($camel);
			\App\Logging\ConsoleLog::info("'$camel' => '$snake',");
		}
	}
	/**
	 * @param string $input
	 * @return string
	 */
	public static function removeDatesAndTimes(string $input): string{
		$output = $input;
		$output = self::removeTimesFromString($output, "00:00:00");
		$output = self::removeDatesFromString($output, "2020-01-01");
		$output = self::removeUnixTimestampsFromString($output, strtotime("2020-01-01 00:00:00"));
		return $output;
	}
	/**
	 * @param string $input
	 * @return string
	 */
	public static function removeDatesTimesAndNumbers(string $input): string{
		$output = self::removeNumbers($input);
		$output = self::stripDates($output);
		return $output;
	}
	/**
	 * @param string $input
	 * @param string $replacement
	 * @return array|string|string[]|null
	 */
	public static function removeNumbers(string $input, string $replacement = '__NUMBER_WAS_HERE__'){
		$words = preg_replace('/[0-9]+/', $replacement, $input);
		return $words;
	}
	/**
	 * @param string $output
	 * @param string|null $replacement
	 * @return string|string[]|null
	 */
	public static function removeTimesFromString(string $output, string $replacement = null){
		if(!$replacement){
			$replacement = "[TIME REDACTED]";
		}
		$output = preg_replace('/\d{2}:\d{2}:\d{2}/', $replacement, $output);
		return $output;
	}
	/**
	 * @param $output
	 * @param string|null $replacement
	 * @return string|string[]|null
	 */
	private static function removeDatesFromString($output, string $replacement = null){
		if(!$replacement){
			$replacement = "[DATE REDACTED]";
		}
		$output = preg_replace('/(\d{4}[\.\/\-][01]\d[\.\/\-][0-3]\d)/', $replacement, $output);
		return $output;
	}
	/**
	 * @param $output
	 * @param int|null $replacement
	 * @return string
	 */
	public static function removeUnixTimestampsFromString(string $output, string $replacement = null){
		if(!$replacement){
			$replacement = "[UNIXTIME REDACTED]";
		}
		$output = preg_replace('/\d{10}/', $replacement, $output);
		return $output;
	}
	/**
	 * @param string $path
	 */
	public static function exceptionIfStringContainsLineBreaks(string $path){
		if(self::containsLineBreaks($path)){
			le("$path should not contain line breaks!");
		}
	}
	/**
	 * @param string $path
	 */
	public static function exceptionIfStringContainsUrl(string $path){
		if(strpos($path, 'https://') !== false || strpos($path, 'http://')){
			le("$path should not contain URL!");
		}
	}
	/**
	 * @param string $path
	 * @return false|string
	 */
	public static function containsLineBreaks(string $path){
		return strstr($path, "\n");
	}
	/**
	 * @param string $table
	 * @return string
	 */
	public static function tableToShortClassName(string $table): string{
		if($table === User::TABLE){
			return "User";
		}
		$table = self::singularize($table);
		if(strpos($table, "oa_") === 0){
			return "OA" . self::toClassName(str_replace("oa_", "", $table));
		}
		return self::toClassName($table);
	}
	/**
	 * @param string $table
	 * @return string|BaseModel
	 */
	public static function tableToFullClassName(string $table): string{
		return BaseModel::getClassByTable($table);
	}
	/**
	 * @param string $shortClass
	 * @return string|BaseModel
	 */
	public static function toFullClassName(string $shortClass): string{
		return BaseModel::generateFullClassName($shortClass);
	}
	/** @noinspection PhpUnused */
	/**
	 * @param string $table
	 * @return array|string|string[]
	 */
	public static function tableToTitle(string $table){
		if($table === User::TABLE){
			return "Users";
		}
		if($table === UserVariableRelationship::TABLE){
			return "Individual Case Studies";
		}
		if($table === GlobalVariableRelationship::TABLE){
			return "Global Population Studies";
		}
		$title = str_replace("wp_", "", $table);
		$title = self::snakeToTitle($title);
		$title = str_replace("Bshaffer Oauth", "OAuth", $title);
		return $title;
	}
	/**
	 * @param string $str
	 * @return string
	 */
	public static function stripTabsAndExtraSpaces(string $str): string{
		return trim(preg_replace('/\t+/', '', $str));
	}
	/**
	 * @param string $str
	 * @return array|string|string[]|null
	 */
	public static function stripWhiteSpaceAtTheBeginningOfLines(string $str){
		return preg_replace('/^\s+/m', '', $str);
	}
	/**
	 * @param string $str
	 * @param string|null $name
	 * @param int $maxKb
	 */
	public static function errorIfLengthGreaterThan(string $str, string $name = null, int $maxKb = 1000){
		$kb = round(strlen($str) / 1024);
		if($kb > $maxKb){
			if(!$name){
				$name = debug_backtrace()[1]['function'];
			}
			self::$sizes[$name] = [
				'kb' => $kb,
				//'string' => $str
			];
			QMLog::error($name . " is $kb kb");
			QMLog::print(self::$sizes, "string sizes");
		}
	}
	/**
	 * @param float|null $num
	 * @param int $precision
	 * @return string
	 */
	public static function abbreviateNumber(?float $num, int $precision = 0): string{
		if($num === null){
			return "N/A";
		}
		$absNum = abs($num);
		if($absNum < 1000){
			return (string)round($num, $precision);
		}
		$groups = ['k', 'M', 'B', 'T', 'Q'];
		foreach($groups as $i => $group){
			$div = 1000 ** ($i + 1);
			if($absNum < $div * 1000){
				return round($num / $div, $precision) . $group;
			}
		}
		return '999Q+';
	}
	/**
	 * @param $out
	 * @return string
	 */
	public static function toConstantName($out): string{
		return ConstantGenerator::toConstantName($out);
	}
	/**
	 * @param $name
	 * @return string
	 */
	public static function snakeToTitle($name): string{
		return self::titleCaseSlow(str_replace("_", " ", $name));
	}
	/**
	 * @param string $table
	 * @return string
	 */
	public static function singularTitleFromTable(string $table): string{
		return self::snakeToSingularTitle($table);
	}
	/**
	 * @param string $snake
	 * @return string
	 */
	public static function snakeToSingularTitle(string $snake): string{
		$class = self::snakeToTitle($snake);
		return self::singularize($class);
	}
	/**
	 * @param string $path
	 * @return string
	 */
	public static function getShortClassNameFromFilePath(string $path): string{
		$class = self::afterLast($path, '/', $path);
		$class = self::before('.', $class);
		return $class;
	}
	/**
	 * @param string $fullClassName
	 * @return string
	 */
	public static function toShortClassName(string $fullClassName): string{
		if(isset(static::$cache[__FUNCTION__][$fullClassName])){
			return static::$cache[__FUNCTION__][$fullClassName];
		}
		if(strpos($fullClassName, '_') !== false){
			return static::$cache[__FUNCTION__][$fullClassName] = self::toClassName($fullClassName);
		}
		$res = self::afterLast($fullClassName, '\\');
		//if($res === "errors"){le('$res === "errors"');}
		return static::$cache[__FUNCTION__][$fullClassName] = ucfirst($res);
	}
	/**
	 * @param string $value
	 * @return array|string|string[]
	 */
	public static function humanizeFieldName(string $value){
		$value = str_replace('_at', '', $value);
		$value = str_replace('_', ' ', $value);
		$value = self::titleCaseSlow($value);
		$value = str_replace('_', ' ', $value);
		if($value === 'Id'){
			$value = 'ID';
		}
		$value = str_replace('Id', 'ID', $value);
		return $value;
	}
	/** @noinspection PhpUnused */
	public static function fieldToTitle(string $field): string{
		return self::humanizeFieldName($field);
	}
	/**
	 * @param string $table
	 * @return string
	 */
	public static function tableToRouteName(string $table): string{
		if($table === User::TABLE){
			return "users";
		}
		return QMStr::camelize($table);
	}
	/**
	 * @param string $name
	 * @return string
	 */
	public static function snakeToClassName(string $name): string{
		return self::toClassName($name);
	}
	/**
	 * @param string $start
	 * @param string $end
	 * @param string $haystack
	 * @return array|string|string[]
	 */
	public static function removeBetweenAndIncluding(string $start, string $end, string $haystack){
		$needle = self::between($haystack, $start, $end, null);
		if(!$needle){
			return $haystack;
		}
		return str_replace($start . $needle . $end, "", $haystack);
	}
	/**
	 * @param string $str
	 * @return array|string|string[]
	 */
	public static function removeDBPrefixes(string $str){
		foreach(self::PREFIXES_TO_REPLACE as $find => $replace){
			$str = str_replace($find, $replace, $str);
		}
		return $str;
	}
	/**
	 * @param string $route
	 * @return string
	 */
	public static function addDBPrefixes(string $route): string{
		if(strpos($route, "oAuth") === 0){
			$route = str_replace("oAuth", "OA", $route);
		}
		if(strpos($route, "oauth_") === 0){
			$route = "oa_" . $route;
		}
		return $route;
	}
	/**
	 * @param string $str
	 * @return array|string|string[]
	 */
	public static function stripPrefixes(string $str){
		return self::removeDBPrefixes($str);
	}
	/**
	 * @param string $needle
	 * @param string $haystack
	 * @param int $surroundingChars
	 * @return string
	 */
	public static function getSurrounding(string $needle, string $haystack, int $surroundingChars = 50): string{
		if(strlen($haystack) < (strlen($needle) + 2 * $surroundingChars)){
			return $haystack;
		}
		$pos = stripos($haystack, $needle);
		$start = $pos - $surroundingChars;
		$length = strlen($needle) + $surroundingChars;
		if($start < 0){
			return substr($haystack, 0, $length) . "...";
		}
		return "..." . substr($haystack, $start, $length) . "...";
	}
	/**
	 * @param int $max
	 * @param string $str
	 * @param string $type
	 * @throws InvalidStringException
	 */
	public static function assertShorterThan(int $max, string $str, string $type){
		self::assertStringShorterThan($max, $str, $type);
	}
	/**
	 * @param string $filePath
	 * @return string
	 */
	public static function filePathToShortClassName(string $filePath): string{
		$parts = explode('/', $filePath);
		$last = end($parts);
		return QMStr::before('.', $last);
	}
	/**
	 * @param string $filePath
	 * @return string
	 */
	public static function pathToNameSpace(string $filePath): string{
		return self::folderToNamespace($filePath);
	}
	/**
	 * @param string $filePath
	 * @return array|string|string[]
	 */
	public static function folderToNamespace(string $filePath){
		if(stripos($filePath, '.') === false){
			$folder = $filePath;
		} else{
			$folder = FileHelper::getFolderFromFilePath($filePath);
		}
		$abs = FileHelper::absPath($folder);
		if(strpos($abs, DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR) !== false){
			$namespace = str_replace(FileHelper::absPath() . DIRECTORY_SEPARATOR.'app', 'App', $abs);
		} elseif(strpos($abs, DIRECTORY_SEPARATOR."tests".DIRECTORY_SEPARATOR) !== false){
			$namespace = str_replace(FileHelper::absPath() . DIRECTORY_SEPARATOR.'tests', 'Tests', $abs);
		} else{
			le('Please fix for ' . __FUNCTION__ . ' for ' . $abs);
			throw new \LogicException();
		}
        $namespace = str_replace(DIRECTORY_SEPARATOR, '\\', $namespace);
		return $namespace;
	}
	/**
	 * @param string $id
	 * @return string
	 */
	public static function camelToSlug(string $id): string{
		$snake = self::snakize($id);
		return self::slugify($snake);
	}
	/**
	 * @param string $s
	 * @return string|string[]
	 */
	public static function unescapeDoubleQuotes(string $s): string{
		$s = str_replace('\"', '"', $s);
		return $s;
	}
	/**
	 * @param string $code
	 * @return string
	 */
	public static function getNamespaceFromCode(string $code): string{
		return QMStr::between($code, "namespace ", ";");
	}
	/**
	 * Convert snake case or kebab case to camelCase
	 * @param $value
	 * @return string
	 */
	public static function snakeKebabToCamelCase($value): string{
		$value = str_replace('-', '', ucwords($value, '-'));
		$value = str_replace('_', '', ucwords($value, '_'));
		return $value;
	}
	/**
	 * @param string $str
	 * @return string
	 */
	public static function removeEmptyLines(string $str){
		$str = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $str);
		$str = self::removeTrailingNewLine($str);
		return $str;
	}
	/**
	 * @param string $START
	 * @param string $END
	 * @param string $html
	 * @return string
	 */
	public static function stripBetween(string $START, string $END, string $html): string{
		return self::replace_between($html, $START, $END, '');
	}
	/**
	 * @param string $class
	 * @return string
	 */
	public static function classToForeignKey(string $class): string{
		$short = self::toShortClassName($class);
		$snake = self::snakize($short);
		$key = $snake . "_id";
		$key = str_replace("qm_", "", $key);
		return $key;
	}
	/**
	 * @param string $key
	 * @return string[]
	 */
	public static function snakeCamelLowercaseVariations(string $key): array{
		if($variations = static::$snakeCamelLowerCaseVariations[$key] ?? []){
			return $variations;
		}
		$variations[] = $key;
		$variations[] = QMStr::toCamelCaseIfSnakeCaseOrSpaces($key);
		$variations[] = QMStr::snakize($key);
		$lowercase = $variations[] = strtolower($key);
		$variations[] = ucfirst($lowercase);
		return static::$snakeCamelLowerCaseVariations[$key] = array_values(array_unique($variations));
	}
	/**
	 * @param $string
	 * @return bool
	 */
	public static function isHtml($string): bool{
		if(!is_string($string)){
			return false;
		}
		return $string != strip_tags($string);
	}
	/**
	 * @param string $input
	 * @return string
	 * @throws InvalidFilePathException
	 */
	public static function sanitizeFilePath(string $input): string{
		// remove illegal file system characters https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
		$input = UrlHelper::stripProtocol($input);
		$path = str_replace(array_merge(array_map('chr', range(0, 31)), [
			'%2F',
			'%3A',
			'<',
			'>',
			':',
			'"',
			//'/',
			'\\',
			'|',
			'?',
			'*',
		]), '-', $input);
		// maximise filename length to 255 bytes http://serverfault.com/a/9548/44086
		//        $ext = pathinfo($name, PATHINFO_EXTENSION);
		//        $name= mb_strcut(pathinfo($name, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($name)) . ($ext ? '.' . $ext : '');
		$path = trim($path);
		FileHelper::validateFilePath($path);
		return $path;
	}
	/**
	 * @param array $syn
	 * @return array
	 */
	public static function getVariations(array $syn): array{
		$variations = $syn;
		foreach($syn as $s){
			$variations = array_merge($variations, self::snakeCamelLowercaseVariations($s));
		}
		return array_unique($variations);
	}
	/**
	 * @param string $needle
	 * @param array $arr
	 * @return bool
	 */
	public static function inArraySnakeCamelInsensitive(string $needle, array $arr): bool{
		return in_array($needle, self::getVariations($arr));
	}
	/**
	 * @param string $haystack
	 * @param             $requiredStrings
	 * @param string $type
	 * @param false $ignoreCase
	 * @param string|null $message
	 * @throws InvalidStringException
	 */
	public static function assertContains(string $haystack, $requiredStrings, string $type, bool $ignoreCase = false,
		string $message = null){
		self::assertStringContains($haystack, $requiredStrings, $type, $ignoreCase, $message);
	}
	/**
	 * @param string $haystack
	 * @param $blackList
	 * @param string $type
	 * @param false $ignoreCase
	 * @param string|null $assertionMessage
	 * @throws InvalidStringException
	 */
	public static function assertDoesNotContain(string $haystack, $blackList, string $type, bool $ignoreCase = false,
		string $assertionMessage = null){
		self::assertStringDoesNotContain($haystack, $blackList, $type, $ignoreCase, $assertionMessage);
	}
	/**
	 * @param int $expected
	 * @param string $needle
	 * @param string $haystack
	 * @param string $type
	 * @throws InvalidStringException
	 */
	public static function assertStringCount(int $expected, string $needle, string $haystack, string $type){
		$actual = substr_count($haystack, $needle);
		if($actual !== $expected){
			throw new InvalidStringException("Should contain $expected occurrences of $needle but contains $actual",
				$haystack, $type);
		}
	}
	/**
	 * @param int $expected
	 * @param string $needle
	 * @param string $haystack
	 * @param string $type
	 * @throws InvalidStringException
	 */
	public static function assertStringCountLessThan(int $expected, string $needle, string $haystack, string $type){
		$actual = substr_count($haystack, $needle);
		if($actual >= $expected){
			throw new InvalidStringException("Should contain less than $expected occurrences of $needle but contains $actual",
				$haystack, $type);
		}
	}
	/**
	 * @param string $str
	 * @return string
	 */
	public static function escapeSingleQuotes(string $str): string{
		return str_replace("'", "\\'", $str);
	}
	/**
	 * @param string $title
	 * @return string
	 */
	private static function titleCaseAcronyms(string $title): string{
		$acronyms = [
			"Api" => "API",
			"Php" => "PHP",
			"Sdk" => "SDK",
			"Js" => "JS",
			"Css" => "CSS",
		];
		foreach($acronyms as $search => $replace){
			$title = str_replace($search, $replace, $title);
		}
		return $title;
	}
	/**
	 * @param string $title
	 * @return string
	 */
	private static function titleCaseArticles(string $title): string{
		$articles = [
			'Of' => 'of',
			'As' => 'as',
			'At' => 'at',
		];
		foreach($articles as $upper => $lower){
			$title = str_replace(' ' . $upper . ' ', ' ' . $lower . ' ', $title);
		}
		return $title;
	}
	/**
	 * @param string $code
	 * @param string $appendStr
	 * @return string
	 */
	public static function appendCode(string $code, string $appendStr): string{
		if(strpos($code, $appendStr) !== false){
			return $code;
		}
		$codeArr = explode("\n", $code);
		$last = array_pop($codeArr);
		$appendArr = explode("\n", $appendStr);
		$codeArr = array_merge($codeArr, $appendArr, [$last]);
		$str = implode("\n", $codeArr);
		return self::removeEmptyLines($str);
	}
	/**
	 * @param string $prefix
	 * @param string $string
	 * @return string
	 */
	public static function addPrefixIfNecessary(string $prefix, string $string): string{
		if(strpos($string, $prefix) === 0){
			return $string;
		}
		return $prefix . $string;
	}
	/**
	 * @param string|null $value
	 * @return string|null
	 */
	public static function wrapInQuotesIfNecessary(?string $value): ?string{
		if(strpos($value, "'") === false && strpos($value, '"') === false){
			$value = "'$value'";
		}
		return $value;
	}
	/**
	 * @param string $old
	 * @return string
	 */
	public static function stripNewLines(string $old): string{
		return str_replace("\n", " ", $old);
	}
	/**
	 * @param string $str
	 * @return string
	 */
	public static function getBeforeNumbers(string $str): string{
		$words = explode(" ", $str);
		$keep = [];
		foreach($words as $word){
			if(is_numeric($word)){
				break;
			}
			$keep[] = $word;
		}
		return implode(" ", $keep);
	}
	/**
	 * @param string $str
	 * @return string
	 */
	public static function replaceBackslashesWithForwardSlashes(string $str): string{
		return str_replace('\\', '/', $str);
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function doubleForwardSlash(string $string): string{
		return str_replace("\\", "\\\\", $string);
	}
	/**
	 * @param $new
	 * @return string
	 */
	public static function efficientPrint($new, int $maxLength = null): string{
		if(is_array($new) || is_object($new)){
			return gettype($new);
		} else{
			if($new === null){
				return "NULL";
			}
			if(is_string($new) && $maxLength){
				$new = QMStr::truncate($new, $maxLength);
			}
			return \App\Logging\QMLog::print_r($new, true);
		}
	}
	/**
	 * @param mixed $mixed
	 * @return string
	 */
	public static function toString($mixed, int $maxLength = null): ?string{
		if($mixed === null){return "";}
		if(is_array($mixed) || is_object($mixed)){
			$mixed = json_encode($mixed);
		}
		if($maxLength && is_string($mixed)){$mixed = self::truncate($mixed, $maxLength);}
		return $mixed;
	}
	/**
	 * @param $str
	 * @return bool
	 */
	public static function isNullString($str): bool{
		return is_string($str) && strtoupper($str) === Enum::NULL;
	}
	/**
	 * Count the number of substring occurrences
	 * @link https://php.net/manual/en/function.substr-count.php
	 * @param string $haystack The string to search in
	 * @param string $needle The substring to search for
	 * @return int
	 */
	public static function numberOfOccurrences(string $haystack, string $needle): int{
		return substr_count($haystack, $needle);
	}
	/**
	 * @param $val
	 */
	public static function assertIsString($val){
		if(!is_string($val)){
			le("should be string but is " . \App\Logging\QMLog::print_r($val, true));
		}
	}
	/**
	 * @param int $length
	 * @return string
	 */
	public static function random(int $length = 10): string{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for($i = 0; $i < $length; $i++){
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	/**
	 * @param string $name
	 * @param string $str
	 */
	public static function logSizeOfString(string $name, string $str){
		$size = self::getSizeOfString($str);
		QMLog::info($name . " is $size");
	}
	/**
	 * @param string $str
	 * @return string
	 */
	public static function getSizeOfString(string $str): string{
		return ObjectHelper::getSizeHumanized($str);
	}
	/**
	 * @param string $str
	 * @return string
	 */
	public static function getSizeOfStringInBytes(string $str): string{
		return strlen($str);
	}
	/**
	 * @param string $str
	 * @return string
	 */
	public static function getSizeOfStringInKB(string $str): string{
		return strlen($str) / 1024;
	}
	/**
	 * @param string $string
	 * @return string
	 */
	public static function getSizeOfStringHumanized(string $string): string{
		$size = strlen($string);
		return QMStr::human_filesize($size);
	}
	/**
	 * @param string $getFullClass
	 * @return string
	 */
	public static function classToNameSpace(string $getFullClass): string{
		return PhpClassFile::classToNameSpace($getFullClass);
	}
	/**
	 * @param $responseBody
	 * @return string
	 */
	public static function jsonEncodeIfNecessary($responseBody): ?string{
		if($responseBody === null){
			return null;
		}
		if(!is_string($responseBody)){
			return json_encode($responseBody);
		}
		return $responseBody;
	}
	/**
	 * @param string $str
	 * @return array
	 */
	public static function explodeNewLines(string $str): array{
		$out = explode("\n", $str);
		return $out;
	}
	/**
	 * @param string $fullClass
	 * @return string
	 */
	public static function getFilePathToClass(string $fullClass): string{
		return FileHelper::getFilePathToClass($fullClass);
	}
	/**
	 * @param $val
	 * @return bool|string
	 */
	public static function print($val): string{
		if(!is_string($val)){
			$val = \App\Logging\QMLog::print_r($val, true);
		}
		return $val;
	}
	/**
	 * @param string $filePath
	 * @return string
	 */
	public static function pathToClass(string $filePath): string{
		return FileHelper::pathToClass($filePath);
	}
	/**
	 * @param string $code
	 * @return string
	 */
	public static function removeNewLinesBeforeOpenBrackets(string $code): string{
		$code = str_replace("
	{", "{", $code);
		$code = str_replace("
{", " {", $code);
		return $code;
	}
	/**
	 * @param string $code
	 * @return string
	 */
	public static function formatPHP(string $code): string{
		$code = QMStr::removeNewLinesBeforeOpenBrackets($code);
		$code = QMStr::removeEmptyLines($code);
		if(stripos($code, "<?php") === false){
			$code = "<?php\n" . $code;
		}
		return $code;
	}
	/**
	 * @param $output
	 * @return string
	 */
	public static function stripDates($output): string{
		$output = self::removeTimesFromString($output, "_TIME_WAS_HERE_");
		$output = self::removeDatesFromString($output, "_DATE_WAS_HERE_");
		$output = self::removeUnixTimestampsFromString($output, "_UNIXTIME_WAS_HERE_");
		return $output;
	}
	/**
	 * @param $class
	 * @return string
	 */
	public static function classToPath($class): string{
		return FileHelper::classToPath($class);
	}
	/**
	 * @param $class
	 * @return string
	 */
	public static function methodToPath($class): string{
		$slug = str_replace("::", "-", $method);
		$slug = self::slugify($class);
		return str_replace("-", "/", $slug);
	}
	/**
	 * @param string $str
	 * @return string
	 */
	public static function stripRepoPaths(string $str): string{
		$str = str_replace(FileHelper::absPath() . "/", "", $str);
		return $str;
	}
	/**
	 * @param string $str
	 * @return string
	 */
	public static function replaceAccessTokens(string $str): string{
		$u = QMAuth::getQMUserIfSet();
		if($u && $u->accessToken){
			$str = str_replace($u->accessToken, "_USER_{$u->id}_ACCESS_TOKEN_WAS_HERE_", $str);
		}
		$tokens = QMAccessToken::getAllFromMemoryIndexedById();
		foreach($tokens as $t){
			$str = str_replace($u->accessToken, "_USER_{$t->getUserId()}_ACCESS_TOKEN_WAS_HERE_", $str);
		}
		return $str;
	}
	/**
	 * @param string $str
	 * @param int $max
	 * @param string $type
	 * @throws InvalidStringException
	 */
	public static function validateMaxLength(string $str, int $max, string $type){
		if(strlen($str) > $max){
			throw new InvalidStringException("$type must be less than 150 characters!", $str, $type);
		}
	}
	/**
	 * @param string $s3Path
	 * @return string
	 */
	public static function pathToTitle(string $s3Path): string{
		$s3Path = str_replace("/", " ", $s3Path);
		$s3Path = str_replace("-", " ", $s3Path);
		return self::titleCaseFast($s3Path);
	}
	/**
	 * @param string $path
	 * @return string
	 */
	public static function fileToTitle(string $path): string{
		$s3Path = FileHelper::name($path, true);
		$s3Path = str_replace("-", " ", $s3Path);
		return self::titleCaseFast($s3Path);
	}
	/**
	 * @param array|object $arrOrObj
	 * @return bool|string
	 */
	public static function printNotNullOrEmptyStringValues($arrOrObj): string{
		return \App\Logging\QMLog::print_r(QMArr::notEmptyValues($arrOrObj), true);
	}
	/**
	 * @param array|object $arrOrObj
	 * @return bool|string
	 */
	public static function printStringsAndNumbers($arrOrObj): string{
		return \App\Logging\QMLog::print_r(QMArr::getStringsAndNumbers($arrOrObj), true);
	}
	/**
	 * @param string $trimmed
	 * @param string $name
	 */
	public static function assertNotEmptyOrNull(string $trimmed, string $name){
		if($trimmed === ""){
			le("Content for $name is empty!");
		}
		if($trimmed === "null"){
			le("Content for $name is 'null'!");
		}
	}
	/**
	 * @param string $message
	 * @return string
	 */
	public static function removeBlankLines(string $message): string{
		return self::removeEmptyLines($message);
	}
	/**
	 * @param string $text
	 * @return string
	 */
	public static function removeNewLines(string $text): string{
		return str_replace(["\n", "\r"], '', $text);
	}
	/**
	 * @param string $haystack
	 * @param string $needle
	 * @return bool
	 */
	public static function contains(string $haystack, string $needle): bool{
		if(isset(self::$contains[$haystack][$needle])){
			return self::$contains[$haystack][$needle];
		}
		$r = stripos($haystack, $needle) !== false;
		return self::$contains[$haystack][$needle] = $r;
	}
	/**
	 * @param string $haystack
	 * @param array $needles
	 * @return bool
	 */
	public static function containsAny(string $haystack, array $needles): bool{
		foreach($needles as $needle){
			if(self::contains($haystack, $needle)){
				return true;
			}
		}
		return false;
	}
	/**
	 * @param string $after
	 * @param string $new
	 * @param string $target
	 * @return array|string|string[]
	 */
	public static function insertAfterFirstLineContaining(string $after, string $new, string $target){
		return str_replace($after, $after . "\n" . $new, $target);
	}
	/**
	 * @param string $upper
	 * @return string
	 */
	public static function toLower(string $upper): string{
		if(isset(self::$lower[$upper])){
			return self::$lower[$upper];
		}
		return self::$lower[$upper] = strtolower($upper);
	}
	/**
	 * @param string $upper
	 * @return string
	 */
	public static function upper(string $upper): string{
		if(isset(self::$upper[$upper])){
			return self::$upper[$upper];
		}
		return self::$upper[$upper] = strtoupper($upper);
	}
	/**
	 * @param string $c
	 * @return string
	 */
	public static function indent(string $c): string{
		return HtmlHelper::indent($c);
	}
	/**
	 * @param $str
	 * @return string
	 */
	public static function removeTrailingNewLine($str): string{
		$str = rtrim($str);
		return $str; // Remove trailing new line
	}
	public static function stripExtension(string $path): string{
		$res = self::beforeLast(".", $path);
		if(!$res){
			le("no extension on $path");
		}
		return $res;
	}
	public static function startsWith(string $haystack, string $needle): string{
		return stripos($haystack, $needle) === 0;
	}

    /**
     * @param string $str
     * @param string $type
     * @throws InvalidStringException
     */
    public static function assertNotJson(string $str, string $type)
    {
        if (stripos($str, "{") === false) {
            return;
        }
        throw new InvalidStringException("$type should not be a json string but is $str", $str, __FUNCTION__);
    }

    /**
     * @param string $haystack
     * @param array|string $requiredStrings
     * @param string $type
     * @param bool $ignoreCase
     * @param string|null $message
     * @throws InvalidStringException
     */
    public static function assertStringContains(string $haystack, $requiredStrings, string $type,
                                                bool   $ignoreCase = false, string $message = null)
    {
        if (!is_array($requiredStrings)) {
            $requiredStrings = [$requiredStrings];
        }
        foreach ($requiredStrings as $expected) {
            $m = "$type must contain $expected";
            if ($ignoreCase) {
                if (stripos($haystack, $expected) === false) {
                    throw new InvalidStringException($message ?? $m, $haystack, $type);
                }
            } else {
                if (strpos($haystack, $expected) === false) {
                    throw new InvalidStringException($message ?? $m, $haystack, $type);
                }
            }
        }
    }

    /**
     * @param string $url
     * @param string $type
     * @throws InvalidUrlException
     */
    public static function assertIsUrl(string $url, string $type)
    {
        if (stripos($url, "#") === 0) {
            return;
        }
        if ($url === "javascript:void(0)") {
            return;
        } // Prevents jumping to the top of page
        if (strlen($url) > 2083) {
            if (stripos($url, '/test') === false) {
                throw new InvalidUrlException("Too long for url", $url, $type);
            }
        }
        if (UrlHelper::urlInvalid($url)) {
            if (stripos($url, 'oauth_test_client') !== false) {
                return;
            }
            throw new InvalidUrlException("$type URL: $url is not a valid URL", $url, $type);
        }
    }

    /**
     * @param string $haystack
     * @param array|string $requiredStrings
     * @param string $type
     * @throws InvalidStringException
     */
    public static function assertStringContainsOneOf(string $haystack, $requiredStrings, string $type)
    {
        $found = false;
        if (!is_array($requiredStrings)) {
            $requiredStrings = [$requiredStrings];
        }
        foreach ($requiredStrings as $expected) {
            if (stripos($haystack, $expected) !== false) {
                $found = true;
            }
        }
        if (!$found) {
            throw new InvalidStringException("$type must contain one of " . \App\Logging\QMLog::print_r($requiredStrings, true), $haystack,
                __FUNCTION__);
        }
    }

    /**
     * @param string $haystack
     * @param array|string $blackList
     * @param string $type
     * @param bool $ignoreCase
     * @param string|null $assertionMessage
     * @throws InvalidStringException
     */
    public static function assertStringDoesNotContain(string $haystack, $blackList, string $type,
                                                      bool   $ignoreCase = false, string $assertionMessage = null)
    {
        if (!is_array($blackList)) {
            $blackList = [$blackList];
        }
        foreach ($blackList as $word) {
            if ($ignoreCase) {
                if (stripos($haystack, $word) !== false) {
                    throw new BlackListedStringException($word, $haystack, $type, $assertionMessage);
                }
            } else {
                if (strpos($haystack, $word) !== false) {
                    throw new BlackListedStringException($word, $haystack, $type, $assertionMessage);
                }
            }
        }
    }

    /**
     * @param int $max
     * @param string $str
     * @param string $type
     * @throws InvalidStringException
     */
    public static function assertStringShorterThan(int $max, string $str, string $type)
    {
        $length = strlen($str);
        if ($length > $max) {
            throw new InvalidStringException("$type must be shorter than $max characters but is $length", $str, $type);
        }
    }
    public static function isInt($data): bool{
        if (is_int($data) === true) return true;
        if (is_string($data) === true && is_numeric($data) === true) {
            return (strpos($data, '.') === false);
        }
        return false;
    }
    public static function afterAndIncluding(string $haystack, string $needle){
		$pos = strpos($haystack, $needle);
		if ($pos === false) {
			return false;
		}
		return substr($haystack, $pos);
    }
}
