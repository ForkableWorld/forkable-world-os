<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
use App\Storage\S3\S3Public;
class WPRepo extends GitRepo {
	public static $REPO_NAME = 'wp-serverless';
	public const USERNAME = 'mikepsinn';
	public const DEFAULT_BRANCH = 'master';
	public const LOCAL_TO_S3_PATH_MAP = [
		'public/wp-content' => 'wp-content',
		'public/wp-includes' => 'wp-includes',
	];
	public static function uploadWpContentToS3(){
		S3Public::uploadFolder(self::getWpContentPath(), 'wp-content', false, true, PublicRepo::excludeNamesLike());
	}
	public static function uploadWpIncludesToS3(){
		S3Public::uploadFolder(self::getAbsolutePath('public/wp-includes'), 'wp-includes', false, true,
			PublicRepo::excludeNamesLike());
	}
	public static function uploadPluginsToS3(){
		self::uploadOnePluginToS3('wp-reactions-child');
	}
	public static function getWpContentPath(): string{
		return self::getAbsolutePath('public/wp-content');
	}
	public static function getPluginPath(string $name = ''): string{
		return self::getAbsolutePath("public/wp-content/plugins/$name");
	}
	/**
	 * @param string $name
	 */
	protected static function uploadOnePluginToS3(string $name): void{
		S3Public::uploadFolder(self::getPluginPath($name), "wp-content/plugins/$name");
	}
}
