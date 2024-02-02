<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Folders;
class HomeFolder extends AbstractFolder {
	public static function absPath(): string{
		return $_SERVER['HOME'];
	}
}
