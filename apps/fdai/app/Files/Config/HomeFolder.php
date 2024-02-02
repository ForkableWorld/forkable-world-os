<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\Config;
use App\Computers\ThisComputer;
use App\Folders\AbstractProjectFolder;
use App\Traits\FileTraits\IsBackedUpFolder;
class HomeFolder extends AbstractProjectFolder {
	use IsBackedUpFolder;
	/**
	 * @return string
	 */
	public static function relativePath(): string{
		return $_SERVER["HOME"] ?? "/home/" . (new ThisComputer)->getUser();
	}
	public function getDestinationFolderPath(): string{
		return self::FOLDER_CONFIGS . "/home-dev";
	}
	public function getDestinationOwner(): string{
		// TODO: Implement getDestinationOwner() method.
	}
}
