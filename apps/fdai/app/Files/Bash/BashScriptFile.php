<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Files\Bash;
use App\Files\FileExtension;
use App\Files\FileHelper;
use App\Files\FileLine;
use App\Files\TypedProjectFile;
use App\Folders\DynamicFolder;
use App\Traits\ConstantGenerator;
use App\Traits\FileTraits\HasFileTemplate;
use App\Traits\FileTraits\IsFormattableFile;
use App\Types\QMStr;
use Illuminate\Support\Collection;
use Illuminate\View\View;
/**
 * @package App\Files\Bash
 */
class BashScriptFile extends TypedProjectFile {
	use HasFileTemplate, IsFormattableFile, ConstantGenerator;
	public const SCRIPTS_PROVISION_SH = "scripts/provision.sh";
	protected $mainContent;
	const BASH_PREFIX = "#!/usr/bin/env bash";
	public const GLOBALLY_REQUIRED = [
		'functions.sh" "${BASH_SOURCE[0]}"',
	];
	const GLOBAL_CONTENT_PREFIX = self::BASH_PREFIX . "
# shellcheck disable=SC2145
# shellcheck disable=SC2053";
	public const SCRIPT_AFTER = 'configs/homestead/after.sh';
	public const SCRIPT_AJENTI_INSTALL = 'scripts/provision/ajenti_install.sh';
	public const SCRIPT_AJENTI_START = 'scripts/ajenti_start.sh';
	public const SCRIPT_ALLOW_ROOT_ACCESS_WITH_PASSWORD = 'scripts/allow_root_access_with_password.sh';
	public const SCRIPT_ALL_FUNCTIONS = 'scripts/all_functions.sh';
	public const SCRIPT_ARRAY = 'scripts/lib/bsfl/examples/array.sh';
	public const SCRIPT_BACKUP_CONFIGS = 'scripts/backup_configs.sh';
	public const SCRIPT_BACKUP_IDE = 'scripts/backup_ide.sh';
	public const SCRIPT_BASH_SCRIPT_QM = 'configs/.idea.bak/fileTemplates/Bash Script QM.sh';
	public const SCRIPT_BATS_INSTALL = 'scripts/lib/bsfl/test/bats_install.sh';
	public const SCRIPT_BSFL = 'scripts/lib/bsfl/lib/bsfl.sh';
	public const SCRIPT_BUILD_ON_WEB_SERVER = 'scripts/deploy/build_on_web_server.sh';
	public const SCRIPT_CHECK_URL_STATUS = 'scripts/deploy/check_url_status.sh';
	public const SCRIPT_CLONE_OR_PULL = 'scripts/provision/clone_or_pull.sh';
	public const SCRIPT_CLONE_QM_DOCKER = 'scripts/provision/clone_qm_docker.sh';
	public const SCRIPT_CLONE_QM_DOCKER_SHALLOW = 'scripts/provision/clone_qm_docker_shallow.sh';
	public const SCRIPT_CLONE_SUB_REPOS = 'scripts/provision/clone_sub_repos.sh';
	public const SCRIPT_CODE_COVERAGE = 'scripts/tests/code-coverage.sh';
	public const SCRIPT_COMMAND = 'scripts/lib/bsfl/examples/command.sh';
	public const SCRIPT_COMMIT_TO_FEATURE_BRANCH = 'scripts/git/commit_to_feature_branch.sh';
	public const SCRIPT_COPY = 'scripts/copy.sh';
	public const SCRIPT_COPY_NEW_RECORDS_INTO_PERMANENT_TABLES = 'scripts/mysql/copy_new_records_into_permanent_tables.sh';
	public const SCRIPT_CREATE_SWAP = 'scripts/provision/create_swap.sh';
	public const SCRIPT_CS_FIXER = 'scripts/cs_fixer.sh';
	public const SCRIPT_CYBERPANEL_INSTALL = 'scripts/cyberpanel_install.sh';
	public const SCRIPT_CYPRESS_TESTS = 'scripts/tests/cypress_tests.sh';
	public const SCRIPT_DEBUG_COMPOSER_INSTALL = 'scripts/debug_composer_install.sh';
	public const SCRIPT_DELETE_TABLES_WITH_PREFIX = 'scripts/mysql/delete_tables_with_prefix.sh';
	public const SCRIPT_DEPLOYER_INSTALL = 'scripts/deploy/deployer_install.sh';
	public const SCRIPT_DEPLOY_PRODUCTION = 'scripts/deploy/deploy_production.sh';
	public const SCRIPT_DEPLOY_STAGING = 'scripts/deploy/deploy_staging.sh';
	public const SCRIPT_DOCKER_INSTALL = 'scripts/provision/docker_install.sh';
	public const SCRIPT_DOCKER_PHPUNIT_INSTALL = 'scripts/tests/docker_phpunit_install.sh';
	public const SCRIPT_DO_SPACES_SIMPLE = 'scripts/do_spaces_simple.sh';
	public const SCRIPT_DUMP_RECORDS_FROM_YESTERDAY = 'scripts/mysql/dump_records_from_yesterday.sh';
	public const SCRIPT_DUMP_TEST_FIXTURES = 'scripts/tests/dump_test_fixtures.sh';
	public const SCRIPT_ENV = 'scripts/env.sh';
	public const SCRIPT_ENV_FUNCTIONS = 'scripts/lib/env_functions.sh';
	public const SCRIPT_ETCKEEPER_INSTALL = 'scripts/etckeeper_install.sh';
	public const SCRIPT_ETCKEEPER_PUSH = 'scripts/etckeeper_push.sh';
	public const SCRIPT_ETC_CONFIGS_COPY = 'scripts/etc_configs_copy.sh';
	public const SCRIPT_EXPORT_MYSQL_TABLE_AS_PHPUNIT_XML = 'scripts/tests/export_mysql_table_as_phpunit_xml.sh';
	public const SCRIPT_FILEBEAT = 'scripts/filebeat.sh';
	public const SCRIPT_FILESYSTEM_FUNCTIONS = 'scripts/lib/filesystem_functions.sh';
	public const SCRIPT_FILE_AND_DIR = 'scripts/lib/bsfl/examples/file_and_dir.sh';
	public const SCRIPT_FREE_UP_DISK_SPACE = 'scripts/free-up-disk-space.sh';
	public const SCRIPT_GITHUB_UPDATE_STATUS = 'scripts/tests/github_update_status.sh';
	public const SCRIPT_GIT_FUNCTIONS = 'scripts/lib/git_functions.sh';
	public const SCRIPT_GIT_LOG_EXPORT = 'scripts/git/git_log_export.sh';
	public const SCRIPT_GIT_PULL_DEVELOP = 'scripts/git/git_pull_develop.sh';
	public const SCRIPT_HEROKU_BUILD = 'scripts/heroku_build.sh';
	public const SCRIPT_HEROKU_DEPLOY = 'scripts/heroku_deploy.sh';
	public const SCRIPT_HIGHCHARTS_EXPORT_SERVER_INSTALL = 'scripts/provision/highcharts_export_server_install.sh';
	public const SCRIPT_HOMESTEAD_WSL2 = 'configs/homestead/homestead_wsl2.sh';
	public const SCRIPT_HOME_DEV_COPY = 'scripts/home_dev_copy.sh';
	public const SCRIPT_HOME_PERMISSIONS = 'scripts/home_permissions.sh';
	public const SCRIPT_IMPORT_DB = 'scripts/mysql/import_db.sh';
	public const SCRIPT_IMPORT_MEDRA_DB_FROM_S3 = 'database/medra/import_medra_db_from_s3.sh';
	public const SCRIPT_INIT_FUNCTIONS = 'scripts/lib/init_functions.sh';
	public const SCRIPT_INSTALL = 'scripts/lib/bsfl/test/bats-core/install.sh';
	public const SCRIPT_INSTALL_DEPENDENCIES_OR_RUN_TESTS = 'scripts/tests/install_dependencies_or_run_tests.sh';
	public const SCRIPT_JENKINS_BACKUP = 'scripts/jenkins_backup.sh';
	public const SCRIPT_JENKINS_INSTALL = 'scripts/jenkins_install.sh';
	public const SCRIPT_JENKINS_PERMISSIONS = 'scripts/jenkins_permissions.sh';
	public const SCRIPT_JENKINS_RESTART = 'scripts/jenkins_restart.sh';
	public const SCRIPT_JENKINS_UPDATE = 'scripts/jenkins_update.sh';
	public const SCRIPT_LARAVEL_BASH_HELPERS = 'scripts/lib/laravel_bash_helpers.sh';
	public const SCRIPT_LOG = 'scripts/lib/bsfl/examples/log.sh';
	public const SCRIPT_LOGGER_FUNCTIONS = 'scripts/lib/logger_functions.sh';
	public const SCRIPT_LOGGLY_INSTALL = 'scripts/provision/loggly_install.sh';
	public const SCRIPT_LOGZ_IO_INSTALL = 'scripts/provision/logz_io_install.sh';
	public const SCRIPT_MAILPARSE_FOR_PHP7_INSTALL = 'scripts/provision/mailparse_for_php7_install.sh';
	public const SCRIPT_MEDIAWIKI_INSTALL = 'scripts/provision/mediawiki_install.sh';
	public const SCRIPT_MESSAGE = 'scripts/lib/bsfl/examples/message.sh';
	public const SCRIPT_MIGRATE_DB = 'scripts/mysql/migrate_db.sh';
	public const SCRIPT_MONGODB_PHP_PLESK = 'scripts/mongo/mongodb_php_plesk.sh';
	public const SCRIPT_MONGO_DB_BACKUP = 'scripts/mongo/mongo_db_backup.sh';
	public const SCRIPT_MONGO_DB_CREATE_USER = 'scripts/mongo/mongo_db_create_user.sh';
	public const SCRIPT_MONGO_DB_FOR_PHP = 'scripts/mongo/mongo_db_for_php.sh';
	public const SCRIPT_MONGO_DB_INSTALL = 'scripts/mongo/mongo_db_install.sh';
	public const SCRIPT_MONGO_DB_REMOVE = 'scripts/mongo/mongo_db_remove.sh';
	public const SCRIPT_MONGO_DB_RESTORE = 'scripts/mongo/mongo_db_restore.sh';
	public const SCRIPT_MYSQL_BACKUP = 'scripts/mysql/mysql_backup.sh';
	public const SCRIPT_MYSQL_COPY_PRODUCTION_TO_STAGING = 'scripts/mysql/mysql_copy_production_to_staging.sh';
	public const SCRIPT_MYSQL_IMPORT_DEVELOPMENT_DATABASE = 'scripts/mysql/mysql_import_development_database.sh';
	public const SCRIPT_MYSQL_LOCAL_BACKUP_AND_RESTORE = 'scripts/mysql/mysql_local_backup_and_restore.sh';
	public const SCRIPT_MYSQL_REPLICATION_FROM_MASTER = 'scripts/mysql/mysql_replication_from_master.sh';
	public const SCRIPT_MYSQL_RESTART = 'scripts/mysql/mysql_restart.sh';
	public const SCRIPT_MYSQL_RESTART_CRON_JOB = 'scripts/mysql/mysql_restart_cron_job.sh';
	public const SCRIPT_MYSQL_RESTORE = 'scripts/mysql/mysql_restore.sh';
	public const SCRIPT_MYSQL_TABLE_COPY = 'scripts/mysql/mysql_table_copy.sh';
	public const SCRIPT_MYSQL_UNINSTALL = 'scripts/mysql/mysql_uninstall.sh';
	public const SCRIPT_N8N = 'scripts/n8n.sh';
	public const SCRIPT_NETWORK = 'scripts/lib/bsfl/examples/network.sh';
	public const SCRIPT_NGINX_AMPLIFY = 'scripts/provision/nginx_amplify.sh';
	public const SCRIPT_NVM = 'scripts/nvm.sh';
	public const SCRIPT_PARSE_DB_URL = 'scripts/mysql/parse_db_url.sh';
	public const SCRIPT_PERMISSIONS_FUNCTIONS = 'scripts/lib/permissions_functions.sh';
	public const SCRIPT_PHANTOMJS_INSTALL = 'scripts/provision/phantomjs_install.sh';
	public const SCRIPT_PHPCODESNIFFER = 'scripts/phpcodesniffer.sh';
	public const SCRIPT_PHPMETRICS = 'scripts/phpmetrics.sh';
	public const SCRIPT_PHPSTAN = 'scripts/phpstan.sh';
	public const SCRIPT_PHPUNIT_TESTS = 'scripts/tests/phpunit_tests.sh';
	public const SCRIPT_PHPUNIT_TEST_ONE = 'scripts/tests/phpunit_test_one.sh';
	public const SCRIPT_PHP_FUNCTIONS = 'scripts/lib/php_functions.sh';
	public const SCRIPT_PHP_UPDATE = 'scripts/php_update.sh';
	public const SCRIPT_RDS_MASTER_REPLICATION_SETUP = 'scripts/mysql/rds_master_replication_setup.sh';
	public const SCRIPT_REDIS_INSTALL = 'scripts/provision/redis_install.sh';
	public const SCRIPT_RELEASE = 'scripts/lib/bsfl/test/bats-core/contrib/release.sh';
	public const SCRIPT_RENOVATE = 'scripts/renovate.sh';
	public const SCRIPT_RESTART_SERVICES = 'scripts/restart_services.sh';
	public const SCRIPT_RUN_JOB = 'app/PhpUnitJobs/run_job.sh';
	public const SCRIPT_S3NINJA = 'scripts/files/s3ninja.sh';
	public const SCRIPT_S3SIMPLE = 'scripts/files/s3simple.sh';
	public const SCRIPT_SCRIPT_TEMPLATE_MERGER = 'scripts/script_template_merger.sh';
	public const SCRIPT_SERVICES = 'scripts/services.sh';
	public const SCRIPT_SERVICES_WSL = 'scripts/services_wsl.sh';
	public const SCRIPT_SERVICE_FUNCTIONS = 'scripts/lib/service_functions.sh';
	public const SCRIPT_PROVISION = 'scripts/provision.sh';
	public const SCRIPT_SET_PRODUCTION_CONNECTION_VARIABLES = 'scripts/mysql/set_production_connection_variables.sh';
	public const SCRIPT_SHELLCHECK = 'scripts/lib/bsfl/test/bats-core/shellcheck.sh';
	public const SCRIPT_STACK = 'scripts/lib/bsfl/examples/stack.sh';
	public const SCRIPT_STAGING_PHPUNIT_TESTS = 'scripts/tests/staging_phpunit_tests.sh';
	public const SCRIPT_STRING = 'scripts/lib/bsfl/examples/string.sh';
	public const SCRIPT_STUDY_IMAGES = 'scripts/study_images.sh';
	public const SCRIPT_SWAP = 'scripts/provision/swap.sh';
	public const SCRIPT_SYMLINKS = 'scripts/symlinks.sh';
	public const SCRIPT_SYMLINK_SLAVE_FOLDERS = 'scripts/symlink_slave_folders.sh';
	public const SCRIPT_SYMLINK_WSL = 'scripts/symlink_wsl.sh';
	public const SCRIPT_SYNCHRONIZE_SERVER_TIME = 'scripts/synchronize_server_time.sh';
	public const SCRIPT_SYNC_TO_RELEASE_FOLDER = 'scripts/deploy/sync_to_release_folder.sh';
	public const SCRIPT_TEST_WEB = 'scripts/tests/test_web.sh';
	public const SCRIPT_TIDEWAYS_INSTALL = 'configs/xhgui/tideways_install.sh';
	public const SCRIPT_TIME = 'scripts/lib/bsfl/examples/time.sh';
	public const SCRIPT_TIME_FUNCTIONS = 'scripts/lib/time_functions.sh';
	public const SCRIPT_TRANSFER_NEW_DATA_FROM_PRODUCTION_TO_DEVELOPMENT_DATABASE = 'scripts/mysql/transfer_new_data_from_production_to_development_database.sh';
	public const SCRIPT_TRANSFER_NEW_RECORDS_TO_NEW_DATABASE = 'scripts/mysql/transfer_new_records_to_new_database.sh';
	public const SCRIPT_TRANSFER_TOKENS_TO_NEW_DATABASE = 'scripts/mysql/transfer_tokens_to_new_database.sh';
	public const SCRIPT_TRAVIS_BUILD_TRIGGER = 'scripts/tests/travis_build_trigger.sh';
	public const SCRIPT_UI_TESTS = 'scripts/tests/ui_tests.sh';
	public const SCRIPT_UNINSTALL = 'scripts/lib/bsfl/test/bats-core/uninstall.sh';
	public const SCRIPT_UNIX_LINE_ENDINGS_FIX = 'scripts/unix_line_endings_fix.sh';
	public const SCRIPT_UPDATE_COLLECTOR_FIXTURES = 'scripts/tests/update_collector_fixtures.sh';
	public const SCRIPT_UPDATE_HTML_FIXTURES = 'scripts/tests/update_html_fixtures.sh';
	public const SCRIPT_VAGRANT_USER_SETUP = 'scripts/provision/vagrant_user_setup.sh';
	public const SCRIPT_VALIDATION_FUNCTIONS = 'scripts/lib/validation_functions.sh';
	public const SCRIPT_VARIABLE = 'scripts/lib/bsfl/examples/variable.sh';
	public const SCRIPT_VUE_DEV_TOOLS = 'scripts/vue_dev_tools.sh';
	public const SCRIPT_WATCHDOG = 'scripts/watchdog.sh';
	public const SCRIPT_WORKER = 'app/PhpUnitJobs/worker.sh';
	public const SCRIPT_WSL2_SYSTEMD = 'configs/wsl/wsl2-systemd.sh';
	public const SCRIPT_WSL_CONFIGS_COPY = 'scripts/wsl/wsl_configs_copy.sh';
	public const SCRIPT_WSL_START = 'scripts/wsl/wsl_start.sh';
	public const SCRIPT_XDEBUG_CLI = 'scripts/xdebug_cli.sh';
	public const SCRIPT_XDEBUG_INSTALL = 'scripts/xdebug_install.sh';
	public const SCRIPT_XDEBUG_WSL = 'scripts/xdebug_wsl.sh';
	public const SCRIPT_XHGUI = 'scripts/xhgui.sh';
	public const SCRIPT_XHGUI_TIDEWAYS_INSTALL = 'configs/xhgui/xhgui_tideways_install.sh';
	public const SCRIPT_ZSH = 'scripts/zsh.sh';
	public const SCRIPT_ZSH_SETUP = 'configs/home-dev/.oh-my-zsh/custom/zsh_setup.sh';
	/**
	 * @return string
	 */
	public static function getDefaultExtension(): string{ return FileExtension::SH; }
	/**
	 * @return array
	 */
	public static function getFolderPaths(): array{
		return [
			DynamicFolder::FOLDER_SCRIPTS,
			DynamicFolder::FOLDER_CONFIGS,
			DynamicFolder::FOLDER_DATABASE,
			DynamicFolder::FOLDER_APP_PHPUNIT_JOBS,
			//Folder::FOLDER_laradock,
			//Folder::FOLDER_tests,
		];
	}
	public static function addGlobalLinesToAll(){
		$all = static::get();
		foreach($all as $one){
			$one->addGlobalLines();
		}
	}
	public static function getProjectFolders(): array{
		return [
			DynamicFolder::FOLDER_CONFIGS,
			DynamicFolder::FOLDER_DATABASE,
			DynamicFolder::FOLDER_APP_PHPUNIT_JOBS,
			DynamicFolder::FOLDER_SCRIPTS,
			DynamicFolder::FOLDER_TESTS,
		];
	}
	public static function moveToScriptsFolder(){
		$all = static::all();
		$notInScripts = $all->filter(function($s){
			/** @var static $s */
			return $s->getRelativeFolder() === static::getDefaultFolderRelative();
		});
	}
	/**
	 * @return string
	 */
	public static function getDefaultFolderRelative(): string{ return DynamicFolder::FOLDER_SCRIPTS; }
	public static function generateConstantName(string $str): string{
		return "SCRIPT_" . self::pathToConstantName(FileHelper::toFileName($str));
	}
	/**
	 * @return mixed
	 */
	public function getMainContent(){
		return $this->mainContent;
	}
	/**
	 * @return BashLibScriptFile[]|Collection
	 */
	public function getLibraryScripts(): Collection{ return BashLibScriptFile::get(); }
	public function reformat(): void {
		$this->removeLinesContaining([
			'log_end ',
			'$(dirname "${BASH_SOURCE[0]}',
			'#!/bin/bash',
			'scripts/functions.sh',
			'#!/usr/bin/env bash',
			'log_end.sh',
			'# shellcheck disable',
			'/functions.sh',
		]);
		$c = $this->getContents();
		$replacements = [
			" /vagrant" => ' "${QM_API}"',
			' ${QM_API}/' => ' "${QM_API}"/',
		];
		foreach($replacements as $search => $replacement){
			$c = str_replace($search, $replacement, $c);
		}
	}
	/**
	 * @param mixed $mainContent
	 */
	public function setMainContent($mainContent): void{
		$this->mainContent = $mainContent;
	}
	/**
	 * @return bool
	 */
	private function isLibrary(): bool{
		if(QMStr::contains($this->getPath(), "/lib/")){
			return true;
		}
		$libraries = BashLibScriptFile::allPaths();
		return in_array($this->getPath(), $libraries);
	}
	/**
	 * @param string $name
	 * @return BashFunction|null
	 */
	protected function getFunction(string $name): ?BashFunction{
		return $this->getFunctions()->filter(function($item) use ($name){
			/** @var BashFunction $item */
			return $item->getNameAttribute() === $name;
		})->first();
	}
	/**
	 * @return BashFunction[]|Collection
	 */
	public function getFunctions(): Collection{
		/** @var FileLine[] $line */
		$lines = $this->getLinesContaining("()");
		$functions = [];
		foreach($lines as $i => $line){
			$name = $line->before("()");
			$name = str_replace("function ", "", $name);
			$nextFunction = $lines[$i];
			$body = $this->getLinesBetweenNumbers($line->getNumber(), $nextFunction->getNumber());
			$functions[] = new BashFunction($name, $this, $body);
		}
		return collect($functions);
	}
	/**
	 * @return \Illuminate\Support\Facades\View
	 */
	protected function getView(): \Illuminate\Support\Facades\View{
		// jetbrains://php-storm/navigate/reference?project=qm-api&path=resources/views/bash/bash_script.blade.php
		return view('bash/bash_script_simple', ['file' => $this]); // resources/views/bash/bash_script.blade.php
	}
	protected static function generateConstantValues(): array{
		$files = static::all();
		$paths = [];
		foreach($files as $file){
			/** @var self $file */
			$paths[] = $file->getRelativePath();
		}
		return $paths;
	}
}
