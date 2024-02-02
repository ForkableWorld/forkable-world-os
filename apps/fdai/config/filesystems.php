<?php

use App\DevOps\Jenkins\Jenkins;
use App\Storage\S3\S3PrivateGlobal;
use App\Storage\S3\S3Private;
use App\Storage\S3\S3Public;
$timeout = 60;
return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

	    Jenkins::JENKINS => [
		    'driver' => 'local',
		    'root' => Jenkins::JENKINS_HOME_FOLDER,
	    ],

        'tmp' => [
            'driver' => 'local',
            'root' => dirname(__DIR__, 1).'/tmp',
        ],

        'base' => [
            'driver' => 'local',
            'root' => base_path(),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        'minio' => [
            'driver' => 's3',
            'key' => env('STORAGE_ACCESS_KEY_ID'),
            'secret' => env('STORAGE_SECRET_ACCESS_KEY'),
            'endpoint' => env('STORAGE_ENDPOINT'),
            'region' => env('STORAGE_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('STORAGE_BUCKET'),
            'use_path_style_endpoint' => true,
            'options' => [
                'override_visibility_on_copy' => 'private',
            ]
        ],

        S3Private::DISK_NAME => S3Private::getConfig(),

	    S3PrivateGlobal::DISK_NAME => S3PrivateGlobal::getConfig(),

        S3Public::DISK_NAME  => S3Public::getConfig(),

        's3' => [
            'driver' => 's3',
            'key' => env('STORAGE_ACCESS_KEY_ID'),
            'secret' => env('STORAGE_SECRET_ACCESS_KEY'),
            'region' => env('STORAGE_DEFAULT_REGION'),
            'bucket' => env('STORAGE_BUCKET'),
            'url' => env('STORAGE_URL'),
            'endpoint' => env('STORAGE_ENDPOINT'),
            'use_path_style_endpoint' => env('STORAGE_USE_PATH_STYLE_ENDPOINT', false),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
