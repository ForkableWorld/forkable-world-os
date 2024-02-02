<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
use App\Computers\ThisComputer;
class BuilderRepo extends GitRepo {
	public const PUBLIC = false;
	public static $REPO_NAME = 'builder';
	public const USERNAME = 'mikepsinn';
	public const DEFAULT_BRANCH = 'develop';
	public const RELATIVE_PATH = 'public/dev';
	public static function getAbsPath(): string{ return abs_path(self::RELATIVE_PATH); }
	public static function build(){
		ThisComputer::exec("cd " . self::RELATIVE_PATH . " && npm install");
		ThisComputer::exec("cd " . self::RELATIVE_PATH . " && gulp");
	}
}
