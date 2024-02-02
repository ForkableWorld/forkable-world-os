<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Folders;
class HomeQmApiFolder extends AbstractFolder {
	public static function absPath(): string{
		return HomeFolder::absPath()."/qm-api";
	}
}
