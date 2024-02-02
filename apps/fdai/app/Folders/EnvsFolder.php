<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Folders;
class EnvsFolder extends ConfigsFolder {
	public static function relativePath(): string{
		return parent::relativePath() . "/envs";
	}
}
