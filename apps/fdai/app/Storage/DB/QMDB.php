<?php /** @noinspection SyntaxError */
/** @noinspection SyntaxError */
/** @noinspection SyntaxError */
/** @noinspection SyntaxError */ /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SqlResolve */
/** @noinspection PhpDeprecationInspection */
/** @noinspection UnknownColumnInspection */
/** @noinspection PhpUnused */
namespace App\Storage\DB;
use App\CodeGenerators\Swagger\SwaggerJson;
use App\Computers\ThisComputer;
use App\DataSources\QMConnector;
use App\DataSources\QMSpreadsheetImporter;
use App\Exceptions\BadRequestException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\InvalidDatabaseCredentialsException;
use App\Exceptions\QMFileNotFoundException;
use App\Exceptions\SlowQueryException;
use App\Files\Config\MyCnfFile;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Files\Json\JsonFile;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Logging\QMLogLevel;
use App\Models\GlobalVariableRelationship;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\OAAccessToken;
use App\Models\OAClient;
use App\Models\User;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Models\WpPost;
use App\Slim\Model\QMUnit;
use App\Slim\Model\QMUnitCategory;
use App\Storage\DemoDatabaseSynchronizer;
use App\Storage\Memory;
use App\Storage\QMQueryExecuted;
use App\Storage\QueryBuilderHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\Utils\Env;
use App\Variables\QMCommonVariable;
use App\Variables\QMVariableCategory;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Exception;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\MySqlBuilder;
use Illuminate\Database\Schema\PostgresBuilder;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use InfyOm\Generator\Utils\FileUtil;
use Jasny\DB\MySQL\Query;
use Jupitern\Table\Table;
use LogicException;
use PDO;
use SqlFormatter;
use Str;
use Throwable;

abstract class QMDB extends Connection {
	public const CONNECTION_NAME  = null;
	const        DB_DRIVER        = null;
	public const DB_HOST_PRIVATE  = null;
	public const DB_HOST_PUBLIC   = null;
	public const DB_NAME          = null;
	public const DB_PASSWORD      = null;
	public const DB_PORT          = null;
	public const DB_USER          = null;
	public const DB_SCHEMA       = null;
	const DEV_MYSQL_CONF          = 'configs/home-web/vagrant/.my.cnf';
	public const FIELD_CLIENT_ID  = "client_id";
	public const FIELD_CREATED_AT = "created_at";
	public const FIELD_DELETED_AT = "deleted_at";
	public const FIELD_UPDATED_AT = "updated_at";
	public const DRIVER_PGSQL           = 'pgsql';
	public const DRIVER_MYSQL           = 'mysql';
	public const DRIVER_SQLITE           = 'sqlite';
	public const LARGE_TABLES = [
		GlobalVariableRelationship::TABLE,
		UserVariableRelationship::TABLE,
		Measurement::TABLE,
		UserVariable::TABLE,
		Variable::TABLE,
		WpPost::TABLE,
		'telescope_entries',
		'studies',
		'sent_emails',
		'tracking_reminder_notifications',
		'wp_postmeta',
		'connector_requests',
		'variable_user_sources',
		'oa_access_tokens',
		'common_tags',
		'third_party_correlations',
		'wp_term_relationships',
		'oa_refresh_tokens',
	];
	public const GLOBAL_TABLES = [
		0 => 'child_parents',
		1 => 'common_tags',
		2 => 'connectors',
		3 => 'ct_causes',
		4 => 'intuitive_condition_cause_votes',
		5 => 'ct_condition_symptom',
		6 => 'ct_condition_treatment',
		7 => 'ct_conditions',
		8 => 'ct_side_effects',
		9 => 'ct_symptoms',
		10 => 'ct_treatment_side_effect',
		11 => 'ct_treatments',
		24 => 'roles',
		28 => 'source_platforms',
		29 => 'spreadsheet_importers',
		31 => 'unit_categories',
		32 => 'units',
		36 => 'variable_categories',
		37 => 'variable_outcome_category',
		38 => 'variable_predictor_category',
		39 => 'variables',
	];
	public const LENGTH_LIMIT_BLOB = MySqlPlatform::LENGTH_LIMIT_BLOB;
	public const LENGTH_LIMIT_LONGTEXT = 2147483647;
	public const LENGTH_LIMIT_MEDIUMBLOB = MySqlPlatform::LENGTH_LIMIT_MEDIUMBLOB;
	public const LENGTH_LIMIT_MEDIUMTEXT = MySqlPlatform::LENGTH_LIMIT_MEDIUMTEXT;
	public const LENGTH_LIMIT_TEXT = MySqlPlatform::LENGTH_LIMIT_TEXT;
	public const LENGTH_LIMIT_TINYBLOB = MySqlPlatform::LENGTH_LIMIT_TINYBLOB;
	public const LENGTH_LIMIT_TINYTEXT = MySqlPlatform::LENGTH_LIMIT_MEDIUMTEXT;
	public const MAX_TEXT_FIELD_LENGTH = self::LENGTH_LIMIT_TEXT;

