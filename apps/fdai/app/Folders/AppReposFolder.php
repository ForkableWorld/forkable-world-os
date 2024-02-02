<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Folders;
class AppReposFolder extends AbstractProjectFolder {
	public static function relativePath(): string{
		return AppFolder::relativePath()."/Repos";
	}
}
