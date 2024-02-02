<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
use App\Computers\ThisComputer;
class JsSdkRepo extends GitRepo {
	public const PUBLIC = true;
	public static $REPO_NAME = 'quantimodo-sdk-javascript';
	public const USERNAME = 'quantimodo';
	public const DEFAULT_BRANCH = 'develop';
	public const RELATIVE_PATH = 'public/dev-docs/sdk-repos/quantimodo-sdk-javascript';
	public static function getAbsPath(): string{ return abs_path(self::RELATIVE_PATH); }
	public static function build(){
		ThisComputer::exec("cd " . self::RELATIVE_PATH . " && npm install");
	}
}
