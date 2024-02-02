<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
use App\AppSettings\AppSettings;
use App\Exceptions\QMFileNotFoundException;
use App\Models\Application;
class ApplicationSettingsRepo extends GitRepo {
	public const PUBLIC = true;
	public const S3_PATH = 'application-settings';
	public static $REPO_NAME = 'qm-application-settings';
	public const RELATIVE_PATH = 'public/qm-application-settings';
	public static function getAbsPath(): string{ return abs_path(self::RELATIVE_PATH); }
	/**
	 * @param string $clientId
	 * @param AppSettings $appSettings
	 * @return void
	 */
	public static function saveAppSettings(string $clientId, AppSettings $appSettings): void{
		parent::writeJsonFile("apps/$clientId", $appSettings);
	}
	protected static function getBlackListedStrings(array $repoSpecific = []): array{
		return parent::getBlackListedStrings([
			"testing.quantimo.do",
		]);
	}
	/**
	 * @param string $clientId
	 * @param int|null $maxAge
	 * @return AppSettings|null
	 * @throws QMFileNotFoundException
	 */
	public static function getAppSettings(string $clientId, int $maxAge = null): ?AppSettings{
		if($maxAge){
			$age = static::getAgeOfAppSettings($clientId);
			if($age > $maxAge){
				return null;
			}
		}
		$path = self::getAppSettingsPath($clientId);
		$str = self::getContents($path);
		return new AppSettings(json_decode($str));
	}
	private static function getAppSettingsPath(string $clientId): string{
		return 'apps/' . $clientId . ".json";
	}
	public static function getAgeOfAppSettings(string $clientId): ?int{
		$path = self::getAppSettingsPath($clientId);
		return self::getAgeOfFileInSeconds($path);
	}
	public static function updateAndCommitAppSettings(){
		static::cloneOrPullIfNecessary();
		$apps = Application::getAllBuildableAppSettings();
		foreach($apps as $app){
			if(!$app->isTestApp()){
				continue;
			}
			$app->writeStaticData();
		}
		static::addAllCommitAndPush("Updated app settings");
	}
	public static function clonePullAndOrUpdateRepo(){
		parent::clonePullAndOrUpdateRepo();
	}
}
