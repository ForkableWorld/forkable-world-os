<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Folders;
use App\Files\FileHelper;
class ClockworkFolder extends AbstractProjectFolder {
	public static function relativePath(): string{
		$absPath = config('clockwork.storage_files_path');
		$relPath = FileHelper::getRelativePath($absPath);
		return $relPath;
	}
}
