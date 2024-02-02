<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
use App\Utils\SecretHelper;
class PublicRepo extends GitRepo {
	public static $REPO_NAME = 'qm-public';
	public const USERNAME = 'mikepsinn';
	public const DEFAULT_BRANCH = 'master';
	public const LOCAL_TO_S3_PATH_MAP = [
		'' => '',
	];
	/**
	 * @return array
	 */
	public static function excludeNamesLike(): array{
		$excludeLike = SecretHelper::SECRET_FILE_PATTERNS;
		$excludeLike[] = '.php';
		return $excludeLike;
	}
}
