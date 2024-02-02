<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\Json;
use App\Files\FileExtension;
use App\Files\TypedProjectFile;
use App\Folders\DynamicFolder;
class JsonFile extends TypedProjectFile {
	public static function getDefaultFolderRelative(): string{
		return DynamicFolder::STORAGE . "/" . static::getDefaultExtension();
	}
	public static function getDefaultExtension(): string{
		return FileExtension::JSON;
	}
    public static function getArray(string $path){
        $f = static::find($path);
        $arr = $f->getDecoded();
        return json_decode(json_encode($arr), true);
    }
	public static function saveArray(string $path, array $toArray){
		return self::write($path, json_encode($toArray, JSON_PRETTY_PRINT));
	}
	public static function write(string $path, $content): string{
        if(!is_string($content)){
            $content = json_encode($content, JSON_PRETTY_PRINT);
        }
        $f = static::find($path);
        return $f->writeContents($content);
    }
}
