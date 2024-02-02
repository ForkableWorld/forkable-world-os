<?php
use App\Providers\DBQueryLogServiceProvider;
use App\Providers\EloquentWherelikeServiceProvider;
use App\Providers\GuzzleClockworkServiceProvider;
use App\Providers\SolutionServiceProvider;
use Clockwork\Support\Laravel\ClockworkServiceProvider;

$config = [
    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */
    'name' => env('APP_NAME', 'Awesome FDA'),
    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */
    'env' => env('APP_ENV', 'production'),
    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */
    'debug' => env('APP_DEBUG', false),
    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    |  DO NOT INCLUDE TRAILING SLASHES HERE!!!
    */
    'url' => env('APP_URL',  getenv('APP_URL')),
    'asset_url' => env('ASSET_URL', getenv('APP_URL')),
    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */
    'timezone' => 'UTC',
    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */
    'locale' => 'en',
    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */
    'fallback_locale' => 'en',
    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */
    'faker_locale' => 'en_US',
    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */
    'aliases' => [
        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'Date' => Illuminate\Support\Facades\Date::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Http' => Illuminate\Support\Facades\Http::class,
        'Js' => Illuminate\Support\Js::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'RateLimiter' => Illuminate\Support\Facades\RateLimiter::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,
        /*
         * Package Facades...
         */
        'Form' => Collective\Html\FormFacade::class,
        'Html' => Collective\Html\HtmlFacade::class,
        'Analytics' => Ipunkt\LaravelAnalytics\AnalyticsFacade::class,
        'Debugbar' => \Barryvdh\Debugbar\Facades\Debugbar::class,  // Why was this commented?
        'Bugsnag' => Bugsnag\BugsnagLaravel\Facades\Bugsnag::class,
        'Flash'     => Laracasts\Flash\Flash::class,
        'DataTables' => Yajra\DataTables\Facades\DataTables::class,
        'MetaTag'   => Torann\LaravelMetaTags\Facades\MetaTag::class,
        'Alert'   => RealRashid\SweetAlert\Facades\Alert::class,
        //'FormBuilder'   => Kris\LaravelFormBuilder\Facades\FormBuilder::class,
        //'Batch' => Mavinoo\Batch\BatchFacade::class,
        //'XmlParser' => Orchestra\Parser\Xml\Facade::class,

    ],
];
/*
|--------------------------------------------------------------------------
| Autoloaded Service Providers
|--------------------------------------------------------------------------
|
| The service providers listed here will be automatically loaded on the
| request to your application. Feel free to add your own services to
| this array to grant expanded functionality to your applications.
|
*/
$config['providers'] = [
	/*
	 * Laravel Framework Service Providers...
	 */
	Illuminate\Auth\AuthServiceProvider::class,
	Illuminate\Broadcasting\BroadcastServiceProvider::class,
	Illuminate\Bus\BusServiceProvider::class,
	Illuminate\Cache\CacheServiceProvider::class,
	Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
	Illuminate\Cookie\CookieServiceProvider::class,
	Illuminate\Database\DatabaseServiceProvider::class,
	Illuminate\Encryption\EncryptionServiceProvider::class,
	Illuminate\Filesystem\FilesystemServiceProvider::class,
	Illuminate\Foundation\Providers\FoundationServiceProvider::class,
	Illuminate\Hashing\HashServiceProvider::class,
	Illuminate\Mail\MailServiceProvider::class,
	Illuminate\Notifications\NotificationServiceProvider::class,
	Illuminate\Pagination\PaginationServiceProvider::class,
	Illuminate\Pipeline\PipelineServiceProvider::class,
	Illuminate\Queue\QueueServiceProvider::class,
	Illuminate\Redis\RedisServiceProvider::class,
	Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
	Illuminate\Session\SessionServiceProvider::class,
	Illuminate\Translation\TranslationServiceProvider::class,
	Illuminate\Validation\ValidationServiceProvider::class,
	Illuminate\View\ViewServiceProvider::class,
	/*
	 * Package Service Providers (MAKE SURE TO PUT NEW ONES BEFORE Application Service Providers)
	 */
	Bugsnag\BugsnagLaravel\BugsnagServiceProvider::class,
	Collective\Html\HtmlServiceProvider::class,
	Ipunkt\LaravelAnalytics\AnalyticsServiceProvider::class,
	Laravel\Cashier\CashierServiceProvider::class,
	//Eloquence\EloquenceServiceProvider::class,  // What was this for?  I uninstalled kirkbushell/eloquence because it makes PHPStorm suggests stupid LogicException
	Yajra\DataTables\DataTablesServiceProvider::class,
	Yajra\DataTables\ButtonsServiceProvider::class,
	//Yajra\Datatables\HtmlServiceProvider::class,
	//Appointer\Swaggervel\SwaggervelServiceProvider::class,
	Torann\LaravelMetaTags\MetaTagsServiceProvider::class,
	//Barryvdh\Debugbar\ServiceProvider::class, // NOTE: Add development-only providers in \App\Providers\AppServiceProvider::register()
	RealRashid\SweetAlert\SweetAlertServiceProvider::class,
	ClockworkServiceProvider::class,
	GuzzleClockworkServiceProvider::class,
	//App\SocialAuthServiceProvider::class, // This is really slow to load all the icons
	/*
	 * Application Service Providers...
	 */
	App\Providers\AppServiceProvider::class,
	App\Providers\AuthServiceProvider::class,
	App\Providers\BroadcastServiceProvider::class,
	App\Providers\EventServiceProvider::class,
	//App\Providers\AstralServiceProvider::class,
	//App\Providers\HorizonServiceProvider::class,
	App\Providers\RouteServiceProvider::class,
	App\Providers\TelescopeServiceProvider::class,
	//App\Providers\TenancyServiceProvider::class,
	App\Providers\VaporUiServiceProvider::class,
	SolutionServiceProvider::class,
	DBQueryLogServiceProvider::class,
    EloquentWherelikeServiceProvider::class,
	// We can just use cloudflare Moesif\Middleware\MoesifLaravelServiceProvider::class,
	//PragmaRX\Health\ServiceProvider::class,
	//\PhpUnitGen\Console\Container\ConsoleServiceProvider::class,
	//Orchestra\Parser\XmlServiceProvider::class,
    \SocialiteProviders\Manager\ServiceProvider::class,
	// NOTE: Add development-only providers in \App\Providers\AppServiceProvider::register()
];

return $config;
