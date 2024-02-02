<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Traits;
use App\Files\FileHelper;
use App\Logging\ConsoleLog;
use App\Types\QMStr;
trait ConstantGenerator {
	use ConstantSearch;
	public static function outputConstant(string $value){
		$name = self::generateConstantName($value);
		if(strpos($value, "http") === 0){
			ConsoleLog::info("public const $name = '$value'; // $value");
		} else{
			ConsoleLog::info("public const $name = '$value';");
		}
	}
	public static function outputConstants(): array{
		$values = self::generateConstantValues();
		$constants = [];
		foreach($values as $value){
			$constants[self::generateConstantName($value)] = (string)$value;
		}
		ksort($constants);
		foreach($constants as $value){
			static::outputConstant($value);
		}
		return $constants;
	}
	abstract public static function generateConstantName(string $str): string;
	/**
	 * @param string $path
	 * @return string
	 */
	protected static function pathToConstantName(string $path): string{
		$path = relative_path($path);
		$name = str_replace('/', "_", $path);
		$name = QMStr::stripExtension($name);
		return QMStr::toScreamingSnakeCase($name);
	}
	/**
	 * @param string $path
	 * @return string
	 */
	protected static function folderPathToConstantName(string $path): string{
		$path = relative_path($path);
		$name = str_replace('/', "_", $path);
		return QMStr::toScreamingSnakeCase($name);
	}
	public static function urlToConstantName(string $url): string{
		$name = QMStr::afterLast($url, '/');
		$name = QMStr::before('.', $name, $name);
		return QMStr::toScreamingSnakeCase($name);
	}
	protected static function generateAndAddConstant(string $str){
		$name = self::generateConstantName($str);
		FileHelper::addConstant($name, static::class, "'$str'");
	}
	public static function updateConstants(){
		$values = self::generateConstantValues();
		foreach($values as $value){
			$name = self::generateConstantName($value);
			FileHelper::addConstant($name, static::class, $value);
		}
	}
	public static function generateAndOutputConstant(string $str){
		self::outputConstant($str);
	}
	/**
	 * @return string[]
	 */
	abstract protected static function generateConstantValues(): array;
	public static function outputConstantsAsArray(): string{
		$names = self::getConstantNames();
		foreach($names as $i => $name){
			$names[$i] = "self::" . $name;
		}
		$str = "[\n" . implode(",\n", $names) . "\n]";
		ConsoleLog::info($str);
		return $str;
	}
	public static function getConstantNames(): array{
		$values = self::generateConstantValues();
		$names = [];
		foreach($values as $value){
			$names[] = self::generateConstantName($value);
		}
		return $names;
	}
}
