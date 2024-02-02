<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\PHP;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileExtension;
use App\Files\FileHelper;
use App\Files\TypedProjectFile;
use App\Folders\DynamicFolder;
/**
 * @package App\Files\PHP
 */
abstract class AbstractPhpFile extends TypedProjectFile {
	public const EXTENSION = FileExtension::PHP;
	/**
	 * @param $path
	 * @return bool
	 */
	public static function pathIsTraitOrClass($path): bool{
		return self::pathIsClass($path) || self::pathIsTrait($path);
	}
	/**
	 * @param $path
	 * @return bool
	 */
	public static function pathIsTrait($path): bool{
		try {
			return (bool)FileHelper::hasLineStartingWith($path, "trait ");
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
	}
	/**
	 * @param $path
	 * @return bool
	 */
	public static function pathIsClass($path): bool{
		try {
			return FileHelper::hasLineStartingWith($path, "class ") || self::isAbstractClass($path);
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
	}
	/**
	 * @param $path
	 * @return bool
	 */
	public static function isAbstractClass($path): bool{
		try {
			return (bool)FileHelper::hasLineStartingWith($path, "abstract class ");
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
	}
	/**
	 * @param string $folder
	 * @param bool $recursive
	 * @param string|null $notLike
	 * @return static[]
	 */
	public static function getTraitsAndClassesInFolder(string $folder, bool $recursive = false,
		string $notLike = null): array{
		$files = [];
		$paths = static::getFilesInFolder($folder, $recursive, $notLike);
		foreach($paths as $path){
			if(static::pathIsTraitOrClass($path)){
				$files[(string)$path] = new PhpClassFile($path);
			}
		}
		return $files;
	}
	/**
	 * @return string
	 */
	public static function getDefaultExtension(): string{ return FileExtension::PHP; }
	/**
	 * @param $path
	 * @return PhpClassFile|TraitFile
	 */
	public static function instantiate($path): self{
		try {
			$contents = FileHelper::getContents($path);
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
		if(strpos($contents, "\ntrait ") !== false){
			return new TraitFile($path);
		} elseif(strpos($contents, 'class') !== false){
			return new PhpClassFile($path);
		} else{
			return new PhpScriptFile($path);
			//le("Please implement instantiation for $path");throw new \LogicException();
		}
	}
	/**
	 * @return string[]
	 * Override in child if multiple folders
	 */
	public static function getFolderPaths(): array{
		return [
			DynamicFolder::FOLDER_APP,
			DynamicFolder::FOLDER_CONFIG,
			DynamicFolder::FOLDER_CONFIGS,
			DynamicFolder::FOLDER_SCRIPTS,
			DynamicFolder::FOLDER_TESTS,
		];
	}
}
