<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\CodeGenerators;
use App\Files\PHP\UnitTestFile;
use App\Folders\DynamicFolder;
use App\Logging\QMLog;
class PhpUnitTestGenerator {
	public static function generatePhpUnitTest(string $class): string{
		$file = self::getTestFile($class);
		return $file->generate();
	}
	/**
	 * @param string $class
	 * @return UnitTestFile
	 */
	public static function getTestFile(string $class): UnitTestFile{
		return UnitTestFile::findOrNew($class);
	}
	/**
	 * @param string $class
	 * @return string
	 */
	public static function getTestFilePath(string $class): string{
		$file = self::getTestFile($class);
		return $file->getPath();
	}
	/**
	 * @param string $class
	 * @return string
	 */
	public static function getTestClass(string $class): string{
		$file = self::getTestFile($class);
		return $file->getClassName();
	}
	public static function generateForFolder(string $folder){
		$classes = DynamicFolder::getClassesInFolder($folder);
		foreach($classes as $class){
			try {
				PhpUnitTestGenerator::generatePhpUnitTest($class);
			} catch (\Throwable $e) {
				QMLog::info(__METHOD__.": ".$e->getMessage());
			}
		}
	}
}
