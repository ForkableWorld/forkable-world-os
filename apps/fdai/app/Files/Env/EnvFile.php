<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\Env;
use App\Files\FileExtension;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Files\PHP\AbstractPhpFile;
use App\Files\TypedProjectFile;
use App\Folders\EnvsFolder;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Repos\QMAPIRepo;
use App\Storage\Memory;
use App\Utils\Env;
use App\Utils\EnvOverride;
use Illuminate\Support\Collection;
class EnvFile extends TypedProjectFile {
	const MERGE_ORDERING = [
		Env::ENV_LOCAL => [self::ENV_GLOBAL, Env::ENV_STAGING, Env::ENV_STAGING_REMOTE, Env::ENV_LOCAL],
		Env::ENV_PRODUCTION => [self::ENV_GLOBAL, Env::ENV_PRODUCTION],
		Env::ENV_OPENCURES => [self::ENV_GLOBAL, Env::ENV_OPENCURES],
		Env::ENV_PRODUCTION_REMOTE => [self::ENV_GLOBAL, Env::ENV_PRODUCTION, Env::ENV_PRODUCTION_REMOTE],
		Env::ENV_STAGING => [self::ENV_GLOBAL, Env::ENV_STAGING],
		Env::ENV_STAGING_REMOTE => [self::ENV_GLOBAL, Env::ENV_STAGING, Env::ENV_STAGING_REMOTE],
		Env::ENV_TESTING => [self::ENV_GLOBAL, Env::ENV_TESTING],
	];
	const ENV_GLOBAL = 'global';
	const PATH = 'configs/envs';
	/**
	 * @param null $file
	 */
	public function __construct($file = ".env"){
		parent::__construct($file);
	}
	public static function createLocalEnv(){
		self::generateFile(Env::ENV_LOCAL);
	}
	public static function generateFile(string $stage){
        le("DEPRECATED: EnvFile::generateFile()");
		$stage = str_replace(".env.", "", $stage);
		$combined = self::getByStage($stage);
		$contents = self::envsToString($combined);
		FileHelper::write(".env.$stage", $contents);
		ConsoleLog::info("Generated .env.$stage");
		if(!EnvOverride::isLocal()){
			FileHelper::write(".env", $contents);
			ConsoleLog::info("Copied .env.$stage to root .env");
		}
	}
	private static function mergeEnvs(array $reverseOrderOfPreference): array {
        le("DEPRECATED: EnvFile::mergeEnvs()");
		$combined = [];
		foreach($reverseOrderOfPreference as $stage){
			$envs = static::readEnvs($stage);
			$combined = array_merge($combined, $envs);
		}
		$buildEnvs = [
			"GIT_COMMIT",
			"BUILD_URL",
			"JOB_NAME",
			"BUILD_ID",
			"NODE_NAME",
			"GIT_URL",
			"GIT_BRANCH",
			"JENKINS_HOME",
			"JENKINS_URL",
		];
		foreach($buildEnvs as $name){
			if($val = \App\Utils\Env::get($name)){
				$combined[$name] = $val;
			}
		}
		$combined["API_LAST_MODIFIED"] = '"' . QMAPIRepo::getCommitDate() . '"';
		ksort($combined);
		return $combined;
	}
	private static function envsToString(array $envs): string{
		$str = "";
		foreach($envs as $name => $val){
			$str .= "$name=$val\n";
		}
		return $str;
	}
	public static function readEnvs(string $stage): array{
		$f = static::find(Env::PATH_TO_CONFIGS . "/.env.$stage");
		return $f->getEnvs();
	}
	public static function getDefaultExtension(): string{
		return FileExtension::ENV;
	}
	public static function getDefaultFolderRelative(): string{
		return EnvsFolder::relativePath();
	}
	public static function deleteUnusedEnvs(){
		$envFiles = static::allQMEnvs();
		$phpFiles = AbstractPhpFile::all();
		$alreadyChecked = [];
		foreach($envFiles as $envFile){
			if(self::isIgnored($envFile)){
				continue;
			}
			$envs = $envFile->getEnvs();
			foreach($envs as $name => $value){
				if(strpos($name, "CONNECTOR_") === 0){
					continue;
				}
				$needle = "'$name'";
				if(in_array($name, $alreadyChecked)){
					continue;
				}
				$alreadyChecked[] = $name;
				foreach($phpFiles as $phpFile){
					if($phpFile->contains($needle)){
						continue 2;
					}
				}
				self::deleteFromAllEnvs($name);
			}
		}
	}
	public function getEnvs(): array{
		return Env::readEnvFile($this->getRealPath());
	}
	public static function data(): array{
		return (new EnvFile)->getEnvs();
	}
	/**
	 * @param array|null $folders
	 * @return static[]|Collection
	 */
	public static function get(array $folders = [], string $pathNotLike = null): Collection{
		if(!$folders){
			$folders = static::getFolderPaths();
		}
		$fileInfos = FileFinder::listProjectFiles(".env", $folders, $pathNotLike, null);
		$files = self::instantiateArray($fileInfos);
		return collect($files);
	}
	/**
	 * @param string $name
	 */
	private static function deleteFromAllEnvs(string $name): void{
		QMLog::error("Deleting '$name' from envs...");
		$envFiles = static::all();
		foreach($envFiles as $envFile2){
			/** @var static $envFile2 */
			$envFile2->deleteLineContaining($name . "=");
		}
	}
	/**
	 * @return static[]
	 */
	public static function allQMEnvs(): array{
		$envFiles = static::all();
		$keep = [];
		foreach($envFiles as $envFile){
			if(self::isIgnored($envFile)){
				continue;
			}
			$keep[(string)$envFile] = $envFile;
		}
		return $keep;
	}
	public static function generateEnvGlobal(){
		le("comment if you really want to do this");
		$files = static::allQMEnvs();
		$byName = [];
		foreach($files as $file){
			$envs = $file->getEnvs();
			foreach($envs as $name => $value){
				$byName[$name][] = $value;
				$byName[$name] = array_unique($byName[$name]);
			}
		}
		$global = static::find(static::PATH . '/.env.global');
		foreach($byName as $name => $values){
			if(count($values) === 1){
				foreach($files as $file){
					$file->deleteLineContaining($name . "=");
				}
				$global->addEnv($name, $values[0]);
			}
		}
	}
	private function addEnv(string $name, string $val){
		$this->append("$name=$val");
	}
	/**
	 * @param $envFile
	 * @return bool
	 */
	private static function isIgnored($envFile): bool{
		return strpos($envFile, self::PATH) === false;
	}
	/**
	 * @return array|false|mixed|null
	 */
	protected static function getEnvsFromRootDotEnv(){
		$path = ".env";
		$envs = Memory::get($path);
		if(!$envs){
			$file = static::find($path);
			$envs = $file->getEnvs();
			Memory::set($path, $envs);
		}
		return $envs;
	}
	/**
	 * @param string $stage
	 * @return array
	 */
	public static function getByStage(string $stage): array{
		if(empty($stage)){
			le("no stage provided to " . __METHOD__);
		}
		if(!isset(self::MERGE_ORDERING[$stage])){
			le("$stage not set in MERGE_ORDERING");
		}
		$combined = self::mergeEnvs(self::MERGE_ORDERING[$stage]);
		return $combined;
	}
	/**
	 * @return EnvFile
	 */
	private static function localEnv(): EnvFile{
		return static::find(".env.local");
	}
	/**
	 * @return EnvFile
	 */
	private static function globalEnv(): EnvFile{
		return static::find(self::PATH . "/.env.global");
	}
	public static function rootEnvHasValue(string $key): bool{
		$envs = self::getEnvsFromRootDotEnv();
		return isset($envs[$key]);
	}
	public static function getValueFromRootEnv(string $key): mixed {
		$envs = self::getEnvsFromRootDotEnv();
		return $envs[$key] ?? null;
	}
	public function getValue(string $name): string{
		return $this->getEnvs()[$name];
	}
	public static function generateAll(){
		foreach(self::MERGE_ORDERING as $stage => $constituents){
			if(in_array($stage, [Env::ENV_PRODUCTION, Env::ENV_STAGING])){
				continue;
			}
			self::generateFile($stage);
		}
	}

    public static function testing(): array
    {
        return self::readEnvs('testing');
    }

    public function load()
    {
        $values = $this->getEnvs();
        foreach ($values as $key => $value) {
            Env::set($key, $value);
        }
    }
}
