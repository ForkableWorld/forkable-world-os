<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files;
use App\Folders\DynamicFolder;
class TextFile extends TypedProjectFile {
	public static function getDefaultFolderRelative(): string{
		return DynamicFolder::STORAGE_LOGS;
	}
	public static function getDefaultExtension(): string{
		return FileExtension::TXT;
	}
}
