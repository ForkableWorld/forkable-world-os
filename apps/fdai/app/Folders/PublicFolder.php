<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Folders;
class PublicFolder extends AbstractProjectFolder {
	public static function relativePath(): string{
		return self::FOLDER_PUBLIC;
	}
}
