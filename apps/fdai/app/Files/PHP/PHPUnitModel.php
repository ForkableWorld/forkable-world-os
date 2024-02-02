<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\PHP;
use App\Files\FileFinder;
use App\Logging\QMLog;
use App\Utils\EnvOverride;
class PHPUnitModel extends PhpClassFile {
	public static function getCurrent(): PHPUnitModel{
		$path = FileFinder::getAbsPathToCurrentTest();
		return new static($path);
	}
	public static function replaceArray(array $expected, array $actual){
		if(!EnvOverride::isLocal()){
			return;
		}
		$expected = QMLog::var_export($expected, true);
		$actual = QMLog::var_export($actual, true);
		$f = static::getCurrent();
		$f->replace($expected, $actual);
	}
}
