<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\PHP;
use App\CodeGenerators\TVarDumper;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Types\QMStr;
class ConstantGenerator {
	/**
	 * @param string $name
	 * @param null $val
	 */
	public static function addConstant(string $name, $val = null): void{
		FileHelper::addConstant($name, $val);
	}
	public static function dumpConstants(array $arr): void{
		foreach($arr as $item){
			self::dumpConstant($item);
		}
	}
	/**
	 * @param string $name
	 * @param null $val
	 */
	public static function dumpConstant(string $name, $val = null): void{
		\App\Logging\ConsoleLog::info(self::generateConstantDeclarationLine($name, $val));
	}
	/**
	 * @param string $name
	 * @param null $val
	 * @return string
	 */
	public static function generateConstantDeclarationLine(string $name, $val = null): string{
		if(!$val){
			$val = $name;
		}
		$dumped = TVarDumper::dump($val);
		return "\tconst " . QMStr::toConstantName($name) . " = " . $dumped . ";";
	}
	/**
	 * @param $constName
	 * @return string
	 */
	public static function toConstantName($constName): string{
		$constName = QMStr::replaceDisallowedVariableCharactersWithUnderscore($constName);
		$constName = strtoupper($constName);
		return $constName;
	}
	public static function replaceStringsWithConstantReferences(){
	}
	public static function replaceColumnStringsWithConstants(string $path){
		$classes = static::getClasses();
		foreach($classes as $class){
			$withoutSlash = QMStr::removeFirstCharacter($class);
			$shortClass = QMStr::toShortClassName($class);
			\App\Logging\ConsoleLog::info("Checking fields for " . $class . "...");
			$fields = $class::getColumns();
			foreach($fields as $field){
				$needle = "'$field'";
				$files = FileFinder::getFilesContaining($path, $needle, true);
				\App\Logging\ConsoleLog::info(count($files) . " files containing $needle in $path...");
				foreach($files as $file){
					\App\Logging\ConsoleLog::info($file);
					try {
						$contents = FileHelper::getContents($file);
					} catch (QMFileNotFoundException $e) {
						/** @var LogicException $e */
						throw $e;
					}
					$contents = str_replace($needle, $shortClass . '::FIELD_' . strtoupper($field), $contents);
					if(strpos($contents, 'use ' . $withoutSlash) === false){
						$contents = str_replace(';
class ', ";
use $withoutSlash;
class ", $contents);
						FileHelper::writeByFilePath($file, $contents);
					}
				}
			}
		}
	}
}
