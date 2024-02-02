<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
use App\Files\Bash\BashScriptFile;
use App\Files\FileHelper;
use App\ShellCommands\DynamicCommand;
use App\Traits\ConstantGenerator;
class HomesteadRepo extends GitRepo {
	use ConstantGenerator;
	public const USERNAME = 'laravel';
	public static $REPO_NAME = 'homestead';
	public const DEFAULT_BRANCH = '18.04';
	public const SCRIPTS_CLEAR_NGINX = 'scripts/clear-nginx.sh';
	public const SCRIPTS_CLEAR_VARIABLES = 'scripts/clear-variables.sh';
	public const SCRIPTS_CONFIGURE_AVAHI = 'scripts/configure-avahi.sh';
	public const SCRIPTS_CREATE_CERTIFICATE = 'scripts/create-certificate.sh';
	public const SCRIPTS_CREATE_COUCH = 'scripts/create-couch.sh';
	public const SCRIPTS_CREATE_ECOSYSTEM = 'scripts/create-ecosystem.sh';
	public const SCRIPTS_CREATE_INFLUXDB = 'scripts/create-influxdb.sh';
	public const SCRIPTS_CREATE_MINIO_BUCKET = 'scripts/create-minio-bucket.sh';
	public const SCRIPTS_CREATE_MONGO = 'scripts/create-mongo.sh';
	public const SCRIPTS_CREATE_MYSQL = 'scripts/create-mysql.sh';
	public const SCRIPTS_CREATE_NGROK = 'scripts/create-ngrok.sh';
	public const SCRIPTS_CREATE_POSTGRES = 'scripts/create-postgres.sh';
	public const SCRIPTS_CRON_SCHEDULE = 'scripts/cron-schedule.sh';
	public const SCRIPTS_FEATURES_BLACKFIRE = 'scripts/features/blackfire.sh';
	public const SCRIPTS_FEATURES_CASSANDRA = 'scripts/features/cassandra.sh';
	public const SCRIPTS_FEATURES_CHRONOGRAF = 'scripts/features/chronograf.sh';
	public const SCRIPTS_FEATURES_COUCHDB = 'scripts/features/couchdb.sh';
	public const SCRIPTS_FEATURES_CRYSTAL = 'scripts/features/crystal.sh';
	public const SCRIPTS_FEATURES_DOCKER = 'scripts/features/docker.sh';
	public const SCRIPTS_FEATURES_ELASTICSEARCH = 'scripts/features/elasticsearch.sh';
	public const SCRIPTS_FEATURES_EVENTSTORE = 'scripts/features/eventstore.sh';
	public const SCRIPTS_FEATURES_FLYWAY = 'scripts/features/flyway.sh';
	public const SCRIPTS_FEATURES_GEARMAN = 'scripts/features/gearman.sh';
	public const SCRIPTS_FEATURES_GOLANG = 'scripts/features/golang.sh';
	public const SCRIPTS_FEATURES_GRAFANA = 'scripts/features/grafana.sh';
	public const SCRIPTS_FEATURES_HEROKU = 'scripts/features/heroku.sh';
	public const SCRIPTS_FEATURES_INFLUXDB = 'scripts/features/influxdb.sh';
	public const SCRIPTS_FEATURES_MARIADB = 'scripts/features/mariadb.sh';
	public const SCRIPTS_FEATURES_MEILISEARCH = 'scripts/features/meilisearch.sh';
	public const SCRIPTS_FEATURES_MINIO = 'scripts/features/minio.sh';
	public const SCRIPTS_FEATURES_MONGODB = 'scripts/features/mongodb.sh';
	public const SCRIPTS_FEATURES_NEO4J = 'scripts/features/neo4j.sh';
	public const SCRIPTS_FEATURES_OHMYZSH = 'scripts/features/ohmyzsh.sh';
	public const SCRIPTS_FEATURES_OPENRESTY = 'scripts/features/openresty.sh';
	public const SCRIPTS_FEATURES_PHP5_6 = 'scripts/features/php5.6.sh';
	public const SCRIPTS_FEATURES_PHP7_0 = 'scripts/features/php7.0.sh';
	public const SCRIPTS_FEATURES_PHP7_1 = 'scripts/features/php7.1.sh';
	public const SCRIPTS_FEATURES_PHP7_2 = 'scripts/features/php7.2.sh';
	public const SCRIPTS_FEATURES_PHP7_3 = 'scripts/features/php7.3.sh';
	public const SCRIPTS_FEATURES_PHP7_4 = 'scripts/features/php7.4.sh';
	public const SCRIPTS_FEATURES_PHP8_0 = 'scripts/features/php8.0.sh';
	public const SCRIPTS_FEATURES_PHP8_1 = 'scripts/features/php8.1.sh';
	public const SCRIPTS_FEATURES_PM2 = 'scripts/features/pm2.sh';
	public const SCRIPTS_FEATURES_PYTHON = 'scripts/features/python.sh';
	public const SCRIPTS_FEATURES_RABBITMQ = 'scripts/features/rabbitmq.sh';
	public const SCRIPTS_FEATURES_RVM = 'scripts/features/rvm.sh';
	public const SCRIPTS_FEATURES_R_BASE = 'scripts/features/r-base.sh';
	public const SCRIPTS_FEATURES_SOLR = 'scripts/features/solr.sh';
	public const SCRIPTS_FEATURES_TIMESCALEDB = 'scripts/features/timescaledb.sh';
	public const SCRIPTS_FEATURES_TRADER = 'scripts/features/trader.sh';
	public const SCRIPTS_FEATURES_WEBDRIVER = 'scripts/features/webdriver.sh';
	public const SCRIPTS_FLIP_WEBSERVER = 'scripts/flip-webserver.sh';
	public const SCRIPTS_HOMESTEAD = 'scripts/homestead.rb';
	public const SCRIPTS_HOSTS_ADD = 'scripts/hosts-add.sh';
	public const SCRIPTS_HOSTS_RESET = 'scripts/hosts-reset.sh';
	public const SCRIPTS_INSTALL_LOAD_BALANCER = 'scripts/install-load-balancer.sh';
	public const SCRIPTS_INSTALL_XHGUI = 'scripts/install-xhgui.sh';
	public const SCRIPTS_RESTART_WEBSERVER = 'scripts/restart-webserver.sh';
	public const SCRIPTS_SITE_TYPES_APACHE = 'scripts/site-types/apache.sh';
	public const SCRIPTS_SITE_TYPES_CAKEPHP3 = 'scripts/site-types/cakephp3.sh';
	public const SCRIPTS_SITE_TYPES_CRYSTAL = 'scripts/site-types/crystal.sh';
	public const SCRIPTS_SITE_TYPES_ELGG = 'scripts/site-types/elgg.sh';
	public const SCRIPTS_SITE_TYPES_FRONTCONTROLLER = 'scripts/site-types/frontcontroller.sh';
	public const SCRIPTS_SITE_TYPES_LARAVEL = 'scripts/site-types/laravel.sh';
	public const SCRIPTS_SITE_TYPES_MAGENTO = 'scripts/site-types/magento.sh';
	public const SCRIPTS_SITE_TYPES_MODX = 'scripts/site-types/modx.sh';
	public const SCRIPTS_SITE_TYPES_PHALCON = 'scripts/site-types/phalcon.sh';
	public const SCRIPTS_SITE_TYPES_PIMCORE = 'scripts/site-types/pimcore.sh';
	public const SCRIPTS_SITE_TYPES_PROXY = 'scripts/site-types/proxy.sh';
	public const SCRIPTS_SITE_TYPES_SILVERSTRIPE = 'scripts/site-types/silverstripe.sh';
	public const SCRIPTS_SITE_TYPES_SPA = 'scripts/site-types/spa.sh';
	public const SCRIPTS_SITE_TYPES_STATAMIC = 'scripts/site-types/statamic.sh';
	public const SCRIPTS_SITE_TYPES_SYMFONY2 = 'scripts/site-types/symfony2.sh';
	public const SCRIPTS_SITE_TYPES_SYMFONY4 = 'scripts/site-types/symfony4.sh';
	public const SCRIPTS_SITE_TYPES_THINKPHP = 'scripts/site-types/thinkphp.sh';
	public const SCRIPTS_SITE_TYPES_UMI = 'scripts/site-types/umi.sh';
	public const SCRIPTS_SITE_TYPES_WORDPRESS = 'scripts/site-types/wordpress.sh';
	public const SCRIPTS_SITE_TYPES_YII = 'scripts/site-types/yii.sh';
	public const SCRIPTS_SITE_TYPES_ZF = 'scripts/site-types/zf.sh';
	public static function generateConstantName(string $str): string{
		return self::pathToConstantName($str);
	}
	public static function copyScripts(){
		$homesteadPaths = self::provisionScripts();
		foreach($homesteadPaths as $homesteadPath){
			$contents = FileHelper::getContents(self::getAbsolutePath($homesteadPath));
			$contents = str_replace("secret", "\$PW", $contents);
			$qmPath = "scripts/homestead/" . str_replace("scripts/", "", $homesteadPath);
			FileHelper::write($qmPath, $contents);
		}
	}
	public static function generateProvisionScript(){
		$script = new BashScriptFile(BashScriptFile::SCRIPTS_PROVISION_SH);
		$scripts = self::provisionScripts();
		$contents = "cd \$QM_API\n";
		$contents .= "bash " . BashScriptFile::SCRIPT_PROVISION;
		foreach($scripts as $relativeToRepo){
			$qmPath = "scripts/homestead/" . str_replace("scripts/", "", $relativeToRepo);
			$contents .= "\nsource $qmPath";
		}
		$script->setMainContent($contents);
		$script->renderAndSave();
	}
	protected static function generateConstantValues(): array{
		$abs = self::listFilesInFolder('scripts');
		$rel = [];
		foreach($abs as $path){
			$rel[] = static::relativeToRepo($path);
		}
		return $rel;
	}
	public static function runScript(string $script): DynamicCommand{
		return parent::execute("sudo bash $script", false);
	}
	public static function xhgui(){
		static::runScript(self::SCRIPTS_INSTALL_XHGUI);
	}
	public static function provisionScripts(): array{
		return [
			//self::SCRIPTS_FEATURES_BLACKFIRE,
			//self::SCRIPTS_FEATURES_CASSANDRA,
			//self::SCRIPTS_FEATURES_CHRONOGRAF,
			//self::SCRIPTS_FEATURES_COUCHDB,
			//self::SCRIPTS_FEATURES_CRYSTAL,
			self::SCRIPTS_FEATURES_DOCKER,
			self::SCRIPTS_FEATURES_ELASTICSEARCH,
			//self::SCRIPTS_FEATURES_EVENTSTORE,
			//self::SCRIPTS_FEATURES_FLYWAY,
			//self::SCRIPTS_FEATURES_GEARMAN,
			self::SCRIPTS_FEATURES_GOLANG,
			self::SCRIPTS_FEATURES_GRAFANA,
			self::SCRIPTS_FEATURES_HEROKU,
			//self::SCRIPTS_FEATURES_INFLUXDB,
			//self::SCRIPTS_FEATURES_MARIADB,
			self::SCRIPTS_FEATURES_MEILISEARCH,
			self::SCRIPTS_FEATURES_MINIO,
			self::SCRIPTS_FEATURES_MONGODB,
			//self::SCRIPTS_FEATURES_NEO4J,
			self::SCRIPTS_FEATURES_OHMYZSH,
			//self::SCRIPTS_FEATURES_OPENRESTY,
			self::SCRIPTS_FEATURES_PHP5_6,
			self::SCRIPTS_FEATURES_PHP7_0,
			self::SCRIPTS_FEATURES_PHP7_1,
			self::SCRIPTS_FEATURES_PHP7_2,
			self::SCRIPTS_FEATURES_PHP7_3,
			self::SCRIPTS_FEATURES_PHP7_4,
			self::SCRIPTS_FEATURES_PHP8_0,
			self::SCRIPTS_FEATURES_PHP8_1,
			self::SCRIPTS_FEATURES_PM2,
			self::SCRIPTS_FEATURES_PYTHON,
			//self::SCRIPTS_FEATURES_R_BASE,
			//self::SCRIPTS_FEATURES_RABBITMQ,
			self::SCRIPTS_FEATURES_RVM,
			self::SCRIPTS_FEATURES_SOLR,
			//self::SCRIPTS_FEATURES_TIMESCALEDB,
			self::SCRIPTS_FEATURES_TRADER,
			self::SCRIPTS_FEATURES_WEBDRIVER,
			//self::SCRIPTS_FLIP_WEBSERVER,
			//self::SCRIPTS_HOMESTEAD,
			//self::SCRIPTS_HOSTS_ADD,
			//self::SCRIPTS_HOSTS_RESET,
			//self::SCRIPTS_INSTALL_LOAD_BALANCER,
			self::SCRIPTS_INSTALL_XHGUI,
			self::SCRIPTS_RESTART_WEBSERVER,
			//self::SCRIPTS_CLEAR_NGINX,
			//			self::SCRIPTS_CLEAR_VARIABLES,
			//			self::SCRIPTS_CONFIGURE_AVAHI,
			//			self::SCRIPTS_CREATE_CERTIFICATE,
			//			self::SCRIPTS_CREATE_COUCH,
			//			self::SCRIPTS_CREATE_ECOSYSTEM,
			//			self::SCRIPTS_CREATE_INFLUXDB,
			self::SCRIPTS_CREATE_MINIO_BUCKET,
			self::SCRIPTS_CREATE_MONGO,
			self::SCRIPTS_CREATE_MYSQL,
			//			self::SCRIPTS_CREATE_NGROK,
			//			self::SCRIPTS_CREATE_POSTGRES,
			self::SCRIPTS_CRON_SCHEDULE,
			//			self::SCRIPTS_SITE_TYPES_APACHE,
			//			self::SCRIPTS_SITE_TYPES_CAKEPHP3,
			//			self::SCRIPTS_SITE_TYPES_CRYSTAL,
			//			self::SCRIPTS_SITE_TYPES_ELGG,
			//			self::SCRIPTS_SITE_TYPES_FRONTCONTROLLER,
			//			self::SCRIPTS_SITE_TYPES_LARAVEL,
			//			self::SCRIPTS_SITE_TYPES_MAGENTO,
			//			self::SCRIPTS_SITE_TYPES_MODX,
			//			self::SCRIPTS_SITE_TYPES_PHALCON,
			//			self::SCRIPTS_SITE_TYPES_PIMCORE,
			//			self::SCRIPTS_SITE_TYPES_PROXY,
			//			self::SCRIPTS_SITE_TYPES_SILVERSTRIPE,
			//			self::SCRIPTS_SITE_TYPES_SPA,
			//			self::SCRIPTS_SITE_TYPES_STATAMIC,
			//			self::SCRIPTS_SITE_TYPES_SYMFONY2,
			//			self::SCRIPTS_SITE_TYPES_SYMFONY4,
			//			self::SCRIPTS_SITE_TYPES_THINKPHP,
			//			self::SCRIPTS_SITE_TYPES_UMI,
			//			self::SCRIPTS_SITE_TYPES_WORDPRESS,
			//			self::SCRIPTS_SITE_TYPES_YII,
			//			self::SCRIPTS_SITE_TYPES_ZF
		];
	}
}
