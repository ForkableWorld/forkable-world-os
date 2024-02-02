<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Files;
use App\Computers\ThisComputer;
use LogicException;
use Throwable;
use xobotyi\rsync\Rsync;
class QMSync extends Rsync {
	/**
	 * @param string $src
	 * @param string $dst
	 * @param string|null $includeLike
	 * @param array $excludeLike
	 */
	public static function copyFilesRecursively(string $src, string $dst, string $includeLike = null,
		array $excludeLike = []){
		$src = FileHelper::absPath($src);
		if(is_dir($src)){
			FileHelper::createDirectoryIfNecessary($dst);
			try {
				$files = scandir($src);
			} catch (Throwable $e) {
				$message = $e->getMessage();
				$message .= "\n" . ThisComputer::outputUser();
				$message .= "\n" . ThisComputer::listDirectoryPermissions($src);
				le($message);
				throw new LogicException();
			}
			foreach($files as $file){
				if($file !== "." && $file !== ".."){
					self::copyFilesRecursively("$src/$file", "$dst/$file", $includeLike, $excludeLike);
				}
			}
		} elseif(file_exists($src)){
			if($includeLike && strpos($src, $includeLike) === false){
				return;
			}
			foreach($excludeLike as $ex){
				if($excludeLike && strpos($src, $ex) !== false){
					return;
				}
			}
			copy($src, $dst);
		}
	}
	/**
	 * @param string $srcFolder
	 * @param string $like
	 * @param string $outBase
	 * @param string|null $notLike
	 */
	public static function backupFilesLike(string $srcFolder, string $like, string $outBase,
		string $notLike = null): void{
		$files = FileHelper::findFilesWithNameLike(FileHelper::toLinuxPath($srcFolder), $like, true, $notLike);
		foreach($files as $file){
			$scrFile = (string)$file;
			$outfile = str_replace("/mnt/c/", $outBase, $scrFile);
			FileHelper::copy($scrFile, $outfile);
		}
	}
}
