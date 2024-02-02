<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
use App\Files\QMSync;
class LaravelModelGeneratorRepo extends GitRepo {
	public const PUBLIC = true;
	public static $REPO_NAME = 'laravel-model-generator';
	public const USERNAME = 'mikepsinn';
	public const DEFAULT_BRANCH = 'custom';
	public static function syncFromVendorAndCommit(){
		LaravelModelGeneratorRepo::cloneIfNecessary();
		QMSync::copyFilesRecursively('vendor/reliese/laravel/src', self::getAbsolutePath('src'));
		//self::commitAndPushStatic(__FUNCTION__);
	}
}
