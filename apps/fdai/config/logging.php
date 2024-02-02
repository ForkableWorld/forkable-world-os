<?php
use App\Logging\QMLogLevel;
use Monolog\Handler\StreamHandler;
return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => [
                // Permission denied 'nginx',
                'single', // Needed for PimpMyLog
                //'daily',
                'stderr', // Needed for Heroku
                'bugsnag',
                //'slack',
            ],
            'ignore_exceptions' => false,
        ],

        'nginx' => [
            'driver' => 'single',
            'path' => '/var/log/nginx/error.log',
            'level' => env('LOG_LEVEL', QMLogLevel::DEFAULT),
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', QMLogLevel::DEFAULT),
	        'permission' => 0664,
	        'locking' => false,
	        'bubble' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', QMLogLevel::DEFAULT),
            'days' => 14,
	        'permission' => 0664,
	        'locking' => false,
	        'bubble' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', QMLogLevel::DEFAULT),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', QMLogLevel::DEFAULT),
        ],

        'bugsnag' => [
            'driver' => 'bugsnag',
        ],
	    
        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],
	    'stderr' => [
		    'driver' => 'monolog',
		    'handler' => StreamHandler::class,
		    'with' => [
			    'stream' => 'php://stderr',
		    ],
	    ],
    ],

    'query' => [
        'enabled' => true, // used in \App\Providers\DBQueryLogServiceProvider()
        'slow_query_seconds' => 5 // seconds
    ],

];
