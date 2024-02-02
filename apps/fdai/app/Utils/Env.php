<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
use App\Buttons\Admin\PHPStormButton;
use App\DevOps\Jenkins\Jenkins;
use App\Files\Env\EnvFile;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Folders\EnvsFolder;
use App\Http\Urls\AdminUrl;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Slim\QMSlim;
use App\Types\BoolHelper;
use App\Types\QMStr;
use App\UI\HtmlHelper;
use LogicException;
use RuntimeException;
class Env {
	public const CLOCKWORK_ENABLE        = 'CLOCKWORK_ENABLE';
	public const CLOCKWORK_TESTS_COLLECT = 'CLOCKWORK_TESTS_COLLECT';
	const        LOG_LEVEL               = 'LOG_LEVEL';
	public const LOG_PHPUNIT_LINKS       = 'LOG_PHPUNIT_LINKS';
    private static array $envs = [];
	public const PATH_TO_CONFIGS = EnvFile::PATH;
	public const APP_DEBUG = 'APP_DEBUG';
	public const APP_ENV = 'APP_ENV';
	public const APP_URL = 'APP_URL';
	public const STORAGE_ACCESS_KEY_ID = 'STORAGE_ACCESS_KEY_ID';
	public const STORAGE_SECRET_ACCESS_KEY = 'STORAGE_SECRET_ACCESS_KEY';
	public const BUGSNAG_API_KEY = 'BUGSNAG_API_KEY';
	public const DB_HOST = 'DB_HOST';
    public const DB_PASSWORD = 'DB_PASSWORD';
    public const DB_URL = 'DB_URL';
    public const DB_DATABASE = 'DB_DATABASE';
	public const DEBUGBAR_WHILE_TESTING = 'DEBUGBAR_WHILE_TESTING';
	public const ENV_LOCAL = 'local';
	public const ENV_PRODUCTION = 'production';
	public const ENV_PRODUCTION_REMOTE = 'production-remote';
	public const ENV_STAGING = 'staging';
	public const ENV_STAGING_REMOTE = 'staging-remote';
	public const ENV_TESTING = 'testing';
	public const ENV_OPENCURES = 'opencures';
	public const PROFILE = 'PROFILE';
	public const REDIS_PORT = 'REDIS_PORT';
	public const SENDGRID_API_KEY = 'SENDGRID_API_KEY';
	public const SIMULATE_JENKINS = 'SIMULATE_JENKINS';
	public const TEST_FOLDER = 'TEST_FOLDER';
	public const UPDATE_COLLECTOR_DATA = 'UPDATE_COLLECTOR_DATA';
	public const UPDATE_HTML_FIXTURES = 'UPDATE_HTML_FIXTURES';
	public static function all(): array{
		if(!isset($_ENV)){
			le("Env::all() \$_ENV is not set");
		}
		return $_ENV;
	}
	/**
	 * @param $connectorName
	 * @return array
	 */
	public static function getClientIdByConnectorName($connectorName): ?string{
		$uppercase = QMStr::toScreamingSnakeCase($connectorName);
		$connectorClientId = Env::get('CONNECTOR_' . $uppercase . '_CLIENT_ID');
		return $connectorClientId;
	}
	/**
	 * @return string
	 */
	public static function getAppUrl(): string {
		static::setAppEnvIfEmpty();
		$APP_URL = static::getRequired(static::APP_URL);
//		$HTTP_HOST = $_SERVER["HTTP_HOST"] ?? null;
//		if($HTTP_HOST && str_contains($APP_URL, $HTTP_HOST)){
//			$APP_URL = static::getRequired(static::APP_URL);
//			le("getRequired(static::APP_URL) $APP_URL does not contain \$_SERVER[\"HTTP_HOST\"]: $HTTP_HOST");
//		}
		return $APP_URL;
	}
	/**
	 * @param $value
	 * @return bool|mixed|null
	 */
	public static function formatValue($value): mixed{
		if($value === false){return null;}
		if($value === ""){return null;}
		if($value === null){return null;}
        $value = str_replace('"', '', $value);
		return BoolHelper::convertFromStringIfNecessary($value);
	}
	/**
	 * @param string $pattern
	 * @param array $whiteList
	 * @return string[]
	 */
	public static function getNonEmptyEnvValuesWithNameLike(string $pattern, array $whiteList = []): array{
		$values = [];
		foreach($_ENV as $key => $value){
			if(stripos($key, $pattern) !== false && !in_array($key, $whiteList)){
				$value = Env::get($key);
				if(!empty($value)){
					$values[$key] = $value;
				}
			}
		}
		return $values;
	}
    public static function outputEnv(){
	    $mixed = static::printObfuscated();
	    ConsoleLog::info($mixed);
    }
	public static function outputEnvConstants(){
		foreach($_ENV as $key => $value){
			QMLog::infoWithoutContext("public const $key = '$key';", false);
		}
		foreach($_ENV as $key => $value){
			QMLog::infoWithoutContext("public const DEFAULT_$key = '$value';", false);
		}
	}
	/**
	 * @param string $path
	 * @param bool $useCached
	 * @return array
	 */
	public static function getEnvVariablesFromFile(string $path = '.env', bool $useCached = true): array{
		if($useCached){
			$variables = static::$envs[$path] ?? null;
			if($variables){
				return $variables;
			}
		}
		// Uncomment for debugging logging issues QMLogger::cli()->info(__METHOD__." calling getAbsolutePathFromRelative...");
		$envPath = FileHelper::absPath($path);
		$exists = file_exists($envPath);
		if(!$exists){
			ConsoleLog::debug("$envPath does not exist");
			return [];
		}
		$str = file_get_contents($envPath);
		$lines = explode(PHP_EOL, $str);
		if(count($lines) === 1){
			$lines = explode("
", $str);
		}
		$variables = [];
		foreach($lines as $line){
			if(str_starts_with($line, "#")){continue;}
			if(empty($line)){
				continue;
			}
			$key = QMStr::before("=", $line);
			$value = QMStr::after("=", $line);
			if($value === "0"){
				$value = false;
			}
			$variables[$key] = $value;
		}
		return static::$envs[$path] = $variables;
	}
	/**
	 * @param string $key
	 * @return mixed
	 */
	public static function getFormatted(string $key): mixed{
		$val = static::get($key);
		return static::formatValue($val);
	}
	/**
	 * @param string $key
	 * @return mixed
	 */
	public static function get(string $key = null): mixed{
		if(!$key){return static::all();}
		$val = getenv($key);
		if(!$val){
			$val = $_ENV[$key] ?? null;
		}
		return $val;
	}
	/**
	 * @return bool
	 */
	public static function isTestingOrDevelopment(): bool{
		return AppMode::isTestingOrStaging() || static::isLocal();
	}
	/**
	 * @return bool
	 */
	public static function isTesting(): bool{
		if(Env::get(static::APP_ENV) === static::ENV_TESTING){
			return true;
		}
		if(Env::getAppUrl() === "http://localhost"){
			return true;
		}
		return false;
	}
	/**
	 * @return bool
	 */
	public static function isLocal(): bool{
		// Uncomment for debugging logging issues QMLogger::cli()->info(__METHOD__." calling getEnv...");
		$env = EnvOverride::getFormatted(Env::APP_ENV);
		return $env === static::ENV_LOCAL;
	}
	/**
	 * @return bool
	 */
	public static function isStaging(): bool{
		return AppMode::appModeIs(Env::ENV_STAGING);
	}
	public static function setLocal(){
		QMSlim::setEnvironmentalVariable(static::APP_ENV, static::ENV_LOCAL);
	}
	public static function setTesting(){
		QMSlim::setEnvironmentalVariable(static::APP_ENV, static::ENV_TESTING);
		QMSlim::bootstrapLaravelConsoleApp(); // Make sure we replace previous values if necessary
	}
	public static function setStagingRemote(){
		QMSlim::setEnvironmentalVariable(static::ENV_STAGING_REMOTE, static::ENV_TESTING);
		QMSlim::bootstrapLaravelConsoleApp(); // Make sure we replace previous values if necessary
	}
	public static function getShowUrl(): string{
		return AdminUrl::getAdminUrl('env-file');
	}
	public static function getPhpStormUrl(): string{
		return PHPStormButton::redirectUrl(".env");
	}
	public static function getPhpStormLink(): string{
		return HtmlHelper::getLinkAnchorHtml(".env", static::getPhpStormUrl());
	}
	/**
	 * @param string $name
	 * @param $value
	 */
	public static function set(string $name, $value): void
    {
		QMSlim::setEnvironmentalVariable($name, $value);
	}
	public static function saveEnvFile(string $filePath, array $envContents){
        le('DEPRECATED saveEnvFile: '.$filePath);
		$str = '';
		ksort($envContents);
		foreach($envContents as $key => $value){
			$str .= $key . "=" . $value . "\n";
		}
		FileHelper::writeByFilePath($filePath, $str);
	}
	public static function alphabetizeEnvFiles(){
		$files = static::listEnvFiles();
		foreach($files as $filePath){
			$envContents = Env::getEnvVariablesFromFile($filePath);
			Env::saveEnvFile($filePath, $envContents);
		}
	}
	public static function listEnvFiles(): array{
		$files = FileFinder::listFiles(Env::PATH_TO_CONFIGS);
		$keep = [];
		foreach($files as $filePath){
			if(str_contains($filePath, '.env')){
				$keep[] = $filePath;
			}
		}
		return $keep;
	}
	public static function addToAllEnvs(string $key, string $value){
		$files = static::listEnvFiles();
		foreach($files as $filePath){
			$envContents = Env::getEnvVariablesFromFile($filePath, false);
			$envContents[$key] = $value;
			Env::saveEnvFile($filePath, $envContents);
		}
	}
    public static function loadEnvFromDoppler(string $token):string {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://api.doppler.com/v3/configs/config/secrets/download?format=json&include_dynamic_secrets=true&dynamic_secrets_ttl_sec=1800', [
            'headers' => [
                'accept' => 'application/json',
                'authorization' => 'Basic ' . $token,
            ],
        ]);

