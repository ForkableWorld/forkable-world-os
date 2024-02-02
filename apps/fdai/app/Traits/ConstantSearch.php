<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Fields\HasMany;
use App\Models\BaseModel;
use App\Storage\DB\QMDB;
use App\Storage\DB\Writable;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
trait ConstantSearch {
	use HasConstants;
	protected static $lowercase;
	protected static $searchResults;
	protected static $nameSearchResults;
	protected static $contains;
	protected static $containsEntireWord;
	/**
	 * @return string[]
	 */
	private static function getLowerCaseConstants(): array{
		if(!self::$lowercase){
			$constants = self::getConstants();
			foreach($constants as $constantName => $iconName){
				self::$lowercase[strtolower($constantName)] = $iconName;
			}
		}
		return self::$lowercase;
	}
	public static function findConstantValueWithNameLike(string $search, string $default = null): ?string{
		if(isset(self::$searchResults[$search])){
			if(self::$searchResults[$search] === false){
				return $default;
			}
			return self::$searchResults[$search];
		}
		$constants = static::getLowerCaseConstants();
		$arr = explode(" ", $search);
		usort($arr, function($a, $b){ return strlen($a) < strlen($b); });
		$lowerCaseNeedles = [];
		foreach($arr as $key => $upperCaseWord){
			if(!empty($upperCaseWord)){
				$lowerCaseNeedles[$key] = strtolower($upperCaseWord);
			}
		}
		foreach($lowerCaseNeedles as $word){
			foreach($constants as $constantName => $url){
				if(!isset(self::$contains[$constantName][$word])){
					self::$contains[$constantName][$word] = strpos($constantName, $word) !== false;
				}
				if(self::$contains[$constantName][$word]){
					return self::$searchResults[$search] = $url;
				}
			}
		}
		if(!$default){
			if(!isset(self::$searchResults[$search])){
				if(static::class === ImageUrls::class){
					\App\Logging\ConsoleLog::info("public const IMAGE_" .
						QMStr::toScreamingSnakeCase($search) . " = self::IMAGE;");
				} else{
					\App\Logging\ConsoleLog::info("public const " . QMStr::toScreamingSnakeCase($search) .
						" = self::;");
				}
			}
			self::$searchResults[$search] = false;
			return $default;
		}
		return self::$searchResults[$search] = $default;
	}
	public static function findConstantNameWithValue($needleValue): ?string{
		$constants = self::getConstants();
		foreach($constants as $name => $value){
			if($value === $needleValue){
				return $name;
			}
		}
		return null;
	}
	public static function findConstantNameLike(string $search, string $default = null): string{
		if(isset(self::$nameSearchResults[$search])){
			if(self::$nameSearchResults[$search] === false){
				return $default;
			}
			return self::$nameSearchResults[$search];
		}
		$screamingSearch = QMStr::toScreamingSnakeCase($search);
		$constants = self::getConstants();
		foreach($constants as $constantName => $iconName){
			if($constantName === $screamingSearch){
				return self::$nameSearchResults[$search] = $constantName;
			}
		}
		$constants = static::getLowerCaseConstants();
		[$search, $lowerCaseNeedles] = self::explodeAndSingularize($search);
		foreach($lowerCaseNeedles as $word){
			foreach($constants as $constantName => $value){
				$wordsInConstName = explode('_', $constantName);
				if(!isset(self::$containsEntireWord[$constantName][$word])){
					self::$containsEntireWord[$constantName][$word] = in_array($word, $wordsInConstName);
				}
				if(self::$containsEntireWord[$constantName][$word]){
					return self::$nameSearchResults[$search] = strtoupper($constantName);
				}
			}
		}
		foreach($lowerCaseNeedles as $word){
			foreach($constants as $constantName => $value){
				if(!isset(self::$contains[$constantName][$word])){
					self::$contains[$constantName][$word] = strpos($constantName, $word) !== false;
				}
				if(self::$contains[$constantName][$word]){
					return self::$nameSearchResults[$search] = strtoupper($constantName);
				}
			}
		}
		if(!$default){
			if(!isset(self::$nameSearchResults[$search])){
				if(static::class === ImageUrls::class){
					\App\Logging\ConsoleLog::info("public const IMAGE_" .
						QMStr::toScreamingSnakeCase($search) . " = self::IMAGE_;");
				} else{
					\App\Logging\ConsoleLog::info("public const IMAGE_" .
						QMStr::toScreamingSnakeCase($search) . " = self::IMAGE_;");
				}
			}
			self::$nameSearchResults[$search] = false;
			return $default;
		}
		return self::$nameSearchResults[$search] = $default;
	}
	/**
	 * @param string $search
	 * @param bool $singularize
	 * @return array
	 */
	public static function explodeAndSingularize(string $search, bool $singularize = true): array{
		$search = self::humanizeFieldName($search);
		$arr = explode(" ", $search);
		if($singularize){
			$lastKey = count($arr) - 1;
			$arr[$lastKey] = QMStr::singularize($arr[$lastKey]);
		}
		usort($arr, function($a, $b){
			return strlen($a) < strlen($b);
		});
		$lowerCaseNeedles = [];
		foreach($arr as $key => $upperCaseWord){
			if(!empty($upperCaseWord)){
				$lowerCaseNeedles[$key] = strtolower($upperCaseWord);
			}
		}
		return [$search, $lowerCaseNeedles];
	}
	/**
	 * @param string $search
	 * @return string
	 */
	public static function humanizeFieldName(string $search): string{
		QMDB::stripPrefixes($search);
		$search = str_replace(HasMany::$number_of_, '', $search);
		$search = str_replace('wp_', '', $search);
		$search = str_replace("_at", "", $search);
		$search = str_replace("_", " ", $search);
		return $search;
	}
	public static function outputForBaseModels(): void{
		$classes = BaseModel::getClasses();
		foreach($classes as $class){
			$short = QMStr::toShortClassName($class);
			$scream = QMStr::toScreamingSnakeCase($short);
			$res = static::findConstantNameWithValue($class::DEFAULT_IMAGE);
			if(static::class === ImageUrls::class){
				if($class::DEFAULT_IMAGE === ImageUrls::PUZZLED_ROBOT){
					$res = ImageUrls::findConstantNameLike($scream);
				}
				\App\Logging\ConsoleLog::info("public const IMAGE_$scream = self::$res;");
			} else{
				if($class::FONT_AWESOME === FontAwesome::QUESTION_CIRCLE){
					$res = FontAwesome::findConstantNameLike($scream);
				}
				\App\Logging\ConsoleLog::info("public const $scream = self::$res;");
			}
		}
	}
	/**
	 * @param string $table
	 */
	public static function outputForTable(string $table){
		$fields = Writable::getFieldsForTable($table);
		foreach($fields as $field){
			$search = ConstantSearch::humanizeFieldName($field);
			$res = FontAwesome::findConstantNameLike($search);
			$upper = QMStr::toScreamingSnakeCase($search);
			if(static::class === ImageUrls::class){
				\App\Logging\ConsoleLog::info("public const IMAGE_$upper = self::$res;");
			} else{
				\App\Logging\ConsoleLog::info("public const $upper = self::$res;");
			}
		}
	}
}