	public const TABLES_BY_SIZE_IN_MB = [
		'user_variable_relationships' => 13470.14,
		'measurements' => 4676.41,
		'user_variables' => 3066.61,
		'global_variable_relationships' => 898.11,
		'variables' => 884.69,
		'telescope_entries' => 745.86,
		'studies' => 717.83,
		'sent_emails' => 184.59,
		'tracking_reminder_notifications' => 136.78,
		'wp_postmeta' => 106.44,
		'connector_requests' => 65.23,
		'variable_user_sources' => 42.64,
		'oa_access_tokens' => 28.66,
		'common_tags' => 23.09,
		'third_party_correlations' => 19.61,
		'wp_term_relationships' => 15.42,
		'oa_refresh_tokens' => 12.58,
		'wp_usermeta' => 11.55,
		'tracking_reminders' => 10,
		'connector_imports' => 9.45,
		'ct_correlations' => 9.06,
		'meddra_all_indications' => 6.03,
		'oa_authorization_codes' => 5,
		'failed_jobs' => 4.52,
		'applications' => 4.48,
		'credentials' => 4.05,
		'wp_as3cf_items' => 2.47,
		'connections' => 2.41,
		'wp_options' => 1.53,
		'votes' => 0.72,
		'ct_condition_treatment' => 0.55,
		'ct_condition_symptom' => 0.42,
		'ct_treatments' => 0.41,
		'device_tokens' => 0.39,
		'ct_symptoms' => 0.38,
		'ct_causes' => 0.31,
		'telescope_entries_tags' => 0.3,
		'intuitive_condition_cause_votes' => 0.25,
		'ct_treatment_sideeffect' => 0.2,
		'tracker_sessions' => 0.19,
		'tracker_log' => 0.17,
		'oa_clients' => 0.17,
		'o_system_event_logs' => 0.17,
		'ct_sideeffects' => 0.13,
		'wp_comments' => 0.13,
		'wp_actionscheduler_actions' => 0.13,
		'o_system_plugin_history' => 0.13,
		'correlation_causality_votes' => 0.11,
		'wp_wc_product_meta_lookup' => 0.11,
		'wp_sitemeta' => 0.11,
		'correlation_usefulness_votes' => 0.11,
		'phrases' => 0.11,
		'o_backend_users' => 0.09,
		'o_deferred_bindings' => 0.09,
		'collaborators' => 0.09,
		'wp_woocommerce_tax_rates' => 0.08,
		'connectors' => 0.08,
		'permission_user' => 0.08,
		'o_users' => 0.08,
		'permission_role' => 0.08,
		'wp_woocommerce_downloadable_product_permissions' => 0.08,
		'wp_signups' => 0.08,
		'wp_simply_static_pages' => 0.08,
		'measurement_exports' => 0.08,
		'role_user' => 0.08,
		'user_variable_clients' => 0.08,
		'wp_wc_order_product_lookup' => 0.08,
		'user_tags' => 0.08,
		'units' => 0.06,
		'o_system_revisions' => 0.06,
		'user_follower' => 0.06,
		'o_system_files' => 0.06,
		'action_events' => 0.06,
		'o_cms_theme_logs' => 0.06,
		'health_checks' => 0.06,
		'notifications' => 0.06,
		'wp_links' => 0.06,
		'wp_da_r_votes' => 0.06,
		'wp_wc_order_stats' => 0.06,
		'astral_menu_menus' => 0.06,
		'o_rainlab_user_mail_blockers' => 0.06,
		'wp_woocommerce_sessions' => 0.06,
		'connector_devices' => 0.06,
		'wp_woocommerce_order_itemmeta' => 0.05,
		'wp_woocommerce_payment_tokenmeta' => 0.05,
		'wp_wc_order_tax_lookup' => 0.05,
		'wp_woocommerce_shipping_zone_locations' => 0.05,
		'wp_woocommerce_tax_rate_locations' => 0.05,
		'user_clients' => 0.05,
		'wp_wc_order_coupon_lookup' => 0.05,
		'wp_wc_download_log' => 0.05,
		'wp_wc_customer_lookup' => 0.05,
		'wp_termmeta' => 0.05,
		'variable_categories' => 0.05,
		'wp_term_taxonomy' => 0.05,
		'wp_terms' => 0.05,
		'purchases' => 0.05,
		'o_rainlab_blog_posts' => 0.05,
		'o_rainlab_notify_rule_conditions' => 0.05,
		'o_flynsarmy_sociallogin_user_providers' => 0.05,
		'o_cms_theme_templates' => 0.05,
		'favorites' => 0.05,
		'o_user_throttle' => 0.05,
		'o_renatio_dynamicpdf_pdf_templates' => 0.05,
		'migrations' => 0.05,
		'likes' => 0.05,
		'o_backend_user_throttle' => 0.05,
		'cards' => 0.05,
		'o_backend_user_roles' => 0.05,
		'password_resets' => 0.05,
		'measurement_imports' => 0.05,
		'o_backend_user_groups' => 0.05,
		'buttons' => 0.05,
		'button_clicks' => 0.05,
		'o_rainlab_blog_categories' => 0.05,
		'spreadsheet_importers' => 0.05,
		'followers' => 0.05,
		'wp_woocommerce_attribute_taxonomies' => 0.03,
		'wp_wc_admin_note_actions' => 0.03,
		'subscriptions' => 0.03,
		'wp_wc_webhooks' => 0.03,
		'wp_wc_tax_rate_classes' => 0.03,
		'o_jobs' => 0.03,
		'wp_registration_log' => 0.03,
		'o_cms_theme_data' => 0.03,
		'wp_sirv_images' => 0.03,
		'wp_site' => 0.03,
		'astral_menu_menu_items' => 0.03,
		'o_backend_user_preferences' => 0.03,
		'o_backend_access_log' => 0.03,
		'media' => 0.03,
		'source_platforms' => 0.03,
		'roles' => 0.03,
		'permissions' => 0.03,
		'oauth_refresh_tokens' => 0.03,
		'oauth_personal_access_clients' => 0.03,
		'oauth_clients' => 0.03,
		'oauth_access_tokens' => 0.03,
		'o_user_groups' => 0.03,
		'o_system_settings' => 0.03,
		'crypto_trades' => 0.03,
		'wp_woocommerce_payment_tokens' => 0.03,
		'wp_blog_versions' => 0.03,
		'ct_conditions' => 0.03,
		'wp_woocommerce_order_items' => 0.03,
		'o_renatio_dynamicpdf_pdf_layouts' => 0.03,
		'wp_woocommerce_log' => 0.03,
		'o_ahmadfatoni_apigenerator_data' => 0.02,
		'wp_woocommerce_shipping_zones' => 0.02,
		'astral_notifications' => 0.02,
		'wp_woocommerce_shipping_zone_methods' => 0.02,
		'mailbox_inbound_emails' => 0.02,
		'telescope_monitoring' => 0.02,
		'o_kurtjensen_passage_keys' => 0.02,
		'wp_wc_category_lookup' => 0.02,
		'o_suresoftware_maillog_log' => 0.02,
		'unit_categories' => 0.02,
		'unit_conversions' => 0.02,
		'oauth_auth_codes' => 0.02,
		'o_users_groups' => 0.02,
		'wp_arete_wp_smiley_settings' => 0.02,
		'wp_arete_wp_smileys' => 0.02,
		'wp_arete_wp_smileys_manage' => 0.02,
		'o_system_request_logs' => 0.02,
		'o_system_mail_partials' => 0.02,
		'o_system_mail_layouts' => 0.02,
		'o_rainlab_blog_posts_categories' => 0.02,
		'wp_wc_admin_notes' => 0.02,
		'o_migrations' => 0.02,
		'wp_da_r_reactions' => 0.02,
		'wp_effecto' => 0.02,
		'o_kurtjensen_passage_groups_keys' => 0.02,
		'wp_mailchimp_carts' => 0.02,
		'wp_mailchimp_jobs' => 0.02,
		'o_failed_jobs' => 0.02,
		'o_cache' => 0.02,
		'o_backend_users_groups' => 0.02,
		'wp_wpreactions_reacted_users' => 0.02,
		'wp_sirv_shortcodes' => 0,
	];
	public const TABLES_TO_EXCLUDE_FROM_MODEL_GENERATION = [
		'o_*',
		'deleted_*',
		'wp_wc*',
		'wp_a*',
		'wp_woo*',
		'wp_wp*',
		'wp_action*',
		'oauth_*',
		'activations',                 // 6 records
		//'global_variable_relationships', // 37663 records
		'api_keys',                    // 17 records
		//'applications', // 538 records
		'billing_plans',               // 6 records
		//'oa_access_tokens', // 89784 records
		//'oa_authorization_codes', // 6217 records
		//'oa_clients', // 537 records
		'oa_jwt',                      // 0 records
		//'oa_refresh_tokens', // 38838 records
		'oa_scopes',                   // 0 records
		'oa_users',                    // 0 records
		//'button_clicks', // 0 records
		//'buttons', // 0 records
		'cards',                       // Conflicts with Laravel billable
		//'collaborators', // 451 records
		//'common_tags', // 111279 records
		//'connections', // 3318 records
		//'connectors', // 60 records
		//'user_variable_relationships', // 541221 records
		'credentials',                 // 4681 records
		'credentials_backup',          // 4529 records
		'crypto_trades',               // 4 records
		'ct_causes',                   // 2458 records
		'intuitive_condition_cause_votes',          // 3653 records
		'ct_condition_symptom',        // 4186 records
		'ct_condition_treatment',      // 5878 records
		'ct_conditions',               // 162 records
		'ct_correlations',             // 38893 records
		'ct_sideeffects',              // 851 records
		'ct_symptoms',                 // 3064 records
		'ct_treatment_sideeffect',     // 2505 records
		'ct_treatments',               // 3908 records
		//'device_tokens', // 716 records
		'doctrine_migration_versions', // 317 records
		'lb_blocks',
		'lb_contents',
		'maileclipse_templates',  // 1 records
		//'measurement_exports', // 315 records
		//'measurement_imports', // 10 records
		//'measurements', // 14188431 records
		'meddra_all_indications', // 30835 records
		'migrations',             // 102 records
		'o_cache',
		'o_deferred_bindings',
		'o_migrations',
		'o_renatio_dynamicpdf_pdf_layouts',
		'o_renatio_dynamicpdf_pdf_templates',
		//'o_sessions',
		'o_system_plugin_versions',
		'o_system_plugin_history',
		'organization_admins', // 3 records
		'organizations',       // 3 records
		'password_resets',     // 55 records
		'persistences',        // 7 record
		'permissions',
		'permission_role',
		'permission_user',
		//'phrases', // 185 records
		//'purchases', // 179 records
		'role_users',          // 3 records
		'roles',               // 2 records
		//'sent_emails', // 1853 records
		'shares',              // 9 records
		//'source_platforms', // 4 records
		'sources',             // 144 records
		//'studies', // 2998 records
		'subscriptions',       // 8 records - subscriptions is handled by laravel Billable and causes conflicts
		//'third_party_correlations', // 61798 records
		'telescope_entries',
		'telescope_entries_tags',
		'telescope_monitoring',
		'throttle',            // 4 records
		//'tracker_log', // 0 records
		//'tracker_sessions', // 0 records
		//'tracking_reminder_notifications', // 489973 records
		//'tracking_reminders', // 23366 records
		//'unit_categories', // 12 records
		//'unit_conversions', // 21 records
		//'units', // 62 records
		'updates',             // 635198 records
		//'user_tags', // 89 records
		//'user_variables', // 214993 records
		//'variable_categories', // 24 records
		//'variable_user_sources', // 177296 records
		//'variables', // 128621 records
		//'votes', // 4730 records
		//'wp_links', // 0 records
		'wp_options',          // 244 records
		//'wp_postmeta', // 34 records
		//'wp_posts', // 358 records
		//'wp_term_relationships', // 18 records
		//'wp_term_taxonomy', // 23 records
		//'wp_termmeta', // 0 records
		//'wp_terms', // 23 records
		//'wp_usermeta', // 48321 records
		//'wp_users', // 9148 records
		'wp_users_backup',     // 9041 records
	];
	public const TABLE_PREFIXES_TO_STRIP = [
		"wp_",
		"oa_",
	]; // ~4GB
	public const TYPE_INT = 'int';
	public const TYPE_MEDIUMTEXT = 'mediumtext';
	public const TYPE_TEXT = 'text';
	public const TYPE_TIMESTAMP = 'timestamp';
	public const TYPE_TIMESTAMP_ON_UPDATE_CURRENT_TIMESTAMP = 'timestamp on update CURRENT_TIMESTAMP';
	public const VIEWS = [
		'global_variable_relationships_aggregated_by_cause_variable_id',
		'global_variable_relationships_aggregated_by_effect_variable_id',
		'average_votes',
		'common_tags_aggregated_by_tag_variable_id',
		'common_tags_aggregated_by_tagged_variable_id',
		'connections_aggregated_by_user_id',
		'correlations_aggregated_by_cause_user_variable_id',
		'correlations_aggregated_by_effect_user_variable_id',
		'correlations_aggregated_by_user_id',
		'demo_measurements',
		'measurements_aggregated_by_user_variable_id',
		'measurements_aggregated_by_variable_id',
		'missing_measurements',
		'mood_measurements_1',
		'number_of_measurements',
		'studies_aggregated_by_user_id',
		'tag_counts',
		'tags_4_humans',
		'tracking_reminders_aggregated_by_user_id',
		'tracking_reminders_aggregated_by_user_variable_id',
		'tracking_reminders_aggregated_by_variable_id',
		'user_variable_relationships_aggregated',
		'user_variables_aggregated_by_user_id',
		'user_variables_aggregated_by_variable_id',
		'variables_with_missing_measurements',
		'votes_aggregated_by_user_id',
	];
	/**
	 * @var array
	 */
	public static array $mappings = [
		'string' => ['varchar', 'text', 'string', 'char', 'enum', 'tinytext', 'mediumtext', 'longtext'],
		'date' => ['datetime', 'year', 'date', 'time', 'timestamp'],
		'int' => ['bigint', 'int', 'integer', 'tinyint', 'smallint', 'mediumint'],
		'float' => ['float', 'decimal', 'numeric', 'dec', 'fixed', 'double', 'real', 'double precision'],
		'boolean' => ['longblob', 'blob', 'bit'],
	];
	protected static array $tables = [];
	protected static array $tableSizes = [];
	protected static array $columnNames = [];
	protected static array $columns = [];
	protected static array $interestingTables = [];
	protected static array $schemaColumns = [];
	protected static array $baseModelTables = [];
	protected static array $tablesWithColumn = [];
	private static string $myCnfPath;
	private static array $tableNames;
	protected static bool $hasReplicationRolePermission = false;
	protected static bool $hasTableTriggerPermission = false;
	protected static $dbTables;
	protected Connection $connection;
	/** @noinspection PhpMissingParentConstructorInspection */
	/**
	 * QMDBConnection constructor.
	 * @param Connection $connection
	 */
	public function __construct(Connection $connection){
		$this->connection = $connection;
		foreach($connection as $key => $value){
			$this->$key = $value;
		}
	}
	public static function find(string $connectionName){
		if($connectionName === DemoSQLiteDB::CONNECTION_NAME){
			return DemoSQLiteDB::db();
		}
		le("please add connection finder for $connectionName");
	}
	/**
	 * @return string
	 */
	public static function getPropertyModelJsonPath(): string{
		return base_path('data/property_models.json');
	}
	/**
	 * @return void
	 */
	public static function migrateForeignKeys(): void{
		$connection = static::getConnectionName();
		static::info("=== Migrating $connection FOREIGN KEYS ===");
		Artisan::call('migrate', [
			'--database' => $connection,
			'--force' => true,
			'--path' => 'database/migrations/fk',
		]);
	}
	/**
	 * @return void
	 */
	public static function migrateTables(): void{
		$connection = static::getConnectionName();
		static::info("=== Migrating $connection TABLES ===");
		Artisan::call('migrate', [
			'--database' => $connection,
			'--force' => true,
			'--path' => 'database/migrations/tables',
		]);
	}
	abstract public static function getDefaultDBName():string;
	/**
	 * @return string
	 */
	public static function getSSLClientKey(): string{
		return FileHelper::absPath('configs/docker/mysql-5.7/client/client-key.pem');
	}
	/**
	 * @return string
	 */
	public static function getSSLClientCert(): string{
		return FileHelper::absPath('configs/docker/mysql-5.7/client/client-cert.pem');
	}
	public static function fixTableDatesForAllTables(){
		$tableNames = static::getTableNames();
		foreach($tableNames as $tableName){
			static::fixDatesForTable($tableName);
		}
	}
	public static function schemaBuilder():PostgresBuilder|MySqlBuilder|SQLiteBuilder{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return static::db()->getSchemaBuilder();
	}
	/**
	 * @return string[]
	 */
	public static function getTableNames(): array{
		$connectionName = static::getConnectionName();
		if(isset(static::$tableNames[$connectionName])){
			return static::$tableNames[$connectionName];
		}
		$tableNames = [];
		if(static::isSQLite()){
			return static::$tableNames[$connectionName] = static::getTableNamesFromSQLite();
		}
		$builder = static::schemaBuilder();
		$tables = $builder->getAllTables();
		//ConsoleLog::debug("show full tables where Table_Type = 'BASE TABLE': " . json_encode($tables));
		foreach($tables as $table){
			foreach($table as $propertyName => $value){
				ConsoleLog::debug("$propertyName property is $value");
				if($propertyName === "tablename" || stripos($propertyName, "Tables_in") !== false){
					if(str_contains($value, ' ')){
						ConsoleLog::debug("$value has a space so it's probably a view, not a table");
						continue;
					}
					$tableNames[] = str_replace('public.', '', $value);
				}
			}
		}
		$tableNames = array_values(array_unique($tableNames));
		return static::$tableNames[$connectionName] = $tableNames;
	}
	abstract public static function getConnectionName(): string;
	/**
	 * @return QMDB
	 */
	public static function db(): QMDB{
        $CONNECTION_NAME = static::getConnectionName();
        $db = Memory::get($CONNECTION_NAME, Memory::DB_CONNECTIONS);
		// Use memory, so it's flushed between tests instead of static::$existingConnections
		if($db){
			return $db;
		} // It's slow to keep initializing connections
		$db = new static(static::getConnectionStatically());
		Memory::set($CONNECTION_NAME, $db, Memory::DB_CONNECTIONS);
		return $db;
	}
	/**
	 * @param string $tableName
	 * @return bool
	 */
	public static function fixDatesForTable(string $tableName): bool{
		$result = true;
		$columnNames = static::getAllColumnsForTable($tableName);
		foreach($columnNames as $columnName){
			if(str_contains($columnName, 'ed_at') ||  // Don't use just "_at"
			   str_contains($columnName, 'last_successful_update_time')){
				try {
					$qb = static::getBuilderByTable($tableName)->where($columnName, "0000-00-00 00:00:00");
					$messedUp = $qb->count();
					if($messedUp){
						static::info("Fixing $messedUp 0000-00-00 00:00:00 $columnName records in $tableName");
						$result = $qb->update([
							                      $columnName => date('Y-m-d H:i:s', 1),
							                      'updated_at' => date('Y-m-d H:i:s'),
						                      ]);
						$afterFixing = $qb->count();
						static::info("$afterFixing $columnName records still messed up in $tableName");
					}
				} catch (Exception $e) {
					$result = false;
					ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
				}
			}
		}
		return $result;
	}
	/**
	 * @param string $tableName
	 * @param bool   $camelCase
	 * @param bool   $useCache
	 * @return array
	 */
	public static function getAllColumnsForTable(string $tableName, bool $camelCase = false,
	                                             bool $useCache = true): array{
		if(!$useCache || !isset(static::$columnNames[$tableName])){
			$connection = static::db();
			$schemaBuilder = $connection->getSchemaBuilder();
			static::$columnNames[$tableName] = $schemaBuilder->getColumnListing($tableName);
		}
		if($camelCase){
			$camelCaseNames = [];
			foreach(static::$columnNames[$tableName] as $columnName){
				$camelCaseNames[] = QMStr::toCamelCase($columnName);
			}
			return $camelCaseNames;
		}
		return static::$columnNames[$tableName];
	}
	/**
	 * Get a schema builder instance for the connection.
	 * @return \Illuminate\Database\Schema\Builder
	 */
	public function getSchemaBuilder(): \Illuminate\Database\Schema\Builder{
		return static::getConnectionStatically()->getSchemaBuilder();
	}
	/**
	 * @return Connection
	 */
	public function getConnection(): Connection{
		return DB::connection(static::getConnectionName());
	}
	/**
	 * @param string $table
	 * @return QMQB
	 */
	public static function getBuilderByTable(string $table): QMQB{
		$db = static::db();
		$table = static::replaceTableAlias($table);
		if($db->getTablePrefix() === 'wp_' && str_contains($table, 'wp_')){
			$table = str_replace('wp_', '', $table);
		}
		return $db->table($table);
	}
	public static function replaceTableAlias(string $table): string{
		if($table === "users"){
			return "wp_users";
		}
		return $table;
	}
	/**
	 * Begin a fluent query against a database table.
	 * @param string $table
	 * @param null $as
	 * @return QMQB
	 */
	public function table($table, $as = null): QMQB{
		return $this->query()->from($table);
	}
	abstract protected static function getDBDriverName():string;
	public static function getDBIdentifier(): string {
		$host = static::getConnectionName();
		$name = static::getDBName();
		return "$host (DB $name)";
	}
	/**
	 * @return array
	 */
	public static function getTableNamesFromSQLite(): array{
		$tables = static::statementStatic("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
		$tables = $tables[0]["result"];
		return collect($tables)->map(function($table){
			return $table->name;
		})->toArray();
	}
	/**
	 * @param string $message
	 * @return string
	 */
	public static function getConnectionLogMessage(): string{
		$connection = static::db()->getConnection();
		$database = $connection->getConfig('database');
		$host = $connection->getConfig('host');
		$port = $connection->getConfig('port');
		$msg = "The default writable DB is $database";
		if($host){$msg .= " on $host";}
		if($port){$msg .= ":$port";}
		return $msg;
	}
	public function commit(){
		$this->getConnection()->commit();
	}
	/**
	 * Get a new query builder instance.
	 * @return QMQB
	 */
	public function query(): QMQB{
		return new QMQB($this->getConnection(), $this->getConnection()->getQueryGrammar(),
		                $this->getConnection()->getPostProcessor());
	}
	/**
	 * @param string $message
	 */
	public static function info(string $message){
		QMLog::infoWithoutContext($message);
	}
	/**
	 * @return array
	 */
	public static function getAllColumnsInDbIndexedByTable(): array{
		$allTables = static::getTableNames();
		$columnsIndexedByTable = [];
		foreach($allTables as $tableName){
			$columnsIndexedByTable[$tableName] = static::getAllColumnsForTable($tableName);
		}
		return $columnsIndexedByTable;
	}
	/**
	 * @param array $columnsRequired
	 * @return string[]
	 */
	public static function getTableNamesWithColumns(array $columnsRequired): array{
		$matchedTables = [];
		$tables = static::getTableNames();
		foreach($tables as $table){
			$columnsForTable = static::getAllColumnsForTable($table);
			$match = true;
			foreach($columnsRequired as $value){
				if(!in_array($value, $columnsForTable, false)){
					$match = false;
				}
			}
			if($match){
				$matchedTables[] = $table;
			}
		}
		return $matchedTables;
	}
	/**
	 * @param string $column
	 * @return DBTable[]
	 */
	public static function getTablesWithColumn(string $column): array{
		$names = self::getTableNamesWithColumns([$column]);
		$tables = [];
		foreach($names as $name){
			$tables[$name] = self::getDBTable($name);
		}
		return $tables;
	}
	public static function addCreatedUpdatedDeletedAtColumns(){
		QMLog::infoWithoutContext(__METHOD__);
		$atColumns = [
			static::FIELD_CREATED_AT,
			static::FIELD_UPDATED_AT,
			static::FIELD_DELETED_AT,
		];
		$tables = static::getTableNames();
		foreach($tables as $tableName){
			$fields = static::getAllColumnsForTable($tableName);
			if(!in_array(static::FIELD_UPDATED_AT, $fields, true)){
				$result = static::tryToAddColumn($tableName,
				                                 "updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
				if(!$result){
					$result = static::tryToAddColumn($tableName, "updated_at timestamp NULL");
				}
			}
			if(!in_array(static::FIELD_CREATED_AT, $fields, true)){
				$result = static::tryToAddColumn($tableName, "created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP");
				if(!$result){
					$result = static::tryToAddColumn($tableName, "created_at timestamp NULL");
				}
			}
			if(!in_array(static::FIELD_DELETED_AT, $fields, true)){
				static::tryToAddColumn($tableName, "deleted_at timestamp NULL");
			}
			if(!in_array(static::FIELD_CLIENT_ID, $fields, true)){
				static::tryToAddColumn($tableName, "client_id varchar(255)");
			}
		}
	}
	/**
	 * @param string $tableName
	 * @param string $statement
	 * @return bool
	 */
	private static function tryToAddColumn(string $tableName, string $statement): bool{
		$connection = Writable::db();
		try {
			$statement = "ALTER TABLE " . $tableName . " ADD COLUMN " . $statement . ";";
			static::info($statement);
			return $connection->statement($statement);
		} catch (Exception $e) {
			static::info(__METHOD__.": ".$e->getMessage());
			return false;
		}
	}
	public static function fixInvalidCreatedAtRecords(): void{
		QMLog::infoWithoutContext(__METHOD__);
		$tables = static::getTableNames();
		$connection = static::db();
		foreach($tables as $tableName){
			$fields = static::getAllColumnsForTable($tableName);
			if(in_array(static::FIELD_CREATED_AT, $fields, true)){
				$statement = "UPDATE " . $tableName .
				             " set created_at = '2000-01-01 00:00:00' where created_at < '2000-01-01 00:00:00';";
				QMLog::debug("Fixing null and invalid dates with " . $statement);
				$connection->statement($statement);
			}
		}
	}
	public static function logDuplicateQuery(QMQueryExecuted $queryExecuted){
		QMLog::info("Duplicate query:\n$queryExecuted->preparedQuery");
	}
	public static function outputFieldsForConstantsForAllTables(){
		$tables = static::getTableNames();
		foreach($tables as $table){
			static::outputFieldsForConstantsForOneTable($table);
		}
	}
	/**
	 * @param string $tableName
	 */
	public static function outputFieldsForConstantsForOneTable(string $tableName){
		echo "=============== START " . strtoupper($tableName) . " ====================\n";
		$record = static::getBuilderByTable($tableName)->first();
		foreach($record as $key => $value){
			$fieldNames[] = $key;
		}
		sort($fieldNames);
		foreach($fieldNames as $fieldName){
			echo "const FIELD_" . strtoupper($fieldName) . " = '$fieldName';\n";
		}
		echo "=============== END " . strtoupper($tableName) . " ====================\n";
	}
	public static function setCharacterSetAndCollationToUTF8GeneralCI(){
		$tables = static::getTableNames();
		static::disableForeignKeyConstraints();
		$dbName = static::getDbName();
		try { // Not sure why this doesn't work
			static::statementStatic("
                ALTER DATABASE $dbName
                CHARACTER SET utf8
                COLLATE utf8_general_ci;
            ");
		} catch (Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
		}
		foreach($tables as $table){
			static::statementStatic("ALTER TABLE $table CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;");
		}
		static::enableForeignKeyConstraints();
	}

	public static function pdoStatement(string $statement, bool $obfuscate = true): bool{
		QMLog::info($statement, [], $obfuscate);
		$db = static::db();
		if(!$db->getPdo()){
			$db->getPdo();
		}
		$result = $db->statement($statement);
		if($result){
			ConsoleLog::info("SUCCESS!");
		} else{
			ConsoleLog::info("FAILED!");
		}
		return $result;
	}
    public static function pdoStatementAsRoot(string $statement, bool $obfuscate = true): bool{
        QMLog::info($statement, [], $obfuscate);
        $db = static::db();
        if(!$db->getPdo()){
            $db->getPdo();
        }
        $user = $db->config["username"];
        $db->config["username"] = "root";
        $result = $db->statement($statement);
        if($result){
            ConsoleLog::info("SUCCESS!");
        } else{
            ConsoleLog::info("FAILED!");
        }
        $db->config["username"] = $user;
        return $result;
    }
	/**
	 * @return string|null
	 */
	public static function getDbName(): ?string{
		if(static::DB_NAME){
			return static::DB_NAME;
		}
        return Env::get('DB_DATABASE');
	}
	/**
	 * @return string
	 */
	public static function getMySQLDbUrl(): string{
        return 'mysql://' . static::getUser() . ':' . static::getPassword() . '@' . static::getHostWithPort() .
            '/' . static::getDbName() . '?reconnect=true';
	}
	/**
	 * @return string|null
	 */
	public static function getUser(): ?string{
		if(static::DB_USER){
			return static::DB_USER;
		}
        return \App\Utils\Env::get('DB_USERNAME');
	}
	/**
	 * @return string|null
	 */
	public static function getPassword(): ?string{
		if(static::DB_PASSWORD){
			return static::DB_PASSWORD;
		}
		$name = static::CONNECTION_NAME;
		if($name){
			$env = strtoupper($name) . "_DB_PASSWORD";
			if($pass = \App\Utils\Env::get($env)){
				return \App\Utils\Env::get($env);
			}
		}
        return \App\Utils\Env::get('DB_PASSWORD');
	}
	/**
	 * @return string
	 */
	public static function getHostWithPort(): string{
		return static::getHost() . ":" . static::getPort();
	}
	/**
	 * @return string|null
	 */
	public static function getHost(): ?string{
		if($h = static::DB_HOST_PUBLIC){
			return $h;
		}
        return \App\Utils\Env::get('DB_HOST');
	}
	/**
	 * @return int|null
	 */
	public static function getPort(): ?int{
		if($p = static::DB_PORT){
			return $p;
		}
        return \App\Utils\Env::get('DB_PORT');
//		$urlComponents = parse_url(static::getDbUrl());
//		$port = $urlComponents['port'] ?? 3306;
//		return $port;
	}
	/**
	 * @param string $string
	 * @param array $allowExceptionsLike
	 * @return array
	 */
	public static function statementStatic(string $string, array $allowExceptionsLike = []): array{
		if(!is_array($allowExceptionsLike)){
			$allowExceptionsLike = [$allowExceptionsLike];
		}
		$arr = explode(";", $string);
		$results = [];
		foreach($arr as $statement){
			$statement = trim($statement);
			if(empty($statement)){
				continue;
			}
			QMLog::info($statement);
			try {
				$start = microtime(true);
				if(stripos($statement, "select") === 0){
					$result = static::selectStatic($statement);
				} else{
					//$result = DB::statement(QMQB::getSqlComments() . $statement);
					try {
						$result = static::db()->statement($statement, []);
					} catch(Throwable $e){
						$result = static::db()->statement($statement, []);
					}

				}
				$duration = static::checkForSlowQuery($start, $statement);
				if($result){
					ConsoleLog::info("Success! :D (Took $duration seconds)");
				} else{
					QMLog::error("FAILED STATEMENT: $statement");
				}
				$results[] = ['statement' => $statement, 'result' => $result, 'duration' => $duration];
			} catch (Throwable $e) {
				$throw = true;
				foreach($allowExceptionsLike as $str){
					if($str && stripos($e->getMessage(), $str) !== false){
						QMLog::infoWithoutContext(__METHOD__.": ".$e->getMessage());
						$throw = false;
					}
				}
				if($throw){
					/** @var LogicException $e */
					throw $e;
				}
			}
		}
		return $results;
	}
	/**
	 * @param string $query
	 * @param bool   $log
	 * @return array
	 */
	public static function selectStatic(string $query, bool $log = false): array{
		if(stripos($query, "select") === false){
			$query = "select " . $query;
		}
		if($log){
			ConsoleLog::info($query);
		} else{
			QMLog::debug($query);
		}
		$start = microtime(true);
		$comments = QMQB::getSqlComments();
		$result = static::db()->select($comments . $query);
		static::checkForSlowQuery($start, $query);
		return $result;
	}
	/**
	 * @param float  $start
	 * @param string $statement
	 * @return int
	 */
	public static function checkForSlowQuery(float $start, string $statement): int{
		$duration = round(microtime(true) - $start);
		SlowQueryException::logIfSlow($duration, $statement); // Don't throw on staging API request in Admin panel
		return $duration;
	}
    public static function disableForeignKeyConstraints(Connection $c = null, string $table = null): void{
        if(!$c){$c = static::db();}
        $builder = $c->getSchemaBuilder();
        if($builder instanceof PostgresBuilder){
	        // Doesn't seem to work either $c->getConnection()->beginTransaction(); // https://bamm
	        //.co/posts/laravel-foreign-key-contraints#postgresql
            $c->getSchemaBuilder()->disableForeignKeyConstraints();
            if($table && self::$hasTableTriggerPermission){self::disableTableTriggers($c, $table);}
	        if(static::$hasReplicationRolePermission){self::setReplicationRoleToReplica($c);}
			if($table && !self::$hasTableTriggerPermission && !self::$hasReplicationRolePermission){
				$c->getDBTable($table)->disableForeignKeyConstraints();
			}
		}
        $builder->disableForeignKeyConstraints();
    }
	public static function enableForeignKeyConstraints(Connection $c = null, string $table = null){
        if(!$c){$c = static::db();}
        $builder = $c->getSchemaBuilder();
        if($builder instanceof PostgresBuilder){
	        if(static::$hasReplicationRolePermission){self::setSessionReplicationRoleOrigin($c);}
	        $builder->enableForeignKeyConstraints();
			if($table && self::$hasTableTriggerPermission){self::enableTableTriggers($c, $table);}
	        // Doesn't seem to work either $c->commit(); // https://bamm.co/posts/laravel-foreign-key-contraints#postgresql
        }
        $builder->enableForeignKeyConstraints();
	}
	public static function addTableDescriptionsToModels(): array{
		$comments = [];
		$tables = static::getTableNames();
		foreach($tables as $table){
			$comments[$table] = $comment = static::getTableComment($table);
			BaseModel::getFilePathByTable($table);
			QMLog::infoWithoutContext("public const CLASS_DESCRIPTION = '" . $comment . "'");
		}
		return $comments;
	}
	/**
	 * @param string $table
	 * @return string
	 */
	public static function getTableComment(string $table): string{
		$tfg = static::getTableInfo($table);
		return $tfg->TABLE_COMMENT;
	}
	/**
	 * @param string $table
	 * @return object
	 */
	public static function getTableInfo(string $table): object{
		$db = static::getDBName();
		$tfg = static::selectStatic("
            SELECT *
                FROM INFORMATION_SCHEMA.TABLES
                WHERE table_schema='$db'
                    AND table_name='$table';
        ");
		return $tfg[0];
	}
	public static function updateTableComments(): string{
		$tables = static::getTableNames();
		$migration = "";
		$schemaColumns = static::getSchemaColumns();
		$swagger = SwaggerJson::getSwaggerJson();
		foreach($tables as $table){
			$class = BaseModel::getClassByTable($table);
			$model_schema_path = FileHelper::absPath('resources/model_schemas/');
			$shortClassName = QMStr::toShortClassName($class);
			$model_schema_filename = $shortClassName . ".json";
			try {
				$model_schema = FileHelper::getDecodedJsonFile($model_schema_path . $model_schema_filename);
			} catch (\Throwable $e) {
				QMLog::info(__METHOD__.": ".$e->getMessage());
				continue;
			}
			$comments[$table] = $fromDB = static::getTableComment($table);
			if(!class_exists($class)){
				QMLog::info($class . " does not exist...");
				continue;
			}
			$fromConstant = $class::CLASS_DESCRIPTION;
			if(strlen($fromConstant) > strlen($fromDB)){
				$migration .= "\talter table $table comment '$fromConstant';\n";
			} elseif(!empty($fromDB)){
				$filePath = $class::getFilePathByTable($table);
				QMLog::infoWithoutContext("Add the below to $filePath");
				QMLog::infoWithoutContext("public const CLASS_DESCRIPTION = '" . $fromDB . "'");
			}
			$fields = static::getAllColumnsForTable($table);
			foreach($fields as $field){
				$comment = "";
				$fieldTitle = QMStr::snakeToTitle($field) . ". ";
				$fieldTitle = str_replace(" Where ", " for this ", $fieldTitle);
				$classTitle = QMStr::classToTitle($class);
				$fromDB = static::getColumnComment($table, $field) ?? '';
				$camelField = QMStr::camelize($field);
				$fromSwagger = SwaggerJson::getDescription($camelField);
				$type = static::getColumnType($table, $field);
				if($fromDB && stripos($comment, $fromDB) === false){
					$comment .= $fromDB . ". ";
				}
				if($fromSwagger && stripos($comment, $fromSwagger) === false){
					$comment .= $fromSwagger . " ";
				}
				if(stripos($comment, $fieldTitle) !== 0){
					$comment = $fieldTitle . ". " . $comment;
				}
				$comment = str_replace("Qm ", "QuantiModo", $comment);
				$comment = str_replace("QM", "QuantiModo", $comment);
				$comment = str_replace("Id", "ID", $comment);
				$comment = str_replace(".  .", ".", $comment);
				$comment = str_replace(". .", ".", $comment);
				$comment = str_replace(". .", ".", $comment);
				$comment = str_replace("..", ".", $comment);
				if($comment === "Charts. "){
					$comment = "Charts visualizing $classTitle data. ";
				}
				if(str_contains($comment, " At")){
					$comment = "Time at which the " . QMStr::classToTitle($class) . " was " . $comment .
					           " in the format YYYY-MM-DD HH:MM:SS in the UTC timezone. ";
				}
				if(!$comment || strlen($fromConstant) > strlen($fromDB)){
					$migration .= "\talter table $table modify $field $type comment '$comment';\n\n";
				}
				$field_schema = collect($model_schema)->where('name', $field)->first();
				try {
					$field_schema->comment = $comment;
				} catch (\Throwable $e) {
					QMLog::info("$table $field: " . $e->getMessage());
				}
			}
			FileUtil::createFile($model_schema_path, $model_schema_filename,
			                     json_encode($model_schema, JSON_PRETTY_PRINT));
		}
		ConsoleLog::info($migration);
		return $migration;
	}
	public static function getSchemaColumns(): array{
		if(static::$schemaColumns){
			return static::$schemaColumns;
		}
		return static::$schemaColumns = static::selectStatic("SELECT * FROM INFORMATION_SCHEMA.COLUMNS;");
	}
	/**
	 * @param string $tableName
	 * @param string $columnName
	 * @return string
	 */
	public static function getColumnComment(string $tableName, string $columnName): string{
		//alter table wp_posts modify ID bigint unsigned auto_increment comment 'unique number assigned to each post.';
		$result = static::getColumnInfo($tableName, $columnName);
		$str = $result->COLUMN_COMMENT;
		return $str;
	}
	/**
	 * @param string $tableName
	 * @param string $columnName
	 * @return object
	 */
	public static function getColumnInfo(string $tableName, string $columnName): object{
		if(isset(static::$columns[$tableName][$columnName])){
			return static::$columns[$tableName][$columnName];
		}
		$schemaColumns = static::$schemaColumns;
		if($schemaColumns){
			return static::$columns[$tableName][$columnName] =
				collect($schemaColumns)->filter(function($column) use ($tableName, $columnName){
					return $column->TABLE_NAME === $tableName && $column->COLUMN_NAME === $columnName;
				})->first();
		}
		$result =
			static::selectStatic("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$tableName' AND COLUMN_NAME = '$columnName';");
		return static::$columns[$tableName][$columnName] = $result[0];
	}
	/**
	 * @param string $tableName
	 * @param string $columnName
	 * @return string|null
	 */
	public static function getColumnType(string $tableName, string $columnName): ?string{
		//alter table wp_posts modify ID bigint unsigned auto_increment comment 'unique number assigned to each post.';
		$result = static::getColumnInfo($tableName, $columnName);
		if(!$result){
			//ConsoleLog::info("No column found named: $tableName.$columnName");
			return null;
		}
		$str = $result->COLUMN_TYPE;
		if($result->EXTRA){
			$str .= " " . $result->EXTRA;
		}
		return $str;
	}
	public static function getInterestingTables(): array{
		if(static::$interestingTables){
			return static::$interestingTables;
		}
		$files = FileFinder::listFilesAndFoldersNonRecursively('app/Models', true);
		$tables = [User::TABLE];
		foreach($files as $file){
			$class = FileHelper::pathToClass($file);
			$table = QMStr::classToTableName($class);
			$allTables = static::getTableNames();
			if(!in_array($table, $allTables)){
				continue;
			}
			try {
				$lastUpdated = static::getBuilderByTable($table)->max(BaseModel::UPDATED_AT);
			} catch (\Throwable $e) {
				QMLog::info("Skipping $table because " . $e->getMessage());
				continue;
			}
			if(!$lastUpdated || strtotime($lastUpdated) < time() - 86400){
				ConsoleLog::info("Skipping $table because last update was $lastUpdated..");
				continue;
			}
			$tables[] = $table;
		}
		return static::$interestingTables = $tables;
	}
	/**
	 * @param string $needle
	 * @param string $fieldName
	 * @param bool   $hardDelete
	 */
	public static function deleteFromAllTablesWhereLike(string $needle, string $fieldName, bool $hardDelete = false){
		$matchedTables = static::getTableNamesWithColumn($fieldName);
		foreach($matchedTables as $matchedTable){
			$qb = static::getBuilderByTable($matchedTable)
                ->whereLike($fieldName, '%' . $needle . '%');
			$count = $qb->count();
			static::info($hardDelete ? "Hard" : "Soft" . "-deleting $count records like $needle in $matchedTable");
			if($hardDelete){
				$qb->delete();
			} else{
				$qb->update([static::FIELD_DELETED_AT => date('Y-m-d H:i:s')]);
			}
		}
	}
	/**
	 * @param string $needle
	 * @param bool   $like
	 * @return string[]
	 */
	public static function getTableNamesWithColumn(string $needle, bool $like = false): array{
		if(isset(static::$tablesWithColumn[$needle])){
			return static::$tablesWithColumn[$needle];
		}
		$matchedTables = [];
		$tables = static::getTableNames();
		foreach($tables as $table){
			$columns = static::getAllColumnsForTable($table);
			foreach($columns as $haystack){
				if($like){
					if(stripos($haystack, $needle) !== false){
						$matchedTables[] = $table;
					}
				} else{
					if($haystack === $needle){
						$matchedTables[] = $table;
					}
				}
			}
		}
		$matchedTables = array_values(array_unique($matchedTables));
		return static::$tablesWithColumn[$needle] = $matchedTables;
	}
	/**
	 * @return PDO
	 */
	public static function pdo(): PDO{
		return static::db()->getPdo();
	}
	public static function assertLogging(): void{
		if(!DB::logging()){
			le("DB NOT LOGGING!");
		}
	}
	/**
	 * @throws InvalidDatabaseCredentialsException
	 */
	public static function createReadonlyUser(): void{
		$testDbCredentials = static::credentialsCommand();
		$testDbName = static::getDbName();
		static::exec("mysql $testDbCredentials $testDbName << EOF
            grant select on $testDbName.* to 'readonly'@'%' identified by 'test_password';
            flush privileges;
            EOF");
	}
	/**
	 * @return string
	 */
	public static function credentialsCommand(): string{
		return //"--defaults-extra-file=" . self::getMySqlConfPath() . " " .
			'-h ' . static::getHost().
			' -u ' .static::getUser().
			' -p' .static::getPassword().
			" --port=" . static::getPort();
	}

	protected static function getMySqlConfPath(): string{
		if(isset(self::$myCnfPath)){
			return self::$myCnfPath;
		}
		$f = new MyCnfFile;
		$f->sync();
		return self::$myCnfPath = $f->getDestinationPath();
	}
	/**
	 * @param string $command
	 * @param bool   $obfuscate
	 * @return string
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function exec(string $command, bool $obfuscate = true): string {
		$out = ThisComputer::exec($command, true, $obfuscate);
		if(stripos($out, "Can't connect to MySQL") !== false){
			/** @noinspection PhpUnhandledExceptionInspection */
			throw new InvalidDatabaseCredentialsException(static::getObfuscatedDbUrl());
		}
		return $out;
	}
	public static function getObfuscatedDbUrl(): string{
		$pw = static::getPassword();
		$url = static::getMySQLDbUrl();
		if($pw !== "test_password" && $pw !== "secret"){
			$url = str_replace($pw, "[HIDDEN]", $url);
		}
		return $url;
	}
	/**
	 * @param string $message
	 */
	public static function logDbNameAndHost(bool $once = false){
		$msg = self::getConnectionLogMessage();
		if($once){
			ConsoleLog::once($msg);
		} else {
			ConsoleLog::info($msg);
		}
	}
	/**
	 * @param string $tableName
	 * @return Column[]
	 */
	public static function listTableColumns(string $tableName): array{
		return Writable::db()->getDoctrineSchemaManager()->listTableColumns($tableName);
	}
	/**
	 * @param string      $selectStatement
	 * @param string|null $title
	 */
	public static function logSelectToTable(string $selectStatement, string $title = null){
		ConsoleLog::info($selectStatement);
		$res = ReadonlyDB::db()->select($selectStatement);
		QMLog::table($res, $title);
	}
	public static function getExternalHost(): string{
		return Migrations::getHost();
	}
	/**
	 * @throws \App\Exceptions\NoInternetException
	 */
	public static function getExternalDbUrl(): string{
		$url = static::getMySQLDbUrl();
		$url = str_replace(static::getHost(), ThisComputer::getCurrentServerExternalIp(), $url);
		return $url;
	}
	public static function dumpTableToXML(string $name): void {
		$table = static::getDBTable($name);
		$table->dumpXML();
	}
	/**
	 * @param string $name
	 * @return DBTable
	 */
	public static function getDBTable(string $name): DBTable{
		$table = static::$dbTables[static::getConnectionName()][$name] ?? null;
		if($table){return $table;}
		try {
			$dbId = static::getDBIdentifier();
            ConsoleLog::info("Getting DBTable $name from $dbId...");
			return static::$dbTables[static::getConnectionName()][$name] = new DBTable($name, static::db());
		} catch (DBALException $e) {
			/** @var \RuntimeException $e */
			throw $e;
		}
	}
	/**
	 * @return DBTable[]
	 */
	public static function getDBTables(): array{
		if(isset(static::$tables[static::getConnectionName()])){
			return static::$tables[static::getConnectionName()];
		}
		$tables = [];
		foreach(static::getTableNames() as $name){
			$tables[] = static::getDBTable($name);
		}
		return static::$tables[static::getConnectionName()] = $tables;
	}
	public static function getDumpPath(): string{
		$path = FileHelper::absPath("storage/db/" . static::getConnectionName());
		FileHelper::createDirectoryIfNecessary($path);
		return $path;
	}
	/**
	 * @return Expression
	 */
	public static function getDatabaseNow(): Expression{
		return ReadonlyDB::db()->raw('NOW()');
	}
	/**
	 * @param int $plusSeconds
	 * @return Expression
	 */
	public static function getDatabaseNowPlusSeconds(int $plusSeconds): Expression{
		return ReadonlyDB::db()->raw('NOW() + INTERVAL ' . $plusSeconds . ' SECOND');
	}
	/**
	 * @param QMQB $qb
	 * @param array $fieldNames
	 * @return QMQB
	 */
	public static function addWhereNotEmptyDbClauses(QMQB $qb, array $fieldNames = []): QMQB{
		foreach($fieldNames as $fieldName){
			$qb->where($fieldName, '<>', '');
			$qb->whereNotNull($fieldName);
		}
		return $qb;
	}
	/**
	 * @param QMQB $qb
	 * @param string $field
	 * @return QMQB
	 */
	public static function addUpdatedInLastDayWhereClause(QMQB $qb, string $field = 'updated_at'): QMQB{
		$qb->whereRaw($field, ">", Carbon::now()->subDay());
		return $qb;
	}
	/**
	 * @param Builder $qb
	 * @return int
	 */
	public static function softDelete(Builder $qb): int{
		return $qb->update([static::FIELD_DELETED_AT => date('Y-m-d H:i:s')]);
	}
	/**
	 * @param Builder $qb
	 * @param                                    $array
	 * @return bool|int
	 */
	public static function insertOrUpdate(Builder $qb, $array): bool|int{
		$first = $qb->first();
		if($first){
			return $qb->update($array);
		}
		return $qb->insert($array);
	}
	/**
	 * @param string $statement
	 * @return bool
	 */
	public static function executeStatement(string $statement): bool{
		return Writable::db()->statement($statement);
	}
	/**
	 * @param Builder $qb
	 * @return Collection
	 */
	public static function getLastUpdatedAt(Builder $qb): Collection{
		return $qb->orderBy(static::FIELD_UPDATED_AT, 'desc')->pluck(static::FIELD_UPDATED_AT);
	}
	/**
	 * @param $tableName
	 * @param $columns
	 * @param $modelName
	 * @return string
	 */
	public static function createMigration($tableName, $columns, $modelName): string{
		sort($columns);
		$_template = '<?php
namespace QuantimodoMigrations;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
 /**
 * @package QuantimodoMigrations
 */
class Version<version> extends AbstractMigration
{
    <fields>
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $table = $schema->createTable(\'' . $tableName . '\');
        $table->addColumn(\'id\', Type::INTEGER, [\'notnull\' => true,  \'unsigned\' => true, \'autoincrement\' => true]);
        $table->addColumn(\'updated_at\', Type::DATETIME, [\'notnull\' => true]);
<up>
        $table->addColumn(\'created_at\', Type::DATETIME, [\'notnull\' => true]);
        $table->addColumn(\'deleted_at\', Type::DATETIME, [\'notnull\' => false]);
    }
    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        <down>
    }
}
';
		$version = date('YmdHis');
		$placeHolders = [
			'<version>',
			'<up>',
			'<down>',
			'<fields>',
		];
		$down = null;
		$up = $fields = '';
		foreach($columns as $column){
			$up .= '$table->addColumn(' . $modelName . '::FIELD_' . $column . ', Type::INTEGER, [\'notnull\' => false]);
';
			$fields .= 'const FIELD_' . $column . ' = \'' . $column . '\';
    ';
		}
		foreach($columns as $column){
			$up .= '$this->skipIf($table->hasColumn(\'' . $column . '\'));
';
		}
		$up .= '$table->setPrimaryKey([\'id\']);
';
		$unique = '$table->addUniqueIndex([
    ';
		foreach($columns as $column){
			$unique .= $modelName . '::FIELD_' . $column . ',
    ';
		}
		$unique .= '], \'unique_index_name_here\');
';
		$up .= $unique;
		$replacements = [
			$version,
			$up ? "        " . implode("\n        ", explode("\n", $up)) : null,
			$down ? "        " . implode("\n        ", explode("\n", $down)) : null,
			$fields,
		];
		$code = str_replace($placeHolders, $replacements, $_template);
		$code = preg_replace('/^ +$/m', '', $code);
		$dir = FileHelper::projectRoot() . '/Migrations';
		$path = $dir . '/Version' . $version . '.php';
		file_put_contents($path, $code);
		static::info("Generated new migration class to $path");
		return $path;
	}
	/**
	 * @param string $tableName
	 * @return bool
	 */
	public static function tableHasUpdatedAtField(string $tableName): bool{
		return in_array(static::FIELD_UPDATED_AT, static::getAllColumnsForTable($tableName), true);
	}
	/**
	 * @param string $tableName
	 */
	public static function outputTableConstants(string $tableName){
		$columnNames = static::getAllColumnsForTable($tableName);
		foreach($columnNames as $columnName){
			static::info("public const FIELD_" . strtoupper($columnName) . " = '" . $columnName . "';");
		}
	}
	/**
	 * @param string $fieldName
	 * @param        $value
	 * @param string $reason
	 */
	public static function hardDeleteFromAllTablesWhere(string $fieldName, $value, string $reason){
		static::deleteFromAllTablesWhere($fieldName, $value, true, $reason);
	}
	/**
	 * @param string $fieldName
	 * @param        $value
	 * @param bool   $hard
	 * @param string $reason
	 */
	private static function deleteFromAllTablesWhere(string $fieldName, $value, bool $hard, string $reason){
		if(empty($value)){
			le("No value");
		}
		$message = 'Soft';
		if($hard){
			$message = 'Hard';
		}
		$message .= "-deleting records from all tables where $fieldName is $value because $reason.";
		$testUser = stripos($reason, 'test user') !== false;
		if($testUser){
			QMLog::infoWithoutContext($message, false);
		} else{
			QMLog::error($message);
		}
		$tables = static::getTableNamesWithColumn($fieldName);
		foreach($tables as $table){
			$qb = Writable::db()->table($table);
			$qb->where($fieldName, $value);
			if($hard){
				$count = $qb->hardDelete($reason, !$testUser);
				QMLog::infoWithoutContext("Hard-deleted $count $table records where $fieldName is $value. ", false);
			} else{
				$count = $qb->softDelete([], $reason);
				QMLog::infoWithoutContext("Soft-deleted $count $table records where $fieldName is $value. ", false);
			}
		}
	}
	/**
	 * @param string $fieldName
	 * @param        $value
	 * @param string $reason
	 */
	public static function softDeleteFromAllTablesWhere(string $fieldName, $value, string $reason){
		static::deleteFromAllTablesWhere($fieldName, $value, false, $reason);
	}
	/**
	 * @param string $tableName
	 * @param array  $data
	 * @param array  $wheres
	 * @return bool
	 */
	public static function alreadyInsertedOrUpdated(string $tableName, array $data, array $wheres): bool{
		QMLog::debug("$tableName: Updating or inserting " . QMLog::var_export($data, true));
		$previousRecords = Memory::get($tableName, Memory::RECORDS_INSERTED_OR_UPDATED, []);
		foreach($previousRecords as $previousRecord){
			if($previousRecord['data'] == $data && $previousRecord['wheres'] == $wheres){
				QMLog::error("Already inserted identical record! " . QMLog::var_export($data, true));
				return true;
			}
		}
		Memory::add($tableName, [
			'data' => $data,
			'wheres' => $wheres,
		], Memory::RECORDS_INSERTED_OR_UPDATED);
		return false;
	}
	/**
	 * @return void
	 */
	public static function migrate(): void{
		static::migrateTables();
		static::migrateForeignKeys();
	}
	public static function flushQueryLogs(string $caller){
		QMLog::debug("Flushing query log in $caller");
		//static::disconnect(); This fixes too many connections but can only be done in Laravel tests or it seems to break Clockwork PDO
		foreach(self::getConnections() as $db){
			$db->flushQueryLog();
		}
		QMQB::flushQueryLog();
		TestDB::setWhiteListedTables([]);
	}
	/**
	 * @return QMDB[]
	 */
	public static function getConnections(): array{
		return Memory::getByPrimaryKey(Memory::DB_CONNECTIONS) ?? [];
	}
	/**
	 * Clear the query log.
	 * @return void
	 */
	public function flushQueryLog(): void{
		QMQB::flushQueryLog();
		$this->getConnection()->flushQueryLog();
		parent::flushQueryLog();
	}
	public static function disconnectAll(){
		DB::disconnect();  // Hopefully fixes SQLSTATE[08004] [1040] Too many connections
		ReadonlyDB::db()->disconnect();
		Writable::db()->disconnect();
	}
	/**
	 * @return bool
	 */
	public static function dbIsProductionOrStaging(): bool{
		return stripos(Writable::getDbName(), 'staging') !== false ||
		       stripos(Writable::getDbName(), 'production') !== false;
	}
	/**
	 * @return string
	 */
	public static function getDateFormat(): string{
		return Writable::db()->getQueryGrammar()->getDateFormat();
	}
	/**
	 * @param int|string|null $timeAt
	 * @return string
	 */
	public static function getDate(int|string $timeAt = null): string{
		return TimeHelper::YmdHis($timeAt);
	}
	/**
	 * @param string $field
	 * @param string $oldValue
	 * @param string $newValue
	 * @param bool   $dryRun
	 */
	public static function replaceEverywhere(string $field, string $oldValue, string $newValue, bool $dryRun = true){
		$tables = static::getTableNamesWithColumn($field);
		foreach($tables as $table){
			$qb = Writable::getBuilderByTable($table)->where($field, $oldValue);
			if($dryRun){
				$number = $qb->count();
			} else{
				$number = $qb->update([$field => $newValue]);
			}
			QMLog::info("Changed $field from $oldValue to $newValue in $number records in $table table!", [], false);
		}
	}
	public static function updateDBConstants(){
		QMSpreadsheetImporter::updateDatabaseTableFromHardCodedConstants();
		QMConnector::updateDatabaseTableFromHardCodedConstants();
		QMUnitCategory::updateDatabaseTableFromHardCodedConstants();
		QMVariableCategory::updateDatabaseTableFromHardCodedConstants();
		QMCommonVariable::updateDatabaseTableFromHardCodedConstants();
		QMUnit::updateDatabaseTableFromHardCodedConstants();
	}
	public static function updateDBConstantsIfNecessary(){
		QMConnector::updateDatabaseTableFromHardCodedConstantsIfNecessary();
		QMUnitCategory::updateDatabaseTableFromHardCodedConstantsIfNecessary();
		QMVariableCategory::updateDatabaseTableFromHardCodedConstantsIfNecessary();
		QMCommonVariable::updateDatabaseTableFromHardCodedConstantsIfNecessary();
		QMUnit::updateDatabaseTableFromHardCodedConstantsIfNecessary();
		QMSpreadsheetImporter::updateDatabaseTableFromHardCodedConstantsIfNecessary();
	}
	/**
	 * @param array $arr
	 * @return array
	 */
	public static function getPropertyNamesFromDbColumnArray(array $arr): array{
		$propertyNames = [];
		$strings = static::getSelectStringsFromDbColumnsArray($arr);
		foreach($strings as $key => $string){
			$name = QMStr::after(' as ', $string, null, true);
			if(!$name){
				$name = QMStr::after('.', $string);
			}
			$propertyNames[] = $name;
		}
		return $propertyNames;
	}
	/**
	 * @param array $arr
	 * @return array
	 */
	public static function getSelectStringsFromDbColumnsArray(array $arr): array{
		$strings = [];
		foreach($arr as $key => $value){
			if(is_string($value)){
				$strings[$key] = $value;
			} else{
				$strings[$key] = $value->getValue();
			}
		}
		return $strings;
	}
	/**
	 * @param string $tableName
	 * @param array  $fields
	 * @return array
	 */
	public static function getFieldsWithAliases(string $tableName, array $fields): array{
		$strings = [];
		foreach($fields as $field){
			$strings[] = "$tableName.$field as " . QMStr::camelize($field);
		}
		return $strings;
	}
	/**
	 * @param array $tableFields
	 * @return array
	 */
	public static function addCamelCaseAliases(array $tableFields): array{
		foreach($tableFields as $key => $tableField){
			if(!is_string($tableField)){
				continue;
			}
			if(stripos($tableField, ' as ') !== false){
				continue;
			}
			$field = QMStr::after('.', $tableField);
			$tableFields[$key] = "$tableField as " . QMStr::camelize($field);
		}
		return $tableFields;
	}
	/**
	 * @param array $tables
	 */
	public static function dropTables(array $tables){
		ConsoleLog::info(__FUNCTION__ . "...");
		foreach($tables as $TABLE){
			try {
				static::statementStatic("drop table $TABLE;");
			} catch (Throwable $e) {
				ConsoleLog::info(__METHOD__.": ".$e->getMessage());
				if(!str_contains($e->getMessage(), "Base table or view not found")){
					le($e);
				}
			}
		}
	}
	/**
	 * @param string $table
	 * @return array
	 */
	public static function getForeignKeysForTable(string $table): array{
		$result = ReadonlyDB::db()->select('
            SELECT *
            FROM
              information_schema.KEY_COLUMN_USAGE
            WHERE
              TABLE_NAME = \'' . $table . '\'
              AND CONSTRAINT_SCHEMA = \'' . Writable::getDbName() . '\'
              ;');
		return $result;
	}
	/**
	 * @param string $table
	 * @param string $needle
	 * @return array
	 */
	public static function getColumnsLike(string $table, string $needle): array{
		$columns = static::getAllColumnsForTable($table);
		$filtered = Arr::where($columns, function($column) use ($needle){
			return str_contains($column, $needle);
		});
		return $filtered;
	}
	/**
	 * @param string $statement
	 */
	public static function statementIfExists(string $statement){
		static::statementStatic($statement, ["not found", "key exists"]);
	}
	public static function enableUnusedIndexLogging(){
		ConsoleLog::info("See UNUSED INDEXES at https://" . Writable::getHost() .
		                                       "/phpmyadmin/sql.php?db=sys&table=schema_unused_indexes&pos=0");
		/** @noinspection SqlResolve */
		static::statementStatic("
            update performance_schema.setup_consumers set enabled = 'yes' where name = 'events_waits_current';
            update performance_schema.setup_instruments set enabled = 'yes' where name = 'wait/io/table/sql/handler';
        ");
		ConsoleLog::info("See REDUNDANT INDEXES at https://" . Writable::getHost() .
		                                       "/phpmyadmin/sql.php?server=1&db=sys&table=schema_redundant_indexes&pos=0");
	}
	/**
	 * @return array
	 */
	public static function getRedundantIndexes(): array{
		if(!static::isMySQL()){
			le("only implemented for MySQL");
		}
		/** @noinspection SqlResolve */
		$arr = DB::select(/** @lang MySQL */ "SELECT * FROM sys.`schema_redundant_indexes`;");
		$unique = [];
		foreach($arr as $item){
			$unique[$item->sql_drop_index] = $item;
		}
		foreach($unique as $item){
			$str = \App\Logging\QMLog::print_r($item, true);
			ConsoleLog::info("
        ConsoleLog::info(\"
            $str
        \");
            ");
			Migrations::makeMigration("drop index $item->redundant_index_name on $item->table_name;",
			                          "drop index $item->redundant_index_name on $item->table_name;");
		}
		return $arr;
	}
	/**
	 * @return array
	 * @noinspection PhpUnused
	 */
	public static function getUnusedIndexes(): array{
		if(!static::isMySQL()){
			le("getUnusedIndexes() is only implemented for MySQL");
		}
		/** @noinspection SqlResolve */
		$arr = DB::select(/** @lang MySQL */ "SELECT * FROM sys.`schema_unused_indexes`;");
		$byTable = [];
		foreach($arr as $item){
			$byTable[$item->object_name][] = $item;
		}
		foreach($arr as $item){
			Migrations::makeMigration(__FUNCTION__, "drop_" . $item->redundant_index_name . "_index",
			                          "drop index $item->redundant_index_name on $item->table_name;");
		}
		return $byTable;
	}
	public static function enableSlowQueryLog(){
		try {
			static::statementStatic("
                SET GLOBAL slow_query_log = 'ON';
                SET GLOBAL log_output = 'TABLE';
            ");
		} catch (Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
		}
	}
	/**
	 * @param int        $lastXHours
	 * @param array|null $queries
	 * @return string
	 * @throws Exception
	 */
	public static function renderSlowQueryTable(int $lastXHours = 1, array $queries = null): string{
		if(!$queries){
			$queries = static::getSlowQueries($lastXHours);
		}
		$tableId = 'data-table-id';
		$html = Table::instance()->setData($queries)->attr('table', 'id', $tableId)
		             ->attr('table', 'class', 'table table-bordered table-striped table-hover')
		             ->attr('table', 'cellspacing', '0')->attr('table', 'width', '100%')
			//->attr('table', 'style', 'width: 100%; table-layout: fixed;')
			         ->column()->title('Avg Duration')->value('avg_duration')
			//->css('td', 'color', 'red')
			         ->css('td', 'width', '5%')->attr('td', 'width', '5%')->add()->column()->title('Number')
		             ->value('numberOfQueries')
			//->css('td', 'color', 'red')
			         ->css('td', 'width', '5%')->attr('td', 'width', '5%')->add()->column()->title('Minutes Ago')
		             ->value('minutes_ago')->css('td', 'width', '5%')->attr('td', 'width', '5%')->add()->column()
		             ->title('Test')->value('test')->css('td', 'width', '5%')->attr('td', 'width', '5%')->add()
			// TODO: Maybe implement explain
			         ->column()
			//                ->value(function ($row) {
			//                    return '<a href="https://local.quantimo.do/sql/explain?sql='.
			//                        urlencode($row->sql_text).
			//                        '">Full Query</a>';
			//                })
			         ->value(function($row) use ($tableId){
				$useToast = false;
				/** @noinspection PhpConditionAlreadyCheckedInspection */
				if($useToast){
					$onclick = 'md.showNotification(\'top\',\'center\', \'' . rawurlencode($row->sql_formatted) . '\')';
				} else{
					//$onclick = '$( "'.$row->sql_formatted.'" ).insertBefore( "#'.$tableId.'" );';
					$onclick = null;
				}
				return '<a href="#' . $row->id . '"
                        style="cursor: pointer;"
                        onclick="' . $onclick . '">Full Query</a>';
			})->css('td', 'color', 'blue')->css('td', 'width', '5%')->attr('td', 'width', '5%')->add()->column()
		             ->filter()->title('Where Clause')->value(function($row){
				$where = QMStr::after(">from</span>", $row->html, null, true);
				if(!$where){
					return "No where clause on " . $row->html;
				}
				return QMStr::truncate($where, 140);
			})->attr('td', 'style', 'width: 75%;')->css('td', 'width', '80%')->attr('td', 'width', '80%')->add()
		             ->render(true);
		return $html;
	}
	public static function getSlowQueries(int $lastXHours = 12): array{
		$arr = Migrations::db()->select("SELECT
                GROUP_CONCAT(DISTINCT(user_host)) AS user_hosts,
                round(avg(query_time)) as avg_duration,
                count(*) as numberOfQueries,
                max(start_time) as at,
                sql_text
            from mysql.slow_log
            where start_time > NOW() - INTERVAL $lastXHours HOUR
            group by sql_text ORDER BY numberOfQueries DESC LIMIT 100;");
		foreach($arr as $key => $value){
			$value->sql_formatted = SqlFormatter::format($value->sql_text, false);
			$value->html = SqlFormatter::format($value->sql_text, true);
			$value->comment = QMStr::between($value->sql_text, "/*", "*/");
			if(!$value->comment){
				$value->comment = "Please add comment for this query!";
			}
			$value->branch = QMStr::between($value->comment, "Branch: ", '\n', 'Unspecified');
			$value->test = QMStr::between($value->comment, "Test: ", '\n', 'Unspecified');
			$value->summary = QMStr::truncate($value->sql_formatted, 140);
			$value->minutes_ago = TimeHelper::minutesAgo($value->at);
			$where = '<pre>' . QMStr::after('>from</span>', $value->html, null, true);
			if(empty($where)){
				le("where empty");
			}
			$value->where = $where;
			$value->id = "query-" . $key;
		}
		return $arr;
	}
	public static function outputSlowQueries(bool $highlight = false): string{
		$arr = static::getSlowQueries($highlight);
		$str = '';
		foreach($arr as $key => $value){
			$str .= QMLog::print($value, $key);
		}
		return $str;
	}
	/** @noinspection SqlResolve
	 * @noinspection SyntaxError
	 */
	public static function outputMostFrequentQueriesInGeneralLog(){
		if(!static::isMySQL()){
			le("only implemented for MySQL");
		}
		static::statementStatic(/** @lang MySQL */ "SELECT count(*) as numberOfQueries, argument from mysql.general_log
            where event_time > NOW() - INTERVAL 1 HOUR
            group by argument ORDER BY numberOfQueries DESC LIMIT 100;");
	}
	/** @noinspection SyntaxError */
	public static function outputHostWithMostQueriesInGeneralLog(){
		if(!static::isMySQL()){
			le("only implemented for MySQL");
		}
		/** @noinspection SqlResolve */
		static::statementStatic(/** @lang MySQL */ "SELECT count(*) as numberOfQueries, user_host from mysql.general_log
            where event_time > NOW() - INTERVAL 1 HOUR
            AND argument = \"select * from `tracker_sessions` where `uuid` = ? limit 1\"
            group by user_host ORDER BY numberOfQueries DESC LIMIT 100");
	}
	/**
	 * @param string $query
	 * @return array
	 */
	public static function explain(string $query): array{
		$result = DB::select("EXPLAIN " . $query);
		QMLog::print($result, "Explain");
		return $result;
	}
	/**
	 * @param string $foreignTable
	 * @param string $referencedTable
	 * @param string $idColumnName
	 * @return bool
	 */
	public static function alreadyHasForeignKey(string $foreignTable, string $referencedTable,
	                                            string $idColumnName = "id"): bool{
		$existingForeignKeys = static::getForeignKeysReferencingGivenTableColumn($referencedTable, $idColumnName);
		$existing = Arr::where($existingForeignKeys, function($info) use ($foreignTable){
			return $info->TABLE_NAME === $foreignTable;
		});
		if($existing){
			return true;
		} else{
			return false;
		}
	}
	/**
	 * @param string $referencedTable
	 * @param string $referencedColumn
	 * @return ForeignKeyConstraint[]
	 */
	public static function getForeignKeysReferencingGivenTableColumn(string $referencedTable, string
    $referencedColumn):
    array{
        $tables = static::getDBTables();
        $existingForeignKeys = [];
        foreach ($tables as $table) {
            $foreignKeys = $table->getForeignKeys();
            foreach ($foreignKeys as $foreignKey) {
                $foreignTableName = $foreignKey->getForeignTableName();
                if ($foreignTableName === $referencedTable){
                    $foreignColumns = $foreignKey->getForeignColumns();
                    $foreignColumn = str_replace('"', '', $foreignColumns[0]);
                    if ($foreignColumn === $referencedColumn) {
                        $existingForeignKeys[] = $foreignKey;
                    }
                }
            }
        }
        return $existingForeignKeys;
	}
	/**
	 * @param string $tableName
	 * @param string $columnName
	 * @return bool
	 */
	public static function columnExists(string $tableName, string $columnName): bool{
		$haystack = static::getAllColumnsForTable($tableName);
		return in_array($columnName, $haystack);
	}
	/**
	 * @param string $viewName
	 * @param string $selectQuery
	 */
	public static function createOrReplaceView(string $viewName, string $selectQuery){
		Migrations::db()->statement("CREATE OR REPLACE VIEW `$viewName` AS $selectQuery;");
	}
	/**
	 * @param string $class
	 * @param string $columnName
	 */
	public static function addCalculatedField(string $class, string $columnName){
		$tableName = $class::TABLE;
		if(!isset($class::MYSQL_COLUMN_TYPES[$columnName])){
			throw new LogicException('Please set ' . $class . '::$mysqlColumnTypes[' . $columnName . ']');
		}
		$type = $class::MYSQL_COLUMN_TYPES[$columnName];
		$vars = get_class_vars($class);
		$calculatedFields = $vars['calculatedFields'];
		if(!isset($calculatedFields[$columnName])){
			throw new LogicException('Please add update query in ' . $class . '::$calculatedFields[' . $columnName .
			                         ']');
		}
		$updateFormula = $calculatedFields[$columnName];
		$formulaWithoutSemicolon = str_replace(';', '', $updateFormula);
		static::statementIfNotExists("alter table $tableName
                add $columnName $type
                    comment 'Formula: $formulaWithoutSemicolon';");
		static::statementStatic($updateFormula);
	}
	/**
	 * @param string $statement
	 */
	public static function statementIfNotExists(string $statement){
		static::statementStatic($statement, ["already exists", "duplicate key", "Duplicate column name"]);
	}
	/**
	 * @return string
	 */
	public static function getReleaseStageFromDbName(): string{
		$dbName = Writable::getDbName();
		if(stripos($dbName, Env::ENV_PRODUCTION) !== false){
			return Env::ENV_PRODUCTION;
		}
		if(stripos($dbName, Env::ENV_STAGING) !== false){
			return Env::ENV_STAGING;
		}
		if(stripos($dbName, Env::ENV_LOCAL) !== false){
			return Env::ENV_LOCAL;
		}
		if(stripos($dbName, 'test') !== false){
			return Env::ENV_TESTING;
		}
		le("Could not determine ReleaseStageQueue from DB name $dbName");
	}
	/**
	 * @param string $table
	 * @return string[]
	 */
	public static function getTimestampColumns(string $table): array{
		$columns = [];
		$all = static::getAllColumnsForTable($table);
		foreach($all as $one){
			$type = static::getColumnType($table, $one);
			if(stripos($type, static::TYPE_TIMESTAMP) !== false){
				$columns[] = $one;
			} elseif($one === static::FIELD_UPDATED_AT){
				$m = "$table $one should be a timestamp but type is $type";
				//throw new LogicException($m);
				QMLog::info($m);
			}
		}
		return $columns;
	}
	public static function getAllColumnsLike(string $needle): array{
		$tables = static::getAllTablesWithColumnLike($needle);
		$matches = [];
		foreach($tables as $table){
			$columns = static::getAllColumnsForTable($table);
			foreach($columns as $column){
				if(stripos($column, $needle) === false){
					continue;
				}
				$matches[$table][] = $column;
			}
		}
		return $matches;
	}
	/**
	 * @param string $needle
	 * @return array|string[]
	 */
	public static function getAllTablesWithColumnLike(string $needle): array{
		return static::getTableNamesWithColumn($needle, true);
	}
	/**
	 * @param string $reason
	 * @param string $referencedTable
	 * @param string $referencedColumn
	 * @param        $idReferenced
	 */
	public static function deleteRelatedForeignRecords(string $reason, string $referencedTable,
	                                                   string $referencedColumn, $idReferenced): void{
		$foreignKeys = static::getForeignKeysReferencingGivenTableColumn($referencedTable, $referencedColumn);
		foreach($foreignKeys as $foreignKey){
			$localTableName = $foreignKey->getLocalTableName();
			$localColumn = $foreignKey->getLocalColumns()[0];
			$qb = Writable::getBuilderByTable($localTableName)->where($localColumn, $idReferenced);
			$qb->hardDelete($reason);
		}
	}
	/**
	 * @param string $table
	 * @param array  $allFields
	 * @param string $select
	 * @return string
	 */
	public static function convertSelectToInsertStatement(string $table, array $allFields, string $select): string{
		$exploded = explode(",", $select);
		$insert = "insert ignore into $table
                (
            ";
		$fieldsAdded = [];
		foreach($exploded as $line){
			$field = QMStr::after('as ', $line, null, true);
			if($field){
				$field = QMStr::before("\n", $field, $field, true);
				$field = trim($field);
				if(!in_array($field, $allFields)){
					le("$field does not exist!");
				}
				$insert .= "\n" . $field . ",";
				$fieldsAdded[] = $field;
			}
		}
		foreach($allFields as $field){
			if(!in_array($field, $fieldsAdded)){
				ConsoleLog::info("$field not in statement");
			}
		}
		$insert .= ")";
		$insert = str_replace(",)", ")", $insert);
		$query = $insert . $select;
		return $query;
	}
	public static function outputDiskSpaceUsageByColumn(int $minMB = 10){
		SlowQueryException::disable();
		$bySize = static::getTableSizesInMbDescending();
		foreach($bySize as $table => $tableSize){
			if($tableSize < $minMB){
				continue;
			}
			ConsoleLog::info("\n$table => $tableSize MB");
			$columns = static::getAllColumnsForTable($table);
			$columnsBySize = [];
			$largestSize = 0;
			$largestSoFar = null;
			foreach($columns as $column){
				$size = static::selectStatic('SELECT sum(char_length(' . $column . '))/1024/1024 as mb FROM ' . $table,
				                             true);
				$size = round(floatval($size[0]->mb));
				$columnsBySize[$size] = $column;
				if($size > $largestSize){
					$largestSize = $size;
					$largestSoFar = $column;
					ConsoleLog::info("Largest so far: $largestSoFar => " . $largestSize . " MB");
				}
			}
			krsort($columnsBySize);
			foreach($columnsBySize as $columnSize => $column){
				if($columnSize > $minMB){
					ConsoleLog::info("\t\t$column => " . $columnSize . " MB");
				}
			}
		}
	}
	public static function getTableSizesInMbDescending(): array{
		if($sizes = static::$tableSizes[static::getConnectionName()] ?? null){
			return $sizes;
		}
		$your_database = static::getDBName();
		/** @noinspection SqlResolve */
		$size = static::selectStatic('SELECT table_name AS `Table`, round(((data_length + index_length) / 1024 / 1024), 2) `mb`
                FROM information_schema.TABLES WHERE table_schema = "' . $your_database . '"');
		$tableSizes = [];
		foreach($size as $arr){
			if(!$arr->mb){
				continue;
			} // View
			$tableSizes[$arr->Table] = floatval($arr->mb);
		}
		arsort($tableSizes);
		return static::$tableSizes[static::getConnectionName()] = $tableSizes;
	}
	public static function getCountAggregatedByDay(string $table, string $field, array $params = []): array{
		$table = static::replaceTableAlias($table);
        $qb = ReadonlyDB::getBuilderByTable($table);
        $postgres = static::isPostgres();
		$sqlite = static::isSqlite();
		if(stripos($field, '_at')){
			$date = "DATE_FORMAT($field,\"%Y-%m-%d\")";
            if($postgres){$date = "to_char($field, 'YYYY-MM-DD')";}
			if($sqlite){$date = "strftime(\"%Y-%m-%d\", $field)";}
		} else{
			$date = "DATE_FORMAT(FROM_UNIXTIME($field),\"%Y-%m-%d\")";
            if($postgres){$date = "extract(day from (to_timestamp($field))";}
			if($sqlite){$date = "strftime(\"%Y-%m-%d\", $field, \"unixepoch\")";}
		}
		/** @noinspection UnknownColumnInspection */
		$qb->selectRaw("
            count(*) as value,
            $date as date
        ")->whereNotNull($field)->groupBy(["date"]);
		$qb = static::addWhereClauses($qb, $params, $table);
		return $qb->getArray();
	}
	/**
	 * @param Builder $qb
	 * @param array $params
	 * @param string|null $tableOrAlias
	 * @return QMQB|Builder
	 */
	public static function addWhereClauses(Builder $qb, array $params,
	                                       string  $tableOrAlias = null): QMQB|Builder{
		$model = BaseModel::getInstanceByTable($qb->from);
		if(!$tableOrAlias){
			$tableOrAlias = $qb->from;
		}
		$fields = $model->getColumns();
		foreach($params as $key => $val){
			$isGlobal = QMArr::inArrayCaseInsensitive($key, QueryBuilderHelper::$GLOBAL_EXCLUDE_FROM_WHERE_PARAMS);
			if($isGlobal){
				continue;
			}
			$snake = QMStr::snakize($key);
			if(!in_array($snake, $fields)){
				if(str_starts_with($key, $qb->from.'_')){
					$snake = str_replace($qb->from . '_', '', $snake);
					if(!in_array($snake, $fields)){
						throw new BadRequestException("$snake field not found in $qb->from table");
					}
				}
			}
			$col = $tableOrAlias . '.' . $snake;
			if(stripos($val, QMArr::OPERATOR_GT) === 0){
				$whereValue = str_replace(QMArr::OPERATOR_GT, '', $val);
				$qb->where($col, ">", $whereValue);
			} elseif(stripos($val, QMArr::OPERATOR_LT) === 0){
				$whereValue = str_replace(QMArr::OPERATOR_LT, '', $val);
				$qb->where($col, "<", $whereValue);
			} elseif(stripos($val, QMArr::OPERATOR_NE) === 0){
				$whereValue = str_replace(QMArr::OPERATOR_NE, '', $val);
				$qb->where($col, "<>", $whereValue);
			} elseif(stripos($val, QMArr::OPERATOR_GTE) === 0){
				$whereValue = str_replace(QMArr::OPERATOR_GTE, '', $val);
				$qb->where($col, ">=", $whereValue);
			} elseif(stripos($val, QMArr::OPERATOR_LTE) === 0){
				$whereValue = str_replace(QMArr::OPERATOR_LTE, '', $val);
				$qb->where($col, "<=", $whereValue);
			} elseif(strtolower(str_replace(" ", "", $val)) === "notnull"){
				$qb->whereNotNull($col);
			} elseif(QMStr::isNullString($val)){
				$qb->whereNull($col);
			} else{
				$qb->where($col, $val);
			}
		}
		return $qb;
	}
	public static function count(string $TABLE, array $params = []): int{
		$TABLE = static::replaceTableAlias($TABLE);
		$qb = ReadonlyDB::getBuilderByTable($TABLE);
		$params['limit'] = 0;
		QueryBuilderHelper::addParams($qb, $params);
		return $qb->count();
	}
	/**
	 * @param string $TABLE
	 * @param array  $params
	 * @return array containing duration, result, and statement
	 * @noinspection SqlWithoutWhere
	 */
	public static function deleteStatic(string $TABLE, array $params = []): array{
		$where = static::paramsToWhereClauseString($params);
		$res = static::statementStatic("delete $TABLE from $TABLE where $where");
		return $res[0];
	}
	public static function paramsToWhereClauseString(array $params): string{
		$strings = [];
		foreach($params as $key => $value){
			if(strtolower($value) === "not null"){
				$strings[] = "$key is $value";
			} elseif(QMStr::isNullString($value)){
				$strings[] = "$key is $value";
			} elseif(str_contains($value, "%")){
                $like = static::like();
                $strings[] = $key . ' ' . $like . ' "' . $value . '"';
			} else{
				$strings[] = "$key = $value";
			}
		}
		return "where " . implode(" and ", $strings);
	}
	public static function getPrimaryKey(string $table): string{
		$res = static::selectStatic("SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'");
		if(!$res){
			throw new \RuntimeException("No primary key for $table");
		}
		return $res[0]->Column_name;
	}
	public static function paramsToHumanizedWhereClauseString(array $params, string $table): string{
		return QueryBuilderHelper::getHumanizedWhereClause($params, $table);
	}
	public static function routeToTable(string $route): string{
		$table = QMStr::before('/', $route, $route);
		$table = QMStr::snakize($table);
		$table = str_replace("_o_auth_", "_oauth_", $table);
		if($table === "users"){
			return User::TABLE;
		}
		if($table === "posts"){
			return WpPost::TABLE;
		}
		if($table === "clients"){
			return OAClient::TABLE;
		}
		if($table === "access_tokens"){
			return OAAccessToken::TABLE;
		}
		return $table;
	}
	public static function assertColumnExists(string $TABLE, string $field){
		$fields = static::getFieldsForTable($TABLE, false, true);
		if(!in_array($field, $fields)){
			le("$TABLE field $field not in " . \App\Logging\QMLog::print_r($fields, true));
		}
	}
	public static function getFieldsForTable(string $tableName, bool $camelCase = false, bool $useCache = true): array{
		return static::getAllColumnsForTable($tableName, $camelCase, $useCache);
	}
	public static function getWhereParamsFromSQL(string $sql): array{
		$builder = static::sqlToBuilder($sql);
		return $builder->wheres;
	}
	/**
	 * @param string $sql
	 * @return QMQB
	 */
	public static function sqlToBuilder(string $sql): QMQB{
		$q = static::parseSQL($sql);
		$table = static::getTableFromSQL($sql);
		$parts = $q->getParts();
		$whereStr = $parts['where'];
		$strings = explode(' and ', $whereStr);
		$operators = static::getOperators();
		$qb = Writable::getBuilderByTable($table);
		foreach($strings as $string){
			$string = trim(strtolower($string));
			foreach($operators as $operator){
				$ploded = explode($operator, $string);
				if(count($ploded) === 2){
					$qb->where($ploded[0], $operator, $ploded[1]);
					continue 2;
				} elseif(stripos($string, 'is null') !== false){
					$field = str_ireplace(' is null', '', $string);
					$qb->whereNull($field);
					continue 2;
				}
			}
		}
		return $qb;
	}
	public static function parseSQL(string $sql): Query{
		$sql = QMStr::after(QMQB::CALLER_PREFIX, $sql, $sql);
		$sql = QMStr::after(QMQB::CALLER_SUFFIX, $sql, $sql);
		return new Query($sql);
	}
	public static function getTableFromSQL(string $sql): string{
		return QMQueryExecuted::getTableFromSQL($sql);
	}
	public static function getOperators(): array{
		return (new Builder(Writable::db()))->operators;
	}
	public static function stripPrefixes(string $search): string{
		return QMStr::removeDBPrefixes($search);
	}
	public static function dbTypeToPhpType(string $dbType): string{
		foreach(\App\CodeGenerators\ModelGenerator\Meta\MySql\Column::$mappings as $phpType => $database){
			if($dbType === $phpType){
				return $phpType;
			}
			if(in_array($dbType, $database)){
				return $phpType;
			}
		}
		le("please define php type for db type $dbType");
	}
	public static function phpTypeToPossibleMySQLTypes(string $needle): array{
		if(!isset(\App\CodeGenerators\ModelGenerator\Meta\MySql\Column::$mappings[$needle])){
			le("please define DB type for db type $needle");
		}
		return \App\CodeGenerators\ModelGenerator\Meta\MySql\Column::$mappings[$needle];
	}
	public static function logTableSizes(){
		$bySize = static::getTableSizesInMbDescending();
		$associative = [];
		foreach($bySize as $table => $size){
			$associative[$table] = ['Table' => $table, 'Size (MB)' => $size];
			ConsoleLog::info("'$table' => $size,");
		}
		QMLog::table($associative, "Tables by Size in MB");
	}
	public static function getTablesLargerThan(float $mb): array{
		$bySize = static::getTableSizesInMbDescending();
		$associative = [];
		foreach($bySize as $table => $size){
			if($size < $mb){
				continue;
			}
			$associative[$table] = ['Table' => $table, 'Size (MB)' => $size];
			ConsoleLog::info("'$table' => $size,");
		}
		return $associative;
	}
	public static function getViewNames(): array{
		$your_database = static::getDBName();
		/** @noinspection SqlResolve */
		$res = static::selectStatic('
            SELECT table_name view_name
            FROM information_schema.tables
            WHERE
                table_type   = \'VIEW\'
                AND table_schema = "' . $your_database . '"
          ');
		$list = [];
		foreach($res as $obj){
			$list = floatval($obj->view_name);
			ConsoleLog::info("'$obj->view_name',");
		}
		sort($list);
		return $list;
	}
	public static function getConfigArray(): array{
		$arr = [
			'driver' => static::getDBDriverName(),
			'host' => static::getHost(),
			'database' => static::getDbName(),
			'username' => static::getUser(),
			'password' => static::getPassword(),
			'charset' => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix' => '',
			'strict' => false,
			'engine' => null,
			'port' => static::DB_PORT,
			'options' => [],
		];
		$arr = array_filter($arr);
		return $arr;
	}
	/**
	 * @param string $table
	 * @return array|Collection
	 */
	public static function getAllFromTable(string $table): array|Collection{
		if($mem = Memory::getByPrimaryKey(static::getConnectionName() . '-' . $table)){
			return $mem;
		}
		$posts = static::getBuilderByTable($table)->get();
		Memory::addByPrimaryKey(static::getConnectionName() . '-' . $table, $posts);
		return $posts;
	}
	/**
	 * @param QMDB|string $sourceDBClass
	 * @param QMDB|string $destDBClass
	 * @param string $table
	 */
	public static function copyTable(string|QMDB $sourceDBClass, string|QMDB $destDBClass, string $table){
		$table = $sourceDBClass::getDBTable($table);
		$table->copy($destDBClass);
	}
	/**
	 * @param QMDB|string $sourceDBClass
	 * @param QMDB|string $destDBClass
	 * @param string $table
	 */
	public static function copyAndUploadTable(string|QMDB $sourceDBClass, string|QMDB $destDBClass, string $table){
		$table = $sourceDBClass::getDBTable($table);
		$table->copyAndUpload($destDBClass);
	}
	/**
	 * @param QMDB|string $sourceDBClass
	 * @param QMDB|string $destDBClass
	 */
	public static function copyDB(string|QMDB $sourceDBClass, string|QMDB $destDBClass){
		$paths = $sourceDBClass::dumpTablesToJson();
		$destDBClass::importTablesFromJson($paths);
	}
	public static function dumpTablesToJson(): array{
		$tables = static::getTableNames();
		$paths = [];
		foreach($tables as $table){
			$paths[] = static::dumpTableToJson($table);
		}
		return $paths;
	}
	public static function dumpTableToJson(string $name, string $folder = null): string{
		$table = static::getDBTable($name);
		return $table->dumpToJson($folder);
	}
	public static function importTablesFromJson(array $paths): array{
		foreach($paths as $path){
			static::importTableFromJson($path);
		}
		return $paths;
	}
	/**
	 * @param string $path
	 */
	public static function importTableFromJson(string $path): void{
		$dbName = static::getDbName();
		if(stripos($path, 'test') !== false && stripos($dbName, 'test') === false){
			le("Can't load $path into $dbName");
		}
		FileHelper::validateExistence($path);
		try {
			FileHelper::validateNotEmpty($path);
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
        //static::db()->unprepared(file_get_contents($path));
		$cmd = "$dbName < $path";
        $arr = JsonFile::getArray($path);
        $file = JsonFile::find($path);
        $table = $file->getFilenameWithoutExtension();
        static::db()->table($table)->insert($arr);
		QMLog::immediately($cmd);
		Memory::flush(); // Avoid getting stuff from memory that's not in DB
	}
	/**
	 * @param string $folder
	 */
	public static function importFromFolder(string $folder){
		$paths = FileFinder::listFiles($folder);
		static::importTablesFromJson($paths);
	}
	/**
	 * @param QMDB|string $sourceDBClass
	 * @param QMDB|string $destDBClass
	 */
	public static function copyWPTables(QMDB|string $sourceDBClass, QMDB|string $destDBClass){
		$tables = $sourceDBClass::dumpWPTables();
		$destDBClass::importTablesFromJson($tables);
	}
	public static function dumpWPTables(): array{
		$names = static::getWPTableNames();
		$paths = [];
		foreach($names as $name){
			$table = static::getDBTable($name);
			$paths[] = $table->dumpToJson();
		}
		return $paths;
	}
	public static function getWPTableNames(): array{
		return static::getAllTableNamesLike('wp_');
	}
	/**
	 * @param string $needle
	 * @return array
	 */
	public static function getAllTableNamesLike(string $needle): array{
		$tables = static::getTableNames();
		return Arr::where($tables, function($table) use ($needle){
			return stripos($table, $needle) !== false;
		});
	}
	public static function dumpBaseModelTables(): array{
		$tables = static::getBaseModelTableNames();
		$paths = [];
		foreach($tables as $table){
			$paths[] = QMDB::dumpTableToJson($table);
		}
		return $paths;
	}
	public static function getBaseModelTableNames(): array{
		if(static::$baseModelTables){
			return static::$baseModelTables;
		}
		$classes = BaseModel::getClasses();
		$tables = [];
		foreach($classes as $class){
			if(defined($class . '::TABLE')){
				if(!$class::TABLE){
					continue;
				}
				$tables[] = $class::TABLE;
			}
		}
		$tables = collect($tables)->sortByDesc(function($string){
			return strlen($string);
		})->all();
		return static::$baseModelTables = $tables;
	}
	public static function getDBClasses(): array{
		return FileHelper::listClassesInNamespace(__NAMESPACE__);
	}
	public static function tableExists(string $table): bool{
		return in_array($table, static::getTableNames());
	}
	/**
	 * @param string $destDBClass
	 * @return DBTable[]
	 * Copy the largest tables first, so we don't miss new users created while copying measurements
	 */
	public static function copyTablesByDescendingSize(string $destDBClass): array{
		$tables =
			static::getTablesByDescendingSize(); // Do largest tables first so we don't miss new users created while copying measurements
		foreach($tables as $table){
			$table->copy($destDBClass);
		}
		return $tables;
	}
	/**
	 * @return DBTable[]
	 */
	public static function getTablesByDescendingSize(): array{
		$bySize = [];
		$names = static::getTableSizesInMbDescending();
		foreach($names as $name => $size){
			$bySize[] = static::getDBTable($name);
		}
		return $bySize;
	}
	public static function getTablesLike(string $needle): array{
		$names = static::getTableNames();
		$like = [];
		foreach($names as $name){
			if(str_contains($name, $needle)){
				$like[] = $name;
			}
		}
		return $like;
	}
	public static function dumpTableStructuresLike(string $needle){
		$tables = Writable::getTablesStartingWith($needle);
		foreach($tables as $table){
			Writable::dumpTableStructureToSQL($table);
		}
	}
	public static function getTablesStartingWith(string $needle): array{
		$names = static::getTableNames();
		$like = [];
		foreach($names as $name){
			if(str_starts_with($name, $needle)){
				$like[] = $name;
			}
		}
		return $like;
	}
	public static function dumpTableStructureToSQL(string $name, string $folder = null): string{
		$table = static::getDBTable($name);
		return $table->dumpStructure($folder);
	}
	public static function logTablesWithColumn(string $col){
		$tables = static::getTableNamesWithColumn($col);
		QMLog::print($tables, "Tables with $col");
	}

    /**
     * @return string
     */
    public static function like(): string
    {
        if(static::isPostgres()){
            return 'ilike';
        }
        return 'LIKE';
    }

    /**
	 * @param string $dbUrl
	 * @return string
	 */
	protected static function getHostIpFromUrl(string $dbUrl): string{
		$urlComponents = parse_url($dbUrl);
		$dbHost = $urlComponents['host'];
		return $dbHost;
	}

    public static function getTableStatic(string $string): QMQB
    {
        return static::db()->table($string);
    }

    /**
	 * @return string
	 * @noinspection PhpUnusedPrivateMethodInspection
	 */
	private static function getSslCa(): string{
		return FileHelper::absPath('configs/docker/mysql-5.7/conf.d/ca.pem');
	}

    /**
     * @return Connection|\Illuminate\Database\ConnectionInterface
     */
    protected static function getConnectionStatically(): \Illuminate\Database\ConnectionInterface|Connection{
	    $name = static::getConnectionName();
	    return DB::connection($name);
    }

    public static function isPostgres(): bool
    {
        return static::getConnectionStatically()->getDriverName() === 'pgsql';
    }
	/** @noinspection PhpUnusedPrivateMethodInspection */
	public static function isMySQL(): bool
    {
        return static::getConnectionStatically()->getDriverName() === 'mysql';
    }

    public static function isSQLite(): bool
    {
	    $driverName = static::getConnectionStatically()->getDriverName();
	    return $driverName === 'sqlite';
    }
	/**
	 * @param QMDB|Connection|null $c
	 * @param string $table
	 * @return void
	 */
	private static function disableTableTriggers(QMDB|Connection|null $c, string $table): void{
		try {
			$c->statement("ALTER TABLE $table DISABLE TRIGGER ALL;");
		} catch (\Throwable $e) {
			ConsoleLog::error("Failed to disable triggers on $table because: ".$e->getMessage());
			static::$hasTableTriggerPermission = false;
		}
	}
	/**
	 * @param QMDB|Connection|null $c
	 * @return void
	 */
	private static function setReplicationRoleToReplica(QMDB|Connection|null $c): void{
		if(!$c){$c = static::db();}
		try {
			$c->statement("SET session_replication_role = 'replica';");
		} catch (\Throwable $e) {
			ConsoleLog::error(__METHOD__.": ".$e->getMessage());
			static::$hasReplicationRolePermission = false;
		}
	}
	/**
	 * @param QMDB|Connection|null $c
	 * @return void
	 */
	private static function setSessionReplicationRoleOrigin(QMDB|Connection|null $c = null): void{
		if(!$c){$c = static::db();}
		try {
			$c->statement("SET session_replication_role = 'origin';");
			return;
		} catch (\Throwable $e) {
			ConsoleLog::error(__METHOD__.": ".$e->getMessage());
			static::$hasReplicationRolePermission = false;
			return;
		}
	}
	/**
	 * @param QMDB|Connection|null $c
	 * @param string|null $table
	 * @return void
	 */
	private static function enableTableTriggers(QMDB|Connection|null $c, ?string $table): void{
		try {
			$c->statement("ALTER TABLE $table ENABLE TRIGGER ALL;");
		} catch (\Throwable $e) {
			ConsoleLog::error("Failed to ENABLE triggers on $table because: ".$e->getMessage());
		}
	}
	public function listTableDetails(string $name): \Doctrine\DBAL\Schema\Table{
		$sm = $this->getDoctrineSchemaManager();
		return $sm->listTableDetails($name);
	}
	public static function getLastModifiedAt(): ?string {
        //return null;
		$name = static::getDbName();
        if(static::isPostgres()){
            $connection = static::db();
            $qb = $connection->table('sessions');
            $qb = $qb->orderBy('last_activity', 'desc');
            try {
                $latest = $qb->first();
            } catch (\Throwable $e) {
                QMLog::error(__METHOD__.": ".$e->getMessage());
                return null;
            }
            if($latest){
                return db_date($latest->last_activity);
            }
            $qb = $connection->table(User::TABLE);
            $qb = $qb->orderBy(User::FIELD_UPDATED_AT, 'desc');
            $latest = $qb->first();
            if(!$latest){
                return null;
            }
            return db_date($latest->updated_at);
        } else {
            $stmt = "SELECT MAX(UPDATE_TIME) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ;";
        }
		//$out = ThisComputer::execLocal(' mysql -e "use '.$name.'; '.$stmt.'"');
        try {
            $out = Writable::statementStatic($stmt);
        } catch (QueryException $e) {
            return null;
        }
		//$lines = QMStr::explodeNewLines($p->getOutput());
		$at = $out[0]["result"][0]->{'MAX(UPDATE_TIME)'};
		if(!$at || $at === "NULL"){
			ConsoleLog::info("DB $name NEVER UPDATED BEFORE");
			return null;
		}
		$ago = TimeHelper::timeSinceHumanString($at);
		ConsoleLog::info("DB $name last UPDATE $ago");
		return $at;
	}
	/**
	 * @param $query
	 * @param array $bindings
	 * @return bool
	 */
	public function statement($query, $bindings = []): bool{
		$start = microtime(true);
		QMLog::info($query, $bindings);
		$res = parent::statement($query, $bindings);
		// I think this is already done self::logQuery($query, $bindings, microtime(true) - $start);
		return $res;
	}
	public static function findTableByName(string $TABLE): ?DBTable{
		foreach(static::getDBTables() as $table){
			if($table->getName() === $TABLE){
				return $table;
			}
		}
		return null;
	}
    public static function generateAmplicationModels(): array
    {
        static::cloneAmplication();
        // https://github.com/amplication/amplication/blob/master/packages/amplication-cli/README.md
        $tables = static::getTableNames();
        $EXCLUDE_TABLES = [];
        $tables = array_filter($tables, function($table) use ($EXCLUDE_TABLES) {
            return !in_array($table, $EXCLUDE_TABLES);
        });
        $displayNames = array_map(function($table){
            return Str::studly($table);
        }, $tables);
        foreach ($displayNames as $name){
            static::amplication("generate model $name");
        }
        return $displayNames;
    }
    private static function amplication(string $cmd): string{
        $dir = base_path('amplication');
        return ThisComputer::exec("cd $dir && amp ". $cmd, true);
    }
    private static function cloneAmplication(): void{
        $dir = base_path('amplication');
        ThisComputer::exec("git clone https://github.com/amplication/amplication.git $dir || true");
        ThisComputer::exec("cd $dir && git pull", true);
        ThisComputer::npm('install', true);
        $token = static::amplication("auth ". \App\Utils\Env::get('AMPLICATION_TOKEN'));
    }
    public static function isTesting(): bool
    {
        $config = DB::connection()->getConfig();
        return str_contains($config["database"], 'test') || str_contains($config["database"], ':memory:');
    }
	public static function isStaging(): bool
	{
		$config = DB::connection()->getConfig();
		return str_contains($config["database"], 'staging');
	}
	public static function isTestingOrStaging(): bool
	{
		return self::isStaging() || self::isTesting();
	}
	public static function createPostgresReplicationUser(string $user)
	{
		$db = static::getDbName();
		$schema = static::getSchemaName();
		static::statementStatic("
GRANT cloudsqlimportexport, cloudsqlsuperuser, pg_read_all_data, pg_write_all_data, postgres TO replicator WITH ADMIN OPTION;

GRANT ALL PRIVILEGES ON DATABASE $db TO replicator WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON DATABASE postgres TO replicator WITH GRANT OPTION;

GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA $schema TO replicator with grant option;
GRANT USAGE ON SCHEMA $schema TO replicator with grant option;

GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA $schema TO replicator with grant option;

ALTER DEFAULT PRIVILEGES IN SCHEMA $schema GRANT ALL PRIVILEGES ON TABLES TO replicator with grant option;
ALTER DEFAULT PRIVILEGES IN SCHEMA $schema GRANT ALL PRIVILEGES ON SEQUENCES TO replicator with grant option;
		");
	}
	public static function syncDemoData(){
		QMLogLevel::setDebug();
		$syncer = new DemoDatabaseSynchronizer(ProductionDB::db(), static::db());
		$syncer->sync();
	}
	/**
	 * @return \App\Storage\DB\DBTable[]
	 */
	public function getTablesWithUserIds(): array {
		$tables = static::getDBTables();
		$tablesWithUserIds = [];
		foreach($tables as $table){
			if($table->hasUserIdColumn()){
				$tablesWithUserIds[$table->getName()] = $table;
			}
		}
		return $tablesWithUserIds;
	}
	/**
	 * @param \App\Storage\DB\QMDB|null $db
	 * @return void
	 */
	public static function updateAllAutoIncrementSequences(): void{
		static::statementStatic('
SELECT \'SELECT SETVAL(\' ||
       quote_literal(quote_ident(PGT.schemaname) || \'.\' || quote_ident(S.relname)) ||
       \', COALESCE(MAX(\' ||quote_ident(C.attname)|| \'), 1) ) FROM \' ||
       quote_ident(PGT.schemaname)|| \'.\'||quote_ident(T.relname)|| \';\'
FROM pg_class AS S,
     pg_depend AS D,
     pg_class AS T,
     pg_attribute AS C,
     pg_tables AS PGT
WHERE S.relkind = \'S\'
    AND S.oid = D.objid
    AND D.refobjid = T.oid
    AND D.refobjid = C.attrelid
    AND D.refobjsubid = C.attnum
    AND T.relname = PGT.tablename
ORDER BY S.relname;            
            
            ');
	}
	public static function populateDocsTable(): void {
		$models = BaseModel::getNonAbstractModelsWithTables();
		$json = $allTableDocs = [];
		$noDescription = [];
		foreach($models as $model){
			//$table = $model->getDBTable();
			//$tableDocs = $table->addCommentsToDocsTable($model);
			//$allTableDocs = array_merge($tableDocs, $allTableDocs);
			$properties = $model->getPropertyModels();
			foreach($properties as $property){
				if($property->description === $property->name){
					$col = $property->getDBColumn();
					$property->description = $col->getComment();
				}
				if(!$property->description){
					if(!isset($noDescription[$property->name])){
						$noDescription[$property->name] = 0;
					}
					$noDescription[$property->name]++;
				}
				$arr = json_decode(json_encode($property), true);
				foreach($arr as $key => $value){
					if($value === null || $value === ''){
						unset($arr[$key]);
					}
				}
				$tableName = $model->getTable();
				$tableName = DBTable::toDisplayName($tableName);
				$arr['table'] = $tableName;
				foreach(DBTable::TABLE_ALIASES as $alias => $table){
					$str = str_replace($table, $alias, json_encode($arr));
					$table = ucfirst(QMStr::camelize($table));
					$alias = ucfirst(QMStr::camelize($alias));
					$str = str_replace($table, $alias, json_encode($arr));
					$arr = json_decode($str);
				}
				$json[] = $arr;
			}
		}
		JsonFile::write(self::getPropertyModelJsonPath(), $json);

		JsonFile::write(base_path('data/noDescription.json'), $noDescription);
		//JsonFile::write(base_path('data/docs_models.json'), $allTableDocs);
	}
}
