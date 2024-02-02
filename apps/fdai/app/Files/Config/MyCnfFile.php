<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\Config;
use App\Computers\ThisComputer;
use App\Files\TypedProjectFile;
use App\Traits\FileTraits\IsSyncableFile;
class MyCnfFile extends TypedProjectFile {
	use IsSyncableFile;
	public static function getDefaultExtension(): string{ return ".cnf"; }
	public function getDestinationFolderPath(): string{ return (new HomeFolder)->getPath(); }
	public function getDestinationPerms(): string{ return "600"; }
	public function getSourcePath(): string{
		return self::getDefaultFolderRelative() . "/.my.cnf";
	}
	public static function getDefaultFolderRelative(): string{ return "configs/home-dev"; }
	public function getDestinationOwner(): string{
		return (new ThisComputer())->getUser();
	}
}
