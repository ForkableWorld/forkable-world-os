<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
use App\Computers\ThisComputer;
use App\Logging\QMLog;
class IonicRepo extends GitRepo {
	public const PUBLIC = false;
	public static $REPO_NAME = 'cd-ionic';
	public const USERNAME = 'mikepsinn';
	public const DEFAULT_BRANCH = 'develop';
	public const RELATIVE_PATH = 'public/app';
	public static function getAbsPath(): string{ return abs_path(self::RELATIVE_PATH); }
	public const IONIC_IMG_PATH = self::RELATIVE_PATH . "apps/ionic/src/img";
	public const LOCAL_TO_S3_PATH_MAP = [
		'apps/ionic/src' => 'ionic',
	];
	const WEB_URL = "https://web.quantimo.do";
	const DEV_WEB_URL = "https://dev-web.quantimo.do";
	public static function build(){
		ThisComputer::exec("cd " . self::RELATIVE_PATH . " && npm install");
		//ThisComputer::exec("cd " . self::RELATIVE_PATH . " && gulp");
	}
	public static function cloneIfNecessary(){
		try {
			parent::cloneIfNecessary();
		} catch (\Throwable $e) {
			if(str_contains($e->getMessage(), "already exists")){
				QMLog::info("Ionic is a submodule of builder so doesn't have a git folder. " . $e->getMessage());
			} else{
				/** @var \LogicException $e */
				throw $e;
			}
		}
	}
}
