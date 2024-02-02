<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\PHP;
use App\Files\FileFinder;
class PhpScriptFile extends AbstractPhpFile {
	public static function getDefaultFolderRelative(): string{
		return 'app/scripts';
	}
	public static function moveAllToDefaultFolder(){
		$inScripts = FileFinder::getFilesInFolder('scripts', true, '.php');
		static::get('scripts');
		foreach($inScripts as $script){
			$file = new static($script);
			$file->moveToDefaultFolder();
		}
	}
}
