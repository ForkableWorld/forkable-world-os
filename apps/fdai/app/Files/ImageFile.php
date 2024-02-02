<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files;
use App\Logging\QMLog;
use App\Types\QMStr;
use App\UI\ImageHelper;
class ImageFile extends UntypedFile
{
	public static function outputConstantsForImageFiles(string $absPath): string{
		$files = FileFinder::listFilesRecursively($absPath);
		$str = "";
		foreach($files as $file){
			$name = $file->getFilename();
			$absPath = $file->getRealPath();
			if(!isset($name)){
				continue;
			}
			if(stripos($name, '.png') === false){
				continue;
			}
			$relativePath = str_replace($absPath, '', $absPath);
			$constName = str_replace('/png', '', $relativePath);
			$constName = str_replace('/', '_', $constName);
			$constName = str_replace('.png', '', $constName);
			$constName = QMStr::toConstantName($constName);
			$url = ImageHelper::BASE_URL . $relativePath;
			$url = str_replace('img//', 'img/', $url);
			$one = "\tpublic const IMAGE$constName = '$url'; // $url";
			$str .= $one . "\n";
			\App\Logging\ConsoleLog::info("\tpublic const IMAGE$constName = '$url'; // $url");
		}
		return $str;
	}
}
