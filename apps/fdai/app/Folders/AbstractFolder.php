<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Folders;
use App\Files\FileHelper;
abstract class AbstractFolder extends DynamicFolder {
	public const PATH_BUILD_LOGFILES    = "build";
	public const STAGING_UNIT_TEST_PATH = 'tests/StagingUnit';
	public function __construct(){ parent::__construct(static::absPath()); }
	abstract public static function absPath(): string;
	public static function getCurrentTestFolder(): string{
		$filePath = \App\Files\FileFinder::getAbsPathLineToCurrentTest();
		return FileHelper::getFolderFromFilePath($filePath);
	}
}
