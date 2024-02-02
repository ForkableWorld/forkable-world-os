<?php
use App\DataSources\Connectors\FacebookConnector;
use App\DataSources\Connectors\GithubConnector;
use App\DataSources\Connectors\GoogleLoginConnector;
use App\DataSources\Connectors\LinkedInConnector;
use App\DataSources\Connectors\RescueTimeConnector;
use App\DataSources\Connectors\TwitterConnector;
return [

    'github' => GithubConnector::getServiceConfig(),

    'facebook' => FacebookConnector::getServiceConfig(),

    'fcm' => [
        'key' => env('GOOGLE_CLOUD_MESSAGING_API_KEY')
    ],

    'google' => GoogleLoginConnector::getServiceConfig(),

    'linkedin' => LinkedInConnector::getServiceConfig(),

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'rescuetime' => RescueTimeConnector::getServiceConfig(),

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_API_PUBLIC'),
        'secret' => env('STRIPE_API_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'twitter' => TwitterConnector::getServiceConfig(),

];
