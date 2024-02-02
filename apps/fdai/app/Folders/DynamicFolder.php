<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Folders;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Files\TypedProjectFile;
use App\Files\UntypedFile;
use App\Logging\ConsoleLog;
use App\Traits\CanBeCalledStatically;
use App\Traits\ConstantGenerator;
use App\Types\QMStr;
use Illuminate\Support\Collection;
use League\Flysystem\Directory;
use Storage;
class DynamicFolder extends Directory {
	use ConstantGenerator;
	use CanBeCalledStatically;
	public const FOLDER_APP = 'app';
	public const FOLDER_APP_APP_SETTINGS = 'app/AppSettings';
	public const FOLDER_APP_BUTTONS = 'app/Buttons';
	public const FOLDER_APP_CARDS = 'app/Cards';
	public const FOLDER_APP_CHARTS = 'app/Charts';
	public const FOLDER_APP_CODE_GENERATORS = 'app/CodeGenerators';
	public const FOLDER_APP_CONSOLE = 'app/Console';
	public const FOLDER_APP_CONVERSATIONS = 'app/Conversations';
	public const FOLDER_APP_CORRELATIONS = 'app/VariableRelationships';
	public const FOLDER_APP_DATA_SOURCES = 'app/DataSources';
	public const FOLDER_APP_DATA_TABLE_SERVICES = 'app/DataTableServices';
	public const FOLDER_APP_DEV_OPS = 'app/DevOps';
	public const FOLDER_APP_EVENTS = 'app/Events';
	public const FOLDER_APP_EXCEPTIONS = 'app/Exceptions';
	public const FOLDER_APP_FILES = 'app/Files';
	public const FOLDER_APP_FOLDERS = 'app/Folders';
	public const FOLDER_APP_FORMS = 'app/Forms';
	public const FOLDER_APP_FORMULAS = 'app/Formulas';
	public const FOLDER_APP_HELPERS = 'app/Helpers';
	public const FOLDER_APP_HTTP = 'app/Http';
	public const FOLDER_APP_IMPORTERS = 'app/Importers';
	public const FOLDER_APP_IMPORTS = 'app/Imports';
	public const FOLDER_APP_INPUT_FIELDS = 'app/InputFields';
	public const FOLDER_APP_INTENTS = 'app/Intents';
	public const FOLDER_APP_JOBS = 'app/Jobs';
	public const FOLDER_APP_LINKS = 'app/Links';
	public const FOLDER_APP_LISTENERS = 'app/Listeners';
	public const FOLDER_APP_LOGGING = 'app/Logging';
	public const FOLDER_APP_MAIL = 'app/Mail';
	public const FOLDER_APP_MENUS = 'app/Menus';
	public const FOLDER_APP_MODELS = 'app/Models';
	public const FOLDER_APP_NOTIFICATIONS = 'app/Notifications';
	public const FOLDER_APP_NOVA = 'app/Astral';
	public const FOLDER_APP_OBSERVERS = 'app/Observers';
	public const FOLDER_APP_OVERRIDE = 'app/Override';
	public const FOLDER_APP_PAGES = 'app/Pages';
	public const FOLDER_APP_PARAMETERS = 'app/Parameters';
	public const FOLDER_APP_POLICIES = 'app/Policies';
	public const FOLDER_APP_PRODUCTS = 'app/Products';
	public const FOLDER_APP_PROPERTIES = 'app/Properties';
	public const FOLDER_APP_PROVIDERS = 'app/Providers';
	public const FOLDER_APP_QUANTIMODO = 'app/Quantimodo';
	public const FOLDER_APP_REPORTS = 'app/Reports';
	public const FOLDER_APP_REPOS = 'app/Repos';
	public const FOLDER_APP_RULES = 'app/Rules';
	public const FOLDER_APP_SCRAPERS = 'app/Scrapers';
	public const FOLDER_APP_SCRIPTS = 'app/Scripts';
	public const FOLDER_APP_SERVICES = 'app/Services';
	public const FOLDER_APP_SHELL_COMMANDS = 'app/ShellCommands';
	public const FOLDER_APP_SOLUTIONS = 'app/Solutions';
	public const FOLDER_APP_SOLUTION_PROVIDERS = 'app/SolutionProviders';
	public const FOLDER_APP_SORTS = 'app/Sorts';
	public const FOLDER_APP_STORAGE = 'app/Storage';
	public const FOLDER_APP_STRATEGIES = 'app/Strategies';
	public const FOLDER_APP_STUDIES = 'app/Studies';
	public const FOLDER_APP_TABLES = 'app/Tables';
	public const FOLDER_APP_TRAITS = 'app/Traits';
	public const FOLDER_APP_TYPES = 'app/Types';
	public const FOLDER_APP_UI = 'app/UI';
	public const FOLDER_APP_UNITS = 'app/Units';
	public const FOLDER_APP_UNIT_CATEGORIES = 'app/UnitCategories';
	public const FOLDER_APP_UTILS = 'app/Utils';
	public const FOLDER_APP_VARIABLES = 'app/Variables';
	public const FOLDER_APP_VARIABLE_CATEGORIES = 'app/VariableCategories';
	public const FOLDER_APP_WIDGETS = 'app/Widgets';
	public const FOLDER_BIN = 'bin';
	public const FOLDER_BOOTSTRAP = 'bootstrap';
	public const FOLDER_BUILD = 'build';
	public const FOLDER_CONFIG = 'config';
	public const FOLDER_CONFIGS = 'configs';
	public const FOLDER_DATA = 'data';
	public const FOLDER_DATABASE = 'database';
	public const FOLDER_DATABASE_MEDRA = 'database/medra';
	public const FOLDER_DATABASE_MIGRATIONS = 'database/migrations';
	public const FOLDER_DATABASE_SCRIPTS = 'database/scripts';
	public const FOLDER_DATABASE_SEEDS = 'database/seeds';
	public const FOLDER_DATABASE_TABLES = 'database/tables';
	public const FOLDER_DATABASE_TRIGGERS = 'database/triggers';
	public const FOLDER_DATABASE_VIEWS = 'database/views';
	public const FOLDER_DOCS = 'docs';
	public const FOLDER_GITHUB = '.github';
	public const FOLDER_IDEA = '.idea';
	public const FOLDER_APP_PHPUNIT_JOBS = 'app/PhpUnitJobs';
	public const FOLDER_LOG = 'log';
	public const FOLDER_NODE_MODULES = 'node_modules';
	public const FOLDER_NOVA = 'astral';
	public const FOLDER_PUBLIC = 'public';
	public const FOLDER_PUBLIC_ALPINE = 'public/alpine';
	public const FOLDER_PUBLIC_API = 'public/api';
	public const FOLDER_PUBLIC_APP_DESIGNER = 'public/app-designer';
	public const FOLDER_PUBLIC_BILLING = 'public/billing';
	public const FOLDER_PUBLIC_BUILDER = 'public/builder';
	public const FOLDER_PUBLIC_CSS = 'public/css';
	public const FOLDER_PUBLIC_DELETE = 'public/delete';
	public const FOLDER_PUBLIC_DEV = 'public/dev';
	public const FOLDER_PUBLIC_DEVELOPER = 'public/developer';
	public const FOLDER_PUBLIC_DEVELOPERS = 'public/developers';
	public const FOLDER_PUBLIC_DEV_DOCS = 'public/dev-docs';
	public const FOLDER_PUBLIC_DISCOVERIES = 'public/discoveries';
	public const FOLDER_PUBLIC_DOCS = 'public/docs';
	public const FOLDER_PUBLIC_DR = 'public/dr';
	public const FOLDER_PUBLIC_EMBEDDABLE = 'public/embeddable';
	public const FOLDER_PUBLIC_ERROR = 'public/error';
	public const FOLDER_PUBLIC_EXPORT = 'public/export';
	public const FOLDER_PUBLIC_FONTS = 'public/fonts';
	public const FOLDER_PUBLIC_GOACCESS = 'public/goaccess';
	public const FOLDER_PUBLIC_HOME = 'public/home';
	public const FOLDER_PUBLIC_IMG = 'public/img';
	public const FOLDER_PUBLIC_IMPORT = 'public/import';
	public const FOLDER_PUBLIC_IONIC = 'public/ionic';
	public const FOLDER_PUBLIC_JS = 'public/js';
	public const FOLDER_PUBLIC_LOGIN = 'public/login';
	public const FOLDER_PUBLIC_LOGOUT = 'public/logout';
	public const FOLDER_PUBLIC_MATERIAL = 'public/material';
	public const FOLDER_PUBLIC_PIMP = 'public/pimp';
	public const FOLDER_PUBLIC_PRIVACY = 'public/privacy';
	public const FOLDER_PUBLIC_PRIVACY_POLICY = 'public/privacy-policy';
	public const FOLDER_PUBLIC_QM_APPLICATION_SETTINGS = 'public/qm-application-settings';
	public const FOLDER_PUBLIC_QM_CONNECT = 'public/qm-connect';
	public const FOLDER_PUBLIC_QM_STATIC_DATA = 'public/qm-static-data';
	public const FOLDER_PUBLIC_REGISTER = 'public/register';
	public const FOLDER_PUBLIC_RESEARCHER = 'public/researcher';
	public const FOLDER_PUBLIC_RESEARCHERS = 'public/researchers';
	public const FOLDER_PUBLIC_STUDY = 'public/study';
	public const FOLDER_PUBLIC_SVG = 'public/svg';
	public const FOLDER_PUBLIC_TAILWIND = 'public/tailwind';
	public const FOLDER_PUBLIC_UPGRADE = 'public/upgrade';
	public const FOLDER_PUBLIC_UPLOADS = 'public/uploads';
	public const FOLDER_PUBLIC_VENDOR = 'public/vendor';
	public const FOLDER_PUBLIC_VENDORS = 'public/vendors';
	public const FOLDER_PUBLIC_WP = 'public/wp';
	public const FOLDER_REPOS = 'repos';
	public const FOLDER_REPOS_POWERSHELL_PHP_WRAPPER = 'repos/powershell-php-wrapper';
	public const FOLDER_RESOURCES = 'resources';
	public const FOLDER_RESOURCES_JSON_RESPONSES = 'resources/json-responses';
	public const FOLDER_RESOURCES_LANG = 'resources/lang';
	public const FOLDER_RESOURCES_LARAVEL_CODE_GENERATOR = 'resources/laravel-code-generator';
	public const FOLDER_RESOURCES_MODEL_SCHEMAS = 'resources/model_schemas';
	public const FOLDER_RESOURCES_SASS = 'resources/sass';
	public const FOLDER_RESOURCES_TEMPLATES = 'resources/templates';
	public const FOLDER_RESOURCES_VIEWS = 'resources/views';
	public const FOLDER_ROUTES = 'routes';
	public const FOLDER_RUN = '.run';
	public const FOLDER_SCRIPTS = 'scripts';
	public const FOLDER_SCRIPTS_DEPLOY = 'scripts/deploy';
	public const FOLDER_SCRIPTS_FILES = 'scripts/files';
	public const FOLDER_SCRIPTS_GIT = 'scripts/git';
	public const FOLDER_SCRIPTS_LIB = 'scripts/lib';
	public const FOLDER_SCRIPTS_MONGO = 'scripts/mongo';
	public const FOLDER_SCRIPTS_MYSQL = 'scripts/mysql';
	public const FOLDER_SCRIPTS_PHP = 'scripts/php';
	public const FOLDER_SCRIPTS_PROVISION = 'scripts/provision';
	public const FOLDER_SCRIPTS_TESTS = 'scripts/tests';
	public const FOLDER_SCRIPTS_WINDOWS = 'scripts/windows';
	public const FOLDER_SCRIPTS_WSL = 'scripts/wsl';
	public const FOLDER_STATIC = 'static';
	public const FOLDER_STORAGE = 'storage';
	public const FOLDER_TESTS = 'tests';
	public const FOLDER_TESTS_BROWSER = 'tests/Browser';
	public const FOLDER_TESTS_SLIM_TESTS = 'tests/SlimTests';
	public const FOLDER_TESTS_DUSK = 'tests/Dusk';
	public const FOLDER_TESTS_FIXTURES = 'tests/fixtures';
	public const FOLDER_TESTS_GENERATED = 'tests/Generated';
	public const FOLDER_TESTS_IVR = 'tests/IVR';
	public const FOLDER_TESTS_LARAVEL_TESTS = 'tests/LaravelTests';
	public const FOLDER_TESTS_PHPSTORM_REQUESTS = 'tests/phpstorm requests';
	public const FOLDER_TESTS_STAGING_UNIT_TESTS = 'tests/StagingUnitTests';
	public const FOLDER_TESTS_TEST_GENERATORS = 'tests/TestGenerators';
	public const FOLDER_TESTS_TRAITS = 'tests/Traits';
	public const FOLDER_TESTS_UNIT_TESTS = 'tests/UnitTests';
	public const FOLDER_TMP = 'tmp';
	public const FOLDER_VENDOR = 'vendor';
	public const FOLDER_VENDOR_OVERRIDES = 'vendor-overrides';
	public const LOCAL_REPO_PATH = '/var/www/html';
	const NGINX_LOGS = '/var/log/nginx';
	const RESOURCES_JSON_SCHEMA = 'resources/json-schema';
	const STORAGE = 'storage';
	const STORAGE_LOGS = self::STORAGE . './logs';
	const STORAGE_LOGS_PHPUNIT = self::STORAGE_LOGS . './phpunit';
	/**
	 * @param string $path
	 */
	public function __construct(string $path){
		$path = FileHelper::absPath($path);
		try {
			$filesystem = Storage::disk('local')->getDriver();
		} catch (\Throwable $e){
		    ConsoleLog::info(" Could not set filesystem for ".static::class." because: ".$e->getMessage());
			$filesystem = null;
		}
		parent::__construct($filesystem, $path);
	}
	public static function getAbsolutePath(): string{
		return FileHelper::absPath(static::relativePath());
	}
	public static function folderToNamespace(string $filePath): string{
		return FileHelper::folderToNamespace($filePath);
	}
	/**
	 * @param string|null $path
	 * @param bool        $recursive
	 * @param string[]    $excludeLike
	 * @return UntypedFile[]|Collection
	 */
	public static function listFolders(string $path = null, bool $recursive = false,
	                                   array  $excludeLike = []): Collection{
		$files = FileFinder::listFolders($path, $recursive, $excludeLike);
		return self::instantiateArray($files);
	}
	/**
	 * @param array $paths
	 * @return Collection|static[]
	 */
	protected static function instantiateArray(array $paths): Collection{
		$files = [];
		foreach($paths as $path){
			$files[$path] = static::instantiate($path);
		}
		return collect($files);
	}
	/**
	 * @param $path
	 * @return static
	 */
	protected static function instantiate($path): self{
		return new static($path);
	}
	public static function all(): array{
		return FileFinder::listFolders(static::relativePath());
	}
	/**
	 * @param string $str
	 * @return string
	 */
	public static function stripLocalRepoPath(string $str): string{
		$str = str_replace(DynamicFolder::LOCAL_REPO_PATH . "/", "", $str);
		return $str;
	}
	public static function generateFolders(){
		$folders = self::listFolders(null, false);
		foreach($folders as $folder){
			$relative = FileHelper::getRelativePath($folder);
			try {
				$contents = FileHelper::getContents('app/Files/Folder.stub');
			} catch (QMFileNotFoundException $e) {
				le($e);
				throw new \LogicException();
			}
			$shortClass = QMStr::toShortClassName($relative) . "Folder";
			$contents = str_replace("{{class_name}}", $shortClass, $contents);
			$contents = str_replace("{{relative_path}}", $relative, $contents);
			FileHelper::writeByFilePath("app/Folders/$shortClass.php", $contents);
		}
	}
	/**
	 * @return static
	 */
	public static function instance(): self{ return static::get(); }
	/**
	 * @return static
	 */
	public static function get(): self{ return new static(); }
	public static function getClassesInFolder(string $folder): array{
		return FileHelper::getClassesInFolder($folder);
	}
	public static function findOrNew(string $getDestinationPath): DynamicFolder{
		return new static($getDestinationPath);
	}
	public function getNewestFile(bool $recursive = false, string $filenameLike = null,
	                              string $notLike = null): ?TypedProjectFile{
		$files = $this->getFiles($recursive, $filenameLike, $notLike);
		$descending = $files->sort(function($f){
			/** @var UntypedFile $f */
			$at = $f->getModifiedAt();
			return -1 * strtotime($at);
		});
		return $descending->first();
	}
	/**
	 * @param bool        $recursive
	 * @param string|null $filenameLike
	 * @param string|null $notLike
	 * @return Collection|
	 */
	public function getFiles(bool $recursive = false, string $filenameLike = null, string $notLike = null): Collection{
		$files = FileFinder::listFiles($this->getPath(), $recursive, $filenameLike, $notLike);
		return TypedProjectFile::instantiateArray($files);
	}
	public function getDotsPathToRoot(): string{
		$relative = $this->getRelativePath();
		$str = self::dotsRelativeToRoot($relative);
		return $str;
	}
	public function getRelativePath(): string{
		$path = $this->getPath();
		return FileHelper::toRelativePath($path);
	}
	/**
	 * @param string $relative
	 * @return string
	 */
	public static function dotsRelativeToRoot(string $relative): string{
		$relative = FileHelper::toRelativePath($relative);
		$num = self::numberOfDirsFromRoot($relative);
		$i = 1;
		$arr = [];
		while($i < $num){
			$arr[] = "..";
			$i++;
		}
		$str = implode("/", $arr);
		return $str;
	}
	public static function numberOfDirsFromRoot(string $relative): int{
		return QMStr::numberOfOccurrences($relative, "/")+1;
	}
	public function getPathToContents(string $relativePath): string{
		return $this->getPath()."/$relativePath";
	}
	public static function generateConstantName(string $str): string{
		$path = relative_path($str);
		$path = str_replace(".", "", $path);
		$path = self::folderPathToConstantName($path);
		return "FOLDER_".$path;
	}
	/**
	 * @return array
	 * @noinspection PhpUnhandledExceptionInspection
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	protected static function generateConstantValues(): array{
		$exclude = ['_gsdata_', '__clockwork', 'laradock', 'idea', 'vscode'];
		$folders = FileFinder::listFolders(abs_path(), false, $exclude);
		$folders = array_merge($folders, FileFinder::listFolders(app_path(), false, $exclude));
		$folders = array_merge($folders, FileFinder::listFolders(public_path(), false, $exclude));
		$folders = array_merge($folders, FileFinder::listFolders(tests_path(), false, $exclude));
		$folders = array_merge($folders, FileFinder::listFolders(configs_path(), false, $exclude));
		$folders = array_merge($folders, FileFinder::listFolders(database_path(), false, $exclude));
		//$folders = array_merge($folders, FileHelper::listFolders(repos_path(), false, $exclude));
		$folders = array_merge($folders, FileFinder::listFolders(resource_path(), false, $exclude));
		$folders = array_merge($folders, FileFinder::listFolders(scripts_path(), false, $exclude));
		$rel = [];
		foreach($folders as $folder){
			$rel[] = relative_path($folder);
		}
		return $rel;
	}
}
