<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files;
use App\Exceptions\QMFileNotFoundException;
use App\Folders\DynamicFolder;
use App\Types\QMArr;
use App\Types\TimeHelper;
use App\Utils\AppMode;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Tests\Traits\LogsTests;
class FileFinder extends Finder {
	public static array $folderExists = [];
	/**
	 * @param string $dir
	 * @param string $needle
	 * @param bool $recursive
	 * @param string|null $filenameLike
	 * @return SplFileInfo[]
	 */
	public static function findFilesContaining(string $dir, string $needle, bool $recursive,
		string $filenameLike = null): array{
		return self::getFilesContaining($dir, $needle, $recursive, $filenameLike);
	}
	public static function getAbsPathToCurrentTest(): string{
		$test = AppMode::getCurrentTest();
		return FileFinder::getPathToTest($test);
	}
	public static function getAbsPathLineToCurrentTest(): string{
		$test = AppMode::getCurrentTest();
		return FileFinder::getPathAndLineToTest($test);
	}
	/**
	 * @param string $dir
	 * @param string $needle
	 * @param bool $recursive
	 * @param string|null $filenameLike
	 * @return SplFileInfo[]
	 */
	public static function getFileNamesContaining(string $dir, string $needle, bool $recursive,
		string $filenameLike = null): array{
		$matches = [];
		$files = self::listFiles($dir, $recursive, $filenameLike);
		foreach($files as $file){
			if(str_contains(FileHelper::getFileNameFromPath($file), $needle)){
				$matches[] = $file;
			}
		}
		return $matches;
	}
	/**
	 * @param string $dir
	 * @param string $needle
	 * @param bool $recursive
	 * @param string|null $filenameLike
	 * @return SplFileInfo[]
	 */
	public static function getFilesContaining(string $dir, string $needle, bool $recursive,
		string $filenameLike = null): array{
		$files = self::listFilesContaining($dir, $needle, $recursive, $filenameLike);
		return $files;
	}
	public static function findFirstContaining(string $dir, string $needle, bool $recursive,
		string $filenameLike = null): ?SplFileInfo{
		$files = self::listFilesContaining($dir, $needle, $recursive, $filenameLike);
		return QMArr::first($files);
	}
	/**
	 * @param string $dirPath
	 * @param bool $recursive
	 * @param string|null $filenameLike
	 * @param string|null $notLike
	 * @return SplFileInfo[]
	 */
	public static function getFilesInFolder(string $dirPath, bool $recursive = false, string $filenameLike = null,
		string $notLike = null): array{
		return FileFinder::listFiles($dirPath, $recursive, $filenameLike, $notLike);
	}
	/**
	 * @param string $dirPath
	 * @param bool $recursive
	 * @param string|null $filenameLike
	 * @param string|null $notLike
	 * @return SplFileInfo[]
	 */
	public static function getFilesLike(string $dirPath, bool $recursive = false, string $filenameLike = null,
		string $notLike = null): array{
		return self::listFiles($dirPath, $recursive, $filenameLike, $notLike);
	}
	public static function finder(): Finder{
		return new Finder();
	}
	public static function getFilesWithExtension(string $dir, string $ext): array{
		return self::getFilesLike($dir, true, ".$ext");
	}
	/**
	 * @param string $path
	 * @return string
	 * @throws QMFileNotFoundException
	 */
	public static function getMostRecentlyModifiedFolder(string $path): string{
		$folders = self::listFolders($path);
		foreach($folders as $folder){
			$byTime[FileHelper::getLastModifiedTime($folder)] = $folder;
		}
		ksort($byTime);
		return QMArr::first($byTime);
	}
	/**
	 * @param string $dir
	 * @return string
	 */
	public static function getNewestFileInFolder(string $dir): string{
		$top = FileHelper::getLastModifiedTimeAndPathInFolder($dir);
		$time = key($top);
		$file = reset($top);
		\App\Logging\ConsoleLog::info($file . " modified " . TimeHelper::timeSinceHumanString($time));
		if(!$time){
			le("No last modified time in $dir");
		}
		return $file;
	}
	/**
	 * @param string $dirPath
	 * @param bool $recursive
	 * @param string|null $filenameLike
	 * @param string|null $pathNotLike
	 * @param string|null $ext
	 * @return SplFileInfo[]
	 */
	public static function listFiles(string $dirPath, bool $recursive = false, string $filenameLike = null,
		string $pathNotLike = null, string $ext = null): array{
		if($recursive){
			return self::listFilesRecursively($dirPath, $filenameLike, $pathNotLike, $ext);
		}
		$filesAndFolders = self::listFilesAndFoldersNonRecursively($dirPath, true, $filenameLike, $pathNotLike, $ext);
		$files = [];
		foreach($filesAndFolders as $file){
			if(!is_dir($file)){
				$files[] = $file;
			}
		}
		return $files;
	}
	/**
	 * @param string $dirPath
	 * @param bool $includePath
	 * @param string|null $needle
	 * @param null $pathNotLike
	 * @param string|null $ext
	 * @return array
	 */
	public static function listFilesAndFoldersNonRecursively(string $dirPath, bool $includePath, string $needle = null,
		$pathNotLike = null, string $ext = null): array{
		$dirPath = FileHelper::absPath($dirPath);
		try {
			$filenames = scandir($dirPath);
		} catch (\ErrorException $e) {
		    le(__FUNCTION__.": Error scanning $dirPath!  " . $e->getMessage());
		}
		$filenames = Arr::where($filenames, static function($file){
			return $file !== "." && $file !== "..";
		});
		if($needle){
			$filenames = Arr::where($filenames, static function($filename) use ($needle){
				return stripos($filename, $needle) !== false;
			});
		}
		if($pathNotLike){
			$filenames = Arr::where($filenames, static function($filename) use ($dirPath, $pathNotLike){
				return !Str::contains($dirPath . '/' . $filename, $pathNotLike);
			});
		}
		if($ext){
			$filenames = FileHelper::filterByExtension($ext, $filenames);
		}
		if($includePath){
			$paths = [];
			foreach($filenames as $filename){
				$paths[] = $dirPath . '/' . $filename;
			}
			return $paths;
		}
		return $filenames;
	}
	/**
	 * @param TestCase|LogsTests $test
	 * @return string
	 * @throws QMFileNotFoundException
	 */
	public static function getPathAndLineToTest(TestCase $test): string{
		$function = $test->getName();
		$file = self::getPathToTest($test);
		$line = self::findLineNumberContainingString($file, "function ".$function);
		return $file . ":" . $line;
	}
	/**
	 * @param string $path
	 * @param string $needle
	 * @param bool $recursive
	 * @param string|null $filenameLike
	 * @return SplFileInfo[]
	 */
	public static function listFilesContaining(string $path, string $needle, bool $recursive,
		string $filenameLike = null): array{
		$matches = [];
		$files = self::listFiles($path, $recursive, $filenameLike);
		foreach($files as $file){
			try {
				$contents = FileHelper::getContents($file);
			} catch (QMFileNotFoundException $e) {
				die(__METHOD__.": ".$e->getMessage());
			}
			if(str_contains($contents, $needle)){
				$matches[(string)$file] = $file;
			}
		}
		return $matches;
	}
	/**
	 * @param string $dir
	 * @param string|null $filenameLike
	 * @param string|null $pathNotLike
	 * @param string|null $ext
	 * @return SplFileInfo[]
	 */
	public static function listFilesRecursively(string $dir, string $filenameLike = null, string $pathNotLike = null,
		string $ext = null): array{
		$dir = FileHelper::absPath($dir);
		$path = realpath($dir);
		$root = FileHelper::absPath();
		if($root === $dir){
			le("Why are we listing all files in project ($dir)?  That will take too long!");
		}
		if(!$path){
			le("No realpath($dir)");
		}
		$before =
			new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
		$onlyFiles = [];
		/** @var SplFileInfo $fileInfo */
		foreach($before as $fileInfo){
			$name = $fileInfo->getFilename();
			if($fileInfo->isDir()){
				continue;
			}
			if($name === '.' || $name === ".."){
				continue;
			} // Not .. or . or a directory
			$path = $fileInfo->getRealPath();
			if($filenameLike && !str_contains($name, $filenameLike)){
				continue;
			}
			if($pathNotLike && Str::contains($path, $pathNotLike)){
				continue;
			}
			$onlyFiles[] = $fileInfo;
		}
		if($ext){
			$onlyFiles = FileHelper::filterByExtension($ext, $onlyFiles);
		}
		return $onlyFiles; // Objects of different files
	}
	/**
	 * @param string|null $path
	 * @param bool $recursive
	 * @param array $excludeLike
	 * @return string[] Absolute paths
	 * @throws QMFileNotFoundException
	 */
	public static function listFolders(string $path = null, bool $recursive = false, array $excludeLike = []): array{
		$relParent = relative_path($path);
		$absParent = abs_path($relParent);
		if(!is_dir($absParent)){
			le("Folder $absParent does not exist or is not a directory.");
		}
		$matches = [];
		$finder = new Finder();
		$finder->directories()->in($absParent)->ignoreDotFiles(false)->ignoreVCS(false);
		if(!$recursive){
			// https://symfony.com/doc/current/components/finder.html#directory-depth
			$finder->depth('== 0');
		}
		foreach($excludeLike as $needle){
			$finder->notPath("*" . $needle . "*");
			$finder->notName("*" . $needle . "*");
		}
		if(!$finder->hasResults()){
			throw new QMFileNotFoundException($absParent);
		} // check if there are any search results
		foreach($finder as $directory){
			$rel = $directory->getRelativePathname();
			$real = $directory->getRealPath();
			foreach($excludeLike as $needle){
				if(stripos($real, $needle) !== false){
					continue 2;
				}
			}
			$matches[] = $absParent . "/$rel";
		}
		if(!$matches){
			throw new QMFileNotFoundException($absParent);
		}
		return $matches;
	}
	/**
	 * @param \Tests\QMBaseTestCase $test
	 * @return string
	 */
	public static function getPathToTest(TestCase $test): string{
		$class = get_class($test);
		$file = FileHelper::getFilePathToClass($class);
		return $file;
	}
	public static function getPathOrUrlToCurrentTest(): string{
		if(AppMode::isJenkins()){
			return AppMode::getPHPStormUrlStatic();
		}
		return self::getAbsPathLineToCurrentTest();
	}
	/**
	 * @param string|null $like
	 * @param array|null $folders
	 * @param string|null $pathNotLike
	 * @param string|null $ext
	 * @return SplFileInfo[]
	 */
	public static function listProjectFiles(string $like = null, array $folders = null, string $pathNotLike = null,
		string $ext = null): array{
		$files = [];
		if(!$folders){
			$folders = [
				DynamicFolder::FOLDER_APP,
				DynamicFolder::FOLDER_DATABASE,
				DynamicFolder::FOLDER_TESTS,
				DynamicFolder::FOLDER_CONFIG,
				DynamicFolder::FOLDER_CONFIGS,
			];
		}
		foreach($folders as $folder){
			$files = array_merge($files, FileFinder::listFiles($folder, true, $like, $pathNotLike, $ext));
		}
		return $files;
	}
	/**
	 * @param string $file
	 * @param string $find
	 * @return int|null Null if not present
	 * @throws QMFileNotFoundException
	 */
	public static function findLineNumberContainingString(string $file, string $find): ?int{
		$file_content = FileHelper::getContents($file);
		$lines = explode("\n", $file_content);
		foreach($lines as $num => $line){
			$pos = strpos($line, $find);
			if($pos !== false){
				return $num + 1;
			}
		}
		return null;
	}
}
