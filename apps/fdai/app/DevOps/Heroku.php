<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DevOps;
use App\Repos\QMAPIRepo;
use App\Types\QMStr;
use App\Utils\Env;
use HerokuClient\Client;
class Heroku {
	public static function client(){
		$heroku = new Client([
			'apiKey' => '2cf8234b-8644-4df2-af50-b4c258478a71', // Or set the HEROKU_API_KEY environmental variable
		]);
		return $heroku;
	}
	public static function getConfigVars(string $appName){
		$c = self::client();
		return $c->get("apps/$appName/config-vars");
	}
	public static function updateEnvFiles(){
		$files = Env::listEnvFiles();
		foreach($files as $filePath){
			$stage = QMStr::after(".env.", $filePath);
			if($stage === Env::ENV_LOCAL){
				$stage = "development";
			}
			$vars = self::getConfigVars("qm-$stage");
			$envContents = Env::getEnvVariablesFromFile($filePath);
			foreach($vars as $key => $value){
				//$envContents[$key] = $value;
			}
			Env::saveEnvFile($filePath, $envContents);
		}
	}
	public static function updateEnvFilesAndCommit(){
		QMAPIRepo::createFeatureBranch(__FUNCTION__);
		self::updateEnvFiles();
		QMAPIRepo::addFilesInFolder(Env::PATH_TO_CONFIGS);
		QMAPIRepo::commitAndPush("Updated env files from Heroku");
	}
}
