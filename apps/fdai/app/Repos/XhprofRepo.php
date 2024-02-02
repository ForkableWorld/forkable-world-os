<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUndefinedVariableInspection */
namespace App\Repos;
use App\Logging\QMLog;
use App\Utils\Env;
class XhprofRepo extends GitRepo {
	public static $REPO_NAME = 'xhprof';
	public const USERNAME = 'mikepsinn';
	public const DEFAULT_BRANCH = 'master';
	public static $_xhprof;
	public static function endProfileAndGetUrl(): string{
		global $_xhprof;
		$_xhprof = self::$_xhprof;
		require self::getAbsolutePath("external/footer.php");
		$profiler_url = sprintf($_xhprof['url'] . '/index.php?run=%s&source=%s', $run_id, $profiler_namespace);
		QMLog::info("PROFILE AT: https://local.quantimo.do/xhprof/$profiler_url");
		return $profiler_url;
	}
	public static function startProfile(){
		Env::set("TIDEWAYS_XHPROF_PROFILE", true);
		require self::getAbsolutePath("external/header.php");
		QMLog::print($_xhprof);
		self::$_xhprof = $_xhprof;
	}
}
