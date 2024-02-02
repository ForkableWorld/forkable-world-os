<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\UI;
use App\Storage\S3\S3Public;
class JSHelper {
	public static function uploadJSFiles(bool $overwrite = true){
		S3Public::uploadFolder("public/js/highcharts-themes", "js/highcharts-themes", $overwrite, true);
		self::uploadBuilderJsLibs($overwrite);
		S3Public::uploadFolder("public/dev/src/ionic/src/lib", "lib", $overwrite, true);
		S3Public::uploadFolder("public/material/js", "material/js", $overwrite, true);
		S3Public::uploadFolder("public/js", "js", $overwrite, true);
	}
	public static function uploadBuilderJsLibs(bool $overwrite = true){
		S3Public::uploadFolder("public/dev/src/lib", "lib", $overwrite, true);
	}
}
