<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\Traits;
use App\Files\FileHelper;
use App\Files\UntypedFile;
use Illuminate\Support\Collection;
trait IsMergableFile {
	abstract public static function getFilePathsToMerge(): array;
	abstract public static function getMergedOutputFilePath(): string;
	public static function merge(): string{
		$str = "#!/usr/bin/env bash\n";
		foreach(static::getFilesToMerge() as $file){
			/** @var UntypedFile $file */
			$rel = $file->getRelativePath();
			$title = $file->getTitleAttribute();
			$str .= "### START IMPORTED FROM $rel ###\n" . "# Group: $title
# ----------------------------------------------------#" . $file->getContents() . "\n### END IMPORTED FROM $rel ###";
		}
		FileHelper::write(static::getMergedOutputFilePath(), $str);
		return $str;
	}
	/**
	 * @return Collection|UntypedFile[]
	 */
	protected static function getFilesToMerge(): Collection{
		return static::instantiateArray(static::getFilePathsToMerge());
	}
}
