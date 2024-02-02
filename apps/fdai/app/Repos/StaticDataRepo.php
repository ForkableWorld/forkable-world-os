<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
use App\AppSettings\StaticAppData;
use App\Logging\QMLog;
use App\Properties\Base\BaseClientIdProperty;
use App\Storage\S3\S3Public;
use Throwable;
class StaticDataRepo extends GitRepo {
	public const S3_PATH = 'data';
	public static $REPO_NAME = 'qm-static-data';
    public const DEFAULT_BRANCH = 'master';
	public const RELATIVE_PATH = 'public/qm-static-data';
	public static function getAbsPath(): string{ return abs_path(self::RELATIVE_PATH); }
	public static function updateAndCommitStaticData(): string{
		static::clonePullAndOrUpdateRepo();
		$branch = "feature/update-static-data";
		static::createFeatureBranch($branch);
		try {
			static::hardReset($branch);
		} catch (Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
		}
		try {
			static::fetchForceCheckoutAndPull($branch);
		} catch (Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
		}
		$staticData = new StaticAppData(BaseClientIdProperty::CLIENT_ID_QUANTIMODO);
		$staticData->writeAllDataTypesToFiles();
		//$staticData->writeCommonVariablesToFile();
		return static::addAllCommitAndPush(__FUNCTION__);
	}
}
