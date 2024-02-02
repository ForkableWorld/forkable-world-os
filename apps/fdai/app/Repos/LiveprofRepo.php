<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUndefinedVariableInspection */
namespace App\Repos;
class LiveprofRepo extends GitRepo {
	const DB_URL = '';
	const PATH = 'profiler';
	const URL = "https://local.quantimo.do/" . self::PATH;
	public static $REPO_NAME = 'liveprof-ui';
	public const USERNAME = 'mikepsinn';
	public const DEFAULT_BRANCH = 'master';
	public static function postUpdate(){
		self::composerInstall();
		parent::postUpdate();
	}
}
