<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files;
use App\Exceptions\InvalidFilePathException;
use App\Files\Bash\BashLibScriptFile;
use App\Files\PHP\AbstractPhpFile;
use App\Folders\DynamicFolder;
use Illuminate\Support\Collection;
use SplFileInfo;
abstract class TypedProjectFile extends UntypedFile {
	const GLOBAL_CONTENT_PREFIX = null;
	abstract public static function getDefaultFolderRelative(): string;
	/**
	 * @param array|null $folders
	 * @return static[]|Collection
	 */
	public static function get(array $folders = [], string $pathNotLike = null): Collection{
		if(!$folders){
			$folders = static::getFolderPaths();
		}
		$ext = static::getDefaultExtension();
		$fileInfos = FileFinder::listProjectFiles("." . $ext, $folders, $pathNotLike, $ext);
		$files = self::instantiateArray($fileInfos);
		return collect($files);
	}
	/**
	 * @return string[]
	 * Override in child if multiple folders
	 */
	public static function getFolderPaths(): array{
		return [
			DynamicFolder::FOLDER_SCRIPTS,
			DynamicFolder::FOLDER_CONFIGS,
			DynamicFolder::FOLDER_CONFIG,
			DynamicFolder::FOLDER_APP,
			DynamicFolder::FOLDER_TESTS,
		];
	}
	/**
	 * @return DynamicFolder[]
	 * Override in child if multiple folders
	 */
	public static function getFolders(): array{
		$arr = [];
		foreach(self::getFolderPaths() as $path){
			$arr[$path] = new DynamicFolder($path);
		}
		return $arr;
	}
	public static function moveAllToDefaultFolder(){
		/** @var static[] $notInDefaultFolder */
		$notInDefaultFolder = static::getFilesNotInDefaultFolder();
		foreach($notInDefaultFolder as $file){
			$file->moveToDefaultFolder();
		}
	}
	public static function getDefaultFolderAbs(): string{ return FileHelper::absPath(static::getDefaultFolderRelative()); }
	/**
	 * @param string $folder
	 * @param bool $recursive
	 * @param string|null $notLike
	 * @return SplFileInfo[]
	 */
	public static function getFilesInFolder(string $folder, bool $recursive = true, string $notLike = null): array{
		return FileFinder::getFilesInFolder($folder, $recursive, "." . static::getDefaultExtension(), $notLike);
	}
	/**
	 * @param string $file
	 * @return string
	 */
	public static function stripExtension(string $file): string{
		return str_replace("." . static::getDefaultExtension(), "", $file);
	}
	/**
	 * @return static[]|Collection
	 */
	public static function getFilesNotInDefaultFolder(): Collection{
		$all = static::get();
		$notInDefault = $all->filter(function($s){
			/** @var static $s */
			return $s->getRelativeFolder() === static::getDefaultFolderRelative();
		});
		return $notInDefault;
	}
	abstract public static function getDefaultExtension(): string;
	/**
	 * @param string $needle
	 * @return AbstractPhpFile[]
	 */
	public static function getProjectFilesContaining(string $needle): array{
		$files = [];
		foreach(static::getFolderPaths() as $folder){
			$files = array_merge($files,
				static::getContaining($folder->getPath(), $needle, true, static::getDefaultExtension()));
		}
		return $files;
	}
	/**
	 * @param string $needle
	 * @param string $folder
	 * @return FileLine[]
	 */
	public static function getFilesAndLinesContaining(string $needle, string $folder): array{
		$files = static::getContaining($folder, $needle, true, static::getDefaultExtension());
		$lines = [];
		foreach($files as $file){
			$lines = array_merge($lines, $file->linesContaining($needle));
		}
		return $files;
	}
	/**
	 * @param string $needle
	 * @return \SplFileInfo[]
	 */
	public static function getProjectFileNamesContaining(string $needle): array{
		$files = [];
		foreach(static::getFolderPaths() as $folder){
			$files = array_merge($files,
				FileFinder::getFileNamesContaining($folder, $needle, true, static::getDefaultExtension()));
		}
		return $files;
	}
	public static function getPaths(): array{
		return static::get()->pluck('absPath')->all();
	}

    protected function moveToDefaultFolder(){
		$folder = static::getDefaultFolderRelative();
		$this->moveAndReplaceReferences($folder);
	}
	/**
	 * @param string $newFolder
	 */
	protected function moveAndReplaceReferences(string $newFolder): void{
		$this->move($newFolder);
		$this->replaceReferences($newFolder);
	}
	/**
	 * @param string $newFolder
	 */
	protected function replaceReferences(string $newFolder): void{
		$oldPath = $this->getPath();
		$newPath = $newFolder . "/" . $this->getFileName();
		static::replaceInAll($oldPath, $newPath);
	}
	public static function getDefaultFolder(): DynamicFolder{ return new DynamicFolder(static::getDefaultFolderAbs()); }
	/**
	 * @param string $search
	 * @param string $replace
	 */
	public static function replaceInAll(string $search, string $replace): void{
		$files = static::get();
		foreach($files as $file){
			$file->replace($search, $replace);
		}
	}
	/**
	 * @return string[]
	 */
	public static function allPaths(): array{
		$files = static::get();
		$paths = $files->map(function($f){
			/** @var BashLibScriptFile $f */
			return $f->getPath();
		});
		return $paths->all();
	}
	public static function appendToAllIfMissing(string $str){
		$files = static::get();
		foreach($files as $file){
			$file->appendIfAbsent($str);
		}
	}
	public static function prefixToAllIfMissing(string $str){
		$files = static::get();
		foreach($files as $file){
			$file->prefixIfAbsent($str);
		}
	}
	/**
	 * @param string $needle
	 * @return FileLine[]
	 */
	public static function getProjectLinesContaining(string $needle): array{
		$files = static::getProjectFilesContaining($needle);
		$lines = [];
		foreach($files as $file){
			$lines = array_merge($lines, $file->linesContaining($needle));
		}
		return $files;
	}
	/**
	 * @param string $needle
	 * @return FileLine[]
	 */
	public static function getProjectLinesStartingWith(string $needle): array{
		$files = static::getProjectFilesContaining($needle);
		$lines = [];
		foreach($files as $file){
			$lines = array_merge($lines, $file->getLinesStartingWith($needle));
		}
		return $lines;
	}
	public static function insertAfterMissing(string $toInsert, string $contentsOfPrecedingLine){
		$files = static::get();
		foreach($files as $file){
			$file->insertAfterIfAbsent($toInsert, $contentsOfPrecedingLine);
		}
	}

    /**
	 * @return static[]|Collection
	 */
	public static function all(){
		return static::get();
	}
	/**
	 * @return string[]
	 */
	public static function allRelativePaths(): array{
		$all = static::all();
		$paths = [];
		foreach($all as $item){
			$paths[] = $item->getRelativePath();
		}
		return $paths;
	}
}