        $envs = $response->getBody();
        $envs = json_decode($envs, true);
        foreach ($envs as $name => $value) {
            static::set($name, $value);
        }
        return Env::get('APP_ENV');
    }
	/**
	 * This is so I can run tests in PHPStorm without having to manually copy env files constantly
	 */
	public static function setAppEnvIfEmpty(): string{
		Env::set_PHP_IDE_CONFIG();
		// Getting 429 from doppler
//        $token = static::getFormatted('DOPPLER_TOKEN_TESTING');
//        if(!$token){$token = static::getFormatted('DOPPLER_TOKEN');}
//        if($token){return static::loadEnvFromDoppler($token);}
		$env = Env::get('APP_ENV');
		if(!empty($env)){
			return $env;
		}
		//if(!EnvOverride::isLocal()){return Env::get('APP_ENV');}
		$env = null;
		if(AppMode::isApiRequest()){
			if(Subdomain::is(Subdomain::LOCAL)){
				$env = Env::ENV_LOCAL;
			}
			if(Subdomain::is(Subdomain::PRODUCTION_REMOTE)){
				$env = Env::ENV_PRODUCTION_REMOTE;
			}
			if(Subdomain::is(Subdomain::STAGING_REMOTE)){
				$env = Env::ENV_STAGING_REMOTE;
			}
			if(Subdomain::is(Subdomain::TESTING)){
				$env = Env::ENV_TESTING;
			}
		}
		if(!$env){
			if(AppMode::workingDirIsStagingUnit()){
				$env = Env::ENV_STAGING_REMOTE;
			}
			if(AppMode::isNonSlimUnitTest() || AppMode::isSlimUnitTest()){
				$env = Env::ENV_TESTING;
			}
		}
		if(!$env && isset($_SERVER["argv"]) && in_array("UpdateHtmlTestFixturesTest.php", $_SERVER["argv"])){
			$env = Env::ENV_TESTING;
		}
		if(!$env && AppMode::isFailedTestRunner()){
			$env = Env::ENV_TESTING;
		}
        if(!$env && $_SERVER["PHP_SELF"] === "/var/www/html/tests/bootstrap.php"){
            $env = Env::ENV_TESTING;
        }
		if(!$env){
			if(FileHelper::fileExists(".env")){
				$values = static::readEnvFile(".env");
				$env = $values[static::APP_ENV];
				ConsoleLog::info("Using APP_ENV $env from root .env file");
			}
		}
		if(!$env){
			$env = static::ENV_TESTING;
			QMLog::error("could not determine env so just using $env");
		}
		$_ENV[static::APP_ENV] = $env;
		putenv('APP_ENV=' . $env);
		//$_SERVER["argv"][] = "--env=".$env;
		if(!FileHelper::fileExists(".env.$env")){
			$msg = "No .env.$env file so just using root .env file";
			try {
				ConsoleLog::info($msg);
			} catch (\Throwable $e){
				error_log($msg);
			}
		}
		return $env;
		//static::set(static::APP_ENV, $env);
	}
	/**
	 * @param string $file
	 * @return array
	 */
	public static function readEnvFile(string $file): array{
		$file = abs_path($file);
		$values = [];
		if(!FileHelper::fileExists($file)){
			return [];
		}
		$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach($lines as $line){
			$before = QMStr::before("#", $line, $line);
			$trimmed = trim($before);
			if(empty($trimmed)){
				continue;
			}
			try {
				[$name, $value] = explode('=', $trimmed, 2);
			} catch (\Throwable $e) {
				ConsoleLog::info(__METHOD__.": ".$e->getMessage());
				[$name, $value] = explode('=', $trimmed, 2);
			}
			$values[trim($name)] = trim(str_replace('"', '', $value));
		}
		return $values;
	}
	public static function validateEnv(){
        if(empty($_ENV[static::APP_ENV]) && !empty($_SERVER[static::APP_ENV])){
           $_ENV = $_SERVER;
        }
		if(count($_ENV) < 10){
            $msg = "\$_ENV is: " . \App\Logging\QMLog::print_r($_ENV, true) . ".  
            Failure to load can be caused by sourcing the .env in a bash script or 
            by conflicts between multiple Apache requests on Windows.  
            You can debug the loader with Env::debugDotEnvLoader or 
            putting an if (function_exists('xdebug_break')) {xdebug_break();}
            line in \Dotenv\Environment\AbstractVariables::set();
            ";
            debugger($msg);
			// Don't use le() here, so it's easier to trace source
            throw new LogicException($msg);
		}
		static::validateBooleanEnvs();
	}
	public static function logValue(string $name){
		ConsoleLog::info($name . " is " . QMLog::print_r(static::getFormatted($name), true) . " in .env");
	}
	public static function isJenkins(): bool{
		return AppMode::isJenkins();
	}
	public static function isDeployment(): bool{
		return Jenkins::currentJobNameContains("DEPLOY");
	}
	public static function getConfigsSuffix(): string{
		if(static::isDeployment()){
			return "web";
		}
		return "dev";
	}
	/**
	 * @return bool
	 */
	public static function APP_DEBUG(): bool{
		return Env::getBool('APP_DEBUG');
	}
	public static function folder(): EnvsFolder{
		return new EnvsFolder();
	}
	public static function copy(string $env){
	}
	/**
	 * @return bool
	 */
	public static function jobInPhpstorm(): bool{
		return isset($_SERVER["JETBRAINS_REMOTE_RUN"]) &&
			AppMode::workingDirectoryOrArgumentStartsWithJobsOrTasksFolder();
	}
	/**
	 * @param string $name
	 * @return bool|string|float|int
	 */
	public static function getRequired(string $name): bool|string|float|int{
		$val = Env::get($name);
		if(empty($val)){
			if(isset($_ENV[$name])){
				//debugger("getenv not working! required env var: $name");
				Env::get($name);
				return $_ENV[$name];
			}
			Env::loadEnvIfNoAppUrl();
			le((new RuntimeException("No value for ENV $name! "))->__toString());
		}
		return $val;
	}
	/**
	 * @return string
	 */
	public static function STORAGE_SECRET_ACCESS_KEY(): string{
		return Env::getRequired(Env::STORAGE_SECRET_ACCESS_KEY);
	}
	/**
	 * @return string
	 */
	public static function STORAGE_ACCESS_KEY_ID(): string{
		return Env::getRequired(Env::STORAGE_ACCESS_KEY_ID);
	}
	private static function validateBooleanEnvs(): void{
		foreach($_ENV as $key => $value){
			if($key === "JETBRAINS_REMOTE_RUN" ||
                $key === "REDIS_DB" ||
                $key === "COMPOSE_INTERACTIVE_NO_CLI" ||
			    $key === "GITHUB_REF_PROTECTED" ||
                $key === "LARAVEL_SAIL"){
				continue;
			}
			$val = Env::get($key);
			if($val === "1" && $key === "CLOCKWORK_WEB"){
				$msg = "For instance, CLOCKWORK_WEB parses 1 as a string and assumes it's a URL";
				QMLog::info("Please set $key to true instead of 1. $msg");
			}
			if($val === "false"){
				QMLog::info("Please set $key to 0 instead of false because it's sometimes considered true because it Env::get() returns a non-empty string");
			}
		}
	}
	public static function set_PHP_IDE_CONFIG(): void{ $_SERVER['PHP_IDE_CONFIG'] = "serverName=*.quantimo.do"; }
	public static function loadEnvIfNoAppUrl(): void{
		$APP_URL = getenv('APP_URL');
		if(!$APP_URL){
			Env::setAppEnvIfEmpty();
			Env::loadEnv();
			$APP_URL = getenv('APP_URL');
			if(!$APP_URL){
				debugger('APP_URL not set in .env file');
				Env::setAppEnvIfEmpty();
				Env::loadEnv();
			}
		}
	}
	public static function loadEnv(): void{
		$contents = [];
		if($appEnv = static::getAppEnv()){
			$contents = static::readEnvFile(".env.$appEnv");
		}
		if(!$contents){
			$contents = static::readEnvFile(".env");
		}
		foreach($contents as $key => $value){
			static::set($key, $value);
		}
		$url = Env::get('APP_URL');
		if(empty($url)){
			$url = Env::get('APP_URL');
		}
		if(empty($url)){
			le("No APP_URL in .env");
		}
	}
	private static function getAppEnv(): bool|string|null{
		return Env::get(static::APP_ENV);
	}
	public static function getBool(string $name): bool {
		return BoolHelper::isTrue(static::get($name));
	}
	public static function log(){
		static::outputEnv();
	}
	public static function printObfuscated(): string {
		$arr = SecretHelper::obfuscateArray(static::all());
		return QMLog::print($arr, "Obfuscated \$_ENV plus .env", true);
	}
}
