<?php
use App\Storage\DB\BackupDB;
use App\Storage\DB\ClinicalTrialsDB;
use App\Storage\DB\DemoMySQLDB;
use App\Storage\DB\DemoSQLiteDB;
use App\Storage\DB\DOProduction;
use App\Storage\DB\DOStaging;
use App\Storage\DB\GlobalDataDB;
use App\Storage\DB\ProductionDB;
use App\Storage\DB\ProductionPgGcpDB;
use App\Storage\DB\QMDB;
use App\Storage\DB\QMGoDaddyDB;
use App\Storage\DB\StagingDB;
use App\Storage\DB\TBNDigitalOceanDB;
use App\Storage\DB\TBNGoDaddyDB;
use App\Storage\DB\TdddDB;
use App\Storage\DestinationDB;
use App\Storage\SourceDB;
use App\Storage\UnifiedHealthApiDB;
use Illuminate\Support\Str;
return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE') === ':memory:' ? ':memory:' : 
	            abs_path(env('DB_DATABASE', 'database/qm_test_db.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'dump' => [
                //'dump_binary_path' => '/path/to/the/binary', // only the path, so without `mysqldump` or `pg_dump`
                'use_single_transaction',
                'timeout' => 60 * 5, // 5 minute timeout
                'exclude_tables' => array_merge(
                    QMDB::LARGE_TABLES,
                    QMDB::VIEWS
                )
                //'add_extra_option' => '--optionname=optionvalue',
            ],

            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_USE_UTF8MB4', true) ? 'utf8mb4' : 'utf8',
            'collation' => env('DB_USE_UTF8MB4', true) ? 'utf8mb4_unicode_ci' : 'utf8_unicode_ci',
            'prefix' => env('DB_PREFIX', ''),
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') && env('DB_HOST') && str_contains(env('DB_HOST'), 'rds') ? [
                PDO::MYSQL_ATTR_SSL_CA => abs_path('config/ssl/rds-combined-ca-bundle.pem'),
            ] : [],
        ],

        'testing' => [
            'driver' => env('DB_DRIVER', 'sqlite'),
            'host' => env('DB_HOST'),
            'unix_socket' => env('DB_UNIX_SOCKET', ''),
            'database' => env('DB_DATABASE', ':memory:'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => env('DB_USE_UTF8MB4', true) ? 'utf8mb4' : 'utf8',
            'collation' => env('DB_USE_UTF8MB4', true) ? 'utf8mb4_unicode_ci' : 'utf8_unicode_ci',
            'prefix' => env('DB_PREFIX', ''),
            'prefix_indexes' => true,
            'strict' => false,
        ],

        'pgsql_testing' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_HOST', 'postgres'),
            'port' => env('DB_TEST_PORT', '5432'),
            'database' => env('DB_TEST_DATABASE', 'quantimodo_test'),
            'username' => env('DB_TEST_USERNAME', 'postgres'),
            'password' => env('DB_TEST_PASSWORD', 'secret'),
	        'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => env('DB_TEST_SCHEMA', 'quantimodo_test'),
        ],

        BackupDB::CONNECTION_NAME => BackupDB::getConfigArray(),

        QMGoDaddyDB::CONNECTION_NAME => QMGoDaddyDB::getConfigArray(),
	    ProductionPgGcpDB::CONNECTION_NAME => ProductionPgGcpDB::getConfigArray(),

        'clockwork' => [ // Has wp_ prefix needed by Corcel
            'driver'    => 'mysql',
            'host'      => 'r5-large-cluster.cluster-corrh0fp2kuj.us-east-1.rds.amazonaws.com',
            'database'  => 'clockwork',
            'username'  => 'clockwork',
            'password'  => env('CLOCKWORK_DB_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
            'engine'    => null,
            'port'      => 3306,
            'options'   => extension_loaded('pdo_mysql') && env('DB_HOST') && str_contains(env('DB_HOST'), 'rds') ? [
                PDO::MYSQL_ATTR_SSL_CA => abs_path('config/ssl/rds-combined-ca-bundle.pem'),
            ] : [],
        ],


        TBNDigitalOceanDB::CONNECTION_NAME => TBNDigitalOceanDB::getConfigArray(),

        TBNGoDaddyDB::CONNECTION_NAME      => TBNGoDaddyDB::getConfigArray(),

        DOStaging::CONNECTION_NAME         => DOStaging::getConfigArray(),

        DOProduction::CONNECTION_NAME      => DOProduction::getConfigArray(),
	    DemoSQLiteDB::CONNECTION_NAME      => DemoSQLiteDB::getConfigArray(),
	    DemoMySQLDB::CONNECTION_NAME      => DemoMySQLDB::getConfigArray(),

        ProductionDB::CONNECTION_NAME     => ProductionDB::getConfigArray(),

        StagingDB::CONNECTION_NAME         => StagingDB::getConfigArray(),

	    GlobalDataDB::CONNECTION_NAME         => GlobalDataDB::getConfigArray(),

	    TdddDB::CONNECTION_NAME         => TdddDB::getConfigArray(),

        ClinicalTrialsDB::CONNECTION_NAME  => ClinicalTrialsDB::getConfigArray(),

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'postgres'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'postgres'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', 'secret'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => env('DB_SCHEMA', 'cd_testing'),
            'sslmode' => 'prefer',
        ],

        'supabase' => [
            'driver' => 'pgsql',
            //'url' => env('SUPABASE_URL'),
            'host' => env('SUPABASE_HOST', 'db.cbdvqiqgmpdcuvtoehvg.supabase.co'),
            'port' => env('SUPABASE_PORT', 6543),
            'database' => 'postgres',
            'username' => env('SUPABASE_USERNAME', 'postgres'),
            'password' => env('SUPABASE_PASS'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

	    UnifiedHealthApiDB::CONNECTION_NAME => [
		    'driver' => 'pgsql',
		    'url' => env('UNIFIED_HEALTH_API_DATABASE_URL', env('DATABASE_URL')),
		    'host' => env('UNIFIED_HEALTH_API_DB_HOST', env('DB_HOST', '127.0.0.1')),
		    'port' => env('UNIFIED_HEALTH_API_DB_PORT', env('DB_PORT', '5432')),
		    'database' => env('UNIFIED_HEALTH_API_DB_DATABASE', env('DB_DATABASE', 'forge')),
		    'username' => env('UNIFIED_HEALTH_API_DB_USERNAME', env('DB_USERNAME', 'forge')),
		    'password' => env('UNIFIED_HEALTH_API_DB_PASSWORD', env('DB_PASSWORD', '')),
		    'charset' => 'utf8',
		    'prefix' => '',
		    'prefix_indexes' => true,
		    'schema' => env('UNIFIED_HEALTH_API_DB_SCHEMA', 'unified_health_api'),
		    'sslmode' => 'prefer',
	    ],


	    SourceDB::CONNECTION_NAME => [
		    'driver' => 'mysql',
		    'url' => env('SOURCE_DATABASE_URL'),
		    'host' => env('SOURCE_DB_HOST', '127.0.0.1'),
		    'port' => env('SOURCE_DB_PORT', '3306'),
		    'database' => env('SOURCE_DB_DATABASE', 'forge'),
		    'username' => env('SOURCE_DB_USERNAME', 'forge'),
		    'password' => env('SOURCE_DB_PASSWORD', ''),
		    'unix_socket' => env('SOURCE_DB_SOCKET', ''),
		    'charset' => 'utf8mb4',
		    'collation' => 'utf8mb4_unicode_ci',
		    'prefix' => '',
		    'prefix_indexes' => true,
		    'strict' => true,
		    'engine' => null,
		    'options' => extension_loaded('pdo_mysql') && env('DB_HOST') && str_contains(env('DB_HOST'), 'rds') ? [
                  PDO::MYSQL_ATTR_SSL_CA => abs_path('config/ssl/rds-combined-ca-bundle.pem'),
              ] : [],
	    ],

	    DestinationDB::CONNECTION_NAME => [
		    'driver' => 'pgsql',
		    'url' => env('DESTINATION_DATABASE_URL'),
		    'host' => env('DESTINATION_DB_HOST'),
		    'port' => env('DESTINATION_DB_PORT', '5432'),
		    'database' => env('DESTINATION_DB_DATABASE'),
		    'username' => env('DESTINATION_DB_USERNAME'),
		    'password' => env('DESTINATION_DB_PASSWORD', ''),
		    'charset' => 'utf8',
		    'prefix' => '',
		    'prefix_indexes' => true,
		    'schema' => env('DESTINATION_DB_SCHEMA'),
		    'sslmode' => 'prefer',
	    ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'cluster' => false,

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
		    'parameters' => ['password' => env('REDIS_PASSWORD', null)],
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDISHOST', env('REDIS_HOST', '127.0.0.1')),
	        // https://console.cloud.google.com/run/detail/us-central1/hldata/integrations?project=curedao
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDISPORT', env('REDIS_PORT', '6379')), 
	        // https://console.cloud.google.com/run/detail/us-central1/hldata/integrations?project=curedao
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
	        'host' => env('REDISHOST', env('REDIS_HOST', '127.0.0.1')),
            'password' => env('REDIS_PASSWORD', null),
	        'port' => env('REDISPORT', env('REDIS_PORT', '6379')),
	        'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
