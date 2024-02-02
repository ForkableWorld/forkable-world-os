<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Folders;
class ReposFolder extends AbstractProjectFolder {
	public static function relativePath(): string{
		return 'repos';
	}
	public static function generateAbsPath(string $repoName):string{
		return static::getParentFolder()."/$repoName";
	}
	public static function generateRelativePath(string $owner, string $name):string{
		return static::relativePath()."/$owner/$name";
	}
	public static function getParentFolder():string{
		return '/www/wwwroot';
	}
}
