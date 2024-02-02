<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Providers;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Facade\Ignition\Http\Controllers\ExecuteSolutionController;
use Facade\Ignition\Http\Controllers\HealthCheckController;
use Facade\Ignition\Http\Controllers\ScriptController;
use Facade\Ignition\Http\Controllers\ShareReportController;
use Facade\Ignition\Http\Controllers\StyleController;
use Facade\Ignition\Http\Middleware\IgnitionConfigValueEnabled;
use Facade\Ignition\Http\Middleware\IgnitionEnabled;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
class RouteServiceProvider extends ServiceProvider {
    /**
     * This namespace is applied to your controller routes.
     * In addition, it is set as the URL generator's root namespace.
     * @var string
     */

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/home';
    /**
     * Define your route model bindings, pattern filters, etc.
     * @return void
     */
    public function boot() {
        $this->configureRateLimiting();

        $this->routes(function () {
            $this->mapApiRoutes();
            $this->mapWebRoutes();
            $this->registerIgnitionRoutes(); // https://github.com/facade/ignition/issues/202#issuecomment-595177155 Fixes InvalidArgumentException: Action Facade\Ignition\Http\Controllers\ExecuteSolutionController not defined on Jenkins
        });
    }
    /**
     * Define the routes for the application.
     * @return void
     */
    /**
     * Define the "web" routes for the application.
     * These routes all receive session state, CSRF protection, etc.
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->group(base_path('routes/web.php'));
    }
    /**
     * Define the "api" routes for the application.
     * These routes are typically stateless.
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->group(base_path('routes/api.php'));
    }
    /**
     * Fixes InvalidArgumentException: Action Facade\Ignition\Http\Controllers\ExecuteSolutionController not defined on Jenkins
     * https://github.com/facade/ignition/issues/202#issuecomment-595177155
     */
    private function registerIgnitionRoutes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        if (
            !class_exists(HealthCheckController::class)
            || !class_exists(ExecuteSolutionController::class)
            || !class_exists(IgnitionConfigValueEnabled::class)
            || !class_exists(ShareReportController::class)
            || !class_exists(ScriptController::class)
        ) {
            return;
        }

        Route::group([
            'prefix' => config('ignition.housekeeping_endpoint_prefix', '_ignition'),
            'middleware' => [IgnitionEnabled::class],
        ], function () {
            Route::get('health-check', HealthCheckController::class);

            Route::post('execute-solution', ExecuteSolutionController::class)
                ->middleware(IgnitionConfigValueEnabled::class.':enableRunnableSolutions');

            Route::post('share-report', ShareReportController::class)
                ->middleware(IgnitionConfigValueEnabled::class.':enableShareButton');

            Route::get('scripts/{script}', ScriptController::class);
            Route::get('styles/{style}', StyleController::class);
        });
    }


    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
