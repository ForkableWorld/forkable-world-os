<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http;

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ClientAuth;
use App\Http\Middleware\LogoutMiddleware;
use App\Http\Middleware\SetGetFromRequest;
use App\Http\Middleware\SetMemoryLimit;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
class Kernel extends HttpKernel
{
	const WEB_MIDDLEWARE = 'web';
	protected function bootstrappers(): array{   // https://docs.bugsnag.com/platforms/php/laravel/#reporting-out-of-memory-exceptions
		return array_merge(
			[\Bugsnag\BugsnagLaravel\OomBootstrapper::class],
			parent::bootstrappers(),
		);
	}
    /**
     * The application's global HTTP middleware stack.
     * These middleware are run during every request to your application.
     * @var array
     */
    protected $middleware = [
	    \App\Http\Middleware\LoadEnv::class,
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        SetMemoryLimit::class,
	    SetGetFromRequest::class, // Need this for tests
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        self::WEB_MIDDLEWARE => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
	        LogoutMiddleware::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            //\App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            //'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'client' => [
            ClientAuth::class
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        //'auth' => \App\Http\Middleware\Authenticate::class,
        'auth' => \App\Http\Middleware\QMAuthenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'didt' => \App\Http\Middleware\CheckDIDT::class,
        'cache.response' => \Spatie\ResponseCache\Middlewares\CacheResponse::class,
        'admin' => AdminMiddleware::class,
	    'nice_artisan' => \App\Http\Middleware\NiceArtisanAuth::class,
    ];
}
