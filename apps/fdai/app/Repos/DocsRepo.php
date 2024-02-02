<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Repos;
use App\CodeGenerators\Swagger\SwaggerJson;
use App\Computers\ThisComputer;
use App\Console\Kernel;
use App\Exceptions\GitLockException;
use App\Exceptions\NoFileChangesException;
use App\Types\QMStr;
class DocsRepo extends GitRepo {
	public const PUBLIC = true;
	public static $REPO_NAME = 'docs';
	public const USERNAME = 'quantimodo';
	public const SWAGGER_JSON_PATH = 'swagger/swagger.json';
	public const DEFAULT_BRANCH = 'develop';
	public const swaggerJsonUrl = 'https://raw.githubusercontent.com/QuantiModo/docs/master/swagger/swagger.json';
	public const RELATIVE_PATH = 'public/dev-docs';
	public static function getAbsPath(): string{ return abs_path(self::RELATIVE_PATH); }
	/**
	 * @return string
	 */
	public static function getSwaggerJSONString(): string{
		$data = static::getContentsViaApi(self::SWAGGER_JSON_PATH);
		return base64_decode($data['content']);
	}
	/**
	 * @param string $updated
	 * @param string $commitMessage
	 * @return array
	 * @throws NoFileChangesException
	 */
	public static function updateSwaggerJSON(string $updated, string $commitMessage): array{
		/** @var SwaggerJson $obj */
		$obj = json_decode($updated, false);
		$obj->info->version = self::generateVersionNumber();
		$str = QMStr::prettyJsonEncodeUnescapedSlashes($obj);
		QMStr::validateJson($str);
		return self::updateOrCreateByAPI('swagger', 'swagger.json', $updated, $commitMessage);
	}
	/**
	 * @param string|array $searches
	 * @param string $replace
	 * @param string $commitMessage
	 * @return array
	 * @throws NoFileChangesException
	 */
	public static function replaceInSwaggerJson($searches, string $replace, string $commitMessage): array{
		if(!is_array($searches)){
			$searches = [$searches];
		}
		$updated = $original = self::getSwaggerJSONString();
		foreach($searches as $search){
			$updated = str_replace($search, $replace, $updated);
		}
		if($original === $updated){
			le("No changes!");
		}
		return self::updateSwaggerJSON($updated, $commitMessage);
	}
	/**
	 * @return string
	 */
	public static function generateVersionNumber(): string{
		$majorMinorVersionNumbers = '5.10.';
		$monthNumber = date("m") + 1;
		$dayOfMonth = date("d");
		$hourOfDay = date("H");
		//hourOfDayString = "72"; // Manually set if need to release more than once per hour
		return $majorMinorVersionNumbers . $monthNumber . $dayOfMonth . $hourOfDay;
	}
	/**
	 * @return string
	 */
	public static function getSwaggerVersion(): string{
		return self::getSwaggerJsonObj()->info->version;
	}
	/**
	 * @return SwaggerJson
	 */
	public static function getSwaggerJsonObj(): SwaggerJson{
		return json_decode(self::getSwaggerJSONString(), false);
	}
	public static function build(){
		ThisComputer::exec("cd " . self::RELATIVE_PATH . " && npm install");
	}
	/**
	 * @throws GitLockException
	 */
	public static function updateAndCommitZiggy(){
		static::createFeatureBranch("ziggy");
		Kernel::artisan("ziggy:generate", [//static::getAbsolutePath("js/ziggy.js") => 1
		]);
		static::addCommitPush("Updated Ziggy", null, "resources/ziggy.js");
	}
}
