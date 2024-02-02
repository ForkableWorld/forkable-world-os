<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\PHP;
use App\Computers\ThisComputer;
use App\Console\Kernel;
use App\DevOps\XDebug;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Properties\PropertiesGenerator;
use App\Storage\DB\TestDB;
use App\Storage\DB\Writable;
use App\Types\QMStr;
use App\Utils\Env;
use DB;
class BaseModelFile extends PhpClassFile {
	public static function getFolderPaths(): array{ return ['app/Models']; }
	/**
	 * @return string|BaseModel
	 */
	public function getClassName(): string{
		return parent::getClassName();
	}
	public function updateFromDB(){
		$class = $this->getClassName();
		$class::updateModel();
		$this->generatePropertyModels(false);
		$this->reformat();
	}
	public function reformat(): void{
		parent::reformat();
		$this->replace('wp_user', 'user');
	}
	public function generatePropertyModels(bool $overwrite){
		$class = $this->getClassName();
		$class::generateProperties((new $class)->getConnectionName(), null, $overwrite);
	}
	/**
	 * @param string $table
	 * @param string|null $connection
	 * @return array
	 */
	public static function generateByTable(string $table, string $connection = null): array{
		if(!$connection){$connection = Writable::getConnectionName();}
		$schema = config('database.'.$connection.'.name');
		config()->set('models.*.connection', $connection);
		Kernel::artisan("code:models", [
			"--table" => $table,
			"--schema" => $schema,
			"--connection" => $connection,
		]);
		$paths[] = $path = app_path('Models/Base/Base' . QMStr::tableToShortClassName($table)) . ".php";
		$replacements = [
			'protected array $rules' => 'protected array $rules',
			'\\App\\Models\\WpUser' => \App\Models\User::class,
		];
		foreach($replacements as $find => $replace){
			FileHelper::replaceStringInFile($path, $find, $replace);
		}
		$generator = new PropertiesGenerator($table, [], $connection);
		$paths[] = $generator->generatePropertyModelCodeFiles();
		$class = BaseModel::getClassByTable($table);
		$shortClass = QMStr::toShortClassName($class);
		$paths = array_merge($paths, FileFinder::getFileNamesContaining("app", $shortClass, true));
		return $paths;
	}
	public static function updateModelAndProperties(string $table, string $schema = null, string $connection = null){
		self::generateByTable($table, $connection);
	}
	public static function generateModels(){
		if(!XDebug::active()){
			le("Run with xdebug active so dev service providers are loaded");
		}
		//Migrations::replaceInColumnNames("-", "_");
		Kernel::artisan('code:models');
		self::replaceWpUser();
		self::updatePHPDocs();
		self::reformatAll();
	}
	public static function updateModelsWithColumn(string $column){
		$tables = TestDB::getTableNamesWithColumn($column);
		foreach($tables as $table){
			try {
				BaseModelFile::generateByTable($table);
			} catch (\Throwable $e) {
				QMLog::error(__METHOD__.": ".$e->getMessage());
			}
		}
		ThisComputer::exec("php artisan ide-helper:models --dir=\"app/Models\" --write");
	}
	public static function replaceWpUser(): void{
		FileHelper::replaceStringInAllFilesInFolder("app/Models", "bshaffer_oauth_", "oa_", "php");
		FileHelper::replaceStringInAllFilesInFolder("app/Models", "Oa", "OA", "php");
		FileHelper::replaceStringInAllFilesInFolder("app/Models", "*", "*", "php");
		PhpClassFile::reformatAll();
		FileHelper::deleteFile('app/Models/WpUser.php', __METHOD__);
		FileHelper::replaceStringInAllFilesInFolder("app/Models/Base", "class ", "abstract class ");
		$aliases = [
			'WpUser' => 'User',
			//'OAClient' => 'OauthClient',
			"Usermetum" => "WpUsermetum",
		];
		foreach($aliases as $searchCamel => $replaceCamel){
			$searchSnake = QMStr::snakize($searchCamel);
			$replaceSnake = QMStr::snakize($replaceCamel);
			FileHelper::replaceTextInAllFilesRecursively("app/Models", "WpUser $" . $searchSnake,
				"User $" . $replaceSnake);
			FileHelper::replaceTextInAllFilesRecursively("app/Models", "function $searchSnake",
				"function $replaceSnake");
			FileHelper::replaceTextInAllFilesRecursively("app/Models", "$searchCamel::", "$replaceCamel::");
			FileHelper::replaceTextInAllFilesRecursively("app/Models", "WpUser[] $" . $searchSnake,
				"User[] $" . $replaceSnake);
			FileHelper::replaceTextInAllFilesRecursively("app/Models", "App\\Models\\$searchCamel ",
				"App\\Models\\$replaceCamel ");
			FileHelper::replaceTextInAllFilesRecursively("app/Models", "App\\Models\\$searchCamel;",
				"App\\Models\\$replaceCamel;");
		}
		static::replaceInAll('wp_user', 'user');
		static::replaceInAll("abstract abstract class", "abstract class");
		static::replaceInAll("WpWp", "Wp");
	}
	public static function updatePHPDocs(){
		Env::setLocal();
		//self::artisan('ide-helper:models', ['--write' => 1, '--dir' => ["app/Models"], '-vvv' => 1]);  // Not sure why this doesn't work
		ThisComputer::exec("echo \$PWD");
		ThisComputer::exec("export APP_ENV=local && php artisan ide-helper:models --write");
	}
	/**
	 * @param string $class
	 */
	public static function generateResources(string $class){
		try {
			Kernel::artisan("resource-file:from-database", ['model-name' => $class, '--force' => true]);
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
		}
		try {
			Kernel::artisan("create:resources", ['model-name' => $class, '--table-exists' => true]);
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
		}
	}
}
