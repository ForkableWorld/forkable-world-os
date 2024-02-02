<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
class QMWPPluginRepo extends GitRepo {
	public static $REPO_NAME = 'QuantiModo-WordPress-Plugin';
	public const USERNAME = 'QuantiModo';
	public const DEFAULT_BRANCH = 'feature/complicated-version';
	public const RELATIVE_PATH = 'public/' . self::URL_PATH;
	public static function getAbsPath(): string{ return abs_path(self::RELATIVE_PATH); }
	public const URL_PATH = 'wp/public/wp-content/plugins/QuantiModo-WordPress-Plugin';
	public const LOCAL_TO_S3_PATH_MAP = [
		'' => 'wp-content/plugins/QuantiModo-WordPress-Plugin',
	];
}
